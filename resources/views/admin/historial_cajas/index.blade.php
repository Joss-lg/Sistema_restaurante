@extends('layouts.admin')

@section('title', 'Historial de Cajas | Ollintem Pro')

@section('content')
<div class="px-4 py-6 sm:p-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-6 relative z-10 font-sans min-h-screen bg-zinc-950 text-zinc-100 modo-crema:bg-white modo-crema:text-zinc-800 transition-colors duration-300">
    
    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-800 modo-crema:border-zinc-100 pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-zinc-100 modo-crema:text-zinc-800 flex items-center gap-2">
                🔄 Historial de Turnos y Cajas
            </h1>
            <p class="text-xs sm:text-sm font-medium text-zinc-400 modo-crema:text-zinc-500 mt-1">
                Auditoría financiera, saldos iniciales, cierres de turno y conciliaciones automáticas.
            </p>
        </div>
        <div class="text-xs font-bold text-zinc-500 modo-crema:text-zinc-400 bg-zinc-900/40 modo-crema:bg-zinc-50 px-4 py-2 rounded-2xl border border-zinc-800 modo-crema:border-zinc-100">
            Mostrando {{ $turnos->count() }} turnos registrados
        </div>
    </div>

    {{-- Tabla Responsiva Estilo Premium --}}
    <div class="w-full rounded-3xl border border-zinc-800 modo-crema:border-zinc-200/70 bg-zinc-900/30 modo-crema:bg-zinc-50/30 shadow-sm overflow-hidden">
        <div class="overflow-x-auto w-full [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-zinc-800 modo-crema:[&::-webkit-scrollbar-thumb]:bg-zinc-300 [&::-webkit-scrollbar-thumb]:rounded-full">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="border-b border-zinc-800 modo-crema:border-zinc-200 bg-zinc-950/60 modo-crema:bg-zinc-50/70 text-[11px] sm:text-xs font-bold uppercase tracking-widest text-zinc-400 modo-crema:text-zinc-500">
                        <th class="p-4 sm:p-5">Fecha</th>
                        <th class="p-4 sm:p-5">Turno (Estimado)</th>
                        <th class="p-4 sm:p-5">Empleado</th>
                        <th class="p-4 sm:p-5 text-center">Estado</th>
                        <th class="p-4 sm:p-5 text-right">Saldo Inicial</th>
                        <th class="p-4 sm:p-5 text-right">Saldo Final</th>
                        <th class="p-4 sm:p-5 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 modo-crema:divide-zinc-100 text-sm font-medium text-zinc-300 modo-crema:text-zinc-700">
                    @forelse($turnos as $turno)
                        <tr class="hover:bg-zinc-900/40 modo-crema:hover:bg-zinc-50/50 transition-colors duration-150">
                            {{-- Fecha --}}
                            <td class="p-4 sm:p-5 font-bold text-zinc-200 modo-crema:text-zinc-900">
                                {{ $turno->created_at->format('d/m/Y') }}
                            </td>
                            {{-- Turno --}}
                            <td class="p-4 sm:p-5">
                                <div class="flex flex-col">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold w-fit
                                        {{ $turno->turno === 'Matutino' ? 'bg-blue-500/10 text-blue-400 modo-crema:bg-blue-50 modo-crema:text-blue-600' : 'bg-amber-500/10 text-amber-400 modo-crema:bg-amber-50 modo-crema:text-amber-600' }}">
                                        {{ $turno->turno === 'Matutino' ? '☀️' : '🌙' }} {{ $turno->turno }}
                                    </span>
                                    <span class="text-[10px] text-zinc-500 modo-crema:text-zinc-400 mt-1 pl-1">
                                        {{ $turno->created_at->format('h:i A') }}
                                    </span>
                                </div>
                            </td>
                            {{-- Empleado --}}
                            <td class="p-4 sm:p-5">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-cyan-500/10 text-cyan-400 modo-crema:bg-cyan-100 modo-crema:text-cyan-700 flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                        {{ substr($turno->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-black text-zinc-200 modo-crema:text-zinc-900 text-xs">{{ $turno->user->name ?? 'N/A' }}</span>
                                        <span class="text-[10px] text-zinc-500 modo-crema:text-zinc-400 font-semibold">ID: {{ $turno->user_id }}</span>
                                    </div>
                                </div>
                            </td>
                            {{-- Estado --}}
                            <td class="p-4 sm:p-5 text-center">
                                @if($turno->estado === 'abierta')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 modo-crema:bg-emerald-50 modo-crema:text-emerald-600 animate-pulse border border-emerald-500/20 modo-crema:border-emerald-200/50">
                                        ● Activa (Abierta)
                                    </span>
                                @else
                                    <div class="flex flex-col items-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-zinc-800 text-zinc-400 modo-crema:bg-zinc-100 modo-crema:text-zinc-600 border border-zinc-700/60 modo-crema:border-zinc-200">
                                            Cerrada
                                        </span>
                                        <span class="text-[10px] text-zinc-500 modo-crema:text-zinc-400 mt-1 font-semibold">
                                            {{ $turno->updated_at->format('h:i A') }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            {{-- Saldo Inicial --}}
                            <td class="p-4 sm:p-5 text-right font-semibold text-zinc-400 modo-crema:text-zinc-500">
                                ${{ number_format($turno->monto_inicial, 2) }}
                            </td>
                            {{-- Saldo Final --}}
                            <td class="p-4 sm:p-5 text-right">
                                @if($turno->estado === 'abierta')
                                    <span class="text-emerald-400 modo-crema:text-emerald-500 font-black text-base">$0.00</span>
                                @else
                                    <span class="text-emerald-400 modo-crema:text-emerald-600 font-black text-base">
                                        ${{ number_format($turno->monto_final_real ?? 0, 2) }}
                                    </span>
                                @endif
                            </td>
                            {{-- Acciones --}}
                            <td class="p-4 sm:p-5 text-center">
                                <a href="{{ route('admin.historial.show', $turno->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold bg-zinc-900 hover:bg-zinc-800 text-zinc-300 border border-zinc-800 modo-crema:bg-white modo-crema:hover:bg-zinc-50 modo-crema:text-zinc-700 modo-crema:border-zinc-200 shadow-sm rounded-xl transition hover:-translate-y-0.5">
                                    👁️ Detalles
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-zinc-500 modo-crema:text-zinc-400 font-semibold">
                                No se encontraron registros de turnos en el historial.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Paginación Nativa de Laravel --}}
        @if($turnos->hasPages())
            <div class="px-5 py-4 border-t border-zinc-800 modo-crema:border-zinc-100 bg-zinc-950/30 modo-crema:bg-zinc-50/50">
                {{ $turnos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection