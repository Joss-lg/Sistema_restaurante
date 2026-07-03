@extends('layouts.admin')

@section('title', 'Empleados | Ollintem Pro')
@section('header-title', 'Gestión de Personal')
@section('header-subtitle', 'Administra roles y permisos del equipo')

@section('content')
<div class="p-4 sm:p-6 md:p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-6 md:space-y-8 flex-1 flex flex-col">
    
    {{-- CABECERA DE LA SECCIÓN --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tight">Empleados</h1>
        </div>

        @if(auth()->user()->tienePermiso('empleados.agregar'))
            <div class="relative group z-10 w-full sm:w-auto">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl blur opacity-30 group-hover:opacity-50 transition duration-500 pointer-events-none dark:opacity-40 dark:group-hover:opacity-70"></div>
                <button type="button" onclick="openModal()" class="relative flex items-center justify-center gap-2.5 bg-blue-500 hover:bg-blue-600 dark:bg-[#3B82F6] dark:hover:bg-[#2563EB] border border-transparent dark:border-white/10 text-white px-6 py-3.5 rounded-xl text-sm font-bold transition-all duration-300 outline-none w-full sm:w-auto shadow-[0_4px_14px_0_rgba(59,130,246,0.39)]">
                    <i class="fas fa-plus"></i> 
                    <span>Agregar Empleado</span>
                </button>
            </div>
        @endif
    </div>

    {{-- LÓGICA DE ESTADÍSTICAS --}}
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

        $tarjetasStats = [
            ['titulo' => 'Administradores', 'valor' => $totalAdmin, 'color' => 'rose', 'hex' => '#f43f5e', 'icono' => 'user-shield', 'bg_light' => 'bg-rose-50'],
            ['titulo' => 'Capitanes', 'valor' => $totalCapitan, 'color' => 'blue', 'hex' => '#3b82f6', 'icono' => 'clipboard-list', 'bg_light' => 'bg-blue-50'],
            ['titulo' => 'Meseros', 'valor' => $totalMesero, 'color' => 'emerald', 'hex' => '#10b981', 'icono' => 'concierge-bell', 'bg_light' => 'bg-emerald-50'],
            ['titulo' => 'Cocineros', 'valor' => $totalCocinero, 'color' => 'orange', 'hex' => '#f97316', 'icono' => 'fire', 'bg_light' => 'bg-orange-50'],
            ['titulo' => 'Cajeros', 'valor' => $totalCajero, 'color' => 'purple', 'hex' => '#a855f7', 'icono' => 'cash-register', 'bg_light' => 'bg-purple-50'],
        ];
    @endphp

    {{-- GRID DE ESTADÍSTICAS PREMIUM --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 md:gap-5">
        @foreach($tarjetasStats as $stat)
        <div class="bg-white dark:bg-[#121318] rounded-[2rem] p-5 md:p-7 flex flex-col justify-between h-36 md:h-40 relative group overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:border dark:border-white/5 dark:shadow-lg transition-transform hover:-translate-y-1 duration-300">
            <div class="flex justify-between items-start w-full relative z-10">
                <h3 class="text-[10px] md:text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">{{ $stat['titulo'] }}</h3>
                <div class="w-10 h-10 md:w-11 md:h-11 rounded-[0.8rem] {{ $stat['bg_light'] }} dark:bg-[{{ $stat['hex'] }}]/10 text-[{{ $stat['hex'] }}] flex items-center justify-center shadow-sm">
                    <i class="fas fa-{{ $stat['icono'] }} text-base"></i>
                </div>
            </div>
            <p class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white relative z-10 tracking-tighter">{{ $stat['valor'] }}</p>
            
            {{-- Resplandor de fondo sutil solo en oscuro --}}
            <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-[{{ $stat['hex'] }}]/10 blur-2xl rounded-full hidden dark:block"></div>
        </div>
        @endforeach
    </div>

    {{-- LISTA DE EMPLEADOS --}}
    <div class="bg-white dark:bg-[#121318] rounded-[2rem] p-6 md:p-8 w-full flex-1 flex flex-col shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:border dark:border-white/5 dark:shadow-xl">
        
        {{-- Cabecera de Tabla y Buscador --}}
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Lista de Empleados</h2>
                <p class="text-xs font-semibold text-gray-400 mt-1">{{ count($empleados ?? []) }} registrados en el sistema</p>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-80 group">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                    <input type="text" id="buscadorEmpleados" placeholder="Buscar empleado..." class="w-full h-12 bg-gray-50/50 hover:bg-gray-100/50 dark:bg-black/40 border-none rounded-[1.25rem] pl-11 pr-4 text-sm font-semibold text-gray-900 dark:text-white placeholder-gray-400 focus:bg-gray-100 dark:focus:bg-black/60 focus:ring-4 focus:ring-blue-500/10 dark:focus:ring-blue-500/20 outline-none transition-all">
                </div>
                
                <a href="{{ request()->has('ver_inactivos') ? route('admin.empleados.index') : route('admin.empleados.index', ['ver_inactivos' => 1]) }}" 
                   class="w-12 h-12 flex items-center justify-center rounded-[1.25rem] bg-gray-50/50 hover:bg-gray-100 dark:bg-white/5 dark:hover:bg-white/10 transition-all {{ request()->has('ver_inactivos') ? 'text-rose-500 bg-rose-50' : 'text-gray-400' }}">
                    <i class="fas fa-{{ request()->has('ver_inactivos') ? 'eye-slash' : 'eye' }}"></i> 
                </a>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-50 dark:border-white/5">
                        <th class="pb-4 px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nombre</th>
                        <th class="pb-4 px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">PIN</th>
                        <th class="pb-4 px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Rol</th>
                        <th class="pb-4 px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Permisos</th>
                        <th class="pb-4 px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaEmpleados">
                    @forelse($empleados ?? [] as $empleado)
                    
                    {{-- LÓGICA DE COLORES DE ROL --}}
                    @php
                        $rolStr = strtolower($empleado->rol?->nombre ?? '');
                        $colorClass = 'bg-gray-500 text-white'; 
                        if (str_contains($rolStr, 'admin')) $colorClass = 'bg-[#f43f5e] text-white dark:shadow-[0_0_12px_rgba(244,63,94,0.4)]'; 
                        elseif (str_contains($rolStr, 'cajero')) $colorClass = 'bg-[#a855f7] text-white dark:shadow-[0_0_12px_rgba(168,85,247,0.4)]'; 
                        elseif (str_contains($rolStr, 'mesero')) $colorClass = 'bg-[#10b981] text-white dark:shadow-[0_0_12px_rgba(16,185,129,0.4)]'; 
                        elseif (str_contains($rolStr, 'capitan')) $colorClass = 'bg-[#3b82f6] text-white dark:shadow-[0_0_12px_rgba(59,130,246,0.4)]'; 
                        elseif (str_contains($rolStr, 'cocinero')) $colorClass = 'bg-[#f97316] text-white dark:shadow-[0_0_12px_rgba(249,115,22,0.4)]';
                    @endphp

                    <tr class="fila-empleado group hover:bg-gray-50/80 dark:hover:bg-white/[0.02] border-b border-gray-50 dark:border-white/5 transition-colors {{ !$empleado->esta_activo ? 'opacity-40 grayscale' : '' }}">
                        
                        {{-- Nombre y Avatar --}}
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-4">
                                <div class="w-11 h-11 rounded-full bg-blue-50 dark:bg-[#1e2028] dark:border dark:border-white/10 flex items-center justify-center text-blue-600 dark:text-blue-400 font-black text-sm">
                                    {{ strtoupper(substr($empleado->nombre, 0, 1)) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-sm text-gray-900 dark:text-gray-100">{{ $empleado->nombre }}</span>
                                    <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 mt-0.5 tracking-wider">ID: EMP-{{ str_pad($empleado->id, 3, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- PIN --}}
                        <td class="py-4 px-4 font-black text-xs text-gray-600 dark:text-gray-400 tracking-[0.2em]">{{ $empleado->codigo_empleado ?? '----' }}</td>
                        
                        {{-- ROL --}}
                        <td class="py-4 px-4">
                            <span class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $colorClass }}">
                                {{ $empleado->rol?->nombre ?? 'Sin rol' }}
                            </span>
                        </td>
                        
                        {{-- PERMISOS --}}
                        <td class="py-4 px-4">
                            <a href="{{ route('admin.empleados.permisos', $empleado->id) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100/80 dark:bg-black/40 text-[10px] font-bold uppercase text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-[#1e2028] transition-all">
                                <i class="fas fa-shield-alt text-blue-500"></i> Configurar
                            </a>
                        </td>
                        
                        {{-- ACCIONES --}}
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-2 md:opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                @if($empleado->esta_activo)
                                    <button type="button" class="btn-abrir-editar w-9 h-9 rounded-full flex items-center justify-center bg-gray-50 dark:bg-transparent text-gray-400 hover:text-blue-600 dark:hover:text-white hover:bg-blue-50 dark:hover:bg-blue-500/20 transition-all shadow-sm dark:shadow-none"
                                        data-id="{{ $empleado->id }}" 
                                        data-nombre="{{ $empleado->nombre }}" 
                                        data-codigo="{{ $empleado->codigo_empleado }}" 
                                        data-rol-id="{{ $empleado->rol_id }}" 
                                        data-rol-nombre="{{ $empleado->rol?->nombre ?? 'Seleccionar...' }}"
                                        data-acceso="{{ $empleado->puede_acceder_pos ? 1 : 0 }}">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <form action="{{ route('admin.empleados.destroy', $empleado->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-9 h-9 rounded-full flex items-center justify-center bg-gray-50 dark:bg-transparent text-gray-400 hover:text-rose-600 dark:hover:text-white hover:bg-rose-50 dark:hover:bg-rose-500/20 transition-all shadow-sm dark:shadow-none"><i class="fas fa-trash text-xs"></i></button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.empleados.reactivar', $empleado->id) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="w-9 h-9 rounded-full flex items-center justify-center bg-gray-50 dark:bg-transparent text-gray-400 hover:text-emerald-600 dark:hover:text-white hover:bg-emerald-50 dark:hover:bg-emerald-500/20 transition-all shadow-sm dark:shadow-none"><i class="fas fa-user-check text-xs"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-20 h-20 bg-gray-50 dark:bg-white/5 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-users-slash text-3xl text-gray-300 dark:text-gray-600"></i>
                                </div>
                                <span class="text-sm font-bold text-gray-400">No hay empleados registrados</span>
                            </div>
                        </td>
                    </tr>
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
        
        // 1. Lógica para el Buscador en tiempo real
        const buscador = document.getElementById('buscadorEmpleados');
        const filasEmpleados = document.querySelectorAll('.fila-empleado');

        if (buscador) {
            buscador.addEventListener('input', function(e) {
                const terminoBusqueda = e.target.value.toLowerCase().trim();

                filasEmpleados.forEach(fila => {
                    const contenidoFila = fila.textContent.toLowerCase();
                    if (contenidoFila.includes(terminoBusqueda)) {
                        fila.classList.remove('hidden');
                    } else {
                        fila.classList.add('hidden');
                    }
                });
            });
        }

        // 2. Lógica para abrir el Modal de Edición
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