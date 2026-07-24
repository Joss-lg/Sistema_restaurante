@extends('layouts.admin') {{-- Ajusta según el nombre de tu plantilla layout --}}

@section('content')
<div class="w-full min-h-screen p-4 sm:p-6 lg:p-8">
    
    {{-- 1. Encabezado / Botones de Área --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
       <div class="flex items-center gap-2 bg-[var(--card-color)] p-1.5 rounded-2xl border border-[var(--border-color)]">
    <a href="{{ route('admin.cocina.index', ['area' => 'cocina']) }}" 
       class="px-5 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition-all {{ $areaSeleccionada === 'Cocina' ? 'bg-blue-600 text-white shadow-lg' : 'text-[var(--text-muted)] hover:text-[var(--text-color)]' }}">
        <i class="fas fa-fire mr-2"></i>Cocina
    </a>
    <a href="{{ route('admin.cocina.index', ['area' => 'barra']) }}" 
       class="px-5 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition-all {{ $areaSeleccionada === 'Barra' ? 'bg-blue-600 text-white shadow-lg' : 'text-[var(--text-muted)] hover:text-[var(--text-color)]' }}">
        <i class="fas fa-glass-martini-alt mr-2"></i>Barra
    </a>
</div>
        <div class="flex items-center gap-2 text-xs font-bold text-emerald-500">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span> EN VIVO
        </div>
    </div>

  {{-- 2. Tarjetas de Estadísticas --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-6">

    {{-- Órdenes Activas - Azul --}}
    <div class="p-4 sm:p-5 rounded-2xl bg-[var(--card-color)] border border-blue-500/40 shadow-[0_0_20px_-5px_rgba(59,130,246,0.35)]">
        <span class="text-[10px] font-black uppercase text-[var(--text-muted)] tracking-wider">Órdenes Activas</span>
        <h4 id="stat-ordenes-activas" class="text-2xl sm:text-3xl font-black text-[var(--text-color)] mt-1">{{ $ordenesActivasEnArea }}</h4>
    </div>

    {{-- En Proceso - Púrpura --}}
    <div class="p-4 sm:p-5 rounded-2xl bg-[var(--card-color)] border border-purple-500/40 shadow-[0_0_20px_-5px_rgba(168,85,247,0.35)]">
        <span class="text-[10px] font-black uppercase text-[var(--text-muted)] tracking-wider">En Proceso</span>
        <h4 id="stat-enproceso" class="text-2xl sm:text-3xl font-black text-[var(--text-color)] mt-1">{{ $enProceso }}</h4>
    </div>

    {{-- Listas (Turno) - Esmeralda --}}
    <div class="p-4 sm:p-5 rounded-2xl bg-[var(--card-color)] border border-emerald-500/40 shadow-[0_0_20px_-5px_rgba(16,185,129,0.35)]">
        <span class="text-[10px] font-black uppercase text-[var(--text-muted)] tracking-wider">Listas (Turno)</span>
        <h4 id="stat-servidas" class="text-2xl sm:text-3xl font-black text-[var(--text-color)] mt-1">{{ $servidas }}</h4>
    </div>

</div>
    {{-- 3. CONTENEDOR DE COMANDAS --}}
    {{-- Importante: Debe ser un div neutro simple, ya que 'comandas.blade.php' incluye su propio grid --}}
    <div id="comandas-container" class="w-full">
        @include('admin.cocina.partials.comandas')
    </div>

</div>
@endsection

@push('scripts')
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

        if (!res.ok) {
            const texto = await res.text();
            console.error('apiComandas falló:', res.status, texto.slice(0, 500));
            return;
        }

        const data = await res.json();
        if (!data || !data.success) {
            console.error('apiComandas respondió sin success:', data);
            return;
        }

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
    // Carga lateral de Mesas Abiertas (KDS)
    // ---------------------------------------------------------------
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

    // ---------------------------------------------------------------
    // Contador de tiempo de espera por ticket + Alertas Visuales
    // ---------------------------------------------------------------
    function formatearEspera(minutos) {
        if (minutos < 1) return 'Recién enviado';
        if (minutos < 60) return `Espera: ${minutos} min`;
        const horas = Math.floor(minutos / 60);
        const resto = minutos % 60;
        return `Espera: ${horas}h ${resto}min`;
    }

    // AJUSTADO: Manejo de colores de alerta según tiempo transcurrido
    function claseNivelEspera(minutos) {
        if (minutos >= 15) {
            // ROJO Parpadeante (Crítico >= 15 min)
            return 'bg-red-500/20 border-red-500/50 text-red-500 animate-pulse';
        }
        if (minutos >= 10) {
            // AMARILLO (Advertencia 10 - 14 min)
            return 'bg-amber-500/20 border-amber-500/50 text-amber-400';
        }
        // NORMAL (< 10 min)
        return 'bg-zinc-500/10 border-zinc-500/30 text-zinc-400';
    }

    function actualizarContadoresEspera() {
        document.querySelectorAll('.tiempo-espera').forEach((el) => {
            const creado = el.dataset.creado;
            if (!creado) return;

            const minutos = Math.max(0, Math.floor((Date.now() - new Date(creado).getTime()) / 60000));
            const texto = el.querySelector('.tiempo-texto');
            if (texto) texto.textContent = formatearEspera(minutos);

            el.className = 'tiempo-espera shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-lg border text-[10px] font-black uppercase tracking-wide whitespace-nowrap transition-colors ' + claseNivelEspera(minutos);
        });
    }

    // ---------------------------------------------------------------
    // Inicialización del DOM
    // ---------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', () => {
        // Carga inicial
        actualizarContadoresEspera();
        cargarKdsMesas();

        // Intervals de actualización
        setInterval(actualizarComandas, 5000);
        setInterval(cargarKdsMesas, 10000);
        setInterval(actualizarContadoresEspera, 10000); // Evaluado cada 10s para mayor precisión
    });
</script>
@endpush