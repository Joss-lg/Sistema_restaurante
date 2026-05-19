<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Bajo Stock</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #1e293b; }
        .title { font-size: 18px; margin-top: 5px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #f8fafc; color: #475569; text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 12px; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .status-badge { background-color: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">OLLINTEM PRO</div>
        <div class="title">Reporte de Insumos con Bajo Stock</div>
        <p style="font-size: 12px;">Generado el: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Insumo</th>
                <th>Categoría</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($insumos as $insumo)
            <tr>
                <td>{{ $insumo->nombre }}</td>
                <td>{{ $insumo->categoria->nombre ?? 'S/C' }}</td>
                <td style="font-weight: bold; color: #e11d48;">{{ $insumo->stock_actual }} {{ $insumo->unidad_medida }}</td>
                <td>{{ $insumo->stock_minimo }}</td>
                <td><span class="status-badge">REABASTECER</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistema de Gestión de Restaurante - Ollintem Pro
    </div>
</body>
</html>