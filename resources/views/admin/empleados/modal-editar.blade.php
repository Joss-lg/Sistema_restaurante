<div id="editEmpleadoModal" class="fixed inset-0 bg-black/75 backdrop-blur-md z-[99999] flex items-center justify-center p-4 hidden opacity-0 transition-all duration-500">
    
    {{-- Contenedor principal sin !bg-white que bloqueaba el tema --}}
    <div class="relative bg-white dark:bg-[#121318] border border-gray-100 dark:border-white/5 rounded-[2rem] p-6 sm:p-8 md:p-10 w-full max-w-[480px] transform scale-95 transition-all duration-500 shadow-2xl flex flex-col max-h-[85vh] sm:max-h-[90vh]" id="editModalContent">
        
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 dark:bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>

        {{-- BOTÓN CERRAR (X) CORREGIDO --}}
        <button type="button" onclick="window.cerrarEditModal()" class="absolute top-5 right-5 sm:top-8 sm:right-8 text-gray-400 hover:text-gray-900 dark:hover:text-white hover:rotate-90 transition-all duration-300 outline-none z-50 bg-gray-100 dark:bg-zinc-800 sm:bg-transparent rounded-full w-9 h-9 flex items-center justify-center cursor-pointer">
            <i class="fas fa-times text-lg sm:text-xl pointer-events-none"></i>
        </button>

        <div class="mb-6 sm:mb-8 relative z-10 flex-shrink-0 pt-2 sm:pt-0">
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tighter">Editar Perfil</h2>
            <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mt-1 sm:mt-2">Actualización de credenciales</p>
        </div>

        <form id="formEditar" action="#" method="POST" class="space-y-5 sm:space-y-6 overflow-y-auto flex-1 pb-2 overscroll-contain scrollbar-thin" style="-webkit-overflow-scrolling: touch;">
            @csrf
            @method('PUT')
            
            {{-- AVISO DE PROTECCIÓN --}}
            <div id="alertaProteccion" class="hidden mb-4 sm:mb-6 p-3 sm:p-4 bg-blue-50/60 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 rounded-xl sm:rounded-2xl flex items-start gap-3">
                <i class="fas fa-shield-check text-blue-600 dark:text-blue-500 text-base sm:text-lg mt-0.5"></i>
                <div>
                    <p class="text-[9px] sm:text-[10px] font-black text-blue-700 dark:text-blue-500 uppercase tracking-widest">Protección de Cuenta</p>
                    <p class="text-[11px] sm:text-xs font-medium text-blue-600 dark:text-blue-400 mt-1">Por seguridad, no puedes quitarte el acceso ni cambiarte tu propio rol.</p>
                </div>
            </div>

            {{-- Nombre (Con Icono) --}}
            <div class="group">
                <label for="edit_nombre" class="text-[9px] sm:text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-[0.2em] mb-2 sm:mb-3 block">Nombre Completo</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                    <input type="text" id="edit_nombre" name="nombre" required 
                        class="w-full h-12 sm:h-14 bg-gray-50 dark:bg-black/40 border border-gray-200 dark:border-white/5 rounded-xl sm:rounded-2xl pl-11 pr-4 sm:pr-6 text-sm font-bold text-gray-900 dark:text-white outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 hover:border-gray-300 dark:hover:border-white/10 transition-all duration-300">
                </div>
            </div>

            {{-- Switch de Acceso --}}
            <div id="cajaAcceso" class="flex items-center justify-between p-3 sm:p-4 bg-gray-50 dark:bg-black/40 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-white/5 hover:border-gray-300 dark:hover:border-white/10 transition-all duration-300 group">
                <div class="flex flex-col pointer-events-none pr-4">
                    <span class="text-[9px] sm:text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-[0.2em]">Acceso al Sistema</span>
                    <p class="text-[8px] sm:text-[9px] text-gray-400 dark:text-gray-500 mt-0.5">¿Este empleado usará la plataforma?</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                    <input type="checkbox" id="edit_toggleAcceso" name="puede_acceder_pos" value="1" onchange="toggleEditAccesoFields()" class="sr-only peer">
                    <div class="w-11 sm:w-12 h-6 bg-gray-300 dark:bg-zinc-700 rounded-full peer 
                                peer-checked:after:translate-x-[20px] sm:peer-checked:after:translate-x-[24px] peer-checked:after:border-white 
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                                after:bg-white after:border border-gray-300 dark:after:border-zinc-600 
                                after:rounded-full after:h-5 after:w-5 
                                after:transition-all after:duration-300 
                                peer-active:after:w-6 
                                peer-checked:bg-blue-500 peer-checked:shadow-[0_0_12px_rgba(59,130,246,0.4)]
                                transition-all duration-300"></div>
                </label>
            </div>

            {{-- Contenedor Condicional (PIN y ROL) --}}
            <div id="edit_accesoFields" class="hidden space-y-5 sm:space-y-6 transition-all duration-500">
                
                {{-- PIN (Con Icono) --}}
                <div class="group">
                    <label for="edit_codigo_empleado" class="text-[9px] sm:text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-[0.2em] mb-2 sm:mb-3 block">PIN de Seguridad (4 dígitos)</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                        <input type="text" id="edit_codigo_empleado" name="codigo_empleado" maxlength="4" 
                            class="w-full h-12 sm:h-14 bg-gray-50 dark:bg-black/40 border border-gray-200 dark:border-white/5 rounded-xl sm:rounded-2xl pl-11 pr-4 sm:pr-6 font-black tracking-[0.8em] text-base sm:text-lg text-gray-900 dark:text-white outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 hover:border-gray-300 dark:hover:border-white/10 transition-all duration-300">
                    </div>
                </div>

                {{-- Rol (Con Dropdown Personalizado) --}}
                <div class="relative group" id="cajaRol">
                    <label class="text-[9px] sm:text-[10px] font-black text-gray-800 dark:text-gray-200 uppercase tracking-[0.2em] mb-2 sm:mb-3 block">Rol del Sistema</label>
                    <input type="hidden" name="rol_id" id="edit_rol_id_input">
                    <button type="button" onclick="toggleEditDropdown(event)" id="editDropdownBtn" 
                        class="flex items-center justify-between w-full h-12 sm:h-14 bg-gray-50 dark:bg-black/40 border border-gray-200 dark:border-white/5 rounded-xl sm:rounded-2xl pl-4 pr-4 sm:pr-6 text-xs sm:text-sm font-bold text-gray-900 dark:text-white outline-none hover:border-gray-300 dark:hover:border-white/10 transition-all duration-300">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shield-alt text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                            <span id="editDropdownSelected">Seleccionar...</span>
                        </div>
                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                    </button>
                    
                    {{-- Dropdown Menu --}}
                    <div id="editDropdownMenu" class="absolute w-full bg-white dark:bg-[#1e2028] border border-gray-200 dark:border-white/5 rounded-xl sm:rounded-2xl shadow-xl z-[110] py-2 hidden mt-2">
                        @foreach($roles ?? [] as $rol)
                            <button type="button" onclick="selectEditRole('{{ $rol->nombre }}', '{{ $rol->id }}')" 
                                class="w-full px-4 sm:px-6 py-2.5 sm:py-3 text-left text-xs sm:text-sm hover:bg-gray-50 dark:hover:bg-white/5 font-semibold text-gray-800 dark:text-gray-200 transition-colors">
                                {{ $rol->nombre }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Botones Inferiores --}}
            <div class="flex justify-between items-center mt-6 sm:mt-8 pt-6 sm:pt-8 border-t border-gray-100 dark:border-white/5 flex-shrink-0">
                <button type="button" onclick="window.cerrarEditModal()" class="px-2 py-3 sm:py-4 text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors outline-none">
                    Cancelar
                </button>
                <button type="submit" class="px-6 sm:px-10 py-3 sm:py-4 rounded-xl sm:rounded-2xl text-[9px] sm:text-[10px] font-black uppercase tracking-widest bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-700 text-white shadow-lg hover:shadow-blue-500/10 hover:-translate-y-0.5 active:translate-y-0 active:scale-95 transition-all duration-300 outline-none">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const idUsuarioActual = {{ auth()->id() ?? 'null' }};

    function toggleEditAccesoFields() {
        const checkbox = document.getElementById('edit_toggleAcceso');
        const fields = document.getElementById('edit_accesoFields');
        if(checkbox && fields) {
            fields.classList.toggle('hidden', !checkbox.checked);
        }
    }

    function toggleEditDropdown(event) {
        event.stopPropagation();
        const menu = document.getElementById('editDropdownMenu');
        if(menu) {
            menu.classList.toggle('hidden');
        }
    }

    function selectEditRole(nombre, id) {
        document.getElementById('editDropdownSelected').innerText = nombre;
        document.getElementById('edit_rol_id_input').value = id;
        document.getElementById('editDropdownMenu').classList.add('hidden');
    }

    // Aseguramos que cerrarEditModal esté en el entorno global (window)
    window.cerrarEditModal = function() {
        const modal = document.getElementById('editEmpleadoModal');
        const content = document.getElementById('editModalContent');
        
        if (modal && content) {
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 500);
        }
    };

    window.prepararModalEditar = function(id, nombre, codigo, rolId, rolNombre, tieneAcceso) {
        const modal = document.getElementById('editEmpleadoModal');
        const content = document.getElementById('editModalContent');
        const form = document.getElementById('formEditar');
        const toggle = document.getElementById('edit_toggleAcceso');
        
        const alertaProteccion = document.getElementById('alertaProteccion');
        const cajaAcceso = document.getElementById('cajaAcceso');
        const cajaRol = document.getElementById('cajaRol');

        if(form) form.action = `/admin/empleados/${id}`;
        
        if(document.getElementById('edit_nombre')) document.getElementById('edit_nombre').value = nombre;
        if(document.getElementById('edit_codigo_empleado')) document.getElementById('edit_codigo_empleado').value = codigo || '';
        if(document.getElementById('edit_rol_id_input')) document.getElementById('edit_rol_id_input').value = rolId || '';
        if(document.getElementById('editDropdownSelected')) document.getElementById('editDropdownSelected').innerText = rolNombre || 'Seleccionar...';
        
        if(toggle) {
            toggle.checked = (tieneAcceso == 1);
            toggleEditAccesoFields();
        }

        if (idUsuarioActual && parseInt(id) === idUsuarioActual) {
            if(alertaProteccion) alertaProteccion.classList.remove('hidden');
            if(cajaAcceso) cajaAcceso.classList.add('pointer-events-none', 'opacity-50', 'grayscale');
            if(cajaRol) cajaRol.classList.add('pointer-events-none', 'opacity-50', 'grayscale');
        } else {
            if(alertaProteccion) alertaProteccion.classList.add('hidden');
            if(cajaAcceso) cajaAcceso.classList.remove('pointer-events-none', 'opacity-50', 'grayscale');
            if(cajaRol) cajaRol.classList.remove('pointer-events-none', 'opacity-50', 'grayscale');
        }

        if(modal && content) {
            modal.classList.remove('hidden');
            // Retraso minúsculo para que la transición CSS funcione al quitar el "hidden"
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
            }, 10);
        }
    };

    // Cerramos el menú dropdown si se hace click fuera
    window.addEventListener('click', () => {
        const menu = document.getElementById('editDropdownMenu');
        if (menu && !menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
        }
    });
</script>