@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800">
    <!-- ENCABEZADO -->
    <div class="sticky top-0 z-50 bg-slate-900/95 backdrop-blur border-b border-slate-700 shadow-lg">
        <div class="max-w-full px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white">Plano Espacial de Mesas</h1>
                    <p class="text-slate-400 mt-1">Gestiona el layout y posición de mesas interactivamente</p>
                </div>

                <!-- CONTROLES PRINCIPALES -->
                <div class="flex flex-wrap gap-2">
                    <button id="btnEditar" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                        Editar
                    </button>

                    <button id="btnGuardar" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition flex items-center gap-2 hidden">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7.707 9.293a1 1 0 010 1.414L4.414 14h11.172a1 1 0 110 2H4.414l3.293 3.293a1 1 0 01-1.414 1.414l-5-5a1 1 0 010-1.414l5-5a1 1 0 011.414 0z"></path>
                        </svg>
                        Guardar
                    </button>

                    <button id="btnCancelar" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition hidden">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        Cancelar
                    </button>
                </div>
            </div>

            <!-- FILTROS Y CONTROLES SECUNDARIOS -->
            <div class="mt-4 flex flex-wrap gap-3 items-center">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-semibold text-slate-300">Zona:</label>
                    <select id="filtroZona" class="px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todas las zonas</option>
                        <option value="salon">Salón</option>
                        <option value="terraza">Terraza</option>
                        <option value="vip">VIP</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <span id="totalMesas" class="text-sm font-semibold text-slate-300 bg-slate-800 px-3 py-2 rounded-lg">Mesas: 0</span>
                </div>

                <!-- MODO EDICIÓN -->
                <div id="modosEdicion" class="hidden flex gap-2 ml-auto">
                    <button id="btnAgregarMesa" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        Agregar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- LIENZO PRINCIPAL -->
            <div class="lg:col-span-3">
                <div class="bg-slate-800 border-2 border-slate-700 rounded-lg overflow-hidden shadow-2xl">
                    <div id="planoContenedor" class="relative w-full bg-gradient-to-br from-slate-700 to-slate-800 overflow-auto" style="height: 600px; cursor: default;">
                        <!-- Las mesas se renderizarán aquí dinámicamente -->
                    </div>
                </div>

                <!-- INDICADORES VISUALES -->
                <div class="mt-4 flex flex-wrap gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-blue-500 rounded-full"></div>
                        <span class="text-slate-300">Disponible / Normal</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-yellow-500 rounded-full"></div>
                        <span class="text-slate-300">Precaución (30-60 min)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                        <span class="text-slate-300">Crítico (>60 min)</span>
                    </div>
                </div>
            </div>

            <!-- PANEL LATERAL - PROPIEDADES -->
            <div class="lg:col-span-1">
                <div class="bg-slate-800 border border-slate-700 rounded-lg p-4 shadow-lg sticky top-20">
                    <h3 class="text-lg font-bold text-white mb-4">Propiedades</h3>

                    <div id="panelVacio" class="text-center text-slate-400 py-8">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-slate-400">Selecciona una mesa</p>
                    </div>

                    <div id="formularioMesa" class="hidden space-y-3">
                        <div>
                            <label class="block text-sm font-semibold text-slate-300 mb-1">Número</label>
                            <input type="text" id="propNumero" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-300 mb-1">Capacidad</label>
                            <input type="number" id="propCapacidad" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" min="1" max="20">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-300 mb-1">Zona</label>
                            <select id="propZona" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="salon">Salón</option>
                                <option value="terraza">Terraza</option>
                                <option value="vip">VIP</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-300 mb-1">Forma</label>
                            <select id="propForma" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="redonda">Redonda</option>
                                <option value="cuadrada">Cuadrada</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-1">Ancho</label>
                                <input type="number" id="propAncho" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" min="30" max="200">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-1">Alto</label>
                                <input type="number" id="propAlto" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" min="30" max="200">
                            </div>
                        </div>

                        <div id="botonesAccion" class="pt-3 flex gap-2">
                            <button id="btnEliminar" class="flex-1 px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-semibold transition">
                                Eliminar
                            </button>
                            <button id="btnActualizar" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-semibold transition hidden">
                                Actualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PARA CREAR MESA -->
    <div id="modalCrearMesa" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-slate-800 rounded-lg shadow-2xl max-w-md w-full border border-slate-700">
            <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center">
                <h2 class="text-xl font-bold text-white">Crear Nueva Mesa</h2>
                <button class="btnCerrarModal text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Número de Mesa <span class="text-red-500">*</span></label>
                    <input type="text" id="newNumero" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="ej: M1, Mesa-1, Table 1" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Capacidad <span class="text-red-500">*</span></label>
                    <input type="number" id="newCapacidad" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-blue-500" min="1" max="20" value="4" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-300 mb-1">Estado Inicial</label>
                    <select id="newEstado" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="disponible">Disponible</option>
                        <option value="reservada">Reservada</option>
                        <option value="limpieza">Limpieza</option>
                    </select>
                </div>

                <p class="text-xs text-slate-400 mt-3">La mesa aparecerá en el plano lista para ser repositionada.</p>
            </div>

            <div class="px-6 py-4 border-t border-slate-700 flex gap-2 justify-end">
                <button class="btnCerrarModal px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded font-semibold transition">
                    Cancelar
                </button>
                <button id="btnConfirmarNueva" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold transition">
                    Crear Mesa
                </button>
            </div>
        </div>
    </div>

    <!-- NOTIFICACIONES -->
    <div id="notificacion" class="fixed bottom-4 right-4 px-4 py-3 rounded-lg text-white text-sm font-semibold hidden z-50 transition-all"></div>
</div>

@endsection

@section('scripts')
<script>
    // Configuración global
    const config = {
        apiBase: '{{ route("admin.plano-espacial.api.mesas") }}',
        apiGuardar: '{{ route("admin.plano-espacial.api.guardar") }}',
        apiCrear: '{{ route("admin.plano-espacial.api.crear") }}',
        apiStore: '{{ route("admin.plano-espacial.api.store") }}',
        apiEliminar: '{{ route("admin.plano-espacial.api.eliminar", ["id" => "ID"]) }}',
    };

    // Estado
    const estado = {
        modoEdicion: false,
        mesaSeleccionada: null,
        mesasOriginales: [],
        mesasActuales: [],
        arrastrando: null,
        offsetX: 0,
        offsetY: 0,
        arrastrandoContenedor: false,
        scrollX: 0,
        scrollY: 0,
    };

    // ========== INICIALIZACIÓN ==========
    document.addEventListener('DOMContentLoaded', async () => {
        await cargarMesas();
        setupEventListeners();
        
        // Auto-sincronizar con el servidor cada 30 segundos (solo si no estamos editando)
        setInterval(async () => {
            if (!estado.modoEdicion) {
                const zona = document.getElementById('filtroZona').value;
                await cargarMesas(zona);
            }
        }, 30000);
    });

    // ========== CARGAR MESAS DEL SERVIDOR ==========
    async function cargarMesas(zona = '') {
        try {
            const url = zona ? `${config.apiBase}?zona=${zona}` : config.apiBase;
            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                estado.mesasActuales = data.data;
                estado.mesasOriginales = JSON.parse(JSON.stringify(data.data));
                renderizarMesas();
                actualizarTotalMesas();
            }
        } catch (error) {
            console.error('Error al cargar mesas:', error);
            mostrarNotificacion('Error al cargar las mesas', 'error');
        }
    }

    // ========== RENDERIZAR MESAS EN EL PLANO ==========
    function renderizarMesas() {
        const contenedor = document.getElementById('planoContenedor');
        contenedor.innerHTML = '';

        estado.mesasActuales.forEach(mesa => {
            const elemento = crearElementoMesa(mesa);
            contenedor.appendChild(elemento);
        });
    }

    function crearElementoMesa(mesa) {
        const div = document.createElement('div');
        div.className = 'mesa-elemento absolute cursor-move transition-all';
        div.dataset.id = mesa.id;
        div.dataset.numero = mesa.numero;

        // Colores según estado visual
        const coloresEstado = {
            'blue': 'bg-blue-500 hover:bg-blue-600',
            'yellow': 'bg-yellow-500 hover:bg-yellow-600',
            'red': 'bg-red-500 hover:bg-red-600',
        };

        const colorClase = coloresEstado[mesa.estadoVisual] || coloresEstado.blue;

        div.style.left = mesa.posicion_x + 'px';
        div.style.top = mesa.posicion_y + 'px';
        div.style.width = mesa.ancho + 'px';
        div.style.height = mesa.alto + 'px';

        const borderRadius = mesa.forma === 'redonda' ? '50%' : '8px';
        div.style.borderRadius = borderRadius;

        div.innerHTML = `
            <div class="w-full h-full ${colorClase} flex items-center justify-center rounded-inherit border-2 border-slate-900 shadow-lg">
                <div class="text-center text-white font-bold text-sm">
                    <div class="text-lg">${mesa.numero}</div>
                    <div class="text-xs opacity-75">${mesa.capacidad} pax</div>
                </div>
            </div>
        `;

        // Event listeners para mesas
        div.addEventListener('mousedown', (e) => iniciarArrastre(e, mesa));
        div.addEventListener('click', (e) => {
            if (!estado.arrastrando && estado.modoEdicion) {
                e.stopPropagation();
                seleccionarMesa(mesa);
            }
        });

        return div;
    }

    function habilitarInteraccionMesa(elemento, mesa) {
        // Re-registrar eventos si es necesario (ya están registrados en crearElementoMesa)
        elemento.addEventListener('mousedown', (e) => {
            if (estado.modoEdicion) {
                iniciarArrastre(e, mesa);
            }
        });
    }

    // ========== DRAG & DROP ==========
    function iniciarArrastre(e, mesa) {
        if (!estado.modoEdicion) return;

        estado.arrastrando = mesa;
        estado.offsetX = e.clientX - e.target.getBoundingClientRect().left;
        estado.offsetY = e.clientY - e.target.getBoundingClientRect().top;

        document.addEventListener('mousemove', moverMesa);
        document.addEventListener('mouseup', terminarArrastre);
    }

    function moverMesa(e) {
        if (!estado.arrastrando) return;

        const contenedor = document.getElementById('planoContenedor');
        const rect = contenedor.getBoundingClientRect();

        let x = e.clientX - rect.left - estado.offsetX;
        let y = e.clientY - rect.top - estado.offsetY;

        // Limitar dentro del contenedor
        x = Math.max(0, Math.min(x, rect.width - estado.arrastrando.ancho));
        y = Math.max(0, Math.min(y, rect.height - estado.arrastrando.alto));

        estado.arrastrando.posicion_x = Math.round(x);
        estado.arrastrando.posicion_y = Math.round(y);

        const elemento = document.querySelector(`[data-id="${estado.arrastrando.id}"]`);
        if (elemento) {
            elemento.style.left = x + 'px';
            elemento.style.top = y + 'px';
        }
    }

    function terminarArrastre() {
        document.removeEventListener('mousemove', moverMesa);
        document.removeEventListener('mouseup', terminarArrastre);

        if (estado.mesaSeleccionada?.id === estado.arrastrando?.id) {
            actualizarPropiedades();
        }

        estado.arrastrando = null;
    }

    // ========== SELECCIONAR MESA ==========
    function seleccionarMesa(mesa) {
        // Remover selección anterior
        document.querySelectorAll('.mesa-elemento').forEach(el => {
            el.classList.remove('ring-4', 'ring-white');
        });

        // Seleccionar nueva
        estado.mesaSeleccionada = mesa;
        const elemento = document.querySelector(`[data-id="${mesa.id}"]`);
        if (elemento) {
            elemento.classList.add('ring-4', 'ring-white');
        }

        mostrarPropiedades(mesa);
    }

    // ========== PROPIEDADES ==========
    function mostrarPropiedades(mesa) {
        document.getElementById('panelVacio').classList.add('hidden');
        document.getElementById('formularioMesa').classList.remove('hidden');

        document.getElementById('propNumero').value = mesa.numero;
        document.getElementById('propCapacidad').value = mesa.capacidad;
        document.getElementById('propZona').value = mesa.zona || 'salon';
        document.getElementById('propForma').value = mesa.forma || 'redonda';
        document.getElementById('propAncho').value = mesa.ancho || 60;
        document.getElementById('propAlto').value = mesa.alto || 60;

        if (!estado.modoEdicion) {
            document.getElementById('propCapacidad').disabled = true;
            document.getElementById('propZona').disabled = true;
            document.getElementById('propForma').disabled = true;
            document.getElementById('propAncho').disabled = true;
            document.getElementById('propAlto').disabled = true;
            document.getElementById('botonesAccion').classList.add('hidden');
        } else {
            document.getElementById('propCapacidad').disabled = false;
            document.getElementById('propZona').disabled = false;
            document.getElementById('propForma').disabled = false;
            document.getElementById('propAncho').disabled = false;
            document.getElementById('propAlto').disabled = false;
            document.getElementById('botonesAccion').classList.remove('hidden');
        }
    }

    function actualizarPropiedades() {
        if (!estado.mesaSeleccionada) return;

        estado.mesaSeleccionada.capacidad = parseInt(document.getElementById('propCapacidad').value);
        estado.mesaSeleccionada.zona = document.getElementById('propZona').value;
        estado.mesaSeleccionada.forma = document.getElementById('propForma').value;
        estado.mesaSeleccionada.ancho = parseInt(document.getElementById('propAncho').value);
        estado.mesaSeleccionada.alto = parseInt(document.getElementById('propAlto').value);

        renderizarMesas();
        seleccionarMesa(estado.mesaSeleccionada);
    }

    // ========== MODOS ==========
    function activarModoEdicion() {
        estado.modoEdicion = true;
        document.getElementById('btnEditar').classList.add('hidden');
        document.getElementById('btnGuardar').classList.remove('hidden');
        document.getElementById('btnCancelar').classList.remove('hidden');
        document.getElementById('modosEdicion').classList.remove('hidden');
        document.getElementById('planoContenedor').style.cursor = 'grab';

        if (estado.mesaSeleccionada) {
            mostrarPropiedades(estado.mesaSeleccionada);
        }
    }

    function desactivarModoEdicion() {
        estado.modoEdicion = false;
        document.getElementById('btnEditar').classList.remove('hidden');
        document.getElementById('btnGuardar').classList.add('hidden');
        document.getElementById('btnCancelar').classList.add('hidden');
        document.getElementById('modosEdicion').classList.add('hidden');
        document.getElementById('planoContenedor').style.cursor = 'default';

        // Restaurar mesas originales
        estado.mesasActuales = JSON.parse(JSON.stringify(estado.mesasOriginales));
        renderizarMesas();
        limpiarSeleccion();
    }

    // ========== GUARDAR PLANO ==========
    async function guardarPlano() {
        try {
            const response = await fetch(config.apiGuardar, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    mesas: estado.mesasActuales,
                }),
            });

            const data = await response.json();

            if (data.success) {
                mostrarNotificacion('¡Plano guardado correctamente!', 'success');
                
                // Recargar mesas desde el servidor para asegurar sincronización
                await cargarMesas();
                desactivarModoEdicion();
            } else {
                mostrarNotificacion(data.message || 'Error al guardar', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('Error al guardar el plano', 'error');
        }
    }

    // ========== ACCIONES ==========
    async function eliminarMesaDelPlano() {
        if (!estado.mesaSeleccionada) return;

        if (!confirm('¿Estás seguro de que deseas eliminar esta mesa del plano?')) return;

        try {
            const url = config.apiEliminar.replace('ID', estado.mesaSeleccionada.id);
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            const data = await response.json();

            if (data.success) {
                // Recargar mesas desde el servidor para asegurar sincronización
                await cargarMesas();
                limpiarSeleccion();
                mostrarNotificacion('Mesa eliminada del plano', 'success');
            } else {
                mostrarNotificacion('Error al eliminar', 'error');
            }
        } catch (error) {
            mostrarNotificacion('Error al eliminar', 'error');
        }
    }

    async function crearNuevaMesa() {
        const numero = document.getElementById('newNumero').value.trim();
        const capacidad = parseInt(document.getElementById('newCapacidad').value);
        const estado = document.getElementById('newEstado').value || 'disponible';

        if (!numero) {
            mostrarNotificacion('Por favor ingresa un número de mesa', 'error');
            return;
        }

        if (isNaN(capacidad) || capacidad < 1) {
            mostrarNotificacion('Capacidad debe ser un número válido', 'error');
            return;
        }

        try {
            const datosEnvio = {
                numero,
                capacidad,
                estado,
                zona: 'salon',
                forma: 'redonda',
                posicion_x: 20,
                posicion_y: 20,
            };

            console.log('📤 Enviando datos:', datosEnvio);
            console.log('📍 URL:', config.apiStore);

            const response = await fetch(config.apiStore, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(datosEnvio),
            });

            console.log('📥 Respuesta status:', response.status);
            const data = await response.json();
            console.log('📥 Respuesta:', data);

            if (data.success) {
                mostrarNotificacion('✓ Mesa creada exitosamente', 'success');
                cerrarModalCrear();
                
                // Recargar desde el servidor
                console.log('🔄 Recargando mesas...');
                await cargarMesas();
                console.log('✅ Mesas recargadas. Total:', estado.mesasActuales.length);
            } else {
                const errorMsg = data.errors?.numero?.[0] || data.message || 'Error desconocido';
                console.error('❌ Error del servidor:', data);
                mostrarNotificacion(errorMsg, 'error');
            }
        } catch (error) {
            console.error('❌ Error en la solicitud:', error);
            mostrarNotificacion('Error: ' + error.message, 'error');
        }
    }

    function actualizarTotalMesas() {
        document.getElementById('totalMesas').textContent = `Mesas: ${estado.mesasActuales.length}`;
    }

    function limpiarSeleccion() {
        estado.mesaSeleccionada = null;
        document.querySelectorAll('.mesa-elemento').forEach(el => {
            el.classList.remove('ring-4', 'ring-white');
        });
        document.getElementById('formularioMesa').classList.add('hidden');
        document.getElementById('panelVacio').classList.remove('hidden');
    }

    // ========== MODALES ==========
    function abrirModalCrear() {
        document.getElementById('modalCrearMesa').classList.remove('hidden');
    }

    function cerrarModalCrear() {
        document.getElementById('modalCrearMesa').classList.add('hidden');
        document.getElementById('newNumero').value = '';
        document.getElementById('newCapacidad').value = '4';
        document.getElementById('newZona').value = 'salon';
        document.getElementById('newForma').value = 'redonda';
    }

    function mostrarNotificacion(mensaje, tipo = 'info') {
        const notif = document.getElementById('notificacion');
        notif.textContent = mensaje;
        notif.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg text-white text-sm font-semibold z-50 transition-all ${
            tipo === 'success' ? 'bg-green-600' :
            tipo === 'error' ? 'bg-red-600' :
            'bg-blue-600'
        }`;
        notif.classList.remove('hidden');

        setTimeout(() => {
            notif.classList.add('hidden');
        }, 3000);
    }

    // ========== EVENT LISTENERS ==========
    function setupEventListeners() {
        // Botones principales
        document.getElementById('btnEditar').addEventListener('click', activarModoEdicion);
        document.getElementById('btnGuardar').addEventListener('click', guardarPlano);
        document.getElementById('btnCancelar').addEventListener('click', desactivarModoEdicion);
        document.getElementById('btnAgregarMesa').addEventListener('click', abrirModalCrear);
        document.getElementById('btnEliminar').addEventListener('click', eliminarMesaDelPlano);
        document.getElementById('btnActualizar').addEventListener('click', actualizarPropiedades);

        // Modal - Crear nueva mesa
        document.getElementById('btnConfirmarNueva').addEventListener('click', crearNuevaMesa);
        
        // Modal - Cerrar con botón X
        document.querySelectorAll('.btnCerrarModal').forEach(btn => {
            btn.addEventListener('click', cerrarModalCrear);
        });

        // Modal - Cerrar con tecla Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.getElementById('modalCrearMesa');
                if (!modal.classList.contains('hidden')) {
                    cerrarModalCrear();
                }
            }
        });

        // Modal - Enviar con Enter en los inputs
        ['newNumero', 'newCapacidad', 'newEstado'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        crearNuevaMesa();
                    }
                });
            }
        });

        // Modal - Cerrar al hacer click fuera
        document.getElementById('modalCrearMesa').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                cerrarModalCrear();
            }
        });

        // Filtro zona
        document.getElementById('filtroZona').addEventListener('change', (e) => {
            cargarMesas(e.target.value);
        });

        // Propiedades en tiempo real
        ['propCapacidad', 'propZona', 'propForma', 'propAncho', 'propAlto'].forEach(id => {
            const elem = document.getElementById(id);
            if (elem) {
                elem.addEventListener('change', actualizarPropiedades);
            }
        });

        // Click en lienzo para deseleccionar
        document.getElementById('planoContenedor').addEventListener('click', (e) => {
            if (e.target === e.currentTarget && estado.modoEdicion) {
                limpiarSeleccion();
            }
        });

        console.log('✓ Event listeners configurados correctamente');
    }
</script>
@endsection
