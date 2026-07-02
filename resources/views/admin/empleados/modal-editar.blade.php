<div id="editEmpleadoModal" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100] flex items-center justify-center p-4 hidden opacity-0 transition-all duration-500">
    <div class="modal-inverso relative bg-[var(--m-bg)] border border-[var(--m-border)] rounded-[2.5rem] p-10 w-full max-w-[480px] transform scale-95 transition-all duration-500" style="box-shadow: var(--m-shadow);" id="editModalContent">
        
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-[var(--m-glow)] rounded-full blur-3xl pointer-events-none"></div>

        <button type="button" onclick="cerrarEditModal()" class="absolute top-8 right-8 text-[var(--m-muted)] hover:text-[var(--m-text)] hover:rotate-90 transition-all duration-300 outline-none z-10">
            <i class="fas fa-times text-xl"></i>
        </button>

        <div class="mb-10 relative z-10">
            <h2 class="text-3xl font-black text-[var(--m-text)] tracking-tighter">Editar Perfil</h2>
            <p class="text-[11px] font-bold text-[var(--m-muted)] uppercase tracking-[0.2em] mt-2 opacity-80">Actualización de credenciales</p>
        </div>

        <form id="formEditar" action="#" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            {{-- Nombre --}}
            <div class="group">
                <label for="edit_nombre" class="text-[10px] font-black text-[var(--m-text)] uppercase tracking-[0.2em] mb-3 block opacity-90">Nombre Completo</label>
                <input type="text" id="edit_nombre" name="nombre" required class="w-full h-14 bg-[var(--m-input-bg)] border border-[var(--m-border)] rounded-2xl px-6 font-bold text-[var(--m-text)] outline-none focus:border-[#3B82F6]">
            </div>

            {{-- Switch de Acceso --}}
            <div class="flex items-center justify-between p-4 bg-[var(--m-input-bg)] rounded-2xl border border-[var(--m-border)]">
                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-[var(--m-text)] uppercase tracking-[0.2em]">Acceso al Sistema</label>
                    <p class="text-[9px] text-[var(--m-muted)]">¿Este empleado usará la plataforma?</p>
                </div>
                <input type="checkbox" id="edit_toggleAcceso" name="puede_acceder_pos" value="1" onchange="toggleEditAccesoFields()" class="toggle-checkbox w-6 h-6 cursor-pointer">
            </div>

            {{-- Contenedor Condicional --}}
            <div id="edit_accesoFields" class="hidden space-y-6 transition-all duration-500">
                <div class="group">
                    <label for="edit_codigo_empleado" class="text-[10px] font-black text-[var(--m-text)] uppercase tracking-[0.2em] mb-3 block opacity-90">PIN de Seguridad (4 dígitos)</label>
                    <input type="text" id="edit_codigo_empleado" name="codigo_empleado" maxlength="4" class="w-full h-14 bg-[var(--m-input-bg)] border border-[var(--m-border)] rounded-2xl px-6 font-black tracking-[0.8em] text-[var(--m-text)] outline-none focus:border-[#3B82F6]">
                </div>

                <div class="relative group" id="editDropdownContainer">
                    <label class="text-[10px] font-black text-[var(--m-text)] uppercase tracking-[0.2em] mb-3 block opacity-90">Rol del Sistema</label>
                    <input type="hidden" name="rol_id" id="edit_rol_id_input">
                    <button type="button" onclick="toggleEditDropdown(event)" id="editDropdownBtn" class="flex items-center justify-between w-full h-14 bg-[var(--m-input-bg)] border border-[var(--m-border)] rounded-2xl pl-5 pr-6 text-sm font-bold text-[var(--m-text)] outline-none">
                        <span id="editDropdownSelected">Seleccionar...</span>
                        <i class="fas fa-chevron-down" id="editDropdownIcon"></i>
                    </button>
                    <div id="editDropdownMenu" class="absolute w-full bg-[var(--m-drop-bg)] border border-[var(--m-border)] rounded-2xl shadow-2xl z-[110] py-2 hidden mt-2">
                        @foreach($roles ?? [] as $rol)
                            <button type="button" onclick="selectEditRole('{{ $rol->nombre }}', '{{ $rol->id }}')" class="w-full px-6 py-3 text-left hover:bg-[var(--m-drop-hover)]">{{ $rol->nombre }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-12 pt-8 border-t border-[var(--m-border)]">
                <button type="button" onclick="cerrarEditModal()" class="px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-[var(--m-muted)] hover:text-[var(--m-text)] hover:bg-[var(--m-input-bg)] transition-all">Cancelar</button>
                <button type="submit" class="px-10 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest bg-[var(--m-btn-bg)] text-[var(--m-btn-text)]">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleEditAccesoFields() {
        const checkbox = document.getElementById('edit_toggleAcceso');
        const fields = document.getElementById('edit_accesoFields');
        fields.classList.toggle('hidden', !checkbox.checked);
    }

    function toggleEditDropdown(event) {
        event.stopPropagation();
        document.getElementById('editDropdownMenu').classList.toggle('hidden');
    }

    function selectEditRole(nombre, id) {
        document.getElementById('editDropdownSelected').innerText = nombre;
        document.getElementById('edit_rol_id_input').value = id;
        document.getElementById('editDropdownMenu').classList.add('hidden');
    }

    function cerrarEditModal() {
        const modal = document.getElementById('editEmpleadoModal');
        const content = document.getElementById('editModalContent');
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 500);
    }

    // Cambiado a prepararModalEditar para coincidir con index.blade.php
    window.prepararModalEditar = function(id, nombre, codigo, rolId, rolNombre, tieneAcceso) {
        const modal = document.getElementById('editEmpleadoModal');
        const content = document.getElementById('editModalContent');
        const form = document.getElementById('formEditar');
        const toggle = document.getElementById('edit_toggleAcceso');

        form.action = `/admin/empleados/${id}`;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_codigo_empleado').value = codigo || '';
        document.getElementById('edit_rol_id_input').value = rolId || '';
        document.getElementById('editDropdownSelected').innerText = rolNombre || 'Seleccionar...';
        
        toggle.checked = (tieneAcceso == 1);
        toggleEditAccesoFields();

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        }, 10);
    };

    window.addEventListener('click', () => {
        document.getElementById('editDropdownMenu').classList.add('hidden');
    });
</script>