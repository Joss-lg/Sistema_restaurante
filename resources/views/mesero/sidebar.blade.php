<aside class="hidden lg:flex flex-none w-full lg:w-[320px] xl:w-[360px] min-w-[280px] h-full flex-col border-r border-[var(--border-color)] bg-[var(--bg-base)] z-20">
    @php
        $esCapitan = strtolower(trim(auth()->user()->rol ?? '')) === 'capitan';
        $mesasActivas = isset($mesas) ? $mesas->where('estado', 'ocupada')->count() : 0;
        $mesasAbiertas = isset($mesas) ? $mesas->where('estado', 'ocupada') : collect();
        $tituloMesas = $esCapitan ? 'Todas las Mesas' : 'Mesas Asignadas';
    @endphp

    {{-- Cabecera del Sidebar --}}
    <div class="px-7 py-7 border-b border-[var(--border-color)] bg-[var(--bg-panel)] shrink-0 flex items-center justify-between shadow-sm relative z-10">
        <div>
            <h2 class="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em] mb-1">{{ $tituloMesas }}</h2>
            <p class="text-[10px] font-medium text-[var(--text-muted)] opacity-60">Selecciona para operar</p>
        </div>
        <div class="w-8 h-8 rounded-[10px] bg-[var(--bg-base)] border border-[var(--border-highlight)] flex items-center justify-center text-[11px] font-black text-[var(--text-main)] shadow-inner">
            {{ $mesasActivas }}
        </div>
    </div>

    {{-- Lista de Mesas --}}
    @if($mesasActivas > 0)
        <div id="sidebarMesasList" class="flex-1 overflow-y-auto p-5 space-y-4 hide-scroll bg-[var(--bg-base)]">
            @foreach($mesasAbiertas as $mesa)
                {{-- Contenedor Exterior (Crea el efecto de marco grueso) --}}
                <a href="{{ route('mesero.comanda.show', $mesa->id) }}" data-mesa-id="{{ $mesa->id }}" class="group block relative rounded-[24px] bg-gradient-to-b from-[var(--bg-panel)] to-[var(--bg-base)] border border-white/[0.03] p-1.5 transition-all duration-500 hover:border-[#3B82F6]/30 hover:shadow-[0_12px_30px_-10px_rgba(59,130,246,0.2)] outline-none transform hover:-translate-y-1">
                    
                    {{-- Sombra de bisel superior --}}
                    <div class="absolute inset-0 rounded-[24px] shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] pointer-events-none"></div>

                    {{-- Contenedor Interior --}}
                    <div class="bg-[var(--bg-panel)] rounded-[18px] p-5 h-full relative z-10 shadow-sm flex flex-col group-hover:bg-[#121216] transition-colors duration-500">
                        
                        {{-- Top: Mesa y Botón --}}
                        <div class="flex items-center justify-between mb-5 gap-3">
                            <div class="flex items-center gap-3.5 overflow-hidden">
                                {{-- Indicador Activa Ultra-Sutil --}}
                                <div class="relative flex h-2 w-2 shrink-0 items-center justify-center">
                                    <span class="animate-ping absolute inline-flex h-4 w-4 rounded-full bg-emerald-400 opacity-20"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.8)]"></span>
                                </div>
                                <h3 class="text-[16px] font-black text-transparent bg-clip-text bg-gradient-to-r from-white to-white/70 tracking-tight leading-none truncate group-hover:to-[#3B82F6] transition-all duration-300">Mesa {{ $mesa->numero }}</h3>
                            </div>
                            
                            {{-- Botón de Acción Cuadrado/Redondeado --}}
                            <div class="w-8 h-8 shrink-0 rounded-[10px] bg-white/[0.02] border border-white/[0.04] flex items-center justify-center text-[var(--text-muted)] group-hover:bg-[#3B82F6] group-hover:text-white group-hover:border-transparent transition-all duration-400 shadow-sm">
                                <i class="fas fa-chevron-right text-[10px] transform group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </div>

                        {{-- Bottom: Métricas Estructuradas --}}
                        <div class="pt-4 border-t border-white/[0.04] flex items-center justify-between">
                            
                            <div class="flex items-center gap-6">
                                <div class="flex flex-col">
                                    <span class="text-[8px] font-bold uppercase tracking-[0.25em] text-[var(--text-muted)] opacity-50 mb-1">Pax</span>
                                    <div class="flex items-center gap-1.5">
                                        <i class="fas fa-user text-[var(--text-muted)] opacity-40 text-[10px]"></i>
                                        <span class="text-[12px] font-black text-[var(--text-main)]">{{ $mesa->capacidad }}</span>
                                    </div>
                                </div>
                                
                                <div class="w-[1px] h-6 bg-white/[0.04]"></div>
                                
                                <div class="flex flex-col">
                                    <span class="text-[8px] font-bold uppercase tracking-[0.25em] text-[var(--text-muted)] opacity-50 mb-1">Tiempo</span>
                                    <div class="flex items-center gap-1.5">
                                        <i class="far fa-clock text-[var(--text-muted)] opacity-40 text-[10px]"></i>
                                        <span class="text-[12px] font-black text-[var(--text-main)]">0m</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-end">
                                <span class="text-[8px] font-bold uppercase tracking-[0.25em] text-[var(--text-muted)] opacity-50 mb-1">Total</span>
                                <span class="text-[15px] font-black text-emerald-400 tracking-tighter leading-none drop-shadow-[0_0_10px_rgba(52,211,153,0.15)]">$ {{ number_format($mesa->total_consumo ?? 0, 2) }}</span>
                            </div>

                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        {{-- Empty State Premium --}}
        <div class="flex-1 flex flex-col items-center justify-center p-8 text-center bg-[var(--bg-base)]">
            <div class="w-20 h-20 rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-inner flex items-center justify-center mb-5 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-white/[0.04] to-transparent"></div>
                <i class="fas fa-layer-group text-2xl text-[var(--text-muted)] opacity-30 relative z-10"></i>
            </div>
            <h3 class="text-[14px] font-black text-[var(--text-main)] mb-1.5 tracking-tight">Sala Despejada</h3>
            <p class="text-[10px] text-[var(--text-muted)] leading-relaxed max-w-[180px] font-medium opacity-70">El área se encuentra sin mesas activas.</p>
        </div>
    @endif
</aside>