@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800">
    <div class="sticky top-0 z-50 bg-slate-900/95 backdrop-blur border-b border-slate-700 shadow-lg">
        <div class="max-w-full px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white">Plano Espacial de Mesas</h1>
                    <p class="text-slate-400 mt-1">Gestiona el layout y posición de mesas interactivamente</p>
                </div>

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

    <div class="p-4 sm:p-6 lg:p-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="lg:col-span-3">
                <div class="bg-slate-800 border-2 border-slate-700 rounded-lg overflow-hidden shadow-2xl">
                    <div id="planoContenedor" class="relative w-full bg-gradient-to-br from-slate-700 to-slate-800 overflow-auto" style="height: 600px; cursor: default;">
                        </div>
                </div>

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

                <p class="text-xs text-slate-400 mt-3">La mesa aparecerá en el plano lista para ser posicionada.</p>
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

    <div id="notificacion" class="fixed bottom-4 right-4 px-4 py-3 rounded-lg text-white text-sm font-semibold hidden z-50 transition-all shadow-xl"></div>
</div>

@endsection

@push('scripts')
    @vite(['resources/js/mesas.js'])
@endpush