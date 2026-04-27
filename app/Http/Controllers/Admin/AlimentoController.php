<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Models\Inventario; // Descomente cuando tenga el modelo

class AlimentoController extends Controller
{
    public function index()
    {
        // === SIMULACIÓN DE DATOS (Para que el modal funcione de una vez) ===
        // Cuando tenga base de datos, cambie esto por: Inventario::all() o similar.
        $ingredientes_disponibles = collect([
            (object)['id' => 1, 'nombre' => 'Harina', 'unidad' => 'gramos'],
            (object)['id' => 2, 'nombre' => 'Salsa de Tomate', 'unidad' => 'gramos'],
            (object)['id' => 3, 'nombre' => 'Queso Mozzarella', 'unidad' => 'gramos'],
            (object)['id' => 4, 'nombre' => 'Aceite de Oliva', 'unidad' => 'litros'],
            (object)['id' => 5, 'nombre' => 'Peperoni', 'unidad' => 'gramos'],
        ]);

        $categorias = collect(['Pizzas', 'Pastas', 'Bebidas', 'Postres', 'Ensaladas']);

        // Enviamos los datos a la vista
        return view('admin.alimentos.index', compact('ingredientes_disponibles', 'categorias'));
    }
}