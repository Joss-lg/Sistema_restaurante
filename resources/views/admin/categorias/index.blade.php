{{-- resources/views/admin/categorias/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Categorías | Ollintem Pro')
@section('header-title', 'Gestión de Categorías')
@section('header-subtitle', 'Organiza y administra las categorías del menú')

@section('content')
<div class="px-4 py-6 sm:p-8 lg:p-10 max-w-[1800px] mx-auto w-full space-y-6 sm:space-y-8 relative z-10 font-sans min-h-screen text-slate-800 dark:text-zinc-100 transition-colors duration-300">

    {{-- ======================================================== --}}
    {{-- HEADER & MÉTRICAS (ESTILO DASHBOARD PREMIUM) --}}
    {{-- ======================================================== --}}
    <div class="flex flex-col xl:flex-row gap-4 sm:gap-6">
        
        {{-- Bloque Principal de Contexto --}}
        <div class="flex-1 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0f1115] p-5 sm:p-6 lg:p-8 shadow-sm relative overflow-hidden flex flex-col justify-between group">
            
            <div class="relative z-10 space-y-3 sm:space-y-4">
                <div class="inline-flex items-center gap-2 rounded-full border border-blue-200 dark:border-blue-500/30 bg-blue-50 dark:bg-blue-900/40 px-3 py-1.5 shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Catálogo</span>
                </div>
                
                <div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                        Gestión de Categorías
                    </h1>
                    <p class="mt-2 text-xs sm:text-sm text-slate-500 dark:text-zinc-400 max-w-xl leading-relaxed">
                        Organiza el menú de tu restaurante mediante bloques estructurales. Control rápido, preciso y con información en tiempo real.
                    </p>
                </div>
            </div>

            {{-- Controles (Buscador y Creación) --}}
            <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4 relative z-10">
                {{-- Buscador Premium con Activador de Teclado --}}
                <div class="relative w-full sm:max-w-sm">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-zinc-500">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" id="buscadorCategorias" data-teclado="texto" placeholder="Buscar categoría por nombre..."
                        class="w-full h-11 rounded-xl bg-slate-50 dark:bg-[#15171c] border border-slate-200 dark:border-slate-700 pl-10 pr-4 text-sm font-medium text-slate-700 dark:text-zinc-200 placeholder:text-slate-400 dark:placeholder:text-zinc-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all shadow-inner dark:shadow-none" />
                </div>
                
                {{-- Botón Crear --}}
                @if(auth()->user()->tienePermiso('categories.agregar') || auth()->user()->tienePermiso('categorias.agregar'))   
                    <button onclick="openModalCrear()"
                        class="inline-flex items-center justify-center gap-2 h-11 rounded-xl bg-blue-600 hover:bg-blue-700 dark:bg-[#2563eb] dark:hover:bg-blue-500 px-6 text-xs font-bold uppercase tracking-wide text-white shadow-md shadow-blue-500/20 transition-all outline-none w-full sm:w-auto active:scale-95">
                        <i class="fas fa-plus"></i> Crear Categoría
                    </button>
                @endif
            </div>
        </div>

        {{-- Tarjetas de Métricas Laterales --}}
        <div class="w-full xl:w-80 flex flex-row sm:flex-row xl:flex-col gap-3 sm:gap-4">
            {{-- Métrica 1: Total Categorías --}}
            <div class="flex-1 rounded-2xl border border-slate-300 dark:border-slate-500/70 bg-white dark:bg-[#0f1115] p-4 sm:p-6 shadow-sm hover:border-slate-400 dark:hover:border-slate-400 transition-colors flex flex-col justify-center">
                <div class="flex items-start justify-between">
                    <span class="text-[10px] sm:text-[11px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-1">Total Categorías</span>
                    <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl bg-slate-100 dark:bg-slate-800/80 flex items-center justify-center text-slate-500 dark:text-slate-300 shrink-0">
                        <i class="fas fa-cubes text-sm"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">{{ count($categorias) }}</p>
                <p class="mt-2 text-xs font-medium text-slate-400 dark:text-zinc-500 hidden xl:block">Bloques registrados en el menú</p>
            </div>
            
            {{-- Métrica 2: Platillos Activos --}}
            <div class="flex-1 rounded-2xl border border-emerald-300 dark:border-emerald-500/60 bg-white dark:bg-[#0f1115] p-4 sm:p-6 shadow-sm hover:border-emerald-400 dark:hover:border-emerald-400 transition-colors flex flex-col justify-center">
                <div class="flex items-start justify-between">
                    <span class="text-[10px] sm:text-[11px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-1">Platillos Activos</span>
                    <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                        <i class="fas fa-utensils text-sm"></i>
                    </div>
                </div>
                <p class="mt-2 text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $categorias->sum('productos_count') }}</p>
                <p class="mt-2 text-xs font-medium text-slate-400 dark:text-zinc-500 hidden xl:block">Asignados a través del sistema</p>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- VISTA MÓVIL: TARJETAS --}}
    {{-- ======================================================== --}}
    <div class="md:hidden space-y-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white">Listado de Categorías</h2>
            <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-zinc-800 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:text-zinc-300">
                {{ count($categorias) }} Registros
            </span>
        </div>

        @forelse($categorias as $categoria)
            <div class="fila-categoria rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0f1115] shadow-sm p-4 space-y-4">

                {{-- Nombre e Icono --}}
                <div class="nombre-celda flex items-center gap-3">
                    <div class="h-11 w-11 rounded-full flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700 shadow-sm"
                         style="background-color: {{ $categoria->color ?? '#3B82F6' }}15; color: {{ $categoria->color ?? '#3B82F6' }};">
                        <span class="text-sm font-black uppercase">
                            {{ substr($categoria->nombre, 0, 1) }}
                        </span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-semibold text-slate-900 dark:text-zinc-200 truncate">
                            {{ $categoria->nombre }}
                        </span>
                        <span class="text-[10px] font-medium text-slate-400 dark:text-zinc-500">
                            Añadido el {{ $categoria->created_at->format('d M, Y') }}
                        </span>
                    </div>
                </div>

                {{-- Área de Impresión y Contenido --}}
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <div class="inline-flex items-center gap-2 rounded-md bg-slate-100 dark:bg-zinc-800/50 px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-zinc-300 border border-slate-200 dark:border-slate-700">
                        @if($categoria->area_impresion == 'Cocina')
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                        @elseif($categoria->area_impresion == 'Barra')
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                        @elseif($categoria->area_impresion == 'Parrilla')
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        @else
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                        @endif
                        <span>{{ $categoria->area_impresion ?? 'Sin asignar' }}</span>
                    </div>

                    <span class="inline-flex items-center justify-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20">
                        {{ $categoria->productos_count ?? $categoria->productos()->count() }} Platillos
                    </span>
                </div>

                {{-- Botones de Acción --}}
                <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                    @if(auth()->user()->tienePermiso('categorias.editar'))
                        <button type="button"
                            onclick="abrirModalEspecifico('modalEditar-{{ $categoria->id }}')"
                            class="flex-1 h-10 rounded-xl flex items-center justify-center gap-2 border border-blue-300 dark:border-blue-500/30 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 active:scale-95 transition-all shadow-sm outline-none text-xs font-bold">
                            <i class="fas fa-pen text-[12px]"></i> Editar
                        </button>
                    @endif
                    @if(auth()->user()->tienePermiso('categorias.eliminar'))
                        <button type="button"
                            onclick="confirmarEliminacion('{{ $categoria->id }}', '{{ $categoria->nombre }}')"
                            class="flex-1 h-10 rounded-xl flex items-center justify-center gap-2 border border-red-300 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 active:scale-95 transition-all shadow-sm outline-none text-xs font-bold">
                            <i class="fas fa-trash-alt text-[12px]"></i> Eliminar
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0f1115] shadow-sm py-14 text-center">
                <div class="mx-auto flex max-w-sm flex-col items-center gap-4 px-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-zinc-800/50 text-slate-400 dark:text-zinc-500 border border-slate-200 dark:border-slate-700">
                        <i class="fas fa-folder-open text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-zinc-200">Aún no hay categorías</h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1.5 leading-relaxed">Comienza creando tu primera categoría para organizar el menú de tu restaurante correctamente.</p>
                    </div>
                    <button onclick="openModalCrear()" class="mt-2 inline-flex items-center gap-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-zinc-900 px-5 py-2.5 text-xs font-bold hover:opacity-90 transition-opacity outline-none shadow-md">
                        <i class="fas fa-plus"></i> Crear Primera Categoría
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    {{-- ======================================================== --}}
    {{-- VISTA ESCRITORIO/TABLET: TABLA --}}
    {{-- ======================================================== --}}
    <div class="hidden md:flex w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0f1115] shadow-sm overflow-hidden flex-col mt-2 sm:mt-6">
        
        {{-- Encabezado de la Tabla --}}
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-transparent flex justify-between items-center">
            <h2 class="text-base font-bold text-slate-800 dark:text-white">Listado de Categorías</h2>
            <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-zinc-800 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:text-zinc-300">
                {{ count($categorias) }} Registros
            </span>
        </div>

        <div class="overflow-x-auto w-full [&::-webkit-scrollbar]:h-2 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-slate-300 dark:[&::-webkit-scrollbar-thumb]:bg-zinc-700 [&::-webkit-scrollbar-thumb]:rounded-full">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-[#15171c]">
                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Categoría</th>
                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Área de Impresión</th>
                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Contenido</th>
                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaCategorias" class="divide-y divide-slate-100 dark:divide-slate-700/80 bg-white dark:bg-transparent">
                    @forelse($categorias as $categoria)
                    <tr class="fila-categoria group hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors duration-200">
                        
                        {{-- Nombre e Icono Visual --}}
                        <td class="px-6 py-4.5 nombre-celda">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700 shadow-sm"
                                     style="background-color: {{ $categoria->color ?? '#3B82F6' }}15; color: {{ $categoria->color ?? '#3B82F6' }};">
                                    <span class="text-sm font-black uppercase">
                                        {{ substr($categoria->nombre, 0, 1) }}
                                    </span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-900 dark:text-zinc-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ $categoria->nombre }}
                                    </span>
                                    <span class="text-[11px] font-medium text-slate-400 dark:text-zinc-500">
                                        Añadido el {{ $categoria->created_at->format('d M, Y') }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- Área de Impresión --}}
                        <td class="px-6 py-4.5">
                            <div class="inline-flex items-center gap-2 rounded-md bg-slate-100 dark:bg-zinc-800/50 px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-zinc-300 border border-slate-200 dark:border-slate-700">
                                @if($categoria->area_impresion == 'Cocina')
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                @elseif($categoria->area_impresion == 'Barra')
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                @elseif($categoria->area_impresion == 'Parrilla')
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                @endif
                                <span>{{ $categoria->area_impresion ?? 'Sin asignar' }}</span>
                            </div>
                        </td>

                        {{-- Contenido --}}
                        <td class="px-6 py-4.5">
                            <span class="inline-flex items-center justify-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20 min-w-[70px]">
                                {{ $categoria->productos_count ?? $categoria->productos()->count() }} Platillos
                            </span>
                        </td>

                        {{-- Botones de Acción --}}
                        <td class="px-6 py-4.5">
                            <div class="flex items-center justify-center gap-2.5">
                                @if(auth()->user()->tienePermiso('categorias.editar'))
                                    <button type="button" title="Editar"
                                        onclick="abrirModalEspecifico('modalEditar-{{ $categoria->id }}')"
                                        class="h-9 w-9 rounded-xl flex items-center justify-center border border-blue-300 dark:border-blue-500/30 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-all shadow-sm outline-none">
                                        <i class="fas fa-pen text-[13px]"></i>
                                    </button>
                                @endif
                                @if(auth()->user()->tienePermiso('categorias.eliminar'))
                                    <button type="button" title="Eliminar"
                                        onclick="confirmarEliminacion('{{ $categoria->id }}', '{{ $categoria->nombre }}')"
                                        class="h-9 w-9 rounded-xl flex items-center justify-center border border-red-300 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 transition-all shadow-sm outline-none">
                                        <i class="fas fa-trash-alt text-[13px]"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @include('admin.categorias.modal-editar', ['categoria' => $categoria])
                    
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="mx-auto flex max-w-sm flex-col items-center gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-zinc-800/50 text-slate-400 dark:text-zinc-500 border border-slate-200 dark:border-slate-700">
                                    <i class="fas fa-folder-open text-3xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-zinc-200">Aún no hay categorías</h3>
                                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1.5 leading-relaxed">Comienza creando tu primera categoría para organizar el menú de tu restaurante correctamente.</p>
                                </div>
                                <button onclick="openModalCrear()" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-zinc-900 px-5 py-2.5 text-xs font-bold hover:opacity-90 transition-opacity outline-none shadow-md">
                                    <i class="fas fa-plus"></i> Crear Primera Categoría
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modales --}}
    @include('admin.categorias.modal-crear')
    @include('admin.categorias.modal-eliminar')

    {{-- Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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

        function confirmarEliminacion(id, nombre) {
            const modal = document.getElementById('modalEliminar');
            const container = document.getElementById('deleteContainer');
            const form = document.getElementById('formEliminar');
            const display = document.getElementById('delete_nombre_display');
            
            if (!modal || !container) return;
            if (display) display.innerText = nombre;
            
            let urlBase = "{{ route('admin.categorias.index') }}"; 
            if (form) form.action = `${urlBase}/${id}`;
            
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

    {{-- ======================================================== --}}
    {{-- INCLUSIÓN DEL TECLADO VIRTUAL --}}
    {{-- ======================================================== --}}
    @include('partials.teclado-virtual')

    {{-- Script de tu teclado --}}
    <script src="{{ asset('js/teclado-virtual.js') }}"></script>

</div>
@endsection