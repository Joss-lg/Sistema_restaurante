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
            --bg-base: #050505; 
            --bg-panel: #0E0E12; 
            --border-color: #1F1F24; 
            --text-main: #FFFFFF;
            --text-muted: #71717A;
            --accent: #3B82F6;
        }

        body.modo-crema {
            --bg-base: #F4F4F5; 
            --bg-panel: #FFFFFF;
            --border-color: rgba(0, 0, 0, 0.08);
            --text-main: #09090B;
            --text-muted: #A1A1AA;
        }

        body { 
            background-color: var(--bg-base);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            overflow: hidden; 
            transition: background-color 0.4s ease, color 0.4s ease;
        }
        
        /* Scrollbars limpias: ocultas donde se usan y con thumb discreto en WebKit */
        .hide-scroll {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .hide-scroll::-webkit-scrollbar {
            width: 0;
            height: 0;
        }
        .hide-scroll::-webkit-scrollbar-thumb,
        .hide-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        /* Opcional: scrollbars suaves en contenido que sí debe aparecer */
        .smooth-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .smooth-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.12);
            border-radius: 999px;
        }
        .smooth-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .smooth-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.12) transparent;
        }
        
        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(5px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-item { animation: fade-in-up 0.2s ease-out forwards; }
        
        .toast-wrapper {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: flex-end;
            pointer-events: none;
        }
        .toast-panel {
            min-width: 18rem;
            max-width: 28rem;
            pointer-events: auto;
            background: rgba(11, 15, 25, 0.92);
            color: #F8FAFC;
            border: 1px solid rgba(148, 163, 184, 0.15);
            border-left-width: 4px;
            border-radius: 1.25rem;
            backdrop-filter: blur(20px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
            padding: 1rem 1.25rem;
            opacity: 0;
            transform: translateX(28px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            font-size: 0.95rem;
            line-height: 1.5;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.85rem;
            align-items: center;
        }
        .toast-panel.show { opacity: 1; transform: translateX(0); }
        .toast-panel.success { border-color: rgba(16, 185, 129, 0.25); }
        .toast-panel.error { border-color: rgba(239, 68, 68, 0.25); }
        .toast-panel.info { border-color: rgba(59, 130, 246, 0.25); }
        .toast-panel.warning { border-color: rgba(245, 158, 11, 0.25); }
        .toast-panel .toast-icon { width: 2.25rem; height: 2.25rem; display: grid; place-items: center; border-radius: 1rem; background: rgba(255, 255, 255, 0.08); color: inherit; }
        .toast-panel.success .toast-icon { background: rgba(16, 185, 129, 0.18); color: #4ade80; }
        .toast-panel.error .toast-icon { background: rgba(239, 68, 68, 0.18); color: #f87171; }
        .toast-panel.info .toast-icon { background: rgba(59, 130, 246, 0.18); color: #60a5fa; }
        .toast-panel.warning .toast-icon { background: rgba(245, 158, 11, 0.18); color: #f59e0b; }
        .toast-panel strong { display: block; color: inherit; font-weight: 700; letter-spacing: 0.01em; }
        .toast-panel span { color: rgba(248, 250, 252, 0.84); font-size: 0.88rem; line-height: 1.55; }
        body.modo-crema .toast-panel { background: rgba(255, 255, 255, 0.96); color: #1f2937; border-color: #e5e7eb; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08); }
        body.modo-crema .toast-panel.success .toast-icon { background: #dcfce7; color: #166534; }
        body.modo-crema .toast-panel.error .toast-icon { background: #fee2e2; color: #991b1b; }
        body.modo-crema .toast-panel.info .toast-icon { background: #dbeafe; color: #1d4ed8; }
        body.modo-crema .toast-panel.warning .toast-icon { background: #ffedd5; color: #c2410c; }
        body.modo-crema .toast-panel span { color: #52525b; }
        
        aside, section, main, div, button { transition: background-color 0.3s ease, border-color 0.3s ease; }
    </style>
</head>
<body class="h-screen w-full flex selection:bg-[#3B82F6]/30">

    <script>if (localStorage.getItem('tema-ollintem') === 'crema') document.body.classList.add('modo-crema');</script>
    <div id="toastContainer" class="toast-wrapper" aria-live="polite" aria-atomic="true"></div>

    {{-- ========================================== --}}
    {{-- COLUMNA 1: ACCIONES RÁPIDAS (LATERAL IZQ)  --}}
    {{-- ========================================== --}}
    <aside class="w-[170px] md:w-[190px] xl:w-[220px] 2xl:w-[260px] flex-shrink-0 h-full flex flex-col bg-[var(--bg-base)] border-r border-[var(--border-color)] p-3 lg:p-4 z-20 transition-all">
        
        {{-- Botón Cerrar --}}
        <button onclick="window.location.href='{{ route('mesero.dashboard') ?? '#' }}'" class="w-full h-10 xl:h-12 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black text-[9px] xl:text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-rose-500/10 hover:text-rose-500 hover:border-rose-500/30 transition-all active:scale-95 outline-none mb-4">
            <i class="fas fa-times"></i> Cerrar
        </button>

        {{-- Info Mesa y Modo Claro --}}
        <div class="flex items-center justify-between px-1 mb-4">
            <h3 class="text-base lg:text-lg xl:text-xl font-black tracking-tighter text-[var(--text-main)] truncate">Mesa {{ $mesa->numero ?? '12M' }}</h3>
            <button onclick="toggleTheme()" class="w-7 h-7 xl:w-8 xl:h-8 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] flex items-center justify-center hover:border-[#3B82F6]/50 transition-all outline-none shadow-sm group flex-shrink-0">
                <i id="themeIcon" class="fas fa-sun text-[10px] xl:text-xs text-[var(--text-muted)] group-hover:scale-110 transition-transform"></i>
            </button>
        </div>

        {{-- Grid de Botones (Más compacto y fluido) --}}
        <div class="grid grid-cols-2 gap-2 xl:gap-3 flex-1 overflow-y-auto hide-scroll pb-2">
            <button onclick="ajustarPersonas()" class="aspect-square flex flex-col items-center justify-center rounded-xl lg:rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#3B82F6]/50 transition-all active:scale-95 group outline-none p-2">
                <i class="fas fa-user-friends text-[#3B82F6] mb-1.5 text-sm xl:text-lg drop-shadow-[0_0_8px_rgba(59,130,246,0.4)]"></i>
                <span class="text-[7px] xl:text-[8px] font-black uppercase tracking-widest text-[#3B82F6]">Personas</span>
                <span id="txtPersonas" class="text-[8px] xl:text-[9px] font-bold text-[#3B82F6] mt-0.5">{{ $mesa->capacidad ?? 1 }}</span>
            </button>
            
            <button onclick="imprimirPrecuenta()" class="aspect-square flex flex-col items-center justify-center rounded-xl lg:rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-slate-300/50 transition-all active:scale-95 group outline-none p-2">
                <i class="fas fa-receipt text-slate-300 mb-1.5 text-sm xl:text-lg drop-shadow-[0_0_8px_rgba(203,213,225,0.4)]"></i>
                <span class="text-[7px] xl:text-[8px] font-black uppercase tracking-widest text-slate-300 text-center leading-tight">Pre<br>Cuenta</span>
            </button>

            <button onclick="agregarNota()" class="aspect-square flex flex-col items-center justify-center rounded-xl lg:rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#8B5CF6]/50 transition-all active:scale-95 group outline-none p-2">
                <i class="fas fa-comment-alt text-[#8B5CF6] mb-1.5 text-sm xl:text-lg drop-shadow-[0_0_8px_rgba(139,92,246,0.4)]"></i>
                <span class="text-[7px] xl:text-[8px] font-black uppercase tracking-widest text-[#8B5CF6]">Nota</span>
            </button>
            
            <button onclick="aplicarDescuento()" class="aspect-square flex flex-col items-center justify-center rounded-xl lg:rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-pink-500/50 transition-all active:scale-95 group outline-none p-2">
                <i class="fas fa-percent text-pink-500 mb-1.5 text-sm xl:text-lg drop-shadow-[0_0_8px_rgba(236,72,153,0.4)]"></i>
                <span class="text-[7px] xl:text-[8px] font-black uppercase tracking-widest text-pink-500">Desc.</span>
            </button>
            
            {{-- BOTÓN GRAMAJE INTELIGENTE --}}
            <button id="btn-gramaje" onclick="ajustarGramaje()" class="relative aspect-square flex flex-col items-center justify-center rounded-xl lg:rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#f97316]/50 transition-all active:scale-95 group outline-none p-2">
                <i class="fas fa-weight-scale text-[#f97316] mb-1.5 text-sm xl:text-lg drop-shadow-[0_0_8px_rgba(249,115,22,0.4)]"></i>
                <span class="text-[7px] xl:text-[8px] font-black uppercase tracking-widest text-[#f97316]">Gramaje</span>
                <span id="indicador-gramaje-pendiente" class="hidden absolute top-1 right-1 bg-[#f97316] text-white text-[8px] font-black px-1.5 py-0.5 rounded-md shadow-md"></span>
            </button>
            
            <button onclick="llamarCapitan()" class="aspect-square flex flex-col items-center justify-center rounded-xl lg:rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-amber-500/50 transition-all active:scale-95 group outline-none p-2">
                <i class="fas fa-exchange-alt text-amber-500 mb-1.5 text-sm xl:text-lg drop-shadow-[0_0_8px_rgba(245,158,11,0.4)]"></i>
                <span class="text-[7px] xl:text-[8px] font-black uppercase tracking-widest text-amber-500 text-center leading-tight">Traspaso</span>
            </button>
            
            <button onclick="mostrarPromociones()" class="aspect-square flex flex-col items-center justify-center rounded-xl lg:rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#3B82F6]/50 transition-all active:scale-95 group outline-none p-2">
                <i class="fas fa-tag text-[#3B82F6] mb-1.5 text-sm xl:text-lg drop-shadow-[0_0_8px_rgba(59,130,246,0.4)]"></i>
                <span class="text-[7px] xl:text-[8px] font-black uppercase tracking-widest text-[#3B82F6]">Promos</span>
            </button>
            
            <button onclick="marcharTiempos()" class="aspect-square flex flex-col items-center justify-center rounded-xl lg:rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-orange-500/50 transition-all active:scale-95 group outline-none p-2">
                <i class="fas fa-fire text-orange-500 mb-1.5 text-sm xl:text-lg drop-shadow-[0_0_8px_rgba(249,115,22,0.4)]"></i>
                <span class="text-[7px] xl:text-[8px] font-black uppercase tracking-widest text-orange-500">Marchar</span>
            </button>
            
            @if($esCapitan ?? false)
                <button onclick="llamarCapitan()" class="col-span-2 h-12 lg:h-14 xl:h-16 flex items-center justify-center gap-2 rounded-xl lg:rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-indigo-400/50 transition-all active:scale-95 group outline-none">
                    <i class="fas fa-user-shield text-indigo-400 text-sm xl:text-base drop-shadow-[0_0_8px_rgba(129,140,248,0.4)]"></i>
                    <span class="text-[8px] xl:text-[10px] font-black uppercase tracking-widest text-indigo-400">Capitán</span>
                </button>
            @endif
        </div>

        <button onclick="limpiarTicket()" class="mt-3 w-full h-10 xl:h-12 rounded-xl border border-rose-500/30 bg-[var(--bg-panel)] hover:bg-rose-500 hover:text-white text-rose-500 transition-all active:scale-95 outline-none flex items-center justify-center gap-2 flex-shrink-0">
            <i class="fas fa-trash-alt text-[10px]"></i>
            <span class="text-[8px] xl:text-[9px] font-black uppercase tracking-widest">Eliminar Todo</span>
        </button>
    </aside>

    @if($esCapitan ?? false)
        <div id="modalCapitan" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        @php $mesasAbiertas = $mesasAbiertas ?? collect(); @endphp
        <div class="w-full max-w-lg rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.2em] text-indigo-400 font-black">Autorización Capitán</p>
                    <h2 class="text-2xl font-black text-[var(--text-main)] mt-1">Selecciona la mesa destino</h2>
                </div>
                <button onclick="cerrarModal('modalCapitan')" class="text-[var(--text-muted)] hover:text-white outline-none text-xl"><i class="fas fa-times"></i></button>
            </div>
            <div id="capitanMesasContainer" class="grid gap-3 max-h-[420px] overflow-y-auto hide-scroll pb-2">
                {{-- El contenido se cargará dinámicamente mediante AJAX al verificar el NIP del capitán. --}}
            </div>
            <div class="mt-5 text-right">
                <button onclick="cerrarModal('modalCapitan')" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-bold text-[var(--text-muted)] hover:text-white transition-all">Cancelar</button>
            </div>
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- COLUMNA 2: TICKET / COMANDA (CENTRAL)      --}}
    {{-- ========================================== --}}
    <section class="w-[280px] md:w-[320px] xl:w-[360px] 2xl:w-[400px] flex-shrink-0 h-full flex flex-col bg-[var(--bg-panel)] border-r border-[var(--border-color)] relative z-10 shadow-2xl transition-all">
        
        {{-- CABECERA ULTRA COMPACTA --}}
        <div class="p-3 xl:p-4 border-b border-[var(--border-color)] flex flex-col gap-2.5">
            
            {{-- Tabs super estilizados (Segmented Control iOS Modificado) --}}
            <div class="relative flex w-full bg-[var(--bg-base)] p-1 rounded-xl border border-[var(--border-color)] shadow-inner">
                {{-- Fondo animado --}}
                <div id="tab-slider" class="absolute left-1 top-1 h-[calc(100%-8px)] w-[calc(33.333%-2.6px)] rounded-lg bg-[#3B82F6] shadow-[0_4px_15px_-3px_rgba(59,130,246,0.5)] transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"></div>

                <button onclick="cambiarTab('nueva-orden', this)" id="btn-tab-nueva-orden" class="tab-btn relative z-10 flex-1 py-1.5 xl:py-2 text-[8px] xl:text-[9px] font-black uppercase tracking-widest text-white transition-colors duration-300 outline-none">
                    Orden
                </button>
                <button onclick="cambiarTab('enviados', this)" id="btn-tab-enviados" class="tab-btn relative z-10 flex-1 py-1.5 xl:py-2 text-[8px] xl:text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors duration-300 outline-none">
                    Enviado
                </button>
                <button onclick="cambiarTab('comanda', this)" id="btn-tab-comanda" class="tab-btn relative z-10 flex-1 py-1.5 xl:py-2 text-[8px] xl:text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors duration-300 outline-none">
                    Total
                </button>
            </div>

            {{-- Selector Maestro de Tiempos --}}
            <div class="flex items-center justify-between bg-[var(--bg-base)] p-1.5 xl:p-2 rounded-xl border border-[var(--border-color)]">
                <span class="text-[8px] font-black uppercase tracking-[0.2em] text-[#f97316] ml-2">Tiempos:</span>
                <div class="flex gap-1 pr-1">
                    <button onclick="cambiarTiempoGlobal('sin-tiempo')" id="tiempo-global-sin" class="px-2 xl:px-3 py-1 rounded text-[8px] xl:text-[9px] font-black uppercase tracking-tight bg-[#3B82F6] text-white shadow-sm tiempo-global-btn transition-all" title="Sin Tiempo">S</button>
                    <button onclick="cambiarTiempoGlobal('primer-tiempo')" id="tiempo-global-1" class="px-2 xl:px-3 py-1 rounded text-[8px] xl:text-[9px] font-black uppercase tracking-tight border border-[#f97316]/30 bg-[var(--bg-panel)] text-[#f97316] hover:bg-[#f97316] hover:text-white transition-all tiempo-global-btn" title="Primer Tiempo">1</button>
                    <button onclick="cambiarTiempoGlobal('segundo-tiempo')" id="tiempo-global-2" class="px-2 xl:px-3 py-1 rounded text-[8px] xl:text-[9px] font-black uppercase tracking-tight border border-[#f97316]/30 bg-[var(--bg-panel)] text-[#f97316] hover:bg-[#f97316] hover:text-white transition-all tiempo-global-btn" title="Segundo Tiempo">2</button>
                    <button onclick="cambiarTiempoGlobal('tercer-tiempo')" id="tiempo-global-3" class="px-2 xl:px-3 py-1 rounded text-[8px] xl:text-[9px] font-black uppercase tracking-tight border border-[#f97316]/30 bg-[var(--bg-panel)] text-[#f97316] hover:bg-[#f97316] hover:text-white transition-all tiempo-global-btn" title="Tercer Tiempo">3</button>
                </div>
            </div>
        </div>

        {{-- VISTA 1: NUEVA ORDEN (Ticket Actual) --}}
        <div id="vista-nueva-orden" class="flex-1 overflow-y-auto hide-scroll p-3 xl:p-4 flex flex-col relative bg-[var(--bg-base)]">
            <div class="grid grid-cols-[30px_1fr_45px] gap-2 text-[8px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] mb-2 pb-2 border-b border-[var(--border-color)]">
                <span class="text-center">Cant.</span>
                <span>Platillo</span>
                <span class="text-right">Monto</span>
            </div>
            <div id="listaTicket" class="flex flex-col gap-2"></div>
            
            <div id="estadoVacio" class="flex-1 flex flex-col items-center justify-center opacity-30 mt-6 transition-opacity duration-300">
                <i class="fas fa-utensils text-3xl mb-3 text-[var(--text-muted)]"></i>
                <p class="text-[9px] xl:text-[10px] font-bold text-[var(--text-muted)] text-center leading-relaxed">Ticket vacío.<br>Agrega platillos.</p>
            </div>
        </div>

        {{-- VISTA 2: ENVIADOS --}}
        <div id="vista-enviados" class="hidden flex-1 overflow-y-auto hide-scroll p-3 flex-col relative bg-[var(--bg-base)]">
            <div class="flex-1 flex flex-col items-center justify-center opacity-40 mt-6">
                <i class="fas fa-fire text-3xl mb-3 text-orange-500"></i>
                <p class="text-[9px] font-bold text-[var(--text-muted)] text-center leading-relaxed">No hay platillos<br>en cocina aún.</p>
            </div>
        </div>

        {{-- VISTA 3: COMANDA TOTAL --}}
        <div id="vista-comanda" class="hidden flex-1 overflow-y-auto hide-scroll p-3 flex-col relative bg-[var(--bg-base)]">
            <div class="flex-1 flex flex-col items-center justify-center opacity-40 mt-6">
                <i class="fas fa-receipt text-3xl mb-3 text-slate-400"></i>
                <p class="text-[10px] font-bold text-[var(--text-muted)] text-center leading-relaxed">Resumen total<br>de la mesa</p>
            </div>
        </div>

        {{-- FOOTER DE TOTALES --}}
        <div class="p-4 border-t border-[var(--border-color)] mt-auto relative z-20 bg-[var(--bg-panel)] flex-shrink-0">
            <div class="flex justify-between items-center mb-1">
                <span class="text-[9px] xl:text-[10px] font-bold text-[var(--text-muted)]">Subtotal:</span>
                <span class="text-[11px] xl:text-xs font-bold text-[var(--text-main)]" id="txtSubtotal">$0.00</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-[9px] xl:text-[10px] font-bold text-[var(--text-muted)]">IVA (16%):</span>
                <span class="text-[11px] xl:text-xs font-bold text-[var(--text-main)]" id="txtIva">$0.00</span>
            </div>
            <div class="flex justify-between items-end mb-4">
                <span class="text-xs xl:text-sm font-black tracking-widest uppercase text-[var(--text-main)]">Total:</span>
                <span class="text-2xl xl:text-3xl font-black text-[#3B82F6] tracking-tighter leading-none" id="txtTotal">$0.00</span>
            </div>

            <button id="btn-enviar" onclick="enviarACocina()" class="w-full h-11 xl:h-12 rounded-xl bg-[#3B82F6] text-white text-[10px] xl:text-[11px] font-black tracking-widest uppercase transition-all shadow-[0_5px_15px_-5px_rgba(59,130,246,0.6)] hover:bg-[#2563EB] active:scale-95 outline-none flex items-center justify-center gap-2">
                <i class="fas fa-paper-plane text-sm"></i>
                <span>Enviar a Cocina</span>
            </button>
            <p id="mensajeMesaDestino" class="mt-2 text-[10px] text-[var(--text-muted)]">Selecciona una mesa destino para enviar.</p>
        </div>
    </section>

    {{-- ========================================== --}}
    {{-- COLUMNA 3: CATÁLOGO DE PRODUCTOS (FLEX)    --}}
    {{-- ========================================== --}}
    <main class="flex-1 min-w-0 flex flex-col bg-[var(--bg-base)] relative overflow-hidden transition-all">
        
        {{-- Menú de Categorías (Scroll Horizontal) --}}
        <div class="px-4 xl:px-6 pt-4 xl:pt-6 pb-2 xl:pb-3 flex gap-2 overflow-x-auto hide-scroll relative z-10 border-b border-[var(--border-color)] bg-[var(--bg-panel)] flex-shrink-0" id="menuCategorias"></div>
        
        {{-- Grid de Productos (Cards responsivas) --}}
        <div class="flex-1 p-4 xl:p-6 overflow-y-auto hide-scroll grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-3 xl:gap-5 content-start relative z-10" onclick="deseleccionarTicket()" id="gridProductos"></div>

        {{-- BARRA DE MODIFICADORES INFERIOR --}}
        <div id="barraModificadores" class="hidden h-[60px] xl:h-[70px] bg-[var(--bg-panel)] border-t border-[var(--border-color)] flex items-center px-4 xl:px-6 relative z-10 transition-all flex-shrink-0 shadow-[0_-10px_30px_rgba(0,0,0,0.2)]">
            <span class="text-[8px] xl:text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] mr-3 whitespace-nowrap">Añadir a platillo:</span>
            <div id="contenedorBotonesModificadores" class="flex gap-2 overflow-x-auto hide-scroll flex-1 items-center"></div>
            <button onclick="deseleccionarTicket()" class="ml-3 w-8 h-8 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-rose-500 hover:border-rose-500/50 transition-all outline-none flex-shrink-0">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </main>

    {{-- ========================================== --}}
    {{-- MODALES DEL SISTEMA                        --}}
    {{-- ========================================== --}}

    {{-- MODAL NOTA --}}
    <div id="modalNota" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.25em] text-[#3B82F6] font-black">Nota</p>
                    <h2 class="text-xl font-black mt-1">Escribe la indicación</h2>
                </div>
                <button onclick="cerrarModal('modalNota')" class="text-[var(--text-muted)] hover:text-white outline-none text-xl"><i class="fas fa-times"></i></button>
            </div>
            <textarea id="notaTextarea" rows="4" readonly class="w-full rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] p-4 text-sm text-[var(--text-main)] outline-none resize-none" placeholder="Ej. Sin cebolla..."></textarea>
            
            <div id="tecladoNotas" class="grid grid-cols-10 gap-1.5 mt-4">
                <button type="button" onclick="insertarNotaCaracter('1')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">1</button>
                <button type="button" onclick="insertarNotaCaracter('2')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">2</button>
                <button type="button" onclick="insertarNotaCaracter('3')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">3</button>
                <button type="button" onclick="insertarNotaCaracter('4')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">4</button>
                <button type="button" onclick="insertarNotaCaracter('5')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">5</button>
                <button type="button" onclick="insertarNotaCaracter('6')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">6</button>
                <button type="button" onclick="insertarNotaCaracter('7')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">7</button>
                <button type="button" onclick="insertarNotaCaracter('8')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">8</button>
                <button type="button" onclick="insertarNotaCaracter('9')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">9</button>
                <button type="button" onclick="insertarNotaCaracter('0')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">0</button>
                <button type="button" onclick="insertarNotaCaracter('Q')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">Q</button>
                <button type="button" onclick="insertarNotaCaracter('W')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">W</button>
                <button type="button" onclick="insertarNotaCaracter('E')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">E</button>
                <button type="button" onclick="insertarNotaCaracter('R')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">R</button>
                <button type="button" onclick="insertarNotaCaracter('T')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">T</button>
                <button type="button" onclick="insertarNotaCaracter('Y')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">Y</button>
                <button type="button" onclick="insertarNotaCaracter('U')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">U</button>
                <button type="button" onclick="insertarNotaCaracter('I')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">I</button>
                <button type="button" onclick="insertarNotaCaracter('O')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">O</button>
                <button type="button" onclick="insertarNotaCaracter('P')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">P</button>
                <button type="button" onclick="insertarNotaCaracter('A')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">A</button>
                <button type="button" onclick="insertarNotaCaracter('S')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">S</button>
                <button type="button" onclick="insertarNotaCaracter('D')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">D</button>
                <button type="button" onclick="insertarNotaCaracter('F')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">F</button>
                <button type="button" onclick="insertarNotaCaracter('G')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">G</button>
                <button type="button" onclick="insertarNotaCaracter('H')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">H</button>
                <button type="button" onclick="insertarNotaCaracter('J')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">J</button>
                <button type="button" onclick="insertarNotaCaracter('K')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">K</button>
                <button type="button" onclick="insertarNotaCaracter('L')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">L</button>
                <button type="button" onclick="insertarNotaCaracter('Ñ')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">Ñ</button>
                <button type="button" onclick="insertarNotaCaracter('Z')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">Z</button>
                <button type="button" onclick="insertarNotaCaracter('X')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">X</button>
                <button type="button" onclick="insertarNotaCaracter('C')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">C</button>
                <button type="button" onclick="insertarNotaCaracter('V')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">V</button>
                <button type="button" onclick="insertarNotaCaracter('B')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">B</button>
                <button type="button" onclick="insertarNotaCaracter('N')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">N</button>
                <button type="button" onclick="insertarNotaCaracter('M')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">M</button>
                <button type="button" onclick="insertarNotaCaracter(',')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]"> , </button>
                <button type="button" onclick="insertarNotaCaracter('.')" class="px-1 py-2 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]"> . </button>
                <button type="button" onclick="insertarNotaEspacio()" class="col-span-4 px-1 py-2 rounded-lg bg-[#3B82F6] text-white font-black hover:bg-[#2563EB]">Espacio</button>
                <button type="button" onclick="borrarNotaCaracter()" class="col-span-3 px-1 py-2 rounded-lg bg-[#ef4444] text-white font-black hover:bg-[#dc2626]">Borrar</button>
                <button type="button" onclick="limpiarNota()" class="col-span-3 px-1 py-2 rounded-lg bg-[var(--text-muted)] text-white font-black hover:bg-[#52525b]">Limpiar</button>
            </div>
            <div class="mt-5 flex gap-3 justify-end">
                <button onclick="cerrarModal('modalNota')" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-bold text-[var(--text-muted)] hover:text-white transition-all">Cancelar</button>
                <button onclick="guardarNota()" class="px-5 py-2.5 rounded-xl bg-[#3B82F6] text-white text-xs font-black uppercase tracking-widest transition-all">Guardar</button>
            </div>
        </div>
    </div>

    {{-- MODAL DESCUENTO --}}
    <div id="modalDescuento" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.25em] text-[#ef4444] font-black">Descuento</p>
                    <h2 class="text-xl font-black mt-1">Aplicar porcentaje</h2>
                </div>
                <button onclick="cerrarModal('modalDescuento')" class="text-[var(--text-muted)] hover:text-white outline-none text-xl"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-3">
                <input id="descuentoInput" type="number" min="0" max="100" step="0.1" class="w-full rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] p-4 text-base font-bold text-[var(--text-main)] outline-none" placeholder="Ej. 10">
                <p class="text-[10px] text-[var(--text-muted)]">Aplica sobre el subtotal antes de impuestos.</p>
            </div>
            <div class="mt-5 flex gap-3 justify-end">
                <button onclick="cerrarModal('modalDescuento')" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-bold text-[var(--text-muted)] hover:text-white transition-all">Cancelar</button>
                <button onclick="guardarDescuento()" class="px-5 py-2.5 rounded-xl bg-[#ef4444] text-white text-xs font-black uppercase tracking-widest transition-all">Aplicar</button>
            </div>
        </div>
    </div>

    {{-- MODAL PERSONAS --}}
    <div id="modalPersonas" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.25em] text-[#3B82F6] font-black">Mesa</p>
                    <h2 class="text-xl font-black mt-1">Ajustar Personas</h2>
                </div>
                <button onclick="cerrarModal('modalPersonas')" class="text-[var(--text-muted)] hover:text-white outline-none text-xl"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-3">
                <input id="personasInput" type="number" min="1" class="w-full rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] p-4 text-base font-bold text-[var(--text-main)] outline-none" placeholder="Ej. 4">
            </div>
            <div class="mt-5 flex gap-3 justify-end">
                <button onclick="cerrarModal('modalPersonas')" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-bold text-[var(--text-muted)] hover:text-white transition-all">Cancelar</button>
                <button onclick="guardarPersonas()" class="px-5 py-2.5 rounded-xl bg-[#3B82F6] text-white text-xs font-black uppercase tracking-widest transition-all">Guardar</button>
            </div>
        </div>
    </div>

    {{-- MODAL GRAMAJE --}}
    <div id="modalGramaje" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.25em] text-[#f97316] font-black"><i class="fas fa-weight mr-1"></i>Gramaje</p>
                    <h2 id="modalGramajeTitulo" class="text-lg font-black mt-1 truncate max-w-[200px]">Peso del platillo</h2>
                </div>
                <button onclick="cerrarModal('modalGramaje')" class="text-[var(--text-muted)] hover:text-white outline-none text-xl"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <input id="gramajeInput" type="text" readonly inputmode="decimal" class="flex-1 rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] p-4 text-lg font-black text-[var(--text-main)] outline-none tracking-widest text-center" placeholder="000">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] px-1">g</span>
                </div>
                <div id="tecladoGramaje" class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="anadirNumeroGramaje('1')" class="px-2 py-3 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-lg text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">1</button>
                    <button type="button" onclick="anadirNumeroGramaje('2')" class="px-2 py-3 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-lg text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">2</button>
                    <button type="button" onclick="anadirNumeroGramaje('3')" class="px-2 py-3 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-lg text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">3</button>
                    <button type="button" onclick="anadirNumeroGramaje('4')" class="px-2 py-3 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-lg text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">4</button>
                    <button type="button" onclick="anadirNumeroGramaje('5')" class="px-2 py-3 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-lg text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">5</button>
                    <button type="button" onclick="anadirNumeroGramaje('6')" class="px-2 py-3 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-lg text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">6</button>
                    <button type="button" onclick="anadirNumeroGramaje('7')" class="px-2 py-3 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-lg text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">7</button>
                    <button type="button" onclick="anadirNumeroGramaje('8')" class="px-2 py-3 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-lg text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">8</button>
                    <button type="button" onclick="anadirNumeroGramaje('9')" class="px-2 py-3 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-lg text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">9</button>
                    <button type="button" onclick="anadirNumeroGramaje('.')" class="px-2 py-3 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-lg text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">.</button>
                    <button type="button" onclick="anadirNumeroGramaje('0')" class="px-2 py-3 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-lg text-[var(--text-main)] font-black hover:bg-[var(--bg-base)]">0</button>
                    <button type="button" onclick="borrarNumeroGramaje()" class="px-2 py-3 rounded-xl bg-[#ef4444] text-white font-black hover:bg-[#dc2626]">DEL</button>
                </div>
            </div>
            <div class="mt-5 flex gap-3 justify-end">
                <button onclick="cerrarModal('modalGramaje')" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-bold text-[var(--text-muted)] hover:text-white transition-all">Cancelar</button>
                <button onclick="guardarGramajeDelItem()" class="px-5 py-2.5 rounded-xl bg-[#f97316] text-white text-xs font-black uppercase tracking-widest transition-all">Guardar</button>
            </div>
        </div>
    </div>

    {{-- LÓGICA DE JAVASCRIPT --}}
    <script>
        function toggleTheme() {
            const body = document.body;
            body.classList.toggle('modo-crema');
            const esCrema = body.classList.contains('modo-crema');
            localStorage.setItem('tema-ollintem', esCrema ? 'crema' : 'negro');
            actualizarIconoTema(esCrema);
        }

        function actualizarIconoTema(esCrema) {
            const icon = document.getElementById('themeIcon');
            if (icon) {
                icon.className = esCrema 
                    ? 'fas fa-moon text-[10px] xl:text-xs text-[#3B82F6] group-hover:scale-110 transition-transform'
                    : 'fas fa-sun text-[10px] xl:text-xs text-amber-400 group-hover:scale-110 transition-transform';
            }
        }
        document.addEventListener('DOMContentLoaded', () => actualizarIconoTema(document.body.classList.contains('modo-crema')));

        function cambiarTab(pestana, btn) {
            const slider = document.getElementById('tab-slider');
            const btnOrden = document.getElementById('btn-tab-nueva-orden');
            const btnEnviados = document.getElementById('btn-tab-enviados');
            const btnComanda = document.getElementById('btn-tab-comanda');

            [btnOrden, btnEnviados, btnComanda].forEach(el => {
                if(el) {
                    el.classList.remove('text-white');
                    el.classList.add('text-[var(--text-muted)]');
                }
            });
            
            document.getElementById('vista-nueva-orden').classList.add('hidden');
            document.getElementById('vista-nueva-orden').classList.remove('flex');
            document.getElementById('vista-enviados').classList.add('hidden');
            document.getElementById('vista-enviados').classList.remove('flex');
            document.getElementById('vista-comanda').classList.add('hidden');
            document.getElementById('vista-comanda').classList.remove('flex');

            if(pestana === 'nueva-orden') {
                if(slider) slider.style.transform = 'translateX(0%)';
                if(btnOrden) { btnOrden.classList.add('text-white'); btnOrden.classList.remove('text-[var(--text-muted)]'); }
                
                document.getElementById('vista-nueva-orden').classList.remove('hidden');
                document.getElementById('vista-nueva-orden').classList.add('flex');
            } else if (pestana === 'enviados') {
                if(slider) slider.style.transform = 'translateX(100%)';
                if(btnEnviados) { btnEnviados.classList.add('text-white'); btnEnviados.classList.remove('text-[var(--text-muted)]'); }
                
                document.getElementById('vista-enviados').classList.remove('hidden');
                document.getElementById('vista-enviados').classList.add('flex');
            } else if (pestana === 'comanda') {
                if(slider) slider.style.transform = 'translateX(200%)';
                if(btnComanda) { btnComanda.classList.add('text-white'); btnComanda.classList.remove('text-[var(--text-muted)]'); }
                
                document.getElementById('vista-comanda').classList.remove('hidden');
                document.getElementById('vista-comanda').classList.add('flex');
            }
        }

        const categoriasDB = @json($categorias ?? []);
        const productosDB = @json($productos ?? []);

        function renderizarMenu() {
            const menuCat = document.getElementById('menuCategorias');
            const gridProd = document.getElementById('gridProductos');
            
            menuCat.innerHTML = `<button onclick="filtrarCategoria('Todos', this)" class="cat-btn px-5 py-2 rounded-full bg-[#3B82F6] text-white text-[10px] font-black uppercase tracking-widest shadow-[0_0_10px_rgba(59,130,246,0.3)] whitespace-nowrap outline-none transition-all">Todos</button>`;
            
            if(categoriasDB.length > 0) {
                categoriasDB.forEach(cat => {
                    menuCat.innerHTML += `<button onclick="filtrarCategoria('${cat.nombre}', this)" class="cat-btn px-5 py-2 rounded-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-muted)] text-[10px] font-bold uppercase tracking-widest hover:border-[#3B82F6]/50 hover:text-[var(--text-main)] transition-all whitespace-nowrap outline-none">${cat.nombre}</button>`;
                });
            }

            gridProd.innerHTML = '';
            
            if(productosDB.length > 0) {
                productosDB.forEach(prod => {
                    const catNombre = prod.categoria ? prod.categoria.nombre : 'Sin Categoría';
                    const precioNum = parseFloat(prod.precio) || 0;
                    const modsJSON = prod.modificadores ? JSON.stringify(prod.modificadores).replace(/'/g, "\\'") : '[]';

                    gridProd.innerHTML += `
                        <div data-categoria-item="${catNombre}" onclick='agregarAlTicket(${prod.id}, "${prod.nombre}", ${precioNum}, "${catNombre}", ${modsJSON}); event.stopPropagation();' class="producto-card group bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-2xl p-3 flex flex-col hover:border-[#3B82F6]/50 transition-all cursor-pointer h-[120px] md:h-[130px] xl:h-[150px]">
                            <div class="w-full flex-1 rounded-xl bg-[var(--bg-base)] mb-2 flex flex-col items-center justify-center relative">
                                <span class="absolute top-1 right-1 text-[6px] xl:text-[7px] font-black uppercase tracking-widest text-[var(--text-muted)] border border-[var(--border-color)] px-1.5 py-0.5 rounded">${catNombre}</span>
                                <i class="fas fa-utensils text-2xl xl:text-3xl text-[var(--border-color)] group-hover:text-[#3B82F6]/20 transition-colors"></i>
                            </div>
                            <h3 class="text-[10px] xl:text-xs font-bold text-[var(--text-main)] tracking-tight mb-0.5 truncate">${prod.nombre}</h3>
                            <span class="text-xs xl:text-sm font-black text-[#3B82F6]">$${precioNum.toFixed(2)}</span>
                        </div>
                    `;
                });
            } else {
                gridProd.innerHTML = `<div class="col-span-full text-center text-[var(--text-muted)] text-xs font-bold mt-10">No hay productos registrados en el menú aún.</div>`;
            }
        }
        
        document.addEventListener('DOMContentLoaded', renderizarMenu);

        function filtrarCategoria(nombreCat, btn) {
            document.querySelectorAll('.cat-btn').forEach(el => el.className = "cat-btn px-5 py-2 rounded-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-muted)] text-[10px] font-bold uppercase tracking-widest hover:border-[#3B82F6]/50 hover:text-[var(--text-main)] transition-all whitespace-nowrap outline-none");
            btn.className = "cat-btn px-5 py-2 rounded-full bg-[#3B82F6] text-white text-[10px] font-black uppercase tracking-widest shadow-[0_0_10px_rgba(59,130,246,0.3)] whitespace-nowrap outline-none transition-all";
            
            const todasLasCards = document.querySelectorAll('.producto-card');
            todasLasCards.forEach(card => {
                if (nombreCat === 'Todos' || card.getAttribute('data-categoria-item') === nombreCat) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        let ticketSubtotal = 0;
        let itemActivo = null; 
        let contadorItems = 0; 
        let tiempoGlobal = 'sin-tiempo'; 
        let gramajePendiente = null;

        const listaTicket = document.getElementById('listaTicket');
        const estadoVacio = document.getElementById('estadoVacio');
        const barraModificadores = document.getElementById('barraModificadores');
        const contenedorBotonesModificadores = document.getElementById('contenedorBotonesModificadores');
        
        function agregarAlTicket(id, nombre, precio, categoria, arrayModificadores = []) {
            agregarItemAlTicketConGramaje(id, nombre, precio, categoria, gramajePendiente, arrayModificadores);
            if (gramajePendiente) {
                gramajePendiente = null;
                document.getElementById('btn-gramaje').classList.remove('border-[#f97316]', 'bg-[#f97316]/10');
                document.getElementById('indicador-gramaje-pendiente').classList.add('hidden');
            }
        }
        
        function ajustarGramaje() {
            const modal = document.getElementById('modalGramaje');
            const input = document.getElementById('gramajeInput');
            const titulo = document.getElementById('modalGramajeTitulo');

            if (itemActivo) {
                const gramajeActual = itemActivo.dataset.gramaje;
                input.value = gramajeActual === 'sin-gramaje' ? '' : gramajeActual;
                titulo.innerText = `${itemActivo.querySelector('.nombre-platillo').innerText}`;
            } else {
                input.value = gramajePendiente || '';
                titulo.innerText = 'Peso próximo platillo';
            }
            
            modal.classList.remove('hidden');
            input.focus();
        }
        
        function guardarGramajeDelItem() {
            const val = document.getElementById('gramajeInput').value.trim() || null;
            
            if (itemActivo) {
                const gramajeKey = val ? val.toString() : 'sin-gramaje';
                itemActivo.dataset.gramaje = gramajeKey;
                const contenedorGramaje = itemActivo.querySelector('.gramaje-etiqueta');
                if (val) {
                    contenedorGramaje.innerHTML = `<span class="text-[7px] bg-[#f97316]/10 border border-[#f97316]/30 text-[#f97316] px-1 py-0.5 rounded font-black uppercase tracking-widest"><i class="fas fa-weight-scale mr-1"></i>${val}g</span>`;
                } else {
                    contenedorGramaje.innerHTML = '';
                }
            } else {
                gramajePendiente = val;
                const btnGramaje = document.getElementById('btn-gramaje');
                const indicador = document.getElementById('indicador-gramaje-pendiente');
                
                if(val) {
                    btnGramaje.classList.add('border-[#f97316]', 'bg-[#f97316]/10');
                    indicador.innerText = val + 'g';
                    indicador.classList.remove('hidden');
                } else {
                    btnGramaje.classList.remove('border-[#f97316]', 'bg-[#f97316]/10');
                    indicador.classList.add('hidden');
                }
            }
            cerrarModal('modalGramaje');
        }

        function anadirNumeroGramaje(numero) {
            const input = document.getElementById('gramajeInput');
            input.value = (input.value === '0' ? '' : input.value) + numero;
        }

        function borrarNumeroGramaje() {
            const input = document.getElementById('gramajeInput');
            input.value = input.value.slice(0, -1);
        }
        
        function agregarItemAlTicketConGramaje(id, nombre, precio, categoria, gramaje = null, arrayModificadores = []) {
            cambiarTab('nueva-orden', document.getElementById('btn-tab-nueva-orden'));
            estadoVacio.classList.add('hidden');

            const modsString = JSON.stringify(arrayModificadores).replace(/'/g, "&#39;").replace(/"/g, "&quot;");
            const precioUnitario = parseFloat(precio);
            const gramajeKey = gramaje ? gramaje.toString() : 'sin-gramaje';

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
                return;
            }

            contadorItems++;
            const itemId = 'ticket-item-' + contadorItems;
            
            const etiquetaGramaje = gramaje ? `<span class="text-[7px] bg-[#f97316]/10 border border-[#f97316]/30 text-[#f97316] px-1 py-0.5 rounded font-black uppercase tracking-widest"><i class="fas fa-weight-scale mr-0.5"></i>${gramaje}g</span>` : '';
            
            let etiquetaTiempo = '';
            if (tiempoGlobal === 'primer-tiempo') etiquetaTiempo = '<span class="text-[7px] bg-[#f97316]/10 border border-[#f97316]/30 text-[#f97316] px-1 py-0.5 rounded font-black uppercase tracking-widest"><i class="fas fa-clock mr-1"></i>1er T.</span>';
            else if (tiempoGlobal === 'segundo-tiempo') etiquetaTiempo = '<span class="text-[7px] bg-[#f97316]/10 border border-[#f97316]/30 text-[#f97316] px-1 py-0.5 rounded font-black uppercase tracking-widest"><i class="fas fa-clock mr-1"></i>2do T.</span>';
            else if (tiempoGlobal === 'tercer-tiempo') etiquetaTiempo = '<span class="text-[7px] bg-[#f97316]/10 border border-[#f97316]/30 text-[#f97316] px-1 py-0.5 rounded font-black uppercase tracking-widest"><i class="fas fa-clock mr-1"></i>3er T.</span>';

            const itemHTML = `
                <div id="${itemId}" data-producto-id="${id}" data-cantidad="1" data-precio="${precioUnitario}" data-modificadores="${modsString}" data-gramaje="${gramajeKey}" data-tiempo="${tiempoGlobal}" class="ticket-item animate-item bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-xl p-2.5 flex flex-col cursor-pointer transition-all" onclick="seleccionarItem('${itemId}')">
                    <div class="flex items-start justify-between gap-1 mb-1">
                        <div class="flex-1">
                            <span class="text-[9px] xl:text-[10px] font-bold text-[var(--text-main)] leading-tight nombre-platillo">${nombre}</span>
                            <div class="flex flex-wrap gap-1 mt-1 empty:hidden">
                                <div class="gramaje-etiqueta empty:hidden">${etiquetaGramaje}</div>
                                ${etiquetaTiempo}
                            </div>
                            <div class="modificadores-lista flex flex-wrap gap-1 mt-1 empty:hidden"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 mt-1.5 border-t border-[var(--border-color)] pt-1.5">
                        <div class="flex items-center gap-1.5">
                            <button onclick="decrementarCantidad('${itemId}'); event.stopPropagation();" class="w-5 h-5 rounded bg-rose-500/10 text-rose-500 text-[10px] font-black flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all">−</button>
                            <span class="cantidad-platillo w-5 h-5 rounded bg-[#3B82F6]/10 text-[#3B82F6] text-[9px] font-black flex items-center justify-center">1</span>
                            <button onclick="incrementarCantidad('${itemId}'); event.stopPropagation();" class="w-5 h-5 rounded bg-emerald-500/10 text-emerald-500 text-[10px] font-black flex items-center justify-center hover:bg-emerald-500 hover:text-white transition-all">+</button>
                        </div>
                        <div class="flex-1 text-right flex flex-col">
                            <span class="text-[10px] xl:text-xs font-black text-[var(--text-main)] precio-platillo">$${precioUnitario.toFixed(2)}</span>
                            <button onclick="eliminarItemFila(this); event.stopPropagation();" class="hidden btn-control-eliminar text-[7px] text-rose-500 font-bold uppercase tracking-widest hover:underline mt-0.5">Quitar</button>
                        </div>
                    </div>
                </div>
            `;

            listaTicket.insertAdjacentHTML('beforeend', itemHTML);
            ticketSubtotal += precioUnitario;
            actualizarTotales();

            seleccionarItem(itemId);
            listaTicket.parentElement.scrollTop = listaTicket.parentElement.scrollHeight;
        }

        function seleccionarItem(id) {
            deseleccionarTicket(); 
            itemActivo = document.getElementById(id);
            if(itemActivo) {
                itemActivo.classList.add('border-[#3B82F6]', 'shadow-[0_0_15px_rgba(59,130,246,0.15)]');
                itemActivo.querySelector('.btn-control-eliminar').classList.remove('hidden');
                
                const modsString = itemActivo.getAttribute('data-modificadores');
                const modificadoresParaPintar = JSON.parse(modsString || '[]');
                
                contenedorBotonesModificadores.innerHTML = '';
                
                if(modificadoresParaPintar.length > 0) {
                    modificadoresParaPintar.forEach(mod => {
                        const nombreMod = mod.nombre || mod.descripcion || mod; 
                        const btnHTML = `<button onclick="agregarModificadorFijo('${nombreMod}')" class="px-5 py-2 rounded-xl border border-[#3B82F6]/30 bg-[var(--bg-panel)] text-[9px] xl:text-[10px] font-bold text-[#3B82F6] hover:bg-[#3B82F6] hover:text-white transition-all whitespace-nowrap outline-none active:scale-95 shadow-sm">${nombreMod}</button>`;
                        contenedorBotonesModificadores.insertAdjacentHTML('beforeend', btnHTML);
                    });
                    barraModificadores.classList.remove('hidden');
                } else {
                    barraModificadores.classList.add('hidden');
                }
            }
        }

        function deseleccionarTicket() {
            document.querySelectorAll('.ticket-item').forEach(el => {
                el.classList.remove('border-[#3B82F6]', 'shadow-[0_0_15px_rgba(59,130,246,0.15)]');
                if(el.querySelector('.btn-control-eliminar')) {
                    el.querySelector('.btn-control-eliminar').classList.add('hidden');
                }
            });
            itemActivo = null;
            barraModificadores.classList.add('hidden');
        }

        function agregarModificadorFijo(texto) {
            if (itemActivo) {
                const contenedorList = itemActivo.querySelector('.modificadores-lista');
                const pillHTML = `<span class="text-[7px] bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-muted)] px-1 py-0.5 rounded shadow-sm">✓ ${texto}</span>`;
                contenedorList.insertAdjacentHTML('beforeend', pillHTML);
            }
        }

        function incrementarCantidad(id) {
            const item = document.getElementById(id);
            if (!item) return;
            const cantidadSpan = item.querySelector('.cantidad-platillo');
            const precioSpan = item.querySelector('.precio-platillo');
            const precioUnitario = parseFloat(item.dataset.precio) || 0;
            let cantidad = parseInt(cantidadSpan.innerText, 10) || 1;
            
            cantidad++;
            cantidadSpan.innerText = cantidad;
            item.dataset.cantidad = cantidad;
            precioSpan.innerText = '$' + (precioUnitario * cantidad).toFixed(2);
            ticketSubtotal += precioUnitario;
            actualizarTotales();
        }
        
        function decrementarCantidad(id) {
            const item = document.getElementById(id);
            if (!item) return;
            const cantidadSpan = item.querySelector('.cantidad-platillo');
            const precioSpan = item.querySelector('.precio-platillo');
            const precioUnitario = parseFloat(item.dataset.precio) || 0;
            let cantidad = parseInt(cantidadSpan.innerText, 10) || 1;
            
            if (cantidad <= 1) {
                eliminarItemFila(item.querySelector('.btn-control-eliminar'));
                return;
            }
            
            cantidad--;
            cantidadSpan.innerText = cantidad;
            item.dataset.cantidad = cantidad;
            precioSpan.innerText = '$' + (precioUnitario * cantidad).toFixed(2);
            ticketSubtotal -= precioUnitario;
            actualizarTotales();
        }
        
        function cambiarTiempoGlobal(tiempo) {
            tiempoGlobal = tiempo;
            const tiemposMap = ['sin-tiempo', 'primer-tiempo', 'segundo-tiempo', 'tercer-tiempo'];
            const tiemposIds = ['tiempo-global-sin', 'tiempo-global-1', 'tiempo-global-2', 'tiempo-global-3'];
            
            tiemposIds.forEach((id, index) => {
                const btn = document.getElementById(id);
                if (tiemposMap[index] === tiempo) {
                    if (index === 0) {
                        btn.className = 'px-2 xl:px-3 py-1 rounded text-[8px] xl:text-[9px] font-black uppercase tracking-tight bg-[#3B82F6] text-white shadow-sm tiempo-global-btn transition-all';
                    } else {
                        btn.className = 'px-2 xl:px-3 py-1 rounded text-[8px] xl:text-[9px] font-black uppercase tracking-tight bg-[#f97316] text-white shadow-sm tiempo-global-btn transition-all';
                    }
                } else {
                    btn.className = 'px-2 xl:px-3 py-1 rounded text-[8px] xl:text-[9px] font-black uppercase tracking-tight border border-[#f97316]/30 bg-[var(--bg-panel)] text-[#f97316] hover:bg-[#f97316] hover:text-white transition-all tiempo-global-btn';
                }
            });
        }
        
        function eliminarItemFila(btn) {
            const fila = btn.closest('.ticket-item');
            const cantidad = parseInt(fila.dataset.cantidad, 10) || 1;
            const precioUnitario = parseFloat(fila.dataset.precio) || 0;
            const totalFila = cantidad * precioUnitario;
            fila.remove();
            ticketSubtotal -= totalFila;
            actualizarTotales();
            deseleccionarTicket();
            
            if(listaTicket.children.length === 0) {
                estadoVacio.classList.remove('hidden');
            }
        }

        function actualizarTotales() {
            const subtotalConDescuento = Math.max(0, ticketSubtotal - (ticketSubtotal * (descuentoPorcentaje / 100)));
            const iva = subtotalConDescuento * 0.16;
            const total = subtotalConDescuento + iva;
            document.getElementById('txtSubtotal').innerText = '$' + subtotalConDescuento.toFixed(2);
            document.getElementById('txtIva').innerText = '$' + iva.toFixed(2);
            document.getElementById('txtTotal').innerText = '$' + total.toFixed(2);
        }

        function limpiarTicket() {
            listaTicket.innerHTML = '';
            estadoVacio.classList.remove('hidden');
            ticketSubtotal = 0;
            descuentoPorcentaje = 0;
            notaGeneral = '';
            actualizarTotales();
            deseleccionarTicket();
        }

        function ajustarPersonas() {
            document.getElementById('personasInput').value = numeroPersonas;
            document.getElementById('modalPersonas').classList.remove('hidden');
            document.getElementById('personasInput').focus();
        }

        function guardarPersonas() {
            const valor = parseInt(document.getElementById('personasInput').value, 10);
            if (isNaN(valor) || valor <= 0) {
                mostrarError('Ingresa cantidad de personas.');
                return;
            }
            numeroPersonas = valor;
            document.getElementById('txtPersonas').innerText = numeroPersonas;
            document.getElementById('badgePersonas').innerText = 'P: ' + numeroPersonas;
            cerrarModal('modalPersonas');
        }

        function agregarNota() {
            if (!itemActivo) {
                mostrarError('Selecciona platillo para nota.');
                return;
            }
            document.getElementById('notaTextarea').value = '';
            document.getElementById('modalNota').classList.remove('hidden');
        }

        function aplicarDescuento() {
            document.getElementById('descuentoInput').value = descuentoPorcentaje;
            document.getElementById('modalDescuento').classList.remove('hidden');
            document.getElementById('descuentoInput').focus();
        }

        function mostrarPromociones() { window.location.href = '/admin/promociones'; }

        function eliminarItemSeleccionado() {
            if (!itemActivo) {
                mostrarError('Selecciona platillo a eliminar.');
                return;
            }
            const cantidad = parseInt(itemActivo.dataset.cantidad, 10) || 1;
            const precioUnitario = parseFloat(itemActivo.dataset.precio) || 0;
            const totalFila = cantidad * precioUnitario;
            itemActivo.remove();
            ticketSubtotal -= totalFila;
            actualizarTotales();
            deseleccionarTicket();
            if (listaTicket.children.length === 0) estadoVacio.classList.remove('hidden');
        }

        let numeroPersonas = {{ $mesa->capacidad ?? 4 }};
        let descuentoPorcentaje = 0;
        let notaGeneral = '';

        document.addEventListener('DOMContentLoaded', function() {
            const badge = document.getElementById('badgePersonas');
            if (badge) badge.innerText = 'P: ' + numeroPersonas;
            actualizarMensajeDestino();
        });

        function cerrarModal(id) { document.getElementById(id).classList.add('hidden'); }

        function guardarNota() {
            const nota = document.getElementById('notaTextarea').value.trim();
            if (!itemActivo) { cerrarModal('modalNota'); return; }
            if (nota.length === 0) { mostrarError('Nota vacía.'); return; }
            const notaPill = `<span class="text-[7px] bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-muted)] px-1.5 py-0.5 rounded shadow-sm">✎ ${nota}</span>`;
            itemActivo.querySelector('.modificadores-lista').insertAdjacentHTML('beforeend', notaPill);
            itemActivo.dataset.nota = nota;
            notaGeneral = nota;
            cerrarModal('modalNota');
        }

        function mostrarToast(message, type = 'info', duration = 3800) {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `toast-panel ${type}`;
            toast.innerHTML = `<div class="toast-icon">${type === 'success' ? '<i class="fas fa-check"></i>' : type === 'error' ? '<i class="fas fa-exclamation-triangle"></i>' : '<i class="fas fa-info"></i>'}</div><div><strong>${type === 'success' ? 'Éxito' : type === 'error' ? 'Error' : 'Aviso'}</strong><span>${message}</span></div>`;
            container.appendChild(toast);
            requestAnimationFrame(() => toast.classList.add('show'));
            setTimeout(() => { toast.classList.remove('show'); toast.addEventListener('transitionend', () => toast.remove(), { once: true }); }, duration);
        }

        function mostrarError(mensaje) { mostrarToast(mensaje, 'error'); }
        function mostrarExito(mensaje) { mostrarToast(mensaje, 'success'); }
        function insertarNotaCaracter(caracter) { const textarea = document.getElementById('notaTextarea'); textarea.value = textarea.value + caracter; textarea.focus(); }
        function insertarNotaEspacio() { const textarea = document.getElementById('notaTextarea'); textarea.value = textarea.value + ' '; textarea.focus(); }
        function borrarNotaCaracter() { const textarea = document.getElementById('notaTextarea'); textarea.value = textarea.value.slice(0, -1); textarea.focus(); }
        function limpiarNota() { const textarea = document.getElementById('notaTextarea'); textarea.value = ''; textarea.focus(); }

        function guardarDescuento() {
            const valor = parseFloat(document.getElementById('descuentoInput').value);
            if (isNaN(valor) || valor < 0 || valor > 100) { mostrarError('Porcentaje inválido.'); return; }
            descuentoPorcentaje = valor;
            actualizarTotales();
            cerrarModal('modalDescuento');
        }

        function obtenerSubtotalConDescuento() { return Math.max(0, ticketSubtotal - (ticketSubtotal * (descuentoPorcentaje / 100))); }
        function imprimirPrecuenta() { mostrarExito("Imprimiendo pre-cuenta..."); }
        function cambiarMesa() { llamarCapitan(); }
        function marcharTiempos() { mostrarExito("¡Marchando platillos!"); }
        let mesaDestinoSeleccionada = null;
        let mesaDestinoSeleccionadaNumero = null;
        let capitanAutorizado = false;

        function actualizarMensajeDestino() {
            const mensaje = document.getElementById('mensajeMesaDestino');
            const btnEnviar = document.getElementById('btn-enviar');
            if (capitanAutorizado && mesaDestinoSeleccionada && mesaDestinoSeleccionadaNumero) {
                mensaje.innerText = `Destino seleccionado: Mesa ${mesaDestinoSeleccionadaNumero}`;
                btnEnviar.innerHTML = `<i class="fas fa-paper-plane text-sm"></i> Enviar a Mesa ${mesaDestinoSeleccionadaNumero}`;
            } else {
                mensaje.innerText = 'Enviar a cocina o selecciona una mesa destino.';
                btnEnviar.innerHTML = `<i class="fas fa-paper-plane text-sm"></i> <span>Enviar a Cocina</span>`;
            }
        }


        async function llamarCapitan() {
            const nip = prompt("NIP Capitán:");
            if (!nip) return;

            try {
                const res = await fetch('/mesero/capitan/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ nip: nip.trim() })
                });

                const data = await res.json().catch(() => null);
                if (!res.ok) throw new Error(data?.message || 'Error al verificar NIP');

                if (data.success) {
                    capitanAutorizado = true;
                    mostrarExito('Capitán autorizado.');
                    // render mesas
                    const container = document.getElementById('capitanMesasContainer');
                    container.innerHTML = '';
                    if (Array.isArray(data.mesas) && data.mesas.length > 0) {
                        data.mesas.forEach(m => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.dataset.mesaId = m.id;
                            btn.className = 'w-full text-left rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] px-4 py-4 hover:border-[#3B82F6]/50 transition-all flex items-center justify-between gap-4';
                            btn.innerHTML = `<div><p class="text-[10px] uppercase tracking-[0.2em] text-[var(--text-muted)] font-bold">Mesa abierta</p><h3 class="text-lg font-black text-[var(--text-main)]">${m.numero}</h3></div><span class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-400"><i class="fas fa-circle text-[6px]"></i> ${m.estado ? m.estado.charAt(0).toUpperCase() + m.estado.slice(1) : ''}</span>`;
                            btn.addEventListener('click', () => seleccionarMesaDestino(m.id, m.numero));
                            container.appendChild(btn);
                        });
                        if (mesaDestinoSeleccionada) {
                            const selectedBtn = container.querySelector(`button[data-mesa-id="${mesaDestinoSeleccionada}"]`);
                            if (selectedBtn) {
                                selectedBtn.classList.remove('border-[var(--border-color)]', 'bg-[var(--bg-base)]');
                                selectedBtn.classList.add('border-blue-500/50', 'bg-blue-500/10');
                            }
                        }
                    } else {
                        container.innerHTML = `<div class="rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] p-6 text-center text-[var(--text-muted)]"><p class="font-bold text-sm mb-2">No hay mesas abiertas disponibles.</p><p class="text-[12px]">Solo el capitán puede enviar un pedido a otra mesa abierta.</p></div>`;
                    }

                    document.getElementById('modalCapitan').classList.remove('hidden');
                }
            } catch (err) {
                mostrarError(err.message || 'Error al verificar NIP');
            }
        }

        function seleccionarMesaDestino(mesaId, mesaNumero) {
            mesaDestinoSeleccionada = mesaId;
            mesaDestinoSeleccionadaNumero = mesaNumero;
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

        function enviarACocina() {
            const itemsHTML = document.querySelectorAll('.ticket-item');
            if(itemsHTML.length === 0) { mostrarError("¡Agrega platillos!"); return; }

            const platillosData = [];
            itemsHTML.forEach(item => {
                const nombre = item.querySelector('.nombre-platillo').innerText;
                const precioUnitario = parseFloat(item.dataset.precio);
                const cantidad = parseInt(item.dataset.cantidad, 10);
                const productoId = parseInt(item.dataset.productoId, 10);
                const gramaje = item.dataset.gramaje === 'sin-gramaje' ? null : item.dataset.gramaje;
                const tiempo = item.dataset.tiempo; 
                const modsElementos = item.querySelectorAll('.modificadores-lista span');
                const mods = [];
                modsElementos.forEach(m => mods.push(m.innerText.replace('✓ ', '').replace('✎ ', '')));

                platillosData.push({ id: productoId, nombre: nombre, cantidad: cantidad, precio: precioUnitario, notas: mods.join(', '), modificadores: mods, gramaje: gramaje, tiempo: tiempo });
            });

            const btn = document.getElementById('btn-enviar');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            const mesaId = capitanAutorizado && mesaDestinoSeleccionada ? mesaDestinoSeleccionada : {{ $mesa->id ?? 1 }};

            fetch('/mesero/comanda/enviar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ mesa_id: mesaId, platillos: platillosData, total: obtenerSubtotalConDescuento() + (obtenerSubtotalConDescuento() * 0.16), personas: numeroPersonas, descuento_porcentaje: descuentoPorcentaje, nota_general: notaGeneral })
            })
            .then(async res => {
                const data = await res.json().catch(() => null);
                if (!res.ok) throw new Error(data?.message || 'Error al enviar.');
                return data;
            })
            .then(data => {
                if (data.success) {
                    mostrarExito("¡Enviado a cocina!");
                    setTimeout(() => window.location.href = '/mesero/dashboard', 1000);
                } else { throw new Error(data.message || 'Error.'); }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarError(error.message || "Error al enviar.");
                btn.innerHTML = '<i class="fas fa-paper-plane text-sm"></i> <span>Enviar a Cocina</span>';
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>