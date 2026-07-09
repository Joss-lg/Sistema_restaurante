<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CocinaController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\ComandaController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\FinanzasController;
use App\Http\Controllers\PlanoEspacialController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\HistorialCajaController; 

// ==========================================
// --- AUTENTICACIÓN ---
// ==========================================
Route::get('/', function () { 
    return view('auth.login'); 
})->name('login');

Route::post('/login-pin', [LoginController::class, 'loginConPin'])->name('login.pin');
Auth::routes();

// ==========================================
// --- RUTAS PROTEGIDAS (AUTH) ---
// ==========================================
Route::middleware(['auth'])->group(function () {

    // ------------------------------------------
    // MÓDULO MESERO
    // ------------------------------------------
    Route::prefix('mesero')->name('mesero.')->group(function () {
        Route::get('/dashboard', [MesaController::class, 'index'])->name('dashboard');
        Route::get('/comanda/{mesa}', [MesaController::class, 'show'])->name('comanda.show');
        Route::post('/comanda/enviar', [MesaController::class, 'enviar'])->name('comanda.enviar');
        Route::post('/capitan/verify', [ComandaController::class, 'verificarCapitan'])->name('capitan.verify');
        Route::get('/mesas/abiertas', [ComandaController::class, 'apiMesasAbiertas'])->name('mesas.abiertas');
        Route::post('/mesa/store', [MesaController::class, 'store'])->name('mesa.store');
        Route::post('/mesa/reabrir', [ComandaController::class, 'reabrir'])->name('mesa.reabrir');
    });
    
    // ------------------------------------------
    // MÓDULOS ADMINISTRATIVOS
    // ------------------------------------------
    Route::name('admin.')->group(function () {
        
        // Bloqueo total: Requiere que la columna 'mostrar' del módulo 'Dashboard' esté en 1
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('permiso:Dashboard,mostrar');

        // --- EMPLEADOS ---
        Route::prefix('admin/empleados')->name('empleados.')->group(function () {
            Route::get('/', [EmpleadoController::class, 'index'])
                ->name('index')
                ->middleware('permiso:Empleados,mostrar');

            Route::post('/store', [EmpleadoController::class, 'store'])
                ->name('store')
                ->middleware('permiso:Empleados,crear');

            Route::put('/{id}', [EmpleadoController::class, 'update'])
                ->name('update')
                ->middleware('permiso:Empleados,editar');

            Route::delete('/{id}', [EmpleadoController::class, 'destroy'])
                ->name('destroy')
                ->middleware('permiso:Empleados,eliminar');
            
            // Gestión avanzada de permisos y reactivación
            Route::get('/{id}/permisos', [EmpleadoController::class, 'permisos'])
                ->name('permisos')
                ->middleware('permiso:Empleados,gestionar');

            Route::post('/{id}/permisos', [EmpleadoController::class, 'actualizarPermisos'])
                ->name('permisos.update')
                ->middleware('permiso:Empleados,gestionar');

            Route::patch('/{id}/reactivar', [EmpleadoController::class, 'reactivar'])
                ->name('reactivar')
                ->middleware('permiso:Empleados,gestionar');
        });

        // --- INVENTARIO ---
        Route::middleware(['role:Inventario'])->prefix('/admin/inventario')->name('inventario.')->group(function () {
            Route::get('/bajo-stock-pdf', [InventarioController::class, 'exportarPdfBajoStock'])->name('exportar_pdf_bajo_stock');
            Route::get('/', [InventarioController::class, 'index'])->name('index');
            Route::post('/store', [InventarioController::class, 'store'])->name('store');
            Route::post('/movimiento', [InventarioController::class, 'registrarMovimiento'])->name('movimiento');
            Route::put('/{id}', [InventarioController::class, 'update'])->name('update');
            Route::delete('/{id}', [InventarioController::class, 'destroy'])->name('destroy');
        });

        // --- PRODUCTOS (ALIMENTOS) ---
        Route::middleware(['role:Alimentos'])->prefix('productos')->name('productos.')->group(function () {
            Route::get('/', [ProductoController::class, 'index'])->name('index');
            
            // API Endpoints
            Route::get('/api/productos', [ProductoController::class, 'getProductos'])->name('api.productos');
            Route::get('/api/estadisticas', [ProductoController::class, 'getEstadisticas'])->name('api.estadisticas');
            Route::post('/api/store', [ProductoController::class, 'store'])->name('api.store');
            Route::put('/api/{id}', [ProductoController::class, 'update'])->name('api.update');
            Route::delete('/api/{id}', [ProductoController::class, 'destroy'])->name('api.destroy');
            Route::patch('/api/{id}/toggle-disponibilidad', [ProductoController::class, 'toggleDisponibilidad'])->name('api.toggle');
        });

        // --- CATEGORÍAS ---
        Route::middleware(['role:Categorias'])->resource('categorias', CategoriaController::class);

        // --- CAJA ---
        Route::middleware(['role:Caja'])->prefix('caja')->name('caja.')->group(function () {
            Route::get('/', [CajaController::class, 'index'])->name('index');
            Route::post('/abrir', [CajaController::class, 'abrir'])->name('abrir');
            Route::post('/cerrar', [CajaController::class, 'cerrar'])->name('cerrar');
            Route::get('/cobrar/{id}', [CajaController::class, 'cobrar'])->name('cobrar');
            
            // API Endpoints
            Route::get('/api/estadisticas', [CajaController::class, 'getEstadisticas'])->name('api.estadisticas');
            Route::get('/api/movimientos', [CajaController::class, 'getMovimientos'])->name('api.movimientos');
            Route::get('/api/promociones-activas', [CajaController::class, 'getPromocionesActivas'])->name('api.promociones');
            Route::post('/api/store', [CajaController::class, 'store'])->name('api.store');
            Route::post('/api/pagar', [CajaController::class, 'pagar'])->name('api.pagar');
            Route::post('/api/procesar-pago', [CajaController::class, 'procesarPago'])->name('procesar.pago.final');
            Route::post('/api/liberar-mesa', [CajaController::class, 'liberarMesa'])->name('api.liberar-mesa');
            Route::post('/api/estado-mesa', [CajaController::class, 'getEstadoMesa'])->name('api.estado-mesa');
            Route::post('/api/abrir-mesa', [CajaController::class, 'abrirMesa'])->name('api.abrir-mesa');
            Route::delete('/{id}', [CajaController::class, 'destroy'])->name('destroy');
        });

        // --- MESAS ---
        Route::middleware(['role:Mesas'])->prefix('mesas')->name('mesas.')->group(function () {
            Route::get('/', [MesaController::class, 'index'])->name('index');
            Route::get('/api/mesas', [MesaController::class, 'getMesas'])->name('api.mesas');
            Route::post('/api', [MesaController::class, 'store'])->name('api.store');
            Route::patch('/api/{id}/posicion', [MesaController::class, 'updatePosicion'])->name('api.posicion');
            Route::post('/api/posiciones', [MesaController::class, 'guardarPosiciones'])->name('api.posiciones');
            Route::patch('/api/fusionar', [MesaController::class, 'fusionarMesas'])->name('api.fusionar');
            Route::patch('/api/{id}/estado', [MesaController::class, 'cambiarEstado'])->name('api.estado');
            Route::put('/api/{id}', [MesaController::class, 'update'])->name('api.update');
            Route::delete('/api/{id}', [MesaController::class, 'destroy'])->name('api.destroy');
        });

        // --- PLANO ESPACIAL ---
        Route::middleware(['role:Mesas'])->prefix('plano-espacial')->name('plano-espacial.')->group(function () {
            Route::get('/', [PlanoEspacialController::class, 'index'])->name('index');
            Route::get('/api/mesas', [PlanoEspacialController::class, 'getMesas'])->name('api.mesas');
            Route::get('/api/mesas/{id}', [PlanoEspacialController::class, 'getMesa'])->name('api.mesa');
            Route::post('/api/guardar', [PlanoEspacialController::class, 'guardarPlano'])->name('api.guardar');
            Route::post('/api/crear', [PlanoEspacialController::class, 'store'])->name('api.crear');
            Route::post('/api/actualizar/{id}', [PlanoEspacialController::class, 'update']);
            Route::delete('/api/eliminar/{id}', [PlanoEspacialController::class, 'eliminarDelPlano'])->name('api.eliminar');
        });

        // --- COCINA ---
        Route::middleware(['role:Cocina'])->prefix('cocina')->name('cocina.')->group(function () {
            Route::get('/', [CocinaController::class, 'index'])->name('index');
            Route::patch('/orden/{id}/estado', [CocinaController::class, 'actualizarEstado'])->name('orden.estado');
        });

        // --- PROMOCIONES ---
        Route::middleware(['role:Promociones'])->prefix('promociones')->name('promociones.')->group(function () {
            Route::get('/', [PromocionController::class, 'index'])->name('index');
            Route::post('/store', [PromocionController::class, 'store'])->name('store');
            Route::get('/{promocion}/edit', [PromocionController::class, 'edit'])->name('edit');
            Route::put('/{promocion}', [PromocionController::class, 'update'])->name('update');
            Route::delete('/{promocion}', [PromocionController::class, 'destroy'])->name('destroy');
        }); 

        // --- ROLES ---
        Route::middleware(['role:Roles'])->prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RolController::class, 'index'])->name('index');
            Route::post('/', [RolController::class, 'store'])->name('store');
            Route::put('/{id}', [RolController::class, 'update'])->name('update');
            Route::delete('/{id}', [RolController::class, 'destroy'])->name('destroy');
        });

        // --- FINANZAS ---
        Route::middleware(['role:Finanzas'])->prefix('finanzas')->name('finanzas.')->group(function () {
            Route::get('/', [FinanzasController::class, 'index'])->name('index');
            Route::get('/exportar-csv', [FinanzasController::class, 'exportarCSV'])->name('exportar');
            Route::post('/estadisticas-periodo', [FinanzasController::class, 'estadisticasPeriodo'])->name('estadisticas.periodo');
        });

        // --- GASTOS Y NÓMINA ---
        Route::prefix('gastos')->name('gastos.')->group(function () {
            Route::post('/', [FinanzasController::class, 'guardarGasto'])->name('store');
        });

        Route::prefix('pagos-nomina')->name('pagos-nomina.')->group(function () {
            Route::post('/', [FinanzasController::class, 'guardarNomina'])->name('store');
        });

        Route::post('/permisos/store', [PermisoController::class, 'store'])->name('permisos.store');

    }); // Cierre del grupo 'admin.'

    // ------------------------------------------
    // HISTORIAL CAJAS (Independiente)
    // ------------------------------------------
    Route::middleware(['role:Historial de Cajas'])->prefix('historial-cajas')->name('historial.')->group(function () {
        Route::get('/', [HistorialCajaController::class, 'index'])->name('index');
        Route::get('/{id}', [HistorialCajaController::class, 'show'])->name('show');
    });

    // ------------------------------------------
    // LOGOUT
    // ------------------------------------------
    Route::post('/logout', function () { 
        Auth::logout(); 
        return redirect()->route('login'); 
    })->name('logout');
});