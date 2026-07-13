/**
 * comanda-gramaje.js
 * Modal de Gramaje: productos que se venden por peso (precio calculado a
 * partir de precio_por_100g) y la etiqueta informativa de gramaje para
 * productos normales.
 * Depende de: itemActivo, gramajePendiente, productoPorPesoPendiente,
 * ticketSubtotal (comanda-core.js); window._insertarItemEnTicket y
 * window.actualizarTotales (comanda-ticket.js); cerrarModal/mostrarError
 * (comanda-core.js).
 * Expone window.abrirModalGramajeNuevo, usada por comanda-ticket.js
 * (agregarAlTicket) cuando el producto se vende por peso.
 */
(function () {
    // Botón "ajustar gramaje" manual: para productos normales solo es una
    // etiqueta informativa; para productos por peso, también recalcula
    // el precio del item ya en el ticket.
    window.ajustarGramaje = function () {
        const modal = document.getElementById('modalGramaje'); const input = document.getElementById('gramajeInput');
        if (itemActivo) { input.value = itemActivo.dataset.gramaje === 'sin-gramaje' ? '' : itemActivo.dataset.gramaje; document.getElementById('modalGramajeTitulo').innerText = itemActivo.querySelector('.nombre-platillo').innerText; }
        else { input.value = gramajePendiente || ''; document.getElementById('modalGramajeTitulo').innerText = 'Gramaje'; }
        actualizarPrecioPreviewGramaje();
        modal.classList.remove('hidden'); modal.classList.add('flex');
        input.focus();
    };

    // Abre el modal de Gramaje para un producto NUEVO que se vende por
    // peso (clic desde el menú). El item todavía no existe en el ticket:
    // se crea hasta confirmar el gramaje en guardarGramajeDelItem().
    window.abrirModalGramajeNuevo = function (productoId, nombre, categoria, arrayModificadores, precioPor100g) {
        productoPorPesoPendiente = {
            id: productoId,
            nombre: nombre,
            categoria: categoria,
            mods: arrayModificadores || [],
            precioPor100g: parseFloat(precioPor100g) || 0
        };
        const modal = document.getElementById('modalGramaje');
        const input = document.getElementById('gramajeInput');
        input.value = '';
        document.getElementById('modalGramajeTitulo').innerText = nombre;
        actualizarPrecioPreviewGramaje();
        modal.classList.remove('hidden'); modal.classList.add('flex');
        input.focus();
    };

    // Botón "X" / click fuera del modal de Gramaje: si había un producto
    // por peso pendiente de agregar, se cancela por completo (no se crea
    // ninguna línea a medias en el ticket).
    window.cerrarModalGramaje = function () {
        productoPorPesoPendiente = null;
        cerrarModal('modalGramaje');
    };

    // Calcula y pinta el precio estimado ($ = precio_por_100g/100 * gramos)
    // mientras el mesero teclea, tanto para un producto nuevo pendiente
    // como para un item ya en el ticket que se vende por peso.
    function actualizarPrecioPreviewGramaje() {
        const preview = document.getElementById('gramajePrecioPreview');
        if (!preview) return;

        let precioPor100g = 0;
        if (productoPorPesoPendiente) {
            precioPor100g = productoPorPesoPendiente.precioPor100g;
        } else if (itemActivo && itemActivo.dataset.sePorPeso === '1') {
            precioPor100g = parseFloat(itemActivo.dataset.precio100g) || 0;
        }

        if (precioPor100g > 0) {
            const gramos = parseFloat(document.getElementById('gramajeInput').value) || 0;
            preview.innerText = gramos > 0 ? ('Precio: $' + ((precioPor100g / 100) * gramos).toFixed(2)) : ('$' + precioPor100g.toFixed(2) + ' por cada 100g');
        } else {
            preview.innerText = '';
        }
    }

    window.seleccionarGramajeRapido = function (gramos) {
        document.getElementById('gramajeInput').value = gramos.toString();
        actualizarPrecioPreviewGramaje();
    };

    window.guardarGramajeDelItem = function () {
        const val = document.getElementById('gramajeInput').value.trim();

        // Caso 1: producto nuevo por peso, agregado desde el menú.
        // Se calcula el precio y AHORA sí se inserta la línea en el ticket.
        if (productoPorPesoPendiente) {
            const gramos = parseFloat(val);
            if (!val || isNaN(gramos) || gramos <= 0) { mostrarError('Ingresa un gramaje válido.'); return; }

            const precioCalculado = (productoPorPesoPendiente.precioPor100g / 100) * gramos;

            _insertarItemEnTicket({
                id: productoPorPesoPendiente.id,
                nombre: productoPorPesoPendiente.nombre,
                precioUnitario: precioCalculado,
                categoria: productoPorPesoPendiente.categoria,
                arrayModificadores: productoPorPesoPendiente.mods,
                sePorPeso: true,
                precioPor100g: productoPorPesoPendiente.precioPor100g,
                gramaje: gramos
            });

            productoPorPesoPendiente = null;
            cerrarModal('modalGramaje');
            return;
        }

        // Caso 2: se está editando el gramaje de un item YA en el ticket.
        if (itemActivo) {
            if (itemActivo.dataset.sePorPeso === '1') {
                const gramos = parseFloat(val);
                if (!val || isNaN(gramos) || gramos <= 0) { mostrarError('Ingresa un gramaje válido.'); return; }

                const precioPor100g = parseFloat(itemActivo.dataset.precio100g) || 0;
                const nuevoPrecioUnitario = (precioPor100g / 100) * gramos;
                const cantidad = parseInt(itemActivo.dataset.cantidad, 10) || 1;

                // Se resta el precio anterior (por la cantidad ya agregada) y se
                // suma el nuevo, para que ticketSubtotal quede correcto.
                ticketSubtotal -= (parseFloat(itemActivo.dataset.precio) * cantidad);
                itemActivo.dataset.precio = nuevoPrecioUnitario;
                itemActivo.dataset.gramaje = gramos.toString();
                itemActivo.querySelector('.precio-platillo').innerText = '$' + (nuevoPrecioUnitario * cantidad).toFixed(2);
                itemActivo.querySelector('.gramaje-etiqueta').innerHTML = `<span class="text-[7px] text-[var(--text-muted)] font-bold bg-[var(--input-bg)] border border-[var(--border-color)] px-1.5 py-0.5 rounded-md shadow-sm">${gramos}g</span>`;
                ticketSubtotal += (nuevoPrecioUnitario * cantidad);

                actualizarTotales();
            } else {
                // Producto normal: el gramaje es solo una etiqueta informativa,
                // no afecta el precio (comportamiento original).
                const valOriginal = val || null;
                itemActivo.dataset.gramaje = valOriginal ? valOriginal.toString() : 'sin-gramaje';
                itemActivo.querySelector('.gramaje-etiqueta').innerHTML = valOriginal ? `<span class="text-[7px] text-[var(--text-muted)] font-bold bg-[var(--input-bg)] border border-[var(--border-color)] px-1.5 py-0.5 rounded-md shadow-sm">${valOriginal}g</span>` : '';
            }
            cerrarModal('modalGramaje');
            return;
        }

        // Caso 3 (compatibilidad): gramaje manual capturado ANTES de elegir
        // el producto (flujo original para productos que no son por peso).
        const val2 = val || null;
        gramajePendiente = val2;
        const ind = document.getElementById('indicador-gramaje-pendiente');
        if (val2) { ind.innerText = val2 + 'g'; ind.classList.remove('hidden'); } else { ind.classList.add('hidden'); }
        cerrarModal('modalGramaje');
    };

    window.anadirNumeroGramaje = function (num) { const i = document.getElementById('gramajeInput'); i.value = (i.value === '0' ? '' : i.value) + num; actualizarPrecioPreviewGramaje(); };
    window.borrarNumeroGramaje = function () { const i = document.getElementById('gramajeInput'); i.value = i.value.slice(0, -1); actualizarPrecioPreviewGramaje(); };
})();