{{-- panel-pago.blade.php --}}
<div class="w-full lg:w-3/5 p-4 lg:p-8 bg-zinc-50 dark:bg-zinc-950 overflow-hidden h-full">
    <div class="max-w-xl mx-auto h-full flex flex-col">

        {{-- Inputs de Estado Ocultos --}}
        <input id="mesa-id" type="hidden" value="{{ $mesa->id }}">
        <input id="orden-id" type="hidden" value="{{ $ordenes->first()->id ?? '' }}">
        <input id="metodo-pago" type="hidden" value="Efectivo">

        <div class="flex-1 space-y-6 overflow-y-auto pr-2">

            {{-- 1. Selector de Método --}}
            <div class="flex items-center justify-center">
                <div class="text-center">
                    <p class="text-zinc-500 dark:text-zinc-400 uppercase tracking-[0.35em] text-[10px] font-black mb-3">Método de pago</p>
                    <button id="btn-abrir-modal-metodo" type="button" class="inline-flex items-center gap-3 rounded-full bg-gradient-to-r from-blue-500/20 to-cyan-500/20 px-6 py-3 text-blue-700 dark:text-blue-300 font-black uppercase tracking-[0.25em] border border-blue-500/50 hover:from-blue-500/30 hover:to-cyan-500/30 transition-all">
                        <i class="fas fa-money-bill-wave text-lg"></i>
                        <span id="metodo-pago-label">Efectivo</span>
                    </button>
                </div>
            </div>

            {{-- 2. Sección Promociones --}}
            <div class="bg-gradient-to-br from-purple-500/10 to-pink-500/10 border border-purple-500/30 p-6 rounded-[2rem]">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-zinc-500 dark:text-zinc-400 uppercase tracking-[0.35em] text-[10px] font-black mb-2">Promociones</p>
                        <span id="promo-label" class="inline-flex items-center gap-2 rounded-full bg-purple-500/10 px-4 py-2 text-purple-600 dark:text-purple-400 font-black uppercase text-xs">
                            <i class="fas fa-tag"></i> Sin promoción
                        </span>
                    </div>
                    <button id="btn-agregar-promo" type="button" class="bg-purple-500 hover:bg-purple-400 text-white font-black py-3 px-4 rounded-2xl uppercase text-xs transition-all">
                        Aplicar
                    </button>
                </div>
                {{-- Contenedor Promo Aplicada --}}
                <div id="promo-aplicada" class="hidden rounded-2xl bg-purple-500/10 border border-purple-500/30 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-700 dark:text-purple-300 text-sm font-bold" id="promo-nombre"></p>
                            <span id="promo-descuento" class="text-purple-600 dark:text-purple-400 font-black"></span>
                        </div>
                        <button id="btn-limpiar-promo" class="text-red-500 dark:text-red-400 text-xs font-bold">Quitar</button>
                    </div>
                </div>
            </div>

            {{-- 3. Display de Montos --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 p-8 rounded-[2.5rem] text-center shadow-sm dark:shadow-2xl">
                <p class="text-zinc-400 dark:text-zinc-500 text-[10px] font-black uppercase mb-4 italic">Monto a cobrar</p>
                {{-- ID monto-input para JS --}}
                <div class="text-6xl font-black text-zinc-900 dark:text-white italic tracking-tighter" id="monto-input">$0.00</div>
                <div class="mt-4 text-sm text-zinc-500 dark:text-zinc-400 space-y-1">
                    <p>Total: <strong class="text-zinc-900 dark:text-white font-black" id="total-pagar-derecha">${{ number_format($totalPagar ?? 0, 2) }}</strong></p>
                    <p>Cambio: <strong class="text-green-600 dark:text-green-500 font-black" id="display-cambio">$0.00</strong></p>
                </div>
            </div>

            {{-- 4. Teclado Numérico --}}
            <div id="cash-section" class="grid grid-cols-4 gap-3">
                @foreach(['1','2','3','4','5','6','7','8','9','.','0','00'] as $key)
                    <button type="button" class="btn-tecla h-16 bg-white dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-2xl font-black text-zinc-900 dark:text-white transition-all active:bg-blue-600/20" data-value="{{ $key }}">{{ $key }}</button>
                @endforeach
                <button type="button" class="btn-tecla col-span-2 h-16 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 rounded-2xl font-black text-red-500 dark:text-red-400" data-value="DEL">BORRAR</button>
            </div>

            {{-- 5. Secciones de Pago --}}
            <div id="non-cash-section" class="hidden space-y-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-white/10 rounded-[2.5rem] p-6">
                <input id="referencia" type="text" placeholder="Referencia de operación" class="w-full rounded-2xl border border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-zinc-950 p-4 text-zinc-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
            </div>

            {{-- 6. Botones Finales --}}
            <div class="grid grid-cols-2 gap-4 pb-12">
                <button id="btn-ticket" class="bg-zinc-100 dark:bg-white/5 hover:bg-zinc-200 dark:hover:bg-white/10 text-zinc-900 dark:text-white font-black py-5 rounded-2xl border border-zinc-200 dark:border-white/10 transition-all">TICKET</button>
                {{-- ID btn-procesar-pago para JS --}}
                <button id="btn-procesar-pago" class="bg-green-500 hover:bg-green-400 text-black font-black py-5 rounded-2xl transition-all">FINALIZAR</button>
            </div>
        </div>
    </div>
</div>