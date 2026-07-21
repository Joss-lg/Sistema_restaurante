{{-- resources/views/admin/cocina/index.blade.php --}}
@extends('layouts.admin')

@section('title', ($areaSeleccionada === 'Barra' ? 'KDS Barra' : 'KDS Cocina') . ' | Ollintem Pro')

@section('header-title', 'Modulo de ' . $areaSeleccionada)
@section('header-subtitle', 'Monitor en tiempo real de comandas, tiempos de preparación y notas especiales')

@section('content')
<div class="p-3 sm:p-6 lg:p-8 w-full max-w-[1800px] mx-auto relative z-10 overflow-x-hidden font-sans">

    {{-- --- Selector de Área (Cocina / Barra) --- --}}
    <div class="flex items-center gap-2 mb-4 sm:mb-6">
        <a href="{{ route('admin.cocina.index', ['area' => 'cocina']) }}"
           class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-black uppercase tracking-widest transition-all border
                  {{ $areaSeleccionada === 'Cocina'
                        ? 'bg-blue-600 text-white border-blue-600 shadow-md'
                        : 'bg-[var(--card-color)] text-[var(--text-muted)] border-[var(--border-color)] hover:border-blue-500/50' }}">
            <i class="fas fa-fire-burner mr-2"></i>Cocina
        </a>
        <a href="{{ route('admin.cocina.index', ['area' => 'barra']) }}"
           class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-black uppercase tracking-widest transition-all border
                  {{ $areaSeleccionada === 'Barra'
                        ? 'bg-blue-600 text-white border-blue-600 shadow-md'
                        : 'bg-[var(--card-color)] text-[var(--text-muted)] border-[var(--border-color)] hover:border-blue-500/50' }}">
            <i class="fas fa-martini-glass mr-2"></i>Barra
        </a>

        {{-- Indicador visual de auto-refresco --}}
        <span class="ml-auto inline-flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            En vivo
        </span>
    </div>

    <div class="glass-card rounded-[20px] sm:rounded-[32px] p-3 sm:p-4 flex flex-col xl:flex-row gap-3 shadow-2xl border border-[var(--border-color)] bg-[var(--card-color)] relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-blue-500/5 to-transparent pointer-events-none"></div>

        <div class="bg-[var(--bg-color)] rounded-[16px] sm:rounded-[24px] p-4 sm:p-8 xl:w-1/3 flex flex-col justify-center relative border border-[var(--border-color)] shadow-inner">
            <div class="flex items-center gap-2.5 mb-2">
                <span class="relative flex h-3 w-3 sm:h-4 sm:w-4 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 sm:h-4 sm:w-4 bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.8)]"></span>
                </span>
                <h2 class="text-[10px] sm:text-xs font-black uppercase tracking-[0.3em] text-[var(--text-muted)]">Fuego Abierto — {{ $areaSeleccionada }}</h2>
            </div>
            <p class="text-3xl sm:text-6xl font-black text-[var(--text-color)] tracking-tighter mt-1 flex flex-col sm:flex-row sm:items-baseline gap-1 sm:gap-3">
                <span id="stat-ordenes-activas">{{ $ordenesActivasEnArea }}</span>
                <span class="text-xs sm:text-base text-[var(--text-muted)] font-bold uppercase tracking-widest">Órdenes activas</span>
            </p>
        </div>

        <div class="flex-1 grid grid-cols-3 gap-1.5 sm:gap-4">
            <div class="bg-[var(--bg-color)] rounded-[14px] sm:rounded-[24px] p-2.5 sm:p-6 border border-orange-500/30 flex flex-col items-center sm:items-start justify-center relative overflow-hidden group hover:border-orange-500/60 transition-colors min-w-0">
                <div class="absolute inset-0 bg-gradient-to-b from-orange-500/10 to-transparent opacity-50"></div>
                <div class="relative z-10 flex flex-col items-center sm:items-start w-full min-w-0">
                    <div class="flex justify-center sm:justify-between items-center w-full mb-1 sm:mb-2">
                        <span class="text-[8px] sm:text-xs font-black uppercase tracking-widest text-orange-500 drop-shadow-sm text-center sm:text-left leading-tight truncate">En Cola</span>
                        <i class="fas fa-receipt text-orange-500/40 text-lg hidden sm:block shrink-0"></i>
                    </div>
                    <p class="text-xl sm:text-5xl font-black text-[var(--text-color)] drop-shadow-md" id="stat-pendientes">{{ $pendientes }}</p>
                </div>
            </div>
            <div class="bg-[var(--bg-color)] rounded-[14px] sm:rounded-[24px] p-2.5 sm:p-6 border border-blue-500/30 flex flex-col items-center sm:items-start justify-center relative overflow-hidden group hover:border-blue-500/60 transition-colors min-w-0">
                <div class="absolute inset-0 bg-gradient-to-b from-blue-500/10 to-transparent opacity-50"></div>
                <div class="relative z-10 flex flex-col items-center sm:items-start w-full min-w-0">
                    <div class="flex justify-center sm:justify-between items-center w-full mb-1 sm:mb-2">
                        <span class="text-[8px] sm:text-xs font-black uppercase tracking-widest text-blue-500 drop-shadow-sm text-center sm:text-left leading-tight truncate">Proceso</span>
                        <i class="fas fa-fire-burner text-blue-500/40 text-lg hidden sm:block shrink-0"></i>
                    </div>
                    <p class="text-xl sm:text-5xl font-black text-[var(--text-color)] drop-shadow-md" id="stat-enproceso">{{ $enProceso }}</p>
                </div>
            </div>
            <div class="bg-[var(--bg-color)] rounded-[14px] sm:rounded-[24px] p-2.5 sm:p-6 border border-emerald-500/30 flex flex-col items-center sm:items-start justify-center relative overflow-hidden group hover:border-emerald-500/60 transition-colors min-w-0">
                <div class="absolute inset-0 bg-gradient-to-b from-emerald-500/10 to-transparent opacity-50"></div>
                <div class="relative z-10 flex flex-col items-center sm:items-start w-full min-w-0">
                    <div class="flex justify-center sm:justify-between items-center w-full mb-1 sm:mb-2">
                        <span class="text-[8px] sm:text-xs font-black uppercase tracking-widest text-emerald-500 drop-shadow-sm text-center sm:text-left leading-tight truncate">Listas</span>
                        <i class="fas fa-bell-concierge text-emerald-500/40 text-lg hidden sm:block shrink-0"></i>
                    </div>
                    <p class="text-xl sm:text-5xl font-black text-[var(--text-color)] drop-shadow-md" id="stat-servidas">{{ $servidas }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenedor de tarjetas: se reemplaza completo cada 5s por el polling --}}
    <div id="comandas-container">
        @include('admin.cocina.partials.comandas', ['comandas' => $comandas, 'areaSeleccionada' => $areaSeleccionada])
    </div>
</div>

<script>
    const AREA_ACTUAL = @json(strtolower($areaSeleccionada));
    const URL_API_COMANDAS = @json(route('admin.cocina.api.comandas'));

    // ---------------------------------------------------------------
    // AUTO-REFRESCO: consulta el servidor cada 5s y reemplaza las
    // tarjetas + contadores, sin recargar la página completa.
    // ---------------------------------------------------------------
    async function actualizarComandas() {
        try {
            const res = await fetch(`${URL_API_COMANDAS}?area=${AREA_ACTUAL}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await res.json().catch(() => null);
            if (!res.ok || !data || !data.success) return;

            const contenedor = document.getElementById('comandas-container');
            if (contenedor) contenedor.innerHTML = data.html;

            const setTexto = (id, valor) => {
                const el = document.getElementById(id);
                if (el) el.innerText = valor;
            };
            setTexto('stat-ordenes-activas', data.ordenesActivasEnArea);
            setTexto('stat-pendientes', data.pendientes);
            setTexto('stat-enproceso', data.enProceso);
            setTexto('stat-servidas', data.servidas);

            actualizarContadoresEspera();
        } catch (err) {
            console.error('Error actualizando comandas:', err);
        }
    }

    // ---------------------------------------------------------------
    // Envío de los botones "Iniciar Preparación" / "Marcar como Lista"
    // por AJAX (en vez de recargar toda la página), usando delegación
    // de eventos porque las tarjetas se reemplazan dinámicamente.
    // ---------------------------------------------------------------
    document.addEventListener('submit', async function (e) {
        const form = e.target.closest('.form-avanzar-estado');
        if (!form) return;

        e.preventDefault();

        const boton = form.querySelector('button[type="submit"]');
        if (boton) { boton.disabled = true; boton.innerText = 'Actualizando...'; }

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action, {
                method: 'POST', // el form ya trae @method('PATCH') vía _method
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json().catch(() => null);

            if (!res.ok || !data || !data.success) {
                alert('No se pudo actualizar el estado. Intenta de nuevo.');
                if (boton) boton.disabled = false;
                return;
            }

            // Refrescamos de inmediato para ver el cambio sin esperar los 5s
            actualizarComandas();
        } catch (err) {
            console.error('Error al avanzar estado:', err);
            alert('Error de red al actualizar el estado.');
            if (boton) boton.disabled = false;
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        setInterval(actualizarComandas, 5000);
    });

    async function cargarKdsMesas() {
        try {
            const res = await fetch('{{ route("mesero.mesas.abiertas") }}', { 
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest', 
                    'Accept': 'application/json' 
                } 
            });
            
            const data = await res.json().catch(() => null);
            if (!res.ok || !data || !data.success) return;

            const list = document.getElementById('kdsMesasList');
            const badge = document.getElementById('kdsBadge');
            if (!list) return;

            list.innerHTML = '';
            if (badge) badge.innerText = data.conteo_abiertas || 0;

            const mesas = data.mesas_abiertas || [];
            
            if (mesas.length === 0) {
                list.innerHTML = `
                    <div class="text-[11px] text-zinc-500 modo-crema:text-zinc-400 font-medium p-4 text-center bg-zinc-950/40 modo-crema:bg-zinc-100/50 rounded-xl border border-zinc-800 modo-crema:border-zinc-200">
                        No hay mesas activas en este momento.
                    </div>`;
                return;
            }

            mesas.forEach(m => {
                const a = document.createElement('a');
                a.href = `{{ url('mesero/comanda') }}/${m.id}`;
                a.className = 'block p-4 rounded-xl border border-zinc-800 modo-crema:border-zinc-200 bg-zinc-900/40 modo-crema:bg-white hover:border-blue-500/50 hover:bg-zinc-900 modo-crema:hover:bg-zinc-50 transition-all flex items-center justify-between group cursor-pointer';
                
                a.innerHTML = `
                    <div class="flex-1 w-full min-w-0 pr-2">
                        <div class="flex items-center gap-2 mb-1">
                            <h5 class="text-sm font-black text-zinc-100 modo-crema:text-zinc-900 truncate group-hover:text-blue-500 transition-colors">
                                Mesa ${m.numero}
                            </h5>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded border border-emerald-500/30 bg-emerald-500/10 text-emerald-500 text-[8px] font-black uppercase tracking-wider shadow-sm">
                                ACTIVA
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-[10px] text-zinc-400 modo-crema:text-zinc-500 font-bold">
                            <span class="flex items-center gap-1.5"><i class="fas fa-users text-blue-500"></i> ${m.capacidad ?? '0'} pax</span>
                            <span class="text-emerald-400 modo-crema:text-emerald-600 tracking-wide">$ ${Number(m.total_consumo || 0).toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="pl-2 flex items-center shrink-0">
                        <div class="w-8 h-8 rounded-full bg-zinc-950 modo-crema:bg-zinc-100 border border-zinc-800 modo-crema:border-zinc-200 flex items-center justify-center group-hover:bg-blue-600 group-hover:border-blue-600 transition-colors">
                            <i class="fas fa-chevron-right text-[9px] text-zinc-500 group-hover:text-white transition-colors"></i>
                        </div>
                    </div>
                `;
                list.appendChild(a);
            });

        } catch (err) {
            console.error('Error cargando mesas KDS:', err);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        cargarKdsMesas();
        setInterval(cargarKdsMesas, 10000);
    });

    // --- Contador de tiempo de espera por ticket (desde que se envió a cocina) ---
    function formatearEspera(minutos) {
        if (minutos < 1) return 'Recién enviado';
        if (minutos < 60) return `Espera: ${minutos} min`;
        const horas = Math.floor(minutos / 60);
        const resto = minutos % 60;
        return `Espera: ${horas}h ${resto}min`;
    }

    function claseNivelEspera(minutos) {
        if (minutos >= 30) return 'bg-red-500/10 border-red-500/30 text-red-500 animate-pulse';
        if (minutos >= 15) return 'bg-orange-500/10 border-orange-500/30 text-orange-500';
        return 'bg-zinc-500/10 border-zinc-500/30 text-zinc-400';
    }

    function actualizarContadoresEspera() {
        document.querySelectorAll('.tiempo-espera').forEach((el) => {
            const creado = el.dataset.creado;
            if (!creado) return;

            const minutos = Math.max(0, Math.floor((Date.now() - new Date(creado).getTime()) / 60000));
            const texto = el.querySelector('.tiempo-texto');
            if (texto) texto.textContent = formatearEspera(minutos);

            el.className = 'tiempo-espera shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-lg border text-[10px] font-black uppercase tracking-wide whitespace-nowrap ' + claseNivelEspera(minutos);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        actualizarContadoresEspera();
        setInterval(actualizarContadoresEspera, 30000);
    });
</script>
@endsection