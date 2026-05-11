<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use Illuminate\Http\Request;

class CocinaController extends Controller
{
    public function index()
    {
        $ordenes = Orden::with(['mesa', 'mesero', 'detalles.producto'])
            ->whereIn('estado', ['pendiente', 'en proceso', 'servida'])
            ->orderBy('abierta_el', 'asc')
            ->get();

        return view('admin.cocina.index', compact('ordenes'));
    }

    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en proceso,servida',
        ]);

        $orden = Orden::findOrFail($id);
        $orden->estado = $request->estado;
        $orden->save();

        return redirect()->route('admin.cocina.index')->with('success', 'Estado de la orden actualizado correctamente.');
    }
}
