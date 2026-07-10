@extends('layouts.admin')

@section('title', 'Caja | Ollintem Pro')

@section('content')
@php
    $mesasLibres = $mesas->where('estado', 'disponible')->count();
@endphp

<div id="toastContainer" class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-8 sm:bottom-8 z-[9999] flex flex-col gap-3 items-center sm:items-end" aria-live="polite" aria-atomic="true"></div>

{{-- Contenedor principal --}}
<div class="px-3 py-4 sm:px-4 sm:py-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-5 sm:space-y-8 relative z-10 font-sans overflow-x-hidden min-h-screen bg-white dark:bg-[#15171c] transition-colors duration-300">
    
    {{-- ALERTAS DE SESIÓN --}}
    @if(session('error'))
        <div class="p-3 sm:p-4 mb-4 text-xs sm:text-sm text-red-700 bg-red-100 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-2xl">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="p-3 sm:p-4 mb-4 text-xs sm:text-sm text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800 rounded-2xl">
            {{ session('success') }}
        </div>
    @endif
    
    {{-- HEADER Y PANEL FINANCIERO --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-5 sm:gap-6 w-full">
        <div class="space-y-2.5 sm:space-y-3 w-full xl:w-auto flex flex-col sm:flex-row sm:items-center sm:justify-between xl:flex-col xl:items-start">
            <div class="w-full">
                <div class="inline-flex items-center gap-2 rounded-full px-2.5 sm:px-3 py-1 sm:py-1.5 text-[10px] sm:text-xs font-bold uppercase tracking-wider shadow-sm transition-colors bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400 max-w-full flex-wrap">
                    <span class="h-2 w-2 rounded-full bg-blue-600 animate-pulse shrink-0"></span>
                    <span class="truncate">Panel Financiero [Turno: {{ $cajaActiva->turno ?? 'N/A' }}]</span>
                </div>
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black tracking-tighter break-words text-gray-900 dark:text-slate-100 mt-1">Panel de Caja</h1>
            </div>
        </div>
        
        {{-- TARJETAS ESTADÍSTICAS --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-4 w-full xl:w-auto">
            <div class="col-span-2 sm:col-span-1 p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl border border-gray-100 dark:border-slate-700/50 bg-gray-50/50 dark:bg-[#1e2026]/40 shadow-sm flex flex-col justify-center w-full transition-colors duration-300">
                <p class="text-gray-500 dark:text-slate-400 text-[10px] sm:text-xs font-bold uppercase tracking-widest">Total abierto</p>
                <p class="mt-1 sm:mt-2 text-xl sm:text-4xl font-black text-emerald-500 tracking-tighter" id="total-abierto-display">${{ number_format($totalAbierto ?? 0, 2) }}</p>
            </div>
            <div class="p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl border border-gray-100 dark:border-slate-700/50 bg-gray-50/50 dark:bg-[#1e2026]/40 shadow-sm flex flex-col justify-center w-full transition-colors duration-300">
                <p class="text-gray-500 dark:text-slate-400 text-[10px] sm:text-xs font-bold uppercase tracking-widest">Mesas activas</p>
                <p class="mt-1 sm:mt-2 text-xl sm:text-4xl font-black tracking-tighter text-gray-900 dark:text-slate-100">{{ $mesasActivas ?? 0 }}</p>
            </div>
            <div class="p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl border border-gray-100 dark:border-slate-700/50 bg-gray-50/50 dark:bg-[#1e2026]/40 shadow-sm flex flex-col justify-center w-full transition-colors duration-300">
                <p class="text-gray-500 dark:text-slate-400 text-[10px] sm:text-xs font-bold uppercase tracking-widest">Mesas libres</p>
                <p class="mt-1 sm:mt-2 text-xl sm:text-4xl font-black tracking-tighter text-gray-500 dark:text-slate-400">{{ $mesasLibres }}</p>
            </div>
        </div>
    </div>

    {{-- GRID DE MESAS --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-6 pt-1 sm:pt-4 w-full" id="mesas-container">
        @forelse ($mesas as $mesa)
            @php
                $cuenta = $mesa->ordenesActivas->first() ?? null; 
            @endphp

            @if($cuenta)
                {{-- 🟢 MESA CON ORDEN ACTIVA --}}
                <a href="{{ route('admin.caja.cobrar', $mesa->id) }}" 
                   data-mesa-status="{{ $mesa->estado }}"
                   class="group relative flex flex-col w-full rounded-2xl sm:rounded-3xl border border-emerald-200 dark:border-emerald-800/60 bg-emerald-50/10 dark:bg-emerald-950/10 shadow-sm hover:shadow-md active:scale-[0.98] cursor-pointer transition-all duration-300 hover:-translate-y-1 overflow-hidden p-3.5 sm:p-6">
                    <div class="relative z-10 flex-1 flex flex-col w-full">
                        <div class="flex justify-between items-start mb-3.5 sm:mb-6 w-full">
                            <div class="w-full">
                               <h3 class="text-base sm:text-2xl font-black tracking-tight text-gray-900 dark:text-slate-100 transition-colors group-hover:text-emerald-500 truncate">{{ $mesa->numero }}</h3>
                                <p class="text-gray-500 dark:text-slate-400 text-[10px] sm:text-xs font-semibold">Cap. {{ $mesa->capacidad }} p.</p>
                            </div>
                        </div>
                        <div class="mt-auto w-full space-y-2 sm:space-y-3">
                            <div class="rounded-xl sm:rounded-2xl p-2.5 sm:p-4 flex justify-between items-center w-full border border-emerald-200 dark:border-emerald-800/50 bg-white dark:bg-[#15171c]">
                                <p class="text-sm sm:text-xl font-black text-emerald-600 dark:text-emerald-400">${{ number_format($mesa->total_consumo ?? 0, 2) }}</p>
                            </div>
                            <div class="w-full py-2.5 sm:py-3.5 flex items-center justify-center rounded-xl sm:rounded-2xl font-bold text-xs sm:text-base bg-emerald-600 dark:bg-emerald-600 text-white shadow-sm transition-all duration-200 group-hover:bg-emerald-700">
                                💰 <span class="hidden sm:inline ml-1">Cobrar</span>
                            </div>
                        </div>
                    </div>
                </a>
            @else
                {{-- 🔴 MESA SIN ORDEN --}}
                <div data-mesa-status="{{ $mesa->estado }}"
                   class="relative flex flex-col w-full rounded-2xl sm:rounded-3xl border border-red-200/50 dark:border-red-950/60 bg-red-50/5 dark:bg-red-950/5 shadow-sm overflow-hidden p-3.5 sm:p-6">
                    <div class="relative z-10 flex-1 flex flex-col w-full">
                        <div class="flex justify-between items-start mb-3.5 sm:mb-6 w-full">
                            <div class="w-full">
                               <h3 class="text-base sm:text-2xl font-black tracking-tight text-gray-400 dark:text-slate-500 truncate">{{ $mesa->numero }}</h3>
                                <p class="text-gray-400 dark:text-slate-500 text-[10px] sm:text-xs font-semibold">Cap. {{ $mesa->capacidad }} p.</p>
                            </div>
                        </div>
                        <div class="mt-auto w-full">
                            <div class="w-full py-2.5 sm:py-3.5 flex items-center justify-center rounded-xl sm:rounded-2xl font-bold text-[10px] sm:text-base border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-950/20 text-red-500 dark:text-red-400 cursor-not-allowed select-none text-center leading-tight px-1">
                                ❌ <span class="hidden sm:inline ml-1">Mesa sin orden</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <p class="col-span-2 lg:col-span-3 xl:col-span-4 text-center w-full py-10 text-gray-500 dark:text-slate-400 font-semibold">No hay mesas configuradas</p>
        @endforelse
    </div>
</div>

<script>
    window.mostrarToast = function(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        
        const toast = document.createElement('div');
        const typeClasses = type === 'success' ? 'border-l-4 border-emerald-500' : 'border-l-4 border-red-500';

        toast.className = `w-full sm:min-w-[300px] sm:w-auto p-4 rounded-2xl bg-white dark:bg-[#1e2026] border border-gray-200 dark:border-slate-700 shadow-xl flex items-center gap-3 opacity-0 translate-y-3 sm:translate-y-0 sm:translate-x-5 transition-all duration-300 ${typeClasses}`;
        toast.innerHTML = `<div><strong class="block text-sm font-bold text-gray-900 dark:text-white">${type === 'success' ? 'Éxito' : 'Error'}</strong><span class="text-xs text-gray-500 dark:text-slate-400">${message}</span></div>`;
        
        container.appendChild(toast);
        
        setTimeout(() => { toast.classList.remove('opacity-0', 'translate-y-3', 'sm:translate-x-5'); }, 50);
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-3', 'sm:translate-x-5');
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
    });
</script>
@endsection