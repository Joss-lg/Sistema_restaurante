{{-- Estilos para manejo de teclado virtual en PC --}}
<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modal-crear-alimento {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        
        body.teclado-virtual-abierto #modal-crear-panel {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important; 
        }
    }
</style>

{{-- MODAL AGREGAR PLATILLO --}}
<div id="modal-crear-alimento" class="fixed inset-y-0 right-0 left-[74px] sm:left-0 sm:inset-0 z-[9999] hidden opacity-0 transition-all duration-300 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="fixed inset-0 bg-black/60 dark:bg-black/80 -ml-[74px] sm:ml-0" onclick="closeModalCrear()"></div>

    <div class="relative bg-white/95 dark:bg-zinc-800/95 backdrop-blur-xl border border-zinc-200 dark:border-zinc-700 w-full max-w-xl sm:max-w-2xl max-h-[92vh] flex flex-col rounded-[1.5rem] sm:rounded-[2.5rem] shadow-2xl transform opacity-0 translate-y-8 transition-all duration-300 overflow-hidden" id="modal-crear-panel">

        <div class="p-5 sm:p-10 pb-4 sm:pb-6 border-b border-zinc-200 dark:border-zinc-700/30 flex justify-between items-start">
            <div>
                <h2 class="text-xl sm:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">Nuevo Platillo</h2>
                <p class="text-[10px] sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Configuración estética del menú</p>
            </div>
            <button type="button" onclick="closeModalCrear()" class="w-9 h-9 flex items-center justify-center text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition rounded-full active:scale-95 shrink-0 outline-none">
                <i class="fas fa-times text-lg sm:text-2xl"></i>
            </button>
        </div>

        <form id="formulario-crear-producto" onsubmit="guardarProducto(event)" class="overflow-y-auto flex-1 p-5 sm:p-10 pt-4 sm:pt-6">
            <div class="grid grid-cols-2 gap-4 sm:gap-6">

                {{-- Nombre --}}
                <div class="col-span-2">
                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Nombre del Platillo</label>
                    <input type="text" id="nombre" name="nombre" readonly data-teclado="texto" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-zinc-900 dark:text-white placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:ring-2 focus:ring-blue-500 outline-none transition text-base" placeholder="Ej: Lasagna de la Casa" required>
                </div>

                {{-- Toggle --}}
                <div class="col-span-2">
                    <label class="flex items-center justify-between gap-3 bg-orange-500/5 border border-orange-500/20 rounded-2xl p-3.5 sm:p-4 cursor-pointer select-none">
                        <span class="flex items-center gap-2.5">
                            <i class="fas fa-weight-hanging text-orange-500 text-sm"></i>
                            <span class="text-xs sm:text-sm font-bold text-zinc-900 dark:text-white">Se vende por peso</span>
                        </span>
                        <span class="relative inline-flex items-center">
                            <input type="checkbox" id="se_vende_por_peso" name="se_vende_por_peso" class="peer sr-only" onchange="toggleModoVentaPeso('crear')">
                            <span class="w-11 h-6 rounded-full bg-zinc-300 dark:bg-zinc-600 peer-checked:bg-orange-500 transition-colors"></span>
                            <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform peer-checked:translate-x-5"></span>
                        </span>
                    </label>
                </div>

                {{-- Precios --}}
                <div class="col-span-2 sm:col-span-1" id="grupo-precio-fijo-crear">
                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Precio</label>
                    <input type="text" id="precio" name="precio" readonly data-teclado="numerico" data-teclado-decimales="true" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition text-base" placeholder="0.00" required>
                </div>

                <div class="col-span-2 sm:col-span-1 hidden" id="grupo-precio-peso-crear">
                    <label class="text-[11px] sm:text-xs font-black text-orange-500 uppercase tracking-widest ml-1">Precio por cada 100g</label>
                    <input type="text" id="precio_por_100g" name="precio_por_100g" readonly data-teclado="numerico" data-teclado-decimales="true" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-orange-500/30 rounded-2xl p-3 sm:p-4 mt-1.5 text-zinc-900 dark:text-white outline-none transition text-base" placeholder="50.00">
                </div>

                {{-- Tiempo --}}
                <div class="col-span-2 sm:col-span-1">
                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Tiempo de Prep. (min)</label>
                    <input type="text" id="tiempo_preparacion" name="tiempo_preparacion" readonly data-teclado="numerico" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition text-base" placeholder="Ej: 20" required>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-zinc-200 dark:border-zinc-700/30 flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" onclick="closeModalCrear()" class="w-full sm:flex-1 bg-zinc-100 dark:bg-zinc-700/50 text-zinc-500 dark:text-zinc-400 font-black py-3 sm:py-4 rounded-xl transition text-xs sm:text-sm tracking-widest">CANCELAR</button>
                <button type="submit" class="w-full sm:flex-1 bg-blue-600 hover:bg-blue-500 text-white font-black py-3 sm:py-4 rounded-xl transition text-xs sm:text-sm tracking-widest" id="btn-guardar">GUARDAR CAMBIOS</button>
            </div>
        </form>
    </div>
</div>

<script>
    // 1. Asegurar que las funciones sean globales
    window.abrirModalCrear = function() {
        // Resetear el formulario cada vez que se abre para crear un producto nuevo
        estadoGlobal.editandoId = null;
        const form = document.getElementById('formulario-crear-producto');
        if (form) form.reset();
        toggleModoVentaPeso('crear');

        const modal = document.getElementById('modal-crear-alimento');
        const panel = document.getElementById('modal-crear-panel');

        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.add('opacity-100');
            panel.classList.remove('opacity-0', 'translate-y-8');
            panel.classList.add('opacity-100', 'translate-y-0');
        });
        
        // Inicializar teclado
        if (typeof TecladoVirtual !== 'undefined') {
            TecladoVirtual.attachAll();
        }
    }

    window.closeModalCrear = function() {
        const modal = document.getElementById('modal-crear-alimento');
        const panel = document.getElementById('modal-crear-panel');

        panel.classList.remove('opacity-100', 'translate-y-0');
        panel.classList.add('opacity-0', 'translate-y-8');
        modal.classList.remove('opacity-100');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // 2. Muestra/oculta el bloque de precio normal vs precio por peso
    window.toggleModoVentaPeso = function(tipo) {
        const checkbox     = document.getElementById('se_vende_por_peso');
        const grupoFijo     = document.getElementById(`grupo-precio-fijo-${tipo}`);
        const grupoPorPeso  = document.getElementById(`grupo-precio-peso-${tipo}`);
        const inputFijo     = document.getElementById(tipo === 'crear' ? 'precio' : `precio-${tipo}`);
        const inputPorPeso  = document.getElementById(tipo === 'crear' ? 'precio_por_100g' : `precio_por_100g-${tipo}`);
        if (!checkbox || !grupoFijo || !grupoPorPeso) return;

        const esPorPeso = checkbox.checked;
        grupoFijo.classList.toggle('hidden', esPorPeso);
        grupoPorPeso.classList.toggle('hidden', !esPorPeso);

        if (inputFijo)    inputFijo.required    = !esPorPeso;
        if (inputPorPeso) inputPorPeso.required = esPorPeso;
    }

    // 3. Envío del formulario de creación
    window.guardarProducto = function(event) {
        event.preventDefault();

        const boton         = document.getElementById('btn-guardar');
        const textoOriginal = boton.textContent;
        boton.textContent   = 'Guardando...';
        boton.disabled      = true;

        const data = _serializarFormulario('formulario-crear-producto');
        data.se_vende_por_peso = document.getElementById('se_vende_por_peso').checked;

        ejecutarPeticion(RUTA_STORE, data, boton, textoOriginal, closeModalCrear);
    }

    // Inicialización al cargar
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TecladoVirtual !== 'undefined') {
            TecladoVirtual.attachAll();
        }
    });
</script>