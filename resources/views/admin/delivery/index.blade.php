@extends('layouts.admin')

@section('title', 'Delivery | Ollintem Pro')
@section('header-title', 'Configuración de Delivery')
@section('header-subtitle', 'Comisión + IVA que cobra cada plataforma')

@section('content')
<div class="px-3 sm:px-6 lg:px-8 py-5 sm:py-8 w-full max-w-4xl mx-auto space-y-5 sm:space-y-8 relative z-10">

    <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-red-600 shadow-[0_4px_14px_rgba(234,88,12,0.35)] shrink-0">
            <i class="fas fa-motorcycle text-white text-sm"></i>
        </div>
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Plataformas de Delivery</h1>
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                Estos porcentajes se negocian directamente con cada plataforma. Ajústalos aquí cuando cambie tu contrato.
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#15151a] border border-slate-100 dark:border-slate-800/60 rounded-2xl sm:rounded-[2rem] p-3.5 sm:p-6 shadow-xl shadow-slate-200/50 dark:shadow-none space-y-3">
        @foreach($plataformas as $plataforma)
            <div class="plataforma-card p-3.5 sm:p-4 rounded-xl border border-slate-100 dark:border-slate-800/60 bg-slate-50/60 dark:bg-white/[0.02]"
                 data-id="{{ $plataforma->id }}">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex items-center gap-2.5 sm:w-40 shrink-0">
                        <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $plataforma->color }}"></span>
                        <span class="font-black text-sm text-slate-900 dark:text-white">{{ $plataforma->nombre }}</span>
                    </div>

                    <div class="flex-1 grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">% Comisión</span>
                            <input type="number" step="0.01" min="0" max="100"
                                   class="input-comision mt-0.5 w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0f0f13] text-sm font-bold text-slate-900 dark:text-white"
                                   value="{{ number_format($plataforma->comision_porcentaje, 2, '.', '') }}">
                        </label>
                        <label class="block">
                            <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">% IVA sobre comisión</span>
                            <input type="number" step="0.01" min="0" max="100"
                                   class="input-iva mt-0.5 w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0f0f13] text-sm font-bold text-slate-900 dark:text-white"
                                   value="{{ number_format($plataforma->iva_comision_porcentaje, 2, '.', '') }}">
                        </label>
                    </div>

                    <div class="flex items-center gap-2 sm:w-auto shrink-0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="input-activo sr-only peer" {{ $plataforma->activo ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-slate-300 dark:bg-slate-700 rounded-full peer peer-checked:bg-emerald-600 transition-colors"></div>
                            <div class="absolute left-0.5 top-0.5 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-4"></div>
                        </label>
                        <button type="button" class="btn-guardar px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-black uppercase tracking-wide transition-colors">
                            Guardar
                        </button>
                    </div>
                </div>
                <p class="mensaje-guardado hidden text-[11px] font-bold text-emerald-500 mt-2"></p>
            </div>
        @endforeach

        @if($plataformas->isEmpty())
            <p class="text-center text-sm text-slate-400 py-8">No hay plataformas configuradas todavía.</p>
        @endif
    </div>

    <p class="text-[11px] text-slate-400 dark:text-slate-500 text-center">
        La comisión se calcula sobre el precio de venta (subtotal + IVA del producto) y se suma al total que paga el cliente en el pedido de delivery.
    </p>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    document.querySelectorAll('.plataforma-card').forEach(card => {
        card.querySelector('.btn-guardar').addEventListener('click', async () => {
            const id = card.dataset.id;
            const comision = parseFloat(card.querySelector('.input-comision').value) || 0;
            const iva = parseFloat(card.querySelector('.input-iva').value) || 0;
            const activo = card.querySelector('.input-activo').checked;
            const mensaje = card.querySelector('.mensaje-guardado');

            try {
                const res = await fetch(`/delivery/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        comision_porcentaje: comision,
                        iva_comision_porcentaje: iva,
                        activo: activo,
                    }),
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    mensaje.textContent = 'Guardado correctamente';
                    mensaje.classList.remove('hidden', 'text-red-500');
                    mensaje.classList.add('text-emerald-500');
                } else {
                    mensaje.textContent = data.message || 'Error al guardar';
                    mensaje.classList.remove('hidden', 'text-emerald-500');
                    mensaje.classList.add('text-red-500');
                }
            } catch (e) {
                mensaje.textContent = 'Error de conexión al guardar';
                mensaje.classList.remove('hidden', 'text-emerald-500');
                mensaje.classList.add('text-red-500');
            }

            setTimeout(() => mensaje.classList.add('hidden'), 2500);
        });
    });
});
</script>
@endsection