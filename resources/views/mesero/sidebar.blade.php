<aside class="hidden lg:flex flex-none w-full lg:w-[300px] xl:w-[340px] 2xl:w-[380px] min-w-[260px] h-full flex-col border-r border-[var(--border-color)] bg-[var(--bg-panel)] z-20 transition-all duration-400">
    @php
        $esCapitan = strtolower(trim(auth()->user()->rol ?? '')) === 'capitan';
        $mesasActivas = isset($mesas) ? $mesas->where('estado', 'ocupada')->count() : 0;
        $mesasAbiertas = isset($mesas) ? $mesas->where('estado', 'ocupada') : collect();
        $mesasLibres = isset($mesas) ? $mesas->where('estado', 'disponible') : collect();
        $tituloMesas = $esCapitan ? 'Mesas abiertas' : 'Mis Mesas';
    @endphp

    <div class="p-4 lg:p-6 flex justify-between items-center border-b border-[var(--border-color)]">
        <h2 class="text-[9px] lg:text-[11px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em]">{{ $tituloMesas }}</h2>
        <div id="sidebarMesasActivas" class="w-6 lg:w-7 h-6 lg:h-7 rounded-lg border border-[var(--border-color)] bg-[var(--bg-base)] flex items-center justify-center text-[9px] lg:text-[10px] font-black text-[var(--text-main)] shadow-sm">
            {{ $mesasActivas }}
        </div>
    </div>

    @if($mesasActivas > 0)
        <div id="sidebarMesasList" class="flex-1 overflow-y-auto p-3 lg:p-4 space-y-3">
            @foreach($mesasAbiertas as $mesa)
                <a href="{{ route('mesero.comanda.show', $mesa->id) }}" data-mesa-id="{{ $mesa->id }}" class="mesa-item block p-3 lg:p-4 rounded-2xl lg:rounded-3xl border border-[var(--border-color)] bg-[var(--bg-panel)] hover:border-[#3B82F6]/40 transition-all">
                    <div class="flex items-start justify-between gap-3 lg:gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <h3 class="text-base lg:text-xl font-black text-[var(--text-main)]">Mesa {{ $mesa->numero }}</h3>
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-700/10 text-emerald-300 text-[10px] font-black uppercase">ACTIVA</span>
                            </div>
                            <div class="mt-2 flex items-center gap-3 text-[12px] text-[var(--text-muted)]">
                                <span class="flex items-center gap-1"><i class="fas fa-user-friends text-[12px]"></i> {{ $mesa->capacidad }} personas</span>
                                <span class="text-[var(--text-muted)]">• 0m</span>
                                <span class="text-emerald-400 font-black">$ {{ number_format($mesa->total_consumo ?? 0, 2) }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col justify-between text-right">
                            <span class="text-[#3B82F6] font-bold">Ver comanda ›</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="flex-1 flex flex-col items-center justify-center p-4 lg:p-8 text-center">
            <div class="relative mb-4 lg:mb-8">
                <div class="absolute inset-0 border border-[var(--border-color)] rounded-full scale-150 opacity-50"></div>
                <div class="absolute inset-0 border border-[var(--border-color)] rounded-full scale-125 opacity-70"></div>
                <div class="w-16 lg:w-24 h-16 lg:h-24 rounded-full bg-gradient-to-br from-white/5 to-transparent border border-white/10 flex items-center justify-center shadow-inner relative z-10 backdrop-blur-sm">
                    <i class="fas fa-receipt text-2xl lg:text-4xl text-[var(--text-muted)] drop-shadow-md"></i>
                </div>
            </div>
            <h3 class="text-sm lg:text-lg font-black text-[var(--text-main)] tracking-tight mb-1 lg:mb-2">Bandeja Vacía</h3>
            <p class="text-[8px] lg:text-xs text-[var(--text-muted)] font-medium leading-relaxed max-w-[150px] lg:max-w-[200px]">
                Inicia una nueva comanda desde el panel central.
            </p>
        </div>
    @endif
</aside>