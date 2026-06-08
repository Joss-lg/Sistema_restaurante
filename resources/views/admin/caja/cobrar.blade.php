@extends('layouts.admin')

@section('title', 'Cobrar Mesa | Ollintem Pro')
@section('no-sidebar', 'true')

@section('content')
{{-- Contenedor principal con scroll natural --}}
<div class="flex flex-col lg:flex-row h-screen overflow-hidden bg-[#0f0f12]">
    
    <div class="w-full lg:w-2/5 border-r border-white/5 bg-[#141417] flex flex-col border-b lg:border-b-0 overflow-hidden">
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
            {{-- Selector de personas para pagar --}}
            <div class="px-8 pb-4">
                <div class="flex gap-2 overflow-x-auto overflow-y-hidden pb-2 -mx-2 px-2">
                    @foreach($cuentasDividadasInfo as $cuenta)
                        <button 
                            type="button" 
                            class="btn-cuenta px-3 py-2 rounded-lg font-bold text-xs whitespace-nowrap transition-all flex items-center gap-2 flex-shrink-0"
                            data-cuenta="{{ $cuenta['numero_cuenta'] ?? $loop->iteration }}"
                            data-orden="{{ $cuenta['orden_id'] ?? '' }}"
                            data-total="{{ number_format($cuenta['total'] ?? 0, 2, '.', '') }}"
                            data-estado="sin-pagar"
                            style="background-color: #141417; border: 2px solid #3B82F6; color: white;">
                            <span class="texto-cuenta text-[11px]">Persona {{ $cuenta['numero_cuenta'] ?? $loop->iteration }} - ${{ number_format($cuenta['total'] ?? 0, 2) }}</span>
                            <i class="fas fa-check hidden opacity-0 transition-all text-xs"></i>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Lista de productos --}}
        <div class="px-8 pb-8 space-y-4 flex-1 overflow-y-auto" id="productos-container">
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
                    <input type="hidden" id="totales-divididas" value='@php echo json_encode($cuentasDividadasInfo, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK); @endphp'>
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

    <div class="w-full lg:w-3/5 p-4 lg:p-8 bg-[#0f0f12] overflow-hidden">
        <div class="max-w-xl mx-auto h-full flex flex-col overflow-hidden">
            <input id="mesa-id" type="hidden" value="{{ $mesa->id }}">
            <input id="orden-id" type="hidden" value="{{ $ordenId ?? ($cuentasDivididas ? ($cuentasDividadasInfo[0]['orden_id'] ?? '') : ($orden->id ?? '')) }}">
            <input id="metodo-pago" type="hidden" value="Efectivo">
            <div class="flex-1 space-y-6 overflow-y-auto">

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

            <div class="bg-[#141417] border border-white/10 p-10 rounded-[2.5rem] text-center shadow-2xl overflow-hidden">
                <p id="titulo-pago" class="text-gray-500 text-[10px] font-black uppercase tracking-widest mb-4 italic">Monto a cobrar</p>
                <div class="flex justify-center w-full">
                    <span id="display-pago" class="precio-display text-6xl font-black text-white italic tracking-tighter">$0</span>
                </div>
                <div class="mt-4 text-sm text-gray-400">
                    <p>Total a pagar: <strong id="total-pagar-derecha" class="text-white font-black">${{ number_format($totalPagar, 2) }}</strong></p>
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
                    <p id="comprobante-info" class="text-[11px] text-emerald-200/80 mt-2"></p>
                </div>
                <div class="space-y-2">
                    <p class="text-gray-400 uppercase tracking-[0.3em] text-[10px] font-black">Información de cuenta</p>
                    <div id="cuenta-info" class="rounded-2xl border border-white/10 bg-[#0f0f12] p-4 text-sm text-white/80">
                        <p class="mb-1"><strong class="text-white">Banco:</strong> Banco Central</p>
                        <p class="mb-1"><strong class="text-white">CLABE:</strong> 002345678901234567</p>
                        <p class="mb-1"><strong class="text-white">Titular:</strong> Ollintem Pro</p>
                        <p class="mb-0"><strong class="text-white">Referencia:</strong> Se genera automáticamente al seleccionar el método</p>
                    </div>
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
        const comprobanteInfoText = document.getElementById('comprobante-info');
        const cuentaInfoBox = document.getElementById('cuenta-info');
        const mesaId = document.getElementById('mesa-id').value;
        const mensajePago = document.getElementById('mensaje-pago');

        // Total original de toda la mesa (para cuentas divididas)
        let totalMesaCompleta = parseFloat('{{ number_format($totalPagar, 2, ".", "") }}');
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

        function generateAutoReference(method) {
            const now = new Date();
            const pad = value => String(value).padStart(2, '0');
            const timestamp = `${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;
            const random = Math.floor(Math.random() * 900 + 100);
            return method === 'Transferencia' ? `TRF-${timestamp}-${random}` : `TAR-${timestamp}-${random}`;
        }

        function setMetodoPago(method) {
            metodoPagoInput.value = method;
            metodoPagoLabel.innerText = method;
            metodoButtons.forEach(btn => {
                btn.classList.remove(
                    'ring-2',
                    'ring-emerald-300/20',
                    'ring-sky-300/20',
                    'ring-violet-300/20',
                    'bg-emerald-500/10',
                    'bg-sky-500/10',
                    'bg-violet-500/10',
                    'border-emerald-300/80',
                    'border-sky-300/80',
                    'border-violet-300/80',
                    'shadow-[0_30px_70px_-20px_rgba(16,185,129,0.35)]',
                    'shadow-[0_30px_70px_-20px_rgba(56,189,248,0.35)]',
                    'shadow-[0_30px_70px_-20px_rgba(139,92,246,0.35)]'
                );

                if (btn.dataset.metodo === method) {
                    btn.classList.add('ring-2');
                    if (method === 'Efectivo') {
                        btn.classList.add('bg-emerald-500/10', 'border-emerald-300/80', 'ring-emerald-300/20', 'shadow-[0_30px_70px_-20px_rgba(16,185,129,0.35)]');
                    } else if (method === 'Transferencia') {
                        btn.classList.add('bg-sky-500/10', 'border-sky-300/80', 'ring-sky-300/20', 'shadow-[0_30px_70px_-20px_rgba(56,189,248,0.35)]');
                    } else if (method === 'Tarjeta') {
                        btn.classList.add('bg-violet-500/10', 'border-violet-300/80', 'ring-violet-300/20', 'shadow-[0_30px_70px_-20px_rgba(139,92,246,0.35)]');
                    }
                }
            });

            if (method === 'Efectivo') {
                cashSection.classList.remove('hidden');
                nonCashSection.classList.add('hidden');
                tituloPago.innerText = 'Efectivo recibido';
                notaMetodo.innerText = 'Ingresa el efectivo recibido para calcular el cambio.';
                referenciaInput.value = '';
                comprobanteInfoText.innerText = '';
                cuentaInfoBox.innerHTML = `
                    <p class="mb-1"><strong class="text-white">Banco:</strong> Banco Central</p>
                    <p class="mb-1"><strong class="text-white">CLABE:</strong> 002345678901234567</p>
                    <p class="mb-1"><strong class="text-white">Titular:</strong> Ollintem Pro</p>
                    <p class="mb-0"><strong class="text-white">Referencia:</strong> Se genera automáticamente al seleccionar el método</p>
                `;
                montoActual = '0';
                displayCambio.innerText = formatter.format(0);
            } else {
                cashSection.classList.add('hidden');
                nonCashSection.classList.remove('hidden');
                tituloPago.innerText = 'Monto a cobrar';
                referenciaInput.value = referenciaInput.value.trim() || generateAutoReference(method);
                comprobanteInfoText.innerText = 'Comprobante generado automáticamente: ' + referenciaInput.value;
                if (method === 'Transferencia') {
                    notaMetodo.innerText = 'Usa los datos de cuenta y registra la referencia de transferencia.';
                    cuentaInfoBox.innerHTML = `
                        <p class="mb-1"><strong class="text-white">Banco:</strong> Banco Central</p>
                        <p class="mb-1"><strong class="text-white">CLABE:</strong> 002345678901234567</p>
                        <p class="mb-1"><strong class="text-white">Titular:</strong> Ollintem Pro</p>
                        <p class="mb-0"><strong class="text-white">Referencia:</strong> ${referenciaInput.value}</p>
                    `;
                } else {
                    notaMetodo.innerText = 'Pago con tarjeta registrado automáticamente con comprobante generado.';
                    cuentaInfoBox.innerHTML = `
                        <p class="mb-1"><strong class="text-white">Terminal:</strong> TPV Automático</p>
                        <p class="mb-1"><strong class="text-white">Autorización:</strong> ${referenciaInput.value}</p>
                        <p class="mb-0"><strong class="text-white">Titular:</strong> Ollintem Pro</p>
                    `;
                }
                montoActual = totalPagar.toFixed(2);
                displayCambio.innerText = formatter.format(0);
            }

            actualizarVista();
            modalMetodo.classList.add('hidden');
            unlockScroll();
        }

        if (botonessCuenta.length > 0) {
            botonessCuenta.forEach(btn => {
                btn.addEventListener('click', () => {
                    // No hacer nada si la cuenta ya está pagada
                    if (btn.dataset.estado === 'pagada') {
                        return;
                    }

                    const nroCuenta = btn.getAttribute('data-cuenta');
                    const totalDivididas = totalDividasData ? JSON.parse(totalDividasData.value) : [];
                    const cuentaInfo = totalDivididas.find(c => c.numero_cuenta == nroCuenta);

                    document.querySelectorAll('[id^="cuenta-"]').forEach(el => el.classList.add('hidden'));
                    document.getElementById(`cuenta-${nroCuenta}`).classList.remove('hidden');

                    if (cuentaInfo) {
                        document.getElementById('total-subtotal').textContent = '$' + parseFloat(cuentaInfo.subtotal).toFixed(2);
                        document.getElementById('total-iva').textContent = '$' + parseFloat(cuentaInfo.iva).toFixed(2);
                        document.getElementById('total-propina').textContent = '$' + parseFloat(cuentaInfo.propina).toFixed(2);

                        totalPagar = parseFloat(cuentaInfo.total);
                        
                        // Actualizar el total en el panel derecho (referencia de lo que debe pagar esta persona)
                        const totalDerechaElement = document.getElementById('total-pagar-derecha');
                        if (totalDerechaElement) {
                            totalDerechaElement.textContent = '$' + totalPagar.toFixed(2);
                        }
                        
                        cuentaActualInput.value = nroCuenta;
                        document.getElementById('orden-id').value = cuentaInfo.orden_id || '';
                    } else {
                        // Fallback usando data-total del botón
                        const totalCuenta = parseFloat(btn.getAttribute('data-total')) || 0;
                        totalPagar = totalCuenta;
                        
                        const totalDerechaElement = document.getElementById('total-pagar-derecha');
                        if (totalDerechaElement) {
                            totalDerechaElement.textContent = '$' + totalCuenta.toFixed(2);
                        }
                    }

                    botonessCuenta.forEach(b => {
                        if (b.dataset.estado !== 'pagada') {
                            b.style.borderColor = '#374151';
                            b.style.backgroundColor = '#141417';
                        }
                    });
                    if (btn.dataset.estado !== 'pagada') {
                        btn.style.borderColor = '#3B82F6';
                        btn.style.backgroundColor = '#3B82F6';
                    }

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

        btnAbrirModal.addEventListener('click', () => {
            modalMetodo.classList.remove('hidden');
            lockScroll();
        });
        btnCerrarModal.addEventListener('click', () => {
            modalMetodo.classList.add('hidden');
            unlockScroll();
        });
        modalMetodo.addEventListener('click', (event) => {
            if (event.target === modalMetodo) {
                modalMetodo.classList.add('hidden');
                unlockScroll();
            }
        });

        function lockScroll() {
            const scrollY = window.scrollY || window.pageYOffset;
            document.body.dataset.scrollY = scrollY;
            document.documentElement.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.top = `-${scrollY}px`;
            document.body.style.left = '0';
            document.body.style.right = '0';
            document.body.style.width = '100%';
            document.body.style.overflow = 'hidden';
        }

        function unlockScroll() {
            const scrollY = document.body.dataset.scrollY ? parseInt(document.body.dataset.scrollY, 10) : 0;
            document.documentElement.style.overflow = '';
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.left = '';
            document.body.style.right = '';
            document.body.style.width = '';
            document.body.style.overflow = '';
            window.scrollTo(0, scrollY);
            delete document.body.dataset.scrollY;
        }

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
                const ordenId = document.getElementById('orden-id').value;
                const formData = new FormData();
                formData.append('_token', token);
                formData.append('mesa_id', mesaId);
                formData.append('orden_id', ordenId);
                formData.append('efectivo', efectivo);
                formData.append('metodo_pago', method);
                formData.append('referencia', referenciaInput.value.trim());

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

                // Marcar la cuenta actual como pagada
                const cuentaActual = cuentaActualInput.value;
                const botonCuentaPagada = document.querySelector(`.btn-cuenta[data-cuenta="${cuentaActual}"]`);
                
                if (botonCuentaPagada) {
                    const montoPagado = parseFloat(botonCuentaPagada.getAttribute('data-total')) || 0;
                    
                    botonCuentaPagada.dataset.estado = 'pagada';
                    botonCuentaPagada.style.opacity = '0.6';
                    botonCuentaPagada.style.borderColor = '#10b981';
                    botonCuentaPagada.style.backgroundColor = '#10b981';
                    botonCuentaPagada.disabled = true;
                    
                    // Restar del total general de la mesa
                    totalMesaCompleta = Math.max(0, totalMesaCompleta - montoPagado);
                    totalPagarDisplay.textContent = '$' + totalMesaCompleta.toFixed(2);
                    
                    // Actualizar también el total en el panel derecho
                    const totalDerechaElement = document.getElementById('total-pagar-derecha');
                    if (totalDerechaElement) {
                        totalDerechaElement.textContent = '$' + totalMesaCompleta.toFixed(2);
                    }
                    
                    // Cambiar el texto del botón a "PAGADO"
                    const textoSpan = botonCuentaPagada.querySelector('.texto-cuenta');
                    if (textoSpan) {
                        const nroPersona = botonCuentaPagada.getAttribute('data-cuenta');
                        textoSpan.textContent = `Persona ${nroPersona} - ✓ PAGADO`;
                    }
                    
                    const iconoCheck = botonCuentaPagada.querySelector('i');
                    if (iconoCheck) {
                        iconoCheck.classList.remove('hidden', 'opacity-0');
                        iconoCheck.classList.add('opacity-100');
                    }
                }

                // Verificar si hay más cuentas sin pagar
                const cuentasSinPagar = document.querySelectorAll('.btn-cuenta[data-estado="sin-pagar"]');
                
                if (cuentasSinPagar.length > 0) {
                    // Hay más cuentas por pagar
                    setTimeout(() => {
                        // Limpiar el monto y permitir seleccionar la siguiente cuenta
                        montoActual = metodoPagoInput.value === 'Efectivo' ? '0' : totalPagar.toFixed(2);
                        actualizarVista();
                        
                        // Seleccionar automáticamente la siguiente cuenta sin pagar
                        const siguienteCuenta = cuentasSinPagar[0];
                        siguienteCuenta.click();
                    }, 800);
                    return;
                }

                // Si no hay más cuentas, redirigir a mesas
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
        
        // Si hay cuentas divididas, seleccionar el primer botón automáticamente
        const primerBotonCuenta = document.querySelector('.btn-cuenta');
        if (primerBotonCuenta) {
            primerBotonCuenta.click();
        }
        
        actualizarVista();
    });
</script>

@section('modals')
<div id="modal-metodo" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/80 backdrop-blur-xl p-4 touch-none overscroll-contain">
    <div class="relative w-full max-w-[920px] rounded-[2rem] bg-[var(--card-color)] border border-[var(--border-color)] shadow-[0_30px_90px_-30px_rgba(0,0,0,0.8)] max-h-[calc(100vh-3rem)] overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-blue-500/10 to-transparent pointer-events-none"></div>
        <button id="btn-cerrar-modal-metodo" type="button" class="absolute right-5 top-5 z-10 text-gray-400 hover:text-white transition-colors">
            <i class="fas fa-times"></i>
        </button>
        <div class="relative max-h-[calc(100vh-3.5rem)] overflow-y-auto p-8 sm:p-10">
            <h2 class="text-2xl font-black text-white mb-2">Selecciona un método de pago</h2>
            <p class="text-sm text-gray-400 mb-7">Elige el método y la referencia se generará automáticamente para transferencia o tarjeta.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <button type="button" data-metodo="Efectivo" class="metodo-btn group relative rounded-[1.75rem] border border-emerald-400/10 bg-emerald-500/5 p-6 text-left transition duration-300 hover:border-emerald-300/70 hover:bg-emerald-500/10 min-h-[170px] shadow-[0_24px_60px_-35px_rgba(16,185,129,0.35)] overflow-visible">
                    <div class="absolute right-5 top-5 w-12 h-12 rounded-3xl bg-emerald-500/15 text-emerald-300 flex items-center justify-center text-lg transition duration-300 group-hover:bg-emerald-500/25">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <p class="text-[10px] uppercase tracking-[0.32em] text-emerald-200 mb-3">Efectivo</p>
                    <p class="text-xl font-black text-white">Pago en efectivo</p>
                    <p class="text-sm text-emerald-100/80 mt-3">Calcula cambio al instante.</p>
                </button>
                <button type="button" data-metodo="Transferencia" class="metodo-btn group relative rounded-[1.75rem] border border-sky-400/10 bg-sky-500/5 p-6 text-left transition duration-300 hover:border-sky-300/70 hover:bg-sky-500/10 min-h-[170px] shadow-[0_24px_60px_-35px_rgba(56,189,248,0.35)] overflow-visible">
                    <div class="absolute right-5 top-5 w-12 h-12 rounded-3xl bg-sky-500/15 text-sky-300 flex items-center justify-center text-lg transition duration-300 group-hover:bg-sky-500/25">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <p class="text-[10px] uppercase tracking-[0.32em] text-sky-200 mb-3">Transferencia</p>
                    <p class="text-xl font-black text-white">Pago por transferencia</p>
                    <p class="text-sm text-sky-100/80 mt-3">Registra la referencia de transferencia y usa los datos de cuenta.</p>
                </button>
                <button type="button" data-metodo="Tarjeta" class="metodo-btn group relative rounded-[1.75rem] border border-violet-400/10 bg-violet-500/5 p-6 text-left transition duration-300 hover:border-violet-300/70 hover:bg-violet-500/10 min-h-[170px] shadow-[0_24px_60px_-35px_rgba(139,92,246,0.35)] overflow-visible">
                    <div class="absolute right-5 top-5 w-12 h-12 rounded-3xl bg-violet-500/15 text-violet-300 flex items-center justify-center text-lg transition duration-300 group-hover:bg-violet-500/25">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <p class="text-[10px] uppercase tracking-[0.32em] text-violet-200 mb-3">Tarjeta</p>
                    <p class="text-xl font-black text-white">Cobro con tarjeta</p>
                    <p class="text-sm text-violet-100/80 mt-3">Pago con tarjeta. Se genera comprobante automático.</p>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection