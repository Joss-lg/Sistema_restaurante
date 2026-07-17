@extends('layouts.admin')

@section('title', 'Corte Mensual | Finanzas | Ollintem Pro')

@section('content')
<div class="p-3 sm:p-6 lg:p-8 xl:p-12 max-w-[1800px] mx-auto w-full space-y-5 sm:space-y-8 flex-1 flex flex-col transition-all duration-300 relative z-10">

    {{-- CABECERA --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end gap-3 sm:gap-6 mb-2">
        <div class="space-y-1 w-full sm:w-auto">
            <h1 class="text-xl sm:text-3xl md:text-4xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight">Corte Mensual</h1>
            <p class="text-xs sm:text-sm font-medium text-zinc-500 dark:text-zinc-400">
                Desglose diario de ingresos y egresos |
                <span class="text-zinc-700 dark:text-zinc-300 font-bold capitalize">{{ $inicioMes->translatedFormat('F Y') }}</span>
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-3 w-full xl:w-auto">
            <a href="{{ route('admin.finanzas.index') }}"
                class="w-full sm:w-auto bg-white hover:bg-zinc-50 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700 px-5 py-3.5 sm:py-2.5 rounded-xl text-sm font-bold transition-all outline-none flex items-center justify-center gap-2 shadow-sm">
                <i class="fas fa-arrow-left"></i> Volver a Finanzas
            </a>

            <a href="{{ route('admin.finanzas.corte.exportar', ['mes' => $mes, 'año' => $año]) }}"
                class="w-full sm:w-auto bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-600/10 dark:hover:bg-emerald-600/20 text-emerald-700 dark:text-emerald-500 border border-emerald-200 dark:border-emerald-500/20 px-5 py-3.5 sm:py-2.5 rounded-xl text-sm font-bold transition-all outline-none flex items-center justify-center gap-2 shadow-sm">
                <i class="fas fa-file-csv"></i> Exportar Corte CSV
            </a>

            <a href="{{ route('admin.finanzas.corte.pdf', ['mes' => $mes, 'año' => $año]) }}"
                class="w-full sm:w-auto bg-rose-50 hover:bg-rose-100 dark:bg-rose-600/10 dark:hover:bg-rose-600/20 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 px-5 py-3.5 sm:py-2.5 rounded-xl text-sm font-bold transition-all outline-none flex items-center justify-center gap-2 shadow-sm">
                <i class="fas fa-file-pdf"></i> Descargar PDF
            </a>
        </div>
    </div>

    {{-- FILTRO DE MES Y AÑO --}}
    <form method="GET" action="{{ route('admin.finanzas.corte.mensual') }}"
        class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-4 flex flex-col sm:flex-row items-stretch sm:items-end gap-3 shadow-sm">
        <div class="flex-1 sm:max-w-[220px]">
            <label class="block text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase mb-1">Mes</label>
            <select name="mes" class="w-full px-3 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm text-zinc-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" @selected($m == $mes)>
                        {{ ucfirst(\Carbon\Carbon::create(null, $m, 1)->translatedFormat('F')) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 sm:max-w-[160px]">
            <label class="block text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase mb-1">Año</label>
            <select name="año" class="w-full px-3 py-2.5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm text-zinc-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach($añosDisponibles as $a)
                    <option value="{{ $a }}" @selected($a == $año)>{{ $a }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit"
            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold transition shadow-sm flex items-center justify-center gap-2">
            <i class="fas fa-filter"></i> Filtrar
        </button>
    </form>

    {{-- TARJETAS DE TOTALES DEL MES --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase">Ingresos</p>
            <p class="text-lg sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">${{ number_format($totales->ingresos, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase">Gastos</p>
            <p class="text-lg sm:text-2xl font-black text-red-500 dark:text-red-400 mt-1">${{ number_format($totales->gastos, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase">Nómina</p>
            <p class="text-lg sm:text-2xl font-black text-purple-600 dark:text-purple-400 mt-1">${{ number_format($totales->nomina, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase">Total Egresos</p>
            <p class="text-lg sm:text-2xl font-black text-orange-500 dark:text-orange-400 mt-1">${{ number_format($totales->egresos, 2) }}</p>
        </div>
        <div class="col-span-2 lg:col-span-1 bg-white dark:bg-zinc-800 border-2 {{ $totales->balance >= 0 ? 'border-emerald-300 dark:border-emerald-600/40' : 'border-red-300 dark:border-red-600/40' }} rounded-xl p-4 shadow-sm">
            <p class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase">Balance del Mes</p>
            <p class="text-lg sm:text-2xl font-black {{ $totales->balance >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400' }} mt-1">
                ${{ number_format($totales->balance, 2) }}
            </p>
        </div>
    </div>

    {{-- DESGLOSE POR CATEGORÍA DEL MES --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-4 shadow-sm">
            <h3 class="text-sm font-black text-zinc-800 dark:text-zinc-100 uppercase mb-3">Ingresos por Categoría</h3>
            @forelse($categoriasIngresos as $categoria => $info)
                <div class="flex justify-between items-center py-1.5 border-b border-zinc-100 dark:border-zinc-700/50 last:border-0 text-sm">
                    <span class="text-zinc-600 dark:text-zinc-300">{{ $categoria ?: 'Sin categoría' }} <span class="text-xs text-zinc-400">({{ $info->cantidad }})</span></span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($info->total, 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-zinc-400">Sin ingresos este mes.</p>
            @endforelse
        </div>

        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-4 shadow-sm">
            <h3 class="text-sm font-black text-zinc-800 dark:text-zinc-100 uppercase mb-3">Egresos por Categoría</h3>
            @forelse($categoriasEgresos as $categoria => $info)
                <div class="flex justify-between items-center py-1.5 border-b border-zinc-100 dark:border-zinc-700/50 last:border-0 text-sm">
                    <span class="text-zinc-600 dark:text-zinc-300">{{ $categoria ?: 'Sin categoría' }} <span class="text-xs text-zinc-400">({{ $info->cantidad }})</span></span>
                    <span class="font-bold text-red-500 dark:text-red-400">${{ number_format($info->total, 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-zinc-400">Sin egresos este mes.</p>
            @endforelse
        </div>
    </div>

    {{-- TABLA DÍA POR DÍA --}}
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-900/60 text-zinc-500 dark:text-zinc-400 text-xs uppercase">
                        <th class="px-4 py-3 text-left font-bold">Día</th>
                        <th class="px-4 py-3 text-right font-bold">Ingresos</th>
                        <th class="px-4 py-3 text-right font-bold">Gastos</th>
                        <th class="px-4 py-3 text-right font-bold">Nómina</th>
                        <th class="px-4 py-3 text-right font-bold">Egresos</th>
                        <th class="px-4 py-3 text-right font-bold">Balance</th>
                        <th class="px-4 py-3 text-right font-bold">Acumulado</th>
                        <th class="px-4 py-3 text-center font-bold">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dias as $dia)
                        <tr class="border-t border-zinc-100 dark:border-zinc-700/50 {{ !$dia->tiene_movimientos ? 'opacity-40' : 'hover:bg-zinc-50 dark:hover:bg-zinc-700/30' }} transition-colors">
                            <td class="px-4 py-2.5 font-semibold text-zinc-800 dark:text-zinc-100 capitalize whitespace-nowrap">
                                {{ $dia->fecha->translatedFormat('D d') }}
                            </td>
                            <td class="px-4 py-2.5 text-right {{ $dia->ingresos > 0 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-zinc-400' }}">
                                ${{ number_format($dia->ingresos, 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-right {{ $dia->gastos > 0 ? 'text-red-500 dark:text-red-400 font-semibold' : 'text-zinc-400' }}">
                                ${{ number_format($dia->gastos, 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-right {{ $dia->nomina > 0 ? 'text-purple-600 dark:text-purple-400 font-semibold' : 'text-zinc-400' }}">
                                ${{ number_format($dia->nomina, 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-right {{ $dia->egresos > 0 ? 'text-orange-500 dark:text-orange-400 font-semibold' : 'text-zinc-400' }}">
                                ${{ number_format($dia->egresos, 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-bold {{ $dia->balance > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($dia->balance < 0 ? 'text-red-500 dark:text-red-400' : 'text-zinc-400') }}">
                                ${{ number_format($dia->balance, 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-right text-zinc-500 dark:text-zinc-400">
                                ${{ number_format($dia->balance_acumulado, 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @if($dia->tiene_movimientos)
                                    <button type="button"
                                        onclick="document.getElementById('detalle-{{ $dia->fecha->format('Ymd') }}').classList.toggle('hidden')"
                                        class="text-blue-600 dark:text-blue-400 hover:underline text-xs font-bold">
                                        Ver ({{ $dia->movimientos->count() }})
                                    </button>
                                @else
                                    <span class="text-zinc-300 dark:text-zinc-600 text-xs">—</span>
                                @endif
                            </td>
                        </tr>

                        @if($dia->tiene_movimientos)
                            <tr id="detalle-{{ $dia->fecha->format('Ymd') }}" class="hidden bg-zinc-50 dark:bg-zinc-900/40">
                                <td colspan="8" class="px-4 py-3">
                                    <div class="space-y-1.5">
                                        @foreach($dia->movimientos as $mov)
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-xs bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <span class="shrink-0 font-mono text-zinc-400">{{ $mov->fecha->format('H:i') }}</span>
                                                    <span class="shrink-0 px-2 py-0.5 rounded-full font-bold {{ $mov->tipo === 'ingreso' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' }}">
                                                        {{ $mov->getTipoLegible() }}
                                                    </span>
                                                    <span class="shrink-0 text-zinc-500 dark:text-zinc-400">{{ $mov->categoria }}</span>
                                                    <span class="truncate text-zinc-700 dark:text-zinc-200">{{ $mov->concepto }}</span>
                                                </div>
                                                <div class="flex items-center gap-3 shrink-0">
                                                    <span class="text-zinc-400">{{ $mov->metodo_pago }}</span>
                                                    <span class="font-bold {{ $mov->tipo === 'ingreso' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400' }}">
                                                        {{ $mov->getSimboloTipo() }}${{ number_format($mov->monto, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-zinc-100 dark:bg-zinc-900 border-t-2 border-zinc-300 dark:border-zinc-600 font-black text-zinc-800 dark:text-zinc-100">
                        <td class="px-4 py-3">TOTALES</td>
                        <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400">${{ number_format($totales->ingresos, 2) }}</td>
                        <td class="px-4 py-3 text-right text-red-500 dark:text-red-400">${{ number_format($totales->gastos, 2) }}</td>
                        <td class="px-4 py-3 text-right text-purple-600 dark:text-purple-400">${{ number_format($totales->nomina, 2) }}</td>
                        <td class="px-4 py-3 text-right text-orange-500 dark:text-orange-400">${{ number_format($totales->egresos, 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $totales->balance >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400' }}">${{ number_format($totales->balance, 2) }}</td>
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection