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
        <button onclick="openModal()" class="relative flex items-center gap-2.5 bg-[#3B82F6] border border-[#3B82F6]/50 text-white hover:bg-[#3B82F6]/90 px-7 py-3 rounded-xl text-xs font-bold transition-all duration-300 shadow-[0_8px_20px_-6px_rgba(59,130,246,0.5)] hover:shadow-[0_12px_25px_-4px_rgba(59,130,246,0.6)] hover:-translate-y-0.5 group">
            <i class="fas fa-plus"></i> 
            <span>Agregar Empleado</span>
        </button>
    </div>

    @php
        $totalAdmin = 0; $totalCapitan = 0; $totalMesero = 0; $totalCocinero = 0; $totalCajero = 0;

        if(isset($empleados)) {
            foreach($empleados as $emp) {
                $rol = strtolower($emp->rol);
                if(in_array($rol, ['admin', 'administrador'])) $totalAdmin++;
                elseif(in_array($rol, ['capitan', 'capitán'])) $totalCapitan++;
                elseif($rol == 'mesero') $totalMesero++;
                elseif(in_array($rol, ['cocinero', 'cocina'])) $totalCocinero++;
                elseif($rol == 'cajero') $totalCajero++;
            }
        }
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-6">
        <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-rose-500/50">
            <div class="flex justify-between items-start">
                <h3 class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em]">Administradores</h3>
                <div class="w-10 h-10 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-user-shield text-lg"></i>
                </div>
            </div>
            <p class="text-5xl font-black text-[var(--text-color)] tracking-tighter">{{ $totalAdmin }}</p>
        </div>
        
        <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-blue-400/50">
            <div class="flex justify-between items-start">
                <h3 class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em]">Capitanes</h3>
                <div class="w-10 h-10 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-clipboard-list text-lg"></i>
                </div>
            </div>
            <p class="text-5xl font-black text-[var(--text-color)] tracking-tighter">{{ $totalCapitan }}</p>
        </div>
        
        <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-emerald-400/50">
            <div class="flex justify-between items-start">
                <h3 class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em]">Meseros</h3>
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-concierge-bell text-lg"></i>
                </div>
            </div>
            <p class="text-5xl font-black text-[var(--text-color)] tracking-tighter">{{ $totalMesero }}</p>
        </div>
        
        <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-orange-400/50">
            <div class="flex justify-between items-start">
                <h3 class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em]">Cocineros</h3>
                <div class="w-10 h-10 rounded-2xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-orange-500 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-fire-burner text-lg"></i>
                </div>
            </div>
            <p class="text-5xl font-black text-[var(--text-color)] tracking-tighter">{{ $totalCocinero }}</p>
        </div>

        <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-purple-400/50">
             <div class="flex justify-between items-start">
                 <h3 class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em]">Cajeros</h3>
                 <div class="w-10 h-10 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-500 group-hover:scale-110 transition-transform duration-300">
                     <i class="fas fa-cash-register text-lg"></i>
                 </div>
             </div>
            <p class="text-5xl font-black text-[var(--text-color)] tracking-tighter">{{ $totalCajero }}</p>
        </div>
    </div> 
    
    <div class="glass-card rounded-[2rem] p-8 lg:p-10 w-full flex-1 flex flex-col modo-crema:shadow-2xl">
        
        <div class="mb-8 flex justify-between items-center">
            <div class="flex flex-col">
                <h2 class="text-xl font-bold text-[var(--text-color)] tracking-tight">Lista de Empleados</h2>
                <p class="text-xs text-[var(--text-muted)] mt-1 font-medium">{{ count($empleados ?? []) }} registrados en el sistema</p>
            </div>
            <div class="relative w-64">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)] text-xs"></i>
                <input type="text" id="buscadorEmpleados" placeholder="Buscar empleado..." class="w-full h-10 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg pl-10 pr-4 text-xs font-medium text-[var(--text-color)] focus:ring-1 focus:ring-[#3B82F6] outline-none transition-all shadow-inner">
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
                    <tr class="fila-empleado group hover:bg-black/[0.03] modo-crema:hover:bg-zinc-100/50 transition-colors border-b border-[var(--border-color)]">
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-[#3B82F6]/10 border border-[#3B82F6]/20 flex items-center justify-center text-[#3B82F6] font-black text-xs shrink-0">
                                    {{ substr($empleado->nombre, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="nombre-empleado font-bold text-sm text-[var(--text-color)]">{{ $empleado->nombre }}</span>
                                    <span class="text-[9px] text-[var(--text-muted)] font-black uppercase tracking-[0.2em] mt-0.5 opacity-60">ID: EMP-00{{ $empleado->id }}</span>
                                </div>
                            </div>
                        </td>
                        
                        <td class="py-4 px-4 tabular-nums">
                            <span class="text-[11px] font-black text-[var(--text-muted)] tracking-widest">{{ $empleado->codigo_empleado }}</span>
                        </td>
                        
                        <td class="py-4 px-4">
                            @php
                                $rolDB = strtolower($empleado->rol);
                                $roleClass = '';
                                $roleName = ucfirst($empleado->rol);

                                if(in_array($rolDB, ['admin', 'administrador'])) {
                                    $roleClass = 'bg-[#E11D48] text-white border-transparent';
                                    $roleName = 'Administrador';
                                } elseif(in_array($rolDB, ['capitan', 'capitán'])) {
                                    $roleClass = 'bg-[#2563EB] text-white border-transparent';
                                    $roleName = 'Capitán';
                                } elseif($rolDB == 'mesero') {
                                    $roleClass = 'bg-[#059669] text-white border-transparent';
                                    $roleName = 'Mesero';
                                } elseif(in_array($rolDB, ['cocinero', 'cocina'])) {
                                    $roleClass = 'bg-[#EA580C] text-white border-transparent';
                                    $roleName = 'Cocinero';
                                } else {
                                    $roleClass = 'bg-[#9333EA] text-white border-transparent';
                                    if($rolDB == 'cajero') $roleName = 'Cajero';
                                }
                            @endphp
                            <span class="px-3.5 py-1.5 rounded-md text-[9px] font-black uppercase tracking-[0.25em] {{ $roleClass }} shadow-sm">
                                {{ $roleName }}
                            </span>
                        </td>
                        
                        <td class="py-4 px-4">
                            <a href="{{ route('admin.empleados.permisos', $empleado->id) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-[var(--border-color)] bg-black/20 text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] hover:text-white hover:border-[#3B82F6]/50 transition-all">
                                <i class="fas fa-shield-halved text-[#3B82F6]"></i>
                                Configurar
                            </a>
                        </td>

                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-2.5">
                                <a href="#" class="w-8 h-8 rounded-full bg-black/5 hover:bg-black/10 modo-crema:bg-zinc-100 modo-crema:hover:bg-zinc-200 flex items-center justify-center text-[var(--text-muted)] hover:text-[#3B82F6] transition-all outline-none">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                
                                <form id="delete-form-{{ $empleado->id }}" action="#" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                        onclick="confirmarEliminacion('{{ $empleado->id }}', '{{ $empleado->nombre }}')" 
                                        class="w-8 h-8 rounded-full bg-black/5 hover:bg-black/10 modo-crema:bg-zinc-100 modo-crema:hover:bg-zinc-200 flex items-center justify-center text-[var(--text-muted)] hover:text-rose-500 transition-all outline-none">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center text-zinc-500">No hay empleados registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div id="mensajeSinResultados" class="hidden py-16 text-center opacity-50">
                <i class="fas fa-search-minus text-4xl mb-4"></i>
                <p class="text-sm font-bold">No se encontraron empleados</p>
            </div>
        </div>
    </div>
</div>

<div id="employeeModal" class="fixed inset-0 bg-black/80 modo-crema:bg-zinc-900/70 backdrop-blur-md z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300">
    <div class="relative bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2.5rem] p-10 w-full max-w-[460px] shadow-[0_30px_70px_-10px_rgba(0,0,0,0.8)] transform scale-95 transition-all duration-300 overflow-visible" id="modalContent">
        <div class="absolute top-[-10%] left-[-10%] w-52 h-52 rounded-full bg-blue-600/10 blur-[90px] pointer-events-none z-0"></div>
        <div class="flex justify-between items-start mb-10 z-10 relative">
            <div class="flex flex-col">
                <h2 class="text-2xl font-black text-[var(--text-color)] tracking-tight">Nuevo Empleado</h2>
                <p class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] mt-1">Agrega un nuevo miembro al sistema</p>
            </div>
            <button onclick="closeModal()" class="w-9 h-9 rounded-full bg-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-color)] outline-none shadow-inner">
                <i class="fas fa-xmark text-sm"></i>
            </button>
        </div>
        <form action="{{ route('admin.empleados.store') ?? '#' }}" method="POST" class="space-y-7 z-10 relative">
            @csrf
            <div>
                <label for="nombre_completo" class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em] mb-3 block">Nombre Completo</label>
                <input type="text" id="nombre_completo" name="nombre" placeholder="Ej: Juan Pérez" required class="w-full h-12 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-sm text-[var(--text-color)] outline-none transition-all">
            </div>
            <div>
                <label for="pin" class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em] mb-3 block">PIN de Acceso</label>
                <input type="text" id="pin" name="codigo_empleado" placeholder="Escribe el código..." required class="w-full h-12 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-sm font-semibold tracking-widest text-[var(--text-color)] outline-none transition-all">
            </div>
            <div class="relative group" id="dropdownContainer">
                <label class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em] mb-3 block">Rol</label>
                <input type="hidden" name="rol" id="rol_input" value="admin">
                <button type="button" onclick="toggleDropdown()" id="dropdownBtn" class="flex items-center justify-between w-full h-12 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-sm font-bold text-[var(--text-color)] outline-none transition-all">
                    <span id="dropdownSelected">Administrador</span>
                    <i class="fas fa-chevron-down text-[var(--text-muted)] transition-transform" id="dropdownIcon"></i>
                </button>
                <div id="dropdownMenu" class="absolute top-full left-0 mt-2 w-full max-h-48 overflow-y-auto bg-[var(--card-color)] border border-[var(--border-color)] rounded-xl shadow-2xl z-50 py-2.5 opacity-0 pointer-events-none transform -translate-y-2 transition-all">
                    @php
                        $rolesList = ['Administrador' => 'admin', 'Capitán' => 'capitan', 'Mesero' => 'mesero', 'Cocinero' => 'cocinero', 'Cajero' => 'cajero'];
                    @endphp
                    @foreach($rolesList as $name => $tech)
                    <button type="button" onclick="selectRole('{{ $name }}', '{{ $tech }}')" class="role-option flex items-center justify-between w-full text-left px-5 py-3 text-sm font-medium text-[var(--text-muted)] hover:text-[var(--text-color)] hover:bg-black/5 transition-all">
                        <span>{{ $name }}</span>
                        <i class="fas fa-check text-[#3B82F6] {{ $name == 'Administrador' ? 'opacity-100' : 'opacity-0' }} ml-3"></i>
                    </button>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-10 pt-4 border-t border-[var(--border-color)]">
                <button type="button" onclick="closeModal()" class="px-7 py-3 rounded-xl text-xs font-bold text-[var(--text-muted)] hover:text-[var(--text-color)] bg-[var(--border-color)] transition-all outline-none shadow-inner">Cancelar</button>
                <button type="submit" class="px-8 py-3 rounded-xl text-xs font-bold bg-[#3B82F6] text-white hover:bg-[#3B82F6]/90 transition-all outline-none">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 bg-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden transition-all duration-300">
    <div class="relative bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2.5rem] p-10 w-full max-w-[400px] shadow-2xl transform transition-all">
        <div class="flex flex-col items-center text-center relative z-10">
            <div class="w-16 h-16 rounded-full bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500 mb-6">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            <h2 class="text-xl font-black text-[var(--text-color)] tracking-tight mb-2">¿Eliminar Empleado?</h2>
            <p class="text-xs text-[var(--text-muted)] font-medium mb-6">Estás a punto de eliminar a <span id="nombreEmpleadoEliminar" class="font-bold text-[var(--text-color)] block"></span></p>
            <div class="flex w-full gap-3">
                <button type="button" onclick="cerrarModalEliminar()" class="flex-1 py-3.5 rounded-xl text-xs font-bold text-[var(--text-muted)] bg-[var(--border-color)] transition-all">Cancelar</button>
                <button type="button" id="btnConfirmarEliminar" class="flex-1 py-3.5 rounded-xl text-xs font-bold bg-rose-500 text-white transition-all">Sí, eliminar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // --- BÚSQUEDA EN TIEMPO REAL (LÓGICA NUEVA) ---
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

    // --- LÓGICA DE MODALES Y DROPDOWN (TU CÓDIGO ORIGINAL) ---
    const modal = document.getElementById('employeeModal');
    const modalContent = document.getElementById('modalContent');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const dropdownIcon = document.getElementById('dropdownIcon');
    const dropdownBtn = document.getElementById('dropdownBtn');

    function openModal() {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modalContent.classList.replace('scale-95', 'scale-100');
    }
    function closeModal() {
        modal.classList.add('opacity-0', 'pointer-events-none');
        modalContent.classList.replace('scale-100', 'scale-95');
        if (!dropdownMenu.classList.contains('opacity-0')) toggleDropdown();
    }
    function toggleDropdown() {
        const isOpen = !dropdownMenu.classList.contains('opacity-0');
        if (isOpen) {
            dropdownMenu.classList.add('opacity-0', 'pointer-events-none', '-translate-y-2');
            dropdownIcon.classList.remove('rotate-180', 'text-[#3B82F6]');
        } else {
            dropdownMenu.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-2');
            dropdownIcon.classList.add('rotate-180', 'text-[#3B82F6]');
        }
    }
    function selectRole(name, techValue) {
        document.getElementById('dropdownSelected').innerText = name;
        document.getElementById('rol_input').value = techValue;
        toggleDropdown();
    }

    let formIdParaEliminar = null;
    function confirmarEliminacion(id, nombre) {
        formIdParaEliminar = 'delete-form-' + id;
        document.getElementById('nombreEmpleadoEliminar').innerText = nombre;
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    function cerrarModalEliminar() {
        document.getElementById('deleteModal').classList.add('hidden');
        formIdParaEliminar = null;
    }
    document.getElementById('btnConfirmarEliminar').addEventListener('click', function() {
        if (formIdParaEliminar) document.getElementById(formIdParaEliminar).submit();
    });
</script>
@endpush