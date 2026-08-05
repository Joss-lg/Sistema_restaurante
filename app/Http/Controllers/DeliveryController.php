<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Orden;
use App\Models\PlataformaDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    /**
     * Crea un pedido de delivery: una "mesa" virtual (no aparece en el
     * plano físico) que sigue exactamente el mismo flujo que abrir una
     * mesa normal — el mesero cae directo a la comanda a capturar el
     * pedido igual que si fuera una mesa del salón.
     *
     * El % de comisión (y su IVA) de la plataforma elegida se "congela"
     * en la mesa en este momento, para que si luego cambias el % desde
     * Configuración > Delivery, este pedido no se recalcule con el nuevo.
     */
    public function crear(Request $request): JsonResponse
    {
        $request->validate([
            'plataforma_delivery_id' => 'required|integer|exists:plataformas_delivery,id',
        ]);

        $plataforma = PlataformaDelivery::findOrFail($request->plataforma_delivery_id);

        if (!$plataforma->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Esta plataforma está desactivada. Actívala en Configuración > Delivery.',
            ], 422);
        }

        $mesa = DB::transaction(function () use ($plataforma) {
            $mesa = Mesa::create([
                // Placeholder temporal: se reemplaza abajo por uno único
                // apoyado en el propio id, para no chocar con el índice
                // único de la columna 'numero'.
                'numero'                  => 'TMP-' . uniqid(),
                'capacidad'               => 1,
                'estado'                  => Mesa::ESTADO_OCUPADA,
                'tipo'                    => Mesa::TIPO_DELIVERY,
                'plataforma_delivery_id'  => $plataforma->id,
                'comision_porcentaje'     => $plataforma->comision_porcentaje,
                'comision_iva_porcentaje' => $plataforma->iva_comision_porcentaje,
                'mesero_id'               => auth()->id(),
                'posicion_x'              => 0,
                'posicion_y'              => 0,
                'ancho'                   => 60,
                'alto'                    => 60,
            ]);

            $mesa->update([
                'numero' => strtoupper($plataforma->slug) . '-' . $mesa->id,
            ]);

            Orden::create([
                'numero_orden' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                'mesa_id'      => $mesa->id,
                'mesero_id'    => auth()->id(),
                'estado'       => Orden::ESTADO_PENDIENTE,
                'total'        => 0,
                'abierta_el'   => now(),
                'personas'     => 1,
                'cuenta_dividida'        => false,
                'numero_cuenta_division' => 1,
                'total_cuentas_division' => 1,
            ]);

            return $mesa;
        });

        return response()->json([
            'success'  => true,
            'mesa_id'  => $mesa->id,
            'redirect' => route('mesero.comanda.show', $mesa->id),
        ], 201);
    }

    /**
     * Elimina una mesa de delivery que no tiene productos capturados.
     * Se llama cuando el mesero entra a un delivery y sale sin agregar nada.
     * Usa borrado suave para no romper historial si ya hay algo registrado.
     */
    public function cancelarVacio(int $mesaId): JsonResponse
    {
        $mesa = Mesa::withTrashed()->findOrFail($mesaId);

        // Solo aplica a delivery y solo si el usuario es el dueño o admin
        if (!$mesa->esDelivery()) {
            return response()->json(['success' => false, 'message' => 'No es un pedido de delivery.'], 422);
        }

        // Verificar que no tenga productos
        $tieneProductos = $mesa->ordenesActivas()
            ->whereHas('detalles', fn($q) => $q->where('estado', '!=', 'cancelado'))
            ->exists();

        if ($tieneProductos) {
            return response()->json(['success' => false, 'message' => 'El pedido ya tiene productos.'], 422);
        }

        DB::transaction(function () use ($mesa) {
            // Eliminar órdenes vacías
            $mesa->ordenes()->delete();
            // Eliminar la mesa virtual
            $mesa->delete();
        });

        return response()->json([
            'success'  => true,
            'redirect' => route('mesero.dashboard'),
        ]);
    }
}