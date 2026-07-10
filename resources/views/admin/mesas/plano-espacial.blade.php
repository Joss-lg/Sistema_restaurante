<div class="min-h-screen bg-[var(--bg-color)] transition-colors duration-300">
    <div class="sticky top-0 z-50 bg-[var(--card-color)] border-b border-[var(--border-color)] shadow-sm">
        <div class="max-w-full px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-[var(--text-color)]">Plano Espacial de Mesas</h1>
                    <p class="text-sm sm:text-base text-[var(--text-muted)] mt-1">Gestiona el layout y posición de mesas interactivamente</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="button" id="btnEditar" class="flex-1 sm:flex-none justify-center px-4 sm:px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm sm:text-base font-semibold transition flex items-center gap-2 shadow-sm">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                        Editar
                    </button>

                    <button type="button" id="btnGuardar" class="flex-1 sm:flex-none justify-center px-4 sm:px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm sm:text-base font-semibold transition flex items-center gap-2 hidden shadow-sm">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7.707 9.293a1 1 0 010 1.414L4.414 14h11.172a1 1 0 110 2H4.414l3.293 3.293a1 1 0 01-1.414 1.414l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 0z"></path>
                        </svg>
                        Guardar
                    </button>

                    <button type="button" id="btnCancelar" class="flex-1 sm:flex-none justify-center px-4 sm:px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm sm:text-base font-semibold transition hidden shadow-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        Cancelar
                    </button>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-3 items-center">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-semibold text-[var(--text-color)]">Zona:</label>
                    <select id="filtroZona" class="px-3 py-2 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                        <option value="">Todas las zonas</option>
                        <option value="salon">Salón</option>
                        <option value="terraza">Terraza</option>
                        <option value="vip">VIP</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <span id="totalMesas" class="text-sm font-semibold text-[var(--text-color)] bg-[var(--input-bg)] border border-[var(--border-color)] px-3 py-2 rounded-lg shadow-sm">Mesas: 0</span>
                </div>

                <div id="modosEdicion" class="hidden flex gap-2 w-full sm:w-auto sm:ml-auto">
                    <button type="button" id="btnAgregarMesa" class="w-full sm:w-auto justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold transition flex items-center gap-1 shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        Agregar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="p-3 sm:p-6 lg:p-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="lg:col-span-3">
                <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-xl overflow-hidden shadow-lg">
                    {{-- El canvas ahora usa un color de fondo para inputs que simula un "lienzo" hundido, compatible con ambos modos --}}
                    {{-- Altura reducida en móvil para que no ocupe toda la pantalla y sea fácil llegar al panel de propiedades --}}
                    <div id="planoContenedor" class="relative w-full h-[380px] sm:h-[480px] lg:h-[600px] bg-[var(--input-bg)] overflow-auto shadow-inner border-b border-[var(--border-color)]" style="cursor: default;">
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

            <div class="lg:col-span-1">
                {{-- Sticky solo en pantallas grandes: en móvil el panel va apilado debajo del canvas, no fijo --}}
                <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-xl p-5 shadow-lg lg:sticky lg:top-28">
                    <h3 class="text-lg font-bold text-[var(--text-color)] mb-4 border-b border-[var(--border-color)] pb-2">Propiedades</h3>

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
                            <input type="text" id="propNumero" class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Capacidad</label>
                            <input type="number" id="propCapacidad" class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner transition-colors" min="1" max="20">
                        </div>
                        
                        <div id="botonesAccion" class="pt-4 flex gap-2 border-t border-[var(--border-color)] mt-2">
                            <button type="button" id="btnEliminar" class="flex-1 px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                                Eliminar
                            </button>
                            <button type="button" id="btnActualizar" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition shadow-sm">
                                Actualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CREAR MESA --}}
    <div id="modalCrearMesa" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center z-[60] p-4 backdrop-blur-sm">
        <div class="bg-[var(--card-color)] rounded-xl shadow-2xl max-w-md w-full border border-[var(--border-color)] overflow-hidden max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-[var(--border-color)] flex justify-between items-center bg-[var(--bg-color)]/50">
                <h2 class="text-xl font-bold text-[var(--text-color)]">Crear Nueva Mesa</h2>
                <button type="button" class="btnCerrarModal text-[var(--text-muted)] hover:text-red-500 transition-colors">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Número de Mesa <span class="text-red-500">*</span></label>
                    <input type="text" id="newNumero" class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner" placeholder="ej: M1, Mesa-1, Table 1" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Capacidad <span class="text-red-500">*</span></label>
                    <input type="number" id="newCapacidad" class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-inner" min="1" max="20" value="4" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[var(--text-color)] mb-1">Estado Inicial</label>
                    <select id="newEstado" class="w-full px-3 py-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-lg text-[var(--text-color)] focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                        <option value="disponible">Disponible</option>
                        <option value="reservada">Reservada</option>
                        <option value="limpieza">Limpieza</option>
                    </select>
                </div>

                <p class="text-xs text-[var(--text-muted)] mt-3">La mesa aparecerá en el plano lista para ser posicionada.</p>
            </div>

            <div class="px-6 py-4 border-t border-[var(--border-color)] flex gap-3 justify-end bg-[var(--bg-color)]/50">
                <button type="button" class="btnCerrarModal px-4 py-2 bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-color)] hover:opacity-80 rounded-lg font-semibold transition shadow-sm">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmarNueva" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition shadow-sm">
                    Crear Mesa
                </button>
            </div>
        </div>
    </div>

    <div id="notificacion" class="fixed bottom-4 right-4 px-4 py-3 rounded-lg text-white text-sm font-semibold hidden z-50 transition-all shadow-xl"></div>
</div>


@push('scripts')
    @vite(['resources/js/mesas.js'])
@endpush