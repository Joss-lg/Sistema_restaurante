{{-- MODAL AGREGAR/EDITAR ALIMENTO --}}
<div id="modal-crear-alimento" class="fixed inset-y-0 right-0 left-[74px] sm:left-0 sm:inset-0 z-[9999] hidden opacity-0 transition-all duration-300 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="fixed inset-0 bg-black/80 -ml-[74px] sm:ml-0" onclick="closeModalCrear()"></div>
    
    <div class="relative glass-card bg-[var(--card-color)] border border-[var(--border-color)] w-full max-w-xl sm:max-w-2xl max-h-[92vh] flex flex-col rounded-[1.5rem] sm:rounded-[2.5rem] shadow-2xl transform opacity-0 translate-y-8 transition-all duration-300 overflow-hidden" id="modal-crear-panel">
        
        <div class="p-5 sm:p-10 pb-4 sm:pb-6 border-b border-[var(--border-color)]/30 flex justify-between items-start">
            <div>
                <h2 class="text-xl sm:text-3xl font-black text-[var(--text-color)] tracking-tight" id="modal-title">Nuevo Platillo</h2>
                <p class="text-[10px] sm:text-sm text-[var(--text-muted)] mt-1" id="modal-subtitle">Configuración estética del menú</p>
            </div>
            <button onclick="closeModalCrear()" class="text-gray-500 hover:text-white transition p-1">
                <i class="fas fa-times text-lg sm:text-2xl"></i>
            </button>
        </div>

        <form id="formulario-crear-alimento" onsubmit="guardarAlimento(event)" class="overflow-y-auto flex-1 p-5 sm:p-10 pt-4 sm:pt-6 custom-scrollbar">
            <div class="grid grid-cols-2 gap-4 sm:gap-6">
                
                {{-- Nombre --}}
                <div class="col-span-2">
                    <label class="text-[11px] sm:text-xs font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Nombre del Platillo</label>
                    <input type="text" id="nombre" name="nombre" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-3 sm:p-4 mt-1.5 text-[var(--text-color)] placeholder:text-[var(--text-muted)] focus:ring-2 focus:ring-blue-500 transition text-sm sm:text-base" placeholder="Ej: Lasagna de la Casa" required>
                </div>
                
                {{-- Precio --}}
                <div class="col-span-2 sm:col-span-1">
                    <label class="text-[11px] sm:text-xs font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Precio</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-3 sm:p-4 mt-1.5 text-[var(--text-color)] focus:ring-2 focus:ring-blue-500 transition text-sm sm:text-base" placeholder="0.00" required>
                </div>
                
                {{-- Categoría HÍBRIDA --}}
                <div class="col-span-2 sm:col-span-1 relative">
                    <label class="text-[11px] sm:text-xs font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Categoría</label>
                    <input type="text" id="categoria_nombre" name="categoria_nombre" list="lista-categorias" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-3 sm:p-4 mt-1.5 text-[var(--text-color)] focus:ring-2 focus:ring-blue-500 transition text-sm sm:text-base" placeholder="Escribe o selecciona..." autocomplete="off" required>
                    <input type="hidden" id="categoria_id" name="categoria_id">
                    <datalist id="lista-categorias">
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->nombre }}"></option>
                        @endforeach
                    </datalist>
                </div>

                {{-- Descripción del Platillo --}}
                <div class="col-span-2">
                    <label class="text-[11px] sm:text-xs font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="2" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-3 sm:p-4 mt-1.5 text-[var(--text-color)] placeholder:text-[var(--text-muted)] focus:ring-2 focus:ring-blue-500 transition resize-none text-sm sm:text-base" placeholder="Describe qué lleva este platillo..."></textarea>
                </div>

                {{-- Ingredientes del Platillo --}}
                <div class="col-span-2">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                        <div>
                            <label class="text-[11px] sm:text-xs font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Ingredientes del Platillo</label>
                            <p class="text-[9px] text-[#3B82F6] font-bold mt-1 ml-1 tracking-wide uppercase">
                                <i class="fas fa-info-circle mr-1"></i> Selecciona los ingredientes y la cantidad.
                            </p>
                        </div>
                        <button type="button" onclick="agregarIngrediente('crear')" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto bg-blue-600 hover:bg-blue-500 text-white px-4 py-2.5 rounded-xl font-black transition text-[11px] sm:text-xs tracking-wider shadow-sm mt-1 sm:mt-0">
                            <i class="fas fa-plus"></i> AGREGAR INGREDIENTE
                        </button>
                    </div>

                    <div id="ingredientes-container-crear" class="space-y-3 mt-3"></div>
                </div>

            </div>

            {{-- Botones de Acción inferiores --}}
            <div class="mt-8 pt-4 border-t border-[var(--border-color)]/30 flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" onclick="closeModalCrear()" class="w-full sm:flex-1 bg-gray-800 hover:bg-gray-700 text-gray-400 font-black py-3 sm:py-4 rounded-xl transition text-xs sm:text-sm tracking-widest">CANCELAR</button>
                <button type="submit" class="w-full sm:flex-1 bg-blue-600 hover:bg-blue-500 text-white font-black py-3 sm:py-4 rounded-xl transition shadow-lg shadow-blue-900/40 text-xs sm:text-sm tracking-widest" id="btn-guardar">GUARDAR CAMBIOS</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Scrollbar estético optimizado */
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: var(--border-color, #333);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>