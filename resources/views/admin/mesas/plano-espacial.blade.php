@php
    // Se calculan aquí (no en el controlador) porque este mismo parcial se
    // reutiliza tanto en /mesas (admin) como en /mesero/dashboard, y así
    // funciona sin importar cuál controlador lo esté renderizando.
    $puedeCrearMesa    = auth()->user()->tienePermiso('Mesas', 'crear');
    $puedeEditarMesa   = auth()->user()->tienePermiso('Mesas', 'editar');
    $puedeEliminarMesa = auth()->user()->tienePermiso('Mesas', 'eliminar');
@endphp

{{-- Le pasamos los permisos a mesas.js para que sepa qué botones/acciones habilitar --}}
<script>
    window.permisosMesas = {
        crear: @json($puedeCrearMesa),
        editar: @json($puedeEditarMesa),
        eliminar: @json($puedeEliminarMesa),
    };
</script>

{{-- ESTILOS DIRECTOS PARA EL TECLADO VIRTUAL --}}
<style>
    body.teclado-virtual-abierto #modalCrearMesa {
        align-items: flex-start !important;
        padding-top: 10px !important;
    }

    body.teclado-virtual-abierto #modalCrearMesaContent {
        max-height: 45dvh !important;
        transform: translateY(0) scale(0.95) !important;
    }

    /* Scroll táctil suave (inercia) en iOS */
    #planoContenedor {
        -webkit-overflow-scrolling: touch;
    }

    /* Hoja inferior de propiedades en móvil: oculta fuera de pantalla por
       defecto (translate-y-full) y visible de nuevo como columna fija en
       escritorio gracias a las clases lg:static lg:translate-y-0 del HTML */
    #panelPropiedades {
        max-height: 85dvh;
    }
</style>

<div class="min-h-screen bg-[var(--bg-color)] transition-colors duration-300">
    {{-- CABECERA Y FILTROS --}}
    <div class="sticky top-0 z-50 bg-[var(--card-color)] border-b border-[var(--border-color)] shadow-sm">
        <div class="max-w-full px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-[var(--text-color)]">Plano Espacial de Mesas</h1>
                    <p class="text-sm sm:text-base text-[var(--text-muted)] mt-1">Gestiona el layout y posición de mesas interactivamente</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    {{-- El modo "Editar" da acceso a mover mesas, agregar y eliminar.
                         Se muestra si el usuario tiene AL MENOS UNO de esos permisos. --}}
                    @if($puedeEditarMesa || $puedeCrearMesa || $puedeEliminarMesa)
                        <button type="button" id="btnEditar" class="flex-1 sm:flex-none justify-center px-4 sm:px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm sm:text-base font-semibold transition flex items-center gap-2 shadow-sm">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                            </svg>
                            Editar
                        </button>
                    @endif

                    {{-- Guardar posiciones (drag & drop) requiere permiso de editar --}}
                    @if($puedeEditarMesa)
                        <button type="button" id="btnGuardar" class="flex-1 sm:flex-none justify-center px-4 sm:px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm sm:text-base font-semibold transition flex items-center gap-2 hidden shadow-sm">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7.707 9.293a1 1 0 010 1.414L4.414 14h11.172a1 1 0 110 2H4.414l3.293 3.293a1 1 0 01-1.414 1.414l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 0z"></path>
                            </svg>
                            Guardar
                        </button>
                    @endif

                    @if($puedeEditarMesa || $puedeCrearMesa || $puedeEliminarMesa)
                        <button type="button" id="btnCancelar" class="flex-1 sm:flex-none justify-center px-4 sm:px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm sm:text-base font-semibold transition hidden shadow-sm flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            Cancelar
                        </button>
                    @endif
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-3 items-center">
                <div class="flex items-center gap-2">
                    <span id="totalMesas" class="text-sm font-semibold text-[var(--text-color)] bg-[var(--input-bg)] border border-[var(--border-color)] px-3 py-2 rounded-lg shadow-sm">Mesas: 0</span>
                </div>

                {{-- "Agregar" solo aparece si el usuario tiene permiso de crear mesas --}}
                @if($puedeCrearMesa)
                    <div id="modosEdicion" class="hidden flex gap-2 w-full sm:w-auto sm:ml-auto">
                        <button type="button" id="btnAgregarMesa" class="w-full sm:w-auto justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold transition flex items-center gap-1 shadow-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                            </svg>
                            Agregar
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- CONTENEDOR PRINCIPAL: MAPA Y PROPIEDADES --}}
    <div class="p-3 sm:p-6 lg:p-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- MAPA --}}
            <div class="lg:col-span-3">
                <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-xl overflow-hidden shadow-lg">

                    {{-- Barra de zoom --}}
                    <div class="flex items-center justify-between gap-2 px-3 py-2 border-b border-[var(--border-color)] bg-[var(--input-bg)]">
                        <span class="text-xs font-semibold text-[var(--text-muted)] hidden sm:inline">Toca y arrastra para mover el plano</span>
                        <span class="text-xs font-semibold text-[var(--text-muted)] sm:hidden">Desliza para mover el plano</span>
                        <div class="flex items-center gap-1 ml-auto">
                            <button type="button" id="btnZoomOut" class="w-11 h-11 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg bg-[var(--card-color)] border border-[var(--border-color)] text-[var(--text-color)] active:scale-95 transition font-bold text-lg">−</button>
                            <span id="zoomLabel" class="text-xs font-semibold text-[var(--text-color)] w-12 text-center select-none">100%</span>
                            <button type="button" id="btnZoomIn" class="w-11 h-11 sm:w-9 sm:h-9 flex items-center justify-center rounded-lg bg-[var(--card-color)] border border-[var(--border-color)] text-[var(--text-color)] active:scale-95 transition font-bold text-lg">+</button>
                            <button type="button" id="btnZoomReset" class="h-11 sm:h-9 px-3 flex items-center justify-center rounded-lg bg-[var(--card-color)] border border-[var(--border-color)] text-[var(--text-color)] active:scale-95 transition text-xs font-semibold">Ajustar</button>
                        </div>
                    </div>

                    {{-- Viewport: esto es lo que hace scroll/pan en el teléfono --}}
                    <div id="planoContenedor"
                         class="relative w-full h-[380px] sm:h-[480px] lg:h-[600px] bg-[var(--input-bg)] overflow-auto shadow-inner"
                         style="touch-action: pan-x pan-y;">

                        {{-- Lienzo real: tamaño fijo, aquí sí tienen sentido las coordenadas x/y de las mesas --}}
                        <div id="planoLienzo" class="relative origin-top-left" style="width:1400px; height:900px; transition: transform 0.15s ease-out;">
                            {{-- Las mesas se inyectan aquí vía JS --}}
                            <div id="planoVacio" class="hidden absolute inset-0 flex flex-col items-center justify-center text-center px-6">
                                <svg class="w-14 h-14 mb-3 text-[var(--text-muted)] opacity-40" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 6a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zm10 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-[var(--text-muted)] font-semibold text-sm sm:text-base">Todavía no hay mesas en el plano</p>
                                <p class="text-[var(--text-muted)] text-xs sm:text-sm mt-1 opacity-80">Toca "Editar" y luego "Agregar" para crear tu primera mesa</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-3 sm:gap-4 text-xs sm:text-sm bg-[var(--card-color)] p-3 sm:p-4 rounded-xl border border-[var(--border-color)] shadow-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-blue-500 rounded-full shadow-sm shrink-0"></div>
                        <span class="text-[var(--text-color)] font-medium">Disponible / Normal</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-yellow-500 rounded-full shadow-sm shrink-0"></div>
                        <span class="text-[var(--text-color)] font-medium">Precaución (30-60 min)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-red-500 rounded-full shadow-sm shrink-0"></div>
                        <span class="text-[var(--text-color)] font-medium">Crítico (&gt;60 min)</span>
                    </div>
                </div>
            </div>

            {{-- PANEL DE PROPIEDADES --}}
            {{-- En móvil esto se convierte en una hoja inferior (bottom sheet) que
                 sube desde abajo al seleccionar una mesa; en escritorio (lg:) se
                 queda como columna lateral fija, igual que antes. --}}
            <div class="lg:col-span-1">
                {{-- Fondo oscuro detrás de la hoja, solo en móvil --}}
                <div id="panelBackdrop" class="hidden lg:hidden fixed inset-0 bg-black/50 z-30"></div>

                <div id="panelPropiedades"
                     class="fixed inset-x-0 bottom-0 z-40 flex flex-col translate-y-full transition-transform duration-300 ease-out bg-[var(--card-color)] border-t border-[var(--border-color)] rounded-t-2xl shadow-2xl
                            lg:sticky lg:inset-auto lg:translate-y-0 lg:transition-none lg:z-auto lg:rounded-xl lg:border lg:shadow-lg lg:top-28"
                     style="max-height: 85dvh;">

                    {{-- Manija de arrastre, solo móvil --}}
                    <div class="lg:hidden flex justify-center pt-2 pb-1 shrink-0">
                        <span class="w-10 h-1.5 rounded-full bg-[var(--border-color)]"></span>
                    </div>

                    <div class="flex items-center justify-between px-5 pt-2 lg:pt-5 pb-2 border-b border-[var(--border-color)] shrink-0">
                        <h3 class="text-lg font-bold text-[var(--text-color)]">Propiedades</h3>
                        <button type="button" id="btnCerrarPanelMovil" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-full text-[var(--text-muted)] active:bg-[var(--input-bg)]">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="px-5 py-4 overflow-y-auto pb-[calc(env(safe-area-inset-bottom)+1rem)] lg:pb-5">
                        <div id="panelVacio" class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto mb-3 text-[var(--text-muted)] opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-[var(--text-muted)] font-medium">Selecciona una mesa</p>
                        </div>

                        <div id="formularioMesa" class="hidden space-y-4 mt-2">
                            <div>
                                <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Número</label>
                                <input type="text" id="propNumero"
                                    autocomplete="off"
                                    data-teclado="texto"
                                    data-teclado-titulo="Número de Mesa"
                                    data-teclado-max="10"
                                    class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Capacidad</label>
                                <input type="text" id="propCapacidad"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    data-teclado="numerico"
                                    data-teclado-titulo="Capacidad"
                                    data-teclado-max="2"
                                    class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner transition-colors">
                            </div>

                            @if($puedeEliminarMesa || $puedeEditarMesa)
                                <div id="botonesAccion" class="pt-4 flex gap-2 border-t border-[var(--border-color)] mt-2">
                                    @if($puedeEliminarMesa)
                                        <button type="button" id="btnEliminar" class="flex-1 px-3 py-2.5 lg:py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                                            Eliminar
                                        </button>
                                    @endif
                                    @if($puedeEditarMesa)
                                        <button type="button" id="btnActualizar" class="flex-1 px-3 py-2.5 lg:py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                                            Actualizar
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CREAR MESA (ACTUALIZADO CON FLEX-COL) --}}
    @if($puedeCrearMesa)
    <div id="modalCrearMesa" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-[60] p-4 backdrop-blur-sm transition-all duration-300">

        {{-- Contenedor del Modal: flex flex-col para permitir scroll interno --}}
        <div id="modalCrearMesaContent" class="bg-[var(--card-color)] rounded-xl shadow-2xl max-w-md w-full border border-[var(--border-color)] overflow-hidden max-h-[90vh] flex flex-col transition-all duration-200">

            {{-- HEADER: shrink-0 --}}
            <div class="px-6 py-4 border-b border-[var(--border-color)] flex justify-between items-center bg-[var(--bg-color)]/50 shrink-0">
                <h2 class="text-xl font-bold text-[var(--text-color)]">Crear Nueva Mesa</h2>
                <button type="button" class="btnCerrarModal text-[var(--text-muted)] hover:text-red-500 transition-colors outline-none">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>

            {{-- BODY: overflow-y-auto --}}
            <div class="px-6 py-5 space-y-4 overflow-y-auto">
                <div>
                    <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Número de Mesa <span class="text-red-500">*</span></label>
                    <input type="text" id="newNumero"
                        autocomplete="off"
                        data-teclado="texto"
                        data-teclado-titulo="Número de Mesa"
                        data-teclado-max="10"
                        class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner transition-all" placeholder="ej: M1, Mesa-1, Table 1" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Capacidad <span class="text-red-500">*</span></label>
                    <input type="text" id="newCapacidad"
                        inputmode="numeric"
                        autocomplete="off"
                        data-teclado="numerico"
                        data-teclado-titulo="Capacidad"
                        data-teclado-max="2"
                        class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner transition-all" placeholder="4" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Estado Inicial</label>
                    <select id="newEstado" class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm transition-all">
                        <option value="disponible">Disponible</option>
                        <option value="reservada">Reservada</option>
                        <option value="limpieza">Limpieza</option>
                    </select>
                </div>

                <p class="text-xs text-[var(--text-muted)] mt-3">La mesa aparecerá en el plano lista para ser posicionada.</p>
            </div>

            {{-- FOOTER: shrink-0 --}}
            <div class="px-6 py-4 border-t border-[var(--border-color)] flex gap-3 justify-end bg-[var(--bg-color)]/50 shrink-0">
                <button type="button" class="btnCerrarModal px-4 py-2 bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-color)] hover:opacity-80 rounded-lg font-semibold transition shadow-sm">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmarNueva" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition shadow-sm">
                    Crear Mesa
                </button>
            </div>
        </div>
    </div>
    @endif

    <div id="notificacion" class="fixed bottom-4 right-4 px-4 py-3 rounded-lg text-white text-sm font-semibold hidden z-50 transition-all shadow-xl"></div>
</div>

{{-- SCRIPTS --}}
@push('scripts')
    @vite(['resources/js/mesas.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof TecladoVirtual !== 'undefined') {
                TecladoVirtual.attachAll();
            }
        });
    </script>
@endpush