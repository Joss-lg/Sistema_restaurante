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

{{-- MODAL AGREGAR/EDITAR --}}
<div id="modal-nuevo-alimento" class="fixed inset-0 z-[100] overflow-y-auto hidden opacity-0 transition-all duration-300 backdrop-blur-sm">
    <div class="fixed inset-0 bg-black/80" onclick="closeModalAlimento()"></div>
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative glass-card bg-[var(--card-color)] border border-[var(--border-color)] w-full max-w-2xl rounded-[2.5rem] shadow-2xl transform opacity-0 translate-y-8 transition-all duration-300" id="modal-panel">
            <div class="p-10">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h2 class="text-3xl font-black text-[var(--text-color)] tracking-tight" id="modal-title">Nuevo Platillo</h2>
                        <p class="text-[var(--text-muted)] mt-1" id="modal-subtitle">Configuración estética del menú</p>
                    </div>
                    <button onclick="closeModalAlimento()" class="text-gray-500 hover:text-white transition"><i class="fas fa-times text-2xl"></i></button>
                </div>

                <form id="formulario-alimento" onsubmit="guardarAlimento(event)">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <label class="text-xs font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Nombre del Platillo</label>
                            <input type="text" id="nombre" name="nombre" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-4 mt-2 text-[var(--text-color)] placeholder:text-[var(--text-muted)] focus:ring-2 focus:ring-blue-500 transition" placeholder="Ej: Lasagna de la Casa" required>
                        </div>
                        <div class="col-span-1">
                            <label class="text-xs font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Precio</label>
                            <input type="number" id="precio" name="precio" step="0.01" min="0" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-4 mt-2 text-[var(--text-color)] focus:ring-2 focus:ring-blue-500 transition" placeholder="0.00" required>
                        </div>
                        <div class="col-span-1">
                            <label class="text-xs font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Categoría</label>
                            <select id="categoria_id" name="categoria_id" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-4 mt-2 text-[var(--text-color)] focus:ring-2 focus:ring-blue-500 transition appearance-none" required>
                                <option value="">Selecciona una categoría</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-10 flex gap-4">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-black py-4 rounded-2xl transition shadow-lg shadow-blue-900/40" id="btn-guardar">GUARDAR CAMBIOS</button>
                        <button type="button" onclick="closeModalAlimento()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-400 font-black py-4 rounded-2xl transition">CANCELAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
            container.innerHTML = '<p class="text-center text-[var(--text-muted)] py-12">No hay platillos registrados aún.</p>';
            return;
        }

        Object.keys(estadoGlobal.productos).forEach(categoriaNombre => {
            const productos = estadoGlobal.productos[categoriaNombre];
            
            const seccion = document.createElement('div');
            seccion.className = 'mb-12';
            
            const icono = obtenerIconoCategoria(categoriaNombre);
            
            seccion.innerHTML = `
                <div class="flex items-center gap-4 mb-6 border-b border-gray-800 pb-4">
                    <div class="w-12 h-12 bg-orange-500/10 rounded-2xl flex items-center justify-center text-orange-500 text-2xl">
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
        card.innerHTML = `
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="text-lg font-black text-[var(--text-color)] tracking-tight group-hover:text-blue-400 transition">${producto.nombre}</h3>
                    <div class="flex gap-2 mt-2">
                        <span class="bg-black/50 ${producto.esta_disponible ? 'text-green-400 border-green-900/50' : 'text-red-400 border-red-900/50'} text-[10px] font-black px-2 py-0.5 rounded-md uppercase tracking-tighter border">${producto.esta_disponible ? 'Disponible' : 'No disponible'}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="w-10 h-10 rounded-xl bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white transition flex items-center justify-center" onclick="editarProducto(${producto.id})">
                        <i class="fas fa-edit text-sm"></i>
                    </button>
                    <button class="w-10 h-10 rounded-xl bg-red-900/20 text-red-500 hover:bg-red-900/40 transition flex items-center justify-center" onclick="eliminarProducto(${producto.id})">
                        <i class="fas fa-trash-alt text-sm"></i>
                    </button>
                </div>
            </div>

            <div class="flex justify-between items-center pt-3 border-t border-[var(--border-color)]">
                <span class="text-xl font-black text-[var(--text-color)]">$${parseFloat(producto.precio).toFixed(2)}<span class="text-xs text-[var(--text-muted)] font-medium ml-1 uppercase">MXN</span></span>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">${producto.esta_disponible ? 'Activo' : 'Inactivo'}</span>
                    <button class="w-10 h-5 rounded-full transition ${producto.esta_disponible ? 'bg-blue-600' : 'bg-gray-600'}" onclick="toggleDisponibilidad(${producto.id})">
                        <div class="w-3 h-3 bg-white rounded-full transition ${producto.esta_disponible ? 'ml-auto mr-1' : 'ml-1'}"></div>
                    </button>
                </div>
            </div>
        `;
        return card;
    }

    // Obtener icono por categoría
    function obtenerIconoCategoria(nombre) {
        const iconos = {
            'Pizzas': 'fas fa-pizza-slice',
            'Pastas': 'fas fa-utensils',
            'Bebidas': 'fas fa-glass-water',
            'Postres': 'fas fa-cake-slice',
            'Ensaladas': 'fas fa-leaf',
            'Carnes': 'fas fa-bacon',
            'Mariscos': 'fas fa-shrimp',
        };
        return iconos[nombre] || 'fas fa-plate-wheat';
    }

    // Abrir modal para agregar
    function openModalAlimento() {
        estadoGlobal.editandoId = null;
        document.getElementById('formulario-alimento').reset();
        document.getElementById('modal-title').textContent = 'Nuevo Platillo';
        document.getElementById('modal-subtitle').textContent = 'Configuración estética del menú';
        
        const modal = document.getElementById('modal-nuevo-alimento');
        const panel = document.getElementById('modal-panel');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            panel.classList.add('opacity-100', 'translate-y-0');
        }, 10);
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

        const url = estadoGlobal.editandoId 
            ? `/admin/alimentos/api/${estadoGlobal.editandoId}`
            : '/admin/alimentos/api/store';
        
        const metodo = estadoGlobal.editandoId ? 'PUT' : 'POST';

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
        })
        .catch(error => {
            mostrarNotificacion('Error al guardar el platillo', 'error');
            console.error(error);
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
        document.getElementById('categoria_id').value = producto.categoria_id || '';
        
        document.getElementById('modal-title').textContent = 'Editar Platillo';
        document.getElementById('modal-subtitle').textContent = 'Actualiza la información del platillo';
        
        openModalAlimento();
    }

    // Encontrar producto por ID
    function encontrarProductoPorId(id) {
        return estadoGlobal.productosMap[id] || null;
    }

    // Eliminar producto
    function eliminarProducto(id) {
        if (!confirm('¿Estás seguro de que deseas eliminar este platillo?')) return;

        fetch(`/admin/alimentos/api/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la solicitud');
            return response.json();
        })
        .then(resultado => {
            cargarProductos();
            cargarEstadisticas();
            mostrarNotificacion(resultado.message, 'success');
        })
        .catch(error => {
            mostrarNotificacion('Error al eliminar el platillo', 'error');
            console.error(error);
        });
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