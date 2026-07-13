{{-- MODAL AGREGAR/EDITAR ALIMENTO --}}
<div id="modal-crear-alimento" class="fixed inset-y-0 right-0 left-[74px] sm:left-0 sm:inset-0 z-[9999] hidden opacity-0 transition-all duration-300 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="fixed inset-0 bg-black/60 dark:bg-black/80 -ml-[74px] sm:ml-0" onclick="closeModalCrear()"></div>

    <div class="relative bg-white/95 dark:bg-zinc-800/95 backdrop-blur-xl border border-zinc-200 dark:border-zinc-700 w-full max-w-xl sm:max-w-2xl max-h-[92vh] flex flex-col rounded-[1.5rem] sm:rounded-[2.5rem] shadow-2xl transform opacity-0 translate-y-8 transition-all duration-300 overflow-hidden" id="modal-crear-panel">

        <div class="p-5 sm:p-10 pb-4 sm:pb-6 border-b border-zinc-200 dark:border-zinc-700/30 flex justify-between items-start">
            <div>
                <h2 class="text-xl sm:text-3xl font-black text-zinc-900 dark:text-white tracking-tight" id="modal-title">Nuevo Platillo</h2>
                <p class="text-[10px] sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1" id="modal-subtitle">Configuración estética del menú</p>
            </div>
            <button onclick="closeModalCrear()" class="w-9 h-9 flex items-center justify-center text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition rounded-full active:scale-95 shrink-0">
                <i class="fas fa-times text-lg sm:text-2xl"></i>
            </button>
        </div>

        <form id="formulario-crear-producto" onsubmit="guardarProducto(event)" class="overflow-y-auto flex-1 p-5 sm:p-10 pt-4 sm:pt-6">
            <div class="grid grid-cols-2 gap-4 sm:gap-6">

                {{-- Nombre --}}
                <div class="col-span-2">
                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Nombre del Platillo</label>
                    <input type="text" id="nombre" name="nombre" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-zinc-900 dark:text-white placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-base" placeholder="Ej: Lasagna de la Casa" required>
                </div>

                {{-- Toggle: Se vende por peso --}}
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

                {{-- Precio fijo (se oculta si es por peso) --}}
                <div class="col-span-2 sm:col-span-1" id="grupo-precio-fijo-crear">
                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Precio</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-base" placeholder="0.00" required>
                </div>

                {{-- Precio por 100g (solo visible si es por peso) --}}
                <div class="col-span-2 sm:col-span-1 hidden" id="grupo-precio-peso-crear">
                    <label class="text-[11px] sm:text-xs font-black text-orange-500 uppercase tracking-widest ml-1">Precio por cada 100g</label>
                    <div class="flex items-center bg-zinc-50 dark:bg-zinc-900/50 border border-orange-500/30 rounded-2xl mt-1.5 focus-within:ring-2 focus-within:ring-orange-500 focus-within:border-orange-500 transition">
                        <span class="pl-4 pr-1.5 text-zinc-400 text-base font-bold select-none">$</span>
                        <input type="number" id="precio_por_100g" name="precio_por_100g" step="0.01" min="0" autocomplete="off" class="flex-1 min-w-0 bg-transparent p-3 sm:p-4 pl-0 text-zinc-900 dark:text-white placeholder:text-zinc-400 outline-none transition text-base" placeholder="50.00">
                    </div>
                    <p class="text-[9px] text-zinc-400 mt-1 ml-1">Ej: $50 por 100g → 700g = $350</p>
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Tiempo de Prep. (min)</label>
                    <input type="number" id="tiempo_preparacion" name="tiempo_preparacion" min="0" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-base" placeholder="Ej: 20" required>
                </div>

                {{-- Categoría HÍBRIDA --}}
                <div class="col-span-2 sm:col-span-1 relative">
                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Categoría</label>
                    <input type="text" id="categoria_nombre" name="categoria_nombre" list="lista-categorias" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-base" placeholder="Escribe o selecciona..." autocomplete="off" required>
                    <input type="hidden" id="categoria_id" name="categoria_id">
                    <datalist id="lista-categorias">
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->nombre }}"></option>
                        @endforeach
                    </datalist>
                </div>

                {{-- Descripción del Platillo --}}
                <div class="col-span-2">
                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="2" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-zinc-900 dark:text-white placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none text-base" placeholder="Describe qué lleva este platillo..."></textarea>
                </div>

                {{-- Ingredientes del Platillo --}}
                <div class="col-span-2">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                        <div>
                            <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Ingredientes del Platillo</label>
                            <p class="text-[9px] text-blue-500 font-bold mt-1 ml-1 tracking-wide uppercase">
                                <i class="fas fa-info-circle mr-1"></i> Selecciona los ingredientes y la cantidad.
                            </p>
                        </div>
                        <button type="button" onclick="agregarIngrediente('crear')" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto bg-blue-600 hover:bg-blue-500 active:scale-95 text-white px-4 py-2.5 rounded-xl font-black transition text-[11px] sm:text-xs tracking-wider shadow-sm mt-1 sm:mt-0">
                            <i class="fas fa-plus"></i> AGREGAR INGREDIENTE
                        </button>
                    </div>

                    <div id="ingredientes-container-crear" class="space-y-3 mt-3"></div>
                </div>

            </div>

            {{-- Botones de Acción inferiores --}}
            <div class="mt-8 pt-4 border-t border-zinc-200 dark:border-zinc-700/30 flex flex-col-reverse sm:flex-row gap-3">
                <button type="button" onclick="closeModalCrear()" class="w-full sm:flex-1 bg-zinc-100 dark:bg-zinc-700/50 hover:bg-zinc-200 dark:hover:bg-zinc-700 active:scale-95 text-zinc-500 dark:text-zinc-400 font-black py-3 sm:py-4 rounded-xl transition text-xs sm:text-sm tracking-widest">CANCELAR</button>
                <button type="submit" class="w-full sm:flex-1 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-black py-3 sm:py-4 rounded-xl transition shadow-lg shadow-blue-900/20 dark:shadow-blue-900/40 text-xs sm:text-sm tracking-widest" id="btn-guardar">GUARDAR CAMBIOS</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ─── Muestra/oculta precio fijo vs precio por 100g según el toggle ──────
    function toggleModoVentaPeso(tipo) {
        const checkbox   = document.getElementById(tipo === 'crear' ? 'se_vende_por_peso' : 'edit-se_vende_por_peso');
        const grupoFijo  = document.getElementById(tipo === 'crear' ? 'grupo-precio-fijo-crear' : 'grupo-precio-fijo-editar');
        const grupoPeso  = document.getElementById(tipo === 'crear' ? 'grupo-precio-peso-crear' : 'grupo-precio-peso-editar');
        const inputFijo  = document.getElementById(tipo === 'crear' ? 'precio' : 'edit-precio');
        const inputPeso  = document.getElementById(tipo === 'crear' ? 'precio_por_100g' : 'edit-precio_por_100g');

        const esPorPeso = checkbox.checked;

        grupoFijo.classList.toggle('hidden', esPorPeso);
        grupoPeso.classList.toggle('hidden', !esPorPeso);

        inputFijo.required = !esPorPeso;
        inputPeso.required = esPorPeso;

        if (esPorPeso) { inputFijo.value = 0; }
    }

    // ─── Cerrar modal crear ──────────────────────────────────────────────────
    function closeModalCrear() {
        _cerrarModal('modal-crear-alimento', 'modal-crear-panel');
    }
 
    // ─── Guardar producto (submit del formulario de creación) ──────────────
    function guardarProducto(event) {
        event.preventDefault();
        const btn = document.getElementById('btn-guardar');
        if (!btn || btn.disabled) return;
        const original = btn.textContent;
        btn.textContent = 'GUARDANDO...';
        btn.disabled    = true;
        const data          = _serializarFormulario('formulario-crear-producto');
        const catNombre     = document.getElementById('categoria_nombre').value;
        data.categoria_nombre = catNombre;
        data.categoria_id     = obtenerCategoriaIdPorNombre(catNombre);
        data.se_vende_por_peso = document.getElementById('se_vende_por_peso').checked ? 1 : 0;
        if (!data.categoria_id) {
            mostrarNotificacion('Selecciona una categoría válida', 'error');
            btn.textContent = original; btn.disabled = false; return;
        }
        ejecutarPeticion(RUTA_STORE, data, btn, original, closeModalCrear);
    }
</script>