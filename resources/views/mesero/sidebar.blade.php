<aside class="hidden lg:flex flex-none w-full lg:w-[320px] xl:w-[360px] min-w-[280px] h-full flex-col border-r border-[var(--border-color)] bg-[var(--bg-base)] z-20">
    @php
        $esCapitan = strtolower(trim(auth()->user()->rol ?? '')) === 'capitan';
        
        // ==========================================================
        // SOLUCIÓN LÓGICA: Se agregaron estados clave como 'reabierta' 
        // y 'sucia' para que las mesas no desaparezcan.
        // ==========================================================
        $estadosActivos = ['ocupada', 'abierta', 'activa', 'en_uso', 'sucia', 'reabierta', 'por_cobrar'];
        $mesasAbiertas = isset($mesas) ? $mesas->whereIn('estado', $estadosActivos) : collect();
        $mesasActivas = $mesasAbiertas->count();
        
        $tituloMesas = $esCapitan ? 'Todas las Mesas' : 'Mesas Asignadas';
    @endphp

    {{-- Cabecera del Sidebar Premium (Glass + Bezel) --}}
    <div class="px-8 py-7 border-b border-[var(--border-color)] bg-[var(--bg-panel)] shrink-0 flex items-center justify-between shadow-sm relative z-10 transition-colors duration-300">
        <div>
            <h2 class="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em] mb-1.5 transition-colors duration-300">{{ $tituloMesas }}</h2>
            <p class="text-[11px] font-medium text-[var(--text-muted)] opacity-70 transition-colors duration-300">Selecciona para operar</p>
        </div>
        <div class="w-9 h-9 rounded-[12px] bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[13px] font-black text-[var(--text-main)] shadow-inner transition-all duration-300">
            {{ $mesasActivas }}
        </div>
    </div>

    {{-- Lista de Mesas --}}
    @if($mesasActivas > 0)
        <div id="sidebarMesasList" class="flex-1 overflow-y-auto p-6 space-y-4 hide-scroll bg-[var(--bg-base)] transition-colors duration-300">
            @foreach($mesasAbiertas as $mesa)
                
                {{-- Tarjeta de Mesa Estilo Pro Studio (Sin Neón) --}}
                <a href="{{ route('mesero.comanda.show', $mesa->id) }}" data-mesa-id="{{ $mesa->id }}" class="group block relative rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-5 transition-all duration-300 hover:border-emerald-500/40 hover:-translate-y-1 hover:shadow-2xl outline-none active:scale-[0.98] z-10">
                    
                    {{-- Pseudo-elemento para el borde de bisel premium --}}
                    <div class="absolute inset-0 rounded-[20px] padding-[1px] bg-gradient-to-b from-white/5 to-black/10 -z-10 [mask-image:linear-gradient(#fff_0_0)_content-box,linear-gradient(#fff_0_0)] [mask-composite:xor] pointer-events-none opacity-50 group-hover:opacity-100 transition-opacity"></div>

                    {{-- Top: Mesa y Botón --}}
                    <div class="flex items-center justify-between mb-5 gap-3 relative z-10">
                        <div class="flex items-center gap-3.5 overflow-hidden">
                            {{-- Indicador Activa (Dinámico según estado) --}}
                            @php
                                $colorEstado = 'bg-emerald-500';
                                $sombraEstado = 'shadow-[0_0_8px_rgba(16,185,129,0.6)]';
                                
                                if($mesa->estado === 'sucia') {
                                    $colorEstado = 'bg-amber-500';
                                    $sombraEstado = 'shadow-[0_0_8px_rgba(245,158,11,0.6)]';
                                } elseif($mesa->estado === 'por_cobrar') {
                                    $colorEstado = 'bg-blue-500';
                                    $sombraEstado = 'shadow-[0_0_8px_rgba(59,130,246,0.6)]';
                                }
                            @endphp
                            <div class="relative flex h-2 w-2 shrink-0 items-center justify-center">
                                <span class="animate-ping absolute inline-flex h-4 w-4 rounded-full {{ $colorEstado }} opacity-30"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 {{ $colorEstado }} {{ $sombraEstado }}"></span>
                            </div>
                            <h3 class="text-[16px] font-black text-[var(--text-main)] tracking-tight leading-none group-hover:text-emerald-400 transition-colors duration-300 uppercase">Mesa {{ $mesa->numero }}</h3>
                        </div>
                        
                        {{-- Botón de Acción Cuadrado Zinc --}}
                        <div class="w-8 h-8 shrink-0 rounded-[10px] bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] group-hover:bg-emerald-500 group-hover:text-white group-hover:border-transparent transition-all duration-400 shadow-inner group-active:scale-90">
                            <i class="fas fa-chevron-right text-[10px] transform group-hover:translate-x-0.5 transition-transform"></i>
                        </div>
                    </div>

                    {{-- Bottom: Métricas Estructuradas --}}
                    <div class="pt-4 border-t border-[var(--border-color)] flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-6">
                            {{-- Capacidad (Pax) --}}
                            <div class="flex flex-col">
                                <span class="text-[8px] font-bold uppercase tracking-[0.25em] text-[var(--text-muted)] opacity-70 mb-1.5 transition-colors duration-300">Pax</span>
                                <div class="flex items-center gap-1.5">
                                    <i class="fas fa-user text-[var(--text-muted)] opacity-50 text-[10px] transition-colors duration-300"></i>
                                    <span class="text-[12px] font-black text-[var(--text-main)] transition-colors duration-300">{{ $mesa->capacidad }}</span>
                                </div>
                            </div>
                            
                            {{-- Separador Zinc --}}
                            <div class="w-[1px] h-6 bg-[var(--border-color)] transition-colors duration-300"></div>
                            
                            {{-- Tiempo Abierta --}}
                            <div class="flex flex-col">
                                <span class="text-[8px] font-bold uppercase tracking-[0.25em] text-[var(--text-muted)] opacity-70 mb-1.5 transition-colors duration-300">Tiempo</span>
                                <div class="flex items-center gap-1.5">
                                    <i class="far fa-clock text-[var(--text-muted)] opacity-50 text-[10px] transition-colors duration-300"></i>
                                    <span class="text-[12px] font-black text-[var(--text-main)] transition-colors duration-300">0m</span>
                                </div>
                            </div>
                        </div>

                        {{-- Total Consumo (Verde Esmeralda) --}}
                        <div class="flex flex-col items-end">
                            <span class="text-[8px] font-bold uppercase tracking-[0.25em] text-[var(--text-muted)] opacity-70 mb-1.5 transition-colors duration-300">Total</span>
                            <span class="text-[15px] font-black text-emerald-400 dark:text-emerald-300 tracking-tighter leading-none">$ {{ number_format($mesa->total_consumo ?? 0, 2) }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        {{-- Empty State Premium Mejorado --}}
        <div class="flex-1 flex flex-col items-center justify-center p-8 text-center bg-[var(--bg-base)] transition-colors duration-300">
            <div class="w-24 h-24 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-inner flex items-center justify-center mb-6 opacity-60 transition-all duration-300">
                <i class="fas fa-folder-open text-3xl text-[var(--text-muted)] transition-colors duration-300"></i>
            </div>
            <h3 class="text-base font-black text-[var(--text-main)] mb-1.5 tracking-tight transition-colors duration-300">Sala Despejada</h3>
            <p class="text-[12px] text-[var(--text-muted)] leading-relaxed max-w-[220px] font-medium opacity-80 transition-colors duration-300">Actualmente no hay mesas activas o asignadas en tu turno.</p>
            <button onclick="abrirModalMesa()" class="mt-6 rounded-[14px] bg-[var(--text-main)] text-[var(--bg-panel)] px-6 py-3 text-[11px] font-black uppercase tracking-widest hover:opacity-80 transition-opacity outline-none shadow-md">
                Abrir mesa nueva
            </button>
        </div>
    @endif
</aside>