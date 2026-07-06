{{-- ========================================== --}}
{{-- COLUMNA 1: ACCIONES RÁPIDAS                --}}
{{-- ========================================== --}}
<aside class="w-[170px] md:w-[190px] xl:w-[220px] flex-shrink-0 h-full flex flex-col bg-[var(--bg-base)] border-r border-[var(--border-color)] p-4 z-20">

    <div class="flex items-center gap-2 mb-6">
        <button type="button" onclick="window.location.href='{{ route('mesero.dashboard') }}'" class="flex-1 h-10 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-semibold text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 shadow-sm">
            <i class="fas fa-arrow-left text-[var(--text-muted)] text-[10px]"></i> Mesas
        </button>
        <button type="button" onclick="toggleTheme()" class="w-10 h-10 shrink-0 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
            <i id="themeIcon" class="fas fa-sun text-[11px] group-hover:rotate-45 transition-transform duration-500"></i>
        </button>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div class="flex flex-col">
            <span class="text-[9px] font-bold uppercase tracking-[0.2em] text-[var(--text-muted)] mb-1">Activa</span>
            <h3 class="text-2xl font-black tracking-tight text-[var(--text-main)] leading-none">Mesa {{ $mesa->numero ?? '12M' }}</h3>
        </div>
        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)] border border-[var(--bg-base)]"></div>
    </div>

    <div class="grid grid-cols-2 gap-2 flex-1 overflow-y-auto hide-scroll pb-2">
        <button type="button" onclick="ajustarPersonas()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
            <i class="fas fa-users text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
            <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Personas</span>
            <span id="txtPersonas" class="text-xs font-bold text-[var(--text-main)] mt-0.5">{{ $mesa->capacidad ?? 1 }}</span>
        </button>

        <button type="button" onclick="imprimirPrecuenta()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
            <i class="fas fa-receipt text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
            <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors leading-tight text-center">Pre<br>Cuenta</span>
        </button>

        <button type="button" onclick="agregarNota()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
            <i class="fas fa-pen text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
            <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Nota</span>
        </button>

        <button type="button" onclick="aplicarDescuento()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
            <i class="fas fa-percent text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
            <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Descuento</span>
        </button>

        <button type="button" id="btn-gramaje" onclick="ajustarGramaje()" class="relative flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
            <i class="fas fa-weight-scale text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
            <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Gramaje</span>
            <span id="indicador-gramaje-pendiente" class="hidden absolute top-2 right-2 bg-orange-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full shadow-md"></span>
        </button>

        {{-- OJO Sebastian: este botón "Traspaso" llama a llamarCapitan(), igual que el
             botón "Capitán" de abajo. Lo dejé tal cual estaba en tu original (no
             cambié comportamiento), pero probablemente sea un copy/paste y debería
             abrir un flujo de traspaso de mesa distinto en vez de pedir el NIP de
             capitán. Revísalo y dime si quieres que lo separe. --}}
        <button type="button" onclick="llamarCapitan()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
            <i class="fas fa-exchange-alt text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
            <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Traspaso</span>
        </button>

        <button type="button" onclick="mostrarPromociones()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
            <i class="fas fa-tag text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
            <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Promos</span>
        </button>

        <button type="button" onclick="marcharTiempos()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
            <i class="fas fa-fire-burner text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
            <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Marchar</span>
        </button>

        @if($esCapitan ?? false)
            <button type="button" onclick="llamarCapitan()" class="col-span-2 mt-1 h-12 flex items-center justify-center gap-2 rounded-[16px] bg-[var(--accent)] border border-[var(--border-color)] hover:opacity-90 transition-all active:scale-95 group text-white shadow-md">
                <i class="fas fa-shield-alt text-[11px]"></i>
                <span class="text-[10px] font-bold uppercase tracking-widest">Capitán</span>
            </button>
        @endif
    </div>

    <button type="button" onclick="limpiarTicket()" class="mt-4 w-full h-12 rounded-[16px] border border-red-500/20 text-red-500 hover:bg-red-500 hover:text-white transition-all active:scale-95 flex items-center justify-center gap-2 font-bold text-[10px] uppercase tracking-widest shadow-sm">
        <i class="fas fa-trash-alt text-[11px]"></i> Eliminar Todo
    </button>
</aside>

{{-- ========================================== --}}
{{-- COLUMNA 2: TICKET / COMANDA (CENTRAL)      --}}
{{-- ========================================== --}}
<section class="w-[280px] md:w-[320px] xl:w-[360px] flex-shrink-0 h-full flex flex-col bg-[var(--bg-base)] border-r border-[var(--border-color)] relative z-10 shadow-[20px_0_40px_-15px_rgba(0,0,0,0.15)]">

    <div class="p-4 border-b border-[var(--border-color)] flex flex-col gap-3 bg-[var(--bg-base)]">
        <div class="relative flex w-full bg-[var(--bg-panel)] p-1 rounded-xl border border-[var(--border-color)] shadow-sm">
            <div class="absolute inset-y-1 left-1 right-1 pointer-events-none">
                <div id="tab-slider" class="h-full w-1/3 rounded-lg bg-[var(--text-main)] shadow-sm transition-transform duration-300 ease-out"></div>
            </div>
            <button type="button" onclick="cambiarTab('nueva-orden', this)" id="btn-tab-nueva-orden" class="relative z-10 flex-1 py-1.5 text-[10px] font-bold text-[var(--bg-base)] transition-colors outline-none">Orden</button>
            <button type="button" onclick="cambiarTab('enviados', this)" id="btn-tab-enviados" class="relative z-10 flex-1 py-1.5 text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors outline-none">Enviado</button>
            <button type="button" onclick="cambiarTab('comanda', this)" id="btn-tab-comanda" class="relative z-10 flex-1 py-1.5 text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors outline-none">Total</button>
        </div>

        <div class="flex items-center justify-between">
            <span class="text-[10px] font-medium text-[var(--text-muted)]">Tiempos:</span>
            <div class="flex gap-1 bg-[var(--bg-panel)] p-1 rounded-lg border border-[var(--border-color)] shadow-sm">
                <button type="button" onclick="cambiarTiempoGlobal('sin-tiempo')" id="tiempo-global-sin" class="w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold bg-[var(--text-main)] text-[var(--bg-base)] transition-all">S</button>
                <button type="button" onclick="cambiarTiempoGlobal('primer-tiempo')" id="tiempo-global-1" class="w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all">1</button>
                <button type="button" onclick="cambiarTiempoGlobal('segundo-tiempo')" id="tiempo-global-2" class="w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all">2</button>
                <button type="button" onclick="cambiarTiempoGlobal('tercer-tiempo')" id="tiempo-global-3" class="w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all">3</button>
            </div>
        </div>
    </div>

    {{-- VISTA 1: NUEVA ORDEN --}}
    <div id="vista-nueva-orden" class="flex-1 overflow-y-auto hide-scroll p-4 flex flex-col relative bg-[var(--bg-base)]">
        <div class="flex justify-between text-[10px] font-bold text-[var(--text-muted)] mb-3 px-1">
            <span>CANT. / PLATILLO</span>
            <span>MONTO</span>
        </div>

        <div id="listaTicket" class="flex flex-col gap-2.5 pb-4"></div>

        <div id="estadoVacio" class="flex-1 flex flex-col items-center justify-center opacity-40 mt-10 transition-opacity duration-300">
            <i class="fas fa-plate-wheat text-3xl text-[var(--text-muted)] mb-3"></i>
            <p class="text-[11px] font-medium text-[var(--text-muted)] text-center">Sin productos.<br>Comienza a agregar.</p>
        </div>
    </div>

    {{-- VISTA 2: ENVIADOS --}}
    <div id="vista-enviados" class="hidden flex-1 overflow-y-auto hide-scroll p-4 flex-col relative bg-[var(--bg-base)]">
        @if(isset($platillosEnviados) && count($platillosEnviados) > 0)
            <div class="flex flex-col gap-2">
                @foreach($platillosEnviados as $item)
                    <div class="bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-xl p-3 flex justify-between items-center shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-main)] text-[11px] font-bold flex items-center justify-center">{{ $item->cantidad ?? 1 }}</span>
                            <span class="text-[12px] font-medium text-[var(--text-main)]">{{ $item->nombre ?? 'Platillo' }}</span>
                        </div>
                        <div class="text-right">
                            @if(($item->estado ?? 'enviado') == 'preparando')
                                <span class="text-[9px] font-bold text-orange-500 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-orange-500 shadow-[0_0_5px_rgba(249,115,22,0.5)]"></span> Cocina</span>
                            @elseif(($item->estado ?? 'enviado') == 'listo')
                                <span class="text-[9px] font-bold text-emerald-500 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.5)]"></span> Listo</span>
                            @else
                                <span class="text-[9px] font-bold text-[var(--text-muted)] flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[var(--text-muted)]"></span> Enviado</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center opacity-40 mt-10">
                <i class="fas fa-clock text-3xl text-[var(--text-muted)] mb-3"></i>
                <p class="text-[11px] font-medium text-[var(--text-muted)] text-center">No hay órdenes en proceso.</p>
            </div>
        @endif
    </div>

    {{-- VISTA 3: COMANDA TOTAL --}}
    <div id="vista-comanda" class="hidden flex-1 overflow-y-auto hide-scroll p-4 flex-col relative bg-[var(--bg-base)]">
        <div id="items-db-total" class="flex flex-col gap-2">
            @if(isset($platillosEnviados) && count($platillosEnviados) > 0)
                <div class="text-[10px] font-bold text-[var(--text-muted)] mb-2 px-1 uppercase tracking-wider">Consumo Procesado</div>
                @foreach($platillosEnviados as $item)
                    <div class="flex justify-between items-center p-2 rounded-xl hover:bg-[var(--hover-bg)] transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="text-[11px] text-[var(--text-muted)] font-bold">{{ $item->cantidad ?? 1 }}x</span>
                            <span class="text-[12px] font-medium text-[var(--text-main)]">{{ $item->nombre ?? 'Platillo' }}</span>
                        </div>
                        <span class="text-[12px] font-bold text-[var(--text-main)]">${{ number_format(($item->precio ?? 0) * ($item->cantidad ?? 1), 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between items-center mt-2 pt-3 border-t border-[var(--border-color)] px-2">
                    <span class="text-[11px] font-medium text-[var(--text-muted)]">Subtotal en mesa:</span>
                    <span class="text-[12px] font-bold text-[var(--text-main)]">
                        ${{ number_format(collect($platillosEnviados)->sum(function($i) { return ($i->precio ?? 0) * ($i->cantidad ?? 1); }), 2) }}
                    </span>
                </div>
            @endif
        </div>

        <div id="lista-comanda-total" class="flex flex-col gap-2 mt-4"></div>

        <div id="estadoVacioComanda" class="flex-1 flex-col items-center justify-center opacity-40 mt-10 {{ (isset($platillosEnviados) && count($platillosEnviados) > 0) ? 'hidden' : 'flex' }}">
            <i class="fas fa-receipt text-3xl text-[var(--text-muted)] mb-3"></i>
            <p class="text-[11px] font-medium text-[var(--text-muted)] text-center">No hay cuenta registrada.</p>
        </div>
    </div>

    {{-- FOOTER DE TOTALES (PREMIUM) --}}
    <div class="p-5 border-t border-[var(--border-color)] bg-[var(--bg-panel)] flex-shrink-0 z-20">
        <div class="flex justify-between items-center mb-1">
            <span class="text-[11px] text-[var(--text-muted)] font-medium">Subtotal Nuevos</span>
            <span class="text-[12px] font-bold text-[var(--text-main)]" id="txtSubtotal">$0.00</span>
        </div>
        <div class="flex justify-between items-center mb-4">
            <span class="text-[11px] text-[var(--text-muted)] font-medium">IVA (16%)</span>
            <span class="text-[12px] font-bold text-[var(--text-main)]" id="txtIva">$0.00</span>
        </div>

        <div class="flex justify-between items-end mb-5 pt-4 border-t border-[var(--border-color)]">
            <span class="text-[13px] font-bold text-[var(--text-main)]">Total a Pagar</span>
            <span class="text-3xl font-black text-[#3b82f6] tracking-tighter" id="txtTotal">$0.00</span>
        </div>

        <button type="button" id="btn-enviar" onclick="enviarACocina()" class="w-full h-12 rounded-xl bg-gradient-to-r from-[#3b82f6] to-[#2563eb] text-white text-[12px] font-bold tracking-wide transition-all shadow-[0_8px_20px_-5px_rgba(59,130,246,0.5)] hover:shadow-[0_8px_25px_-5px_rgba(59,130,246,0.6)] active:scale-95 flex items-center justify-center gap-2 outline-none">
            <i class="fas fa-paper-plane text-sm"></i>
            <span>Enviar Orden</span>
        </button>
        <p id="mensajeMesaDestino" class="mt-3 text-[10px] font-bold text-[var(--text-muted)] text-center uppercase tracking-widest">Enviando a Mesa {{ $mesa->numero ?? '12M' }}</p>
    </div>
</section>