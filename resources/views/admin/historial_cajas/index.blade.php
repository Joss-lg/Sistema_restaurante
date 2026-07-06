@extends('layouts.admin')

@section('title', 'Historial de Cajas | Ollintem Pro')

@section('content')
<div class="px-4 py-8 sm:p-8 lg:p-10 w-full max-w-[1600px] mx-auto space-y-8 relative z-10 font-sans min-h-screen bg-zinc-50 dark:bg-[#09090b] transition-colors duration-500">
    
    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 pb-6 border-b border-zinc-200 dark:border-zinc-800/80">
        <div class="space-y-1">
            <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white flex items-center gap-3">
                <span class="p-2 bg-blue-600/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </span>
                Historial de Turnos y Cajas
            </h1>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                Auditoría financiera, saldos iniciales, cierres de turno y conciliaciones automáticas.
            </p>
        </div>
        <div class="inline-flex items-center gap-2 text-xs font-semibold text-zinc-600 dark:text-zinc-300 bg-white dark:bg-zinc-900/50 px-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800/80 shadow-sm">
            <span class="flex h-2 w-2 rounded-full bg-blue-500"></span>
            Mostrando {{ $turnos->count() }} turnos
        </div>
    </div>

    {{-- Tabla Responsiva Estilo Premium --}}
    <div class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-800/80 bg-white dark:bg-[#0c0c0e] shadow-sm overflow-hidden transition-all">
        <div class="overflow-x-auto w-full [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-zinc-300 dark:[&::-webkit-scrollbar-thumb]:bg-zinc-700 [&::-webkit-scrollbar-thumb]:rounded-full">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-800/80 bg-zinc-50/50 dark:bg-zinc-900/30 text-[10px] font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-500">
                        <th class="px-6 py-4">Fecha</th>
                        <th class="px-6 py-4">Turno</th>
                        <th class="px-6 py-4">Empleado</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-right">Saldo Inicial</th>
                        <th class="px-6 py-4 text-right">Saldo Final</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/50 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    @forelse($turnos as $turno)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/40 transition-colors duration-200 group">
                            {{-- Fecha --}}
                            <td class="px-6 py-5 font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $turno->created_at->format('d/m/Y') }}
                            </td>
                            
                            {{-- Turno --}}
                            <td class="px-6 py-5">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold w-fit border
                                        {{ $turno->turno === 'Matutino' ? 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20' }}">
                                        {{ $turno->turno === 'Matutino' ? '☀️' : '🌙' }} {{ $turno->turno }}
                                    </span>
                                    <span class="text-[10px] text-zinc-500 dark:text-zinc-500 font-medium tracking-wide">
                                        {{ $turno->created_at->format('h:i A') }}
                                    </span>
                                </div>
                            </td>
                            
                            {{-- Empleado --}}
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center font-bold text-sm uppercase shadow-inner ring-1 ring-indigo-500/20 dark:ring-indigo-500/30">
                                        {{ substr($turno->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $turno->user->name ?? 'N/A' }}</span>
                                        <span class="text-[10px] text-zinc-500 dark:text-zinc-500 font-semibold mt-0.5 tracking-wider">ID: {{ $turno->user_id }}</span>
                                    </div>
                                </div>
                            </td>
                            
                            {{-- Estado --}}
                            <td class="px-6 py-5 text-center">
                                @if($turno->estado === 'abierta')
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                        <span class="relative flex h-2 w-2">
                                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                        Activa
                                    </div>
                                @else
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[11px] font-bold bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                            Cerrada
                                        </span>
                                        <span class="text-[10px] text-zinc-500 dark:text-zinc-500 font-medium">
                                            {{ $turno->updated_at->format('h:i A') }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            
                            {{-- Saldo Inicial --}}
                            <td class="px-6 py-5 text-right font-semibold text-zinc-600 dark:text-zinc-400">
                                ${{ number_format($turno->monto_inicial, 2) }}
                            </td>
                            
                            {{-- Saldo Final --}}
                            <td class="px-6 py-5 text-right">
                                @if($turno->estado === 'abierta')
                                    <span class="text-zinc-400 dark:text-zinc-600 font-bold tracking-tight">--</span>
                                @else
                                    <span class="text-zinc-900 dark:text-white font-black tracking-tight text-base">
                                        ${{ number_format($turno->monto_final_real ?? 0, 2) }}
                                    </span>
                                @endif
                            </td>
                            
                            {{-- Acciones --}}
                            <td class="px-6 py-5 text-center">
                                <a href="{{ route('historial.show', $turno->id) }} class="inline-flex
                                   class="inline-flex items-center justify-center gap-2 px-4 py-2 text-[11px] font-bold rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-200 shadow-sm transition-all hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Ver Detalles
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-20">
                                <div class="flex flex-col items-center justify-center gap-4">
                                    <div class="h-14 w-14 rounded-2xl bg-zinc-100 dark:bg-zinc-800/50 flex items-center justify-center text-zinc-400 dark:text-zinc-500">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    </div>
                                    <div class="space-y-1">
                                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Sin registros</h3>
                                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-500">No se encontraron turnos en el historial.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Paginación Nativa de Laravel --}}
        @if($turnos->hasPages())
            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800/80 bg-zinc-50/50 dark:bg-zinc-900/30">
                {{ $turnos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection