<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket {{ $folio }}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        body {
            width: 80mm;
            margin: 0 auto;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #000;
        }
        .center { text-align: center; }
        .linea { border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        .total { font-weight: bold; font-size: 14px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

    <div class="center">
        <strong>{{ $negocio['nombre'] }}</strong><br>
        Ticket {{ $folio }}<br>
        {{ $fecha }}
        @if($mesa) <br>Mesa: {{ $mesa }} @endif
        @if($mesero) <br>Atendió: {{ $mesero }} @endif
    </div>
    <div class="linea"></div>

    <table>
        @foreach($items as $item)
            <tr>
                <td>{{ $item['cantidad'] }}x {{ $item['nombre'] }}</td>
                <td class="right">${{ number_format($item['subtotal'], 2) }}</td>
            </tr>
            @if($item['descuento'] > 0)
                <tr>
                    <td style="font-size:10px; padding-left:8px;">
                        Descuento{{ $item['promocion_nombre'] ? ' ('.$item['promocion_nombre'].')' : '' }}
                    </td>
                    <td class="right" style="font-size:10px;">-${{ number_format($item['descuento'], 2) }}</td>
                </tr>
            @endif
        @endforeach
    </table>

    <div class="linea"></div>
    <table>
        <tr><td>Subtotal</td><td class="right">${{ number_format($subtotal, 2) }}</td></tr>
        @if($descuentoTotal > 0)
        <tr><td>Descuento total</td><td class="right">-${{ number_format($descuentoTotal, 2) }}</td></tr>
        @endif
        @if($propina > 0)
        <tr><td>Propina</td><td class="right">${{ number_format($propina, 2) }}</td></tr>
        @endif
        <tr class="total"><td>TOTAL</td><td class="right">${{ number_format($total, 2) }}</td></tr>
    </table>
    
    @if($pagos->isNotEmpty())
        <div class="linea"></div>
        <table>
            @foreach($pagos as $pago)
                <tr>
                    <td>{{ $pago['metodo'] }}</td>
                    <td class="right">${{ number_format($pago['monto'], 2) }}</td>
                </tr>
                @if($pago['referencia'])
                <tr><td colspan="2" style="font-size:10px;">Ref: {{ $pago['referencia'] }}</td></tr>
                @endif
            @endforeach
        </table>
    @endif

    <div class="linea"></div>
    <div class="center">¡Gracias por su compra!</div>

    <button class="no-print" onclick="window.print()">Imprimir</button>

    <script>
        window.onload = () => window.print();
        window.onafterprint = () => window.close();
    </script>
</body>
</html>