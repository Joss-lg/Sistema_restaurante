<aside class="w-[320px] h-full flex flex-col border-r border-[var(--border-color)] bg-[var(--bg-panel)] z-20 transition-colors duration-400">
    @php
        $esCapitan = strtolower(trim(auth()->user()->rol ?? '')) === 'capitan';
        $mesasActivas = isset($mesas) ? $mesas->where('estado', 'ocupada')->count() : 0;
        $mesasAbiertas = isset($mesas) ? $mesas->where('estado', 'ocupada') : collect();
        $tituloMesas = $esCapitan ? 'Mesas abiertas' : 'Mis Mesas Activas';
    @endphp

    <div class="p-6 flex justify-between items-center border-b border-[var(--border-color)]">
        <h2 class="text-[11px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em]">{{ $tituloMesas }}</h2>
        <div class="w-7 h-7 rounded-lg border border-[var(--border-color)] bg-[var(--bg-base)] flex items-center justify-center text-[10px] font-black text-[var(--text-main)] shadow-sm">
            {{ $mesasActivas }}
        </div>
    </div>

    @if($mesasActivas > 0)
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            @foreach($mesasAbiertas as $mesa)
                <a href="{{ route('mesero.comanda.show', $mesa->id) }}" class="block p-4 rounded-3xl border border-white/10 bg-[#0f1014] hover:border-[#3B82F6]/40 transition-all">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs text-[var(--text-muted)] uppercase tracking-[0.2em] font-bold">Mesa</p>
                            <h3 class="text-xl font-black text-white">{{ $mesa->numero }}</h3>
                        </div>
                        <span class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-green-400">
                            <i class="fas fa-circle text-[8px]"></i> Activa
                        </span>
                    </div>
                    <p class="mt-3 text-[9px] text-[var(--text-muted)]">Capacidad: {{ $mesa->capacidad }}</p>
                </a>
            @endforeach
        </div>
    @else
        <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
            <div class="relative mb-8">
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
    @endif
</aside>