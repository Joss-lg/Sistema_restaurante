<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class InventarioController extends Controller
{
    /**
     * Muestra el panel principal del Inventario.
     */
    public function index()
    {
        // 1. Obtenemos todos los insumos activos con su categoría
        $insumos = Insumo::with('categoria')
                        ->where('esta_activo', true)
                        ->orderBy('nombre')
                        ->get();
                        
        // 2. Obtenemos categorías para el modal de "Agregar Insumo"
        $categorias = Categoria::orderBy('nombre')->get();
        
        // 3. Calculamos métricas para las tarjetas superiores (Dashboard del inventario)
        $totalInsumos = $insumos->count();
        $valorInventario = $insumos->sum(function($insumo) {
            return $insumo->stock_actual * $insumo->precio_compra;
        });
        
        // 4. Alertas de stock bajo
        $alertasStock = $insumos->filter(function($insumo) {
            return $insumo->stock_actual <= $insumo->stock_minimo;
        });

        // 5. Últimos movimientos (Historial rápido)
        $ultimosMovimientos = MovimientoInventario::with(['insumo', 'usuario'])
                                ->orderBy('created_at', 'desc')
                                ->take(10)
                                ->get();

        return view('admin.inventario.tabla-inventario', compact(
            'insumos', 
            'categorias', 
            'totalInsumos', 
            'valorInventario', 
            'alertasStock',
            'ultimosMovimientos'
        ));
    }

    /**
     * Registra un nuevo Insumo en el catálogo del almacén.
     */
    public function store(Request $request)
    {
        // Validación estricta
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'unidad_medida' => 'required|string|max:20', // Ej: Kg, L, Pza
            'stock_minimo' => 'required|numeric|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
        ]);

        // Generamos un código único (Ej: INS-001)
        $ultimoId = Insumo::max('id') ?? 0;
        $codigo = 'INS-' . str_pad($ultimoId + 1, 3, '0', STR_PAD_LEFT);

        Insumo::create([
            'codigo' => $codigo,
            'nombre' => $request->nombre,
            'categoria_id' => $request->categoria_id,
            'unidad_medida' => $request->unidad_medida,
            'stock_actual' => 0, // Siempre empieza en 0 hasta que le den "Entrada"
            'stock_minimo' => $request->stock_minimo,
            'precio_compra' => $request->precio_compra,
            'esta_activo' => true,
        ]);

        return redirect()->route('admin.inventario.index')
        ->with('success', 'Insumo registrado correctamente en el catálogo.');
    }

    /**
     * La función más importante: Registra Entradas (Compras) o Salidas (Mermas).
     */
    public function registrarMovimiento(Request $request)
    {
        $request->validate([
            'insumo_id' => 'required|exists:insumos,id',
            'tipo' => 'required|in:entrada,salida,ajuste',
            'cantidad' => 'required|numeric|gt:0',
            'motivo' => 'required|string|max:255',
        ]);

        // Usamos una transacción para que si algo falla, no se descuadre el inventario
        try {
            DB::beginTransaction();

            $insumo = Insumo::findOrFail($request->insumo_id);
            $cantidad = $request->cantidad;

            // 1. Guardamos el registro en el historial para auditoría
            MovimientoInventario::create([
                'insumo_id' => $insumo->id,
                'user_id' => auth()->id(), // El empleado que hizo el movimiento
                'cantidad' => $cantidad,
                'tipo' => $request->tipo,
                'motivo' => $request->motivo,
            ]);

            // 2. Actualizamos el stock real del insumo
            if ($request->tipo === 'entrada') {
                $insumo->stock_actual += $cantidad;
            } elseif ($request->tipo === 'salida' || $request->tipo === 'ajuste') {
                // Validamos que no deje el stock en negativo si es salida
                if ($insumo->stock_actual < $cantidad) {
                    return redirect()->back()->with('error', 'No hay suficiente stock para realizar esta salida.');
                }
                $insumo->stock_actual -= $cantidad;
            }

            $insumo->save();
            DB::commit();

            return redirect()->back()->with('success', 'Movimiento registrado y stock actualizado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error al registrar el movimiento.');
        }
    }

    public function update(Request $request, $id)
    {
        $insumo = Insumo::findOrFail($id);

        $request->validate([
            'nombre'       => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:20',
            'stock_minimo' => 'required|numeric|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
        ]);

        $insumo->update([
            'nombre'        => $request->nombre,
            'unidad_medida' => $request->unidad_medida,
            'stock_minimo'  => $request->stock_minimo,
            'precio_compra' => $request->precio_compra,
        ]);

        return redirect()->route('admin.inventario.index')
            ->with('success', 'Los datos de ' . $insumo->nombre . ' fueron actualizados correctamente.');
    }
    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);
        
        $insumo->update([
            'esta_activo' => false 
        ]);

        return redirect()->route('admin.inventario.index')
            ->with('success', 'El insumo ' . $insumo->nombre . ' fue dado de baja del almacén.');
    }
    public function exportarPdfBajoStock()
        {
            // 1. Validar permisos
            if (!auth()->user()->tienePermiso('gestionar.reporte')) {
                return back()->with('error', 'No tienes permiso para generar reportes.');
            }

            // 2. Obtener datos (donde el stock actual es menor o igual al mínimo)
            $insumos = Insumo::whereColumn('stock_actual', '<=', 'stock_minimo')
                            ->with('categoria') // Para que no haga muchas consultas
                            ->get();

            // 3. Pasar los datos a la vista y cargarla en DomPDF
            $pdf = Pdf::loadView('admin.inventario.bajo_stock_pdf', compact('insumos'));

            // 4. Descargar
            return $pdf->download('Reporte_Bajo_Stock_'.date('Ymd').'.pdf');
        }
}