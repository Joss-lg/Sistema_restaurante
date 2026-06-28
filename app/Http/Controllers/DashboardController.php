<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Muestra las métricas operativas y el reporte de ventas del día actual.
     */
    public function index()
    {
        $today = Carbon::today()->toDateString(); // Formato Y-m-d seguro para la base de datos

        // 1. Traer solo las órdenes pagadas de hoy optimizando campos
        $ordersToday = Orden::select(['id', 'total', 'cuenta_dividida', 'numero_cuenta_division', 'cerrada_el'])
            ->where('estado', 'pagada')
            ->whereDate('cerrada_el', $today)
            ->get();

        // 2. Cálculo de métricas principales mediante colecciones (en memoria, evitando más queries)
        $ventasDia      = $ordersToday->sum('total');
        $ordenesDia     = $ordersToday->count();
        $ticketPromedio = $ordenesDia > 0 ? $ventasDia / $ordenesDia : 0;
        
        // Estimación de comensales/clientes
        $clientes = $ordersToday->reduce(function ($carry, $orden) {
            $personas = ($orden->cuenta_dividida && $orden->numero_cuenta_division > 1)
                ? $orden->numero_cuenta_division
                : 1;
            return $carry + $personas;
        }, 0);

        // 3. Agrupación por horas usando la colección en memoria (Ahorramos una query SQL pesada)
        $hourlyRaw = $ordersToday->groupBy(function ($orden) {
            return Carbon::parse($orden->cerrada_el)->format('H'); // Extrae la hora '00' a '23'
        });

        // 4. Rango de operación dinámico (Ejemplo: de 08:00 a 23:00 para cubrir turnos completos)
        $startHour  = 9;
        $endHour    = 22;
        $labels     = [];
        $salesData  = [];
        $ordersData = [];

        foreach (range($startHour, $endHour) as $hour) {
            $formattedHour = str_pad($hour, 2, '0', STR_PAD_LEFT); // '09', '10', '11'...
            
            // Obtenemos el grupo de órdenes para esta hora específica
            $ordersInHour = $hourlyRaw->get($formattedHour);

            $labels[]     = "{$formattedHour}:00";
            $salesData[]  = $ordersInHour ? (float) $ordersInHour->sum('total') : 0.0;
            $ordersData[] = $ordersInHour ? $ordersInHour->count() : 0;
        }

        $stats = [
            'ventas_dia'      => $ventasDia,
            'ordenes_dia'     => $ordenesDia,
            'ticket_promedio' => $ticketPromedio,
            'clientes'        => $clientes,
        ];

        $chart = [
            'labels'       => $labels,
            'sales'        => $salesData,
            'transactions' => $ordersData,
        ];

        return view('admin.dashboard', compact('stats', 'chart'));
    }
}