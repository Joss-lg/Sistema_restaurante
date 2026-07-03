<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja; // Cambia esto por tu modelo real (ej. CajaActiva, Turno, etc.)
use Illuminate\Http\Request;

class HistorialCajaController extends Controller
{
    public function index()
    {
        // Paginamos de 10 en 10 ordenando por el más reciente
        $turnos = Caja::with('user') // Suponiendo que la caja pertenece a un usuario (empleado)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.historial_cajas.index', compact('turnos'));
    }

    public function show($id)
    {
        $turno = Caja::with(['user', 'ventas'])->findOrFail($id);
        return view('admin.historial_cajas.show', compact('turno'));
    }
}