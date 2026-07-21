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
    protected $ticketService; // <-- nuevo

    public function __construct(CajaService $cajaService, TicketService $ticketService)
    {
        $this->cajaService = $cajaService;
        $this->ticketService = $ticketService; // <-- nuevo
    }

    public function index()
    {
        $cajaActiva = CajaMovimiento::where('estado', 'abierta')->first();

        if (!$cajaActiva) {
            return view('admin.caja.apertura');
        }

        $mesas = Mesa::orderBy('numero', 'asc')->with(['ordenesActivas.detalles.producto'])->get();

        // Limpieza de estados inconsistentes
        $mesas->each(function ($mesa) {
            if ($mesa->estado === Mesa::ESTADO_OCUPADA && $mesa->ordenesActivas()->count() === 0) {
                $this->cajaService->liberarMesa($mesa);
                $mesa->estado = Mesa::ESTADO_DISPONIBLE;
            }
        });

        // --- AJUSTE: calculamos el total real de cada mesa ocupada usando
        // CajaService (la misma fuente que usa el modal de cobro), en vez
        // de confiar en el campo acumulado 'total_consumo', que se
        // desincroniza con descuentos, IVA y ediciones posteriores.
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

        return view('admin.caja.index', compact('mesas', 'mesasActivas', 'totalAbierto', 'cajaActiva'));
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

        // --- Reparto de propinas pendientes del turno, agrupadas por mesero ---
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

            // Egreso real que sale del cajón para pagarle al mesero
            $egreso = FlujoCaja::create([
                'caja_movimiento_id' => $cajaActiva->id,
                'tipo'               => 'egreso',
                'categoria'          => 'Propinas',
                'concepto'           => "Propina — {$nombreMesero}",
                'monto'              => $montoMesero,
                'metodo_pago'        => 'efectivo',
                'fecha'              => now(),
            ]);

            // Marcamos todas las filas de este mesero como pagadas, referenciando el egreso
            PropinaMesero::whereIn('id', $filas->pluck('id'))
                ->update([
                    'pagada'        => true,
                    'pagada_el'     => now(),
                    'flujo_caja_id' => $egreso->id,
                ]);

            $totalPropinasEntregadas += $montoMesero;
        }

        // --- Cálculo original, ahora ya incluye el egreso de propinas dentro de "egresos" ---
        $ventas = $cajaActiva->flujos()->porCategoria('Ventas')->sum('monto');
        $anticipos = $cajaActiva->flujos()->porCategoria('Anticipos')->sum('monto');
        $gastos = $cajaActiva->flujos()->egresos()->sum('monto');
        
        $montoEsperado = $cajaActiva->monto_inicial + $ventas + $anticipos - $gastos;
        $montoReal = $request->monto_final_real;
        $diferencia = $montoReal - $montoEsperado;

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
            return view('admin.caja.apertura');
        }

        // Usamos tus scopes del modelo y cambiamos los métodos de pago a minúsculas para alinearse con JS
        $baseVentas = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->ingresos()->porCategoria('Ventas');

        $totalVentas        = (clone $baseVentas)->sum('monto');
        $ventasEfectivo     = (clone $baseVentas)->porMetodoPago('efectivo')->sum('monto');
        $ventasTarjeta      = (clone $baseVentas)->porMetodoPago('tarjeta')->sum('monto');
        $ventasTransferencia = (clone $baseVentas)->porMetodoPago('transferencia')->sum('monto');
        
        $totalGastos   = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->egresos()->sum('monto');
        $saldoEstimado = $cajaActiva->monto_inicial + $totalVentas - $totalGastos;

        $historicoVentas = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->ingresos()->porCategoria('Ventas')->ordenado()->get();
        $historicoGastos = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->egresos()->ordenado()->get();

        // --- NUEVO: desglose de propinas pendientes de entregar en este turno ---
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

        return view('admin.caja.flujo', compact(
            'cajaActiva', 'totalVentas', 'ventasEfectivo', 'ventasTarjeta', 'ventasTransferencia',
            'totalGastos', 'saldoEstimado', 'historicoVentas', 'historicoGastos',
            'propinasPendientes', 'totalPropinasPendientes'
        ));
    }

    public function generarReportePdf($id)
    {
        $cajaActiva = CajaMovimiento::with('user')->findOrFail($id);
        
        $totalVentas   = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->ingresos()->porCategoria('Ventas')->sum('monto');
        $totalGastos   = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->egresos()->sum('monto');
        $saldoEstimado = $cajaActiva->monto_inicial + $totalVentas - $totalGastos;

        $historicoVentas = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->ingresos()->porCategoria('Ventas')->ordenado()->get();
        $historicoGastos = FlujoCaja::where('caja_movimiento_id', $cajaActiva->id)->egresos()->ordenado()->get();

        $pdf = Pdf::loadView('admin.caja.reporte_pdf', compact('cajaActiva', 'totalVentas', 'totalGastos', 'saldoEstimado', 'historicoVentas', 'historicoGastos'));
        return $pdf->stream('reporte-caja-turno-' . $cajaActiva->id . '.pdf');
    }

   public function imprimirTicket($id)
    {
        $datos = $this->ticketService->obtenerDatosTicket((int) $id);
        return view('admin.caja.ticket', $datos);
    }

  public function toggleIva(Request $request)
{
    // Obtenemos el estado actual de la sesión (por defecto true)
    $estadoActual = session('iva_habilitado', true);
    $nuevoEstado = !$estadoActual;
    
    // Guardamos el nuevo estado
    session(['iva_habilitado' => $nuevoEstado]);

    return response()->json([
        'success' => true,
        'ivaHabilitado' => $nuevoEstado
    ]);
}
}