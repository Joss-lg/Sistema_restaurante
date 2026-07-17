<style>
    /* Solo aplicamos el truco de subir el modal en pantallas grandes (computadoras/punto de venta) */
    @media (min-width: 768px) {
        /* 1. Mandamos el modal a la parte de arriba de la pantalla */
        body.teclado-virtual-abierto #modal-crear-alimento {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }

        /* 2. Hacemos que el modal sea más corto para que no choque con el teclado y active el scroll interno */
        body.teclado-virtual-abierto #modal-crear-panel {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important;
        }
    }
</style>

{{-- MODAL CREAR ALIMENTO --}}
<div id="modal-crear-alimento" class="fixed inset-y-0 right-0 left-[74px] sm:left-0 sm:inset-0 z-[9999] hidden opacity-0 transition-all duration-300 flex items-center justify-center p-3 sm:p-4">
    <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm -ml-[74px] sm:ml-0" onclick="closeModalCrear()"></div>

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

        <form id="formulario-crear-producto" onsubmit="guardarProducto(event)" enctype="multipart/form-data" class="overflow-y-auto overscroll-contain flex-1 p-5 sm:p-10 pt-4 sm:pt-6">
            <div class="grid grid-cols-2 gap-4 sm:gap-6">

                {{-- Nombre (TECLADO VIRTUAL DE TEXTO) --}}
                <div class="col-span-2">
                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Nombre del Platillo</label>
                    <input type="text" id="nombre" name="nombre" readonly data-teclado="texto" inputmode="none" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-zinc-900 dark:text-white placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-base" placeholder="Ej: Lasagna de la Casa" required>
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

                {{-- Precio fijo (se oculta si es por peso) — TECLADO VIRTUAL NUMÉRICO --}}
                <div class="col-span-2 sm:col-span-1" id="grupo-precio-fijo-crear">
                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Precio</label>
                    <input type="text" id="precio" name="precio" pattern="[0-9]*\.?[0-9]*" readonly data-teclado="numerico" inputmode="none" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-base" placeholder="0.00" required>
                </div>

                {{-- Precio por 100g (solo visible si es por peso) — TECLADO VIRTUAL NUMÉRICO --}}
                <div class="col-span-2 sm:col-span-1 hidden" id="grupo-precio-peso-crear">
                    <label class="text-[11px] sm:text-xs font-black text-orange-500 uppercase tracking-widest ml-1">Precio por cada 100g</label>
                    <div class="flex items-center bg-zinc-50 dark:bg-zinc-900/50 border border-orange-500/30 rounded-2xl mt-1.5 focus-within:ring-2 focus-within:ring-orange-500 focus-within:border-orange-500 transition">
                        <span class="pl-4 pr-1.5 text-zinc-400 text-base font-bold select-none">$</span>
                        <input type="text" id="precio_por_100g" name="precio_por_100g" pattern="[0-9]*\.?[0-9]*" autocomplete="off" readonly data-teclado="numerico" inputmode="none" class="flex-1 min-w-0 bg-transparent p-3 sm:p-4 pl-0 text-zinc-900 dark:text-white placeholder:text-zinc-400 outline-none transition text-base" placeholder="50.00">
                    </div>
                    <p class="text-[9px] text-zinc-400 mt-1 ml-1">Ej: $50 por 100g → 700g = $350</p>
                </div>

                {{-- Categoría HÍBRIDA — TECLADO VIRTUAL DE TEXTO --}}
                <div class="col-span-2 sm:col-span-1 relative">
                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Categoría</label>
                    <input type="text" id="categoria_nombre" name="categoria_nombre" list="lista-categorias" readonly data-teclado="texto" inputmode="none" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-zinc-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-base" placeholder="Escribe o selecciona..." autocomplete="off" required>
                    <input type="hidden" id="categoria_id" name="categoria_id">
                    <datalist id="lista-categorias">
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->nombre }}"></option>
                        @endforeach
                    </datalist>
                </div>

                {{-- Descripción del Platillo — TECLADO VIRTUAL DE TEXTO --}}
                <div class="col-span-2">
                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="2" readonly data-teclado="texto" inputmode="none" class="w-full bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-3 sm:p-4 mt-1.5 text-zinc-900 dark:text-white placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none text-base" placeholder="Describe qué lleva este platillo..."></textarea>
                </div>

                {{-- Imagen del Platillo --}}
                <div class="col-span-2">
                    <label class="text-[11px] sm:text-xs font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-widest ml-1">
                        Imagen del Platillo <span class="normal-case font-semibold text-zinc-400">(Opcional)</span>
                    </label>

                    <div class="mt-1.5 relative w-full rounded-2xl overflow-hidden border-2 border-dashed border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-900/40">
                        <label for="imagen" class="flex flex-col items-center justify-center w-full aspect-video sm:aspect-[21/9] cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-900/70 transition relative">
                            <div id="imagen-placeholder-crear" class="flex flex-col items-center justify-center text-center px-4">
                                <i class="fas fa-image text-2xl text-zinc-400 mb-2"></i>
                                <p class="text-xs sm:text-sm font-bold text-zinc-700 dark:text-zinc-300">Haz clic para subir</p>
                                <p class="text-[10px] text-zinc-400 mt-0.5">PNG, JPG, WEBP hasta 2MB</p>
                            </div>
                            <img id="imagen-preview-crear" src="#" alt="Preview" class="hidden absolute inset-0 w-full h-full object-cover">
                            <input id="imagen" name="imagen" type="file" accept="image/png, image/jpeg, image/webp" class="hidden" onchange="previewImagen(event, 'crear')">
                        </label>

                        <button type="button" id="btn-quitar-imagen-crear" onclick="quitarImagenCrear()"
                            class="hidden absolute top-2.5 right-2.5 w-8 h-8 rounded-full bg-black/60 hover:bg-red-500 text-white flex items-center justify-center transition active:scale-90 backdrop-blur-sm z-10 shadow-lg">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
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
    // ─── Bloqueo/desbloqueo de scroll del fondo mientras un modal está abierto ──
    // Evita que al llegar al tope del scroll interno del modal, el navegador
    // le "pase" el scroll a la página de atrás (efecto de traslape/salto raro).
    window.bloquearScrollFondo = function () {
        document.body.style.overflow = 'hidden';
    };
    window.desbloquearScrollFondo = function () {
        document.body.style.overflow = '';
    };

   // ─── Abrir modal crear ───────────────────────────────────────────────────
    window.openModalCrear = window.abrirModalCrear = function() {
        const modal = document.getElementById('modal-crear-alimento');
        const panel = document.getElementById('modal-crear-panel');

        // NUEVO: si algún contenedor padre tiene transform/filter, "fixed" deja
        // de posicionarse contra toda la pantalla. Movemos el modal para que
        // sea hijo directo de <body> y así siempre cubra la ventana completa.
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        if (modal && panel) {
            modal.classList.remove('hidden');
            bloquearScrollFondo();
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                panel.classList.remove('opacity-0', 'translate-y-8');
            }, 10);
        }
    };

    // ─── Cerrar modal crear ──────────────────────────────────────────────────
    window.closeModalCrear = function() {
        if (typeof _cerrarModal === 'function') {
            _cerrarModal('modal-crear-alimento', 'modal-crear-panel');
        } else {
            const modal = document.getElementById('modal-crear-alimento');
            const panel = document.getElementById('modal-crear-panel');
            
            modal.classList.add('opacity-0');
            panel.classList.add('opacity-0', 'translate-y-8');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
        desbloquearScrollFondo();
        const form = document.getElementById('formulario-crear-producto');
        if (form) form.reset();
        resetPreviewImagen('crear');
    };

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

    // ─── Preview de imagen (compartido crear/editar) ─────────────────────────
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

    // ─── Resetea el preview de imagen a su estado vacío ──────────────────────
    function resetPreviewImagen(tipo) {
        const preview     = document.getElementById(tipo === 'crear' ? 'imagen-preview-crear' : 'imagen-preview-editar');
        const placeholder = document.getElementById(tipo === 'crear' ? 'imagen-placeholder-crear' : 'imagen-placeholder-editar');
        const btnQuitar   = document.getElementById(tipo === 'crear' ? 'btn-quitar-imagen-crear' : 'btn-quitar-imagen-editar');
        if (preview) { preview.src = '#'; preview.classList.add('hidden'); }
        if (placeholder) { placeholder.classList.remove('hidden'); }
        if (btnQuitar) { btnQuitar.classList.add('hidden'); }
    }

    // ─── Botón ✕ en CREAR: solo limpia el archivo seleccionado ──────────────
    function quitarImagenCrear() {
        const inputImagen = document.getElementById('imagen');
        if (inputImagen) inputImagen.value = '';
        resetPreviewImagen('crear');
    }

    // ─── Envío multipart genérico (soporta archivos) ─────────────────────────
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

            setTimeout(() => location.reload(), 500);
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

    // ─── Guardar producto (submit del formulario de creación) ──────────────
    function guardarProducto(event) {
        event.preventDefault();
        const btn = document.getElementById('btn-guardar');
        if (!btn || btn.disabled) return;
        const original = btn.textContent;
        btn.textContent = 'GUARDANDO...';
        btn.disabled    = true;

        const catNombre = document.getElementById('categoria_nombre').value;
        const catId     = obtenerCategoriaIdPorNombre(catNombre);
        if (!catId) {
            mostrarNotificacion('Selecciona una categoría válida', 'error');
            btn.textContent = original; btn.disabled = false; return;
        }
        document.getElementById('categoria_id').value = catId;

        const formEl   = document.getElementById('formulario-crear-producto');
        const formData = new FormData(formEl);
        formData.set('categoria_id', catId);
        formData.set('se_vende_por_peso', document.getElementById('se_vende_por_peso').checked ? '1' : '0');

        enviarFormularioConImagen(RUTA_STORE, formData, btn, original, closeModalCrear);
    }
</script>