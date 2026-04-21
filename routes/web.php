<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\EmpleadoController;

Route::get('/', function () {
    return view('auth.login'); 
})->name('login');

Route::post('/login-pin', [LoginController::class, 'loginConPin'])->name('login.pin');

Auth::routes();

Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');


    Route::prefix('admin/empleados')->group(function () {
        Route::get('/', [EmpleadoController::class, 'index'])->name('admin.empleados.index');
        Route::post('/store', [EmpleadoController::class, 'store'])->name('admin.empleados.store');
        Route::delete('/{id}', [EmpleadoController::class, 'destroy'])->name('admin.empleados.destroy');
        Route::put('/{id}', [EmpleadoController::class, 'update'])->name('admin.empleados.update');
        Route::get('/{id}/permisos', [EmpleadoController::class, 'permisos'])->name('admin.empleados.permisos');
        Route::post('/{id}/permisos', [EmpleadoController::class, 'actualizarPermisos'])->name('admin.empleados.permisos.update');
    });

    Route::prefix('admin/inventario')->group(function () {
        Route::get('/', function() { return "Módulo de Inventario - Próximamente"; })
            ->name('admin.inventario.index');
    });

    Route::prefix('admin/alimentos')->group(function () {
        Route::get('/', function() { return "Módulo de Alimentos - Próximamente"; })
            ->name('admin.productos.index');
    });

    Route::prefix('admin/promociones')->group(function () {
        Route::get('/', function() { return "Módulo de Promociones - Próximamente"; })
            ->name('admin.promociones.index');
    });

    Route::post('/admin/permisos/store', [PermisoController::class, 'store'])
        ->name('permisos.store');


    Route::post('/logout', function () {
        Auth::logout();
        return redirect()->route('login');
    })->name('logout');

});