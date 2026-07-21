{{-- detalle-cuenta.blade.php --}}
@php
    $esDividida = ($cuentasDivididas ?? false);
    $totalPartes = $totalCuentasDivision ?? ($ordenes->first()->numero_cuenta_division ?? 1);
@endphp
<div class="flex flex-col h-full bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100">

    <div class="p-6">
        @if($esDividida)
            <div class="mt-2 p-4 bg-blue-500/10 border border-blue-500/20 rounded-2xl">
                <p class="text-blue-600 dark:text-blue-400 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 mb-1">
                    <i class="fas fa-users"></i> Cuenta Dividida
                </p>
                <p class="text-zinc-900 dark:text-white text-sm font-bold">Dividida entre {{ $totalPartes }} personas</p>
                <p class="text-zinc-500 dark:text-zinc-400 text-[10px] uppercase mt-1 font-bold">
                    Pago por persona: <span class="text-green-600 dark:text-green-400">${{ number_format(($totalPagar ?? 0) / ($totalPartes > 0 ? $totalPartes : 1), 2) }}</span>
                </p>
            </div>
        @else
            <p class="text-zinc-500 dark:text-zinc-400 text-xs font-bold uppercase tracking-widest">
                Personas: {{ $mesa->capacidad ?? 'N/A' }}
            </p>
        @endif
    </div>

    @if($esDividida && !empty($cuentasDivididas))
        <div class="px-6 pb-4">
            <div class="flex gap-2 overflow-x-auto pb-2 -mx-2 px-2">
                @foreach($cuentasDivididas as $index => $cuenta)
                    @php
                        $esPagada = ($cuenta['estado_orden'] ?? '') === 'pagada';
                    @endphp
                    <button
                        type="button"
                        class="btn-cuenta px-4 py-2 rounded-xl font-bold text-xs whitespace-nowrap transition-all flex items-center gap-2 border-2 {{ $esPagada ? 'bg-emerald-50 dark:bg-emerald-900/50 border-emerald-500 text-emerald-700 dark:text-emerald-200 opacity-70 cursor-not-allowed' : 'bg-zinc-50 dark:bg-zinc-900 border-blue-500 text-zinc-900 dark:text-white hover:bg-blue-500/10' }}"
                        data-cuenta="{{ $cuenta['numero_cuenta'] ?? ($index + 1) }}"
                        data-orden="{{ $cuenta['orden_id'] ?? '' }}"
                        data-total="{{ number_format($cuenta['total'] ?? 0, 2, '.', '') }}"
                        {{ $esPagada ? 'disabled' : '' }}>
                        <span class="texto-cuenta">Persona {{ $cuenta['numero_cuenta'] ?? ($index + 1) }} - ${{ number_format($cuenta['total'] ?? 0, 2) }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <div class="px-6 pb-6 space-y-4 flex-1 overflow-y-auto" id="productos-container">

        @if($esDividida && !empty($cuentasDivididas))
            <div id="cuenta-activa">
                @forelse(($cuentasDivididas[0]['productos'] ?? []) as $producto)
                    <div class="flex items-center justify-between group p-3 rounded-2xl hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-blue-500/10 text-blue-600 dark:text-blue-400 font-black text-xs rounded-xl flex items-center justify-center border border-blue-500/20">
                                {{ $producto->cantidad }}x
                            </div>
                            <div>
                                <p class="text-zinc-900 dark:text-white font-bold text-sm">{{ $producto->nombre }}</p>
                                <p class="text-[10px] text-zinc-500 dark:text-zinc-400 font-semibold">Unit: ${{ number_format($producto->precio_unitario, 2) }}</p>
                            </div>
                        </div>
                        <span class="text-zinc-900 dark:text-white font-black text-sm">${{ number_format($producto->precio_unitario * $producto->cantidad, 2) }}</span>
                    </div>
                @empty
                    <div class="p-8 rounded-3xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 text-center">
                        <p class="text-zinc-500 dark:text-zinc-400 text-sm">No hay productos en esta cuenta.</p>
                    </div>
                @endforelse
            </div>

        @else
            @foreach($ordenes as $ordenActual)
                @foreach($ordenActual->detalles as $detalle)
                    <div class="flex items-center justify-between group p-3 rounded-2xl hover:bg-zinc-100 dark:hover:bg-white/5 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-blue-500/10 text-blue-600 dark:text-blue-400 font-black text-xs rounded-xl flex items-center justify-center border border-blue-500/20">
                                {{ $detalle->cantidad }}x
                            </div>
                            <div>
                                <p class="text-zinc-900 dark:text-white font-bold text-sm">{{ $detalle->producto->nombre ?? 'Producto sin nombre' }}</p>
                                <p class="text-[10px] text-zinc-500 dark:text-zinc-400 font-semibold">Unit: ${{ number_format($detalle->precio_unitario, 2) }}</p>

                                @if($detalle->notas)
                                    <p class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold uppercase italic mt-0.5">{{ $detalle->notas }}</p>
                                @endif

                                @if($detalle->promocionAplicada)
                                    <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-black uppercase mt-1 flex items-center gap-1">
                                        <i class="fas fa-tag"></i> {{ $detalle->promocionAplicada->promocion->nombre ?? 'Promo' }}
                                        (-${{ number_format($detalle->promocionAplicada->monto_descuento, 2) }})
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            @if($detalle->promocionAplicada)
                                <span class="text-zinc-400 dark:text-zinc-500 text-[10px] line-through block">
                                    ${{ number_format($detalle->precio_unitario * $detalle->cantidad, 2) }}
                                </span>
                            @endif
                            <span class="text-zinc-900 dark:text-white font-black text-sm">
                                ${{ number_format(($detalle->precio_unitario * $detalle->cantidad) - ($detalle->promocionAplicada->monto_descuento ?? 0), 2) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            @endforeach
        @endif

    </div>

    <div class="mt-auto px-6 py-6 bg-zinc-50 dark:bg-zinc-900 border-t border-zinc-200 dark:border-white/10 rounded-t-3xl shadow-sm">
        <div class="space-y-3">
            <div class="space-y-2">
                <div class="flex justify-between text-zinc-600 dark:text-zinc-400 text-xs sm:text-sm font-semibold">
                    <span>Subtotal</span>
                    <span class="font-bold text-zinc-900 dark:text-white">${{ number_format($subtotalBruto ?? 0, 2) }}</span>
                </div>

                @if(($descuentoPromociones ?? 0) > 0)
                    <div class="flex justify-between text-emerald-600 dark:text-emerald-400 text-xs sm:text-sm font-semibold">
                        <span>Descuento (promociones)</span>
                        <span class="font-bold">-${{ number_format($descuentoPromociones, 2) }}</span>
                    </div>
                @endif

                {{-- --- NUEVO: Switch de IVA --- --}}
                <div class="flex justify-between items-center text-zinc-600 dark:text-zinc-400 text-xs sm:text-sm font-semibold py-1">
                    <span class="flex items-center gap-2">
                        IVA ({{ number_format($ivaPorcentaje ?? 16, 0) }}%)
                        <label class="relative inline-flex items-center cursor-pointer align-middle">
                            <input
                                type="checkbox"
                                id="ivaSwitch"
                                class="sr-only peer"
                                {{ ($ivaHabilitado ?? true) ? 'checked' : '' }}
                                data-toggle-url="{{ route('admin.caja.toggle-iva') }}"
                                data-csrf="{{ csrf_token() }}"
                            >
                            <div class="w-9 h-5 bg-zinc-300 dark:bg-zinc-700 rounded-full peer
                                        peer-checked:bg-blue-600 transition-colors"></div>
                            <div class="absolute left-0.5 top-0.5 bg-white w-4 h-4 rounded-full
                                        transition-transform peer-checked:translate-x-4"></div>
                        </label>
                    </span>
                    <span class="font-bold text-zinc-900 dark:text-white">${{ number_format($iva ?? 0, 2) }}</span>
                </div>

                @if(($propina ?? 0) > 0)
                    <div class="flex justify-between text-amber-600 dark:text-amber-400 text-xs sm:text-sm font-semibold">
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-hand-holding-dollar text-[11px]"></i> Propina
                        </span>
                        <span class="font-bold">${{ number_format($propina, 2) }}</span>
                    </div>
                @endif
            </div>

            <div class="border-t border-zinc-200 dark:border-white/10 pt-3 flex justify-between items-center">
                <span class="text-zinc-500 dark:text-zinc-400 font-black uppercase tracking-[0.2em] text-xs">Total</span>
                <span class="text-3xl sm:text-4xl font-black text-zinc-900 dark:text-white tracking-tighter italic">
                    ${{ number_format($totalPagar ?? 0, 2) }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- --- NUEVO: Script del switch de IVA --- --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ivaSwitch = document.getElementById('ivaSwitch');
    if (!ivaSwitch) return;

    ivaSwitch.addEventListener('change', async function (e) {
        const habilitado = e.target.checked;
        const url = e.target.dataset.toggleUrl;
        const csrf = e.target.dataset.csrf;

        ivaSwitch.disabled = true;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ habilitado }),
            });

            if (!response.ok) {
                throw new Error('Respuesta no exitosa del servidor');
            }

            window.location.reload();

        } catch (error) {
            console.error('Error al cambiar el estado del IVA:', error);
            e.target.checked = !habilitado;
            ivaSwitch.disabled = false;
            alert('No se pudo actualizar el IVA. Intenta de nuevo.');
        }
    });
});
</script>