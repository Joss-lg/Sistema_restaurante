/**
 * comanda-envio.js
 * Acciones finales del pedido: número de personas, nota, descuento
 * manual, pre-cuenta imprimible y el envío final a cocina.
 * Depende de: numeroPersonas, descuentoPorcentaje, itemActivo,
 * notaGeneral, platillosEnviadosDB, mesaDestinoSeleccionada
 * (comanda-core.js); window.actualizarTotales y window.limpiarTicket
 * (comanda-ticket.js); csrfToken/mostrarError/mostrarExito/cerrarModal
 * (comanda-core.js).
 */
(function () {
    const config = window.ComandaConfig || {};

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
                // OJO: ya NO se manda "notas" por separado — `modificadores`
                // ya contiene todo el texto (mods clickeados + notas escritas
                // a mano, ambos caen en la misma lista .nota-texto-real).
                // Antes se mandaban duplicados (modificadores + notas con el
                // mismo contenido) y ComandaService los concatenaba,
                // apareciendo repetidos en Cocina.
                modificadores: mods,
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