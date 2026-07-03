{{-- detalle-cuenta.blade.php --}}
@php
    $esDividida = ($cuentasDivididas ?? false);
    $totalPartes = $totalCuentasDivision ?? ($orden->numero_cuenta_division ?? 1);
@endphp

{{-- 1. Cabecera y Lógica de Cuenta --}}
<div class="p-8">
    @if($esDividida)
        <div class="mt-4 p-4 bg-blue-500/10 border border-blue-500/20 rounded-2xl">
            <p class="text-blue-600 dark:text-blue-400 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 mb-1">
                <i class="fas fa-users"></i> Cuenta Dividida
            </p>
            <p class="text-zinc-900 dark:text-white text-sm font-bold">Dividida entre {{ $totalPartes }} personas</p>
            <p class="text-zinc-500 dark:text-zinc-400 text-[10px] uppercase mt-1 font-bold">
                Pago por persona: <span class="text-green-600 dark:text-green-400">${{ number_format(($totalPagar ?? 0) / ($totalPartes > 0 ? $totalPartes : 1), 2) }}</span>
            </p>
        </div>
    @else
        <p class="text-zinc-500 dark:text-zinc-400 text-xs font-bold uppercase tracking-widest mt-2">
            Personas: {{ $mesa->capacidad ?? 'N/A' }}
        </p>
    @endif
</div>

{{-- 2. Selector de Personas (Solo si es dividida) --}}
@if($esDividida && !empty($cuentasDividadasInfo))
    <div class="px-8 pb-4">
        <div class="flex gap-2 overflow-x-auto pb-2 -mx-2 px-2">
            @foreach($cuentasDividadasInfo as $index => $cuenta)
                @php 
                    $esPagada = ($cuenta['estado_orden'] ?? '') === 'pagada';
                @endphp
                <button 
                    type="button" 
                    class="btn-cuenta px-4 py-2 rounded-xl font-bold text-xs whitespace-nowrap transition-all flex items-center gap-2 border-2 {{ $esPagada ? 'bg-emerald-50 dark:bg-emerald-900/50 border-emerald-500 text-emerald-700 dark:text-emerald-200 opacity-70 cursor-not-allowed' : 'bg-white dark:bg-zinc-900 border-blue-500 text-zinc-900 dark:text-white hover:bg-blue-500/10' }}"
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

{{-- 3. Lista de Productos --}}
<div class="px-8 pb-8 space-y-4 flex-1 overflow-y-auto" id="productos-container">
    @if($esDividida && !empty($cuentasDividadasInfo))
        <div id="cuenta-activa">
            @forelse(($cuentasDividadasInfo[0]['productos'] ?? []) as $producto)
                <div class="flex items-center justify-between group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-zinc-100 dark:bg-white/5 rounded-xl flex items-center justify-center text-blue-500 font-black text-xs border border-zinc-200 dark:border-white/5 group-hover:border-blue-500/30 transition-colors">
                            {{ $producto->cantidad }}x
                        </div>
                        <div>
                            <p class="text-zinc-900 dark:text-white font-bold text-sm">{{ $producto->nombre }}</p>
                            <p class="text-[10px] text-zinc-500 dark:text-zinc-400">Unit: ${{ number_format($producto->precio_unitario, 2) }}</p>
                            @if($producto->notas)
                                <p class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold uppercase italic">{{ $producto->notas }}</p>
                            @endif
                        </div>
                    </div>
                    <span class="text-zinc-900 dark:text-white font-black text-sm">${{ number_format($producto->precio_unitario * $producto->cantidad, 2) }}</span>
                </div>
            @empty
                <div class="p-8 rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 text-center">
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm">No hay productos en esta cuenta.</p>
                </div>
            @endforelse
        </div>
    @endif
</div>
