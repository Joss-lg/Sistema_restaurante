@extends('layouts.admin')

@section('title', 'Caja | Ollintem Pro')

@section('content')
@php
    $mesasLibres = $mesas->where('estado', 'disponible')->count();
@endphp

<div id="toastContainer" class="fixed bottom-8 right-8 z-[9999] flex flex-col gap-3" aria-live="polite" aria-atomic="true"></div>

{{-- Contenedor principal 100% adaptativo con Tailwind --}}
<div class="px-4 py-6 sm:p-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-6 sm:space-y-8 relative z-10 font-sans overflow-x-hidden min-h-screen bg-white dark:bg-[#15171c] transition-colors duration-300">
    
    {{-- ALERTAS DE SESIÓN --}}
    @if(session('error'))
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-2xl">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800 rounded-2xl">
            {{ session('success') }}
        </div>
    @endif
    
    {{-- HEADER Y PANEL FINANCIERO --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 w-full">
        <div class="space-y-3 w-full xl:w-auto flex flex-col sm:flex-row sm:items-center sm:justify-between xl:flex-col xl:items-start">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold uppercase tracking-wider shadow-sm transition-colors bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400">
                    <span class="h-2 w-2 rounded-full bg-blue-600 animate-pulse"></span>
                    Panel Financiero [Turno: {{ $cajaActiva->turno ?? 'N/A' }}]
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tighter break-words text-gray-900 dark:text-slate-100">Panel de Caja</h1>
            </div>
            
            <div class="mt-3 sm:mt-0">
                <button type="button" onclick="toggleModalCierre(true)"
                    class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-bold text-sm shadow-sm transition-all flex items-center gap-2 hover:-translate-y-0.5 active:translate-y-0">
                    🔒 Realizar Corte / Cerrar Caja
                </button>
            </div>
        </div>
        
        {{-- TARJETAS ESTADÍSTICAS (Actualizadas para modo claro y oscuro dinámico) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 w-full xl:w-auto">
            <div class="p-5 sm:p-6 rounded-3xl border border-gray-100 dark:border-slate-700/50 bg-gray-50/50 dark:bg-[#1e2026]/40 shadow-sm flex flex-col justify-center w-full transition-colors duration-300">
                <p class="text-gray-500 dark:text-slate-400 text-[11px] sm:text-xs font-bold uppercase tracking-widest">Total abierto</p>
                <p class="mt-1 sm:mt-2 text-3xl sm:text-4xl font-black text-emerald-500 tracking-tighter" id="total-abierto-display">${{ number_format($totalAbierto ?? 0, 2) }}</p>
            </div>
            <div class="p-5 sm:p-6 rounded-3xl border border-gray-100 dark:border-slate-700/50 bg-gray-50/50 dark:bg-[#1e2026]/40 shadow-sm flex flex-col justify-center w-full transition-colors duration-300">
                <p class="text-gray-500 dark:text-slate-400 text-[11px] sm:text-xs font-bold uppercase tracking-widest">Mesas activas</p>
                <p class="mt-1 sm:mt-2 text-3xl sm:text-4xl font-black tracking-tighter text-gray-900 dark:text-slate-100">{{ $mesasActivas ?? 0 }}</p>
            </div>
            <div class="p-5 sm:p-6 rounded-3xl border border-gray-100 dark:border-slate-700/50 bg-gray-50/50 dark:bg-[#1e2026]/40 shadow-sm flex flex-col justify-center w-full transition-colors duration-300">
                <p class="text-gray-500 dark:text-slate-400 text-[11px] sm:text-xs font-bold uppercase tracking-widest">Mesas libres</p>
                <p class="mt-1 sm:mt-2 text-3xl sm:text-4xl font-black tracking-tighter text-gray-500 dark:text-slate-400">{{ $mesasLibres }}</p>
            </div>
        </div>
    </div>

    {{-- GRID DE MESAS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 pt-2 sm:pt-4 w-full" id="mesas-container">
        @forelse ($mesas as $mesa)
            @php
                $cuenta = $mesa->cuentaActiva ?? null; 
                $theme = [
                    'disponible' => ['text' => 'group-hover:text-emerald-500'],
                    'ocupada'    => ['text' => 'group-hover:text-blue-500'],
                    'mantenimiento' => ['text' => 'group-hover:text-amber-500'],
                ][$mesa->estado] ?? ['text' => 'group-hover:text-gray-500'];
            @endphp

            <a href="{{ route('admin.caja.cobrar', $mesa->id) }}" 
               data-mesa-status="{{ $mesa->estado }}"
               class="group relative flex flex-col w-full rounded-3xl border border-gray-200/60 dark:border-slate-700/60 bg-slate-50/50 dark:bg-[#1e2026]/70 shadow-sm hover:shadow-md cursor-pointer transition-all duration-300 hover:-translate-y-1 overflow-hidden p-5 sm:p-6">
                <div class="relative z-10 flex-1 flex flex-col w-full">
                    <div class="flex justify-between items-start mb-5 sm:mb-6 w-full">
                        <div class="w-full">
                           <h3 class="text-xl sm:text-2xl font-black tracking-tight text-gray-900 dark:text-slate-100 transition-colors {{ $theme['text'] }}">{{ $mesa->numero }}</h3>
                            <p class="text-gray-500 dark:text-slate-400 text-xs font-semibold">Capacidad {{ $mesa->capacidad }} p.</p>
                        </div>
                    </div>
                    <div class="mt-auto w-full">
                        @if($cuenta)
                            <div class="rounded-2xl p-4 flex justify-between items-center w-full border border-gray-200 dark:border-slate-700 bg-gray-100 dark:bg-[#15171c]">
                                <p class="text-xl font-black text-gray-900 dark:text-slate-100">${{ number_format($cuenta->total ?? 0, 2) }}</p>
                            </div>
                        @else
                            <div class="w-full py-3.5 flex items-center justify-center rounded-2xl font-bold border border-gray-200 dark:border-slate-700 bg-slate-50 dark:bg-[#15171c] text-gray-500 dark:text-slate-400 group-hover:bg-emerald-600 dark:group-hover:bg-emerald-600 group-hover:text-white dark:group-hover:text-white group-hover:border-emerald-600 dark:group-hover:border-emerald-600 transition-all duration-200">
                                Mesa Libre
                            </div>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <p class="text-center w-full py-10 text-gray-500 dark:text-slate-400 font-semibold">No hay mesas configuradas</p>
        @endforelse
    </div>
</div>

{{-- MODAL DE CIERRE DE CAJA --}}
<div id="modalCierreCaja" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="inline-block bg-white dark:bg-[#1e2026] rounded-3xl text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full border border-gray-200 dark:border-slate-700 p-6">
            <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-slate-700 pb-3">
                <h3 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                    🔒 Realizar Corte de Caja
                </h3>
                <button type="button" onclick="toggleModalCierre(false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('admin.caja.cerrar') }}" method="POST" class="space-y-4">
                @csrf
                <p class="text-xs font-semibold text-gray-500 dark:text-slate-400">
                    Ingresa el monto total en efectivo que tienes físicamente en la caja para realizar la conciliación automática.
                </p>

                <div>
                    <label for="monto_final_real" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-slate-300 mb-1">Efectivo Físico en Caja</label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 dark:text-slate-400 text-sm">$</span>
                        </div>
                        <input type="number" name="monto_final_real" id="monto_final_real" step="0.01" min="0" required
                            class="w-full pl-7 rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-[#15171c] text-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                            placeholder="0.00" onfocus="this.select()">
                    </div>
                </div>

                <div>
                    <label for="comentarios" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-slate-300 mb-1">Notas de Auditoría (Opcional)</label>
                    <textarea name="comentarios" id="comentarios" rows="3" maxlength="500"
                        class="w-full rounded-xl border-gray-300 dark:border-slate-600 bg-white dark:bg-[#15171c] text-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                        placeholder="Observaciones sobre faltantes, sobrantes o incidentes en el turno..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                    <button type="button" onclick="toggleModalCierre(false)"
                        class="px-4 py-2 text-sm font-bold text-gray-700 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-xl transition">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition shadow-sm">
                        ⚠️ Cerrar Turno Actual
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Forzamos que las funciones sean accesibles globalmente asignándolas explícitamente a window
    window.toggleModalCierre = function(show) {
        const modal = document.getElementById('modalCierreCaja');
        if (!modal) return;
        
        if (show) {
            modal.classList.remove('hidden');
            const inputMonto = document.getElementById('monto_final_real');
            if (inputMonto) inputMonto.focus();
        } else {
            modal.classList.add('hidden');
        }
    };

    window.mostrarToast = function(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        
        const toast = document.createElement('div');
        const typeClasses = type === 'success' 
            ? 'border-l-4 border-emerald-500' 
            : 'border-l-4 border-red-500';

        toast.className = `min-w-[300px] p-4 rounded-2xl bg-white dark:bg-[#1e2026] border border-gray-200 dark:border-slate-700 shadow-xl flex items-center gap-3 opacity-0 translate-x-5 transition-all duration-300 ${typeClasses}`;
        toast.innerHTML = `<div><strong class="block text-sm font-bold text-gray-900 dark:text-white">${type === 'success' ? 'Éxito' : 'Error'}</strong><span class="text-xs text-gray-500 dark:text-slate-400">${message}</span></div>`;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('opacity-0', 'translate-x-5');
        }, 50);

        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-x-5');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    };

    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('[data-filter]');
        const mesaCards = document.querySelectorAll('[data-mesa-status]');

        buttons.forEach(button => {
            button.addEventListener('click', () => {
                buttons.forEach(btn => btn.classList.remove('filter-button--active'));
                button.classList.add('filter-button--active');
                const filter = button.dataset.filter;
                
                mesaCards.forEach(card => {
                    const status = card.dataset.mesaStatus;
                    if (filter === 'all' || status === filter) {
                        card.style.display = 'flex';
                        card.style.opacity = '1';
                    } else {
                        card.style.opacity = '0';
                        setTimeout(() => { card.style.display = 'none'; }, 300);
                    }
                });
            });
        });

        const botonesAbrirMesa = document.querySelectorAll('.btn-abrir-mesa');
        const modalAbrirMesa = document.getElementById('modal-abrir-mesa');
        const btnConfirmarAbrir = document.getElementById('btn-confirmar-abrir-mesa');
        const capacidadInput = document.getElementById('capacidad-personas');
        let mesaSeleccionada = null;

        botonesAbrirMesa.forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (e.target.closest('[data-mesa-delete="true"]')) return;
                mesaSeleccionada = { id: this.dataset.mesaId, numero: this.dataset.mesaNumero };
                if (modalAbrirMesa) modalAbrirMesa.classList.remove('hidden');
            });
        });

        if (btnConfirmarAbrir) {
            btnConfirmarAbrir.addEventListener('click', async () => {
                if (!mesaSeleccionada) return;
                const response = await fetch('{{ route("admin.caja.api.abrir-mesa") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ mesa_id: mesaSeleccionada.id, capacidad: capacidadInput.value })
                });
                const data = await response.json();
                if (data.success) location.reload();
                else alert(data.message);
            });
        }
    });
</script>

@endsection