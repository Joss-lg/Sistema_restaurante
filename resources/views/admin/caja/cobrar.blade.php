@extends('layouts.admin')

@section('title', 'Cobrar Mesa | Ollintem Pro')

@section('content')
{{-- Contenedor principal con scroll natural --}}
<div class="flex flex-col lg:flex-row min-h-screen bg-[#0f0f12]">
    
    <div class="w-full lg:w-2/5 border-r border-white/5 bg-[#141417] flex flex-col border-b lg:border-b-0">
        <div class="p-8">
            <a href="{{ route('admin.caja.index') }}" class="text-gray-500 hover:text-white text-xs font-bold flex items-center gap-2 mb-6 transition-colors">
                <i class="fas fa-arrow-left"></i> VOLVER A MESAS
            </a>
            <h1 class="text-4xl font-black text-white italic tracking-tighter uppercase">Mesa {{ $mesa->numero }}</h1>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-widest mt-1">
                {{ $ordenLabel ?? 'Orden #'.($orden->numero_orden ?? 'N/A') }} • {{ $meseroNombre ?? ($orden->mesero->name ?? 'Sin mesero asignado') }}
            </p>

            {{-- Lógica de Cuenta Dividida --}}
            @php
                $esDividida = $cuentasDivididas || ($orden->cuenta_dividida ?? false);
                $totalPartes = $totalCuentasDivision ?? ($orden->numero_cuenta_division ?? 1);
            @endphp

            @if($esDividida)
                <div class="mt-4 p-4 bg-blue-500/10 border border-blue-500/20 rounded-2xl">
                    <p class="text-blue-400 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 mb-1">
                        <i class="fas fa-users"></i> Cuenta Dividida
                    </p>
                    <p class="text-white text-sm font-bold">
                        Dividida entre {{ $totalPartes }} personas
                    </p>
                    <p class="text-gray-400 text-[10px] uppercase mt-1 font-bold">
                        Pago por persona: <span class="text-green-400">${{ number_format($totalPagar / $totalPartes, 2) }}</span>
                    </p>
                </div>
            @else
                <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mt-2">
                    Personas: {{ $mesa->capacidad ?? 'N/A' }}
                </p>
            @endif
        </div>

        @if($cuentasDivididas)
            {{-- Selector de cuentas divididas --}}
            <div class="px-8 pb-4">
                <div class="flex gap-2 overflow-x-auto pb-2">
                    @foreach($cuentasDividadasInfo as $cuenta)
                        <button 
                            type="button" 
                            class="btn-cuenta px-4 py-2 rounded-lg font-bold text-xs whitespace-nowrap transition-all"
                            data-cuenta="{{ $cuenta['numero_cuenta'] }}"
                            data-orden="{{ $cuenta['orden_id'] }}"
                            style="background-color: #141417; border: 2px solid #3B82F6; color: white;">
                            Cuenta {{ $cuenta['numero_cuenta'] }} - ${{ number_format($cuenta['total'], 2) }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Lista de productos --}}
        <div class="px-8 pb-8 space-y-4" id="productos-container">
            @if($cuentasDivididas)
                {{-- Para cuentas divididas, mostrar productos de la primera cuenta por defecto --}}
                <div id="cuenta-1">
                    @if($cuentasDividadasInfo[0]['productos']->isNotEmpty())
                        @foreach($cuentasDividadasInfo[0]['productos'] as $producto)
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
                            <p class="text-gray-400 text-sm">No hay productos en esta cuenta.</p>
                        </div>
                    @endif
                </div>
                {{-- Contenedores ocultos para otras cuentas --}}
                @foreach($cuentasDividadasInfo as $cuenta)
                    @if($cuenta['numero_cuenta'] !== 1)
                        <div id="cuenta-{{ $cuenta['numero_cuenta'] }}" class="hidden">
                            @if($cuenta['productos']->isNotEmpty())
                                @foreach($cuenta['productos'] as $producto)
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
                                    <p class="text-gray-400 text-sm">No hay productos en esta cuenta.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            @else
                {{-- Para cuentas normales --}}
                @if(isset($productos) && $productos->isNotEmpty())
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
            @endif
        </div>

        {{-- Footer de totales --}}
        <div class="mt-auto p-8 bg-[#1a1a1e] border-t border-white/5 sticky bottom-0 lg:relative">
            <div class="space-y-2 mb-6" id="totales-container">
                @if($cuentasDivididas)
                    {{-- Mostrar totales de la primera cuenta por defecto --}}
                    <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                        <span>Subtotal</span>
                        <span class="text-white" id="total-subtotal">${{ number_format($cuentasDividadasInfo[0]['subtotal'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                        <span>IVA (16%)</span>
                        <span class="text-white" id="total-iva">${{ number_format($cuentasDividadasInfo[0]['iva'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                        <span>Propina Sugerida</span>
                        <span class="text-white" id="total-propina">${{ number_format($cuentasDividadasInfo[0]['propina'], 2) }}</span>
                    </div>
                    <input type="hidden" id="cuenta-actual" value="1">
                    <input type="hidden" id="totales-divididas" value="{{ json_encode($cuentasDividadasInfo) }}">
                @else
                    <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                        <span>Subtotal</span>
                        <span class="text-white">${{ number_format($subtotal ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                        <span>IVA (16%)</span>
                        <span class="text-white">${{ number_format($iva ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-gray-500 uppercase">
                        <span>Propina Sugerida</span>
                        <span class="text-white">${{ number_format($propina ?? 0, 2) }}</span>
                    </div>
                @endif
            </div>
            <div class="flex justify-between items-end">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Total a Pagar</span>
                <span class="text-5xl font-black text-green-500 tracking-tighter leading-none" id="total-pagar">${{ number_format($totalPagar, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="w-full lg:w-3/5 p-8 lg:p-12 bg-[#0f0f12]">
        <div class="max-w-xl mx-auto space-y-8">
            <input id="mesa-id" type="hidden" value="{{ $mesa->id }}">
            <input id="orden-id" type="hidden" value="{{ $ordenId ?? ($orden->id ?? '') }}">
            <input id="metodo-pago" type="hidden" value="Efectivo">

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <p class="text-gray-400 uppercase tracking-[0.35em] text-[10px] font-black mb-2">Método de pago</p>
                    <span id="metodo-pago-label" class="inline-flex items-center gap-2 rounded-full bg-white/5 px-4 py-2 text-white font-black uppercase tracking-[0.25em]">
                        <i class="fas fa-money-bill-wave"></i> Efectivo
                    </span>
                </div>
                <button id="btn-abrir-modal-metodo" type="button" class="bg-blue-500 hover:bg-blue-400 text-white font-black py-3 px-4 rounded-2xl uppercase text-xs tracking-[0.28em] transition-all shadow-lg shadow-blue-500/20">
                    Seleccionar método
                </button>
            </div>

            <div id="modal-metodo" class="fixed inset-0 left-0 right-0 top-0 bottom-0 z-[9999] hidden flex items-center justify-center overflow-y-auto bg-black/70 p-4">
                <div class="relative w-full max-w-[88vw] rounded-[1.75rem] bg-[#141417] border border-white/10 p-8 shadow-2xl max-h-[calc(100vh-4rem)] overflow-hidden">
                    <button id="btn-cerrar-modal-metodo" type="button" class="absolute right-5 top-5 text-gray-400 hover:text-white z-[10000]">
                        <i class="fas fa-times"></i>
                    </button>
                    <h2 class="text-2xl font-black text-white mb-3">Selecciona un método de pago</h2>
                    <p class="text-sm text-gray-400 mb-6">Elige el método de pago y registra la referencia o comprobante cuando sea necesario.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <button type="button" data-metodo="Efectivo" class="metodo-btn rounded-2xl border border-white/10 bg-white/5 py-5 px-4 text-left transition-all hover:border-blue-400/30 hover:bg-white/10 min-h-[150px] shadow-lg shadow-black/10">
                            <p class="text-[10px] uppercase tracking-[0.32em] text-gray-400 mb-2">Efectivo</p>
                            <p class="text-lg font-black text-white">Pago en efectivo</p>
                            <p class="text-sm text-gray-500 mt-2">Calcula cambio al instante.</p>
                        </button>
                        <button type="button" data-metodo="Transferencia" class="metodo-btn rounded-2xl border border-white/10 bg-white/5 py-5 px-4 text-left transition-all hover:border-blue-400/30 hover:bg-white/10 min-h-[150px] shadow-lg shadow-black/10">
                            <p class="text-[10px] uppercase tracking-[0.32em] text-gray-400 mb-2">Transferencia</p>
                            <p class="text-lg font-black text-white">Pago por transferencia</p>
                            <p class="text-sm text-gray-500 mt-2">Registra referencia o sube comprobante.</p>
                        </button>
                        <button type="button" data-metodo="Tarjeta" class="metodo-btn rounded-2xl border border-white/10 bg-white/5 py-5 px-4 text-left transition-all hover:border-blue-400/30 hover:bg-white/10 min-h-[150px] shadow-lg shadow-black/10">
                            <p class="text-[10px] uppercase tracking-[0.32em] text-gray-400 mb-2">Tarjeta</p>
                            <p class="text-lg font-black text-white">Cobro con tarjeta</p>
                            <p class="text-sm text-gray-500 mt-2">Marca el pago con tarjeta.</p>
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-[#141417] border border-white/10 p-10 rounded-[2.5rem] text-center shadow-2xl overflow-hidden">
                <p id="titulo-pago" class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-4 italic">Monto a cobrar</p>
                <div class="flex justify-center w-full">
                    <span id="display-pago" class="precio-display text-6xl font-black text-white italic tracking-tighter">$0</span>
                </div>
                <div class="mt-4 text-sm text-gray-400">
                    <p>Total a pagar: <strong class="text-white font-black">${{ number_format($totalPagar, 2) }}</strong></p>
                    <p>Cambio: <strong id="display-cambio" class="text-green-500 font-black">$0</strong></p>
                </div>
                <p id="nota-metodo" class="mt-4 text-sm text-gray-400">Selecciona un método para ver los detalles.</p>
                <div id="mensaje-pago" class="mt-4 hidden rounded-3xl border border-transparent px-4 py-3 text-sm"></div>
            </div>

            <div id="cash-section" class="grid grid-cols-4 gap-4">
                @foreach(['1','2','3','4','5','6','7','8','9','.','0','00','DEL'] as $key)
                    <button type="button" 
                            class="btn-tecla h-20 bg-[#141417] hover:bg-white/5 border border-white/5 rounded-2xl text-2xl font-black text-white transition-all active:scale-95 active:bg-blue-600/20 shadow-lg"
                            data-value="{{ $key }}">
                        {{ $key }}
                    </button>
                @endforeach
            </div>

            <div id="non-cash-section" class="hidden space-y-4 bg-[#141417] border border-white/10 rounded-[2.5rem] p-6">
                <div class="space-y-2">
                    <p class="text-gray-400 uppercase tracking-[0.3em] text-[10px] font-black">Datos de pago</p>
                    <input id="referencia" type="text" placeholder="Referencia / número de operación" class="w-full rounded-2xl border border-white/10 bg-[#0f0f12] px-4 py-4 text-sm text-white outline-none focus:border-blue-500/70" />
                </div>
                <div class="space-y-2">
                    <label class="text-gray-400 uppercase tracking-[0.3em] text-[10px] font-black">Comprobante (opcional)</label>
                    <input id="comprobante" type="file" accept=".jpg,.jpeg,.png,.pdf" class="w-full rounded-2xl border border-white/10 bg-[#0f0f12] px-4 py-3 text-sm text-white outline-none file:bg-blue-500 file:text-white file:px-4 file:py-2 file:rounded-full" />
                </div>
            </div>

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

{{-- Script de lógica del teclado --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const totalDividasData = document.getElementById('totales-divididas');
        const cuentaActualInput = document.getElementById('cuenta-actual');
        const botonessCuenta = document.querySelectorAll('.btn-cuenta');
        const totalPagarDisplay = document.getElementById('total-pagar');
        const display = document.getElementById('display-pago');
        const displayCambio = document.getElementById('display-cambio');
        const botonesTeclado = document.querySelectorAll('.btn-tecla');
        const btnFinalizar = document.getElementById('btn-finalizar');
        const btnAbrirModal = document.getElementById('btn-abrir-modal-metodo');
        const btnCerrarModal = document.getElementById('btn-cerrar-modal-metodo');
        const modalMetodo = document.getElementById('modal-metodo');
        const metodoPagoInput = document.getElementById('metodo-pago');
        const metodoPagoLabel = document.getElementById('metodo-pago-label');
        const metodoButtons = document.querySelectorAll('.metodo-btn');
        const cashSection = document.getElementById('cash-section');
        const nonCashSection = document.getElementById('non-cash-section');
        const tituloPago = document.getElementById('titulo-pago');
        const notaMetodo = document.getElementById('nota-metodo');
        const referenciaInput = document.getElementById('referencia');
        const comprobanteInput = document.getElementById('comprobante');
        const mesaId = document.getElementById('mesa-id').value;
        const ordenId = document.getElementById('orden-id').value;
        const mensajePago = document.getElementById('mensaje-pago');

        let totalPagar = parseFloat('{{ number_format($totalPagar, 2, ".", "") }}');
        let montoActual = '0';

        if (totalDividasData) {
            const totalDivididas = JSON.parse(totalDividasData.value);
            if (totalDivididas.length > 0) {
                totalPagar = parseFloat(totalDivididas[0].total);
            }
        }

        const formatter = new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 2
        });

        function actualizarVista() {
            const numero = parseFloat(montoActual) || 0;
            display.innerText = formatter.format(numero);
            const cambio = numero - totalPagar;
            displayCambio.innerText = formatter.format(Math.max(cambio, 0));
        }

        function setMetodoPago(method) {
            metodoPagoInput.value = method;
            metodoPagoLabel.innerText = method;
            metodoButtons.forEach(btn => {
                if (btn.dataset.metodo === method) {
                    btn.classList.add('bg-blue-500', 'text-white');
                } else {
                    btn.classList.remove('bg-blue-500', 'text-white');
                }
            });

            if (method === 'Efectivo') {
                cashSection.classList.remove('hidden');
                nonCashSection.classList.add('hidden');
                tituloPago.innerText = 'Efectivo recibido';
                notaMetodo.innerText = 'Ingresa el efectivo recibido para calcular el cambio.';
                montoActual = '0';
                displayCambio.innerText = formatter.format(0);
            } else {
                cashSection.classList.add('hidden');
                nonCashSection.classList.remove('hidden');
                tituloPago.innerText = 'Monto a cobrar';
                notaMetodo.innerText = method === 'Transferencia'
                    ? 'Registra la referencia o sube el comprobante de la transferencia.'
                    : 'Marca el pago con tarjeta y guarda los datos de referencia si los tienes.';
                montoActual = totalPagar.toFixed(2);
                displayCambio.innerText = formatter.format(0);
            }

            actualizarVista();
            modalMetodo.classList.add('hidden');
        }

        if (botonessCuenta.length > 0) {
            botonessCuenta.forEach(btn => {
                btn.addEventListener('click', () => {
                    const nroCuenta = btn.getAttribute('data-cuenta');
                    const totalDivididas = JSON.parse(totalDividasData.value);

                    document.querySelectorAll('[id^="cuenta-"]').forEach(el => el.classList.add('hidden'));
                    document.getElementById(`cuenta-${nroCuenta}`).classList.remove('hidden');

                    const cuentaInfo = totalDivididas.find(c => c.numero_cuenta == nroCuenta);
                    document.getElementById('total-subtotal').textContent = '$' + parseFloat(cuentaInfo.subtotal).toFixed(2);
                    document.getElementById('total-iva').textContent = '$' + parseFloat(cuentaInfo.iva).toFixed(2);
                    document.getElementById('total-propina').textContent = '$' + parseFloat(cuentaInfo.propina).toFixed(2);

                    totalPagar = parseFloat(cuentaInfo.total);
                    totalPagarDisplay.textContent = '$' + totalPagar.toFixed(2);

                    botonessCuenta.forEach(b => {
                        b.style.borderColor = '#374151';
                        b.style.backgroundColor = '#141417';
                    });
                    btn.style.borderColor = '#3B82F6';
                    btn.style.backgroundColor = '#3B82F6';

                    cuentaActualInput.value = nroCuenta;
                    montoActual = metodoPagoInput.value === 'Efectivo' ? '0' : totalPagar.toFixed(2);
                    actualizarVista();
                });
            });
        }

        botonesTeclado.forEach(boton => {
            boton.addEventListener('click', () => {
                const valor = boton.getAttribute('data-value');
                if (valor === 'DEL') {
                    montoActual = montoActual.slice(0, -1);
                    if (montoActual === '' || montoActual === '.') montoActual = '0';
                } else if (valor === '.') {
                    if (!montoActual.includes('.')) montoActual += '.';
                } else {
                    if (montoActual === '0') montoActual = valor;
                    else montoActual += valor;
                }
                actualizarVista();
            });
        });

        metodoButtons.forEach(btn => {
            btn.addEventListener('click', () => setMetodoPago(btn.dataset.metodo));
        });

        btnAbrirModal.addEventListener('click', () => modalMetodo.classList.remove('hidden'));
        btnCerrarModal.addEventListener('click', () => modalMetodo.classList.add('hidden'));
        modalMetodo.addEventListener('click', (event) => {
            if (event.target === modalMetodo) {
                modalMetodo.classList.add('hidden');
            }
        });

        function mostrarMensajePago(texto, tipo = 'error') {
            mensajePago.classList.remove('hidden', 'bg-red-500/10', 'text-red-200', 'bg-emerald-500/10', 'text-emerald-200');
            mensajePago.classList.add(tipo === 'success' ? 'bg-emerald-500/10' : 'bg-red-500/10');
            mensajePago.classList.add(tipo === 'success' ? 'text-emerald-200' : 'text-red-200');
            mensajePago.textContent = texto;
        }

        btnFinalizar.addEventListener('click', async () => {
            const method = metodoPagoInput.value;
            const efectivo = parseFloat(montoActual) || 0;

            if (method === 'Efectivo' && efectivo < totalPagar - 0.01) {
                mostrarMensajePago('El efectivo recibido es menor al total.');
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const formData = new FormData();
                formData.append('_token', token);
                formData.append('mesa_id', mesaId);
                formData.append('orden_id', ordenId);
                formData.append('efectivo', efectivo);
                formData.append('metodo_pago', method);
                formData.append('referencia', referenciaInput.value.trim());
                if (comprobanteInput.files.length > 0) {
                    formData.append('comprobante', comprobanteInput.files[0]);
                }

                const response = await fetch('{{ route("admin.caja.api.pagar") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: formData
                });

                let data = null;
                try {
                    data = await response.json();
                } catch (jsonError) {
                    const text = await response.text();
                    mostrarMensajePago('Error en la respuesta del servidor: ' + (text || response.statusText));
                    return;
                }

                if (!response.ok) {
                    mostrarMensajePago(data.message || 'Error en el pago.');
                    return;
                }

                mostrarMensajePago(data.message + ' Cambio: $' + data.cambio, 'success');

                if (data.comprobante_url) {
                    const enlace = document.createElement('a');
                    enlace.href = data.comprobante_url;
                    enlace.download = '';
                    enlace.target = '_blank';
                    document.body.appendChild(enlace);
                    enlace.click();
                    document.body.removeChild(enlace);
                    setTimeout(() => window.location.href = '{{ route("admin.caja.index") }}', 1200);
                    return;
                }

                setTimeout(() => window.location.href = '{{ route("admin.caja.index") }}', 1200);
            } catch (error) {
                console.error('Pago error:', error);
                mostrarMensajePago('Error de red al procesar el pago.');
            }
        });

        setMetodoPago('Efectivo');
        actualizarVista();
    });
</script>
@endsection