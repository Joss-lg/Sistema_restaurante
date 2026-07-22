<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CajaMovimiento; 

class HistorialCajaController extends Controller
{
    public function index()
    {
        // Cargamos los turnos de la tabla macro 'caja_movimientos'
        $turnos = CajaMovimiento::with('user') 
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.historial_cajas.index', compact('turnos'));
    }

    public function show($id)
    {
        // Buscamos el turno específico e incluimos sus flujos de dinero internos
        $turno = CajaMovimiento::with(['user', 'flujos'])->findOrFail($id);

        // --- Ventas (ingresos categoría 'Ventas') ---
        $historicoVentas = $turno->flujos
            ->where('categoria', 'Ventas')
            ->sortByDesc('fecha')
            ->values();

        $totalVentas         = $historicoVentas->sum('monto');
        $ventasEfectivo      = $historicoVentas->where('metodo_pago', 'efectivo')->sum('monto');
        $ventasTarjeta       = $historicoVentas->where('metodo_pago', 'tarjeta')->sum('monto');
        $ventasTransferencia = $historicoVentas->where('metodo_pago', 'transferencia')->sum('monto');

        // --- Gastos y salidas (egresos) ---
        $historicoGastos = $turno->flujos
            ->where('tipo', 'egreso')
            ->sortByDesc('fecha')
            ->values();

        $totalGastos = $historicoGastos->sum('monto');

        // --- Saldo estimado del turno ---
        $saldoEstimado = $turno->monto_inicial + $totalVentas - $totalGastos;

        return view('admin.historial_cajas.show', compact(
            'turno', 'historicoVentas', 'totalVentas',
            'ventasEfectivo', 'ventasTarjeta', 'ventasTransferencia',
            'historicoGastos', 'totalGastos', 'saldoEstimado'
        ));
    }
}