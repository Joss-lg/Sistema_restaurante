<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// Aquí importarás tus modelos de Ventas u Órdenes cuando los tengas
// use App\Models\Venta; 

class DashboardController extends Controller
{
    public function index()
    {
        // Por ahora mandamos datos de prueba exactos a tu captura
        $stats = [
            'ventas_dia' => 1220.00,
            'ordenes_dia' => 3,
            'ticket_promedio' => 406.67,
            'clientes' => 7.5
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
