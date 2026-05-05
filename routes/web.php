<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\EmpleadoController;
use App\Http\Controllers\Admin\AlimentoController;
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\CajaController;
use App\Http\Controllers\Admin\MesaController;
use App\Http\Controllers\ComandaController;

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
        
        // ---> RUTA ACTUALIZADA PARA TRAER LAS MESAS AL DASHBOARD <---
        Route::get('/dashboard', function () {
            $usuario = auth()->user();
            $rol = strtolower(trim($usuario->rol));
            $query = \App\Models\Mesa::query();

            if ($rol === 'mesero' && Schema::hasColumn('mesas', 'mesero_id')) {
                $query->where('mesero_id', $usuario->id);
            } else {
                $query->where('estado', 'ocupada');
            }

            $mesas = $query->get();
            return view('mesero.dashboard', compact('mesas'));
        })->name('dashboard');

        // ---> RUTA ACTUALIZADA PARA TRAER DATOS REALES DE POSTGRESQL <---
        Route::get('/comanda', function () {
            return redirect()->route('mesero.dashboard');
        })->name('comanda');

        // ---> NUEVA RUTA PARA ENVIAR LA COMANDA <---
        Route::post('/comanda/enviar', [ComandaController::class, 'enviar'])->name('comanda.enviar');

        Route::get('/comanda/{mesa}', [ComandaController::class, 'show'])->name('comanda.show');

        // ---> NUEVA RUTA PARA GUARDAR LA MESA <---
        Route::post('/mesa/store', [ComandaController::class, 'storeMesa'])->name('mesa.store');

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

        Route::get('/cobrar/{id}', [CajaController::class, 'cobrar'])->name('cobrar');

        // Rutas de API y Store
        Route::get('/api/estadisticas', [CajaController::class, 'getEstadisticas'])->name('api.estadisticas');
        Route::get('/api/movimientos', [CajaController::class, 'getMovimientos'])->name('api.movimientos');
        Route::post('/api/store', [CajaController::class, 'store'])->name('api.store');
        Route::post('/api/pagar', [CajaController::class, 'pagar'])->name('api.pagar');
    });

    // ==========================================
    // MÓDULO DE MESAS ACTIVO (CORREGIDO) 
    // ==========================================
    Route::prefix('admin/mesas')->name('admin.mesas.')->group(function () {
        Route::get('/', [MesaController::class, 'index'])->name('index');
        Route::get('/api/mesas', [MesaController::class, 'getMesas'])->name('api.mesas');
        Route::patch('/api/{id}/estado', [MesaController::class, 'cambiarEstado'])->name('api.estado');
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