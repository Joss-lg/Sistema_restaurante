{{-- resources/views/admin/cocina/partials/comandas.blade.php --}}
@if($comandas->isEmpty())
    <div class="glass-card rounded-[24px] px-6 py-16 sm:py-24 text-center border border-[var(--border-color)] shadow-xl mt-6 sm:mt-8 bg-[var(--card-color)]">
        <i class="fas fa-check-double text-5xl text-emerald-500 mb-4"></i>
        <h2 class="text-xl sm:text-2xl font-black text-[var(--text-color)]">¡{{ $areaSeleccionada }} Despejada!</h2>
    </div>
@else
    <div class="grid gap-3 sm:gap-6 grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 mt-4 sm:mt-8 items-start w-full">
        @foreach($comandas as $comanda)
            @php
                $config = [
                    'pendiente' => ['border' => 'border-t-orange-500', 'btn' => 'bg-orange-500 hover:bg-orange-400 text-white', 'textColor' => 'text-orange-500', 'badgeBg' => 'bg-orange-500/10 border-orange-500/30'],
                    'en proceso' => ['border' => 'border-t-blue-500', 'btn' => 'bg-emerald-500 hover:bg-emerald-400 text-white', 'textColor' => 'text-blue-500', 'badgeBg' => 'bg-blue-500/10 border-blue-500/30'],
                    'servida' => ['border' => 'border-t-emerald-500', 'btn' => 'bg-[var(--input-bg)] text-[var(--text-muted)] cursor-not-allowed border border-[var(--border-color)]', 'textColor' => 'text-emerald-500', 'badgeBg' => 'bg-emerald-500/10 border-emerald-500/30']
                ][$comanda->estado] ?? ['border' => 'border-t-gray-500', 'btn' => 'bg-gray-500', 'textColor' => 'text-gray-500', 'badgeBg' => 'bg-gray-500/10'];
            @endphp

            <article class="bg-[var(--card-color)] w-full rounded-[20px] border border-[var(--border-color)] border-t-[6px] {{ $config['border'] }} shadow-lg flex flex-col h-full overflow-hidden relative" data-comanda-id="{{ $comanda->id }}">
                <div class="p-4 border-b border-[var(--border-color)] min-w-0 flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <h3 class="font-black text-lg truncate">Mesa {{ $comanda->mesa->numero }}</h3>
                        <p class="text-xs text-[var(--text-muted)] truncate">Mesero: {{ $comanda->mesero->nombre ?? 'N/A' }}</p>
                    </div>
                    <span
                        class="tiempo-espera shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-lg border text-[10px] font-black uppercase tracking-wide whitespace-nowrap bg-zinc-500/10 border-zinc-500/30 text-zinc-400"
                        data-creado="{{ optional($comanda->creado_en)->toIso8601String() }}"
                    >
                        <i class="fas fa-clock"></i>
                        <span class="tiempo-texto">--</span>
                    </span>
                </div>

                <div class="p-4 flex-1 min-w-0">
                    <ul class="space-y-2">
                        @foreach($comanda->detalles as $detalle)
                            @php
                                $tiempoClases = [
                                    'sin-tiempo'     => ['label' => 'S', 'clase' => 'text-zinc-400 bg-zinc-500/10 border-zinc-500/30'],
                                    'primer-tiempo'  => ['label' => '1', 'clase' => 'text-blue-400 bg-blue-500/10 border-blue-500/30'],
                                    'segundo-tiempo' => ['label' => '2', 'clase' => 'text-purple-400 bg-purple-500/10 border-purple-500/30'],
                                    'tercer-tiempo'  => ['label' => '3', 'clase' => 'text-pink-400 bg-pink-500/10 border-pink-500/30'],
                                ];
                                $tInfo = $tiempoClases[$detalle->tiempo] ?? null;
                            @endphp
                            <li class="flex flex-col sm:flex-row sm:justify-between sm:items-center text-sm gap-0.5">
                                <span class="font-bold text-[var(--text-color)] break-words flex flex-wrap items-center gap-1.5">
                                    {{ $detalle->cantidad }}x {{ $detalle->producto->nombre ?? 'Producto Eliminado' }}
                                    @if($tInfo)
                                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wide px-1.5 py-0.5 rounded-md border {{ $tInfo['clase'] }}">
                                            <i class="fas fa-clock"></i>Tiempo {{ $tInfo['label'] }}
                                        </span>
                                    @endif
                                    @if($detalle->gramaje)
                                        @php
                                            $gramajeLimpio = rtrim(rtrim(number_format((float) $detalle->gramaje, 2, '.', ''), '0'), '.');
                                        @endphp
                                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wide text-orange-400 bg-orange-500/10 border border-orange-500/30 px-1.5 py-0.5 rounded-md">
                                            <i class="fas fa-weight-hanging"></i>{{ $gramajeLimpio }}g
                                        </span>
                                    @endif
                                </span>
                                @if($detalle->notas)
                                    <span class="block text-[10px] text-red-400 italic break-words">{{ $detalle->notas }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="p-3 sm:p-4 bg-[var(--bg-color)] border-t border-[var(--border-color)]">
                    <form action="{{ route('admin.cocina.orden.estado', $comanda->orden_id) }}" method="POST" class="form-avanzar-estado">
                        @csrf @method('PATCH')
                        <input type="hidden" name="estado" value="{{ $comanda->estado === 'pendiente' ? 'en proceso' : 'servida' }}">
                        <input type="hidden" name="lote" value="{{ $comanda->lote }}">
                        <input type="hidden" name="area" value="{{ strtolower($areaSeleccionada) }}">
                        <button type="submit" {{ $comanda->estado === 'servida' ? 'disabled' : '' }}
                            class="w-full py-3.5 rounded-xl font-black uppercase text-[12px] tracking-[0.1em] transition-all active:scale-95 {{ $config['btn'] }}">
                            {{ $comanda->estado === 'pendiente' ? 'Iniciar Preparación' : ($comanda->estado === 'en proceso' ? 'Marcar como Lista' : 'Entregada') }}
                        </button>
                    </form>
                </div>
            </article>
        @endforeach
    </div>
@endif