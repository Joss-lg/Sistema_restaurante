<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Bajo Stock</title>
</head>
<body class="font-sans text-slate-900 bg-white dark:bg-slate-900 dark:text-slate-100 transition-colors duration-300">

    <div class="text-center mb-8 pb-3 border-b-2 border-blue-500 dark:border-blue-400">
        <div class="text-2xl font-bold text-slate-900 dark:text-white">OLLINTEM PRO</div>
        <div class="text-lg mt-1 text-slate-500 dark:text-slate-400">Reporte de Insumos con Bajo Stock</div>
        <p class="text-xs text-slate-500 dark:text-slate-500 mt-2">Generado el: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table class="w-full border-collapse mt-5">
        <thead>
            <tr>
                <th class="px-3 py-3 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider bg-slate-100 dark:bg-slate-800 border-b-2 border-slate-200 dark:border-slate-700 text-left">Insumo</th>
                <th class="px-3 py-3 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider bg-slate-100 dark:bg-slate-800 border-b-2 border-slate-200 dark:border-slate-700 text-left">Categoría</th>
                <th class="px-3 py-3 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider bg-slate-100 dark:bg-slate-800 border-b-2 border-slate-200 dark:border-slate-700 text-left">Stock Actual</th>
                <th class="px-3 py-3 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider bg-slate-100 dark:bg-slate-800 border-b-2 border-slate-200 dark:border-slate-700 text-left">Stock Mínimo</th>
                <th class="px-3 py-3 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider bg-slate-100 dark:bg-slate-800 border-b-2 border-slate-200 dark:border-slate-700 text-left">Estado</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
            @foreach($insumos as $insumo)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                <td class="px-3 py-3 text-sm text-slate-800 dark:text-slate-200">{{ $insumo->nombre }}</td>
                <td class="px-3 py-3 text-sm text-slate-600 dark:text-slate-400">{{ $insumo->categoria->nombre ?? 'S/C' }}</td>
                <td class="px-3 py-3 text-sm font-semibold text-rose-600 dark:text-rose-400">{{ $insumo->stock_actual }} {{ $insumo->unidad_medida }}</td>
                <td class="px-3 py-3 text-sm text-slate-600 dark:text-slate-400">{{ $insumo->stock_minimo }}</td>
                <td class="px-3 py-3 text-sm">
                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-rose-100 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-transparent dark:border-rose-500/20 text-[11px] font-semibold">REABASTECER</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="fixed bottom-0 left-0 w-full text-center text-[10px] text-slate-400 dark:text-slate-500 pb-2">
        Sistema de Gestión de Restaurante - Ollintem Pro
    </div>

</body>
</html>