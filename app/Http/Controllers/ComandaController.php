@extends('layouts.admin')

@section('title', 'Empleados | Ollintem Pro')
@section('header-title', 'Gestión de Personal')
@section('header-subtitle', 'Administra roles y permisos del equipo')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#0B0C10] p-4 sm:p-6 md:p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-6 md:space-y-8 flex-1 flex flex-col">
    
    {{-- CABECERA --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tight">Empleados</h1>
        </div>

        @if(auth()->user()->tienePermiso('empleados.agregar'))
            <div class="relative group z-10 w-full sm:w-auto">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl blur opacity-40 group-hover:opacity-70 transition duration-500 pointer-events-none"></div>
                <button type="button" onclick="abrirModalCrear()" class="relative flex items-center justify-center gap-2.5 bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white px-6 py-3.5 rounded-xl text-sm font-bold transition-all duration-300 outline-none w-full sm:w-auto shadow-lg">
                    <i class="fas fa-plus"></i> 
                    <span>Agregar Empleado</span>
                </button>
            </div>
        @endif
    </div>

    {{-- GRID DE ESTADÍSTICAS --}}
    {{-- Mantenemos tu lógica PHP de colores, pero asegurando coherencia en el diseño --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-5">
        @foreach($tarjetasStats as $stat)
        <div class="bg-white dark:bg-[#121318] border border-gray-200 dark:border-gray-800 rounded-2xl p-4 md:p-6 flex flex-col justify-between h-28 md:h-36 relative group overflow-hidden shadow-sm dark:shadow-none transition-colors">
            <div class="flex justify-between items-start w-full relative z-10">
                <h3 class="text-[9px] md:text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest truncate">{{ $stat['titulo'] }}</h3>
                <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg {{ $stat['bgIcono'] }} {{ $stat['textIcono'] }} flex items-center justify-center flex-shrink-0 transition-transform group-hover:scale-105 duration-300">
                    <i class="fas fa-{{ $stat['icono'] }} text-xs md:text-base"></i>
                </div>
            </div>
            <p class="text-3xl md:text-5xl font-black text-gray-900 dark:text-white relative z-10 mt-2">{{ $stat['valor'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- LISTA DE EMPLEADOS --}}
    <div class="bg-white dark:bg-[#121318] border border-gray-200 dark:border-gray-800 rounded-2xl p-5 md:p-8 w-full flex-1 flex flex-col shadow-sm dark:shadow-none transition-colors">
        
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white">Lista de Empleados</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ count($empleados ?? []) }} registrados</p>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <input type="text" id="buscadorEmpleados" placeholder="Buscar..." class="w-full sm:w-72 h-10 bg-gray-100 dark:bg-black/20 border border-gray-200 dark:border-gray-800 rounded-full px-4 text-xs text-gray-900 dark:text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[800px] text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="pb-4 px-4 text-[10px] font-black text-gray-400 uppercase">Nombre</th>
                        <th class="pb-4 px-4 text-[10px] font-black text-gray-400 uppercase">PIN</th>
                        <th class="pb-4 px-4 text-[10px] font-black text-gray-400 uppercase">Rol</th>
                        <th class="pb-4 px-4 text-[10px] font-black text-gray-400 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaEmpleados">
                    @forelse($empleados ?? [] as $empleado)
                    <tr class="fila-empleado hover:bg-gray-50 dark:hover:bg-white/5 border-b border-gray-100 dark:border-gray-800 transition-colors">
                        <td class="py-4 px-4 font-bold text-gray-900 dark:text-gray-100">{{ $empleado->nombre }}</td>
                        <td class="py-4 px-4 text-gray-600 dark:text-gray-400 font-mono">{{ $empleado->codigo_empleado }}</td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-1 rounded text-[9px] font-bold uppercase {{ $colorClass }}">
                                {{ $empleado->rol?->nombre ?? 'Sin rol' }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right">
                                {{-- Acciones (Botones) --}}
                                <button class="text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-10 text-center text-gray-400">No hay empleados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection