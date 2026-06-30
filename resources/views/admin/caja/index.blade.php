@extends('layouts.admin')

@section('title', 'Caja | Ollintem Pro')

@section('content')
@php
    $mesasLibres = $mesas->where('estado', 'disponible')->count();
@endphp

<div id="toastContainer" class="toast-wrapper" aria-live="polite" aria-atomic="true"></div>

{{-- Contenedor principal con fondo adaptativo --}}
<div class="px-4 py-6 sm:p-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-6 sm:space-y-8 relative z-10 font-sans overflow-x-hidden min-h-screen bg-white dark:bg-[#15171c]">
    
    {{-- ALERTAS DE SESIÓN (Persistentes tras recarga) --}}
    @if(session('error'))
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif
    
    {{-- HEADER Y PANEL FINANCIERO --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 w-full">
        <div class="space-y-3 w-full xl:w-auto">
            <div class="caja-badge inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold uppercase tracking-wider shadow-sm transition-colors">
                <span class="h-2 w-2 rounded-full bg-blue-600 animate-pulse"></span>
                Panel Financiero
            </div>
            <h1 class="caja-main-title text-3xl sm:text-4xl lg:text-5xl font-black tracking-tighter break-words">Panel de Caja</h1>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 w-full xl:w-auto">
            <div class="caja-card p-5 sm:p-6 rounded-3xl shadow-sm flex flex-col justify-center w-full transition-colors">
                <p class="caja-text-muted text-[11px] sm:text-xs font-bold uppercase tracking-widest">Total abierto</p>
                <p class="mt-1 sm:mt-2 text-3xl sm:text-4xl font-black text-emerald-500 tracking-tighter" id="total-abierto-display">${{ number_format($totalAbierto ?? 0, 2) }}</p>
            </div>
            <div class="caja-card p-5 sm:p-6 rounded-3xl shadow-sm flex flex-col justify-center w-full transition-colors">
                <p class="caja-text-muted text-[11px] sm:text-xs font-bold uppercase tracking-widest">Mesas activas</p>
                <p class="caja-main-title mt-1 sm:mt-2 text-3xl sm:text-4xl font-black tracking-tighter">{{ $mesasActivas ?? 0 }}</p>
            </div>
            <div class="caja-card p-5 sm:p-6 rounded-3xl shadow-sm flex flex-col justify-center w-full transition-colors">
                <p class="caja-text-muted text-[11px] sm:text-xs font-bold uppercase tracking-widest">Mesas libres</p>
                <p class="caja-text-muted mt-1 sm:mt-2 text-3xl sm:text-4xl font-black tracking-tighter">{{ $mesasLibres }}</p>
            </div>
        </div>
    </div>

    {{-- GRID DE MESAS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 pt-2 sm:pt-4 w-full" id="mesas-container">
        @forelse ($mesas as $mesa)
            @php
                $cuenta = $mesa->cuentaActiva ?? null; 
                $theme = [
                    'disponible' => ['dot' => 'bg-emerald-500', 'text' => 'text-emerald-500'],
                    'ocupada'    => ['dot' => 'bg-blue-500', 'text' => 'text-blue-500'],
                    'mantenimiento' => ['dot' => 'bg-amber-500', 'text' => 'text-amber-500'],
                ][$mesa->estado] ?? ['dot' => 'bg-gray-400', 'text' => 'text-gray-500'];
            @endphp

            <a href="{{ route('admin.caja.cobrar', $mesa->id) }}" 
               data-mesa-status="{{ $mesa->estado }}"
               class="caja-card group relative flex flex-col w-full rounded-3xl shadow-sm hover:shadow-lg cursor-pointer transition-all duration-300 hover:-translate-y-1 overflow-hidden p-5 sm:p-6">
                <div class="relative z-10 flex-1 flex flex-col w-full">
                    <div class="flex justify-between items-start mb-5 sm:mb-6 w-full">
                        <div class="w-full">
                            <h3 class="caja-main-title text-xl sm:text-2xl font-black tracking-tight group-hover:{{ $theme['text'] }} transition-colors">Mesa {{ $mesa->numero }}</h3>
                            <p class="caja-text-muted text-xs font-semibold">Capacidad {{ $mesa->capacidad }} p.</p>
                        </div>
                    </div>
                    <div class="mt-auto w-full">
                        @if($cuenta)
                            <div class="caja-inner-card rounded-2xl p-4 flex justify-between items-center w-full">
                                <p class="caja-main-title text-xl font-black">${{ number_format($cuenta->total ?? 0, 2) }}</p>
                            </div>
                        @else
                            <div class="caja-btn w-full py-3.5 flex items-center justify-center rounded-2xl font-bold">Mesa Libre</div>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <p class="text-center w-full py-10 caja-text-muted">No hay mesas configuradas</p>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ... (Tu script existente se mantiene igual)
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
                modalAbrirMesa.classList.remove('hidden');
            });
        });

        if (btnConfirmarAbrir) {
            btnConfirmarAbrir.addEventListener('click', async () => {
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

    });

    function mostrarToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast-panel ${type} show`;
        toast.innerHTML = `<div><strong>${type === 'success' ? 'Éxito' : 'Error'}</strong><span>${message}</span></div>`;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }
</script>
@endpush

@push('styles')
<style>
    :root {
        --caja-bg-card: #ffffff;
        --caja-border-card: #e5e7eb;
        --caja-text-main: #020617;
        --caja-text-muted: #64748b;
        --caja-bg-inner: #f1f5f9;
        --caja-btn-bg: #f8fafc;
        --caja-badge-bg: #eff6ff;
        --caja-badge-border: #dbeafe;
        --caja-badge-text: #1d4ed8;
    }
    .dark {
        --caja-bg-card: #1e2026;
        --caja-border-card: #334155;
        --caja-text-main: #f8fafc;
        --caja-text-muted: #94a3b8;
        --caja-bg-inner: #15171c;
        --caja-btn-bg: #15171c;
        --caja-badge-bg: rgba(30, 58, 138, 0.3);
        --caja-badge-border: #1e3a8a;
        --caja-badge-text: #60a5fa;
    }

    /* Ajustes generales */
    .caja-card { 
        background-color: var(--caja-bg-card) !important; 
        border: 1px solid var(--caja-border-card) !important; 
        transition: all 0.3s ease !important;
    }
    .caja-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px -10px rgba(0,0,0,0.15) !important; }

    .caja-inner-card { 
        background-color: var(--caja-bg-inner) !important; 
        border: 1px solid var(--caja-border-card) !important;
        border-radius: 12px !important;
    }

    .caja-btn { 
        background-color: var(--caja-btn-bg) !important; 
        color: var(--caja-text-muted) !important; 
        border: 1px solid var(--caja-border-card) !important;
        cursor: pointer;
    }
    .caja-btn:hover { background-color: #059669 !important; color: #ffffff !important; border-color: #059669 !important; }

    /* Toast Mejorado */
    .toast-wrapper { position: fixed; bottom: 2rem; right: 2rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.75rem; }
    .toast-panel { 
        min-width: 300px; padding: 1rem 1.5rem; border-radius: 16px; 
        background: var(--caja-bg-card); border: 1px solid var(--caja-border-card);
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        display: flex; align-items: center; gap: 10px;
        opacity: 0; transform: translateX(20px); transition: 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .toast-panel.show { opacity: 1; transform: translateX(0); }
    .toast-panel.error { border-left: 4px solid #ef4444; }
    .toast-panel.success { border-left: 4px solid #10b981; }
</style>
@endpush