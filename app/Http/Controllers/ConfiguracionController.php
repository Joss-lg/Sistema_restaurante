<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConfiguracionController extends Controller
{
    /**
     * Activa o desactiva el cálculo de IVA de forma global.
     * Se llama desde el switch del modal de cobro.
     */
    public function toggleIva(Request $request): JsonResponse
    {
        $request->validate([
            'habilitado' => 'required|boolean',
        ]);

        Configuracion::establecer('iva_habilitado', $request->habilitado ? '1' : '0');

        return response()->json([
            'message'        => $request->habilitado
                ? 'IVA habilitado correctamente.'
                : 'IVA deshabilitado correctamente.',
            'iva_habilitado' => Configuracion::ivaHabilitado(),
            'iva_porcentaje' => Configuracion::ivaPorcentaje(),
        ]);
    }
}