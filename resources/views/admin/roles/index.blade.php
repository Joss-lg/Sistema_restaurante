@extends('layouts.admin')

@section('title', 'Roles y Puestos | Ollintem Pro')
@section('header-title', 'Configuración del Sistema')
@section('header-subtitle', 'Administra el catálogo de puestos disponibles')

@section('content')
<div class="p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col">
    
    {{-- 🌟 HEADER MODIFICADO: Título a la izquierda y Botón Nuevo a la derecha --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-4 border-b border-[var(--border-color)]/60">
        <div>
            <h1 class="text-3xl font-black text-[var(--text-color)] tracking-tight">Catálogo de Roles y Puestos</h1>
            <p class="text-xs text-[var(--text-muted)] mt-1 font-medium">Administra y asigna los niveles de acceso para el personal del restaurante</p>
        </div>
        
        {{-- Botón condicional según el permiso de creación --}}
        @if(auth()->user()->tienePermiso('roles.agregar'))
            <button type="button" 
                    onclick="abrirModalCrear()" 
                    class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 h-12 rounded-xl text-xs font-black uppercase tracking-wider bg-[#3B82F6] text-white hover:bg-[#3B82F6]/90 transition-all outline-none shadow-lg shadow-blue-500/10 active:scale-[0.98]">
                <i class="fas fa-plus text-sm"></i>
                Nuevo Puesto
            </button>
        @endif
    </div>

    @if(session('success'))
        <div class="fixed right-6 top-6 z-50 max-w-lg w-[min(95vw,420px)] rounded-2xl border border-emerald-200/60 bg-[#0B0F19]/90 px-5 py-4 shadow-2xl backdrop-blur-xl text-white text-sm font-semibold flex items-start gap-3 modo-crema:relative modo-crema:top-0 modo-crema:right-0 modo-crema:bg-white modo-crema:border-gray-100 modo-crema:text-gray-800 modo-crema:shadow-[0_8px_30px_rgba(0,0,0,0.08)]">
            <div class="toast-icon bg-emerald-500/15 text-emerald-200 rounded-2xl w-10 h-10 grid place-items-center">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <strong class="block text-sm">Éxito</strong>
                <p class="mt-1 text-[0.92rem] text-white/85 modo-crema:text-slate-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- 🌟 TABLA EXPANDIDA: Ya no hay columnas divididas, toma todo el ancho --}}
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2rem] p-8 lg:p-10 shadow-sm w-full flex flex-col">
        <h2 class="text-lg font-bold text-[var(--text-color)] tracking-tight mb-6">Puestos Activos en el Sistema</h2>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[var(--border-color)]">
                        <th class="pb-5 px-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.3em]">Puesto</th>
                        <th class="pb-5 px-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.3em]">Slug Identificador</th>
                        <th class="pb-5 px-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.3em]">Permiso POS</th>
                        <th class="pb-5 px-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.3em] text-center">Empleados</th>
                        <th class="pb-5 px-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.3em] text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $rol)
                    <tr class="group hover:bg-black/[0.02] modo-crema:hover:bg-zinc-100/30 transition-colors border-b border-[var(--border-color)]">
                        <td class="py-4 px-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-sm text-[var(--text-color)]">{{ $rol->nombre }}</span>
                                @if($rol->descripcion)
                                    <span class="text-xs text-[var(--text-muted)] mt-0.5 max-w-xs truncate">{{ $rol->descripcion }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-4 px-4 font-mono text-xs text-blue-400">
                            <code>{{ $rol->slug }}</code>
                        </td>
                        <td class="py-4 px-4">
                            @if($rol->puede_acceder_pos)
                                <span class="px-2.5 py-1 rounded bg-emerald-500/10 text-emerald-400 modo-crema:bg-emerald-50 modo-crema:text-emerald-900 text-[10px] font-bold uppercase">Permitido</span>
                            @else
                                <span class="px-2.5 py-1 rounded bg-rose-500/10 text-rose-400 modo-crema:bg-rose-50 modo-crema:text-rose-900 text-[10px] font-bold uppercase">Restringido</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-center tabular-nums">
                            <span class="bg-black/20 text-[var(--text-color)] px-3 py-1 rounded-full text-xs font-black">
                                {{ $rol->usuarios_count }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <div class="flex justify-center gap-2">
                                @if(auth()->user()->tienePermiso('roles.editar'))
                                    <button type="button" 
                                            onclick="abrirModalEditar({{ $rol->id }}, '{{ addslashes($rol->nombre) }}', '{{ addslashes($rol->descripcion ?? '') }}', {{ $rol->puede_acceder_pos ? 'true' : 'false' }})"
                                            class="px-3 py-2 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 text-xs font-bold uppercase tracking-widest transition-all outline-none flex items-center gap-1.5">
                                        <i class="fas fa-pencil-alt"></i> 
                                    </button>
                                @endif

                                @if(auth()->user()->tienePermiso('roles.eliminar'))
                                    <button type="button"
                                            onclick="abrirModalEliminar({{ $rol->id }}, '{{ addslashes($rol->nombre) }}')"
                                            class="px-3 py-2 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 text-xs font-bold uppercase tracking-widest transition-all outline-none flex items-center gap-1.5">
                                        <i class="fas fa-trash-alt"></i> 
                                    </button>
                                @endif

                                @if(!auth()->user()->tienePermiso('roles.editar') && !auth()->user()->tienePermiso('roles.eliminar'))
                                    <span class="text-[9px] font-black uppercase text-[var(--text-muted)] opacity-50">Sin acceso</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-zinc-500 text-sm font-medium">No se han registrado roles dinámicos aún.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 🌟 CONTROL DE MODALES INCLUYENDO EL DE CREACIÓN --}}
@if(auth()->user()->tienePermiso('roles.agregar'))
    @include('admin.roles.modal-crear')
@endif

@if(auth()->user()->tienePermiso('roles.editar'))
    @include('admin.roles.modal-editar')
@endif

@if(auth()->user()->tienePermiso('roles.eliminar'))
    @include('admin.roles.modal-eliminar')
@endif

@endsection