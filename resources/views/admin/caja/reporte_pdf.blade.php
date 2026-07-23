<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Caja - Turno #{{ $cajaActiva->id }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #27272a; font-size: 12px; margin: 20px; }
        .header { border-b: 2px solid #e4e4e7; padding-bottom: 12px; margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; color: #09090b; margin: 0; }
        .subtitle { font-size: 11px; color: #71717a; margin-top: 4px; }
        .meta-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .meta-table td { padding: 4px 0; vertical-align: top; }
        .summary-box { background-color: #fafafa; border: 1px solid #e4e4e7; padding: 15px; border-radius: 8px; margin-bottom: 25px; }
        .summary-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #18181b; }
        .grid { width: 100%; }
        .grid td { width: 33.33%; }
        .amount { font-size: 16px; font-weight: bold; color: #0f172a; margin-top: 4px; }
        .section-title { font-size: 13px; font-weight: bold; text-transform: uppercase; tracking: 1px; color: #4b5563; margin-bottom: 10px; margin-top: 20px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th { background-color: #f4f4f5; font-size: 10px; text-transform: uppercase; font-weight: bold; color: #52525b; padding: 8px; text-align: left; border-bottom: 1px solid #e4e4e7; }
        table.data-table td { padding: 8px; border-bottom: 1px solid #f4f4f5; font-size: 11px; }
        .text-right { text-align: right; }
        .badge { padding: 2px 6px; font-size: 10px; font-weight: bold; border-radius: 4px; background-color: #e0f2fe; color: #0369a1; }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%">
            <tr>
                <td>
                    <h1 class="title">Agostadero</h1>
                    <div class="subtitle">Reporte de Turno y Flujo de Caja</div>
                </td>
                <td class="text-right" style="vertical-align: bottom; color: #71717a;">
                    Fecha Impresión: {{ now()->format('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>ID Corte/Turno:</strong> #{{ $cajaActiva->id }}</td>
            <td><strong>Turno:</strong> {{ $cajaActiva->turno }}</td>
            <td><strong>Empleado:</strong> {{ $cajaActiva->user->nombre ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Apertura:</strong> {{ $cajaActiva->created_at->format('d/m/Y') }}</td>
            <td><strong>Estado:</strong> <span class="badge">{{ strtoupper($cajaActiva->estado) }}</span></td>
            <td></td>
        </tr>
    </table>

    <div class="summary-box">
        <div class="summary-title">Balance Financiero del Turno</div>
        <table class="grid">
            <tr>
                <td>
                    <div style="color: #71717a; font-size: 11px;">Monto Inicial</div>
                    <div class="amount">${{ number_format($cajaActiva->monto_inicial, 2) }}</div>
                </td>
                <td>
                    <div style="color: #16a34a; font-size: 11px;">(+) Ventas Ingresadas</div>
                    <div class="amount" style="color: #16a34a;">+${{ number_format($totalVentas, 2) }}</div>
                </td>
                <td>
                    <div style="color: #dc2626; font-size: 11px;">(-) Gastos / Egresos</div>
                    <div class="amount" style="color: #dc2626;">-${{ number_format($totalGastos, 2) }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="padding-top: 15px; border-top: 1px solid #e4e4e7; margin-top: 10px;">
                    <div style="color: #09090b; font-size: 12px; font-weight: bold;">Saldo Estimado en Caja Actual:</div>
                    <div class="amount" style="font-size: 20px; color: #2563eb;">${{ number_format($saldoEstimado, 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">Historial de Ventas e Ingresos</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Hora</th>
                <th>Concepto</th>
                <th>Método de Pago</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historicoVentas as $venta)
                <tr>
                    <td>{{ $venta->created_at->format('h:i A') }}</td>
                    <td>{{ $venta->concepto }}</td>
                    <td>{{ $venta->metodo_pago }}</td>
                    <td class="text-right" style="color: #16a34a; font-weight: bold;">${{ number_format($venta->monto, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #a1a1aa;">No hay ventas registradas en este turno.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Historial de Gastos y Egresos</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Hora</th>
                <th>Concepto</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historicoGastos as $gasto)
                <tr>
                    <td>{{ $gasto->created_at->format('h:i A') }}</td>
                    <td>{{ $gasto->concepto }}</td>
                    <td class="text-right" style="color: #dc2626; font-weight: bold;">${{ number_format($gasto->monto, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #a1a1aa;">No hay egresos registrados en este turno.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>