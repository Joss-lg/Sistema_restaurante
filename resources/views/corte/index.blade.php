@extends('layouts.admin')

@section('title', 'Productos Vendidos | Ollintem Pro')

@section('content')
<div class="p-4 sm:p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-6 sm:space-y-8 flex-1 flex flex-col">

    {{-- Encabezado y Filtros --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end gap-6 mb-2">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-[var(--text-color)] dark:text-zinc-100 tracking-tight">Reporte de Productos Vendidos</h1>
            <p class="text-xs sm:text-sm text-[var(--text-muted)] dark:text-zinc-500 mt-1">
                Mostrando resultados del <span class="font-bold text-[var(--text-color)]">{{ $fechaInicio }}</span> al <span class="font-bold text-[var(--text-color)]">{{ $fechaFin }}</span>
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
            
            {{-- Formulario de Filtro por Fechas --}}
            <form method="GET" action="{{ route('corte.index') }}" class="flex w-full sm:w-auto items-center gap-2">
                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" class="w-full sm:w-36 h-12 bg-black/5 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 rounded-2xl px-4 text-xs font-bold text-[var(--text-color)] dark:text-zinc-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                
                <span class="text-[var(--text-muted)] font-bold">-</span>
                
                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" class="w-full sm:w-36 h-12 bg-black/5 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 rounded-2xl px-4 text-xs font-bold text-[var(--text-color)] dark:text-zinc-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                
                <button type="submit" title="Filtrar Fechas" class="bg-zinc-800 hover:bg-zinc-900 dark:bg-zinc-100 dark:hover:bg-white dark:text-zinc-900 text-white w-12 h-12 rounded-2xl flex-shrink-0 flex items-center justify-center transition-all shadow-lg active:scale-95">
                    <i class="fas fa-calendar-alt"></i>
                </button>
            </form>

            {{-- Botón PDF actualizado para mandar las dos fechas --}}
            <a href="{{ route('corte.pdf', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" 
               class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-7 h-12 rounded-2xl text-xs font-black uppercase tracking-[0.15em] transition-all shadow-lg shadow-blue-600/20 hover:shadow-blue-600/30 active:scale-95 outline-none flex items-center justify-center gap-2">
                <i class="fas fa-file-pdf"></i> REPORTE PDF
            </a>
        </div>
    </div>

    {{-- Contenedor principal estilo Ollintem Pro --}}
    <div class="bg-[var(--card-color)] border border-zinc-200/60 dark:border-zinc-800/60 rounded-2xl sm:rounded-[2.5rem] shadow-sm p-4 sm:p-6 lg:p-8 w-full">
        
        @forelse($ventasPorArea as $area => $productos)
            <div class="mb-8 last:mb-0">
                <div class="mb-4 flex items-center gap-3 border-b border-zinc-100 dark:border-zinc-900 pb-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/15 flex items-center justify-center text-emerald-600 dark:text-emerald-400 border border-emerald-500/5 dark:border-emerald-500/10">
                        <i class="fas {{ strtolower($area) == 'barra' ? 'fa-glass-martini-alt' : 'fa-utensils' }} text-sm"></i>
                    </div>
                    <h3 class="text-lg font-black text-[var(--text-color)] dark:text-zinc-100 uppercase tracking-tight">
                        Área: {{ $area }}
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-zinc-100 dark:border-zinc-900">
                                <th class="pb-4 px-4 text-[10px] font-black text-[var(--text-muted)] dark:text-zinc-500 uppercase tracking-[0.2em]">Producto / Platillo</th>
                                <th class="pb-4 px-4 text-[10px] font-black text-[var(--text-muted)] dark:text-zinc-500 uppercase tracking-[0.2em] text-center">Cantidad Vendida</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos as $item)
                                <tr class="border-b border-zinc-100/50 dark:border-zinc-900/30 hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="py-4 px-4 text-sm font-black text-[var(--text-color)] dark:text-zinc-200">{{ $item->producto }}</td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="px-4 py-1.5 bg-zinc-100 dark:bg-white/5 border border-zinc-200/40 dark:border-zinc-800/40 rounded-xl text-xs font-black text-[var(--text-color)] dark:text-zinc-100">
                                            {{ $item->total_vendido }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="py-12 text-center text-[var(--text-muted)] dark:text-zinc-500 font-bold text-sm">
                No hay productos vendidos registrados en las fechas seleccionadas.
            </div>
        @endforelse

    </div>
</div>
@endsection