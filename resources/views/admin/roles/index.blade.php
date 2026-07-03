@extends('layouts.admin')

@section('title', 'Roles y Puestos | Ollintem Pro')
@section('header-title', 'Configuración del Sistema')
@section('header-subtitle', 'Administra el catálogo de puestos disponibles')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-7xl mx-auto space-y-8">
    
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Catálogo de Puestos
            </h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1.5">
                Administra los niveles de acceso para el personal del restaurante
            </p>
        </div>
        
        @if(auth()->user()->tienePermiso('roles.agregar'))
            <button type="button" 
                    onclick="abrirModalCrear()" 
                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-[0_4px_12px_rgba(37,99,235,0.25)] hover:shadow-[0_6px_16px_rgba(37,99,235,0.35)] hover:-translate-y-0.5 outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-[#15151a]">
                <i class="fas fa-plus text-xs"></i> 
                Nuevo Puesto
            </button>
        @endif
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-xl shadow-sm">
            <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400 text-lg"></i>
            <div>
                <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">¡Éxito!</p>
                <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400/80">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl shadow-sm">
            <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 text-lg"></i>
            <div>
                <p class="text-sm font-bold text-red-800 dark:text-red-300">Atención</p>
                <p class="text-xs font-medium text-red-600 dark:text-red-400/80">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- CONTENEDOR DE LA TABLA (Corregido para alternar entre fondo blanco impecable y oscuro premium) --}}
    <div class="bg-white dark:bg-[#15151a] border border-slate-100 dark:border-slate-800/60 rounded-[2rem] p-8 shadow-xl dark:shadow-none transition-all duration-300 relative">
        
        <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight mb-6">
            Puestos Activos en el Sistema
        </h3>

        {{-- TABLA RESPONSIVA (3 columnas fijas: Puesto, Empleados, Acciones) --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800/60">
                        <th class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] pb-4 pl-2 w-1/2">
                            Puesto
                        </th>
                        <th class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] pb-4 text-center">
                            Empleados
                        </th>
                        <th class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] pb-4 text-right pr-4">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                    @forelse($roles as $rol)
                    <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors duration-200">
                        
                        {{-- Columna: Puesto (Únicamente el nombre) --}}
                        <td class="py-5 pl-2">
                            <div class="flex items-center gap-4">
                                {{-- Icono decorativo estilo premium --}}
                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800/80 dark:text-slate-400 shadow-sm shrink-0">
                                    @if(Str::slug($rol->nombre) == 'administrador' || Str::slug($rol->nombre) == 'admin')
                                        <i class="fas fa-crown text-sm"></i>
                                    @else
                                        <i class="fas fa-user-shield text-sm"></i>
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors text-sm sm:text-base">
                                        {{ $rol->nombre }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        
                        {{-- Columna: Empleados --}}
                        <td class="py-5 text-center">
                            <span class="inline-flex items-center justify-center font-black text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-800/60 px-3 py-1 rounded-lg text-sm min-w-[2.5rem]">
                                {{ $rol->usuarios_count }}
                            </span>
                        </td>
                        
                        {{-- Columna: Acciones --}}
                        <td class="py-5 text-right pr-4">
                            <div class="flex items-center justify-end gap-2">
                                @if(auth()->user()->tienePermiso('roles.editar'))
                                    <button type="button" 
                                            data-id="{{ $rol->id }}"
                                            data-nombre="{{ $rol->nombre }}"
                                            onclick="abrirModalEditar(this)"
                                            class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:hover:bg-blue-500/20 dark:text-blue-400 transition-all active:scale-95 shadow-sm"
                                            title="Editar puesto">
                                        <i class="fas fa-pen text-xs"></i> 
                                    </button>
                                @endif

                                @if(auth()->user()->tienePermiso('roles.eliminar'))
                                    <button type="button"
                                            data-id="{{ $rol->id }}"
                                            data-nombre="{{ $rol->nombre }}"
                                            onclick="abrirModalEliminar(this)"
                                            class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 dark:text-rose-400 transition-all active:scale-95 shadow-sm"
                                            title="Eliminar puesto">
                                        <i class="fas fa-trash-alt text-xs"></i> 
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-14 h-14 bg-slate-50 dark:bg-slate-800/50 rounded-full border border-slate-100 dark:border-slate-800 flex items-center justify-center mb-4 shadow-sm">
                                    <i class="fas fa-shield-alt text-xl text-slate-400 dark:text-slate-500"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">No hay roles registrados</p>
                                <p class="text-xs font-medium text-slate-400 dark:text-slate-500 mt-1">Crea un nuevo puesto para comenzar a asignar permisos.</p>
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
    window.abrirModalEditar = function(btn) {
        const form = document.getElementById('formEditarRol');
        if(form) form.action = '/roles/' + btn.getAttribute('data-id'); 
        
        // Solo actualizamos el nombre, que es lo único que nos queda en la tabla
        document.getElementById('editNombre').value = btn.getAttribute('data-nombre');
        
        document.getElementById('modalEditarRol').classList.remove('hidden');
    }

    window.abrirModalEliminar = function(btn) {
        const form = document.getElementById('formEliminarRol');
        if(form) form.action = '/roles/' + btn.getAttribute('data-id');
        
        const spanNombre = document.getElementById('nombreRolEliminar');
        if(spanNombre) spanNombre.innerText = btn.getAttribute('data-nombre');
        
        document.getElementById('modalEliminarRol').classList.remove('hidden');
    }

    window.cerrarModalEditar = function() { document.getElementById('modalEditarRol').classList.add('hidden'); }
    window.cerrarModalEliminar = function() { document.getElementById('modalEliminarRol').classList.add('hidden'); }

    document.addEventListener('click', (e) => {
        if (e.target.id === 'modalEditarRol') cerrarModalEditar();
        if (e.target.id === 'modalEliminarRol') cerrarModalEliminar();
    });
</script>
@endpush