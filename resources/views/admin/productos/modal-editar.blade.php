{{-- MODAL EDITAR ALIMENTO --}}
<div id="modal-editar-alimento" class="fixed inset-0 z-[100] overflow-y-auto hidden opacity-0 transition-all duration-300 backdrop-blur-sm">
    <div class="fixed inset-0 bg-black/60 dark:bg-black/80" onclick="closeModalEditar()"></div>
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative bg-white/95 dark:bg-zinc-800/95 backdrop-blur-xl border border-zinc-200 dark:border-zinc-700 w-full max-w-2xl rounded-[2.5rem] shadow-2xl transform opacity-0 translate-y-8 transition-all duration-300" id="modal-editar-panel">
            <div class="p-6 sm:p-10">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">Editar Platillo</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Actualiza la información y receta del platillo</p>
                    </div>
                    <button type="button" onclick="closeModalEditar()" class="text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <form id="formulario-editar-alimento" onsubmit="actualizarProducto(event)">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- Nombre --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Nombre del Platillo</label>
                            <input type="text" id="edit-nombre" name="nombre" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-4 mt-2 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" required>
                        </div>

                        {{-- Precio --}}
                        <div class="col-span-1">
                            <label class="text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Precio</label>
                            <input type="number" id="edit-precio" name="precio" step="0.01" min="0" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-4 mt-2 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" required>
                        </div>

                        {{-- Categoría --}}
                        <div class="col-span-1 relative">
                            <label class="text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Categoría</label>
                            <input type="text" id="edit-categoria_nombre" name="categoria_nombre" list="lista-categorias-editar" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-4 mt-2 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" autocomplete="off" required>
                            <input type="hidden" id="edit-categoria_id" name="categoria_id">
                            <datalist id="lista-categorias-editar">
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->nombre }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div class="col-span-1">
                            <label class="text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Tiempo (min)</label>
                            <input type="number" id="edit-tiempo_preparacion" name="tiempo_preparacion" min="1" placeholder="Ej: 15" 
                                class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-4 mt-2 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" required>
                        </div>

                        {{-- Descripción --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Descripción</label>
                            <textarea id="edit-descripcion" name="descripcion" rows="3" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-4 mt-2 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"></textarea>
                        </div>


                        {{-- Ingredientes --}}
                        <div class="col-span-1 sm:col-span-2">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <label class="text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Ingredientes de la Receta</label>
                                </div>
                                <button type="button" onclick="agregarIngrediente('editar')" class="inline-flex items-center gap-2 self-start bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-2xl font-black transition shadow-sm">
                                    <i class="fas fa-plus"></i> Agregar ingrediente
                                </button>
                            </div>
                            <div id="ingredientes-container-editar" class="space-y-4 mt-4"></div>
                        </div>
                    </div>

                    <div class="mt-10 flex flex-col sm:flex-row gap-4">
                        <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-black py-4 rounded-2xl transition shadow-lg shadow-emerald-900/20 dark:shadow-emerald-900/40" id="btn-actualizar">ACTUALIZAR PLATILLO</button>
                        <button type="button" onclick="closeModalEditar()" class="flex-1 bg-zinc-100 dark:bg-zinc-700/50 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-500 dark:text-zinc-400 font-black py-4 rounded-2xl transition">CANCELAR</button>
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
        document.getElementById('edit-descripcion').value      = producto.descripcion         ?? '';
        document.getElementById('edit-categoria_nombre').value = producto.categoria?.nombre   ?? '';
        document.getElementById('edit-categoria_id').value     = producto.categoria?.id       ?? '';
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
        data._method          = 'PUT';
        if (!data.categoria_id) {
            mostrarNotificacion('Selecciona una categoría válida', 'error');
            btn.textContent = original; btn.disabled = false; return;
        }
        ejecutarPeticion(RUTA_API_BASE + estadoGlobal.editandoId, data, btn, original, closeModalEditar);
    }
</script>