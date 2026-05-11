@extends('layouts.admin')

@section('title', 'Cocina | Ollintem Pro')

@section('header-title', 'Módulo de Cocina')
@section('header-subtitle', 'Órdenes enviadas desde mesero')

@section('content')
<div class="p-10 space-y-8">
    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-200 rounded-3xl px-6 py-4 shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="glass-card kitchen-card rounded-[2rem] p-8 shadow-xl">
            <h2 class="text-sm font-black uppercase tracking-[0.35em] accent-title mb-6">Órdenes activas</h2>
            <p class="text-3xl font-black text-[var(--text-color)]">{{ $ordenes->count() }}</p>
            <p class="mt-4 text-sm text-[var(--text-muted)]">Todas las órdenes que están en cocina, en proceso o servidas pero aún no han sido pagadas.</p>
        </div>

        <div class="glass-card kitchen-card rounded-[2rem] p-8 xl:col-span-2 shadow-xl">
            <h2 class="text-sm font-black uppercase tracking-[0.35em] accent-title mb-6">Resumen rápido</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-[1.75rem] bg-[var(--card-color)] border border-[var(--border-color)] p-6 shadow-sm ring-1 ring-sky-500/10">
                    <span class="text-[10px] uppercase tracking-[0.35em] text-[var(--text-muted)]">Pendientes</span>
                    <p class="mt-3 text-3xl font-black">{{ $ordenes->where('estado','pendiente')->count() }}</p>
                </div>
                <div class="rounded-[1.75rem] bg-[var(--card-color)] border border-[var(--border-color)] p-6 shadow-sm ring-1 ring-sky-500/10">
                    <span class="text-[10px] uppercase tracking-[0.35em] text-[var(--text-muted)]">En Proceso</span>
                    <p class="mt-3 text-3xl font-black">{{ $ordenes->where('estado','en proceso')->count() }}</p>
                </div>
                <div class="rounded-[1.75rem] bg-[var(--card-color)] border border-[var(--border-color)] p-6 md:col-span-2 shadow-sm ring-1 ring-sky-500/10">
                    <span class="text-[10px] uppercase tracking-[0.35em] text-[var(--text-muted)]">Servidas</span>
                    <p class="mt-3 text-3xl font-black">{{ $ordenes->where('estado','servida')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($ordenes->isEmpty())
        <div class="glass-card kitchen-card rounded-[2rem] p-10 text-center border border-[var(--border-color)] shadow-xl">
            <p class="text-lg font-bold text-[var(--text-color)]">No hay órdenes en cocina por el momento.</p>
            <p class="mt-3 text-sm text-[var(--text-muted)]">Cuando un mesero envíe una orden, aparecerá aquí automáticamente.</p>
        </div>
    @else
        <div class="grid gap-6 xl:grid-cols-2">
            @foreach($ordenes as $orden)
                <article class="glass-card kitchen-card rounded-[2rem] border border-[var(--border-color)] p-8 shadow-[0_25px_50px_rgba(0,0,0,0.15)]">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <span class="text-[10px] uppercase tracking-[0.35em] text-[var(--text-muted)]">Orden #{{ $orden->numero_orden }}</span>
                            <h3 class="mt-3 text-2xl font-black text-[var(--text-color)]">Mesa {{ $orden->mesa->numero ?? 'N/A' }}</h3>
                            <p class="mt-2 text-sm text-[var(--text-muted)]">Mesero: {{ $orden->mesero->nombre ?? 'Sin mesero' }}</p>
                        </div>
                        <div class="rounded-3xl px-5 py-4 text-center border border-[var(--border-color)] bg-[var(--bg-color)]">
                            <span class="block text-[10px] uppercase tracking-[0.35em] text-[var(--text-muted)]">Estado</span>
                            <span class="mt-2 inline-flex rounded-full px-4 py-2 text-xs font-black uppercase tracking-[0.2em] kitchen-pill {{ $orden->estado === 'pendiente' ? 'bg-yellow-500/10 text-amber-300' : ($orden->estado === 'en proceso' ? 'bg-blue-500/10 text-sky-300' : 'bg-emerald-500/10 text-emerald-300') }}">{{ ucfirst($orden->estado) }}</span>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div class="rounded-3xl bg-[var(--card-color)] border border-[var(--border-color)] p-5">
                            <p class="text-[11px] uppercase tracking-[0.35em] text-[var(--text-muted)]">Total</p>
                            <p class="mt-2 text-3xl font-black text-[var(--text-color)]">${{ number_format($orden->total, 2) }}</p>
                            <p class="mt-2 text-xs text-[var(--text-muted)]">Incluye IVA y propina si aplica.</p>
                        </div>
                        <div class="rounded-3xl bg-[var(--card-color)] border border-[var(--border-color)] p-5">
                            <p class="text-[11px] uppercase tracking-[0.35em] text-[var(--text-muted)]">Creada</p>
                            <p class="mt-2 text-sm font-bold text-[var(--text-color)]">{{ $orden->abierta_el ? $orden->abierta_el->format('d/m/Y H:i') : 'Sin fecha' }}</p>
                            <p class="mt-2 text-xs text-[var(--text-muted)]">Última actualización: {{ $orden->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h4 class="text-sm font-black uppercase tracking-[0.35em] text-[var(--text-muted)] mb-4">Productos</h4>
                        <div class="space-y-3">
                            @foreach($orden->detalles as $detalle)
                                <div class="rounded-3xl product-item border border-[var(--border-color)] p-4 flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-bold text-[var(--text-color)]">{{ $detalle->producto->nombre ?? 'Producto' }}</p>
                                        <p class="mt-1 text-[11px] text-[var(--text-muted)]">Cantidad: {{ $detalle->cantidad }} · ${{ number_format($detalle->precio_unitario, 2) }}</p>
                                        @if($detalle->notas)
                                            <p class="mt-2 text-[10px] uppercase tracking-[0.2em] text-[#9ca3af]">Notas: {{ $detalle->notas }}</p>
                                        @endif
                                    </div>
                                    <span class="text-sm font-black text-[var(--text-color)]">${{ number_format($detalle->subtotal ?? ($detalle->cantidad * $detalle->precio_unitario), 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <form action="{{ route('admin.cocina.orden.estado', $orden->id) }}" method="POST" class="mt-8">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="{{ $orden->estado === 'pendiente' ? 'en proceso' : ($orden->estado === 'en proceso' ? 'servida' : 'servida') }}">
                        <button type="submit" class="w-full rounded-3xl py-4 text-sm font-black uppercase tracking-[0.25em] transition-all {{ $orden->estado === 'pendiente' ? 'bg-blue-500 text-white hover:bg-blue-400' : ($orden->estado === 'en proceso' ? 'bg-emerald-500 text-white hover:bg-emerald-400' : 'bg-slate-600 text-white cursor-not-allowed opacity-60') }}" {{ $orden->estado === 'servida' ? 'disabled' : '' }}>
                            {{ $orden->estado === 'pendiente' ? 'Marcar en proceso' : ($orden->estado === 'en proceso' ? 'Marcar servida' : 'Orden servida') }}
                        </button>
                    </form>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
