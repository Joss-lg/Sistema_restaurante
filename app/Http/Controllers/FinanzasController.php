<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FlujoCaja;
use App\Models\PagoNomina;
use App\Models\Gasto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class FinanzasController extends Controller
{
    /**
     * Mostrar el dashboard de finanzas con métricas y flujo de caja
     */
    public function index(Request $request): View
    {
        // 1. Obtener y validar el filtro de pestaña (todos, ingresos, egresos)
        $tab = $request->query('tab', 'todos');
        if (!in_array($tab, ['todos', 'ingresos', 'egresos'], true)) {
            $tab = 'todos';
        }

        // 2. Obtener empleados activos optimizando memoria
        $empleados = User::where('esta_activo', true)
            ->orderBy('nombre') // CAMBIADO DE 'name' A 'nombre'
            ->select(['id', 'nombre']) // CAMBIADO DE 'name' A 'nombre'
            ->get();

        // 3. Mes y año actual mediante Carbon fijo
        $now = Carbon::now();
        $mesActual = $now->month;
        $añoActual = $now->year;

        // --- MÉTRICAS MENSUALES ---
        $ingresosMes = FlujoCaja::ingresos()->delMes($mesActual, $añoActual)->sum('monto');
        $egresosMes  = FlujoCaja::egresos()->delMes($mesActual, $añoActual)->sum('monto');
        $balanceNeto = $ingresosMes - $egresosMes;

        // --- NÓMINA PENDIENTE ---
        $nominaPendiente = PagoNomina::pendientes()->sum('monto_neto');

        // --- FLUJO DE CAJA FILTRADO ---
        $query = FlujoCaja::query();

        match ($tab) {
            'ingresos' => $query->ingresos(),
            'egresos'  => $query->egresos(),
            default    => null,
        };

        // Paginación limpia
        $flujosCaja = $query->ordenado('desc')->paginate(20)->withQueryString();

        // --- ESTADÍSTICAS ADICIONALES POR CATEGORÍA ---
        $categoriasIngresos = FlujoCaja::ingresos()
            ->delMes($mesActual, $añoActual)
            ->selectRaw('categoria, SUM(monto) as total, COUNT(*) as cantidad')
            ->groupBy('categoria')
            ->get();

        $categoriasEgresos = FlujoCaja::egresos()
            ->delMes($mesActual, $añoActual)
            ->selectRaw('categoria, SUM(monto) as total, COUNT(*) as cantidad')
            ->groupBy('categoria')
            ->get();

        // --- ÚLTIMOS 7 DÍAS ---
        $ultimosSieteDias = FlujoCaja::entre($now->copy()->subDays(7)->startOfDay(), $now->endOfDay())
            ->ordenado('desc')
            ->get();

        // --- TOP 5 GASTOS DEL MES ---
        $top5Gastos = FlujoCaja::egresos()
            ->delMes($mesActual, $añoActual)
            ->orderByDesc('monto')
            ->limit(5)
            ->get();

        // --- TOP 5 MÉTODOS DE PAGO ---
        $metodosPago = FlujoCaja::delMes($mesActual, $añoActual)
            ->selectRaw('metodo_pago, tipo, SUM(monto) as total, COUNT(*) as cantidad')
            ->groupBy('metodo_pago', 'tipo')
            ->get();

        return view('admin.finanzas.index', compact(
            'ingresosMes', 'egresosMes', 'balanceNeto', 'nominaPendiente', 
            'flujosCaja', 'tab', 'categoriasIngresos', 'categoriasEgresos', 
            'ultimosSieteDias', 'top5Gastos', 'metodosPago', 'mesActual', 'añoActual', 'empleados'
        ));
    }

    /**
     * Exportar el flujo de caja a CSV
     */
    public function exportarCSV(Request $request)
    {
        $mes = $request->query('mes', now()->month);
        $año = $request->query('año', now()->year);

        $flujos = FlujoCaja::delMes($mes, $año)->ordenado('desc')->get();
        $filename = "flujo_caja_{$año}_{$mes}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($flujos) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Fecha', 'Tipo', 'Categoría', 'Concepto', 'Monto', 'Método de Pago'], ';');

            foreach ($flujos as $flujo) {
                fputcsv($file, [
                    $flujo->fecha ? $flujo->fecha->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                    $flujo->getTipoLegible(),
                    $flujo->categoria,
                    $flujo->concepto,
                    $flujo->monto,
                    $flujo->metodo_pago,
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Obtener estadísticas por período (AJAX)
     */
    public function estadisticasPeriodo(Request $request)
    {
        $request->validate([
            'fechaInicio' => 'required|date',
            'fechaFin'    => 'required|date|after_or_equal:fechaInicio',
        ]);

        $ingresos = FlujoCaja::ingresos()->entre($request->fechaInicio, $request->fechaFin)->sum('monto');
        $egresos  = FlujoCaja::egresos()->entre($request->fechaInicio, $request->fechaFin)->sum('monto');

        return response()->json([
            'ingresos' => (float)$ingresos,
            'egresos'  => (float)$egresos,
            'balance'  => (float)($ingresos - $egresos),
        ]);
    }

    /**
     * Guardar un nuevo gasto
     */
    public function guardarGasto(Request $request)
    {
        $request->validate([
            'concepto'    => 'required|string|max:255',
            'categoria'   => 'required|in:Compra Insumos,Servicios,Renta,Mantenimiento,Otro',
            'monto'       => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:Efectivo,Tarjeta,Transferencia',
            'estado'      => 'required|in:pendiente,pagado',
            'documento'   => 'nullable|string|max:100',
            'descripcion' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request) {
            Gasto::create([
                'concepto'    => $request->concepto,
                'categoria'   => $request->categoria,
                'monto'       => $request->monto,
                'metodo_pago' => $request->metodo_pago,
                'estado'      => $request->estado,
                'fecha'       => now(),
                'documento'   => $request->documento,
                'descripcion' => $request->descripcion,
            ]);

            if ($request->estado === 'pagado') {
                FlujoCaja::create([
                    'tipo'        => 'egreso',
                    'categoria'   => $request->categoria,
                    'concepto'    => "GASTO: " . $request->concepto,
                    'monto'       => $request->monto,
                    'metodo_pago' => $request->metodo_pago,
                    'fecha'       => now(),
                ]);
            }
        });

        return redirect()->route('admin.finanzas.index')->with('success', 'Gasto registrado correctamente.');
    }

    /**
     * Guardar un nuevo pago de nómina
     */
    public function guardarNomina(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'periodo'       => 'required|string|max:100',
            'sueldo_base'   => 'required|numeric|min:0',
            'bonos'         => 'nullable|numeric|min:0',
            'deducciones'   => 'nullable|numeric|min:0',
            'metodo_pago'   => 'required|in:Efectivo,Tarjeta,Transferencia',
            'estado'        => 'required|in:pendiente,pagado',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $sueldoBase  = $request->sueldo_base;
        $bonos       = $request->bonos ?? 0;
        $deducciones = $request->deducciones ?? 0;
        $montoNeto   = PagoNomina::calcularMontoNeto($sueldoBase, $bonos, $deducciones);

        DB::transaction(function () use ($request, $montoNeto) {
            $nomina = PagoNomina::create([
                'user_id'       => $request->user_id,
                'periodo'       => $request->periodo,
                'sueldo_base'   => $request->sueldo_base,
                'bonos'         => $request->bonos ?? 0,
                'deducciones'   => $request->deducciones ?? 0,
                'monto_neto'    => $montoNeto,
                'metodo_pago'   => $request->metodo_pago,
                'estado'        => $request->estado,
                'fecha_pago'    => $request->estado === 'pagado' ? now() : null,
                'observaciones' => $request->observaciones,
            ]);

            if ($request->estado === 'pagado') {
                FlujoCaja::create([
                    'tipo'        => 'egreso',
                    'categoria'   => 'Nómina',
                    'concepto'    => "PAGO NÓMINA - PERÍODO: " . $request->periodo . " (Emp: ID {$request->user_id})",
                    'monto'       => $montoNeto,
                    'metodo_pago' => $request->metodo_pago,
                    'fecha'       => now(),
                ]);
            }
        });

        return redirect()->route('admin.finanzas.index')->with('success', 'Pago de nómina registrado correctamente.');
    }
}