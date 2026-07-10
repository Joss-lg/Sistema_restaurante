@extends('layouts.admin')

@section('content')
{{-- Cambiado a w-full y eliminado el max-w para ocupar todo el espacio disponible --}}
<div class="p-3 sm:p-6 w-full space-y-4 sm:space-y-6">
    
    {{-- Encabezado y Alertas --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 mb-2 w-full">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-wide text-[var(--text-color)]">Gestión de Flujo de Caja</h1>
            <p class="text-[10px] sm:text-xs text-[var(--text-muted)] uppercase tracking-widest font-bold mt-1">Monitoreo de movimientos del turno</p>
        </div>
        @if(session('success'))
            <div class="w-full sm:w-auto flex items-center p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-500 text-xs sm:text-sm animate-fade-in shadow-lg shadow-emerald-500/5">
                <i class="fas fa-check-circle mr-2"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif
    </div>

    {{-- Grid Principal: Reajustado a 12 columnas distribuidas para mayor amplitud --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-start w-full">
        
        {{-- COLUMNA IZQUIERDA: Resumen de Turno (Se mantiene firme en 3 columnas para no estirarse de más) --}}
        <div class="lg:col-span-3 bg-[var(--card-color)] border border-[var(--border-color)] rounded-2xl shadow-xl p-4 sm:p-5 flex flex-col justify-between relative overflow-hidden group lg:min-h-[500px]">
            <div class="absolute top-0 left-0 w-full h-[4px] bg-gradient-to-r from-blue-500 to-indigo-600"></div>
            
            <div>
                <h3 class="text-xs sm:text-sm font-black text-[var(--text-muted)] uppercase tracking-wider mb-4 sm:mb-5 flex items-center">
                    <i class="fas fa-cash-register text-blue-500 mr-2"></i> Resumen de Turno
                </h3>

                {{-- Datos Informativos --}}
                <div class="space-y-2.5 sm:space-y-3 mb-5 sm:mb-6 text-xs sm:text-sm">
                    <div class="flex justify-between items-center border-b border-[var(--border-color)] pb-2">
                        <span class="text-[var(--text-muted)] font-medium">ID Caja:</span>
                        <span class="font-bold text-[var(--text-color)]">#{{ $cajaActiva->id }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-[var(--border-color)] pb-2">
                        <span class="text-[var(--text-muted)] font-medium">Cajero:</span>
                        <span class="font-bold text-[var(--text-color)] text-right">{{ $cajaActiva->user->name ?? 'Admin' }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-[var(--border-color)] pb-2">
                        <span class="text-[var(--text-muted)] font-medium">Turno:</span>
                        <span class="px-2 sm:px-2.5 py-0.5 rounded-md text-[10px] sm:text-xs font-bold bg-blue-500/10 border border-blue-500/20 text-blue-500 uppercase tracking-wider">
                            {{ $cajaActiva->turno ?? 'Matutino' }}
                        </span>
                    </div>
                </div>

                {{-- Bloque de Contabilidad Flujo Matemático --}}
                <div class="bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl p-3 sm:p-4 space-y-2.5 sm:space-y-3 shadow-inner">
                    <div class="flex justify-between items-center text-xs sm:text-sm">
                        <span class="text-[var(--text-muted)] font-medium">Saldo Inicial:</span>
                        <span class="font-bold text-[var(--text-color)]">${{ number_format($cajaActiva->monto_inicial, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs sm:text-sm">
                        <span class="text-[var(--text-muted)] font-medium">+ Ventas (Efectivo):</span>
                        <span class="font-bold text-emerald-500">+${{ number_format($totalVentas, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs sm:text-sm">
                        <span class="text-[var(--text-muted)] font-medium">- Salidas / Gastos:</span>
                        <span class="font-bold text-rose-500">-${{ number_format($totalGastos, 2) }}</span>
                    </div>
                    
                    <div class="border-t border-[var(--border-color)] pt-3 text-center">
                        <span class="text-[9px] sm:text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest block mb-1">Saldo Actual Estimado</span>
                        <h2 class="text-2xl sm:text-3xl font-black text-blue-500 tracking-tight">${{ number_format($saldoEstimado, 2) }}</h2>
                    </div>
                </div>
            </div>

            {{-- Acciones del Turno --}}
            <div class="mt-6 sm:mt-8 space-y-3">
                {{-- Botón de Exportar Reporte (Convertido a Enlace para Generar PDF) --}}
                <a href="{{ route('admin.caja.reporte.pdf', $cajaActiva->id) }}" target="_blank"
                    class="w-full flex items-center justify-center bg-[var(--input-bg)] border border-[var(--border-color)] hover:bg-blue-500/10 hover:border-blue-500/40 text-[var(--text-color)] font-bold text-[11px] sm:text-xs tracking-widest uppercase py-3 px-4 rounded-xl transition-all duration-300 shadow-md group">
                     <i class="fas fa-file-export mr-2 text-[var(--text-muted)] group-hover:text-blue-500"></i> Exportar Reporte
                </a>
                
                {{-- Botón de Cerrar Caja --}}
                <button id="btnAbrirCierreCaja" type="button" class="w-full flex items-center justify-center bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500 hover:text-white text-rose-500 font-bold text-[11px] sm:text-xs tracking-widest uppercase py-3 px-4 rounded-xl transition-all duration-300 shadow-md shadow-rose-500/5 cursor-pointer">
                    <i class="fas fa-lock mr-2"></i> Cerrar Caja
                </button>
            </div>
        </div>

        {{-- COLUMNA DERECHA: Tablas de Historial (Crecieron a 9 de 12 columnas para maximizar el espacio) --}}
        <div class="lg:col-span-9 space-y-4 sm:space-y-6 w-full">
            
            {{-- BLOQUE 1: Ventas del Turno --}}
            <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-2xl shadow-xl overflow-hidden w-full">
                <div class="bg-gradient-to-r from-sky-500/10 to-transparent p-3 sm:p-4 border-b border-[var(--border-color)] flex flex-wrap gap-2 justify-between items-center w-full">
                    <h3 class="text-xs sm:text-sm font-black text-[var(--text-color)] uppercase tracking-wider flex items-center">
                        <i class="fas fa-shopping-cart text-sky-500 mr-2"></i> Ventas del Turno
                    </h3>
                    <span class="text-[10px] sm:text-xs font-black bg-sky-500/10 text-sky-500 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg border border-sky-500/20 whitespace-nowrap">
                        Total: ${{ number_format($totalVentas, 2) }}
                    </span>
                </div>
                
                <div class="overflow-x-auto w-full -webkit-overflow-scrolling-touch">
                    @if($historicoVentas->isEmpty())
                        <div class="p-8 sm:p-12 text-center flex flex-col items-center justify-center min-h-[140px] sm:min-h-[180px]">
                            <i class="fas fa-inbox text-2xl sm:text-3xl text-[var(--text-muted)] mb-3"></i>
                            <p class="text-xs sm:text-sm text-[var(--text-muted)] font-medium">No hay ventas registradas en este turno.</p>
                        </div>
                    @else
                        <table class="w-full min-w-[560px] text-xs sm:text-sm text-center border-collapse">
                            <thead>
                                <tr class="bg-[var(--input-bg)] text-[var(--text-muted)] font-bold text-[10px] sm:text-xs border-b border-[var(--border-color)] uppercase tracking-wider">
                                    <th class="py-2.5 sm:py-3.5 px-3 sm:px-6">Hora</th>
                                    <th class="py-2.5 sm:py-3.5 px-3 sm:px-6">Concepto</th>
                                    <th class="py-2.5 sm:py-3.5 px-3 sm:px-6">Método de Pago</th>
                                    <th class="py-2.5 sm:py-3.5 px-3 sm:px-6">Monto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border-color)] text-[var(--text-color)]">
                                @foreach($historicoVentas as $venta)
                                    <tr class="hover:bg-[var(--input-bg)]/50 transition-colors">
                                        <td class="py-3 sm:py-4 px-3 sm:px-6 text-[10px] sm:text-xs font-medium text-[var(--text-muted)] whitespace-nowrap">{{ \Carbon\Carbon::parse($venta->fecha)->format('H:i') }} hrs</td>
                                        <td class="py-3 sm:py-4 px-3 sm:px-6 font-semibold">{{ $venta->concepto }}</td>
                                        <td class="py-3 sm:py-4 px-3 sm:px-6">
                                            <span class="px-2 sm:px-2.5 py-1 rounded-md text-[10px] sm:text-[11px] font-black tracking-wider bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 uppercase whitespace-nowrap">
                                                {{ $venta->metodo_pago }}
                                            </span>
                                        </td>
                                        <td class="py-3 sm:py-4 px-3 sm:px-6 font-black text-emerald-500 whitespace-nowrap">+${{ number_format($venta->monto, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            {{-- BLOQUE 2: Gastos y Salidas --}}
            <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-2xl shadow-xl overflow-hidden w-full">
                <div class="bg-gradient-to-r from-rose-500/10 to-transparent p-3 sm:p-4 border-b border-[var(--border-color)] flex flex-wrap gap-2 justify-between items-center w-full">
                    <h3 class="text-xs sm:text-sm font-black text-[var(--text-color)] uppercase tracking-wider flex items-center">
                        <i class="fas fa-hand-holding-usd text-rose-500 mr-2"></i> Gastos y Salidas
                    </h3>
                    <span class="text-[10px] sm:text-xs font-black bg-rose-500/10 text-rose-500 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg border border-rose-500/20 whitespace-nowrap">
                        Total: ${{ number_format($totalGastos, 2) }}
                    </span>
                </div>
                
                <div class="overflow-x-auto w-full -webkit-overflow-scrolling-touch">
                    @if($historicoGastos->isEmpty())
                        <div class="p-8 sm:p-12 text-center flex flex-col items-center justify-center min-h-[140px] sm:min-h-[180px]">
                            <i class="fas fa-receipt text-2xl sm:text-3xl text-[var(--text-muted)] mb-3"></i>
                            <p class="text-xs sm:text-sm text-[var(--text-muted)] font-medium">No hay gastos o salidas registrados en este turno.</p>
                        </div>
                    @else
                        <table class="w-full min-w-[620px] text-xs sm:text-sm text-center border-collapse">
                            <thead>
                                <tr class="bg-[var(--input-bg)] text-[var(--text-muted)] font-bold text-[10px] sm:text-xs border-b border-[var(--border-color)] uppercase tracking-wider">
                                    <th class="py-2.5 sm:py-3.5 px-3 sm:px-6">Hora</th>
                                    <th class="py-2.5 sm:py-3.5 px-3 sm:px-6">Categoría</th>
                                    <th class="py-2.5 sm:py-3.5 px-3 sm:px-6 text-left">Concepto / Descripción</th>
                                    <th class="py-2.5 sm:py-3.5 px-3 sm:px-6">Monto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border-color)] text-[var(--text-color)]">
                                @foreach($historicoGastos as $gasto)
                                    <tr class="hover:bg-[var(--input-bg)]/50 transition-colors">
                                        <td class="py-3 sm:py-4 px-3 sm:px-6 text-[10px] sm:text-xs font-medium text-[var(--text-muted)] whitespace-nowrap">{{ \Carbon\Carbon::parse($gasto->fecha)->format('H:i') }} hrs</td>
                                        <td class="py-3 sm:py-4 px-3 sm:px-6">
                                            <span class="px-2 py-0.5 rounded text-[10px] sm:text-[11px] font-bold bg-rose-500/10 border border-rose-500/20 text-rose-500 uppercase tracking-wide whitespace-nowrap">
                                                {{ $gasto->categoria }}
                                            </span>
                                        </td>
                                        <td class="py-3 sm:py-4 px-3 sm:px-6 text-left font-medium">
                                            <span class="font-semibold block">{{ $gasto->concepto }}</span>
                                            @if($gasto->observaciones)
                                                <span class="text-[10px] sm:text-xs text-[var(--text-muted)] block mt-0.5">{{ $gasto->observaciones }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 sm:py-4 px-3 sm:px-6 font-black text-rose-500 whitespace-nowrap">-${{ number_format($gasto->monto, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@include('admin.caja.corte')
@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnAbrir = document.getElementById('btnAbrirCierreCaja');
    const modal = document.getElementById('modalCierreCaja');
    const btnCerrarX = document.getElementById('btnCerrarModalX');
    const btnCancelar = document.getElementById('btnCancelarModal');
    const backdrop = document.getElementById('backdropCierreCaja');
    const inputMonto = document.getElementById('monto_final_real');

    if (btnAbrir && modal) {
        btnAbrir.addEventListener('click', () => {
            modal.classList.remove('hidden');
            if (inputMonto) {
                setTimeout(() => inputMonto.focus(), 50); // Pequeño delay para asegurar el enfoque del input
            }
        });
    }

    const ocultarModal = () => {
        if (modal) modal.classList.add('hidden');
    };

    if (btnCerrarX) btnCerrarX.addEventListener('click', ocultarModal);
    if (btnCancelar) btnCancelar.addEventListener('click', ocultarModal);
    if (backdrop) backdrop.addEventListener('click', ocultarModal);
});
</script>