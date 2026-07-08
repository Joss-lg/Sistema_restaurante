<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DivisionCuentaService;

class PagoController extends Controller
{
    protected $divisionService;

    public function __construct(DivisionCuentaService $divisionService)
    {
        $this->divisionService = $divisionService;
    }

    public function procesarPago(Request $request)
    {
        // 1. Validaciones
        $request->validate([
            'orden_id'      => 'required|exists:ordenes,id',
            'monto'         => 'required|numeric|min:0.01',
            'tipo_division' => 'required|in:equitativa,por_producto,personalizado',
            'detalles_ids'  => 'array|required_if:tipo_division,por_producto',
        ]);

        try {
            // 2. Procesar a través del Service
            $transaccion = $this->divisionService->procesarPago($request->all());

            return response()->json([
                'message' => 'Pago procesado exitosamente.',
                'transaccion' => $transaccion
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}