{{-- Modal de Método de Pago --}}
<div id="modal-metodo" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/80 backdrop-blur-sm px-4 transition-all duration-300">
    <div class="relative !bg-white dark:!bg-[#1c1c1e] border !border-gray-200 dark:!border-white/5 rounded-[2rem] p-8 max-w-md w-full shadow-2xl animate-in fade-in zoom-in-95 duration-200 overflow-hidden">
        
        {{-- Resplandor decorativo --}}
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/5 dark:bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <h2 id="modal-metodo-titulo" class="text-2xl font-black !text-gray-900 dark:!text-white mb-6 text-center tracking-tight relative z-10">Método de Pago</h2>
        
        <div id="seccion-metodos-lista" class="space-y-3 mb-6 relative z-10">
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 !border-gray-200 dark:!border-white/5 !bg-gray-50 dark:!bg-black/20 hover:!border-emerald-500/50 hover:!bg-emerald-50 dark:hover:!bg-emerald-500/10 transition-all font-bold !text-gray-900 dark:!text-white group outline-none" data-metodo="Efectivo">
                <i class="fas fa-money-bill-wave !text-emerald-500 dark:!text-emerald-400 mr-3 group-hover:scale-110 transition-transform"></i> Efectivo
            </button>
            
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 !border-gray-200 dark:!border-white/5 !bg-gray-50 dark:!bg-black/20 hover:!border-sky-500/50 hover:!bg-sky-50 dark:hover:!bg-sky-500/10 transition-all font-bold !text-gray-900 dark:!text-white group outline-none" data-metodo="Transferencia">
                <i class="fas fa-bank !text-sky-500 dark:!text-sky-400 mr-3 group-hover:scale-110 transition-transform"></i> Transferencia
            </button>
            
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 !border-gray-200 dark:!border-white/5 !bg-gray-50 dark:!bg-black/20 hover:!border-violet-500/50 hover:!bg-violet-50 dark:hover:!bg-violet-500/10 transition-all font-bold !text-gray-900 dark:!text-white group outline-none" data-metodo="Tarjeta">
                <i class="fas fa-credit-card !text-violet-500 dark:!text-violet-400 mr-3 group-hover:scale-110 transition-transform"></i> Tarjeta Bancaria
            </button>

            <div class="relative py-2">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gray-200 dark:border-white/10"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-white dark:bg-[#1c1c1e] px-3 text-gray-500 dark:text-white/40 font-black tracking-widest">Opciones Mixtas</span>
                </div>
            </div>

            <button type="button" id="btn-activar-combinado" class="w-full text-left p-4 rounded-2xl border-2 border-dashed !border-gray-300 dark:!border-white/10 !bg-transparent hover:!border-amber-500/50 hover:!bg-amber-50 dark:hover:!bg-amber-500/10 transition-all font-black !text-amber-600 dark:!text-amber-400 group outline-none">
                <i class="fas fa-layer-group mr-3 group-hover:scale-110 transition-transform"></i> Combinar Pagos / Mixto
            </button>
        </div>

        <div id="seccion-metodos-combinado" class="hidden space-y-4 mb-6 relative z-10">
            <div class="grid grid-cols-2 gap-4 !bg-gray-50 dark:!bg-black/40 p-4 rounded-2xl border !border-gray-200 dark:!border-white/5">
                <div>
                    <p class="text-[10px] text-zinc-400 uppercase font-black tracking-wider">Total Orden</p>
                    <p id="comb-total-requerido" class="text-xl font-black !text-gray-900 dark:!text-white">$0.00</p>
                </div>
                <div>
                    <p id="comb-label-status" class="text-[10px] text-zinc-400 uppercase font-black tracking-wider">Restante</p>
                    <p id="comb-monto-status" class="text-xl font-black text-red-500">$0.00</p>
                </div>
            </div>

            {{-- EFECTIVO --}}
            <div class="!bg-gray-50 dark:!bg-black/20 p-3.5 rounded-2xl border !border-gray-200 dark:!border-white/5">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-[100px]">
                        <label class="text-xs font-black uppercase text-emerald-500 select-none">💵 Efectivo</label>
                    </div>
                    <div class="flex-1">
                        <input type="number" id="comb-input-efectivo" step="0.01" min="0" placeholder="0.00" class="w-full text-right bg-white dark:bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl px-3 py-2 !text-gray-900 dark:!text-white font-mono font-bold focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
            </div>

            {{-- TARJETA --}}
            <div class="!bg-gray-50 dark:!bg-black/20 p-3.5 rounded-2xl border !border-gray-200 dark:!border-white/5 space-y-3">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-[100px]">
                        <label class="text-xs font-black uppercase text-violet-500 select-none">💳 Tarjeta</label>
                    </div>
                    <div class="flex-1">
                        <input type="number" id="comb-input-tarjeta" step="0.01" min="0" placeholder="0.00" class="w-full text-right bg-white dark:bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl px-3 py-2 !text-gray-900 dark:!text-white font-mono font-bold focus:outline-none focus:border-violet-500">
                    </div>
                </div>
                {{-- Referencia Tarjeta justo abajo del monto --}}
                <div class="pl-2">
                    <input type="text" id="comb-ref-tarjeta" placeholder="N° Referencia / Voucher" class="w-full bg-white dark:bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl px-3 py-2 text-sm !text-gray-900 dark:!text-white font-mono focus:outline-none focus:border-violet-500">
                </div>
            </div>

            {{-- TRANSFERENCIA --}}
            <div class="!bg-gray-50 dark:!bg-black/20 p-3.5 rounded-2xl border !border-gray-200 dark:!border-white/5 space-y-3">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-[100px]">
                        <label class="text-xs font-black uppercase text-sky-500 select-none">🏦 Transf.</label>
                    </div>
                    <div class="flex-1">
                        <input type="number" id="comb-input-transferencia" step="0.01" min="0" placeholder="0.00" class="w-full text-right bg-white dark:bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl px-3 py-2 !text-gray-900 dark:!text-white font-mono font-bold focus:outline-none focus:border-sky-500">
                    </div>
                </div>
                {{-- Referencia Transferencia justo abajo del monto --}}
                <div class="pl-2">
                    <input type="text" id="comb-ref-transferencia" placeholder="Código de Autorización / Clave" class="w-full bg-white dark:bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl px-3 py-2 text-sm !text-gray-900 dark:!text-white font-mono focus:outline-none focus:border-sky-500">
                </div>
            </div>

            <button type="button" id="btn-confirmar-combinado" disabled class="w-full py-4 px-4 !bg-gray-100 dark:!bg-white/5 !text-gray-400 dark:!text-white/30 font-black text-sm uppercase tracking-wider rounded-2xl border !border-gray-200 dark:!border-white/5 cursor-not-allowed transition-all outline-none">
                Confirmar Pago Combinado
            </button>
        </div>
        
        <button type="button" id="btn-cerrar-modal-metodo" class="w-full py-4 px-4 !bg-gray-100 dark:!bg-white/5 hover:!bg-gray-200 dark:hover:!bg-white/10 !text-gray-500 dark:!text-white/60 hover:!text-gray-900 dark:hover:!text-white font-black text-xs uppercase tracking-widest rounded-2xl border !border-gray-200 dark:!border-white/5 transition-all outline-none relative z-10">
            Cancelar
        </button>
    </div>
</div>