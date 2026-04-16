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
                <h3 class="text-[11px] font-bold text-[var(--text-muted)] uppercase tracking-[0.1em]">Administradores</h3>
                <div class="w-10 h-10 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-user-shield text-lg"></i>
                </div>
            </div>
            <p class="text-4xl font-black text-[var(--text-color)] tracking-tighter">{{ $totalAdmin }}</p>
        </div>
        
        <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-blue-400/50">
            <div class="flex justify-between items-start">
                <h3 class="text-[11px] font-bold text-[var(--text-muted)] uppercase tracking-[0.1em]">Capitanes</h3>
                <div class="w-10 h-10 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-clipboard-list text-lg"></i>
                </div>
            </div>
            <p class="text-4xl font-black text-[var(--text-color)] tracking-tighter">{{ $totalCapitan }}</p>
        </div>
        
        <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-emerald-400/50">
            <div class="flex justify-between items-start">
                <h3 class="text-[11px] font-bold text-[var(--text-muted)] uppercase tracking-[0.1em]">Meseros</h3>
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-concierge-bell text-lg"></i>
                </div>
            </div>
            <p class="text-4xl font-black text-[var(--text-color)] tracking-tighter">{{ $totalMesero }}</p>
        </div>
        
        <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-orange-400/50">
            <div class="flex justify-between items-start">
                <h3 class="text-[11px] font-bold text-[var(--text-muted)] uppercase tracking-[0.1em]">Cocineros</h3>
                <div class="w-10 h-10 rounded-2xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-orange-500 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-fire-burner text-lg"></i>
                </div>
            </div>
            <p class="text-4xl font-black text-[var(--text-color)] tracking-tighter">{{ $totalCocinero }}</p>
        </div>

        <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-purple-400/50">
             <div class="flex justify-between items-start">
                 <h3 class="text-[11px] font-bold text-[var(--text-muted)] uppercase tracking-[0.1em]">Cajeros</h3>
                 <div class="w-10 h-10 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-500 group-hover:scale-110 transition-transform duration-300">
                     <i class="fas fa-cash-register text-lg"></i>
                 </div>
             </div>
            <p class="text-4xl font-black text-[var(--text-color)] tracking-tighter">{{ $totalCajero }}</p>
        </div>
    </div> <div class="glass-card rounded-[2rem] p-8 lg:p-10 w-full flex-1 flex flex-col modo-crema:shadow-2xl">
        
        <div class="mb-8 flex justify-between items-center">
            <div class="flex flex-col">
                <h2 class="text-xl font-bold text-[var(--text-color)] tracking-tight">Lista de Empleados</h2>
                <p class="text-xs text-[var(--text-muted)] mt-1 font-medium">{{ count($empleados ?? []) }} registrados en el sistema</p>
            </div>
            <div class="relative w-64">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)] text-xs"></i>
                <input type="text" placeholder="Buscar empleado..." class="w-full h-10 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg pl-10 pr-4 text-xs font-medium text-[var(--text-color)] focus:ring-1 focus:ring-[#3B82F6] outline-none transition-all shadow-inner">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[var(--border-color)]">
                        <th class="pb-5 px-4 text-[10px] font-black text-[var(--text-muted)] opacity-80 uppercase tracking-widest">Nombre</th>
                        <th class="pb-5 px-4 text-[10px] font-black text-[var(--text-muted)] opacity-80 uppercase tracking-widest">PIN</th>
                        <th class="pb-5 px-4 text-[10px] font-black text-[var(--text-muted)] opacity-80 uppercase tracking-widest">Rol</th>
                        <th class="pb-5 px-4 text-[10px] font-black text-[var(--text-muted)] opacity-80 uppercase tracking-widest">Permisos Asignados</th>
                        <th class="pb-5 px-4 text-[10px] font-black text-[var(--text-muted)] opacity-80 uppercase tracking-widest text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($empleados ?? [] as $empleado)
                    <tr class="group hover:bg-black/[0.03] modo-crema:hover:bg-zinc-100/50 transition-colors border-b border-[var(--border-color)]">
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-9 h-9 rounded-xl bg-[#3B82F6]/10 border border-[#3B82F6]/20 flex items-center justify-center text-[#3B82F6] font-black text-xs shrink-0">
                                    {{ substr($empleado->nombre, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-sm text-[var(--text-color)]">{{ $empleado->nombre }}</span>
                                    <span class="text-[10px] text-[var(--text-muted)] font-medium mt-0.5">ID: EMP-00{{ $empleado->id }}</span>
                                </div>
                            </div>
                        </td>
                        
                        <td class="py-4 px-4 tabular-nums">
                            <span class="text-xs font-medium text-[var(--text-muted)]">{{ $empleado->codigo_empleado }}</span>
                        </td>
                        
                        <td class="py-4 px-4">
                            @php
                                $rolDB = strtolower($empleado->rol);
                                $roleClass = '';
                                $roleName = ucfirst($empleado->rol);

                                if(in_array($rolDB, ['admin', 'administrador'])) {
                                    $roleClass = 'bg-[#1E1214] border-rose-500/20 text-rose-400 modo-crema:bg-rose-50 modo-crema:border-rose-200 modo-crema:text-rose-600';
                                    $roleName = 'Administrador';
                                } elseif(in_array($rolDB, ['capitan', 'capitán'])) {
                                    $roleClass = 'bg-[#11141A] border-blue-500/20 text-blue-400 modo-crema:bg-blue-50 modo-crema:border-blue-200 modo-crema:text-blue-600';
                                    $roleName = 'Capitán';
                                } elseif($rolDB == 'mesero') {
                                    $roleClass = 'bg-[#111613] border-emerald-500/20 text-emerald-400 modo-crema:bg-zinc-100 modo-crema:border-zinc-200 modo-crema:text-zinc-600';
                                    $roleName = 'Mesero';
                                } elseif(in_array($rolDB, ['cocinero', 'cocina'])) {
                                    $roleClass = 'bg-[#1A1411] border-orange-500/20 text-orange-400 modo-crema:bg-orange-50 modo-crema:border-orange-200 modo-crema:text-orange-600';
                                    $roleName = 'Cocinero';
                                } else {
                                    $roleClass = 'bg-[#15121A] border-purple-500/20 text-purple-400 modo-crema:bg-purple-50 modo-crema:border-purple-200 modo-crema:text-purple-600';
                                    if($rolDB == 'cajero') $roleName = 'Cajero';
                                }
                            @endphp
                            <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $roleClass }}">
                                {{ $roleName }}
                            </span>
                        </td>
                        
                        <td class="py-4 px-4">
                            <div class="flex flex-wrap gap-x-4 gap-y-2.5 max-w-sm">
                                @foreach($permisos ?? [] as $permiso)
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" 
                                               name="permisos[{{ $empleado->id }}][]"
                                               value="{{ $permiso->id }}"
                                               id="p-{{ $empleado->id }}-{{ $permiso->id }}"
                                               class="w-3.5 h-3.5 rounded-sm border-[var(--border-color)] modo-crema:border-zinc-300 bg-transparent text-[#3B82F6] cursor-pointer" 
                                               style="accent-color: #3B82F6;"
                                               {{ $empleado->tienePermiso($permiso->slug) ? 'checked' : '' }}>
                                        <label for="p-{{ $empleado->id }}-{{ $permiso->id }}" class="text-[10px] font-medium text-[var(--text-muted)] cursor-pointer select-none hover:text-[var(--text-color)] transition-colors">
                                            {{ $permiso->nombre }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
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
                        <td colspan="5" class="py-16 text-center">
                            <div class="flex flex-col items-center justify-center opacity-50">
                                <i class="fas fa-users-slash text-4xl text-[var(--text-muted)] mb-5"></i>
                                <p class="text-sm font-medium text-[var(--text-muted)] mb-1">No hay empleados registrados</p>
                                <p class="text-xs text-[var(--text-muted)]">Comienza agregando un nuevo miembro al equipo</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-10 pt-6 border-t border-[var(--border-color)] flex justify-end">
            <button class="flex items-center gap-2.5 bg-gradient-to-r from-[#3B82F6] to-[#2563EB] hover:from-[#2563EB] hover:to-[#1D4ED8] text-white px-10 py-3.5 rounded-xl text-xs font-bold transition-all duration-300 shadow-[0_10px_25px_-5px_rgba(59,130,246,0.5)] hover:shadow-[0_15px_30px_rgba(59,130,246,0.6)] hover:-translate-y-1">
                <i class="fas fa-save text-sm"></i> 
                <span>Guardar Cambios</span>
            </button>
        </div>

    </div>
</div>

<div id="employeeModal" class="fixed inset-0 bg-black/80 modo-crema:bg-zinc-900/70 backdrop-blur-md z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300">
    <div class="relative bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2.5rem] p-10 w-full max-w-[460px] shadow-[0_30px_70px_-10px_rgba(0,0,0,0.8)] modo-crema:shadow-[0_20px_40px_-5px_rgba(0,0,0,0.08)] transform scale-95 transition-all duration-300 overflow-visible" id="modalContent">
        
        <div class="absolute top-[-10%] left-[-10%] w-52 h-52 rounded-full bg-blue-600/10 modo-crema:bg-blue-600/5 blur-[90px] pointer-events-none z-0"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-48 h-48 rounded-full bg-orange-600/10 modo-crema:bg-orange-600/5 blur-[90px] pointer-events-none z-0"></div>

        <div class="flex justify-between items-start mb-10 z-10 relative">
            <div class="flex flex-col">
                <h2 class="text-2xl font-black text-[var(--text-color)] tracking-tight">Nuevo Empleado</h2>
                <p class="text-xs text-[var(--text-muted)] font-medium mt-1">Agrega un nuevo empleado al sistema</p>
            </div>
            <button onclick="closeModal()" class="w-9 h-9 rounded-full bg-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-color)] transition-colors outline-none shadow-inner modo-crema:bg-zinc-100 modo-crema:shadow">
                <i class="fas fa-xmark text-sm"></i>
            </button>
        </div>

        <form action="{{ route('admin.empleados.store') ?? '#' }}" method="POST" class="space-y-7 z-10 relative">
            @csrf
            
            <div>
                <label for="nombre_completo" class="text-[10px] font-black text-[var(--text-muted)] opacity-80 uppercase tracking-widest mb-3 block">Nombre Completo</label>
                <input type="text" id="nombre_completo" name="nombre" placeholder="Ej: Juan Pérez" required class="w-full h-12 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-sm font-medium text-[var(--text-color)] placeholder:text-[var(--text-muted)] focus:border-[#3B82F6] focus:ring-1 focus:ring-[#3B82F6] outline-none transition-all shadow-inner">
            </div>

            <div>
                <label for="pin" class="text-[10px] font-black text-[var(--text-muted)] opacity-80 uppercase tracking-widest mb-3 block">PIN de Acceso</label>
                <input type="text" id="pin" name="codigo_empleado" placeholder="Escribe el código..." required class="w-full h-12 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-sm font-semibold tracking-widest tabular-nums text-[var(--text-color)] placeholder:text-[var(--text-muted)] focus:border-[#3B82F6] focus:ring-1 focus:ring-[#3B82F6] outline-none transition-all shadow-inner">
            </div>

            <div class="relative group" id="dropdownContainer">
                <label class="text-[10px] font-black text-[var(--text-muted)] opacity-80 uppercase tracking-widest mb-3 block">Rol</label>
                
                <input type="hidden" name="rol" id="rol_input" value="admin">

                <button type="button" onclick="toggleDropdown()" id="dropdownBtn" class="flex items-center justify-between w-full h-12 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-sm font-bold text-[var(--text-color)] focus:border-[#3B82F6] focus:ring-1 focus:ring-[#3B82F6] transition-all outline-none shadow-inner">
                    <span id="dropdownSelected">Administrador</span>
                    <i class="fas fa-chevron-down text-[var(--text-muted)] transition-transform" id="dropdownIcon"></i>
                </button>
                
                <div id="dropdownMenu" class="absolute top-full left-0 mt-2 w-full max-h-48 overflow-y-auto bg-[var(--card-color)] border border-[var(--border-color)] rounded-xl shadow-2xl z-50 py-2.5 opacity-0 pointer-events-none transform -translate-y-2 transition-all duration-200">
                    @php
                        $rolesList = ['Administrador' => 'admin', 'Capitán' => 'capitan', 'Mesero' => 'mesero', 'Cocinero' => 'Cocinero', 'Cajero' => 'cajero'];
                    @endphp
                    @foreach($rolesList as $name => $tech)
                    <button type="button" onclick="selectRole('{{ $name }}', '{{ $tech }}')" class="role-option flex items-center justify-between w-full text-left px-5 py-3 text-sm font-medium text-[var(--text-muted)] hover:text-[var(--text-color)] hover:bg-black/5 modo-crema:hover:bg-zinc-100 transition-all">
                        <span>{{ $name }}</span>
                        <i class="fas fa-check text-[#3B82F6] {{ $name == 'Administrador' ? 'opacity-100' : 'opacity-0' }} ml-3"></i>
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-10 pt-4 border-t border-[var(--border-color)]">
                <button type="button" onclick="closeModal()" class="px-7 py-3 rounded-xl text-xs font-bold text-[var(--text-muted)] hover:text-[var(--text-color)] bg-[var(--border-color)] hover:bg-black/10 transition-all outline-none shadow-inner modo-crema:bg-zinc-100 modo-crema:hover:bg-zinc-200">
                    Cancelar
                </button>
                <button type="submit" class="px-8 py-3 rounded-xl text-xs font-bold bg-[#3B82F6] text-white hover:bg-[#3B82F6]/90 shadow-[0_8px_20px_-6px_rgba(59,130,246,0.5)] hover:-translate-y-0.5 transition-all outline-none">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 bg-black/80 modo-crema:bg-zinc-900/70 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden opacity-100 pointer-events-auto transition-all duration-300">
    <div class="relative bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2.5rem] p-10 w-full max-w-[400px] shadow-[0_30px_70px_-10px_rgba(0,0,0,0.8)] modo-crema:shadow-[0_20px_40px_-5px_rgba(0,0,0,0.08)] transform transition-all duration-300">
        
        <div class="absolute top-[-10%] left-[-10%] w-48 h-48 rounded-full bg-rose-600/10 modo-crema:bg-rose-600/5 blur-[80px] pointer-events-none z-0"></div>

        <div class="flex flex-col items-center text-center relative z-10">
            <div class="w-16 h-16 rounded-full bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500 mb-6 shadow-inner">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            
            <h2 class="text-xl font-black text-[var(--text-color)] tracking-tight mb-2">¿Eliminar Empleado?</h2>
            <p class="text-xs text-[var(--text-muted)] font-medium mb-6 leading-relaxed">
                Estás a punto de eliminar a <br>
                <span id="nombreEmpleadoEliminar" class="font-bold text-[var(--text-color)] text-sm block mt-1"></span>
            </p>
            <p class="text-[10px] text-rose-500/80 font-bold uppercase tracking-widest bg-rose-500/5 px-4 py-2 rounded-lg mb-8 border border-rose-500/10">Esta acción no se puede deshacer</p>

            <div class="flex w-full gap-3">
                <button type="button" onclick="cerrarModalEliminar()" class="flex-1 py-3.5 rounded-xl text-xs font-bold text-[var(--text-muted)] hover:text-[var(--text-color)] bg-[var(--border-color)] hover:bg-black/10 transition-all outline-none shadow-inner modo-crema:bg-zinc-100 modo-crema:hover:bg-zinc-200">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmarEliminar" class="flex-1 py-3.5 rounded-xl text-xs font-bold bg-rose-500 text-white hover:bg-rose-600 shadow-[0_8px_20px_-6px_rgba(244,63,94,0.5)] hover:-translate-y-0.5 transition-all outline-none">
                    Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Modal Logic
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
            dropdownBtn.classList.remove('border-[#3B82F6]', 'ring-1', 'ring-[#3B82F6]');
        } else {
            dropdownMenu.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-2');
            dropdownIcon.classList.add('rotate-180', 'text-[#3B82F6]');
            dropdownBtn.classList.add('border-[#3B82F6]', 'ring-1', 'ring-[#3B82F6]');
        }
    }

    function selectRole(name, techValue) {
        document.getElementById('dropdownSelected').innerText = name;
        document.getElementById('rol_input').value = techValue;
        
        const options = document.querySelectorAll('.role-option');
        options.forEach(opt => {
            const spanText = opt.querySelector('span').innerText;
            const check = opt.querySelector('.fa-check');
            if (spanText === name) {
                opt.classList.replace('font-medium', 'font-bold');
                opt.classList.replace('text-[var(--text-muted)]', 'text-[var(--text-color)]');
                check.classList.replace('opacity-0', 'opacity-100');
            } else {
                opt.classList.replace('font-bold', 'font-medium');
                opt.classList.replace('text-[var(--text-color)]', 'text-[var(--text-muted)]');
                check.classList.replace('opacity-100', 'opacity-0');
            }
        });
        toggleDropdown();
    }

    document.addEventListener('click', (e) => {
        const container = document.getElementById('dropdownContainer');
        if (container && !container.contains(e.target) && !dropdownMenu.classList.contains('opacity-0')) toggleDropdown();
    });

    let formIdParaEliminar = null;

    function confirmarEliminacion(id, nombre) {
        formIdParaEliminar = 'delete-form-' + id;
        const txtNombre = document.getElementById('nombreEmpleadoEliminar');
        if(txtNombre) txtNombre.innerText = nombre;
        const delModal = document.getElementById('deleteModal');
        if(delModal) delModal.classList.remove('hidden');
    }

    function cerrarModalEliminar() {
        const delModal = document.getElementById('deleteModal');
        if(delModal) delModal.classList.add('hidden');
        formIdParaEliminar = null;
    }

    const btnConfirmar = document.getElementById('btnConfirmarEliminar');
    if(btnConfirmar) {
        btnConfirmar.addEventListener('click', function() {
            if (formIdParaEliminar) {
                document.getElementById(formIdParaEliminar).submit();
            }
        });
    }
</script>
@endpush