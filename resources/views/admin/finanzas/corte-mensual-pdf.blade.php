<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corte Mensual</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #27272a;
            padding: 15px 25px;
        }

        .header { border-bottom: 3px solid #2563eb; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 18px; color: #18181b; }
        .header .periodo { font-size: 13px; color: #2563eb; font-weight: bold; text-transform: capitalize; margin-top: 2px; }
        .header .meta { font-size: 8px; color: #71717a; margin-top: 4px; }

        .badge-parcial {
            display: inline-block; background: #fef3c7; color: #92400e;
            border: 1px solid #fcd34d; border-radius: 4px;
            padding: 2px 8px; font-size: 8px; font-weight: bold; margin-top: 4px;
        }

        .kpis { width: 100%; border-collapse: separate; border-spacing: 5px; margin-bottom: 15px; }
        .kpis td {
            width: 20%; border: 1px solid #e4e4e7; border-radius: 6px;
            padding: 8px; text-align: center; vertical-align: top;
        }
        .kpis .label { font-size: 7px; text-transform: uppercase; letter-spacing: 1px; color: #71717a; font-weight: bold; }
        .kpis .valor { font-size: 13px; font-weight: bold; margin-top: 3px; }
        .verde  { color: #059669; }
        .rojo   { color: #dc2626; }
        .morado { color: #7c3aed; }
        .naranja{ color: #ea580c; }
        .gris   { color: #a1a1aa; }

        h2 {
            font-size: 11px; text-transform: uppercase; letter-spacing: 1px;
            color: #18181b; margin: 15px 0 6px 0;
            border-left: 4px solid #2563eb; padding-left: 6px;
        }

        table.datos { width: 100%; border-collapse: collapse; }
        table.datos th {
            background: #f4f4f5; color: #52525b; font-size: 8px;
            text-transform: uppercase; letter-spacing: 0.5px;
            padding: 5px 6px; border: 1px solid #e4e4e7; text-align: right;
        }
        table.datos th:first-child { text-align: left; }
        table.datos td {
            padding: 4px 6px; border: 1px solid #e4e4e7;
            text-align: right; font-size: 9px;
        }
        table.datos td:first-child { text-align: left; font-weight: bold; text-transform: capitalize; }
        table.datos tr.sin-mov td { color: #d4d4d8; }
        table.datos tr.totales td {
            background: #f4f4f5; font-weight: bold; font-size: 9.5px;
            border-top: 2px solid #a1a1aa;
        }

        table.cats { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        table.cats td { padding: 3px 6px; border-bottom: 1px solid #f4f4f5; font-size: 9px; }
        table.cats td:last-child { text-align: right; font-weight: bold; }

        .dos-columnas { width: 100%; border-collapse: separate; border-spacing: 8px 0; }
        .dos-columnas > tbody > tr > td { width: 50%; vertical-align: top; border: 1px solid #e4e4e7; border-radius: 6px; padding: 8px; }
        .dos-columnas h3 { font-size: 9px; text-transform: uppercase; color: #52525b; margin-bottom: 5px; }

        .detalle-dia { margin-bottom: 8px; page-break-inside: avoid; }
        .detalle-dia .titulo-dia {
            background: #eff6ff; color: #1d4ed8; font-weight: bold; font-size: 9px;
            padding: 4px 8px; border-radius: 4px; text-transform: capitalize;
        }
        table.movs { width: 100%; border-collapse: collapse; margin-top: 3px; }
        table.movs td { padding: 3px 6px; border-bottom: 1px solid #f4f4f5; font-size: 8.5px; }
        table.movs .hora { width: 8%; color: #71717a; }
        table.movs .tipo { width: 10%; font-weight: bold; }
        table.movs .cat  { width: 17%; color: #52525b; }
        table.movs .monto{ width: 13%; text-align: right; font-weight: bold; }
        table.movs .metodo{ width: 12%; text-align: right; color: #71717a; }

        .footer {
            position: fixed; bottom: 0; left: 25px; right: 25px;
            font-size: 7px; color: #a1a1aa; text-align: center;
            border-top: 1px solid #e4e4e7; padding-top: 4px;
        }
    </style>
</head>
<body>

    <div class="footer">
        Corte generado el {{ $generadoEn->translatedFormat('d \d\e F \d\e Y \a \l\a\s H:i') }} por {{ $generadoPor }} — Ollintem Pro
    </div>

    {{-- CABECERA --}}
    <div class="header">
        <h1>Corte Mensual de Finanzas</h1>
        <div class="periodo">{{ $inicioMes->translatedFormat('F Y') }}</div>
        <div class="meta">
            Período: {{ $inicioMes->format('d/m/Y') }} — {{ $fechaCorte->format('d/m/Y') }}
        </div>
        @if($esParcial)
            <span class="badge-parcial">CORTE PARCIAL — incluye hasta el día {{ $fechaCorte->format('d') }} ({{ $dias->count() }} días transcurridos)</span>
        @endif
    </div>

    {{-- KPIs --}}
    <table class="kpis">
        <tr>
            <td>
                <div class="label">Ingresos</div>
                <div class="valor verde">${{ number_format($totales->ingresos, 2) }}</div>
            </td>
            <td>
                <div class="label">Gastos</div>
                <div class="valor rojo">${{ number_format($totales->gastos, 2) }}</div>
            </td>
            <td>
                <div class="label">Nómina</div>
                <div class="valor morado">${{ number_format($totales->nomina, 2) }}</div>
            </td>
            <td>
                <div class="label">Total Egresos</div>
                <div class="valor naranja">${{ number_format($totales->egresos, 2) }}</div>
            </td>
            <td>
                <div class="label">Balance</div>
                <div class="valor {{ $totales->balance >= 0 ? 'verde' : 'rojo' }}">${{ number_format($totales->balance, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- DESGLOSE POR CATEGORÍA --}}
    <table class="dos-columnas">
        <tbody>
            <tr>
                <td>
                    <h3>Ingresos por Categoría</h3>
                    <table class="cats">
                        @forelse($categoriasIngresos as $categoria => $info)
                            <tr>
                                <td>{{ $categoria ?: 'Sin categoría' }} ({{ $info->cantidad }})</td>
                                <td class="verde">${{ number_format($info->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td class="gris">Sin ingresos en el período.</td><td></td></tr>
                        @endforelse
                    </table>
                </td>
                <td>
                    <h3>Egresos por Categoría</h3>
                    <table class="cats">
                        @forelse($categoriasEgresos as $categoria => $info)
                            <tr>
                                <td>{{ $categoria ?: 'Sin categoría' }} ({{ $info->cantidad }})</td>
                                <td class="rojo">${{ number_format($info->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td class="gris">Sin egresos en el período.</td><td></td></tr>
                        @endforelse
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- TABLA DIARIA --}}
    <h2>Desglose Diario</h2>
    <table class="datos">
        <thead>
            <tr>
                <th>Día</th>
                <th>Ingresos</th>
                <th>Gastos</th>
                <th>Nómina</th>
                <th>Egresos</th>
                <th>Balance</th>
                <th>Acumulado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dias as $dia)
                <tr class="{{ !$dia->tiene_movimientos ? 'sin-mov' : '' }}">
                    <td>{{ $dia->fecha->translatedFormat('D d') }}</td>
                    <td class="{{ $dia->ingresos > 0 ? 'verde' : '' }}">${{ number_format($dia->ingresos, 2) }}</td>
                    <td class="{{ $dia->gastos > 0 ? 'rojo' : '' }}">${{ number_format($dia->gastos, 2) }}</td>
                    <td class="{{ $dia->nomina > 0 ? 'morado' : '' }}">${{ number_format($dia->nomina, 2) }}</td>
                    <td class="{{ $dia->egresos > 0 ? 'naranja' : '' }}">${{ number_format($dia->egresos, 2) }}</td>
                    <td class="{{ $dia->balance > 0 ? 'verde' : ($dia->balance < 0 ? 'rojo' : '') }}">${{ number_format($dia->balance, 2) }}</td>
                    <td>${{ number_format($dia->balance_acumulado, 2) }}</td>
                </tr>
            @endforeach
            <tr class="totales">
                <td>TOTALES</td>
                <td class="verde">${{ number_format($totales->ingresos, 2) }}</td>
                <td class="rojo">${{ number_format($totales->gastos, 2) }}</td>
                <td class="morado">${{ number_format($totales->nomina, 2) }}</td>
                <td class="naranja">${{ number_format($totales->egresos, 2) }}</td>
                <td class="{{ $totales->balance >= 0 ? 'verde' : 'rojo' }}">${{ number_format($totales->balance, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- DETALLE DE MOVIMIENTOS POR DÍA --}}
    <h2 style="page-break-before: always;">Detalle de Movimientos</h2>
    @forelse($dias->where('tiene_movimientos', true) as $dia)
        <div class="detalle-dia">
            <div class="titulo-dia">
                {{ $dia->fecha->translatedFormat('l d \d\e F') }}
                — {{ $dia->movimientos->count() }} movimiento(s)
                — Balance del día: ${{ number_format($dia->balance, 2) }}
            </div>
            <table class="movs">
                @foreach($dia->movimientos as $mov)
                    <tr>
                        <td class="hora">{{ $mov->fecha->format('H:i') }}</td>
                        <td class="tipo {{ $mov->tipo === 'ingreso' ? 'verde' : 'rojo' }}">{{ $mov->tipo === 'ingreso' ? 'Ingreso' : 'Egreso' }}</td>
                        <td class="cat">{{ $mov->categoria }}</td>
                        <td>{{ $mov->concepto }}</td>
                        <td class="monto {{ $mov->tipo === 'ingreso' ? 'verde' : 'rojo' }}">
                            {{ $mov->tipo === 'ingreso' ? '+' : '-' }}${{ number_format($mov->monto, 2) }}
                        </td>
                        <td class="metodo">{{ $mov->metodo_pago }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @empty
        <p class="gris" style="font-size:9px;">No hubo movimientos en el período.</p>
    @endforelse

</body>
</html>