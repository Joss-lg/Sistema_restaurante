@extends('layouts.admin')

@section('title', 'Roles y Puestos | Ollintem Pro')
@section('header-title', 'Configuración del Sistema')
@section('header-subtitle', 'Administra el catálogo de puestos disponibles')

@section('content')

<div class="px-3 sm:px-6 lg:px-8 py-5 sm:py-8 w-full max-w-7xl mx-auto space-y-5 sm:space-y-8 relative z-10">
    
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-6">
        <div class="w-full sm:w-auto">
            <h1 class="text-xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Catálogo de Puestos
            </h1>
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-1.5">
                Administra los niveles de acceso para el personal del restaurante
            </p>
        </div>
        
        @if(auth()->user()->tienePermiso('roles.agregar'))
            <button type="button" 
                    onclick="abrirModalCrear()" 
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 sm:py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-[0_4px_12px_rgba(37,99,235,0.25)] hover:shadow-[0_6px_16px_rgba(37,99,235,0.35)] hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-[#15151a]">
                <i class="fas fa-plus text-xs"></i> 
                Nuevo Puesto
            </button>
        @endif
    </div>

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="bg-white dark:bg-[#15151a] border border-slate-100 dark:border-slate-800/60 rounded-2xl sm:rounded-[2rem] p-3.5 sm:p-8 shadow-xl dark:shadow-none transition-all duration-300 relative z-20">
        <h3 class="text-sm sm:text-lg font-black text-slate-900 dark:text-white tracking-tight mb-3.5 sm:mb-6 px-1 sm:px-0">Puestos Activos</h3>

        {{-- ===================== VISTA MÓVIL: TARJETAS (solo < sm) ===================== --}}
        <div class="flex flex-col gap-3 sm:hidden">
            @forelse($roles as $rol)
            <div class="border border-slate-100 dark:border-slate-800/60 rounded-2xl p-3.5 bg-slate-50/50 dark:bg-white/[0.02]">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800/80 dark:text-slate-400 shadow-sm shrink-0">
                            <i class="fas fa-user-shield text-sm"></i>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-bold text-sm text-slate-900 dark:text-white truncate">{{ $rol->nombre }}</span>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-0.5">{{ $rol->usuarios_count }} empleado{{ $rol->usuarios_count == 1 ? '' : 's' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if(auth()->user()->tienePermiso('roles.editar'))
                            <button onclick="abrirModalEditar(this)" data-id="{{ $rol->id }}" data-nombre="{{ $rol->nombre }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 active:scale-95 transition-all outline-none"><i class="fas fa-pen text-xs"></i></button>
                        @endif
                        @if(auth()->user()->tienePermiso('roles.eliminar'))
                            <button onclick="abrirModalEliminar(this)" data-id="{{ $rol->id }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 active:scale-95 transition-all outline-none"><i class="fas fa-trash-alt text-xs"></i></button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <p class="py-10 text-center text-sm font-medium text-slate-400">No hay roles registrados.</p>
            @endforelse
        </div>

        {{-- ===================== VISTA ESCRITORIO: TABLA (solo sm+) ===================== --}}
        <div class="hidden sm:block w-full overflow-x-auto pb-2">
            <table class="w-full min-w-[500px] text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800/60">
                        <th class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] pb-3 sm:pb-4 pl-2">Puesto</th>
                        <th class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] pb-3 sm:pb-4 text-center">Empleados</th>
                        <th class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] pb-3 sm:pb-4 text-right pr-2 sm:pr-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40">
                    @forelse($roles as $rol)
                    <tr class="group hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors duration-200">
                        <td class="py-4 sm:py-5 pl-2">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800/80 dark:text-slate-400 shadow-sm shrink-0">
                                    <i class="fas fa-user-shield text-xs sm:text-sm"></i>
                                </div>
                                <span class="font-bold text-sm sm:text-base text-slate-900 dark:text-white">{{ $rol->nombre }}</span>
                            </div>
                        </td>
                        <td class="py-4 sm:py-5 text-center">
                            <span class="inline-flex items-center justify-center font-black text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-800/60 px-3 py-1 rounded-lg text-xs sm:text-sm">{{ $rol->usuarios_count }}</span>
                        </td>
                        <td class="py-4 sm:py-5 text-right pr-2 sm:pr-4">
                            <div class="flex items-center justify-end gap-2">
                                @if(auth()->user()->tienePermiso('roles.editar'))
                                    <button onclick="abrirModalEditar(this)" data-id="{{ $rol->id }}" data-nombre="{{ $rol->nombre }}" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 transition-all outline-none"><i class="fas fa-pen text-xs"></i></button>
                                @endif
                                @if(auth()->user()->tienePermiso('roles.eliminar'))
                                    <button onclick="abrirModalEliminar(this)" data-id="{{ $rol->id }}" class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 transition-all outline-none"><i class="fas fa-trash-alt text-xs"></i></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="py-12 sm:py-16 text-center text-sm font-medium text-slate-400">No hay roles registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODALES --}}
@if(auth()->user()->tienePermiso('roles.agregar')) @include('admin.roles.modal-crear') @endif
@if(auth()->user()->tienePermiso('roles.editar')) @include('admin.roles.modal-editar') @endif
@if(auth()->user()->tienePermiso('roles.eliminar')) @include('admin.roles.modal-eliminar') @endif
@endsection

@push('scripts')
<script>
    // PASO 2: Liberar modales al <body> para que se sobrepongan al menú en móviles
    document.addEventListener('DOMContentLoaded', () => {
        // Asegúrate de que estos IDs coincidan exactamente con los que tienes en tus archivos modal-*.blade.php
        const modales = ['modalCrearRol', 'modalEditarRol', 'modalEliminarRol']; 
        
        modales.forEach(id => {
            const modalElement = document.getElementById(id);
            if (modalElement) {
                document.body.appendChild(modalElement);
            }
        });
    });

    // Lógica para cerrar modales al hacer clic fuera
    document.addEventListener('click', (e) => {
        const modalEditar = document.getElementById('modalEditarRol');
        const modalEliminar = document.getElementById('modalEliminarRol');
        
        if (modalEditar && e.target === modalEditar) cerrarModalEditar();
        if (modalEliminar && e.target === modalEliminar) cerrarModalEliminar();
    });
</script>
@endpush