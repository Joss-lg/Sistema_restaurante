var ComandaConfig_ = window.ComandaConfig || {};

var categoriasDB = ComandaConfig_.categorias || [];
var productosDB = ComandaConfig_.productos || [];
var platillosEnviadosDB = ComandaConfig_.platillosEnviados || [];

var ticketSubtotal = 0;
var itemActivo = null;
var contadorItems = 0;
var tiempoGlobal = 'sin-tiempo';
var gramajePendiente = null;
// Producto seleccionado desde el menú que se vende por peso, en espera
// de que el mesero capture el gramaje en el modal antes de agregarse
// al ticket con el precio ya calculado.
var productoPorPesoPendiente = null;
var numeroPersonas = (ComandaConfig_.mesa && ComandaConfig_.mesa.personas) || 4;
var descuentoPorcentaje = 0;
var notaGeneral = '';
var promocion2x1Activa = false;
var promocion2x1Nombre = '';
var comboActivo = false;
var comboNombre = '';
var comboProductoIds = [];
var comboMonto = 0;
var capitanAutorizado = false;
var mesaDestinoSeleccionada = null;
var mesaDestinoSeleccionadaNumero = null;

// =====================================================================
// UTILIDADES COMPARTIDAS
// =====================================================================
(function () {
    const config = window.ComandaConfig || {};

    window.csrfToken = function () {
        return config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;
    };

    // ---------------------------------------------------------------
    // Compatibilidad del catálogo: algunos botones del Blade aún
    // invocan agregarProducto(id), por lo que exponemos un puente que
    // reenvía al handler real del ticket.
    // ---------------------------------------------------------------
    window.agregarProducto = function (productoId) {
        const producto = Array.isArray(productosDB)
            ? productosDB.find(p => Number(p.id) === Number(productoId))
            : null;

        if (!producto) {
            mostrarError('No se encontró el producto seleccionado.');
            return;
        }

        const categoriaNombre = producto.categoria?.nombre ?? '';
        const precioNum = parseFloat(producto.precio) || 0;
        const precioPor100g = parseFloat(producto.precio_por_100g) || 0;
        const modificadores = Array.isArray(producto.modificadores) ? producto.modificadores : [];

        window.agregarAlTicket(
            producto.id,
            producto.nombre,
            precioNum,
            categoriaNombre,
            modificadores,
            !!producto.se_vende_por_peso,
            precioPor100g
        );
    };

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
    // Corregido: Se añade validación 'if' para evitar el error 'null' en textTotal
    // ---------------------------------------------------------------
    window.cambiarTab = function (pestana) {
        const slider = document.getElementById('tab-slider');
        const btns = [document.getElementById('btn-tab-nueva-orden'), document.getElementById('btn-tab-enviados'), document.getElementById('btn-tab-comanda')];
        const txtTotalElement = document.getElementById('txtTotal');

        btns.forEach(el => { if (el) { el.classList.remove('text-[var(--bg-base)]'); el.classList.add('text-[var(--text-muted)]'); } });

        ['vista-nueva-orden', 'vista-enviados', 'vista-comanda'].forEach(id => {
            const vista = document.getElementById(id);
            if (vista) {
                vista.classList.add('hidden');
                vista.classList.remove('flex');
            }
        });

        if (pestana === 'nueva-orden') {
            if (slider) slider.style.transform = 'translateX(0%)';
            if (btns[0]) { btns[0].classList.add('text-[var(--bg-base)]'); btns[0].classList.remove('text-[var(--text-muted)]'); }
            const vNueva = document.getElementById('vista-nueva-orden');
            if (vNueva) { vNueva.classList.remove('hidden'); vNueva.classList.add('flex'); }
            
            // Verificación segura del elemento de precio total
            if (txtTotalElement) txtTotalElement.innerText = '$0.00';

        } else if (pestana === 'enviados') {
            if (slider) slider.style.transform = 'translateX(100%)';
            if (btns[1]) { btns[1].classList.add('text-[var(--bg-base)]'); btns[1].classList.remove('text-[var(--text-muted)]'); }
            const vEnviados = document.getElementById('vista-enviados');
            if (vEnviados) { vEnviados.classList.remove('hidden'); vEnviados.classList.add('flex'); }
            
            // Verificación segura del elemento de precio total
            if (txtTotalElement) txtTotalElement.innerText = '$0.00';

        } else if (pestana === 'comanda') {
            if (slider) slider.style.transform = 'translateX(200%)';
            if (btns[2]) { btns[2].classList.add('text-[var(--bg-base)]'); btns[2].classList.remove('text-[var(--text-muted)]'); }
            const vComanda = document.getElementById('vista-comanda');
            if (vComanda) { vComanda.classList.remove('hidden'); vComanda.classList.add('flex'); }
            
            // El total real (enviado + nuevo, con IVA) solo se calcula y se
            // muestra aquí, en la pestaña "Total". En las otras dos pestañas
            // se deja en $0.00 a propósito.
            actualizarVistaTotal();
        }
    };

    // ---------------------------------------------------------------
    // Recalcula y pinta el "Total a Pagar". Depende de calcularDescuento2x1Monto
    // y calcularDescuentoComboMonto, definidas en comanda-ticket.js y
    // expuestas globalmente (window.calcularDescuento2x1Monto / ...ComboMonto).
    // ---------------------------------------------------------------
    window.actualizarVistaTotal = function () {
        const contenedorNuevos = document.getElementById('lista-comanda-total');
        const mensajeVacio = document.getElementById('estadoVacioComanda');
        const itemsEnTicket = document.querySelectorAll('#listaTicket .ticket-item');
        const contenedorDB = document.getElementById('items-db-total');
        const hayPlatillosEnDB = contenedorDB && contenedorDB.children.length > 0;
        const txtTotalElement = document.getElementById('txtTotal');

        if (contenedorNuevos) contenedorNuevos.innerHTML = '';

        if (itemsEnTicket.length === 0 && !hayPlatillosEnDB) {
            if (mensajeVacio) { mensajeVacio.classList.remove('hidden'); mensajeVacio.classList.add('flex'); }
        } else {
            if (mensajeVacio) { mensajeVacio.classList.add('hidden'); mensajeVacio.classList.remove('flex'); }
            if (itemsEnTicket.length > 0 && contenedorNuevos) {
                contenedorNuevos.innerHTML += `<div class="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] mt-4 mb-2 px-1">Por Enviar</div>`;
                itemsEnTicket.forEach(item => {
                    const cantEl = item.querySelector('.cantidad-platillo');
                    const nomEl = item.querySelector('.nombre-platillo');
                    const preEl = item.querySelector('.precio-platillo');

                    const cant = cantEl ? cantEl.innerText : '1';
                    const nombre = nomEl ? nomEl.innerText : 'Producto';
                    const precio = preEl ? preEl.innerText : '$0.00';

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

        // Cálculos de montos y totales seguros
        const totalHistorial = platillosEnviadosDB.reduce((acc, i) => acc + ((i.precio || 0) * (i.cantidad || 1)), 0);
        
        const descuento2x1Monto = typeof window.calcularDescuento2x1Monto === 'function' ? window.calcularDescuento2x1Monto() : 0;
        const descuentoComboMonto = typeof window.calcularDescuentoComboMonto === 'function' ? window.calcularDescuentoComboMonto() : 0;
        
        const subtotalTicketTras2x1 = Math.max(0, ticketSubtotal - descuento2x1Monto - descuentoComboMonto);
        const subtotalTicketConDescuento = Math.max(0, subtotalTicketTras2x1 - (subtotalTicketTras2x1 * (descuentoPorcentaje / 100)));
        const subtotalGeneral = subtotalTicketConDescuento + totalHistorial;

        // --- AJUSTE: IVA habilitable desde configuración global ---
        const ivaConfig = (window.ComandaConfig && window.ComandaConfig.iva) || { habilitado: true, porcentaje: 16 };
        const ivaGeneral = ivaConfig.habilitado ? subtotalGeneral * (ivaConfig.porcentaje / 100) : 0;
        
        if (txtTotalElement) {
            txtTotalElement.innerText = '$' + (subtotalGeneral + ivaGeneral).toFixed(2);
        }
    };

    // ---------------------------------------------------------------
    // NOTIFICACIONES TOAST (compartidas por todos los módulos)
    // ---------------------------------------------------------------
    window.mostrarToast = function (msg, type = 'info') {
        const c = document.getElementById('toastContainer'); if (!c) return;
        const t = document.createElement('div');
        t.className = `toast-panel ${type}`;
        t.innerHTML = `<div class="toast-icon"><i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}"></i></div><div><strong>${type === 'success' ? 'Éxito' : type === 'error' ? 'Error' : 'Aviso'}</strong><span>${msg}</span></div>`;
        c.appendChild(t);
        requestAnimationFrame(() => t.classList.add('show'));
        setTimeout(() => { t.classList.remove('show'); t.addEventListener('transitionend', () => t.remove(), { once: true }); }, 3000);
    };
    window.mostrarError = function (m) { mostrarToast(m, 'error'); };
    window.mostrarExito = function (m) { mostrarToast(m, 'success'); };

    // ---------------------------------------------------------------
    // Cierra cualquier modal genérico del POS.
    // ---------------------------------------------------------------
    window.cerrarModal = function (id) {
        const modal = document.getElementById(id);
        if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
    };
})();