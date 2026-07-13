/**
 * comanda-ticket.js
 * Manejo del ticket: agregar/quitar/editar líneas, cantidades y totales.
 * Usa el estado compartido de comanda-core.js (ticketSubtotal, itemActivo,
 * contadorItems, tiempoGlobal, gramajePendiente, descuentoPorcentaje,
 * promocion2x1Activa, comboActivo, etc.) y expone funciones que otros
 * módulos también necesitan:
 *   - window.calcularDescuento2x1Monto / window.calcularDescuentoComboMonto
 *     (usadas también por comanda-core.js y comanda-promociones.js)
 *   - window._insertarItemEnTicket (usada por comanda-gramaje.js)
 *   - window.actualizarTotales (usada por comanda-gramaje.js,
 *     comanda-promociones.js y comanda-capitan-traspaso.js)
 */
(function () {
    const listaTicket = document.getElementById('listaTicket');
    const estadoVacio = document.getElementById('estadoVacio');
    const barraModificadores = document.getElementById('barraModificadores');
    const contenedorBotonesModificadores = document.getElementById('contenedorBotonesModificadores');

    // Cada 2 unidades del mismo platillo (mismo producto, mismos mods,
    // gramaje y tiempo, que es como ya se agrupan en el ticket), la
    // segunda sale gratis. Se recalcula en cada actualizarTotales(),
    // así que si el mesero agrega/quita productos después de activar
    // la promo, el monto se ajusta solo.
    window.calcularDescuento2x1Monto = function () {
        if (!promocion2x1Activa) return 0;
        let monto = 0;
        document.querySelectorAll('#listaTicket .ticket-item').forEach(item => {
            const cantidad = parseInt(item.dataset.cantidad, 10) || 0;
            const precio = parseFloat(item.dataset.precio) || 0;
            const pares = Math.floor(cantidad / 2);
            monto += pares * precio;
        });
        return monto;
    };

    // El combo se aplica UNA sola vez (no por sets repetidos) y solo si
    // el ticket tiene, ahora mismo, al menos 1 unidad de CADA producto
    // vinculado a la promo.
    window.calcularDescuentoComboMonto = function () {
        if (!comboActivo || comboProductoIds.length === 0) return 0;
        const idsEnTicket = new Set();
        document.querySelectorAll('#listaTicket .ticket-item').forEach(item => {
            idsEnTicket.add(parseInt(item.dataset.productoId, 10));
        });
        const completo = comboProductoIds.every(id => idsEnTicket.has(id));
        return completo ? comboMonto : 0;
    };

    // Punto de entrada al hacer clic en una tarjeta del menú. Si el
    // producto se vende por peso, NO se agrega directo: se abre el modal
    // de Gramaje (comanda-gramaje.js) y la línea se crea hasta que el
    // mesero confirma el peso, ya con el precio calculado.
    window.agregarAlTicket = function (id, nombre, precio, categoria, arrayModificadores = [], sePorPeso = false, precioPor100g = 0) {
        cambiarTab('nueva-orden', document.getElementById('btn-tab-nueva-orden'));

        if (sePorPeso) {
            abrirModalGramajeNuevo(id, nombre, categoria, arrayModificadores, precioPor100g);
            return;
        }

        _insertarItemEnTicket({
            id, nombre,
            precioUnitario: parseFloat(precio),
            categoria,
            arrayModificadores,
            sePorPeso: false,
            precioPor100g: 0,
            gramaje: gramajePendiente
        });

        if (gramajePendiente) { gramajePendiente = null; document.getElementById('indicador-gramaje-pendiente').classList.add('hidden'); }
    };

    // Inserta (o agrupa) una línea en el ticket. Compartida por el flujo
    // normal y por el flujo de productos por peso (una vez calculado el
    // precio a partir del gramaje capturado en comanda-gramaje.js).
    window._insertarItemEnTicket = function ({ id, nombre, precioUnitario, categoria, arrayModificadores, sePorPeso, precioPor100g, gramaje }) {
        estadoVacio.classList.add('hidden');

        const modsString = JSON.stringify(arrayModificadores).replace(/'/g, "&#39;").replace(/"/g, "&quot;");
        const gramajeKey = gramaje ? gramaje.toString() : 'sin-gramaje';

        const existingItem = Array.from(listaTicket.querySelectorAll('.ticket-item')).find(item => {
            return parseInt(item.dataset.productoId, 10) === id
                && item.dataset.modificadores === modsString
                && item.dataset.gramaje === gramajeKey
                && item.dataset.tiempo === tiempoGlobal;
        });

        if (existingItem) {
            const cantidadSpan = existingItem.querySelector('.cantidad-platillo');
            const cantidad = parseInt(cantidadSpan.innerText, 10) + 1;
            cantidadSpan.innerText = cantidad;
            existingItem.dataset.cantidad = cantidad;

            const precioSpan = existingItem.querySelector('.precio-platillo');
            precioSpan.innerText = '$' + (precioUnitario * cantidad).toFixed(2);
            existingItem.dataset.precio = precioUnitario;

            ticketSubtotal += precioUnitario;
            actualizarTotales();
            seleccionarItem(existingItem.id);
            return existingItem.id;
        }

        contadorItems++;
        const itemId = 'ticket-item-' + contadorItems;

        const etiquetaGramaje = gramaje ? `<span class="text-[7px] text-[var(--text-muted)] font-bold bg-[var(--input-bg)] border border-[var(--border-color)] px-1.5 py-0.5 rounded-md shadow-sm">${gramaje}g</span>` : '';

        let etiquetaTiempo = '';
        if (tiempoGlobal !== 'sin-tiempo') {
            const t = tiempoGlobal === 'primer-tiempo' ? '1T' : (tiempoGlobal === 'segundo-tiempo' ? '2T' : '3T');
            etiquetaTiempo = `<span class="text-[7px] text-[var(--text-muted)] font-bold bg-[var(--input-bg)] border border-[var(--border-color)] px-1.5 py-0.5 rounded-md shadow-sm">${t}</span>`;
        }

        const etiquetaPorPeso = sePorPeso
            ? `<span class="text-[7px] text-orange-500 font-bold bg-orange-500/10 border border-orange-500/20 px-1.5 py-0.5 rounded-md"><i class="fas fa-weight-hanging"></i> Por peso</span>`
            : '';

        const itemHTML = `
            <div id="${itemId}" data-producto-id="${id}" data-cantidad="1" data-precio="${precioUnitario}" data-modificadores="${modsString}" data-gramaje="${gramajeKey}" data-tiempo="${tiempoGlobal}" data-se-por-peso="${sePorPeso ? '1' : '0'}" data-precio100g="${precioPor100g}" class="ticket-item animate-item relative w-full rounded-[18px] bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-sm p-4 flex flex-col gap-3 cursor-pointer transition-all duration-300 outline-none" onclick="seleccionarItem('${itemId}')">

                <div class="flex justify-between items-start gap-2">
                    <div class="flex-1">
                        <h3 class="text-[12px] font-bold text-[var(--text-main)] leading-tight nombre-platillo">${nombre}</h3>
                        <div class="flex flex-wrap gap-1.5 mt-1.5 empty:hidden">
                            <div class="gramaje-etiqueta empty:hidden">${etiquetaGramaje}</div>
                            ${etiquetaTiempo}
                            ${etiquetaPorPeso}
                        </div>
                    </div>
                    <span class="text-[13px] font-black text-[var(--text-main)] tracking-tight precio-platillo">$${precioUnitario.toFixed(2)}</span>
                </div>

                <div class="modificadores-lista w-full text-[10px] text-orange-500 font-medium leading-relaxed empty:hidden"></div>

                <div class="flex items-center justify-between pt-3 mt-1 border-t border-[var(--border-color)]">
                    <div class="flex items-center bg-[var(--input-bg)] rounded-[10px] p-0.5 border border-[var(--border-color)] shadow-inner" onclick="event.stopPropagation();">
                        <button type="button" onclick="decrementarCantidad('${itemId}')" class="w-7 h-7 rounded-[8px] flex items-center justify-center text-[var(--text-muted)] hover:bg-[var(--bg-panel)] hover:text-[var(--text-main)] hover:shadow-sm transition-all outline-none">
                            <i class="fas fa-minus text-[10px]"></i>
                        </button>
                        <span class="cantidad-platillo w-8 text-center text-[12px] font-bold text-[var(--text-main)]">1</span>
                        <button type="button" onclick="incrementarCantidad('${itemId}')" class="w-7 h-7 rounded-[8px] flex items-center justify-center text-[var(--text-muted)] hover:bg-[var(--bg-panel)] hover:text-[var(--text-main)] hover:shadow-sm transition-all outline-none">
                            <i class="fas fa-plus text-[10px]"></i>
                        </button>
                    </div>

                    <button type="button" onclick="eliminarItemFila(this); event.stopPropagation();" class="hidden btn-control-eliminar w-8 h-8 rounded-[8px] text-red-500 bg-red-500/10 border border-red-500/20 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center outline-none">
                        <i class="fas fa-trash-alt text-[10px]"></i>
                    </button>
                </div>
            </div>
        `;

        listaTicket.insertAdjacentHTML('beforeend', itemHTML);
        ticketSubtotal += precioUnitario;
        actualizarTotales();
        seleccionarItem(itemId);
        listaTicket.parentElement.scrollTop = listaTicket.parentElement.scrollHeight;
        return itemId;
    };

    window.seleccionarItem = function (id) {
        deseleccionarTicket();
        itemActivo = document.getElementById(id);
        if (itemActivo) {
            itemActivo.classList.add('bg-[#3b82f6]/5', 'border-[#3b82f6]/40');
            itemActivo.classList.remove('bg-[var(--bg-panel)]', 'border-[var(--border-color)]');
            itemActivo.querySelector('.btn-control-eliminar').classList.remove('hidden');
            itemActivo.querySelector('.btn-control-eliminar').classList.add('flex');

            const modsString = itemActivo.getAttribute('data-modificadores');
            const modificadoresParaPintar = JSON.parse(modsString || '[]');
            contenedorBotonesModificadores.innerHTML = '';

            if (modificadoresParaPintar.length > 0) {
                modificadoresParaPintar.forEach(mod => {
                    const nombreMod = mod.nombre || mod.descripcion || mod;
                    contenedorBotonesModificadores.insertAdjacentHTML('beforeend', `<button type="button" onclick="agregarModificadorFijo('${nombreMod}')" class="px-5 py-2 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-main)] text-[10px] font-bold hover:border-[#3b82f6] transition-all shadow-sm">${nombreMod}</button>`);
                });
                barraModificadores.classList.remove('hidden');
            } else {
                barraModificadores.classList.add('hidden');
            }
        }
    };

    window.deseleccionarTicket = function () {
        document.querySelectorAll('.ticket-item').forEach(el => {
            el.classList.remove('bg-[#3b82f6]/5', 'border-[#3b82f6]/40');
            el.classList.add('bg-[var(--bg-panel)]', 'border-[var(--border-color)]');
            if (el.querySelector('.btn-control-eliminar')) {
                el.querySelector('.btn-control-eliminar').classList.add('hidden');
                el.querySelector('.btn-control-eliminar').classList.remove('flex');
            }
        });
        itemActivo = null; barraModificadores.classList.add('hidden');
    };

    window.agregarModificadorFijo = function (texto) {
        if (itemActivo) {
            const list = itemActivo.querySelector('.modificadores-lista');
            const separador = list.children.length > 0 ? `<span class="mx-1.5 opacity-50 text-[12px] leading-none text-orange-500">•</span>` : `<i class="fas fa-pen mr-1.5 opacity-70 text-[9px] text-orange-500"></i>`;
            list.insertAdjacentHTML('beforeend', `<span class="inline-flex items-center"><span class="nota-texto-real">${separador}${texto}</span></span>`);
        }
    };

    window.guardarNota = function () {
        const nota = document.getElementById('notaTextarea').value.trim();
        if (!itemActivo) { cerrarModal('modalNota'); return; }
        if (nota.length === 0) { mostrarError('Nota vacía.'); return; }

        const list = itemActivo.querySelector('.modificadores-lista');
        const separador = list.children.length > 0 ? `<span class="mx-1.5 opacity-50 text-[12px] leading-none text-orange-500">•</span>` : `<i class="fas fa-pen mr-1.5 opacity-70 text-[9px] text-orange-500"></i>`;
        list.insertAdjacentHTML('beforeend', `<span class="inline-flex items-center"><span class="nota-texto-real">${separador}${nota}</span></span>`);

        itemActivo.dataset.nota = nota; notaGeneral = nota; cerrarModal('modalNota');
    };

    window.incrementarCantidad = function (id) {
        const item = document.getElementById(id); if (!item) return;
        const cantidadSpan = item.querySelector('.cantidad-platillo');
        const precioUnitario = parseFloat(item.dataset.precio) || 0;
        let cantidad = parseInt(cantidadSpan.innerText, 10) + 1;
        cantidadSpan.innerText = cantidad; item.dataset.cantidad = cantidad;
        item.querySelector('.precio-platillo').innerText = '$' + (precioUnitario * cantidad).toFixed(2);
        ticketSubtotal += precioUnitario; actualizarTotales();
    };

    window.decrementarCantidad = function (id) {
        const item = document.getElementById(id); if (!item) return;
        const cantidadSpan = item.querySelector('.cantidad-platillo');
        const precioUnitario = parseFloat(item.dataset.precio) || 0;
        let cantidad = parseInt(cantidadSpan.innerText, 10) - 1;
        if (cantidad <= 0) { eliminarItemFila(item.querySelector('.btn-control-eliminar')); return; }
        cantidadSpan.innerText = cantidad; item.dataset.cantidad = cantidad;
        item.querySelector('.precio-platillo').innerText = '$' + (precioUnitario * cantidad).toFixed(2);
        ticketSubtotal -= precioUnitario; actualizarTotales();
    };

    window.eliminarItemFila = function (btn) {
        const fila = btn.closest('.ticket-item');
        ticketSubtotal -= (parseInt(fila.dataset.cantidad, 10) * parseFloat(fila.dataset.precio));
        fila.remove(); actualizarTotales(); deseleccionarTicket();
        if (document.getElementById('listaTicket').children.length === 0) document.getElementById('estadoVacio').classList.remove('hidden');
    };

    window.actualizarTotales = function () {
        const descuento2x1Monto = calcularDescuento2x1Monto();
        const descuentoComboMonto = calcularDescuentoComboMonto();
        const subtotalTras2x1 = Math.max(0, ticketSubtotal - descuento2x1Monto - descuentoComboMonto);
        const subtotalConDescuento = Math.max(0, subtotalTras2x1 - (subtotalTras2x1 * (descuentoPorcentaje / 100)));
        const iva = subtotalConDescuento * 0.16;
        document.getElementById('txtSubtotal').innerText = '$' + subtotalConDescuento.toFixed(2);
        document.getElementById('txtIva').innerText = '$' + iva.toFixed(2);
        actualizarVistaTotal();
    };

    window.limpiarTicket = function () {
        document.getElementById('listaTicket').innerHTML = ''; document.getElementById('estadoVacio').classList.remove('hidden');
        ticketSubtotal = 0; descuentoPorcentaje = 0; notaGeneral = '';
        promocion2x1Activa = false; promocion2x1Nombre = '';
        comboActivo = false; comboNombre = ''; comboProductoIds = []; comboMonto = 0;
        actualizarTotales(); deseleccionarTicket();
    };

    // ---------------------------------------------------------------
    // Tiempos de cocina (S / 1 / 2 / 3) que se etiquetan en cada línea
    // del ticket al agregarla.
    // ---------------------------------------------------------------
    window.cambiarTiempoGlobal = function (tiempo) {
        tiempoGlobal = tiempo;
        const mapas = ['sin-tiempo', 'primer-tiempo', 'segundo-tiempo', 'tercer-tiempo'];
        const ids = ['tiempo-global-sin', 'tiempo-global-1', 'tiempo-global-2', 'tiempo-global-3'];
        ids.forEach((id, i) => {
            const btn = document.getElementById(id);
            if (mapas[i] === tiempo) btn.className = "w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold bg-[var(--text-main)] text-[var(--bg-base)] transition-all";
            else btn.className = "w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all";
        });
    };
})();