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
        } else if (pestana === 'enviados') {
            if (slider) slider.style.transform = 'translateX(100%)';
            if (btns[1]) { btns[1].classList.add('text-[var(--bg-base)]'); btns[1].classList.remove('text-[var(--text-muted)]'); }
            document.getElementById('vista-enviados').classList.remove('hidden'); document.getElementById('vista-enviados').classList.add('flex');
        } else if (pestana === 'comanda') {
            if (slider) slider.style.transform = 'translateX(200%)';
            if (btns[2]) { btns[2].classList.add('text-[var(--bg-base)]'); btns[2].classList.remove('text-[var(--text-muted)]'); }
            document.getElementById('vista-comanda').classList.remove('hidden'); document.getElementById('vista-comanda').classList.add('flex');
            actualizarVistaTotal();
        }
    };

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
        const subtotalGeneral = ticketSubtotal + totalHistorial;
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
                const modsJSON = prod.modificadores ? JSON.stringify(prod.modificadores).replace(/'/g, "\\'") : '[]';
                const letraInicial = prod.nombre.charAt(0).toUpperCase();

                gridProd.innerHTML += `
                    <div data-categoria-item="${catNombre}" onclick='agregarAlTicket(${prod.id}, "${prod.nombre}", ${precioNum}, "${catNombre}", ${modsJSON}); event.stopPropagation();'
                         class="producto-card rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-[var(--card-shadow)] overflow-hidden hover:border-[#3b82f6]/50 hover:-translate-y-1 transition-all duration-300 group cursor-pointer flex flex-col h-[150px] xl:h-[170px] outline-none">

                        <div class="h-[50%] bg-[var(--input-bg)] flex items-center justify-center relative overflow-hidden border-b border-[var(--border-color)]">
                            <span class="absolute top-2.5 right-2.5 text-[8px] font-bold uppercase tracking-widest text-[var(--text-muted)] bg-[var(--bg-panel)] border border-[var(--border-color)] px-2 py-0.5 rounded-md shadow-sm z-10">${catNombre}</span>
                            <span class="text-5xl font-black text-[var(--text-muted)] opacity-10 group-hover:opacity-20 transition-all duration-500 transform group-hover:scale-110 select-none">${letraInicial}</span>
                        </div>

                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <h3 class="text-[12px] xl:text-[13px] font-bold text-[var(--text-main)] leading-snug line-clamp-2">${prod.nombre}</h3>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-[14px] font-black text-[var(--text-main)] tracking-tight">$${precioNum.toFixed(2)}</span>
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
    let numeroPersonas = (config.mesa && config.mesa.capacidad) || 4;
    let descuentoPorcentaje = 0;
    let notaGeneral = '';

    const listaTicket = document.getElementById('listaTicket');
    const estadoVacio = document.getElementById('estadoVacio');
    const barraModificadores = document.getElementById('barraModificadores');
    const contenedorBotonesModificadores = document.getElementById('contenedorBotonesModificadores');

    window.agregarAlTicket = function (id, nombre, precio, categoria, arrayModificadores = []) {
        cambiarTab('nueva-orden', document.getElementById('btn-tab-nueva-orden'));
        estadoVacio.classList.add('hidden');

        const modsString = JSON.stringify(arrayModificadores).replace(/'/g, "&#39;").replace(/"/g, "&quot;");
        const precioUnitario = parseFloat(precio);
        const gramajeKey = gramajePendiente ? gramajePendiente.toString() : 'sin-gramaje';

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
        } else {
            contadorItems++;
            const itemId = 'ticket-item-' + contadorItems;

            const etiquetaGramaje = gramajePendiente ? `<span class="text-[7px] text-[var(--text-muted)] font-bold bg-[var(--input-bg)] border border-[var(--border-color)] px-1.5 py-0.5 rounded-md shadow-sm">${gramajePendiente}g</span>` : '';

            let etiquetaTiempo = '';
            if (tiempoGlobal !== 'sin-tiempo') {
                const t = tiempoGlobal === 'primer-tiempo' ? '1T' : (tiempoGlobal === 'segundo-tiempo' ? '2T' : '3T');
                etiquetaTiempo = `<span class="text-[7px] text-[var(--text-muted)] font-bold bg-[var(--input-bg)] border border-[var(--border-color)] px-1.5 py-0.5 rounded-md shadow-sm">${t}</span>`;
            }

            const itemHTML = `
                <div id="${itemId}" data-producto-id="${id}" data-cantidad="1" data-precio="${precioUnitario}" data-modificadores="${modsString}" data-gramaje="${gramajeKey}" data-tiempo="${tiempoGlobal}" class="ticket-item animate-item relative w-full rounded-[18px] bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-sm p-4 flex flex-col gap-3 cursor-pointer transition-all duration-300 outline-none" onclick="seleccionarItem('${itemId}')">

                    <div class="flex justify-between items-start gap-2">
                        <div class="flex-1">
                            <h3 class="text-[12px] font-bold text-[var(--text-main)] leading-tight nombre-platillo">${nombre}</h3>
                            <div class="flex flex-wrap gap-1.5 mt-1.5 empty:hidden">
                                <div class="gramaje-etiqueta empty:hidden">${etiquetaGramaje}</div>
                                ${etiquetaTiempo}
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
        }

        if (gramajePendiente) { gramajePendiente = null; document.getElementById('indicador-gramaje-pendiente').classList.add('hidden'); }
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

    function actualizarTotales() {
        const subtotalConDescuento = Math.max(0, ticketSubtotal - (ticketSubtotal * (descuentoPorcentaje / 100)));
        const iva = subtotalConDescuento * 0.16;
        document.getElementById('txtSubtotal').innerText = '$' + subtotalConDescuento.toFixed(2);
        document.getElementById('txtIva').innerText = '$' + iva.toFixed(2);
        actualizarVistaTotal();
    }

    window.limpiarTicket = function () {
        document.getElementById('listaTicket').innerHTML = ''; document.getElementById('estadoVacio').classList.remove('hidden');
        ticketSubtotal = 0; descuentoPorcentaje = 0; notaGeneral = ''; actualizarTotales(); deseleccionarTicket();
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

    window.ajustarGramaje = function () {
        const modal = document.getElementById('modalGramaje'); const input = document.getElementById('gramajeInput');
        if (itemActivo) { input.value = itemActivo.dataset.gramaje === 'sin-gramaje' ? '' : itemActivo.dataset.gramaje; document.getElementById('modalGramajeTitulo').innerText = itemActivo.querySelector('.nombre-platillo').innerText; }
        else { input.value = gramajePendiente || ''; document.getElementById('modalGramajeTitulo').innerText = 'Gramaje'; }
        modal.classList.remove('hidden'); input.focus();
    };

    window.guardarGramajeDelItem = function () {
        const val = document.getElementById('gramajeInput').value.trim() || null;
        if (itemActivo) {
            itemActivo.dataset.gramaje = val ? val.toString() : 'sin-gramaje';
            itemActivo.querySelector('.gramaje-etiqueta').innerHTML = val ? `<span class="text-[9px] text-[var(--text-muted)] font-bold bg-[var(--input-bg)] border border-[var(--border-color)] px-1.5 py-0.5 rounded-md shadow-sm">${val}g</span>` : '';
        } else {
            gramajePendiente = val; const ind = document.getElementById('indicador-gramaje-pendiente');
            if (val) { ind.innerText = val + 'g'; ind.classList.remove('hidden'); } else { ind.classList.add('hidden'); }
        }
        cerrarModal('modalGramaje');
    };

    window.anadirNumeroGramaje = function (num) { const i = document.getElementById('gramajeInput'); i.value = (i.value === '0' ? '' : i.value) + num; };
    window.borrarNumeroGramaje = function () { const i = document.getElementById('gramajeInput'); i.value = i.value.slice(0, -1); };

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
    window.guardarPersonas = function () { const v = parseInt(document.getElementById('personasInput').value, 10); if (isNaN(v) || v <= 0) { mostrarError('Inválido'); return; } numeroPersonas = v; document.getElementById('txtPersonas').innerText = v; cerrarModal('modalPersonas'); };
    window.agregarNota = function () { if (!itemActivo) { mostrarError('Selecciona platillo'); return; } document.getElementById('notaTextarea').value = ''; document.getElementById('modalNota').classList.remove('hidden'); };
    window.aplicarDescuento = function () { document.getElementById('descuentoInput').value = descuentoPorcentaje; document.getElementById('modalDescuento').classList.remove('hidden'); };
    window.guardarDescuento = function () { const v = parseFloat(document.getElementById('descuentoInput').value); if (isNaN(v) || v < 0 || v > 100) { mostrarError('Inválido'); return; } descuentoPorcentaje = v; actualizarTotales(); cerrarModal('modalDescuento'); };
    window.mostrarPromociones = function () { window.location.href = (config.rutas && config.rutas.promociones) || '#'; };
    window.cerrarModal = function (id) { document.getElementById(id).classList.add('hidden'); };
    window.insertarNotaCaracter = function (c) { const t = document.getElementById('notaTextarea'); t.value += c; t.focus(); };
    window.insertarNotaEspacio = function () { const t = document.getElementById('notaTextarea'); t.value += ' '; t.focus(); };
    window.borrarNotaCaracter = function () { const t = document.getElementById('notaTextarea'); t.value = t.value.slice(0, -1); t.focus(); };
    window.limpiarNota = function () { const t = document.getElementById('notaTextarea'); t.value = ''; t.focus(); };
    window.imprimirPrecuenta = function () { mostrarExito("Imprimiendo pre-cuenta..."); };
    window.marcharTiempos = function () { mostrarExito("¡Marchando platillos!"); };

    // ---------------------------------------------------------------
    // FLUJO CAPITÁN / TRASPASO
    // ---------------------------------------------------------------
    let capitanAutorizado = false;
    let mesaDestinoSeleccionada = null;
    let mesaDestinoSeleccionadaNumero = null;

    window.llamarCapitan = async function () {
        const nip = prompt("NIP Capitán:"); if (!nip) return;
        try {
            const res = await fetch(config.rutas.capitanVerify, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ nip: nip.trim() })
            });
            const data = await res.json().catch(() => null);
            if (!res.ok) throw new Error(data?.message || 'Error');
            if (data.success) {
                capitanAutorizado = true;
                mostrarExito('Capitán autorizado.');
                const container = document.getElementById('capitanMesasContainer');
                container.innerHTML = '';
                if (Array.isArray(data.mesas) && data.mesas.length > 0) {
                    data.mesas.forEach(m => {
                        const btn = document.createElement('button');
                        btn.type = 'button'; btn.dataset.mesaId = m.id;
                        btn.className = 'w-full text-left rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] px-4 py-4 hover:border-[#3B82F6]/50 transition-all flex items-center justify-between gap-4';
                        btn.innerHTML = `<div><p class="text-[10px] uppercase tracking-[0.2em] text-[var(--text-muted)] font-bold">Mesa abierta</p><h3 class="text-lg font-black text-[var(--text-main)]">${m.numero}</h3></div><span class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-400"><i class="fas fa-circle text-[6px]"></i> ${m.estado ? m.estado.charAt(0).toUpperCase() + m.estado.slice(1) : ''}</span>`;
                        btn.addEventListener('click', () => seleccionarMesaDestino(m.id, m.numero));
                        container.appendChild(btn);
                    });
                } else {
                    container.innerHTML = `<div class="rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] p-6 text-center text-[var(--text-muted)]"><p class="font-bold text-sm mb-2">No hay mesas abiertas disponibles.</p></div>`;
                }
                document.getElementById('modalCapitan').classList.remove('hidden');
            }
        } catch (err) { mostrarError(err.message); }
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
        actualizarMensajeDestino();
        document.getElementById('modalCapitan').classList.add('hidden');
        mostrarExito(`Mesa ${mesaNumero} lista para envío.`);
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

                    // Reflejamos de inmediato lo recién enviado en el historial local,
                    // para que "Total" ya sume ese monto sin esperar el redirect.
                    platillosData.forEach(p => {
                        platillosEnviadosDB.push({
                            nombre: p.nombre,
                            cantidad: p.cantidad,
                            precio: p.precio,
                            estado: 'enviado'
                        });
                    });

                    // Vaciamos el carrito YA, no hasta que llegue el redirect. Así la
                    // pestaña "Orden" queda en $0 de verdad sin importar a qué pestaña
                    // se cambie mientras se completa la navegación de vuelta a Mesas.
                    limpiarTicket();

                    setTimeout(() => window.location.href = (config.rutas && config.rutas.dashboard) || '/', 1000);
                }
                else throw new Error(data.message);
            }).catch(error => { mostrarError(error.message); btn.innerHTML = '<i class="fas fa-paper-plane text-sm"></i><span>Enviar Orden</span>'; btn.disabled = false; });
    };
})();