@extends('layouts.admin')

@section('title', 'KDS Cocina | Ollintem Pro')

@section('header-title', 'Kitchen Display System')
@section('header-subtitle', 'Monitor en tiempo real de comandas, tiempos de preparación y notas especiales')

@section('content')
<div class="p-3 sm:p-6 lg:p-8 w-full max-w-[1800px] mx-auto relative z-10 overflow-x-hidden font-sans">

    {{-- PANEL DE ESTADÍSTICAS KDS (HUD Moderno de Alto Contraste) --}}
    <div class="glass-card rounded-[20px] sm:rounded-[32px] p-3 sm:p-4 flex flex-col xl:flex-row gap-3 shadow-2xl border border-[var(--border-color)] bg-[var(--card-color)] relative overflow-hidden">
        
        {{-- Brillo decorativo de fondo --}}
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-blue-500/5 to-transparent pointer-events-none"></div>

        {{-- KPI Principal --}}
        <div class="bg-[var(--bg-color)] rounded-[16px] sm:rounded-[24px] p-5 sm:p-8 xl:w-1/3 flex flex-col justify-center relative border border-[var(--border-color)] shadow-inner">
            <div class="flex items-center gap-2.5 mb-2">
                <span class="relative flex h-3 w-3 sm:h-4 sm:w-4 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 sm:h-4 sm:w-4 bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.8)]"></span>
                </span>
                <h2 class="text-[10px] sm:text-xs font-black uppercase tracking-[0.3em] text-[var(--text-muted)]">Fuego Abierto</h2>
            </div>
            <p class="text-4xl sm:text-6xl font-black text-[var(--text-color)] tracking-tighter mt-1 flex items-baseline">
                {{ $ordenes->count() }} 
                <span class="text-xs sm:text-base text-[var(--text-muted)] font-bold uppercase tracking-widest ml-3">Órdenes activas</span>
            </p>
        </div>

        {{-- KPIs Secundarios (Alto Contraste y Compactos en Móvil) --}}
        <div class="flex-1 grid grid-cols-3 gap-2 sm:gap-4">
            {{-- Tarjeta Pendientes --}}
            <div class="bg-[var(--bg-color)] rounded-[16px] sm:rounded-[24px] p-4 sm:p-6 border border-orange-500/30 flex flex-col items-center sm:items-start justify-center relative overflow-hidden group hover:border-orange-500/60 transition-colors">
                <div class="absolute inset-0 bg-gradient-to-b from-orange-500/10 to-transparent opacity-50"></div>
                <div class="relative z-10 flex flex-col items-center sm:items-start w-full">
                    <div class="flex justify-center sm:justify-between items-center w-full mb-1 sm:mb-2">
                        <span class="text-[9px] sm:text-xs font-black uppercase tracking-widest text-orange-500 drop-shadow-sm">En Cola</span>
                        <i class="fas fa-receipt text-orange-500/40 text-lg hidden sm:block"></i>
                    </div>
                    {{-- Contraste forzado para el número --}}
                    <p class="text-3xl sm:text-5xl font-black text-[var(--text-color)] drop-shadow-md">{{ $pendientes }}</p>
                </div>
            </div>
            
            {{-- Tarjeta Preparando --}}
            <div class="bg-[var(--bg-color)] rounded-[16px] sm:rounded-[24px] p-4 sm:p-6 border border-blue-500/30 flex flex-col items-center sm:items-start justify-center relative overflow-hidden group hover:border-blue-500/60 transition-colors">
                <div class="absolute inset-0 bg-gradient-to-b from-blue-500/10 to-transparent opacity-50"></div>
                <div class="relative z-10 flex flex-col items-center sm:items-start w-full">
                    <div class="flex justify-center sm:justify-between items-center w-full mb-1 sm:mb-2">
                        <span class="text-[9px] sm:text-xs font-black uppercase tracking-widest text-blue-500 drop-shadow-sm">Proceso</span>
                        <i class="fas fa-fire-burner text-blue-500/40 text-lg hidden sm:block"></i>
                    </div>
                    {{-- Contraste forzado para el número --}}
                    <p class="text-3xl sm:text-5xl font-black text-[var(--text-color)] drop-shadow-md">{{ $enProceso }}</p>
                </div>
            </div>
            
            {{-- Tarjeta Listas --}}
            <div class="bg-[var(--bg-color)] rounded-[16px] sm:rounded-[24px] p-4 sm:p-6 border border-emerald-500/30 flex flex-col items-center sm:items-start justify-center relative overflow-hidden group hover:border-emerald-500/60 transition-colors">
                <div class="absolute inset-0 bg-gradient-to-b from-emerald-500/10 to-transparent opacity-50"></div>
                <div class="relative z-10 flex flex-col items-center sm:items-start w-full">
                    <div class="flex justify-center sm:justify-between items-center w-full mb-1 sm:mb-2">
                        <span class="text-[9px] sm:text-xs font-black uppercase tracking-widest text-emerald-500 drop-shadow-sm">Listas</span>
                        <i class="fas fa-bell-concierge text-emerald-500/40 text-lg hidden sm:block"></i>
                    </div>
                    {{-- Contraste forzado para el número --}}
                    <p class="text-3xl sm:text-5xl font-black text-[var(--text-color)] drop-shadow-md">{{ $servidas }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLERO DE TICKETS KDS --}}
    @if($ordenes->isEmpty())
        <div class="glass-card rounded-[24px] px-6 py-16 sm:py-24 text-center border border-[var(--border-color)] shadow-xl flex flex-col items-center justify-center mt-6 sm:mt-8 mx-auto w-full bg-[var(--card-color)]">
            <div class="relative mb-5">
                <div class="absolute inset-0 bg-emerald-500/20 blur-[30px] rounded-full"></div>
                <div class="w-20 h-20 bg-[var(--bg-color)] rounded-[20px] flex items-center justify-center text-[var(--text-muted)] border border-[var(--border-color)] shadow-inner relative z-10">
                    <i class="fas fa-check-double text-4xl text-emerald-500 drop-shadow-[0_0_10px_rgba(16,185,129,0.5)]"></i>
                </div>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-[var(--text-color)] tracking-tight">¡Cocina Despejada!</h2>
            <p class="mt-2 text-sm text-[var(--text-muted)] font-medium">No hay tickets con productos pendientes.</p>
        </div>
    @else
        <div class="grid gap-4 sm:gap-6 grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 mt-6 sm:mt-8 items-start w-full">
            @foreach($ordenes as $orden)
                
                {{-- Configuración visual basada en el estado --}}
                @php
                    $kds = [
                        'pendiente' => ['border' => 'border-t-orange-500', 'bgGlow' => 'from-orange-500/15', 'btn' => 'bg-orange-500 hover:bg-orange-400 text-white shadow-[0_4px_15px_rgba(249,115,22,0.4)]', 'icon' => 'fa-fire', 'textColor' => 'text-orange-500', 'badgeBg' => 'bg-orange-500/10 border-orange-500/30'],
                        'en proceso' => ['border' => 'border-t-blue-500', 'bgGlow' => 'from-blue-500/15', 'btn' => 'bg-emerald-500 hover:bg-emerald-400 text-white shadow-[0_4px_15px_rgba(16,185,129,0.4)]', 'icon' => 'fa-bell', 'textColor' => 'text-blue-500', 'badgeBg' => 'bg-blue-500/10 border-blue-500/30'],
                        'servida' => ['border' => 'border-t-emerald-500', 'bgGlow' => 'from-emerald-500/10', 'btn' => 'bg-[var(--input-bg)] text-[var(--text-muted)] cursor-not-allowed border border-[var(--border-color)]', 'icon' => 'fa-check', 'textColor' => 'text-emerald-500', 'badgeBg' => 'bg-emerald-500/10 border-emerald-500/30'],
                    ][$orden->estado] ?? ['border' => 'border-t-[var(--border-color)]', 'bgGlow' => 'from-transparent', 'btn' => 'bg-gray-500 text-white', 'icon' => 'fa-circle', 'textColor' => 'text-gray-400', 'badgeBg' => 'bg-[var(--input-bg)] border-[var(--border-color)]'];
                @endphp

                <article class="bg-[var(--card-color)] w-full rounded-[20px] border border-[var(--border-color)] border-t-[6px] {{ $kds['border'] }} shadow-lg flex flex-col h-full overflow-hidden relative transition-transform duration-300 hover:-translate-y-1">
                    
                    {{-- Brillo de fondo sutil --}}
                    <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b {{ $kds['bgGlow'] }} to-transparent pointer-events-none"></div>

                    {{-- HEADER DEL TICKET --}}
                    <div class="p-4 sm:p-5 relative z-10 w-full border-b border-dashed border-[var(--border-color)]">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between gap-3 w-full">
                                <h3 class="text-3xl sm:text-4xl font-black text-[var(--text-color)] tracking-tighter truncate drop-shadow-sm">Mesa {{ $orden->mesa->numero ?? '?' }}</h3>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md border text-[10px] font-black uppercase tracking-widest {{ $kds['badgeBg'] }} {{ $kds['textColor'] }} whitespace-nowrap shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full {{ str_replace('text-', 'bg-', $kds['textColor']) }} mr-1.5 {{ $orden->estado === 'pendiente' ? 'animate-pulse shadow-[0_0_5px_currentColor]' : '' }}"></span>
                                    {{ $orden->estado }}
                                </span>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2 mt-1 text-[11px] text-[var(--text-muted)] font-medium">
                                <span class="font-mono font-bold text-[var(--text-color)] bg-[var(--input-bg)] px-2 py-1 rounded border border-[var(--border-color)] shadow-inner">
                                    #{{ str_pad($orden->numero_orden, 5, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="flex items-center bg-[var(--bg-color)] px-2 py-1 rounded border border-[var(--border-color)]">
                                    <i class="fas fa-user-tag text-[10px] mr-1.5 text-blue-400"></i>{{ $orden->mesero->nombre ?? 'N/A' }}
                                </span>
                                <span class="flex items-center bg-[var(--bg-color)] px-2 py-1 rounded border border-[var(--border-color)]">
                                    <i class="fas fa-clock text-[10px] mr-1.5 text-orange-400"></i>{{ $orden->abierta_el ? $orden->abierta_el->diffForHumans() : '0m' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Recortes visuales laterales del ticket --}}
                    <div class="absolute top-[88px] sm:top-[98px] -left-3 w-6 h-6 bg-[var(--bg-color)] rounded-full border-r border-[var(--border-color)] z-20 shadow-inner"></div>
                    <div class="absolute top-[88px] sm:top-[98px] -right-3 w-6 h-6 bg-[var(--bg-color)] rounded-full border-l border-[var(--border-color)] z-20 shadow-inner"></div>

                    {{-- LISTA DE PRODUCTOS --}}
                    <div class="p-4 sm:p-5 flex-1 bg-[var(--input-bg)]/30">
                        <div class="space-y-4">
                            @foreach($orden->detalles as $detalle)
                                <div class="flex gap-3 sm:gap-4 w-full">
                                    {{-- Cantidad --}}
                                    <div class="flex flex-col items-center shrink-0 w-10 sm:w-12">
                                        <div class="w-full bg-[var(--bg-color)] border border-[var(--border-color)] rounded-xl py-2 flex items-center justify-center shadow-md">
                                            <span class="text-xl sm:text-2xl font-black text-[var(--text-color)] leading-none">{{ $detalle->cantidad }}</span>
                                        </div>
                                    </div>
                                    
                                    {{-- Detalle del platillo --}}
                                    <div class="flex-1 min-w-0 pt-1 border-b border-[var(--border-color)] pb-4 last:border-0 last:pb-0">
                                        <p class="text-[14px] sm:text-[16px] font-black text-[var(--text-color)] leading-tight break-words drop-shadow-sm">{{ $detalle->producto->nombre ?? 'Producto no encontrado' }}</p>
                                        
                                        {{-- Notas de alerta --}}
                                        @if($detalle->notas)
                                            <div class="mt-3 flex items-start gap-2 rounded-lg bg-red-500/10 border border-red-500/30 px-3 py-2 w-full shadow-inner">
                                                <i class="fas fa-exclamation-triangle text-red-500 text-[11px] mt-0.5 shrink-0 animate-pulse"></i>
                                                <p class="text-[11px] sm:text-[12px] font-black text-red-500 leading-snug uppercase tracking-wide break-words">{{ $detalle->notas }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- FOOTER / BOTÓN DE ACCIÓN --}}
                    <div class="p-3 sm:p-4 bg-[var(--bg-color)] border-t border-[var(--border-color)] z-20 w-full relative">
                        <form action="{{ route('admin.cocina.orden.estado', $orden->id) }}" method="POST" class="w-full">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="estado" value="{{ $orden->estado === 'pendiente' ? 'en proceso' : 'servida' }}">
                            
                            @if($orden->estado === 'pendiente')
                                <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl py-3.5 px-4 text-[11px] sm:text-[13px] font-black uppercase tracking-[0.2em] transition-all duration-200 active:scale-[0.98] {{ $kds['btn'] }}">
                                    <i class="fas fa-fire text-sm sm:text-base"></i> Iniciar Preparación
                                </button>
                            @elseif($orden->estado === 'en proceso')
                                <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl py-3.5 px-4 text-[11px] sm:text-[13px] font-black uppercase tracking-[0.2em] transition-all duration-200 active:scale-[0.98] {{ $kds['btn'] }}">
                                    <i class="fas fa-check-circle text-sm sm:text-base"></i> Marcar como Lista
                                </button>
                            @else
                                <button type="button" disabled class="w-full flex items-center justify-center gap-2 rounded-xl py-3.5 px-4 text-[11px] sm:text-[13px] font-black uppercase tracking-[0.2em] {{ $kds['btn'] }}">
                                    <i class="fas fa-check-double text-sm sm:text-base"></i> Entregada
                                </button>
                            @endif
                        </form>
                    </div>

                </article>
            @endforeach
        </div>
    @endif
</div>

<script>
    // Script de carga de mesas KDS (Se mantiene tu lógica original intacta)
    async function cargarKdsMesas() {
        try {
            const res = await fetch('/mesero/mesas/abiertas', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            const data = await res.json().catch(() => null);
            if (!res.ok || !data || !data.success) return;

            const list = document.getElementById('kdsMesasList');
            const badge = document.getElementById('kdsBadge');
            if (!list) return;
            list.innerHTML = '';
            badge && (badge.innerText = data.conteo_abiertas || 0);

            const mesas = data.mesas_abiertas || [];
            if (mesas.length === 0) {
                list.innerHTML = '<div class="text-[13px] text-[var(--text-muted)] font-medium p-4 text-center bg-[var(--input-bg)] rounded-xl border border-[var(--border-color)]">No tienes mesas abiertas.</div>';
                return;
            }

            mesas.forEach(m => {
                const a = document.createElement('a');
                a.href = `/mesero/comanda/${m.id}`;
                a.className = 'block p-3.5 rounded-xl border border-[var(--border-color)] bg-[var(--card-color)] hover:border-blue-500/50 hover:bg-[var(--bg-color)] transition-all flex items-center justify-between group';
                a.innerHTML = `
                    <div class="flex-1 w-full min-w-0 pr-2">
                        <div class="flex items-center gap-2 mb-1">
                            <h5 class="text-base font-black text-[var(--text-color)] truncate group-hover:text-blue-400 transition-colors">Mesa ${m.numero}</h5>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md border border-emerald-500/30 bg-emerald-500/10 text-emerald-500 text-[9px] font-black uppercase shrink-0 shadow-sm">ACTIVA</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-[var(--text-muted)] font-medium">
                            <span class="flex items-center gap-1.5"><i class="fas fa-users text-blue-400"></i> ${m.capacidad ?? ''} pax</span>
                            <span class="text-emerald-400 font-bold tracking-wide">$ ${Number(m.total_consumo || 0).toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="pl-2 flex items-center shrink-0">
                        <div class="w-8 h-8 rounded-full bg-[var(--input-bg)] border border-[var(--border-color)] flex items-center justify-center group-hover:bg-blue-500 group-hover:border-blue-500 transition-colors">
                            <i class="fas fa-chevron-right text-[10px] text-[var(--text-muted)] group-hover:text-white transition-colors"></i>
                        </div>
                    </div>
                `;
                list.appendChild(a);
            });

        } catch (err) {
            console.error('Error cargando mesas KDS:', err);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        cargarKdsMesas();
        setInterval(cargarKdsMesas, 5000);
    });
</script>
@endsection