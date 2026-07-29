<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Bajo Stock</title>
    <style>
        @page { margin: 25px 30px; }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            color: #1a1a1a;
            font-size: 11px;
        }

        .header {
            border-bottom: 3px solid #e11d48;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0 0 4px 0;
            color: #1a1a1a;
        }
        .header p {
            margin: 0;
            font-size: 10px;
            color: #666;
        }

        .resumen {
            width: 100%;
            margin-bottom: 18px;
        }
        .resumen td {
            padding: 8px 12px;
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            font-size: 11px;
        }
        .resumen .valor {
            font-size: 16px;
            font-weight: bold;
            color: #e11d48;
        }

        table.datos {
            width: 100%;
            border-collapse: collapse;
        }
        table.datos thead th {
            background-color: #1a1a1a;
            color: #ffffff;
            text-align: left;
            padding: 8px 6px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.datos tbody td {
            padding: 7px 6px;
            border-bottom: 1px solid #e5e5e5;
            font-size: 10px;
        }
        table.datos tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #ffffff;
            background-color: #e11d48;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #e5e5e5;
            font-size: 8px;
            color: #999;
            text-align: center;
        }

        .sin-datos {
            text-align: center;
            padding: 30px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Reporte de Insumos con Bajo Stock</h1>
        <p>Generado el {{ now()->format('d/m/Y') }}</p>
    </div>

    <table class="resumen">
        <tr>
            <td style="width: 33%;">
                Total de insumos en alerta<br>
                <span class="valor">{{ $insumos->count() }}</span>
            </td>
            <td style="width: 33%;">
                Valor estimado en riesgo<br>
                <span class="valor">
                    ${{ number_format($insumos->sum(fn($i) => (float)$i->stock_actual * (float)$i->precio_compra), 2) }}
                </span>
            </td>
            <td style="width: 34%;">
                Insumos en cero<br>
                <span class="valor">{{ $insumos->where('stock_actual', '<=', 0)->count() }}</span>
            </td>
        </tr>
    </table>

    @if($insumos->isEmpty())
        <div class="sin-datos">No hay insumos con bajo stock en este momento.</div>
    @else
        <table class="datos">
            <thead>
                <tr>
                    <th style="width: 10%;">Código</th>
                    <th style="width: 25%;">Producto</th>
                    <th style="width: 15%;">Categoría</th>
                    <th style="width: 12%;" class="text-right">Stock Actual</th>
                    <th style="width: 12%;" class="text-right">Stock Mínimo</th>
                    <th style="width: 8%;" class="text-center">Unidad</th>
                    <th style="width: 10%;" class="text-right">Precio Compra</th>
                    <th style="width: 8%;" class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($insumos as $insumo)
                    <tr>
                        <td>{{ $insumo->codigo ?? 'S/N' }}</td>
                        <td>{{ $insumo->nombre }}</td>
                        <td>{{ $insumo->categoria->nombre ?? 'Sin categoría' }}</td>
                        <td class="text-right">{{ number_format($insumo->stock_actual, 2) }}</td>
                        <td class="text-right">{{ number_format($insumo->stock_minimo, 2) }}</td>
                        <td class="text-center">{{ $insumo->unidad_medida }}</td>
                        <td class="text-right">${{ number_format($insumo->precio_compra ?? 0, 2) }}</td>
                        <td class="text-center">
                            <span class="badge">
                                {{ $insumo->stock_actual <= 0 ? 'Agotado' : 'Crítico' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        El Agostadero &mdash; Sistema de Gestión de Restaurante
    </div>

</body>
</html>