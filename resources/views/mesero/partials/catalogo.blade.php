{{-- ========================================== --}}
{{-- COLUMNA 3: CATÁLOGO DE PRODUCTOS (FLEX)    --}}
{{-- ========================================== --}}
<main class="flex-1 min-w-0 flex flex-col bg-[var(--bg-base)] relative overflow-hidden">
    <div class="px-6 py-4 flex gap-2 overflow-x-auto hide-scroll relative z-10 border-b border-[var(--border-color)] bg-[var(--bg-panel)] flex-shrink-0 shadow-sm" id="menuCategorias"></div>
    <div class="flex-1 p-6 overflow-y-auto hide-scroll grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-5 content-start relative z-10 bg-[var(--bg-base)]" onclick="deseleccionarTicket()" id="gridProductos"></div>

    <div id="barraModificadores" class="hidden h-[70px] bg-[var(--bg-panel)] border-t border-[var(--border-color)] flex items-center px-6 relative z-10 transition-all flex-shrink-0 shadow-[0_-10px_30px_rgba(0,0,0,0.05)]">
        <span class="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] mr-4 whitespace-nowrap">Opciones:</span>
        <div id="contenedorBotonesModificadores" class="flex gap-2 overflow-x-auto hide-scroll flex-1 items-center"></div>
        <button type="button" onclick="deseleccionarTicket()" class="ml-4 w-8 h-8 rounded-full flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--hover-bg)] transition-all outline-none">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>
</main>