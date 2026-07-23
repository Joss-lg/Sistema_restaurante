<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket {{ $folio }}</title>
    <style>
        @page { 
            size: 80mm auto; 
            margin: 0; 
        }
        body {
            width: 72mm;
            margin: 4mm auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 15px;
            line-height: 1.35;
            color: #000;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .linea { 
            border-top: 1px dashed #000; 
            margin: 8px 0; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        td { 
            padding: 4px 0;
            vertical-align: top; 
        }
        .right { text-align: right; }
        
        .item-row td {
            font-weight: bold;
        }
        .desc-row td {
            font-size: 12px;
            color: #333;
            padding-left: 10px;
            padding-bottom: 4px;
        }
        .totales-table td {
            padding: 3px 0;
        }
        .total-final { 
            font-weight: bold; 
            font-size: 18px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0 !important;
            margin-top: 4px;
        }
        
        @media print { 
            .no-print { display: none !important; } 
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="center">
        <span style="font-size: 17px;" class="bold">{{ $negocio['nombre'] }}</span><br>
        @if($mesa)
            <span class="bold" style="font-size: 16px;">Mesa {{ preg_replace('/^mesa\s*/i', '', $mesa) }}</span><br>
        @endif
        <span style="font-size: 13px;">{{ $fecha }}</span>
        @if($mesero) <br><span style="font-size: 13px;">Atendió: {{ $mesero }}</span> @endif
    </div>

    <div class="linea"></div>

    <!-- Lista de Items -->
    <table>
        @foreach($items as $item)
            <tr class="item-row">
                <td>{{ $item['cantidad'] }}x {{ $item['nombre'] }}</td>
                <td class="right">${{ number_format($item['subtotal'], 2) }}</td>
            </tr>
            @if(($item['descuento'] ?? 0) > 0)
                <tr class="desc-row">
                    <td colspan="2">
                        Desc{{ !empty($item['promocion_nombre']) ? ' ('.$item['promocion_nombre'].')' : '' }}: -${{ number_format($item['descuento'], 2) }}
                    </td>
                </tr>
            @endif
        @endforeach
    </table>

    <div class="linea"></div>

    <!-- Totales -->
    <table class="totales-table">
        <tr>
            <td>Subtotal</td>
            <td class="right">${{ number_format($subtotal, 2) }}</td>
        </tr>
        @if(($descuentoTotal ?? 0) > 0)
        <tr>
            <td>Descuento total</td>
            <td class="right">-${{ number_format($descuentoTotal, 2) }}</td>
        </tr>
        @endif

        @if(($ivaHabilitado ?? session('iva_habilitado', true)) && isset($iva) && $iva > 0)
        <tr>
            <td>IVA ({{ number_format($ivaPorcentaje ?? 16, 0) }}%)</td>
            <td class="right">${{ number_format($iva, 2) }}</td>
        </tr>
        @endif

        @if(($propina ?? 0) > 0)
        <tr>
            <td>Propina</td>
            <td class="right">${{ number_format($propina, 2) }}</td>
        </tr>
        @endif
        <tr class="total-final">
            <td>TOTAL</td>
            <td class="right">${{ number_format($total, 2) }}</td>
        </tr>
    </table>
    
    <!-- Pagos -->
    @if(isset($pagos) && collect($pagos)->isNotEmpty())
        <div class="linea"></div>
        <div class="center bold" style="font-size: 13px; margin-bottom: 2px;">FORMA DE PAGO</div>
        <table>
            @foreach($pagos as $pago)
                <tr>
                    <td>{{ $pago['metodo'] }}</td>
                    <td class="right">${{ number_format($pago['monto'], 2) }}</td>
                </tr>
                @if(!empty($pago['referencia']))
                <tr>
                    <td colspan="2" style="font-size: 12px; font-style: italic;">Ref: {{ $pago['referencia'] }}</td>
                </tr>
                @endif
            @endforeach
        </table>
    @endif

    <div class="linea"></div>
    <div class="center" style="margin-top: 6px; font-size: 13px;">¡Gracias por su compra!</div>

    <!-- Leyenda promocional del software -->
    <div class="linea" style="margin-top: 10px;"></div>
    <div class="center" style="margin-top: 8px; font-size: 12px; line-height: 1.5;">
        <span class="bold">¿Necesitas un Software para tu negocio?</span><br>
        <span class="bold">¡Contáctanos!</span><br>
        www.ollintem.com.mx
    </div>

    <!-- Botón de respaldo -->
    <div class="center" style="margin-top: 15px;">
        <button class="no-print" style="padding: 6px 14px; cursor: pointer; font-family: sans-serif; font-weight: bold; background: #000; color: #fff; border: none; border-radius: 4px;" onclick="window.print()">Imprimir Ticket</button>
    </div>

    <script>
        window.onload = () => window.print();
        window.onafterprint = () => window.close();
    </script>
</body>
</html>