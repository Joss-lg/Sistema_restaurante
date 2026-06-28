@extends('layouts.admin')

@section('title', 'Empleados | Ollintem Pro')
@section('header-title', 'Gestión de Personal')
@section('header-subtitle', 'Administra roles y permisos del equipo')

@section('content')
<div class="p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-3xl font-black text-[var(--text-color)] tracking-tight">Empleados</h1>
        </div>

        @if(auth()->user()->tienePermiso('empleados.agregar'))
            <div class="relative group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl blur opacity-30 group-hover:opacity-60 transition duration-500 pointer-events-none"></div>
                <button onclick="openModal()" class="relative flex items-center gap-2.5 bg-[#3B82F6] border border-white/10 text-white px-7 py-3.5 rounded-xl text-xs font-bold transition-all duration-300 hover:-translate-y-0.5 outline-none">
                    <i class="fas fa-plus"></i> 
                    <span>Agregar Empleado</span>
                </button>
            </div>
        @endif
    </div>

    {{-- ESTADÍSTICAS --}}
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
        {{-- ... (Tu bloque de estadísticas existente) ... --}}
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

    {{-- BOTÓN DE FILTRO --}}
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
                        <td class="py-4 px-4">{{ $empleado->codigo_empleado }}</td>
                        <td class="py-4 px-4">{{ $empleado->rol?->nombre ?? 'Sin rol' }}</td>
                        <td class="py-4 px-4">
                            <a href="{{ route('admin.empleados.permisos', $empleado->id) }}" class="text-[9px] font-black uppercase text-[#3B82F6] hover:underline">Configurar</a>
                        </td>
                        <td class="py-4 px-4 text-right">
                            @if($empleado->esta_activo)
                                <form action="{{ route('admin.empleados.destroy', $empleado->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-500"><i class="fas fa-user-slash"></i></button>
                                </form>
                            @else
                                <form action="{{ route('admin.empleados.reactivar', $empleado->id) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-emerald-500"><i class="fas fa-user-check"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-zinc-500">No hay empleados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.empleados.modal-editar')

@endsection

@push('scripts')
<script>
    // --- 1. DELEGACIÓN DE EVENTOS PARA EL BOTÓN EDITAR ---
    document.body.addEventListener('click', function(e) {
        const botonEditar = e.target.closest('.btn-abrir-editar');
        
        if (botonEditar) {
            e.preventDefault(); 

            const id = botonEditar.getAttribute('data-id');
            const nombre = botonEditar.getAttribute('data-nombre');
            const codigo = botonEditar.getAttribute('data-codigo');
            const rolId = botonEditar.getAttribute('data-rol-id');
            const rolNombre = botonEditar.getAttribute('data-rol-nombre');

            const inNombre = document.getElementById('edit_nombre');
            const inCodigo = document.getElementById('edit_codigo_empleado');
            const inRolId = document.getElementById('edit_rol_id_input');
            const editForm = document.getElementById('formEditar');

            if (inNombre) inNombre.value = nombre;
            if (inCodigo) inCodigo.value = codigo;
            if (inRolId) inRolId.value = rolId;

            // También llenamos el dropdown visual personalizado
            const labelDropdown = document.getElementById('editDropdownSelected');
            if (labelDropdown) labelDropdown.innerText = rolNombre || 'Seleccionar...';

            if (editForm) editForm.action = `/admin/empleados/${id}`;

            const editModal = document.getElementById('editEmpleadoModal');
            const editModalContent = document.getElementById('editModalContent');

            if (editModal) {
                editModal.classList.remove('hidden');
                setTimeout(() => {
                    editModal.classList.remove('opacity-0');
                    if (editModalContent) editModalContent.classList.replace('scale-95', 'scale-100');
                }, 10);
            }
        }
    });

    // --- 2. FUNCIONES DEL MODAL EDITAR (CON CORRECCIÓN DE DROPDOWN) ---
    window.cerrarEditModal = function() {
        const editModal = document.getElementById('editEmpleadoModal');
        const editModalContent = document.getElementById('editModalContent');
        
        if (editModal) editModal.classList.add('opacity-0');
        if (editModalContent) editModalContent.classList.replace('scale-100', 'scale-95');
        
        // Cerrar menú por si quedó abierto
        if(typeof window.cerrarMenuEdit === 'function') window.cerrarMenuEdit();
        
        setTimeout(() => {
            if (editModal) editModal.classList.add('hidden');
        }, 300);
    }

    // AQUI ESTA LA CORRECCIÓN: el event.stopPropagation()
    window.toggleEditDropdown = function(event) {
        if(event) event.stopPropagation(); // <-- Detiene el clic para que no lo cierre
        
        const menu = document.getElementById('editDropdownMenu');
        const icon = document.getElementById('editDropdownIcon');
        if(!menu) return;

        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            setTimeout(() => {
                menu.classList.remove('opacity-0', 'translate-y-[-10px]');
                if(icon) icon.classList.add('rotate-180');
            }, 10);
        } else {
            window.cerrarMenuEdit();
        }
    }

    window.cerrarMenuEdit = function() {
        const menu = document.getElementById('editDropdownMenu');
        const icon = document.getElementById('editDropdownIcon');
        if(!menu || menu.classList.contains('hidden')) return;
        
        menu.classList.add('opacity-0', 'translate-y-[-10px]');
        if(icon) icon.classList.remove('rotate-180');
        setTimeout(() => menu.classList.add('hidden'), 300);
    }

    window.selectEditRole = function(label, value) {
        const selected = document.getElementById('editDropdownSelected');
        const input = document.getElementById('edit_rol_id_input');
        if(selected) selected.innerText = label;
        if(input) input.value = value;
        window.cerrarMenuEdit();
    }

    // Cierra el menú al dar clic fuera
    document.addEventListener('click', (e) => {
        const container = document.getElementById('editDropdownContainer');
        if (container && !container.contains(e.target)) {
            window.cerrarMenuEdit();
        }
    });

    // --- 3. BÚSQUEDA EN TIEMPO REAL ---
    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscadorEmpleados');
        const filas = document.querySelectorAll('.fila-empleado');
        const mensajeVacio = document.getElementById('mensajeSinResultados');

        if (buscador) {
            buscador.addEventListener('input', function(e) {
                const terminoBusqueda = e.target.value.toLowerCase().trim();
                let empleadosVisibles = 0;

                filas.forEach(fila => {
                    const nombre = fila.querySelector('.nombre-empleado').textContent.toLowerCase();
                    if (nombre.includes(terminoBusqueda)) {
                        fila.style.display = '';
                        empleadosVisibles++;
                    } else {
                        fila.style.display = 'none';
                    }
                });

                if (empleadosVisibles === 0 && filas.length > 0) {
                    mensajeVacio.classList.remove('hidden');
                } else {
                    mensajeVacio.classList.add('hidden');
                }
            });
        }
    });

    // --- 4. MODALES GENERALES (NUEVO Y ELIMINAR) ---
    const modal = document.getElementById('employeeModal');
    const modalContent = document.getElementById('modalContent');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const dropdownIcon = document.getElementById('dropdownIcon');

    window.openModal = function() {
        if(modal) modal.classList.remove('opacity-0', 'pointer-events-none');
        if(modalContent) modalContent.classList.replace('scale-95', 'scale-100');
    }

    window.closeModal = function() {
        if(modal) modal.classList.add('opacity-0', 'pointer-events-none');
        if(modalContent) modalContent.classList.replace('scale-100', 'scale-95');
        if (dropdownMenu && !dropdownMenu.classList.contains('opacity-0')) window.toggleDropdown();
    }

    window.toggleDropdown = function() {
        if(!dropdownMenu) return;
        const isOpen = !dropdownMenu.classList.contains('opacity-0');
        if (isOpen) {
            dropdownMenu.classList.add('opacity-0', 'pointer-events-none', '-translate-y-2');
            if(dropdownIcon) dropdownIcon.classList.remove('rotate-180', 'text-[#3B82F6]');
        } else {
            dropdownMenu.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-2');
            if(dropdownIcon) dropdownIcon.classList.add('rotate-180', 'text-[#3B82F6]');
        }
    }

    window.selectRole = function(name, techValue) {
        const selected = document.getElementById('dropdownSelected');
        const input = document.getElementById('rol_id_input');
        if(selected) selected.innerText = name;
        if(input) input.value = techValue;
        window.toggleDropdown();
    }

    // ELIMINACIÓN
    let formIdParaEliminar = null;
    window.confirmarEliminacion = function(id, nombre) {
        formIdParaEliminar = 'delete-form-' + id;
        const elimNombre = document.getElementById('nombreEmpleadoEliminar');
        const elimModal = document.getElementById('deleteModal');
        if(elimNombre) elimNombre.innerText = nombre;
        if(elimModal) elimModal.classList.remove('hidden');
    }

    window.cerrarModalEliminar = function() {
        const elimModal = document.getElementById('deleteModal');
        if(elimModal) elimModal.classList.add('hidden');
        formIdParaEliminar = null;
    }

    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', function() {
            if (formIdParaEliminar) {
                const form = document.getElementById(formIdParaEliminar);
                if(form) form.submit();
            }
        });
    }
</script>
@endpush