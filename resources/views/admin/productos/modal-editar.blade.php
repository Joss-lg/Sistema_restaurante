<style>
    /* Solo aplicamos el truco de subir el modal en pantallas grandes (computadoras/punto de venta) */
    @media (min-width: 768px) {
        /* 1. Mandamos el modal a la parte de arriba de la pantalla */
        body.teclado-virtual-abierto #modal-editar-wrapper {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }

        /* 2. Hacemos que el modal sea más corto para que no choque con el teclado y active el scroll interno */
        body.teclado-virtual-abierto #modal-editar-panel {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important;
            overflow-y: auto !important;
        }
    }
</style>

{{-- MODAL EDITAR ALIMENTO --}}
<div id="modal-editar-alimento" class="fixed inset-y-0 right-0 left-[74px] sm:left-0 sm:inset-0 z-[100] overflow-y-auto hidden opacity-0 transition-all duration-300 backdrop-blur-sm">
    <div class="fixed inset-0 bg-black/60 dark:bg-black/80 -ml-[74px] sm:ml-0" onclick="closeModalEditar()"></div>
    <div id="modal-editar-wrapper" class="flex min-h-screen items-center justify-center p-3 sm:p-4">
        <div class="relative bg-white/95 dark:bg-zinc-800/95 backdrop-blur-xl border border-zinc-200 dark:border-zinc-700 w-full max-w-2xl rounded-[1.5rem] sm:rounded-[2.5rem] shadow-2xl transform opacity-0 translate-y-8 transition-all duration-300" id="modal-editar-panel">
            <div class="p-5 sm:p-10">
                <div class="flex justify-between items-start mb-6 sm:mb-8">
                    <div>
                        <h2 class="text-xl sm:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">Editar Platillo</h2>
                        <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Actualiza la información y receta del platillo</p>
                    </div>
                    <button type="button" onclick="closeModalEditar()" class="w-9 h-9 flex items-center justify-center text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition rounded-full active:scale-95 shrink-0">
                        <i class="fas fa-times text-lg sm:text-2xl"></i>
                    </button>
                </div>

                <form id="formulario-editar-alimento" onsubmit="actualizarProducto(event)">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        {{-- Nombre (TECLADO VIRTUAL DE TEXTO) --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Nombre del Platillo</label>
                            <input type="text" id="edit-nombre" name="nombre" readonly data-teclado="texto" inputmode="none" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 sm:mt-2 text-base text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" required>
                        </div>

                        {{-- Toggle: Se vende por peso --}}
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

                        {{-- Precio fijo (se oculta si es por peso) — TECLADO VIRTUAL NUMÉRICO --}}
                        <div class="col-span-1" id="grupo-precio-fijo-editar">
                            <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Precio</label>
                            <input type="text" id="edit-precio" name="precio" pattern="[0-9]*\.?[0-9]*" readonly data-teclado="numerico" inputmode="none" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 sm:mt-2 text-base text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" required>
                        </div>

                        {{-- Precio por 100g (solo visible si es por peso) — TECLADO VIRTUAL NUMÉRICO --}}
                        <div class="col-span-1 hidden" id="grupo-precio-peso-editar">
                            <label class="text-[11px] sm:text-xs font-black text-orange-500 uppercase tracking-widest ml-1">Precio por cada 100g</label>
                            <div class="flex items-center bg-zinc-50 dark:bg-zinc-900/50 border border-orange-500/30 rounded-2xl mt-1.5 sm:mt-2 focus-within:ring-2 focus-within:ring-orange-500 focus-within:border-orange-500 transition">
                                <span class="pl-4 pr-1.5 text-zinc-400 text-base font-bold select-none">$</span>
                                <input type="text" id="edit-precio_por_100g" name="precio_por_100g" pattern="[0-9]*\.?[0-9]*" autocomplete="off" readonly data-teclado="numerico" inputmode="none" class="flex-1 min-w-0 bg-transparent p-3 sm:p-4 pl-0 text-base text-zinc-900 dark:text-white placeholder:text-zinc-400 outline-none transition" placeholder="50.00">
                            </div>
                            <p class="text-[9px] text-zinc-400 mt-1 ml-1">Ej: $50 por 100g → 700g = $350</p>
                        </div>

                        {{-- Categoría — TECLADO VIRTUAL DE TEXTO --}}
                        <div class="col-span-1 relative">
                            <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Categoría</label>
                            <input type="text" id="edit-categoria_nombre" name="categoria_nombre" list="lista-categorias-editar" readonly data-teclado="texto" inputmode="none" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 sm:mt-2 text-base text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" autocomplete="off" required>
                            <input type="hidden" id="edit-categoria_id" name="categoria_id">
                            <datalist id="lista-categorias-editar">
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->nombre }}"></option>
                                @endforeach
                            </datalist>
                        </div>

    

                        {{-- Descripción — TECLADO VIRTUAL DE TEXTO --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Descripción</label>
                            <textarea id="edit-descripcion" name="descripcion" rows="3" readonly data-teclado="texto" inputmode="none" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 sm:mt-2 text-base text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"></textarea>
                        </div>


                        {{-- Ingredientes --}}
                        <div class="col-span-1 sm:col-span-2">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 sm:gap-3">
                                <div>
                                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Ingredientes de la Receta</label>
                                </div>
                                <button type="button" onclick="agregarIngrediente('editar')" class="inline-flex items-center justify-center gap-2 w-full sm:w-auto self-start bg-blue-600 hover:bg-blue-500 active:scale-95 text-white px-4 py-2.5 sm:py-2 rounded-2xl font-black transition text-[11px] sm:text-sm shadow-sm">
                                    <i class="fas fa-plus"></i> Agregar ingrediente
                                </button>
                            </div>
                            <div id="ingredientes-container-editar" class="space-y-3 sm:space-y-4 mt-3 sm:mt-4"></div>
                        </div>
                    </div>

                    <div class="mt-8 sm:mt-10 flex flex-col-reverse sm:flex-row gap-3 sm:gap-4">
                        <button type="button" onclick="closeModalEditar()" class="flex-1 bg-zinc-100 dark:bg-zinc-700/50 hover:bg-zinc-200 dark:hover:bg-zinc-700 active:scale-95 text-zinc-500 dark:text-zinc-400 font-black py-3 sm:py-4 rounded-2xl transition text-sm">CANCELAR</button>
                        <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-black py-3 sm:py-4 rounded-2xl transition shadow-lg shadow-emerald-900/20 dark:shadow-emerald-900/40 text-sm" id="btn-actualizar">ACTUALIZAR PLATILLO</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    // ─── Cerrar modal editar ─────────────────────────────────────────────────
    function closeModalEditar() {
        _cerrarModal('modal-editar-alimento', 'modal-editar-panel');
    }
 
    // ─── Abrir modal con datos del producto (trigger desde la tarjeta) ─────
    function editarProducto(id) {
        if (!tienePermisoEditar) { mostrarNotificacion('Sin permisos para editar', 'error'); return; }
        const producto = estadoGlobal.productosMap[id];
        if (!producto) return;
        estadoGlobal.editandoId = id;
        document.getElementById('edit-nombre').value           = producto.nombre              ?? '';
        document.getElementById('edit-precio').value           = producto.precio              ?? '';
        document.getElementById('edit-precio_por_100g').value  = producto.precio_por_100g      ?? '';
        document.getElementById('edit-se_vende_por_peso').checked = !!producto.se_vende_por_peso;
        document.getElementById('edit-descripcion').value      = producto.descripcion         ?? '';
        document.getElementById('edit-categoria_nombre').value = producto.categoria?.nombre   ?? '';
        document.getElementById('edit-categoria_id').value     = producto.categoria?.id       ?? '';
        toggleModoVentaPeso('editar');
        llenarIngredientesEdicion(producto);
        _abrirModal('modal-editar-alimento', 'modal-editar-panel');
    }
 
    // ─── Actualizar producto (submit del formulario de edición) ────────────
    function actualizarProducto(event) {
        event.preventDefault();
        if (!tienePermisoEditar) { mostrarNotificacion('Sin autorización para editar', 'error'); return; }
        const btn = document.getElementById('btn-actualizar');
        if (!btn || btn.disabled) return;
        const original = btn.textContent;
        btn.textContent = 'ACTUALIZANDO...';
        btn.disabled    = true;
        const data            = _serializarFormulario('formulario-editar-alimento');
        data.categoria_nombre = document.getElementById('edit-categoria_nombre').value;
        data.categoria_id     = obtenerCategoriaIdPorNombre(data.categoria_nombre);
        data.se_vende_por_peso = document.getElementById('edit-se_vende_por_peso').checked ? 1 : 0;
        data._method          = 'PUT';
        if (!data.categoria_id) {
            mostrarNotificacion('Selecciona una categoría válida', 'error');
            btn.textContent = original; btn.disabled = false; return;
        }
        ejecutarPeticion(RUTA_API_BASE + estadoGlobal.editandoId, data, btn, original, closeModalEditar);
    }
</script>