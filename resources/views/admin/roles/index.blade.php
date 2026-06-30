@extends('layouts.admin')

@section('title', 'Roles y Puestos | Ollintem Pro')
@section('header-title', 'Configuración del Sistema')
@section('header-subtitle', 'Administra el catálogo de puestos disponibles')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-6 sm:space-y-8 relative z-10">
    
    {{-- HEADER PREMIUM --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-4 border-b border-gray-200 dark:border-gray-800/60">
        <div>
            <h1 class="text-3xl sm:text-4xl font-black !text-black dark:!text-white tracking-tight">Catálogo de Roles y Puestos</h1>
            <p class="text-sm !text-gray-500 dark:!text-gray-400 mt-1 font-medium">Administra y asigna los niveles de acceso para el personal del restaurante</p>
        </div>
        
        @if(auth()->user()->tienePermiso('roles.agregar'))
            <button type="button" 
                    onclick="abrirModalCrear()" 
                    class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full text-xs font-black uppercase tracking-widest !bg-blue-600 dark:!bg-blue-500 !text-white hover:scale-105 transition-all outline-none shadow-[0_8px_20px_rgba(37,99,235,0.2)] dark:shadow-none">
                <i class="fas fa-plus text-sm"></i>
                Nuevo Puesto
            </button>
        @endif
    </div>

    @if(session('success'))
        <div class="fixed right-6 top-6 z-50 max-w-lg w-[min(95vw,420px)] rounded-2xl border border-emerald-200/60 !bg-white dark:!bg-[#14151a] px-5 py-4 shadow-[0_8px_30px_rgb(0,0,0,0.12)] dark:shadow-2xl text-sm font-semibold flex items-start gap-3">
            <div class="bg-emerald-50 dark:bg-emerald-500/15 !text-emerald-500 dark:!text-emerald-400 rounded-full w-10 h-10 flex items-center justify-center">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <strong class="block text-sm !text-black dark:!text-white">Éxito</strong>
                <p class="mt-1 text-[0.92rem] !text-gray-600 dark:!text-gray-400">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- TABLA EXPANDIDA (Diseño Limpio y Forzado a Claro/Oscuro Perfecto) --}}
    <div class="!bg-white dark:!bg-[#14151a] border border-gray-200 dark:border-gray-800 rounded-[2rem] p-6 lg:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-none w-full flex flex-col">
        <h2 class="text-xl font-black !text-black dark:!text-white tracking-tight mb-6">Puestos Activos en el Sistema</h2>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="pb-4 px-4 text-[10px] font-black !text-gray-400 dark:!text-gray-500 uppercase tracking-[0.2em]">Puesto</th>
                        <th class="pb-4 px-4 text-[10px] font-black !text-gray-400 dark:!text-gray-500 uppercase tracking-[0.2em]">Slug</th>
                        <th class="pb-4 px-4 text-[10px] font-black !text-gray-400 dark:!text-gray-500 uppercase tracking-[0.2em]">Permiso POS</th>
                        <th class="pb-4 px-4 text-[10px] font-black !text-gray-400 dark:!text-gray-500 uppercase tracking-[0.2em] text-center">Empleados</th>
                        <th class="pb-4 px-4 text-[10px] font-black !text-gray-400 dark:!text-gray-500 uppercase tracking-[0.2em] text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $rol)
                    <tr class="group hover:!bg-gray-50 dark:hover:!bg-white/5 transition-colors border-b border-gray-100 dark:border-gray-800/60 last:border-0">
                        <td class="py-4 px-4">
                            <div class="flex flex-col">
                                <span class="font-black text-sm !text-black dark:!text-white">{{ $rol->nombre }}</span>
                                @if($rol->descripcion)
                                    <span class="text-xs !text-gray-500 dark:!text-gray-400 mt-0.5 max-w-xs truncate">{{ $rol->descripcion }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-4 px-4">
                            <span class="font-mono text-[11px] font-bold px-2.5 py-1 rounded-md !bg-blue-50 dark:!bg-blue-500/10 !text-blue-600 dark:!text-blue-400">
                                {{ $rol->slug }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            @if($rol->puede_acceder_pos)
                                <span class="px-2.5 py-1 rounded-full !bg-emerald-50 dark:!bg-emerald-500/10 !text-emerald-600 dark:!text-emerald-400 text-[10px] font-bold uppercase tracking-wider flex w-max items-center gap-1.5">
                                    <i class="fas fa-check-circle"></i> Permitido
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full !bg-rose-50 dark:!bg-rose-500/10 !text-rose-600 dark:!text-rose-400 text-[10px] font-bold uppercase tracking-wider flex w-max items-center gap-1.5">
                                    <i class="fas fa-lock"></i> Restringido
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="!bg-gray-100 dark:!bg-gray-800 !text-gray-800 dark:!text-gray-200 px-3 py-1 rounded-full text-xs font-black">
                                {{ $rol->usuarios_count }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <div class="flex justify-center gap-2">
                                @if(auth()->user()->tienePermiso('roles.editar'))
                                    <button type="button" 
                                            onclick="abrirModalEditar({{ $rol->id }}, '{{ addslashes($rol->nombre) }}', '{{ addslashes($rol->descripcion ?? '') }}', {{ $rol->puede_acceder_pos ? 'true' : 'false' }})"
                                            class="w-8 h-8 rounded-full flex items-center justify-center !bg-blue-50 dark:!bg-blue-500/10 !text-blue-600 dark:!text-blue-400 hover:scale-110 transition-transform outline-none">
                                        <i class="fas fa-pencil-alt text-xs"></i> 
                                    </button>
                                @endif

                                @if(auth()->user()->tienePermiso('roles.eliminar'))
                                    <button type="button"
                                            onclick="abrirModalEliminar({{ $rol->id }}, '{{ addslashes($rol->nombre) }}')"
                                            class="w-8 h-8 rounded-full flex items-center justify-center !bg-rose-50 dark:!bg-rose-500/10 !text-rose-600 dark:!text-rose-400 hover:scale-110 transition-transform outline-none">
                                        <i class="fas fa-trash-alt text-xs"></i> 
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="h-16 w-16 !bg-gray-50 dark:!bg-gray-800/50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-user-shield text-2xl !text-gray-400 dark:!text-gray-500"></i>
                                </div>
                                <p class="text-sm font-bold !text-gray-900 dark:!text-white">No hay roles registrados</p>
                                <p class="text-xs !text-gray-500 dark:!text-gray-400 mt-1">Crea un nuevo puesto para comenzar a asignar permisos.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- CONTROL DE MODALES --}}
@if(auth()->user()->tienePermiso('roles.agregar')) @include('admin.roles.modal-crear') @endif
@if(auth()->user()->tienePermiso('roles.editar')) @include('admin.roles.modal-editar') @endif
@if(auth()->user()->tienePermiso('roles.eliminar')) @include('admin.roles.modal-eliminar') @endif
@endsection

@push('scripts')
<script>
    // Usamos 'window' para hacer las funciones accesibles globalmente
    window.abrirModalEditar = function(id, nombre, descripcion, puedePOS) {
        console.log("Editando: " + nombre);
        const form = document.getElementById('formEditarRol');
        if(form) form.action = '/roles/' + id; 
        
        document.getElementById('editNombre').value = nombre;
        document.getElementById('editDescripcion').value = descripcion;
        document.getElementById('editPuedePOS').value = puedePOS ? '1' : '0';
        document.getElementById('modalEditarRol').classList.remove('hidden');
    }

    window.abrirModalEliminar = function(id, nombre) {
        console.log("Eliminando: " + nombre);
        const form = document.getElementById('formEliminarRol');
        if(form) form.action = '/roles/' + id;
        
        const spanNombre = document.getElementById('nombreRolEliminar');
        if(spanNombre) spanNombre.innerText = nombre;
        
        document.getElementById('modalEliminarRol').classList.remove('hidden');
    }
</script>
@endpush