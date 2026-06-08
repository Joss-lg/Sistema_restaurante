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

<div class="p-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-8 relative z-10">
    
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">
        <div class="space-y-4 max-w-2xl">
            <div class="inline-flex items-center gap-2 rounded-full bg-blue-500/10 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.35em] text-blue-500 shadow-inner">
                <span class="h-2.5 w-2.5 rounded-full bg-blue-500 animate-pulse shadow-[0_0_8px_rgba(59,130,246,0.8)]"></span>
                Panel Financiero
            </div>
            <h1 class="text-3xl font-black text-[var(--text-color)] tracking-tight drop-shadow-sm">Panel de Caja</h1>
            <p class="text-sm text-[var(--text-muted)] font-medium">Control en tiempo real del flujo de efectivo, mesas activas y facturación.</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full xl:w-auto">
            <div class="glass-card p-5 rounded-3xl relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 text-emerald-500/10 transition-transform group-hover:scale-110"><i class="fas fa-wallet text-6xl"></i></div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-[var(--text-muted)] relative z-10">Total abierto</p>
                <p class="mt-2 text-2xl font-black bg-clip-text text-transparent bg-gradient-to-r from-emerald-400 to-teal-500 relative z-10">${{ number_format($totalAbierto ?? 0, 2) }}</p>
            </div>
            <div class="glass-card p-5 rounded-3xl relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 text-blue-500/10 transition-transform group-hover:scale-110"><i class="fas fa-chair text-6xl"></i></div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-[var(--text-muted)] relative z-10">Mesas activas</p>
                <p class="mt-2 text-2xl font-black text-[var(--text-color)] relative z-10">{{ $mesasActivas ?? 0 }}</p>
            </div>
            <div class="glass-card p-5 rounded-3xl relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 text-slate-500/10 transition-transform group-hover:scale-110"><i class="fas fa-check-circle text-6xl"></i></div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-[var(--text-muted)] relative z-10">Mesas libres</p>
                <p class="mt-2 text-2xl font-black text-[var(--text-muted)] relative z-10">{{ $mesasLibres }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="glass-card p-6 rounded-3xl flex flex-col justify-between relative overflow-hidden">
            <div class="flex justify-between items-start">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[var(--text-muted)]">Ingresos hoy</p>
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-500"><i class="fas fa-arrow-trend-up"></i></span>
            </div>
            <p id="ingresos-hoy" class="mt-4 text-3xl font-black text-[var(--text-color)] tracking-tight">$0.00</p>
        </div>

        <div class="glass-card p-6 rounded-3xl flex flex-col justify-between relative overflow-hidden">
            <div class="flex justify-between items-start">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[var(--text-muted)]">Egresos hoy</p>
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-500/10 text-rose-500"><i class="fas fa-arrow-trend-down"></i></span>
            </div>
            <p id="egresos-hoy" class="mt-4 text-3xl font-black text-[var(--text-color)] tracking-tight">$0.00</p>
        </div>

        <div class="glass-card p-6 rounded-3xl flex flex-col justify-between relative overflow-hidden border-blue-500/30">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent pointer-events-none"></div>
            <div class="flex justify-between items-start relative z-10">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-500">Total en caja</p>
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-500 text-white shadow-lg shadow-blue-500/30"><i class="fas fa-vault"></i></span>
            </div>
            <p id="total-en-caja" class="mt-4 text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-indigo-500 tracking-tight relative z-10">$0.00</p>
        </div>
    </div>

    <div class="space-y-6">
            
            <div class="glass-card rounded-2xl p-2 flex flex-wrap gap-2 w-fit">
                <button type="button" data-filter="all" class="filter-button filter-button--active rounded-xl">Todas las mesas</button>
                <button type="button" data-filter="ocupada" class="filter-button rounded-xl flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Activas</button>
                <button type="button" data-filter="disponible" class="filter-button rounded-xl flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-[var(--text-muted)]"></span> Libres</button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($mesas as $mesa)
                    @if($mesa->estado === 'ocupada')
                        <a href="{{ route('admin.caja.cobrar', $mesa->id) }}" data-mesa-status="{{ $mesa->estado }}" class="mesa-card group relative overflow-hidden rounded-3xl border border-blue-500/30 glass-card p-6 transition-all duration-300 hover:-translate-y-1 hover:border-blue-400 hover:shadow-[0_15px_30px_-10px_rgba(59,130,246,0.3)] cursor-pointer">
                            <div class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-blue-500/10 to-transparent"></div>
                            
                            <div class="flex justify-between items-start mb-4 relative z-10">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-500 shadow-inner border border-blue-500/20">
                                    <i class="fas fa-couch text-xl"></i>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-blue-500/10 border border-blue-500/20 px-3 py-1 text-[9px] font-black uppercase tracking-widest text-blue-500 backdrop-blur-sm">En uso</span>
                            </div>
                            
                            <div class="relative z-10">
                                <h3 class="text-[var(--text-color)] font-black text-xl tracking-tight">Mesa {{ $mesa->numero }}</h3>
                                <p class="bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-indigo-500 font-black text-2xl mt-1">${{ number_format($mesa->total_consumo, 2) }}</p>
                            </div>
                            
                            @if($mesa->productos && count($mesa->productos) > 0)
                                <div class="bg-[var(--input-bg)] rounded-2xl p-3 mt-4 border border-[var(--border-color)] text-left max-h-[110px] overflow-y-auto relative z-10">
                                    <p class="text-[9px] text-[var(--text-muted)] font-black uppercase tracking-widest mb-2 px-1">Comanda actual</p>
                                    @foreach($mesa->productos as $detalle)
                                        <div class="flex items-start justify-between py-1.5 border-b border-[var(--border-color)] last:border-0 px-1">
                                            <div class="flex gap-2 overflow-hidden">
                                                <span class="font-bold text-blue-500 text-[11px]">{{ $detalle->cantidad }}x</span>
                                                <div class="flex flex-col truncate">
                                                    <span class="text-[11px] font-medium text-[var(--text-color)] truncate">{{ $detalle->producto->nombre ?? 'Sin nombre' }}</span>
                                                    @if($detalle->notas)
                                                        <span class="text-[9px] text-orange-400 italic truncate">{{ $detalle->notas }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            <div class="mt-4 flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-blue-500 group-hover:text-blue-400 relative z-10">
                                <span>Cobrar mesa</span>
                                <i class="fas fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                            </div>
                        </a>
                    @else
                        <div data-mesa-status="{{ $mesa->estado }}" class="mesa-card relative overflow-hidden rounded-3xl border border-dashed border-[var(--border-color)] bg-[var(--card-color)] p-6 transition-all duration-300 opacity-60 hover:opacity-100 flex flex-col justify-between min-h-[220px]">
                            <div class="flex justify-between items-start relative z-10">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[var(--input-bg)] text-[var(--text-muted)] border border-[var(--border-color)]">
                                    <i class="fas fa-chair text-xl"></i>
                                </div>
                                <span class="inline-flex items-center rounded-full bg-[var(--input-bg)] border border-[var(--border-color)] px-3 py-1 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">Libre</span>
                            </div>
                            
                            <div class="relative z-10 mt-auto">
                                <h3 class="text-[var(--text-color)] font-black text-xl tracking-tight">Mesa {{ $mesa->numero }}</h3>
                                <p class="text-[11px] text-[var(--text-muted)] font-bold uppercase tracking-widest mt-1">Disponible para asginar</p>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="col-span-full glass-card rounded-3xl flex flex-col items-center justify-center py-16 border border-[var(--border-color)]">
                        <div class="w-16 h-16 bg-[var(--input-bg)] rounded-full flex items-center justify-center text-[var(--text-muted)] mb-4">
                            <i class="fas fa-store-slash text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--text-color)]">Sin mesas configuradas</h3>
                        <p class="text-sm text-[var(--text-muted)] mt-1">Ve al módulo de mesas para agregar tu distribución.</p>
                    </div>
                @endforelse
            </div>
        </div>
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
                    card.style.display = 'flex'; // Usamos flex porque las tarjetas están configuradas como flex col
                    setTimeout(() => { card.style.opacity = card.dataset.mesaStatus === 'disponible' ? '0.6' : '1'; }, 10);
                } else {
                    card.style.opacity = '0';
                    setTimeout(() => { card.style.display = 'none'; }, 300);
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
        // cargarMovimientosCaja(); - Removido con el flujo de efectivo

        async function cargarEstadisticasCaja() {
            try {
                const response = await fetch('{{ route('admin.caja.api.estadisticas') }}');
                const data = await response.json();
                document.getElementById('ingresos-hoy').textContent = '$' + Number(data.ingresos_hoy || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('egresos-hoy').textContent = '$' + Number(data.egresos_hoy || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                document.getElementById('total-en-caja').textContent = '$' + Number(data.total_en_caja || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
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
                    contenedor.innerHTML = `
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <i class="fas fa-receipt text-3xl text-[var(--text-muted)] opacity-50 mb-3"></i>
                            <p class="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">No hay movimientos hoy</p>
                        </div>`;
                    return;
                }

                // Generación de lista estilo Fintech
                contenedor.innerHTML = movimientos.map(movimiento => {
                    const isIngreso = movimiento.tipo.toLowerCase() === 'ingreso';
                    const iconClass = isIngreso ? 'fa-arrow-down' : 'fa-arrow-up';
                    const colorClass = isIngreso ? 'text-emerald-500 bg-emerald-500/10' : 'text-rose-500 bg-rose-500/10';
                    const amountColor = isIngreso ? 'text-emerald-500' : 'text-[var(--text-color)]';
                    const sign = isIngreso ? '+' : '-';

                    return `
                    <div class="group relative overflow-hidden rounded-2xl border border-[var(--border-color)] bg-[var(--card-color)] p-4 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full ${colorClass}">
                                    <i class="fas ${iconClass} text-sm"></i>
                                </div>
                                <div class="flex flex-col truncate">
                                    <h4 class="text-sm font-bold text-[var(--text-color)] truncate">${movimiento.concepto}</h4>
                                    <p class="text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-wider mt-0.5">${movimiento.metodo_pago || 'Efectivo'} • ${movimiento.estado}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-base font-black ${amountColor}">${sign}$${Number(movimiento.monto).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                            </div>
                        </div>
                        ${movimiento.comentarios ? `<p class="text-[11px] text-[var(--text-muted)] mt-3 pt-2 border-t border-[var(--border-color)] italic">"${movimiento.comentarios}"</p>` : ''}
                        ${movimiento.comprobante_url ? `
                            <a href="${movimiento.comprobante_url}" target="_blank" rel="noopener" class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--input-bg)] py-2 text-[10px] font-bold uppercase tracking-widest text-[var(--text-color)] transition-colors hover:bg-blue-500/10 hover:text-blue-500">
                                Ver recibo <i class="fas fa-external-link-alt"></i>
                            </a>
                        ` : ''}
                    </div>
                `}).join('');
            } catch (error) {
                console.error('No se pudieron cargar los movimientos.', error);
                contenedor.innerHTML = '<p class="text-sm text-rose-500 text-center py-4 font-bold">Error cargando historial.</p>';
            }
        }
    });
</script>
@endpush
@endsection