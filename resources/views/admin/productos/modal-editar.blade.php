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
<div id="modal-editar-alimento" class="fixed inset-y-0 right-0 left-[74px] sm:left-0 sm:inset-0 z-[100] overflow-y-auto overscroll-contain hidden opacity-0 transition-all duration-300">
    <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm -ml-[74px] sm:ml-0" onclick="closeModalEditar()"></div>
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

                <form id="formulario-editar-alimento" onsubmit="actualizarProducto(event)" enctype="multipart/form-data">
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

                        {{-- Imagen del Platillo --}}
                        <div class="col-span-1 sm:col-span-2">
                            <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">
                                Imagen del Platillo <span class="normal-case font-semibold text-zinc-400">(Opcional)</span>
                            </label>

                            <div class="mt-1.5 sm:mt-2 relative w-full rounded-2xl overflow-hidden border-2 border-dashed border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-900/40">
                                <label for="edit-imagen" class="flex flex-col items-center justify-center w-full aspect-video sm:aspect-[21/9] cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-900/70 transition relative">
                                    <div id="imagen-placeholder-editar" class="flex flex-col items-center justify-center text-center px-4">
                                        <i class="fas fa-image text-2xl text-zinc-400 mb-2"></i>
                                        <p class="text-xs sm:text-sm font-bold text-zinc-700 dark:text-zinc-300">Haz clic para subir</p>
                                        <p class="text-[10px] text-zinc-400 mt-0.5">PNG, JPG, WEBP hasta 2MB</p>
                                    </div>
                                    <img id="imagen-preview-editar" src="#" alt="Preview" class="hidden absolute inset-0 w-full h-full object-cover">
                                    <input id="edit-imagen" name="imagen" type="file" accept="image/png, image/jpeg, image/webp" class="hidden" onchange="previewImagen(event, 'editar')">
                                </label>

                                <button type="button" id="btn-quitar-imagen-editar" onclick="quitarImagenEditar()"
                                    class="hidden absolute top-2.5 right-2.5 w-8 h-8 rounded-full bg-black/60 hover:bg-red-500 text-white flex items-center justify-center transition active:scale-90 backdrop-blur-sm z-10 shadow-lg">
                                    <i class="fas fa-times text-sm"></i>
                                </button>
                            </div>

                            <input type="checkbox" id="edit-quitar_imagen" name="quitar_imagen" value="1" class="hidden">
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
    // ─── Bloqueo/desbloqueo de scroll del fondo (redeclarado por si este
    // archivo carga antes que modal-crear.blade.php) ─────────────────────────
    window.bloquearScrollFondo = function () {
        document.body.style.overflow = 'hidden';
    };
    window.desbloquearScrollFondo = function () {
        document.body.style.overflow = '';
    };

    // ─── Cerrar modal editar ─────────────────────────────────────────────────
    function closeModalEditar() {
        _cerrarModal('modal-editar-alimento', 'modal-editar-panel');
        desbloquearScrollFondo();
        const form = document.getElementById('formulario-editar-alimento');
        if (form) form.reset();
        resetPreviewImagen('editar');
    }

    // ─── Preview de imagen (redeclarado por si este archivo carga antes que modal-crear) ───
    function previewImagen(event, tipo) {
        const input = event.target;
        const preview     = document.getElementById(tipo === 'crear' ? 'imagen-preview-crear' : 'imagen-preview-editar');
        const placeholder = document.getElementById(tipo === 'crear' ? 'imagen-placeholder-crear' : 'imagen-placeholder-editar');
        const btnQuitar   = document.getElementById(tipo === 'crear' ? 'btn-quitar-imagen-crear' : 'btn-quitar-imagen-editar');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
                if (btnQuitar) btnQuitar.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);

            if (tipo === 'editar') {
                const chkQuitar = document.getElementById('edit-quitar_imagen');
                if (chkQuitar) chkQuitar.checked = false;
            }
        }
    }

    function resetPreviewImagen(tipo) {
        const preview     = document.getElementById(tipo === 'crear' ? 'imagen-preview-crear' : 'imagen-preview-editar');
        const placeholder = document.getElementById(tipo === 'crear' ? 'imagen-placeholder-crear' : 'imagen-placeholder-editar');
        const btnQuitar   = document.getElementById(tipo === 'crear' ? 'btn-quitar-imagen-crear' : 'btn-quitar-imagen-editar');
        if (preview) { preview.src = '#'; preview.classList.add('hidden'); }
        if (placeholder) { placeholder.classList.remove('hidden'); }
        if (btnQuitar) { btnQuitar.classList.add('hidden'); }
    }

    // ─── Botón ✕ en EDITAR: marca la imagen actual para borrarse en el servidor ───
    function quitarImagenEditar() {
        const inputImagen = document.getElementById('edit-imagen');
        const chkQuitar    = document.getElementById('edit-quitar_imagen');
        if (inputImagen) inputImagen.value = '';
        if (chkQuitar) chkQuitar.checked = true;
        resetPreviewImagen('editar');
    }

    // ─── Envío multipart genérico (redeclarado por si este archivo carga antes que modal-crear) ───
    // FIX: ya NO recarga la página con location.reload(). Ahora solo refresca
    // los datos vía AJAX (cargarProductos / cargarEstadisticas), por lo que
    // el scroll de la página se mantiene donde estaba.
    function enviarFormularioConImagen(url, formData, btn, textoOriginal, onSuccess) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
        })
        .then(async (response) => {
            const json = await response.json().catch(() => ({}));

            if (!response.ok) {
                const mensaje = json.message || 'Ocurrió un error al guardar.';
                if (typeof mostrarNotificacion === 'function') {
                    mostrarNotificacion(mensaje, 'error');
                } else {
                    alert(mensaje);
                }
                btn.textContent = textoOriginal;
                btn.disabled = false;
                return;
            }

            if (typeof mostrarNotificacion === 'function') {
                mostrarNotificacion(json.message || 'Guardado correctamente.', 'success');
            }

            if (typeof onSuccess === 'function') onSuccess();

            // Antes: setTimeout(() => location.reload(), 500);
            // Ahora: solo refrescamos los datos, sin recargar toda la página.
            if (typeof cargarProductos === 'function')    cargarProductos();
            if (typeof cargarEstadisticas === 'function')  cargarEstadisticas();

            btn.textContent = textoOriginal;
            btn.disabled = false;
        })
        .catch((err) => {
            console.error(err);
            if (typeof mostrarNotificacion === 'function') {
                mostrarNotificacion('Error de conexión al guardar.', 'error');
            } else {
                alert('Error de conexión al guardar.');
            }
            btn.textContent = textoOriginal;
            btn.disabled = false;
        });
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

        document.getElementById('edit-quitar_imagen').checked = false;
        document.getElementById('edit-imagen').value = '';
        if (producto.imagen_url) {
            document.getElementById('imagen-preview-editar').src = producto.imagen_url;
            document.getElementById('imagen-preview-editar').classList.remove('hidden');
            document.getElementById('imagen-placeholder-editar').classList.add('hidden');
            document.getElementById('btn-quitar-imagen-editar').classList.remove('hidden');
        } else {
            resetPreviewImagen('editar');
        }

        toggleModoVentaPeso('editar');
        llenarIngredientesEdicion(producto);
        bloquearScrollFondo();

        // NUEVO: mismo fix que en crear — mueve el modal a <body> para que
        // el fondo oscuro cubra toda la pantalla, sin importar el layout.
        const modalEditar = document.getElementById('modal-editar-alimento');
        if (modalEditar && modalEditar.parentElement !== document.body) {
            document.body.appendChild(modalEditar);
        }

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

        const catNombre = document.getElementById('edit-categoria_nombre').value;
        const catId     = obtenerCategoriaIdPorNombre(catNombre);
        if (!catId) {
            mostrarNotificacion('Selecciona una categoría válida', 'error');
            btn.textContent = original; btn.disabled = false; return;
        }
        document.getElementById('edit-categoria_id').value = catId;

        const formEl   = document.getElementById('formulario-editar-alimento');
        const formData = new FormData(formEl);
        formData.set('categoria_id', catId);
        formData.set('se_vende_por_peso', document.getElementById('edit-se_vende_por_peso').checked ? '1' : '0');
        formData.set('_method', 'PUT');

        if (!document.getElementById('edit-quitar_imagen').checked) {
            formData.delete('quitar_imagen');
        }

        enviarFormularioConImagen(RUTA_API_BASE + estadoGlobal.editandoId, formData, btn, original, closeModalEditar);
    }
</script>