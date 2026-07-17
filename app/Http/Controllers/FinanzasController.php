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
use Barryvdh\DomPDF\Facade\Pdf;

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
            ->orderBy('nombre')
            ->select(['id', 'nombre'])
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
     * CORTE MENSUAL: desglose día por día de ingresos, gastos y nómina,
     * con totales del mes, filtrable por mes y año.
     */
    public function corteMensual(Request $request): View
    {
        [$mes, $año] = $this->sanearMesAño($request);

        $inicioMes = Carbon::create($año, $mes, 1)->startOfMonth();
        $finMes    = $inicioMes->copy()->endOfMonth();

        $movimientos = FlujoCaja::whereBetween('fecha', [$inicioMes, $finMes])
            ->orderBy('fecha', 'asc')
            ->get();

        $porDia = $movimientos->groupBy(fn ($m) => $m->fecha->toDateString());

        $dias = collect();
        $balanceAcumulado = 0;

        for ($d = 1; $d <= $inicioMes->daysInMonth; $d++) {
            $fecha = Carbon::create($año, $mes, $d);
            $fila  = $this->armarFilaDia($fecha, $porDia, $balanceAcumulado);
            $dias->push($fila);
        }

        $totales = $this->calcularTotales($dias);

        $categoriasIngresos = $movimientos->where('tipo', 'ingreso')
            ->groupBy('categoria')
            ->map(fn ($grupo) => (object) [
                'total'    => (float) $grupo->sum('monto'),
                'cantidad' => $grupo->count(),
            ]);

        $categoriasEgresos = $movimientos->where('tipo', 'egreso')
            ->groupBy('categoria')
            ->map(fn ($grupo) => (object) [
                'total'    => (float) $grupo->sum('monto'),
                'cantidad' => $grupo->count(),
            ]);

        $primerAño = FlujoCaja::min('fecha');
        $primerAño = $primerAño ? Carbon::parse($primerAño)->year : now()->year;
        $añosDisponibles = range((int) now()->year, $primerAño);

        return view('admin.finanzas.corte-mensual', compact(
            'dias', 'totales', 'mes', 'año',
            'categoriasIngresos', 'categoriasEgresos',
            'añosDisponibles', 'inicioMes'
        ));
    }

    /**
     * NUEVO: Exportar el corte mensual a PDF.
     * Si el mes solicitado es el mes en curso, el corte llega solo
     * hasta el día de HOY (corte parcial "a la fecha"); si es un mes
     * pasado, incluye el mes completo.
     */
    public function exportarCortePDF(Request $request)
    {
        [$mes, $año] = $this->sanearMesAño($request);

        $inicioMes = Carbon::create($año, $mes, 1)->startOfMonth();
        $finMes    = $inicioMes->copy()->endOfMonth();

        // ¿Es el mes en curso? Entonces cortamos al día de hoy.
        $esMesActual = $inicioMes->isSameMonth(now(), true);
        $ultimoDia   = $esMesActual ? now()->day : $inicioMes->daysInMonth;
        $fechaCorte  = Carbon::create($año, $mes, $ultimoDia)->endOfDay();

        $movimientos = FlujoCaja::whereBetween('fecha', [$inicioMes, $fechaCorte])
            ->orderBy('fecha', 'asc')
            ->get();

        $porDia = $movimientos->groupBy(fn ($m) => $m->fecha->toDateString());

        $dias = collect();
        $balanceAcumulado = 0;

        for ($d = 1; $d <= $ultimoDia; $d++) {
            $fecha = Carbon::create($año, $mes, $d);
            $fila  = $this->armarFilaDia($fecha, $porDia, $balanceAcumulado);
            $dias->push($fila);
        }

        $totales = $this->calcularTotales($dias);

        $categoriasIngresos = $movimientos->where('tipo', 'ingreso')
            ->groupBy('categoria')
            ->map(fn ($grupo) => (object) [
                'total'    => (float) $grupo->sum('monto'),
                'cantidad' => $grupo->count(),
            ]);

        $categoriasEgresos = $movimientos->where('tipo', 'egreso')
            ->groupBy('categoria')
            ->map(fn ($grupo) => (object) [
                'total'    => (float) $grupo->sum('monto'),
                'cantidad' => $grupo->count(),
            ]);

        $pdf = Pdf::loadView('admin.finanzas.corte-mensual-pdf', [
            'dias'               => $dias,
            'totales'            => $totales,
            'mes'                => $mes,
            'año'                => $año,
            'inicioMes'          => $inicioMes,
            'fechaCorte'         => $fechaCorte,
            'esParcial'          => $esMesActual && $ultimoDia < $inicioMes->daysInMonth,
            'categoriasIngresos' => $categoriasIngresos,
            'categoriasEgresos'  => $categoriasEgresos,
            'generadoEn'         => now(),
            'generadoPor'        => auth()->user()->nombre ?? 'Sistema',
        ])->setPaper('letter', 'portrait');

        $sufijo = $esMesActual ? '_al_' . $fechaCorte->format('d') : '';
        $filename = "corte_mensual_{$año}_" . str_pad($mes, 2, '0', STR_PAD_LEFT) . $sufijo . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Exportar el corte mensual (desglose diario) a CSV
     */
    public function exportarCorteCSV(Request $request)
    {
        [$mes, $año] = $this->sanearMesAño($request);

        $inicioMes = Carbon::create($año, $mes, 1)->startOfMonth();
        $finMes    = $inicioMes->copy()->endOfMonth();

        $movimientos = FlujoCaja::whereBetween('fecha', [$inicioMes, $finMes])
            ->orderBy('fecha', 'asc')
            ->get();

        $porDia = $movimientos->groupBy(fn ($m) => $m->fecha->toDateString());

        $filename = "corte_mensual_{$año}_" . str_pad($mes, 2, '0', STR_PAD_LEFT) . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($porDia, $inicioMes, $año, $mes) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM para Excel

            // --- SECCIÓN 1: RESUMEN DIARIO ---
            fputcsv($file, ['CORTE MENSUAL', $inicioMes->translatedFormat('F Y')], ';');
            fputcsv($file, [], ';');
            fputcsv($file, ['Fecha', 'Ingresos', 'Gastos', 'Nómina', 'Total Egresos', 'Balance del Día', 'Balance Acumulado'], ';');

            $totIngresos = 0;
            $totGastos = 0;
            $totNomina = 0;
            $acumulado = 0;

            for ($d = 1; $d <= $inicioMes->daysInMonth; $d++) {
                $fecha = Carbon::create($año, $mes, $d);
                $movsDelDia = $porDia->get($fecha->toDateString(), collect());

                $ingresos = $movsDelDia->where('tipo', 'ingreso')->sum('monto');
                $nomina   = $movsDelDia->where('tipo', 'egreso')->where('categoria', 'Nómina')->sum('monto');
                $gastos   = $movsDelDia->where('tipo', 'egreso')->where('categoria', '!=', 'Nómina')->sum('monto');
                $egresos  = $gastos + $nomina;
                $balance  = $ingresos - $egresos;
                $acumulado += $balance;

                $totIngresos += $ingresos;
                $totGastos   += $gastos;
                $totNomina   += $nomina;

                fputcsv($file, [
                    $fecha->format('Y-m-d'),
                    number_format((float) $ingresos, 2, '.', ''),
                    number_format((float) $gastos, 2, '.', ''),
                    number_format((float) $nomina, 2, '.', ''),
                    number_format((float) $egresos, 2, '.', ''),
                    number_format((float) $balance, 2, '.', ''),
                    number_format((float) $acumulado, 2, '.', ''),
                ], ';');
            }

            fputcsv($file, [
                'TOTALES',
                number_format((float) $totIngresos, 2, '.', ''),
                number_format((float) $totGastos, 2, '.', ''),
                number_format((float) $totNomina, 2, '.', ''),
                number_format((float) ($totGastos + $totNomina), 2, '.', ''),
                number_format((float) ($totIngresos - $totGastos - $totNomina), 2, '.', ''),
                '',
            ], ';');

            // --- SECCIÓN 2: DETALLE DE MOVIMIENTOS ---
            fputcsv($file, [], ';');
            fputcsv($file, ['DETALLE DE MOVIMIENTOS'], ';');
            fputcsv($file, ['Fecha', 'Hora', 'Tipo', 'Categoría', 'Concepto', 'Monto', 'Método de Pago'], ';');

            foreach ($porDia as $movsDelDia) {
                foreach ($movsDelDia as $flujo) {
                    fputcsv($file, [
                        $flujo->fecha->format('Y-m-d'),
                        $flujo->fecha->format('H:i'),
                        $flujo->getTipoLegible(),
                        $flujo->categoria,
                        $flujo->concepto,
                        number_format((float) $flujo->monto, 2, '.', ''),
                        $flujo->metodo_pago,
                    ], ';');
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper privado: sanear mes y año de la request
     */
    private function sanearMesAño(Request $request): array
    {
        $mes = (int) $request->query('mes', now()->month);
        $año = (int) $request->query('año', now()->year);

        if ($mes < 1 || $mes > 12) {
            $mes = now()->month;
        }
        if ($año < 2020 || $año > (int) now()->year + 1) {
            $año = now()->year;
        }

        return [$mes, $año];
    }

    /**
     * Helper privado: construir la fila-resumen de un día
     * ($balanceAcumulado se pasa por referencia y se va acumulando)
     */
    private function armarFilaDia(Carbon $fecha, $porDia, &$balanceAcumulado): object
    {
        $movsDelDia = $porDia->get($fecha->toDateString(), collect());

        $ingresos = $movsDelDia->where('tipo', 'ingreso')->sum('monto');
        $nomina   = $movsDelDia->where('tipo', 'egreso')
                               ->where('categoria', 'Nómina')
                               ->sum('monto');
        $gastos   = $movsDelDia->where('tipo', 'egreso')
                               ->where('categoria', '!=', 'Nómina')
                               ->sum('monto');

        $egresos  = $gastos + $nomina;
        $balance  = $ingresos - $egresos;
        $balanceAcumulado += $balance;

        return (object) [
            'fecha'             => $fecha,
            'ingresos'          => (float) $ingresos,
            'gastos'            => (float) $gastos,
            'nomina'            => (float) $nomina,
            'egresos'           => (float) $egresos,
            'balance'           => (float) $balance,
            'balance_acumulado' => (float) $balanceAcumulado,
            'movimientos'       => $movsDelDia,
            'tiene_movimientos' => $movsDelDia->isNotEmpty(),
        ];
    }

    /**
     * Helper privado: totales a partir de la colección de días
     */
    private function calcularTotales($dias): object
    {
        return (object) [
            'ingresos' => (float) $dias->sum('ingresos'),
            'gastos'   => (float) $dias->sum('gastos'),
            'nomina'   => (float) $dias->sum('nomina'),
            'egresos'  => (float) $dias->sum('egresos'),
            'balance'  => (float) $dias->sum('balance'),
        ];
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