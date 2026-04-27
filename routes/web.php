<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\EmpleadoController;
use App\Http\Controllers\Admin\AlimentoController;
use App\Http\Controllers\Admin\InventarioController; // Importación correcta

Route::get('/', function () {
    return view('auth.login'); 
})->name('login');

Route::post('/login-pin', [LoginController::class, 'loginConPin'])->name('login.pin');

Auth::routes();

Route::middleware(['auth'])->group(function () {
    
    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    // MÓDULO DE EMPLEADOS
    Route::prefix('admin/empleados')->group(function () {
        Route::get('/', [EmpleadoController::class, 'index'])->name('admin.empleados.index');
        Route::post('/store', [EmpleadoController::class, 'store'])->name('admin.empleados.store');
        Route::delete('/{id}', [EmpleadoController::class, 'destroy'])->name('admin.empleados.destroy');
        Route::put('/{id}', [EmpleadoController::class, 'update'])->name('admin.empleados.update');
        Route::get('/{id}/permisos', [EmpleadoController::class, 'permisos'])->name('admin.empleados.permisos');
        Route::post('/{id}/permisos', [EmpleadoController::class, 'actualizarPermisos'])->name('admin.empleados.permisos.update');
    });

    // ==========================================
    // MÓDULO DE INVENTARIO (CORREGIDO)
    // ==========================================
    Route::prefix('admin/inventario')->group(function () {
        // Vista principal
        Route::get('/', [InventarioController::class, 'index'])
            ->name('admin.inventario.index');
        
        // RUTA PARA GUARDAR (La que necesita el modal de Agregar)
        Route::post('/store', [InventarioController::class, 'store'])
            ->name('admin.inventario.store');
        
        // Movimientos (Entradas/Salidas)
        Route::post('/movimiento', [InventarioController::class, 'registrarMovimiento'])
            ->name('admin.inventario.movimiento');
        
        // Editar detalles
        Route::put('/{id}', [InventarioController::class, 'update'])
            ->name('admin.inventario.update');
        
        // Dar de baja
        Route::delete('/{id}', [InventarioController::class, 'destroy'])
            ->name('admin.inventario.destroy');
    });

    // MÓDULO DE ALIMENTOS
    Route::prefix('admin/alimentos')->group(function () {
        Route::get('/', [AlimentoController::class, 'index']) 
            ->name('admin.productos.index');
    });

    // MÓDULO DE PROMOCIONES
    Route::prefix('admin/promociones')->group(function () {
        Route::get('/', function() { return "Módulo de Promociones - Próximamente"; })
            ->name('admin.promociones.index');
    });

    // PERMISOS
    Route::post('/admin/permisos/store', [PermisoController::class, 'store'])
        ->name('permisos.store');

    // LOGOUT
    Route::post('/logout', function () {
        Auth::logout();
        return redirect()->route('login');
    })->name('logout');

});