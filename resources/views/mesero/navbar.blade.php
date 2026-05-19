<header class="h-[76px] flex-none flex justify-between items-center px-8 glass-panel border-b border-[var(--border-color)] z-30 transition-colors duration-400">
    
    {{-- Branding --}}
<div class="flex items-center gap-4 hover:opacity-90 transition-opacity cursor-pointer">
        <div class="w-12 h-12 rounded-3xl bg-gradient-to-br from-[#3B82F6]/20 via-[#38bdf8]/10 to-transparent border border-[#3B82F6]/30 flex items-center justify-center shadow-[0_0_22px_rgba(59,130,246,0.2)] relative overflow-hidden">
            <span class="font-black text-[#3B82F6] text-2xl relative z-10 drop-shadow-md">O</span>
        </div>
        <div class="flex flex-col justify-center">
            <span class="font-black tracking-[0.1em] text-[18px] text-[var(--text-main)] leading-none">Ollintem <span class="text-[#3B82F6]">Pro</span></span>
            <span class="text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-[0.3em] mt-0.5">Punto de Venta</span>
        </div>
    </div>

    {{-- Controles de Usuario --}}
    <div class="flex items-center gap-6">
        
        <button onclick="toggleTheme()" class="w-10 h-10 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[var(--border-highlight)] flex items-center justify-center transition-all shadow-sm outline-none">
            <i id="dashThemeIcon" class="fas fa-sun"></i>
        </button>

        <div class="w-[1px] h-8 bg-gradient-to-b from-transparent via-[var(--border-color)] to-transparent"></div>

        <div class="flex items-center gap-4">
            <div class="flex flex-col text-right">
                <span class="text-sm font-bold text-[var(--text-main)] tracking-tight">{{ auth()->user()->nombre ?? 'Mesero' }}</span>
                <div class="flex items-center justify-end gap-1.5 mt-0.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(52,211,153,0.8)] animate-pulse-soft"></span>
                    <span class="text-[9px] text-[var(--text-muted)] font-bold uppercase tracking-widest">Activo</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-2xl border border-[var(--border-color)] bg-gradient-to-br from-[var(--bg-panel)] to-[var(--bg-base)] flex items-center justify-center text-sm font-black text-[var(--text-main)] shadow-inner">
                {{ substr(auth()->user()->nombre ?? 'M', 0, 1) }}
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="ml-2">
            @csrf
            <button type="submit" class="group flex items-center gap-2 px-5 py-2.5 rounded-2xl border border-rose-500/20 bg-rose-500/5 text-rose-500 hover:bg-rose-500 hover:text-white transition-all duration-300 shadow-sm outline-none overflow-hidden relative">
                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                <i class="fas fa-sign-out-alt relative z-10"></i>
                <span class="text-[10px] font-black uppercase tracking-widest relative z-10">Salir</span>
            </button>
        </form>
    </div>
</header>