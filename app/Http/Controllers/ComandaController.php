<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Schema, DB, Log};
use App\Models\{Mesa, Categoria, Producto, Orden, DetalleOrden, User};
use App\Services\ComandaService;

class ComandaController extends Controller
{
    protected $comandaService;

    public function __construct(ComandaService $comandaService)
    {
        $this->comandaService = $comandaService;
    }

    public function show($mesaId)
    {
        $mesa = Mesa::findOrFail($mesaId);
        $usuario = auth()->user();
        $rolSlug = strtolower(trim($usuario->rol?->slug ?? ''));
        $esCapitan = $rolSlug === 'capitan';

        // Restricciones de acceso
        if ($rolSlug === 'mesero' && Schema::hasColumn('mesas', 'mesero_id') && $mesa->mesero_id !== $usuario->id) {
            abort(403, 'No tienes permiso para ver esta mesa.');
        }
        if ($esCapitan && $mesa->estado !== 'ocupada') {
            abort(403, 'Solo puedes ver mesas abiertas.');
        }

        $categorias = Categoria::all();
        $productos = Producto::with(['categoria', 'modificadores'])->orderBy('nombre', 'asc')->get();
        $mesasAbiertas = $esCapitan ? Mesa::where('estado', 'ocupada')->orderBy('numero', 'asc')->get() : collect();

        $ordenActiva = Orden::where('mesa_id', $mesa->id)->where('estado', '!=', 'pagada')->latest()->first();
        $platillosEnviados = $ordenActiva ? $ordenActiva->detalles()->with('producto')->get() : collect();

        return view('mesero.comanda', compact('mesa', 'categorias', 'productos', 'mesasAbiertas', 'esCapitan', 'platillosEnviados'));
    }

    public function enviar(Request $request)
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'platillos' => 'required|array|min:1',
        ]);

        try {
            $mesa = Mesa::findOrFail($request->mesa_id);
            $usuario = auth()->user();

            // Validación de permisos
            if ($usuario->rol?->slug !== 'capitan') {
                if (Schema::hasColumn('mesas', 'mesero_id') && $mesa->mesero_id !== $usuario->id) {
                    return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
                }
            }

            // Delegamos toda la lógica transaccional, inventario y orden al Service
            $orden = $this->comandaService->procesarEnvio($mesa, $request->platillos, $usuario);

            return response()->json([
                'success' => true,
                'message' => 'Orden enviada y mesa actualizada.',
                'orden_id' => $orden->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function verificarCapitan(Request $request)
    {
        $request->validate(['nip' => 'required|string']);
        $usuario = User::where('codigo_empleado', $request->nip)->first();

        if (!$usuario || strtolower($usuario->rol?->slug ?? '') !== 'capitan') {
            return response()->json(['success' => false, 'message' => 'Acceso denegado.'], 403);
        }

        $mesas = Mesa::where('estado', 'ocupada')->get(['id', 'numero', 'estado']);
        return response()->json(['success' => true, 'mesas' => $mesas]);
    }

    public function apiMesasAbiertas()
    {
        $mesas = Mesa::all();
        return response()->json([
            'success' => true,
            'mesas_abiertas' => $mesas->where('estado', 'ocupada')->values(),
            'mesas_libres' => $mesas->where('estado', 'disponible')->values(),
        ]);
    }

    public function storeMesa(Request $request)
    {
        // Tu lógica de apertura sigue aquí, pero podrías considerar moverla a un MesaService
        $request->validate(['numero' => 'required', 'capacidad' => 'required|integer']);
        
        $mesa = Mesa::updateOrCreate(['numero' => $request->numero], [
            'estado' => 'ocupada',
            'capacidad' => $request->capacidad,
            'mesero_id' => auth()->user()->id
        ]);
        
        // ... Lógica de creación de órdenes iniciales ...
        return response()->json(['success' => true, 'mesa' => $mesa]);
    }

    public function reabrir(Request $request)
    {
        $mesa = Mesa::findOrFail($request->mesa_id);
        $mesa->update(['estado' => 'ocupada', 'mesero_id' => auth()->user()->id]);
        
        // ... Creación de nueva orden ...
        return response()->json(['success' => true]);
    }
}