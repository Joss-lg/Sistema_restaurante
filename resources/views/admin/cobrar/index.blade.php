@extends('layouts.admin')

@section('title', 'Cobrar Mesa | Ollintem Pro')
@section('no-sidebar', 'true')

@section('content')
@php
    // Cuando se divide "por consumo" los contadores +/- por persona
    // necesitan más ancho horizontal, así que le damos más espacio al
    // panel de la izquierda en pantallas grandes.
    $esPorProducto = ($division['tipo'] ?? null) === 'por_producto';
    $anchoIzquierda = $esPorProducto ? 'lg:w-3/5' : 'lg:w-2/5';
    $anchoDerecha   = $esPorProducto ? 'lg:w-2/5' : 'lg:w-3/5';
@endphp
<div class="flex flex-col lg:flex-row min-h-screen lg:h-screen lg:overflow-hidden bg-zinc-100 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
    
    {{-- IZQUIERDA: Detalle --}}
    <div class="w-full {{ $anchoIzquierda }} border-r border-zinc-200 dark:border-white/10 bg-white dark:bg-zinc-900 flex flex-col border-b lg:border-b-0 lg:overflow-hidden lg:min-h-0 shadow-sm">
        <div class="p-4 sm:p-5 border-b border-zinc-200 dark:border-white/10">
            <a href="{{ route('admin.caja.index') }}" class="text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white text-[10px] font-black flex items-center gap-2 mb-1 transition-all hover:translate-x-1 uppercase tracking-widest">
                <i class="fas fa-arrow-left"></i> VOLVER A CAJA
            </a>
            
            <h1 class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white italic tracking-tighter uppercase break-words">
                Mesa {{ $mesa->numero }}
            </h1>
            
            <p class="text-[11px] sm:text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mt-0.5">
                {{ $orden->numero_orden ?? 'ORDEN SIN NÚMERO' }} • {{ $orden->mesero->nombre ?? 'MESERO NO ASIGNADO' }}
            </p>
        </div>

        <div class="flex-1 lg:min-h-0 lg:overflow-y-auto custom-scrollbar">
            @include('admin.cobrar.partials.detalle-cuenta')
        </div>
    </div>

    {{-- DERECHA: Pago --}}
    @include('admin.cobrar.partials.panel-pago')
</div>

{{-- Modales integrados --}}
@include('admin.cobrar.modals.metodo-pago')
@include('admin.cobrar.modals.exito')
@include('admin.cobrar.modals.error')
@include('admin.cobrar.modals.ticket-preview')
@endsection

@push('scripts')
@vite(['resources/js/cobro.js'])
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.COBRO_CONFIG = {
            mesaId: {{ $mesa->id }},
            // AJUSTE: se deja de mandar ordenId (solo tomaba la primera orden
            // de la mesa y se perdían productos/total si había más de una).
            // El ticket ahora se imprime por MESA completa, agregando todas
            // sus órdenes activas — la misma unidad que ya usa el desglose
            // que ves en pantalla (subtotal, IVA, propina, total).
            urlTicket: "{{ route('admin.caja.ticket.imprimir', $mesa->id) }}",
            total: {{ $totalPagar ?? 0 }},
            csrfToken: "{{ csrf_token() }}",
            urlPago: "{{ route('admin.caja.procesar-pago') }}",
            // NUEVO: endpoints y datos para la división de cuenta
            urlDivisionIniciar: "{{ route('admin.caja.division.iniciar') }}",
            urlDivisionAsignar: "{{ route('admin.caja.division.asignar') }}",
            urlDivisionCancelar: "{{ route('admin.caja.division.cancelar') }}",
            division: @json($division ?? null)
        };
    });
</script>
@endpush