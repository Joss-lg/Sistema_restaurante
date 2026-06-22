@extends('layouts.admin')

@section('title', 'Alimentos | Ollintem Pro')

@section('header-title', 'Gestión de Alimentos')
@section('header-subtitle', 'Administra el menú y las recetas de los platillos')

@section('content')
<div class="p-6 lg:p-8 xl:p-10 max-w-[1400px] mx-auto w-full space-y-6 flex-1 flex flex-col bg-[var(--bg-color)] text-[var(--text-color)]">

    {{-- CABECERA Y BOTÓN --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-3xl font-black text-[var(--text-color)] tracking-tight">Menú de Alimentos</h1>
            <p class="text-[var(--text-muted)] mt-1">Gestiona los platillos del restaurante</p>
        </div>

        {{-- 🌟 PERMISO: productos.agregar --}}
        @if(auth()->user()->tienePermiso('productos.agregar'))
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl blur opacity-30 group-hover:opacity-60 transition duration-150"></div>
                <button onclick="openModalAlimento()" class="relative flex items-center gap-2.5 bg-[#3B82F6] hover:bg-blue-600 text-white px-6 py-3 rounded-xl font-bold transition duration-150 shadow-lg shadow-blue-900/20">
                    <i class="fas fa-plus text-sm"></i>
                    <span>Agregar Platillo</span>
                </button>
            </div>
        @endif
    </div>

    {{-- CARDS DE ESTADÍSTICAS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-card rounded-[1.75rem] p-6 shadow-xl border border-[var(--border-color)] flex flex-col justify-between min-h-[140px] relative overflow-hidden group">
            <div class="absolute right-5 top-5 text-blue-900/20 text-4xl group-hover:scale-110 transition-transform"><i class="fas fa-utensils"></i></div>
            <span class="text-sm font-bold text-[var(--text-muted)] uppercase tracking-widest">Total Platillos</span>
            <span class="text-5xl font-black text-blue-500 tracking-tighter" id="stat-total">0</span>
        </div>

        <div class="glass-card rounded-[1.75rem] p-6 shadow-xl border border-[var(--border-color)] flex flex-col justify-between min-h-[140px] relative overflow-hidden group">
            <div class="absolute right-5 top-5 text-green-900/20 text-4xl group-hover:scale-110 transition-transform"><i class="fas fa-check-circle"></i></div>
            <span class="text-sm font-bold text-[var(--text-muted)] uppercase tracking-widest">Disponibles</span>
            <span class="text-5xl font-black text-green-500 tracking-tighter" id="stat-disponibles">0</span>
        </div>

        <div class="glass-card rounded-[1.75rem] p-6 shadow-xl border border-[var(--border-color)] flex flex-col justify-between min-h-[140px] relative overflow-hidden group">
            <div class="absolute right-5 top-5 text-purple-900/20 text-4xl group-hover:scale-110 transition-transform"><i class="fas fa-tags"></i></div>
            <span class="text-sm font-bold text-[var(--text-muted)] uppercase tracking-widest">Categorías</span>
            <span class="text-5xl font-black text-purple-500 tracking-tighter" id="stat-categorias">0</span>
        </div>
    </div>

    {{-- LISTADO DE PLATILLOS --}}
    <div class="glass-card rounded-[2rem] p-6 shadow-xl border border-[var(--border-color)] min-h-[420px]">
        {{-- 🌟 INYECCIÓN DE PERMISOS EN ATRIBUTOS DATA para usarlos en JS --}}
        <div id="categorias-container"
             data-permiso-editar="{{ auth()->user()->tienePermiso('productos.editar') ? 'true' : 'false' }}"
             data-permiso-eliminar="{{ auth()->user()->tienePermiso('productos.eliminar') ? 'true' : 'false' }}"
             data-permiso-gestionar="{{ auth()->user()->tienePermiso('productos.gestionar') ? 'true' : 'false' }}">
            {{-- Se llena dinámicamente con JavaScript --}}
        </div>
    </div>
</div>

{{-- COMPONENTES MODALES --}}
@include('admin.alimentos.modal-crear')
@include('admin.alimentos.modal-editar')
@include('admin.alimentos.modal-eliminar')

<script>
    // =========================================================================
    // ESTADO GLOBAL Y CONFIGURACIÓN
    // =========================================================================
    let estadoGlobal = {
        editandoId: null,
        productos: {},
        productosMap: {}, 
        categorias: {}
    };

    // Leer permisos desde los atributos data del contenedor
    const container = document.getElementById('categorias-container');
    const tienePermisoEditar = container.dataset.permisoEditar === 'true';
    const tienePermisoEliminar = container.dataset.permisoEliminar === 'true';
    const tienePermisoGestionar = container.dataset.permisoGestionar === 'true';

    // Carga segura de datos PHP desde Laravel
    const categoriasDisponibles = {!! Illuminate\Support\Js::from($categorias->map(function($c) { return ['id' => $c->id, 'nombre' => $c->nombre]; })) !!};
    const insumosDisponibles = {!! Illuminate\Support\Js::from($insumosDisponibles->map(function($i) { return ['id' => $i->id, 'nombre' => $i->nombre, 'unidad_medida' => $i->unidad_medida, 'stock_actual' => $i->stock_actual]; })) !!};

    // Inicialización de la vista
    document.addEventListener('DOMContentLoaded', function() {
        cargarProductos();
        cargarEstadisticas();
        setInterval(cargarEstadisticas, 10000);
    });

    // =========================================================================
    // CARGA DE DATOS DESDE LA API
    // =========================================================================
    function cargarProductos() {
        fetch('/admin/alimentos/api/productos')
            .then(response => response.json())
            .then(data => {
                estadoGlobal.productos = data;
                estadoGlobal.productosMap = {};
                Object.keys(data).forEach(categoria => {
                    data[categoria].forEach(producto => {
                        estadoGlobal.productosMap[producto.id] = producto;
                    });
                });
                renderizarProductos();
            })
            .catch(error => console.error('Error cargando productos:', error));
    }

    function cargarEstadisticas() {
        fetch('/admin/alimentos/api/estadisticas')
            .then(response => response.json())
            .then(data => {
                document.getElementById('stat-total').textContent = data.total;
                document.getElementById('stat-disponibles').textContent = data.disponibles;
                document.getElementById('stat-categorias').textContent = data.categorias;
            })
            .catch(error => console.error('Error cargando estadísticas:', error));
    }

    // =========================================================================
    // RENDERIZADO DE LA INTERFAZ (CARDS)
    // =========================================================================
    function renderizarProductos() {
        const container = document.getElementById('categorias-container');
        container.innerHTML = '';

        if (Object.keys(estadoGlobal.productos).length === 0) {
            container.innerHTML = '<p class="text-center text-[var(--text-muted)] py-12 font-bold">No hay platillos registrados aún.</p>';
            return;
        }

        Object.keys(estadoGlobal.productos).forEach(categoriaNombre => {
            const productos = estadoGlobal.productos[categoriaNombre];
            const seccion = document.createElement('div');
            seccion.className = 'mb-12';
            
            const icono = obtenerIconoCategoria(categoriaNombre);
            
            seccion.innerHTML = `
                <div class="flex items-center gap-4 mb-6 border-b border-gray-800 pb-4">
                    <div class="w-12 h-12 bg-blue-500/10 rounded-2xl flex items-center justify-center text-[#3B82F6] text-2xl border border-[#3B82F6]/20">
                        <i class="${icono}"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-[var(--text-color)]">${categoriaNombre}</h2>
                        <p class="text-xs text-[var(--text-muted)] font-bold uppercase tracking-widest">${productos.length} PLATILLO${productos.length !== 1 ? 'S' : ''} REGISTRADO${productos.length !== 1 ? 'S' : ''}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6" id="grid-${categoriaNombre.replace(/\s+/g, '-')}"></div>
            `;
            
            container.appendChild(seccion);
            
            const grid = container.querySelector(`#grid-${categoriaNombre.replace(/\s+/g, '-')}`);
            productos.forEach(producto => {
                grid.appendChild(crearCardProducto(producto));
            });
        });
    }

    function crearCardProducto(producto) {
        const card = document.createElement('div');
        card.className = 'glass-card rounded-3xl p-5 border border-[var(--border-color)] hover:border-[var(--text-muted)] transition-all group';
        
        let etiquetasMods = '';
        if(producto.modificadores && producto.modificadores.length > 0) {
            etiquetasMods = `<p class="text-[10px] text-[var(--text-muted)] mt-1 truncate"><i class="fas fa-list-ul mr-1"></i> ${producto.modificadores.map(m => m.nombre).join(', ')}</p>`;
        }

        // Construir botones solo si el usuario tiene permisos
        let botonesHTML = '';
        if (tienePermisoEditar) {
            botonesHTML += `<button class="w-10 h-10 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-muted)] hover:border-blue-500/50 hover:text-blue-400 transition flex items-center justify-center shadow-sm" onclick="editarProducto(${producto.id})" title="Editar">
                <i class="fas fa-edit text-sm"></i>
            </button>`;
        }
        if (tienePermisoEliminar) {
            botonesHTML += `<button class="w-10 h-10 rounded-xl bg-red-900/10 border border-red-900/20 text-red-500 hover:bg-red-900/40 transition flex items-center justify-center shadow-sm" onclick="eliminarProducto(${producto.id})" title="Eliminar">
                <i class="fas fa-trash-alt text-sm"></i>
            </button>`;
        }

        // Toggle solo si tiene permiso de gestionar
        let toggleHTML = '';
        if (tienePermisoGestionar) {
            toggleHTML = `<button class="w-10 h-5 rounded-full transition ${producto.esta_disponible ? 'bg-blue-600' : 'bg-gray-600'}" onclick="toggleDisponibilidad(${producto.id})" title="Cambiar disponibilidad">
                <div class="w-3 h-3 bg-white rounded-full transition ${producto.esta_disponible ? 'ml-auto mr-1' : 'ml-1'} mt-1"></div>
            </button>`;
        } else {
            toggleHTML = `<div class="w-10 h-5 rounded-full transition ${producto.esta_disponible ? 'bg-blue-600' : 'bg-gray-600'} opacity-50 cursor-not-allowed">
                <div class="w-3 h-3 bg-white rounded-full transition ${producto.esta_disponible ? 'ml-auto mr-1' : 'ml-1'} mt-1"></div>
            </div>`;
        }

        card.innerHTML = `
            <div class="flex justify-between items-start mb-3">
                <div class="overflow-hidden pr-2">
                    <h3 class="text-lg font-black text-[var(--text-color)] tracking-tight group-hover:text-blue-400 transition truncate">${producto.nombre}</h3>
                    ${producto.descripcion ? `<p class="text-sm text-[var(--text-muted)] mt-2 line-clamp-2">${producto.descripcion}</p>` : ''}
                    ${etiquetasMods}
                    <div class="flex gap-2 mt-2">
                        <span class="bg-black/50 ${producto.esta_disponible ? 'text-green-400 border-green-900/50' : 'text-red-400 border-red-900/50'} text-[10px] font-black px-2 py-0.5 rounded-md uppercase tracking-tighter border">${producto.esta_disponible ? 'Disponible' : 'No disponible'}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    ${botonesHTML}
                </div>
            </div>
            
            <div class="flex justify-between items-center pt-3 border-t border-[var(--border-color)] mt-3">
                <span class="text-xl font-black text-[var(--text-color)]">$${parseFloat(producto.precio).toFixed(2)}<span class="text-xs text-[var(--text-muted)] font-medium ml-1 uppercase">MXN</span></span>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">${producto.esta_disponible ? 'Activo' : 'Inactivo'}</span>
                    ${toggleHTML}
                </div>
            </div>
        `;
        return card;
    }

    function obtenerIconoCategoria(nombre) {
        const nomNormalizado = nombre.toLowerCase();
        if(nomNormalizado.includes('pizza')) return 'fas fa-pizza-slice';
        if(nomNormalizado.includes('pasta')) return 'fas fa-utensils';
        if(nomNormalizado.includes('bebida') || nomNormalizado.includes('cocteleria')) return 'fas fa-glass-water';
        if(nomNormalizado.includes('postre')) return 'fas fa-cake-slice';
        if(nomNormalizado.includes('ensalada') || nomNormalizado.includes('verdura')) return 'fas fa-leaf';
        if(nomNormalizado.includes('carne') || nomNormalizado.includes('parrillada') || nomNormalizado.includes('conejo')) return 'fas fa-drumstick-bite';
        if(nomNormalizado.includes('marisco') || nomNormalizado.includes('pescado')) return 'fas fa-fish';
        if(nomNormalizado.includes('sopa')) return 'fas fa-bowl-food';
        if(nomNormalizado.includes('abarrote')) return 'fas fa-box-open';
        
        return 'fas fa-concierge-bell';
    }

    // =========================================================================
    // CONTROL DEL MODAL CREAR
    // =========================================================================
    function openModalAlimento() { 
        estadoGlobal.editandoId = null;
        const form = document.getElementById('formulario-crear-alimento');
        if(form) form.reset();
        
        document.getElementById('categoria_id').value = '';
        document.getElementById('descripcion').value = '';
        
        limpiarIngredientesContainer('crear');
        agregarIngrediente('crear');

        const modal = document.getElementById('modal-crear-alimento');
        const panel = document.getElementById('modal-crear-panel');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            panel.classList.add('opacity-100', 'translate-y-0');
        }, 10);
    }

    function closeModalCrear() {
        const modal = document.getElementById('modal-crear-alimento');
        const panel = document.getElementById('modal-crear-panel');
        modal.classList.remove('opacity-100');
        panel.classList.remove('opacity-100', 'translate-y-0');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    // =========================================================================
    // CONTROL DEL MODAL EDITAR
    // =========================================================================
    function editarProducto(id) {
        const producto = encontrarProductoPorId(id);
        if (!producto) {
            console.error('Producto no encontrado:', id);
            return;
        }

        estadoGlobal.editandoId = id;
        document.getElementById('edit-nombre').value = producto.nombre || '';
        document.getElementById('edit-precio').value = producto.precio || '';
        document.getElementById('edit-descripcion').value = producto.descripcion || '';
        
        document.getElementById('edit-categoria_nombre').value = producto.categoria ? producto.categoria.nombre : '';
        document.getElementById('edit-categoria_id').value = producto.categoria ? producto.categoria.id : '';

        let modsString = '';
        if (producto.modificadores && producto.modificadores.length > 0) {
            modsString = producto.modificadores.map(m => m.nombre).join(', ');
        }
        document.getElementById('edit-modificadores_input').value = modsString;

        llenarIngredientesEdicion(producto);

        const modal = document.getElementById('modal-editar-alimento');
        const panel = document.getElementById('modal-editar-panel');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            panel.classList.add('opacity-100', 'translate-y-0');
        }, 10);
    }

    function closeModalEditar() {
        const modal = document.getElementById('modal-editar-alimento');
        const panel = document.getElementById('modal-editar-panel');
        modal.classList.remove('opacity-100');
        panel.classList.remove('opacity-100', 'translate-y-0');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    function encontrarProductoPorId(id) {
        return estadoGlobal.productosMap[id] || null;
    }

    function obtenerCategoriaIdPorNombre(nombre) {
        if (!nombre) return null;
        const categoria = categoriasDisponibles.find(cat => cat.nombre.toLowerCase() === nombre.toLowerCase());
        return categoria ? categoria.id : null;
    }

    // =========================================================================
    // GESTIÓN DINÁMICA DE INGREDIENTES (RECETAS)
    // =========================================================================
    function limpiarIngredientesContainer(tipo) {
        document.getElementById(`ingredientes-container-${tipo}`).innerHTML = '';
    }

    function crearFilaIngrediente(ingrediente = {}) {
        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-3 items-end ingrediente-row';

        const insumoValue = ingrediente.insumo_id || '';
        const cantidadValue = ingrediente.cantidad || '';
        const unidadValue = ingrediente.unidad_medida || '';
        const stockActual = ingrediente.stock_actual ?? '';

        const options = insumosDisponibles.map(insumo => {
            const selected = insumo.id == insumoValue ? 'selected' : '';
            return `<option value="${insumo.id}" data-unidad="${insumo.unidad_medida}" data-stock="${insumo.stock_actual}" ${selected}>${insumo.nombre}</option>`;
        }).join('');

        row.innerHTML = `
            <div class="col-span-7">
                <label class="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Ingrediente</label>
                <select name="insumos[]" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-4 mt-2 text-[var(--text-color)] focus:ring-2 focus:ring-blue-500 transition" onchange="sincronizarInsumo(this)" required>
                    <option value="">Seleccionar...</option>
                    ${options}
                </select>
            </div>
            <div class="col-span-3">
                <label class="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Cantidad</label>
                <input type="number" name="cantidades[]" step="0.001" min="0.001" value="${cantidadValue}" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-4 mt-2 text-[var(--text-color)] focus:ring-2 focus:ring-blue-500 transition" placeholder="0.000" required>
            </div>
            <div class="col-span-1">
                <label class="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Uni</label>
                <input type="text" value="${unidadValue}" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-4 mt-2 text-[var(--text-color)]" disabled>
            </div>
            <div class="col-span-1 flex flex-col items-end gap-2">
                <button type="button" class="w-10 h-10 rounded-xl bg-red-900/10 border border-red-900/20 text-red-500 hover:bg-red-900/40 transition shadow-sm" onclick="eliminarIngredienteRow(this)">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <span class="text-[10px] text-[var(--text-muted)] mt-1">${stockActual ? `Stock ${stockActual}` : ''}</span>
            </div>
        `;

        const select = row.querySelector('select[name="insumos[]"]');
        if (insumoValue) {
            select.value = insumoValue;
            sincronizarInsumo(select);
        }

        return row;
    }

    function agregarIngrediente(tipo = 'crear', ingrediente = {}) {
        const container = document.getElementById(`ingredientes-container-${tipo}`);
        if(container) {
            container.appendChild(crearFilaIngrediente(ingrediente));
        }
    }

    function eliminarIngredienteRow(button) {
        const row = button.closest('.ingrediente-row');
        if (row) row.remove();
    }

    function sincronizarInsumo(select) {
        const selectedOption = select.querySelector('option:checked');
        const row = select.closest('.ingrediente-row');
        if (!row) return;

        const unidadInput = row.querySelector('input[disabled]');
        const stockLabel = row.querySelector('span');

        if (selectedOption && selectedOption.value !== "") {
            unidadInput.value = selectedOption.dataset.unidad || '';
            stockLabel.textContent = selectedOption.dataset.stock ? `Stock ${selectedOption.dataset.stock}` : '';
        } else {
            unidadInput.value = '';
            stockLabel.textContent = '';
        }
    }

    function llenarIngredientesEdicion(producto) {
        limpiarIngredientesContainer('editar');
        if (producto.insumos && producto.insumos.length > 0) {
            producto.insumos.forEach(insumo => {
                agregarIngrediente('editar', {
                    insumo_id: insumo.id,
                    cantidad: insumo.pivot?.cantidad_usada || '',
                    unidad_medida: insumo.unidad_medida || '',
                    stock_actual: insumo.stock_actual || ''
                });
            });
        } else {
            agregarIngrediente('editar');
        }
    }

    // =========================================================================
    // PROCESAMIENTO Y ENVÍO DE FORMULARIOS (STORE / UPDATE SEPARADOS)
    // =========================================================================
    function guardarAlimento(event) {
        event.preventDefault();
        
        const btnGuardar = document.getElementById('btn-guardar');
        if (!btnGuardar || btnGuardar.disabled) return;

        const textoOriginal = btnGuardar.textContent;
        btnGuardar.textContent = 'GUARDANDO...';
        btnGuardar.disabled = true;
        
        const form = document.getElementById('formulario-crear-alimento');
        const formData = new FormData(form);
        const data = {};

        formData.forEach((value, key) => {
            if (key.endsWith('[]')) {
                const name = key.slice(0, -2);
                if (!data[name]) data[name] = [];
                data[name].push(value);
            } else {
                data[key] = value;
            }
        });

        const txtCategoria = document.getElementById('categoria_nombre').value;
        data.categoria_nombre = txtCategoria;
        data.categoria_id = obtenerCategoriaIdPorNombre(txtCategoria);

        ejecutarPeticion('/admin/alimentos/api/store', data, btnGuardar, textoOriginal, closeModalCrear);
    }

    function actualizarAlimento(event) {
        event.preventDefault();

        const btnActualizar = document.getElementById('btn-actualizar');
        if (!btnActualizar || btnActualizar.disabled) return;

        const textoOriginal = btnActualizar.textContent;
        btnActualizar.textContent = 'ACTUALIZANDO...';
        btnActualizar.disabled = true;

        const form = document.getElementById('formulario-editar-alimento');
        const formData = new FormData(form);
        const data = {};

        formData.forEach((value, key) => {
            if (key.endsWith('[]')) {
                const name = key.slice(0, -2);
                if (!data[name]) data[name] = [];
                data[name].push(value);
            } else {
                data[key] = value;
            }
        });

        const txtCategoria = document.getElementById('edit-categoria_nombre').value;
        data.categoria_nombre = txtCategoria;
        data.categoria_id = obtenerCategoriaIdPorNombre(txtCategoria);
        data._method = 'PUT'; // Laravel requiere spoofing de método para simular PUT vía JSON

        ejecutarPeticion(`/admin/alimentos/api/${estadoGlobal.editandoId}`, data, btnActualizar, textoOriginal, closeModalEditar);
    }

    // Procesador Ajax Universal para evitar redundancia
    function ejecutarPeticion(url, data, boton, textoBoton, cerrarModalFn) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { 
                    throw new Error(err.message || 'Error en la validación del servidor'); 
                });
            }
            return response.json();
        })
        .then(resultado => {
            cerrarModalFn();
            cargarProductos();
            cargarEstadisticas();
            mostrarNotificacion(resultado.message || 'Operación realizada con éxito', 'success');
        })
        .catch(error => {
            mostrarNotificacion(error.message || 'Error en el servidor', 'error');
            console.error('Error detallado:', error);
        })
        .finally(() => {
            boton.textContent = textoBoton;
            boton.disabled = false;
        });
    }

    function eliminarProducto(id) {
        const producto = encontrarProductoPorId(id);
        if (!producto) {
            console.error('Producto no encontrado:', id);
            return;
        }
        abrirModalEliminar(id, producto.nombre);
    }

    function toggleDisponibilidad(id) {
        fetch(`/admin/alimentos/api/${id}/toggle-disponibilidad`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            }
        })
        .then(response => response.json())
        .then(() => {
            cargarProductos();
            cargarEstadisticas();
        })
        .catch(error => console.error(error));
    }

    // Notificaciones flotantes rápidas
    function mostrarNotificacion(mensaje, tipo) {
        const notificacion = document.createElement('div');
        notificacion.className = `fixed top-4 right-4 px-6 py-3 rounded-lg font-bold text-white z-[200] shadow-xl transition-all duration-300 ${
            tipo === 'success' ? 'bg-green-600' : 'bg-red-600'
        }`;
        notificacion.textContent = mensaje;
        document.body.appendChild(notificacion);
        
        setTimeout(() => notificacion.remove(), 3000);
    }
</script>
@endsection