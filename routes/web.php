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
use App\Http\Controllers\MesaOperacionController; // <-- NUEVO CONTROLADOR IMPORTADO
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
        Route::post('/comanda/transferir', [ComandaController::class, 'transferirProductos'])->name('comanda.transferir');
        Route::patch('/comanda/{mesa}/personas', [MesaController::class, 'actualizarPersonas'])->name('comanda.personas'); 
        Route::get('/comanda/promociones/activas', [MesaController::class, 'promocionesActivas'])->name('comanda.promociones.activas');
        Route::get('/comanda/{mesa}/precuenta', [ComandaController::class, 'precuenta'])->name('comanda.precuenta');
    });
    
    // ------------------------------------------
    // MÓDULOS ADMINISTRATIVOS
    // ------------------------------------------
    Route::name('admin.')->group(function () {
        
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('permiso:Dashboard,mostrar');

        // --- EMPLEADOS ---
        Route::prefix('admin/empleados')->name('empleados.')->group(function () {
            Route::get('/', [EmpleadoController::class, 'index'])->name('index')->middleware('permiso:Empleados,mostrar');
            Route::post('/store', [EmpleadoController::class, 'store'])->name('store')->middleware('permiso:Empleados,crear');
            Route::put('/{id}', [EmpleadoController::class, 'update'])->name('update')->middleware('permiso:Empleados,editar');
            Route::delete('/{id}', [EmpleadoController::class, 'destroy'])->name('destroy')->middleware('permiso:Empleados,eliminar');
            Route::get('/{id}/permisos', [EmpleadoController::class, 'permisos'])->name('permisos')->middleware('permiso:Empleados,gestionar');
            Route::post('/{id}/permisos', [EmpleadoController::class, 'actualizarPermisos'])->name('permisos.update')->middleware('permiso:Empleados,gestionar');
            Route::patch('/{id}/reactivar', [EmpleadoController::class, 'reactivar'])->name('reactivar')->middleware('permiso:Empleados,gestionar');
        });

        // --- INVENTARIO ---
        Route::middleware(['permiso:Inventario,mostrar'])->prefix('/admin/inventario')->name('inventario.')->group(function () {
            Route::get('/bajo-stock-pdf', [InventarioController::class, 'exportarPdfBajoStock'])->name('exportar_pdf_bajo_stock');
            Route::get('/', [InventarioController::class, 'index'])->name('index');
            Route::post('/store', [InventarioController::class, 'store'])->name('store')->middleware('permiso:Inventario,crear');
            Route::post('/movimiento', [InventarioController::class, 'registrarMovimiento'])->name('movimiento')->middleware('permiso:Inventario,crear');
            Route::put('/{id}', [InventarioController::class, 'update'])->name('update')->middleware('permiso:Inventario,editar');
            Route::delete('/{id}', [InventarioController::class, 'destroy'])->name('destroy')->middleware('permiso:Inventario,eliminar');
        });

        // --- PRODUCTOS (ALIMENTOS) ---
        Route::middleware(['permiso:Productos,mostrar'])->prefix('productos')->name('productos.')->group(function () {
            Route::get('/', [ProductoController::class, 'index'])->name('index');
            Route::get('/api/productos', [ProductoController::class, 'getProductos'])->name('api.productos');
            Route::get('/api/estadisticas', [ProductoController::class, 'getEstadisticas'])->name('api.estadisticas');
            Route::post('/api/store', [ProductoController::class, 'store'])->name('api.store')->middleware('permiso:Productos,crear');
            Route::put('/api/{id}', [ProductoController::class, 'update'])->name('api.update')->middleware('permiso:Productos,editar');
            Route::patch('/api/{id}/toggle-disponibilidad', [ProductoController::class, 'toggleDisponibilidad'])->name('api.toggle')->middleware('permiso:Productos,editar');
            Route::delete('/api/{id}', [ProductoController::class, 'destroy'])->name('api.destroy')->middleware('permiso:Productos,eliminar');
        });

        // --- CATEGORÍAS ---
        Route::middleware(['permiso:Categorías,mostrar'])->resource('categorias', CategoriaController::class);

       // --- MÓDULO CAJA ---
        Route::middleware(['permiso:Caja,mostrar'])->prefix('caja')->name('caja.')->group(function () {
            
            // Dominios Financieros (CajaController)
            Route::get('/', [CajaController::class, 'index'])->name('index');
            Route::get('/flujo', [CajaController::class, 'flujoDeCaja'])->name('flujo');
            Route::get('/reporte-pdf/{id}', [CajaController::class, 'generarReportePdf'])->name('reporte.pdf');
            Route::get('/ticket/{id}', [CajaController::class, 'imprimirTicket'])->name('ticket.imprimir');
            Route::get('/api/estadisticas', [CajaController::class, 'getEstadisticas'])->name('api.estadisticas');
            Route::get('/api/movimientos', [CajaController::class, 'getMovimientos'])->name('api.movimientos');
            Route::get('/api/promociones-activas', [CajaController::class, 'getPromocionesActivas'])->name('api.promociones');
            
            Route::post('/abrir', [CajaController::class, 'abrir'])->name('abrir')->middleware('permiso:Caja,gestionar');
            Route::post('/cerrar', [CajaController::class, 'cerrar'])->name('cerrar')->middleware('permiso:Caja,gestionar');
            Route::post('/api/store', [CajaController::class, 'store'])->name('api.store')->middleware('permiso:Caja,crear');

            // Dominios Tácticos de Mesa y Cobros (MesaOperacionController)
            Route::get('/cobrar/{id}', [MesaOperacionController::class, 'cobrar'])->name('cobrar');
            Route::post('/api/estado-mesa', [MesaOperacionController::class, 'getEstadoMesa'])->name('api.estado-mesa');
            
            // CORRECCIÓN: Quitamos el '/api/' de la URL física y limpiamos el nombre para que machee con cobro.js
            Route::post('/procesar-pago', [MesaOperacionController::class, 'procesarPago'])->name('procesar-pago')->middleware('permiso:Caja,crear');
            
            Route::post('/api/liberar-mesa', [MesaOperacionController::class, 'liberarMesa'])->name('api.liberar-mesa')->middleware('permiso:Caja,gestionar');
            Route::post('/api/abrir-mesa', [MesaOperacionController::class, 'abrirMesa'])->name('api.abrir-mesa')->middleware('permiso:Caja,gestionar');
            Route::delete('/{id}', [MesaOperacionController::class, 'destroy'])->name('destroy')->middleware('permiso:Caja,eliminar');
        });

        // --- MESAS ---
        Route::middleware(['permiso:Mesas,mostrar'])->prefix('mesas')->name('mesas.')->group(function () {
            Route::get('/', [MesaController::class, 'index'])->name('index');
            Route::get('/api/mesas', [MesaController::class, 'getMesas'])->name('api.mesas');
            Route::post('/api', [MesaController::class, 'store'])->name('api.store')->middleware('permiso:Mesas,crear');
            Route::post('/api/posiciones', [MesaController::class, 'guardarPosiciones'])->name('api.posiciones')->middleware('permiso:Mesas,editar');
            Route::patch('/api/{id}/posicion', [MesaController::class, 'updatePosicion'])->name('api.posicion')->middleware('permiso:Mesas,editar');
            Route::patch('/api/fusionar', [MesaController::class, 'fusionarMesas'])->name('api.fusionar')->middleware('permiso:Mesas,editar');
            Route::patch('/api/{id}/estado', [MesaController::class, 'cambiarEstado'])->name('api.estado')->middleware('permiso:Mesas,editar');
            Route::put('/api/{id}', [MesaController::class, 'update'])->name('api.update')->middleware('permiso:Mesas,editar');
            Route::delete('/api/{id}', [MesaController::class, 'destroy'])->name('api.destroy')->middleware('permiso:Mesas,eliminar');
        });

        // --- PLANO ESPACIAL ---
        Route::middleware(['permiso:Mesas,mostrar'])->prefix('plano-espacial')->name('plano-espacial.')->group(function () {
            Route::get('/', [PlanoEspacialController::class, 'index'])->name('index');
            Route::get('/api/mesas', [PlanoEspacialController::class, 'getMesas'])->name('api.mesas');
            Route::get('/api/mesas/{id}', [PlanoEspacialController::class, 'getMesa'])->name('api.mesa');
            Route::post('/api/guardar', [PlanoEspacialController::class, 'guardarPlano'])->name('api.guardar')->middleware('permiso:Mesas,editar');
            Route::post('/api/crear', [PlanoEspacialController::class, 'store'])->name('api.crear')->middleware('permiso:Mesas,crear');
            Route::post('/api/actualizar/{id}', [PlanoEspacialController::class, 'update'])->middleware('permiso:Mesas,editar');
            Route::delete('/api/eliminar/{id}', [PlanoEspacialController::class, 'eliminarDelPlano'])->name('api.eliminar')->middleware('permiso:Mesas,eliminar');
        });

        // --- COCINA ---
        Route::middleware(['permiso:Cocina,mostrar'])->prefix('cocina')->name('cocina.')->group(function () {
            Route::get('/', [CocinaController::class, 'index'])->name('index');
            Route::patch('/orden/{id}/estado', [CocinaController::class, 'actualizarEstado'])->name('orden.estado')->middleware('permiso:Cocina,editar');
        });

        // --- PROMOCIONES ---
        Route::middleware(['permiso:Promociones,mostrar'])->prefix('promociones')->name('promociones.')->group(function () {
            Route::get('/', [PromocionController::class, 'index'])->name('index');
            Route::get('/{promocion}/edit', [PromocionController::class, 'edit'])->name('edit');
            Route::post('/store', [PromocionController::class, 'store'])->name('store')->middleware('permiso:Promociones,crear');
            Route::put('/{promocion}', [PromocionController::class, 'update'])->name('update')->middleware('permiso:Promociones,editar');
            Route::delete('/{promocion}', [PromocionController::class, 'destroy'])->name('destroy')->middleware('permiso:Promociones,eliminar');
        }); 

        // --- ROLES ---
        Route::middleware(['permiso:Roles,mostrar'])->prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RolController::class, 'index'])->name('index');
            Route::post('/', [RolController::class, 'store'])->name('store')->middleware('permiso:Roles,crear');
            Route::put('/{id}', [RolController::class, 'update'])->name('update')->middleware('permiso:Roles,editar');
            Route::delete('/{id}', [RolController::class, 'destroy'])->name('destroy')->middleware('permiso:Roles,eliminar');
        });

        // --- FINANZAS ---
        Route::middleware(['permiso:Finanzas,mostrar'])->prefix('finanzas')->name('finanzas.')->group(function () {
        Route::get('/', [FinanzasController::class, 'index'])->name('index');
        Route::get('/exportar-csv', [FinanzasController::class, 'exportarCSV'])->name('exportar');
        Route::post('/estadisticas-periodo', [FinanzasController::class, 'estadisticasPeriodo'])->name('estadisticas.periodo');
        Route::get('/corte-mensual', [FinanzasController::class, 'corteMensual'])->name('corte.mensual');
        Route::get('/corte-mensual/exportar', [FinanzasController::class, 'exportarCorteCSV'])->name('corte.exportar');
        Route::get('/corte-mensual/pdf', [FinanzasController::class, 'exportarCortePDF'])->name('corte.pdf');
    });

        // --- GASTOS Y NÓMINA ---
        Route::middleware(['permiso:Finanzas,crear'])->group(function () {
            Route::prefix('gastos')->name('gastos.')->group(function () {
                Route::post('/', [FinanzasController::class, 'guardarGasto'])->name('store');
            });
            Route::prefix('pagos-nomina')->name('pagos-nomina.')->group(function () {
                Route::post('/', [FinanzasController::class, 'guardarNomina'])->name('store');
            });
        });

        Route::post('/permisos/store', [PermisoController::class, 'store'])->name('permisos.store');

    });

    // ------------------------------------------
    // HISTORIAL CAJAS
    // ------------------------------------------
    Route::middleware(['permiso:Historial de Cajas,mostrar'])->prefix('historial-cajas')->name('historial.')->group(function () {
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