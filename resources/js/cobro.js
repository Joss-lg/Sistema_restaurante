function mostrarTicketFlotante() {
    // AJUSTE: ya no recibe ordenId. Usa la URL del ticket por MESA que
    // el Blade ya construyó en window.COBRO_CONFIG.urlTicket, para que
    // el ticket impreso siempre incluya TODAS las órdenes activas de la mesa
    // (antes se imprimía solo la primera orden y se perdían productos/total
    // cuando el pedido tuvo varias rondas de envío a cocina).
    const urlTicket = window.COBRO_CONFIG && window.COBRO_CONFIG.urlTicket;
    if (!urlTicket) {
        console.warn('No se encontró urlTicket en COBRO_CONFIG.');
        return;
    }

    // Si ya había un iframe de ticket pendiente, lo quitamos
    const anterior = document.getElementById('ticket-print-frame');
    if (anterior) anterior.remove();

    const iframe = document.createElement('iframe');
    iframe.id = 'ticket-print-frame';
    // Invisible: no se ve ninguna tarjeta ni modal, solo dispara la impresión
    iframe.style.cssText = 'position: fixed; top: -9999px; left: -9999px; width: 0; height: 0; border: none;';
    iframe.src = urlTicket;

    document.body.appendChild(iframe);

    // El propio ticket.blade.php ya hace window.print() al cargar.
    // Aquí solo esperamos a que el usuario cierre el diálogo (imprimir o cancelar)
    // para quitar el iframe y no dejar basura en el DOM.
    iframe.addEventListener('load', () => {
        try {
            iframe.contentWindow.addEventListener('afterprint', () => {
                iframe.remove();
            });
        } catch (e) {
            console.error('No se pudo enlazar el evento afterprint del ticket:', e);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // ==========================================================================
    // SELECCIÓN DE ELEMENTOS DE LA INTERFAZ
    // ==========================================================================
    const displayMonto = document.getElementById('monto-input');
    const displayCambio = document.getElementById('display-cambio');
    const btnPagar = document.getElementById('btn-procesar-pago'); // Botón "FINALIZAR"
    const teclas = document.querySelectorAll('.btn-tecla');
    const btnTicket = document.getElementById('btn-ticket');
    
    // Disparador del Modal desde tu panel-pago
    const btnAbrirModal = document.getElementById('btn-abrir-modal-metodo');
    
    // Elementos del modal integrado (metodo-pago.blade.php)
    const modalMetodo = document.getElementById('modal-metodo');
    const btnConfirmarCombinado = document.getElementById('btn-confirmar-combinado');
    const btnCerrarModal = document.getElementById('btn-cerrar-modal-metodo');
    const btnActivarCombinado = document.getElementById('btn-activar-combinado');
    
    // Secciones y títulos del modal para el intercambio de vistas
    const seccionLista = document.getElementById('seccion-metodos-lista');
    const seccionCombinado = document.getElementById('seccion-metodos-combinado');
    const tituloModal = document.getElementById('modal-metodo-titulo');

    // Inputs de montos e indicadores del flujo mixto
    const cInputEfectivo = document.getElementById('comb-input-efectivo');
    const cInputTarjeta = document.getElementById('comb-input-tarjeta');
    const cInputTransferencia = document.getElementById('comb-input-transferencia');
    const cDisplayTotal = document.getElementById('comb-total-requerido');
    const cDisplayStatus = document.getElementById('comb-monto-status');
    const cLabelStatus = document.getElementById('comb-label-status');
    
    // ELEMENTOS DE REFERENCIA (Fijos y Directos)
    const cInputRefTarjeta = document.getElementById('comb-ref-tarjeta');
    const cInputRefTransferencia = document.getElementById('comb-ref-transferencia');
    
    // Elementos de estado en el panel derecho
    const inputMetodoOculto = document.getElementById('metodo-pago');
    const labelMetodoVisible = document.getElementById('metodo-pago-label');

    // NUEVO: sección + input de referencia para pago único (Flujo A)
    const nonCashSection = document.getElementById('non-cash-section');
    const inputReferenciaUnica = document.getElementById('referencia');

    let modoCombinadoActivo = false;

    // ==========================================================================
    // 1. VALIDACIÓN DE SEGURIDAD PARA EL TOTAL DE LA ORDEN
    // ==========================================================================
    const totalElement = document.getElementById('total-pagar-derecha');
    if (!totalElement) {
        console.warn("Elemento 'total-pagar-derecha' no encontrado. Script de cobro detenido.");
        return; 
    }

    const totalPagar = parseFloat(totalElement.innerText.replace(/[^0-9.]/g, '')) || 0;
    let montoActual = "0.00";

    // ==========================================================================
    // 2. TECLADO DIGITAL DEL PANEL DE CONTROL
    // ==========================================================================
    if (teclas.length > 0 && displayMonto && displayCambio) {
        teclas.forEach(tecla => {
            tecla.addEventListener('click', () => {
                const valor = tecla.dataset.value;
                if (valor === 'DEL') {
                    montoActual = "0.00";
                } else {
                    montoActual = (montoActual === "0.00") ? valor : montoActual + valor;
                }
                actualizarDisplay(montoActual);
            });
        });
    }

    function actualizarDisplay(valor) {
        const montoIngresado = parseFloat(valor.replace(/[^0-9.]/g, '')) || 0;
        displayMonto.textContent = '$' + montoIngresado.toLocaleString('en-US', {minimumFractionDigits: 2});
        
        const cambio = montoIngresado - totalPagar;
        if (cambio >= 0) {
            displayCambio.textContent = '$' + cambio.toLocaleString('en-US', {minimumFractionDigits: 2});
            displayCambio.className = 'text-green-500 font-black';
        } else {
            displayCambio.textContent = '$' + Math.abs(cambio).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' (Falta)';
            displayCambio.className = 'text-red-500 font-black';
        }
    }

    // ==========================================================================
    // 3. CONTROL DE APERTURA Y CIERRE DEL MODAL
    // ==========================================================================
    if (btnAbrirModal && modalMetodo) {
        btnAbrirModal.addEventListener('click', (e) => {
            e.preventDefault();
            modalMetodo.classList.remove('hidden'); 
        });
    }

    if (btnCerrarModal && modalMetodo) {
        btnCerrarModal.addEventListener('click', () => {
            if (modoCombinadoActivo) {
                modoCombinadoActivo = false;
                tituloModal.textContent = "Método de Pago";
                seccionCombinado.classList.add('hidden');
                seccionLista.classList.remove('hidden');
                btnCerrarModal.textContent = "Cancelar";
            } else {
                modalMetodo.classList.add('hidden');
            }
        });
    }

    // NUEVO: Muestra/oculta el campo de referencia según el método elegido.
    // Efectivo -> oculto. Transferencia / Tarjeta -> visible.
    function actualizarVisibilidadReferencia(metodo) {
        if (!nonCashSection) return;
        const esEfectivo = metodo.toLowerCase() === 'efectivo';
        nonCashSection.classList.toggle('hidden', esEfectivo);
        if (esEfectivo && inputReferenciaUnica) {
            inputReferenciaUnica.value = '';
        }
    }

    // SELECCIÓN DE MÉTODO INDIVIDUAL EN LA LISTA
    document.querySelectorAll('.metodo-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const metodo = this.getAttribute('data-metodo');
            
            if (inputMetodoOculto) inputMetodoOculto.value = metodo;
            if (labelMetodoVisible) labelMetodoVisible.textContent = metodo.charAt(0).toUpperCase() + metodo.slice(1);
            
            const icon = labelMetodoVisible ? labelMetodoVisible.previousElementSibling : null;
            if (icon) {
                icon.className = this.querySelector('i').className + " text-lg";
            }

            // NUEVO: alterna el campo de referencia según el método elegido
            actualizarVisibilidadReferencia(metodo);
            
            if (modalMetodo) modalMetodo.classList.add('hidden');
        });
    });

    // ==========================================================================
    // 4. INTERCAMBIO DINÁMICO Y MATEMÁTICAS DEL PAGO MIXTO / COMBINADO
    // ==========================================================================
    if (btnActivarCombinado) {
        btnActivarCombinado.addEventListener('click', () => {
            modoCombinadoActivo = true;
            tituloModal.textContent = "Pagos Combinados";
            seccionLista.classList.add('hidden');
            seccionCombinado.classList.remove('hidden');
            btnCerrarModal.textContent = "Volver Atrás";

            if (cDisplayTotal) {
                cDisplayTotal.textContent = '$' + totalPagar.toLocaleString('en-US', {minimumFractionDigits: 2});
            }
            
            if (cInputEfectivo) cInputEfectivo.value = '';
            if (cInputTarjeta) cInputTarjeta.value = '';
            if (cInputTransferencia) cInputTransferencia.value = '';
            
            if (cInputRefTarjeta) cInputRefTarjeta.value = '';
            if (cInputRefTransferencia) cInputRefTransferencia.value = '';
            
            calcularMatematicasCombinado();
        });
    }

    [cInputEfectivo, cInputTarjeta, cInputTransferencia].forEach(input => {
        if (input) {
            input.addEventListener('input', () => {
                calcularMatematicasCombinado();
            });
        }
    });

    function calcularMatematicasCombinado() {
        if (!cDisplayStatus || !cLabelStatus || !btnConfirmarCombinado) return;

        const efec = parseFloat(cInputEfectivo.value) || 0;
        const tarj = parseFloat(cInputTarjeta.value) || 0;
        const transf = parseFloat(cInputTransferencia.value) || 0;

        const totalIngresado = efec + tarj + transf;
        const diferencia = totalIngresado - totalPagar;

        if (diferencia >= 0) {
            cLabelStatus.textContent = 'Cambio';
            cDisplayStatus.textContent = '$' + diferencia.toLocaleString('en-US', {minimumFractionDigits: 2});
            cDisplayStatus.className = 'text-xl font-black text-emerald-500';

            btnConfirmarCombinado.disabled = false;
            btnConfirmarCombinado.className = "w-full py-4 px-4 bg-emerald-600 dark:bg-emerald-500 text-white font-black text-sm uppercase tracking-wider rounded-2xl border border-emerald-500 hover:bg-emerald-500 transition-all cursor-pointer";
        } else {
            cLabelStatus.textContent = 'Restante';
            cDisplayStatus.textContent = '$' + Math.abs(diferencia).toLocaleString('en-US', {minimumFractionDigits: 2});
            cDisplayStatus.className = 'text-xl font-black text-red-500';

            btnConfirmarCombinado.disabled = true;
            btnConfirmarCombinado.className = "w-full py-4 px-4 !bg-gray-100 dark:!bg-white/5 !text-gray-400 dark:!text-white/30 font-black text-sm uppercase tracking-wider rounded-2xl border !border-gray-200 dark:!border-white/5 cursor-not-allowed transition-all";
        }
    }

    // ==========================================================================
    // 5. ENVÍOS FETCH POST (FLUJO A Y FLUJO B)
    // ==========================================================================
    
    // FLUJO A: Botón FINALIZAR de la pantalla principal (Pago Único)
    if (btnPagar) {
        btnPagar.addEventListener('click', async () => {
            const montoRaw = displayMonto.textContent.replace(/[^0-9.]/g, '');
            const inputMesa = document.getElementById('mesa-id');
            const metodo = inputMetodoOculto ? inputMetodoOculto.value : 'efectivo';

            if (!inputMesa) {
                alert('Faltan datos de la mesa.');
                return;
            }

            btnPagar.disabled = true;
            btnPagar.innerText = 'PROCESANDO...';

            // NUEVO: si el método no es efectivo, mandamos la referencia capturada
            const esEfectivo = metodo.toLowerCase() === 'efectivo';
            const referencia = (!esEfectivo && inputReferenciaUnica) ? inputReferenciaUnica.value.trim() : null;

            const payload = {
                mesa_id: inputMesa.value,
                pagos: [
                    { 
                        metodo: metodo.toLowerCase(), 
                        monto: parseFloat(montoRaw) || totalPagar,
                        referencia: referencia || null
                    }
                ]
            };

            await enviarPeticionPago(payload, btnPagar);
        });
    }

    // FLUJO B: Botón CONFIRMAR PAGO COMBINADO del Modal Mixto
    if (btnConfirmarCombinado) {
        btnConfirmarCombinado.addEventListener('click', async () => {
            const inputMesa = document.getElementById('mesa-id');
            if (!inputMesa) return;

            btnConfirmarCombinado.disabled = true;
            btnConfirmarCombinado.innerText = 'PROCESANDO...';

            const montoEfectivo = parseFloat(cInputEfectivo.value) || 0;
            const montoTarjeta = parseFloat(cInputTarjeta.value) || 0;
            const montoTransferencia = parseFloat(cInputTransferencia.value) || 0;

            const payload = {
                mesa_id: inputMesa.value,
                pagos: [
                    { 
                        metodo: 'efectivo', 
                        monto: montoEfectivo, 
                        referencia: null 
                    },
                    { 
                        metodo: 'tarjeta', 
                        monto: montoTarjeta, 
                        // Optimizamos: Si el monto es 0, forzamos null ignorando texto huérfano en el input
                        referencia: (montoTarjeta > 0 && cInputRefTarjeta) ? cInputRefTarjeta.value.trim() : null 
                    },
                    { 
                        metodo: 'transferencia', 
                        monto: montoTransferencia, 
                        // Optimizamos: Si el monto es 0, forzamos null ignorando texto huérfano en el input
                        referencia: (montoTransferencia > 0 && cInputRefTransferencia) ? cInputRefTransferencia.value.trim() : null 
                    }
                ]
            };

            await enviarPeticionPago(payload, btnConfirmarCombinado);
        });
    }

    // PETICIÓN AJAX CENTRALIZADA
    async function enviarPeticionPago(payload, botonActivo) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            alert('Error de seguridad: Falta el token CSRF.');
            restaurarBoton(botonActivo);
            return;
        }

        const urlEnvio = (window.COBRO_CONFIG && window.COBRO_CONFIG.urlPago) 
            ? window.COBRO_CONFIG.urlPago 
            : '/caja/procesar-pago';

        try {
            const response = await fetch(urlEnvio, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMeta.content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            
            if (data.success) {
                // AJUSTE: ya no depende de leer 'orden-id' del DOM. El ticket
                // se imprime por MESA usando la URL ya armada en COBRO_CONFIG,
                // así siempre incluye todas las órdenes activas de la mesa.
                mostrarTicketFlotante();
                window.location.href = data.redirect_url || '/caja';
            } else {
                alert('Error: ' + data.message);
                restaurarBoton(botonActivo);
            }
        } catch (error) {
            console.error('Error de red o servidor:', error);
            alert('Ocurrió un error al procesar el pago en el servidor.');
            restaurarBoton(botonActivo);
        }
    }

    function restaurarBoton(boton) {
        if (!boton) return;
        boton.disabled = false;
        if (boton.id === 'btn-confirmar-combinado') {
            boton.className = "w-full py-4 px-4 bg-emerald-600 dark:bg-emerald-500 text-white font-black text-sm uppercase tracking-wider rounded-2xl border border-emerald-500 hover:bg-emerald-500 transition-all cursor-pointer";
            boton.innerHTML = 'Confirmar Pago Combinado';
        } else {
            boton.innerText = 'FINALIZAR';
        }
    }

    // ==========================================================================
    // 6. CONTROL DEL BOTÓN TICKET
    // ==========================================================================
    if (btnTicket) {
        btnTicket.addEventListener('click', () => {
            // AJUSTE: ya no valida 'orden-id', solo requiere que exista
            // la URL del ticket por mesa en COBRO_CONFIG.
            if (!window.COBRO_CONFIG || !window.COBRO_CONFIG.urlTicket) {
                alert('No se encontró información de la mesa para generar el ticket.');
                return;
            }
            mostrarTicketFlotante();
        });
    }

    // ==========================================================================
    // 7. SELECTOR DE PROPINA
    // ==========================================================================
    const propinaBotones = document.querySelectorAll('.propina-btn');
    const propinaManualInput = document.getElementById('propina-manual-input');
    const btnAplicarPropinaManual = document.getElementById('btn-aplicar-propina-manual');

    async function aplicarPropina(tipo, valor, botonActivo) {
        const inputOrden = document.getElementById('orden-id');
        if (!inputOrden || !inputOrden.value) {
            alert('No se encontró una orden activa para aplicar la propina.');
            return;
        }

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) {
            alert('Error de seguridad: Falta el token CSRF.');
            return;
        }

        if (botonActivo) {
            botonActivo.disabled = true;
        }

        try {
            const response = await fetch(`/caja/orden/${inputOrden.value}/propina`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMeta.content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ tipo, valor })
            });

            const data = await response.json();

            if (data.success) {
                // Recargamos para que el total (monto a cobrar) se recalcule
                // desde el servidor con la nueva propina ya aplicada.
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo aplicar la propina.'));
                if (botonActivo) botonActivo.disabled = false;
            }
        } catch (error) {
            console.error('Error al aplicar propina:', error);
            alert('Ocurrió un error al aplicar la propina.');
            if (botonActivo) botonActivo.disabled = false;
        }
    }

    propinaBotones.forEach(btn => {
        btn.addEventListener('click', () => {
            const porcentaje = parseFloat(btn.dataset.porcentaje) || 0;
            aplicarPropina('porcentaje', porcentaje, btn);
        });
    });

    if (btnAplicarPropinaManual) {
        btnAplicarPropinaManual.addEventListener('click', () => {
            const valor = parseFloat(propinaManualInput.value) || 0;
            if (valor < 0) {
                alert('El monto de propina no puede ser negativo.');
                return;
            }
            aplicarPropina('manual', valor, btnAplicarPropinaManual);
        });
    }
});