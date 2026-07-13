/**
 * comanda-pos.js
 * Lógica del POS de comanda (Ollintem Pro).
 * Depende de que window.ComandaConfig ya exista en el DOM (ver
 * resources/views/mesero/comanda/index.blade.php) ANTES de que este
 * script se cargue, ya que lee de ahí: csrfToken, mesa, esCapitan,
 * categorias, productos, platillosEnviados y rutas.
 */
(function () {
    const config = window.ComandaConfig || {};
    const categoriasDB = config.categorias || [];
    const productosDB = config.productos || [];
    const platillosEnviadosDB = config.platillosEnviados || [];

    function csrfToken() {
        return config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;
    }

    // ---------------------------------------------------------------
    // TEMA (oscuro / crema)
    // ---------------------------------------------------------------
    window.toggleTheme = function () {
        const body = document.body;
        body.classList.toggle('modo-crema');
        localStorage.setItem('tema-ollintem', body.classList.contains('modo-crema') ? 'crema' : 'negro');
        actualizarIconoTema(body.classList.contains('modo-crema'));
    };

    function actualizarIconoTema(esCrema) {
        const icon = document.getElementById('themeIcon');
        if (icon) {
            icon.className = esCrema
                ? 'fas fa-moon text-[11px] group-hover:rotate-45 transition-transform duration-500'
                : 'fas fa-sun text-[11px] group-hover:rotate-45 transition-transform duration-500';
        }
    }

    // ---------------------------------------------------------------
    // TABS (Orden / Enviado / Total)
    // ---------------------------------------------------------------
    window.cambiarTab = function (pestana) {
        const slider = document.getElementById('tab-slider');
        const btns = [document.getElementById('btn-tab-nueva-orden'), document.getElementById('btn-tab-enviados'), document.getElementById('btn-tab-comanda')];

        btns.forEach(el => { if (el) { el.classList.remove('text-[var(--bg-base)]'); el.classList.add('text-[var(--text-muted)]'); } });

        ['vista-nueva-orden', 'vista-enviados', 'vista-comanda'].forEach(id => {
            document.getElementById(id).classList.add('hidden');
            document.getElementById(id).classList.remove('flex');
        });

        if (pestana === 'nueva-orden') {
            if (slider) slider.style.transform = 'translateX(0%)';
            if (btns[0]) { btns[0].classList.add('text-[var(--bg-base)]'); btns[0].classList.remove('text-[var(--text-muted)]'); }
            document.getElementById('vista-nueva-orden').classList.remove('hidden'); document.getElementById('vista-nueva-orden').classList.add('flex');
            document.getElementById('txtTotal').innerText = '$0.00';
        } else if (pestana === 'enviados') {
            if (slider) slider.style.transform = 'translateX(100%)';
            if (btns[1]) { btns[1].classList.add('text-[var(--bg-base)]'); btns[1].classList.remove('text-[var(--text-muted)]'); }
            document.getElementById('vista-enviados').classList.remove('hidden'); document.getElementById('vista-enviados').classList.add('flex');
            document.getElementById('txtTotal').innerText = '$0.00';
        } else if (pestana === 'comanda') {
            if (slider) slider.style.transform = 'translateX(200%)';
            if (btns[2]) { btns[2].classList.add('text-[var(--bg-base)]'); btns[2].classList.remove('text-[var(--text-muted)]'); }
            document.getElementById('vista-comanda').classList.remove('hidden'); document.getElementById('vista-comanda').classList.add('flex');
            // El total real (enviado + nuevo, con IVA) solo se calcula y se
            // muestra aquí, en la pestaña "Total". En las otras dos pestañas
            // se deja en $0.00 a propósito.
            actualizarVistaTotal();
        }
    };

    // ---------------------------------------------------------------
    // FIX: esta función ahora SÍ respeta descuentoPorcentaje al pintar
    // el total en la pestaña "Total". Antes recalculaba el total desde
    // ticketSubtotal sin restar el descuento, por eso el número final
    // no se movía aunque el descuento sí se guardara internamente.
    // ---------------------------------------------------------------
    function actualizarVistaTotal() {
        const contenedorNuevos = document.getElementById('lista-comanda-total');
        const mensajeVacio = document.getElementById('estadoVacioComanda');
        const itemsEnTicket = document.querySelectorAll('#listaTicket .ticket-item');
        const contenedorDB = document.getElementById('items-db-total');
        const hayPlatillosEnDB = contenedorDB && contenedorDB.children.length > 0;

        contenedorNuevos.innerHTML = '';

        if (itemsEnTicket.length === 0 && !hayPlatillosEnDB) {
            mensajeVacio.classList.remove('hidden'); mensajeVacio.classList.add('flex');
        } else {
            mensajeVacio.classList.add('hidden'); mensajeVacio.classList.remove('flex');
            if (itemsEnTicket.length > 0) {
                contenedorNuevos.innerHTML += `<div class="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] mt-4 mb-2 px-1">Por Enviar</div>`;
                itemsEnTicket.forEach(item => {
                    const cant = item.querySelector('.cantidad-platillo').innerText;
                    const nombre = item.querySelector('.nombre-platillo').innerText;
                    const precio = item.querySelector('.precio-platillo').innerText;
                    contenedorNuevos.innerHTML += `
                        <div class="flex justify-between items-center p-2.5 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-sm mb-2">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-main)] text-[10px] font-bold flex items-center justify-center">${cant}</span>
                                <span class="text-[11px] font-bold text-[var(--text-main)]">${nombre}</span>
                            </div>
                            <span class="text-[11px] font-bold text-[var(--text-main)]">${precio}</span>
                        </div>
                    `;
                });
            }
        }

        const totalHistorial = platillosEnviadosDB.reduce((acc, i) => acc + ((i.precio || 0) * (i.cantidad || 1)), 0);
        const descuento2x1Monto = calcularDescuento2x1Monto();
        const descuentoComboMonto = calcularDescuentoComboMonto();
        const subtotalTicketTras2x1 = Math.max(0, ticketSubtotal - descuento2x1Monto - descuentoComboMonto);
        const subtotalTicketConDescuento = Math.max(0, subtotalTicketTras2x1 - (subtotalTicketTras2x1 * (descuentoPorcentaje / 100)));
        const subtotalGeneral = subtotalTicketConDescuento + totalHistorial;
        const ivaGeneral = subtotalGeneral * 0.16;
        document.getElementById('txtTotal').innerText = '$' + (subtotalGeneral + ivaGeneral).toFixed(2);
    }

    // ---------------------------------------------------------------
    // CATÁLOGO
    // ---------------------------------------------------------------
    function renderizarMenu() {
        const menuCat = document.getElementById('menuCategorias');
        const gridProd = document.getElementById('gridProductos');

        menuCat.innerHTML = `<button type="button" onclick="filtrarCategoria('Todos', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[var(--text-main)] text-[var(--bg-base)] text-[11px] font-bold tracking-wide shadow-sm transition-all outline-none border border-transparent">Todos</button>`;

        if (categoriasDB.length > 0) {
            categoriasDB.forEach(cat => {
                menuCat.innerHTML += `<button type="button" onclick="filtrarCategoria('${cat.nombre}', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--border-highlight)] text-[11px] font-semibold tracking-wide shadow-sm transition-all outline-none">${cat.nombre}</button>`;
            });
        }

        gridProd.innerHTML = '';

        if (productosDB.length > 0) {
            productosDB.forEach(prod => {
                const catNombre = prod.categoria ? prod.categoria.nombre : '';
                const precioNum = parseFloat(prod.precio) || 0;
                const sePorPeso = !!prod.se_vende_por_peso;
                const precioPor100g = parseFloat(prod.precio_por_100g) || 0;
                const modsJSON = prod.modificadores ? JSON.stringify(prod.modificadores).replace(/'/g, "\\'") : '[]';
                const letraInicial = prod.nombre.charAt(0).toUpperCase();

                const etiquetaPrecio = sePorPeso
                    ? `$${precioPor100g.toFixed(2)} <span class="text-[9px] font-bold opacity-70">/100g</span>`
                    : `$${precioNum.toFixed(2)}`;

                const badgePorPeso = sePorPeso
                    ? `<span class="absolute top-2.5 left-2.5 text-[7px] font-black uppercase tracking-widest text-white bg-orange-500 px-1.5 py-0.5 rounded-md shadow-sm z-10"><i class="fas fa-weight-hanging mr-0.5"></i>Peso</span>`
                    : '';

                gridProd.innerHTML += `
                    <div data-categoria-item="${catNombre}" onclick='agregarAlTicket(${prod.id}, "${prod.nombre}", ${precioNum}, "${catNombre}", ${modsJSON}, ${sePorPeso ? 'true' : 'false'}, ${precioPor100g}); event.stopPropagation();'
                         class="producto-card rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-[var(--card-shadow)] overflow-hidden hover:border-[#3b82f6]/50 hover:-translate-y-1 transition-all duration-300 group cursor-pointer flex flex-col h-[150px] xl:h-[170px] outline-none relative">

                        <div class="h-[50%] bg-[var(--input-bg)] flex items-center justify-center relative overflow-hidden border-b border-[var(--border-color)]">
                            ${badgePorPeso}
                            <span class="absolute top-2.5 right-2.5 text-[8px] font-bold uppercase tracking-widest text-[var(--text-muted)] bg-[var(--bg-panel)] border border-[var(--border-color)] px-2 py-0.5 rounded-md shadow-sm z-10">${catNombre}</span>
                            <span class="text-5xl font-black text-[var(--text-muted)] opacity-10 group-hover:opacity-20 transition-all duration-500 transform group-hover:scale-110 select-none">${letraInicial}</span>
                        </div>

                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <h3 class="text-[12px] xl:text-[13px] font-bold text-[var(--text-main)] leading-snug line-clamp-2">${prod.nombre}</h3>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-[14px] font-black text-[var(--text-main)] tracking-tight">${etiquetaPrecio}</span>
                                <div class="w-6 h-6 rounded-full bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] group-hover:bg-[#3b82f6] group-hover:text-white group-hover:border-transparent transition-all duration-300 shadow-sm">
                                    <i class="fas fa-plus text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            gridProd.innerHTML = `
                <div class="col-span-full flex flex-col items-center justify-center text-[var(--text-muted)] mt-20">
                    <i class="fas fa-box-open text-4xl mb-4 opacity-50"></i>
                    <p class="text-xs font-medium">Catálogo vacío</p>
                </div>`;
        }
    }

    document.addEventListener('DOMContentLoaded', renderizarMenu);

    window.filtrarCategoria = function (nombreCat, btn) {
        document.querySelectorAll('.cat-btn').forEach(el => el.className = "cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--border-highlight)] text-[11px] font-semibold tracking-wide shadow-sm transition-all outline-none");
        btn.className = "cat-btn px-6 py-2.5 rounded-full bg-[var(--text-main)] text-[var(--bg-base)] text-[11px] font-bold tracking-wide shadow-sm transition-all outline-none border border-transparent";

        document.querySelectorAll('.producto-card').forEach(card => {
            card.style.display = (nombreCat === 'Todos' || card.getAttribute('data-categoria-item') === nombreCat) ? 'flex' : 'none';
        });
    };

    // ---------------------------------------------------------------
    // TICKET
    // ---------------------------------------------------------------
    let ticketSubtotal = 0;
    let itemActivo = null;
    let contadorItems = 0;
    let tiempoGlobal = 'sin-tiempo';
    let gramajePendiente = null;
    // Producto seleccionado desde el menú que se vende por peso, en espera
    // de que el mesero capture el gramaje en el modal antes de agregarse
    // al ticket con el precio ya calculado.
    let productoPorPesoPendiente = null;
    let numeroPersonas = (config.mesa && config.mesa.personas) || 4;
    let descuentoPorcentaje = 0;
    let notaGeneral = '';
    let promocion2x1Activa = false;
    let promocion2x1Nombre = '';
    let comboActivo = false;
    let comboNombre = '';
    let comboProductoIds = [];
    let comboMonto = 0;

    // Cada 2 unidades del mismo platillo (mismo producto, mismos mods,
    // gramaje y tiempo, que es como ya se agrupan en el ticket), la
    // segunda sale gratis. Se recalcula en cada actualizarTotales(),
    // así que si el mesero agrega/quita productos después de activar
    // la promo, el monto se ajusta solo.
    function calcularDescuento2x1Monto() {
        if (!promocion2x1Activa) return 0;
        let monto = 0;
        document.querySelectorAll('#listaTicket .ticket-item').forEach(item => {
            const cantidad = parseInt(item.dataset.cantidad, 10) || 0;
            const precio = parseFloat(item.dataset.precio) || 0;
            const pares = Math.floor(cantidad / 2);
            monto += pares * precio;
        });
        return monto;
    }

    // El combo se aplica UNA sola vez (no por sets repetidos) y solo si
    // el ticket tiene, ahora mismo, al menos 1 unidad de CADA producto
    // vinculado a la promo. Se recalcula en cada actualizarTotales(),
    // así que si el mesero quita uno de los productos requeridos, el
    // descuento se retira solo (igual que ya pasa con el 2x1).
    function calcularDescuentoComboMonto() {
        if (!comboActivo || comboProductoIds.length === 0) return 0;
        const idsEnTicket = new Set();
        document.querySelectorAll('#listaTicket .ticket-item').forEach(item => {
            idsEnTicket.add(parseInt(item.dataset.productoId, 10));
        });
        const completo = comboProductoIds.every(id => idsEnTicket.has(id));
        return completo ? comboMonto : 0;
    }

    const listaTicket = document.getElementById('listaTicket');
    const estadoVacio = document.getElementById('estadoVacio');
    const barraModificadores = document.getElementById('barraModificadores');
    const contenedorBotonesModificadores = document.getElementById('contenedorBotonesModificadores');

    // Punto de entrada al hacer clic en una tarjeta del menú. Si el
    // producto se vende por peso, NO se agrega directo: se abre el modal
    // de Gramaje y la línea se crea hasta que el mesero confirma el peso
    // (ver guardarGramajeDelItem), ya con el precio calculado.
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
    // precio a partir del gramaje capturado).
    function _insertarItemEnTicket({ id, nombre, precioUnitario, categoria, arrayModificadores, sePorPeso, precioPor100g, gramaje }) {
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
    }

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

    function actualizarTotales() {
        const descuento2x1Monto = calcularDescuento2x1Monto();
        const descuentoComboMonto = calcularDescuentoComboMonto();
        const subtotalTras2x1 = Math.max(0, ticketSubtotal - descuento2x1Monto - descuentoComboMonto);
        const subtotalConDescuento = Math.max(0, subtotalTras2x1 - (subtotalTras2x1 * (descuentoPorcentaje / 100)));
        const iva = subtotalConDescuento * 0.16;
        document.getElementById('txtSubtotal').innerText = '$' + subtotalConDescuento.toFixed(2);
        document.getElementById('txtIva').innerText = '$' + iva.toFixed(2);
        actualizarVistaTotal();
    }

    window.limpiarTicket = function () {
        document.getElementById('listaTicket').innerHTML = ''; document.getElementById('estadoVacio').classList.remove('hidden');
        ticketSubtotal = 0; descuentoPorcentaje = 0; notaGeneral = '';
        promocion2x1Activa = false; promocion2x1Nombre = '';
        comboActivo = false; comboNombre = ''; comboProductoIds = []; comboMonto = 0;
        actualizarTotales(); deseleccionarTicket();
    };

    // ---------------------------------------------------------------
    // FUNCIONES COMPLEMENTARIAS
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

    // Botón "ajustar gramaje" manual: sigue funcionando igual que antes
    // para productos normales (solo etiqueta informativa), y ahora
    // también recalcula el precio si el item activo se vende por peso.
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
    function abrirModalGramajeNuevo(productoId, nombre, categoria, arrayModificadores, precioPor100g) {
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
    }

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

    function mostrarToast(msg, type = 'info') {
        const c = document.getElementById('toastContainer'); if (!c) return;
        const t = document.createElement('div');
        t.className = `toast-panel ${type}`;
        t.innerHTML = `<div class="toast-icon"><i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}"></i></div><div><strong>${type === 'success' ? 'Éxito' : type === 'error' ? 'Error' : 'Aviso'}</strong><span>${msg}</span></div>`;
        c.appendChild(t);
        requestAnimationFrame(() => t.classList.add('show'));
        setTimeout(() => { t.classList.remove('show'); t.addEventListener('transitionend', () => t.remove(), { once: true }); }, 3000);
    }
    function mostrarError(m) { mostrarToast(m, 'error'); }
    function mostrarExito(m) { mostrarToast(m, 'success'); }

    window.ajustarPersonas = function () { document.getElementById('personasInput').value = numeroPersonas; document.getElementById('modalPersonas').classList.remove('hidden'); };

    window.guardarPersonas = function () {
        const v = parseInt(document.getElementById('personasInput').value, 10);
        if (isNaN(v) || v <= 0) { mostrarError('Inválido'); return; }

        numeroPersonas = v;
        document.getElementById('txtPersonas').innerText = v;
        cerrarModal('modalPersonas');

        fetch(config.rutas.comandaPersonas, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken()
            },
            body: JSON.stringify({ personas: v })
        })
        .then(res => res.json())
        .then(data => { if (!data.success) mostrarError('No se pudo guardar el número de personas.'); })
        .catch(() => mostrarError('Error de red al guardar personas.'));
    };

    window.agregarNota = function () { if (!itemActivo) { mostrarError('Selecciona platillo'); return; } document.getElementById('notaTextarea').value = ''; document.getElementById('modalNota').classList.remove('hidden'); };
    window.aplicarDescuento = function () { document.getElementById('descuentoInput').value = descuentoPorcentaje; document.getElementById('modalDescuento').classList.remove('hidden'); };
    window.guardarDescuento = function () { const v = parseFloat(document.getElementById('descuentoInput').value); if (isNaN(v) || v < 0 || v > 100) { mostrarError('Inválido'); return; } descuentoPorcentaje = v; actualizarTotales(); cerrarModal('modalDescuento'); };
    window.cerrarModal = function (id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); };
    window.insertarNotaCaracter = function (c) { const t = document.getElementById('notaTextarea'); t.value += c; t.focus(); };
    window.insertarNotaEspacio = function () { const t = document.getElementById('notaTextarea'); t.value += ' '; t.focus(); };
    window.borrarNotaCaracter = function () { const t = document.getElementById('notaTextarea'); t.value = t.value.slice(0, -1); t.focus(); };
    window.limpiarNota = function () { const t = document.getElementById('notaTextarea'); t.value = ''; t.focus(); };
    window.imprimirPrecuenta = function () {
        const url = config.rutas && config.rutas.comandaPrecuenta;
        if (!url) { mostrarError('No se encontró la ruta de la pre-cuenta. Revisa ComandaConfig.rutas.comandaPrecuenta.'); return; }
        window.open(url, '_blank');
    };

    // ---------------------------------------------------------------
    // PROMOCIONES — abre modal, y aplica la promo seleccionada como
    // descuento automático al ticket (porcentaje, descuento_fijo,
    // dos_por_uno y ahora también combo).
    // ---------------------------------------------------------------
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
            // El combo se resta como descuento fijo (una sola vez por
            // ticket) en cuanto el ticket contiene TODOS los productos
            // vinculados a la promo en el admin. Si más adelante se
            // quita alguno de esos productos, el descuento se retira
            // solo (mismo patrón que ya usa el 2x1).
            if (!Array.isArray(promo.producto_ids) || promo.producto_ids.length === 0) {
                mostrarError(`"${promo.nombre}" no tiene productos vinculados; agrégalos desde el admin de Promociones.`);
                return;
            }
            comboActivo = true;
            comboNombre = promo.nombre;
            comboProductoIds = promo.producto_ids.map(id => parseInt(id, 10));
            comboMonto = parseFloat(promo.valor_descuento) || 0;
            actualizarTotales();

            if (calcularDescuentoComboMonto() > 0) {
                mostrarExito(`Combo "${promo.nombre}" aplicado (-$${comboMonto.toFixed(2)}).`);
            } else {
                mostrarToast(`Combo "${promo.nombre}" activado: se descontará en cuanto el ticket tenga todos sus productos.`, 'info');
            }
            cerrarModal('modalPromociones');

        } else {
            mostrarError(`No se reconoce el tipo de promoción de "${promo.nombre}".`);
        }
    };

    // ---------------------------------------------------------------
    // FLUJO CAPITÁN / TRASPASO
    // ---------------------------------------------------------------
    let capitanAutorizado = false;
    let mesaDestinoSeleccionada = null;
    let mesaDestinoSeleccionadaNumero = null;

    // Abre el modal para capturar el NIP del capitán (reemplaza al antiguo prompt()).
    window.llamarCapitan = function () {
        const input = document.getElementById('nipInput');
        if (input) input.value = '';
        const modal = document.getElementById('modalNip');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        setTimeout(() => { if (input) input.focus(); }, 100);
    };

    document.addEventListener('DOMContentLoaded', () => {
        const nipInput = document.getElementById('nipInput');
        if (nipInput) {
            nipInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    confirmarNipCapitan();
                }
            });
        }
    });

    window.confirmarNipCapitan = async function () {
        const input = document.getElementById('nipInput');
        const nip = input ? input.value.trim() : '';

        if (!nip) { mostrarError('Ingresa tu NIP.'); return; }

        const modalNip = document.getElementById('modalNip');
        if (modalNip) {
            modalNip.classList.add('hidden');
            modalNip.classList.remove('flex');
        }

        try {
            const res = await fetch(config.rutas.capitanVerify, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ nip: nip })
            });

            const data = await res.json().catch(() => null);
            if (!res.ok) throw new Error(data?.message || 'Error de autenticación');

            if (data.success) {
                capitanAutorizado = true;
                mostrarExito('Capitán autorizado.');

                const container = document.getElementById('capitanMesasContainer');
                const modal = document.getElementById('modalCapitan');

                if (container) {
                    container.innerHTML = '';
                    if (Array.isArray(data.mesas) && data.mesas.length > 0) {
                        data.mesas.forEach(m => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.dataset.mesaId = m.id;

                            const esOcupada = m.estado === 'ocupada';
                            btn.className = 'w-full text-left rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] px-4 py-4 hover:border-[#3B82F6]/50 transition-all flex items-center justify-between gap-4';

                            btn.innerHTML = `
    <div>
        <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--text-muted)] font-bold">Mesa</p>
        <h3 class="text-lg font-black text-[var(--text-main)]">${m.numero}</h3>
    </div>
    <span class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] ${esOcupada ? 'text-emerald-400' : 'text-blue-400'}">
        <i class="fas fa-circle text-[6px]"></i> ${esOcupada ? 'Activa' : 'Disponible'}
    </span>`;

                            btn.addEventListener('click', () => {
                                seleccionarMesaDestino(m.id, m.numero);
                            });
                            container.appendChild(btn);
                        });
                    } else {
                        container.innerHTML = `<div class="text-xs text-[var(--text-muted)] text-center py-6">No hay mesas disponibles.</div>`;
                    }
                }

                if (modal) {
                    modal.classList.remove('hidden');
                }
            }
        } catch (err) {
            mostrarError(err.message);
        }
    };

    function seleccionarMesaDestino(mesaId, mesaNumero) {
        mesaDestinoSeleccionada = mesaId; mesaDestinoSeleccionadaNumero = mesaNumero;
        const container = document.getElementById('capitanMesasContainer');
        if (container) {
            container.querySelectorAll('button[data-mesa-id]').forEach(btn => {
                btn.classList.remove('border-blue-500/50', 'bg-blue-500/10');
                btn.classList.add('border-[var(--border-color)]', 'bg-[var(--bg-base)]');
            });
            const button = container.querySelector(`button[data-mesa-id="${mesaId}"]`);
            if (button) {
                button.classList.remove('border-[var(--border-color)]', 'bg-[var(--bg-base)]');
                button.classList.add('border-blue-500/50', 'bg-blue-500/10');
            }
        }
        document.getElementById('modalCapitan').classList.add('hidden');
        abrirModalTipoTraspaso();
    }

    function abrirModalTipoTraspaso() {
        const modal = document.getElementById('modalTipoTraspaso');
        if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    }

    // --- "Pedido Completo": arma automáticamente TODO (ticket sin enviar + ya en cocina) ---
    window.elegirTraspasoCompleto = function () {
        cerrarModal('modalTipoTraspaso');

        if (!mesaDestinoSeleccionada) { mostrarError('No hay mesa destino seleccionada.'); return; }

        const productosNuevos = [];
        const ticketItemIdsAEliminar = [];
        const detalleIds = [];

        // Todo lo que está en el ticket (aún no enviado a cocina)
        document.querySelectorAll('#listaTicket .ticket-item').forEach(item => {
            const nombre = item.querySelector('.nombre-platillo').innerText;
            const modsElementos = item.querySelectorAll('.nota-texto-real');
            const mods = []; modsElementos.forEach(m => mods.push(m.innerText.replace('•', '').trim()));
            productosNuevos.push({
                id: parseInt(item.dataset.productoId, 10),
                nombre: nombre,
                cantidad: parseInt(item.dataset.cantidad, 10),
                precio: parseFloat(item.dataset.precio),
                notas: mods.join(' '),
                modificadores: mods,
                gramaje: item.dataset.gramaje === 'sin-gramaje' ? null : item.dataset.gramaje,
                tiempo: item.dataset.tiempo
            });
            ticketItemIdsAEliminar.push(item.id);
        });

        // Todo lo que ya se mandó a cocina
        platillosEnviadosDB.filter(p => p.id).forEach(p => detalleIds.push(p.id));

        if (productosNuevos.length === 0 && detalleIds.length === 0) {
            mostrarError('No hay productos en esta mesa para traspasar.');
            return;
        }

        ejecutarTraspaso(productosNuevos, ticketItemIdsAEliminar, detalleIds, null);
    };

    window.elegirTraspasoProducto = function () {
        cerrarModal('modalTipoTraspaso');
        abrirModalSeleccionProductos();
    };

    function abrirModalSeleccionProductos() {
        const contenedor = document.getElementById('listaProductosTraspaso');
        if (!contenedor) return;
        contenedor.innerHTML = '';

        const itemsTicket = document.querySelectorAll('#listaTicket .ticket-item');
        if (itemsTicket.length > 0) {
            contenedor.insertAdjacentHTML('beforeend', `<div class="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] mb-1 px-1">Por enviar</div>`);
            itemsTicket.forEach(item => {
                const nombre = item.querySelector('.nombre-platillo').innerText;
                const cantidad = item.dataset.cantidad;
                const precio = (parseFloat(item.dataset.precio) * parseInt(item.dataset.cantidad, 10)).toFixed(2);
                contenedor.insertAdjacentHTML('beforeend', `
                    <label class="flex items-center justify-between gap-3 p-3 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] cursor-pointer">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" class="chk-traspaso w-4 h-4" data-tipo="nuevo" data-ticket-item-id="${item.id}">
                            <span class="text-[11px] font-bold text-[var(--text-main)]">${cantidad}x ${nombre}</span>
                        </div>
                        <span class="text-[11px] font-bold text-[var(--text-main)]">$${precio}</span>
                    </label>
                `);
            });
        }

        const enviadosConId = platillosEnviadosDB.filter(p => p.id);
        if (enviadosConId.length > 0) {
            contenedor.insertAdjacentHTML('beforeend', `<div class="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] mb-1 mt-3 px-1">Ya enviados a cocina</div>`);
            enviadosConId.forEach(p => {
                contenedor.insertAdjacentHTML('beforeend', `
                    <label class="flex items-center justify-between gap-3 p-3 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] cursor-pointer">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" class="chk-traspaso w-4 h-4" data-tipo="enviado" data-detalle-id="${p.id}">
                            <span class="text-[11px] font-bold text-[var(--text-main)]">${p.cantidad}x ${p.nombre}</span>
                        </div>
                        <span class="text-[11px] font-bold text-[var(--text-main)]">$${((p.precio || 0) * (p.cantidad || 1)).toFixed(2)}</span>
                    </label>
                `);
            });
        }

        if (itemsTicket.length === 0 && enviadosConId.length === 0) {
            contenedor.innerHTML = `<div class="text-xs text-[var(--text-muted)] text-center py-6">No hay productos para traspasar.</div>`;
        }

        const modal = document.getElementById('modalSeleccionProductos');
        if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
    }

    window.confirmarTraspasoProductos = function () {
        const checks = document.querySelectorAll('#listaProductosTraspaso .chk-traspaso:checked');
        if (checks.length === 0) { mostrarError('Selecciona al menos un producto.'); return; }
        if (!mesaDestinoSeleccionada) { mostrarError('No hay mesa destino seleccionada.'); return; }

        const productosNuevos = [];
        const ticketItemIdsAEliminar = [];
        const detalleIds = [];

        checks.forEach(chk => {
            if (chk.dataset.tipo === 'nuevo') {
                const item = document.getElementById(chk.dataset.ticketItemId);
                if (!item) return;
                const nombre = item.querySelector('.nombre-platillo').innerText;
                const modsElementos = item.querySelectorAll('.nota-texto-real');
                const mods = []; modsElementos.forEach(m => mods.push(m.innerText.replace('•', '').trim()));
                productosNuevos.push({
                    id: parseInt(item.dataset.productoId, 10),
                    nombre: nombre,
                    cantidad: parseInt(item.dataset.cantidad, 10),
                    precio: parseFloat(item.dataset.precio),
                    notas: mods.join(' '),
                    modificadores: mods,
                    gramaje: item.dataset.gramaje === 'sin-gramaje' ? null : item.dataset.gramaje,
                    tiempo: item.dataset.tiempo
                });
                ticketItemIdsAEliminar.push(item.id);
            } else if (chk.dataset.tipo === 'enviado') {
                detalleIds.push(parseInt(chk.dataset.detalleId, 10));
            }
        });

        const btn = document.querySelector('#modalSeleccionProductos button[onclick="confirmarTraspasoProductos()"]');
        ejecutarTraspaso(productosNuevos, ticketItemIdsAEliminar, detalleIds, btn);
    };

    // --- Función compartida: hace el POST real de traspaso y limpia el estado local ---
    function ejecutarTraspaso(productosNuevos, ticketItemIdsAEliminar, detalleIds, btn) {
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }

        fetch(config.rutas.comandaTransferir, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({
                mesa_origen_id: (config.mesa && config.mesa.id),
                mesa_destino_id: mesaDestinoSeleccionada,
                productos_nuevos: productosNuevos,
                productos_enviados_ids: detalleIds
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarExito(data.message || 'Productos traspasados.');

                    ticketItemIdsAEliminar.forEach(id => {
                        const item = document.getElementById(id);
                        if (item) {
                            ticketSubtotal -= (parseInt(item.dataset.cantidad, 10) * parseFloat(item.dataset.precio));
                            item.remove();
                        }
                    });
                    if (document.getElementById('listaTicket').children.length === 0) document.getElementById('estadoVacio').classList.remove('hidden');

                    detalleIds.forEach(id => {
                        const idx = platillosEnviadosDB.findIndex(p => p.id === id);
                        if (idx !== -1) platillosEnviadosDB.splice(idx, 1);
                    });

                    actualizarTotales();
                    cerrarModal('modalSeleccionProductos');
                    mesaDestinoSeleccionada = null; mesaDestinoSeleccionadaNumero = null;
                    actualizarMensajeDestino();

                    // Si ya no queda nada en esta mesa, regresa al dashboard
                    const quedanEnviados = platillosEnviadosDB.length > 0;
                    const quedanEnTicket = document.getElementById('listaTicket').children.length > 0;
                    if (!quedanEnviados && !quedanEnTicket) {
                        setTimeout(() => window.location.href = (config.rutas && config.rutas.dashboard) || '/', 1200);
                    }
                } else {
                    throw new Error(data.message || 'No se pudo traspasar.');
                }
            })
            .catch(err => mostrarError(err.message))
            .finally(() => {
                if (btn) { btn.disabled = false; btn.innerHTML = 'Traspasar'; }
            });
    }

    function actualizarMensajeDestino() {
        const mensaje = document.getElementById('mensajeMesaDestino');
        const btnEnviar = document.getElementById('btn-enviar');
        if (capitanAutorizado && mesaDestinoSeleccionada && mesaDestinoSeleccionadaNumero) {
            mensaje.innerText = `Destino: Mesa ${mesaDestinoSeleccionadaNumero}`;
            btnEnviar.innerHTML = `<i class="fas fa-paper-plane text-sm"></i> <span>Enviar a Mesa ${mesaDestinoSeleccionadaNumero}</span>`;
        } else {
            mensaje.innerText = 'Enviar a cocina o selecciona mesa destino.';
            btnEnviar.innerHTML = `<i class="fas fa-paper-plane text-sm"></i> <span>Enviar Orden</span>`;
        }
    }

    // ---------------------------------------------------------------
    // ENVÍO A COCINA
    // ---------------------------------------------------------------
    window.enviarACocina = function () {
        const itemsHTML = document.querySelectorAll('.ticket-item');
        if (itemsHTML.length === 0) { mostrarError("¡Agrega platillos!"); return; }

        const platillosData = [];
        itemsHTML.forEach(item => {
            const nombre = item.querySelector('.nombre-platillo').innerText;
            const modsElementos = item.querySelectorAll('.nota-texto-real');
            const mods = []; modsElementos.forEach(m => mods.push(m.innerText.replace('•', '').trim()));
            platillosData.push({
                id: parseInt(item.dataset.productoId, 10), nombre: nombre,
                cantidad: parseInt(item.dataset.cantidad, 10), precio: parseFloat(item.dataset.precio),
                notas: mods.join(' '), modificadores: mods,
                gramaje: item.dataset.gramaje === 'sin-gramaje' ? null : item.dataset.gramaje,
                tiempo: item.dataset.tiempo
            });
        });

        const btn = document.getElementById('btn-enviar'); btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; btn.disabled = true;
        fetch(config.rutas.comandaEnviar, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({
                mesa_id: mesaDestinoSeleccionada || (config.mesa && config.mesa.id) || 1,
                platillos: platillosData,
                total: parseFloat(document.getElementById('txtTotal').innerText.replace('$', '')),
                personas: numeroPersonas,
                descuento_porcentaje: descuentoPorcentaje,
                nota_general: notaGeneral
            })
        })
            .then(res => res.json()).then(data => {
                if (data.success) {
                    mostrarExito("¡Enviado a cocina!");

                    platillosData.forEach(p => {
                        platillosEnviadosDB.push({
                            nombre: p.nombre,
                            cantidad: p.cantidad,
                            precio: p.precio,
                            estado: 'enviado'
                        });
                    });

                    limpiarTicket();

                    setTimeout(() => window.location.href = (config.rutas && config.rutas.dashboard) || '/', 1000);
                }
                else throw new Error(data.message);
            }).catch(error => { mostrarError(error.message); btn.innerHTML = '<i class="fas fa-paper-plane text-sm"></i><span>Enviar Orden</span>'; btn.disabled = false; });
    };
})();