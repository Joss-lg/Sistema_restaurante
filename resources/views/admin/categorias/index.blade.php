{{-- resources/views/admin/categorias/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Categorías | Ollintem Pro')

@section('content')
<div class="px-4 py-6 sm:p-8 lg:p-10 max-w-[1800px] mx-auto w-full space-y-8 relative z-10 font-sans min-h-screen text-slate-800 dark:text-zinc-100 transition-colors duration-300">

    {{-- ======================================================== --}}
    {{-- ALERTAS DE SESIÓN (ÉXITO Y ERROR) --}}
    {{-- ======================================================== --}}
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 flex items-center gap-3 shadow-sm">
            <i class="fas fa-check-circle text-lg"></i>
            <span class="text-sm font-bold">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 flex items-center gap-3 shadow-sm">
            <i class="fas fa-exclamation-triangle text-lg"></i>
            <span class="text-sm font-bold">{{ session('error') }}</span>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- HEADER & MÉTRICAS (ESTILO DASHBOARD PREMIUM) --}}
    {{-- ======================================================== --}}
    <div class="flex flex-col xl:flex-row gap-6">
        
        {{-- Bloque Principal de Contexto --}}
        <div class="flex-1 rounded-2xl border border-slate-200 dark:border-zinc-800/80 bg-white dark:bg-zinc-900/50 p-6 sm:p-8 shadow-sm relative overflow-hidden flex flex-col justify-between group">
            
            {{-- Destellos sutiles (Solo visibles en dark mode) --}}
            <div class="hidden dark:block absolute -top-32 -left-32 w-64 h-64 bg-blue-500/10 rounded-full blur-[80px] pointer-events-none transition-transform duration-700 group-hover:translate-x-4"></div>
            
            <div class="relative z-10 space-y-4">
                <div class="inline-flex items-center gap-2 rounded-full border border-blue-200 dark:border-blue-500/20 bg-blue-50 dark:bg-blue-500/10 px-3 py-1.5 shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Catálogo</span>
                </div>
                
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-zinc-100">
                        Gestión de Categorías
                    </h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-zinc-400 max-w-xl leading-relaxed">
                        Organiza el menú de tu restaurante mediante bloques estructurales. Control rápido, preciso y con información en tiempo real.
                    </p>
                </div>
            </div>

            {{-- Controles (Buscador y Creación) --}}
            <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-4 relative z-10">
                {{-- Buscador Premium --}}
                <div class="relative w-full sm:max-w-sm">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-zinc-500">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input type="text" id="buscadorCategorias" placeholder="Buscar categoría por nombre..."
                        class="w-full h-11 rounded-xl bg-slate-50 dark:bg-zinc-950/50 border border-slate-200 dark:border-zinc-800 pl-10 pr-4 text-sm font-medium text-slate-700 dark:text-zinc-200 placeholder:text-slate-400 dark:placeholder:text-zinc-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all shadow-inner dark:shadow-none" />
                </div>
                
                {{-- Botón Crear --}}
                @if(auth()->user()->tienePermiso('categories.agregar') || auth()->user()->tienePermiso('categorias.agregar'))   
                    <button onclick="openModalCrear()"
                        class="inline-flex items-center justify-center gap-2 h-11 rounded-xl bg-blue-600 hover:bg-blue-700 dark:hover:bg-blue-500 px-6 text-xs font-bold uppercase tracking-wide text-white shadow-md shadow-blue-500/20 transition-all outline-none w-full sm:w-auto active:scale-95">
                        <i class="fas fa-plus"></i> Crear Categoría
                    </button>
                @endif
            </div>
        </div>

        {{-- Tarjetas de Métricas Laterales --}}
        <div class="w-full xl:w-80 flex flex-col sm:flex-row xl:flex-col gap-4">
            <div class="flex-1 rounded-2xl border border-slate-200 dark:border-zinc-800/80 bg-white dark:bg-zinc-900/50 p-6 shadow-sm hover:border-slate-300 dark:hover:bg-zinc-900 transition-colors flex flex-col justify-center">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Total Categorías</span>
                    <div class="h-8 w-8 rounded-full bg-slate-50 dark:bg-zinc-800 flex items-center justify-center text-slate-400">
                        <i class="fas fa-cubes text-xs"></i>
                    </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-zinc-100">{{ count($categorias) }}</p>
                <p class="mt-1 text-xs font-medium text-slate-400 dark:text-zinc-500">Bloques registrados en el menú</p>
            </div>
            
            <div class="flex-1 rounded-2xl border border-slate-200 dark:border-zinc-800/80 bg-white dark:bg-zinc-900/50 p-6 shadow-sm hover:border-slate-300 dark:hover:bg-zinc-900 transition-colors flex flex-col justify-center">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Platillos Activos</span>
                    <div class="h-8 w-8 rounded-full bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                        <i class="fas fa-utensils text-xs"></i>
                    </div>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-zinc-100">{{ $categorias->sum('productos_count') }}</p>
                <p class="mt-1 text-xs font-medium text-slate-400 dark:text-zinc-500">Asignados a través del sistema</p>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- TABLA DE DATOS ROBUSTA Y LIMPIA --}}
    {{-- ======================================================== --}}
    <div class="w-full rounded-2xl border border-slate-200 dark:border-zinc-800/80 bg-white dark:bg-zinc-900/40 shadow-sm overflow-hidden flex flex-col">
        
        {{-- Encabezado de la Tabla --}}
        <div class="px-6 py-5 border-b border-slate-200 dark:border-zinc-800/80 bg-slate-50/50 dark:bg-transparent flex justify-between items-center">
            <h2 class="text-base font-bold text-slate-800 dark:text-zinc-200">Listado de Categorías</h2>
            <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-zinc-800 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:text-zinc-400">
                {{ count($categorias) }} Registros
            </span>
        </div>

        <div class="overflow-x-auto w-full [&::-webkit-scrollbar]:h-2 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-slate-300 dark:[&::-webkit-scrollbar-thumb]:bg-zinc-700 [&::-webkit-scrollbar-thumb]:rounded-full">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900/80">
                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Categoría</th>
                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Área de Impresión</th>
                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400">Contenido</th>
                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-zinc-400 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaCategorias" class="divide-y divide-slate-100 dark:divide-zinc-800/60 bg-white dark:bg-transparent">
                    @forelse($categorias as $categoria)
                    <tr class="fila-categoria group hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors duration-200">
                        
                        {{-- Nombre e Icono Visual --}}
                        <td class="px-6 py-4.5 nombre-celda">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center shrink-0 border border-slate-200 dark:border-white/5 shadow-sm"
                                     style="background-color: {{ $categoria->color ?? '#3B82F6' }}15; color: {{ $categoria->color ?? '#3B82F6' }};">
                                    <span class="text-sm font-black uppercase">
                                        {{ substr($categoria->nombre, 0, 1) }}
                                    </span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-900 dark:text-zinc-200 group-hover:text-blue-600 dark:group-hover:text-white transition-colors">
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
                            <div class="inline-flex items-center gap-2 rounded-md bg-slate-100 dark:bg-zinc-800/80 px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700/50">
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
                                        class="h-9 w-9 rounded-xl flex items-center justify-center border border-blue-300 dark:border-blue-500/50 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-all shadow-sm outline-none">
                                        <i class="fas fa-pen text-[13px]"></i>
                                    </button>
                                @endif
                                @if(auth()->user()->tienePermiso('categorias.eliminar'))
                                    <button type="button" title="Eliminar"
                                        onclick="confirmarEliminacion('{{ $categoria->id }}', '{{ $categoria->nombre }}')"
                                        class="h-9 w-9 rounded-xl flex items-center justify-center border border-red-300 dark:border-red-500/50 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 transition-all shadow-sm outline-none">
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
                                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-zinc-800/50 text-slate-400 dark:text-zinc-500">
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
            
            // Ruta de borrado armada correctamente
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
</div>
@endsection