@extends('layouts.admin')

@section('title', 'Cobrar Mesa | Ollintem Pro')
@section('no-sidebar', 'true')

@section('content')
<div class="flex flex-col lg:flex-row min-h-screen lg:h-screen lg:overflow-hidden bg-zinc-100 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
    
    {{-- IZQUIERDA: Detalle (2/5 del ancho) --}}
    <div class="w-full lg:w-2/5 border-r border-zinc-200 dark:border-white/10 bg-white dark:bg-zinc-900 flex flex-col border-b lg:border-b-0 lg:overflow-hidden shadow-sm">
        <div class="p-5 sm:p-8 border-b border-zinc-200 dark:border-white/10">
            <a href="{{ route('admin.caja.index') }}" class="text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white text-[10px] font-black flex items-center gap-2 mb-2 transition-all hover:translate-x-1 uppercase tracking-widest">
                <i class="fas fa-arrow-left"></i> VOLVER A CAJA
            </a>
            
            <h1 class="text-3xl sm:text-5xl font-black text-zinc-900 dark:text-white italic tracking-tighter uppercase break-words">
                Mesa {{ $mesa->numero }}
            </h1>
            
            <p class="text-xs sm:text-sm font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mt-1">
                {{ $orden->numero_orden ?? 'ORDEN SIN NÚMERO' }} • {{ $orden->mesero->nombre ?? 'MESERO NO ASIGNADO' }}
            </p>
        </div>

        <div class="flex-1 lg:overflow-y-auto custom-scrollbar p-5 sm:p-8">
            @include('admin.cobrar.partials.detalle-cuenta')
        </div>
    </div>

    {{-- DERECHA: Pago (3/5 del ancho renderizado directamente desde el partial) --}}
    @include('admin.cobrar.partials.panel-pago')
</div>

{{-- Modales integrados --}}
@include('admin.cobrar.modals.metodo-pago')
@include('admin.cobrar.modals.exito')
@include('admin.cobrar.modals.error')
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
            urlPago: "{{ route('admin.caja.procesar-pago') }}" 
        };
    });
</script>
@endpush