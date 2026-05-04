@extends('layouts.admin')

@section('title', 'Cobrar Mesa | Ollintem Pro')

@section('content')
{{-- Contenedor principal con scroll natural --}}
<div class="flex flex-col lg:flex-row min-h-screen bg-[#0f0f12]">
    
    <!-- COLUMNA IZQUIERDA: DETALLE DE LA ORDEN -->
    <div class="w-full lg:w-2/5 border-r border-white/5 bg-[#141417] flex flex-col border-b lg:border-b-0">
        <div class="p-8">
            <a href="{{ route('admin.caja.index') }}" class="text-gray-500 hover:text-white text-xs font-bold flex items-center gap-2 mb-6 transition-colors">
                <i class="fas fa-arrow-left"></i> VOLVER A MESAS
            </a>
            <h1 class="text-4xl font-black text-white italic tracking-tighter uppercase">Mesa 4T4</h1>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mt-1">Orden #12345 • Juan Mesero</p>
        </div>

        {{-- Lista de productos --}}
        <div class="px-8 pb-8 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-white/5 rounded-xl flex items-center justify-center text-blue-500 font-black text-xs border border-white/5">1x</div>
                    <div>
                        <p class="text-white font-bold text-sm">Pizza Margarita</p>
                        <p class="text-[10px] text-gray-500 font-bold uppercase italic">Grande • Extra queso</p>
                    </div>
                </div>
                <span class="text-white font-black text-sm">$245.00</span>
            </div>
        </div>

        {{-- Footer de totales --}}
        <div class="mt-auto p-8 bg-[#1a1a1e] border-t border-white/5 sticky bottom-0 lg:relative">
            <div class="space-y-2 mb-6">
                <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                    <span>Subtotal</span>
                    <span class="text-white">$520.00</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                    <span>Propina Sugerida (10%)</span>
                    <span class="text-white">$52.00</span>
                </div>
            </div>
            <div class="flex justify-between items-end">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Total a Pagar</span>
                <span class="text-5xl font-black text-green-500 tracking-tighter leading-none">$572.00</span>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: TECLADO Y PAGO -->
    <div class="w-full lg:w-3/5 p-8 lg:p-12 bg-[#0f0f12]">
        <div class="max-w-md mx-auto space-y-8">
            
            <!-- Display de Efectivo (Vinculado al script) -->
            <div class="bg-[#141417] border border-white/10 p-10 rounded-[2.5rem] text-center shadow-2xl">
                <p class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-4 italic">Efectivo Recibido</p>
                <span id="display-pago" class="text-7xl font-black text-white tracking-tighter">$0</span>
            </div>

            <!-- Teclado Numérico (Funcional) -->
            <div class="grid grid-cols-3 gap-4">
                @foreach(['1','2','3','4','5','6','7','8','9','0','00','DEL'] as $key)
                    <button type="button" 
                            class="btn-tecla h-20 bg-[#141417] hover:bg-white/5 border border-white/5 rounded-2xl text-2xl font-black text-white transition-all active:scale-95 active:bg-blue-600/20 shadow-lg"
                            data-value="{{ $key }}">
                        {{ $key }}
                    </button>
                @endforeach
            </div>

            <!-- Botones de Acción Final -->
            <div class="grid grid-cols-2 gap-4 pb-12">
                <button class="bg-white/5 hover:bg-white/10 text-white font-black py-5 rounded-2xl border border-white/10 transition-all uppercase text-xs tracking-widest flex items-center justify-center gap-2">
                    <i class="fas fa-print"></i> Ticket
                </button>
                <button class="bg-green-500 hover:bg-green-400 text-[#0f0f12] font-black py-5 rounded-2xl transition-all uppercase text-xs tracking-widest shadow-xl shadow-green-500/20">
                    Finalizar Pago
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Script de lógica del teclado --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const display = document.getElementById('display-pago');
        const botones = document.querySelectorAll('.btn-tecla');
        let montoActual = "0";

        botones.forEach(boton => {
            boton.addEventListener('click', () => {
                const valor = boton.getAttribute('data-value');

                if (valor === 'DEL') {
                    // Borrar el último dígito
                    montoActual = montoActual.slice(0, -1);
                    if (montoActual === "") montoActual = "0";
                } else {
                    // Evitar ceros extra a la izquierda
                    if (montoActual === "0") {
                        montoActual = valor;
                    } else {
                        montoActual += valor;
                    }
                }

                // Actualizar la vista del display
                display.innerText = '$' + montoActual;
            });
        });
    });
</script>
@endsection