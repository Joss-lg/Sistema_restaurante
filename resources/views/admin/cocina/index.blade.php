@extends('layouts.admin')

@section('title', 'KDS Cocina | Ollintem Pro')

@section('header-title', 'Kitchen Display System')
@section('header-subtitle', 'Monitor en tiempo real de comandas, tiempos de preparación y notas especiales')

@section('content')
<div class="p-6 lg:p-8 w-full max-w-[1800px] mx-auto relative z-10">
    <div class="flex gap-6">
        {{-- COLUMNA IZQUIERDA: MIS MESAS ACTIVAS (KDS) --}}
        <aside class="hidden lg:block w-[320px] flex-shrink-0">
            <div class="p-4 rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-[11px] font-black uppercase text-[var(--text-muted)]">Mis Mesas Activas</h4>
                    <div id="kdsBadge" class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center font-black">0</div>
                </div>

                <div id="kdsMesasList" class="space-y-3 max-h-[72vh] overflow-y-auto hide-scroll">
                    {{-- Cards se cargarán por JS --}}
                    <div class="text-[12px] text-[var(--text-muted)]">Cargando mesas...</div>
                </div>
            </div>
        </aside>

        <main class="flex-1">

    {{-- PANEL DE ESTADÍSTICAS KDS (Estilo Terminal / HUD) --}}
    <div class="glass-card rounded-[32px] p-2 flex flex-col xl:flex-row gap-2 shadow-2xl border border-[var(--border-color)] relative overflow-hidden">
        {{-- Fondo con brillo sutil --}}
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/5 via-transparent to-orange-500/5 pointer-events-none"></div>

        {{-- KPI Principal --}}
        <div class="bg-[var(--bg-color)] rounded-[24px] p-8 xl:w-1/3 flex flex-col justify-center relative border border-[var(--border-color)]">
            <div class="flex items-center gap-3 mb-2">
                <span class="relative flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-blue-500"></span>
                </span>
                <h2 class="text-xs font-black uppercase tracking-[0.4em] text-[var(--text-muted)]">Fuego Abierto</h2>
            </div>
            <p class="text-6xl font-black text-[var(--text-color)] tracking-tighter mt-2">{{ $ordenes->count() }} <span class="text-xl text-[var(--text-muted)] font-bold uppercase tracking-widest ml-2">Órdenes activas</span></p>
        </div>

        {{-- KPIs Secundarios --}}
        <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2">
            {{-- Tarjeta Pendientes --}}
            <div class="bg-[var(--card-color)] rounded-[24px] p-6 border border-orange-500/20 flex flex-col justify-between group hover:bg-orange-500/5 transition-colors">
                <div class="flex justify-between items-start">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-orange-500">En Cola</span>
                    <i class="fas fa-receipt text-orange-500/50 text-xl group-hover:scale-110 transition-transform"></i>
                </div>
                <p class="text-4xl font-black text-[var(--text-color)] mt-4">{{ $pendientes }}</p>
            </div>
            
            {{-- Tarjeta Preparando --}}
            <div class="bg-[var(--card-color)] rounded-[24px] p-6 border border-blue-500/20 flex flex-col justify-between group hover:bg-blue-500/5 transition-colors">
                <div class="flex justify-between items-start">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-500">Preparando</span>
                    <i class="fas fa-fire-burner text-blue-500/50 text-xl group-hover:scale-110 transition-transform"></i>
                </div>
                <p class="text-4xl font-black text-[var(--text-color)] mt-4">{{ $enProceso }}</p>
            </div>
            
            {{-- Tarjeta Listas --}}
            <div class="bg-[var(--card-color)] rounded-[24px] p-6 border border-emerald-500/20 flex flex-col justify-between group hover:bg-emerald-500/5 transition-colors">
                <div class="flex justify-between items-start">
                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-500">Por entregar</span>
                    <i class="fas fa-bell-concierge text-emerald-500/50 text-xl group-hover:scale-110 transition-transform"></i>
                </div>
                <p class="text-4xl font-black text-[var(--text-color)] mt-4">{{ $servidas }}</p>
            </div>
        </div>
    </div>

    {{-- TABLERO DE TICKETS KDS --}}
    @if($ordenes->isEmpty())
        <div class="glass-card rounded-[32px] p-20 text-center border border-[var(--border-color)] shadow-2xl flex flex-col items-center justify-center mt-8">
            <div class="relative mb-6">
                <div class="absolute inset-0 bg-emerald-500/20 blur-[40px] rounded-full"></div>
                <div class="w-24 h-24 bg-gradient-to-br from-[var(--card-color)] to-[var(--input-bg)] rounded-3xl flex items-center justify-center text-[var(--text-muted)] border border-[var(--border-color)] shadow-inner relative z-10">
                    <i class="fas fa-check-double text-4xl text-emerald-500"></i>
                </div>
            </div>
            <h2 class="text-3xl font-black text-[var(--text-color)] tracking-tight">¡Cocina Despejada!</h2>
            <p class="mt-3 text-base text-[var(--text-muted)] font-medium max-w-md">No hay tickets con productos pendientes. Los meseros aún no han agregado productos a sus órdenes.</p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 mt-8 items-start">
            @foreach($ordenes as $orden)
                
                {{-- Configuración visual basada en el estado --}}
                @php
                    $kds = [
                        'pendiente' => ['border' => 'border-t-orange-500', 'bgGlow' => 'from-orange-500/10', 'btn' => 'bg-orange-500 hover:bg-orange-400 shadow-[0_0_20px_rgba(249,115,22,0.3)]', 'icon' => 'fa-fire', 'btnText' => 'INICIAR PREPARACIÓN', 'textColor' => 'text-orange-500', 'badgeBg' => 'bg-orange-500/10 border-orange-500/20'],
                        'en proceso' => ['border' => 'border-t-blue-500', 'bgGlow' => 'from-blue-500/10', 'btn' => 'bg-emerald-500 hover:bg-emerald-400 shadow-[0_0_20px_rgba(16,185,129,0.3)]', 'icon' => 'fa-bell', 'btnText' => 'MARCAR COMO LISTA', 'textColor' => 'text-blue-500', 'badgeBg' => 'bg-blue-500/10 border-blue-500/20'],
                        'servida' => ['border' => 'border-t-emerald-500', 'bgGlow' => 'from-emerald-500/5', 'btn' => 'bg-[var(--input-bg)] text-[var(--text-muted)] cursor-not-allowed', 'icon' => 'fa-check', 'btnText' => 'ENTREGADA AL MESERO', 'textColor' => 'text-emerald-500', 'badgeBg' => 'bg-emerald-500/10 border-emerald-500/20'],
                    ][$orden->estado] ?? ['border' => 'border-t-[var(--border-color)]', 'bgGlow' => 'from-transparent', 'btn' => 'bg-gray-500', 'icon' => 'fa-circle', 'btnText' => 'ACCIÓN', 'textColor' => 'text-gray-500', 'badgeBg' => 'bg-gray-500/10 border-gray-500/20'];
                @endphp

                <article class="bg-[var(--card-color)] rounded-[24px] border border-[var(--border-color)] border-t-[6px] {{ $kds['border'] }} shadow-[0_15px_40px_-15px_rgba(0,0,0,0.3)] flex flex-col h-full overflow-hidden relative group transition-all duration-300 hover:-translate-y-1">
                    
                    {{-- Brillo de fondo --}}
                    <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b {{ $kds['bgGlow'] }} to-transparent pointer-events-none"></div>

                    {{-- HEADER DEL TICKET (Estilo Recibo Térmico) --}}
                    <div class="p-5 pb-4 relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-4xl font-black text-[var(--text-color)] tracking-tighter">Mesa {{ $orden->mesa->numero ?? '?' }}</h3>
                                    <span class="inline-block px-3 py-1 rounded-full {{ $kds['badgeBg'] }} border text-[10px] font-black uppercase tracking-widest {{ $kds['textColor'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ str_replace('text-', 'bg-', $kds['textColor']) }} {{ $orden->estado === 'pendiente' ? 'animate-pulse inline-block mr-1.5' : 'inline-block mr-1.5' }}"></span>
                                        {{ $orden->estado }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-3 mt-2 text-[12px]">
                                    <span class="font-mono font-bold text-[var(--text-color)] bg-[var(--input-bg)] px-2.5 py-1 rounded-md border border-[var(--border-color)]">Pedido #{{ str_pad($orden->numero_orden, 5, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-[var(--text-muted)]">
                                        <i class="fas fa-user-tag text-[10px] mr-1.5"></i>{{ $orden->mesero->nombre ?? 'N/A' }}
                                    </span>
                                    <span class="text-[var(--text-muted)]">
                                        <i class="fas fa-clock text-[10px] mr-1.5"></i>{{ $orden->abierta_el ? $orden->abierta_el->diffForHumans() : '0m' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Línea de corte (Simulando ticket) --}}
                    <div class="w-full flex items-center px-2">
                        <div class="w-3 h-3 rounded-full bg-[var(--bg-color)] border-r border-[var(--border-color)] -ml-3 z-10 shadow-inner"></div>
                        <div class="flex-1 border-t-2 border-dashed border-[var(--border-color)] mx-1"></div>
                        <div class="w-3 h-3 rounded-full bg-[var(--bg-color)] border-l border-[var(--border-color)] -mr-3 z-10 shadow-inner"></div>
                    </div>

                    {{-- LISTA DE PRODUCTOS (Checklist Visual) --}}
                    <div class="p-5 flex-1 bg-gradient-to-b from-transparent to-[var(--input-bg)]/30">
                        <div class="space-y-4">
                            @foreach($orden->detalles as $detalle)
                                <div class="flex gap-4 group/item">
                                    {{-- Cantidad Masiva (Crucial para el chef) --}}
                                    <div class="flex flex-col items-center shrink-0 w-12 pt-1">
                                        <div class="w-full bg-[var(--bg-color)] border border-[var(--border-color)] rounded-xl py-2 flex items-center justify-center shadow-sm transition-colors group-hover/item:border-blue-500/40">
                                            <span class="text-xl font-black text-[var(--text-color)]">{{ $detalle->cantidad }}</span>
                                        </div>
                                        <span class="text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)] mt-1">Cant</span>
                                    </div>
                                    
                                    {{-- Detalle del platillo --}}
                                    <div class="flex-1 min-w-0 pt-1.5 border-b border-[var(--border-color)] pb-4 group-last/item:border-0 group-last/item:pb-0">
                                        <p class="text-[15px] font-black text-[var(--text-color)] leading-tight">{{ $detalle->producto->nombre ?? 'Producto no encontrado' }}</p>
                                        
                                        {{-- Notas de alerta estilo KDS --}}
                                        @if($detalle->notas)
                                            <div class="mt-2.5 flex items-start gap-2 rounded-lg bg-red-500/10 border-l-2 border-red-500 px-3 py-2 w-full shadow-sm">
                                                <i class="fas fa-bell text-red-500 text-[11px] mt-0.5 shrink-0 animate-bounce"></i>
                                                <p class="text-[12px] font-black text-red-500 leading-snug uppercase tracking-wide">{{ $detalle->notas }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- FOOTER / ACCIÓN KDS --}}
                    <div class="p-4 bg-[var(--bg-color)] border-t border-[var(--border-color)] relative z-20">
                        <form action="{{ route('admin.cocina.orden.estado', $orden->id) }}" method="POST" class="w-full">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="estado" value="{{ $orden->estado === 'pendiente' ? 'en proceso' : 'servida' }}">
                            
                            @if($orden->estado === 'pendiente')
                                <button type="submit" class="w-full flex items-center justify-center gap-3 rounded-lg py-3 px-4 text-[12px] font-black uppercase tracking-[0.2em] text-white bg-orange-500 hover:bg-orange-400 shadow-lg hover:shadow-[0_0_20px_rgba(249,115,22,0.4)] active:scale-95 transition-all duration-200">
                                    <i class="fas fa-fire text-base"></i> INICIAR PREPARACIÓN
                                </button>
                            @elseif($orden->estado === 'en proceso')
                                <button type="submit" class="w-full flex items-center justify-center gap-3 rounded-lg py-3 px-4 text-[12px] font-black uppercase tracking-[0.2em] text-white bg-emerald-500 hover:bg-emerald-400 shadow-lg hover:shadow-[0_0_20px_rgba(16,185,129,0.4)] active:scale-95 transition-all duration-200">
                                    <i class="fas fa-check-circle text-base"></i> MARCAR COMO LISTA
                                </button>
                            @else
                                <button type="button" disabled class="w-full flex items-center justify-center gap-3 rounded-lg py-3 px-4 text-[12px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] bg-[var(--input-bg)] cursor-not-allowed opacity-50">
                                    <i class="fas fa-check-double text-base"></i> ENTREGADA
                                </button>
                            @endif
                        </form>
                    </div>

                </article>
            @endforeach
        </div>
    @endif
        </main>
    </div>
</div>

<style>
    @keyframes slideInUp {
        from { transform: translateY(100%) scale(0.9); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }
</style>

<script>
    // Cargar mesas abiertas en panel KDS (admin.cocina)
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
                list.innerHTML = '<div class="text-[12px] text-[var(--text-muted)]">No tienes mesas abiertas.</div>';
                return;
            }

            mesas.forEach(m => {
                const a = document.createElement('a');
                a.href = `/mesero/comanda/${m.id}`;
                a.className = 'block p-3 rounded-2xl border border-[var(--border-color)] bg-[var(--bg-panel)] hover:border-[#3B82F6]/40 transition-all flex items-start justify-between';
                a.innerHTML = `
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h5 class="text-lg font-black text-[var(--text-main)]">Mesa ${m.numero}</h5>
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-700/10 text-emerald-300 text-[10px] font-black uppercase">ACTIVA</span>
                        </div>
                        <div class="mt-2 flex items-center gap-3 text-[12px] text-[var(--text-muted)]">
                            <span class="flex items-center gap-1"><i class="fas fa-user-friends text-[12px]"></i> ${m.capacidad ?? ''} personas</span>
                            <span class="text-[var(--text-muted)]">• 0m</span>
                            <span class="text-emerald-400 font-black">$ ${Number(m.total_consumo || 0).toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="pl-3 flex items-center">
                        <span class="text-[#3B82F6] font-bold">Ver comanda ›</span>
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