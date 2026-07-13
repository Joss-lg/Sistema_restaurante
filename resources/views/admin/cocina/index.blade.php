{{-- resources/views/admin/cocina/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'KDS Cocina | Ollintem Pro')

@section('header-title', 'Modulo de Cocina')
@section('header-subtitle', 'Monitor en tiempo real de comandas, tiempos de preparación y notas especiales')

@section('content')
<div class="p-3 sm:p-6 lg:p-8 w-full max-w-[1800px] mx-auto relative z-10 overflow-x-hidden font-sans">

    {{-- PANEL DE ESTADÍSTICAS KDS --}}
    <div class="glass-card rounded-[20px] sm:rounded-[32px] p-3 sm:p-4 flex flex-col xl:flex-row gap-3 shadow-2xl border border-[var(--border-color)] bg-[var(--card-color)] relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-blue-500/5 to-transparent pointer-events-none"></div>

        {{-- KPI Principal --}}
        <div class="bg-[var(--bg-color)] rounded-[16px] sm:rounded-[24px] p-4 sm:p-8 xl:w-1/3 flex flex-col justify-center relative border border-[var(--border-color)] shadow-inner">
            <div class="flex items-center gap-2.5 mb-2">
                <span class="relative flex h-3 w-3 sm:h-4 sm:w-4 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 sm:h-4 sm:w-4 bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.8)]"></span>
                </span>
                <h2 class="text-[10px] sm:text-xs font-black uppercase tracking-[0.3em] text-[var(--text-muted)]">Fuego Abierto</h2>
            </div>
            <p class="text-3xl sm:text-6xl font-black text-[var(--text-color)] tracking-tighter mt-1 flex flex-col sm:flex-row sm:items-baseline gap-1 sm:gap-3">
                <span>{{ $ordenes->count() }}</span>
                <span class="text-xs sm:text-base text-[var(--text-muted)] font-bold uppercase tracking-widest">Órdenes activas</span>
            </p>
        </div>

        {{-- KPIs Secundarios --}}
        <div class="flex-1 grid grid-cols-3 gap-1.5 sm:gap-4">
            {{-- Tarjetas de estado (Pendientes, En Proceso, Listas) --}}
            @foreach(['pendientes' => ['text' => 'En Cola', 'val' => $pendientes, 'border' => 'orange', 'icon' => 'fa-receipt'], 
                      'enProceso' => ['text' => 'Proceso', 'val' => $enProceso, 'border' => 'blue', 'icon' => 'fa-fire-burner'], 
                      'servidas' => ['text' => 'Listas', 'val' => $servidas, 'border' => 'emerald', 'icon' => 'fa-bell-concierge']] as $key => $kpi)
            <div class="bg-[var(--bg-color)] rounded-[14px] sm:rounded-[24px] p-2.5 sm:p-6 border border-{{$kpi['border']}}-500/30 flex flex-col items-center sm:items-start justify-center relative overflow-hidden group hover:border-{{$kpi['border']}}-500/60 transition-colors min-w-0">
                <div class="absolute inset-0 bg-gradient-to-b from-{{$kpi['border']}}-500/10 to-transparent opacity-50"></div>
                <div class="relative z-10 flex flex-col items-center sm:items-start w-full min-w-0">
                    <div class="flex justify-center sm:justify-between items-center w-full mb-1 sm:mb-2">
                        <span class="text-[8px] sm:text-xs font-black uppercase tracking-widest text-{{$kpi['border']}}-500 drop-shadow-sm text-center sm:text-left leading-tight truncate">{{$kpi['text']}}</span>
                        <i class="fas {{$kpi['icon']}} text-{{$kpi['border']}}-500/40 text-lg hidden sm:block shrink-0"></i>
                    </div>
                    <p class="text-xl sm:text-5xl font-black text-[var(--text-color)] drop-shadow-md">{{$kpi['val']}}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- TABLERO DE TICKETS --}}
    @if($ordenes->isEmpty())
        <div class="glass-card rounded-[24px] px-6 py-16 sm:py-24 text-center border border-[var(--border-color)] shadow-xl mt-6 sm:mt-8 bg-[var(--card-color)]">
            <i class="fas fa-check-double text-5xl text-emerald-500 mb-4"></i>
            <h2 class="text-xl sm:text-2xl font-black text-[var(--text-color)]">¡Cocina Despejada!</h2>
        </div>
    @else
        <div class="grid gap-3 sm:gap-6 grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 mt-4 sm:mt-8 items-start w-full">
            @foreach($ordenes as $orden)
                @php
                    $config = [
                        'pendiente' => ['border' => 'border-t-orange-500', 'btn' => 'bg-orange-500 hover:bg-orange-400 text-white', 'textColor' => 'text-orange-500', 'badgeBg' => 'bg-orange-500/10 border-orange-500/30'],
                        'en proceso' => ['border' => 'border-t-blue-500', 'btn' => 'bg-emerald-500 hover:bg-emerald-400 text-white', 'textColor' => 'text-blue-500', 'badgeBg' => 'bg-blue-500/10 border-blue-500/30'],
                        'servida' => ['border' => 'border-t-emerald-500', 'btn' => 'bg-[var(--input-bg)] text-[var(--text-muted)] cursor-not-allowed border border-[var(--border-color)]', 'textColor' => 'text-emerald-500', 'badgeBg' => 'bg-emerald-500/10 border-emerald-500/30']
                    ][$orden->estado] ?? ['border' => 'border-t-gray-500', 'btn' => 'bg-gray-500', 'textColor' => 'text-gray-500', 'badgeBg' => 'bg-gray-500/10'];
                @endphp

                <article class="bg-[var(--card-color)] w-full rounded-[20px] border border-[var(--border-color)] border-t-[6px] {{ $config['border'] }} shadow-lg flex flex-col h-full overflow-hidden relative">
                   {{-- CABECERA DEL TICKET --}}
                    <div class="p-4 border-b border-[var(--border-color)] min-w-0">
                        <h3 class="font-black text-lg truncate">Mesa {{ $orden->mesa->numero }}</h3>
                        <p class="text-xs text-[var(--text-muted)] truncate">Mesero: {{ $orden->mesero->nombre ?? 'N/A' }}</p>
                    </div>

                    {{-- LISTA DE PRODUCTOS --}}
                    <div class="p-4 flex-1 min-w-0">
                        <ul class="space-y-2">
                            @foreach($orden->detalles as $detalle)
                                <li class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-sm gap-0.5">
                                    <span class="font-bold text-[var(--text-color)] break-words flex flex-wrap items-center gap-1.5">
                                        {{ $detalle->cantidad }}x {{ $detalle->producto->nombre ?? 'Producto Eliminado' }}
                                        @if($detalle->gramaje)
                                            @php
                                                // El gramaje viene de una columna DECIMAL, así que MySQL
                                                // siempre regresa los 2 decimales (ej. "650.00"). Aquí se
                                                // quitan los ceros sobrantes para mostrar "650g" en vez de
                                                // "650.00g", conservando decimales reales si los hay (ej. 650.5g).
                                                $gramajeLimpio = rtrim(rtrim(number_format((float) $detalle->gramaje, 2, '.', ''), '0'), '.');
                                            @endphp
                                            <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wide text-orange-400 bg-orange-500/10 border border-orange-500/30 px-1.5 py-0.5 rounded-md">
                                                <i class="fas fa-weight-hanging"></i>{{ $gramajeLimpio }}g
                                            </span>
                                        @endif
                                    </span>
                                    @if($detalle->notas)
                                        <span class="block text-[10px] text-red-400 italic break-words">{{ $detalle->notas }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    <div class="p-3 sm:p-4 bg-[var(--bg-color)] border-t border-[var(--border-color)]">
                        <form action="{{ route('admin.cocina.orden.estado', $orden->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="estado" value="{{ $orden->estado === 'pendiente' ? 'en proceso' : 'servida' }}">
                            <button type="submit" {{ $orden->estado === 'servida' ? 'disabled' : '' }} 
                                class="w-full py-3.5 rounded-xl font-black uppercase text-[12px] tracking-[0.1em] transition-all active:scale-95 {{ $config['btn'] }}">
                                {{ $orden->estado === 'pendiente' ? 'Iniciar Preparación' : ($orden->estado === 'en proceso' ? 'Marcar como Lista' : 'Entregada') }}
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection {{-- <--- ESTO ES VITAL QUE ESTÉ AL FINAL --}}

<script>
    /**
     * Carga dinámica de mesas para el KDS.
     * Actualiza la lista cada 5 segundos mediante polling.
     */
    async function cargarKdsMesas() {
        try {
            const res = await fetch('{{ route("mesero.mesas.abiertas") }}', { 
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest', 
                    'Accept': 'application/json' 
                } 
            });
            
            const data = await res.json().catch(() => null);
            if (!res.ok || !data || !data.success) return;

            const list = document.getElementById('kdsMesasList');
            const badge = document.getElementById('kdsBadge');
            if (!list) return;

            list.innerHTML = '';
            if (badge) badge.innerText = data.conteo_abiertas || 0;

            const mesas = data.mesas_abiertas || [];
            
            if (mesas.length === 0) {
                list.innerHTML = `
                    <div class="text-[11px] text-zinc-500 modo-crema:text-zinc-400 font-medium p-4 text-center bg-zinc-950/40 modo-crema:bg-zinc-100/50 rounded-xl border border-zinc-800 modo-crema:border-zinc-200">
                        No hay mesas activas en este momento.
                    </div>`;
                return;
            }

            mesas.forEach(m => {
                const a = document.createElement('a');
                a.href = `{{ url('mesero/comanda') }}/${m.id}`;
                a.className = 'block p-4 rounded-xl border border-zinc-800 modo-crema:border-zinc-200 bg-zinc-900/40 modo-crema:bg-white hover:border-blue-500/50 hover:bg-zinc-900 modo-crema:hover:bg-zinc-50 transition-all flex items-center justify-between group cursor-pointer';
                
                a.innerHTML = `
                    <div class="flex-1 w-full min-w-0 pr-2">
                        <div class="flex items-center gap-2 mb-1">
                            <h5 class="text-sm font-black text-zinc-100 modo-crema:text-zinc-900 truncate group-hover:text-blue-500 transition-colors">
                                Mesa ${m.numero}
                            </h5>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded border border-emerald-500/30 bg-emerald-500/10 text-emerald-500 text-[8px] font-black uppercase tracking-wider shadow-sm">
                                ACTIVA
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-[10px] text-zinc-400 modo-crema:text-zinc-500 font-bold">
                            <span class="flex items-center gap-1.5"><i class="fas fa-users text-blue-500"></i> ${m.capacidad ?? '0'} pax</span>
                            <span class="text-emerald-400 modo-crema:text-emerald-600 tracking-wide">$ ${Number(m.total_consumo || 0).toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="pl-2 flex items-center shrink-0">
                        <div class="w-8 h-8 rounded-full bg-zinc-950 modo-crema:bg-zinc-100 border border-zinc-800 modo-crema:border-zinc-200 flex items-center justify-center group-hover:bg-blue-600 group-hover:border-blue-600 transition-colors">
                            <i class="fas fa-chevron-right text-[9px] text-zinc-500 group-hover:text-white transition-colors"></i>
                        </div>
                    </div>
                `;
                list.appendChild(a);
            });

        } catch (err) {
            console.error('Error cargando mesas KDS:', err);
        }
    }

    // Inicialización al cargar el DOM
    document.addEventListener('DOMContentLoaded', () => {
        cargarKdsMesas();
        // Polling de seguridad ajustado a 10 segundos para no saturar el servidor
        setInterval(cargarKdsMesas, 10000);
    });
</script>