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
use App\Http\Controllers\Admin\CocinaController;
use App\Http\Controllers\Admin\MesaController;
use App\Http\Controllers\Admin\PlanoEspacialController;
use App\Http\Controllers\ComandaController;
use App\Http\Controllers\Admin\PromocionController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\FinanzasController;

Route::get('/', function () {
    return view('auth.login'); 
})->name('login');

Route::post('/login-pin', [LoginController::class, 'loginConPin'])->name('login.pin');

Auth::routes();

Route::middleware(['auth'])->group(function () {

    Route::prefix('mesero')->name('mesero.')->group(function () {
        
        Route::get('/dashboard', function () {
            $esCapitan = strtolower(auth()->user()->rol ?? '') === 'capitan';

            if ($esCapitan) {
                $mesas = \App\Models\Mesa::orderBy('numero', 'asc')->get();
            } else {
                if (Schema::hasColumn('mesas', 'mesero_id')) {
                    $mesas = \App\Models\Mesa::where(function ($query) {
                            $query->where('estado', 'ocupada')
                                  ->where('mesero_id', auth()->id());
                        })
                        ->orWhere(function ($query) {
                            $query->where('estado', 'disponible')
                                  ->where('mesero_id', auth()->id());
                        })
                        ->orderBy('numero', 'asc')
                        ->get();
                } else {
                    $mesas = \App\Models\Mesa::where('estado', 'ocupada')
                        ->orderBy('numero', 'asc')
                        ->get();
                }
            }

            return view('mesero.dashboard', compact('mesas'));
        })->name('dashboard');

        // ---> RUTA ACTUALIZADA PARA TRAER DATOS REALES DE POSTGRESQL <---
        Route::get('/comanda', function () {
            return redirect()->route('mesero.dashboard');
        })->name('comanda');

        // ---> NUEVA RUTA PARA ENVIAR LA COMANDA <---
        Route::post('/comanda/enviar', [ComandaController::class, 'enviar'])->name('comanda.enviar');

        // Verificar NIP de capitán y obtener mesas abiertas
        Route::post('/capitan/verify', [ComandaController::class, 'verificarCapitan'])->name('capitan.verify');
        // API para obtener mesas abiertas (JSON)
        Route::get('/mesas/abiertas', [ComandaController::class, 'apiMesasAbiertas'])->name('mesas.abiertas');

        Route::get('/comanda/{mesa}', [ComandaController::class, 'show'])->name('comanda.show');

        // ---> NUEVA RUTA PARA GUARDAR LA MESA <---
        Route::post('/mesa/store', [ComandaController::class, 'storeMesa'])->name('mesa.store');
        
        // ---> NUEVA RUTA PARA REABRIR MESA <---
        Route::post('/mesa/reabrir', [ComandaController::class, 'reabrir'])->name('mesa.reabrir');

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
        Route::get('/bajo-stock-pdf', [InventarioController::class, 'exportarPdfBajoStock'])->name('exportar_pdf_bajo_stock');
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
        Route::get('/api/promociones-activas', [CajaController::class, 'getPromocionesActivas'])->name('api.promociones');
        Route::post('/api/store', [CajaController::class, 'store'])->name('api.store');
        Route::post('/api/pagar', [CajaController::class, 'pagar'])->name('api.pagar');
        Route::post('/api/liberar-mesa', [CajaController::class, 'liberarMesa'])->name('api.liberar-mesa');
        Route::post('/api/abrir-mesa', [CajaController::class, 'abrirMesa'])->name('api.abrir-mesa');
        Route::delete('/{id}', [CajaController::class, 'destroy'])->name('destroy');
    });



    // ==========================================
    // MÓDULO DE MESAS ACTIVO (CORREGIDO) 
    // ==========================================
    Route::prefix('admin/mesas')->name('admin.mesas.')->group(function () {
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

    // ==========================================
    // MÓDULO DE PLANO ESPACIAL
    // ==========================================
    Route::prefix('admin/plano-espacial')->name('admin.plano-espacial.')->group(function () {
        Route::get('/', [PlanoEspacialController::class, 'index'])->name('index');
        Route::get('/api/mesas', [PlanoEspacialController::class, 'getMesas'])->name('api.mesas');
        Route::get('/api/mesas/{id}', [PlanoEspacialController::class, 'getMesa'])->name('api.mesa');
        Route::post('/api/guardar', [PlanoEspacialController::class, 'guardarPlano'])->name('api.guardar');
        Route::post('/api/crear', [PlanoEspacialController::class, 'crearMesa'])->name('api.crear');
        Route::post('/api/store', [PlanoEspacialController::class, 'store'])->name('api.store');
        Route::delete('/api/eliminar/{id}', [PlanoEspacialController::class, 'eliminarDelPlano'])->name('api.eliminar');
        
        // Test directo - eliminar después
        Route::get('/test-crear/{numero}/{capacidad}', [PlanoEspacialController::class, 'testCrearMesa'])->name('test.crear');
    });

    Route::prefix('admin/cocina')->name('admin.cocina.')->group(function () {
        Route::get('/', [CocinaController::class, 'index'])->name('index');
        Route::patch('/orden/{id}/estado', [CocinaController::class, 'actualizarEstado'])->name('orden.estado');
    });

    Route::prefix('admin/promociones')->name('admin.promociones.')->group(function () {
        // Vista principal donde viven todos tus modales
        Route::get('/', [PromocionController::class, 'index'])->name('index');
        
        // Procesar la creación desde el modal
        Route::post('/store', [PromocionController::class, 'store'])->name('store');
        
        // Retorna el JSON para el modal de editar (Cambiamos /editar por /edit para estandarizar con el JS)
        Route::get('/{promocion}/edit', [PromocionController::class, 'edit'])->name('edit');
        
        // Actualizar y Eliminar usando el ID/Modelo dinámico
        Route::put('/{promocion}', [PromocionController::class, 'update'])->name('update');
        Route::delete('/{promocion}', [PromocionController::class, 'destroy'])->name('destroy');
    });

    Route::get('/admin/roles', [RolController::class, 'index'])->name('roles.index');
    Route::post('/admin/roles', [RolController::class, 'store'])->name('roles.store');
    Route::put('/admin/roles/{id}', [RolController::class, 'update'])->name('roles.update');
    Route::delete('/admin/roles/{id}', [RolController::class, 'destroy'])->name('roles.destroy');

    // ==========================================
    // MÓDULO DE FINANZAS - FLUJO DE CAJA
    // ==========================================
    Route::prefix('admin/finanzas')->name('admin.finanzas.')->group(function () {
        Route::get('/', [FinanzasController::class, 'index'])->name('index');
        Route::get('/exportar-csv', [FinanzasController::class, 'exportarCSV'])->name('exportar');
        Route::post('/estadisticas-periodo', [FinanzasController::class, 'estadisticasPeriodo'])->name('estadisticas.periodo');
    });

    Route::prefix('admin/gastos')->name('admin.gastos.')->group(function () {
        Route::post('/', [FinanzasController::class, 'guardarGasto'])->name('store');
    });

    Route::prefix('admin/pagos-nomina')->name('admin.pagos-nomina.')->group(function () {
        Route::post('/', [FinanzasController::class, 'guardarNomina'])->name('store');
    });

    // PERMISOS Y LOGOUT
    Route::post('/admin/permisos/store', [PermisoController::class, 'store'])->name('permisos.store');
    Route::post('/logout', function () { Auth::logout(); return redirect()->route('login'); })->name('logout');

});