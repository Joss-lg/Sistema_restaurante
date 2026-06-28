<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\{
    Auth\LoginController, DashboardController, PermisoController, EmpleadoController,
    AlimentoController, InventarioController, CategoriaController, CajaController,
    CocinaController, MesaController, PlanoEspacialController, ComandaController,
    PromocionController, RolController, FinanzasController
};

// --- AUTENTICACIÓN ---
Route::get('/', function () { return view('auth.login'); })->name('login');
Route::post('/login-pin', [LoginController::class, 'loginConPin'])->name('login.pin');
Auth::routes();

Route::middleware(['auth'])->group(function () {

    // --- MÓDULO MESERO ---
    Route::prefix('mesero')->name('mesero.')->group(function () {
        Route::get('/dashboard', [MesaController::class, 'index'])->name('dashboard');
        Route::get('/comanda/{mesa}', [MesaController::class, 'show'])->name('comanda.show');
        Route::post('/comanda/enviar', [MesaController::class, 'enviar'])->name('comanda.enviar');
        Route::post('/capitan/verify', [ComandaController::class, 'verificarCapitan'])->name('capitan.verify');
        Route::get('/mesas/abiertas', [ComandaController::class, 'apiMesasAbiertas'])->name('mesas.abiertas');
        Route::post('/mesa/store', [MesaController::class, 'store'])->name('mesa.store');
        Route::post('/mesa/reabrir', [ComandaController::class, 'reabrir'])->name('mesa.reabrir');
    });
    
    // --- MÓDULOS ADMINISTRATIVOS (URL LIMPIAS) ---
    // Quitamos el prefijo 'admin' de la URL, pero mantenemos el nombre 'admin.' para tus vistas
    Route::name('admin.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('empleados')->name('empleados.')->group(function () {
            Route::get('/', [EmpleadoController::class, 'index'])->name('index');
            Route::post('/store', [EmpleadoController::class, 'store'])->name('store');
            Route::delete('/{id}', [EmpleadoController::class, 'destroy'])->name('destroy');
            Route::put('/{id}', [EmpleadoController::class, 'update'])->name('update');
            Route::get('/{id}/permisos', [EmpleadoController::class, 'permisos'])->name('permisos');
            Route::post('/{id}/permisos', [EmpleadoController::class, 'actualizarPermisos'])->name('permisos.update');
        });

        Route::prefix('inventario')->name('inventario.')->group(function () {
            Route::get('/bajo-stock-pdf', [InventarioController::class, 'exportarPdfBajoStock'])->name('exportar_pdf_bajo_stock');
            Route::get('/', [InventarioController::class, 'index'])->name('index');
            Route::post('/store', [InventarioController::class, 'store'])->name('store');
            Route::post('/movimiento', [InventarioController::class, 'registrarMovimiento'])->name('movimiento');
            Route::put('/{id}', [InventarioController::class, 'update'])->name('update');
            Route::delete('/{id}', [InventarioController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('alimentos')->name('productos.')->group(function () {
            Route::get('/', [AlimentoController::class, 'index'])->name('index');
            Route::get('/api/productos', [AlimentoController::class, 'getProductos'])->name('api.productos');
            Route::get('/api/estadisticas', [AlimentoController::class, 'getEstadisticas'])->name('api.estadisticas');
            Route::post('/api/store', [AlimentoController::class, 'store'])->name('api.store');
            Route::put('/api/{id}', [AlimentoController::class, 'update'])->name('api.update');
            Route::delete('/api/{id}', [AlimentoController::class, 'destroy'])->name('api.destroy');
            Route::patch('/api/{id}/toggle-disponibilidad', [AlimentoController::class, 'toggleDisponibilidad'])->name('api.toggle');
        });

        Route::resource('categorias', CategoriaController::class);

        Route::prefix('caja')->name('caja.')->group(function () {
            Route::get('/', [CajaController::class, 'index'])->name('index');
            Route::get('/cobrar/{id}', [CajaController::class, 'cobrar'])->name('cobrar');
            Route::get('/api/estadisticas', [CajaController::class, 'getEstadisticas'])->name('api.estadisticas');
            Route::get('/api/movimientos', [CajaController::class, 'getMovimientos'])->name('api.movimientos');
            Route::get('/api/promociones-activas', [CajaController::class, 'getPromocionesActivas'])->name('api.promociones');
            Route::post('/api/store', [CajaController::class, 'store'])->name('api.store');
            Route::post('/api/pagar', [CajaController::class, 'pagar'])->name('api.pagar');
            Route::post('/api/liberar-mesa', [CajaController::class, 'liberarMesa'])->name('api.liberar-mesa');
            Route::post('/api/estado-mesa', [CajaController::class, 'getEstadoMesa'])->name('api.estado-mesa');
            Route::post('/api/abrir-mesa', [CajaController::class, 'abrirMesa'])->name('api.abrir-mesa');
            Route::delete('/{id}', [CajaController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('mesas')->name('mesas.')->group(function () {
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

        Route::prefix('plano-espacial')->name('plano-espacial.')->group(function () {
            Route::get('/', [PlanoEspacialController::class, 'index'])->name('index');
            Route::get('/api/mesas', [PlanoEspacialController::class, 'getMesas'])->name('api.mesas');
            Route::get('/api/mesas/{id}', [PlanoEspacialController::class, 'getMesa'])->name('api.mesa');
            Route::post('/api/guardar', [PlanoEspacialController::class, 'guardarPlano'])->name('api.guardar');
            Route::post('/api/crear', [PlanoEspacialController::class, 'crearMesa'])->name('api.crear');
            Route::post('/api/store', [PlanoEspacialController::class, 'store'])->name('api.store');
            Route::delete('/api/eliminar/{id}', [PlanoEspacialController::class, 'eliminarDelPlano'])->name('api.eliminar');
        });

        Route::prefix('cocina')->name('cocina.')->group(function () {
            Route::get('/', [CocinaController::class, 'index'])->name('index');
            Route::patch('/orden/{id}/estado', [CocinaController::class, 'actualizarEstado'])->name('orden.estado');
        });

       Route::prefix('promociones')->name('promociones.')->group(function () {
            Route::get('/', [PromocionController::class, 'index'])->name('index');
            
            Route::post('/store', [PromocionController::class, 'store'])->name('store');
            
            Route::get('/{promocion}/edit', [PromocionController::class, 'edit'])->name('edit');
            Route::put('/{promocion}', [PromocionController::class, 'update'])->name('update');
            Route::delete('/{promocion}', [PromocionController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RolController::class, 'index'])->name('index');
            Route::post('/', [RolController::class, 'store'])->name('store');
            Route::put('/{id}', [RolController::class, 'update'])->name('update');
            Route::delete('/{id}', [RolController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('finanzas')->name('finanzas.')->group(function () {
            Route::get('/', [FinanzasController::class, 'index'])->name('index');
            Route::get('/exportar-csv', [FinanzasController::class, 'exportarCSV'])->name('exportar');
            Route::post('/estadisticas-periodo', [FinanzasController::class, 'estadisticasPeriodo'])->name('estadisticas.periodo');
        });

        Route::prefix('gastos')->name('gastos.')->group(function () {
            Route::post('/', [FinanzasController::class, 'guardarGasto'])->name('store');
        });

        Route::prefix('pagos-nomina')->name('pagos-nomina.')->group(function () {
            Route::post('/', [FinanzasController::class, 'guardarNomina'])->name('store');
        });

        Route::post('/permisos/store', [PermisoController::class, 'store'])->name('permisos.store');
    });

    Route::post('/logout', function () { Auth::logout(); return redirect()->route('login'); })->name('logout');

    Route::patch('admin/empleados/{id}/reactivar', [EmpleadoController::class, 'reactivar'])->name('admin.empleados.reactivar');
});