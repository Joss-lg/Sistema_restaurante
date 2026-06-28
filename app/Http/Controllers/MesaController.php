<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Orden;
use App\Models\DetalleOrden;
use App\Services\MesaService;
use App\Services\ComandaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MesaController extends Controller
{
    protected $mesaService;

    public function __construct(MesaService $mesaService)
    {
        $this->mesaService = $mesaService;
    }

    public function index()
    {
        $mesas = $this->mesaService->obtenerMesasParaUsuario(auth()->user());
        return view('admin.mesas.index', compact('mesas'));
    }

    public function show($mesaId)
    {
        $mesa = Mesa::findOrFail($mesaId);
        $usuario = auth()->user();

        // Validaciones de acceso (asegúrate de que estos métodos existan en tu MesaService)
        $this->mesaService->verificarAccesoMesa($mesa, $usuario);
        $esCapitan = $this->mesaService->esCapitan($usuario);
        
        $categorias = Categoria::all();
        $productos = Producto::with(['categoria', 'modificadores'])->orderBy('nombre', 'asc')->get();
        
        $mesasAbiertas = $esCapitan ? Mesa::where('estado', 'ocupada')->orderBy('numero', 'asc')->get() : collect();
        
        $ordenActiva = Orden::where('mesa_id', $mesa->id)->where('estado', '!=', 'pagada')->latest()->first();
        
        $platillosEnviados = collect();
        if ($ordenActiva) {
            $platillosEnviados = DetalleOrden::where('orden_id', $ordenActiva->id)
                ->join('productos', 'detalles_orden.producto_id', '=', 'productos.id')
                ->select('detalles_orden.*', 'productos.nombre as nombre', 'detalles_orden.precio_unitario as precio')
                ->get();
        }

        return view('mesero.comanda', compact('mesa', 'categorias', 'productos', 'mesasAbiertas', 'esCapitan', 'platillosEnviados'));
    }

    public function enviar(Request $request)
{
    try {
        $request->validate([
            'mesa_id' => 'required|integer|exists:mesas,id',
            'platillos' => 'required|array|min:1',
        ]);

        $mesa = Mesa::findOrFail($request->mesa_id);
        
        // Llamamos al servicio inyectando los datos
        $orden = app(ComandaService::class)->procesarEnvio(
            $mesa, 
            $request->platillos, 
            auth()->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Orden enviada correctamente',
            'orden_id' => $orden->id
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

    public function abrirMesa(Request $request): JsonResponse
    {
        return $this->mesaService->abrirMesa($request);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'numero' => 'required|string|max:20|unique:mesas,numero',
            'capacidad' => 'required|integer|min:1',
            'estado' => 'nullable|string|in:disponible,ocupada,reservada',
            'zona' => 'nullable|string|in:salon,terraza,vip',
            'forma' => 'nullable|string|in:redonda,cuadrada'
        ]);

        $mesa = Mesa::create(array_merge($validated, [
            'posicion_x' => 20, 'posicion_y' => 20, 'ancho' => 60, 'alto' => 60
        ]));

        return response()->json(['success' => true, 'message' => 'Mesa creada', 'mesa' => $mesa], 201);
    }

    public function update(Request $request, $id)
    {
        $mesa = Mesa::findOrFail($id);
        $mesa->update($request->validate([
            'numero' => 'required|string|max:20',
            'capacidad' => 'required|integer|min:1',
            'estado' => 'required|string',
        ]));
        return response()->json(['success' => true, 'message' => 'Mesa actualizada', 'mesa' => $mesa]);
    }

    public function destroy($id)
    {
        Mesa::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Mesa eliminada']);
    }
}