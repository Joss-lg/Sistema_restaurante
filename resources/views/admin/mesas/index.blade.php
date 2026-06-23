@extends('layouts.admin')

@section('title', 'Mesas | Ollintem Pro')

@section('header-title', 'Gestión de Mesas')
@section('header-subtitle', 'Supervisión en tiempo real y gestión espacial')

@section('content')
<div class="p-3 sm:p-4 lg:p-6 max-w-[1600px] mx-auto w-full space-y-4 flex-1 flex flex-col bg-[var(--bg-color)] text-[var(--text-color)] h-[calc(100vh-80px)] overflow-hidden">
    
    {{-- Contenedor de Toasts para Notificaciones --}}
    <div id="toastContainer" class="toast-wrapper" aria-live="polite" aria-atomic="true"></div>
    
    <style>
        /* Variables del Plano Interactivo Premium */
        :root {
            --mapa-bg: #050507; 
            --mapa-dot: rgba(255, 255, 255, 0.04);
            --grid-size: 30px; 
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-highlight: rgba(255, 255, 255, 0.15);
            --card-color: #0E0E12;
        }
        body.modo-crema {
            --mapa-bg: #f8fafc; 
            --mapa-dot: rgba(0, 0, 0, 0.06);
            --glass-border: rgba(0, 0, 0, 0.05);
            --glass-highlight: rgba(255, 255, 255, 0.8);
            --card-color: #ffffff;
        }

        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }

        .mapa-scroll::-webkit-scrollbar { height: 8px; width: 8px; }
        .mapa-scroll::-webkit-scrollbar-track { background: var(--bg-color); border-radius: 8px; }
        .mapa-scroll::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 8px; border: 2px solid var(--bg-color); }
        .mapa-scroll::-webkit-scrollbar-thumb:hover { background: #3B82F6; }

        .toast-wrapper { position: fixed !important; top: 1.5rem !important; right: 1.5rem !important; z-index: 99999 !important; display: flex; flex-direction: column; gap: 0.75rem; pointer-events: none; align-items: flex-end; }
        .toast-panel { pointer-events: auto; background: rgba(11, 15, 25, 0.92); color: #F8FAFC; border: 1px solid rgba(148, 163, 184, 0.15); border-left-width: 4px; border-radius: 1.25rem; backdrop-filter: blur(20px); box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35); padding: 1rem 1.25rem; opacity: 0; transform: translateX(20px); transition: opacity 0.35s ease, transform 0.35s ease; font-size: 0.95rem; display: grid; grid-template-columns: auto 1fr; gap: 0.85rem; align-items: center; min-width: 280px; max-width: 360px; }
        .toast-panel.show { opacity: 1; transform: translateX(0); }
        .toast-panel.success { border-color: rgba(16, 185, 129, 0.25); }
        .toast-panel.error { border-color: rgba(239, 68, 68, 0.25); }
        .toast-panel .toast-icon { width: 2.25rem; height: 2.25rem; display: grid; place-items: center; border-radius: 1rem; background: rgba(255, 255, 255, 0.08); color: inherit; }
        .toast-panel.success .toast-icon { background: rgba(16, 185, 129, 0.18); color: #4ade80; }
        .toast-panel.error .toast-icon { background: rgba(239, 68, 68, 0.18); color: #f87171; }
        .toast-panel strong { display: block; color: inherit; font-weight: 700; letter-spacing: 0.01em; }
        .toast-panel span { display: block; margin-top: 0.1rem; color: rgba(248, 250, 252, 0.8); font-size: 0.88rem; line-height: 1.5; }

        @keyframes slideUpFade {
            0% { opacity: 0; transform: translateY(15px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .mesa-animada { animation: slideUpFade 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .tooltip-card { opacity: 0; visibility: hidden; transform: translateY(10px) scale(0.95); transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .mesa-ui:hover .tooltip-card { opacity: 1; visibility: visible; transform: translateY(0) scale(1); }
        
        .mesa-ui { box-shadow: inset 0 1px 1px var(--glass-highlight), 0 4px 15px rgba(0, 0, 0, 0.2); transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease; touch-action: none; user-select: none; -webkit-user-drag: none; }
        .mesa-ui:hover { transform: translateY(-3px) scale(1.02); z-index: 40; }
        .mesa-fusion-selected { box-shadow: 0 0 0 3px #a855f7, 0 10px 25px rgba(168,85,247,0.4), inset 0 1px 1px var(--glass-highlight) !important; border-color: #a855f7 !important; transform: scale(1.03) translateY(-3px); z-index: 50; }
        .radar-ping { position: absolute; inset: -3px; border-radius: 50%; animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite; opacity: 0.6; }
    </style>

    {{-- CABECERA COMPACTA --}}
    <div class="flex justify-between items-center flex-shrink-0 animate-[slideUpFade_0.3s_ease-out]">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-[var(--text-color)] tracking-tight leading-none">Plano Espacial</h1>
        </div>
        <div class="flex gap-2">
            <button onclick="toggleMapaMesas()" id="btnVistaPrincipal" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-gradient-to-r from-[#3B82F6] to-[#2563EB] hover:-translate-y-0.5 text-white shadow-md flex items-center gap-2 outline-none">
                <i class="fas fa-list"></i> Ver Lista
            </button>
        </div>
    </div>

    {{-- FILTROS Y CONTROLES (Vista Lista) --}}
    <div id="controlesLista" class="hidden flex-col lg:flex-row gap-3 justify-between items-start flex-shrink-0">
        <div class="flex gap-2 flex-wrap p-1 rounded-xl bg-[var(--card-color)] border border-[var(--border-color)]">
            <button onclick="filtrarMesas('todos')" class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-blue-600 text-white shadow-sm filtro-btn" data-filtro="todos">Todas</button>
            <button onclick="filtrarMesas('libre')" class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-transparent text-[var(--text-muted)] hover:text-[var(--text-color)] filtro-btn" data-filtro="libre">Libres</button>
            <button onclick="filtrarMesas('ocupada')" class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-transparent text-[var(--text-muted)] hover:text-[var(--text-color)] filtro-btn" data-filtro="ocupada">Ocupadas</button>
            <button onclick="filtrarMesas('reservada')" class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-transparent text-[var(--text-muted)] hover:text-[var(--text-color)] filtro-btn" data-filtro="reservada">Reservadas</button>
        </div>
    </div>

    {{-- VISTA: MAPA DE MESAS (Lienzo Arquitectónico) --}}
    <div id="mapa-mesas-wrapper" class="flex-1 flex flex-col min-h-0">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3 flex-shrink-0 bg-[var(--card-color)]/90 backdrop-blur-xl p-2 sm:p-3 rounded-2xl border border-[var(--border-color)] shadow-sm z-20">
            <div class="flex gap-1 overflow-x-auto hide-scroll bg-[var(--bg-color)] p-1 rounded-xl border border-[var(--border-color)]">
                <button onclick="cambiarZona('Todas')" class="zona-btn px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-[#3B82F6] text-white transition-all whitespace-nowrap">Todas</button>
                <button onclick="cambiarZona('Salón')" class="zona-btn px-3 py-1.5 rounded-lg text-[9px] font-bold uppercase tracking-widest text-[var(--text-muted)] hover:text-[var(--text-color)] transition-all whitespace-nowrap">Salón</button>
                <button onclick="cambiarZona('Terraza')" class="zona-btn px-3 py-1.5 rounded-lg text-[9px] font-bold uppercase tracking-widest text-[var(--text-muted)] hover:text-[var(--text-color)] transition-all whitespace-nowrap">Terraza</button>
                <button onclick="cambiarZona('VIP')" class="zona-btn px-3 py-1.5 rounded-lg text-[9px] font-bold uppercase tracking-widest text-[var(--text-muted)] hover:text-[var(--text-color)] transition-all whitespace-nowrap">VIP</button>
            </div>

            <div class="flex gap-2 items-center">
                <button id="btnModoFusion" onclick="toggleModoFusion()" class="px-3 py-1.5 rounded-lg bg-transparent border border-purple-500/30 text-purple-500 text-[10px] font-black uppercase tracking-widest hover:bg-purple-500 hover:text-white transition-all outline-none flex items-center gap-1.5">
                    <i class="fas fa-link"></i> <span id="txtBotonFusion" class="hidden sm:inline">Unir</span>
                </button>
                <button id="botonModoEdicion" onclick="toggleModoEdicion()" class="px-3 py-1.5 rounded-lg bg-[var(--bg-color)] border border-[var(--border-color)] text-[var(--text-color)] text-[10px] font-black uppercase tracking-widest hover:border-[#3B82F6] hover:text-[#3B82F6] transition-all outline-none flex items-center gap-1.5">
                    <i class="fas fa-crosshairs"></i> <span id="txtBotonEdicion" class="hidden sm:inline">Editar</span>
                </button>
                <button id="botonAgregarMesaMapa" onclick="abrirModalNuevaMesa()" class="hidden px-3 py-1.5 rounded-lg bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest hover:bg-emerald-400 transition-all outline-none flex items-center gap-1.5">
                    <i class="fas fa-plus"></i> <span class="hidden sm:inline">Mesa</span>
                </button>
            </div>
        </div>

        <div class="flex-1 relative w-full border border-[var(--border-color)] rounded-[1.5rem] overflow-hidden shadow-xl transition-all duration-300 bg-[var(--mapa-bg)] group">
            <div id="scroll-area" class="w-full h-full overflow-auto mapa-scroll relative z-0">
                <div id="contenedor-lienzo" class="relative min-w-[2000px] min-h-[2000px] transition-all duration-300" style="background-image: radial-gradient(var(--mapa-dot) 1px, transparent 1px); background-size: var(--grid-size) var(--grid-size);">
                    <div id="mapa-mesas" class="absolute inset-0 w-full h-full"></div>
                </div>
            </div>
            
            <div class="absolute bottom-4 right-4 bg-[var(--card-color)]/90 backdrop-blur-xl border border-[var(--glass-border)] rounded-xl p-3 flex flex-col gap-2 pointer-events-none shadow-lg z-20 transition-all duration-300 group-hover:bg-[var(--card-color)]/95">
                <div class="flex items-center gap-2 text-[9px] font-bold text-[var(--text-color)]"><div class="w-2 h-2 rounded-full bg-[#3B82F6]"></div> Normal (&lt; 45m)</div>
                <div class="flex items-center gap-2 text-[9px] font-bold text-[var(--text-color)]"><div class="w-2 h-2 rounded-full bg-amber-500"></div> Precaución (+45m)</div>
                <div class="flex items-center gap-2 text-[9px] font-bold text-[var(--text-color)]"><div class="relative w-2 h-2 rounded-full bg-rose-500"><div class="radar-ping bg-rose-500"></div></div> Crítico (+1.5h)</div>
            </div>
        </div>
    </div>

    {{-- VISTA: GRID DE MESAS (Lista Normal) --}}
    <div id="vista-lista-wrapper" class="hidden flex-1 overflow-y-auto hide-scroll pb-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3" id="mesas-container"></div>
    </div>

    {{-- Modal: Crear Nueva Mesa --}}
    <div id="modalNuevaMesa" class="fixed inset-0 z-[100] flex items-center justify-center hidden opacity-0 bg-black/60 backdrop-blur-sm transition-all duration-300 p-4">
        <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2rem] shadow-2xl w-full max-w-sm p-6 transform scale-95 transition-transform duration-300" id="cardNueva">
            <div class="flex items-center justify-between mb-5 border-b border-[var(--border-color)] pb-4">
                <div>
                    <h2 class="text-xl font-black text-[var(--text-color)] tracking-tight">Nueva Mesa</h2>
                    <p class="text-[10px] uppercase tracking-widest text-emerald-500 font-bold mt-1">Crear Registro</p>
                </div>
                <button type="button" onclick="cerrarModalNuevaMesa()" class="w-8 h-8 rounded-full bg-[var(--bg-color)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-rose-500 transition-colors outline-none"><i class="fas fa-times"></i></button>
            </div>
            <div class="grid gap-4">
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Nombre / Número</span>
                    <input id="nuevaMesaNumero" type="text" class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-emerald-500 transition-colors">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Capacidad (Personas)</span>
                    <input id="nuevaMesaCapacidad" type="number" min="1" class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-emerald-500 transition-colors">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Estado Inicial</span>
                    <select id="nuevaMesaEstado" class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-emerald-500 transition-colors appearance-none">
                        <option value="disponible">Libre</option>
                        <option value="ocupada">Ocupada</option>
                        <option value="reservada">Reservada</option>
                    </select>
                </label>
            </div>
            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-[var(--border-color)]">
                <button type="button" onclick="cerrarModalNuevaMesa()" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-bold text-[var(--text-color)] hover:bg-white/5 transition outline-none">Cancelar</button>
                <button type="button" onclick="crearNuevaMesa()" class="px-5 py-2.5 rounded-xl bg-emerald-500 text-white text-xs font-black uppercase tracking-widest hover:bg-emerald-600 transition outline-none shadow-sm">Crear Mesa</button>
            </div>
        </div>
    </div>

    {{-- Modal: Editar Mesa --}}
    <div id="modalEditarMesa" class="fixed inset-0 z-[100] flex items-center justify-center hidden opacity-0 bg-black/60 backdrop-blur-sm transition-all duration-300 p-4">
        <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2rem] shadow-2xl w-full max-w-sm p-6 transform scale-95 transition-transform duration-300" id="cardEditar">
            <div class="flex items-center justify-between mb-5 border-b border-[var(--border-color)] pb-4">
                <div>
                    <h2 class="text-xl font-black text-[var(--text-color)] tracking-tight">Editar Mesa</h2>
                    <p class="text-[10px] uppercase tracking-widest text-[#3B82F6] font-bold mt-1">Ajustes Generales</p>
                </div>
                <button type="button" onclick="cerrarModalEditarMesa()" class="w-8 h-8 rounded-full bg-[var(--bg-color)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-rose-500 transition-colors outline-none"><i class="fas fa-times"></i></button>
            </div>
            <input type="hidden" id="editarMesaId">
            <div class="grid gap-4">
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Nombre / Número</span>
                    <input id="editarMesaNumero" type="text" class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-[#3B82F6] transition-colors">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Capacidad (Personas)</span>
                    <input id="editarMesaCapacidad" type="number" min="1" class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-[#3B82F6] transition-colors">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Estado Actual</span>
                    <select id="editarMesaEstado" class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-[#3B82F6] transition-colors appearance-none">
                        <option value="disponible">Libre</option>
                        <option value="ocupada">Ocupada</option>
                        <option value="reservada">Reservada</option>
                    </select>
                </label>
            </div>
            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-[var(--border-color)]">
                <button type="button" onclick="cerrarModalEditarMesa()" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-bold text-[var(--text-color)] hover:bg-white/5 transition outline-none">Cancelar</button>
                <button type="button" onclick="guardarMesaEditada()" class="px-5 py-2.5 rounded-xl bg-[#3B82F6] text-white text-xs font-black uppercase tracking-widest hover:bg-[#2563EB] transition outline-none shadow-sm">Guardar</button>
            </div>
        </div>
    </div>

    {{-- Modal: Eliminar Mesa --}}
    <div id="modalEliminarMesa" class="fixed inset-0 z-[100] flex items-center justify-center hidden opacity-0 bg-black/60 backdrop-blur-sm transition-all duration-300 p-4">
        <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2rem] shadow-2xl w-full max-w-sm p-6 transform scale-95 transition-transform duration-300" id="cardEliminar">
            <div class="flex items-center justify-between mb-5 border-b border-[var(--border-color)] pb-4">
                <div>
                    <h2 class="text-xl font-black text-rose-500 tracking-tight">Eliminar Mesa</h2>
                    <p class="text-[10px] uppercase tracking-widest text-[var(--text-muted)] font-bold mt-1">Acción irreversible</p>
                </div>
                <button type="button" onclick="cerrarModalEliminarMesa()" class="w-8 h-8 rounded-full bg-[var(--bg-color)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-rose-500 transition-colors outline-none"><i class="fas fa-times"></i></button>
            </div>
            <input type="hidden" id="eliminarMesaId">
            <div class="mb-2 p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl">
                <p class="text-sm font-bold text-[var(--text-color)]">¿Confirmas la eliminación?</p>
                <p class="text-xs text-[var(--text-muted)] mt-1.5">La mesa <span id="eliminarMesaNumero" class="font-black text-rose-400"></span> y sus coordenadas serán borradas permanentemente.</p>
            </div>
            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-[var(--border-color)]">
                <button type="button" onclick="cerrarModalEliminarMesa()" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-bold text-[var(--text-color)] hover:bg-white/5 transition outline-none">Cancelar</button>
                <button type="button" onclick="confirmarEliminarMesa()" class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-black uppercase tracking-widest hover:bg-rose-500 transition outline-none shadow-sm">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let estadoGlobal = {
        mesas: [],
        filtroActual: 'todos',
        vista: 'mapa', 
        modoEdicion: false,
        modoFusion: false,
        mesasParaFusionar: [],
        zonaActual: 'Todas',
        primeraCarga: true 
    };

    let dragState = { activo: false, elemento: null, mesaId: null, originX: 0, originY: 0, startX: 0, startY: 0, contenedor: null };

    document.addEventListener('DOMContentLoaded', function() {
        cargarMesas();
        setInterval(() => { if (!estadoGlobal.modoEdicion && !estadoGlobal.modoFusion) cargarMesas(); }, 5000);
        document.addEventListener('click', cerrarMenuMesa);
    });

    function mostrarToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `toast-panel ${type}`;
        let iconHtml = type === 'success' ? '<i class="fas fa-check"></i>' : type === 'error' ? '<i class="fas fa-exclamation-triangle"></i>' : '<i class="fas fa-info"></i>';
        toast.innerHTML = `<div class="toast-icon">${iconHtml}</div><div><strong>Notificación</strong><span>${message}</span></div>`;
        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('show'));
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3800);
    }
    function mostrarError(mensaje) { mostrarToast(mensaje, 'error'); }
    function mostrarExito(mensaje) { mostrarToast(mensaje, 'success'); }

    function cargarMesas() {
        fetch('/admin/mesas/api/mesas')
            .then(res => {
                if(!res.ok) throw new Error('Error en API');
                return res.json();
            })
            .then(data => {
                estadoGlobal.mesas = data.map(m => ({
                    ...m,
                    zona: m.zona || ['Salón', 'Terraza', 'VIP'][m.id % 3],
                    posicion_x: m.posicion_x,
                    posicion_y: m.posicion_y,
                }));
                renderizarPantalla();
            }).catch(e => console.error('Error red:', e));
    }

    function renderizarPantalla() {
        const wMapa = document.getElementById('mapa-mesas-wrapper'), wLista = document.getElementById('vista-lista-wrapper'), cLista = document.getElementById('controlesLista'), btn = document.getElementById('btnVistaPrincipal');
        if (estadoGlobal.vista === 'mapa') {
            wLista.classList.add('hidden'); cLista.classList.add('hidden'); wMapa.classList.remove('hidden'); wMapa.classList.add('flex');
            btn.innerHTML = '<i class="fas fa-list"></i> <span class="hidden sm:inline">Ver Lista</span>';
            renderizarMapaMesas();
        } else {
            wLista.classList.remove('hidden'); cLista.classList.remove('hidden'); wMapa.classList.add('hidden'); wMapa.classList.remove('flex');
            btn.innerHTML = '<i class="fas fa-map-marked-alt"></i> <span class="hidden sm:inline">Ver Mapa</span>';
            renderizarVistaLista();
        }
        setTimeout(() => { estadoGlobal.primeraCarga = false; }, 800);
    }

    function toggleMapaMesas() { 
        estadoGlobal.vista = estadoGlobal.vista === 'mapa' ? 'lista' : 'mapa'; 
        estadoGlobal.primeraCarga = true; 
        renderizarPantalla(); 
    }

    function cambiarZona(zona) {
        estadoGlobal.zonaActual = zona;
        document.querySelectorAll('.zona-btn').forEach(b => {
            b.className = "zona-btn px-3 py-1.5 rounded-lg text-[9px] font-bold uppercase tracking-widest text-[var(--text-muted)] hover:text-[var(--text-color)] transition-all whitespace-nowrap";
            if(b.innerText.includes(zona) || (zona==='Todas' && b.innerText==='TODAS')) b.className = "zona-btn px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest bg-[#3B82F6] text-white transition-all whitespace-nowrap";
        });
        estadoGlobal.primeraCarga = true; 
        renderizarMapaMesas();
    }

    const tw = {
        emerald: { bg: 'bg-emerald-500/10', border: 'border-emerald-400/30 hover:border-emerald-400', text: 'text-emerald-400', dot: 'bg-emerald-400', glow: 'shadow-[0_0_15px_rgba(52,211,153,0.1)]' },
        blue: { bg: 'bg-blue-500/10', border: 'border-blue-400/30 hover:border-blue-400', text: 'text-blue-400', dot: 'bg-blue-400', glow: 'shadow-[0_0_15px_rgba(96,165,250,0.1)]' },
        amber: { bg: 'bg-amber-500/10', border: 'border-amber-400/30 hover:border-amber-400', text: 'text-amber-400', dot: 'bg-amber-400', glow: 'shadow-[0_0_15px_rgba(251,191,36,0.1)]' },
        rose: { bg: 'bg-rose-500/10', border: 'border-rose-400/30 hover:border-rose-400', text: 'text-rose-400', dot: 'bg-rose-400', glow: 'shadow-[0_0_15px_rgba(251,113,133,0.15)]' },
        purple: { bg: 'bg-purple-500/10', border: 'border-purple-400/30 hover:border-purple-400', text: 'text-purple-400', dot: 'bg-purple-400', glow: 'shadow-[0_0_15px_rgba(192,132,252,0.1)]' }
    };

    function renderizarMapaMesas() {
        const mapa = document.getElementById('mapa-mesas');
        const mesasEdit = dragState.activo ? [dragState.mesaId] : [];
        const mesasFiltradas = estadoGlobal.zonaActual === 'Todas' ? estadoGlobal.mesas : estadoGlobal.mesas.filter(m => m.zona === estadoGlobal.zonaActual);
        
        // CORRECCIÓN MAGISTRAL: Borramos todo el mapa antes de dibujar para que las mesas nuevas NO se oculten
        mapa.innerHTML = '';

        if (mesasFiltradas.length === 0) {
            mapa.innerHTML = `<div class="empty-state absolute inset-0 flex flex-col items-center justify-center opacity-60"><i class="fas fa-chair text-3xl mb-2 text-[var(--text-muted)]"></i><p class="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">Zona vacía</p></div>`;
            return;
        }

        mesasFiltradas.forEach((m, index) => { 
            if(mesasEdit.includes(m.id)) return; 
            const newCard = crearMesaGlass(m, index);
            mapa.appendChild(newCard);
        });
    }

    function crearMesaGlass(mesa, index) {
        const card = document.createElement('div');
        const estadoStr = (mesa.estado || 'Libre').toLowerCase();
        const isOcupada = estadoStr === 'ocupada';
        const isReservada = estadoStr === 'reservada';
        
        let sTheme = 'emerald', ping = '', radar = '';

        if (isOcupada) {
            if (mesa.minutos_activa < 45) sTheme = 'blue'; 
            else if (mesa.minutos_activa < 90) sTheme = 'amber'; 
            else { sTheme = 'rose'; ping = 'animate-pulse'; radar = '<div class="radar-ping bg-rose-500"></div>'; }
        } else if (isReservada) {
            sTheme = 'purple';
        }
        
        const s = tw[sTheme], esRect = (parseInt(mesa.capacidad)||0) >= 5;
        const wClass = esRect ? 'w-40' : 'w-28', hClass = 'h-28';

        // CORRECCIÓN: Parseo estricto para evitar que la mesa se mande a la coordenada NaNpx
        const posX = (mesa.posicion_x != null && mesa.posicion_x !== "") ? parseInt(mesa.posicion_x) : ((index % 8) * 180 + 30);
        const posY = (mesa.posicion_y != null && mesa.posicion_y !== "") ? parseInt(mesa.posicion_y) : (Math.floor(index / 8) * 140 + 30);
        
        const cursor = estadoGlobal.modoEdicion ? 'cursor-grab' : 'cursor-pointer';
        const clsAnimacion = estadoGlobal.primeraCarga ? 'mesa-animada' : '';

        card.className = `mesa-ui ${clsAnimacion} absolute ${wClass} ${hClass} rounded-[1.25rem] flex flex-col justify-between p-3 border border-[var(--glass-border)] bg-[var(--card-color)]/90 backdrop-blur-md ${s.bg} hover:${s.border} ${s.glow} ${cursor}`;
        card.style.touchAction = 'none';
        card.style.webkitUserDrag = 'none';
        card.style.userSelect = 'none';
        if (estadoGlobal.primeraCarga) card.style.animationDelay = `${Math.min(index * 0.02, 0.3)}s`; 
        
        card.style.left = `${posX}px`; 
        card.style.top = `${posY}px`; 
        card.dataset.mesaId = mesa.id;

        card.onpointerdown = e => iniciarArrastre(e, card, mesa);
        if (estadoGlobal.modoFusion) {
            if (estadoGlobal.mesasParaFusionar.includes(mesa.id)) card.classList.add('mesa-fusion-selected');
            card.onclick = () => selFusion(mesa.id, card);
        } else if (!estadoGlobal.modoEdicion) {
            card.onclick = e => { e.stopPropagation(); mostrarMenu(mesa.id, card); };
        }

        const tip = isOcupada && !estadoGlobal.modoEdicion && !estadoGlobal.modoFusion ? `
            <div class="tooltip-card absolute -top-[4rem] left-1/2 -translate-x-1/2 w-44 bg-[var(--card-color)]/95 border border-[var(--glass-border)] rounded-xl p-2.5 shadow-xl z-[100] pointer-events-none">
                <div class="flex justify-between items-center mb-1 border-b border-[var(--border-color)] pb-1"><span class="text-[8px] font-black uppercase text-[var(--text-muted)]">Mesero</span><span class="text-[9px] font-bold text-[var(--text-color)]">${mesa.mesero_nombre}</span></div>
                <div class="flex justify-between items-end"><span class="text-[8px] font-black uppercase text-[var(--text-muted)]">Total</span><span class="text-xs font-black text-[#3B82F6]">$${mesa.total_cuenta}</span></div>
            </div>` : '';

        card.innerHTML = `${tip}
            <div class="flex justify-between w-full"><span class="text-[8px] font-black uppercase tracking-widest ${s.text} opacity-80">Mesa</span></div>
            <div class="flex-1 flex items-center justify-center w-full"><span class="text-xl sm:text-2xl font-black text-[var(--text-color)] leading-none truncate">${mesa.numero}</span></div>
            <div class="flex items-center justify-center w-full"><div class="flex items-center gap-1 px-2 py-0.5 rounded-md border border-[var(--border-color)] bg-[var(--bg-color)]/80"><i class="fas fa-users text-[8px] ${s.text}"></i><span class="text-[9px] font-bold text-[var(--text-color)]">${mesa.capacidad}</span></div></div>
            <div class="absolute -top-1 -right-1 w-3 h-3 rounded-full ${s.dot} border-[2px] border-[var(--mapa-bg)] ${ping}">${radar}</div>
            <div class="absolute -right-2 top-1/2 -translate-y-1/2 translate-x-full hidden w-28 rounded-xl border border-[var(--glass-border)] bg-[var(--card-color)]/95 p-1.5 shadow-xl z-50" data-menu="mesa">
                <button onclick="event.stopPropagation(); irACobrar(${mesa.id})" class="w-full mb-1 rounded-lg bg-emerald-500/10 px-2 py-1.5 text-[8px] font-black uppercase text-emerald-500 hover:bg-emerald-500 hover:text-white text-left"><i class="fas fa-cash-register w-3"></i> Cobrar</button>
                <button onclick="event.stopPropagation(); abrirModalEditarMesa(${mesa.id})" class="w-full mb-1 rounded-lg bg-[#3B82F6]/10 px-2 py-1.5 text-[8px] font-black uppercase text-[#3B82F6] hover:bg-[#3B82F6] hover:text-white text-left"><i class="fas fa-pen w-3"></i> Editar</button>
                <button onclick="event.stopPropagation(); eliminarMesa(${mesa.id})" class="w-full rounded-lg bg-rose-500/10 px-2 py-1.5 text-[8px] font-black uppercase text-rose-500 hover:bg-rose-500 hover:text-white text-left"><i class="fas fa-trash w-3"></i> Eliminar</button>
            </div>`;
        return card;
    }

    function renderizarVistaLista() {
        const c = document.getElementById('mesas-container');
        c.innerHTML = '';
        const mFiltradas = estadoGlobal.filtroActual === 'todos' ? estadoGlobal.mesas : estadoGlobal.mesas.filter(m => {
            const estadoStr = (m.estado || '').toLowerCase();
            return (estadoStr==='reservada'?'reservada':estadoStr==='ocupada'?'ocupada':'libre') === estadoGlobal.filtroActual;
        });

        mFiltradas.forEach((m, i) => {
            const estadoStr = (m.estado || 'Libre').toLowerCase();
            const est = estadoStr==='reservada'?'reservada':estadoStr==='ocupada'?'ocupada':'libre';
            const color = est==='ocupada'?'rose':est==='reservada'?'amber':'emerald';
            
            const div = document.createElement('div');
            div.className = `group relative bg-[var(--card-color)] border border-[var(--border-color)] rounded-xl p-4 cursor-pointer shadow-sm hover:border-[#3B82F6]/50 ${estadoGlobal.primeraCarga ? 'mesa-animada' : ''}`;
            if (estadoGlobal.primeraCarga) div.style.animationDelay = `${i * 0.02}s`; 
            div.dataset.mesaId = m.id;
            div.onclick = e => { e.stopPropagation(); mostrarMenu(m.id, div); };
            div.innerHTML = `
                <div class="flex justify-between mb-3"><div><span class="text-[8px] font-black uppercase text-[var(--text-muted)] block">Mesa</span><span class="text-xl font-black">${m.numero}</span></div><div class="flex items-center gap-1 px-1.5 py-0.5 rounded bg-${color}-500/10"><div class="w-1.5 h-1.5 rounded-full bg-${color}-500"></div><span class="text-[7px] font-black uppercase text-${color}-500">${est}</span></div></div>
                ${m.fusionada ? `<div class="mb-2 px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-[0.1em] text-purple-500 bg-purple-500/10 inline-block">Unida</div>` : ''}
                <div class="pt-3 border-t border-[var(--border-color)] flex items-center gap-1.5 text-[var(--text-muted)]"><i class="fas fa-users text-[9px]"></i><span class="text-[10px] font-bold">Capacidad: ${m.capacidad}</span></div>
                <div class="absolute right-3 top-10 hidden w-32 rounded-lg border border-[var(--border-color)] bg-[var(--bg-color)] p-1.5 shadow-xl z-20" data-menu="mesa"><button onclick="event.stopPropagation(); irACobrar(${m.id})" class="w-full mb-1 rounded bg-emerald-500/10 px-2 py-1 text-[8px] font-bold uppercase text-emerald-500 hover:bg-emerald-500 hover:text-white text-left">Cobrar</button><button onclick="event.stopPropagation(); abrirModalEditarMesa(${m.id})" class="w-full mb-1 rounded bg-[#3B82F6]/10 px-2 py-1 text-[8px] font-bold uppercase text-[#3B82F6] hover:bg-[#3B82F6] hover:text-white text-left">Editar</button><button onclick="event.stopPropagation(); eliminarMesa(${m.id})" class="w-full rounded bg-rose-500/10 px-2 py-1 text-[8px] font-bold uppercase text-rose-500 hover:bg-rose-500 hover:text-white text-left">Eliminar</button></div>`;
            c.appendChild(div);
        });
    }

    function filtrarMesas(f) { estadoGlobal.filtroActual = f; estadoGlobal.primeraCarga = true; document.querySelectorAll('.filtro-btn').forEach(b => b.className = "px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-transparent text-[var(--text-muted)] hover:text-[var(--text-color)] filtro-btn"); document.querySelector(`[data-filtro="${f}"]`).className = "px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-blue-600 text-white shadow-sm filtro-btn"; renderizarVistaLista(); setTimeout(()=>estadoGlobal.primeraCarga=false, 500); }
    function irACobrar(id) { window.location.href = `/admin/caja/cobrar/${id}`; }

    // ----------------------------------------------------
    // MENÚS Y CONTROLES DE INTERFAZ
    // ----------------------------------------------------
    function mostrarMenu(id, card) { cerrarMenuMesa(); const m = card.querySelector('[data-menu="mesa"]'); if(m) m.classList.remove('hidden'); }
    function cerrarMenuMesa(e) { if (e && e.target.closest('[data-menu="mesa"]')) return; document.querySelectorAll('[data-menu="mesa"]').forEach(m => m.classList.add('hidden')); }
    
    function toggleModoEdicion() {
        if(estadoGlobal.modoFusion) toggleModoFusion();
        estadoGlobal.modoEdicion = !estadoGlobal.modoEdicion;
        const btn = document.getElementById('botonModoEdicion'), txt = document.getElementById('txtBotonEdicion'), c = document.getElementById('contenedor-lienzo'), btnA = document.getElementById('botonAgregarMesaMapa');
        if (estadoGlobal.modoEdicion) {
            btn.className = "px-3 py-1.5 rounded-lg bg-[#3B82F6] text-white text-[10px] font-black uppercase tracking-widest shadow-md flex items-center gap-1.5 outline-none";
            txt.innerText = 'Guardar'; c.style.boxShadow = "inset 0 0 40px rgba(59,130,246,0.1)"; btnA.classList.remove('hidden');
        } else {
            btn.className = "px-3 py-1.5 rounded-lg bg-[var(--bg-color)] border border-[var(--border-color)] text-[var(--text-color)] text-[10px] font-black uppercase tracking-widest hover:border-[#3B82F6] hover:text-[#3B82F6] flex items-center gap-1.5 outline-none";
            txt.innerText = 'Editar'; c.style.boxShadow = "none"; btnA.classList.add('hidden');
            if(estadoGlobal.mesas.length > 0) guardarCoordenadasFinales();
        }
        renderizarMapaMesas(); cerrarMenuMesa();
    }

    function toggleModoFusion() {
        if(estadoGlobal.modoEdicion) toggleModoEdicion();
        const btn = document.getElementById('btnModoFusion'), txt = document.getElementById('txtBotonFusion');
        if (estadoGlobal.modoFusion && estadoGlobal.mesasParaFusionar.length > 1) { fusionarMesas(estadoGlobal.mesasParaFusionar); } 
        else if (estadoGlobal.modoFusion && estadoGlobal.mesasParaFusionar.length === 1) { mostrarError('Selecciona al menos dos mesas para unir.'); }
        estadoGlobal.modoFusion = !estadoGlobal.modoFusion;
        if (estadoGlobal.modoFusion) {
            btn.className = "px-3 py-1.5 rounded-lg bg-purple-600 text-white text-[10px] font-black uppercase tracking-widest shadow-[0_4px_15px_rgba(168,85,247,0.4)] flex items-center gap-1.5 outline-none";
            txt.innerText = 'Unir 2+'; estadoGlobal.mesasParaFusionar = [];
        } else {
            btn.className = "px-3 py-1.5 rounded-lg bg-transparent border border-purple-500/30 text-purple-500 text-[10px] font-black uppercase tracking-widest hover:bg-purple-500 hover:text-white flex items-center gap-1.5 outline-none";
            txt.innerText = 'Unir'; estadoGlobal.mesasParaFusionar = [];
        }
        renderizarMapaMesas();
    }
    
    async function fusionarMesas(ids) {
        if (!ids || ids.length < 2) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        try {
            const response = await fetch('/admin/mesas/api/fusionar', { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }, body: JSON.stringify({ mesas: ids }) });
            const data = await response.json();
            if (response.ok && data.success) { mostrarExito('Mesas unidas correctamente.'); estadoGlobal.modoFusion = false; estadoGlobal.mesasParaFusionar = []; cargarMesas(); } 
            else { mostrarError(data.message || 'No se pudo unir las mesas.'); }
        } catch (error) { mostrarError('Error de red al unir mesas.'); }
    }
    
    function selFusion(id, c) { const i = estadoGlobal.mesasParaFusionar.indexOf(id); if(i > -1) { estadoGlobal.mesasParaFusionar.splice(i,1); c.classList.remove('mesa-fusion-selected'); } else { estadoGlobal.mesasParaFusionar.push(id); c.classList.add('mesa-fusion-selected'); } }

    // ----------------------------------------------------
    // ARRASTRE MAGNÉTICO (SNAP TO GRID)
    // ----------------------------------------------------
    function iniciarArrastre(e, el, mesa) {
        if (estadoGlobal.modoFusion || e.button !== 0) return;
        e.preventDefault(); e.stopPropagation();
        dragState.activo = true; dragState.elemento = el; dragState.mesaId = mesa.id;
        dragState.originX = parseInt(el.style.left||0, 10); dragState.originY = parseInt(el.style.top||0, 10);
        dragState.startX = e.clientX; dragState.startY = e.clientY; dragState.contenedor = document.getElementById('contenedor-lienzo');
        el.setPointerCapture(e.pointerId);
        el.classList.remove('mesa-animada'); el.classList.replace('cursor-grab', 'cursor-grabbing'); el.classList.add('scale-105', 'z-50', 'opacity-90');
        el.addEventListener('pointermove', manejarArrastre); el.addEventListener('pointerup', detenerArrastre); el.addEventListener('pointercancel', detenerArrastre);
    }
    
    function manejarArrastre(e) {
        if (!dragState.activo) return;
        let nX = dragState.originX + (e.clientX - dragState.startX), nY = dragState.originY + (e.clientY - dragState.startY);
        const GS = 30; nX = Math.round(nX/GS)*GS; nY = Math.round(nY/GS)*GS;
        nX = Math.max(0, Math.min(nX, dragState.contenedor.offsetWidth - dragState.elemento.offsetWidth));
        nY = Math.max(0, Math.min(nY, dragState.contenedor.offsetHeight - dragState.elemento.offsetHeight));
        dragState.elemento.style.left = `${nX}px`; dragState.elemento.style.top = `${nY}px`;
    }
    
    function detenerArrastre(e) {
        if (!dragState.activo) return;
        dragState.elemento.releasePointerCapture(e.pointerId); dragState.elemento.classList.replace('cursor-grabbing', 'cursor-grab'); dragState.elemento.classList.remove('scale-105', 'z-50', 'opacity-90');
        guardarPos(dragState.mesaId, parseInt(dragState.elemento.style.left,10), parseInt(dragState.elemento.style.top,10));
        dragState.activo = false; dragState.elemento.removeEventListener('pointermove', manejarArrastre); dragState.elemento.removeEventListener('pointerup', detenerArrastre);
    }

    async function guardarPos(id, x, y) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        try {
            const res = await fetch(`/admin/mesas/api/${id}/posicion`, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }, body: JSON.stringify({ posicion_x: x, posicion_y: y }) });
            if (res.ok) { const idx = estadoGlobal.mesas.findIndex(m => m.id === id); if (idx !== -1) { estadoGlobal.mesas[idx].posicion_x = x; estadoGlobal.mesas[idx].posicion_y = y; } } 
            else { mostrarError('Aviso: No se guardó la posición.'); }
        } catch (e) {}
    }

    async function guardarCoordenadasFinales() {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const coords = estadoGlobal.mesas.map(m => ({ id: m.id, x: m.posicion_x, y: m.posicion_y }));
        try {
            const res = await fetch('/admin/mesas/api/posiciones', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }, body: JSON.stringify({ coordenadas: coords }) });
            if(res.ok) mostrarExito('Distribución guardada exitosamente.');
        } catch(e) {}
    }

    // ----------------------------------------------------
    // OPERACIONES CRUD
    // ----------------------------------------------------
    function abrirModalEditarMesa(id) {
        const mesa = estadoGlobal.mesas.find(m => m.id === id);
        if (!mesa) return;
        const estadoNormalizado = (mesa.estado || 'disponible').toLowerCase();
        document.getElementById('editarMesaId').value = mesa.id; document.getElementById('editarMesaNumero').value = mesa.numero; document.getElementById('editarMesaCapacidad').value = mesa.capacidad; document.getElementById('editarMesaEstado').value = estadoNormalizado === 'libre' ? 'disponible' : estadoNormalizado;
        const modal = document.getElementById('modalEditarMesa'), card = document.getElementById('cardEditar');
        modal.classList.remove('hidden'); setTimeout(() => { modal.classList.remove('opacity-0'); card.classList.remove('scale-95'); }, 10); cerrarMenuMesa();
    }

    function cerrarModalEditarMesa() {
        const modal = document.getElementById('modalEditarMesa'), card = document.getElementById('cardEditar');
        modal.classList.add('opacity-0'); card.classList.add('scale-95'); setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    async function guardarMesaEditada() {
        const id = document.getElementById('editarMesaId').value, numero = document.getElementById('editarMesaNumero').value.trim(), capacidad = parseInt(document.getElementById('editarMesaCapacidad').value, 10), estado = document.getElementById('editarMesaEstado').value, token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        if (!numero || !capacidad) return mostrarError('Completa el número y la capacidad.');
        try {
            const res = await fetch(`/admin/mesas/api/${id}`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }, body: JSON.stringify({ numero, capacidad, estado }) });
            const data = await res.json();
            if (res.ok && data.success) { cerrarModalEditarMesa(); cargarMesas(); mostrarExito('Mesa actualizada correctamente.'); } else { mostrarError(data.message || 'Error al actualizar.'); }
        } catch (e) { mostrarError('Error de conexión con la base de datos.'); }
    }

    function abrirModalNuevaMesa() {
        document.getElementById('nuevaMesaNumero').value = ''; document.getElementById('nuevaMesaCapacidad').value = '';
        const modal = document.getElementById('modalNuevaMesa'), card = document.getElementById('cardNueva');
        modal.classList.remove('hidden'); setTimeout(() => { modal.classList.remove('opacity-0'); card.classList.remove('scale-95'); }, 10);
    }

    function cerrarModalNuevaMesa() {
        const modal = document.getElementById('modalNuevaMesa'), card = document.getElementById('cardNueva');
        modal.classList.add('opacity-0'); card.classList.add('scale-95'); setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    async function crearNuevaMesa() {
        const numero = document.getElementById('nuevaMesaNumero').value.trim();
        const capacidad = parseInt(document.getElementById('nuevaMesaCapacidad').value, 10);
        const estado = document.getElementById('nuevaMesaEstado').value;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (!numero || !capacidad) return mostrarError('Completa todos los campos.');

        try {
            const response = await fetch('/admin/mesas/api', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ numero, capacidad, estado })
            });
            const data = await response.json();
            if (response.ok && data.success) {
                // ✅ INYECCIÓN DIRECTA EN EL MAPA (sin recargar todo)
                const nuevaMesa = data.data;
                
                // 1. Agregar la mesa a estadoGlobal
                estadoGlobal.mesas.push(nuevaMesa);
                
                // 2. Cambiar a "Todas" para ver la mesa inmediatamente (sin importar zona)
                if (estadoGlobal.zonaActual !== 'Todas') {
                    cambiarZona('Todas');
                }
                
                // 3. Re-renderizar el mapa para que aparezca
                renderizarMapaMesas();
                
                // 4. Cerrar el modal
                cerrarModalNuevaMesa();
                
                // 5. Notificación con efecto especial
                mostrarExito('✨ Mesa creada e inyectada en el mapa. ¡Lista para arrastrar!');
                
                // 6. Pequeño refresco en 3 segundos para sincronizar con servidor
                setTimeout(() => {
                    cargarMesas();
                }, 3000);
                
            } else {
                mostrarError(data.message || 'Error al crear la mesa.');
            }
        } catch (error) { 
            console.error('Error:', error);
            mostrarError('Error al procesar la mesa en el servidor.'); 
        }
    }

    function eliminarMesa(id) {
        const mesa = estadoGlobal.mesas.find(m => m.id === id);
        if (!mesa) return;
        document.getElementById('eliminarMesaId').value = mesa.id; document.getElementById('eliminarMesaNumero').textContent = mesa.numero;
        const modal = document.getElementById('modalEliminarMesa'), card = document.getElementById('cardEliminar');
        modal.classList.remove('hidden'); setTimeout(() => { modal.classList.remove('opacity-0'); card.classList.remove('scale-95'); }, 10); cerrarMenuMesa();
    }

    function cerrarModalEliminarMesa() {
        const modal = document.getElementById('modalEliminarMesa'), card = document.getElementById('cardEliminar');
        modal.classList.add('opacity-0'); card.classList.add('scale-95'); setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    async function confirmarEliminarMesa() {
        const id = document.getElementById('eliminarMesaId').value, token = document.querySelector('meta[name="csrf-token"]').getAttribute('content'), btnEliminar = event.target;
        btnEliminar.disabled = true; btnEliminar.textContent = 'Eliminando...';
        try {
            const response = await fetch(`/admin/mesas/api/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });
            const data = await response.json();
            if (data.success) { cerrarModalEliminarMesa(); await new Promise(resolve => setTimeout(resolve, 300)); cargarMesas(); mostrarExito('Mesa eliminada correctamente.'); } 
            else { mostrarError(data.message || 'No se pudo eliminar la mesa.'); btnEliminar.disabled = false; btnEliminar.textContent = 'Eliminar'; }
        } catch (error) { mostrarError('Error al eliminar.'); btnEliminar.disabled = false; btnEliminar.textContent = 'Eliminar'; }
    }
</script>
@endsection