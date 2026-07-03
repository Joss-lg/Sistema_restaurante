<div id="employeeModal" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100] flex items-center justify-center p-4 hidden opacity-0 transition-all duration-500">
    <div id="modalContent" class="modal-inverso relative bg-[var(--m-bg,#ffffff)] border border-[var(--m-border,#e2e8f0)] rounded-[2.5rem] p-10 w-full max-w-[480px] transform scale-95 transition-all duration-500" style="box-shadow: var(--m-shadow);">
        
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-[var(--m-glow,rgba(0,0,0,0.02))] rounded-full blur-3xl pointer-events-none"></div>

        <button type="button" onclick="closeModal()" class="absolute top-8 right-8 text-[var(--m-muted,#71717a)] hover:text-[var(--m-text,#09090b)] hover:rotate-90 transition-all duration-300 outline-none z-10">
            <i class="fas fa-times text-xl"></i>
        </button>

        <div class="mb-10 relative z-10">
            {{-- Si existe $usuario, es edición. Si no, es registro. --}}
            <h2 class="text-3xl font-black text-[var(--m-text,#09090b)] tracking-tighter">
                {{ isset($usuario) ? 'Editar Perfil' : 'Registrar Perfil' }}
            </h2>
            <p class="text-[11px] font-bold text-[var(--m-muted,#71717a)] uppercase tracking-[0.2em] mt-2 opacity-80">
                {{ isset($usuario) ? 'Actualización de credenciales' : 'Nueva credencial de acceso' }}
            </p>
        </div>

        {{-- Ajuste de acción para permitir edición o creación --}}
        <form action="{{ isset($usuario) ? route('admin.empleados.update', $usuario->id) : route('admin.empleados.store') }}" method="POST" class="p-8 pt-4 space-y-6">
            @csrf
            @if(isset($usuario)) @method('PUT') @endif

            {{-- Nombre --}}
            <div class="group">
                <label for="nombre" class="text-[10px] font-black text-[var(--m-text,#09090b)] uppercase tracking-[0.2em] mb-3 block opacity-90">Nombre Completo</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $usuario->nombre ?? '') }}" required placeholder="Ej. Juan Pérez" class="w-full h-14 bg-[var(--m-input-bg,#f8fafc)] border border-[var(--m-border,#e2e8f0)] rounded-2xl px-6 font-bold text-[var(--m-text,#09090b)] outline-none focus:border-[#3B82F6]">
            </div>

            {{-- Rol del Sistema --}}
            <div class="relative group" id="dropdownContainer">
                <label class="text-[10px] font-black text-[var(--m-text,#09090b)] uppercase tracking-[0.2em] mb-3 block opacity-90">Rol del Sistema</label>
                <input type="hidden" name="rol_id" id="rol_id_input" value="{{ $usuario->rol_id ?? '' }}" required>
                <button type="button" onclick="toggleDropdown(event)" id="dropdownBtn" class="flex items-center justify-between w-full h-14 bg-[var(--m-input-bg,#f8fafc)] border border-[var(--m-border,#e2e8f0)] rounded-2xl pl-5 pr-6 text-sm font-bold text-[var(--m-text,#09090b)] outline-none">
                    <span id="dropdownSelected">{{ isset($usuario) ? $usuario->rol->nombre : 'Seleccionar...' }}</span>
                    <i class="fas fa-chevron-down" id="dropdownIcon"></i>
                </button>
                <div id="dropdownMenu" class="absolute w-full bg-[var(--m-drop-bg,#ffffff)] border border-[var(--m-border,#e2e8f0)] rounded-2xl shadow-2xl z-[110] py-2 hidden mt-2">
                    @foreach($roles ?? [] as $rol)
                        <button type="button" onclick="selectRole('{{ $rol->nombre }}', '{{ $rol->id }}')" class="w-full px-6 py-3 text-left hover:bg-[var(--m-drop-hover,#f1f5f9)]">{{ $rol->nombre }}</button>
                    @endforeach
                </div>
            </div>

            {{-- Switch de Acceso --}}
            <div class="flex items-center justify-between p-4 bg-[var(--m-input-bg,#f8fafc)] rounded-2xl border border-[var(--m-border,#e2e8f0)]">
                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-[var(--m-text,#09090b)] uppercase tracking-[0.2em]">Acceso al Sistema</label>
                    <p class="text-[9px] text-[var(--m-muted,#71717a)]">¿Este empleado usará la plataforma?</p>
                </div>
                {{-- LÓGICA DE PROTECCIÓN: Si es ID 1, ponemos 'disabled' --}}
                <input type="checkbox" 
                       id="toggleAcceso" 
                       name="puede_acceder_pos" 
                       value="1" 
                       {{ (isset($usuario) && $usuario->esta_activo) ? 'checked' : '' }}
                       {{ (isset($usuario) && $usuario->id == 1) ? 'disabled' : '' }} 
                       onchange="toggleAccesoFields()" 
                       class="toggle-checkbox w-6 h-6 cursor-pointer">
            </div>

            {{-- CONDICIONAL --}}
            <div id="accesoFields" class="{{ (isset($usuario) && $usuario->esta_activo) ? '' : 'hidden' }} space-y-6 transition-all duration-500">
                <div class="group">
                    <label for="codigo_empleado" class="text-[10px] font-black text-[var(--m-text,#09090b)] uppercase tracking-[0.2em] mb-3 block opacity-90">PIN de Seguridad (4 dígitos)</label>
                    <input type="text" id="codigo_empleado" name="codigo_empleado" value="{{ $usuario->codigo_empleado ?? '' }}" maxlength="4" placeholder="0000" class="w-full h-14 bg-[var(--m-input-bg,#f8fafc)] border border-[var(--m-border,#e2e8f0)] rounded-2xl px-6 font-black tracking-[0.8em] text-[var(--m-text,#09090b)] outline-none focus:border-[#3B82F6]">
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-12 pt-8 border-t border-[var(--m-border,#e2e8f0)]">
                <button type="button" onclick="closeModal()" class="px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-[var(--m-muted,#71717a)]">Cancelar</button>
                <button type="submit" class="px-10 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest bg-[var(--m-btn-bg,#0f172a)] text-[var(--m-btn-text,#ffffff)]">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Inicializar visualización de campos al cargar (si estás editando)
    document.addEventListener('DOMContentLoaded', () => {
        toggleAccesoFields();
    });

    function toggleAccesoFields() {
        const checkbox = document.getElementById('toggleAcceso');
        const fields = document.getElementById('accesoFields');
        // Si el checkbox está marcado, mostramos los campos
        if (checkbox.checked) {
            fields.classList.remove('hidden');
        } else {
            fields.classList.add('hidden');
        }
    }

    function openModal() {
        document.getElementById('employeeModal').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('employeeModal').classList.remove('opacity-0');
            document.getElementById('modalContent').classList.remove('scale-95');
        }, 10);
    }

    function closeModal() {
        document.getElementById('employeeModal').classList.add('opacity-0');
        document.getElementById('modalContent').classList.add('scale-95');
        setTimeout(() => document.getElementById('employeeModal').classList.add('hidden'), 500);
    }

    function toggleDropdown(event) {
        event.stopPropagation();
        document.getElementById('dropdownMenu').classList.toggle('hidden');
    }

    function selectRole(nombre, id) {
        document.getElementById('dropdownSelected').innerText = nombre;
        document.getElementById('rol_id_input').value = id;
        document.getElementById('dropdownMenu').classList.add('hidden');
    }

    window.addEventListener('click', () => document.getElementById('dropdownMenu').classList.add('hidden'));
</script>