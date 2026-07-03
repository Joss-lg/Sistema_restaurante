{{-- resources/views/admin/categorias/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Categorías | Ollintem Pro')

@section('content')
<div class="px-4 py-6 sm:p-6 lg:p-8 xl:p-10 max-w-[1800px] mx-auto w-full space-y-6 sm:space-y-8 relative z-10 font-sans min-h-screen bg-zinc-950 text-zinc-100 modo-crema:bg-white modo-crema:text-zinc-800 transition-colors duration-300">

    {{-- ======================================================== --}}
    {{-- HERO BANNER (ESTRUCTURA PREMIUM LIMPIA) --}}
    {{-- ======================================================== --}}
    <div class="w-full rounded-3xl border border-zinc-800/80 modo-crema:border-zinc-200 bg-zinc-900/30 modo-crema:bg-zinc-50/40 shadow-xl overflow-hidden relative group transition-all duration-300">
        
        {{-- Destellos Atmosféricos de Fondo (Mesh suave usando Tailwind) --}}
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-500/10 rounded-full blur-[100px] pointer-events-none transition-all duration-500 group-hover:scale-110"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-500/5 rounded-full blur-[100px] pointer-events-none transition-all duration-500 group-hover:scale-110"></div>
        
        <div class="relative z-10 p-6 sm:p-8 lg:p-10 space-y-8">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 sm:gap-8">
                
                {{-- Títulos y Contexto --}}
                <div class="max-w-2xl space-y-3">
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-500/20 bg-blue-500/10 px-3.5 py-1 backdrop-blur-sm shadow-inner">
                        <i class="fas fa-layer-group text-[10px] text-blue-400 modo-crema:text-blue-600"></i>
                        <span class="text-[9px] font-black uppercase tracking-[0.25em] text-blue-400 modo-crema:text-blue-600">Categorías Premium</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-zinc-100 modo-crema:text-zinc-900 leading-tight">
                        Gestión de Categorías
                    </h1>
                    <p class="text-xs sm:text-sm font-medium leading-relaxed text-zinc-400 modo-crema:text-zinc-500">
                        Diseña y organiza el menú de tu restaurante con un tablero visual arquitectónico. Rápido, preciso y con toda la información clave al primer vistazo.
                    </p>
                </div>
                
                {{-- Controles Superiores (Buscador y Creación) --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 w-full lg:w-auto shrink-0">
                    
                    {{-- Buscador Estilo Inset --}}
                    <div class="relative w-full sm:w-[260px]">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-500 modo-crema:text-zinc-400">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                        <input type="text" id="buscadorCategorias" placeholder="Buscar categoría..."
                            class="w-full h-11 rounded-xl bg-zinc-950/60 modo-crema:bg-zinc-100/80 border border-zinc-800 modo-crema:border-zinc-200/80 pl-9 pr-4 text-xs font-semibold text-zinc-100 modo-crema:text-zinc-800 placeholder:text-zinc-500 placeholder:opacity-70 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none shadow-inner transition-all" />
                    </div>
                    
                    {{-- Botón de Acción Sólido Premium --}}
                    @if(auth()->user()->tienePermiso('categories.agregar') || auth()->user()->tienePermiso('categorias.agregar'))   
                        <button onclick="openModalCrear()"
                            class="inline-flex items-center justify-center gap-2 h-11 rounded-xl bg-blue-600 hover:bg-blue-500 active:scale-[0.98] px-6 text-xs font-bold uppercase tracking-wider text-white shadow-lg shadow-blue-600/10 transition-all outline-none w-full sm:w-auto cursor-pointer">
                            <i class="fas fa-plus text-[10px]"></i> Crear Categoría
                        </button>
                    @endif
                </div>
            </div>

            {{-- Tarjetas de Métricas Internas --}}
            <div class="grid gap-4 sm:grid-cols-3 pt-2">
                <div class="rounded-2xl border border-zinc-800/60 modo-crema:border-zinc-200/60 bg-zinc-950/40 modo-crema:bg-white/60 p-5 transition-all hover:-translate-y-0.5 shadow-sm group">
                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500 modo-crema:text-zinc-400 group-hover:text-blue-500 modo-crema:group-hover:text-blue-600 transition-colors">Total Categorías</span>
                    <p class="mt-2 text-3xl font-black text-zinc-100 modo-crema:text-zinc-900 tracking-tight">{{ count($categorias) }}</p>
                    <p class="mt-1 text-[11px] font-medium text-zinc-500 modo-crema:text-zinc-400">Bloques registrados en el menú.</p>
                </div>
                <div class="rounded-2xl border border-zinc-800/60 modo-crema:border-zinc-200/60 bg-zinc-950/40 modo-crema:bg-white/60 p-5 transition-all hover:-translate-y-0.5 shadow-sm group">
                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500 modo-crema:text-zinc-400 group-hover:text-emerald-500 modo-crema:group-hover:text-emerald-600 transition-colors">Platillos Activos</span>
                    <p class="mt-2 text-3xl font-black text-zinc-100 modo-crema:text-zinc-900 tracking-tight">{{ $categorias->sum('productos_count') }}</p>
                    <p class="mt-1 text-[11px] font-medium text-zinc-500 modo-crema:text-zinc-400">Asignados a través del sistema.</p>
                </div>
                <div class="rounded-2xl border border-zinc-800/60 modo-crema:border-zinc-200/60 bg-zinc-950/40 modo-crema:bg-white/60 p-5 transition-all hover:-translate-y-0.5 shadow-sm group">
                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500 modo-crema:text-zinc-400 group-hover:text-purple-500 modo-crema:group-hover:text-purple-600 transition-colors">Jerarquía</span>
                    <p class="mt-2 text-3xl font-black text-zinc-100 modo-crema:text-zinc-900 tracking-tight">{{ $categorias->max('orden_visualizacion') ?? 0 }}</p>
                    <p class="mt-1 text-[11px] font-medium text-zinc-500 modo-crema:text-zinc-400">Orden de visualización máximo.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- TABLA DE CONTENIDO DE ALTA GAMA --}}
    {{-- ======================================================== --}}
    <div class="w-full rounded-3xl border border-zinc-800/80 modo-crema:border-zinc-200 bg-zinc-900/30 modo-crema:bg-zinc-50/40 shadow-xl overflow-hidden">
        
        {{-- Cabecera de la Tabla --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 sm:p-6 border-b border-zinc-800 modo-crema:border-zinc-200/80 bg-zinc-950/20 modo-crema:bg-white/40">
            <div>
                <h2 class="text-lg font-black text-zinc-100 modo-crema:text-zinc-900 tracking-tight">Catálogo General</h2>
                <p class="text-xs font-medium text-zinc-400 modo-crema:text-zinc-500 mt-0.5">Inventario y organización de tus categorías.</p>
            </div>
            
            <div class="inline-flex items-center gap-3 bg-zinc-950/60 modo-crema:bg-zinc-100 border border-zinc-800 modo-crema:border-zinc-200 rounded-xl px-4 py-2 shadow-inner w-fit">
                <span class="text-[9px] font-black uppercase tracking-wider text-zinc-400 modo-crema:text-zinc-500">Total Registradas</span>
                <div class="w-[1px] h-3.5 bg-zinc-800 modo-crema:bg-zinc-300"></div>
                <span class="text-sm font-black text-zinc-100 modo-crema:text-zinc-900">{{ count($categorias) }}</span>
            </div>
        </div>

        {{-- Tabla Responsiva con Scrollbar Premium --}}
        <div class="overflow-x-auto w-full [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-zinc-800 modo-crema:[&::-webkit-scrollbar-thumb]:bg-zinc-300 [&::-webkit-scrollbar-thumb]:rounded-full">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-zinc-800 modo-crema:border-zinc-200 bg-zinc-950/40 modo-crema:bg-zinc-50/70 text-[10px] font-black uppercase tracking-widest text-zinc-400 modo-crema:text-zinc-500">
                        <th class="px-6 py-4.5 sm:px-8">Categoría</th>
                        <th class="px-6 py-4.5 sm:px-8">Identificador</th>
                        <th class="px-6 py-4.5 sm:px-8 text-center">Prioridad</th>
                        <th class="px-6 py-4.5 sm:px-8">Contenido</th>
                        <th class="px-6 py-4.5 sm:px-8 text-right">Ajustes</th>
                    </tr>
                </thead>
                <tbody id="tablaCategorias" class="divide-y divide-zinc-800/50 modo-crema:divide-zinc-200/60 font-medium text-sm text-zinc-300 modo-crema:text-zinc-700">
                    @forelse($categorias as $categoria)
                    <tr class="fila-categoria group hover:bg-zinc-900/30 modo-crema:hover:bg-zinc-50/60 transition-colors duration-150">
                        
                        {{-- Categoría / Nombre Visual --}}
                        <td class="px-6 py-4.5 sm:px-8 nombre-celda">
                            <div class="flex items-center gap-4">
                                <div class="h-11 w-11 rounded-xl border border-zinc-800/80 modo-crema:border-zinc-200 bg-zinc-950/60 modo-crema:bg-zinc-100 shadow-inner flex items-center justify-center group-hover:scale-105 group-hover:rotate-2 transition-all duration-300 relative overflow-hidden shrink-0">
                                    {{-- Resplandor suave usando el color dinámico --}}
                                    <div class="absolute inset-0 opacity-15" style="background-color: {{ $categoria->color ?? '#3B82F6' }}"></div>
                                    <span class="text-base font-black relative z-10" style="color: {{ $categoria->color ?? '#3B82F6' }}">
                                        {{ substr($categoria->nombre, 0, 1) }}
                                    </span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-zinc-100 modo-crema:text-zinc-900 tracking-tight group-hover:text-blue-400 modo-crema:group-hover:text-blue-600 transition-colors">
                                        {{ $categoria->nombre }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Identificador (Slug) --}}
                        <td class="px-6 py-4.5 sm:px-8">
                            <span class="inline-flex items-center rounded-lg bg-zinc-950/40 modo-crema:bg-zinc-100 border border-zinc-800/60 modo-crema:border-zinc-200 px-2.5 py-1 text-xs font-bold text-zinc-400 modo-crema:text-zinc-600 shadow-inner">
                                {{ $categoria->slug }}
                            </span>
                        </td>

                        {{-- Prioridad / Orden --}}
                        <td class="px-6 py-4.5 sm:px-8 text-center">
                            <span class="text-sm font-black text-zinc-200 modo-crema:text-zinc-900">{{ $categoria->orden_visualizacion ?? 0 }}</span>
                        </td>

                        {{-- Contenido (Cantidad Productos) --}}
                        <td class="px-6 py-4.5 sm:px-8">
                            <span class="inline-flex items-center rounded-lg bg-blue-500/10 border border-blue-500/20 px-2.5 py-1 text-xs font-bold text-blue-400 modo-crema:text-blue-600 shadow-sm">
                                {{ $categoria->productos_count ?? $categoria->productos()->count() }} Platillos
                            </span>
                        </td>

                        {{-- Ajustes y Botones de Control --}}
                        <td class="px-6 py-4.5 sm:px-8 text-right">
                            <div class="inline-flex items-center justify-end gap-2">
                                @if(auth()->user()->tienePermiso('categorias.editar'))
                                    <button type="button" title="Editar"
                                        onclick="abrirModalEspecifico('modalEditar-{{ $categoria->id }}')"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-800 modo-crema:border-zinc-200 bg-zinc-950/40 modo-crema:bg-white text-zinc-400 modo-crema:text-zinc-500 transition-all hover:bg-blue-600 hover:text-white hover:border-transparent hover:shadow-lg hover:shadow-blue-600/20 active:scale-95 cursor-pointer">
                                        <i class="fas fa-pen text-[10px]"></i>
                                    </button>
                                @endif
                                @if(auth()->user()->tienePermiso('categorias.eliminar'))
                                    <button type="button" title="Eliminar"
                                        onclick="confirmarEliminacion('{{ $categoria->id }}', '{{ $categoria->nombre }}')"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-800 modo-crema:border-zinc-200 bg-zinc-950/40 modo-crema:bg-white text-zinc-400 modo-crema:text-zinc-500 transition-all hover:bg-red-600 hover:text-white hover:border-transparent hover:shadow-lg hover:shadow-red-600/20 active:scale-95 cursor-pointer">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @include('admin.categorias.modal-editar', ['categoria' => $categoria])
                    
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-20 text-center">
                            <div class="mx-auto flex max-w-sm flex-col items-center gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-950/50 modo-crema:bg-zinc-100 border border-zinc-800 modo-crema:border-zinc-200 text-zinc-500 shadow-inner">
                                    <i class="fas fa-folder-open text-2xl"></i>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-black text-zinc-100 modo-crema:text-zinc-900 tracking-tight">Tu catálogo está vacío.</p>
                                    <p class="text-xs font-medium text-zinc-400 modo-crema:text-zinc-500">Crea tu primera categoría para organizar tu menú de platillos.</p>
                                </div>
                                <button onclick="openModalCrear()" class="mt-2 rounded-xl bg-zinc-100 text-zinc-950 modo-crema:bg-zinc-900 modo-crema:text-white px-5 py-2.5 text-xs font-black uppercase tracking-wider hover:opacity-90 active:scale-95 transition-all outline-none shadow-md cursor-pointer">
                                    Comenzar ahora
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modales de Soporte integrados ordenadamente adentro de la sección --}}
    @include('admin.categorias.modal-crear')
    @include('admin.categorias.modal-eliminar')

    {{-- Scripts de Interactividad Premium --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ==========================================================
            // BUSCADOR EN TIEMPO REAL (Filtro por caracteres)
            // ==========================================================
            const buscador = document.getElementById('buscadorCategorias');
            if (buscador) {
                buscador.addEventListener('input', function () {
                    const term = this.value.toLowerCase().trim();
                    const filas = document.querySelectorAll('.fila-categoria');

                    filas.forEach(fila => {
                        const celdaNombre = fila.querySelector('.nombre-celda');
                        const nombre = celdaNombre ? celdaNombre.textContent.toLowerCase() : '';
                        
                        if (nombre.includes(term)) {
                            fila.style.display = '';
                        } else {
                            fila.style.display = 'none';
                        }
                    });
                });
            }
        });

        // ==========================================================
        // CONTROLADORES DE MODALES DINÁMICOS (Editar / Específicos)
        // ==========================================================
        function abrirModalEspecifico(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            const container = modal.querySelector('div[id^="modalContainer-"]');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                if (container) {
                    container.classList.remove('scale-95', 'opacity-0');
                    container.classList.add('scale-100', 'opacity-100');
                }
            }, 15);
        }

        function cerrarModalEspecifico(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            const container = modal.querySelector('div[id^="modalContainer-"]');
            
            if (container) {
                container.classList.remove('scale-100', 'opacity-100');
                container.classList.add('scale-95', 'opacity-0');
            }
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        // ==========================================================
        // MODAL DE CREACIÓN DE CATEGORÍAS
        // ==========================================================
        function openModalCrear() {
            const modal = document.getElementById('modalCrear');
            const container = document.getElementById('createContainer');
            if (!modal || !container) return;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 15);
        }

        function closeCreateModal() {
            const modal = document.getElementById('modalCrear');
            const container = document.getElementById('createContainer');
            if (!container || !modal) return;

            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        // ==========================================================
        // MODAL DE ELIMINACIÓN SEGURA
        // ==========================================================
        function confirmarEliminacion(id, nombre) {
            const modal = document.getElementById('modalEliminar');
            const container = document.getElementById('deleteContainer');
            const form = document.getElementById('formEliminar');
            const display = document.getElementById('delete_nombre_display');
            
            if (!modal || !container) return;

            if (display) display.innerText = nombre;
            
            if (form) form.action = `{{ url('admin/categorias') }}/${id}`;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            setTimeout(() => {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }, 15);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('modalEliminar');
            const container = document.getElementById('deleteContainer');
            if (!container || !modal) return;

            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }
    </script>
</div>
@endsection