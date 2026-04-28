<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\EmpleadoController;
use App\Http\Controllers\Admin\AlimentoController;
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\Admin\CategoriaController; // <-- Agregada importación

Route::get('/', function () {
    return view('auth.login'); 
})->name('login');

Route::post('/login-pin', [LoginController::class, 'loginConPin'])->name('login.pin');

Auth::routes();

Route::middleware(['auth'])->group(function () {
    
    // DASHBOARD
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
        Route::get('/', [InventarioController::class, 'index'])->name('index');
        Route::post('/store', [InventarioController::class, 'store'])->name('store');
        Route::post('/movimiento', [InventarioController::class, 'registrarMovimiento'])->name('movimiento');
        Route::put('/{id}', [InventarioController::class, 'update'])->name('update');
        Route::delete('/{id}', [InventarioController::class, 'destroy'])->name('destroy');
    });

    // MÓDULO DE ALIMENTOS
    Route::prefix('admin/alimentos')->name('admin.productos.')->group(function () {
        Route::get('/', [AlimentoController::class, 'index'])->name('index');
    });

    // ==========================================
    // MÓDULO DE CATEGORÍAS (CORREGIDO)
    // ==========================================
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('categorias', CategoriaController::class);
    });

    // ==========================================
    // MÓDULOS EN CONSTRUCCIÓN (Para que el Sidebar no truene)
    // ==========================================
    Route::prefix('admin/caja')->name('admin.caja.')->group(function () {
        Route::get('/', function() { return "Módulo de Caja - Próximamente"; })->name('index');
    });

    Route::prefix('admin/mesas')->name('admin.mesas.')->group(function () {
        Route::get('/', function() { return "Módulo de Mesas - Próximamente"; })->name('index');
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