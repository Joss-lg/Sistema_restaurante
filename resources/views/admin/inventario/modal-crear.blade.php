<div id="modalCrear" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all duration-300">

    <div id="createContainer" class="bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0">

        <div class="p-8 pb-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 border border-blue-500/20">
                    <i class="fas fa-layer-group text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight uppercase">Nuevo Artículo</h3>
                    <p class="text-[9px] text-zinc-500 dark:text-zinc-400 font-bold uppercase tracking-[0.2em]">Añadir al Inventario</p>
                </div>
            </div>
            <button onclick="closeCreateModal()" class="w-9 h-9 rounded-xl flex items-center justify-center bg-zinc-100 dark:bg-white/5 text-zinc-500 dark:text-zinc-400 hover:text-rose-500 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form action="{{ route('admin.inventario.store') }}" method="POST" class="p-8 pt-2 space-y-5">
            @csrf

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-tag opacity-40"></i> Nombre del Artículo
                </label>
                <input type="text" name="nombre" required
                    class="w-full h-11 bg-zinc-50 dark:bg-white/5 border border-transparent rounded-xl px-5 text-xs font-bold text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-white/10 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-500"
                    placeholder="Ej: Harina">
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-folder opacity-40"></i> Categoría
                </label>
                <div class="relative">
                    <select name="categoria_id" required
                        class="w-full h-11 bg-zinc-50 dark:bg-white/5 border border-transparent rounded-xl px-5 text-xs font-bold text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-white/10 focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer">
                        <option value="" class="dark:bg-zinc-900">Selecciona una categoría</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" class="dark:bg-zinc-900">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-zinc-500 dark:text-zinc-400 pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-scale-balanced opacity-40"></i> Unidad de Medida
                </label>
                <div class="relative">
                    <select name="unidad_medida" required
                        class="w-full h-11 bg-zinc-50 dark:bg-white/5 border border-transparent rounded-xl px-5 text-xs font-bold text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-white/10 focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer">
                        <option value="gramos" class="dark:bg-zinc-900">Gramos (g)</option>
                        <option value="litros" class="dark:bg-zinc-900">Litros (l)</option>
                        <option value="piezas" class="dark:bg-zinc-900">Piezas (pz)</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-zinc-500 dark:text-zinc-400 pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-dollar-sign opacity-40"></i> Precio Compra
                    </label>
                    <input type="number" step="0.01" name="precio_compra"
                        class="w-full h-11 bg-zinc-50 dark:bg-white/5 border border-transparent rounded-xl px-5 text-xs font-black text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-white/10 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-500"
                        placeholder="0.00">
                </div>

                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-bell opacity-40"></i> Stock Mínimo
                    </label>
                    <input type="number" name="stock_minimo" required
                        class="w-full h-11 bg-zinc-50 dark:bg-white/5 border border-transparent rounded-xl px-5 text-xs font-bold text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-white/10 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-500"
                        placeholder="0">
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 pb-2">
                <button type="button" onclick="closeCreateModal()"
                    class="flex-1 h-12 rounded-xl text-[9px] font-black uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-white/5 transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-[1.5] h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-blue-600/20 transition-all active:scale-95 outline-none">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>