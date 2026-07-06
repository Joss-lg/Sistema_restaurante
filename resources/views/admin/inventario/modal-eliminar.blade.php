{{-- modal-eliminar.blade.php --}}
<div id="modalEliminar" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4 transition-all duration-300">
    
    <div class="relative bg-white dark:bg-zinc-950 border border-zinc-200/80 dark:border-zinc-800/80 w-full max-w-sm rounded-[1.5rem] sm:rounded-[2.5rem] shadow-[0_25px_50px_-12px_rgba(244,63,94,0.15)] dark:shadow-[0_0_80px_rgba(244,63,94,0.2)] overflow-hidden transform transition-all duration-500 scale-95 opacity-0 flex flex-col max-h-[95vh]" id="deleteContainer">
        
        <div class="p-6 sm:p-10 text-center space-y-5 sm:space-y-6 overflow-y-auto hide-scroll">
            
            <div class="mx-auto w-16 h-16 sm:w-20 sm:h-20 rounded-2xl sm:rounded-3xl bg-rose-50 dark:bg-rose-500/10 flex items-center justify-center text-rose-500 border border-rose-100 dark:border-rose-500/20 shadow-[0_0_30px_rgba(244,63,94,0.1)] shrink-0">
                <i class="far fa-trash-alt text-2xl sm:text-3xl animate-bounce-slow"></i>
            </div>

            <div class="space-y-2">
                <h3 class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tighter">¿Eliminar Insumo?</h3>
                <p class="text-[10px] sm:text-xs text-zinc-500 dark:text-zinc-400 font-bold leading-relaxed px-2 sm:px-4">
                    Estás a punto de borrar <span id="delete_nombre_display" class="text-zinc-900 dark:text-white font-black"></span>. Esta acción no se puede deshacer.
                </p>
            </div>

            {{-- Formulario para eliminar (Action se llena dinámicamente con JS) --}}
            <form id="formEliminar" action="" method="POST" class="flex flex-col gap-2 sm:gap-3 pt-2 sm:pt-4">
                @csrf
                @method('DELETE')
                
                <button type="submit" class="w-full h-11 sm:h-14 bg-rose-500 sm:bg-gradient-to-br sm:from-rose-500 sm:to-rose-700 text-white rounded-xl sm:rounded-2xl text-[10px] sm:text-[11px] font-black uppercase tracking-[0.2em] shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 hover:-translate-y-1 transition-all active:scale-95 outline-none">
                    Confirmar Eliminación
                </button>
            </form>

            <button type="button" onclick="closeDeleteModal()" class="w-full h-11 sm:h-14 rounded-xl sm:rounded-2xl text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-white/5 transition-all outline-none">
                No, mantenerlo
            </button>
        </div>
    </div>
</div>