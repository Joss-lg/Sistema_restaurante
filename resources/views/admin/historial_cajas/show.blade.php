@extends('layouts.admin')

@section('title', 'Detalle de Turno | OllinRest')

@section('content')
<div class="px-4 py-6 sm:p-6 lg:p-8 w-full max-w-[1200px] mx-auto space-y-6 relative z-10 font-sans min-h-screen bg-white dark:bg-[#15171c] transition-colors duration-300">
    
    {{-- Botón de Regresar y Encabezado --}}
    <div class="flex flex-col gap-3 border-b border-gray-100 dark:border-slate-800 pb-5">
        <div>
            <a href="{{ route('admin.historial.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition-colors mb-2">
                ← Volver al historial
            </a>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900 dark:text-slate-100 flex items-center gap-2">
                    📊 Auditoría de Caja #{{ $turno->id }}
                </h1>
                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-slate-400 mt-1">
                    Detalles específicos del flujo financiero capturado en este turno.
                </p>
            </div>
            
            {{-- Badge de Estado --}}
            <div>
                @if($turno->estado === 'abierta')
                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 animate-pulse border border-emerald-200/50 dark:border-emerald-800/30">
                        ● Caja Activa
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-400 border border-gray-200 dark:border-slate-700/60">
                        🔒 Turno Cerrado
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Grid de Información General --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {{-- Tarjeta 1: Metadatos del Turno --}}
        <div class="p-6 rounded-3xl border border-gray-200 dark:border-slate-800 bg-slate-50/50 dark:bg-[#1e2026]/40 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500">Datos de Apertura</h3>
            
            <div class="space-y-3">
                <div>
                    <span class="block text-[11px] text-gray-400 dark:text-slate-500 uppercase font-bold">Empleado Responsable</span>
                    <span class="text-sm font-bold text-gray-800 dark:text-slate-200">{{ $turno->user->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="block text-[11px] text-gray-400 dark:text-slate-500 uppercase font-bold">TurnoAsignado</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-blue-50 dark:bg-blue-900/10 text-blue-600 dark:text-blue-400 mt-0.5">
                        {{ $turno->turno === 'Matutino' ? '☀️' : '🌙' }} {{ $turno->turno }}
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] text-gray-400 dark:text-slate-500 uppercase font-bold">Fecha y Hora Apertura</span>
                    <span class="text-xs font-semibold text-gray-700 dark:text-slate-300">{{ $turno->created_at->format('d/m/Y - h:i A') }}</span>
                </div>
            </div>
        </div>

        {{-- Tarjeta 2: Balance Financiero --}}
        <div class="p-6 rounded-3xl border border-gray-200 dark:border-slate-800 bg-slate-50/50 dark:bg-[#1e2026]/40 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500">Conciliación de Saldos</h3>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center border-b border-gray-100 dark:border-slate-800 pb-1.5">
                    <span class="text-xs text-gray-500 dark:text-slate-400 font-medium">(+) Fondo Inicial:</span>
                    <span class="text-xs font-bold text-gray-800 dark:text-slate-200">${{ number_format($turno->monto_inicial, 2) }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-gray-100 dark:border-slate-800 pb-1.5">
                    <span class="text-xs text-gray-500 dark:text-slate-400 font-medium">(+) Efectivo Real Entregado:</span>
                    <span class="text-xs font-bold text-gray-800 dark:text-slate-200">${{ number_format($turno->monto_final_real ?? 0, 2) }}</span>
                </div>
                
                {{-- Lógica para calcular desfases si el turno está cerrado --}}
                @if($turno->estado === 'cerrada')
                    @php
                        // Ajusta estos campos según tus columnas reales del sistema de ventas
                        $montoEsperado = $turno->monto_inicial + ($turno->monto_final_esperado ?? 0);
                        $diferencia = ($turno->monto_final_real ?? 0) - $montoEsperado;
                    @php
                    
                    <div class="flex justify-between items-center pt-1">
                        <span class="text-xs font-bold text-gray-700 dark:text-slate-300">Resultado:</span>
                        @if($diferencia == 0)
                            <span class="text-xs font-bold text-emerald-500">✓ Caja Cuadrada</span>
                        @elseif($diferencia < 0)
                            <span class="text-xs font-bold text-red-500">⚠ Faltante: ${{ number_format(abs($diferencia), 2) }}</span>
                        @else
                            <span class="text-xs font-bold text-amber-500">⚠ Sobrante: ${{ number_format($diferencia, 2) }}</span>
                        @endif
                    </div>
                @else
                    <div class="text-center pt-2 text-xs font-semibold text-gray-400 dark:text-slate-500 italic">
                        El balance final se calculará al cerrar el turno.
                    </div>
                @endif
            </div>
        </div>

        {{-- Tarjeta 3: Notas de Auditoría --}}
        <div class="p-6 rounded-3xl border border-gray-200 dark:border-slate-800 bg-slate-50/50 dark:bg-[#1e2026]/40 flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-3">Notas / Observaciones</h3>
                <p class="text-xs font-medium text-gray-600 dark:text-slate-400 leading-relaxed bg-white dark:bg-[#15171c]/50 p-3 rounded-2xl border border-gray-100 dark:border-slate-800 min-h-[75px]">
                    {{ $turno->observaciones ?? 'Sin comentarios ni incidentes reportados en este turno.' }}
                </p>
            </div>
            @if($turno->estado === 'cerrada')
                <span class="text-[10px] text-gray-400 dark:text-slate-500 font-semibold block text-right mt-2">
                    Cierre procesado a las: {{ $turno->updated_at->format('h:i A') }}
                </span>
            @endif
        </div>
    </div>

    {{-- Sección de Ventas Realizadas (Opcional, si tienes relación con ventas) --}}
    <div class="w-full rounded-3xl border border-gray-200/70 dark:border-slate-700/60 bg-slate-50/30 dark:bg-[#1e2026]/30 shadow-sm p-6 space-y-4">
        <h2 class="text-base font-black text-gray-900 dark:text-slate-100">
            🛒 Resumen Operativo del Turno
        </h2>
        <p class="text-xs text-gray-400 dark:text-slate-500">
            Aquí puedes inyectar mediante una relación la lista de órdenes o transacciones cobradas específicamente entre las horas de apertura y cierre de este registro.
        </p>
    </div>
</div>
@endsection