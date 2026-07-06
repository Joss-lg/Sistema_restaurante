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
    public function index()
    {
        $insumos = Insumo::with('categoria:id,nombre')
                            ->where('esta_activo', true)
                            ->orderBy('nombre')
                            ->get();
                        
        $categorias = Categoria::orderBy('nombre')->select(['id', 'nombre'])->get();
        
        $totalInsumos = $insumos->count();
        $valorInventario = $insumos->sum(function($insumo) {
            return (float)$insumo->stock_actual * (float)$insumo->precio_compra;
        });
        
        $alertasStock = $insumos->filter(function($insumo) {
            return $insumo->stock_actual <= $insumo->stock_minimo;
        });

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

        $conteoHistorico = Insumo::withTrashed()->count();
        $codigo = 'INS-' . str_pad($conteoHistorico + 1, 3, '0', STR_PAD_LEFT);

        // Lógica agregada: Convertir litros a mililitros
        $unidad = $request->unidad_medida;
        $stockMinimo = (float)$request->stock_minimo;
        if ($unidad === 'l') { $unidad = 'ml'; $stockMinimo *= 1000; }

        Insumo::create([
            'codigo'        => $codigo,
            'nombre'        => $request->nombre,
            'categoria_id'  => $request->categoria_id,
            'unidad_medida' => $unidad,
            'stock_actual'  => 0,
            'stock_minimo'  => $stockMinimo,
            'precio_compra' => $request->precio_compra,
            'esta_activo'   => true,
        ]);

        return redirect()->route('admin.inventario.index')
                            ->with('success', 'Insumo registrado correctamente en el catálogo.');
    }

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

            // Lógica agregada: Si el insumo es en ml y envían litros, convertir
            if ($insumo->unidad_medida === 'ml' && $request->has('unidad_movimiento') && $request->unidad_movimiento === 'l') {
                $cantidad *= 1000;
            }

            if (in_array($request->tipo, ['salida', 'ajuste_negativo'], true) && $insumo->stock_actual < $cantidad) {
                DB::rollBack();
                return redirect()->back()->with('error', "No hay suficiente stock en el almacén de ({$insumo->nombre}) para realizar la operación.");
            }

            match ($request->tipo) {
                'entrada', 'ajuste_positivo' => $insumo->stock_actual += $cantidad,
                'salida', 'ajuste_negativo'  => $insumo->stock_actual -= $cantidad,
            };

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
        
        // Lógica agregada: Convertir si cambian a litros
        $unidad = $request->unidad_medida;
        $stockMinimo = (float)$request->stock_minimo;
        if ($unidad === 'l') { $unidad = 'ml'; $stockMinimo *= 1000; }

        $insumo->update([
            'nombre'        => $request->nombre,
            'unidad_medida' => $unidad,
            'stock_minimo'  => $stockMinimo,
            'precio_compra' => $request->precio_compra,
        ]);

        return redirect()->route('admin.inventario.index')
                            ->with('success', "Los datos de {$insumo->nombre} fueron actualizados correctamente.");
    }

    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);
        $insumo->update(['esta_activo' => false]);

        return redirect()->route('admin.inventario.index')
                            ->with('success', "El insumo {$insumo->nombre} fue dado de baja del almacén.");
    }

    public function exportarPdfBajoStock()
    {
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