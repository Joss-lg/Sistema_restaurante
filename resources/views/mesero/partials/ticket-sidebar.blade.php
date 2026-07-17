{{-- ========================================== --}}
{{-- COLUMNA 1: ACCIONES RÁPIDAS                --}}
{{-- Mejoras: focus-visible en botones táctiles,
     borde/resplandor sutil en el header de mesa,
     transición al cambiar de panel en móvil.      --}}
{{-- ========================================== --}}
<aside id="col-acciones" class="col-mobile-panel hidden md:flex w-full md:w-[170px] xl:w-[220px] flex-shrink-0 h-full flex-col bg-[var(--bg-base)] border-r border-[var(--border-color)] p-4 pb-24 md:pb-4 z-20 overflow-y-auto hide-scroll">

    <div class="flex items-center gap-2 mb-6">
        <button type="button" onclick="window.location.href='{{ route('mesero.dashboard') }}'" class="flex-1 h-11 md:h-10 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-semibold text-[11px] md:text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] hover:shadow-md transition-all duration-150 active:scale-95 shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/50">
            <i class="fas fa-arrow-left text-[var(--text-muted)] text-[10px]"></i> Mesas
        </button>
        <button type="button" onclick="toggleTheme()" class="w-11 h-11 md:w-10 md:h-10 shrink-0 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--border-highlight)] hover:shadow-md transition-all duration-150 active:scale-95 group shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/50">
            <i id="themeIcon" class="fas fa-sun text-xs md:text-[11px] group-hover:rotate-45 transition-transform duration-500"></i>
        </button>
    </div>

    <div class="flex items-center justify-between mb-6 p-3 rounded-2xl bg-gradient-to-br from-[var(--bg-panel)] to-transparent border border-[var(--border-color)] shadow-sm">
        <div class="flex flex-col">
            <span class="text-[10px] md:text-[9px] font-bold uppercase tracking-[0.2em] text-[var(--text-muted)] mb-1">Activa</span>
            <h3 class="text-3xl md:text-2xl font-black tracking-tight text-[var(--text-main)] leading-none">Mesa {{ $mesa->numero ?? '12M' }}</h3>
        </div>
        <div class="relative flex items-center justify-center">
            <div class="absolute w-4 h-4 rounded-full bg-emerald-500/30 animate-pulse"></div>
            <div class="relative w-3 h-3 md:w-2.5 md:h-2.5 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.6)] border border-[var(--bg-base)]"></div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 md:gap-2 flex-1 overflow-y-auto hide-scroll pb-2">
        <button type="button" onclick="ajustarPersonas()" class="flex flex-col items-center justify-center p-4 md:p-3 rounded-[18px] md:rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-blue-500/30 hover:shadow-md transition-all duration-150 active:scale-95 group shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/50">
            <i class="fas fa-users text-[var(--text-muted)] group-hover:text-blue-500 mb-2 text-base md:text-sm transition-colors duration-150"></i>
            <span class="text-[10px] md:text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Personas</span>
            <span id="txtPersonas" class="text-sm md:text-xs font-bold text-[var(--text-main)] mt-0.5">{{ $mesa->capacidad ?? 1 }}</span>
        </button>

        <button type="button" onclick="imprimirPrecuenta()" class="flex flex-col items-center justify-center p-4 md:p-3 rounded-[18px] md:rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-blue-500/30 hover:shadow-md transition-all duration-150 active:scale-95 group shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/50">
            <i class="fas fa-receipt text-[var(--text-muted)] group-hover:text-blue-500 mb-2 text-base md:text-sm transition-colors duration-150"></i>
            <span class="text-[10px] md:text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors leading-tight text-center">Pre<br>Cuenta</span>
        </button>

        <button type="button" onclick="agregarNota()" class="flex flex-col items-center justify-center p-4 md:p-3 rounded-[18px] md:rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-blue-500/30 hover:shadow-md transition-all duration-150 active:scale-95 group shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/50">
            <i class="fas fa-pen text-[var(--text-muted)] group-hover:text-blue-500 mb-2 text-base md:text-sm transition-colors duration-150"></i>
            <span class="text-[10px] md:text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Nota</span>
        </button>

        <button type="button" onclick="aplicarDescuento()" class="flex flex-col items-center justify-center p-4 md:p-3 rounded-[18px] md:rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-blue-500/30 hover:shadow-md transition-all duration-150 active:scale-95 group shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/50">
            <i class="fas fa-percent text-[var(--text-muted)] group-hover:text-blue-500 mb-2 text-base md:text-sm transition-colors duration-150"></i>
            <span class="text-[10px] md:text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Descuento</span>
        </button>
        

        <button type="button" id="btn-gramaje" onclick="ajustarGramaje()" class="relative flex flex-col items-center justify-center p-4 md:p-3 rounded-[18px] md:rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-orange-500/30 hover:shadow-md transition-all duration-150 active:scale-95 group shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-500/50">
            <i class="fas fa-weight-scale text-[var(--text-muted)] group-hover:text-orange-500 mb-2 text-base md:text-sm transition-colors duration-150"></i>
            <span class="text-[10px] md:text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Gramaje</span>
            <span id="indicador-gramaje-pendiente" class="hidden absolute top-2 right-2 bg-gradient-to-b from-orange-500 to-orange-600 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full shadow-md shadow-orange-500/30"></span>
        </button>

        {{-- OJO Sebastian: este botón "Traspaso" llama a llamarCapitan(), igual que el
             botón "Capitán" de abajo. Lo dejé tal cual estaba en tu original (no
             cambié comportamiento), pero probablemente sea un copy/paste y debería
             abrir un flujo de traspaso de mesa distinto en vez de pedir el NIP de
             capitán. Revísalo y dime si quieres que lo separe. --}}
        <button type="button" onclick="llamarCapitan()" class="flex flex-col items-center justify-center p-4 md:p-3 rounded-[18px] md:rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-indigo-500/30 hover:shadow-md transition-all duration-150 active:scale-95 group shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/50">
            <i class="fas fa-exchange-alt text-[var(--text-muted)] group-hover:text-indigo-500 mb-2 text-base md:text-sm transition-colors duration-150"></i>
            <span class="text-[10px] md:text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Traspaso</span>
        </button>

        {{-- Le quitamos el col-span-2 a Promos para que quede abajo en su propia fila junto a lo que necesites después --}}
        <button type="button" onclick="mostrarPromociones()" class="col-span-2 flex flex-col items-center justify-center p-4 md:p-3 rounded-[18px] md:rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-blue-500/30 hover:shadow-md transition-all duration-150 active:scale-95 group shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/50">
            <i class="fas fa-tag text-[var(--text-muted)] group-hover:text-blue-500 mb-2 text-base md:text-sm transition-colors duration-150"></i>
            <span class="text-[10px] md:text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Promos</span>
        </button>

        @if($esCapitan ?? false)
            <button type="button" onclick="llamarCapitan()" class="col-span-2 mt-1 h-13 md:h-12 flex items-center justify-center gap-2 rounded-[18px] md:rounded-[16px] bg-gradient-to-b from-[var(--accent)] to-[var(--accent)] border border-[var(--border-color)] hover:opacity-90 hover:shadow-lg transition-all duration-150 active:scale-95 group text-white shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--accent)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--bg-base)]">
                <i class="fas fa-shield-alt text-xs md:text-[11px]"></i>
                <span class="text-[11px] md:text-[10px] font-bold uppercase tracking-widest">Capitán</span>
            </button>
        @endif
    </div>

    <button type="button" onclick="limpiarTicket()" class="mt-4 w-full h-13 md:h-12 rounded-[18px] md:rounded-[16px] border border-red-500/20 text-red-500 hover:bg-red-500 hover:text-white hover:shadow-md hover:shadow-red-500/20 transition-all duration-150 active:scale-95 flex items-center justify-center gap-2 font-bold text-[11px] md:text-[10px] uppercase tracking-widest shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500/50">
        <i class="fas fa-trash-alt text-xs md:text-[11px]"></i> Eliminar Todo
    </button>
</aside>

{{-- ========================================== --}}
{{-- COLUMNA 2: TICKET / COMANDA (CENTRAL)      --}}
{{-- ========================================== --}}
<section id="col-ticket" class="col-mobile-panel hidden md:flex w-full md:w-[320px] xl:w-[360px] flex-shrink-0 h-full flex-col bg-[var(--bg-base)] border-r border-[var(--border-color)] relative z-10 shadow-[20px_0_40px_-15px_rgba(0,0,0,0.15)]">

    <div class="p-4 border-b border-[var(--border-color)] flex flex-col gap-3 bg-[var(--bg-base)]">
        <div id="barraModificadores" class="hidden rounded-2xl border border-[var(--border-color)] bg-[var(--bg-panel)] p-3 shadow-sm">
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-[10px] md:text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">Acciones del platillo</span>
                <span class="text-[9px] md:text-[8px] font-semibold text-[var(--text-muted)]">Selección activa</span>
            </div>
            <div id="contenedorBotonesModificadores" class="flex flex-wrap gap-2"></div>
        </div>

        <div class="relative flex w-full bg-[var(--bg-panel)] p-1 rounded-xl border border-[var(--border-color)] shadow-inner">
            <div class="absolute inset-y-1 left-1 right-1 pointer-events-none">
                <div id="tab-slider" class="h-full w-1/3 rounded-lg bg-[var(--text-main)] shadow-md transition-transform duration-300 ease-out"></div>
            </div>
            <button type="button" onclick="cambiarTab('nueva-orden', this)" id="btn-tab-nueva-orden" class="relative z-10 flex-1 py-2.5 md:py-1.5 text-[11px] md:text-[10px] font-bold text-[var(--bg-base)] transition-colors outline-none focus-visible:ring-2 focus-visible:ring-blue-500/60 rounded-lg">Orden</button>
            <button type="button" onclick="cambiarTab('enviados', this)" id="btn-tab-enviados" class="relative z-10 flex-1 py-2.5 md:py-1.5 text-[11px] md:text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors outline-none focus-visible:ring-2 focus-visible:ring-blue-500/60 rounded-lg">Enviado</button>
            <button type="button" onclick="cambiarTab('comanda', this)" id="btn-tab-comanda" class="relative z-10 flex-1 py-2.5 md:py-1.5 text-[11px] md:text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors outline-none focus-visible:ring-2 focus-visible:ring-blue-500/60 rounded-lg">Total</button>
        </div>

        <div class="flex items-center justify-between">
            <span class="text-[11px] md:text-[10px] font-medium text-[var(--text-muted)]">Tiempos:</span>
            <div class="flex gap-1 bg-[var(--bg-panel)] p-1 rounded-lg border border-[var(--border-color)] shadow-inner">
                <button type="button" onclick="cambiarTiempoGlobal('sin-tiempo')" id="tiempo-global-sin" class="w-9 h-8 md:w-8 md:h-6 rounded flex items-center justify-center text-[11px] md:text-[10px] font-bold bg-[var(--text-main)] text-[var(--bg-base)] shadow-sm transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/50">S</button>
                <button type="button" onclick="cambiarTiempoGlobal('primer-tiempo')" id="tiempo-global-1" class="w-9 h-8 md:w-8 md:h-6 rounded flex items-center justify-center text-[11px] md:text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/50">1</button>
                <button type="button" onclick="cambiarTiempoGlobal('segundo-tiempo')" id="tiempo-global-2" class="w-9 h-8 md:w-8 md:h-6 rounded flex items-center justify-center text-[11px] md:text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/50">2</button>
                <button type="button" onclick="cambiarTiempoGlobal('tercer-tiempo')" id="tiempo-global-3" class="w-9 h-8 md:w-8 md:h-6 rounded flex items-center justify-center text-[11px] md:text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/50">3</button>
            </div>
        </div>
    </div>

    {{-- VISTA 1: NUEVA ORDEN --}}
    <div id="vista-nueva-orden" class="panel-fade flex-1 overflow-y-auto hide-scroll p-4 flex flex-col relative bg-[var(--bg-base)]">
        <div class="flex justify-between text-[11px] md:text-[10px] font-bold text-[var(--text-muted)] mb-3 px-1">
            <span>CANT. / PLATILLO</span>
            <span>MONTO</span>
        </div>

        <div id="listaTicket" class="flex flex-col gap-2.5 pb-4"></div>

        <div id="estadoVacio" class="flex-1 flex flex-col items-center justify-center opacity-40 mt-10 transition-opacity duration-300">
            <i class="fas fa-plate-wheat text-4xl md:text-3xl text-[var(--text-muted)] mb-3"></i>
            <p class="text-xs md:text-[11px] font-medium text-[var(--text-muted)] text-center">Sin productos.<br>Comienza a agregar.</p>
        </div>
    </div>

    {{-- VISTA 2: ENVIADOS --}}
    <div id="vista-enviados" class="hidden panel-fade flex-1 overflow-y-auto hide-scroll p-4 flex-col relative bg-[var(--bg-base)]">
        @if(isset($platillosEnviados) && count($platillosEnviados) > 0)
            <div class="flex flex-col gap-2">
                @foreach($platillosEnviados as $item)
                    <div class="bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-xl p-3 flex justify-between items-center shadow-sm hover:shadow-md transition-shadow duration-150">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 md:w-6 md:h-6 rounded-lg bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-main)] text-xs md:text-[11px] font-bold flex items-center justify-center shadow-sm">{{ $item->cantidad ?? 1 }}</span>
                            <span class="text-[13px] md:text-[12px] font-medium text-[var(--text-main)]">{{ $item->nombre ?? 'Platillo' }}</span>
                        </div>
                        <div class="text-right">
                            @if(($item->estado ?? 'enviado') == 'preparando')
                                <span class="text-[10px] md:text-[9px] font-bold text-orange-500 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-orange-500 shadow-[0_0_5px_rgba(249,115,22,0.5)]"></span> Cocina</span>
                            @elseif(($item->estado ?? 'enviado') == 'listo')
                                <span class="text-[10px] md:text-[9px] font-bold text-emerald-500 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.5)]"></span> Listo</span>
                            @else
                                <span class="text-[10px] md:text-[9px] font-bold text-[var(--text-muted)] flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[var(--text-muted)]"></span> Enviado</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center opacity-40 mt-10">
                <i class="fas fa-clock text-4xl md:text-3xl text-[var(--text-muted)] mb-3"></i>
                <p class="text-xs md:text-[11px] font-medium text-[var(--text-muted)] text-center">No hay órdenes en proceso.</p>
            </div>
        @endif
    </div>

    {{-- VISTA 3: COMANDA TOTAL --}}
    <div id="vista-comanda" class="hidden panel-fade flex-1 overflow-y-auto hide-scroll p-4 flex-col relative bg-[var(--bg-base)]">
        <div id="items-db-total" class="flex flex-col gap-2">
            @if(isset($platillosEnviados) && count($platillosEnviados) > 0)
                <div class="text-[11px] md:text-[10px] font-bold text-[var(--text-muted)] mb-2 px-1 uppercase tracking-wider">Consumo Procesado</div>
                @foreach($platillosEnviados as $item)
                    <div class="flex justify-between items-center p-2 rounded-xl hover:bg-[var(--hover-bg)] transition-colors duration-150">
                        <div class="flex items-center gap-3">
                            <span class="text-xs md:text-[11px] text-[var(--text-muted)] font-bold">{{ $item->cantidad ?? 1 }}x</span>
                            <span class="text-[13px] md:text-[12px] font-medium text-[var(--text-main)]">{{ $item->nombre ?? 'Platillo' }}</span>
                        </div>
                        <span class="text-[13px] md:text-[12px] font-bold text-[var(--text-main)]">${{ number_format(($item->precio ?? 0) * ($item->cantidad ?? 1), 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between items-center mt-2 pt-3 border-t border-[var(--border-color)] px-2">
                    <span class="text-xs md:text-[11px] font-medium text-[var(--text-muted)]">Subtotal en mesa:</span>
                    <span class="text-[13px] md:text-[12px] font-bold text-[var(--text-main)]">
                        ${{ number_format(collect($platillosEnviados)->sum(function($i) { return ($i->precio ?? 0) * ($i->cantidad ?? 1); }), 2) }}
                    </span>
                </div>
            @endif
        </div>

        <div id="lista-comanda-total" class="flex flex-col gap-2 mt-4"></div>

        <div id="estadoVacioComanda" class="flex-1 flex-col items-center justify-center opacity-40 mt-10 {{ (isset($platillosEnviados) && count($platillosEnviados) > 0) ? 'hidden' : 'flex' }}">
            <i class="fas fa-receipt text-4xl md:text-3xl text-[var(--text-muted)] mb-3"></i>
            <p class="text-xs md:text-[11px] font-medium text-[var(--text-muted)] text-center">No hay cuenta registrada.</p>
        </div>
    </div>

    {{-- FOOTER DE TOTALES (PREMIUM) --}}
    <div class="p-5 pb-8 md:pb-5 border-t border-[var(--border-color)] bg-gradient-to-b from-[var(--bg-panel)] to-[var(--bg-panel)] flex-shrink-0 z-20 shadow-[0_-10px_30px_rgba(0,0,0,0.06)]">
        <div class="flex justify-between items-center mb-1">
            <span class="text-xs md:text-[11px] text-[var(--text-muted)] font-medium">Subtotal Nuevos</span>
            <span class="text-[13px] md:text-[12px] font-bold text-[var(--text-main)]" id="txtSubtotal">$0.00</span>
        </div>
       <div class="flex justify-between items-center mb-2">
            <span class="text-xs md:text-[11px] text-[var(--text-muted)] font-medium">IVA (16%)</span>
            <span class="text-[13px] md:text-[12px] font-bold text-[var(--text-main)]" id="txtIva">$0.00</span>
        </div>
        
        {{-- NUEVA FILA: PROPINA --}}
        <div class="flex justify-between items-center mb-4">
            <span class="text-xs md:text-[11px] text-[var(--text-muted)] font-medium">Propina</span>
            <span class="text-[13px] md:text-[12px] font-bold text-emerald-500" id="txtPropina">$0.00</span>
        </div>


        <button type="button" id="btn-enviar" onclick="enviarACocina()" class="w-full h-13 md:h-12 rounded-xl bg-gradient-to-r from-[#3b82f6] to-[#2563eb] text-white text-[13px] md:text-[12px] font-bold tracking-wide transition-all duration-150 shadow-[0_8px_20px_-5px_rgba(59,130,246,0.5)] hover:shadow-[0_10px_28px_-5px_rgba(59,130,246,0.6)] active:scale-95 flex items-center justify-center gap-2 outline-none focus-visible:ring-2 focus-visible:ring-blue-400 focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--bg-panel)]">
            <i class="fas fa-paper-plane text-sm"></i>
            <span>Enviar Orden</span>
        </button>
        <p id="mensajeMesaDestino" class="mt-3 text-[11px] md:text-[10px] font-bold text-[var(--text-muted)] text-center uppercase tracking-widest">Enviando a Mesa {{ $mesa->numero ?? '12M' }}</p>
    </div>
</section>

<nav id="mobileNav" class="md:hidden fixed bottom-0 left-0 right-0 z-40 flex bg-[var(--bg-panel)]/95 backdrop-blur-md border-t border-[var(--border-color)] shadow-[0_-8px_24px_rgba(0,0,0,0.15)] ring-1 ring-black/5" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
    <button type="button" id="navbtn-acciones" onclick="mostrarColumnaMobile('acciones')" class="mobile-nav-btn flex-1 flex flex-col items-center justify-center gap-1 py-3 min-h-[52px] text-[var(--text-muted)] transition-colors duration-150 active:scale-95">
        <i class="fas fa-sliders-h text-base"></i>
        <span class="text-[10px] font-bold uppercase tracking-wide">Mesa</span>
    </button>
    <button type="button" id="navbtn-ticket" onclick="mostrarColumnaMobile('ticket')" class="mobile-nav-btn relative flex-1 flex flex-col items-center justify-center gap-1 py-3 min-h-[52px] text-[var(--text-muted)] transition-colors duration-150 active:scale-95">
        <i class="fas fa-receipt text-base"></i>
        <span class="text-[10px] font-bold uppercase tracking-wide">Orden</span>
        {{-- Badge opcional de # de productos en el ticket. Oculto por defecto;
             muéstralo (quita "hidden" y pon el número) desde tu JS donde
             actualizas listaTicket, igual que ya haces con
             #indicador-gramaje-pendiente. --}}
        <span id="navBadgeOrden" class="hidden absolute top-1.5 right-[22%] min-w-[16px] h-4 px-1 rounded-full bg-blue-500 text-white text-[9px] font-bold flex items-center justify-center shadow-sm shadow-blue-500/40"></span>
    </button>
    <button type="button" id="navbtn-catalogo" onclick="mostrarColumnaMobile('catalogo')" class="mobile-nav-btn flex-1 flex flex-col items-center justify-center gap-1 py-3 min-h-[52px] text-[var(--text-muted)] transition-colors duration-150 active:scale-95">
        <i class="fas fa-utensils text-base"></i>
        <span class="text-[10px] font-bold uppercase tracking-wide">Menú</span>
    </button>
</nav>

<style>
    /* Barra inferior móvil: solo estética, no toca tu lógica */
    .mobile-nav-active {
        color: var(--text-main) !important;
        position: relative;
    }
    .mobile-nav-active::before {
        content: '';
        position: absolute;
        top: 0;
        left: 20%;
        right: 20%;
        height: 2px;
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
        border-radius: 2px;
        box-shadow: 0 0 8px rgba(59,130,246,0.5);
    }

    /* Transición suave al cambiar de columna en móvil y al cambiar de tab
       (Orden/Enviado/Total). Solo estética: no depende de ninguna clase
       nueva que tu JS deba manejar más allá de quitar/poner "hidden". */
    @media (prefers-reduced-motion: no-preference) {
        .col-mobile-panel:not(.hidden) { animation: panelFadeIn .2s ease-out; }
        .panel-fade:not(.hidden) { animation: panelFadeIn .18s ease-out; }
    }
    @keyframes panelFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    // Toggle de columnas SOLO para móvil. No interactúa con cambiarTab,
    // enviarACocina ni ninguna otra función de tu lógica de negocio.
    function mostrarColumnaMobile(col) {
        if (window.innerWidth >= 768) return; // en desktop las 3 se ven siempre, esto no aplica

        document.querySelectorAll('.col-mobile-panel').forEach(function (el) {
            el.classList.add('hidden');
        });
        var objetivo = document.getElementById('col-' + col);
        if (objetivo) objetivo.classList.remove('hidden');

        document.querySelectorAll('.mobile-nav-btn').forEach(function (btn) {
            btn.classList.remove('mobile-nav-active');
        });
        var btnActivo = document.getElementById('navbtn-' + col);
        if (btnActivo) btnActivo.classList.add('mobile-nav-active');
    }

    // Al cargar en móvil, arranca mostrando el catálogo (lo más usado)
    document.addEventListener('DOMContentLoaded', function () {
        if (window.innerWidth < 768) {
            mostrarColumnaMobile('catalogo');
        }
    });
</script>