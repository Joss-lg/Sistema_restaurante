{{-- resources/views/admin/categorias/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Categorías | Ollintem Pro')

@section('content')

<style>
    /* ==========================================================
       VARIABLES DE ALTA GAMA (Apple / Vercel Tier)
       ========================================================== */
    .view-container {
        --glass-bg: rgba(24, 24, 27, 0.65); /* Zinc 900 translúcido */
        --glass-border-top: rgba(255, 255, 255, 0.08);
        --glass-border-bot: rgba(0, 0, 0, 0.4);
        --glass-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        --glow-primary: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.15) 0%, transparent 50%);
        --glow-secondary: radial-gradient(circle at 100% 100%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);
        --text-hero: #ffffff;
        --text-body: #a1a1aa; /* Zinc 400 */
        --card-inner-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
        --search-bg: rgba(0, 0, 0, 0.3);
        --search-border: rgba(255, 255, 255, 0.05);
        --search-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5);
    }

    body.modo-crema .view-container {
        --glass-bg: rgba(255, 255, 255, 0.7); /* Blanco translúcido */
        --glass-border-top: rgba(255, 255, 255, 1);
        --glass-border-bot: rgba(0, 0, 0, 0.04);
        --glass-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05);
        --glow-primary: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.06) 0%, transparent 60%);
        --glow-secondary: radial-gradient(circle at 100% 100%, rgba(16, 185, 129, 0.04) 0%, transparent 60%);
        --text-hero: #0f172a; /* Slate 900 */
        --text-body: #64748b; /* Slate 500 */
        --card-inner-shadow: inset 0 1px 0 rgba(255, 255, 255, 1);
        --search-bg: rgba(248, 250, 252, 0.8);
        --search-border: rgba(0, 0, 0, 0.06);
        --search-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    /* Contenedor de cristal perfecto (Glassmorphism + Bezel) */
    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border-style: solid;
        border-width: 1px;
        border-image-source: linear-gradient(to bottom, var(--glass-border-top), var(--glass-border-bot));
        border-image-slice: 1;
        box-shadow: var(--glass-shadow), var(--card-inner-shadow);
        border-radius: inherit; /* Tailwind toma el control del radius */
    }

    /* Para que el border-image no rompa el border-radius de Tailwind, usamos un truco de pseudo-elemento */
    .glass-panel {
        position: relative;
        background: var(--glass-bg);
        backdrop-filter: blur(30px);
        -webkit-backdrop-filter: blur(30px);
        box-shadow: var(--glass-shadow);
    }
    .glass-panel::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: 1px;
        background: linear-gradient(to bottom, var(--glass-border-top), var(--glass-border-bot));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }

    /* Botón Premium (Zafiro) */
    .btn-sapphire {
        background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.3), 
                    inset 0 -2px 4px rgba(0, 0, 0, 0.2), 
                    0 8px 20px -6px rgba(37, 99, 235, 0.4);
        border: 1px solid #1d4ed8;
    }
    .btn-sapphire:hover {
        background: linear-gradient(180deg, #4f46e5 0%, #3b82f6 100%);
        box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.4), 
                    inset 0 -2px 4px rgba(0, 0, 0, 0.2), 
                    0 12px 25px -6px rgba(37, 99, 235, 0.5);
    }
    
    /* Input Search Hundido */
    .search-inset {
        background: var(--search-bg);
        border: 1px solid var(--search-border);
        box-shadow: var(--search-shadow);
    }
</style>

<div class="view-container p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col relative z-0">

    {{-- ======================================================== --}}
    {{-- HERO BANNER (MESH GRADIENT + GLASSMORPHISM) --}}
    {{-- ======================================================== --}}
    <div class="glass-panel rounded-[32px] overflow-hidden transition-all duration-500">
        
        {{-- Luces Atmosféricas (Mesh Gradient) --}}
        <div class="absolute inset-0 z-0" style="background: var(--glow-primary);"></div>
        <div class="absolute inset-0 z-0" style="background: var(--glow-secondary);"></div>
        
        <div class="relative z-10 p-8 lg:p-12 xl:p-14 space-y-10">
            
            <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-10">
                
                {{-- Textos --}}
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-500/20 bg-blue-500/10 px-4 py-1.5 backdrop-blur-sm mb-6 shadow-inner">
                        <i class="fas fa-layer-group text-[10px] text-blue-500"></i>
                        <span class="text-[9px] font-black uppercase tracking-[0.3em] text-blue-500/90">Categorías Premium</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-black tracking-tighter text-[var(--text-hero)] leading-[1.1] transition-colors duration-300">
                        Gestión de Categorías
                    </h1>
                    <p class="mt-5 max-w-2xl text-[15px] font-medium leading-relaxed text-[var(--text-body)] transition-colors duration-300">
                        Diseña y organiza el menú de tu restaurante con un tablero visual arquitectónico. Rápido, preciso y con toda la información clave al primer vistazo.
                    </p>
                </div>
                
                {{-- Controles (Buscar y Crear) --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 w-full xl:w-auto shrink-0">
                    
                    {{-- Buscador Premium Hundido --}}
                    <div class="relative w-full sm:w-[280px]">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[var(--text-body)] opacity-60">
                            <i class="fas fa-search text-[13px]"></i>
                        </div>
                        <input type="text" id="buscadorCategorias" placeholder="Buscar categoría..."
                            class="w-full h-12 rounded-[16px] search-inset pl-10 pr-4 text-[13px] font-semibold text-[var(--text-hero)] placeholder:text-[var(--text-body)] placeholder:opacity-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all" />
                    </div>
                    
                    {{-- Botón Zafiro Ultra Premium --}}
                    <button onclick="openModalCrear()"
                        class="btn-sapphire inline-flex items-center justify-center gap-3 rounded-[16px] px-8 py-0 h-12 text-[11px] font-black uppercase tracking-[0.15em] text-white transition-all duration-300 transform active:scale-95 outline-none">
                        <i class="fas fa-plus"></i> Crear Categoría
                    </button>
                </div>
            </div>

            {{-- Tarjetas de Métricas Arquitectónicas --}}
            <div class="grid gap-5 sm:grid-cols-3 pt-4">
                <div class="glass-panel rounded-[24px] p-6 transition-all hover:-translate-y-1 hover:shadow-2xl group">
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-[var(--text-body)] opacity-60 group-hover:text-blue-500 group-hover:opacity-100 transition-colors">Total Categorías</span>
                    <p class="mt-4 text-[42px] leading-none font-black text-[var(--text-hero)] tracking-tighter">{{ count($categorias) }}</p>
                    <p class="mt-2 text-[11px] font-medium text-[var(--text-body)] opacity-70">Bloques registrados en el menú.</p>
                </div>
                <div class="glass-panel rounded-[24px] p-6 transition-all hover:-translate-y-1 hover:shadow-2xl group">
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-[var(--text-body)] opacity-60 group-hover:text-emerald-500 group-hover:opacity-100 transition-colors">Platillos Activos</span>
                    <p class="mt-4 text-[42px] leading-none font-black text-[var(--text-hero)] tracking-tighter">{{ $categorias->sum('productos_count') }}</p>
                    <p class="mt-2 text-[11px] font-medium text-[var(--text-body)] opacity-70">Asignados a través del sistema.</p>
                </div>
                <div class="glass-panel rounded-[24px] p-6 transition-all hover:-translate-y-1 hover:shadow-2xl group">
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-[var(--text-body)] opacity-60 group-hover:text-purple-500 group-hover:opacity-100 transition-colors">Jerarquía</span>
                    <p class="mt-4 text-[42px] leading-none font-black text-[var(--text-hero)] tracking-tighter">{{ $categorias->max('orden_visualizacion') ?? 0 }}</p>
                    <p class="mt-2 text-[11px] font-medium text-[var(--text-body)] opacity-70">Orden de visualización máximo.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- TABLA DE CONTENIDO (CRISTAL) --}}
    {{-- ======================================================== --}}
    <div class="glass-panel rounded-[32px] overflow-hidden transition-colors duration-500 mt-6">
        
        <div class="flex flex-col gap-4 p-8 border-b border-[var(--glass-border-bot)] sm:flex-row sm:items-center sm:justify-between relative z-10">
            <div>
                <h2 class="text-xl font-black text-[var(--text-hero)] tracking-tight">Catálogo General</h2>
                <p class="mt-1 text-[13px] font-medium text-[var(--text-body)] opacity-80">Inventario y organización de tus categorías.</p>
            </div>
            
            <div class="flex items-center gap-4 bg-[var(--search-bg)] border border-[var(--search-border)] rounded-[14px] px-5 py-2.5 shadow-[var(--search-shadow)]">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-body)] opacity-70">Total Registradas</span>
                <div class="w-[1px] h-4 bg-[var(--text-body)] opacity-20"></div>
                <span class="text-lg font-black text-[var(--text-hero)]">{{ count($categorias) }}</span>
            </div>
        </div>

        <div class="overflow-x-auto relative z-10">
            <table class="min-w-full border-collapse text-left">
                <thead class="bg-[var(--glass-bg)] border-b border-[var(--glass-border-bot)] backdrop-blur-md">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.25em] text-[var(--text-body)] opacity-60">Categoría</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.25em] text-[var(--text-body)] opacity-60">Identificador</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.25em] text-[var(--text-body)] opacity-60 text-center">Prioridad</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.25em] text-[var(--text-body)] opacity-60">Contenido</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.25em] text-[var(--text-body)] opacity-60 text-right">Ajustes</th>
                    </tr>
                </thead>
                <tbody id="tablaCategorias" class="divide-y divide-[var(--glass-border-bot)] bg-transparent">
                    @forelse($categorias as $categoria)
                    <tr class="fila-categoria group transition-all duration-300 hover:bg-[var(--glass-border-top)]">
                        
                        <td class="px-8 py-5 nombre-celda">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-[14px] border border-[var(--glass-border-bot)] shadow-inner flex items-center justify-center transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 bg-[var(--search-bg)] relative overflow-hidden">
                                     {{-- Resplandor de color de la categoría --}}
                                     <div class="absolute inset-0 opacity-20" style="background-color: {{ $categoria->color ?? '#3B82F6' }}"></div>
                                     <span class="text-xl font-black relative z-10" style="color: {{ $categoria->color ?? '#3B82F6' }}">{{ substr($categoria->nombre, 0, 1) }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[15px] font-bold text-[var(--text-hero)] tracking-tight">{{ $categoria->nombre }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="px-8 py-5">
                            <span class="inline-flex items-center rounded-lg bg-[var(--search-bg)] border border-[var(--search-border)] px-3 py-1.5 text-[11px] font-bold text-[var(--text-body)] shadow-inner">
                                {{ $categoria->slug }}
                            </span>
                        </td>

                        <td class="px-8 py-5 text-center">
                            <span class="text-[14px] font-black text-[var(--text-hero)]">{{ $categoria->orden_visualizacion ?? 0 }}</span>
                        </td>

                        <td class="px-8 py-5">
                            <span class="inline-flex items-center rounded-lg bg-blue-500/10 border border-blue-500/20 px-3 py-1.5 text-[11px] font-black text-blue-500 shadow-sm">
                                {{ $categoria->productos_count ?? $categoria->productos()->count() }} Platillos
                            </span>
                        </td>

                        <td class="px-8 py-5 text-right">
                            <div class="inline-flex items-center justify-end gap-2">
                                <button type="button" title="Editar"
                                    onclick="abrirModalEspecifico('modalEditar-{{ $categoria->id }}')"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-[12px] border border-[var(--search-border)] bg-[var(--search-bg)] text-[var(--text-body)] transition-all hover:bg-blue-500 hover:text-white hover:border-transparent hover:shadow-[0_4px_15px_rgba(59,130,246,0.4)] outline-none active:scale-95 shadow-inner">
                                    <i class="fas fa-pen text-[11px]"></i>
                                </button>
                                <button type="button" title="Eliminar"
                                    onclick="confirmarEliminacion('{{ $categoria->id }}', '{{ $categoria->nombre }}')"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-[12px] border border-[var(--search-border)] bg-[var(--search-bg)] text-[var(--text-body)] transition-all hover:bg-red-500 hover:text-white hover:border-transparent hover:shadow-[0_4px_15px_rgba(239,68,68,0.4)] outline-none active:scale-95 shadow-inner">
                                    <i class="fas fa-trash-alt text-[11px]"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @include('admin.categorias.modal-editar', ['categoria' => $categoria])
                    
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-24 text-center">
                            <div class="mx-auto flex max-w-md flex-col items-center gap-4">
                                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-[var(--search-bg)] border border-[var(--search-border)] text-[var(--text-body)] shadow-inner opacity-60">
                                    <i class="fas fa-folder-open text-3xl"></i>
                                </div>
                                <p class="text-base font-black text-[var(--text-hero)] tracking-tight">Tu catálogo está vacío.</p>
                                <p class="text-[13px] font-medium text-[var(--text-body)] mt-[-10px] opacity-70">Crea tu primera categoría para organizar tu menú.</p>
                                <button onclick="openModalCrear()" class="mt-4 rounded-[14px] bg-[var(--text-hero)] text-[var(--bg-base)] px-8 py-3.5 text-[11px] font-black uppercase tracking-widest hover:opacity-80 transition-opacity outline-none shadow-xl">
                                    Comenzar ahora
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script>
    document.getElementById('buscadorCategorias')?.addEventListener('input', function () {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('.fila-categoria').forEach(fila => {
            const nombre = fila.querySelector('.nombre-celda')?.textContent.toLowerCase();
            fila.style.display = nombre.includes(term) ? '' : 'none';
        });
    });

    // Modales
    function abrirModalEspecifico(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        const container = modal.querySelector('div[id^="modalContainer-"]');
        modal.classList.remove('hidden'); modal.classList.add('flex');
        setTimeout(() => { if (container) { container.classList.remove('scale-95', 'opacity-0'); container.classList.add('scale-100', 'opacity-100'); } }, 10);
    }
    function cerrarModalEspecifico(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        const container = modal.querySelector('div[id^="modalContainer-"]');
        if (container) { container.classList.remove('scale-100', 'opacity-100'); container.classList.add('scale-95', 'opacity-0'); }
        setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 200);
    }
    function openModalCrear() {
        const modal = document.getElementById('modalCrear');
        const container = document.getElementById('createContainer');
        if (!modal || !container) return;
        modal.classList.remove('hidden'); modal.classList.add('flex');
        setTimeout(() => { container.classList.remove('scale-95', 'opacity-0'); container.classList.add('scale-100', 'opacity-100'); }, 10);
    }
    function closeCreateModal() {
        const modal = document.getElementById('modalCrear');
        const container = document.getElementById('createContainer');
        if (container) { container.classList.remove('scale-100', 'opacity-100'); container.classList.add('scale-95', 'opacity-0'); }
        setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 200);
    }
    function confirmarEliminacion(id, nombre) {
        const modal = document.getElementById('modalEliminar');
        const container = document.getElementById('deleteContainer');
        const form = document.getElementById('formEliminar');
        const display = document.getElementById('delete_nombre_display');
        if (!modal || !container) return;
        if (display) display.innerText = nombre;
        if (form) form.action = `/admin/categorias/${id}`;
        modal.classList.remove('hidden'); modal.classList.add('flex');
        setTimeout(() => { container.classList.remove('scale-95', 'opacity-0'); container.classList.add('scale-100', 'opacity-100'); }, 10);
    }
    function closeDeleteModal() {
        const modal = document.getElementById('modalEliminar');
        const container = document.getElementById('deleteContainer');
        if (container) { container.classList.remove('scale-100', 'opacity-100'); container.classList.add('scale-95', 'opacity-0'); }
        setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 200);
    }
</script>

@include('admin.categorias.modal-crear')
@include('admin.categorias.modal-eliminar')

@endsection