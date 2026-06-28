@extends('layouts.admin')

@section('title', 'Alimentos | Ollintem Pro')

@section('header-title', 'Gestión de Alimentos')
@section('header-subtitle', 'Administra el menú y las recetas de los platillos')

@section('content')
<div class="p-6 lg:p-8 xl:p-10 max-w-[1400px] mx-auto w-full space-y-8 flex-1 flex flex-col bg-[var(--bg-color)] text-[var(--text-color)]">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-[var(--text-color)]">Menú de Alimentos</h1>
            <p class="text-sm font-medium text-[var(--text-muted)] mt-1">Gestiona los platillos del restaurante</p>
        </div>

        @if(auth()->user()->tienePermiso('productos.agregar'))
            <button onclick="openModalAlimento()" class="flex items-center gap-2 bg-[var(--text-color)] text-[var(--bg-color)] hover:opacity-80 px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm">
                <i class="fas fa-plus text-[12px]"></i>
                <span>Agregar Platillo</span>
            </button>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-[var(--bg-panel)] rounded-[20px] p-6 shadow-sm border border-[var(--border-color)] flex flex-col justify-between relative overflow-hidden transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black text-[var(--text-muted)] uppercase tracking-widest">Total Platillos</span>
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500">
                    <i class="fas fa-utensils text-sm"></i>
                </div>
            </div>
            <span class="text-4xl font-black text-[var(--text-color)] tracking-tight" id="stat-total">0</span>
        </div>

        <div class="bg-[var(--bg-panel)] rounded-[20px] p-6 shadow-sm border border-[var(--border-color)] flex flex-col justify-between relative overflow-hidden transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black text-[var(--text-muted)] uppercase tracking-widest">Disponibles</span>
                <div class="w-8 h-8 rounded-lg bg-green-500/10 flex items-center justify-center text-green-500">
                    <i class="fas fa-check text-sm"></i>
                </div>
            </div>
            <span class="text-4xl font-black text-[var(--text-color)] tracking-tight" id="stat-disponibles">0</span>
        </div>

        <div class="bg-[var(--bg-panel)] rounded-[20px] p-6 shadow-sm border border-[var(--border-color)] flex flex-col justify-between relative overflow-hidden transition-all hover:shadow-md">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black text-[var(--text-muted)] uppercase tracking-widest">Categorías</span>
                <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500">
                    <i class="fas fa-tags text-sm"></i>
                </div>
            </div>
            <span class="text-4xl font-black text-[var(--text-color)] tracking-tight" id="stat-categorias">0</span>
        </div>
    </div>

    <div class="bg-[var(--bg-panel)] rounded-[24px] p-2 md:p-6 shadow-sm border border-[var(--border-color)] min-h-[420px]">
        <div id="categorias-container" class="space-y-6"
             data-permiso-editar="{{ auth()->user()->tienePermiso('productos.editar') ? 'true' : 'false' }}"
             data-permiso-eliminar="{{ auth()->user()->tienePermiso('productos.eliminar') ? 'true' : 'false' }}"
             data-permiso-gestionar="{{ auth()->user()->tienePermiso('productos.reporte') ? 'true' : 'false' }}">
        </div>
    </div>
</div>

@include('admin.alimentos.modal-crear')
@include('admin.alimentos.modal-editar')
@include('admin.alimentos.modal-eliminar')

<script>
    // RUTAS DEFINIDAS PARA EVITAR ERRORES DE PREFIJO
    const RUTA_PRODUCTOS = "{{ route('admin.productos.api.productos') }}";
    const RUTA_ESTADISTICAS = "{{ route('admin.productos.api.estadisticas') }}";
    const RUTA_STORE = "{{ route('admin.productos.api.store') }}";
    const RUTA_UPDATE_BASE = "/alimentos/api/"; // URL base para los updates
    const RUTA_TOGGLE_BASE = "/alimentos/api/"; // URL base para el toggle

    let estadoGlobal = {
        editandoId: null,
        productos: {},
        productosMap: {}, 
        categorias: {}
    };

    const container = document.getElementById('categorias-container');
    const tienePermisoEditar = container.dataset.permisoEditar === 'true';
    const tienePermisoEliminar = container.dataset.permisoEliminar === 'true';
    const tienePermisoGestionar = container.dataset.permisoGestionar === 'true';

    const categoriasDisponibles = {!! Illuminate\Support\Js::from($categorias->map(function($c) { return ['id' => $c->id, 'nombre' => $c->nombre]; })) !!};
    const insumosDisponibles = {!! Illuminate\Support\Js::from($insumosDisponibles->map(function($i) { return ['id' => $i->id, 'nombre' => $i->nombre, 'unidad_medida' => $i->unidad_medida, 'stock_actual' => $i->stock_actual]; })) !!};

    document.addEventListener('DOMContentLoaded', function() {
        cargarProductos();
        cargarEstadisticas();
        setInterval(cargarEstadisticas, 10000);
    });

    function cargarProductos() {
        fetch(RUTA_PRODUCTOS)
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
        fetch(RUTA_ESTADISTICAS)
            .then(response => response.json())
            .then(data => {
                document.getElementById('stat-total').textContent = data.total;
                document.getElementById('stat-disponibles').textContent = data.disponibles;
                document.getElementById('stat-categorias').textContent = data.categorias;
            })
            .catch(error => console.error('Error cargando estadísticas:', error));
    }

    function renderizarProductos() {
        const container = document.getElementById('categorias-container');
        container.innerHTML = '';

        if (Object.keys(estadoGlobal.productos).length === 0) {
            container.innerHTML = '<p class="text-center text-[var(--text-muted)] py-12 font-bold text-sm">No hay platillos registrados aún.</p>';
            return;
        }

        Object.keys(estadoGlobal.productos).forEach(categoriaNombre => {
            const productos = estadoGlobal.productos[categoriaNombre];
            const seccion = document.createElement('div');
            seccion.className = 'mb-8 bg-[var(--bg-color)] rounded-[20px] p-4 border border-[var(--border-color)]';
            
            const icono = obtenerIconoCategoria(categoriaNombre);
            
            seccion.innerHTML = `
                <div class="flex items-center gap-4 mb-5 border-b border-[var(--border-color)] pb-4 px-2">
                    <div class="w-10 h-10 bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-xl flex items-center justify-center text-[var(--text-color)] shadow-sm">
                        <i class="${icono} text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-[var(--text-color)] tracking-tight uppercase">${categoriaNombre}</h2>
                        <p class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-widest mt-0.5">${productos.length} Platillo${productos.length !== 1 ? 's' : ''}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4" id="grid-${categoriaNombre.replace(/\s+/g, '-')}"></div>
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
        card.className = 'bg-[var(--bg-panel)] rounded-[16px] p-5 border border-[var(--border-color)] shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-1 group flex flex-col relative';
        
        let etiquetasMods = '';
        if(producto.modificadores && producto.modificadores.length > 0) {
            etiquetasMods = `<p class="text-[10px] text-[var(--text-muted)] mt-1.5 truncate"><i class="fas fa-list-ul mr-1 opacity-70"></i> ${producto.modificadores.map(m => m.nombre).join(', ')}</p>`;
        }

        let botonesHTML = '';
        if (tienePermisoEditar) {
            botonesHTML += `<button class="w-8 h-8 rounded-lg bg-[var(--bg-color)] border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-color)] flex items-center justify-center transition" onclick="editarProducto(${producto.id})" title="Editar">
                <i class="fas fa-pen text-[11px]"></i>
            </button>`;
        }
        if (tienePermisoEliminar) {
            botonesHTML += `<button class="w-8 h-8 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition" onclick="eliminarProducto(${producto.id})" title="Eliminar">
                <i class="fas fa-trash text-[11px]"></i>
            </button>`;
        }

        let toggleHTML = '';
        if (tienePermisoGestionar) {
            toggleHTML = `<button class="w-9 h-5 rounded-full transition-colors duration-300 relative ${producto.esta_disponible ? 'bg-green-500' : 'bg-gray-300 dark:bg-zinc-700'}" onclick="toggleDisponibilidad(${producto.id})" title="Cambiar disponibilidad">
                <div class="w-4 h-4 bg-white rounded-full shadow-sm absolute top-0.5 transition-transform duration-300 ${producto.esta_disponible ? 'translate-x-4.5' : 'translate-x-0.5'}"></div>
            </button>`;
        } else {
            toggleHTML = `<div class="w-9 h-5 rounded-full relative ${producto.esta_disponible ? 'bg-green-500' : 'bg-gray-300 dark:bg-zinc-700'} opacity-50 cursor-not-allowed">
                <div class="w-4 h-4 bg-white rounded-full shadow-sm absolute top-0.5 ${producto.esta_disponible ? 'translate-x-4.5' : 'translate-x-0.5'}"></div>
            </div>`;
        }

        card.innerHTML = `
            <div class="flex justify-between items-start mb-4">
                <div class="overflow-hidden pr-3">
                    <h3 class="text-[15px] font-bold text-[var(--text-color)] tracking-tight truncate">${producto.nombre}</h3>
                    <p class="text-[12px] text-[var(--text-muted)] mt-1 line-clamp-1">${producto.descripcion ? producto.descripcion : 'Sin descripción'}</p>
                    ${etiquetasMods}
                </div>
                <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                    ${botonesHTML}
                </div>
            </div>
            
            <div class="flex justify-between items-center mt-auto pt-4 border-t border-[var(--border-color)]">
                <div class="flex items-center gap-3">
                    ${toggleHTML}
                    <span class="text-[9px] font-bold text-[var(--text-muted)] uppercase tracking-widest">${producto.esta_disponible ? 'Disponible' : 'Agotado'}</span>
                </div>
                <span class="text-[16px] font-black text-[var(--text-color)] tracking-tight">$${parseFloat(producto.precio).toFixed(2)}</span>
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

    function editarProducto(id) {
        const producto = encontrarProductoPorId(id);
        if (!producto) return;
        estadoGlobal.editandoId = id;
        document.getElementById('edit-nombre').value = producto.nombre || '';
        document.getElementById('edit-precio').value = producto.precio || '';
        document.getElementById('edit-descripcion').value = producto.descripcion || '';
        document.getElementById('edit-categoria_nombre').value = producto.categoria ? producto.categoria.nombre : '';
        document.getElementById('edit-categoria_id').value = producto.categoria ? producto.categoria.id : '';
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

    function encontrarProductoPorId(id) { return estadoGlobal.productosMap[id] || null; }
    function obtenerCategoriaIdPorNombre(nombre) {
        if (!nombre) return null;
        const categoria = categoriasDisponibles.find(cat => cat.nombre.toLowerCase() === nombre.toLowerCase());
        return categoria ? categoria.id : null;
    }
    function limpiarIngredientesContainer(tipo) { document.getElementById(`ingredientes-container-${tipo}`).innerHTML = ''; }

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
        if (insumoValue) { select.value = insumoValue; sincronizarInsumo(select); }
        return row;
    }

    function agregarIngrediente(tipo = 'crear', ingrediente = {}) {
        const container = document.getElementById(`ingredientes-container-${tipo}`);
        if(container) container.appendChild(crearFilaIngrediente(ingrediente));
    }

    function eliminarIngredienteRow(button) { button.closest('.ingrediente-row').remove(); }
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
                agregarIngrediente('editar', { insumo_id: insumo.id, cantidad: insumo.pivot?.cantidad_usada || '', unidad_medida: insumo.unidad_medida || '', stock_actual: insumo.stock_actual || '' });
            });
        } else { agregarIngrediente('editar'); }
    }

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
            } else { data[key] = value; }
        });
        const txtCategoria = document.getElementById('categoria_nombre').value;
        data.categoria_nombre = txtCategoria;
        data.categoria_id = obtenerCategoriaIdPorNombre(txtCategoria);
        ejecutarPeticion(RUTA_STORE, data, btnGuardar, textoOriginal, closeModalCrear);
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
            } else { data[key] = value; }
        });
        data.categoria_nombre = document.getElementById('edit-categoria_nombre').value;
        data.categoria_id = obtenerCategoriaIdPorNombre(data.categoria_nombre);
        data._method = 'PUT'; 
        ejecutarPeticion(RUTA_UPDATE_BASE + estadoGlobal.editandoId, data, btnActualizar, textoOriginal, closeModalEditar);
    }

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
            if (!response.ok) return response.json().then(err => { throw new Error(err.message || 'Error'); });
            return response.json();
        })
        .then(resultado => {
            cerrarModalFn();
            cargarProductos();
            cargarEstadisticas();
            mostrarNotificacion(resultado.message || 'Operación exitosa', 'success');
        })
        .catch(error => {
            mostrarNotificacion(error.message || 'Error en el servidor', 'error');
        })
        .finally(() => { boton.textContent = textoBoton; boton.disabled = false; });
    }

    function eliminarProducto(id) {
        const producto = encontrarProductoPorId(id);
        if (producto) abrirModalEliminar(id, producto.nombre);
    }

    function toggleDisponibilidad(id) {
        fetch(RUTA_TOGGLE_BASE + id + '/toggle-disponibilidad', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
        })
        .then(() => { cargarProductos(); cargarEstadisticas(); })
        .catch(error => console.error(error));
    }

    function mostrarNotificacion(mensaje, tipo) {
        const notificacion = document.createElement('div');
        notificacion.className = `fixed top-4 right-4 px-6 py-3 rounded-lg font-bold text-white z-[200] shadow-xl ${tipo === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
        notificacion.textContent = mensaje;
        document.body.appendChild(notificacion);
        setTimeout(() => notificacion.remove(), 3000);
    }
</script>
@endsection