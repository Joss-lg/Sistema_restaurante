<div id="modalEliminarRol" class="hidden fixed inset-0 bg-gray-900/40 dark:bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity duration-300">
    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-3xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all duration-300 scale-100">
        
        <div class="p-6 sm:p-8 border-b border-rose-100 dark:border-rose-900/20 bg-rose-50/50 dark:bg-rose-500/5">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center flex-shrink-0 text-rose-600 dark:text-rose-400">
                    <i class="fas fa-exclamation-triangle text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">¿Eliminar Puesto?</h2>
                    <p class="text-xs text-rose-600 dark:text-rose-400 mt-1 font-semibold">Esta acción es irreversible</p>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-5">
            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                Estás a punto de eliminar el puesto <span class="font-bold text-gray-900 dark:text-white px-2 py-0.5 rounded bg-gray-100 dark:bg-zinc-800" id="nombreRolEliminar"></span>.
            </p>
            
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200/50 dark:border-amber-500/20">
                <p class="text-xs text-amber-700 dark:text-amber-400 font-semibold flex items-start gap-2 leading-relaxed">
                    <i class="fas fa-info-circle mt-0.5"></i>
                    Asegúrate de que no haya empleados activos asignados a este nivel antes de proceder.
                </p>
            </div>
        </div>

        <div class="p-6 sm:p-8 border-t border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-950 flex justify-end gap-3">
            <button type="button" onclick="cerrarModalEliminar()" 
                    class="px-5 py-2.5 rounded-full text-xs font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-zinc-800 transition-colors outline-none">
                Cancelar
            </button>
            <form id="formEliminarRol" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-6 py-2.5 bg-rose-600 text-white rounded-full text-xs font-bold hover:bg-rose-700 transition-colors shadow-lg shadow-rose-500/20 outline-none flex items-center gap-2">
                    <i class="fas fa-trash-alt"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
</div>