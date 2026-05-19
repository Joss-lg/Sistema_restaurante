@extends('layouts.admin')

@section('title', 'Caja | Ollintem Pro')
@section('header-title', 'Panel de Caja')
@section('header-subtitle', 'Visualiza y controla las mesas activas, libres y los totales que genera cada mesa')
@section('header-actions')
    <button type="button" class="header-action-btn">
        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
        Total abierto: ${{ number_format($totalAbierto ?? 0, 2) }}
    </button>
@endsection

@section('content')
@php
    $mesasLibres = $mesas->where('estado', 'disponible')->count();
@endphp

<div class="p-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-8">
    
    <!-- ENCABEZADO Y RESUMEN DINÁMICO -->
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">
        <div class="space-y-4 max-w-2xl">
            <div class="inline-flex items-center gap-2 rounded-full bg-blue-500/10 px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.35em] text-blue-200">
                <span class="h-2.5 w-2.5 rounded-full bg-blue-400 animate-pulse"></span>
                Panel de caja moderno
            </div>
            <h1 class="text-3xl font-extrabold text-[var(--text-color)] tracking-tight">Panel de Caja</h1>
            <p class="text-sm text-[var(--text-muted)]">Filtra las mesas por estado y selecciona una mesa libre u ocupada rápidamente desde un panel visual más moderno.</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full xl:w-auto">
            <div class="glass-card p-5 rounded-[28px] border border-white/10 shadow-[0_25px_60px_-40px_rgba(0,0,0,0.8)]">
                <p class="text-[10px] uppercase tracking-[0.3em] text-[var(--text-muted)]">Total abierto</p>
                <p class="mt-3 text-2xl font-black text-emerald-400">${{ number_format($totalAbierto ?? 0, 2) }}</p>
            </div>
            <div class="glass-card p-5 rounded-[28px] border border-white/10 shadow-[0_25px_60px_-40px_rgba(0,0,0,0.8)]">
                <p class="text-[10px] uppercase tracking-[0.3em] text-[var(--text-muted)]">Mesas activas</p>
                <p class="mt-3 text-2xl font-black text-sky-400">{{ $mesasActivas ?? 0 }}</p>
            </div>
            <div class="glass-card p-5 rounded-[28px] border border-white/10 shadow-[0_25px_60px_-40px_rgba(0,0,0,0.8)]">
                <p class="text-[10px] uppercase tracking-[0.3em] text-[var(--text-muted)]">Mesas libres</p>
                <p class="mt-3 text-2xl font-black text-cyan-400">{{ $mesasLibres }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="glass-card p-5 rounded-[28px] border border-white/10 shadow-[0_25px_60px_-40px_rgba(0,0,0,0.8)]">
            <p class="text-[10px] uppercase tracking-[0.3em] text-[var(--text-muted)]">Ingresos hoy</p>
            <p id="ingresos-hoy" class="mt-3 text-2xl font-black text-emerald-400">$0.00</p>
        </div>
        <div class="glass-card p-5 rounded-[28px] border border-white/10 shadow-[0_25px_60px_-40px_rgba(0,0,0,0.8)]">
            <p class="text-[10px] uppercase tracking-[0.3em] text-[var(--text-muted)]">Egresos hoy</p>
            <p id="egresos-hoy" class="mt-3 text-2xl font-black text-rose-400">$0.00</p>
        </div>
        <div class="glass-card p-5 rounded-[28px] border border-white/10 shadow-[0_25px_60px_-40px_rgba(0,0,0,0.8)]">
            <p class="text-[10px] uppercase tracking-[0.3em] text-[var(--text-muted)]">Total en caja</p>
            <p id="total-en-caja" class="mt-3 text-2xl font-black text-sky-400">$0.00</p>
        </div>
    </div>

    <div id="movimientos-caja" class="glass-card p-6 rounded-[28px] border border-white/10 shadow-[0_25px_60px_-40px_rgba(0,0,0,0.8)]">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-[10px] uppercase tracking-[0.3em] text-[var(--text-muted)]">Movimientos recientes</p>
                <h2 class="text-xl font-black text-[var(--text-color)]">Flujo de efectivo</h2>
            </div>
            <span id="cierres-pendientes" class="rounded-full bg-yellow-500/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.25em] text-yellow-200">0 pendientes</span>
        </div>
        <div id="movimientos-list" class="space-y-4">
            <p class="text-sm text-[var(--text-muted)]">Cargando movimientos...</p>
        </div>
    </div>

    <!-- FILTROS RÁPIDOS -->
    <div class="flex flex-wrap gap-2 border-b border-[var(--border-color)] pb-4">
        <button type="button" data-filter="all" class="filter-button filter-button--active">Todas</button>
        <button type="button" data-filter="ocupada" class="filter-button">Mesas Activas</button>
        <button type="button" data-filter="disponible" class="filter-button">Mesas Libres</button>
    </div>

    <!-- CUADRÍCULA DE MESAS REALES -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
        
        @forelse ($mesas as $mesa)
            
            @if($mesa->estado === 'ocupada')
                <!-- MESA OCUPADA / ACTIVA -->
                <a href="{{ route('admin.caja.cobrar', $mesa->id) }}" data-mesa-status="{{ $mesa->estado }}" class="mesa-card group relative overflow-hidden rounded-[28px] border border-blue-500/20 bg-slate-950/90 p-6 shadow-[0_25px_70px_-45px_rgba(59,130,246,0.65)] transition-all duration-300 hover:-translate-y-1 hover:border-blue-400/60 cursor-pointer">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-blue-500/15 to-transparent"></div>
                    <span class="absolute top-4 right-4 inline-flex items-center rounded-full bg-blue-500/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-blue-100 backdrop-blur-sm">Activa</span>
                    
                    <div class="relative text-center space-y-3">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-blue-500/10 text-blue-300 shadow-[0_12px_30px_-20px_rgba(56,189,248,0.8)]">
                            <i class="fas fa-couch text-2xl"></i>
                        </div>
                        <h3 class="text-[var(--text-color)] font-bold text-lg">Mesa {{ $mesa->numero }}</h3>
                        
                        <!-- TOTAL DINÁMICO -->
                        <p class="text-emerald-400 font-black text-base">${{ number_format($mesa->total_consumo, 2) }}</p>
                        
                        <!-- PRODUCTOS PENDIENTES -->
                        @if($mesa->productos && count($mesa->productos) > 0)
                            <div class="bg-[var(--card-color)]/70 rounded-3xl p-3 mt-2 border border-blue-500/15 text-left max-h-[96px] overflow-y-auto backdrop-blur-sm">
                                <p class="text-[10px] text-[var(--text-muted)] font-semibold uppercase tracking-[0.2em] mb-2">Productos</p>
                                @foreach($mesa->productos as $detalle)
                                    <div class="text-[11px] text-[var(--text-color)] py-1 border-b border-[var(--border-color)] last:border-0">
                                        <span class="font-semibold text-blue-300">{{ $detalle->cantidad }}x</span>
                                        <span class="truncate">{{ $detalle->producto->nombre ?? 'Sin nombre' }}</span>
                                        @if($detalle->notas)
                                            <p class="text-[9px] text-yellow-300 italic mt-1">{{ $detalle->notas }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        
                        <p class="text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-[0.25em] mt-2">Click para cobrar</p>
                    </div>
                </a>
            @else
                <!-- MESA VACÍA / LIBRE -->
                <div data-mesa-status="{{ $mesa->estado }}" class="mesa-card relative overflow-hidden rounded-[28px] border border-dashed border-[var(--border-color)] bg-white/5 p-6 shadow-[0_20px_55px_-45px_rgba(56,189,248,0.18)] transition-all duration-300 hover:-translate-y-1 hover:border-sky-400/40">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-sky-500/12 to-transparent"></div>
                    <span class="absolute top-4 right-4 inline-flex items-center rounded-full bg-sky-500/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-sky-100 backdrop-blur-sm">Libre</span>
                    <div class="relative text-center space-y-3">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-sky-500/10 text-sky-300 shadow-[0_12px_30px_-20px_rgba(56,189,248,0.35)]">
                            <i class="fas fa-chair text-2xl"></i>
                        </div>
                        <h3 class="text-[var(--text-color)] font-bold text-lg">Mesa {{ $mesa->numero }}</h3>
                        <p class="text-[11px] text-[var(--text-muted)] font-bold uppercase tracking-[0.32em]">Disponible</p>
                    </div>
                </div>
            @endif

        @empty
            <div class="col-span-full text-center py-10">
                <p class="text-[var(--text-muted)] font-bold uppercase tracking-widest text-xs">No tienes mesas registradas en la base de datos.</p>
            </div>
        @endforelse

    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('[data-filter]');
        const mesaCards = document.querySelectorAll('[data-mesa-status]');

        const applyFilter = (filter) => {
            mesaCards.forEach(card => {
                const status = card.dataset.mesaStatus;
                if (filter === 'all' || status === filter) {
                    card.classList.remove('hidden');
                    card.classList.add('block');
                } else {
                    card.classList.add('hidden');
                    card.classList.remove('block');
                }
            });
        };

        buttons.forEach(button => {
            button.addEventListener('click', () => {
                buttons.forEach(btn => btn.classList.remove('filter-button--active'));
                button.classList.add('filter-button--active');
                applyFilter(button.dataset.filter);
            });
        });

        applyFilter('all');
        cargarEstadisticasCaja();
        cargarMovimientosCaja();

        async function cargarEstadisticasCaja() {
            try {
                const response = await fetch('{{ route('admin.caja.api.estadisticas') }}');
                const data = await response.json();
                document.getElementById('ingresos-hoy').textContent = '$' + Number(data.ingresos_hoy || 0).toFixed(2);
                document.getElementById('egresos-hoy').textContent = '$' + Number(data.egresos_hoy || 0).toFixed(2);
                document.getElementById('total-en-caja').textContent = '$' + Number(data.total_en_caja || 0).toFixed(2);
                document.getElementById('cierres-pendientes').textContent = Number(data.cierres_pendientes || 0) + ' pendientes';
            } catch (error) {
                console.error('No se pudo cargar la estadística de caja.', error);
            }
        }

        async function cargarMovimientosCaja() {
            const contenedor = document.getElementById('movimientos-list');
            try {
                const response = await fetch('{{ route('admin.caja.api.movimientos') }}');
                const movimientos = await response.json();
                if (!Array.isArray(movimientos) || movimientos.length === 0) {
                    contenedor.innerHTML = '<p class="text-sm text-[var(--text-muted)]">No hay movimientos recientes.</p>';
                    return;
                }

                contenedor.innerHTML = movimientos.map(movimiento => `
                    <div class="rounded-3xl border border-white/10 bg-[#0b111a] p-4">
                        <div class="flex items-center justify-between gap-4 mb-2">
                            <span class="text-sm font-bold text-white">${movimiento.concepto}</span>
                            <span class="text-sm font-semibold text-emerald-400">$${Number(movimiento.monto).toFixed(2)}</span>
                        </div>
                        <p class="text-[11px] text-[var(--text-muted)]">${movimiento.tipo} • ${movimiento.estado}</p>
                        <p class="text-[11px] text-[var(--text-muted)] mt-2">${movimiento.comentarios || 'Sin comentarios'}</p>
                        <p class="text-[11px] text-[var(--text-muted)] mt-2">Método: ${movimiento.metodo_pago || 'N/A'}</p>
                        ${movimiento.referencia ? `<p class="text-[11px] text-[var(--text-muted)] mt-2">Referencia: ${movimiento.referencia}</p>` : ''}
                        ${movimiento.comprobante_url ? `<a href="${movimiento.comprobante_url}" target="_blank" rel="noopener" class="inline-flex mt-3 items-center gap-2 text-xs font-semibold uppercase tracking-[0.3em] text-cyan-300 hover:text-cyan-200">Descargar comprobante <i class="fas fa-download"></i></a>` : ''}
                    </div>
                `).join('');
            } catch (error) {
                console.error('No se pudieron cargar los movimientos de caja.', error);
                contenedor.innerHTML = '<p class="text-sm text-[var(--text-muted)]">Error cargando movimientos.</p>';
            }
        }
    });
</script>
@endpush
@endsection