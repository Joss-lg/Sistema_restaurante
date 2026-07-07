document.addEventListener('DOMContentLoaded', () => {
    const displayMonto = document.getElementById('monto-input');
    const displayCambio = document.getElementById('display-cambio');
    const btnPagar = document.getElementById('btn-procesar-pago');
    const teclas = document.querySelectorAll('.btn-tecla');
    
    // 1. Validación de seguridad para el total
    const totalElement = document.getElementById('total-pagar-derecha');
    if (!totalElement) {
        console.warn("Elemento 'total-pagar-derecha' no encontrado. Script de cobro detenido.");
        return; // Salimos del script para no romper otras funciones
    }

    const totalPagar = parseFloat(totalElement.innerText.replace(/[^0-9.]/g, '')) || 0;
    let montoActual = "0.00";

    // 2. Asignación a las teclas
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

    // 3. Validación de seguridad para el botón de pago
    if (btnPagar) {
        btnPagar.addEventListener('click', async () => {
            const montoRaw = displayMonto.textContent.replace(/[^0-9.]/g, '');
            const inputMesa = document.getElementById('mesa-id');
            const inputMetodo = document.getElementById('metodo-pago');

            // Verificamos que los inputs existan antes de leer su valor
            if (!inputMesa || !inputMetodo) {
                alert('Faltan datos de la mesa o el método de pago.');
                return;
            }

            const mesaId = inputMesa.value;
            const metodo = inputMetodo.value;
            
            // Validar CSRF
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfMeta) {
                alert('Error de seguridad: Falta el token CSRF en el documento.');
                return;
            }

            // Deshabilitar botón para evitar doble cobro
            btnPagar.disabled = true;
            btnPagar.innerText = 'Procesando...';

            try {
                const response = await fetch(window.COBRO_CONFIG.urlPago, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfMeta.content,
                        'Accept': 'application/json' // Importante para que Laravel devuelva JSON en errores
                    },
                    body: JSON.stringify({ mesa_id: mesaId, monto_pagado: montoRaw, metodo_pago: metodo })
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.href = '/admin/caja';
                } else {
                    alert('Error: ' + data.message);
                    btnPagar.disabled = false;
                    btnPagar.innerText = 'Cobrar';
                }
            } catch (error) {
                console.error('Error de red o servidor:', error);
                alert('Ocurrió un error al procesar el pago. Revisa la consola.');
                btnPagar.disabled = false;
                btnPagar.innerText = 'Cobrar';
            }
        });
    }
});