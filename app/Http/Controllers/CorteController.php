<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CorteController extends Controller
{
    public function index(Request $request)
    {
        // Tomamos las fechas del request, o usamos hoy por defecto
        $fechaInicio = $request->input('fecha_inicio', today()->toDateString());
        $fechaFin = $request->input('fecha_fin', today()->toDateString());
        
        $ventasPorArea = $this->obtenerDatosCorte($fechaInicio, $fechaFin);

        return view('corte.index', compact('ventasPorArea', 'fechaInicio', 'fechaFin'));
    }

    public function descargarPdf(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', today()->toDateString());
        $fechaFin = $request->input('fecha_fin', today()->toDateString());
        $ventasPorArea = $this->obtenerDatosCorte($fechaInicio, $fechaFin);

        $pdf = Pdf::loadView('corte.pdf', compact('ventasPorArea', 'fechaInicio', 'fechaFin'));
        
        return $pdf->download('ventas_' . $fechaInicio . '_al_' . $fechaFin . '.pdf'); 
    }

    private function obtenerDatosCorte($fechaInicio, $fechaFin)
    {
        $resultados = DB::table('detalles_orden')
            ->join('ordenes', 'detalles_orden.orden_id', '=', 'ordenes.id')
            ->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            // Cambiamos el whereDate exacto por un rango
            ->whereDate('ordenes.created_at', '>=', $fechaInicio)
            ->whereDate('ordenes.created_at', '<=', $fechaFin)
            //->where('ordenes.estado', 'en cocina') 
            ->select(
                'productos.nombre as producto', 
                'categorias.area_impresion as area', 
                DB::raw('SUM(detalles_orden.cantidad) as total_vendido')
            )
            ->groupBy('productos.id', 'productos.nombre', 'categorias.area_impresion')
            ->get();

        return $resultados->groupBy('area');
    }
}