@extends('layouts.admin')

@section('title', 'Inventario | Ollintem Pro')

@section('content')
{{-- Ajustamos los paddings iniciales para que en móvil no desperdicien espacio --}}
<div class="p-4 sm:p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-6 sm:space-y-8 flex-1 flex flex-col">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 md:gap-6 mb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-[var(--text-color)] dark:text-zinc-100 tracking-tight">Inventario del Restaurante</h1>
            <p class="text-xs sm:text-sm text-[var(--text-muted)] dark:text-zinc-500 mt-1">Control de insumos y materia prima</p>
        </div>
        
        {{-- En móviles todo se apila de forma vertical y limpia ocupando el ancho completo --}}
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-72 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-blue-500">
                    <i class="fas fa-search text-[var(--text-muted)] dark:text-zinc-500 text-sm"></i>
                </div>
                {{-- AQUÍ AGREGAMOS data-teclado="texto" --}}
                <input type="text" id="buscadorInventario" data-teclado="texto" placeholder="Buscar ingrediente..." class="w-full h-12 bg-black/5 dark:bg-zinc-900/50 modo-crema:bg-black/5 border border-zinc-200/60 dark:border-zinc-800/60 rounded-2xl pl-11 pr-4 text-xs font-bold text-[var(--text-color)] dark:text-zinc-100 focus:bg-[var(--card-color)] dark:focus:bg-zinc-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
            </div>

            @if(auth()->user()->tienePermiso('gestionar.reporte'))
                <a href="{{ route('admin.inventario.exportar_pdf_bajo_stock') }}" 
                class="w-full sm:w-auto bg-rose-600 hover:bg-rose-700 text-white px-6 h-12 rounded-2xl text-xs font-black uppercase tracking-[0.15em] transition-all shadow-lg shadow-rose-600/20 hover:shadow-rose-600/30 active:scale-95 outline-none flex items-center justify-center gap-2">
                    <i class="fas fa-file-pdf"></i> Reporte Bajo Stock
                </a>
            @endif

            @if(auth()->user()->tienePermiso('inventario.agregar'))
            <button onclick="openModalCrear()" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-7 h-12 rounded-2xl text-xs font-black uppercase tracking-[0.15em] transition-all shadow-lg shadow-blue-600/20 hover:shadow-blue-600/30 active:scale-95 outline-none flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i> Agregar Producto
            </button>
            @endif
        </div>
    </div>

    {{-- Contenedor principal: Ajuste de bordes redondeados y padding en móvil para aprovechar la pantalla --}}
    <div class="bg-[var(--card-color)] border border-zinc-200/60 dark:border-zinc-800/60 rounded-2xl sm:rounded-[2.5rem] shadow-sm p-4 sm:p-6 lg:p-8 w-full">
        
        <div class="mb-6 flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-blue-500/10 dark:bg-blue-500/15 flex items-center justify-center text-blue-600 dark:text-blue-400 border border-blue-500/5 dark:border-blue-500/10">
                <i class="fas fa-boxes text-lg"></i>
            </div>
            <h2 class="text-lg sm:text-xl font-black text-[var(--text-color)] dark:text-zinc-100 uppercase tracking-tight">Existencias | <span class="text-[var(--text-muted)] dark:text-zinc-500 font-bold text-xs sm:text-sm normal-case tracking-normal">{{ count($insumos ?? []) }} registrados</span></h2>
        </div>

        {{-- ================= VISTA PARA ESCRITORIO (pantallas grandes, >=1024px) ================= --}}
        {{-- Cambiado de md a lg: las pantallas táctiles de POS (1024-1280px) ahora usan la vista de tarjetas --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-900">
                        <th class="pb-4 px-4 text-[10px] font-black text-[var(--text-muted)] dark:text-zinc-500 uppercase tracking-[0.2em]">Código</th>
                        <th class="pb-4 px-4 text-[10px] font-black text-[var(--text-muted)] dark:text-zinc-500 uppercase tracking-[0.2em]">Producto / Ingrediente</th>
                        <th class="pb-4 px-4 text-[10px] font-black text-[var(--text-muted)] dark:text-zinc-500 uppercase tracking-[0.2em]">Cantidad</th>
                        <th class="pb-4 px-4 text-[10px] font-black text-[var(--text-muted)] dark:text-zinc-500 uppercase tracking-[0.2em]">Unidad</th>
                        <th class="pb-4 px-4 text-[10px] font-black text-[var(--text-muted)] dark:text-zinc-500 uppercase tracking-[0.2em]">Stock Mínimo</th>
                        <th class="pb-4 px-4 text-[10px] font-black text-[var(--text-muted)] dark:text-zinc-500 uppercase tracking-[0.2em]">Estado</th>
                        <th class="pb-4 px-4 text-[10px] font-black text-[var(--text-muted)] dark:text-zinc-500 uppercase tracking-[0.2em] text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaInventario">
                    @forelse($insumos ?? [] as $item)
                    <tr class="fila-articulo border-b border-zinc-100/50 dark:border-zinc-900/30 hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors group">
                        <td class="py-4 px-4 text-xs font-mono font-bold text-[var(--text-muted)] dark:text-zinc-500">
                            {{ $item->codigo ?? 'S/N' }}
                        </td>
                        <td class="py-4 px-4 text-sm font-black text-[var(--text-color)] dark:text-zinc-200 nombre-celda">
                            {{ $item->nombre }}
                        </td>
                        <td class="py-4 px-4 text-sm font-black text-[var(--text-color)] dark:text-zinc-100">
                            {{ number_format($item->stock_actual, 2) }}
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-3 py-1.5 bg-zinc-100 dark:bg-white/5 border border-zinc-200/40 dark:border-zinc-800/40 rounded-xl text-[10px] font-black text-[var(--text-muted)] dark:text-zinc-400 uppercase tracking-widest">
                                {{ $item->unidad_medida }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm font-bold text-[var(--text-muted)] dark:text-zinc-400">
                            {{ number_format($item->stock_minimo, 2) }}
                        </td>
                        <td class="py-4 px-4">
                            @php
                                $minimo = $item->stock_minimo > 0 ? $item->stock_minimo : 1; 
                                $porcentaje = ($item->stock_actual / $minimo) * 100;
                                
                                if($porcentaje >= 150) {
                                    $colorClase = 'bg-emerald-500 text-white shadow-emerald-500/10'; 
                                    $modoClase = 'modo-crema:bg-emerald-50 modo-crema:text-emerald-900';
                                    $textoEstado = 'Óptimo';
                                } elseif($porcentaje > 100) {
                                    $colorClase = 'bg-blue-500 text-white shadow-blue-500/10'; 
                                    $modoClase = 'modo-crema:bg-blue-100 modo-crema:text-blue-900';
                                    $textoEstado = 'Bien';
                                } elseif($porcentaje >= 50) {
                                    $colorClase = 'bg-amber-500 text-white shadow-amber-500/10'; 
                                    $modoClase = 'modo-crema:bg-orange-100 modo-crema:text-orange-900';
                                    $textoEstado = 'Regular';
                                } else {
                                    $colorClase = 'bg-rose-500 text-white shadow-rose-500/10'; 
                                    $modoClase = 'modo-crema:bg-rose-50 modo-crema:text-rose-900';
                                    $textoEstado = 'Crítico';
                                }
                            @endphp
                            <span class="px-3 py-1.5 inline-block {{ $colorClase }} {{ $modoClase }} rounded-full text-[10px] font-black shadow-sm uppercase tracking-wider">
                                {{ $textoEstado }} ({{ round($porcentaje) }}%)
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-2.5">
                                @if(auth()->user()->tienePermiso('inventario.editar'))
                                <button type="button" title="Ajustar Stock"
                                    onclick="openModalMovimiento('{{ $item->id }}', '{{ $item->nombre }}')" 
                                    class="w-11 h-11 flex items-center justify-center rounded-xl text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 transition-all outline-none">
                                    <i class="fas fa-exchange-alt text-sm"></i>
                                </button>
                                <button type="button" title="Editar Detalles"
                                    onclick="abrirModalEspecifico('modalEditar-{{ $item->id }}')" 
                                    class="w-11 h-11 flex items-center justify-center rounded-xl text-blue-600 dark:text-blue-400 bg-blue-500/10 hover:bg-blue-500/20 transition-all outline-none">
                                    <i class="fas fa-cog text-sm"></i>
                                </button>
                                @endif
                                @if(auth()->user()->tienePermiso('inventario.eliminar'))
                                <button type="button" title="Dar de baja"
                                    onclick="confirmarEliminacion('{{ $item->id }}', '{{ $item->nombre }}')" 
                                    class="w-11 h-11 flex items-center justify-center rounded-xl text-rose-600 dark:text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 transition-all outline-none">
                                    <i class="far fa-trash-alt text-sm"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @include('admin.inventario.modal-editar', ['item' => $item])
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-[var(--text-muted)] dark:text-zinc-500 font-bold text-sm">No hay productos registrados en el inventario.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ================= VISTA DE TARJETAS (móvil y pantallas táctiles POS hasta 1024px) ================= --}}
        {{-- Cambiado de md a lg: esto cubre también las pantallas táctiles del restaurante --}}
        <div class="block lg:hidden space-y-4">
            @forelse($insumos ?? [] as $item)
                @php
                    $minimo = $item->stock_minimo > 0 ? $item->stock_minimo : 1; 
                    $porcentaje = ($item->stock_actual / $minimo) * 100;
                    
                    if($porcentaje >= 150) {
                        $colorClase = 'bg-emerald-500 text-white'; $modoClase = 'modo-crema:bg-emerald-50 modo-crema:text-emerald-900'; $textoEstado = 'Óptimo';
                    } elseif($porcentaje > 100) {
                        $colorClase = 'bg-blue-500 text-white'; $modoClase = 'modo-crema:bg-blue-100 modo-crema:text-blue-900'; $textoEstado = 'Bien';
                    } elseif($porcentaje >= 50) {
                        $colorClase = 'bg-amber-500 text-white'; $modoClase = 'modo-crema:bg-orange-100 modo-crema:text-orange-900'; $textoEstado = 'Regular';
                    } else {
                        $colorClase = 'bg-rose-500 text-white'; $modoClase = 'modo-crema:bg-rose-50 modo-crema:text-rose-900'; $textoEstado = 'Crítico';
                    }
                @endphp

                <div class="fila-articulo bg-zinc-50 dark:bg-zinc-900/40 border border-zinc-200/60 dark:border-zinc-800/60 p-4 rounded-2xl space-y-3 transition-all shadow-sm">
                    
                    {{-- Fila Superior: Código y Estado --}}
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="font-mono font-bold text-[var(--text-muted)] dark:text-zinc-500">Cod: {{ $item->codigo ?? 'S/N' }}</span>
                        <span class="px-2.5 py-1 {{ $colorClase }} {{ $modoClase }} rounded-full font-black uppercase tracking-wider text-[10px]">
                            {{ $textoEstado }} ({{ round($porcentaje) }}%)
                        </span>
                    </div>

                    {{-- Nombre del producto --}}
                    <h3 class="text-base font-black text-[var(--text-color)] dark:text-zinc-100 nombre-celda leading-tight uppercase tracking-tight">
                        {{ $item->nombre }}
                    </h3>

                    {{-- Cuadrícula de Stocks --}}
                    <div class="grid grid-cols-2 gap-2 pt-3 border-t border-zinc-200/50 dark:border-zinc-800/50 text-xs">
                        <div>
                            <span class="block text-[9px] uppercase font-black tracking-[0.15em] text-[var(--text-muted)] dark:text-zinc-500 mb-0.5">Stock Actual</span>
                            <span class="font-black text-[var(--text-color)] dark:text-zinc-100 text-sm">
                                {{ number_format($item->stock_actual, 2) }}
                                <span class="text-[10px] font-black text-[var(--text-muted)] dark:text-zinc-400 uppercase tracking-wider ml-0.5">{{ $item->unidad_medida }}</span>
                            </span>
                        </div>
                        <div>
                            <span class="block text-[9px] uppercase font-black tracking-[0.15em] text-[var(--text-muted)] dark:text-zinc-500 mb-0.5">Mínimo Req.</span>
                            <span class="font-bold text-[var(--text-muted)] dark:text-zinc-400 text-sm">
                                {{ number_format($item->stock_minimo, 2) }}
                            </span>
                        </div>
                    </div>

                    {{-- Botones de Acción de tamaño grande --}}
                    <div class="flex items-center gap-2 pt-3 border-t border-zinc-200/50 dark:border-zinc-800/50">
                        @if(auth()->user()->tienePermiso('inventario.editar'))
                            <button type="button" 
                                onclick="openModalMovimiento('{{ $item->id }}', '{{ $item->nombre }}')" 
                                class="flex-1 h-11 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-wider rounded-xl flex items-center justify-center gap-1.5 transition-colors">
                                <i class="fas fa-exchange-alt"></i> Ajustar
                            </button>
                            <button type="button" 
                                onclick="abrirModalEspecifico('modalEditar-{{ $item->id }}')" 
                                class="flex-1 h-11 bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 text-[10px] font-black uppercase tracking-wider rounded-xl flex items-center justify-center gap-1.5 transition-colors">
                                <i class="fas fa-cog"></i> Editar
                            </button>
                        @endif

                        @if(auth()->user()->tienePermiso('inventario.eliminar'))
                            <button type="button" 
                                onclick="confirmarEliminacion('{{ $item->id }}', '{{ $item->nombre }}')" 
                                class="w-11 h-11 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-xl flex items-center justify-center transition-colors">
                                <i class="far fa-trash-alt text-sm"></i>
                            </button>
                        @endif
                    </div>
                </div>
                
                {{-- Corregido: El modal de edición móvil debe incluirse siempre si se tiene el permiso de editar --}}
                @if(auth()->user()->tienePermiso('inventario.editar'))
                    @include('admin.inventario.modal-editar', ['item' => $item])
                @endif

            @empty
                <div class="py-8 text-center text-[var(--text-muted)] dark:text-zinc-500 font-bold text-sm">No hay productos registrados en el inventario.</div>
            @endforelse
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- APÉNDICE DE MODALES AL BODY (Arreglo del Sidebar en Móviles) ---
        // Toma dinámicamente todos los modales existentes y los pasa a la raíz del body
        const modales = document.querySelectorAll('#modalCrear, #modalEliminar, #modalMovimiento, [id^="modalEditar-"], [id^="modalMovimiento-"]');
        modales.forEach(modal => {
            if(modal) {
                document.body.appendChild(modal);
            }
        });

        // --- BUSCADOR EN TIEMPO REAL CON INTEGRACIÓN DE TECLADO VIRTUAL ---
        const buscador = document.getElementById('buscadorInventario');
        const filas = document.querySelectorAll('.fila-articulo');
        
        function filtrarInventario(term) {
            filas.forEach(fila => {
                const nombre = fila.querySelector('.nombre-celda').textContent.toLowerCase();
                fila.style.display = nombre.includes(term) ? '' : 'none';
            });
        }

        if (buscador) {
            // Escucha tanto el input normal (teclado físico) como los eventos custom (teclado virtual)
            buscador.addEventListener('input', function(e) {
                filtrarInventario(e.target.value.toLowerCase().trim());
            });
            
            buscador.addEventListener('virtualKeyboardInput', function(e) {
                filtrarInventario(e.target.value.toLowerCase().trim());
            });
        }
    });

    // --- MODAL AGREGAR (NUEVO INSUMO) ---
    function openModalCrear() {
        const modal = document.getElementById('modalCrear');
        const container = document.getElementById('createContainer');
        if (!modal || !container) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    // El nombre de esta función coincide con tu disparador onclick
    function closeCreateModal() {
        const modal = document.getElementById('modalCrear');
        const container = document.getElementById('createContainer');
        if(container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }

    // --- MODAL EDITAR DINÁMICO ---
    function abrirModalEspecifico(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        const container = modal.querySelector('div[id^="modalContainer-"]');
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            if(container) {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }
        }, 10);
    }

    function cerrarModalEspecifico(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const container = modal.querySelector('div[id^="modalContainer-"]');
        
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        if(container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
        }
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    // --- MODAL ELIMINAR ---
    function confirmarEliminacion(id, nombre) {
        const modal = document.getElementById('modalEliminar');
        const container = document.getElementById('deleteContainer');
        const form = document.getElementById('formEliminar');
        const displayNombre = document.getElementById('delete_nombre_display');

        if (!modal || !container) return;
        if(displayNombre) displayNombre.innerText = nombre;
        if(form) form.action = `/admin/inventario/${id}`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('modalEliminar');
        const container = document.getElementById('deleteContainer');
        if(container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }
</script>

@include('admin.inventario.modal-crear')
@include('admin.inventario.modal-eliminar')
@include('admin.inventario.modal-movimiento')

{{-- AQUÍ INCLUIMOS EL COMPONENTE DEL TECLADO VIRTUAL --}}
@include('partials.teclado-virtual')
@endsection