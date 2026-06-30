@extends('layouts.admin')

@section('title', 'Caja | Ollintem Pro')

@section('content')
@php
    $mesasLibres = $mesas->where('estado', 'disponible')->count();
@endphp

<div id="toastContainer" class="toast-wrapper" aria-live="polite" aria-atomic="true"></div>

<div class="p-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-8 relative z-10">
    
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">
        <div class="space-y-4">
            <div class="inline-flex items-center gap-2 rounded-full bg-blue-500/10 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.35em] text-blue-500">
                <span class="h-2.5 w-2.5 rounded-full bg-blue-500 animate-pulse"></span>
                Panel Financiero
            </div>
            <h1 class="text-3xl font-black text-white">Panel de Caja</h1>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full xl:w-auto">
            <div class="glass-card p-5 rounded-3xl relative overflow-hidden">
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Total abierto</p>
                <p class="mt-2 text-2xl font-black text-emerald-400" id="total-abierto-display">${{ number_format($totalAbierto ?? 0, 2) }}</p>
            </div>
            <div class="glass-card p-5 rounded-3xl relative overflow-hidden">
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Mesas activas</p>
                <p class="mt-2 text-2xl font-black text-white">{{ $mesasActivas ?? 0 }}</p>
            </div>
            <div class="glass-card p-5 rounded-3xl relative overflow-hidden">
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Mesas libres</p>
                <p class="mt-2 text-2xl font-black text-gray-500">{{ $mesasLibres }}</p>
            </div>
        </div>
    </div>

    {{-- Grid de Mesas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5" id="mesas-container">
        @forelse ($mesas as $mesa)
            @include('admin.caja.partials.mesa-card', ['mesa' => $mesa])
        @empty
            <div class="col-span-full py-16 text-center text-gray-500">No hay mesas configuradas.</div>
        @endforelse
    </div>

   
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Lógica de Filtros
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

        // Gestión de Apertura de Mesa
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

    // Funciones de Toast Globales
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
    .toast-wrapper { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.75rem; }
    .toast-panel { min-width: 18rem; background: #0b0f19; color: white; border: 1px solid rgba(255,255,255,0.1); padding: 1rem; border-radius: 1rem; opacity: 0; transition: 0.3s; }
    .toast-panel.show { opacity: 1; }
    .filter-button--active { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
</style>
@endpush