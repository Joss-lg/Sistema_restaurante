<?php

namespace App\Http\Controllers;

use App\Models\CajaMovimiento;
use App\Models\FlujoCaja;
use App\Models\Mesa;
use App\Models\User;
use App\Models\PropinaMesero;
use App\Services\CajaService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class CajaController extends Controller
{
    protected $cajaService;
    protected $ticketService;

    public function __construct(CajaService $cajaService, TicketService $ticketService)
    {
        $this->cajaService = $cajaService;
        $this->ticketService = $ticketService;
    }

    public function index()
    {
        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->first();

        if (!$cajaActiva) {
            return view('admin.caja.apertura');
        }

        $mesas = Mesa::orderBy('numero', 'asc')->with(['ordenesActivas.detalles.producto', 'plataformaDelivery'])->get();

        $mesas->each(function ($mesa) {
            if ($mesa->estado === Mesa::ESTADO_OCUPADA && $mesa->ordenesActivas()->count() === 0) {
                $this->cajaService->liberarMesa($mesa);
                $mesa->estado = Mesa::ESTADO_DISPONIBLE;
            }
        });

        $mesas->each(function ($mesa) {
            if ($mesa->estado === Mesa::ESTADO_OCUPADA && $mesa->ordenesActivas->isNotEmpty()) {
                $desglose = $this->cajaService->obtenerDesgloseMesa($mesa);
                $mesa->total_real = $desglose['total'];
            } else {
                $mesa->total_real = 0;
            }
        });

        $mesasActivas = $mesas->where('estado', Mesa::ESTADO_OCUPADA)->count();
        $totalAbierto = $mesas->where('estado', Mesa::ESTADO_OCUPADA)->sum(fn($m) => floatval($m->total_real ?? 0));

        // Se cuenta ANTES de filtrar. Las mesas de delivery no cuentan como
        // "libres": son virtuales y desaparecen al cobrarse, así que este
        // número refleja solo las mesas reales del salón.
        $mesasLibres = $mesas
            ->where('estado', Mesa::ESTADO_DISPONIBLE)
            ->filter(fn($m) => !$m->esDelivery())
            ->count();

        // --- NUEVO: Caja solo muestra mesas con cuenta abierta ---
        // Las mesas libres ya no se listan aquí: en Caja no hay nada que
        // hacer con ellas y solo estorban. En cuanto se cobra una mesa
        // queda "disponible" y desaparece sola de esta pantalla.
        //
        // OJO: se filtra DESPUÉS de calcular los totales de arriba para que
        // las tarjetas de resumen sigan siendo correctas.
        // Las mesas NO se borran: siguen intactas en el plano espacial.
        $mesas = $mesas->where('estado', Mesa::ESTADO_OCUPADA)->values();

        return view('admin.caja.index', compact('mesas', 'mesasActivas', 'totalAbierto', 'mesasLibres', 'cajaActiva'));
    }

    public function abrir(Request $request)
    {
        $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
            'turno'         => 'required|in:Matutino,Vespertino',
        ]);

        if (CajaMovimiento::where('estado', 'abierta')->exists()) {
            return redirect()->back()->with('error', 'Ya existe una sesión de caja abierta actualmente.');
        }

        CajaMovimiento::create([
            'user_id'       => Auth::id(),
            'turno'         => $request->turno,
            'monto_inicial' => $request->monto_inicial,
        ]);

        return redirect()->route('admin.caja.index')->with('success', '¡Caja abierta correctamente!');
    }

    public function cerrar(Request $request)
    {
        $request->validate([
            'monto_final_real' => 'required|numeric|min:0',
            'comentarios'      => 'nullable|string|max:500'
        ]);

        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->firstOrFail();

        $propinasPendientes = PropinaMesero::where('caja_movimiento_id', $cajaActiva->id)
            ->where('pagada', false)
            ->get()
            ->groupBy('mesero_id');

        $totalPropinasEntregadas = 0;

        foreach ($propinasPendientes as $meseroId => $filas) {
            $montoMesero = $filas->sum('monto');
            if ($montoMesero <= 0) {
                continue;
            }

            $mesero = User::find($meseroId);
            $nombreMesero = $mesero ? $mesero->nombre : "Mesero #{$meseroId}";

            $egreso = FlujoCaja::create([
                'caja_movimiento_id' => $cajaActiva->id,
                'tipo'               => 'egreso',
                'categoria'          => 'Propinas',
                'concepto'           => "Propina — {$nombreMesero}",
                'monto'              => $montoMesero,
                'metodo_pago'        => 'efectivo',
                'fecha'              => now(),
            ]);

            PropinaMesero::whereIn('id', $filas->pluck('id'))
                ->update([
                    'pagada'        => true,
                    'pagada_el'     => now(),
                    'flujo_caja_id' => $egreso->id,
                ]);

            $totalPropinasEntregadas += $montoMesero;
        }

        // Se recalcula DESPUÉS de registrar las propinas como egreso, para que
        // el efectivo esperado ya las tenga descontadas. Usa el mismo método
        // que la pantalla y el PDF, así los tres números siempre coinciden.
        $desglose = $this->cajaService->calcularEfectivoEsperado($cajaActiva->fresh());

        $montoEsperado = $desglose['esperado'];
        $montoReal = (float) $request->monto_final_real;
        $diferencia = round($montoReal - $montoEsperado, 2);

        $cajaActiva->update([
            'monto_final_esperado' => $montoEsperado,
            'monto_final_real'     => $montoReal,
            'diferencia'           => $diferencia,
            'estado'               => 'cerrada',
            'comentarios'          => $request->comentarios
        ]);

        return redirect()->route('admin.caja.index')->with('success', 'La caja ha sido cerrada y el corte se generó con éxito.');
    }

    public function flujoDeCaja()
    {
        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->first();

        if (!$cajaActiva) {
            // Con la caja cerrada ya no se queda en un callejón sin salida:
            // se muestran los últimos turnos cerrados para poder consultarlos
            // o reimprimir su corte sin tener que abrir caja primero.
            $turnosCerrados = CajaMovimiento::with('user')
                ->where('estado', 'cerrada')
                ->orderByDesc('updated_at')
                ->limit(10)
                ->get();

            return view('admin.caja.apertura', compact('turnosCerrados'));
        }

        $baseVentas = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->ingresos()->porCategoria('Ventas');

        $totalVentas        = (clone $baseVentas)->sum('monto');
        $ventasEfectivo     = (clone $baseVentas)->porMetodoPago('efectivo')->sum('monto');
        $ventasTarjeta      = (clone $baseVentas)->porMetodoPago('tarjeta')->sum('monto');
        $ventasTransferencia = (clone $baseVentas)->porMetodoPago('transferencia')->sum('monto');
        
        $totalGastos   = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->egresos()->sum('monto');
        $saldoEstimado = $cajaActiva->monto_inicial + $totalVentas - $totalGastos;

        $historicoVentas = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->ingresos()->porCategoria('Ventas')->ordenado()->get();
        $historicoGastos = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->egresos()->ordenado()->get();

        $propinasPendientes = PropinaMesero::with('mesero:id,nombre')
            ->where('caja_movimiento_id', $cajaActiva->id)
            ->where('pagada', false)
            ->get()
            ->groupBy('mesero_id')
            ->map(function ($filas) {
                return (object) [
                    'mesero_id' => $filas->first()->mesero_id,
                    'mesero'    => $filas->first()->mesero->nombre ?? ('Mesero #' . $filas->first()->mesero_id),
                    'total'     => $filas->sum('monto'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $totalPropinasPendientes = $propinasPendientes->sum('total');

        // Efectivo que debe haber físicamente en el cajón. Es el MISMO cálculo
        // que se usará al cerrar, para que el cajero no se lleve sorpresas.
        $efectivo = $this->cajaService->calcularEfectivoEsperado($cajaActiva);

        // Las propinas pendientes se pagan al cerrar (salen del cajón), así que
        // el efectivo que realmente debe quedar al final es el esperado menos
        // esas propinas. Este es el número contra el que se compara el conteo.
        $efectivoEsperadoAlCierre = round($efectivo['esperado'] - $totalPropinasPendientes, 2);

        return view('admin.caja.flujo', compact(
            'cajaActiva', 'totalVentas', 'ventasEfectivo', 'ventasTarjeta', 'ventasTransferencia',
            'totalGastos', 'saldoEstimado', 'historicoVentas', 'historicoGastos',
            'propinasPendientes', 'totalPropinasPendientes',
            'efectivo', 'efectivoEsperadoAlCierre'
        ));
    }

    public function generarReportePdf($id, Request $request)
    {
        $cajaActiva = CajaMovimiento::with('user')->findOrFail($id);
        
        $totalVentas   = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->ingresos()->porCategoria('Ventas')->sum('monto');
        $totalGastos   = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->egresos()->sum('monto');
        $saldoEstimado = $cajaActiva->monto_inicial + $totalVentas - $totalGastos;

        $historicoVentas = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->ingresos()->porCategoria('Ventas')->ordenado()->get();
        $historicoGastos = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->egresos()->ordenado()->get();

        // Desglose del efectivo del cajón, con el mismo cálculo del cierre.
        $efectivo = $this->cajaService->calcularEfectivoEsperado($cajaActiva);

        // Si el turno ya está cerrado se usan los valores que quedaron
        // guardados en el corte (son el registro oficial de lo que se contó);
        // si sigue abierto, se muestra el esperado del momento y aún no hay
        // diferencia que reportar.
        $cerrado = $cajaActiva->estado === 'cerrada';
        $montoEsperado = $cerrado ? (float) $cajaActiva->monto_final_esperado : $efectivo['esperado'];
        $montoReal     = $cerrado ? (float) $cajaActiva->monto_final_real : null;
        $diferencia    = $cerrado ? (float) $cajaActiva->diferencia : null;

        $pdf = Pdf::loadView('admin.caja.reporte_pdf', compact(
            'cajaActiva', 'totalVentas', 'totalGastos', 'saldoEstimado',
            'historicoVentas', 'historicoGastos',
            'efectivo', 'cerrado', 'montoEsperado', 'montoReal', 'diferencia'
        ));

        // Nombre con la fecha del turno para que los archivos descargados no
        // se pisen entre sí ni haya que abrirlos para saber a cuál corresponde.
        $fecha = optional($cajaActiva->updated_at ?? $cajaActiva->created_at)->format('Y-m-d') ?? 'sin-fecha';
        $nombre = "corte-caja-{$fecha}-turno-{$cajaActiva->id}.pdf";

        // ?descargar=1 fuerza la descarga; sin el parámetro se abre en el
        // navegador. Así el mismo enlace sirve para los dos botones.
        return $request->boolean('descargar')
            ? $pdf->download($nombre)
            : $pdf->stream($nombre);
    }

    public function imprimirTicket($mesaId)
    {
        // AJUSTE: ahora recibe el ID de la MESA (no de una orden individual).
        // Una mesa puede tener varias órdenes activas (varias rondas de
        // envío a cocina) y el ticket final debe reflejarlas TODAS, igual
        // que ya hace CajaService::obtenerDesgloseMesa() en el modal de cobro.
        $datos = $this->ticketService->obtenerDatosTicketPorMesa((int) $mesaId);
        return view('admin.caja.ticket', $datos);
    }

    public function toggleIva(Request $request)
    {
        $habilitado = $request->boolean('habilitado');
        session(['iva_habilitado' => $habilitado]);

        return response()->json([
            'success' => true,
            'ivaHabilitado' => $habilitado
        ]);
    }
}