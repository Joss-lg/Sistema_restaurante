<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
// Importaciones de tus controladores en la carpeta Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\EmpleadoController; // <-- INDISPENSABLE AGREGAR ESTO

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

    // Permisos (Acción de guardado)
    Route::post('/admin/permisos/store', [PermisoController::class, 'store'])
        ->name('permisos.store');
});