@extends('layouts.admin')

@section('title', 'Historial de Comandas | ' . $area)

@section('content')
<div class="px-3 sm:px-6 lg:px-8 py-5 sm:py-8 w-full max-w-5xl mx-auto space-y-5">

    {{-- CABECERA --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('admin.cocina.index') }}"
               class="text-xs font-bold text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors">
                &larr; Volver a {{ $area }}
            </a>
            <h1 class="text-xl sm:text-3xl font-black text-zinc-900 dark:text-white tracking-tight mt-1">
                Historial de comandas
            </h1>
            <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                Lo que llegó a <span class="font-bold text-zinc-700 dark:text-zinc-300">{{ $area }}</span>
                el {{ $fecha }}.
                Este registro es inmutable: muestra el pedido tal como llegó al momento del envío.
            </p>
        </div>

        {{-- Selector de area --}}
        <div class="flex gap-2">
            <a href="{{ route('admin.cocina.historial', ['area' => 'Cocina']) }}"
               class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-colors
                      {{ $areaSeleccionada !== 'Barra' ? 'bg-zinc-900 dark:bg-white text-white dark:text-zinc-900' : 'border border-zinc-300 dark:border-white/10 text-zinc-700 dark:text-zinc-300 hover:border-zinc-500' }}">
                Cocina
            </a>
            <a href="{{ route('admin.cocina.historial', ['area' => 'Barra']) }}"
               class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-colors
                      {{ $areaSeleccionada === 'Barra' ? 'bg-zinc-900 dark:bg-white text-white dark:text-zinc-900' : 'border border-zinc-300 dark:border-white/10 text-zinc-700 dark:text-zinc-300 hover:border-zinc-500' }}">
                Barra
            </a>
        </div>
    </div>

    {{-- AVISO cuando no hay nada --}}
    @if($jobs->isEmpty())
        <div class="text-center py-16">
            <i class="fas fa-clipboard-list text-4xl text-zinc-300 dark:text-zinc-700 mb-3"></i>
            <p class="font-bold text-zinc-500">Sin comandas registradas hoy en {{ $area }}</p>
            <p class="text-xs text-zinc-400 mt-1">
                Aquí aparecerá cada envío a cocina con el detalle exacto de lo que se pidió.
            </p>
        </div>

    @else
        {{-- Una tarjeta por LOTE DE ENVIO (cada vez que el mesero presionó "Enviar") --}}
        <div class="space-y-3">
            @foreach($jobs as $lote => $lotejobs)
                @php
                    $primerJob = $lotejobs->first();
                    $orden = $primerJob?->orden;
                    $mesa = optional($orden?->mesa)->numero ?? '—';
                    $mesero = optional($orden?->mesero)->nombre ?? optional($orden?->mesero)->name ?? 'Sin asignar';
                    $hora = $primerJob?->created_at?->format('H:i');
                @endphp

                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 rounded-2xl overflow-hidden">

                    {{-- Encabezado del lote --}}
                    <button type="button"
                        class="btn-lote w-full px-4 py-3 flex items-center justify-between gap-3 text-left hover:bg-zinc-50 dark:hover:bg-white/[0.02] transition-colors">

                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-white/5 flex items-center justify-center shrink-0">
                                <i class="fas fa-receipt text-zinc-500 text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-black text-zinc-900 dark:text-white text-sm">
                                    Mesa {{ $mesa }}
                                    <span class="font-normal text-zinc-500 text-xs ml-1">· {{ $mesero }}</span>
                                </p>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                    {{ $hora }} hrs
                                    · lote <span class="font-mono">{{ substr($lote, 0, 12) }}</span>
                                </p>
                            </div>
                        </div>

                        <i class="fas fa-chevron-down text-zinc-400 text-xs shrink-0 transition-transform icono-lote"></i>
                    </button>

                    {{-- Contenido del ticket (el texto exacto que llegó a cocina) --}}
                    <div class="contenido-lote border-t border-zinc-200 dark:border-white/10">
                        @foreach($lotejobs as $job)
                            <div class="px-4 py-3 {{ !$loop->first ? 'border-t border-zinc-100 dark:border-white/5' : '' }}">

                                {{-- Estado del job --}}
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-zinc-500">
                                        {{ $job->area ?? $area }}
                                    </span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                                        {{ $job->estado === 'impreso' ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/15 text-amber-600 dark:text-amber-400' }}">
                                        {{ $job->estado === 'impreso' ? 'Impreso' : 'Pendiente' }}
                                    </span>
                                </div>

                                {{-- El texto del ticket TAL CUAL llegó: fuente monoespaciada para
                                     respetar el formato de la impresora térmica. Esto es el
                                     respaldo oficial — no se puede editar ni reinterpretar. --}}
                                <pre class="text-xs font-mono bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-white/10 rounded-xl px-4 py-3 text-zinc-800 dark:text-zinc-200 whitespace-pre-wrap overflow-x-auto leading-relaxed">{{ $job->contenido }}</pre>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 leading-snug text-center pb-4">
            El contenido de cada comanda es exactamente lo que llegó al momento del envío.
            Si existe discrepancia entre lo que el cliente dice haber pedido y lo que aparece aquí,
            este registro es el referente oficial.
        </p>
    @endif
</div>

{{-- Botón flotante para volver a cocina --}}
<a href="{{ route('admin.cocina.index') }}"
   class="fixed bottom-5 right-5 w-12 h-12 rounded-full bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 flex items-center justify-center shadow-xl hover:scale-105 transition-transform z-50"
   title="Volver a {{ $area }}">
    <i class="fas fa-arrow-left text-sm"></i>
</a>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Plegar/desplegar cada lote. El primero arranca abierto.
    document.querySelectorAll('.btn-lote').forEach((btn, i) => {
        const contenido = btn.nextElementSibling;
        const icono = btn.querySelector('.icono-lote');

        // El mas reciente (primero en el DOM) viene abierto.
        if (i !== 0) {
            contenido.classList.add('hidden');
            icono.style.transform = 'rotate(-90deg)';
        }

        btn.addEventListener('click', () => {
            const oculto = contenido.classList.toggle('hidden');
            icono.style.transform = oculto ? 'rotate(-90deg)' : 'rotate(0deg)';
        });
    });
});
</script>
@endsection