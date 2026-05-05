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

        <div class="relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl blur opacity-30 group-hover:opacity-60 transition duration-150"></div>
            <button onclick="openModalAlimento()" class="relative flex items-center gap-2.5 bg-[#3B82F6] hover:bg-blue-600 text-white px-6 py-3 rounded-xl font-bold transition duration-150 shadow-lg shadow-blue-900/20">
                <i class="fas fa-plus text-sm"></i>
                <span>Agregar Platillo</span>
            </button>
        </div>
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
        <div id="categorias-container">
            {{-- Se llena dinámicamente con JavaScript --}}
        </div>
    </div>
</div>

{{-- COMPONENTES MODALES --}}
@include('admin.alimentos.modal-crear')
@include('admin.alimentos.modal-eliminar')

<script>
    // Estado global
    let estadoGlobal = {
        editandoId: null,
        productos: {},
        productosMap: {}, // Mapa plano para búsqueda rápida
        categorias: {}
    };

    // Inicializar
    document.addEventListener('DOMContentLoaded', function() {
        cargarProductos();
        cargarEstadisticas();
        
        // Recargar estadísticas cada 10 segundos
        setInterval(cargarEstadisticas, 10000);
    });

    // Cargar productos desde API
    function cargarProductos() {
        fetch('/admin/alimentos/api/productos')
            .then(response => response.json())
            .then(data => {
                estadoGlobal.productos = data;
                // Llenar el mapa plano para búsqueda rápida
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

    // Cargar estadísticas
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

    // Renderizar productos agrupados por categoría
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
                const card = crearCardProducto(producto);
                grid.appendChild(card);
            });
        });
    }

    // Crear card de producto
    function crearCardProducto(producto) {
        const card = document.createElement('div');
        card.className = 'glass-card rounded-3xl p-5 border border-[var(--border-color)] hover:border-[var(--text-muted)] transition-all group';
        
        // Ver si tiene modificadores para mostrarlos en la tarjeta (opcional, se ve pro)
        let etiquetasMods = '';
        if(producto.modificadores && producto.modificadores.length > 0) {
            etiquetasMods = `<p class="text-[10px] text-[var(--text-muted)] mt-1 truncate"><i class="fas fa-list-ul mr-1"></i> ${producto.modificadores.map(m => m.nombre).join(', ')}</p>`;
        }

        card.innerHTML = `
            <div class="flex justify-between items-start mb-3">
                <div class="overflow-hidden pr-2">
                    <h3 class="text-lg font-black text-[var(--text-color)] tracking-tight group-hover:text-blue-400 transition truncate">${producto.nombre}</h3>
                    ${etiquetasMods}
                    <div class="flex gap-2 mt-2">
                        <span class="bg-black/50 ${producto.esta_disponible ? 'text-green-400 border-green-900/50' : 'text-red-400 border-red-900/50'} text-[10px] font-black px-2 py-0.5 rounded-md uppercase tracking-tighter border">${producto.esta_disponible ? 'Disponible' : 'No disponible'}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button class="w-10 h-10 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-muted)] hover:border-blue-500/50 hover:text-blue-400 transition flex items-center justify-center shadow-sm" onclick="editarProducto(${producto.id})">
                        <i class="fas fa-edit text-sm"></i>
                    </button>
                    <button class="w-10 h-10 rounded-xl bg-red-900/10 border border-red-900/20 text-red-500 hover:bg-red-900/40 transition flex items-center justify-center shadow-sm" onclick="eliminarProducto(${producto.id})">
                        <i class="fas fa-trash-alt text-sm"></i>
                    </button>
                </div>
            </div>

            <div class="flex justify-between items-center pt-3 border-t border-[var(--border-color)] mt-3">
                <span class="text-xl font-black text-[var(--text-color)]">$${parseFloat(producto.precio).toFixed(2)}<span class="text-xs text-[var(--text-muted)] font-medium ml-1 uppercase">MXN</span></span>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">${producto.esta_disponible ? 'Activo' : 'Inactivo'}</span>
                    <button class="w-10 h-5 rounded-full transition ${producto.esta_disponible ? 'bg-blue-600' : 'bg-gray-600'}" onclick="toggleDisponibilidad(${producto.id})">
                        <div class="w-3 h-3 bg-white rounded-full transition ${producto.esta_disponible ? 'ml-auto mr-1' : 'ml-1'} mt-1"></div>
                    </button>
                </div>
            </div>
        `;
        return card;
    }

    // Obtener icono por categoría
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
        
        return 'fas fa-concierge-bell'; // Icono por defecto
    }

    // Abrir modal para agregar o editar
    function openModalAlimento(resetForm = true) {
        if (resetForm) {
            estadoGlobal.editandoId = null;
            document.getElementById('formulario-alimento').reset();
            document.getElementById('categoria_id').value = '';
            document.getElementById('modal-title').textContent = 'Nuevo Platillo';
            document.getElementById('modal-subtitle').textContent = 'Configuración estética del menú';
        }
        
        const modal = document.getElementById('modal-nuevo-alimento');
        const panel = document.getElementById('modal-panel');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            panel.classList.add('opacity-100', 'translate-y-0');
        }, 10);
    }

    const categoriasDisponibles = @json($categorias->map(fn($categoria) => ['id' => $categoria->id, 'nombre' => $categoria->nombre]));

    function obtenerCategoriaIdPorNombre(nombre) {
        if (!nombre) return null;
        const categoria = categoriasDisponibles.find(cat => cat.nombre.toLowerCase() === nombre.toLowerCase());
        return categoria ? categoria.id : null;
    }

    // Cerrar modal
    function closeModalAlimento() {
        const modal = document.getElementById('modal-nuevo-alimento');
        const panel = document.getElementById('modal-panel');
        modal.classList.remove('opacity-100');
        panel.classList.remove('opacity-100', 'translate-y-0');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    // Guardar producto (crear o actualizar)
    function guardarAlimento(event) {
        event.preventDefault();
        
        const form = document.getElementById('formulario-alimento');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.categoria_id = obtenerCategoriaIdPorNombre(data.categoria_nombre);
        document.getElementById('categoria_id').value = data.categoria_id ?? '';

        const url = estadoGlobal.editandoId 
            ? `/admin/alimentos/api/${estadoGlobal.editandoId}`
            : '/admin/alimentos/api/store';
        
        const metodo = estadoGlobal.editandoId ? 'PUT' : 'POST';

        // Cambiar el texto del botón a "Guardando..."
        const btnGuardar = document.getElementById('btn-guardar');
        const textoOriginal = btnGuardar.textContent;
        btnGuardar.textContent = 'GUARDANDO...';
        btnGuardar.disabled = true;

        fetch(url, {
            method: metodo,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la solicitud');
            return response.json();
        })
        .then(resultado => {
            closeModalAlimento();
            cargarProductos();
            cargarEstadisticas();
            mostrarNotificacion(resultado.message, 'success');
            
            // Recargar para que la nueva categoría aparezca en el datalist
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(error => {
            mostrarNotificacion('Error al guardar el platillo', 'error');
            console.error(error);
        })
        .finally(() => {
            btnGuardar.textContent = textoOriginal;
            btnGuardar.disabled = false;
        });
    }

    // Editar producto
    function editarProducto(id) {
        const producto = encontrarProductoPorId(id);
        if (!producto) {
            console.error('Producto no encontrado:', id);
            return;
        }

        estadoGlobal.editandoId = id;
        document.getElementById('nombre').value = producto.nombre || '';
        document.getElementById('precio').value = producto.precio || '';
        
        // Asignamos el nombre de la categoría en lugar del ID
        document.getElementById('categoria_nombre').value = producto.categoria ? producto.categoria.nombre : '';        document.getElementById('categoria_id').value = producto.categoria ? producto.categoria.id : '';        
        // Llenar el campo de modificadores (Convierte el array a una cadena separada por comas)
        let modsString = '';
        if (producto.modificadores && producto.modificadores.length > 0) {
            modsString = producto.modificadores.map(m => m.nombre).join(', ');
        }
        document.getElementById('modificadores_input').value = modsString;
        
        document.getElementById('modal-title').textContent = 'Editar Platillo';
        document.getElementById('modal-subtitle').textContent = 'Actualiza la información del platillo';
        
        openModalAlimento(false);
    }

    // Encontrar producto por ID
    function encontrarProductoPorId(id) {
        return estadoGlobal.productosMap[id] || null;
    }

    // Eliminar producto
    function eliminarProducto(id) {
        const producto = encontrarProductoPorId(id);
        if (!producto) {
            console.error('Producto no encontrado:', id);
            return;
        }
        abrirModalEliminar(id, producto.nombre);
    }

    // Toggle disponibilidad
    function toggleDisponibilidad(id) {
        fetch(`/admin/alimentos/api/${id}/toggle-disponibilidad`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            }
        })
        .then(response => response.json())
        .then(resultado => {
            cargarProductos();
            cargarEstadisticas();
        })
        .catch(error => console.error(error));
    }

    // Mostrar notificación
    function mostrarNotificacion(mensaje, tipo) {
        const notificacion = document.createElement('div');
        notificacion.className = `fixed top-4 right-4 px-6 py-3 rounded-lg font-bold text-white z-[200] ${
            tipo === 'success' ? 'bg-green-600' : 'bg-red-600'
        }`;
        notificacion.textContent = mensaje;
        document.body.appendChild(notificacion);
        
        setTimeout(() => notificacion.remove(), 3000);
    }
</script>
@endsection