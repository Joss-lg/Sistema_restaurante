{{-- resources/views/admin/categorias/modal-eliminar.blade.php --}}
<div id="modalEliminar" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-zinc-950/75 backdrop-blur-sm p-4">
    {{-- Capa trasera para cerrar si hacen click fuera --}}
    <div class="fixed inset-0 bg-transparent" onclick="closeDeleteModal()"></div>
    
    {{-- Contenedor de Alerta Crítica --}}
    <div id="deleteContainer" class="relative bg-zinc-900 modo-crema:bg-white rounded-2xl w-full max-w-sm mx-auto shadow-2xl scale-95 opacity-0 transition-all duration-200 border border-zinc-800 modo-crema:border-zinc-200 overflow-hidden">

        {{-- Icono de Advertencia Destacado y Mensaje --}}
        <div class="p-6 text-center space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 flex items-center justify-center mx-auto ring-1 ring-rose-500/20">
                <i class="fas fa-trash-alt text-rose-500 text-lg"></i>
            </div>
            <div class="space-y-1.5">
                <h2 class="text-base font-black text-zinc-100 modo-crema:text-zinc-900 tracking-tight">¿Eliminar categoría?</h2>
                <p class="text-xs font-medium text-zinc-400 modo-crema:text-zinc-500 leading-relaxed px-2">
                    Vas a eliminar permanentemente la categoria <span id="delete_nombre_display" class="text-zinc-100 modo-crema:text-zinc-900 font-bold underline decoration-rose-500/40 decoration-2"></span>. Esta acción no se puede revertir.
                </p>
            </div>
        </div>

        {{-- Formulario con Botones Balanceados --}}
        <form id="formEliminar" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex items-center gap-3 px-6 pb-6">
                {{-- Botón Cancelar (Neutro) --}}
                <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 h-10 bg-zinc-800/50 hover:bg-zinc-800 modo-crema:bg-zinc-100 modo-crema:hover:bg-zinc-200 text-zinc-400 modo-crema:text-zinc-600 hover:text-zinc-200 modo-crema:hover:text-zinc-800 font-bold text-xs uppercase tracking-wider rounded-xl transition-all outline-none cursor-pointer">
                    Cancelar
                </button>
                {{-- Botón de Destrucción (Peligro) --}}
                <button type="submit"
                    class="flex-1 h-10 bg-rose-600 hover:bg-rose-500 active:scale-[0.98] text-white font-black text-xs uppercase tracking-wider rounded-xl transition-all shadow-md shadow-rose-600/10 outline-none cursor-pointer">
                    Sí, eliminar
                </button>
            </div>
        </form>
    </div>
</div>