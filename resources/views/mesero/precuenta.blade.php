<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pre-cuenta — Mesa {{ $mesa->numero }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Courier New', Courier, monospace;
        width: 300px;
        margin: 0 auto;
        padding: 16px 12px 32px;
        color: #111;
        font-size: 13px;
    }
    .centro { text-align: center; }
    .negrita { font-weight: bold; }
    .titulo { font-size: 16px; font-weight: bold; letter-spacing: 1px; }
    .subtitulo { font-size: 11px; color: #444; margin-top: 2px; }
    hr {
        border: none;
        border-top: 1px dashed #000;
        margin: 10px 0;
    }
    table { width: 100%; border-collapse: collapse; font-size: 12px; }
    td { padding: 3px 0; vertical-align: top; }
    .col-cant { width: 28px; }
    .col-monto { width: 60px; text-align: right; white-space: nowrap; }
    .item-nombre { font-weight: bold; }
    .item-detalle { font-size: 10px; color: #444; padding-left: 2px; }
    .fila-total td { padding-top: 6px; font-weight: bold; }
    .fila-total.grande td { font-size: 15px; padding-top: 8px; }
    .footer { margin-top: 18px; font-size: 10px; text-align: center; color: #444; }
    .badge-noesun-ticket-fiscal { margin-top: 4px; font-size: 9px; text-align: center; color: #777; }

    .btn-imprimir {
        display: block;
        width: 100%;
        margin-top: 20px;
        padding: 10px;
        background: #111;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-family: Arial, sans-serif;
        font-size: 13px;
        font-weight: bold;
        cursor: pointer;
    }

    @media print {
        .btn-imprimir { display: none; }
        body { width: auto; padding: 0 4px; }
    }
</style>
</head>
<body>

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

    <script>
        // Se dispara automático para que el mesero solo tenga que elegir
        // impresora o "Guardar como PDF" en el diálogo del navegador.
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>

</body>
</html>