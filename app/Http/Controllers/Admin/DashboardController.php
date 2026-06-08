<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $ordersToday = Orden::select(['id', 'total', 'cuenta_dividida', 'numero_cuenta_division', 'cerrada_el'])
            ->where('estado', 'pagada')
            ->whereDate('cerrada_el', $today)
            ->get();

        $ventasDia = $ordersToday->sum('total');
        $ordenesDia = $ordersToday->count();
        $ticketPromedio = $ordenesDia > 0 ? $ventasDia / $ordenesDia : 0;
        $clientes = $ordersToday->reduce(function ($carry, $orden) {
            $personas = $orden->cuenta_dividida && $orden->numero_cuenta_division > 1
                ? $orden->numero_cuenta_division
                : 1;
            return $carry + $personas;
        }, 0);

        $hourlyRaw = Orden::selectRaw('HOUR(cerrada_el) as hour, SUM(total) as total, COUNT(*) as orders')
            ->where('estado', 'pagada')
            ->whereDate('cerrada_el', $today)
            ->groupByRaw('HOUR(cerrada_el)')
            ->orderByRaw('HOUR(cerrada_el)')
            ->get()
            ->keyBy('hour');

        $labels = ['10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
        $salesData = [];
        $ordersData = [];

        foreach (range(10, 17) as $hour) {
            $hourRecord = $hourlyRaw->get($hour);
            $salesData[] = $hourRecord ? (float) $hourRecord->total : 0;
            $ordersData[] = $hourRecord ? (int) $hourRecord->orders : 0;
        }

        $stats = [
            'ventas_dia' => $ventasDia,
            'ordenes_dia' => $ordenesDia,
            'ticket_promedio' => $ticketPromedio,
            'clientes' => $clientes,
        ];

        $chart = [
            'labels' => $labels,
            'sales' => $salesData,
            'transactions' => $ordersData,
        ];

        return view('admin.dashboard', compact('stats', 'chart'));
    }
}
