<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Comanda | Ollintem Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* MODO OSCURO: Estilo Pro Studio (Profundo y elegante) */
            --bg-base: #09090b; 
            --bg-panel: #18181b; 
            --border-color: rgba(255, 255, 255, 0.08); 
            --border-highlight: rgba(255, 255, 255, 0.12);
            --text-main: #f4f4f5; 
            --text-muted: #a1a1aa; 
            --accent: #3b82f6; 
            --input-bg: #09090b;
            --card-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.7);
            --hover-bg: rgba(255, 255, 255, 0.04);
        }

        body.modo-crema {
            /* MODO CREMA: Fresco, nítido y con alto contraste */
            --bg-base: #f3f4f6; 
            --bg-panel: #ffffff; 
            --border-color: rgba(0, 0, 0, 0.08);
            --border-highlight: rgba(0, 0, 0, 0.15);
            --text-main: #111827; 
            --text-muted: #6b7280; 
            --accent: #2563eb; 
            --input-bg: #f9fafb; 
            --card-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05), 0 2px 6px -2px rgba(0, 0, 0, 0.025);
            --hover-bg: rgba(0, 0, 0, 0.03);
        }

        body { 
            background-color: var(--bg-base);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            overflow: hidden; 
            transition: background-color 0.4s ease, color 0.4s ease;
        }
        
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        .hide-scroll::-webkit-scrollbar { width: 0; height: 0; display: none !important; }
        
        @keyframes fade-in-up { 0% { opacity: 0; transform: translateY(5px); } 100% { opacity: 1; transform: translateY(0); } }
        .animate-item { animation: fade-in-up 0.2s ease-out forwards; }
        
        /* Toast Premium */
        .toast-wrapper { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.75rem; align-items: flex-end; pointer-events: none; }
        .toast-panel { min-width: 18rem; max-width: 28rem; pointer-events: auto; background: var(--bg-panel); color: var(--text-main); border: 1px solid var(--border-color); border-radius: 16px; box-shadow: var(--card-shadow); padding: 1rem 1.25rem; opacity: 0; transform: translateY(10px); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); display: flex; gap: 0.85rem; align-items: center; }
        .toast-panel.show { opacity: 1; transform: translateY(0); }
        .toast-panel .toast-icon { display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .toast-panel.success .toast-icon { color: #10b981; }
        .toast-panel.error .toast-icon { color: #ef4444; }
        .toast-panel.info .toast-icon { color: #3b82f6; }
        .toast-panel strong { display: block; font-weight: 700; font-size: 0.95rem; }
        .toast-panel span { color: var(--text-muted); font-size: 0.85rem; }
        
        button { outline: none !important; -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="h-screen w-full flex selection:bg-[#3b82f6]/30">

    <script>if (localStorage.getItem('tema-ollintem') === 'crema') document.body.classList.add('modo-crema');</script>
    <div id="toastContainer" class="toast-wrapper" aria-live="polite" aria-atomic="true"></div>

    {{-- ========================================== --}}
    {{-- COLUMNA 1: ACCIONES RÁPIDAS --}}
    {{-- ========================================== --}}
    <aside class="w-[170px] md:w-[190px] xl:w-[220px] flex-shrink-0 h-full flex flex-col bg-[var(--bg-base)] border-r border-[var(--border-color)] p-4 z-20">
        
        <div class="flex items-center gap-2 mb-6">
            <button onclick="window.location.href='{{ route('mesero.dashboard') }}'" class="flex-1 h-10 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-semibold text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 shadow-sm">
                <i class="fas fa-arrow-left text-[var(--text-muted)] text-[10px]"></i> Mesas
            </button>
            <button onclick="toggleTheme()" class="w-10 h-10 shrink-0 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
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
            <button onclick="ajustarPersonas()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
                <i class="fas fa-users text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
                <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Personas</span>
                <span id="txtPersonas" class="text-xs font-bold text-[var(--text-main)] mt-0.5">{{ $mesa->capacidad ?? 1 }}</span>
            </button>
            
            <button onclick="imprimirPrecuenta()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
                <i class="fas fa-receipt text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
                <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors leading-tight text-center">Pre<br>Cuenta</span>
            </button>

            <button onclick="agregarNota()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
                <i class="fas fa-pen text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
                <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Nota</span>
            </button>
            
            <button onclick="aplicarDescuento()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
                <i class="fas fa-percent text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
                <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Descuento</span>
            </button>
            
            <button id="btn-gramaje" onclick="ajustarGramaje()" class="relative flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
                <i class="fas fa-weight-scale text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
                <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Gramaje</span>
                <span id="indicador-gramaje-pendiente" class="hidden absolute top-2 right-2 bg-orange-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full shadow-md"></span>
            </button>
            
            <button onclick="llamarCapitan()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
                <i class="fas fa-exchange-alt text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
                <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Traspaso</span>
            </button>
            
            <button onclick="mostrarPromociones()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
                <i class="fas fa-tag text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
                <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Promos</span>
            </button>
            
            <button onclick="marcharTiempos()" class="flex flex-col items-center justify-center p-3 rounded-[16px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:bg-[var(--hover-bg)] hover:border-[var(--border-highlight)] transition-all active:scale-95 group shadow-sm">
                <i class="fas fa-fire-burner text-[var(--text-muted)] group-hover:text-[var(--text-main)] mb-2 text-sm transition-colors"></i>
                <span class="text-[9px] font-medium text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Marchar</span>
            </button>
            
            @if($esCapitan ?? false)
                <button onclick="llamarCapitan()" class="col-span-2 mt-1 h-12 flex items-center justify-center gap-2 rounded-[16px] bg-[var(--accent)] border border-[var(--border-color)] hover:opacity-90 transition-all active:scale-95 group text-white shadow-md">
                    <i class="fas fa-shield-alt text-[11px]"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest">Capitán</span>
                </button>
            @endif
        </div>

        <button onclick="limpiarTicket()" class="mt-4 w-full h-12 rounded-[16px] border border-red-500/20 text-red-500 hover:bg-red-500 hover:text-white transition-all active:scale-95 flex items-center justify-center gap-2 font-bold text-[10px] uppercase tracking-widest shadow-sm">
            <i class="fas fa-trash-alt text-[11px]"></i> Eliminar Todo
        </button>
    </aside>

    @if($esCapitan ?? false)
        <div id="modalCapitan" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        @php $mesasAbiertas = $mesasAbiertas ?? collect(); @endphp
        <div class="w-full max-w-md rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-indigo-500 font-bold">Autorización</p>
                    <h2 class="text-xl font-semibold text-[var(--text-main)] mt-1">Selecciona mesa destino</h2>
                </div>
                <button onclick="cerrarModal('modalCapitan')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
            </div>
            <div id="capitanMesasContainer" class="grid gap-2 max-h-[300px] overflow-y-auto hide-scroll pb-2"></div>
            <div class="mt-5 text-right">
                <button onclick="cerrarModal('modalCapitan')" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-medium text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all">Cancelar</button>
            </div>
        </div>
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- COLUMNA 2: TICKET / COMANDA (CENTRAL)      --}}
    {{-- ========================================== --}}
    <section class="w-[280px] md:w-[320px] xl:w-[360px] flex-shrink-0 h-full flex flex-col bg-[var(--bg-base)] border-r border-[var(--border-color)] relative z-10 shadow-[20px_0_40px_-15px_rgba(0,0,0,0.15)]">
        
        <div class="p-4 border-b border-[var(--border-color)] flex flex-col gap-3 bg-[var(--bg-base)]">
            <div class="relative flex w-full bg-[var(--bg-panel)] p-1 rounded-xl border border-[var(--border-color)] shadow-sm">
                <div class="absolute inset-y-1 left-1 right-1 pointer-events-none">
                    <div id="tab-slider" class="h-full w-1/3 rounded-lg bg-[var(--text-main)] shadow-sm transition-transform duration-300 ease-out"></div>
                </div>
                <button onclick="cambiarTab('nueva-orden', this)" id="btn-tab-nueva-orden" class="relative z-10 flex-1 py-1.5 text-[10px] font-bold text-[var(--bg-base)] transition-colors outline-none">Orden</button>
                <button onclick="cambiarTab('enviados', this)" id="btn-tab-enviados" class="relative z-10 flex-1 py-1.5 text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors outline-none">Enviado</button>
                <button onclick="cambiarTab('comanda', this)" id="btn-tab-comanda" class="relative z-10 flex-1 py-1.5 text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors outline-none">Total</button>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-[10px] font-medium text-[var(--text-muted)]">Tiempos:</span>
                <div class="flex gap-1 bg-[var(--bg-panel)] p-1 rounded-lg border border-[var(--border-color)] shadow-sm">
                    <button onclick="cambiarTiempoGlobal('sin-tiempo')" id="tiempo-global-sin" class="w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold bg-[var(--text-main)] text-[var(--bg-base)] transition-all">S</button>
                    <button onclick="cambiarTiempoGlobal('primer-tiempo')" id="tiempo-global-1" class="w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all">1</button>
                    <button onclick="cambiarTiempoGlobal('segundo-tiempo')" id="tiempo-global-2" class="w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all">2</button>
                    <button onclick="cambiarTiempoGlobal('tercer-tiempo')" id="tiempo-global-3" class="w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all">3</button>
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
                                    <span class="text-[9px] font-bold text-orange-500 flex items-center gap-1.5"><div class="w-1.5 h-1.5 rounded-full bg-orange-500 shadow-[0_0_5px_rgba(249,115,22,0.5)]"></div> Cocina</span>
                                @elseif(($item->estado ?? 'enviado') == 'listo')
                                    <span class="text-[9px] font-bold text-emerald-500 flex items-center gap-1.5"><div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.5)]"></div> Listo</span>
                                @else
                                    <span class="text-[9px] font-bold text-[var(--text-muted)] flex items-center gap-1.5"><div class="w-1.5 h-1.5 rounded-full bg-[var(--text-muted)]"></div> Enviado</span>
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

            <button id="btn-enviar" onclick="enviarACocina()" class="w-full h-12 rounded-xl bg-gradient-to-r from-[#3b82f6] to-[#2563eb] text-white text-[12px] font-bold tracking-wide transition-all shadow-[0_8px_20px_-5px_rgba(59,130,246,0.5)] hover:shadow-[0_8px_25px_-5px_rgba(59,130,246,0.6)] active:scale-95 flex items-center justify-center gap-2 outline-none">
                <i class="fas fa-paper-plane text-sm"></i>
                <span>Enviar Orden</span>
            </button>
            <p id="mensajeMesaDestino" class="mt-3 text-[10px] font-bold text-[var(--text-muted)] text-center uppercase tracking-widest">Enviando a Mesa {{ $mesa->numero ?? '12M' }}</p>
        </div>
    </section>

    {{-- ========================================== --}}
    {{-- COLUMNA 3: CATÁLOGO DE PRODUCTOS (FLEX)    --}}
    {{-- ========================================== --}}
    <main class="flex-1 min-w-0 flex flex-col bg-[var(--bg-base)] relative overflow-hidden">
        <div class="px-6 py-4 flex gap-2 overflow-x-auto hide-scroll relative z-10 border-b border-[var(--border-color)] bg-[var(--bg-panel)] flex-shrink-0 shadow-sm" id="menuCategorias"></div>
        <div class="flex-1 p-6 overflow-y-auto hide-scroll grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-5 content-start relative z-10 bg-[var(--bg-base)]" onclick="deseleccionarTicket()" id="gridProductos"></div>
        
        <div id="barraModificadores" class="hidden h-[70px] bg-[var(--bg-panel)] border-t border-[var(--border-color)] flex items-center px-6 relative z-10 transition-all flex-shrink-0 shadow-[0_-10px_30px_rgba(0,0,0,0.05)]">
            <span class="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] mr-4 whitespace-nowrap">Opciones:</span>
            <div id="contenedorBotonesModificadores" class="flex gap-2 overflow-x-auto hide-scroll flex-1 items-center"></div>
            <button onclick="deseleccionarTicket()" class="ml-4 w-8 h-8 rounded-full flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--hover-bg)] transition-all outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
    </main>

    {{-- MODAL NOTAS --}}
    <div id="modalNota" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-[var(--text-main)]">Instrucción Especial</h2>
                <button onclick="cerrarModal('modalNota')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
            </div>
            <textarea id="notaTextarea" rows="3" readonly class="w-full rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] p-4 text-sm font-medium text-[var(--text-main)] outline-none resize-none focus:border-[#3b82f6] transition-colors" placeholder="Ej. Sin cebolla..."></textarea>
            
            <div id="tecladoNotas" class="grid grid-cols-10 gap-1.5 mt-4">
                @foreach(['1','2','3','4','5','6','7','8','9','0','Q','W','E','R','T','Y','U','I','O','P','A','S','D','F','G','H','J','K','L','Ñ','Z','X','C','V','B','N','M',',','.'] as $key)
                    <button type="button" onclick="insertarNotaCaracter('{{ $key }}')" class="px-1 py-2.5 rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-[var(--border-highlight)] hover:bg-[var(--hover-bg)] text-[var(--text-main)] text-xs font-bold transition-colors">{{ $key }}</button>
                @endforeach
                <button type="button" onclick="insertarNotaEspacio()" class="col-span-4 px-1 py-2.5 rounded-lg bg-[#3b82f6] text-white text-xs font-bold transition-colors hover:opacity-90">Espacio</button>
                <button type="button" onclick="borrarNotaCaracter()" class="col-span-3 px-1 py-2.5 rounded-lg bg-red-500 text-white text-xs font-bold transition-colors hover:opacity-90"><i class="fas fa-backspace"></i></button>
                <button type="button" onclick="limpiarNota()" class="col-span-3 px-1 py-2.5 rounded-lg bg-[var(--text-muted)] text-white text-xs font-bold transition-colors hover:opacity-90">Limpiar</button>
            </div>
            
            <div class="mt-6 flex justify-end gap-3">
                <button onclick="cerrarModal('modalNota')" class="px-6 py-2.5 rounded-xl border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] text-xs font-bold transition-all">Cancelar</button>
                <button onclick="guardarNota()" class="px-6 py-2.5 rounded-xl bg-[var(--text-main)] text-[var(--bg-base)] text-xs font-bold transition-all hover:opacity-90">Confirmar</button>
            </div>
        </div>
    </div>

    {{-- MODAL DESCUENTO --}}
    <div id="modalDescuento" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-[var(--text-main)]">Descuento (%)</h2>
                <button onclick="cerrarModal('modalDescuento')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
            </div>
            <input id="descuentoInput" type="number" min="0" max="100" class="w-full rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] p-4 text-xl font-black text-center text-[var(--text-main)] outline-none focus:border-[#3b82f6] transition-colors" placeholder="0">
            <div class="mt-6 flex justify-end gap-3">
                <button onclick="guardarDescuento()" class="w-full px-6 py-3 rounded-xl bg-[#3b82f6] text-white text-sm font-bold transition-all hover:opacity-90">Aplicar</button>
            </div>
        </div>
    </div>

    {{-- MODAL PERSONAS --}}
    <div id="modalPersonas" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-[var(--text-main)]">Personas en Mesa</h2>
                <button onclick="cerrarModal('modalPersonas')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
            </div>
            <input id="personasInput" type="number" min="1" class="w-full rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] p-4 text-xl font-black text-center text-[var(--text-main)] outline-none focus:border-[#3b82f6] transition-colors">
            <div class="mt-6 flex justify-end gap-3">
                <button onclick="guardarPersonas()" class="w-full px-6 py-3 rounded-xl bg-[var(--text-main)] text-[var(--bg-base)] text-sm font-bold transition-all hover:opacity-90">Guardar</button>
            </div>
        </div>
    </div>

    {{-- MODAL GRAMAJE --}}
    <div id="modalGramaje" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
            <div class="flex items-center justify-between mb-4">
                <h2 id="modalGramajeTitulo" class="text-lg font-bold text-[var(--text-main)] truncate max-w-[200px]">Gramaje</h2>
                <button onclick="cerrarModal('modalGramaje')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
            </div>
            <div class="flex items-center gap-3 mb-4">
                <input id="gramajeInput" type="text" readonly class="flex-1 rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] p-4 text-2xl font-black text-[var(--text-main)] text-center outline-none" placeholder="0">
                <span class="text-[var(--text-muted)] font-bold text-lg">g</span>
            </div>
            <div id="tecladoGramaje" class="grid grid-cols-3 gap-2">
                @foreach(['1','2','3','4','5','6','7','8','9','.','0'] as $key)
                    <button type="button" onclick="anadirNumeroGramaje('{{ $key }}')" class="py-3 rounded-xl bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-[var(--border-highlight)] hover:bg-[var(--hover-bg)] text-[var(--text-main)] text-lg font-bold transition-colors">{{ $key }}</button>
                @endforeach
                <button type="button" onclick="borrarNumeroGramaje()" class="py-3 rounded-xl bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white text-sm font-bold transition-colors"><i class="fas fa-backspace"></i></button>
            </div>
            <div class="mt-6">
                <button onclick="guardarGramajeDelItem()" class="w-full px-6 py-3 rounded-xl bg-[#f97316] text-white text-sm font-bold transition-all hover:opacity-90">Confirmar</button>
            </div>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const body = document.body;
            body.classList.toggle('modo-crema');
            localStorage.setItem('tema-ollintem', body.classList.contains('modo-crema') ? 'crema' : 'negro');
            actualizarIconoTema(body.classList.contains('modo-crema'));
        }

        function actualizarIconoTema(esCrema) {
            const icon = document.getElementById('themeIcon');
            if (icon) {
                icon.className = esCrema 
                    ? 'fas fa-moon text-[11px] group-hover:rotate-45 transition-transform duration-500'
                    : 'fas fa-sun text-[11px] group-hover:rotate-45 transition-transform duration-500';
            }
        }

        // TABS
        function cambiarTab(pestana, btn) {
            const slider = document.getElementById('tab-slider');
            const btns = [document.getElementById('btn-tab-nueva-orden'), document.getElementById('btn-tab-enviados'), document.getElementById('btn-tab-comanda')];
            
            btns.forEach(el => { if(el) { el.classList.remove('text-[var(--bg-base)]'); el.classList.add('text-[var(--text-muted)]'); }});
            
            ['vista-nueva-orden', 'vista-enviados', 'vista-comanda'].forEach(id => {
                document.getElementById(id).classList.add('hidden');
                document.getElementById(id).classList.remove('flex');
            });

            if(pestana === 'nueva-orden') {
                if(slider) slider.style.transform = 'translateX(0%)';
                if(btns[0]) { btns[0].classList.add('text-[var(--bg-base)]'); btns[0].classList.remove('text-[var(--text-muted)]'); }
                document.getElementById('vista-nueva-orden').classList.remove('hidden'); document.getElementById('vista-nueva-orden').classList.add('flex');
            } else if (pestana === 'enviados') {
                if(slider) slider.style.transform = 'translateX(100%)';
                if(btns[1]) { btns[1].classList.add('text-[var(--bg-base)]'); btns[1].classList.remove('text-[var(--text-muted)]'); }
                document.getElementById('vista-enviados').classList.remove('hidden'); document.getElementById('vista-enviados').classList.add('flex');
            } else if (pestana === 'comanda') {
                if(slider) slider.style.transform = 'translateX(200%)';
                if(btns[2]) { btns[2].classList.add('text-[var(--bg-base)]'); btns[2].classList.remove('text-[var(--text-muted)]'); }
                document.getElementById('vista-comanda').classList.remove('hidden'); document.getElementById('vista-comanda').classList.add('flex');
                actualizarVistaTotal();
            }
        }

        function actualizarVistaTotal() {
            const contenedorNuevos = document.getElementById('lista-comanda-total');
            const mensajeVacio = document.getElementById('estadoVacioComanda');
            const itemsEnTicket = document.querySelectorAll('#listaTicket .ticket-item');
            const contenedorDB = document.getElementById('items-db-total');
            const hayPlatillosEnDB = contenedorDB && contenedorDB.children.length > 0;

            contenedorNuevos.innerHTML = '';

            if (itemsEnTicket.length === 0 && !hayPlatillosEnDB) {
                mensajeVacio.classList.remove('hidden'); mensajeVacio.classList.add('flex');
            } else {
                mensajeVacio.classList.add('hidden'); mensajeVacio.classList.remove('flex');
                if (itemsEnTicket.length > 0) {
                    contenedorNuevos.innerHTML += `<div class="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] mt-4 mb-2 px-1">Por Enviar</div>`;
                    itemsEnTicket.forEach(item => {
                        const cant = item.querySelector('.cantidad-platillo').innerText;
                        const nombre = item.querySelector('.nombre-platillo').innerText;
                        const precio = item.querySelector('.precio-platillo').innerText;
                        contenedorNuevos.innerHTML += `
                            <div class="flex justify-between items-center p-2.5 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-sm mb-2">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-main)] text-[10px] font-bold flex items-center justify-center">${cant}</span>
                                    <span class="text-[11px] font-bold text-[var(--text-main)]">${nombre}</span>
                                </div>
                                <span class="text-[11px] font-bold text-[var(--text-main)]">${precio}</span>
                            </div>
                        `;
                    });
                }
            }
            
            let totalHistorial = 0;
            @if(isset($platillosEnviados) && count($platillosEnviados) > 0)
                totalHistorial = {{ collect($platillosEnviados)->sum(function($i) { return ($i->precio ?? 0) * ($i->cantidad ?? 1); }) }};
            @endif
            
            const subtotalGeneral = ticketSubtotal + totalHistorial;
            const ivaGeneral = subtotalGeneral * 0.16;
            document.getElementById('txtTotal').innerText = '$' + (subtotalGeneral + ivaGeneral).toFixed(2);
        }

        const categoriasDB = @json($categorias ?? []);
        const productosDB = @json($productos ?? []);

        function renderizarMenu() {
            const menuCat = document.getElementById('menuCategorias');
            const gridProd = document.getElementById('gridProductos');
            
            menuCat.innerHTML = `<button onclick="filtrarCategoria('Todos', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[var(--text-main)] text-[var(--bg-base)] text-[11px] font-bold tracking-wide shadow-sm transition-all outline-none border border-transparent">Todos</button>`;
            
            if(categoriasDB.length > 0) {
                categoriasDB.forEach(cat => {
                    menuCat.innerHTML += `<button onclick="filtrarCategoria('${cat.nombre}', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--border-highlight)] text-[11px] font-semibold tracking-wide shadow-sm transition-all outline-none">${cat.nombre}</button>`;
                });
            }

            gridProd.innerHTML = '';
            
            if(productosDB.length > 0) {
                productosDB.forEach(prod => {
                    const catNombre = prod.categoria ? prod.categoria.nombre : '';
                    const precioNum = parseFloat(prod.precio) || 0;
                    const modsJSON = prod.modificadores ? JSON.stringify(prod.modificadores).replace(/'/g, "\\'") : '[]';
                    const letraInicial = prod.nombre.charAt(0).toUpperCase();

                    gridProd.innerHTML += `
                        <div data-categoria-item="${catNombre}" onclick='agregarAlTicket(${prod.id}, "${prod.nombre}", ${precioNum}, "${catNombre}", ${modsJSON}); event.stopPropagation();' 
                             class="producto-card rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-[var(--card-shadow)] overflow-hidden hover:border-[#3b82f6]/50 hover:-translate-y-1 transition-all duration-300 group cursor-pointer flex flex-col h-[150px] xl:h-[170px] outline-none">
                            
                            <div class="h-[50%] bg-[var(--input-bg)] flex items-center justify-center relative overflow-hidden border-b border-[var(--border-color)]">
                                <span class="absolute top-2.5 right-2.5 text-[8px] font-bold uppercase tracking-widest text-[var(--text-muted)] bg-[var(--bg-panel)] border border-[var(--border-color)] px-2 py-0.5 rounded-md shadow-sm z-10">${catNombre}</span>
                                <span class="text-5xl font-black text-[var(--text-muted)] opacity-10 group-hover:opacity-20 transition-all duration-500 transform group-hover:scale-110 select-none">${letraInicial}</span>
                            </div>
                            
                            <div class="p-4 flex-1 flex flex-col justify-between">
                                <h3 class="text-[12px] xl:text-[13px] font-bold text-[var(--text-main)] leading-snug line-clamp-2">${prod.nombre}</h3>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-[14px] font-black text-[var(--text-main)] tracking-tight">$${precioNum.toFixed(2)}</span>
                                    <div class="w-6 h-6 rounded-full bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] group-hover:bg-[#3b82f6] group-hover:text-white group-hover:border-transparent transition-all duration-300 shadow-sm">
                                        <i class="fas fa-plus text-[10px]"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                gridProd.innerHTML = `
                    <div class="col-span-full flex flex-col items-center justify-center text-[var(--text-muted)] mt-20">
                        <i class="fas fa-box-open text-4xl mb-4 opacity-50"></i>
                        <p class="text-xs font-medium">Catálogo vacío</p>
                    </div>`;
            }
        }
        
        document.addEventListener('DOMContentLoaded', renderizarMenu);

        function filtrarCategoria(nombreCat, btn) {
            document.querySelectorAll('.cat-btn').forEach(el => el.className = "cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--border-highlight)] text-[11px] font-semibold tracking-wide shadow-sm transition-all outline-none");
            btn.className = "cat-btn px-6 py-2.5 rounded-full bg-[var(--text-main)] text-[var(--bg-base)] text-[11px] font-bold tracking-wide shadow-sm transition-all outline-none border border-transparent";
            
            document.querySelectorAll('.producto-card').forEach(card => {
                card.style.display = (nombreCat === 'Todos' || card.getAttribute('data-categoria-item') === nombreCat) ? 'flex' : 'none';
            });
        }

        let ticketSubtotal = 0; let itemActivo = null; let contadorItems = 0; let tiempoGlobal = 'sin-tiempo'; let gramajePendiente = null;
        let numeroPersonas = {{ $mesa->capacidad ?? 4 }}; let descuentoPorcentaje = 0; let notaGeneral = '';

        const listaTicket = document.getElementById('listaTicket');
        const estadoVacio = document.getElementById('estadoVacio');
        const barraModificadores = document.getElementById('barraModificadores');
        const contenedorBotonesModificadores = document.getElementById('contenedorBotonesModificadores');
        
        function agregarAlTicket(id, nombre, precio, categoria, arrayModificadores = []) {
            cambiarTab('nueva-orden', document.getElementById('btn-tab-nueva-orden'));
            estadoVacio.classList.add('hidden');

            const modsString = JSON.stringify(arrayModificadores).replace(/'/g, "&#39;").replace(/"/g, "&quot;");
            const precioUnitario = parseFloat(precio);
            const gramajeKey = gramajePendiente ? gramajePendiente.toString() : 'sin-gramaje';

            const existingItem = Array.from(listaTicket.querySelectorAll('.ticket-item')).find(item => {
                return parseInt(item.dataset.productoId, 10) === id
                    && item.dataset.modificadores === modsString
                    && item.dataset.gramaje === gramajeKey
                    && item.dataset.tiempo === tiempoGlobal; 
            });

            if (existingItem) {
                const cantidadSpan = existingItem.querySelector('.cantidad-platillo');
                const cantidad = parseInt(cantidadSpan.innerText, 10) + 1;
                cantidadSpan.innerText = cantidad;
                existingItem.dataset.cantidad = cantidad;

                const precioSpan = existingItem.querySelector('.precio-platillo');
                precioSpan.innerText = '$' + (precioUnitario * cantidad).toFixed(2);
                existingItem.dataset.precio = precioUnitario;

                ticketSubtotal += precioUnitario;
                actualizarTotales();
                seleccionarItem(existingItem.id);
            } else {
                contadorItems++;
                const itemId = 'ticket-item-' + contadorItems;
                
                const etiquetaGramaje = gramajePendiente ? `<span class="text-[7px] text-[var(--text-muted)] font-bold bg-[var(--input-bg)] border border-[var(--border-color)] px-1.5 py-0.5 rounded-md shadow-sm">${gramajePendiente}g</span>` : '';
                
                let etiquetaTiempo = '';
                if (tiempoGlobal !== 'sin-tiempo') {
                    const t = tiempoGlobal === 'primer-tiempo' ? '1T' : (tiempoGlobal === 'segundo-tiempo' ? '2T' : '3T');
                    etiquetaTiempo = `<span class="text-[7px] text-[var(--text-muted)] font-bold bg-[var(--input-bg)] border border-[var(--border-color)] px-1.5 py-0.5 rounded-md shadow-sm">${t}</span>`;
                }

                const itemHTML = `
                    <div id="${itemId}" data-producto-id="${id}" data-cantidad="1" data-precio="${precioUnitario}" data-modificadores="${modsString}" data-gramaje="${gramajeKey}" data-tiempo="${tiempoGlobal}" class="ticket-item animate-item relative w-full rounded-[18px] bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-sm p-4 flex flex-col gap-3 cursor-pointer transition-all duration-300 outline-none" onclick="seleccionarItem('${itemId}')">
                        
                        <div class="flex justify-between items-start gap-2">
                            <div class="flex-1">
                                <h3 class="text-[12px] font-bold text-[var(--text-main)] leading-tight nombre-platillo">${nombre}</h3>
                                <div class="flex flex-wrap gap-1.5 mt-1.5 empty:hidden">
                                    <div class="gramaje-etiqueta empty:hidden">${etiquetaGramaje}</div>
                                    ${etiquetaTiempo}
                                </div>
                            </div>
                            <span class="text-[13px] font-black text-[var(--text-main)] tracking-tight precio-platillo">$${precioUnitario.toFixed(2)}</span>
                        </div>

                        {{-- Notas ámbar integradas --}}
                        <div class="modificadores-lista w-full text-[10px] text-orange-500 font-medium leading-relaxed empty:hidden"></div>

                        <div class="flex items-center justify-between pt-3 mt-1 border-t border-[var(--border-color)]">
                            <div class="flex items-center bg-[var(--input-bg)] rounded-[10px] p-0.5 border border-[var(--border-color)] shadow-inner" onclick="event.stopPropagation();">
                                <button type="button" onclick="decrementarCantidad('${itemId}')" class="w-7 h-7 rounded-[8px] flex items-center justify-center text-[var(--text-muted)] hover:bg-[var(--bg-panel)] hover:text-[var(--text-main)] hover:shadow-sm transition-all outline-none">
                                    <i class="fas fa-minus text-[10px]"></i>
                                </button>
                                <span class="cantidad-platillo w-8 text-center text-[12px] font-bold text-[var(--text-main)]">1</span>
                                <button type="button" onclick="incrementarCantidad('${itemId}')" class="w-7 h-7 rounded-[8px] flex items-center justify-center text-[var(--text-muted)] hover:bg-[var(--bg-panel)] hover:text-[var(--text-main)] hover:shadow-sm transition-all outline-none">
                                    <i class="fas fa-plus text-[10px]"></i>
                                </button>
                            </div>

                            <button type="button" onclick="eliminarItemFila(this); event.stopPropagation();" class="hidden btn-control-eliminar w-8 h-8 rounded-[8px] text-red-500 bg-red-500/10 border border-red-500/20 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center outline-none">
                                <i class="fas fa-trash-alt text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                `;

                listaTicket.insertAdjacentHTML('beforeend', itemHTML);
                ticketSubtotal += precioUnitario;
                actualizarTotales();
                seleccionarItem(itemId);
                listaTicket.parentElement.scrollTop = listaTicket.parentElement.scrollHeight;
            }

            if (gramajePendiente) { gramajePendiente = null; document.getElementById('indicador-gramaje-pendiente').classList.add('hidden'); }
        }
        
        function seleccionarItem(id) {
            deseleccionarTicket(); 
            itemActivo = document.getElementById(id);
            if(itemActivo) {
                itemActivo.classList.add('bg-[#3b82f6]/5', 'border-[#3b82f6]/40');
                itemActivo.classList.remove('bg-[var(--bg-panel)]', 'border-[var(--border-color)]');
                itemActivo.querySelector('.btn-control-eliminar').classList.remove('hidden');
                itemActivo.querySelector('.btn-control-eliminar').classList.add('flex');
                
                const modsString = itemActivo.getAttribute('data-modificadores');
                const modificadoresParaPintar = JSON.parse(modsString || '[]');
                contenedorBotonesModificadores.innerHTML = '';
                
                if(modificadoresParaPintar.length > 0) {
                    modificadoresParaPintar.forEach(mod => {
                        const nombreMod = mod.nombre || mod.descripcion || mod; 
                        contenedorBotonesModificadores.insertAdjacentHTML('beforeend', `<button onclick="agregarModificadorFijo('${nombreMod}')" class="px-5 py-2 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-main)] text-[10px] font-bold hover:border-[#3b82f6] transition-all shadow-sm">${nombreMod}</button>`);
                    });
                    barraModificadores.classList.remove('hidden');
                } else {
                    barraModificadores.classList.add('hidden');
                }
            }
        }

        function deseleccionarTicket() {
            document.querySelectorAll('.ticket-item').forEach(el => {
                el.classList.remove('bg-[#3b82f6]/5', 'border-[#3b82f6]/40');
                el.classList.add('bg-[var(--bg-panel)]', 'border-[var(--border-color)]');
                if(el.querySelector('.btn-control-eliminar')) {
                    el.querySelector('.btn-control-eliminar').classList.add('hidden');
                    el.querySelector('.btn-control-eliminar').classList.remove('flex');
                }
            });
            itemActivo = null; barraModificadores.classList.add('hidden');
        }

        function agregarModificadorFijo(texto) {
            if (itemActivo) {
                const list = itemActivo.querySelector('.modificadores-lista');
                const separador = list.children.length > 0 ? `<span class="mx-1.5 opacity-50 text-[12px] leading-none text-orange-500">•</span>` : `<i class="fas fa-pen mr-1.5 opacity-70 text-[9px] text-orange-500"></i>`;
                list.insertAdjacentHTML('beforeend', `<span class="inline-flex items-center"><span class="nota-texto-real">${separador}${texto}</span></span>`);
            }
        }

        function guardarNota() {
            const nota = document.getElementById('notaTextarea').value.trim();
            if (!itemActivo) { cerrarModal('modalNota'); return; }
            if (nota.length === 0) { mostrarError('Nota vacía.'); return; }
            
            const list = itemActivo.querySelector('.modificadores-lista');
            const separador = list.children.length > 0 ? `<span class="mx-1.5 opacity-50 text-[12px] leading-none text-orange-500">•</span>` : `<i class="fas fa-pen mr-1.5 opacity-70 text-[9px] text-orange-500"></i>`;
            list.insertAdjacentHTML('beforeend', `<span class="inline-flex items-center"><span class="nota-texto-real">${separador}${nota}</span></span>`);
            
            itemActivo.dataset.nota = nota; notaGeneral = nota; cerrarModal('modalNota');
        }

        function incrementarCantidad(id) {
            const item = document.getElementById(id); if (!item) return;
            const cantidadSpan = item.querySelector('.cantidad-platillo');
            const precioUnitario = parseFloat(item.dataset.precio) || 0;
            let cantidad = parseInt(cantidadSpan.innerText, 10) + 1;
            cantidadSpan.innerText = cantidad; item.dataset.cantidad = cantidad;
            item.querySelector('.precio-platillo').innerText = '$' + (precioUnitario * cantidad).toFixed(2);
            ticketSubtotal += precioUnitario; actualizarTotales();
        }
        
        function decrementarCantidad(id) {
            const item = document.getElementById(id); if (!item) return;
            const cantidadSpan = item.querySelector('.cantidad-platillo');
            const precioUnitario = parseFloat(item.dataset.precio) || 0;
            let cantidad = parseInt(cantidadSpan.innerText, 10) - 1;
            if (cantidad <= 0) { eliminarItemFila(item.querySelector('.btn-control-eliminar')); return; }
            cantidadSpan.innerText = cantidad; item.dataset.cantidad = cantidad;
            item.querySelector('.precio-platillo').innerText = '$' + (precioUnitario * cantidad).toFixed(2);
            ticketSubtotal -= precioUnitario; actualizarTotales();
        }

        function eliminarItemFila(btn) {
            const fila = btn.closest('.ticket-item');
            ticketSubtotal -= (parseInt(fila.dataset.cantidad, 10) * parseFloat(fila.dataset.precio));
            fila.remove(); actualizarTotales(); deseleccionarTicket();
            if(document.getElementById('listaTicket').children.length === 0) document.getElementById('estadoVacio').classList.remove('hidden');
        }

        function actualizarTotales() {
            const subtotalConDescuento = Math.max(0, ticketSubtotal - (ticketSubtotal * (descuentoPorcentaje / 100)));
            const iva = subtotalConDescuento * 0.16;
            document.getElementById('txtSubtotal').innerText = '$' + subtotalConDescuento.toFixed(2);
            document.getElementById('txtIva').innerText = '$' + iva.toFixed(2);
            actualizarVistaTotal();
        }

        function limpiarTicket() {
            document.getElementById('listaTicket').innerHTML = ''; document.getElementById('estadoVacio').classList.remove('hidden');
            ticketSubtotal = 0; descuentoPorcentaje = 0; notaGeneral = ''; actualizarTotales(); deseleccionarTicket();
        }

        // FUNCIONES COMPLEMENTARIAS
        function cambiarTiempoGlobal(tiempo) {
            tiempoGlobal = tiempo;
            const mapas = ['sin-tiempo', 'primer-tiempo', 'segundo-tiempo', 'tercer-tiempo'];
            const ids = ['tiempo-global-sin', 'tiempo-global-1', 'tiempo-global-2', 'tiempo-global-3'];
            ids.forEach((id, i) => {
                const btn = document.getElementById(id);
                if (mapas[i] === tiempo) btn.className = "w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold bg-[var(--text-main)] text-[var(--bg-base)] transition-all";
                else btn.className = "w-8 h-6 rounded flex items-center justify-center text-[10px] font-bold text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all";
            });
        }

        function ajustarGramaje() {
            const modal = document.getElementById('modalGramaje'); const input = document.getElementById('gramajeInput');
            if (itemActivo) { input.value = itemActivo.dataset.gramaje === 'sin-gramaje' ? '' : itemActivo.dataset.gramaje; document.getElementById('modalGramajeTitulo').innerText = itemActivo.querySelector('.nombre-platillo').innerText; } 
            else { input.value = gramajePendiente || ''; document.getElementById('modalGramajeTitulo').innerText = 'Gramaje'; }
            modal.classList.remove('hidden'); input.focus();
        }
        
        function guardarGramajeDelItem() {
            const val = document.getElementById('gramajeInput').value.trim() || null;
            if (itemActivo) {
                itemActivo.dataset.gramaje = val ? val.toString() : 'sin-gramaje';
                itemActivo.querySelector('.gramaje-etiqueta').innerHTML = val ? `<span class="text-[9px] text-[var(--text-muted)] font-bold bg-[var(--input-bg)] border border-[var(--border-color)] px-1.5 py-0.5 rounded-md shadow-sm">${val}g</span>` : '';
            } else {
                gramajePendiente = val; const ind = document.getElementById('indicador-gramaje-pendiente');
                if(val) { ind.innerText = val + 'g'; ind.classList.remove('hidden'); } else { ind.classList.add('hidden'); }
            }
            cerrarModal('modalGramaje');
        }

        function anadirNumeroGramaje(num) { const i = document.getElementById('gramajeInput'); i.value = (i.value === '0' ? '' : i.value) + num; }
        function borrarNumeroGramaje() { const i = document.getElementById('gramajeInput'); i.value = i.value.slice(0, -1); }
        function mostrarToast(msg, type='info') { const c = document.getElementById('toastContainer'); if(!c)return; const t = document.createElement('div'); t.className=`toast-panel ${type}`; t.innerHTML=`<div class="toast-icon"><i class="fas fa-${type==='success'?'check':type==='error'?'exclamation-triangle':'info'}"></i></div><div><strong>${type==='success'?'Éxito':type==='error'?'Error':'Aviso'}</strong><span>${msg}</span></div>`; c.appendChild(t); requestAnimationFrame(()=>t.classList.add('show')); setTimeout(()=>{t.classList.remove('show'); t.addEventListener('transitionend', ()=>t.remove(), {once:true});}, 3000); }
        function mostrarError(m) { mostrarToast(m, 'error'); } function mostrarExito(m) { mostrarToast(m, 'success'); }
        function ajustarPersonas() { document.getElementById('personasInput').value = numeroPersonas; document.getElementById('modalPersonas').classList.remove('hidden'); }
        function guardarPersonas() { const v = parseInt(document.getElementById('personasInput').value, 10); if(isNaN(v) || v<=0){ mostrarError('Inválido'); return;} numeroPersonas = v; document.getElementById('txtPersonas').innerText = v; cerrarModal('modalPersonas'); }
        function agregarNota() { if(!itemActivo) { mostrarError('Selecciona platillo'); return; } document.getElementById('notaTextarea').value = ''; document.getElementById('modalNota').classList.remove('hidden'); }
        function aplicarDescuento() { document.getElementById('descuentoInput').value = descuentoPorcentaje; document.getElementById('modalDescuento').classList.remove('hidden'); }
        function guardarDescuento() { const v = parseFloat(document.getElementById('descuentoInput').value); if(isNaN(v)||v<0||v>100){mostrarError('Inválido'); return;} descuentoPorcentaje = v; actualizarTotales(); cerrarModal('modalDescuento'); }
        function mostrarPromociones() { window.location.href = '{{ route("admin.promociones.index") ?? "#" }}'; }
        function cerrarModal(id) { document.getElementById(id).classList.add('hidden'); }
        function insertarNotaCaracter(c) { const t = document.getElementById('notaTextarea'); t.value += c; t.focus(); }
        function insertarNotaEspacio() { const t = document.getElementById('notaTextarea'); t.value += ' '; t.focus(); }
        function borrarNotaCaracter() { const t = document.getElementById('notaTextarea'); t.value = t.value.slice(0,-1); t.focus(); }
        function limpiarNota() { const t = document.getElementById('notaTextarea'); t.value = ''; t.focus(); }
        function obtenerSubtotalConDescuento() { return Math.max(0, ticketSubtotal - (ticketSubtotal * (descuentoPorcentaje / 100))); }
        function imprimirPrecuenta() { mostrarExito("Imprimiendo pre-cuenta..."); }
        function marcharTiempos() { mostrarExito("¡Marchando platillos!"); }

        let capitanAutorizado = false; let mesaDestinoSeleccionada = null; let mesaDestinoSeleccionadaNumero = null;
        async function llamarCapitan() {
            const nip = prompt("NIP Capitán:"); if(!nip) return;
            try {
                const res = await fetch('/mesero/capitan/verify', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ nip: nip.trim() }) });
                const data = await res.json().catch(()=>null);
                if (!res.ok) throw new Error(data?.message || 'Error');
                if (data.success) { 
                    capitanAutorizado = true; 
                    mostrarExito('Capitán autorizado.');
                    const container = document.getElementById('capitanMesasContainer');
                    container.innerHTML = '';
                    if (Array.isArray(data.mesas) && data.mesas.length > 0) {
                        data.mesas.forEach(m => {
                            const btn = document.createElement('button');
                            btn.type = 'button'; btn.dataset.mesaId = m.id;
                            btn.className = 'w-full text-left rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] px-4 py-4 hover:border-[#3B82F6]/50 transition-all flex items-center justify-between gap-4';
                            btn.innerHTML = `<div><p class="text-[10px] uppercase tracking-[0.2em] text-[var(--text-muted)] font-bold">Mesa abierta</p><h3 class="text-lg font-black text-[var(--text-main)]">${m.numero}</h3></div><span class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-400"><i class="fas fa-circle text-[6px]"></i> ${m.estado ? m.estado.charAt(0).toUpperCase() + m.estado.slice(1) : ''}</span>`;
                            btn.addEventListener('click', () => seleccionarMesaDestino(m.id, m.numero));
                            container.appendChild(btn);
                        });
                    } else {
                        container.innerHTML = `<div class="rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] p-6 text-center text-[var(--text-muted)]"><p class="font-bold text-sm mb-2">No hay mesas abiertas disponibles.</p></div>`;
                    }
                    document.getElementById('modalCapitan').classList.remove('hidden'); 
                }
            } catch (err) { mostrarError(err.message); }
        }
        
        function seleccionarMesaDestino(mesaId, mesaNumero) {
            mesaDestinoSeleccionada = mesaId; mesaDestinoSeleccionadaNumero = mesaNumero;
            const container = document.getElementById('capitanMesasContainer');
            if (container) {
                container.querySelectorAll('button[data-mesa-id]').forEach(btn => {
                    btn.classList.remove('border-blue-500/50', 'bg-blue-500/10');
                    btn.classList.add('border-[var(--border-color)]', 'bg-[var(--bg-base)]');
                });
                const button = container.querySelector(`button[data-mesa-id="${mesaId}"]`);
                if (button) {
                    button.classList.remove('border-[var(--border-color)]', 'bg-[var(--bg-base)]');
                    button.classList.add('border-blue-500/50', 'bg-blue-500/10');
                }
            }
            actualizarMensajeDestino();
            document.getElementById('modalCapitan').classList.add('hidden');
            mostrarExito(`Mesa ${mesaNumero} lista para envío.`);
        }
        
        function actualizarMensajeDestino() {
            const mensaje = document.getElementById('mensajeMesaDestino');
            const btnEnviar = document.getElementById('btn-enviar');
            if (capitanAutorizado && mesaDestinoSeleccionada && mesaDestinoSeleccionadaNumero) {
                mensaje.innerText = `Destino: Mesa ${mesaDestinoSeleccionadaNumero}`;
                btnEnviar.innerHTML = `<i class="fas fa-paper-plane text-sm"></i> <span>Enviar a Mesa ${mesaDestinoSeleccionadaNumero}</span>`;
            } else {
                mensaje.innerText = 'Enviar a cocina o selecciona mesa destino.';
                btnEnviar.innerHTML = `<i class="fas fa-paper-plane text-sm"></i> <span>Enviar Orden</span>`;
            }
        }

        function enviarACocina() {
            const itemsHTML = document.querySelectorAll('.ticket-item');
            if(itemsHTML.length === 0) { mostrarError("¡Agrega platillos!"); return; }

            const platillosData = [];
            itemsHTML.forEach(item => {
                const nombre = item.querySelector('.nombre-platillo').innerText;
                const modsElementos = item.querySelectorAll('.nota-texto-real');
                const mods = []; modsElementos.forEach(m => mods.push(m.innerText.replace('•','').trim()));
                platillosData.push({ 
                    id: parseInt(item.dataset.productoId, 10), nombre: nombre, 
                    cantidad: parseInt(item.dataset.cantidad, 10), precio: parseFloat(item.dataset.precio), 
                    notas: mods.join(' '), modificadores: mods, 
                    gramaje: item.dataset.gramaje === 'sin-gramaje' ? null : item.dataset.gramaje, 
                    tiempo: item.dataset.tiempo 
                });
            });

            const btn = document.getElementById('btn-enviar'); btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; btn.disabled = true;
            fetch('/mesero/comanda/enviar', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ mesa_id: mesaDestinoSeleccionada || {{ $mesa->id ?? 1 }}, platillos: platillosData, total: parseFloat(document.getElementById('txtTotal').innerText.replace('$','')), personas: numeroPersonas, descuento_porcentaje: descuentoPorcentaje, nota_general: notaGeneral })
            })
            .then(res => res.json()).then(data => {
                if (data.success) { 
                    mostrarExito("¡Enviado a cocina!"); 
                    setTimeout(() => window.location.href = '{{ route("mesero.dashboard") }}', 1000); 
                } 
                else throw new Error(data.message);
            }).catch(error => { mostrarError(error.message); btn.innerHTML = '<i class="fas fa-paper-plane text-sm"></i><span>Enviar Orden</span>'; btn.disabled = false; });
        }
    </script>
</body>
</html>