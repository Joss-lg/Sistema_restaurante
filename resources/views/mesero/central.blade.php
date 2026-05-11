<main class="flex-1 relative flex flex-col items-center overflow-y-auto hide-scroll pb-12">
    
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

    <div class="animate-float flex flex-col items-center relative z-10 mt-12 w-full px-8">
        
        @if($esCapitan)
            <div class="mb-8 w-full max-w-2xl p-4 rounded-3xl border border-[#38bdf8]/30 bg-[#38bdf8]/5 text-center shadow-[0_0_20px_rgba(56,189,248,0.1)] backdrop-blur-md">
                <p class="text-[11px] uppercase tracking-[0.2em] text-[#38bdf8] font-black mb-1"><i class="fas fa-star mr-2"></i>Panel del Capitán</p>
                <p class="text-xs text-[var(--text-main)] font-medium">Control total activado. Tienes acceso sin restricciones a las comandas de todo el restaurante.</p>
            </div>
        @endif

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

        <div class="mt-16 mb-12 w-full max-w-lg p-6 rounded-[2rem] bg-[rgba(255,255,255,0.02)] border border-[rgba(255,255,255,0.05)] backdrop-blur-xl shadow-2xl text-center">
            <p class="text-sm text-[var(--text-muted)] uppercase tracking-[0.2em] mb-4 font-bold">Resumen Operativo</p>
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 rounded-3xl bg-[var(--bg-base)] border border-[var(--border-color)]">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--text-muted)]">Mesas totales</p>
                    <p class="text-3xl font-black text-white mt-2">{{ $totalMesas }}</p>
                </div>
                <div class="p-4 rounded-3xl bg-[var(--bg-base)] border border-[#34D399]/20">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--text-muted)]">Mesas abiertas</p>
                    <p class="text-3xl font-black text-[#34D399] mt-2">{{ $mesasActivas }}</p>
                </div>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- EL MAPA DE MESAS DINÁMICO (LÓGICA DE CAPITÁN VS MESERO)  --}}
        {{-- ======================================================== --}}
        <div class="w-full max-w-6xl mt-4">
            <h3 class="text-[11px] uppercase tracking-[0.2em] text-[var(--text-muted)] font-black mb-6 border-b border-[var(--border-color)] pb-3 text-left">
                @if($esCapitan) <i class="fas fa-globe mr-2"></i> Panel de Control Global @else <i class="fas fa-user mr-2"></i> Tus Mesas Asignadas @endif
            </h3>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
                @forelse($mesas as $mesa)
                    <a href="{{ route('mesero.comanda.show', $mesa->id) }}" class="group p-5 rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#3B82F6] hover:shadow-[0_10px_30px_rgba(59,130,246,0.15)] transition-all cursor-pointer flex flex-col justify-between h-[140px] relative overflow-hidden">
                        @if($mesa->estado === 'ocupada')
                            <div class="absolute top-0 right-0 w-12 h-12 bg-gradient-to-bl from-[#34D399]/20 to-transparent flex justify-end items-start p-3 rounded-tr-3xl">
                                <span class="w-2 h-2 rounded-full bg-[#34D399] animate-pulse"></span>
                            </div>
                        @endif

                        <div>
                            <h4 class="text-2xl font-black text-[var(--text-main)] italic">Mesa {{ $mesa->numero }}</h4>
                            <p class="text-[10px] font-bold uppercase tracking-widest mt-1 {{ $mesa->estado === 'ocupada' ? 'text-[#34D399]' : 'text-[var(--text-muted)]' }}">
                                {{ $mesa->estado }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <span class="text-[10px] font-bold text-[var(--text-muted)] group-hover:text-[#3B82F6] transition-colors">Entrar</span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-10 text-center">
                        <p class="text-sm font-bold text-[var(--text-muted)] uppercase tracking-widest">No hay mesas registradas en el sistema.</p>
                    </div>
                @endforelse
            </div>
        </div>
        
    </div>
</main>