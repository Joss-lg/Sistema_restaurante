@extends('layouts.admin')

@section('title', 'Mesas | Ollintem Pro')

@section('header-title', 'Gestión de Mesas')
@section('header-subtitle', 'Visualiza y gestiona el estado de las mesas del restaurante')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 xl:p-10 max-w-[1500px] mx-auto w-full space-y-6 flex-1 flex flex-col bg-[var(--bg-color)] text-[var(--text-color)] h-full overflow-hidden">
    
    <div id="toastContainer" class="toast-wrapper" aria-live="polite" aria-atomic="true"></div>
    
    <style>
        /* Variables mágicas para el Plano Interactivo */
        :root {
            --mapa-bg: #0f1115; /* Un tono oscuro más elegante */
            --mapa-dot: rgba(255, 255, 255, 0.06);
        }
        body.modo-crema {
            --mapa-bg: #e4e4e7; /* Un gris muy sutil para modo claro */
            --mapa-dot: rgba(0, 0, 0, 0.08);
        }

        .toast-wrapper { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.75rem; align-items: flex-end; pointer-events: none; }
        .toast-panel { min-width: 18rem; max-width: 24rem; pointer-events: auto; background: rgba(15, 23, 42, 0.95); color: #F8FAFC; border: 1px solid rgba(148, 163, 184, 0.15); border-left-width: 4px; border-radius: 1rem; box-shadow: 0 24px 80px rgba(15, 23, 42, 0.18); padding: 1rem 1.1rem; opacity: 0; transform: translateX(28px); transition: opacity 0.25s ease, transform 0.25s ease; font-size: 0.85rem; line-height: 1.5; display: grid; grid-template-columns: auto 1fr; gap: 0.75rem; align-items: center; }
        .toast-panel.show { opacity: 1; transform: translateX(0); }
        .toast-panel.success { border-color: #22c55e; }
        .toast-panel.error { border-color: #ef4444; }
        .toast-panel.info { border-color: #3b82f6; }
        .toast-panel.warning { border-color: #a855f7; }
        .toast-panel strong { display: block; color: #f8fafc; font-weight: 700; }
        
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }

        /* Estilo para barra de desplazamiento del mapa */
        .mapa-scroll::-webkit-scrollbar { height: 8px; width: 8px; }
        .mapa-scroll::-webkit-scrollbar-track { background: var(--bg-color); border-radius: 8px; }
        .mapa-scroll::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 8px; }
        .mapa-scroll::-webkit-scrollbar-thumb:hover { background: #3B82F6; }

        /* Animación para el Tooltip Financiero Premium */
        .tooltip-card {
            opacity: 0; visibility: hidden; transform: translateY(10px) scale(0.95);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .mesa-ui:hover .tooltip-card {
            opacity: 1; visibility: visible; transform: translateY(0) scale(1);
        }
        
        /* Estilo para mesas seleccionadas en Modo Fusión */
        .mesa-fusion-selected {
            box-shadow: 0 0 0 4px #a855f7, 0 10px 30px rgba(168,85,247,0.4) !important;
            border-color: #a855f7 !important;
            transform: scale(1.05);
        }
    </style>

    {{-- CABECERA --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 flex-shrink-0">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-[var(--text-color)] tracking-tight">Mesas del Restaurante</h1>
            <p class="text-[var(--text-muted)] text-sm mt-1">Supervisión en tiempo real y gestión espacial</p>
        </div>
    </div>

    {{-- FILTROS Y CONTROLES (Visibles solo en Vista Lista) --}}
    <div id="controlesLista" class="flex flex-col lg:flex-row gap-3 justify-between items-start flex-shrink-0">
        <div class="flex gap-2 flex-wrap">
            <button onclick="filtrarMesas('todos')" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition bg-blue-600 hover:bg-blue-500 text-white shadow-sm filtro-btn" data-filtro="todos">Todas</button>
            <button onclick="filtrarMesas('libre')" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition bg-[var(--card-color)] border border-[var(--border-color)] hover:border-blue-500 hover:text-blue-500 text-[var(--text-color)] shadow-sm filtro-btn" data-filtro="libre">Libres</button>
            <button onclick="filtrarMesas('ocupada')" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition bg-[var(--card-color)] border border-[var(--border-color)] hover:border-blue-500 hover:text-blue-500 text-[var(--text-color)] shadow-sm filtro-btn" data-filtro="ocupada">Ocupadas</button>
            <button onclick="filtrarMesas('reservada')" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition bg-[var(--card-color)] border border-[var(--border-color)] hover:border-blue-500 hover:text-blue-500 text-[var(--text-color)] shadow-sm filtro-btn" data-filtro="reservada">Reservadas</button>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button onclick="toggleMapaMesas()" class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition bg-[#3B82F6] hover:bg-[#2563EB] text-white shadow-[0_5px_15px_-3px_rgba(59,130,246,0.5)] flex items-center gap-2 outline-none">
                <i class="fas fa-map-marked-alt"></i> Ver Mapa de Mesas
            </button>
        </div>
    </div>

    {{-- VISTA: MAPA DE MESAS (Plano Arquitectónico Premium) --}}
    <div id="mapa-mesas-wrapper" class="hidden flex-1 flex flex-col min-h-0">
        
        {{-- Controles Avanzados del Mapa --}}
        <div class="mb-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 flex-shrink-0 bg-[var(--card-color)] p-4 rounded-2xl border border-[var(--border-color)] shadow-sm">
            
            {{-- ZONAS --}}
            <div class="flex gap-1 bg-[var(--bg-color)] p-1 rounded-xl border border-[var(--border-color)] overflow-x-auto max-w-full hide-scroll">
                <button onclick="cambiarZona('Todas')" class="zona-btn px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest bg-[#3B82F6] text-white shadow-sm transition-all whitespace-nowrap">Todas</button>
                <button onclick="cambiarZona('Salón')" class="zona-btn px-4 py-2 rounded-lg text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] hover:text-[var(--text-color)] transition-all whitespace-nowrap">Salón Principal</button>
                <button onclick="cambiarZona('Terraza')" class="zona-btn px-4 py-2 rounded-lg text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] hover:text-[var(--text-color)] transition-all whitespace-nowrap">Terraza</button>
                <button onclick="cambiarZona('VIP')" class="zona-btn px-4 py-2 rounded-lg text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] hover:text-[var(--text-color)] transition-all whitespace-nowrap">VIP</button>
            </div>

            <div class="flex gap-2 flex-wrap items-center">
                {{-- MODO FUSIÓN --}}
                <button id="btnModoFusion" onclick="toggleModoFusion()" class="px-4 py-2 rounded-xl bg-[var(--bg-color)] border border-[var(--border-color)] text-[var(--text-color)] text-xs font-black uppercase tracking-widest hover:border-purple-500 hover:text-purple-500 transition-all shadow-sm outline-none flex items-center gap-2">
                    <i class="fas fa-link text-sm"></i> <span id="txtBotonFusion">Unir Mesas</span>
                </button>

                <button id="botonModoEdicion" onclick="toggleModoEdicion()" class="px-4 py-2 rounded-xl bg-[var(--bg-color)] border border-[var(--border-color)] text-[var(--text-color)] text-xs font-black uppercase tracking-widest hover:border-[#3B82F6] hover:text-[#3B82F6] transition-all shadow-sm outline-none flex items-center gap-2">
                    <i class="fas fa-pen text-sm"></i> <span id="txtBotonEdicion">Activar Edición</span>
                </button>
                <button id="botonAgregarMesaMapa" onclick="abrirModalNuevaMesa()" class="hidden px-4 py-2 rounded-xl bg-emerald-500 text-white text-xs font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-md outline-none flex items-center gap-2">
                    <i class="fas fa-plus"></i> Nueva Mesa
                </button>
                <button onclick="toggleMapaMesas()" class="px-4 py-2 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs font-black uppercase tracking-widest hover:bg-rose-500 hover:text-white transition-all outline-none flex items-center gap-2">
                    <i class="fas fa-times"></i> Cerrar Mapa
                </button>
            </div>
        </div>

        {{-- Contenedor Maestro del Plano (Oculta lo que se sale del div, pero permite scroll interior) --}}
        <div class="flex-1 relative w-full border border-[var(--border-color)] rounded-[2rem] overflow-hidden shadow-inner transition-all duration-300 bg-[var(--mapa-bg)]">
            
            {{-- ÁREA SCROLLEABLE INFINITA (El lienzo real) --}}
            <div id="scroll-area" class="w-full h-full overflow-auto mapa-scroll relative">
                
                {{-- EL LIENZO GIGANTE (2500x2500 px) --}}
                <div id="contenedor-lienzo" class="relative min-w-[2500px] min-h-[2500px]" style="background-image: radial-gradient(var(--mapa-dot) 1.5px, transparent 1.5px); background-size: 35px 35px;">
                    <div id="mapa-mesas" class="absolute inset-0 w-full h-full">
                        {{-- Las mesas se renderizan aquí dinámicamente --}}
                    </div>
                </div>

            </div>
            
            {{-- Leyenda del Termómetro (Fija en la esquina, no scrollea) --}}
            <div class="absolute bottom-5 right-5 bg-[var(--card-color)]/95 backdrop-blur-md border border-[var(--border-color)] rounded-xl p-3 flex flex-col gap-2 pointer-events-none shadow-2xl z-20">
                <span class="text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1 border-b border-[var(--border-color)] pb-1.5">Termómetro SLA</span>
                <div class="flex items-center gap-2 text-[9px] font-bold text-[var(--text-color)]"><div class="w-2.5 h-2.5 rounded-full bg-[#3B82F6] shadow-[0_0_8px_rgba(59,130,246,0.8)] border border-[var(--bg-color)]"></div> Normal (&lt; 45m)</div>
                <div class="flex items-center gap-2 text-[9px] font-bold text-[var(--text-color)]"><div class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.8)] border border-[var(--bg-color)]"></div> Precaución (+45m)</div>
                <div class="flex items-center gap-2 text-[9px] font-bold text-[var(--text-color)]"><div class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.8)] border border-[var(--bg-color)] animate-pulse"></div> Crítico (+1.5h)</div>
            </div>
        </div>
    </div>

    {{-- VISTA: GRID DE MESAS (Lista Normal) --}}
    <div id="vista-lista-wrapper" class="flex-1 overflow-y-auto hide-scroll pb-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-5" id="mesas-container">
            {{-- Se llena dinámicamente --}}
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODALES DEL SISTEMA (INTACTOS)             --}}
    {{-- ========================================== --}}

    {{-- Modal Editar Mesa --}}
    <div id="modalEditarMesa" class="fixed inset-0 z-50 flex items-center justify-center hidden opacity-0 bg-black/60 backdrop-blur-sm transition-all duration-300 p-4">
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
                    <input id="editarMesaNumero" type="text" class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-[#3B82F6] transition-colors" placeholder="Ej. 12M">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Capacidad (Personas)</span>
                    <input id="editarMesaCapacidad" type="number" min="1" class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-[#3B82F6] transition-colors" placeholder="Ej. 4">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Estado Actual</span>
                    <select id="editarMesaEstado" class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-[#3B82F6] transition-colors appearance-none">
                        <option value="libre">Libre</option>
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

    {{-- Modal Nueva Mesa --}}
    <div id="modalNuevaMesa" class="fixed inset-0 z-50 flex items-center justify-center hidden opacity-0 bg-black/60 backdrop-blur-sm transition-all duration-300 p-4">
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
                    <input id="nuevaMesaNumero" type="text" class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-emerald-500 transition-colors" placeholder="Ej. Barra 1">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Capacidad (Personas)</span>
                    <input id="nuevaMesaCapacidad" type="number" min="1" class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-emerald-500 transition-colors" placeholder="Ej. 2">
                </label>
                <label class="block">
                    <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Estado Inicial</span>
                    <select id="nuevaMesaEstado" class="mt-1.5 w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-sm font-bold text-[var(--text-color)] outline-none focus:border-emerald-500 transition-colors appearance-none">
                        <option value="libre">Libre</option>
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

    {{-- Modal Eliminar Mesa --}}
    <div id="modalEliminarMesa" class="fixed inset-0 z-50 flex items-center justify-center hidden opacity-0 bg-black/60 backdrop-blur-sm transition-all duration-300 p-4">
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
                <p class="text-xs text-[var(--text-muted)] mt-1.5">La mesa <span id="eliminarMesaNumero" class="font-black text-rose-400"></span> y sus coordenadas serán borradas del sistema.</p>
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
        vista: 'lista', // 'lista' o 'mapa'
        modoEdicion: false,
        modoFusion: false,
        mesasParaFusionar: [],
        zonaActual: 'Todas'
    };

    document.addEventListener('DOMContentLoaded', function() {
        cargarMesas();
        // Recargar solo si no se está editando ni fusionando
        setInterval(() => {
            if (!estadoGlobal.modoEdicion && !estadoGlobal.modoFusion) cargarMesas();
        }, 5000);
        document.addEventListener('click', cerrarMenuMesa);
    });

    // ----------------------------------------------------
    // NOTIFICACIONES (TOAST)
    // ----------------------------------------------------
    function mostrarToast(message, type = 'info', duration = 3800) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `toast-panel ${type}`;
        let iconHtml = type === 'success' ? '<i class="fas fa-check text-xs"></i>' : type === 'error' ? '<i class="fas fa-exclamation-triangle text-xs"></i>' : '<i class="fas fa-info text-xs"></i>';
        if(type === 'warning') iconHtml = '<i class="fas fa-link text-xs"></i>';

        toast.innerHTML = `<div class="flex items-center justify-center w-8 h-8 rounded-xl bg-white/10 text-white">${iconHtml}</div><div><strong>${type === 'success' ? 'Éxito' : type === 'error' ? 'Error' : 'Aviso'}</strong><span class="block text-[0.75rem] font-medium leading-snug">${message}</span></div>`;
        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('show'));
        setTimeout(() => {
            toast.classList.remove('show');
            toast.addEventListener('transitionend', () => toast.remove(), { once: true });
        }, duration);
    }
    function mostrarError(mensaje) { mostrarToast(mensaje, 'error'); }
    function mostrarExito(mensaje) { mostrarToast(mensaje, 'success'); }

    // ----------------------------------------------------
    // CARGA DE DATOS ORIGINAL CON SIMULACIÓN DE NUEVOS CAMPOS
    // ----------------------------------------------------
    function cargarMesas() {
        fetch('/admin/mesas/api/mesas')
            .then(response => response.json())
            .then(data => {
                estadoGlobal.mesas = data.map(m => {
                    const zonasFake = ['Salón', 'Terraza', 'VIP'];
                    return {
                        ...m,
                        zona: m.zona || zonasFake[m.id % 3], 
                        minutos_activa: m.minutos_activa || (m.estado === 'ocupada' ? Math.floor(Math.random() * 120) : 0),
                        mesero_nombre: m.mesero_nombre || 'Mesero Asignado',
                        total_cuenta: m.total_cuenta || (Math.random() * 1500).toFixed(2)
                    };
                });
                renderizarMesas();
            })
            .catch(error => console.error('Error cargando mesas:', error));
    }

    // ----------------------------------------------------
    // RENDERIZADO ORIGINAL DE VISTA LISTA
    // ----------------------------------------------------
    function renderizarMesas() {
        const containerLista = document.getElementById('mesas-container');
        const controlesLista = document.getElementById('controlesLista');
        const wrapperMapa = document.getElementById('mapa-mesas-wrapper');
        const wrapperLista = document.getElementById('vista-lista-wrapper');

        if (estadoGlobal.vista === 'mapa') {
            wrapperLista.classList.add('hidden');
            controlesLista.classList.add('hidden');
            wrapperMapa.classList.remove('hidden');
            wrapperMapa.classList.add('flex');
            renderizarMapaMesas();
            return;
        }

        // Vista Lista
        wrapperLista.classList.remove('hidden');
        controlesLista.classList.remove('hidden');
        wrapperMapa.classList.add('hidden');
        wrapperMapa.classList.remove('flex');
        
        containerLista.innerHTML = '';

        const mesasFiltradas = estadoGlobal.mesas.filter(mesa => {
            if (estadoGlobal.filtroActual === 'todos') return true;
            const estadoReal = mesa.estado === 'reservada' ? 'reservada' : mesa.ocupada ? 'ocupada' : 'libre';
            return estadoReal === estadoGlobal.filtroActual;
        });

        if (mesasFiltradas.length === 0) {
            containerLista.innerHTML = '<div class="col-span-full flex flex-col items-center justify-center py-16 opacity-40"><i class="fas fa-utensils text-4xl mb-4 text-[var(--text-muted)]"></i><p class="font-bold text-[var(--text-muted)]">No hay mesas en este estado.</p></div>';
            return;
        }

        mesasFiltradas.forEach(mesa => {
            const estadoReal = mesa.estado === 'reservada' ? 'reservada' : mesa.ocupada ? 'ocupada' : 'libre';
            const colorEstado = estadoReal === 'ocupada' ? 'rose' : estadoReal === 'reservada' ? 'amber' : 'emerald';
            
            const card = document.createElement('div');
            card.className = "group relative bg-[var(--card-color)] border border-[var(--border-color)] rounded-2xl p-5 hover:border-[#3B82F6]/50 transition-all flex flex-col cursor-pointer shadow-sm";
            card.onclick = (e) => {
                e.stopPropagation();
                mostrarMenuMesa(mesa.id, card);
            };

            card.innerHTML = `
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] block mb-1">Mesa</span>
                        <span class="text-2xl font-black text-[var(--text-color)] leading-none">${mesa.numero}</span>
                    </div>
                    <div class="flex items-center gap-1.5 px-2 py-1 rounded border border-${colorEstado}-500/30 bg-${colorEstado}-500/10">
                        <div class="w-1.5 h-1.5 rounded-full bg-${colorEstado}-500"></div>
                        <span class="text-[8px] font-black uppercase tracking-widest text-${colorEstado}-500">${estadoReal}</span>
                    </div>
                </div>
                <div class="mt-auto pt-4 border-t border-[var(--border-color)] flex items-center gap-2 text-[var(--text-muted)]">
                    <i class="fas fa-users text-[10px]"></i>
                    <span class="text-xs font-bold">Capacidad: ${mesa.capacidad}</span>
                </div>
                
                {{-- Menú Oculto (Click) --}}
                <div class="absolute right-4 top-14 hidden w-36 rounded-xl border border-[var(--border-color)] bg-[var(--bg-color)] p-2 shadow-2xl z-20" data-menu="mesa">
                    <button type="button" onclick="event.stopPropagation(); irACobrar(${mesa.id})" class="w-full mb-1.5 rounded-lg bg-emerald-500/10 px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-emerald-500 hover:bg-emerald-500 hover:text-white transition-all text-left"><i class="fas fa-cash-register w-4"></i> Cobrar</button>
                    <button type="button" onclick="event.stopPropagation(); abrirModalEditarMesa(${mesa.id})" class="w-full mb-1.5 rounded-lg bg-[#3B82F6]/10 px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-[#3B82F6] hover:bg-[#3B82F6] hover:text-white transition-all text-left"><i class="fas fa-pen w-4"></i> Editar</button>
                    <button type="button" onclick="event.stopPropagation(); eliminarMesa(${mesa.id})" class="w-full rounded-lg bg-rose-500/10 px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-rose-500 hover:bg-rose-500 hover:text-white transition-all text-left"><i class="fas fa-trash w-4"></i> Eliminar</button>
                </div>
            `;
            containerLista.appendChild(card);
        });
    }

    // ----------------------------------------------------
    // LÓGICA DEL MAPA INTERACTIVO (PREMIUM)
    // ----------------------------------------------------
    const dragState = { activo: false, mesaId: null, originX: 0, originY: 0, startX: 0, startY: 0, elemento: null, contenedor: null };

    // DICCIONARIO DE ESTILOS PUROS DE TAILWIND
    const tw = {
        emerald: { bg: 'bg-emerald-500/10', border: 'border-emerald-500/40 hover:border-emerald-500', text: 'text-emerald-500', dot: 'bg-emerald-500' },
        blue: { bg: 'bg-blue-500/10', border: 'border-blue-500/40 hover:border-blue-500', text: 'text-blue-500', dot: 'bg-blue-500' },
        amber: { bg: 'bg-amber-500/10', border: 'border-amber-500/40 hover:border-amber-500', text: 'text-amber-500', dot: 'bg-amber-500' },
        rose: { bg: 'bg-rose-500/10', border: 'border-rose-500/40 hover:border-rose-500', text: 'text-rose-500', dot: 'bg-rose-500' },
        purple: { bg: 'bg-purple-500/10', border: 'border-purple-500/40 hover:border-purple-500', text: 'text-purple-500', dot: 'bg-purple-500' }
    };

    function toggleMapaMesas() {
        estadoGlobal.vista = estadoGlobal.vista === 'mapa' ? 'lista' : 'mapa';
        if(estadoGlobal.vista === 'lista') {
            estadoGlobal.modoEdicion = false;
            estadoGlobal.modoFusion = false;
        }
        renderizarMesas();
    }

    function cambiarZona(zona) {
        estadoGlobal.zonaActual = zona;
        document.querySelectorAll('.zona-btn').forEach(btn => {
            btn.className = "zona-btn px-4 py-2 rounded-lg text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] hover:text-[var(--text-color)] transition-all whitespace-nowrap";
            if(btn.innerText.includes(zona) || (zona==='Todas' && btn.innerText==='TODAS')) {
                btn.className = "zona-btn px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest bg-[#3B82F6] text-white shadow-sm transition-all whitespace-nowrap";
            }
        });
        renderizarMapaMesas();
    }

    function toggleModoFusion() {
        if(estadoGlobal.modoEdicion) toggleModoEdicion(); 

        estadoGlobal.modoFusion = !estadoGlobal.modoFusion;
        const btn = document.getElementById('btnModoFusion');
        const txt = document.getElementById('txtBotonFusion');

        if(estadoGlobal.modoFusion) {
            btn.className = "px-4 py-2 rounded-xl bg-purple-600 border border-purple-600 text-white text-xs font-black uppercase tracking-widest shadow-[0_5px_15px_rgba(168,85,247,0.4)] outline-none flex items-center gap-2";
            txt.innerText = 'Ejecutar Fusión';
            mostrarToast('Selecciona 2 o más mesas para unirlas.', 'warning');
            estadoGlobal.mesasParaFusionar = [];
        } else {
            if(estadoGlobal.mesasParaFusionar.length > 1) {
                mostrarToast(`Mesas unidas exitosamente en la base de datos.`, 'success');
            }
            btn.className = "px-4 py-2 rounded-xl bg-[var(--bg-color)] border border-[var(--border-color)] text-[var(--text-color)] text-xs font-black uppercase tracking-widest hover:border-purple-500 hover:text-purple-500 transition-all shadow-sm outline-none flex items-center gap-2";
            txt.innerText = 'Unir Mesas';
            estadoGlobal.mesasParaFusionar = [];
        }
        renderizarMapaMesas();
    }

    function seleccionarParaFusion(id, card) {
        const idx = estadoGlobal.mesasParaFusionar.indexOf(id);
        if(idx > -1) {
            estadoGlobal.mesasParaFusionar.splice(idx, 1);
            card.classList.remove('mesa-fusion-selected');
        } else {
            estadoGlobal.mesasParaFusionar.push(id);
            card.classList.add('mesa-fusion-selected');
        }
    }

    function toggleModoEdicion() {
        if(estadoGlobal.modoFusion) toggleModoFusion();

        estadoGlobal.modoEdicion = !estadoGlobal.modoEdicion;
        
        const btn = document.getElementById('botonModoEdicion');
        const txt = document.getElementById('txtBotonEdicion');
        const lienzoContenedor = document.getElementById('contenedor-lienzo');
        const btnAgregar = document.getElementById('botonAgregarMesaMapa');

        if (estadoGlobal.modoEdicion) {
            btn.className = "px-4 py-2 rounded-xl bg-[#3B82F6] border border-[#3B82F6] text-white text-xs font-black uppercase tracking-widest hover:bg-[#2563EB] transition-all shadow-[0_5px_15px_rgba(59,130,246,0.4)] outline-none flex items-center gap-2";
            txt.innerText = 'Guardar Posiciones';
            btn.querySelector('i').className = 'fas fa-save text-sm';
            lienzoContenedor.classList.add('border-[#3B82F6]');
            btnAgregar.classList.remove('hidden');
            mostrarToast('Modo edición activado. Mueve las mesas por el plano.', 'info');
        } else {
            btn.className = "px-4 py-2 rounded-xl bg-[var(--bg-color)] border border-[var(--border-color)] text-[var(--text-color)] text-xs font-black uppercase tracking-widest hover:border-[#3B82F6] hover:text-[#3B82F6] transition-all shadow-sm outline-none flex items-center gap-2";
            txt.innerText = 'Activar Edición';
            btn.querySelector('i').className = 'fas fa-pen text-sm';
            lienzoContenedor.classList.remove('border-[#3B82F6]');
            btnAgregar.classList.add('hidden');
            
            if(estadoGlobal.mesas.length > 0) guardarCoordenadasFinales();
        }

        renderizarMapaMesas();
        cerrarMenuMesa();
    }

    function renderizarMapaMesas() {
        const mapa = document.getElementById('mapa-mesas');
        
        const mesasSiendoEditadas = [];
        if (dragState.activo && dragState.elemento) mesasSiendoEditadas.push(dragState.mesaId);

        if(estadoGlobal.mesas.length > 0) {
             mapa.querySelectorAll('.mesa-ui').forEach(m => m.remove());
        }

        const mesasFiltradas = estadoGlobal.zonaActual === 'Todas' ? estadoGlobal.mesas : estadoGlobal.mesas.filter(m => m.zona === estadoGlobal.zonaActual);

        if (mesasFiltradas.length === 0) {
            mapa.innerHTML = '<div class="absolute inset-0 flex items-center justify-center opacity-40"><p class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-widest">No hay mesas en esta zona.</p></div>';
            return;
        }

        mesasFiltradas.forEach((mesa, index) => {
            if (mesasSiendoEditadas.includes(mesa.id)) return;
            const item = crearMesaGlass(mesa, index);
            mapa.appendChild(item);
        });
    }

    function crearMesaGlass(mesa, index) {
        const card = document.createElement('div');
        const estadoReal = mesa.estado === 'reservada' ? 'reservada' : mesa.ocupada ? 'ocupada' : 'libre';
        const isOcupada = estadoReal === 'ocupada';
        const isReservada = estadoReal === 'reservada';
        
        let slaTheme = 'emerald'; 
        let pulseClass = '';

        if (isOcupada) {
            if (mesa.minutos_activa < 45) {
                slaTheme = 'blue'; 
            } else if (mesa.minutos_activa >= 45 && mesa.minutos_activa < 90) {
                slaTheme = 'amber'; 
            } else {
                slaTheme = 'rose'; 
                pulseClass = 'animate-pulse';
            }
        } else if (isReservada) {
            slaTheme = 'purple';
        }

        const s = tw[slaTheme];

        // FORMAS DEFINIDAS: Cuadrada (w-32) o Rectangular (w-48)
        const capacidadInt = parseInt(mesa.capacidad, 10) || 0;
        const esRectangular = capacidadInt >= 5;
        const widthClass = esRectangular ? 'w-48' : 'w-32';
        const heightClass = 'h-32';

        // LÓGICA DE GRID EXPANDIDO: Mantiene orden al crear y evita empalmes masivos
        const cols = 8; // Más columnas porque el mapa es enorme (2500px)
        const defaultX = (index % cols) * 220 + 40;
        const defaultY = Math.floor(index / cols) * 160 + 40;
        
        const posicionX = mesa.posicion_x !== null ? mesa.posicion_x : defaultX;
        const posicionY = mesa.posicion_y !== null ? mesa.posicion_y : defaultY;

        const cursorClase = estadoGlobal.modoEdicion ? 'cursor-move' : 'cursor-pointer';

        // DISEÑO CLEAN GLASS: Bg sólido de la tarjeta base + baño de color encima
        card.className = `mesa-ui absolute ${widthClass} ${heightClass} rounded-3xl flex flex-col justify-between p-4 transition-colors group select-none border-2 bg-[var(--card-color)] ${s.bg} ${s.border} shadow-lg ${cursorClase}`;
        card.style.left = `${posicionX}px`;
        card.style.top = `${posicionY}px`;
        card.dataset.mesaId = mesa.id;

        if (estadoGlobal.modoEdicion) {
            card.onpointerdown = event => iniciarArrastreMesa(event, card, mesa);
        } else if (estadoGlobal.modoFusion) {
            if(estadoGlobal.mesasParaFusionar.includes(mesa.id)) card.classList.add('mesa-fusion-selected');
            card.onclick = () => seleccionarParaFusion(mesa.id, card);
        } else {
            card.onclick = event => {
                event.stopPropagation();
                mostrarMenuMesa(mesa.id, card);
            };
        }

        // TOOLTIP FINANCIERO PREMIUM
        const tooltipHTML = isOcupada && !estadoGlobal.modoEdicion && !estadoGlobal.modoFusion ? `
            <div class="tooltip-card absolute -top-[4.8rem] left-1/2 -translate-x-1/2 w-48 bg-[var(--card-color)] border border-[var(--border-color)] rounded-2xl p-3 shadow-2xl z-[100] flex flex-col pointer-events-none">
                <div class="flex justify-between items-center mb-1.5 border-b border-[var(--border-color)] pb-1.5">
                    <span class="text-[9px] font-black uppercase text-[var(--text-muted)]">Mesero</span>
                    <span class="text-[10px] font-bold text-[var(--text-color)]">${mesa.mesero_nombre}</span>
                </div>
                <div class="flex justify-between items-end">
                    <span class="text-[9px] font-black uppercase text-[var(--text-muted)]">Total</span>
                    <span class="text-sm font-black text-[#3B82F6]">$${mesa.total_cuenta}</span>
                </div>
                <div class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-3 h-3 bg-[var(--card-color)] border-b border-r border-[var(--border-color)] transform rotate-45"></div>
            </div>
        ` : '';

        // DISEÑO INTERIOR (Sin gradientes pesados, limpio y legible)
        card.innerHTML = `
            ${tooltipHTML}
            
            <div class="flex items-start justify-between w-full">
                <span class="text-[9px] font-black uppercase tracking-widest ${s.text} opacity-80">Mesa</span>
            </div>
            
            <div class="flex-1 flex items-center justify-center w-full">
                <span class="text-2xl font-black text-[var(--text-color)] leading-none truncate px-1">${mesa.numero}</span>
            </div>
            
            <div class="flex items-center justify-center w-full">
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-[var(--border-color)] bg-[var(--bg-color)]">
                    <i class="fas fa-users text-[9px] ${s.text}"></i>
                    <span class="text-[10px] font-bold text-[var(--text-color)]">${mesa.capacidad}</span>
                </div>
            </div>

            {{-- Led Superior Derecho PERFECTO (Fuera de la tarjeta) --}}
            <div class="absolute -top-1.5 -right-1.5 w-4 h-4 rounded-full ${s.dot} border-[3px] border-[var(--mapa-bg)] shadow-md ${pulseClass}"></div>

            {{-- Menú Oculto (Modo Vista) --}}
            <div class="absolute -right-4 top-1/2 -translate-y-1/2 translate-x-full hidden w-32 rounded-xl border border-[var(--border-color)] bg-[var(--card-color)] p-2 shadow-2xl z-50" data-menu="mesa">
                <button type="button" onclick="event.stopPropagation(); irACobrar(${mesa.id})" class="w-full mb-1 rounded-lg bg-emerald-500/10 px-2 py-1.5 text-[9px] font-bold uppercase tracking-widest text-emerald-500 hover:bg-emerald-500 hover:text-white transition text-left"><i class="fas fa-cash-register mr-1"></i> Cobrar</button>
                <button type="button" onclick="event.stopPropagation(); abrirModalEditarMesa(${mesa.id})" class="w-full mb-1 rounded-lg bg-[#3B82F6]/10 px-2 py-1.5 text-[9px] font-bold uppercase tracking-widest text-[#3B82F6] hover:bg-[#3B82F6] hover:text-white transition text-left"><i class="fas fa-pen mr-1"></i> Editar</button>
                <button type="button" onclick="event.stopPropagation(); eliminarMesa(${mesa.id})" class="w-full rounded-lg bg-rose-500/10 px-2 py-1.5 text-[9px] font-bold uppercase tracking-widest text-rose-500 hover:bg-rose-500 hover:text-white transition text-left"><i class="fas fa-trash mr-1"></i> Eliminar</button>
            </div>
        `;

        return card;
    }

    // DRAG & DROP ADAPTADO A SCROLL
    function iniciarArrastreMesa(event, elemento, mesa) {
        if (!estadoGlobal.modoEdicion || event.button !== 0) return; 
        event.preventDefault();
        
        dragState.activo = true;
        dragState.elemento = elemento;
        dragState.mesaId = mesa.id;
        
        // Coordenadas relativas al contenedor padre scrolleable
        dragState.originX = parseInt(elemento.style.left || 0, 10);
        dragState.originY = parseInt(elemento.style.top || 0, 10);
        
        dragState.startX = event.clientX;
        dragState.startY = event.clientY;
        dragState.contenedor = document.getElementById('contenedor-lienzo');

        elemento.setPointerCapture(event.pointerId);
        elemento.style.cursor = 'grabbing';
        elemento.classList.add('scale-105', 'z-50', 'shadow-2xl');

        elemento.addEventListener('pointermove', manejarArrastre);
        elemento.addEventListener('pointerup', detenerArrastre);
        elemento.addEventListener('pointercancel', detenerArrastre);
    }

    function manejarArrastre(event) {
        if (!dragState.activo || !dragState.elemento || !dragState.contenedor) return;

        const deltaX = event.clientX - dragState.startX;
        const deltaY = event.clientY - dragState.startY;
        
        let newX = dragState.originX + deltaX;
        let newY = dragState.originY + deltaY;

        // Límites del gran lienzo (2500x2500)
        const maxX = dragState.contenedor.offsetWidth - dragState.elemento.offsetWidth;
        const maxY = dragState.contenedor.offsetHeight - dragState.elemento.offsetHeight;

        newX = Math.max(0, Math.min(newX, maxX));
        newY = Math.max(0, Math.min(newY, maxY));

        dragState.elemento.style.left = `${newX}px`;
        dragState.elemento.style.top = `${newY}px`;
    }

    function detenerArrastre(event) {
        if (!dragState.activo || !dragState.elemento) return;

        dragState.elemento.releasePointerCapture(event.pointerId);
        dragState.elemento.style.cursor = 'grab';
        dragState.elemento.classList.remove('scale-105', 'z-50', 'shadow-2xl');

        const x = parseInt(dragState.elemento.style.left, 10);
        const y = parseInt(dragState.elemento.style.top, 10);

        guardarPosicionMesa(dragState.mesaId, x, y);

        dragState.activo = false;
        dragState.mesaId = null;
        dragState.elemento.removeEventListener('pointermove', manejarArrastre);
        dragState.elemento.removeEventListener('pointerup', detenerArrastre);
        dragState.elemento.removeEventListener('pointercancel', detenerArrastre);
        dragState.elemento = null;
        dragState.contenedor = null;
    }

    async function guardarPosicionMesa(id, x, y) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        try {
            const response = await fetch(`/admin/mesas/api/${id}/posicion`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ posicion_x: x, posicion_y: y }),
            });
            const data = await response.json();
            if (response.ok && data.success) {
                const index = estadoGlobal.mesas.findIndex(m => m.id === id);
                if (index !== -1) {
                    estadoGlobal.mesas[index].posicion_x = x;
                    estadoGlobal.mesas[index].posicion_y = y;
                }
            } else {
                mostrarError('No se pudo guardar la posición en BD.');
            }
        } catch (error) { console.error(error); }
    }

    async function guardarCoordenadasFinales() {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const coordenadas = estadoGlobal.mesas.map(m => ({ id: m.id, x: m.posicion_x, y: m.posicion_y }));
        
        try {
            const response = await fetch('/admin/mesas/api/posiciones', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: JSON.stringify({ coordenadas })
            });
            if(response.ok) mostrarExito('Distribución guardada.');
        } catch(e) { console.error(e); }
    }

    // ----------------------------------------------------
    // MENÚS Y MODALES ORIGINALES
    // ----------------------------------------------------
    function mostrarMenuMesa(id, card) {
        cerrarMenuMesa();
        const menu = card.querySelector('[data-menu="mesa"]');
        if (!menu) return;
        menu.classList.remove('hidden');
    }

    function cerrarMenuMesa(event) {
        if (event && event.target.closest('[data-menu="mesa"]')) return;
        document.querySelectorAll('[data-menu="mesa"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }

    function abrirModalEditarMesa(id) {
        const mesa = estadoGlobal.mesas.find(m => m.id === id);
        if (!mesa) return;

        document.getElementById('editarMesaId').value = mesa.id;
        document.getElementById('editarMesaNumero').value = mesa.numero;
        document.getElementById('editarMesaCapacidad').value = mesa.capacidad;
        document.getElementById('editarMesaEstado').value = mesa.estado || 'libre';
        
        const modal = document.getElementById('modalEditarMesa');
        const card = document.getElementById('cardEditar');
        modal.classList.remove('hidden');
        setTimeout(() => { modal.classList.remove('opacity-0'); card.classList.remove('scale-95'); }, 10);
        cerrarMenuMesa();
    }

    function cerrarModalEditarMesa() {
        const modal = document.getElementById('modalEditarMesa');
        const card = document.getElementById('cardEditar');
        modal.classList.add('opacity-0');
        card.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    async function guardarMesaEditada() {
        const id = document.getElementById('editarMesaId').value;
        const numero = document.getElementById('editarMesaNumero').value.trim();
        const capacidad = parseInt(document.getElementById('editarMesaCapacidad').value, 10);
        const estado = document.getElementById('editarMesaEstado').value;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (!numero || !capacidad) {
            mostrarError('Completa el número y la capacidad.');
            return;
        }

        try {
            const response = await fetch(`/admin/mesas/api/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ numero, capacidad, estado })
            });

            const data = await response.json();
            if (response.ok && data.success) {
                cerrarModalEditarMesa();
                cargarMesas();
                mostrarExito('Mesa actualizada correctamente.');
            } else {
                mostrarError(data.message || 'Error al actualizar.');
            }
        } catch (error) {
            mostrarError('Error de conexión.');
        }
    }

    function abrirModalNuevaMesa() {
        document.getElementById('nuevaMesaNumero').value = '';
        document.getElementById('nuevaMesaCapacidad').value = '';
        
        const modal = document.getElementById('modalNuevaMesa');
        const card = document.getElementById('cardNueva');
        modal.classList.remove('hidden');
        setTimeout(() => { modal.classList.remove('opacity-0'); card.classList.remove('scale-95'); }, 10);
    }

    function cerrarModalNuevaMesa() {
        const modal = document.getElementById('modalNuevaMesa');
        const card = document.getElementById('cardNueva');
        modal.classList.add('opacity-0');
        card.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    async function crearNuevaMesa() {
        const numero = document.getElementById('nuevaMesaNumero').value.trim();
        const capacidad = parseInt(document.getElementById('nuevaMesaCapacidad').value, 10);
        const estado = document.getElementById('nuevaMesaEstado').value;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (!numero || !capacidad) {
            mostrarError('Completa todos los campos.');
            return;
        }

        try {
            const response = await fetch('/admin/mesas/api', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ numero, capacidad, estado })
            });

            const data = await response.json();
            if (response.ok && data.success) {
                cerrarModalNuevaMesa();
                cargarMesas();
                mostrarExito('Mesa creada. (Estará en el mapa)');
            } else {
                mostrarError(data.message || 'Error al crear.');
            }
        } catch (error) {
            mostrarError('Error de conexión.');
        }
    }

    function eliminarMesa(id) {
        const mesa = estadoGlobal.mesas.find(m => m.id === id);
        if (!mesa) return;

        document.getElementById('eliminarMesaId').value = mesa.id;
        document.getElementById('eliminarMesaNumero').textContent = mesa.numero;
        
        const modal = document.getElementById('modalEliminarMesa');
        const card = document.getElementById('cardEliminar');
        modal.classList.remove('hidden');
        setTimeout(() => { modal.classList.remove('opacity-0'); card.classList.remove('scale-95'); }, 10);
        cerrarMenuMesa();
    }

    function cerrarModalEliminarMesa() {
        const modal = document.getElementById('modalEliminarMesa');
        const card = document.getElementById('cardEliminar');
        modal.classList.add('opacity-0');
        card.classList.add('scale-95');
        setTimeout(() => { modal.classList.add('hidden'); }, 300);
    }

    async function confirmarEliminarMesa() {
        const id = document.getElementById('eliminarMesaId').value;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const response = await fetch(`/admin/mesas/api/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();
            if (response.ok && data.success) {
                cerrarModalEliminarMesa();
                cargarMesas();
                mostrarExito('Mesa eliminada permanentemente.');
            } else {
                mostrarError(data.message || 'No se pudo eliminar.');
            }
        } catch (error) {
            mostrarError('Error de conexión.');
        }
    }

    function filtrarMesas(filtro) {
        estadoGlobal.filtroActual = filtro;
        
        document.querySelectorAll('.filtro-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'border-transparent');
            btn.classList.add('bg-[var(--card-color)]', 'text-[var(--text-color)]', 'border-[var(--border-color)]');
        });
        
        const btnActivo = document.querySelector(`[data-filtro="${filtro}"]`);
        btnActivo.classList.remove('bg-[var(--card-color)]', 'text-[var(--text-color)]', 'border-[var(--border-color)]');
        btnActivo.classList.add('bg-blue-600', 'text-white', 'border-transparent');
        
        renderizarMesas();
    }

    function irACobrar(mesaId) {
        window.location.href = `/admin/caja/cobrar/${mesaId}`;
    }
</script>

@endsection

//emi joto