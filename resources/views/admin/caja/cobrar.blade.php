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
            <div class="space-y-3 mb-6" id="totales-container">
                @if($cuentasDivididas)
                    {{-- Mostrar totales de la primera cuenta por defecto --}}
                    <div class="flex justify-between text-xs font-bold text-gray-500 uppercase items-center">
                        <span>Subtotal</span>
                        <span class="text-white" id="total-subtotal">${{ number_format($cuentasDividadasInfo[0]['subtotal'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-gray-500 uppercase items-center gap-2">
                        <span>IVA (16%)</span>
                        <div class="flex items-center gap-2">
                            <span class="text-white">$</span>
                            <input type="number" id="total-iva" step="0.01" min="0" value="{{ number_format($cuentasDividadasInfo[0]['iva'], 2, '.', '') }}" class="w-20 bg-[#0f0f12] border border-white/10 rounded-lg px-2 py-1 text-white text-right text-xs focus:border-blue-500 focus:outline-none"/>
                        </div>
                    </div>
                    
                    {{-- Sección de Propina --}}
                    <div class="mt-4 pt-3 border-t border-white/10">
                        <p class="text-xs font-bold text-gray-500 uppercase mb-2">Propina</p>
                        <div class="grid grid-cols-4 gap-2 mb-2">
                            <button type="button" class="btn-propina-pct px-2 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold text-xs transition-all border border-white/10" data-percent="10">10%</button>
                            <button type="button" class="btn-propina-pct px-2 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold text-xs transition-all border border-white/10" data-percent="15">15%</button>
                            <button type="button" class="btn-propina-pct px-2 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold text-xs transition-all border border-white/10" data-percent="20">20%</button>
                            <button type="button" class="btn-propina-pct px-2 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold text-xs transition-all border border-white/10" data-percent="custom">Otros</button>
                        </div>
                        <div id="propina-custom-input" class="hidden">
                            <div class="flex items-center gap-2">
                                <span class="text-white">$</span>
                                <input type="number" id="total-propina" step="0.01" min="0" value="0.00" class="flex-1 bg-[#0f0f12] border border-white/10 rounded-lg px-2 py-1 text-white text-right text-xs focus:border-blue-500 focus:outline-none"/>
                            </div>
                        </div>
                        <div id="propina-display" class="flex items-center justify-end gap-2 text-xs font-bold text-gray-400">
                            <span>Propina:</span>
                            <span class="text-white" id="total-propina-display">$0.00</span>
                        </div>
                    </div>
                    
                    <input type="hidden" id="cuenta-actual" value="1">
                    <input type="hidden" id="totales-divididas" value='@php echo json_encode($cuentasDividadasInfo, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK); @endphp'>
                @else
                    <div class="flex justify-between text-xs font-bold text-gray-500 uppercase items-center">
                        <span>Subtotal</span>
                        <span class="text-white">${{ number_format($subtotal ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-gray-500 uppercase items-center gap-2">
                        <span>IVA (16%)</span>
                        <div class="flex items-center gap-2">
                            <span class="text-white">$</span>
                            <input type="number" id="total-iva" step="0.01" min="0" value="{{ number_format($iva ?? 0, 2, '.', '') }}" class="w-20 bg-[#0f0f12] border border-white/10 rounded-lg px-2 py-1 text-white text-right text-xs focus:border-blue-500 focus:outline-none"/>
                        </div>
                    </div>
                    
                    {{-- Sección de Propina --}}
                    <div class="mt-4 pt-3 border-t border-white/10">
                        <p class="text-xs font-bold text-gray-500 uppercase mb-2">Propina</p>
                        <div class="grid grid-cols-4 gap-2 mb-2">
                            <button type="button" class="btn-propina-pct px-2 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold text-xs transition-all border border-white/10" data-percent="10">10%</button>
                            <button type="button" class="btn-propina-pct px-2 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold text-xs transition-all border border-white/10" data-percent="15">15%</button>
                            <button type="button" class="btn-propina-pct px-2 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold text-xs transition-all border border-white/10" data-percent="20">20%</button>
                            <button type="button" class="btn-propina-pct px-2 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-white font-bold text-xs transition-all border border-white/10" data-percent="custom">Otros</button>
                        </div>
                        <div id="propina-custom-input" class="hidden">
                            <div class="flex items-center gap-2">
                                <span class="text-white">$</span>
                                <input type="number" id="total-propina" step="0.01" min="0" value="0.00" class="flex-1 bg-[#0f0f12] border border-white/10 rounded-lg px-2 py-1 text-white text-right text-xs focus:border-blue-500 focus:outline-none"/>
                            </div>
                        </div>
                        <div id="propina-display" class="flex items-center justify-end gap-2 text-xs font-bold text-gray-400">
                            <span>Propina:</span>
                            <span class="text-white" id="total-propina-display">$0.00</span>
                        </div>
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
        const ivaInput = document.getElementById('total-iva');
        const propinaInput = document.getElementById('total-propina');
        const subtotalDisplay = document.getElementById('total-subtotal');
        const ivaDisplay = document.getElementById('total-iva-display');
        const propinaDisplay = document.getElementById('total-propina-display');
        const botonesPropinaPercentaje = document.querySelectorAll('.btn-propina-pct');
        const propinCustomInput = document.getElementById('propina-custom-input');

        // Total original de toda la mesa (para cuentas divididas)
        let totalMesaCompleta = parseFloat('{{ number_format($totalPagar, 2, ".", "") }}');
        let totalPagar = parseFloat('{{ number_format($totalPagar, 2, ".", "") }}');
        let montoActual = '0';
        let subtotalActual = parseFloat('{{ number_format($subtotal ?? 0, 2, ".", "") }}');
        let ivaActual = parseFloat('{{ number_format($iva ?? 0, 2, ".", "") }}');
        let propinaActual = 0;

        if (totalDividasData) {
            const totalDivididas = JSON.parse(totalDividasData.value);
            if (totalDivididas.length > 0) {
                totalPagar = parseFloat(totalDivididas[0].total);
                subtotalActual = parseFloat(totalDivididas[0].subtotal);
                ivaActual = parseFloat(totalDivididas[0].iva);
                propinaActual = parseFloat(totalDivididas[0].propina);
            }
        }

        const formatter = new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 2
        });

        // Eventos para interactuar con las teclas numéricas
        botonesTeclado.forEach(boton => {
            boton.addEventListener('click', () => {
                const valor = boton.getAttribute('data-value');

                if (valor === 'DEL') {
                    if (montoActual.length > 1) {
                        montoActual = montoActual.slice(0, -1);
                    } else {
                        montoActual = '0';
                    }
                } else if (valor === '.') {
                    if (!montoActual.includes('.')) {
                        montoActual += '.';
                    }
                } else if (valor === '00') {
                    if (montoActual !== '0') {
                        montoActual += '00';
                    }
                } else {
                    if (montoActual === '0') {
                        montoActual = valor;
                    } else {
                        montoActual += valor;
                    }
                }
                actualizarVista();
            });
        });

        // Función para recalcular el total
        function recalcularTotal() {
            const subtotal = subtotalActual || 0;
            const iva = ivaActual || 0;
            const propina = parseFloat(propinaInput?.value) || propinaActual || 0;
            const nuevoTotal = subtotal + iva + propina;
            
            totalPagar = Math.round(nuevoTotal * 100) / 100; // Redondear a 2 decimales
            propinaActual = propina;
            
            const totalDerechaElement = document.getElementById('total-pagar-derecha');
            if (totalDerechaElement) {
                totalDerechaElement.textContent = '$' + totalPagar.toFixed(2);
            }
            
            // Actualizar el total general en el panel izquierdo
            if (totalPagarDisplay) {
                totalPagarDisplay.textContent = '$' + totalPagar.toFixed(2);
            }
            
            // Actualizar display de propina
            if (propinaDisplay) {
                propinaDisplay.textContent = '$' + propina.toFixed(2);
            }
            
            actualizarVista();
        }

        // Agregar listeners para botones de propina por porcentaje
        botonesPropinaPercentaje.forEach(btn => {
            btn.addEventListener('click', () => {
                const percent = btn.getAttribute('data-percent');
                let nuevaPropina = 0;
                
                // Deseleccionar todos los botones
                botonesPropinaPercentaje.forEach(b => {
                    b.classList.remove('bg-blue-500/20', 'border-blue-400');
                    b.classList.add('bg-white/5', 'border-white/10');
                });
                
                // Seleccionar el botón actual
                btn.classList.remove('bg-white/5', 'border-white/10');
                btn.classList.add('bg-blue-500/20', 'border-blue-400');
                
                if (percent === 'custom') {
                    // Mostrar input personalizado
                    propinCustomInput.classList.remove('hidden');
                    if (propinaInput) {
                        propinaInput.focus();
                    }
                } else {
                    // Calcular porcentaje sobre el subtotal
                    const porcentaje = parseInt(percent) / 100;
                    nuevaPropina = Math.round(subtotalActual * porcentaje * 100) / 100;
                    propinCustomInput.classList.add('hidden');
                    if (propinaInput) {
                        propinaInput.value = nuevaPropina.toFixed(2);
                    }
                }
                
                recalcularTotal();
            });
        });

        // Agregar listeners para cambios en el IVA
        if (ivaInput) {
            ivaInput.addEventListener('change', () => {
                ivaActual = parseFloat(ivaInput.value) || 0;
                recalcularTotal();
            });
            ivaInput.addEventListener('input', () => {
                ivaActual = parseFloat(ivaInput.value) || 0;
                recalcularTotal();
            });
        }

        // Agregar listeners para cambios en el input personalizado de propina
        if (propinaInput) {
            propinaInput.addEventListener('change', () => {
                recalcularTotal();
            });
            propinaInput.addEventListener('input', () => {
                recalcularTotal();
            });
        }

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
            });
        }
    });
</script>
@endsection