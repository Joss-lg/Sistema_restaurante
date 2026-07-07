@extends('layouts.admin')

@section('title', 'Cobrar Mesa | Ollintem Pro')
@section('no-sidebar', 'true')

@section('content')
<div class="flex flex-col lg:flex-row h-screen overflow-hidden bg-[#0f0f12]">
    
    {{-- IZQUIERDA: Detalle --}}
    <div class="w-full lg:w-2/5 border-r border-white/5 bg-[#141417] flex flex-col border-b lg:border-b-0 overflow-hidden">
            <div class="p-8">
                <a href="{{ route('admin.caja.index') }}" class="text-gray-500 hover:text-white text-[10px] font-black flex items-center gap-2 mb-2 transition-all hover:translate-x-1">
                    <i class="fas fa-arrow-left"></i> VOLVER A CAJA
                </a>
                
                {{-- Texto agrandado aquí --}}
                <h1 class="text-5xl font-black text-white italic tracking-tighter uppercase">Mesa {{ $mesa->numero }}</h1>
                
                {{-- Texto agrandado y más visible aquí --}}
                <p class="text-lg font-bold text-gray-400 uppercase tracking-wide mt-2">
                    {{ $orden->numero_orden ?? 'ORDEN SIN NÚMERO' }} • {{ $orden->mesero->nombre ?? 'MESERO NO ASIGNADO' }}
                </p>
            </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
            {{-- Cambiado a la ruta de administración corregida --}}
            @include('admin.cobrar.partials.detalle-cuenta')
        </div>
    </div>

    {{-- DERECHA: Pago --}}
    {{-- Cambiado a la ruta de administración corregida --}}
    @include('admin.cobrar.partials.panel-pago')
</div>

{{-- Modales con rutas de administración corregidas --}}
@include('admin.cobrar.modals.metodo-pago')
@include('admin.cobrar.modals.promociones')
@include('admin.cobrar.modals.exito')
@include('admin.cobrar.modals.error')
@endsection

@push('scripts')
@vite(['resources/js/cobro.js'])
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.COBRO_CONFIG = {
            mesaId: {{ $mesa->id }},
            ordenId: {{ $ordenes->first()->id ?? 0 }},
            total: {{ $totalPagar ?? 0 }},
            csrfToken: "{{ csrf_token() }}",
            urlPago: "{{ route('admin.caja.procesar.pago.final') }}"
        };
    });
</script>
@endpush


