@extends('layouts.admin')

@section('title', 'Empleados | Ollintem Pro')
@section('header-title', 'Gestión de Personal')
@section('header-subtitle', 'Administra roles y permisos del equipo')

@section('content')
<div class="p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col">
    
    {{-- CABECERA DE LA SECCIÓN --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-3xl font-black text-[var(--text-color)] tracking-tight">Empleados</h1>
        </div>

        @if(auth()->user()->tienePermiso('empleados.agregar'))
            <div class="relative group z-10">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl blur opacity-30 group-hover:opacity-60 transition duration-500 pointer-events-none"></div>
                <button type="button" onclick="openModal()" class="relative flex items-center gap-2.5 bg-[#3B82F6] border border-white/10 text-white px-7 py-3.5 rounded-xl text-xs font-bold transition-all duration-300 hover:-translate-y-0.5 outline-none">
                    <i class="fas fa-plus"></i> 
                    <span>Agregar Empleado</span>
                </button>
            </div>
        @endif
    </div>

    {{-- ESTADÍSTICAS PROCESADAS --}}
    @php
        $totalAdmin = 0; $totalCapitan = 0; $totalMesero = 0; $totalCocinero = 0; $totalCajero = 0;
        if(isset($empleados)) {
            foreach($empleados as $emp) {
                $rolSlug = strtolower($emp->rol?->slug ?? '');
                if(in_array($rolSlug, ['admin', 'administrador'])) $totalAdmin++;
                elseif(in_array($rolSlug, ['capitan'])) $totalCapitan++;
                elseif($rolSlug == 'mesero') $totalMesero++;
                elseif(in_array($rolSlug, ['cocinero'])) $totalCocinero++;
                elseif($rolSlug == 'cajero') $totalCajero++;
            }
        }
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-6">
        <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative overflow-hidden transition-all hover:border-rose-500/50 shadow-sm">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-rose-500/10 to-transparent rounded-full -mr-10 -mt-10 blur-2xl transition-all group-hover:bg-rose-500/20"></div>
            <h3 class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em]">Administradores</h3>
            <p class="text-5xl font-black text-[var(--text-color)]">{{ $totalAdmin }}</p>
        </div>
        <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative overflow-hidden transition-all hover:border-blue-400/50 shadow-sm">
            <h3 class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em]">Capitanes</h3>
            <p class="text-5xl font-black text-[var(--text-color)]">{{ $totalCapitan }}</p>
        </div>
        <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative overflow-hidden transition-all hover:border-emerald-400/50 shadow-sm">
            <h3 class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em]">Meseros</h3>
            <p class="text-5xl font-black text-[var(--text-color)]">{{ $totalMesero }}</p>
        </div>
        <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative overflow-hidden transition-all hover:border-orange-400/50 shadow-sm">
            <h3 class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em]">Cocineros</h3>
            <p class="text-5xl font-black text-[var(--text-color)]">{{ $totalCocinero }}</p>
        </div>
        <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative overflow-hidden transition-all hover:border-purple-400/50 shadow-sm">
            <h3 class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em]">Cajeros</h3>
            <p class="text-5xl font-black text-[var(--text-color)]">{{ $totalCajero }}</p>
        </div>
    </div> 

    {{-- FILTRO DE INACTIVOS --}}
    <div class="flex justify-end">
        @if(request()->has('ver_inactivos'))
            <a href="{{ route('admin.empleados.index') }}" class="text-xs font-bold text-rose-500 hover:underline flex items-center gap-2">
                <i class="fas fa-eye-slash"></i> Ocultar inactivos
            </a>
        @else
            <a href="{{ route('admin.empleados.index', ['ver_inactivos' => 1]) }}" class="text-xs font-bold text-blue-500 hover:underline flex items-center gap-2">
                <i class="fas fa-eye"></i> Ver empleados inactivos
            </a>
        @endif
    </div>
    
    {{-- CONTENEDOR DE LA TABLA --}}
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2rem] p-8 lg:p-10 w-full flex-1 flex flex-col shadow-sm">
        <div class="mb-8 flex justify-between items-center">
            <div class="flex flex-col">
                <h2 class="text-xl font-bold text-[var(--text-color)] tracking-tight">Lista de Empleados</h2>
            </div>
            <div class="relative w-64">
                <input type="text" id="buscadorEmpleados" placeholder="Buscar empleado..." class="w-full h-11 bg-black/5 border border-transparent rounded-xl pl-10 pr-4 text-xs font-medium focus:border-[#3B82F6] outline-none">
            </div>
        </div>

        <div class="overflow-x-auto">
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
                        <td class="py-4 px-4">
                            <span class="nombre-empleado font-bold text-sm text-[var(--text-color)]">{{ $empleado->nombre }}</span>
                        </td>
                        <td class="py-4 px-4 tracking-wider">{{ $empleado->codigo_empleado }}</td>
                        <td class="py-4 px-4">{{ $empleado->rol?->nombre ?? 'Sin rol' }}</td>
                        <td class="py-4 px-4">
                            <a href="{{ route('admin.empleados.permisos', $empleado->id) }}" class="text-[9px] font-black uppercase text-[#3B82F6] hover:underline">Configurar</a>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-3.5">
                                @if($empleado->esta_activo)
                                    {{-- BOTÓN EDITAR PERFIL --}}
                                    <button type="button" 
                                        class="btn-abrir-editar text-blue-500 hover:text-blue-700 transition-colors"
                                        data-id="{{ $empleado->id }}"
                                        data-nombre="{{ $empleado->nombre }}"
                                        data-codigo="{{ $empleado->codigo_empleado }}"
                                        data-rol-id="{{ $empleado->rol_id }}"
                                        data-rol-nombre="{{ $empleado->rol?->nombre }}">
                                        <i class="fas fa-edit text-base"></i>
                                    </button>

                                    <form action="{{ route('admin.empleados.destroy', $empleado->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 transition-colors"><i class="fas fa-user-slash"></i></button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.empleados.reactivar', $empleado->id) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-emerald-500 hover:text-emerald-700 transition-colors"><i class="fas fa-user-check"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="5" id="mensajeSinResultados" class="py-10 text-center text-zinc-500">No hay empleados registrados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL DE EDITAR (CONEXIÓN EXTERNA) --}}
@include('admin.empleados.modal-editar')

{{-- MODAL DE CREAR (ESTRUCTURA INTEGRADA PREMIUM) --}}
<div id="employeeModal" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100] flex items-center justify-center p-4 hidden opacity-0 transition-all duration-500">
    <div id="modalContent" class="modal-inverso relative bg-[var(--m-bg,#ffffff)] border border-[var(--m-border,#e2e8f0)] rounded-[2.5rem] p-10 w-full max-w-[480px] transform scale-95 transition-all duration-500" style="box-shadow: var(--m-shadow);">
        
        <button type="button" onclick="closeModal()" class="absolute top-8 right-8 text-[var(--m-muted,#71717a)] hover:text-[var(--m-text,#09090b)] hover:rotate-90 transition-all duration-300 outline-none z-10">
            <i class="fas fa-times text-xl"></i>
        </button>

        <div class="mb-10 relative z-10">
            <h2 class="text-3xl font-black text-[var(--m-text,#09090b)] tracking-tighter">Registrar Perfil</h2>
            <p class="text-[11px] font-bold text-[var(--m-muted,#71717a)] uppercase tracking-[0.2em] mt-2 opacity-80">Nueva credencial de acceso</p>
        </div>

        <form action="{{ route('admin.empleados.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- CAMPO: NOMBRE COMPLETO -->
            <div class="group">
                <label for="nombre" class="text-[10px] font-black text-[var(--m-text,#09090b)] uppercase tracking-[0.2em] mb-3 block opacity-90 group-focus-within:text-[#3B82F6] transition-colors">
                    Nombre Completo
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <i class="fas fa-user text-[var(--m-muted,#71717a)] group-focus-within:text-[#3B82F6] transition-colors text-sm"></i>
                    </div>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej. Sebastian Admin"
                        class="w-full h-14 bg-[var(--m-input-bg,#f8fafc)] border border-[var(--m-border,#e2e8f0)] rounded-2xl pl-12 pr-6 text-sm font-bold text-[var(--m-text,#09090b)] outline-none transition-all focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10">
                </div>
            </div>
            
            <!-- CAMPO: PIN DE SEGURIDAD -->
            <div class="group">
                <label for="codigo_empleado" class="text-[10px] font-black text-[var(--m-text,#09090b)] uppercase tracking-[0.2em] mb-3 block opacity-90 group-focus-within:text-[#3B82F6] transition-colors">
                    PIN de Seguridad (4 dígitos)
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-[var(--m-muted,#71717a)] group-focus-within:text-[#3B82F6] transition-colors text-sm"></i>
                    </div>
                    <input type="text" id="codigo_empleado" name="codigo_empleado" maxlength="4" required placeholder="0000"
                        class="w-full h-14 bg-[var(--m-input-bg,#f8fafc)] border border-[var(--m-border,#e2e8f0)] rounded-2xl pl-12 pr-6 text-base font-black tracking-[0.8em] text-[var(--m-text,#09090b)] outline-none transition-all focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10">
                </div>
            </div>

            <!-- CAMPO DROPDOWN: ROL DEL SISTEMA -->
            <div class="relative group" id="dropdownContainer">
                <label class="text-[10px] font-black text-[var(--m-text,#09090b)] uppercase tracking-[0.2em] mb-3 block opacity-90 group-focus-within:text-[#3B82F6] transition-colors">
                    Rol del Sistema
                </label>
                
                <input type="hidden" name="rol_id" id="rol_id_input" required>
                
                <button type="button" onclick="toggleDropdown(event)" id="dropdownBtn"
                    class="flex items-center justify-between w-full h-14 bg-[var(--m-input-bg,#f8fafc)] border border-[var(--m-border,#e2e8f0)] rounded-2xl pl-5 pr-6 text-sm font-bold text-[var(--m-text,#09090b)] outline-none transition-all focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-shield-halved text-[var(--m-muted,#71717a)] group-focus-within:text-[#3B82F6] transition-colors text-sm"></i>
                        <span id="dropdownSelected">Seleccionar...</span>
                    </div>
                    <i class="fas fa-chevron-down text-[var(--m-muted,#71717a)] transition-transform duration-300" id="dropdownIcon"></i>
                </button>

                <div id="dropdownMenu" class="absolute top-[calc(100%+8px)] left-0 w-full bg-[var(--m-drop-bg,#ffffff)] border border-[var(--m-border,#e2e8f0)] rounded-2xl shadow-2xl z-[110] py-2 hidden opacity-0 translate-y-[-10px] transition-all duration-300 max-h-48 overflow-y-auto backdrop-blur-xl">
                    @foreach($roles ?? [] as $rol)
                        <button type="button" onclick="selectRole('{{ $rol->nombre }}', '{{ $rol->id }}')"
                            class="flex items-center justify-between w-full px-6 py-3.5 text-sm font-bold text-[var(--m-text,#09090b)] hover:bg-[var(--m-drop-hover,#f1f5f9)] hover:text-[#3B82F6] transition-all outline-none">
                            {{ $rol->nombre }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- BOTONES DE ACCIÓN -->
            <div class="flex justify-end gap-4 mt-12 pt-8 border-t border-[var(--m-border,#e2e8f0)]">
                <button type="button" onclick="closeModal()" 
                    class="px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-[var(--m-muted,#71717a)] hover:text-[var(--m-text,#09090b)] hover:bg-[var(--m-input-bg,#f8fafc)] transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit" 
                    class="px-10 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest bg-[var(--m-btn-bg,#0f172a)] text-[var(--m-btn-text,#ffffff)] hover:-translate-y-1 active:scale-95 transition-all shadow-xl outline-none">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // --- 1. ESCUCHADOR DE EVENTOS GLOBAL PARA INTERFACES DE EDICIÓN ---
    document.body.addEventListener('click', function(e) {
        const botonEditar = e.target.closest('.btn-abrir-editar');
        
        if (botonEditar) {
            e.preventDefault(); 

            const id = botonEditar.getAttribute('data-id');
            const nombre = botonEditar.getAttribute('data-nombre');
            const codigo = botonEditar.getAttribute('data-codigo');
            const rolId = botonEditar.getAttribute('data-rol-id');
            const rolNombre = botonEditar.getAttribute('data-rol-nombre');

            // --- CORRECCIÓN: Actualizamos dinámicamente la ruta del formulario ---
            const formEditar = document.getElementById('formEditar');
            if (formEditar) {
                formEditar.action = '/empleados/' + id;
            }

            // Llama a la función nativa del modal-editar de manera segura
            if (typeof window.abrirModalEditar === 'function') {
                window.abrirModalEditar(id, nombre, codigo, rolId, rolNombre);
            } else if (typeof abrirModalEditar === 'function') {
                abrirModalEditar(id, nombre, codigo, rolId, rolNombre);
            }
        }
    });

    // --- 2. CONTROLADORES DEL MODAL CREAR EMPLEADO ---
    window.openModal = function() {
        const modal = document.getElementById('employeeModal');
        const content = document.getElementById('modalContent');
        if (!modal) return;

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            if (content) content.classList.remove('scale-95');
        }, 10);
    }

    window.closeModal = function() {
        const modal = document.getElementById('employeeModal');
        const content = document.getElementById('modalContent');
        if (!modal) return;

        modal.classList.add('opacity-0');
        if (content) content.classList.add('scale-95');
        
        // Cierre preventivo del menú interno
        const menu = document.getElementById('dropdownMenu');
        if (menu) menu.classList.add('hidden', 'opacity-0', 'translate-y-[-10px]');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 500);
    }

    window.toggleDropdown = function(event) {
        if (event) event.stopPropagation();
        const menu = document.getElementById('dropdownMenu');
        const icon = document.getElementById('dropdownIcon');
        if (!menu) return;

        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            setTimeout(() => {
                menu.classList.remove('opacity-0', 'translate-y-[-10px]');
                if (icon) icon.classList.add('rotate-180');
            }, 10);
        } else {
            menu.classList.add('opacity-0', 'translate-y-[-10px]');
            if (icon) icon.classList.remove('rotate-180');
            setTimeout(() => menu.classList.add('hidden'), 300);
        }
    }

    window.selectRole = function(name, techValue) {
        const selected = document.getElementById('dropdownSelected');
        const input = document.getElementById('rol_id_input');
        const menu = document.getElementById('dropdownMenu');
        const icon = document.getElementById('dropdownIcon');

        if (selected) selected.innerText = name;
        if (input) input.value = techValue;
        
        if (menu) menu.classList.add('hidden', 'opacity-0', 'translate-y-[-10px]');
        if (icon) icon.classList.remove('rotate-180');
    }

    // Cerrar el dropdown de crear si se hace clic fuera del contenedor
    document.addEventListener('click', function(e) {
        const container = document.getElementById('dropdownContainer');
        const menu = document.getElementById('dropdownMenu');
        const icon = document.getElementById('dropdownIcon');
        
        if (container && !container.contains(e.target)) {
            if (menu && !menu.classList.contains('hidden')) {
                menu.classList.add('opacity-0', 'translate-y-[-10px]');
                if (icon) icon.classList.remove('rotate-180');
                setTimeout(() => menu.classList.add('hidden'), 300);
            }
        }
    });

    // --- 3. FILTRO DEL BUSCADOR EN TIEMPO REAL ---
    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscadorEmpleados');
        const filas = document.querySelectorAll('.fila-empleado');
        const mensajeVacio = document.getElementById('mensajeSinResultados');

        if (buscador) {
            buscador.addEventListener('input', function(e) {
                const terminoBusqueda = e.target.value.toLowerCase().trim();
                let empleadosVisibles = 0;

                filas.forEach(fila => {
                    const elNombre = fila.querySelector('.nombre-empleado');
                    if (elNombre) {
                        const nombre = elNombre.textContent.toLowerCase();
                        if (nombre.includes(terminoBusqueda)) {
                            fila.style.display = '';
                            empleadosVisibles++;
                        } else {
                            fila.style.display = 'none';
                        }
                    }
                });

                if (mensajeVacio && filas.length > 0) {
                    if (empleadosVisibles === 0) {
                        mensajeVacio.style.display = '';
                        mensajeVacio.innerText = "No se encontraron empleados que coincidan";
                    } else {
                        mensajeVacio.style.display = 'none';
                    }
                }
            });
        }
    });
</script>
@endpush