@extends('layouts.admin')

@section('title', 'Finanzas | Flujo de Caja | Ollintem Pro')

@section('content')
<div class="p-6 sm:p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col transition-all duration-300">

    {{-- CABECERA Y BOTONES --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end gap-6 mb-2">
        <div class="space-y-1">
            <h1 class="text-3xl sm:text-4xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight">Flujo de Caja</h1>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Control centralizado de ingresos y egresos | <span class="text-zinc-700 dark:text-zinc-300 font-bold capitalize">{{ now()->format('F Y') }}</span></p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full xl:w-auto">
            @if(auth()->user()->tienePermiso('finanzas.reporte'))
                <a href="{{ route('admin.finanzas.exportar') }}"
                    class="w-full sm:w-auto bg-white hover:bg-zinc-50 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700 px-5 py-3 rounded-xl text-sm font-bold transition-all outline-none flex items-center justify-center gap-2 hover:-translate-y-0.5 shadow-sm">
                    <i class="fas fa-download"></i> Exportar CSV
                </a>
            @endif

            @if(auth()->user()->tienePermiso('finanzas.editar'))
                <button onclick="openModalCrearNomina()"
                    class="w-full sm:w-auto bg-purple-50 hover:bg-purple-100 dark:bg-purple-600/10 dark:hover:bg-purple-600/20 text-purple-700 dark:text-purple-500 border border-purple-200 dark:border-purple-500/20 px-5 py-3 rounded-xl text-sm font-bold transition-all outline-none flex items-center justify-center gap-2 hover:-translate-y-0.5 shadow-sm">
                    <i class="fas fa-users"></i> Pagar Nómina
                </button>
            @endif

            @if(auth()->user()->tienePermiso('finanzas.agregar'))
                <button onclick="openModalCrearGasto()"
                    class="w-full sm:w-auto bg-rose-500 hover:bg-rose-600 dark:bg-rose-600 dark:hover:bg-rose-500 text-white shadow-lg shadow-rose-600/20 px-6 py-3 rounded-xl text-sm font-bold transition-all outline-none flex items-center justify-center gap-2 hover:-translate-y-0.5">
                    <i class="fas fa-plus"></i> Nuevo Gasto
                </button>
            @endif
        </div>
    </div>

    {{-- TARJETAS DE INDICADORES (KPIs) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        {{-- Ingresos --}}
        <div class="relative overflow-hidden bg-white dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-3xl p-6 group hover:border-emerald-200 dark:hover:border-emerald-500/40 transition-colors shadow-sm">
            <div class="absolute top-0 right-0 p-4 opacity-[0.05] dark:opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-arrow-up text-6xl text-emerald-500"></i>
            </div>
            <div class="flex items-center justify-between mb-4 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-500 border border-emerald-100 dark:border-emerald-500/20">
                    <i class="fas fa-arrow-up text-lg"></i>
                </div>
                <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-transparent rounded-full text-[10px] font-black text-emerald-700 dark:text-emerald-500 uppercase tracking-widest">Este Mes</span>
            </div>
            <div class="relative z-10">
                <p class="text-emerald-600 dark:text-emerald-600/70 text-[11px] font-bold uppercase tracking-widest mb-1">Ingresos</p>
                <h3 class="text-3xl font-black text-emerald-600 dark:text-emerald-400">$ {{ number_format($ingresosMes, 2) }}</h3>
                <p class="text-[11px] font-medium text-emerald-500 dark:text-emerald-600/60 mt-2">Ventas registradas</p>
            </div>
        </div>

        {{-- Egresos --}}
        <div class="relative overflow-hidden bg-white dark:bg-rose-500/5 border border-rose-100 dark:border-rose-500/20 rounded-3xl p-6 group hover:border-rose-200 dark:hover:border-rose-500/40 transition-colors shadow-sm">
            <div class="absolute top-0 right-0 p-4 opacity-[0.05] dark:opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-arrow-down text-6xl text-rose-500"></i>
            </div>
            <div class="flex items-center justify-between mb-4 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center text-rose-600 dark:text-rose-500 border border-rose-100 dark:border-rose-500/20">
                    <i class="fas fa-arrow-down text-lg"></i>
                </div>
                <span class="px-3 py-1 bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-transparent rounded-full text-[10px] font-black text-rose-700 dark:text-rose-500 uppercase tracking-widest">Este Mes</span>
            </div>
            <div class="relative z-10">
                <p class="text-rose-600 dark:text-rose-600/70 text-[11px] font-bold uppercase tracking-widest mb-1">Egresos</p>
                <h3 class="text-3xl font-black text-rose-600 dark:text-rose-400">$ {{ number_format($egresosMes, 2) }}</h3>
                <p class="text-[11px] font-medium text-rose-500 dark:text-rose-600/60 mt-2">Gastos registrados</p>
            </div>
        </div>

        {{-- Balance --}}
        <div class="relative overflow-hidden bg-white dark:bg-blue-500/5 border border-blue-100 dark:border-blue-500/20 rounded-3xl p-6 group hover:border-blue-200 dark:hover:border-blue-500/40 transition-colors shadow-sm">
            <div class="absolute top-0 right-0 p-4 opacity-[0.05] dark:opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-wallet text-6xl text-blue-500"></i>
            </div>
            <div class="flex items-center justify-between mb-4 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-500 border border-blue-100 dark:border-blue-500/20">
                    <i class="fas fa-chart-line text-lg"></i>
                </div>
                <span class="px-3 py-1 bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-transparent rounded-full text-[10px] font-black text-blue-700 dark:text-blue-500 uppercase tracking-widest">Balance</span>
            </div>
            <div class="relative z-10">
                <p class="text-blue-600 dark:text-blue-600/70 text-[11px] font-bold uppercase tracking-widest mb-1">Neto</p>
                <h3 class="text-3xl font-black {{ $balanceNeto >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-rose-600 dark:text-rose-400' }}">
                    $ {{ number_format($balanceNeto, 2) }}
                </h3>
                <p class="text-[11px] font-medium mt-2 {{ $balanceNeto >= 0 ? 'text-blue-500 dark:text-blue-600/60' : 'text-rose-500 dark:text-rose-600/60' }}">
                    {{ $balanceNeto >= 0 ? 'Superávit registrado' : 'Déficit registrado' }}
                </p>
            </div>
        </div>

        {{-- Pendiente Nómina --}}
        <div class="relative overflow-hidden bg-white dark:bg-purple-500/5 border border-purple-100 dark:border-purple-500/20 rounded-3xl p-6 group hover:border-purple-200 dark:hover:border-purple-500/40 transition-colors shadow-sm">
            <div class="absolute top-0 right-0 p-4 opacity-[0.05] dark:opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-clock text-6xl text-purple-500"></i>
            </div>
            <div class="flex items-center justify-between mb-4 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center text-purple-600 dark:text-purple-500 border border-purple-100 dark:border-purple-500/20">
                    <i class="fas fa-clock text-lg"></i>
                </div>
                <span class="px-3 py-1 bg-purple-50 dark:bg-purple-500/10 border border-purple-100 dark:border-transparent rounded-full text-[10px] font-black text-purple-700 dark:text-purple-500 uppercase tracking-widest">Pendiente</span>
            </div>
            <div class="relative z-10">
                <p class="text-purple-600 dark:text-purple-600/70 text-[11px] font-bold uppercase tracking-widest mb-1">Nómina</p>
                <h3 class="text-3xl font-black text-purple-600 dark:text-purple-400">$ {{ number_format($nominaPendiente, 2) }}</h3>
                <p class="text-[11px] font-medium text-purple-500 dark:text-purple-600/60 mt-2">Por pagar a empleados</p>
            </div>
        </div>
    </div>

    {{-- PESTAÑAS (TABS) --}}
    <div class="flex flex-wrap items-center gap-2 p-1 bg-zinc-100 dark:bg-zinc-900/80 border border-zinc-200/60 dark:border-zinc-800 rounded-2xl w-fit">
        <a href="{{ route('admin.finanzas.index', ['tab' => 'todos']) }}"
            class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $tab === 'todos' ? 'bg-white dark:bg-zinc-800 text-blue-600 dark:text-blue-400 shadow-sm border border-zinc-200 dark:border-zinc-700' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 border border-transparent' }}">
            <i class="fas fa-list"></i> Todos
        </a>
        <a href="{{ route('admin.finanzas.index', ['tab' => 'ingresos']) }}"
            class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $tab === 'ingresos' ? 'bg-white dark:bg-zinc-800 text-emerald-600 dark:text-emerald-400 shadow-sm border border-zinc-200 dark:border-zinc-700' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 border border-transparent' }}">
            <i class="fas fa-arrow-up"></i> Ingresos
        </a>
        <a href="{{ route('admin.finanzas.index', ['tab' => 'egresos']) }}"
            class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2 {{ $tab === 'egresos' ? 'bg-white dark:bg-zinc-800 text-rose-600 dark:text-rose-400 shadow-sm border border-zinc-200 dark:border-zinc-700' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 border border-transparent' }}">
            <i class="fas fa-arrow-down"></i> Egresos
        </a>
    </div>

    {{-- TABLA DE HISTORIAL --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-lg p-6 lg:p-8 w-full flex-1">
        
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 flex items-center justify-center text-blue-600 dark:text-blue-500">
                    <i class="fas fa-exchange-alt text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight">Historial de Transacciones</h2>
                    <p class="text-zinc-500 dark:text-zinc-500 text-sm font-medium mt-0.5">Total: {{ $flujosCaja->total() }} registros encontrados</p>
                </div>
            </div>
            
            <div class="relative w-full sm:w-64">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-zinc-400 dark:text-zinc-500"></i>
                <input type="text" id="buscadorFlujo" placeholder="Buscar concepto..." 
                    class="w-full bg-white dark:bg-zinc-950 border border-zinc-200/60 dark:border-zinc-800 rounded-xl py-2.5 pl-11 pr-4 text-sm text-zinc-800 dark:text-zinc-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-600 shadow-sm">
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-zinc-200 dark:border-zinc-800/50">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead class="bg-zinc-50 dark:bg-zinc-950/50 border-b border-zinc-200 dark:border-zinc-800/50">
                    <tr>
                        <th class="py-4 px-5 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Fecha</th>
                        <th class="py-4 px-5 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Tipo</th>
                        <th class="py-4 px-5 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Categoría</th>
                        <th class="py-4 px-5 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Concepto</th>
                        <th class="py-4 px-5 text-[10px] font-black text-zinc-500 uppercase tracking-widest">Método</th>
                        <th class="py-4 px-5 text-[10px] font-black text-zinc-500 uppercase tracking-widest text-right">Monto</th>
                    </tr>
                </thead>
                <tbody id="tablaFlujoCaja" class="divide-y divide-zinc-100 dark:divide-zinc-800/50">
                    @forelse($flujosCaja as $flujo)
                    <tr class="fila-flujo hover:bg-zinc-50/80 dark:hover:bg-zinc-800/30 transition-colors group">
                        
                        <td class="py-4 px-5 text-sm font-medium text-zinc-600 dark:text-zinc-400">
                            {{ $flujo->fecha->format('d M, Y') }}
                        </td>

                        <td class="py-4 px-5">
                            @if($flujo->tipo === 'ingreso')
                                <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-full text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 w-fit">
                                    <i class="fas fa-arrow-up text-[10px]"></i> Ingreso
                                </span>
                            @else
                                <span class="px-3 py-1 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 rounded-full text-[10px] font-black uppercase tracking-wider flex items-center gap-1.5 w-fit">
                                    <i class="fas fa-arrow-down text-[10px]"></i> Egreso
                                </span>
                            @endif
                        </td>

                        <td class="py-4 px-5">
                            <span class="px-3 py-1.5 bg-white dark:bg-zinc-800 rounded-lg text-[10px] font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-widest border border-zinc-200 dark:border-zinc-700/50 shadow-sm">
                                {{ $flujo->categoria }}
                            </span>
                        </td>

                        <td class="py-4 px-5 text-sm font-bold text-zinc-800 dark:text-zinc-200 concepto-celda">
                            {{ $flujo->concepto }}
                        </td>

                        <td class="py-4 px-5">
                            <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                                <i class="fas fa-credit-card text-xs opacity-70"></i>
                                <span class="text-xs font-semibold">{{ $flujo->metodo_pago }}</span>
                            </div>
                        </td>

                        <td class="py-4 px-5 text-right font-black text-base {{ $flujo->tipo === 'ingreso' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $flujo->tipo === 'ingreso' ? '+' : '-' }}${{ number_format($flujo->monto, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center bg-zinc-50/50 dark:bg-zinc-950/20">
                            <div class="flex flex-col items-center justify-center text-zinc-500 dark:text-zinc-500">
                                <i class="fas fa-folder-open text-4xl mb-3 opacity-40 dark:opacity-20"></i>
                                <p class="text-sm font-medium">No hay registros de flujo de caja aún.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-between items-center">
            {{ $flujosCaja->links() }}
        </div>
    </div>

    {{-- RESUMEN POR CATEGORÍAS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- Resumen Ingresos --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-lg p-6 lg:p-8">
            <div class="mb-6 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-500">
                    <i class="fas fa-chart-pie text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-zinc-900 dark:text-zinc-100">Ingresos por Categoría</h3>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-500 mt-0.5">Distribución del período</p>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($categoriasIngresos as $cat)
                <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-950/40 border border-zinc-100 dark:border-zinc-800/30 rounded-2xl hover:bg-zinc-100 dark:hover:bg-zinc-800/50 transition-colors group">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-8 bg-emerald-400 dark:bg-emerald-500/50 rounded-full"></div>
                        <div>
                            <p class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ $cat->categoria }}</p>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-500 mt-0.5">{{ $cat->cantidad }} transacciones</p>
                        </div>
                    </div>
                    <p class="text-lg font-black text-emerald-600 dark:text-emerald-400">$ {{ number_format($cat->total, 2) }}</p>
                </div>
                @empty
                <div class="py-8 text-center bg-zinc-50 dark:bg-zinc-950/20 rounded-2xl border border-dashed border-zinc-200 dark:border-zinc-800">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-500">Sin ingresos este período</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Resumen Egresos --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl shadow-lg p-6 lg:p-8">
            <div class="mb-6 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20 flex items-center justify-center text-rose-600 dark:text-rose-500">
                    <i class="fas fa-chart-pie text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-zinc-900 dark:text-zinc-100">Egresos por Categoría</h3>
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-500 mt-0.5">Distribución del período</p>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($categoriasEgresos as $cat)
                <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-950/40 border border-zinc-100 dark:border-zinc-800/30 rounded-2xl hover:bg-zinc-100 dark:hover:bg-zinc-800/50 transition-colors group">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-8 bg-rose-400 dark:bg-rose-500/50 rounded-full"></div>
                        <div>
                            <p class="text-sm font-bold text-zinc-800 dark:text-zinc-200">{{ $cat->categoria }}</p>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-500 mt-0.5">{{ $cat->cantidad }} transacciones</p>
                        </div>
                    </div>
                    <p class="text-lg font-black text-rose-600 dark:text-rose-400">$ {{ number_format($cat->total, 2) }}</p>
                </div>
                @empty
                <div class="py-8 text-center bg-zinc-50 dark:bg-zinc-950/20 rounded-2xl border border-dashed border-zinc-200 dark:border-zinc-800">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-500">Sin egresos este período</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

<script>
    function openTailwindModal(modalId, containerId) {
        const modal = document.getElementById(modalId);
        const container = document.getElementById(containerId);
        if (!modal || !container) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeTailwindModal(modalId, containerId) {
        const modal = document.getElementById(modalId);
        const container = document.getElementById(containerId);
        if (container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }, 200);
    }

    function openModalCrearGasto() { openTailwindModal('modalCrearGasto', 'createGastoContainer'); }
    function closeCreateGastoModal() { closeTailwindModal('modalCrearGasto', 'createGastoContainer'); }
    function openModalCrearNomina() { openTailwindModal('modalCrearNomina', 'createNominaContainer'); }
    function closeCreateNominaModal() { closeTailwindModal('modalCrearNomina', 'createNominaContainer'); }

    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscadorFlujo');
        const filas = document.querySelectorAll('.fila-flujo');

        if (buscador) {
            buscador.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase().trim();
                filas.forEach(fila => {
                    const celdaConcepto = fila.querySelector('.concepto-celda');
                    if (!celdaConcepto) return;

                    const concepto = celdaConcepto.textContent.toLowerCase();
                    if (concepto.includes(term)) {
                        fila.classList.remove('hidden');
                    } else {
                        fila.classList.add('hidden');
                    }
                });
            });
        }
    });
</script>
@endsection

@if(auth()->user()->tienePermiso('finanzas.crear'))
    @include('admin.finanzas.modal-crear-gasto')
@endif

@if(auth()->user()->tienePermiso('finanzas.editar'))
    @include('admin.finanzas.modal-crear-nomina')
@endif