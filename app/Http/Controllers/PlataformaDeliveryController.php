<?php

namespace App\Http\Controllers;

use App\Models\PlataformaDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PlataformaDeliveryController extends Controller
{
    /**
     * Pantalla de Configuración > Delivery: editar el % de comisión y el
     * % de IVA sobre esa comisión de cada plataforma (Rappi, Uber Eats,
     * DiDi Food...). Estos valores se negocian con cada plataforma y
     * cambian con el tiempo, por eso son editables aquí y no están fijos
     * en el código.
     */
    public function index()
    {
        $plataformas = PlataformaDelivery::orderBy('nombre')->get();

        return view('admin.delivery.index', compact('plataformas'));
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'comision_porcentaje'     => 'required|numeric|min:0|max:100',
            'iva_comision_porcentaje' => 'required|numeric|min:0|max:100',
            'activo'                  => 'boolean',
        ]);

        $plataforma = PlataformaDelivery::findOrFail($id);
        $plataforma->update($validated);

        return response()->json([
            'success'     => true,
            'message'     => "Comisión de {$plataforma->nombre} actualizada.",
            'plataforma'  => $plataforma,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre'                  => 'required|string|max:60',
            'slug'                    => 'required|string|max:30|alpha_dash|unique:plataformas_delivery,slug',
            'color'                   => 'nullable|string|max:20',
            'comision_porcentaje'     => 'required|numeric|min:0|max:100',
            'iva_comision_porcentaje' => 'required|numeric|min:0|max:100',
        ]);

        $plataforma = PlataformaDelivery::create(array_merge($validated, ['activo' => true]));

        return response()->json(['success' => true, 'plataforma' => $plataforma], 201);
    }

    public function destroy($id): JsonResponse
    {
        $plataforma = PlataformaDelivery::findOrFail($id);

        if ($plataforma->mesas()->where('tipo', 'delivery')->whereHas('ordenesActivas')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: hay pedidos activos con esta plataforma.',
            ], 422);
        }

        $plataforma->delete();

        return response()->json(['success' => true, 'message' => 'Plataforma eliminada.']);
    }
}