<!-- resources/views/corte/pdf.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corte de Ventas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        h3 { color: #444; border-bottom: 2px solid #ccc; padding-bottom: 5px; margin-top: 20px; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Corte General de Ventas</h2>
        <p>Reporte del: <strong>{{ $fechaInicio }}</strong> al <strong>{{ $fechaFin }}</strong></p>
    </div>

    @foreach($ventasPorArea as $area => $productos)
        <h3>Área: {{ $area }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th style="width: 150px;" class="text-center">Total Vendidos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $item)
                    <tr>
                        <td>{{ $item->producto }}</td>
                        <td class="text-center">{{ $item->total_vendido }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>