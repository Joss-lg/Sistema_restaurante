<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InventarioController extends Controller
{
    /**
     * Muestra el panel principal del Inventario.
     */
    public function index()
    {
        // 1. Obtenemos todos los insumos activos con su categoría acotando columnas
        $insumos = Insumo::with('categoria:id,nombre')
                         ->where('esta_activo', true)
                         ->orderBy('nombre')
                         ->get();
                        
        // 2. Categorías para selectores de modales
        $categorias = Categoria::orderBy('nombre')->select(['id', 'nombre'])->get();
        
        // 3. Métricas calculadas eficientemente en memoria
        $totalInsumos = $insumos->count();
        $valorInventario = $insumos->sum(function($insumo) {
            return (float)$insumo->stock_actual * (float)$insumo->precio_compra;
        });
        
        // 4. Filtrado de alertas de stock bajo
        $alertasStock = $insumos->filter(function($insumo) {
            return $insumo->stock_actual <= $insumo->stock_minimo;
        });

        // 5. Historial rápido de los últimos 10 movimientos con Eager Loading
        $ultimosMovimientos = MovimientoInventario::with(['insumo:id,nombre,unidad_medida', 'usuario:id,nombre'])
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
        $request->merge([
            'nombre'        => trim($request->nombre),
            'unidad_medida' => trim($request->unidad_medida)
        ]);

        $request->validate([
            'nombre'        => 'required|string|max:255',
            'categoria_id'  => 'required|exists:categorias,id',
            'unidad_medida' => 'required|string|max:20', 
            'stock_minimo'  => 'required|numeric|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
        ]);

        // Generación de código único concurrente basado en conteo total histórico
        $conteoHistorico = Insumo::withTrashed()->count();
        $codigo = 'INS-' . str_pad($conteoHistorico + 1, 3, '0', STR_PAD_LEFT);

        Insumo::create([
            'codigo'        => $codigo,
            'nombre'        => $request->nombre,
            'categoria_id'  => $request->categoria_id,
            'unidad_medida' => $request->unidad_medida,
            'stock_actual'  => 0, // Inicia en cero hasta su primer movimiento de entrada
            'stock_minimo'  => $request->stock_minimo,
            'precio_compra' => $request->precio_compra,
            'esta_activo'   => true,
        ]);

        return redirect()->route('admin.inventario.index')
                         ->with('success', 'Insumo registrado correctamente en el catálogo.');
    }

    /**
     * Registra Entradas (Compras), Salidas (Mermas) o Ajustes de inventario.
     */
    public function registrarMovimiento(Request $request)
    {
        $request->validate([
            'insumo_id' => 'required|exists:insumos,id',
            'tipo'      => 'required|in:entrada,salida,ajuste_positivo,ajuste_negativo',
            'cantidad'  => 'required|numeric|gt:0',
            'motivo'    => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $insumo = Insumo::findOrFail($request->insumo_id);
            $cantidad = (float)$request->cantidad;

            // 1. Validar que no se generen existencias negativas en salidas o ajustes negativos
            if (in_array($request->tipo, ['salida', 'ajuste_negativo'], true) && $insumo->stock_actual < $cantidad) {
                DB::rollBack();
                return redirect()->back()->with('error', "No hay suficiente stock en el almacén de ({$insumo->nombre}) para realizar la operación.");
            }

            // 2. Modificación del stock real según el caso de flujo
            match ($request->tipo) {
                'entrada', 'ajuste_positivo' => $insumo->stock_actual += $cantidad,
                'salida', 'ajuste_negativo'  => $insumo->stock_actual -= $cantidad,
            };

            // 3. Persistencia del historial de auditoría
            MovimientoInventario::create([
                'insumo_id' => $insumo->id,
                'user_id'   => auth()->id(),
                'cantidad'  => $cantidad,
                'tipo'      => $request->tipo,
                'motivo'    => trim($request->motivo),
            ]);

            $insumo->save();
            DB::commit();

            return redirect()->back()->with('success', 'Movimiento de almacén procesado y stock actualizado con éxito.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error inesperado al procesar el inventario.');
        }
    }

    /**
     * Actualiza los parámetros base de control del insumo.
     */
    public function update(Request $request, $id)
    {
        $request->merge([
            'nombre'        => trim($request->nombre),
            'unidad_medida' => trim($request->unidad_medida)
        ]);

        $request->validate([
            'nombre'        => 'required|string|max:255',
            'unidad_medida' => 'required|string|max:20',
            'stock_minimo'  => 'required|numeric|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
        ]);

        $insumo = Insumo::findOrFail($id);
        $insumo->update([
            'nombre'        => $request->nombre,
            'unidad_medida' => $request->unidad_medida,
            'stock_minimo'  => $request->stock_minimo,
            'precio_compra' => $request->precio_compra,
        ]);

        return redirect()->route('admin.inventario.index')
                         ->with('success', "Los datos de {$insumo->nombre} fueron actualizados correctamente.");
    }

    /**
     * Desactiva lógicamente el insumo del almacén.
     */
    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);
        $insumo->update(['esta_activo' => false]);

        return redirect()->route('admin.inventario.index')
                         ->with('success', "El insumo {$insumo->nombre} fue dado de baja del almacén.");
    }

    /**
     * Genera y exporta el reporte PDF con insumos en stock crítico.
     */
    public function exportarPdfBajoStock()
    {
        // Validación de políticas / permisos nativos de tu arquitectura
        if (!auth()->user()->tienePermiso('gestionar.reporte')) {
            return back()->with('error', 'No tienes permiso para generar reportes.');
        }

        $insumos = Insumo::whereColumn('stock_actual', '<=', 'stock_minimo')
                         ->where('esta_activo', true)
                         ->with('categoria:id,nombre')
                         ->get();

        $pdf = Pdf::loadView('admin.inventario.bajo_stock_pdf', compact('insumos'));

        return $pdf->download('Reporte_Bajo_Stock_' . date('Ymd_His') . '.pdf');
    }
}