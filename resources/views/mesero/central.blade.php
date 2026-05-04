<main class="flex-1 relative flex flex-col items-center justify-center overflow-hidden">
    
    {{-- Resplandor radial inmersivo --}}
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div class="absolute w-[800px] h-[800px] rounded-full" style="background: radial-gradient(circle, var(--glow-color) 0%, transparent 70%);"></div>
    </div>

    {{-- Contenedor Animado Flotante --}}
    <div class="animate-float flex flex-col items-center relative z-10">
        
        <button type="button" onclick="abrirModalMesa()" class="group outline-none flex flex-col items-center cursor-pointer">
            
            {{-- El Botón Azul con efecto Glass e Inner Shadow --}}
            <div class="w-32 h-32 mb-10 rounded-[3rem] bg-gradient-to-b from-[#4F93F7] to-[#1D4ED8] flex items-center justify-center shadow-[0_20px_50px_rgba(59,130,246,0.35)] group-hover:shadow-[0_30px_60px_rgba(59,130,246,0.5)] group-hover:scale-105 transition-all duration-400 relative overflow-hidden animate-glow">
                {{-- Brillo superior interno (Bevel) --}}
                <div class="absolute inset-0 rounded-[3rem] border-t-2 border-white/40 opacity-80"></div>
                {{-- Reflejo diagonal --}}
                <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/10 to-transparent translate-y-full group-hover:-translate-y-full transition-transform duration-700 ease-in-out"></div>
                
                <i class="fas fa-plus text-6xl text-white drop-shadow-[0_2px_4px_rgba(0,0,0,0.3)] relative z-10 transition-transform group-hover:rotate-90 duration-500"></i>
            </div>
            
            {{-- Bloque Tipográfico Balanceado --}}
            <div class="text-center">
                <h1 class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-b from-[var(--text-main)] to-[var(--text-muted)] tracking-tight mb-1 group-hover:from-white group-hover:to-white transition-all duration-300 drop-shadow-sm">
                    ABRIR
                </h1>
                <h2 class="text-3xl font-black text-[#3B82F6] tracking-[0.15em] uppercase drop-shadow-[0_0_15px_rgba(59,130,246,0.2)]">
                    NUEVA MESA
                </h2>
            </div>
            
            {{-- Badge Pulsante --}}
            <div class="mt-8 px-6 py-2.5 rounded-full border border-[#3B82F6]/30 bg-[#3B82F6]/5 backdrop-blur-md shadow-[0_0_20px_rgba(59,130,246,0.1)] group-hover:bg-[#3B82F6]/10 transition-colors">
                <p class="text-[11px] font-black text-[#3B82F6] tracking-[0.2em] uppercase flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-[#3B82F6] animate-pulse"></span>
                    Toca para comenzar
                </p>
            </div>

        </button>
    </div>
</main>