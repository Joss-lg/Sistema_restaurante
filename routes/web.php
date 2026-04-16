<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
// Importamos los controladores necesarios
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermisoController;
use App\Http\Controllers\Admin\EmpleadoController; 

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

// Vista de inicio: El Login con teclado numérico
Route::get('/', function () {
    return view('auth.login'); 
})->name('login');

// Procesa el acceso por PIN (el 1010 del Admin)
Route::post('/login-pin', [LoginController::class, 'loginConPin'])->name('login.pin');

// Rutas estándar de Laravel (Email/Password para recuperación y login web)
Auth::routes();

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Solo usuarios autenticados)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // Dashboard Principal
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    /* --- Módulo de Empleados --- */
    Route::prefix('admin/empleados')->group(function () {
        Route::get('/', [EmpleadoController::class, 'index'])->name('admin.empleados.index');
        Route::post('/store', [EmpleadoController::class, 'store'])->name('admin.empleados.store');
        Route::delete('/{id}', [EmpleadoController::class, 'destroy'])->name('admin.empleados.destroy');
    });

    /* --- Módulo de Permisos --- */
    Route::post('/admin/permisos/store', [PermisoController::class, 'store'])
        ->name('permisos.store');

    // Logout
    Route::post('/logout', function () {
        Auth::logout();
        return redirect()->route('login');
    })->name('logout');
});