{{-- FILTROS Y CONTROLES (Vista Lista) --}}
<div id="controlesLista" class="hidden flex-col lg:flex-row gap-3 justify-between items-start flex-shrink-0">
    <div class="flex gap-2 flex-wrap p-1 rounded-xl bg-[var(--card-color)] border border-[var(--border-color)]">
        <button onclick="filtrarMesas('todos')" 
                class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-blue-600 text-white shadow-sm filtro-btn" 
                data-filtro="todos">
            Todas
        </button>
        <button onclick="filtrarMesas('libre')" 
                class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-transparent text-[var(--text-muted)] hover:text-[var(--text-color)] filtro-btn" 
                data-filtro="libre">
            Libres
        </button>
        <button onclick="filtrarMesas('ocupada')" 
                class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-transparent text-[var(--text-muted)] hover:text-[var(--text-color)] filtro-btn" 
                data-filtro="ocupada">
            Ocupadas
        </button>
        <button onclick="filtrarMesas('reservada')" 
                class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all bg-transparent text-[var(--text-muted)] hover:text-[var(--text-color)] filtro-btn" 
                data-filtro="reservada">
            Reservadas
        </button>
    </div>
</div>