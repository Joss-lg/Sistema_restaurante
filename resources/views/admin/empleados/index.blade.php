@extends('layouts.admin')

@section('title', 'Empleados | Ollintem Pro')
@section('header-title', 'Gestión de Personal')
@section('header-subtitle', 'Administra roles y permisos del equipo')

@section('content')
<div class="p-4 sm:p-6 md:p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-6 md:space-y-8 flex-1 flex flex-col">
    
    {{-- CABECERA DE LA SECCIÓN --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-[var(--text-color)] tracking-tight">Empleados</h1>
        </div>

        @if(auth()->user()->tienePermiso('empleados.agregar'))
            <div class="relative group z-10 w-full sm:w-auto">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl blur opacity-30 group-hover:opacity-60 transition duration-500 pointer-events-none"></div>
                <button type="button" onclick="openModal()" class="relative flex items-center justify-center gap-2.5 bg-[#3B82F6] border border-white/10 text-white px-6 py-3.5 rounded-xl text-xs font-bold transition-all duration-300 hover:-translate-y-0.5 outline-none w-full sm:w-auto">
                    <i class="fas fa-plus"></i> 
                    <span>Agregar Empleado</span>
                </button>
            </div>
        @endif
    </div>

    {{-- ESTADÍSTICAS --}}
    @php
        $totalAdmin = 0; $totalCapitan = 0; $totalMesero = 0; $totalCocinero = 0; $totalCajero = 0;
        foreach($empleados ?? [] as $emp) {
            $rolSlug = strtolower($emp->rol?->slug ?? '');
            if(in_array($rolSlug, ['admin', 'administrador'])) $totalAdmin++;
            elseif(in_array($rolSlug, ['capitan'])) $totalCapitan++;
            elseif($rolSlug == 'mesero') $totalMesero++;
            elseif(in_array($rolSlug, ['cocinero'])) $totalCocinero++;
            elseif($rolSlug == 'cajero') $totalCajero++;
        }
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-6">
        @foreach(['Administradores' => $totalAdmin, 'Capitanes' => $totalCapitan, 'Meseros' => $totalMesero, 'Cocineros' => $totalCocinero, 'Cajeros' => $totalCajero] as $label => $count)
        <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-xl md:rounded-[1.5rem] p-4 md:p-6 flex flex-col justify-between h-24 md:h-32 group relative overflow-hidden shadow-sm">
            <h3 class="text-[8px] md:text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em]">{{ $label }}</h3>
            <p class="text-3xl md:text-5xl font-black text-[var(--text-color)]">{{ $count }}</p>
        </div>
        @endforeach
    </div>

    {{-- FILTRO DE INACTIVOS --}}
    <div class="flex justify-end">
        <a href="{{ request()->has('ver_inactivos') ? route('admin.empleados.index') : route('admin.empleados.index', ['ver_inactivos' => 1]) }}" 
           class="text-xs font-bold {{ request()->has('ver_inactivos') ? 'text-rose-500' : 'text-blue-500' }} hover:underline flex items-center gap-2">
            <i class="fas fa-{{ request()->has('ver_inactivos') ? 'eye-slash' : 'eye' }}"></i> 
            {{ request()->has('ver_inactivos') ? 'Ocultar inactivos' : 'Ver empleados inactivos' }}
        </a>
    </div>
    
    {{-- LISTA DE EMPLEADOS --}}
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-2xl md:rounded-[2rem] p-4 sm:p-6 md:p-8 lg:p-10 w-full flex-1 flex flex-col shadow-sm">
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="text-lg md:text-xl font-bold text-[var(--text-color)] tracking-tight">Lista de Empleados</h2>
            <input type="text" id="buscadorEmpleados" placeholder="Buscar empleado..." class="w-full sm:w-64 h-11 bg-black/5 border border-transparent rounded-xl pl-10 pr-4 text-xs font-medium focus:border-[#3B82F6] outline-none">
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[var(--border-color)]">
                        <th class="pb-5 px-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.3em]">Nombre</th>
                        <th class="pb-5 px-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.3em]">PIN</th>
                        <th class="pb-5 px-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.3em]">Rol</th>
                        <th class="pb-5 px-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.3em]">Permisos</th>
                        <th class="pb-5 px-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.3em] text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaEmpleados">
                    @forelse($empleados ?? [] as $empleado)
                    <tr class="fila-empleado group hover:bg-black/[0.03] border-b border-[var(--border-color)] {{ !$empleado->esta_activo ? 'opacity-50 grayscale' : '' }}">
                        <td class="py-4 px-4 font-bold text-sm text-[var(--text-color)]">{{ $empleado->nombre }}</td>
                        <td class="py-4 px-4 tracking-wider">{{ $empleado->codigo_empleado }}</td>
                        <td class="py-4 px-4">{{ $empleado->rol?->nombre ?? 'Sin rol' }}</td>
                        <td class="py-4 px-4"><a href="{{ route('admin.empleados.permisos', $empleado->id) }}" class="text-[9px] font-black uppercase text-[#3B82F6] hover:underline">Configurar</a></td>
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-3.5">
                                @if($empleado->esta_activo)
                                    <button type="button" class="btn-abrir-editar text-blue-500 hover:text-blue-700"
                                        data-id="{{ $empleado->id }}" 
                                        data-nombre="{{ $empleado->nombre }}" 
                                        data-codigo="{{ $empleado->codigo_empleado }}" 
                                        data-rol-id="{{ $empleado->rol_id }}" 
                                        data-rol-nombre="{{ $empleado->rol?->nombre ?? 'Seleccionar...' }}"
                                        data-acceso="{{ $empleado->puede_acceder_pos ? 1 : 0 }}">
                                        <i class="fas fa-edit text-base"></i>
                                    </button>
                                    <form action="{{ route('admin.empleados.destroy', $empleado->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700"><i class="fas fa-user-slash"></i></button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.empleados.reactivar', $empleado->id) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-user-check"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-10 text-center text-zinc-500">No hay empleados registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.empleados.modal-editar')
@include('admin.empleados.modal-crear')
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.body.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-abrir-editar');
            if (btn) {
                if (typeof window.prepararModalEditar === 'function') {
                    window.prepararModalEditar(
                        btn.dataset.id, 
                        btn.dataset.nombre, 
                        btn.dataset.codigo, 
                        btn.dataset.rolId, 
                        btn.dataset.rolNombre, 
                        btn.dataset.acceso
                    );
                }
            }
        });
    });
</script>
@endpush