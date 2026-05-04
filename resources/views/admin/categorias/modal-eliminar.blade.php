<div id="modalEliminar" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/60 backdrop-blur-sm">
    <div id="deleteContainer" class="relative bg-[#1c1c1e] rounded-2xl w-full max-w-sm mx-4 shadow-2xl scale-95 opacity-0 transition-all duration-200">

        <div class="p-7 text-center space-y-4">
            <div class="w-14 h-14 rounded-2xl bg-rose-500/15 flex items-center justify-center mx-auto">
                <i class="fas fa-trash-alt text-rose-400 text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-white">¿Eliminar categoría?</h2>
                <p class="text-sm text-white/40 mt-1">
                    Vas a eliminar <span id="delete_nombre_display" class="text-white font-bold"></span>.
                    Esta acción no se puede deshacer.
                </p>
            </div>
        </div>

        <form id="formEliminar" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex items-center gap-3 px-7 pb-7">
                <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 h-12 bg-white/5 hover:bg-white/10 text-white/60 hover:text-white font-bold text-sm uppercase tracking-widest rounded-xl transition-colors outline-none">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-1 h-12 bg-rose-500 hover:bg-rose-400 text-white font-black text-sm uppercase tracking-widest rounded-xl transition-colors shadow-lg shadow-rose-500/20 outline-none">
                    Sí, eliminar
                </button>
            </div>
        </form>
    </div>
</div>