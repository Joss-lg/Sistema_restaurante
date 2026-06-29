document.addEventListener('DOMContentLoaded', () => {
    const displayMonto = document.getElementById('monto-input');
    const displayCambio = document.getElementById('display-cambio');
    const btnPagar = document.getElementById('btn-procesar-pago');
    const teclas = document.querySelectorAll('.btn-tecla');
    
    // Obtenemos el total desde el atributo data del elemento en el HTML
    const totalPagar = parseFloat(document.getElementById('total-pagar-derecha').innerText.replace(/[^0-9.]/g, ''));
    let montoActual = "0.00";

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

    function actualizarDisplay(valor) {
        const montoIngresado = parseFloat(valor.replace(/[^0-9.]/g, ''));
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

    if (btnPagar) {
        btnPagar.addEventListener('click', async () => {
            const montoRaw = displayMonto.textContent.replace(/[^0-9.]/g, '');
            const mesaId = document.getElementById('mesa-id').value;
            const metodo = document.getElementById('metodo-pago').value;

            try {
                const response = await fetch('/admin/caja/api/procesar-pago', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ mesa_id: mesaId, monto_pagado: montoRaw, metodo_pago: metodo })
                });

                const data = await response.json();
                if (data.success) {
                    window.location.href = '/admin/caja';
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }
});