<footer class="h-[90px] flex-none border-t border-[var(--border-color)] glass-panel flex items-center justify-center relative z-30 transition-colors duration-400">
    
    @php
        $totalMesas = isset($mesas) ? $mesas->count() : 0;
        $mesasActivas = isset($mesas) ? $mesas->where('estado', 'ocupada')->count() : 0;
    @endphp

    <div class="flex w-full max-w-3xl justify-between px-10">
        
        {{-- Stat 1 --}}
        <div class="flex flex-col items-center justify-center w-32 group cursor-default">
            <span class="text-3xl font-black text-[#3B82F6] tracking-tighter group-hover:scale-110 transition-transform drop-shadow-[0_0_12px_rgba(59,130,246,0.3)]">{{ $totalMesas }}</span>
            <span class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] mt-2">Mesas</span>
        </div>

        {{-- Divisor --}}
        <div class="w-[1px] h-12 bg-gradient-to-b from-transparent via-[var(--border-color)] to-transparent"></div>

        {{-- Stat 2 (Destacada) --}}
        <div class="flex flex-col items-center justify-center w-40 group cursor-default">
            <span class="text-3xl font-black text-emerald-500 tracking-tighter group-hover:scale-110 transition-transform drop-shadow-[0_0_12px_rgba(16,185,129,0.3)]">$0.00</span>
            <span class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] mt-2">Total Vendido</span>
        </div>

        {{-- Divisor --}}
        <div class="w-[1px] h-12 bg-gradient-to-b from-transparent via-[var(--border-color)] to-transparent"></div>

        {{-- Stat 3 --}}
        <div class="flex flex-col items-center justify-center w-32 group cursor-default">
            <span class="text-3xl font-black text-amber-500 tracking-tighter group-hover:scale-110 transition-transform drop-shadow-[0_0_12px_rgba(245,158,11,0.3)]">{{ $mesasActivas }}</span>
            <span class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] mt-2">Activas</span>
        </div>

    </div>

    {{-- Botón de Soporte/Ayuda Flotante --}}
    <button class="absolute right-8 w-11 h-11 rounded-2xl border border-[var(--border-color)] bg-gradient-to-br from-[var(--bg-panel)] to-[var(--bg-base)] text-[var(--text-muted)] hover:text-white hover:border-white/20 flex items-center justify-center transition-all outline-none shadow-lg">
        <i class="fas fa-question text-sm"></i>
    </button>
</footer>