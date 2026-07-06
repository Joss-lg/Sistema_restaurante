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
        
        return view('admin.historial_cajas.show', compact('turno'));
    }
}