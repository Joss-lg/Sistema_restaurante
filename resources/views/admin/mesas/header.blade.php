{{-- CABECERA COMPACTA --}}
<div class="flex justify-between items-center flex-shrink-0 animate-[slideUpFade_0.3s_ease-out]">
    <div>
        <h1 class="text-xl sm:text-2xl font-black text-[var(--text-color)] tracking-tight leading-none">Plano Espacial</h1>
    </div>
    <div class="flex gap-2">
        <button onclick="toggleMapaMesas()" 
                id="btnVistaPrincipal" 
                class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-gradient-to-r from-[#3B82F6] to-[#2563EB] hover:-translate-y-0.5 text-white shadow-md flex items-center gap-2 outline-none">
            <i class="fas fa-list"></i> <span class="hidden sm:inline">Ver Lista</span>
        </button>
    </div>
</div>