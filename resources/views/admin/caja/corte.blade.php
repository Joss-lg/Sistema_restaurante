{{-- resources/views/admin/caja/corte.blade.php --}}
<div id="modalCierreCaja" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" id="backdropCierreCaja"></div>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="inline-block bg-white dark:bg-[#1e2026] rounded-3xl text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full border border-gray-200 dark:border-slate-700 p-6 z-10">
            <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-slate-700 pb-3">
                <h3 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                    Realizar Corte de Caja
                </h3>
                <button type="button" id="btnCerrarModalX" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('admin.caja.cerrar') }}" method="POST" class="space-y-4">
                @csrf
                <p class="text-xs font-semibold text-gray-500 dark:text-slate-400">
                    Ingresa el monto total en efectivo que tienes físicamente en la caja para realizar la conciliación automática.
                </p>

                <div>
                    <label for="monto_final_real" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-slate-300 mb-1">Efectivo Físico en Caja</label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 dark:text-slate-400 text-sm">$</span>
                        </div>
                        <input type="number" name="monto_final_real" id="monto_final_real" step="0.01" min="0" required
                            class="w-full pl-7 py-2.5 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-[#15171c] text-gray-900 dark:text-white focus:border-rose-500 focus:outline-none transition-colors"
                            placeholder="0.00" onfocus="this.select()">
                    </div>
                </div>

                <div>
                    <label for="comentarios" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-slate-300 mb-1">Notas de Auditoría (Opcional)</label>
                    <textarea name="comentarios" id="comentarios" rows="3" maxlength="500"
                        class="w-full p-3 rounded-xl border border-gray-300 dark:border-slate-600 bg-white dark:bg-[#15171c] text-gray-900 dark:text-white focus:border-rose-500 focus:outline-none transition-colors resize-none"
                        placeholder="Observaciones sobre faltantes, sobrantes o incidentes en el turno..."></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-slate-700">
                    <button type="button" id="btnCancelarModal"
                        class="px-4 py-2 text-sm font-bold text-gray-700 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-xl transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-bold text-white bg-rose-500 hover:bg-rose-600 rounded-xl transition shadow-sm cursor-pointer">
                        Cerrar Turno Actual
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>