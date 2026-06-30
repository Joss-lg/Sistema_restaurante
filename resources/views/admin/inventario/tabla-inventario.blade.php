@extends('layouts.admin')

@section('title', 'Inventario | Ollintem Pro')

@section('content')
{{-- Ajustamos los paddings iniciales para que en móvil no desperdicien espacio (p-4 en móvil, p-8+ en pantallas grandes) --}}
<div class="p-4 sm:p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-6 sm:space-y-8 flex-1 flex flex-col">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 md:gap-6 mb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-[var(--text-color)] tracking-tight">Inventario del Restaurante</h1>
            <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1">Control de insumos y materia prima</p>
        </div>
        
        {{-- En móviles todo se apila de forma vertical y limpia ocupando el ancho completo --}}
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-72 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-[#3B82F6]">
                    <i class="fas fa-search text-[var(--text-muted)] text-sm"></i>
                </div>
                <input type="text" id="buscadorInventario" placeholder="Buscar ingrediente..." class="w-full h-12 bg-black/5 modo-crema:bg-black/5 border border-transparent rounded-xl pl-11 pr-4 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10 outline-none transition-all">
            </div>

            @if(auth()->user()->tienePermiso('gestionar.reporte'))
                <a href="{{ route('admin.inventario.exportar_pdf_bajo_stock') }}" 
                class="w-full sm:w-auto bg-[#e11d48] hover:bg-[#be123c] text-white px-5 py-3 rounded-xl text-sm font-bold transition-all shadow-lg shadow-[#e11d48]/20 outline-none flex items-center justify-center gap-2">
                    <i class="fas fa-file-pdf"></i> Reporte Bajo Stock
                </a>
            @endif

            @if(auth()->user()->tienePermiso('inventario.agregar'))
            <button onclick="openModalCrear()" class="w-full sm:w-auto bg-[#3B82F6] hover:bg-[#2563EB] text-white px-7 py-3 rounded-xl text-sm font-bold transition-all shadow-lg shadow-[#3B82F6]/20 outline-none flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i> Agregar Producto
            </button>
            @endif
        </div>
    </div>

    {{-- Contenedor principal: Ajuste de padding en móvil (p-4) para aprovechar la pantalla --}}
    <div class="bg-[var(--card-color)] rounded-[1.5rem] shadow-sm p-4 sm:p-6 lg:p-8 w-full">
        
        <div class="mb-6 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#3B82F6]/10 flex items-center justify-center text-[#3B82F6]">
                <i class="fas fa-boxes text-lg"></i>
            </div>
            <h2 class="text-lg sm:text-xl font-bold text-[var(--text-color)]">Existencias | <span class="text-[var(--text-muted)] font-normal text-xs sm:text-sm">{{ count($insumos ?? []) }} registrados</span></h2>
        </div>

        {{-- ================= VISTA PARA ESCRITORIO (Se oculta en móviles: hidden md:block) ================= --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Código</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Producto / Ingrediente</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Cantidad</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Unidad</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Stock Mínimo</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Estado</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaInventario">
                    @forelse($insumos ?? [] as $item)
                    <tr class="fila-articulo border-none hover:bg-black/5 transition-colors group rounded-xl">
                        <td class="py-4 px-4 text-xs font-mono text-[var(--text-muted)] rounded-l-xl">
                            {{ $item->codigo ?? 'S/N' }}
                        </td>
                        <td class="py-4 px-4 text-sm font-bold text-[var(--text-color)] nombre-celda">
                            {{ $item->nombre }}
                        </td>
                        <td class="py-4 px-4 text-sm font-medium text-[var(--text-color)]">
                            {{ number_format($item->stock_actual, 2) }}
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-3 py-1 bg-black/5 modo-crema:bg-white/5 rounded-md text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-widest">
                                {{ $item->unidad_medida }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-sm font-medium text-[var(--text-muted)]">
                            {{ number_format($item->stock_minimo, 2) }}
                        </td>
                        <td class="py-4 px-4">
                            @php
                                $minimo = $item->stock_minimo > 0 ? $item->stock_minimo : 1; 
                                $porcentaje = ($item->stock_actual / $minimo) * 100;
                                
                                if($porcentaje >= 150) {
                                    $colorClase = 'bg-[#22c55e]'; 
                                    $modoClase = 'modo-crema:bg-emerald-50 modo-crema:text-emerald-900';
                                    $textoEstado = 'Óptimo';
                                } elseif($porcentaje > 100) {
                                    $colorClase = 'bg-[#3B82F6]'; 
                                    $modoClase = 'modo-crema:bg-blue-100 modo-crema:text-blue-900';
                                    $textoEstado = 'Bien';
                                } elseif($porcentaje >= 50) {
                                    $colorClase = 'bg-orange-500'; 
                                    $modoClase = 'modo-crema:bg-orange-100 modo-crema:text-orange-900';
                                    $textoEstado = 'Regular';
                                } else {
                                    $colorClase = 'bg-rose-500'; 
                                    $modoClase = 'modo-crema:bg-rose-50 modo-crema:text-rose-900';
                                    $textoEstado = 'Crítico';
                                }
                            @endphp
                            <span class="px-3 py-1.5 {{ $colorClase }} {{ $modoClase }} text-white rounded-full text-[11px] font-black shadow-sm uppercase tracking-wider">
                                {{ $textoEstado }} ({{ round($porcentaje) }}%)
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right rounded-r-xl">
                            <div class="flex items-center justify-end gap-3">
                                @if(auth()->user()->tienePermiso('inventario.editar'))
                                <button type="button" title="Ajustar Stock"
                                    onclick="openModalMovimiento('{{ $item->id }}', '{{ $item->nombre }}')" 
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-emerald-500 hover:bg-emerald-500/10 transition-colors outline-none">
                                    <i class="fas fa-exchange-alt text-sm"></i>
                                </button>
                                <button type="button" title="Editar Detalles"
                                    onclick="abrirModalEspecifico('modalEditar-{{ $item->id }}')" 
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-[#3B82F6] hover:bg-[#3B82F6]/10 transition-colors outline-none">
                                    <i class="fas fa-cog text-sm"></i>
                                </button>
                                @endif
                                @if(auth()->user()->tienePermiso('inventario.eliminar'))
                                <button type="button" title="Dar de baja"
                                    onclick="confirmarEliminacion('{{ $item->id }}', '{{ $item->nombre }}')" 
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 transition-colors outline-none">
                                    <i class="far fa-trash-alt text-sm"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @include('admin.inventario.modal-editar', ['item' => $item])
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-[var(--text-muted)]">No hay productos registrados en el inventario.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ================= VISTA COMODA PARA CELULARES (Se activa en móviles: block md:hidden) ================= --}}
        <div class="block md:hidden space-y-4">
            @forelse($insumos ?? [] as $item)
                @php
                    // Mismo cálculo de estado para mantener la coherencia visual
                    $minimo = $item->stock_minimo > 0 ? $item->stock_minimo : 1; 
                    $porcentaje = ($item->stock_actual / $minimo) * 100;
                    
                    if($porcentaje >= 150) {
                        $colorClase = 'bg-[#22c55e]'; $modoClase = 'modo-crema:bg-emerald-50 modo-crema:text-emerald-900'; $textoEstado = 'Óptimo';
                    } elseif($porcentaje > 100) {
                        $colorClase = 'bg-[#3B82F6]'; $modoClase = 'modo-crema:bg-blue-100 modo-crema:text-blue-900'; $textoEstado = 'Bien';
                    } elseif($porcentaje >= 50) {
                        $colorClase = 'bg-orange-500'; $modoClase = 'modo-crema:bg-orange-100 modo-crema:text-orange-900'; $textoEstado = 'Regular';
                    } else {
                        $colorClase = 'bg-rose-500'; $modoClase = 'modo-crema:bg-rose-50 modo-crema:text-rose-900'; $textoEstado = 'Crítico';
                    }
                @endphp

                {{-- La clase "fila-articulo" permite que tu buscador JS encuentre y oculte esta tarjeta también --}}
                <div class="fila-articulo bg-black/5 modo-crema:bg-white/5 border border-black/5 dark:border-white/5 p-4 rounded-xl space-y-3 transition-all">
                    
                    {{-- Fila Superior: Código y Estado --}}
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="font-mono text-[var(--text-muted)]">Cod: {{ $item->codigo ?? 'S/N' }}</span>
                        <span class="px-2.5 py-1 {{ $colorClase }} {{ $modoClase }} text-white rounded-full font-black uppercase tracking-wider text-[10px]">
                            {{ $textoEstado }} ({{ round($porcentaje) }}%)
                        </span>
                    </div>

                    {{-- Nombre del producto (La clase "nombre-celda" mapea con tu buscador JS) --}}
                    <h3 class="text-sm font-black text-[var(--text-color)] nombre-celda leading-tight">
                        {{ $item->nombre }}
                    </h3>

                    {{-- Cuadrícula de Stocks (Limpia y espaciada para el dedo) --}}
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-black/5 dark:border-white/10 text-xs">
                        <div>
                            <span class="block text-[10px] uppercase font-black tracking-wider text-[var(--text-muted)] mb-0.5">Stock Actual</span>
                            <span class="font-bold text-[var(--text-color)] text-sm">
                                {{ number_format($item->stock_actual, 2) }}
                                <span class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider ml-0.5">{{ $item->unidad_medida }}</span>
                            </span>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-black tracking-wider text-[var(--text-muted)] mb-0.5">Mínimo Req.</span>
                            <span class="font-medium text-[var(--text-muted)] text-sm">
                                {{ number_format($item->stock_minimo, 2) }}
                            </span>
                        </div>
                    </div>

                    {{-- Botones de Acción de tamaño grande (Fáciles de presionar en pantallas touch) --}}
                    <div class="flex items-center gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                        @if(auth()->user()->tienePermiso('inventario.editar'))
                            <button type="button" 
                                onclick="openModalMovimiento('{{ $item->id }}', '{{ $item->nombre }}')" 
                                class="flex-1 h-10 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-500 text-xs font-bold rounded-lg flex items-center justify-center gap-1 transition-colors">
                                <i class="fas fa-exchange-alt"></i> Ajustar
                            </button>
                            <button type="button" 
                                onclick="abrirModalEspecifico('modalEditar-{{ $item->id }}')" 
                                class="flex-1 h-10 bg-[#3B82F6]/10 hover:bg-[#3B82F6]/20 text-[#3B82F6] text-xs font-bold rounded-lg flex items-center justify-center gap-1 transition-colors">
                                <i class="fas fa-cog"></i> Editar
                            </button>
                        @endif

                        @if(auth()->user()->tienePermiso('inventario.eliminar'))
                            <button type="button" 
                                onclick="confirmarEliminacion('{{ $item->id }}', '{{ $item->nombre }}')" 
                                class="w-10 h-10 bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 rounded-lg flex items-center justify-center transition-colors">
                                <i class="far fa-trash-alt text-sm"></i>
                            </button>
                        @endif
                    </div>
                </div>
                
                {{-- Mantenemos el include del modal de edición aquí adentro también --}}
                @if(!auth()->user()->tienePermiso('inventario.editar'))
                    @include('admin.inventario.modal-editar', ['item' => $item])
                @endif

            @empty
                <div class="py-8 text-center text-[var(--text-muted)] text-sm">No hay productos registrados en el inventario.</div>
            @endforelse
        </div>

    </div>
</div>

<script>
    // BUSCADOR EN TIEMPO REAL (Tu script intacto, ahora lee automáticamente filas y tarjetas móviles)
    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscadorInventario');
        const filas = document.querySelectorAll('.fila-articulo');
        if (buscador) {
            buscador.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase().trim();
                filas.forEach(fila => {
                    const nombre = fila.querySelector('.nombre-celda').textContent.toLowerCase();
                    fila.style.display = nombre.includes(term) ? '' : 'none';
                });
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
@endsection