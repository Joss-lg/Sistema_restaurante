<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#f3f4f6">
<title>Pre-cuenta — Mesa {{ $mesa->numero }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

    html, body {
        height: 100%;
    }

    body {
        font-family: 'Courier New', Courier, monospace;
        color: #111;
        font-size: 13px;
        background: #e5e7eb;
        /* soporte para el "notch"/barras del sistema en móviles */
        padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);
    }

    /* ===== Contenedor de pantalla: centra el ticket como una tarjeta ===== */
    .pagina {
        min-height: 100%;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: clamp(12px, 4vw, 32px) clamp(10px, 3vw, 20px) calc(env(safe-area-inset-bottom) + 24px);
    }

    .recibo-envoltura {
        width: 100%;
        max-width: 360px;
    }

    .recibo {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 10px 30px -8px rgba(0,0,0,0.18), 0 2px 8px -2px rgba(0,0,0,0.06);
        padding: clamp(18px, 5vw, 26px) clamp(16px, 4.5vw, 22px) clamp(24px, 6vw, 30px);
        width: 100%;
    }

    .centro { text-align: center; }
    .negrita { font-weight: bold; }
    .titulo { font-size: clamp(15px, 4.2vw, 17px); font-weight: bold; letter-spacing: 1.5px; }
    .subtitulo { font-size: clamp(10px, 2.8vw, 11px); color: #555; margin-top: 3px; line-height: 1.4; }

    hr {
        border: none;
        border-top: 1px dashed #bbb;
        margin: 14px 0;
    }

    table { width: 100%; border-collapse: collapse; font-size: clamp(11.5px, 3.1vw, 12.5px); }
    td { padding: 4px 0; vertical-align: top; word-break: break-word; }
    .col-cant { width: 30px; white-space: nowrap; }
    .col-monto { width: 70px; text-align: right; white-space: nowrap; }
    .item-nombre { font-weight: bold; }
    .item-detalle { font-size: clamp(9.5px, 2.6vw, 10.5px); color: #555; padding-left: 2px; padding-top: 1px; }

    .fila-total td { padding-top: 8px; font-weight: bold; border-top: 1px solid #eee; }
    .fila-total.grande td {
        font-size: clamp(16px, 4.5vw, 18px);
        padding-top: 10px;
        border-top: none;
    }

    .footer {
        margin-top: 20px;
        font-size: clamp(9.5px, 2.6vw, 10.5px);
        text-align: center;
        color: #555;
        line-height: 1.5;
    }

    .btn-imprimir {
        display: block;
        width: 100%;
        margin-top: 22px;
        padding: 14px;
        background: #111;
        color: #fff;
        border: none;
        border-radius: 12px;
        font-family: Arial, sans-serif;
        font-size: 14px;
        font-weight: bold;
        letter-spacing: 0.2px;
        cursor: pointer;
        min-height: 48px; /* área táctil cómoda */
        -webkit-user-select: none;
        user-select: none;
        transition: transform 0.15s ease, opacity 0.15s ease;
    }
    .btn-imprimir:active {
        transform: scale(0.97);
        opacity: 0.9;
    }

    /* ===== Ajuste fino para pantallas muy angostas (≤340px) ===== */
    @media (max-width: 340px) {
        .col-monto { width: 58px; }
        .recibo { padding: 16px 14px 22px; }
    }

    /* ===== Impresión: vuelve al formato de ticket térmico clásico ===== */
    @media print {
        body { background: #fff; padding: 0; }
        .pagina { padding: 0; display: block; }
        .recibo-envoltura { max-width: none; }
        .recibo {
            box-shadow: none;
            border-radius: 0;
            width: 300px;
            margin: 0 auto;
            padding: 16px 12px 32px;
        }
        .btn-imprimir { display: none; }
    }
</style>
</head>
<body>

    <div class="pagina">
        <div class="recibo-envoltura">
            <div class="recibo">

                <div class="centro">
                    <div class="titulo">OLLINREST</div>
                    <div class="subtitulo">Pre-cuenta / cuenta informativa</div>
                    <div class="subtitulo">No es un comprobante fiscal</div>
                </div>

                <hr>

                <table>
                    <tr>
                        <td>Mesa:</td>
                        <td class="col-monto negrita">{{ $mesa->numero }}</td>
                    </tr>
                    <tr>
                        <td>Mesero:</td>
                        <td class="col-monto">{{ $orden->mesero->nombre ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Fecha:</td>
                        <td class="col-monto">{{ $fecha->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>

                <hr>

                @if($detalles->isEmpty())
                    <p class="centro">Esta mesa aún no tiene productos enviados a cocina.</p>
                @else
                    <table>
                        @foreach($detalles as $detalle)
                            <tr>
                                <td class="col-cant">{{ $detalle->cantidad }}x</td>
                                <td class="item-nombre">
                                    {{ $detalle->producto->nombre ?? 'Producto eliminado' }}
                                    @if($detalle->gramaje)
                                        @php
                                            $gramajeLimpio = rtrim(rtrim(number_format((float) $detalle->gramaje, 2, '.', ''), '0'), '.');
                                        @endphp
                                        <div class="item-detalle">{{ $gramajeLimpio }}g</div>
                                    @endif
                                    @if($detalle->notas)
                                        <div class="item-detalle">{{ $detalle->notas }}</div>
                                    @endif
                                </td>
                                <td class="col-monto">${{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}</td>
                            </tr>
                        @endforeach
                    </table>

                    <hr>

                    <table>
                        <tr>
                            <td>Subtotal</td>
                            <td class="col-monto">${{ number_format($subtotal, 2) }}</td>
                        </tr>
                        @if($descuento > 0)
                            <tr>
                                <td>Descuento</td>
                                <td class="col-monto">-${{ number_format($descuento, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td>IVA (16%)</td>
                            <td class="col-monto">${{ number_format($iva, 2) }}</td>
                        </tr>
                        <tr class="fila-total grande">
                            <td>TOTAL</td>
                            <td class="col-monto">${{ number_format($total, 2) }}</td>
                        </tr>
                    </table>
                @endif

                <div class="footer">
                    Esta pre-cuenta es solo informativa.<br>
                    Solicita tu ticket de pago en caja.
                </div>

                <button class="btn-imprimir" onclick="window.print()">Imprimir / Guardar como PDF</button>

            </div>
        </div>
    </div>

    <script>
        // Se dispara automático para que el mesero solo tenga que elegir
        // impresora o "Guardar como PDF" en el diálogo del navegador.
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>

</body>
</html>