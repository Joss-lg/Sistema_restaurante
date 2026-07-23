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
            width: 72mm; /* Ligeramente menor que 80mm para dejar un margen físico limpio en la impresora */
            margin: 4mm auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 15px;   /* AJUSTE: antes 12px */
            line-height: 1.35; /* AJUSTE: antes 1.2, un poco más de aire entre líneas */
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
            padding: 4px 0; /* AJUSTE: antes 3px, un poco más de espacio con letra más grande */
            vertical-align: top; 
        }
        .right { text-align: right; }
        
        /* Estilos de productos y totales */
        .item-row td {
            font-weight: bold;
        }
        .desc-row td {
            font-size: 12px; /* AJUSTE: antes 10px */
            color: #333;
            padding-left: 10px;
            padding-bottom: 4px;
        }
        .totales-table td {
            padding: 3px 0; /* AJUSTE: antes 2px */
        }
        .total-final { 
            font-weight: bold; 
            font-size: 18px; /* AJUSTE: antes 15px */
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0 !important; /* AJUSTE: antes 4px */
            margin-top: 4px;
        }
        
        /* Botón de impresión oculto al mandar a imprimir */
        @media print { 
            .no-print { display: none !important; } 
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="center">
        <span style="font-size: 17px;" class="bold">{{ $negocio['nombre'] }}</span><br> <!-- AJUSTE: antes 14px -->
        <span class="bold">Ticket: {{ $folio }}</span><br>
        <span style="font-size: 13px;">{{ $fecha }}</span> <!-- AJUSTE: antes 11px -->
        @if($mesa) <br><span class="bold">Mesa: {{ $mesa }}</span> @endif
        @if($mesero) <br><span style="font-size: 13px;">Atendió: {{ $mesero }}</span> @endif <!-- AJUSTE: antes 11px -->
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

        {{-- --- CONDICIONAL DE IVA --- --}}
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
        <div class="center bold" style="font-size: 13px; margin-bottom: 2px;">FORMA DE PAGO</div> <!-- AJUSTE: antes 11px -->
        <table>
            @foreach($pagos as $pago)
                <tr>
                    <td>{{ $pago['metodo'] }}</td>
                    <td class="right">${{ number_format($pago['monto'], 2) }}</td>
                </tr>
                @if(!empty($pago['referencia']))
                <tr>
                    <td colspan="2" style="font-size: 12px; font-style: italic;">Ref: {{ $pago['referencia'] }}</td> <!-- AJUSTE: antes 10px -->
                </tr>
                @endif
            @endforeach
        </table>
    @endif

    <div class="linea"></div>
    <div class="center" style="margin-top: 6px; font-size: 13px;">¡Gracias por su compra!</div> <!-- AJUSTE: antes 11px -->

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