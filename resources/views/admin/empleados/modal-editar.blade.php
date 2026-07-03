<div id="editEmpleadoModal" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100] flex items-center justify-center p-4 hidden opacity-0 transition-all duration-500">
    <div class="relative !bg-white dark:!bg-[#121318] border !border-transparent dark:!border-white/5 rounded-[2.5rem] p-8 md:p-10 w-full max-w-[480px] transform scale-95 transition-all duration-500 shadow-2xl" id="editModalContent">
        
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 dark:bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <button type="button" onclick="cerrarEditModal()" class="absolute top-8 right-8 !text-gray-400 hover:!text-gray-900 dark:hover:!text-white hover:rotate-90 transition-all duration-300 outline-none z-10">
            <i class="fas fa-times text-xl"></i>
        </button>

        <div class="mb-8 relative z-10">
            <h2 class="text-3xl font-black !text-gray-900 dark:!text-white tracking-tighter">Editar Perfil</h2>
            <p class="text-[10px] font-bold !text-gray-400 dark:!text-gray-500 uppercase tracking-[0.2em] mt-2">Actualización de credenciales</p>
        </div>

        {{-- AVISO DE PROTECCIÓN --}}
        <div id="alertaProteccion" class="hidden mb-6 p-4 !bg-blue-50 dark:!bg-blue-500/10 border !border-blue-100 dark:!border-blue-500/20 rounded-2xl flex items-start gap-3">
            <i class="fas fa-shield-check !text-blue-500 text-lg mt-0.5"></i>
            <div>
                <p class="text-[10px] font-black !text-blue-600 dark:!text-blue-500 uppercase tracking-widest">Protección de Cuenta</p>
                <p class="text-xs font-medium !text-blue-500 dark:!text-blue-400 mt-1">Por seguridad, no puedes quitarte el acceso ni cambiarte tu propio rol.</p>
            </div>
        </div>

        <form id="formEditar" action="#" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            {{-- Nombre (Con Icono) --}}
            <div class="group">
                <label for="edit_nombre" class="text-[10px] font-black !text-gray-900 dark:!text-white uppercase tracking-[0.2em] mb-3 block">Nombre Completo</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 !text-gray-400 group-focus-within:!text-blue-500 transition-colors"></i>
                    <input type="text" id="edit_nombre" name="nombre" required class="w-full h-14 !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-2xl pl-11 pr-6 font-bold !text-gray-900 dark:!text-white outline-none focus:!border-blue-500 hover:!border-gray-300 dark:hover:!border-white/10 transition-colors duration-300">
                </div>
            </div>

            {{-- Switch de Acceso (Con animación tipo iOS y glow sutil) --}}
            <div id="cajaAcceso" class="flex items-center justify-between p-4 !bg-gray-50 dark:!bg-black/40 rounded-2xl border !border-gray-200 dark:!border-white/5 hover:!border-gray-300 dark:hover:!border-white/10 transition-colors duration-300 group">
                <div class="flex flex-col pointer-events-none">
                    <span class="text-[10px] font-black !text-gray-900 dark:!text-white uppercase tracking-[0.2em]">Acceso al Sistema</span>
                    <p class="text-[9px] !text-gray-500 mt-0.5">¿Este empleado usará la plataforma?</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="edit_toggleAcceso" name="puede_acceder_pos" value="1" onchange="toggleEditAccesoFields()" class="sr-only peer">
                    <div class="w-12 h-6 !bg-gray-300 dark:!bg-zinc-700 rounded-full peer 
                                peer-checked:after:translate-x-full peer-checked:after:!border-white 
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                                after:!bg-white after:border !border-gray-300 dark:after:!border-zinc-600 
                                after:rounded-full after:h-5 after:w-5 
                                after:transition-all after:duration-300 
                                peer-active:after:w-6 peer-checked:peer-active:after:translate-x-[18px]
                                peer-checked:!bg-blue-500 peer-checked:shadow-[0_0_12px_rgba(59,130,246,0.4)]
                                transition-all duration-300"></div>
                </label>
            </div>

            {{-- Contenedor Condicional (PIN y ROL) --}}
            <div id="edit_accesoFields" class="hidden space-y-6 transition-all duration-500">
                
                {{-- PIN (Con Icono) --}}
                <div class="group">
                    <label for="edit_codigo_empleado" class="text-[10px] font-black !text-gray-900 dark:!text-white uppercase tracking-[0.2em] mb-3 block">PIN de Seguridad (4 dígitos)</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 !text-gray-400 group-focus-within:!text-blue-500 transition-colors"></i>
                        <input type="text" id="edit_codigo_empleado" name="codigo_empleado" maxlength="4" class="w-full h-14 !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-2xl pl-11 pr-6 font-black tracking-[0.8em] !text-gray-900 dark:!text-white outline-none focus:!border-blue-500 hover:!border-gray-300 dark:hover:!border-white/10 transition-colors duration-300">
                    </div>
                </div>

                {{-- Rol (Con Icono) --}}
                <div class="relative group" id="cajaRol">
                    <label class="text-[10px] font-black !text-gray-900 dark:!text-white uppercase tracking-[0.2em] mb-3 block">Rol del Sistema</label>
                    <input type="hidden" name="rol_id" id="edit_rol_id_input">
                    <button type="button" onclick="toggleEditDropdown(event)" id="editDropdownBtn" class="flex items-center justify-between w-full h-14 !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-2xl pl-4 pr-6 text-sm font-bold !text-gray-900 dark:!text-white outline-none hover:!border-gray-300 dark:hover:!border-white/10 transition-colors duration-300">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shield-alt !text-gray-400 group-focus-within:!text-blue-500 transition-colors"></i>
                            <span id="editDropdownSelected">Seleccionar...</span>
                        </div>
                        <i class="fas fa-chevron-down !text-gray-400 text-xs"></i>
                    </button>
                    <div id="editDropdownMenu" class="absolute w-full !bg-white dark:!bg-[#1e2028] border !border-gray-200 dark:!border-white/5 rounded-2xl shadow-xl z-[110] py-2 hidden mt-2">
                        @foreach($roles ?? [] as $rol)
                            <button type="button" onclick="selectEditRole('{{ $rol->nombre }}', '{{ $rol->id }}')" class="w-full px-6 py-3 text-left hover:!bg-gray-50 dark:hover:!bg-white/5 font-medium !text-gray-900 dark:!text-white transition-colors">{{ $rol->nombre }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex justify-between items-center mt-12 pt-8 border-t !border-gray-100 dark:!border-white/5">
                <button type="button" onclick="cerrarEditModal()" class="px-2 py-4 text-[10px] font-black uppercase tracking-widest !text-gray-400 hover:!text-gray-900 dark:hover:!text-white transition-colors outline-none">Cancelar</button>
                <button type="submit" class="px-10 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest !bg-slate-900 hover:!bg-slate-800 dark:!bg-[#3B82F6] dark:hover:!bg-[#2563EB] !text-white shadow-lg hover:shadow-blue-500/25 hover:-translate-y-0.5 active:translate-y-0 active:scale-95 transition-all duration-300 outline-none">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
    const idUsuarioActual = {{ auth()->id() }};

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

    window.prepararModalEditar = function(id, nombre, codigo, rolId, rolNombre, tieneAcceso) {
        const modal = document.getElementById('editEmpleadoModal');
        const content = document.getElementById('editModalContent');
        const form = document.getElementById('formEditar');
        const toggle = document.getElementById('edit_toggleAcceso');
        
        const alertaProteccion = document.getElementById('alertaProteccion');
        const cajaAcceso = document.getElementById('cajaAcceso');
        const cajaRol = document.getElementById('cajaRol');

        form.action = `/admin/empleados/${id}`;
        document.getElementById('edit_nombre').value = nombre;
        document.getElementById('edit_codigo_empleado').value = codigo || '';
        document.getElementById('edit_rol_id_input').value = rolId || '';
        document.getElementById('editDropdownSelected').innerText = rolNombre || 'Seleccionar...';
        
        toggle.checked = (tieneAcceso == 1);
        toggleEditAccesoFields();

        if (parseInt(id) === idUsuarioActual) {
            alertaProteccion.classList.remove('hidden');
            cajaAcceso.classList.add('pointer-events-none', 'opacity-50', 'grayscale');
            cajaRol.classList.add('pointer-events-none', 'opacity-50', 'grayscale');
        } else {
            alertaProteccion.classList.add('hidden');
            cajaAcceso.classList.remove('pointer-events-none', 'opacity-50', 'grayscale');
            cajaRol.classList.remove('pointer-events-none', 'opacity-50', 'grayscale');
        }

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