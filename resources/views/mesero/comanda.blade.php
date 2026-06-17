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
            --border-highlight: rgba(255, 255, 255, 0.05);
            --text-main: #FFFFFF;
            --text-muted: #71717A;
            --accent: #3B82F6;
            --input-bg: #141418;
        }

        body.modo-crema {
            --bg-base: #F4F4F5; 
            --bg-panel: #FFFFFF;
            --border-color: rgba(0, 0, 0, 0.08);
            --border-highlight: rgba(0, 0, 0, 0.04);
            --text-main: #09090B;
            --text-muted: #A1A1AA;
            --input-bg: #E4E4E7;
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
        
        .toast-wrapper { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.75rem; align-items: flex-end; pointer-events: none; }
        .toast-panel { min-width: 18rem; max-width: 28rem; pointer-events: auto; background: rgba(11, 15, 25, 0.92); color: #F8FAFC; border: 1px solid rgba(148, 163, 184, 0.15); border-left-width: 4px; border-radius: 1.25rem; backdrop-filter: blur(20px); box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35); padding: 1rem 1.25rem; opacity: 0; transform: translateX(28px); transition: opacity 0.3s ease, transform 0.3s ease; font-size: 0.95rem; line-height: 1.5; display: grid; grid-template-columns: auto 1fr; gap: 0.85rem; align-items: center; }
        .toast-panel.show { opacity: 1; transform: translateX(0); }
        .toast-panel.success { border-color: rgba(16, 185, 129, 0.25); }
        .toast-panel.error { border-color: rgba(239, 68, 68, 0.25); }
        .toast-panel.info { border-color: rgba(59, 130, 246, 0.25); }
        .toast-panel .toast-icon { width: 2.25rem; height: 2.25rem; display: grid; place-items: center; border-radius: 1rem; background: rgba(255, 255, 255, 0.08); color: inherit; }
        .toast-panel.success .toast-icon { background: rgba(16, 185, 129, 0.18); color: #4ade80; }
        .toast-panel.error .toast-icon { background: rgba(239, 68, 68, 0.18); color: #f87171; }
        .toast-panel.info .toast-icon { background: rgba(59, 130, 246, 0.18); color: #60a5fa; }
        .toast-panel strong { display: block; color: inherit; font-weight: 700; }
        .toast-panel span { color: rgba(248, 250, 252, 0.84); font-size: 0.88rem; }
        
        body.modo-crema .toast-panel { background: rgba(255, 255, 255, 0.96); color: #1f2937; border-color: #e5e7eb; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08); }
        body.modo-crema .toast-panel.success .toast-icon { background: #dcfce7; color: #166534; }
        body.modo-crema .toast-panel.error .toast-icon { background: #fee2e2; color: #991b1b; }
        body.modo-crema .toast-panel.info .toast-icon { background: #dbeafe; color: #1d4ed8; }
        body.modo-crema .toast-panel span { color: #52525b; }
        
        aside, section, main, div, button { transition: background-color 0.3s ease, border-color 0.3s ease; }
    </style>
</head>
<body class="h-screen w-full flex selection:bg-[#3B82F6]/30">

    <script>if (localStorage.getItem('tema-ollintem') === 'crema') document.body.classList.add('modo-crema');</script>
    <div id="toastContainer" class="toast-wrapper" aria-live="polite" aria-atomic="true"></div>

    {{-- ========================================== --}}
    {{-- COLUMNA 1: ACCIONES RÁPIDAS --}}
    {{-- ========================================== --}}
    <aside class="w-[170px] md:w-[190px] xl:w-[220px] 2xl:w-[260px] flex-shrink-0 h-full flex flex-col bg-[var(--bg-base)] border-r border-[var(--border-color)] p-3 lg:p-4 z-20 transition-all">
        
        <div class="flex items-center gap-2 mb-4">
            <button onclick="window.location.href='{{ route('mesero.dashboard') ?? '#' }}'" class="flex-1 h-10 xl:h-12 rounded-[14px] bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-main)] font-black text-[8px] xl:text-[9px] uppercase tracking-widest flex items-center justify-center gap-2 hover:border-[var(--text-muted)] transition-all shadow-sm active:scale-95 outline-none">
                <i class="fas fa-chevron-left text-[var(--text-muted)]"></i> Mesas
            </button>
            <button onclick="toggleTheme()" class="w-10 h-10 xl:w-12 xl:h-12 shrink-0 rounded-[14px] bg-[var(--input-bg)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--text-muted)] transition-all shadow-sm active:scale-95 outline-none group">
                <i id="themeIcon" class="fas fa-sun text-xs group-hover:rotate-90 transition-transform duration-500"></i>
            </button>
        </div>

        <div class="flex items-center justify-between px-2 mb-5">
            <div class="flex flex-col">
                <span class="text-[7px] xl:text-[8px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] mb-0.5">Mesa Activa</span>
                <h3 class="text-xl xl:text-2xl font-black tracking-tighter text-[var(--text-main)] leading-none">Mesa {{ $mesa->numero ?? '12M' }}</h3>
            </div>
            <div class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500 border-2 border-[var(--bg-base)]"></span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 xl:gap-3 flex-1 overflow-y-auto hide-scroll pb-2">
            <button onclick="ajustarPersonas()" class="relative w-full aspect-square flex flex-col items-center justify-center rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#3B82F6]/50 hover:shadow-[0_8px_20px_-6px_rgba(59,130,246,0.2)] transition-all duration-300 active:scale-95 group overflow-hidden">
                <div class="absolute top-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity modo-crema:via-black/10"></div>
                <div class="w-8 h-8 xl:w-10 xl:h-10 rounded-full bg-[#3B82F6]/10 border border-[#3B82F6]/20 flex items-center justify-center mb-2 group-hover:bg-[#3B82F6] transition-colors duration-300">
                    <i class="fas fa-user-friends text-[#3B82F6] group-hover:text-white text-xs xl:text-sm transition-colors"></i>
                </div>
                <span class="text-[7px] xl:text-[8px] font-bold uppercase tracking-[0.15em] text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Personas</span>
                <span id="txtPersonas" class="text-[9px] xl:text-[10px] font-black text-[var(--text-main)] mt-0.5">{{ $mesa->capacidad ?? 1 }}</span>
            </button>
            
            <button onclick="imprimirPrecuenta()" class="relative w-full aspect-square flex flex-col items-center justify-center rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#94A3B8]/50 hover:shadow-[0_8px_20px_-6px_rgba(148,163,184,0.2)] transition-all duration-300 active:scale-95 group overflow-hidden">
                <div class="absolute top-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity modo-crema:via-black/10"></div>
                <div class="w-8 h-8 xl:w-10 xl:h-10 rounded-full bg-[#94A3B8]/10 border border-[#94A3B8]/20 flex items-center justify-center mb-2 group-hover:bg-[#94A3B8] transition-colors duration-300">
                    <i class="fas fa-receipt text-[#94A3B8] group-hover:text-white text-xs xl:text-sm transition-colors"></i>
                </div>
                <span class="text-[7px] xl:text-[8px] font-bold uppercase tracking-[0.15em] text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors leading-tight text-center">Pre<br>Cuenta</span>
            </button>

            <button onclick="agregarNota()" class="relative w-full aspect-square flex flex-col items-center justify-center rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#8B5CF6]/50 hover:shadow-[0_8px_20px_-6px_rgba(139,92,246,0.2)] transition-all duration-300 active:scale-95 group overflow-hidden">
                <div class="absolute top-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity modo-crema:via-black/10"></div>
                <div class="w-8 h-8 xl:w-10 xl:h-10 rounded-full bg-[#8B5CF6]/10 border border-[#8B5CF6]/20 flex items-center justify-center mb-2 group-hover:bg-[#8B5CF6] transition-colors duration-300">
                    <i class="fas fa-comment-alt text-[#8B5CF6] group-hover:text-white text-xs xl:text-sm transition-colors"></i>
                </div>
                <span class="text-[7px] xl:text-[8px] font-bold uppercase tracking-[0.15em] text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Nota</span>
            </button>
            
            <button onclick="aplicarDescuento()" class="relative w-full aspect-square flex flex-col items-center justify-center rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#EC4899]/50 hover:shadow-[0_8px_20px_-6px_rgba(236,72,153,0.2)] transition-all duration-300 active:scale-95 group overflow-hidden">
                <div class="absolute top-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity modo-crema:via-black/10"></div>
                <div class="w-8 h-8 xl:w-10 xl:h-10 rounded-full bg-[#EC4899]/10 border border-[#EC4899]/20 flex items-center justify-center mb-2 group-hover:bg-[#EC4899] transition-colors duration-300">
                    <i class="fas fa-percent text-[#EC4899] group-hover:text-white text-xs xl:text-sm transition-colors"></i>
                </div>
                <span class="text-[7px] xl:text-[8px] font-bold uppercase tracking-[0.15em] text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Desc.</span>
            </button>
            
            <button id="btn-gramaje" onclick="ajustarGramaje()" class="relative w-full aspect-square flex flex-col items-center justify-center rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#F97316]/50 hover:shadow-[0_8px_20px_-6px_rgba(249,115,22,0.2)] transition-all duration-300 active:scale-95 group overflow-hidden">
                <div class="absolute top-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity modo-crema:via-black/10"></div>
                <div class="w-8 h-8 xl:w-10 xl:h-10 rounded-full bg-[#F97316]/10 border border-[#F97316]/20 flex items-center justify-center mb-2 group-hover:bg-[#F97316] transition-colors duration-300">
                    <i class="fas fa-weight-scale text-[#F97316] group-hover:text-white text-xs xl:text-sm transition-colors"></i>
                </div>
                <span class="text-[7px] xl:text-[8px] font-bold uppercase tracking-[0.15em] text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Gramaje</span>
                <span id="indicador-gramaje-pendiente" class="hidden absolute top-2 right-2 bg-[#f97316] text-white text-[7px] font-black px-1.5 py-0.5 rounded shadow-md"></span>
            </button>
            
            <button onclick="llamarCapitan()" class="relative w-full aspect-square flex flex-col items-center justify-center rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#F59E0B]/50 hover:shadow-[0_8px_20px_-6px_rgba(245,158,11,0.2)] transition-all duration-300 active:scale-95 group overflow-hidden">
                <div class="absolute top-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity modo-crema:via-black/10"></div>
                <div class="w-8 h-8 xl:w-10 xl:h-10 rounded-full bg-[#F59E0B]/10 border border-[#F59E0B]/20 flex items-center justify-center mb-2 group-hover:bg-[#F59E0B] transition-colors duration-300">
                    <i class="fas fa-exchange-alt text-[#F59E0B] group-hover:text-white text-xs xl:text-sm transition-colors"></i>
                </div>
                <span class="text-[7px] xl:text-[8px] font-bold uppercase tracking-[0.15em] text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Traspaso</span>
            </button>
            
            <button onclick="mostrarPromociones()" class="relative w-full aspect-square flex flex-col items-center justify-center rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#0EA5E9]/50 hover:shadow-[0_8px_20px_-6px_rgba(14,165,233,0.2)] transition-all duration-300 active:scale-95 group overflow-hidden">
                <div class="absolute top-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity modo-crema:via-black/10"></div>
                <div class="w-8 h-8 xl:w-10 xl:h-10 rounded-full bg-[#0EA5E9]/10 border border-[#0EA5E9]/20 flex items-center justify-center mb-2 group-hover:bg-[#0EA5E9] transition-colors duration-300">
                    <i class="fas fa-tag text-[#0EA5E9] group-hover:text-white text-xs xl:text-sm transition-colors"></i>
                </div>
                <span class="text-[7px] xl:text-[8px] font-bold uppercase tracking-[0.15em] text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Promos</span>
            </button>
            
            <button onclick="marcharTiempos()" class="relative w-full aspect-square flex flex-col items-center justify-center rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#EF4444]/50 hover:shadow-[0_8px_20px_-6px_rgba(239,68,68,0.2)] transition-all duration-300 active:scale-95 group overflow-hidden">
                <div class="absolute top-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity modo-crema:via-black/10"></div>
                <div class="w-8 h-8 xl:w-10 xl:h-10 rounded-full bg-[#EF4444]/10 border border-[#EF4444]/20 flex items-center justify-center mb-2 group-hover:bg-[#EF4444] transition-colors duration-300">
                    <i class="fas fa-fire text-[#EF4444] group-hover:text-white text-xs xl:text-sm transition-colors"></i>
                </div>
                <span class="text-[7px] xl:text-[8px] font-bold uppercase tracking-[0.15em] text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Marchar</span>
            </button>
            
            @if($esCapitan ?? false)
                <button onclick="llamarCapitan()" class="col-span-2 relative h-12 lg:h-14 xl:h-16 flex items-center justify-center gap-3 rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#6366F1]/50 hover:shadow-[0_8px_20px_-6px_rgba(99,102,241,0.2)] transition-all duration-300 active:scale-95 group overflow-hidden">
                    <div class="absolute top-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity modo-crema:via-black/10"></div>
                    <div class="w-8 h-8 rounded-full bg-[#6366F1]/10 border border-[#6366F1]/20 flex items-center justify-center group-hover:bg-[#6366F1] transition-colors duration-300">
                        <i class="fas fa-user-shield text-[#6366F1] group-hover:text-white text-xs transition-colors"></i>
                    </div>
                    <span class="text-[8px] xl:text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors">Capitán</span>
                </button>
            @endif
        </div>

        <button onclick="limpiarTicket()" class="mt-4 w-full h-12 xl:h-14 rounded-[18px] border border-rose-500/20 bg-rose-500/5 text-rose-500 hover:bg-rose-500 hover:text-white transition-all duration-300 active:scale-95 outline-none flex items-center justify-center gap-2 flex-shrink-0 group shadow-sm">
            <i class="fas fa-trash-alt text-[10px] group-hover:animate-pulse"></i>
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
            <div id="capitanMesasContainer" class="grid gap-3 max-h-[420px] overflow-y-auto hide-scroll pb-2"></div>
            <div class="mt-5 text-right">
                <button onclick="cerrarModal('modalCapitan')" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-bold text-[var(--text-muted)] hover:text-white transition-all">Cancelar</button>
            </div>
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- COLUMNA 2: TICKET / COMANDA (CENTRAL)      --}}
    {{-- ========================================== --}}
    <section class="w-[280px] md:w-[320px] xl:w-[360px] 2xl:w-[400px] flex-shrink-0 h-full flex flex-col bg-[var(--bg-panel)] border-r border-[var(--border-color)] relative z-10 shadow-2xl transition-all">
        
        <div class="p-3 xl:p-4 border-b border-[var(--border-color)] flex flex-col gap-2.5">
            <div class="relative flex w-full bg-[var(--bg-base)] p-1 rounded-xl border border-[var(--border-color)] shadow-inner">
                <div class="absolute inset-y-1 left-1 right-1 pointer-events-none">
                    <div id="tab-slider" class="h-full w-1/3 rounded-lg bg-[#3B82F6] shadow-[0_4px_15px_-3px_rgba(59,130,246,0.5)] transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"></div>
                </div>
                <button onclick="cambiarTab('nueva-orden', this)" id="btn-tab-nueva-orden" class="tab-btn relative z-10 flex-1 py-1.5 xl:py-2 text-[8px] xl:text-[9px] font-black uppercase tracking-widest text-white transition-colors duration-300 outline-none">Orden</button>
                <button onclick="cambiarTab('enviados', this)" id="btn-tab-enviados" class="tab-btn relative z-10 flex-1 py-1.5 xl:py-2 text-[8px] xl:text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors duration-300 outline-none">Enviado</button>
                <button onclick="cambiarTab('comanda', this)" id="btn-tab-comanda" class="tab-btn relative z-10 flex-1 py-1.5 xl:py-2 text-[8px] xl:text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors duration-300 outline-none">Total</button>
            </div>

            <div class="flex items-center justify-between bg-[var(--bg-base)] p-1.5 xl:p-2 rounded-xl border border-[var(--border-color)]">
                <span class="text-[8px] font-black uppercase tracking-[0.2em] text-[#f97316] ml-2">Tiempos:</span>
                <div class="flex gap-1 pr-1">
                    <button onclick="cambiarTiempoGlobal('sin-tiempo')" id="tiempo-global-sin" class="px-2 xl:px-3 py-1 rounded text-[8px] xl:text-[9px] font-black uppercase tracking-tight bg-[#3B82F6] text-white shadow-sm tiempo-global-btn transition-all outline-none" title="Sin Tiempo">S</button>
                    <button onclick="cambiarTiempoGlobal('primer-tiempo')" id="tiempo-global-1" class="px-2 xl:px-3 py-1 rounded text-[8px] xl:text-[9px] font-black uppercase tracking-tight border border-[#f97316]/30 bg-[var(--bg-panel)] text-[#f97316] hover:bg-[#f97316] hover:text-white transition-all tiempo-global-btn outline-none" title="Primer Tiempo">1</button>
                    <button onclick="cambiarTiempoGlobal('segundo-tiempo')" id="tiempo-global-2" class="px-2 xl:px-3 py-1 rounded text-[8px] xl:text-[9px] font-black uppercase tracking-tight border border-[#f97316]/30 bg-[var(--bg-panel)] text-[#f97316] hover:bg-[#f97316] hover:text-white transition-all tiempo-global-btn outline-none" title="Segundo Tiempo">2</button>
                    <button onclick="cambiarTiempoGlobal('tercer-tiempo')" id="tiempo-global-3" class="px-2 xl:px-3 py-1 rounded text-[8px] xl:text-[9px] font-black uppercase tracking-tight border border-[#f97316]/30 bg-[var(--bg-panel)] text-[#f97316] hover:bg-[#f97316] hover:text-white transition-all tiempo-global-btn outline-none" title="Tercer Tiempo">3</button>
                </div>
            </div>
        </div>

        {{-- VISTA 1: NUEVA ORDEN --}}
        <div id="vista-nueva-orden" class="flex-1 overflow-y-auto hide-scroll p-3 xl:p-4 flex flex-col relative bg-[var(--bg-base)]">
            <div class="grid grid-cols-[30px_1fr_45px] gap-2 text-[8px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] mb-2 pb-2 border-b border-[var(--border-color)]">
                <span class="text-center">Cant.</span>
                <span>Platillo</span>
                <span class="text-right">Monto</span>
            </div>
            
            <div id="listaTicket" class="flex flex-col gap-3 pb-4"></div>
            
            <div id="estadoVacio" class="flex-1 flex flex-col items-center justify-center opacity-40 mt-6 transition-opacity duration-300">
                <div class="w-16 h-16 rounded-full bg-[var(--input-bg)] border border-[var(--border-color)] flex items-center justify-center mb-3 shadow-inner">
                    <i class="fas fa-utensils text-2xl text-[var(--text-muted)]"></i>
                </div>
                <p class="text-[9px] xl:text-[10px] font-bold text-[var(--text-muted)] text-center leading-relaxed">Ticket vacío.<br>Agrega platillos.</p>
            </div>
        </div>

        {{-- VISTA 2: ENVIADOS --}}
        <div id="vista-enviados" class="hidden flex-1 overflow-y-auto hide-scroll p-3 xl:p-4 flex-col relative bg-[var(--bg-base)]">
            @if(isset($platillosEnviados) && count($platillosEnviados) > 0)
                <div class="flex flex-col gap-2">
                    <div class="text-[8px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] mb-1 pb-1 border-b border-[var(--border-color)]">Estatus Actual</div>
                    @foreach($platillosEnviados as $item)
                        <div class="bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-xl p-3 flex justify-between items-center shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-md bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-main)] text-[10px] font-black flex items-center justify-center">{{ $item->cantidad ?? 1 }}</span>
                                <div>
                                    <span class="text-[11px] font-bold text-[var(--text-main)] block leading-tight">{{ $item->nombre ?? 'Platillo' }}</span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                @if(($item->estado ?? 'enviado') == 'preparando')
                                    <span class="text-[8px] font-black uppercase tracking-widest text-orange-500 bg-orange-500/10 px-2 py-1 rounded-md border border-orange-500/20"><i class="fas fa-fire mr-1"></i>En cocina</span>
                                @elseif(($item->estado ?? 'enviado') == 'listo')
                                    <span class="text-[8px] font-black uppercase tracking-widest text-emerald-500 bg-emerald-500/10 px-2 py-1 rounded-md border border-emerald-500/20"><i class="fas fa-check mr-1"></i>Listo</span>
                                @else
                                    <span class="text-[8px] font-black uppercase tracking-widest text-blue-500 bg-blue-500/10 px-2 py-1 rounded-md border border-blue-500/20"><i class="fas fa-clock mr-1"></i>Enviado</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center opacity-40 mt-6">
                    <div class="w-16 h-16 rounded-full bg-orange-500/5 border border-orange-500/10 flex items-center justify-center mb-3">
                        <i class="fas fa-fire text-2xl text-orange-500/60"></i>
                    </div>
                    <p class="text-[9px] xl:text-[10px] font-bold text-[var(--text-muted)] text-center leading-relaxed">No hay platillos<br>en cocina aún.</p>
                </div>
            @endif
        </div>

        {{-- VISTA 3: COMANDA TOTAL --}}
        <div id="vista-comanda" class="hidden flex-1 overflow-y-auto hide-scroll p-3 xl:p-4 flex-col relative bg-[var(--bg-base)]">
            <div id="items-db-total" class="flex flex-col gap-2">
                @if(isset($platillosEnviados) && count($platillosEnviados) > 0)
                    <div class="text-[8px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] mb-1 pb-1 border-b border-[var(--border-color)]">Consumo Ya en Mesa</div>
                    @foreach($platillosEnviados as $item)
                        <div class="bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-xl p-2.5 flex justify-between items-center shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="w-5 h-5 rounded bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-main)] text-[9px] font-black flex items-center justify-center">{{ $item->cantidad ?? 1 }}</span>
                                <span class="text-[10px] font-bold text-[var(--text-main)]">{{ $item->nombre ?? 'Platillo' }}</span>
                            </div>
                            <span class="text-[10px] font-black text-[var(--text-main)]">${{ number_format(($item->precio ?? 0) * ($item->cantidad ?? 1), 2) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between items-center mt-2 pt-2 border-t border-[var(--border-color)]">
                        <span class="text-[9px] font-bold text-[var(--text-muted)] uppercase tracking-widest">Subtotal ya en mesa:</span>
                        <span class="text-[11px] font-black text-[var(--text-main)]">
                            ${{ number_format(collect($platillosEnviados)->sum(function($i) { return ($i->precio ?? 0) * ($i->cantidad ?? 1); }), 2) }}
                        </span>
                    </div>
                @endif
            </div>

            <div id="lista-comanda-total" class="flex flex-col gap-2 mt-4"></div>
            
            <div id="estadoVacioComanda" class="flex-1 flex-col items-center justify-center opacity-40 mt-6 {{ (isset($platillosEnviados) && count($platillosEnviados) > 0) ? 'hidden' : 'flex' }}">
                <div class="w-16 h-16 rounded-full bg-[var(--input-bg)] border border-[var(--border-color)] flex items-center justify-center mb-3 shadow-inner">
                    <i class="fas fa-receipt text-2xl text-[var(--text-muted)]"></i>
                </div>
                <p class="text-[9px] xl:text-[10px] font-bold text-[var(--text-muted)] text-center leading-relaxed">Aún no hay<br>cuenta en esta mesa</p>
            </div>
        </div>

        {{-- FOOTER DE TOTALES (ELEGANT DESIGN) --}}
        <div class="p-4 border-t border-[var(--border-color)] mt-auto relative z-20 bg-[var(--bg-panel)] flex-shrink-0">
            <div class="flex justify-between items-center mb-1">
                <span class="text-[9px] xl:text-[10px] font-bold text-[var(--text-muted)]">Subtotal Nuevos:</span>
                <span class="text-[11px] xl:text-xs font-bold text-[var(--text-main)]" id="txtSubtotal">$0.00</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-[9px] xl:text-[10px] font-bold text-[var(--text-muted)]">IVA (16%):</span>
                <span class="text-[11px] xl:text-xs font-bold text-[var(--text-main)]" id="txtIva">$0.00</span>
            </div>
            <div class="flex justify-between items-end mb-4 pt-4 border-t border-[var(--border-color)]">
                <div class="flex flex-col">
                    <span class="text-[9px] font-black tracking-widest uppercase text-[var(--text-muted)] mb-1">Total a pagar</span>
                    <span class="text-[10px] font-bold text-[var(--text-muted)]">Incluye todo el consumo</span>
                </div>
                <span class="text-3xl font-black text-emerald-400 tracking-tighter leading-none drop-shadow-[0_0_12px_rgba(52,211,153,0.15)]" id="txtTotal">$0.00</span>
            </div>

            <button id="btn-enviar" onclick="enviarACocina()" class="w-full h-12 xl:h-14 rounded-xl bg-gradient-to-r from-[#3B82F6] to-[#2563EB] text-white text-[10px] xl:text-[11px] font-black tracking-widest uppercase transition-all shadow-[0_8px_20px_-5px_rgba(59,130,246,0.4)] hover:shadow-[0_8px_25px_-5px_rgba(59,130,246,0.6)] hover:-translate-y-0.5 active:scale-95 outline-none flex items-center justify-center gap-2 border border-[#60A5FA]/30">
                <i class="fas fa-paper-plane text-sm"></i>
                <span>Enviar a Cocina</span>
            </button>
            <p id="mensajeMesaDestino" class="mt-2.5 text-[10px] font-bold text-[var(--text-muted)] text-center">Enviando a Mesa {{ $mesa->numero ?? '12M' }}</p>
        </div>
    </section>

    {{-- ========================================== --}}
    {{-- COLUMNA 3: CATÁLOGO DE PRODUCTOS (FLEX)    --}}
    {{-- ========================================== --}}
    <main class="flex-1 min-w-0 flex flex-col bg-[var(--bg-base)] relative overflow-hidden transition-all">
        <div class="px-4 xl:px-6 pt-4 xl:pt-6 pb-2 xl:pb-3 flex gap-2 overflow-x-auto hide-scroll relative z-10 border-b border-[var(--border-color)] bg-[var(--bg-panel)] flex-shrink-0" id="menuCategorias"></div>
        <div class="flex-1 p-4 xl:p-6 overflow-y-auto hide-scroll grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-4 xl:gap-6 content-start relative z-10" onclick="deseleccionarTicket()" id="gridProductos"></div>
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

    <div id="modalNota" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div><p class="text-[10px] uppercase tracking-[0.25em] text-[#3B82F6] font-black">Nota</p><h2 class="text-xl font-black mt-1">Escribe la indicación</h2></div>
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

    <div id="modalDescuento" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div><p class="text-[10px] uppercase tracking-[0.25em] text-[#ef4444] font-black">Descuento</p><h2 class="text-xl font-black mt-1">Aplicar porcentaje</h2></div>
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

    <div id="modalPersonas" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div><p class="text-[10px] uppercase tracking-[0.25em] text-[#3B82F6] font-black">Mesa</p><h2 class="text-xl font-black mt-1">Ajustar Personas</h2></div>
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

    <div id="modalGramaje" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div><p class="text-[10px] uppercase tracking-[0.25em] text-[#f97316] font-black"><i class="fas fa-weight mr-1"></i>Gramaje</p><h2 id="modalGramajeTitulo" class="text-lg font-black mt-1 truncate max-w-[200px]">Peso del platillo</h2></div>
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
                mensajeVacio.classList.remove('hidden');
                mensajeVacio.classList.add('flex');
            } else {
                mensajeVacio.classList.add('hidden');
                mensajeVacio.classList.remove('flex');

                if (itemsEnTicket.length > 0) {
                    contenedorNuevos.innerHTML += `<div class="text-[8px] font-black uppercase tracking-[0.2em] text-[#3B82F6] mb-1 pb-1 border-b border-[var(--border-color)]">Por Enviar (Nuevos)</div>`;
                    itemsEnTicket.forEach(item => {
                        const cant = item.querySelector('.cantidad-platillo').innerText;
                        const nombre = item.querySelector('.nombre-platillo').innerText;
                        const precio = item.querySelector('.precio-platillo').innerText;
                        contenedorNuevos.innerHTML += `
                            <div class="bg-[var(--input-bg)] border border-[#3B82F6]/30 rounded-xl p-2.5 flex justify-between items-center shadow-sm mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded bg-[#3B82F6] text-white text-[9px] font-black flex items-center justify-center">${cant}</span>
                                    <span class="text-[10px] font-bold text-[var(--text-main)]">${nombre}</span>
                                </div>
                                <span class="text-[10px] font-black text-[#3B82F6]">${precio}</span>
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
            const totalPagar = subtotalGeneral + ivaGeneral;
            
            document.getElementById('txtTotal').innerText = '$' + totalPagar.toFixed(2);
        }

        const categoriasDB = @json($categorias ?? []);
        const productosDB = @json($productos ?? []);

        function renderizarMenu() {
            const menuCat = document.getElementById('menuCategorias');
            const gridProd = document.getElementById('gridProductos');
            
            menuCat.innerHTML = `<button onclick="filtrarCategoria('Todos', this)" class="cat-btn px-6 py-2.5 rounded-full bg-gradient-to-b from-[#3B82F6] to-[#2563EB] text-white text-[10px] font-black uppercase tracking-widest shadow-[0_8px_15px_-3px_rgba(59,130,246,0.5)] border border-[#60A5FA]/30 whitespace-nowrap outline-none transition-all active:scale-95">Todos</button>`;
            
            if(categoriasDB.length > 0) {
                categoriasDB.forEach(cat => {
                    menuCat.innerHTML += `<button onclick="filtrarCategoria('${cat.nombre}', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] text-[10px] font-bold uppercase tracking-widest hover:border-[#3B82F6]/40 hover:bg-[#3B82F6]/5 hover:text-[#3B82F6] transition-all whitespace-nowrap outline-none shadow-sm active:scale-95">${cat.nombre}</button>`;
                });
            }

            gridProd.innerHTML = '';
            
            if(productosDB.length > 0) {
                productosDB.forEach(prod => {
                    const catNombre = prod.categoria ? prod.categoria.nombre : 'Sin Categoría';
                    const precioNum = parseFloat(prod.precio) || 0;
                    const modsJSON = prod.modificadores ? JSON.stringify(prod.modificadores).replace(/'/g, "\\'") : '[]';
                    const letraInicial = prod.nombre.charAt(0).toUpperCase();

                    gridProd.innerHTML += `
                        <div data-categoria-item="${catNombre}" onclick='agregarAlTicket(${prod.id}, "${prod.nombre}", ${precioNum}, "${catNombre}", ${modsJSON}); event.stopPropagation();' 
                             class="producto-card group relative bg-[var(--bg-panel)] rounded-[20px] flex flex-col cursor-pointer transition-all duration-400 h-[140px] xl:h-[160px] 
                                    border border-[var(--border-color)] shadow-sm 
                                    hover:border-[#3B82F6]/40 hover:shadow-[0_12px_30px_-10px_rgba(59,130,246,0.35)] hover:-translate-y-1.5 overflow-hidden">
                            
                            <div class="h-[55%] w-full bg-[var(--input-bg)] relative flex items-center justify-center border-b border-[var(--border-color)] overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-br from-white/[0.03] to-transparent pointer-events-none"></div>
                                <div class="absolute top-2.5 right-2.5 bg-[var(--bg-base)]/50 backdrop-blur-md px-2 py-0.5 rounded shadow-sm border border-[var(--border-color)] z-10 transition-colors group-hover:border-[#3B82F6]/30">
                                    <span class="text-[7px] font-black uppercase tracking-widest text-[var(--text-muted)] group-hover:text-[#3B82F6]/80">${catNombre}</span>
                                </div>
                                <div class="relative z-10 transform group-hover:scale-[1.15] transition-transform duration-500">
                                    <span class="text-[40px] leading-none font-black text-transparent bg-clip-text bg-gradient-to-br from-[var(--text-muted)] to-[var(--border-color)] group-hover:from-[#3B82F6] group-hover:to-[#60A5FA] transition-all duration-300 select-none opacity-30 group-hover:opacity-100">
                                        ${letraInicial}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="p-3.5 flex flex-col flex-1 justify-between bg-gradient-to-b from-[var(--bg-panel)] to-[var(--bg-base)]">
                                <h3 class="text-[11px] xl:text-xs font-medium text-[var(--text-main)] tracking-tight leading-snug line-clamp-2 transition-colors">${prod.nombre}</h3>
                                <div class="flex items-center justify-between mt-auto pt-1">
                                    <span class="text-xs xl:text-sm font-black text-[var(--text-main)] tracking-tighter">$${precioNum.toFixed(2)}</span>
                                    <div class="w-6 h-6 rounded-full bg-[#3B82F6]/10 border border-[#3B82F6]/20 text-[#3B82F6] flex items-center justify-center opacity-0 transform translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300">
                                        <i class="fas fa-plus text-[10px]"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                gridProd.innerHTML = `
                    <div class="col-span-full flex flex-col items-center justify-center text-[var(--text-muted)] mt-20 opacity-50">
                        <div class="w-20 h-20 rounded-full border border-dashed border-[var(--border-color)] flex items-center justify-center mb-4 bg-[var(--input-bg)]">
                            <i class="fas fa-box-open text-3xl"></i>
                        </div>
                        <p class="text-xs font-black tracking-widest uppercase">Menú Vacío</p>
                    </div>`;
            }
        }
        
        document.addEventListener('DOMContentLoaded', renderizarMenu);

        function filtrarCategoria(nombreCat, btn) {
            document.querySelectorAll('.cat-btn').forEach(el => el.className = "cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] text-[10px] font-bold uppercase tracking-widest hover:border-[#3B82F6]/40 hover:bg-[#3B82F6]/5 hover:text-[#3B82F6] transition-all whitespace-nowrap outline-none shadow-sm active:scale-95");
            btn.className = "cat-btn px-6 py-2.5 rounded-full bg-gradient-to-b from-[#3B82F6] to-[#2563EB] text-white text-[10px] font-black uppercase tracking-widest shadow-[0_8px_15px_-3px_rgba(59,130,246,0.5)] border border-[#60A5FA]/30 whitespace-nowrap outline-none transition-all active:scale-95";
            
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
        let numeroPersonas = {{ $mesa->capacidad ?? 4 }};
        let descuentoPorcentaje = 0;
        let notaGeneral = '';

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
                
                const etiquetaGramaje = gramajePendiente ? `<span class="text-[7px] bg-[#f97316]/10 border border-[#f97316]/30 text-[#f97316] px-1 py-0.5 rounded font-black uppercase tracking-widest"><i class="fas fa-weight-scale mr-0.5"></i>${gramajePendiente}g</span>` : '';
                
                let etiquetaTiempo = '';
                if (tiempoGlobal === 'primer-tiempo') etiquetaTiempo = '<span class="text-[7px] bg-[#f97316]/10 border border-[#f97316]/30 text-[#f97316] px-1 py-0.5 rounded font-black uppercase tracking-widest"><i class="fas fa-clock mr-1"></i>1er T.</span>';
                else if (tiempoGlobal === 'segundo-tiempo') etiquetaTiempo = '<span class="text-[7px] bg-[#f97316]/10 border border-[#f97316]/30 text-[#f97316] px-1 py-0.5 rounded font-black uppercase tracking-widest"><i class="fas fa-clock mr-1"></i>2do T.</span>';
                else if (tiempoGlobal === 'tercer-tiempo') etiquetaTiempo = '<span class="text-[7px] bg-[#f97316]/10 border border-[#f97316]/30 text-[#f97316] px-1 py-0.5 rounded font-black uppercase tracking-widest"><i class="fas fa-clock mr-1"></i>3er T.</span>';

                const itemHTML = `
                    <div id="${itemId}" data-producto-id="${id}" data-cantidad="1" data-precio="${precioUnitario}" data-modificadores="${modsString}" data-gramaje="${gramajeKey}" data-tiempo="${tiempoGlobal}" class="ticket-item animate-item relative w-full rounded-[24px] bg-gradient-to-b from-[var(--bg-panel)] to-[var(--bg-base)] border border-white/[0.05] p-4 flex flex-col gap-3 cursor-pointer transition-all duration-300 outline-none transform hover:-translate-y-0.5" onclick="seleccionarItem('${itemId}')">
                        
                        <div class="flex justify-between items-start gap-3 relative z-10">
                            <div class="flex-1">
                                <h3 class="text-[14px] font-black text-transparent bg-clip-text bg-gradient-to-r from-white to-white/70 tracking-tight leading-tight nombre-platillo">${nombre}</h3>
                                <div class="flex flex-wrap gap-1.5 mt-1.5 empty:hidden">
                                    <div class="gramaje-etiqueta empty:hidden">${etiquetaGramaje}</div>
                                    ${etiquetaTiempo}
                                </div>
                            </div>
                            <span class="text-[15px] font-black text-emerald-400 tracking-tighter drop-shadow-[0_0_8px_rgba(52,211,153,0.2)] precio-platillo">$${precioUnitario.toFixed(2)}</span>
                        </div>

                        <div class="modificadores-lista w-full rounded-[12px] bg-[#F59E0B]/[0.05] border border-[#F59E0B]/[0.15] px-3.5 py-2.5 flex flex-wrap items-center gap-x-2 gap-y-1.5 empty:hidden relative z-10"></div>

                        <div class="flex items-center justify-between pt-3 mt-1 relative z-10">
                            <div class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-white/[0.05] to-transparent"></div>
                            
                            <div class="flex items-center bg-[var(--bg-base)] rounded-[14px] p-1 border border-white/[0.02] shadow-[inset_0_2px_5px_rgba(0,0,0,0.4)]" onclick="event.stopPropagation();">
                                <button type="button" onclick="decrementarCantidad('${itemId}')" class="w-8 h-8 rounded-[10px] flex items-center justify-center text-[var(--text-muted)] hover:bg-white/[0.05] hover:text-[var(--text-main)] transition-colors outline-none">
                                    <i class="fas fa-minus text-[11px]"></i>
                                </button>
                                <span class="cantidad-platillo w-8 text-center text-[13px] font-black text-[var(--text-main)]">1</span>
                                <button type="button" onclick="incrementarCantidad('${itemId}')" class="w-8 h-8 rounded-[10px] flex items-center justify-center text-[var(--text-muted)] hover:bg-[#3B82F6]/20 hover:text-[#3B82F6] transition-colors outline-none">
                                    <i class="fas fa-plus text-[11px]"></i>
                                </button>
                            </div>

                            <button type="button" onclick="eliminarItemFila(this); event.stopPropagation();" class="hidden btn-control-eliminar w-10 h-10 rounded-[14px] bg-rose-500/10 border border-rose-500/20 text-rose-500 flex items-center justify-center hover:bg-rose-500 hover:text-white hover:border-transparent hover:shadow-[0_4px_15px_rgba(244,63,94,0.4)] transition-all duration-300 outline-none">
                                <i class="fas fa-trash-alt text-[12px]"></i>
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

        function seleccionarItem(id) {
            deseleccionarTicket(); 
            itemActivo = document.getElementById(id);
            if(itemActivo) {
                itemActivo.classList.add('bg-[#1E293B]/40', 'border-[#3B82F6]/40', 'shadow-[0_8px_25px_-5px_rgba(59,130,246,0.15)]');
                itemActivo.classList.remove('bg-[var(--bg-panel)]', 'border-white/[0.05]');
                itemActivo.querySelector('.btn-control-eliminar').classList.remove('hidden');
                itemActivo.querySelector('.btn-control-eliminar').classList.add('flex');
                
                const modsString = itemActivo.getAttribute('data-modificadores');
                const modificadoresParaPintar = JSON.parse(modsString || '[]');
                
                contenedorBotonesModificadores.innerHTML = '';
                
                if(modificadoresParaPintar.length > 0) {
                    modificadoresParaPintar.forEach(mod => {
                        const nombreMod = mod.nombre || mod.descripcion || mod; 
                        const btnHTML = `<button onclick="agregarModificadorFijo('${nombreMod}')" class="px-5 py-2 rounded-xl border border-[#3B82F6]/30 bg-[var(--bg-base)] text-[9px] xl:text-[10px] font-bold text-[#3B82F6] hover:bg-[#3B82F6] hover:text-white transition-all whitespace-nowrap outline-none active:scale-95 shadow-sm">${nombreMod}</button>`;
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
                el.classList.remove('bg-[#1E293B]/40', 'border-[#3B82F6]/40', 'shadow-[0_8px_25px_-5px_rgba(59,130,246,0.15)]');
                el.classList.add('bg-[var(--bg-panel)]', 'border-white/[0.05]');
                if(el.querySelector('.btn-control-eliminar')) {
                    el.querySelector('.btn-control-eliminar').classList.add('hidden');
                    el.querySelector('.btn-control-eliminar').classList.remove('flex');
                }
            });
            itemActivo = null;
            barraModificadores.classList.add('hidden');
        }

        function agregarModificadorFijo(texto) {
            if (itemActivo) {
                const contenedorList = itemActivo.querySelector('.modificadores-lista');
                const tieneHijos = contenedorList.children.length > 0;
                const separador = tieneHijos ? `<span class="opacity-30 text-[#F59E0B] text-[10px] mx-1">•</span>` : `<i class="fas fa-pen text-[#F59E0B] opacity-70 text-[9px] mr-1 mt-[1px]"></i>`;
                const notaTexto = `<span class="text-[11px] font-black uppercase tracking-wider text-[#F59E0B] leading-none inline-flex items-center">${separador} <span class="nota-texto-real ml-0.5">${texto}</span></span>`;
                contenedorList.insertAdjacentHTML('beforeend', notaTexto);
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
                    btn.className = `px-3 xl:px-4 py-1.5 rounded-lg text-[9px] xl:text-[10px] font-black uppercase tracking-tight text-white shadow-sm tiempo-global-btn transition-all outline-none ${index === 0 ? 'bg-[#3B82F6]' : 'bg-[#f97316]'}`;
                } else {
                    btn.className = 'px-3 xl:px-4 py-1.5 rounded-lg text-[9px] xl:text-[10px] font-black uppercase tracking-tight border border-[#f97316]/30 bg-[var(--bg-panel)] text-[#f97316] hover:bg-[#f97316] hover:text-white transition-all tiempo-global-btn outline-none';
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
                document.getElementById('estadoVacio').classList.remove('hidden');
            }
        }

        function actualizarTotales() {
            const subtotalConDescuento = Math.max(0, ticketSubtotal - (ticketSubtotal * (descuentoPorcentaje / 100)));
            const iva = subtotalConDescuento * 0.16;
            const total = subtotalConDescuento + iva;
            document.getElementById('txtSubtotal').innerText = '$' + subtotalConDescuento.toFixed(2);
            document.getElementById('txtIva').innerText = '$' + iva.toFixed(2);
            
            actualizarVistaTotal();
        }

        function limpiarTicket() {
            listaTicket.innerHTML = '';
            document.getElementById('estadoVacio').classList.remove('hidden');
            ticketSubtotal = 0;
            descuentoPorcentaje = 0;
            notaGeneral = '';
            actualizarTotales();
            deseleccionarTicket();
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

        function mostrarPromociones() { window.location.href = '{{ route("admin.promociones.index") ?? '#' }}'; }

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
            if (listaTicket.children.length === 0) document.getElementById('estadoVacio').classList.remove('hidden');
        }

        function cerrarModal(id) { document.getElementById(id).classList.add('hidden'); }

        function guardarNota() {
            const nota = document.getElementById('notaTextarea').value.trim();
            if (!itemActivo) { cerrarModal('modalNota'); return; }
            if (nota.length === 0) { mostrarError('Nota vacía.'); return; }
            
            const contenedorList = itemActivo.querySelector('.modificadores-lista');
            const tieneHijos = contenedorList.children.length > 0;
            const separador = tieneHijos ? `<span class="opacity-30 text-[#F59E0B] text-[10px] mx-1">•</span>` : `<i class="fas fa-pen text-[#F59E0B] opacity-70 text-[9px] mr-1 mt-[1px]"></i>`;
            const notaTexto = `<span class="text-[11px] font-black uppercase tracking-wider text-[#F59E0B] leading-none inline-flex items-center">${separador} <span class="nota-texto-real ml-0.5">${nota}</span></span>`;
            
            contenedorList.insertAdjacentHTML('beforeend', notaTexto);
            itemActivo.dataset.nota = nota;
            notaGeneral = nota;
            cerrarModal('modalNota');
        }

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
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: JSON.stringify({ nip: nip.trim() })
                });
                const data = await res.json().catch(() => null);
                if (!res.ok) throw new Error(data?.message || 'Error al verificar NIP');
                if (data.success) {
                    capitanAutorizado = true;
                    mostrarExito('Capitán autorizado.');
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
                    } else {
                        container.innerHTML = `<div class="rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] p-6 text-center text-[var(--text-muted)]"><p class="font-bold text-sm mb-2">No hay mesas abiertas disponibles.</p><p class="text-[12px]">Solo el capitán puede enviar un pedido a otra mesa abierta.</p></div>`;
                    }
                    document.getElementById('modalCapitan').classList.remove('hidden');
                }
            } catch (err) { mostrarError(err.message || 'Error al verificar NIP'); }
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
                const modsElementos = item.querySelectorAll('.nota-texto-real');
                const mods = [];
                modsElementos.forEach(m => mods.push(m.innerText.trim()));
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
                    setTimeout(() => window.location.href = '{{ route("mesero.dashboard") ?? '#' }}', 1000);
                } else { throw new Error(data.message || 'Error.'); }
            })
            .catch(error => {
                mostrarError(error.message || "Error al enviar.");
                btn.innerHTML = '<i class="fas fa-paper-plane text-sm"></i> <span>Enviar a Cocina</span>';
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>