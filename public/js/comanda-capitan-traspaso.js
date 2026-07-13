/**
 * comanda-capitan-traspaso.js
 * Verificación de NIP de capitán y traspaso de productos entre mesas.
 * Depende de: platillosEnviadosDB, ticketSubtotal, capitanAutorizado,
 * mesaDestinoSeleccionada, mesaDestinoSeleccionadaNumero (comanda-core.js);
 * window.actualizarTotales (comanda-ticket.js); cerrarModal/mostrarError/
 * mostrarExito/csrfToken (comanda-core.js).
 */
(function () {
    const config = window.ComandaConfig || {};

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
                // OJO: ya NO se manda "notas" por separado (ver comanda-envio.js
                // para el detalle del bug de duplicado que esto corrige).
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
})();