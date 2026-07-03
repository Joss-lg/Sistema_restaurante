@extends('layouts.admin')

@section('title', 'Empleados | Ollintem Pro')
@section('header-title', 'Gestión de Personal')
@section('header-subtitle', 'Administra roles y permisos del equipo')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#0B0C10] p-4 sm:p-6 md:p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-6 md:space-y-8 flex-1 flex flex-col transition-colors duration-300">
    
    {{-- CABECERA DE LA SECCIÓN --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 relative z-10">
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tight">Empleados</h1>
        </div>

        @if(auth()->user()->tienePermiso('empleados.agregar'))
            <div class="relative group w-full sm:w-auto">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl blur opacity-30 group-hover:opacity-60 transition duration-500 pointer-events-none"></div>
                <button type="button" onclick="abrirModalCrear()" class="relative flex items-center justify-center gap-2.5 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3.5 rounded-xl text-sm font-bold transition-all duration-300 outline-none w-full sm:w-auto shadow-md hover:shadow-blue-500/20 hover:-translate-y-0.5 active:translate-y-0">
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
            $rolStr = strtolower($emp->rol?->nombre ?? '');
            
            if(str_contains($rolStr, 'admin')) $totalAdmin++;
            elseif(str_contains($rolStr, 'capitan')) $totalCapitan++;
            elseif(str_contains($rolStr, 'mesero')) $totalMesero++;
            elseif(str_contains($rolStr, 'cocinero')) $totalCocinero++;
            elseif(str_contains($rolStr, 'cajero')) $totalCajero++;
        }

        $tarjetasStats = [
            ['titulo' => 'Administradores', 'valor' => $totalAdmin, 'bgIcono' => 'bg-rose-50 text-rose-600 border border-rose-100 dark:border-transparent dark:bg-rose-500/10 dark:text-rose-400', 'bgGlow' => 'bg-rose-500/[0.03] dark:bg-rose-500/5', 'icono' => 'user-shield'],
            ['titulo' => 'Capitanes', 'valor' => $totalCapitan, 'bgIcono' => 'bg-blue-50 text-blue-600 border border-blue-100 dark:border-transparent dark:bg-blue-500/10 dark:text-blue-400', 'bgGlow' => 'bg-blue-500/[0.03] dark:bg-blue-500/5', 'icono' => 'clipboard-list'],
            ['titulo' => 'Meseros', 'valor' => $totalMesero, 'bgIcono' => 'bg-emerald-50 text-emerald-600 border border-emerald-100 dark:border-transparent dark:bg-emerald-500/10 dark:text-emerald-400', 'bgGlow' => 'bg-emerald-500/[0.03] dark:bg-emerald-500/5', 'icono' => 'concierge-bell'],
            ['titulo' => 'Cocineros', 'valor' => $totalCocinero, 'bgIcono' => 'bg-orange-50 text-orange-600 border border-orange-100 dark:border-transparent dark:bg-orange-500/10 dark:text-orange-400', 'bgGlow' => 'bg-orange-500/[0.03] dark:bg-orange-500/5', 'icono' => 'fire'],
            ['titulo' => 'Cajeros', 'valor' => $totalCajero, 'bgIcono' => 'bg-purple-50 text-purple-600 border border-purple-100 dark:border-transparent dark:bg-purple-500/10 dark:text-purple-400', 'bgGlow' => 'bg-purple-500/[0.03] dark:bg-purple-500/5', 'icono' => 'cash-register'],
        ];
    @endphp

    {{-- GRID DE ESTADÍSTICAS --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-5 relative z-10">
        @foreach($tarjetasStats as $stat)
        <div class="bg-white dark:bg-[#121318] border border-gray-200/70 dark:border-white/5 rounded-2xl md:rounded-[1.5rem] p-4 md:p-6 flex flex-col justify-between h-28 md:h-36 relative group overflow-hidden shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex justify-between items-start w-full relative z-10">
                <h3 class="text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest truncate pr-2">{{ $stat['titulo'] }}</h3>
                <div class="w-8 h-8 md:w-10 md:h-10 rounded-xl {{ $stat['bgIcono'] }} flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-{{ $stat['icono'] }} text-xs md:text-base"></i>
                </div>
            </div>
            <p class="text-3xl md:text-5xl font-black text-gray-900 dark:text-white relative z-10 mt-2 md:mt-0">{{ $stat['valor'] }}</p>
            <div class="absolute -bottom-4 -right-4 w-20 h-20 md:w-24 md:h-24 {{ $stat['bgGlow'] }} blur-2xl rounded-full pointer-events-none"></div>
        </div>
        @endforeach
    </div>

    {{-- LISTA DE EMPLEADOS --}}
    <div class="bg-white dark:bg-[#121318] border border-gray-200/70 dark:border-white/5 rounded-2xl md:rounded-3xl p-5 md:p-8 w-full flex-1 flex flex-col shadow-sm dark:shadow-xl relative z-20">
        
        {{-- Cabecera de Tabla y Buscador --}}
        <div class="mb-6 md:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 relative z-30">
            <div>
                <h2 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Lista de Empleados</h2>
                <p class="text-[10px] md:text-xs font-medium text-gray-400 dark:text-gray-500 mt-1">{{ count($empleados ?? []) }} registrados en el sistema</p>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-72 group">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm group-focus-within:text-blue-500 transition-colors"></i>
                    <input type="text" id="buscadorEmpleados" placeholder="Buscar empleado..." 
                        class="w-full h-11 bg-gray-50 dark:bg-black/40 border border-gray-200 dark:border-white/5 rounded-full pl-11 pr-4 text-xs font-semibold text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 focus:bg-white dark:focus:bg-black/60 outline-none transition-all">
                </div>
                
                <a href="{{ request()->has('ver_inactivos') ? route('admin.empleados.index') : route('admin.empleados.index', ['ver_inactivos' => 1]) }}" 
                    class="w-11 h-11 flex-shrink-0 flex items-center justify-center rounded-full border border-gray-200 dark:border-white/5 bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors {{ request()->has('ver_inactivos') ? 'text-rose-500' : 'text-gray-400' }}">
                    <i class="fas fa-{{ request()->has('ver_inactivos') ? 'eye-slash' : 'eye' }} text-sm"></i> 
                </a>
            </div>
        </div>

        {{-- TABLA COHESIVA Y BIEN ALINEADA --}}
        <div class="w-full overflow-x-auto relative z-20">
            <table class="w-full min-w-[800px] text-left border-collapse table-fixed">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-white/5">
                        <th class="w-[30%] pb-4 px-4 text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest text-left">Nombre</th>
                        <th class="w-[15%] pb-4 px-4 text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest text-center">PIN</th>
                        <th class="w-[20%] pb-4 px-4 text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest text-center">Rol</th>
                        <th class="w-[20%] pb-4 px-4 text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest text-center">Permisos</th>
                        <th class="w-[15%] pb-4 px-4 text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaEmpleados" class="divide-y divide-gray-100 dark:divide-white/[0.03]">
                    @forelse($empleados ?? [] as $empleado)
                    
                    @php
                        $rolStr = strtolower($empleado->rol?->nombre ?? '');
                        $colorClass = 'bg-gray-100 text-gray-700 dark:bg-zinc-800 dark:text-zinc-300'; 
                        if (str_contains($rolStr, 'admin')) $colorClass = 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'; 
                        elseif (str_contains($rolStr, 'cajero')) $colorClass = 'bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400'; 
                        elseif (str_contains($rolStr, 'mesero')) $colorClass = 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'; 
                        elseif (str_contains($rolStr, 'capitan')) $colorClass = 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400'; 
                        elseif (str_contains($rolStr, 'cocinero')) $colorClass = 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400';
                    @endphp

                    <tr class="fila-empleado group hover:bg-gray-50/70 dark:hover:bg-white/[0.01] transition-all duration-200 {{ !$empleado->esta_activo ? 'opacity-40 grayscale' : '' }}">
                        
                        {{-- COLUMNA NOMBRE --}}
                        <td class="py-5 px-4 align-middle">
                            <div class="flex items-center gap-3 md:gap-4">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200/60 dark:from-[#1e2028] dark:to-[#15171e] dark:border-white/10 flex items-center justify-center text-blue-600 dark:text-blue-400 font-black text-sm flex-shrink-0">
                                    {{ strtoupper(substr($empleado->nombre, 0, 1)) }}
                                </div>
                                <div class="flex flex-col min-w-0 truncate">
                                    <span class="font-bold text-sm text-gray-900 dark:text-gray-100 truncate">{{ $empleado->nombre }}</span>
                                    <span class="text-[10px] font-bold text-gray-400 dark:text-gray-600 mt-0.5">ID: EMP-{{ str_pad($empleado->id, 3, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- COLUMNA PIN --}}
                        <td class="py-5 px-4 align-middle font-black text-xs text-gray-600 dark:text-gray-400 tracking-[0.2em] text-center">
                            {{ $empleado->codigo_empleado ?? '----' }}
                        </td>
                        
                        {{-- COLUMNA ROL --}}
                        <td class="py-5 px-4 align-middle text-center">
                            <span class="inline-block px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-widest {{ $colorClass }}">
                                {{ $empleado->rol?->nombre ?? 'Sin rol' }}
                            </span>
                        </td>
                        
                        {{-- COLUMNA PERMISOS --}}
                        <td class="py-5 px-4 align-middle text-center">
                            <a href="{{ route('admin.empleados.permisos', $empleado->id) }}" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-gray-100 border border-gray-200 text-[9px] font-black uppercase text-gray-600 dark:bg-black/20 dark:border-white/5 dark:text-gray-400 dark:hover:text-blue-400 transition-all">
                                <i class="fas fa-shield-alt text-blue-500 text-[10px]"></i> Configurar
                            </a>
                        </td>
                        
                        {{-- COLUMNA ACCIONES --}}
                        <td class="py-5 px-4 align-middle text-center">
                            <div class="flex items-center justify-center gap-2 md:opacity-40 group-hover:opacity-100 transition-opacity duration-200 relative z-30">
                                @if($empleado->esta_activo)
                                    <button type="button" 
                                        onclick="window.ejecutarEditar(this)"
                                        class="cursor-pointer relative z-50 w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-blue-600 dark:hover:text-white bg-gray-50 hover:bg-blue-50 border border-gray-200/50 dark:bg-zinc-800 dark:border-white/5 dark:hover:bg-blue-600 transition-all flex-shrink-0"
                                        data-id="{{ $empleado->id }}" 
                                        data-nombre="{{ $empleado->nombre }}" 
                                        data-codigo="{{ $empleado->codigo_empleado }}" 
                                        data-rol-id="{{ $empleado->rol_id }}" 
                                        data-rol-nombre="{{ $empleado->rol?->nombre ?? 'Seleccionar...' }}"
                                        data-acceso="{{ $empleado->puede_acceder_pos ? 1 : 0 }}">
                                        <i class="fas fa-edit text-xs pointer-events-none"></i>
                                    </button>
                                    <form action="{{ route('admin.empleados.destroy', $empleado->id) }}" method="POST" class="inline-flex items-center justify-center m-0 p-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-rose-600 bg-gray-50 hover:bg-rose-50 border border-gray-200/50 dark:bg-zinc-800 dark:border-white/5 dark:hover:bg-rose-600 transition-all flex-shrink-0">
                                            <i class="fas fa-trash text-xs pointer-events-none"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.empleados.reactivar', $empleado->id) }}" method="POST" class="inline-flex items-center justify-center m-0 p-0">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-emerald-600 bg-gray-50 hover:bg-emerald-50 border border-gray-200/50 dark:bg-zinc-800 dark:border-white/5 dark:hover:bg-emerald-600 transition-all flex-shrink-0">
                                            <i class="fas fa-user-check text-xs pointer-events-none"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-users-slash text-3xl text-gray-300 mb-4"></i>
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

{{-- MODALES INCLUIDOS --}}
@include('admin.empleados.modal-editar')
@include('admin.empleados.modal-crear')

{{-- SCRIPTS MOVIDOS DENTRO DE LA SECCIÓN PARA ASEGURAR QUE SE EJECUTEN --}}
<script>
    // Script para preparar y abrir el modal de editar
    window.ejecutarEditar = function(btn) {
        if (typeof window.prepararModalEditar === 'function') {
            window.prepararModalEditar(
                btn.getAttribute('data-id'), 
                btn.getAttribute('data-nombre'), 
                btn.getAttribute('data-codigo'), 
                btn.getAttribute('data-rol-id'), 
                btn.getAttribute('data-rol-nombre'), 
                btn.getAttribute('data-acceso')
            );
        } else {
            const modal = document.getElementById('modalEditar');
            if(modal) {
                document.getElementById('edit_empleado_id').value = btn.getAttribute('data-id');
                document.getElementById('edit_nombre').value = btn.getAttribute('data-nombre');
                document.getElementById('edit_codigo_empleado').value = btn.getAttribute('data-codigo');
                document.getElementById('edit_rol_id').value = btn.getAttribute('data-rol-id');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } else {
                alert("Error: El modal editar no se encuentra cargado en el HTML.");
            }
        }
    };

    // Script para la búsqueda en tiempo real
    document.addEventListener('DOMContentLoaded', () => {
        const buscador = document.getElementById('buscadorEmpleados');
        // Aquí tomamos todas las filas que tienen la clase 'fila-empleado'
        const filasEmpleados = document.querySelectorAll('.fila-empleado');

        if (buscador) {
            buscador.addEventListener('input', function(e) {
                const terminoBusqueda = e.target.value.toLowerCase().trim();
                
                filasEmpleados.forEach(fila => {
                    const textoFila = fila.textContent.toLowerCase();
                    // Buscamos si el texto de la fila contiene lo que se escribió
                    if (textoFila.includes(terminoBusqueda)) {
                        // Restaura el display original
                        fila.style.display = '';
                    } else {
                        // Oculta la fila por completo de la tabla
                        fila.style.display = 'none';
                    }
                });
            });
        }
    });
</script>

@endsection