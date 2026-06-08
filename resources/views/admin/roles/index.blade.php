@extends('layouts.admin')

@section('title', 'Roles y Puestos | Ollintem Pro')
@section('header-title', 'Configuración del Sistema')
@section('header-subtitle', 'Administra el catálogo de puestos disponibles')

@section('content')
<div class="p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-3xl font-black text-[var(--text-color)] tracking-tight">Catálogo de Roles y Puestos</h1>
            <p class="text-xs text-[var(--text-muted)] mt-1 font-medium">Da de alta nuevos puestos para el personal del restaurante</p>
        </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2rem] p-8 shadow-sm relative overflow-hidden">
            <div class="absolute top-[-10%] left-[-10%] w-32 h-32 rounded-full bg-blue-600/10 blur-[60px] pointer-events-none z-0"></div>
            
            <h2 class="text-lg font-bold text-[var(--text-color)] tracking-tight mb-6 relative z-10">Crear Puesto Nuevo</h2>
            
            <form action="{{ route('roles.store') }}" method="POST" class="space-y-6 relative z-10">
                @csrf
                
                <div>
                    <label for="nombre" class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em] mb-3 block">Nombre del Puesto</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: Guardia Nocturno" required 
                           class="w-full h-12 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-sm text-[var(--text-color)] outline-none transition-all focus:border-[#3B82F6]">
                    @error('nombre') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="descripcion" class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em] mb-3 block">Descripción / Notas</label>
                    <textarea id="descripcion" name="descripcion" placeholder="Responsabilidades o notas del puesto..." rows="3"
                              class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl p-5 text-sm text-[var(--text-color)] outline-none transition-all focus:border-[#3B82F6] resize-none"></textarea>
                </div>

                <div>
                    <label for="puede_acceder_pos" class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em] mb-3 block">¿Tiene acceso al Punto de Venta (POS)?</label>
                    <select id="puede_acceder_pos" name="puede_acceder_pos" required 
                            class="w-full h-12 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-sm font-medium text-[var(--text-color)] outline-none transition-all focus:border-[#3B82F6]">
                        <option value="1">Sí (Meseros, Cajeros, Bartenders, Admins)</option>
                        <option value="0">No (Solo Administrativos / Personal de Nómina)</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-[var(--border-color)] flex justify-end">
                    <button type="submit" class="w-full lg:w-auto px-8 py-3.5 rounded-xl text-xs font-bold bg-[#3B82F6] text-white hover:bg-[#3B82F6]/90 transition-all outline-none shadow-md">
                        <i class="fas fa-save mr-2"></i> Guardar Puesto
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2rem] p-8 lg:p-10 shadow-sm lg:col-span-2 flex flex-col">
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
                                    <button type="button" 
                                            onclick="abrirModalEditar({{ $rol->id }}, '{{ addslashes($rol->nombre) }}', '{{ addslashes($rol->descripcion ?? '') }}', {{ $rol->puede_acceder_pos ? 'true' : 'false' }})"
                                            class="px-3 py-2 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 text-xs font-bold uppercase tracking-widest transition-all outline-none flex items-center gap-1.5">
                                        <i class="fas fa-pencil-alt"></i> Editar
                                    </button>
                                    <button type="button"
                                            onclick="abrirModalEliminar({{ $rol->id }}, '{{ addslashes($rol->nombre) }}')"
                                            class="px-3 py-2 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 text-xs font-bold uppercase tracking-widest transition-all outline-none flex items-center gap-1.5">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </button>
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
</div>

@include('admin.roles.modal-editar')
@include('admin.roles.modal-eliminar')

@endsection