<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlujoCaja;
use App\Models\PagoNomina;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanzasController extends Controller
{
    /**
     * Mostrar el dashboard de finanzas con métricas y flujo de caja
     */
    public function index(Request $request): View
    {
        // Obtener el filtro de pestaña (todos, ingresos, egresos)
        $tab = $request->query('tab', 'todos');

        // Validar el valor de tab
        if (!in_array($tab, ['todos', 'ingresos', 'egresos'])) {
            $tab = 'todos';
        }

        // Obtener empleados para los selectores
        $empleados = \App\Models\User::where('esta_activo', true)
            ->orderBy('nombre')
            ->get();

        // Mes y año actual
        $mesActual = now()->month;
        $añoActual = now()->year;

        // --- MÉTRICAS MENSUALES ---

        // Calcular ingresos del mes
        $ingresosMes = FlujoCaja::ingresos()
            ->delMes($mesActual, $añoActual)
            ->sum('monto');

        // Calcular egresos del mes
        $egresosMes = FlujoCaja::egresos()
            ->delMes($mesActual, $añoActual)
            ->sum('monto');

        // Calcular balance neto
        $balanceNeto = $ingresosMes - $egresosMes;

        // --- NÓMINA PENDIENTE ---

        $nominaPendiente = PagoNomina::pendientes()
            ->sum('monto_neto');

        // --- FLUJO DE CAJA FILTRADO ---

        $query = FlujoCaja::query();

        // Aplicar filtro de pestaña
        match ($tab) {
            'ingresos' => $query->ingresos(),
            'egresos' => $query->egresos(),
            default => null,
        };

        // Obtener el listado ordenado por fecha descendente
        $flujosCaja = $query->ordenado('desc')->paginate(20);

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

        $ultimosSieteDias = FlujoCaja::entre(
            now()->subDays(7)->startOfDay(),
            now()->endOfDay()
        )->ordenado('desc')->get();

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

        return view('admin.finanzas.index', [
            'ingresosMes' => $ingresosMes,
            'egresosMes' => $egresosMes,
            'balanceNeto' => $balanceNeto,
            'nominaPendiente' => $nominaPendiente,
            'flujosCaja' => $flujosCaja,
            'tab' => $tab,
            'categoriasIngresos' => $categoriasIngresos,
            'categoriasEgresos' => $categoriasEgresos,
            'ultimosSieteDias' => $ultimosSieteDias,
            'top5Gastos' => $top5Gastos,
            'metodosPago' => $metodosPago,
            'mesActual' => $mesActual,
            'añoActual' => $añoActual,
            'empleados' => $empleados,
        ]);
    }

    /**
     * Exportar el flujo de caja a CSV
     */
    public function exportarCSV(Request $request)
    {
        $mes = $request->query('mes', now()->month);
        $año = $request->query('año', now()->year);

        $flujos = FlujoCaja::delMes($mes, $año)
            ->ordenado('desc')
            ->get();

        $filename = "flujo_caja_{$año}_{$mes}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($flujos) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Fecha', 'Tipo', 'Categoría', 'Concepto', 'Monto', 'Método de Pago'], ';');

            foreach ($flujos as $flujo) {
                fputcsv($file, [
                    $flujo->fecha->format('Y-m-d H:i:s'),
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
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
        ]);

        $ingresos = FlujoCaja::ingresos()
            ->entre($request->fechaInicio, $request->fechaFin)
            ->sum('monto');

        $egresos = FlujoCaja::egresos()
            ->entre($request->fechaInicio, $request->fechaFin)
            ->sum('monto');

        return response()->json([
            'ingresos' => $ingresos,
            'egresos' => $egresos,
            'balance' => $ingresos - $egresos,
        ]);
    }

    /**
     * Guardar un nuevo gasto
     */
    public function guardarGasto(Request $request)
    {
        $request->validate([
            'concepto' => 'required|string|max:255',
            'categoria' => 'required|in:Compra Insumos,Servicios,Renta,Mantenimiento,Otro',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:Efectivo,Tarjeta,Transferencia',
            'estado' => 'required|in:pendiente,pagado',
            'documento' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string|max:500',
        ]);

        \App\Models\Gasto::create([
            'concepto' => $request->concepto,
            'categoria' => $request->categoria,
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'estado' => $request->estado,
            'fecha' => now(),
            'documento' => $request->documento,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('admin.finanzas.index')->with('success', 'Gasto registrado correctamente');
    }

    /**
     * Guardar un nuevo pago de nómina
     */
    public function guardarNomina(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'periodo' => 'required|string|max:100',
            'sueldo_base' => 'required|numeric|min:0',
            'bonos' => 'nullable|numeric|min:0',
            'deducciones' => 'nullable|numeric|min:0',
            'metodo_pago' => 'required|in:Efectivo,Tarjeta,Transferencia',
            'estado' => 'required|in:pendiente,pagado',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $sueldoBase = $request->sueldo_base;
        $bonos = $request->bonos ?? 0;
        $deducciones = $request->deducciones ?? 0;
        $montoNeto = \App\Models\PagoNomina::calcularMontoNeto($sueldoBase, $bonos, $deducciones);

        \App\Models\PagoNomina::create([
            'user_id' => $request->user_id,
            'periodo' => $request->periodo,
            'sueldo_base' => $sueldoBase,
            'bonos' => $bonos,
            'deducciones' => $deducciones,
            'monto_neto' => $montoNeto,
            'metodo_pago' => $request->metodo_pago,
            'estado' => $request->estado,
            'fecha_pago' => $request->estado === 'pagado' ? now() : null,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('admin.finanzas.index')->with('success', 'Pago de nómina registrado correctamente');
    }
}

