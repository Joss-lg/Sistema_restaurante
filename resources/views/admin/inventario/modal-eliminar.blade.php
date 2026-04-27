<div id="modalEliminar" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-xl p-4 transition-all duration-300">
    
    <div class="bg-[#0f0f11] border border-white/10 w-full max-w-sm rounded-[2.5rem] shadow-[0_0_80px_rgba(244,63,94,0.2)] overflow-hidden transform transition-all duration-500 scale-95 opacity-0" id="deleteContainer">
        
        <div class="p-10 text-center space-y-6">
            
            <div class="mx-auto w-20 h-20 rounded-3xl bg-rose-500/10 flex items-center justify-center text-rose-500 border border-rose-500/20 shadow-[0_0_30px_rgba(244,63,94,0.1)]">
                <i class="far fa-trash-alt text-3xl animate-bounce-slow"></i>
            </div>

            <div class="space-y-2">
                <h3 class="text-2xl font-black text-white tracking-tighter">¿Eliminar Insumo?</h3>
                <p class="text-xs text-zinc-500 font-bold leading-relaxed px-4">
                    Estás a punto de borrar <span id="delete_nombre_display" class="text-white"></span>. Esta acción no se puede deshacer.
                </p>
            </div>

            <div class="flex flex-col gap-3 pt-4">
                <form id="formEliminar" action="#" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full h-14 bg-gradient-to-br from-rose-500 to-rose-700 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 hover:-translate-y-1 transition-all active:scale-95 outline-none">
                        Confirmar Eliminación
                    </button>
                </form>

                <button type="button" onclick="closeDeleteModal()" class="w-full h-14 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 hover:text-white hover:bg-white/5 transition-all outline-none">
                    No, mantenerlo
                </button>
            </div>
        </div>
    </div>
</div>