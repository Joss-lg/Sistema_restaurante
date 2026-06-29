@extends('layouts.admin')

@section('title', 'Cobrar Mesa | Ollintem Pro')
@section('no-sidebar', 'true')

@section('content')
{{-- Contenedor principal --}}
<div class="flex flex-col lg:flex-row h-screen overflow-hidden bg-[#0f0f12]">
    
    {{-- LADO IZQUIERDO: Detalle de la cuenta --}}
    <div class="w-full lg:w-2/5 border-r border-white/5 bg-[#141417] flex flex-col border-b lg:border-b-0 overflow-hidden">
        <div class="p-8">
            <a href="{{ route('admin.caja.index') }}" class="text-gray-500 hover:text-white text-xs font-bold flex items-center gap-2 mb-6 transition-colors">
                <i class="fas fa-arrow-left"></i> VOLVER A MESAS
            </a>
            <h1 class="text-4xl font-black text-white italic tracking-tighter uppercase">Mesa {{ $mesa->numero }}</h1>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mt-1">
                {{ $ordenLabel ?? 'Orden #'.($orden->numero_orden ?? 'N/A') }} • {{ $meseroNombre ?? ($orden->mesero->name ?? 'Sin mesero asignado') }}
            </p>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
            @include('cobrar.partials.detalle-cuenta')
        </div>
    </div>

    {{-- LADO DERECHO: Panel de Pago --}}
    @include('cobrar.partials.panel-pago')

</div>

{{-- Modales --}}
@include('cobrar.modals.metodo-pago')
@include('cobrar.modals.promociones')
@include('cobrar.modals.exito')
@include('cobrar.modals.error')
@endsection

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #141417; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
</style>
@endpush

@push('scripts')
{{-- Primero cargamos la lógica externa --}}
<script src="{{ asset('js/cobro.js') }}"></script>

{{-- Luego la lógica específica de esta vista --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        console.log("Interfaz de cobro inicializada para Mesa ID: {{ $mesa->id }}");
        
        // Aquí puedes manejar variables de Blade hacia JS de forma segura
        window.MESA_DATA = {
            id: {{ $mesa->id }},
            total: {{ $orden->total ?? 0 }}
        };
    });
</script>
@endpush