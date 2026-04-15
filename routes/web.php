<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\EmpleadoController; 

// Inicio: Login
Route::get('/', function () {
    return view('auth.login'); 
});

Auth::routes();


// Rutas protegidas (Solo para usuarios logueados)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    // Empleados (Vista principal)
    Route::get('/admin/empleados', [EmpleadoController::class, 'index'])
        ->name('admin.empleados.index');

    Route::post('/admin/empleados/store', [EmpleadoController::class, 'store'])
        ->name('admin.empleados.store');
    
    Route::delete('/admin/empleados/{id}', [EmpleadoController::class, 'destroy'])->name('admin.empleados.destroy');

    // Permisos (Acción de guardado)
    Route::post('/admin/permisos/store', [PermisoController::class, 'store'])
        ->name('permisos.store');
});