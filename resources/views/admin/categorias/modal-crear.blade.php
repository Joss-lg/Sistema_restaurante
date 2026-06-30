<div id="modalCrear" class="hidden fixed inset-y-0 right-0 left-[74px] sm:left-0 sm:inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-3 sm:p-4">
    <div class="fixed inset-0 bg-black/60 -ml-[74px] sm:ml-0" onclick="closeCreateModal()"></div>
    
    <div id="createContainer" class="relative bg-[#1c1c1e] rounded-2xl w-full max-w-md mx-auto shadow-2xl scale-95 opacity-0 transition-all duration-200 max-h-[92vh] flex flex-col overflow-hidden">

        <div class="flex items-center gap-4 p-5 sm:p-7 pb-4 sm:pb-5">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-[#3B82F6]/15 flex items-center justify-center shrink-0">
                <i class="fas fa-plus text-[#3B82F6]"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-black text-white">Nueva Categoría</h2>
                <p class="text-[10px] text-white/30 uppercase tracking-widest mt-0.5">Agregar al menú</p>
            </div>
            <button onclick="closeCreateModal()" class="ml-auto w-8 h-8 flex items-center justify-center rounded-lg text-white/30 hover:text-white hover:bg-white/10 transition-colors outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <div class="mx-5 sm:mx-7 border-t border-white/5"></div>

        <form action="{{ route('admin.categorias.store') }}" method="POST" class="overflow-y-auto flex-1 custom-scrollbar">
            @csrf
            <div class="p-5 sm:p-7 space-y-4 sm:space-y-5">

                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                        <i class="fas fa-tag text-[#3B82F6] text-[10px]"></i> Nombre
                    </label>
                    <input type="text" name="nombre" placeholder="Ej: Platos Fuertes" required
                        class="w-full h-12 bg-[#111113] border border-white/8 rounded-xl px-4 text-sm font-semibold text-white placeholder:text-white/20 outline-none focus:border-[#3B82F6]/60 focus:ring-2 focus:ring-[#3B82F6]/10 transition-all">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2 col-span-2 sm:col-span-1">
                        <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                            <i class="fas fa-palette text-[#3B82F6] text-[10px]"></i> Color
                        </label>
                        <div class="flex items-center gap-3 h-12 bg-[#111113] border border-white/8 rounded-xl px-4">
                            <input type="color" name="color" value="#3B82F6"
                                class="w-7 h-7 rounded-md cursor-pointer border-0 bg-transparent outline-none">
                            <span class="text-xs text-white/40 font-mono">Elige un color</span>
                        </div>
                    </div>
                    <div class="space-y-2 col-span-2 sm:col-span-1">
                        <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                            <i class="fas fa-sort text-[#3B82F6] text-[10px]"></i> Orden
                        </label>
                        <input type="number" name="orden_visualizacion" placeholder="0" min="0"
                            class="w-full h-12 bg-[#111113] border border-white/8 rounded-xl px-4 text-sm font-semibold text-white placeholder:text-white/20 outline-none focus:border-[#3B82F6]/60 focus:ring-2 focus:ring-[#3B82F6]/10 transition-all">
                    </div>
                </div>

            </div>

            <div class="flex flex-col-reverse sm:flex-row items-center justify-between px-5 sm:px-7 pb-5 sm:pb-7 gap-3 sm:gap-4">
                <button type="button" onclick="closeCreateModal()"
                    class="w-full sm:w-auto text-center text-xs sm:text-sm font-bold text-white/30 hover:text-white transition-colors uppercase tracking-widest outline-none py-3">
                    Cancelar
                </button>
                <button type="submit"
                    class="w-full sm:flex-1 h-12 bg-[#3B82F6] hover:bg-[#2563EB] text-white font-black text-xs sm:text-sm uppercase tracking-widest rounded-xl flex items-center justify-center gap-2 transition-colors shadow-lg shadow-[#3B82F6]/20 outline-none">
                    <i class="fas fa-save"></i> Crear Categoría
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
    }
</style>