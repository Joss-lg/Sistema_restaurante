<aside class="w-[320px] h-full flex flex-col border-r border-[var(--border-color)] bg-[var(--bg-panel)] z-20 transition-colors duration-400">
    
    <div class="p-6 flex justify-between items-center border-b border-[var(--border-color)]">
        <h2 class="text-[11px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em]">Mis Mesas Activas</h2>
        <div class="w-7 h-7 rounded-lg border border-[var(--border-color)] bg-[var(--bg-base)] flex items-center justify-center text-[10px] font-black text-[var(--text-main)] shadow-sm">
            0
        </div>
    </div>

    {{-- Estado Vacío (Mucho más visible y elegante) --}}
    <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
        <div class="relative mb-8">
            {{-- Anillos de fondo sutiles --}}
            <div class="absolute inset-0 border border-[var(--border-color)] rounded-full scale-150 opacity-50"></div>
            <div class="absolute inset-0 border border-[var(--border-color)] rounded-full scale-125 opacity-70"></div>
            
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-white/5 to-transparent border border-white/10 flex items-center justify-center shadow-inner relative z-10 backdrop-blur-sm">
                <i class="fas fa-receipt text-4xl text-[var(--text-muted)] drop-shadow-md"></i>
            </div>
        </div>
        
        <h3 class="text-lg font-black text-[var(--text-main)] tracking-tight mb-2">Bandeja Vacía</h3>
        <p class="text-xs text-[var(--text-muted)] font-medium leading-relaxed max-w-[200px]">
            Inicia una nueva comanda desde el panel central.
        </p>
    </div>

</aside>