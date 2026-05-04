<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\EmpleadoController;
use App\Http\Controllers\Admin\AlimentoController;
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\CajaController;

Route::get('/', function () {
    return view('auth.login'); 
})->name('login');

Route::post('/login-pin', [LoginController::class, 'loginConPin'])->name('login.pin');

Auth::routes();

Route::middleware(['auth'])->group(function () {
    
    // ==========================================
    // MÓDULO DEL MESERO (Pantalla POS completa)
    // ==========================================
    Route::prefix('mesero')->name('mesero.')->group(function () {
        
        Route::get('/dashboard', function () {
            return view('mesero.dashboard'); 
        })->name('dashboard');

        // ---> RUTA ACTUALIZADA PARA TRAER DATOS REALES DE POSTGRESQL <---
        Route::get('/comanda', function () {
            // Traemos las categorías activas
            $categorias = \App\Models\Categoria::all();
            
            // Traemos los productos junto con su categoría y sus modificadores de la tabla pivote
            // Nota: Si tu modelo se llama Alimento en lugar de Producto, solo cambia la palabra aquí abajo.
            $productos = \App\Models\Producto::with(['categoria', 'modificadores'])->get();
            
            return view('mesero.comanda', compact('categorias', 'productos')); 
        })->name('comanda');

    });
    
    // DASHBOARD ADMINISTRADOR
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // MÓDULO DE EMPLEADOS
    Route::prefix('admin/empleados')->name('admin.empleados.')->group(function () {
        Route::get('/', [EmpleadoController::class, 'index'])->name('index');
        Route::post('/store', [EmpleadoController::class, 'store'])->name('store');
        Route::delete('/{id}', [EmpleadoController::class, 'destroy'])->name('destroy');
        Route::put('/{id}', [EmpleadoController::class, 'update'])->name('update');
        Route::get('/{id}/permisos', [EmpleadoController::class, 'permisos'])->name('permisos');
        Route::post('/{id}/permisos', [EmpleadoController::class, 'actualizarPermisos'])->name('permisos.update');
    });

    // MÓDULO DE INVENTARIO
    Route::prefix('admin/inventario')->name('admin.inventario.')->group(function () {
        Route::get('/exportar-bajo-stock', [InventarioController::class, 'exportarBajoStock'])->name('exportar_bajo_stock');
        Route::get('/', [InventarioController::class, 'index'])->name('index');
        Route::post('/store', [InventarioController::class, 'store'])->name('store');
        Route::post('/movimiento', [InventarioController::class, 'registrarMovimiento'])->name('movimiento');
        Route::put('/{id}', [InventarioController::class, 'update'])->name('update');
        Route::delete('/{id}', [InventarioController::class, 'destroy'])->name('destroy');
    });

    // MÓDULO DE ALIMENTOS
    Route::prefix('admin/alimentos')->name('admin.productos.')->group(function () {
        Route::get('/', [AlimentoController::class, 'index'])->name('index');
        Route::get('/api/productos', [AlimentoController::class, 'getProductos'])->name('api.productos');
        Route::get('/api/estadisticas', [AlimentoController::class, 'getEstadisticas'])->name('api.estadisticas');
        Route::post('/api/store', [AlimentoController::class, 'store'])->name('api.store');
        Route::put('/api/{id}', [AlimentoController::class, 'update'])->name('api.update');
        Route::delete('/api/{id}', [AlimentoController::class, 'destroy'])->name('api.destroy');
        Route::patch('/api/{id}/toggle-disponibilidad', [AlimentoController::class, 'toggleDisponibilidad'])->name('api.toggle');
    });

    // ==========================================
    // MÓDULO DE CATEGORÍAS (CORREGIDO)
    // ==========================================
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('categorias', CategoriaController::class);
    });

    // ==========================================
    // MÓDULO DE CAJA
    // ==========================================
    Route::prefix('admin/caja')->name('admin.caja.')->group(function () {
        // Vista principal de las mesas
        Route::get('/', [CajaController::class, 'index'])->name('index');

        Route::get('/cobrar/{id}', function ($id) {
            return view('admin.caja.cobrar', ['mesaId' => $id]);
        })->name('cobrar');

        // Rutas de API y Store
        Route::get('/api/estadisticas', [CajaController::class, 'getEstadisticas'])->name('api.estadisticas');
        Route::get('/api/movimientos', [CajaController::class, 'getMovimientos'])->name('api.movimientos');
        Route::post('/api/store', [CajaController::class, 'store'])->name('api.store');
    });

    // ==========================================
    // MÓDULO DE MESAS ACTIVO (CORREGIDO) 
    // ==========================================
    Route::prefix('admin/mesas')->name('admin.mesas.')->group(function () {
        Route::get('/', function () {
            return view('admin.mesas.index'); 
        })->name('index');
    });

    Route::prefix('admin/cocina')->name('admin.cocina.')->group(function () {
        Route::get('/', function() { return "Módulo de Cocina KDS - Próximamente"; })->name('index');
    });

    Route::prefix('admin/promociones')->name('admin.promociones.')->group(function () {
        Route::get('/', function() { return "Módulo de Promociones - Próximamente"; })->name('index');
    });

    // PERMISOS Y LOGOUT
    Route::post('/admin/permisos/store', [PermisoController::class, 'store'])->name('permisos.store');
    Route::post('/logout', function () { Auth::logout(); return redirect()->route('login'); })->name('logout');

});