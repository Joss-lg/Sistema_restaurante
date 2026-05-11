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
            <h1 class="text-4xl font-black text-white italic tracking-tighter uppercase">Mesa {{ $mesa->numero }}</h1>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mt-1">
                {{ $ordenLabel ?? 'Orden #N/A' }} • {{ $meseroNombre ?? 'Sin mesero asignado' }}
            </p>
        </div>

        {{-- Lista de productos --}}
        <div class="px-8 pb-8 space-y-4">
            @if($productos->isNotEmpty())
                @foreach($productos as $producto)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-white/5 rounded-xl flex items-center justify-center text-blue-500 font-black text-xs border border-white/5">{{ $producto->cantidad }}x</div>
                            <div>
                                <p class="text-white font-bold text-sm">{{ $producto->nombre }}</p>
                                <p class="text-[10px] text-gray-400">Precio unitario: ${{ number_format($producto->precio_unitario, 2) }}</p>
                                @if($producto->notas)
                                    <p class="text-[10px] text-gray-500 font-bold uppercase italic">{{ $producto->notas }}</p>
                                @endif
                            </div>
                        </div>
                        <span class="text-white font-black text-sm">${{ number_format($producto->precio_unitario * $producto->cantidad, 2) }}</span>
                    </div>
                @endforeach
            @else
                <div class="p-8 rounded-3xl bg-[#141417] border border-white/10 text-center">
                    <p class="text-gray-400 text-sm">No hay productos cargados para esta mesa todavía.</p>
                </div>
            @endif
        </div>

        {{-- Footer de totales --}}
        <div class="mt-auto p-8 bg-[#1a1a1e] border-t border-white/5 sticky bottom-0 lg:relative">
            <div class="space-y-2 mb-6">
                <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                    <span>Subtotal</span>
                    <span class="text-white">${{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                    <span>IVA (16%)</span>
                    <span class="text-white">${{ number_format($iva ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                    <span>Propina Sugerida</span>
                    <span class="text-white">${{ number_format($propina, 2) }}</span>
                </div>
            </div>
            <div class="flex justify-between items-end">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Total a Pagar</span>
                <span class="text-5xl font-black text-green-500 tracking-tighter leading-none">${{ number_format($totalPagar, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: TECLADO Y PAGO -->
    <div class="w-full lg:w-3/5 p-8 lg:p-12 bg-[#0f0f12]">
        <div class="max-w-md mx-auto space-y-8">
            <input id="mesa-id" type="hidden" value="{{ $mesa->id }}">
            <input id="orden-id" type="hidden" value="{{ $ordenId ?? '' }}">
            <input id="metodo-pago" type="hidden" value="Efectivo">

            <!-- Display de Efectivo (Actualizado con la clase .precio-display) -->
            <div class="bg-[#141417] border border-white/10 p-10 rounded-[2.5rem] text-center shadow-2xl overflow-hidden">
                <p class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-4 italic">Efectivo Recibido</p>
                <div class="flex justify-center w-full">
                    <span id="display-pago" class="precio-display">$0</span>
                </div>
                <div class="mt-4 text-sm text-gray-400">
                    <p>Total a pagar: <strong>${{ number_format($totalPagar, 2) }}</strong></p>
                    <p>Cambio: <strong id="display-cambio">$0</strong></p>
                </div>
                <div id="mensaje-pago" class="mt-4 hidden rounded-3xl border border-transparent bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200"></div>
            </div>

            <!-- Teclado Numérico (Funcional) -->
            <div class="grid grid-cols-4 gap-4">
                @foreach(['1','2','3','4','5','6','7','8','9','.','0','00','DEL'] as $key)
                    <button type="button" 
                            class="btn-tecla h-20 bg-[#141417] hover:bg-white/5 border border-white/5 rounded-2xl text-2xl font-black text-white transition-all active:scale-95 active:bg-blue-600/20 shadow-lg"
                            data-value="{{ $key }}">
                        {{ $key }}
                    </button>
                @endforeach
            </div>

            <!-- Botones de Acción Final -->
            <div class="grid grid-cols-2 gap-4 pb-12">
                <button type="button" id="btn-ticket" class="bg-white/5 hover:bg-white/10 text-white font-black py-5 rounded-2xl border border-white/10 transition-all uppercase text-xs tracking-widest flex items-center justify-center gap-2">
                    <i class="fas fa-print"></i> Ticket
                </button>
                <button type="button" id="btn-finalizar" class="bg-green-500 hover:bg-green-400 text-[#0f0f12] font-black py-5 rounded-2xl transition-all uppercase text-xs tracking-widest shadow-xl shadow-green-500/20">
                    Finalizar Pago
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Script de lógica del teclado optimizado --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const display = document.getElementById('display-pago');
        const displayCambio = document.getElementById('display-cambio');
        const botones = document.querySelectorAll('.btn-tecla');
        const btnFinalizar = document.getElementById('btn-finalizar');
        const mesaId = document.getElementById('mesa-id').value;
        const ordenId = document.getElementById('orden-id').value || null;
        const metodoPago = document.getElementById('metodo-pago').value;
        const totalPagar = parseFloat('{{ number_format($totalPagar, 2, '.', '') }}');
        let montoActual = "0";

        // Formateador de moneda profesional
        const formatter = new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        function actualizarVista() {
            const numero = parseFloat(montoActual) || 0;
            display.innerText = formatter.format(numero);
            const cambio = numero - totalPagar;
            displayCambio.innerText = formatter.format(Math.max(cambio, 0));
        }

        botones.forEach(boton => {
            boton.addEventListener('click', () => {
                const valor = boton.getAttribute('data-value');

                if (valor === 'DEL') {
                    montoActual = montoActual.slice(0, -1);
                    if (montoActual === "" || montoActual === "-" || montoActual === ".") montoActual = "0";
                } else if (valor === '.') {
                    if (!montoActual.includes('.')) {
                        montoActual += '.';
                    }
                } else {
                    if (montoActual.length >= 14) return;

                    if (montoActual === "0") {
                        montoActual = valor;
                    } else {
                        montoActual += valor;
                    }
                }

                actualizarVista();
            });
        });

        const mensajePago = document.getElementById('mensaje-pago');

        function mostrarMensajePago(texto, tipo = 'error') {
            mensajePago.classList.remove('hidden', 'bg-red-500/10', 'text-red-200', 'border-red-500/20', 'bg-emerald-500/10', 'text-emerald-200', 'border-emerald-500/20');
            mensajePago.classList.add(tipo === 'success' ? 'bg-emerald-500/10' : 'bg-red-500/10');
            mensajePago.classList.add(tipo === 'success' ? 'text-emerald-200' : 'text-red-200');
            mensajePago.classList.add(tipo === 'success' ? 'border-emerald-500/20' : 'border-red-500/20');
            mensajePago.textContent = texto;
        }

        function ocultarMensajePago() {
            mensajePago.classList.add('hidden');
        }

        btnFinalizar.addEventListener('click', async () => {
            const efectivo = parseFloat(montoActual) || 0;
            const importeTotal = parseFloat(totalPagar.toFixed(2));

            ocultarMensajePago();

            if (importeTotal <= 0) {
                mostrarMensajePago('Esta mesa no tiene monto a pagar.', 'error');
                return;
            }

            if (efectivo + 0.001 < importeTotal) {
                mostrarMensajePago('El efectivo recibido es menor al total a pagar.', 'error');
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('{{ route('admin.caja.api.pagar') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        mesa_id: mesaId,
                        orden_id: ordenId,
                        efectivo,
                        metodo_pago: metodoPago
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    mostrarMensajePago(data.message || 'Error al procesar el pago.', 'error');
                    return;
                }

                mostrarMensajePago(data.message + ' Cambio: $' + data.cambio, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route('admin.caja.index') }}';
                }, 1200);
            } catch (error) {
                console.error(error);
                mostrarMensajePago('Ocurrió un error al procesar el pago.', 'error');
            }
        });
        
        // Inicializar
        actualizarVista();
    });
</script>
@endsection