<main class="flex-1 w-full h-full flex flex-col relative bg-[var(--bg-base)] p-6 lg:p-10">
    
    @php
        $totalMesas = isset($mesas) ? $mesas->count() : 0;
        $mesasActivas = isset($mesas) ? $mesas->where('estado', 'ocupada')->count() : 0;
        $esCapitan = strtolower(trim(auth()->user()->rol ?? '')) === 'capitan';
        
        // Fecha 100% en español estricto
        date_default_timezone_set('America/Mexico_City');
        $diasES = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $mesesES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $diaDeLaSemana = $diasES[date('w')];
        $diaDelMes = date('d');
        $mesDelAño = $mesesES[date('n') - 1];
        $fechaFormateada = $diaDeLaSemana . ', ' . $diaDelMes . ' de ' . $mesDelAño;
    @endphp

    <div class="w-full max-w-6xl mx-auto flex flex-col h-full">
        
        {{-- CABECERA --}}
        <div class="flex justify-between items-end mb-8 w-full border-b border-[var(--border-color)] pb-4">
            <div>
                <h1 class="text-2xl font-black text-[var(--text-main)] tracking-tight">Panel Principal</h1>
                <p class="text-[11px] font-medium text-[var(--text-muted)] uppercase tracking-wider mt-1">{{ $fechaFormateada }}</p>
            </div>
            
            <div class="text-right">
                <span class="text-2xl font-black text-[var(--text-main)] tracking-tighter" id="reloj-live">{{ date('H:i') }}</span>
                @if($esCapitan)
                    <div class="mt-1 flex items-center gap-1.5 justify-end">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#3B82F6]"></span>
                        <span class="text-[9px] font-bold uppercase tracking-widest text-[var(--text-muted)]">Capitán</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- CONTENEDOR DE TARJETAS (Bento Grid Limpio) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- TARJETA PRINCIPAL: ABRIR MESA --}}
            <div class="lg:col-span-2">
                <button onclick="abrirModalMesa()" class="w-full h-full min-h-[220px] rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-8 flex flex-col justify-center items-center text-center transition-all duration-300 hover:border-[#3B82F6]/40 hover:bg-[var(--input-bg)] outline-none group">
                    
                    <div class="w-14 h-14 rounded-full bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center mb-5 group-hover:bg-[#3B82F6] group-hover:border-[#3B82F6] transition-colors duration-300">
                        <i class="fas fa-plus text-lg text-[var(--text-main)] group-hover:text-white transition-colors duration-300"></i>
                    </div>
                    
                    <h2 class="text-xl font-bold text-[var(--text-main)] tracking-tight mb-1">Abrir Nueva Mesa</h2>
                    <p class="text-xs text-[var(--text-muted)]">Asignar comensales y comenzar el servicio</p>
                </button>
            </div>

            {{-- MÉTRICAS LATERALES --}}
            <div class="flex flex-col gap-6">
                
                {{-- Tarjeta: Estado de Sala --}}
                <div class="rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 flex flex-col justify-between h-full min-h-[140px]">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">Ocupación</span>
                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    </div>
                    
                    <div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-4xl font-black text-[var(--text-main)] tracking-tighter" id="txtMesasAbiertas">{{ $mesasActivas }}</span>
                            <span class="text-sm font-medium text-[var(--text-muted)]">/ <span id="txtTotalMesas">{{ $totalMesas }}</span> mesas</span>
                        </div>
                    </div>
                </div>

                {{-- Tarjeta: Administración (Bloqueado) --}}
                <div class="rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 flex flex-col justify-between h-full min-h-[140px] opacity-60">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">Corte de Turno</span>
                        <i class="fas fa-lock text-[var(--text-muted)] text-[10px]"></i>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full border border-[var(--border-color)] flex items-center justify-center">
                            <i class="fas fa-chart-line text-[var(--text-muted)] text-[10px]"></i>
                        </div>
                        <div class="flex flex-col gap-1.5 w-full">
                            <div class="h-1.5 w-16 bg-[var(--border-color)] rounded-full"></div>
                            <div class="h-1.5 w-10 bg-[var(--border-color)] rounded-full"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        setInterval(() => {
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', hour12: false });
            const reloj = document.getElementById('reloj-live');
            if(reloj) reloj.innerText = timeString;
        }, 1000);
    </script>
</main>