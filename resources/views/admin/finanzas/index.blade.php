@extends('layouts.admin')

@section('title', 'Finanzas | Flujo de Caja | Ollintem Pro')

@section('content')
<div class="p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col">

    <!-- ENCABEZADO Y ACCIONES -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-2">
        <div>
            <h1 class="text-3xl font-black text-[var(--text-color)] tracking-tight">Flujo de Caja</h1>
            <p class="text-sm text-[var(--text-muted)] mt-1">Control centralizado de ingresos y egresos | {{ now()->format('F Y') }}</p>
        </div>
        
        <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
            <a href="{{ route('admin.finanzas.exportar') }}" 
                class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-xl text-sm font-bold transition-all shadow-lg shadow-emerald-600/20 outline-none flex items-center justify-center gap-2">
                <i class="fas fa-download"></i> Exportar CSV
            </a>

            <button onclick="openModalCrearGasto()" 
                class="w-full md:w-auto bg-rose-600 hover:bg-rose-700 text-white px-5 py-3 rounded-xl text-sm font-bold transition-all shadow-lg shadow-rose-600/20 outline-none flex items-center justify-center gap-2">
                <i class="fas fa-money-bill-wave"></i> Nuevo Gasto
            </button>

            <button onclick="openModalCrearNomina()" 
                class="w-full md:w-auto bg-purple-600 hover:bg-purple-700 text-white px-5 py-3 rounded-xl text-sm font-bold transition-all shadow-lg shadow-purple-600/20 outline-none flex items-center justify-center gap-2">
                <i class="fas fa-users"></i> Pagar Nómina
            </button>
        </div>
    </div>

    <!-- TARJETAS DE MÉTRICAS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- TARJETA: INGRESOS -->
        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-500/5 border border-emerald-500/20 rounded-[1.5rem] p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center text-emerald-500">
                    <i class="fas fa-arrow-up text-xl"></i>
                </div>
                <span class="text-[11px] font-black text-emerald-500 uppercase tracking-wider">Este Mes</span>
            </div>
            <p class="text-[var(--text-muted)] text-xs font-bold uppercase tracking-widest mb-1">Ingresos</p>
            <h3 class="text-2xl font-black text-emerald-500">S/ {{ number_format($ingresosMes, 2) }}</h3>
            <p class="text-[10px] text-[var(--text-muted)] mt-2">Ventas registradas</p>
        </div>

        <!-- TARJETA: EGRESOS -->
        <div class="bg-gradient-to-br from-rose-500/10 to-rose-500/5 border border-rose-500/20 rounded-[1.5rem] p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-rose-500/20 flex items-center justify-center text-rose-500">
                    <i class="fas fa-arrow-down text-xl"></i>
                </div>
                <span class="text-[11px] font-black text-rose-500 uppercase tracking-wider">Este Mes</span>
            </div>
            <p class="text-[var(--text-muted)] text-xs font-bold uppercase tracking-widest mb-1">Egresos</p>
            <h3 class="text-2xl font-black text-rose-500">S/ {{ number_format($egresosMes, 2) }}</h3>
            <p class="text-[10px] text-[var(--text-muted)] mt-2">Gastos registrados</p>
        </div>

        <!-- TARJETA: BALANCE NETO -->
        <div class="bg-gradient-to-br from-[#3B82F6]/10 to-[#3B82F6]/5 border border-[#3B82F6]/20 rounded-[1.5rem] p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-[#3B82F6]/20 flex items-center justify-center text-[#3B82F6]">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <span class="text-[11px] font-black text-[#3B82F6] uppercase tracking-wider">Balance</span>
            </div>
            <p class="text-[var(--text-muted)] text-xs font-bold uppercase tracking-widest mb-1">Neto</p>
            <h3 class="text-2xl font-black {{ $balanceNeto >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                S/ {{ number_format($balanceNeto, 2) }}
            </h3>
            <p class="text-[10px] text-[var(--text-muted)] mt-2">{{ $balanceNeto >= 0 ? '✅ Superávit' : '⚠️ Déficit' }}</p>
        </div>

        <!-- TARJETA: NÓMINA PENDIENTE -->
        <div class="bg-gradient-to-br from-purple-500/10 to-purple-500/5 border border-purple-500/20 rounded-[1.5rem] p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center text-purple-500">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <span class="text-[11px] font-black text-purple-500 uppercase tracking-wider">Pendiente</span>
            </div>
            <p class="text-[var(--text-muted)] text-xs font-bold uppercase tracking-widest mb-1">Nómina</p>
            <h3 class="text-2xl font-black text-purple-500">S/ {{ number_format($nominaPendiente, 2) }}</h3>
            <p class="text-[10px] text-[var(--text-muted)] mt-2">Por pagar a empleados</p>
        </div>
    </div>

    <!-- PESTAÑAS DE FILTRO -->
    <div class="flex items-center gap-3 border-b border-black/5 py-4">
        <a href="{{ route('admin.finanzas.index', ['tab' => 'todos']) }}" 
            class="px-6 py-2 text-sm font-bold {{ $tab === 'todos' ? 'text-[#3B82F6] border-b-2 border-[#3B82F6]' : 'text-[var(--text-muted)]' }} transition-all">
            <i class="fas fa-list mr-2"></i> Todos
        </a>
        <a href="{{ route('admin.finanzas.index', ['tab' => 'ingresos']) }}" 
            class="px-6 py-2 text-sm font-bold {{ $tab === 'ingresos' ? 'text-emerald-500 border-b-2 border-emerald-500' : 'text-[var(--text-muted)]' }} transition-all">
            <i class="fas fa-arrow-up mr-2"></i> Ingresos
        </a>
        <a href="{{ route('admin.finanzas.index', ['tab' => 'egresos']) }}" 
            class="px-6 py-2 text-sm font-bold {{ $tab === 'egresos' ? 'text-rose-500 border-b-2 border-rose-500' : 'text-[var(--text-muted)]' }} transition-all">
            <i class="fas fa-arrow-down mr-2"></i> Egresos
        </a>
    </div>

    <!-- TABLA DEL FLUJO DE CAJA -->
    <div class="bg-[var(--card-color)] rounded-[1.5rem] shadow-sm p-6 lg:p-8 w-full">
        
        <div class="mb-6 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#3B82F6]/10 flex items-center justify-center text-[#3B82F6]">
                <i class="fas fa-exchange-alt text-lg"></i>
            </div>
            <h2 class="text-xl font-bold text-[var(--text-color)]">Historial | <span class="text-[var(--text-muted)] font-normal text-sm">{{ $flujosCaja->total() }} registros</span></h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Fecha</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Tipo</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Categoría</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Concepto</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Método de Pago</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider text-right">Monto</th>
                    </tr>
                </thead>
                <tbody id="tablaFlujoCaja">
                    @forelse($flujosCaja as $flujo)
                    <tr class="fila-flujo border-none hover:bg-black/5 transition-colors group rounded-xl">
                        
                        <td class="py-4 px-4 text-xs font-mono text-[var(--text-muted)]">
                            {{ $flujo->fecha->format('d/m/Y H:i') }}
                        </td>

                        <td class="py-4 px-4">
                            @if($flujo->tipo === 'ingreso')
                                <span class="px-3 py-1.5 bg-emerald-500/10 text-emerald-500 rounded-full text-[11px] font-black uppercase tracking-wider flex items-center gap-2 w-fit">
                                    <i class="fas fa-arrow-up text-xs"></i> Ingreso
                                </span>
                            @else
                                <span class="px-3 py-1.5 bg-rose-500/10 text-rose-500 rounded-full text-[11px] font-black uppercase tracking-wider flex items-center gap-2 w-fit">
                                    <i class="fas fa-arrow-down text-xs"></i> Egreso
                                </span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-sm font-bold text-[var(--text-color)]">
                            <span class="px-3 py-1 bg-black/5 rounded-md text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-widest">
                                {{ $flujo->categoria }}
                            </span>
                        </td>

                        <td class="py-4 px-4 text-sm font-medium text-[var(--text-color)] concepto-celda">
                            {{ $flujo->concepto }}
                        </td>

                        <td class="py-4 px-4">
                            <span class="px-3 py-1 bg-black/5 rounded-md text-[10px] font-bold text-[var(--text-muted)]">
                                {{ $flujo->metodo_pago }}
                            </span>
                        </td>

                        <td class="py-4 px-4 text-right font-bold text-lg {{ $flujo->tipo === 'ingreso' ? 'text-emerald-500' : 'text-rose-500' }}">
                            {{ $flujo->tipo === 'ingreso' ? '+' : '-' }}S/ {{ number_format($flujo->monto, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-[var(--text-muted)]">
                            No hay registros de flujo de caja aún.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div class="mt-6 flex justify-center">
            {{ $flujosCaja->links() }}
        </div>
    </div>

    <!-- GRID DE ESTADÍSTICAS ADICIONALES -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- CATEGORÍAS DE INGRESOS -->
        <div class="bg-[var(--card-color)] rounded-[1.5rem] shadow-sm p-6 lg:p-8">
            <div class="mb-6 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                    <i class="fas fa-chart-pie text-lg"></i>
                </div>
                <h3 class="text-lg font-bold text-[var(--text-color)]">Ingresos por Categoría</h3>
            </div>

            <div class="space-y-3">
                @forelse($categoriasIngresos as $cat)
                <div class="flex items-center justify-between p-3 bg-black/2 rounded-lg hover:bg-black/5 transition-colors">
                    <div>
                        <p class="text-sm font-bold text-[var(--text-color)]">{{ $cat->categoria }}</p>
                        <p class="text-xs text-[var(--text-muted)]">{{ $cat->cantidad }} transacciones</p>
                    </div>
                    <p class="text-lg font-black text-emerald-500">S/ {{ number_format($cat->total, 2) }}</p>
                </div>
                @empty
                <p class="text-center text-[var(--text-muted)] py-6">Sin ingresos este período</p>
                @endforelse
            </div>
        </div>

        <!-- CATEGORÍAS DE EGRESOS -->
        <div class="bg-[var(--card-color)] rounded-[1.5rem] shadow-sm p-6 lg:p-8">
            <div class="mb-6 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 flex items-center justify-center text-rose-500">
                    <i class="fas fa-chart-pie text-lg"></i>
                </div>
                <h3 class="text-lg font-bold text-[var(--text-color)]">Egresos por Categoría</h3>
            </div>

            <div class="space-y-3">
                @forelse($categoriasEgresos as $cat)
                <div class="flex items-center justify-between p-3 bg-black/2 rounded-lg hover:bg-black/5 transition-colors">
                    <div>
                        <p class="text-sm font-bold text-[var(--text-color)]">{{ $cat->categoria }}</p>
                        <p class="text-xs text-[var(--text-muted)]">{{ $cat->cantidad }} transacciones</p>
                    </div>
                    <p class="text-lg font-black text-rose-500">S/ {{ number_format($cat->total, 2) }}</p>
                </div>
                @empty
                <p class="text-center text-[var(--text-muted)] py-6">Sin egresos este período</p>
                @endforelse
            </div>
        </div>
    </div>

</div>

<script>
    // Función para abrir modal de crear gasto
    function openModalCrearGasto() {
        const modal = document.getElementById('modalCrearGasto');
        const container = document.getElementById('createGastoContainer');
        if (!modal || !container) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeCreateGastoModal() {
        const modal = document.getElementById('modalCrearGasto');
        const container = document.getElementById('createGastoContainer');
        if(container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }

    // Función para abrir modal de crear nómina
    function openModalCrearNomina() {
        const modal = document.getElementById('modalCrearNomina');
        const container = document.getElementById('createNominaContainer');
        if (!modal || !container) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeCreateNominaModal() {
        const modal = document.getElementById('modalCrearNomina');
        const container = document.getElementById('createNominaContainer');
        if(container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }

    // Buscador en tiempo real de flujo de caja
    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscadorFlujo');
        const filas = document.querySelectorAll('.fila-flujo');
        if (buscador) {
            buscador.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase().trim();
                filas.forEach(fila => {
                    const concepto = fila.querySelector('.concepto-celda').textContent.toLowerCase();
                    fila.style.display = concepto.includes(term) ? '' : 'none';
                });
            });
        }
    });
</script>

@include('admin.finanzas.modal-crear-gasto')
@include('admin.finanzas.modal-crear-nomina')
@endsection
