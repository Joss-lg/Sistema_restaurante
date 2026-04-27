@extends('layouts.admin')

@section('title', 'Inventario | Ollintem Pro')

@section('content')
<div class="p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-2">
        <div>
            <h1 class="text-3xl font-black text-[var(--text-color)] tracking-tight">Inventario del Restaurante</h1>
            <p class="text-sm text-[var(--text-muted)] mt-1">Control de insumos y materia prima</p>
        </div>
        
        <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
            <div class="relative w-full md:w-72 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-[#3B82F6]">
                    <i class="fas fa-search text-[var(--text-muted)] text-sm"></i>
                </div>
                <input type="text" id="buscadorInventario" placeholder="Buscar ingrediente..." class="w-full h-12 bg-black/5 modo-crema:bg-black/5 border border-transparent rounded-xl pl-11 pr-4 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10 outline-none transition-all">
            </div>

            @if(auth()->user()->tienePermiso('inventario.agregar'))
            <button onclick="openModalCrear()" class="w-full md:w-auto bg-[#3B82F6] hover:bg-[#2563EB] text-white px-7 py-3 rounded-xl text-sm font-bold transition-all shadow-lg shadow-[#3B82F6]/20 outline-none flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i> Agregar Producto
            </button>
            @endif
        </div>
    </div>

    <div class="bg-[var(--card-color)] rounded-[1.5rem] shadow-sm p-6 lg:p-8 w-full">
        
        <div class="mb-6 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#3B82F6]/10 flex items-center justify-center text-[#3B82F6]">
                <i class="fas fa-boxes text-lg"></i>
            </div>
            <h2 class="text-xl font-bold text-[var(--text-color)]">Existencias | <span class="text-[var(--text-muted)] font-normal text-sm">{{ count($insumos ?? []) }} registrados</span></h2>
        </div>

        <div class="overflow-x-auto">
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
                                    $textoEstado = 'Óptimo';
                                } elseif($porcentaje > 100) {
                                    $colorClase = 'bg-[#3B82F6]'; 
                                    $textoEstado = 'Bien';
                                } elseif($porcentaje >= 50) {
                                    $colorClase = 'bg-orange-500'; 
                                    $textoEstado = 'Regular';
                                } else {
                                    $colorClase = 'bg-rose-500'; 
                                    $textoEstado = 'Crítico';
                                }
                            @endphp
                            <span class="px-3 py-1.5 {{ $colorClase }} text-white rounded-full text-[11px] font-black shadow-sm uppercase tracking-wider">
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
                                    onclick="abrirModalEditar('{{ $item->id }}', '{{ $item->nombre }}', '{{ $item->stock_actual }}', '{{ $item->stock_minimo }}', '{{ $item->unidad_medida }}')" 
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
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-[var(--text-muted)]">No hay productos registrados en el inventario.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // BUSCADOR EN TIEMPO REAL
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

    // --- MODAL AGREGAR (NUEVO) ---
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

    // --- MODAL EDITAR ---
    function abrirModalEditar(id, nombre, cantidad, minimo, unidad) {
        const modal = document.getElementById('modalEditar');
        const container = document.getElementById('modalContainer');
        const form = document.getElementById('formEditar');

        if (!modal || !container) return;
        if(form) form.action = `/admin/inventario/${id}`;
        
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_minimo').value = minimo;
        
        const selectUnidad = document.getElementById('edit_unidad');
        if (selectUnidad) selectUnidad.value = unidad.toLowerCase();

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModal() {
        const modal = document.getElementById('modalEditar');
        const container = document.getElementById('modalContainer');
        if(container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
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

    function openModalMovimiento(id, nombre) { console.log('Abrir Movimiento para:', nombre); }
</script>

{{-- LLAMADA A LOS MODALES DESDE ARCHIVOS SEPARADOS --}}
@include('admin.inventario.modal-crear')
@include('admin.inventario.modal-editar')
@include('admin.inventario.modal-eliminar')

@endsection