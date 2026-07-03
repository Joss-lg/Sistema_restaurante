@extends('layouts.admin')

@section('title', 'Cobrar Mesa | Ollintem Pro')
@section('no-sidebar', 'true')

@section('content')
<div class="flex flex-col lg:flex-row h-screen overflow-hidden bg-[#0f0f12]">
    
    {{-- IZQUIERDA: Detalle --}}
    <div class="w-full lg:w-2/5 border-r border-white/5 bg-[#141417] flex flex-col border-b lg:border-b-0 overflow-hidden">
        <div class="p-8">
            <a href="{{ route('admin.caja.index') }}" class="text-gray-500 hover:text-white text-[10px] font-black flex items-center gap-2 mb-6 transition-all hover:translate-x-1">
                <i class="fas fa-arrow-left"></i> VOLVER A CAJA
            </a>
            <h1 class="text-4xl font-black text-white italic tracking-tighter uppercase">Mesa {{ $mesa->numero }}</h1>
            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mt-1">
                {{ $orden->numero_orden ?? 'ORDEN SIN NÚMERO' }} • {{ $orden->mesero->name ?? 'MESERO NO ASIGNADO' }}
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
<script src="{{ asset('js/cobro.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Inicialización de contexto global para evitar errores
        window.COBRO_CONFIG = {
            mesaId: {{ $mesa->id }},
            ordenId: {{ $orden->id ?? 0 }},
            total: parseFloat("{{ $orden->total ?? 0 }}"),
            csrfToken: "{{ csrf_token() }}"
        };

        // Verificación de seguridad básica
        if (typeof CobroManager !== 'undefined') {
            CobroManager.init(window.COBRO_CONFIG);
        } else {
            console.error("CobroManager no cargado. Revisa cobro.js");
        }
    });
</script>
@endpush