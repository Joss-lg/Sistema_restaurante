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
                            data-pagado="false"
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
                    <input type="hidden" id="totales-divididas" value='{{ json_encode($cuentasDividadasInfo ?? [], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK) }}'>
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
                    <div class="flex gap-2 flex-wrap">
                        <button id="btn-abrir-modal-promos" type="button" class="bg-purple-500 hover:bg-purple-400 text-white font-black py-3 px-4 rounded-2xl uppercase text-xs tracking-[0.28em] transition-all shadow-lg shadow-purple-500/20">
                            <i class="fas fa-tag mr-2"></i>Promociones
                        </button>
                    </div>
                </div>

                {{-- Sección de Promociones --}}
                <div class="bg-[#141417] border border-purple-500/30 p-6 rounded-[2rem] shadow-lg">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-gray-400 uppercase tracking-[0.35em] text-[10px] font-black mb-2">Promociones Disponibles</p>
                            <span id="promo-label" class="inline-flex items-center gap-2 rounded-full bg-purple-500/10 px-4 py-2 text-purple-400 font-black uppercase tracking-[0.2em] text-xs">
                                <i class="fas fa-tag"></i> Sin promoción
                            </span>
                        </div>
                        <button id="btn-agregar-promo" type="button" class="bg-purple-500 hover:bg-purple-400 text-white font-black py-3 px-4 rounded-2xl uppercase text-xs tracking-[0.28em] transition-all shadow-lg shadow-purple-500/20">
                            <i class="fas fa-gift mr-2"></i> Agregar
                        </button>
                    </div>
                    <div id="promo-aplicada" class="hidden rounded-2xl bg-purple-500/10 border border-purple-500/30 p-4">
                        <p class="text-purple-300 text-sm font-bold mb-2">Descuento aplicado:</p>
                        <div class="flex items-center justify-between">
                            <span id="promo-nombre" class="text-white font-black text-base"></span>
                            <span id="promo-descuento" class="text-purple-400 font-black text-lg"></span>
                        </div>
                        <button id="btn-limpiar-promo" type="button" class="w-full mt-3 py-2 px-4 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-bold rounded-xl border border-red-500/30 transition-all text-xs">
                            <i class="fas fa-times mr-2"></i>Quitar
                        </button>
                    </div>
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

{{-- Modal de Confirmación Liberar Mesa --}}
<div id="modal-confirmar-liberar" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-gradient-to-br from-[#141417] to-[#0a0a0c] border border-amber-500/20 rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl animate-in fade-in zoom-in duration-300">
        <div class="flex justify-center mb-6">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-500/10 border border-amber-500/30">
                <i class="fas fa-door-open text-3xl text-amber-400"></i>
            </div>
        </div>
        
        <h2 class="text-2xl font-black text-center text-white mb-2">¿Liberar Mesa?</h2>
        <p class="text-center text-gray-400 text-sm mb-6">
            ¿Deseas liberar esta mesa? Los datos de la orden se guardarán en el historial.
        </p>
        
        <div class="flex gap-3">
            <button type="button" id="btn-cancelar-liberar" class="flex-1 py-3 px-4 bg-white/5 hover:bg-white/10 text-white font-bold rounded-2xl border border-white/10 transition-all">
                Cancelar
            </button>
            <button type="button" id="btn-confirmar-liberar" class="flex-1 py-3 px-4 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-400 hover:to-orange-500 text-black font-black rounded-2xl transition-all shadow-lg shadow-amber-500/20">
                <i class="fas fa-check mr-2"></i> Liberar
            </button>
        </div>
    </div>
</div>

{{-- Modal de Método de Pago --}}
<div id="modal-metodo" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-[#141417] border border-white/10 rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl animate-in fade-in zoom-in duration-200">
        <h2 class="text-2xl font-black text-white mb-6">Seleccionar Método de Pago</h2>
        
        <div class="space-y-3 mb-6">
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 border-white/10 hover:border-emerald-400/50 hover:bg-emerald-500/5 transition-all font-bold text-white" data-metodo="Efectivo">
                <i class="fas fa-money-bill-wave text-emerald-400 mr-3"></i> Efectivo
            </button>
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 border-white/10 hover:border-sky-400/50 hover:bg-sky-500/5 transition-all font-bold text-white" data-metodo="Transferencia">
                <i class="fas fa-bank text-sky-400 mr-3"></i> Transferencia Bancaria
            </button>
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 border-white/10 hover:border-violet-400/50 hover:bg-violet-500/5 transition-all font-bold text-white" data-metodo="Tarjeta">
                <i class="fas fa-credit-card text-violet-400 mr-3"></i> Tarjeta de Crédito
            </button>
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 border-white/10 hover:border-orange-400/50 hover:bg-orange-500/5 transition-all font-bold text-white" data-metodo="Tarjeta Débito">
                <i class="fas fa-credit-card text-orange-400 mr-3"></i> Tarjeta de Débito
            </button>
        </div>
        
        <button type="button" id="btn-cerrar-modal-metodo" class="w-full py-3 px-4 bg-white/5 hover:bg-white/10 text-white font-bold rounded-2xl border border-white/10 transition-all">
            Cancelar
        </button>
    </div>
</div>

{{-- Modal de Promociones --}}
<div id="modal-promociones" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-[#141417] border border-white/10 rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl animate-in fade-in zoom-in duration-200 max-h-[90vh] overflow-y-auto">
        <h2 class="text-2xl font-black text-white mb-6">Seleccionar Promoción</h2>
        
        <div class="space-y-3 mb-6" id="promos-list">
            {{-- Las promociones se cargarán aquí vía JavaScript --}}
            <div class="text-center py-6">
                <p class="text-gray-400 text-sm">Cargando promociones...</p>
            </div>
        </div>
        
        <div class="space-y-3">
            <button type="button" id="btn-cerrar-modal-promos" class="w-full py-3 px-4 bg-white/5 hover:bg-white/10 text-white font-bold rounded-2xl border border-white/10 transition-all">
                Cerrar
            </button>
        </div>
    </div>
</div>

{{-- Modal de Pago Exitoso --}}
<div id="modal-exito" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-gradient-to-br from-[#141417] to-[#0a0a0c] border border-emerald-500/20 rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl animate-in fade-in zoom-in duration-300">
        <div class="flex justify-center mb-6">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/10 border border-emerald-500/30 animate-pulse">
                <i class="fas fa-check text-3xl text-emerald-400"></i>
            </div>
        </div>
        
        <h2 id="modal-titulo" class="text-2xl font-black text-center text-white mb-2">¡Pago Exitoso!</h2>
        <p id="modal-descripcion" class="text-center text-gray-400 text-sm mb-6">
            Se procesó correctamente el pago de <strong id="modal-nombre-persona" class="text-emerald-400">Persona X</strong>
        </p>
        
        <div class="bg-white/5 border border-white/10 rounded-2xl p-4 mb-6 space-y-2">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-400">Monto pagado</span>
                <span class="text-white font-black" id="modal-monto-pagado">$0.00</span>
            </div>
            <div class="flex justify-between items-center text-sm pt-2 border-t border-white/10">
                <span class="text-gray-400" id="modal-etiqueta-total">Nuevo total en mesa</span>
                <span class="text-emerald-400 font-black" id="modal-nuevo-total">$0.00</span>
            </div>
        </div>
        
        <div class="flex gap-3">
            <button type="button" id="btn-cerrar-modal-exito" class="flex-1 py-3 px-4 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-black font-black rounded-2xl transition-all shadow-lg shadow-emerald-500/20">
                Continuar
            </button>
            <button type="button" id="btn-liberar-mesa-modal" class="flex-1 py-3 px-4 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-400 hover:to-red-500 text-black font-black rounded-2xl transition-all shadow-lg shadow-orange-500/20 hidden">
                <i class="fas fa-door-open mr-2"></i> Liberar Mesa
            </button>
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
        const btnTicket = document.getElementById('btn-ticket');
        const btnAbrirModal = document.getElementById('btn-abrir-modal-metodo');
        const metodoPagoInput = document.getElementById('metodo-pago');
        const metodoPagoLabel = document.getElementById('metodo-pago-label');
        const cashSection = document.getElementById('cash-section');
        const nonCashSection = document.getElementById('non-cash-section');
        const mesaId = document.getElementById('mesa-id').value;
        const ordenId = document.getElementById('orden-id').value;
        const ivaInput = document.getElementById('total-iva');
        const propinaInput = document.getElementById('total-propina');
        const propinaDisplay = document.getElementById('total-propina-display');
        const botonesPropinaPercentaje = document.querySelectorAll('.btn-propina-pct');
        const propinCustomInput = document.getElementById('propina-custom-input');

        // ========== VARIABLES PARA PROMOCIONES ==========
        const btnAgregarPromo = document.getElementById('btn-agregar-promo');
        const btnCerrarModalPromos = document.getElementById('btn-cerrar-modal-promos');
        const btnLimpiarPromo = document.getElementById('btn-limpiar-promo');
        const modalPromociones = document.getElementById('modal-promociones');
        const promosList = document.getElementById('promos-list');
        const promoLabel = document.getElementById('promo-label');
        const promoAplicada = document.getElementById('promo-aplicada');
        const promoNombreDisplay = document.getElementById('promo-nombre');
        const promoDescuentoDisplay = document.getElementById('promo-descuento');
        
        let promocionActual = null;
        let descuentoActual = 0;
        let subtotalSinDescuento = 0;

        // Total original de toda la mesa (para cuentas divididas)
        let totalMesaCompleta = parseFloat('{{ number_format($totalPagar, 2, ".", "") }}');
        let totalPagar = parseFloat('{{ number_format($totalPagar, 2, ".", "") }}');
        let montoActual = '0';
        let subtotalActual = parseFloat('{{ number_format($subtotal ?? 0, 2, ".", "") }}');
        let ivaActual = parseFloat('{{ number_format($iva ?? 0, 2, ".", "") }}');
        let propinaActual = 0;
        let cuentaActual = 1;
        let totalDividadasInfo = {};

        if (totalDividasData && totalDividasData.value) {
            try {
                totalDividadasInfo = JSON.parse(totalDividasData.value);
                if (Array.isArray(totalDividadasInfo) && totalDividadasInfo.length > 0) {
                    totalPagar = parseFloat(totalDividadasInfo[0].total);
                    subtotalActual = parseFloat(totalDividadasInfo[0].subtotal);
                    ivaActual = parseFloat(totalDividadasInfo[0].iva);
                    propinaActual = parseFloat(totalDividadasInfo[0].propina) || 0;
                    cuentaActual = 1;
                }
            } catch (e) {
                console.error('Error parsing totalDividadas:', e);
            }
        }

        const formatter = new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 2
        });

        // ========== TECLADO NUMÉRICO ==========
        botonesTeclado.forEach(boton => {
            boton.addEventListener('click', function(e) {
                e.preventDefault();
                const valor = this.getAttribute('data-value');

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

        // ========== SELECTOR DE PERSONAS (CUENTA DIVIDIDA) ==========
        botonessCuenta.forEach(boton => {
            boton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // No permitir seleccionar si ya está pagado
                if (this.getAttribute('data-pagado') === 'true') {
                    return;
                }
                
                const numeroCuenta = parseInt(this.getAttribute('data-cuenta'));
                
                // Cambiar clase activa
                botonessCuenta.forEach(btn => {
                    // Si está pagado, mantener su estilo de pagado
                    if (btn.getAttribute('data-pagado') === 'true') {
                        btn.setAttribute('style', 'background-color: #064e3b; border: 2px solid #10b981; color: #d1fae5; opacity: 0.7; cursor: not-allowed;');
                    } else {
                        btn.setAttribute('style', 'background-color: #141417; border: 2px solid #3B82F6; color: white;');
                    }
                });
                
                this.setAttribute('style', 'background-color: #3B82F6; border: 2px solid #1E3A8A; color: white; box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);');
                
                // Actualizar la cuenta actual
                cuentaActual = numeroCuenta;
                if (cuentaActualInput) {
                    cuentaActualInput.value = numeroCuenta;
                }
                
                // Cambiar productos mostrados
                const productosContainers = document.querySelectorAll('[id^="cuenta-"]');
                productosContainers.forEach(container => {
                    container.classList.add('hidden');
                });
                
                const cuentaActualContainer = document.getElementById(`cuenta-${numeroCuenta}`);
                if (cuentaActualContainer) {
                    cuentaActualContainer.classList.remove('hidden');
                }
                
                // Actualizar totales de esta cuenta
                if (Array.isArray(totalDividadasInfo) && totalDividadasInfo.length > 0) {
                    const cuentaInfo = totalDividadasInfo.find(c => c.numero_cuenta === numeroCuenta);
                    if (cuentaInfo) {
                        totalPagar = parseFloat(cuentaInfo.total) || 0;
                        subtotalActual = parseFloat(cuentaInfo.subtotal) || 0;
                        ivaActual = parseFloat(cuentaInfo.iva) || 0;
                        propinaActual = parseFloat(cuentaInfo.propina) || 0;
                        
                        // Actualizar displays
                        const totalSubtotalDisplay = document.getElementById('total-subtotal');
                        if (totalSubtotalDisplay) {
                            totalSubtotalDisplay.textContent = '$' + subtotalActual.toFixed(2);
                        }
                        
                        if (ivaInput) {
                            ivaInput.value = ivaActual.toFixed(2);
                        }
                        
                        recalcularTotal();
                        actualizarVista();
                    }
                }
            });
        });

        // ========== FUNCIÓN PARA RECALCULAR TOTAL ==========
        function recalcularTotal() {
            const subtotal = subtotalActual || 0;
            const iva = parseFloat(ivaInput?.value) || ivaActual || 0;
            const propina = parseFloat(propinaInput?.value) || propinaActual || 0;
            
            // Incluir descuento de promoción
            const descuento = descuentoActual || 0;
            const nuevoTotal = subtotal + iva + propina - descuento;
            
            totalPagar = Math.round(nuevoTotal * 100) / 100;
            
            const totalDerechaElement = document.getElementById('total-pagar-derecha');
            if (totalDerechaElement) {
                totalDerechaElement.textContent = '$' + totalPagar.toFixed(2);
            }
            
            if (totalPagarDisplay) {
                totalPagarDisplay.textContent = '$' + totalPagar.toFixed(2);
            }
            
            if (propinaDisplay) {
                propinaDisplay.textContent = '$' + propina.toFixed(2);
            }
            
            actualizarVista();
        }

        // ========== BOTONES DE PROPINA ==========
        botonesPropinaPercentaje.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const percent = this.getAttribute('data-percent');
                
                botonesPropinaPercentaje.forEach(b => {
                    b.classList.remove('bg-blue-500/20', 'border-blue-400');
                    b.classList.add('bg-white/5', 'border-white/10');
                });
                
                this.classList.remove('bg-white/5', 'border-white/10');
                this.classList.add('bg-blue-500/20', 'border-blue-400');
                
                if (percent === 'custom') {
                    propinCustomInput.classList.remove('hidden');
                    if (propinaInput) {
                        propinaInput.focus();
                    }
                } else {
                    const porcentaje = parseInt(percent) / 100;
                    const nuevaPropina = Math.round(subtotalActual * porcentaje * 100) / 100;
                    propinCustomInput.classList.add('hidden');
                    if (propinaInput) {
                        propinaInput.value = nuevaPropina.toFixed(2);
                    }
                }
                
                recalcularTotal();
            });
        });

        // ========== CAMBIOS EN IVA Y PROPINA ==========
        if (ivaInput) {
            ivaInput.addEventListener('change', function() {
                ivaActual = parseFloat(this.value) || 0;
                recalcularTotal();
            });
            ivaInput.addEventListener('input', function() {
                ivaActual = parseFloat(this.value) || 0;
                recalcularTotal();
            });
        }

        if (propinaInput) {
            propinaInput.addEventListener('change', function() {
                recalcularTotal();
            });
            propinaInput.addEventListener('input', function() {
                recalcularTotal();
            });
        }

        // ========== ACTUALIZAR VISTA ==========
        function actualizarVista() {
            const numero = parseFloat(montoActual) || 0;
            display.textContent = formatter.format(numero);
            const cambio = numero - totalPagar;
            displayCambio.textContent = formatter.format(Math.max(cambio, 0));
        }

        // ========== BOTÓN TICKET ==========
        if (btnTicket) {
            btnTicket.addEventListener('click', function(e) {
                e.preventDefault();
                alert('Función de impresión en desarrollo');
            });
        }

        // ========== BOTÓN FINALIZAR PAGO ==========
        if (btnFinalizar) {
            btnFinalizar.addEventListener('click', function(e) {
                e.preventDefault();
                
                const montoIngresado = parseFloat(montoActual) || 0;
                
                if (montoIngresado === 0) {
                    showNotification('Por favor ingresa un monto válido', 'error');
                    return;
                }
                
                if (montoIngresado < totalPagar) {
                    showNotification(`Monto insuficiente. Se requieren $${totalPagar.toFixed(2)}`, 'warning');
                    return;
                }
                
                // Procesar pago exitoso
                procesarPagoExitoso(cuentaActual, totalPagar, montoIngresado);
            });
        }
        
        // ========== PROCESAR PAGO EXITOSO ==========
        function procesarPagoExitoso(numeroCuenta, montoPersona, montoIngresado) {
            // Obtener el orden_id correcto para esta persona (en cuenta dividida)
            let ordenIdActual = ordenId;
            if (Array.isArray(totalDividadasInfo) && totalDividadasInfo.length > 0) {
                const cuentaInfo = totalDividadasInfo.find(c => c.numero_cuenta === numeroCuenta);
                if (cuentaInfo && cuentaInfo.orden_id) {
                    ordenIdActual = cuentaInfo.orden_id;
                }
            }
            
            // Enviar el pago al servidor
            const pagoData = {
                mesa_id: mesaId,
                orden_id: ordenIdActual || null,
                efectivo: montoIngresado,
                metodo_pago: metodoPagoInput.value || 'Efectivo',
                referencia: document.getElementById('referencia')?.value || null,
                iva: ivaActual,
                propina: parseFloat(propinaInput?.value) || 0,
                promocion_id: promocionActual?.id || null,
                descuento: descuentoActual || 0
            };

            fetch('{{ route("admin.caja.api.pagar") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(pagoData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Verificar si es mesa dividida (hay botones de cuentas)
                    const esMesaDividida = document.querySelectorAll('.btn-cuenta').length > 0;
                    
                    if (esMesaDividida) {
                        // MESA DIVIDIDA: Procesar por personas
                        // Marcar persona como pagada
                        const btnCuentaPagada = document.querySelector(`.btn-cuenta[data-cuenta="${numeroCuenta}"]`);
                        if (btnCuentaPagada) {
                            btnCuentaPagada.setAttribute('data-pagado', 'true');
                            btnCuentaPagada.setAttribute('style', 'background-color: #064e3b; border: 2px solid #10b981; color: #d1fae5; opacity: 0.7; cursor: not-allowed;');
                            btnCuentaPagada.disabled = true;
                        }
                        
                        // Actualizar el total general (panel izquierdo)
                        const totalPagarDisplay = document.getElementById('total-pagar');
                        if (totalPagarDisplay) {
                            let totalActualMesa = parseFloat(totalPagarDisplay.textContent.replace('$', '')) || totalMesaCompleta;
                            totalActualMesa = Math.round((totalActualMesa - montoPersona) * 100) / 100;
                            totalPagarDisplay.textContent = '$' + totalActualMesa.toFixed(2);
                        }
                        
                        // Mostrar modal de éxito
                        showSuccessModal(numeroCuenta, montoPersona, totalPagarDisplay?.textContent || '$0.00');
                        
                        // Resetear el display de ingreso
                        montoActual = '0';
                        actualizarVista();
                        
                        // Buscar siguiente persona sin pagar
                        const siguientePersona = encontrarSiguientePersonaSinPagar();
                        if (siguientePersona) {
                            // Seleccionar automáticamente la siguiente persona
                            setTimeout(() => {
                                siguientePersona.click();
                            }, 1500);
                        } else {
                            // Si no hay más personas, mostrar botón "Liberar Mesa"
                            mostrarBotonLiberarMesa();
                        }
                    } else {
                        // MESA NORMAL: Mostrar botón "Liberar Mesa"
                        showSuccessModal(1, totalPagar, '$0.00');
                        mostrarBotonLiberarMesa();
                    }
                } else {
                    console.error('Error en respuesta:', data.message);
                    showNotification(data.message || 'Error procesando pago', 'error');
                }
            })
            .catch(error => {
                console.error('Error en AJAX:', error);
                showNotification('Error de conexión al procesar pago', 'error');
            });
        }
        
        // ========== ENCONTRAR SIGUIENTE PERSONA SIN PAGAR ==========
        function encontrarSiguientePersonaSinPagar() {
            const botonesCuenta = document.querySelectorAll('.btn-cuenta');
            for (let btn of botonesCuenta) {
                if (btn.getAttribute('data-pagado') !== 'true') {
                    return btn;
                }
            }
            return null;
        }
        
        // ========== MOSTRAR MODAL DE ÉXITO ==========
        function showSuccessModal(numeroCuenta, montoPagado, nuevoTotal) {
            const modalExito = document.getElementById('modal-exito');
            const modalNombrePersona = document.getElementById('modal-nombre-persona');
            const modalMontoPagado = document.getElementById('modal-monto-pagado');
            const modalNuevoTotal = document.getElementById('modal-nuevo-total');
            const btnCerrarExito = document.getElementById('btn-cerrar-modal-exito');
            
            if (modalNombrePersona) {
                modalNombrePersona.textContent = `Persona ${numeroCuenta}`;
            }
            if (modalMontoPagado) {
                modalMontoPagado.textContent = '$' + montoPagado.toFixed(2);
            }
            if (modalNuevoTotal) {
                modalNuevoTotal.textContent = nuevoTotal;
            }
            
            if (modalExito) {
                modalExito.classList.remove('hidden');
            }
            
            if (btnCerrarExito) {
                btnCerrarExito.onclick = function() {
                    // Verificar si todas las personas están pagadas
                    const personasRestantes = encontrarSiguientePersonaSinPagar();
                    
                    if (!personasRestantes) {
                        // Todas pagadas, redirigir a caja
                        window.location.href = '{{ route("admin.caja.index") }}';
                    } else {
                        // Aún hay personas, solo cerrar el modal
                        modalExito.classList.add('hidden');
                    }
                };
            }
        }
        
        // ========== MOSTRAR BOTÓN LIBERAR MESA ==========
        function mostrarBotonLiberarMesa() {
            const btnContinuar = document.getElementById('btn-cerrar-modal-exito');
            const btnLiberar = document.getElementById('btn-liberar-mesa-modal');
            
            if (btnContinuar) {
                btnContinuar.classList.add('hidden');
            }
            if (btnLiberar) {
                btnLiberar.classList.remove('hidden');
                // Remover eventos anteriores y agregar nuevo
                btnLiberar.replaceWith(btnLiberar.cloneNode(true));
                const btnLiberarNuevo = document.getElementById('btn-liberar-mesa-modal');
                if (btnLiberarNuevo) {
                    btnLiberarNuevo.addEventListener('click', function(e) {
                        e.preventDefault();
                        liberarMesa();
                    });
                }
            }
        }
        
        // ========== LIBERAR MESA ==========
        function liberarMesa() {
            const mesaId = document.getElementById('mesa-id').value;
            const modalExito = document.getElementById('modal-exito');
            const modalConfirmar = document.getElementById('modal-confirmar-liberar');
            const btnConfirmar = document.getElementById('btn-confirmar-liberar');
            const btnCancelar = document.getElementById('btn-cancelar-liberar');
            
            // Cerrar modal de éxito
            if (modalExito) {
                modalExito.classList.add('hidden');
            }
            
            // Mostrar modal de confirmación
            if (modalConfirmar) {
                modalConfirmar.classList.remove('hidden');
            }
            
            // Botón cancelar - Remover eventos anteriores
            if (btnCancelar) {
                btnCancelar.replaceWith(btnCancelar.cloneNode(true));
                const btnCancelarNuevo = document.getElementById('btn-cancelar-liberar');
                if (btnCancelarNuevo) {
                    btnCancelarNuevo.addEventListener('click', function(e) {
                        e.preventDefault();
                        const modal = document.getElementById('modal-confirmar-liberar');
                        if (modal) {
                            modal.classList.add('hidden');
                        }
                        // Volver a mostrar modal de éxito
                        if (modalExito) {
                            modalExito.classList.remove('hidden');
                        }
                    });
                }
            }
            
            // Botón confirmar - Remover eventos anteriores
            if (btnConfirmar) {
                btnConfirmar.replaceWith(btnConfirmar.cloneNode(true));
                const btnConfirmarNuevo = document.getElementById('btn-confirmar-liberar');
                if (btnConfirmarNuevo) {
                    btnConfirmarNuevo.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        fetch('{{ route("admin.caja.api.liberar-mesa") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ mesa_id: mesaId })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error en la respuesta del servidor');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                showNotification('Mesa liberada correctamente', 'success');
                                setTimeout(() => {
                                    window.location.href = '{{ route("admin.caja.index") }}';
                                }, 1000);
                            } else {
                                showNotification(data.message || 'Error liberando mesa', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification('Error de conexión al liberar mesa', 'error');
                        });
                    });
                }
            }
        }
        
        // ========== NOTIFICATION SIMPLE ==========
        function showNotification(message, type = 'error') {
            // Simple fallback - puede mejorarse con un toast system
            console.log(`[${type.toUpperCase()}] ${message}`);
        }

        // ========== FUNCIONES DE PROMOCIONES ==========
        function cargarPromociones() {
            fetch('/admin/caja/api/promociones-activas')
                .then(response => response.json())
                .then(data => {
                    if (data.promociones && data.promociones.length > 0) {
                        promosList.innerHTML = '';
                        data.promociones.forEach(promo => {
                            const descuentoMostrado = promo.tipo_promocion === 'porcentaje' 
                                ? `${promo.valor_descuento}%` 
                                : `$${promo.valor_descuento}`;
                            
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'promo-btn w-full text-left p-4 rounded-2xl border-2 border-white/10 hover:border-purple-400/50 hover:bg-purple-500/5 transition-all font-bold text-white';
                            btn.innerHTML = `
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-gift text-purple-400 text-lg"></i>
                                        <div>
                                            <p class="text-white font-black">${promo.nombre}</p>
                                            <p class="text-xs text-gray-400">${promo.descripcion || 'Promoción especial'}</p>
                                        </div>
                                    </div>
                                    <span class="text-purple-400 font-black text-lg">${descuentoMostrado}</span>
                                </div>
                            `;
                            btn.dataset.promoid = promo.id;
                            btn.dataset.tipo = promo.tipo_promocion;
                            btn.dataset.valor = promo.valor_descuento;
                            btn.dataset.nombre = promo.nombre;
                            btn.addEventListener('click', () => aplicarPromocion(promo.id, promo.nombre, promo.tipo_promocion, promo.valor_descuento));
                            promosList.appendChild(btn);
                        });
                    } else {
                        promosList.innerHTML = '<div class="text-center py-6"><p class="text-gray-400 text-sm">No hay promociones activas disponibles.</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error cargando promociones:', error);
                    promosList.innerHTML = '<div class="text-center py-6"><p class="text-red-400 text-sm">Error al cargar promociones</p></div>';
                });
        }

        function aplicarPromocion(promoId, promoNombre, tipoPromocion, valorDescuento) {
            promocionActual = {
                id: promoId,
                nombre: promoNombre,
                tipo: tipoPromocion,
                valor: valorDescuento
            };

            // Calcular el descuento
            if (tipoPromocion === 'porcentaje') {
                descuentoActual = Math.round(subtotalActual * (valorDescuento / 100) * 100) / 100;
            } else {
                descuentoActual = Math.round(parseFloat(valorDescuento) * 100) / 100;
            }

            // Actualizar la UI
            promoLabel.classList.add('hidden');
            promoAplicada.classList.remove('hidden');
            promoNombreDisplay.textContent = promoNombre;
            
            if (tipoPromocion === 'porcentaje') {
                promoDescuentoDisplay.textContent = `-${valorDescuento}%`;
            } else {
                promoDescuentoDisplay.textContent = `-$${parseFloat(valorDescuento).toFixed(2)}`;
            }

            // Recalcular el total
            recalcularTotal();

            // Cerrar modal
            modalPromociones.classList.add('hidden');

            showNotification(`Promoción "${promoNombre}" aplicada correctamente`, 'success');
        }

        function limpiarPromocion() {
            promocionActual = null;
            descuentoActual = 0;
            promoLabel.classList.remove('hidden');
            promoAplicada.classList.add('hidden');
            recalcularTotal();
        }

        // ========== SELECTOR DE MÉTODO DE PAGO ==========
        const btnCerrarModal = document.getElementById('btn-cerrar-modal-metodo');
        const modalMetodo = document.getElementById('modal-metodo');
        const metodoButtons = document.querySelectorAll('.metodo-btn');
        
        if (btnAbrirModal) {
            btnAbrirModal.addEventListener('click', function(e) {
                e.preventDefault();
                modalMetodo.classList.remove('hidden');
            });
        }
        
        if (btnCerrarModal) {
            btnCerrarModal.addEventListener('click', function(e) {
                e.preventDefault();
                modalMetodo.classList.add('hidden');
            });
        }
        
        metodoButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const metodo = this.getAttribute('data-metodo');
                
                metodoPagoInput.value = metodo;
                metodoPagoLabel.textContent = metodo;
                
                // Mostrar/ocultar secciones según método
                if (metodo === 'Efectivo') {
                    cashSection.classList.remove('hidden');
                    nonCashSection.classList.add('hidden');
                } else {
                    cashSection.classList.add('hidden');
                    nonCashSection.classList.remove('hidden');
                }
                
                modalMetodo.classList.add('hidden');
            });
        });

        // Cerrar modal al hacer clic fuera
        modalMetodo.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        // ========== EVENT LISTENERS DE PROMOCIONES ==========
        if (btnAbrirModalPromos) {
            btnAbrirModalPromos.addEventListener('click', function(e) {
                e.preventDefault();
                cargarPromociones();
                modalPromociones.classList.remove('hidden');
            });
        }

        if (btnAgregarPromo) {
            btnAgregarPromo.addEventListener('click', function(e) {
                e.preventDefault();
                cargarPromociones();
                modalPromociones.classList.remove('hidden');
            });
        }

        if (btnCerrarModalPromos) {
            btnCerrarModalPromos.addEventListener('click', function(e) {
                e.preventDefault();
                modalPromociones.classList.add('hidden');
            });
        }

        if (btnLimpiarPromo) {
            btnLimpiarPromo.addEventListener('click', function(e) {
                e.preventDefault();
                limpiarPromocion();
                btnLimpiarPromo.classList.add('hidden');
            });
        }

        // Cerrar modal de promociones al hacer clic fuera
        modalPromociones.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        // Inicializar vista
        actualizarVista();

        // ========== CARGAR PROMOCIONES AL INICIALIZAR ==========
        cargarPromociones();
    });
</script>
@endsection