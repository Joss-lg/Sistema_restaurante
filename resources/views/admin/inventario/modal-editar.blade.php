<div id="modalEditar" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/70 backdrop-blur-md p-4 transition-all">
    
    <div class="bg-[#121214] border border-white/10 w-full max-w-md rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.5)] overflow-hidden transform transition-all scale-95 opacity-0" id="modalContainer">
        
        <div class="p-8 pb-4 flex justify-between items-start">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-[#3B82F6]/10 flex items-center justify-center text-[#3B82F6]">
                    <i class="fas fa-pen-to-square text-xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight leading-none">Ajustar Insumo</h3>
                    <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-[0.2em] mt-2">Gestión de existencias</p>
                </div>
            </div>
            <button onclick="closeModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-zinc-500 hover:bg-zinc-800 hover:text-rose-500 transition-all">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form action="#" method="POST" class="p-8 pt-4 space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-3">
                <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 uppercase tracking-widest pl-1">
                    <i class="fas fa-tag text-[#3B82F6]"></i> Nombre del Artículo
                </label>
                <input type="text" id="edit_nombre" name="nombre" 
                    class="w-full h-14 bg-white/[0.03] border border-white/10 rounded-2xl px-5 text-sm font-bold text-white focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10 outline-none transition-all placeholder:text-zinc-600"
                    placeholder="Ej. Harina de Trigo">
            </div>

            <div class="space-y-3">
                <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 uppercase tracking-widest pl-1">
                    <i class="fas fa-scale-balanced text-[#3B82F6]"></i> Unidad de Medida
                </label>
                <div class="relative">
                    <select id="edit_unidad" name="unidad" class="w-full h-14 bg-white/[0.03] border border-white/10 rounded-2xl px-5 text-sm font-bold text-white focus:border-[#3B82F6] outline-none transition-all appearance-none cursor-pointer">
                        <option value="gramos" class="bg-zinc-900">Gramos (g)</option>
                        <option value="litros" class="bg-zinc-900">Litros (l)</option>
                        <option value="piezas" class="bg-zinc-900">Piezas (pz)</option>
                        <option value="kilos" class="bg-zinc-900">Kilogramos (kg)</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-zinc-500 pointer-events-none text-xs"></i>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 uppercase tracking-widest pl-1">
                        <i class="fas fa-layer-group text-[#3B82F6]"></i> Actual
                    </label>
                    <input type="number" id="edit_cantidad" name="cantidad" 
                        class="w-full h-14 bg-white/[0.03] border border-white/10 rounded-2xl px-5 text-sm font-black text-[#3B82F6] focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10 outline-none transition-all">
                </div>
                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 uppercase tracking-widest pl-1">
                        <i class="fas fa-bell text-rose-500"></i> Mínimo
                    </label>
                    <input type="number" id="edit_minimo" name="minimo" 
                        class="w-full h-14 bg-white/[0.03] border border-white/10 rounded-2xl px-5 text-sm font-bold text-white focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10 outline-none transition-all">
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="button" onclick="closeModal()" class="flex-1 h-14 rounded-2xl text-xs font-black uppercase tracking-widest text-zinc-400 hover:text-white hover:bg-white/5 transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit" class="flex-[1.5] h-14 bg-gradient-to-r from-[#3B82F6] to-[#2563EB] text-white rounded-2xl text-xs font-black uppercase tracking-[0.15em] shadow-lg shadow-[#3B82F6]/30 hover:shadow-[#3B82F6]/50 transition-all transform active:scale-95 outline-none">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>