<div id="modalCrear" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-md p-4 transition-all duration-300">
    
    <div class="bg-[#0f0f11] border border-white/10 w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0" id="createContainer">
        
        <div class="p-8 pb-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#3B82F6]/10 flex items-center justify-center text-[#3B82F6] border border-[#3B82F6]/20">
                    <i class="fas fa-layer-group text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-white tracking-tighter uppercase">Nuevo Insumo</h3>
                    <p class="text-[9px] text-zinc-500 font-bold uppercase tracking-[0.2em]">Añadir al Inventario</p>
                </div>
            </div>
            <button onclick="closeCreateModal()" class="w-9 h-9 rounded-lg flex items-center justify-center bg-white/5 text-zinc-500 hover:text-rose-500 transition-all outline-none">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>

        <form action="{{ route('admin.inventario.store') }}" method="POST" class="p-8 pt-2 space-y-5">
            @csrf

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[#3B82F6] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-fingerprint opacity-50"></i> Código Único (Automático)
                </label>
                <input type="text" name="codigo" readonly
                    class="w-full h-11 bg-white/[0.02] border border-white/5 rounded-xl px-5 text-xs font-mono font-bold text-[#3B82F6]/70 outline-none cursor-not-allowed tracking-[0.1em]"
                    value="OLN-INV-{{ rand(1000, 9999) }}">
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-tag opacity-40"></i> Nombre del Producto
                </label>
                <input type="text" name="nombre" required
                    class="w-full h-11 bg-white/[0.03] border border-white/5 rounded-xl px-5 text-xs font-bold text-white focus:border-[#3B82F6]/40 outline-none transition-all placeholder:text-zinc-700"
                    placeholder="Harina, Salsa, etc...">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-hashtag opacity-40"></i> Cantidad
                    </label>
                    <input type="number" name="cantidad" required
                        class="w-full h-11 bg-white/[0.03] border border-white/5 rounded-xl px-5 text-xs font-black text-white focus:border-[#3B82F6]/40 outline-none transition-all">
                </div>

                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-scale-balanced opacity-40"></i> Unidad
                    </label>
                    <div class="relative">
                        <select name="unidad" required
                            class="w-full h-11 bg-[#121214] border border-white/5 rounded-xl px-5 text-xs font-bold text-white focus:border-[#3B82F6]/40 outline-none transition-all appearance-none cursor-pointer">
                            <option value="gramos">Gramos (g)</option>
                            <option value="litros">Litros (l)</option>
                            <option value="piezas">Piezas (pz)</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-zinc-600 pointer-events-none text-[10px]"></i>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 pb-2">
                <button type="button" onclick="closeCreateModal()" 
                    class="flex-1 h-12 rounded-xl text-[9px] font-black uppercase tracking-[0.2em] text-zinc-500 hover:text-white transition-all outline-none">
                    Descartar
                </button>
                <button type="submit" 
                    class="flex-[1.5] h-12 bg-[#3B82F6] hover:bg-[#2563EB] text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-[#3B82F6]/20 transition-all active:scale-95 outline-none">
                    Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>