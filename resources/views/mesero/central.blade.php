<main class="flex-1 relative flex flex-col items-center overflow-y-auto hide-scroll pb-6 sm:pb-8 md:pb-12">
    
    {{-- Resplandor radial inmersivo --}}
    <div class="fixed inset-0 flex items-center justify-center pointer-events-none z-0">
        <div class="absolute w-[800px] h-[800px] rounded-full" style="background: radial-gradient(circle, var(--glow-color) 0%, transparent 70%);"></div>
    </div>

    {{-- Contenedor Animado Flotante --}}
    @php
        $totalMesas = isset($mesas) ? $mesas->count() : 0;
        $mesasActivas = isset($mesas) ? $mesas->where('estado', 'ocupada')->count() : 0;
        $esCapitan = strtolower(trim(auth()->user()->rol ?? '')) === 'capitan';
    @endphp

    <div class="animate-float flex flex-col items-center relative z-10 mt-6 sm:mt-8 md:mt-12 w-full px-4 sm:px-6 md:px-8">
        
        @if($esCapitan)
            <div class="mb-6 sm:mb-8 w-full max-w-sm sm:max-w-xl md:max-w-2xl p-3 sm:p-4 rounded-2xl sm:rounded-3xl border border-[#38bdf8]/30 bg-[#38bdf8]/5 text-center shadow-[0_0_20px_rgba(56,189,248,0.1)] backdrop-blur-md">
                <p class="text-[10px] sm:text-[11px] uppercase tracking-[0.2em] text-[#38bdf8] font-black mb-1"><i class="fas fa-star mr-2"></i>Panel del Capitán</p>
                <p class="text-xs sm:text-sm text-[var(--text-main)] font-medium">Control total activado. Tienes acceso sin restricciones a las comandas de todo el restaurante.</p>
            </div>
        @endif

        <button type="button" onclick="abrirModalMesa()" class="group outline-none flex flex-col items-center cursor-pointer">
            
            {{-- El Botón Azul con efecto Glass e Inner Shadow --}}
            <div class="w-40 h-40 md:w-52 md:h-52 rounded-[2rem] md:rounded-[3rem] aspect-square flex flex-col items-center justify-center text-center bg-gradient-to-b from-[#4F93F7] to-[#1D4ED8] shadow-[0_20px_50px_rgba(59,130,246,0.35)] group-hover:shadow-[0_30px_60px_rgba(59,130,246,0.5)] group-hover:scale-105 transition-all duration-400 relative overflow-hidden animate-glow">
                {{-- Brillo superior interno (Bevel) --}}
                <div class="absolute inset-0 rounded-[2rem] md:rounded-[3rem] border-t-2 border-white/40 opacity-80"></div>
                {{-- Reflejo diagonal --}}
                <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/10 to-transparent translate-y-full group-hover:-translate-y-full transition-transform duration-700 ease-in-out"></div>
                
                <i class="fas fa-plus text-4xl sm:text-5xl md:text-6xl text-[var(--text-main)] drop-shadow-[0_2px_4px_rgba(0,0,0,0.3)] relative z-10 transition-transform group-hover:rotate-90 duration-500"></i>
                <h2 class="mt-3 text-base sm:text-lg md:text-xl lg:text-2xl font-black text-[#3B82F6] tracking-[0.15em] uppercase drop-shadow-[0_0_15px_rgba(59,130,246,0.2)] leading-tight relative z-10">
                    Nueva Mesa
                </h2>
            </div>
            
            {{-- Badge Pulsante --}}
            <div class="mt-5 px-4 sm:px-5 md:px-6 py-2 sm:py-2.5 rounded-full border border-[#3B82F6]/30 bg-[#3B82F6]/5 backdrop-blur-md shadow-[0_0_20px_rgba(59,130,246,0.1)] group-hover:bg-[#3B82F6]/10 transition-colors">
                <p class="text-[10px] sm:text-[11px] font-black text-[#3B82F6] tracking-[0.2em] uppercase flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-[#3B82F6] animate-pulse"></span>
                    Toca para comenzar
                </p>
            </div>
        </button>

        <div class="mt-10 sm:mt-12 md:mt-16 mb-8 sm:mb-10 md:mb-12 w-full max-w-xs sm:max-w-sm md:max-w-lg p-4 sm:p-5 md:p-6 rounded-xl sm:rounded-2xl md:rounded-[2rem] bg-[rgba(255,255,255,0.02)] border border-[rgba(255,255,255,0.05)] backdrop-blur-xl shadow-2xl text-center">
            <p class="text-xs sm:text-sm text-[var(--text-muted)] uppercase tracking-[0.2em] mb-3 sm:mb-4 font-bold">Resumen Operativo</p>
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <div class="p-3 sm:p-4 rounded-2xl sm:rounded-3xl bg-[var(--bg-base)] border border-[var(--border-color)]">
                    <p class="text-[8px] sm:text-[10px] uppercase tracking-[0.2em] text-[var(--text-muted)]">Mesas totales</p>
                    <p class="text-2xl sm:text-3xl font-black text-[var(--text-main)] mt-2">{{ $totalMesas }}</p>
                </div>
                <div class="p-3 sm:p-4 rounded-2xl sm:rounded-3xl bg-[var(--bg-base)] border border-[#34D399]/20">
                    <p class="text-[8px] sm:text-[10px] uppercase tracking-[0.2em] text-[var(--text-muted)]">Mesas abiertas</p>
                    <p class="text-2xl sm:text-3xl font-black text-[#34D399] mt-2">{{ $mesasActivas }}</p>
                </div>
            </div>
        </div>


        
    </div>
</main>