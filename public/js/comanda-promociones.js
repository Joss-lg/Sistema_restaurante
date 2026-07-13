/**
 * comanda-promociones.js
 * Modal y aplicación de promociones (porcentaje, descuento fijo, 2x1,
 * combo) como descuento automático del ticket.
 * Depende de: descuentoPorcentaje, promocion2x1Activa/Nombre,
 * comboActivo/Nombre/ProductoIds/Monto, ticketSubtotal (comanda-core.js);
 * window.actualizarTotales y window.calcularDescuentoComboMonto
 * (comanda-ticket.js); cerrarModal/mostrarError/mostrarExito/mostrarToast
 * (comanda-core.js).
 */
(function () {
    const config = window.ComandaConfig || {};

    window.mostrarPromociones = function () {
        const modal = document.getElementById('modalPromociones');
        const contenedor = document.getElementById('listaPromocionesMesero');
        if (!modal || !contenedor) { mostrarError('No se encontró el modal de promociones.'); return; }

        contenedor.innerHTML = `<div class="text-xs text-[var(--text-muted)] text-center py-8"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando promociones...</div>`;
        modal.classList.remove('hidden'); modal.classList.add('flex');

        fetch(config.rutas.comandaPromociones, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json().then(data => ({ ok: res.ok, status: res.status, data })))
        .then(({ ok, status, data }) => {
            if (!ok || data.success === false) {
                const msg = (data && data.message) ? data.message : `Error del servidor (${status}).`;
                contenedor.innerHTML = `<div class="text-xs text-red-500 text-center py-8">${msg}</div>`;
                console.error('promocionesActivas error:', status, data);
                return;
            }

            if (!Array.isArray(data.promociones) || data.promociones.length === 0) {
                contenedor.innerHTML = `<div class="text-xs text-[var(--text-muted)] text-center py-8">No hay promociones activas hoy.</div>`;
                return;
            }

            contenedor.innerHTML = '';
            data.promociones.forEach(promo => {
                const valor = promo.tipo_promocion === 'porcentaje' ? `${parseInt(promo.valor_descuento, 10)}%`
                    : promo.tipo_promocion === 'descuento_fijo' ? `$${parseFloat(promo.valor_descuento).toFixed(2)}`
                    : promo.tipo_promocion === 'dos_por_uno' ? '2x1'
                    : 'Combo';

                const promoAttr = JSON.stringify(promo).replace(/"/g, '&quot;');

                contenedor.insertAdjacentHTML('beforeend', `
                    <button type="button" data-promo="${promoAttr}" onclick="aplicarPromocion(this)" class="w-full text-left rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] px-4 py-4 hover:border-[#3b82f6]/50 transition-all flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-[var(--text-main)] truncate">${promo.nombre}</p>
                            ${promo.descripcion ? `<p class="text-[11px] text-[var(--text-muted)] mt-1 line-clamp-2">${promo.descripcion}</p>` : ''}
                        </div>
                        <span class="text-lg font-black text-[#3b82f6] shrink-0">${valor}</span>
                    </button>
                `);
            });
        })
        .catch(() => {
            contenedor.innerHTML = `<div class="text-xs text-red-500 text-center py-8">Error al cargar promociones.</div>`;
        });
    };

    window.aplicarPromocion = function (btn) {
        let promo;
        try { promo = JSON.parse(btn.getAttribute('data-promo').replace(/&quot;/g, '"')); }
        catch (e) { mostrarError('No se pudo leer la promoción.'); return; }

        if (promo.tipo_promocion === 'porcentaje') {
            descuentoPorcentaje = parseFloat(promo.valor_descuento) || 0;
            actualizarTotales();
            mostrarExito(`Promoción "${promo.nombre}" aplicada (-${descuentoPorcentaje}%).`);
            cerrarModal('modalPromociones');

        } else if (promo.tipo_promocion === 'descuento_fijo') {
            if (ticketSubtotal <= 0) { mostrarError('Agrega productos al ticket antes de aplicar esta promoción.'); return; }
            const monto = parseFloat(promo.valor_descuento) || 0;
            descuentoPorcentaje = Math.min(100, (monto / ticketSubtotal) * 100);
            actualizarTotales();
            mostrarExito(`Promoción "${promo.nombre}" aplicada (-$${monto.toFixed(2)}).`);
            cerrarModal('modalPromociones');

        } else if (promo.tipo_promocion === 'dos_por_uno') {
            promocion2x1Activa = true;
            promocion2x1Nombre = promo.nombre;
            actualizarTotales();
            mostrarExito(`Promoción "${promo.nombre}" activada: cada 2 unidades del mismo platillo, la segunda es gratis.`);
            cerrarModal('modalPromociones');

        } else if (promo.tipo_promocion === 'combo') {
            // Antes esto activaba el combo exigiendo TODOS los productos
            // vinculados en el ticket. Ahora se abre un modal para que el
            // mesero elija cuáles de esos productos (ya presentes en el
            // ticket) quiere que cuenten para el descuento.
            if (!Array.isArray(promo.producto_ids) || promo.producto_ids.length === 0) {
                mostrarError(`"${promo.nombre}" no tiene productos vinculados; agrégalos desde el admin de Promociones.`);
                return;
            }
            cerrarModal('modalPromociones');
            abrirModalSeleccionCombo(promo);

        } else {
            mostrarError(`No se reconoce el tipo de promoción de "${promo.nombre}".`);
        }
    };

    // ---------------------------------------------------------------
    // Selección manual de productos del combo. `promo.producto_ids` es
    // la lista completa vinculada en el admin; aquí solo se muestran
    // marcables (checkbox habilitado) los que YA están en el ticket —
    // los demás aparecen deshabilitados con la etiqueta "No está en el
    // ticket", para que quede claro por qué no se pueden marcar.
    // ---------------------------------------------------------------
    let comboPromoPendiente = null;

    function abrirModalSeleccionCombo(promo) {
        const contenedor = document.getElementById('listaSeleccionCombo');
        const titulo = document.getElementById('tituloSeleccionCombo');
        if (!contenedor || !titulo) { mostrarError('No se encontró el modal de selección de combo.'); return; }

        titulo.innerText = `Selecciona productos de "${promo.nombre}"`;
        contenedor.innerHTML = '';

        const idsEnTicket = new Set();
        document.querySelectorAll('#listaTicket .ticket-item').forEach(item => {
            idsEnTicket.add(parseInt(item.dataset.productoId, 10));
        });

        promo.producto_ids.forEach(idRaw => {
            const id = parseInt(idRaw, 10);
            const productoEncontrado = productosDB.find(p => p.id === id);
            const nombre = productoEncontrado ? productoEncontrado.nombre : `Producto #${id}`;
            const enTicket = idsEnTicket.has(id);

            contenedor.insertAdjacentHTML('beforeend', `
                <label class="flex items-center justify-between gap-3 p-3 rounded-xl border border-[var(--border-color)] transition-all ${enTicket ? 'bg-[var(--bg-base)] cursor-pointer' : 'bg-[var(--bg-base)]/40 opacity-50 cursor-not-allowed'}">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" class="chk-combo w-4 h-4" data-producto-id="${id}" ${enTicket ? 'checked' : 'disabled'}>
                        <span class="text-[12px] font-bold text-[var(--text-main)]">${nombre}</span>
                    </div>
                    ${!enTicket ? '<span class="text-[9px] text-[var(--text-muted)] uppercase font-bold">No está en el ticket</span>' : ''}
                </label>
            `);
        });

        comboPromoPendiente = promo;
        const modal = document.getElementById('modalSeleccionCombo');
        modal.classList.remove('hidden'); modal.classList.add('flex');
    }

    window.confirmarSeleccionCombo = function () {
        const checks = document.querySelectorAll('#listaSeleccionCombo .chk-combo:checked');
        if (checks.length === 0) { mostrarError('Marca al menos un producto para aplicar el combo.'); return; }
        if (!comboPromoPendiente) { mostrarError('No hay un combo pendiente de aplicar.'); return; }

        const promo = comboPromoPendiente;
        comboActivo = true;
        comboNombre = promo.nombre;
        comboProductoIds = Array.from(checks).map(chk => parseInt(chk.dataset.productoId, 10));
        comboMonto = parseFloat(promo.valor_descuento) || 0;
        actualizarTotales();

        // Como solo se puede marcar lo que ya está en el ticket, el
        // descuento debería aplicarse de inmediato al confirmar.
        if (calcularDescuentoComboMonto() > 0) {
            mostrarExito(`Combo "${promo.nombre}" aplicado (-$${comboMonto.toFixed(2)}).`);
        } else {
            mostrarToast(`Combo "${promo.nombre}" activado: se descontará en cuanto los productos seleccionados sigan en el ticket.`, 'info');
        }

        comboPromoPendiente = null;
        cerrarModal('modalSeleccionCombo');
    };
})();