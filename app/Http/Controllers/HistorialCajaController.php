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

        // --- Movimiento total del turno (todos los métodos de pago) ---
        $saldoEstimado = $turno->monto_inicial + $totalVentas - $totalGastos;

        // --- Arqueo de efectivo: solo lo que pasa por el cajón ---
        // Mismo cálculo que usa el cierre y el PDF, para que los tres coincidan.
        $efectivo = app(\App\Services\CajaService::class)->calcularEfectivoEsperado($turno);

        $cerrado    = $turno->estado === 'cerrada';
        $diferencia = $cerrado ? (float) $turno->diferencia : null;

        return view('admin.historial_cajas.show', compact(
            'turno', 'historicoVentas', 'totalVentas',
            'ventasEfectivo', 'ventasTarjeta', 'ventasTransferencia',
            'historicoGastos', 'totalGastos', 'saldoEstimado',
            'efectivo', 'cerrado', 'diferencia'
        ));
    }
}