<?php

namespace App\Services;

use App\Models\Mesa;
use App\Models\Orden;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MesaService
{
    public function obtenerMesasParaUsuario($usuario)
    {
        if ($this->esCapitan($usuario)) {
            return Mesa::orderBy('numero', 'asc')->get();
        }

        return Mesa::where('mesero_id', $usuario->id)
            ->whereIn('estado', ['ocupada', 'disponible'])
            ->orderBy('numero', 'asc')
            ->get();
    }

    public function esCapitan($usuario)
    {
        // Accedemos directamente al nombre del rol
        return strtolower(trim($usuario->rol?->nombre ?? '')) === 'capitán';
    }

    public function verificarAccesoMesa($mesa, $usuario)
    {
        $nombreRol = strtolower(trim($usuario->rol?->nombre ?? ''));

        // Si es Administrador, dejamos pasar siempre
        if ($nombreRol === 'administrador') {
            return; 
        }

        // Lógica para Meseros
        if ($nombreRol === 'mesero' && Schema::hasColumn('mesas', 'mesero_id')) {
            if ($mesa->mesero_id !== null && $mesa->mesero_id !== $usuario->id) {
                abort(403, 'No tienes permiso para ver esta mesa.');
            }
        }

        // Lógica para Capitanes
        if ($nombreRol === 'capitán' && $mesa->estado !== 'ocupada') {
            abort(403, 'Solo puedes ver mesas abiertas.');
        }
    }

    public function abrirMesa(Request $request)
    {
        $validated = $request->validate([
            'mesa_id' => 'required|integer|exists:mesas,id',
            'capacidad' => 'required|integer|min:1|max:20',
            'cuenta_dividida' => 'boolean',
            'total_cuentas_division' => 'nullable|integer|min:2|max:10',
        ]);

        $mesa = Mesa::findOrFail($validated['mesa_id']);

        if ($mesa->estado === 'ocupada') {
            return response()->json(['success' => false, 'message' => 'Esta mesa ya está ocupada.'], 422);
        }

        DB::transaction(function () use ($mesa, $validated) {
            $mesa->update([
                'capacidad' => $validated['capacidad'],
                'estado' => 'ocupada',
                'mesero_id' => auth()->id(),
                'updated_at' => Carbon::now(),
            ]);

            $totalCuentas = $validated['cuenta_dividida'] ? ($validated['total_cuentas_division'] ?? 1) : 1;
            
            for ($i = 1; $i <= $totalCuentas; $i++) {
                Orden::create([
                    'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                    'mesa_id' => $mesa->id,
                    'mesero_id' => auth()->id(),
                    'estado' => 'pendiente',
                    'total' => 0,
                    'abierta_el' => now(),
                    'cuenta_dividida' => $validated['cuenta_dividida'] ?? false,
                    'numero_cuenta_division' => $i,
                    'total_cuentas_division' => $totalCuentas,
                ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Mesa abierta correctamente.']);
    }
}