<style>
    /* Solo aplicamos el truco de subir el modal en pantallas grandes (computadoras/punto de venta) */
    @media (min-width: 768px) {
        /* 1. Mandamos el modal a la parte de arriba de la pantalla */
        body.teclado-virtual-abierto #modalCrearEmpleado {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        
        /* 2. Hacemos que el modal sea más corto para que no choque con el teclado y active el scroll interno */
        body.teclado-virtual-abierto #modalCrearContent {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important; 
        }
    }
</style>

{{-- CONTENEDOR PRINCIPAL DEL MODAL (Fondo oscuro translúcido) --}}
{{-- Se cambió a z-[99999] para romper el bloqueo del menú lateral en celulares --}}
<div id="modalCrearEmpleado" class="fixed inset-0 z-[99999] hidden bg-black/75 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 transition-all duration-300">
    
    {{-- Tarjeta blanca/oscura del Modal --}}
    <div class="bg-white dark:bg-[#111315] border border-gray-200 dark:border-gray-800 w-full max-w-md rounded-3xl sm:rounded-[2rem] shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[88dvh] sm:max-h-[90dvh]" id="modalCrearContent">
        
        <form id="formCrearEmpleado" method="POST" action="{{ route('admin.empleados.store') }}" class="flex flex-col h-full relative z-10 overflow-hidden bg-white dark:bg-[#111315]">
            @csrf
            
            {{-- Cuerpo del modal (Con Scroll táctil optimizado para celulares) --}}
            <div class="p-5 sm:p-8 overflow-y-auto flex-1 space-y-5 bg-white dark:bg-[#111315] overscroll-contain scrollbar-thin" style="-webkit-overflow-scrolling: touch;">
                
                {{-- Encabezado --}}
                <div class="flex justify-between items-start pb-2 gap-3">
                    <div class="min-w-0">
                        <h2 class="text-lg sm:text-xl font-black text-gray-800 dark:text-gray-100 tracking-tight flex items-center gap-2 flex-wrap">
                            Registrar Perfil <i class="fas fa-user-plus text-blue-600 dark:text-blue-500 text-base"></i>
                        </h2>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-0.5">Nueva credencial de acceso</p>
                    </div>
                    <button type="button" onclick="cerrarModalCrear()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800/60 flex-shrink-0 outline-none">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                {{-- Campo: Nombre Completo (TECLADO VIRTUAL DE TEXTO) --}}
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Nombre Completo</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400 dark:text-gray-500 group-focus-within:text-blue-600 transition-colors text-sm"></i>
                        </div>
                        <input type="text" name="nombre" id="crear_nombre" required placeholder="Ej. Juan Pérez" autocomplete="off"
                            readonly data-teclado="texto"
                            class="w-full h-12 bg-gray-50 dark:bg-[#1a1d20] border border-gray-300 dark:border-gray-700 rounded-xl pl-11 pr-4 text-base sm:text-sm font-semibold text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all shadow-sm">
                    </div>
                </div>

                {{-- Campo: Rol del Sistema --}}
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Rol del Sistema</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-shield-alt text-gray-400 dark:text-gray-500 group-focus-within:text-blue-600 transition-colors text-sm"></i>
                        </div>
                        <select name="rol_id" id="crear_rol_id" required 
                            class="w-full h-12 bg-gray-50 dark:bg-[#1a1d20] border border-gray-300 dark:border-gray-700 rounded-xl pl-11 pr-10 text-base sm:text-sm font-semibold text-gray-800 dark:text-gray-100 appearance-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all cursor-pointer shadow-sm">
                            <option value="" disabled selected class="text-gray-400 dark:text-gray-500">Seleccionar rol...</option>
                            @foreach($roles ?? [] as $rol)
                                <option value="{{ $rol->id }}" class="text-gray-800 dark:text-gray-200 bg-white dark:bg-[#1a1d20]">{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 dark:text-gray-500 text-[10px]"></i>
                        </div>
                    </div>
                </div>

                {{-- Switch: Acceso al Sistema --}}
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-[#1a1d20] border border-gray-200 dark:border-gray-800/80 rounded-xl shadow-sm gap-3">
                    <div class="min-w-0 flex-1">
                        <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider leading-tight">Acceso al sistema</label>
                        <p class="text-[11px] font-medium text-gray-400 dark:text-gray-500 mt-0.5 truncate">¿Usará la plataforma?</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 select-none">
                        <input type="checkbox" name="puede_acceder_pos" id="crear_acceso" value="1" onchange="toggleCrearAccesoFields()" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                {{-- Contenedor dinámico: PIN (TECLADO VIRTUAL NUMÉRICO) --}}
                <div id="crear_accesoFields" class="hidden transition-all duration-300 space-y-2">
                    <label class="block text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wider">PIN de Seguridad (4 dígitos)</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400 dark:text-gray-500 group-focus-within:text-blue-500 transition-colors text-sm"></i>
                        </div>
                        <input type="password" name="codigo_empleado" id="crear_codigo" maxlength="4" pattern="[0-9]*" placeholder="••••" autocomplete="new-password"
                            readonly data-teclado="numerico" data-teclado-max="4" inputmode="none"
                            class="w-full h-12 bg-gray-50 dark:bg-[#1a1d20] border border-gray-300 dark:border-gray-700 rounded-xl pl-11 pr-4 text-lg tracking-[0.4em] font-black text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all shadow-sm">
                    </div>
                </div>
            </div>

            {{-- Barra inferior de acciones --}}
            <div class="px-5 sm:px-6 py-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-[#15181b] flex-shrink-0" style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                <button type="button" onclick="cerrarModalCrear()" class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors outline-none">
                    Cancelar
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md active:scale-95 outline-none">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleCrearAccesoFields() {
        const checkbox = document.getElementById('crear_acceso');
        const fields = document.getElementById('crear_accesoFields');
        const inputPin = document.getElementById('crear_codigo');
        
        if (checkbox && fields && inputPin) {
            if (checkbox.checked) {
                fields.classList.remove('hidden');
                inputPin.setAttribute('required', 'required');
            } else {
                fields.classList.add('hidden');
                inputPin.removeAttribute('required');
                inputPin.value = '';
            }
        }
    }

    function abrirModalCrear() {
        const form = document.getElementById('formCrearEmpleado');
        if (form) form.reset();
        
        toggleCrearAccesoFields();

        const modal = document.getElementById('modalCrearEmpleado');
        const content = document.getElementById('modalCrearContent');
        
        if (modal && content) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }, 10);
        }
    }

    function cerrarModalCrear() {
        const modal = document.getElementById('modalCrearEmpleado');
        const content = document.getElementById('modalCrearContent');
        
        if (modal && content) {
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }
    }
</script>