{{-- Estilos para manejo de teclado virtual en PC --}}
<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modal-editar-alimento {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        
        body.teclado-virtual-abierto #modal-editar-panel {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important; 
        }
    }
</style>

{{-- MODAL EDITAR ALIMENTO --}}
<div id="modal-editar-alimento" class="fixed inset-y-0 right-0 left-[74px] sm:left-0 sm:inset-0 z-[100] overflow-y-auto hidden opacity-0 transition-all duration-300 backdrop-blur-sm">
    <div class="fixed inset-0 bg-black/60 dark:bg-black/80 -ml-[74px] sm:ml-0" onclick="closeModalEditar()"></div>
    <div class="flex min-h-screen items-center justify-center p-3 sm:p-4">
        <div class="relative bg-white/95 dark:bg-zinc-800/95 backdrop-blur-xl border border-zinc-200 dark:border-zinc-700 w-full max-w-2xl rounded-[1.5rem] sm:rounded-[2.5rem] shadow-2xl transform opacity-0 translate-y-8 transition-all duration-300 overflow-hidden" id="modal-editar-panel">
            <div class="p-5 sm:p-10">
                <div class="flex justify-between items-start mb-6 sm:mb-8">
                    <div>
                        <h2 class="text-xl sm:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">Editar Platillo</h2>
                        <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Actualiza la información y receta del platillo</p>
                    </div>
                    <button type="button" onclick="closeModalEditar()" class="w-9 h-9 flex items-center justify-center text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition rounded-full active:scale-95 shrink-0 outline-none">
                        <i class="fas fa-times text-lg sm:text-2xl"></i>
                    </button>
                </div>

                <form id="formulario-editar-alimento" onsubmit="actualizarProducto(event)">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        
                        {{-- Nombre --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Nombre del Platillo</label>
                            <input type="text" id="edit-nombre" name="nombre" readonly data-teclado="texto" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-base text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                        </div>

                        {{-- Toggle --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="flex items-center justify-between gap-3 bg-orange-500/5 border border-orange-500/20 rounded-2xl p-3.5 sm:p-4 cursor-pointer select-none">
                                <span class="flex items-center gap-2.5">
                                    <i class="fas fa-weight-hanging text-orange-500 text-sm"></i>
                                    <span class="text-xs sm:text-sm font-bold text-zinc-900 dark:text-white">Se vende por peso</span>
                                </span>
                                <span class="relative inline-flex items-center">
                                    <input type="checkbox" id="edit-se_vende_por_peso" name="se_vende_por_peso" class="peer sr-only" onchange="toggleModoVentaPeso('editar')">
                                    <span class="w-11 h-6 rounded-full bg-zinc-300 dark:bg-zinc-600 peer-checked:bg-orange-500 transition-colors"></span>
                                    <span class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform peer-checked:translate-x-5"></span>
                                </span>
                            </label>
                        </div>

                        {{-- Precios --}}
                        <div class="col-span-1" id="grupo-precio-fijo-editar">
                            <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Precio</label>
                            <input type="text" id="edit-precio" name="precio" readonly data-teclado="numerico" data-teclado-decimales="true" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-base text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                        </div>

                        <div class="col-span-1 hidden" id="grupo-precio-peso-editar">
                            <label class="text-[11px] sm:text-xs font-black text-orange-500 uppercase tracking-widest ml-1">Precio por cada 100g</label>
                            <input type="text" id="edit-precio_por_100g" name="precio_por_100g" readonly data-teclado="numerico" data-teclado-decimales="true" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-orange-500/30 rounded-2xl p-3 sm:p-4 mt-1.5 text-base text-zinc-900 dark:text-white outline-none transition" placeholder="50.00">
                        </div>

                        {{-- Tiempo --}}
                        <div class="col-span-1">
                            <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Tiempo (min)</label>
                            <input type="text" id="edit-tiempo_preparacion" name="tiempo_preparacion" readonly data-teclado="numerico" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-base text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                        </div>

                        {{-- Botones --}}
                        <div class="mt-8 sm:mt-10 flex flex-col-reverse sm:flex-row gap-3 sm:gap-4 col-span-2">
                            <button type="button" onclick="closeModalEditar()" class="flex-1 bg-zinc-100 dark:bg-zinc-700/50 text-zinc-500 dark:text-zinc-400 font-black py-3 sm:py-4 rounded-2xl transition text-sm">CANCELAR</button>
                            <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-black py-3 sm:py-4 rounded-2xl transition shadow-lg text-sm" id="btn-actualizar">ACTUALIZAR PLATILLO</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TecladoVirtual !== 'undefined') {
            TecladoVirtual.attachAll();
        }
    });
</script>