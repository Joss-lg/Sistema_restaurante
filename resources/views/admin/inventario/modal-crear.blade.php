{{-- modal-crear.blade.php --}}
<div id="modalCrear" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-3 sm:p-4 transition-all duration-300">
    
    <div id="createContainer" class="bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 w-full max-w-md rounded-[1.5rem] sm:rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0 flex flex-col max-h-[95dvh] sm:max-h-[92dvh]">
        
        <div class="p-5 sm:p-8 pb-4 shrink-0 flex justify-between items-center border-b border-zinc-100 dark:border-zinc-900/50">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg sm:rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 border border-blue-500/20 shrink-0">
                    <i class="fas fa-layer-group text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight uppercase m-0 leading-tight truncate">Nuevo Artículo</h3>
                    <p class="text-[8px] sm:text-[9px] text-zinc-500 dark:text-zinc-400 font-bold uppercase tracking-[0.2em] mt-1">Añadir al Inventario</p>
                </div>
            </div>
            
            <button onclick="closeCreateModal()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl flex items-center justify-center bg-zinc-100 dark:bg-white/5 text-zinc-500 dark:text-zinc-400 hover:text-rose-500 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all outline-none shrink-0">
                <i class="fas fa-times text-xs sm:text-sm"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.inventario.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
            @csrf

            {{-- Cuerpo con scroll táctil --}}
            <div class="p-5 sm:p-8 pt-4 sm:pt-6 space-y-4 sm:space-y-5 overflow-y-auto flex-1 overscroll-contain hide-scroll" style="-webkit-overflow-scrolling: touch;">

                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[8px] sm:text-[9px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-tag opacity-40"></i> Nombre del Artículo
                    </label>
                    <input type="text" name="nombre" required
                        class="w-full h-11 bg-zinc-50 dark:bg-white/5 border border-transparent rounded-xl px-4 sm:px-5 text-base sm:text-xs font-bold text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-white/10 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-500"
                        placeholder="Ej: Harina">
                </div>
                
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[8px] sm:text-[9px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-folder opacity-40"></i> Categoría
                    </label>
                    <div class="relative">
                        <select name="categoria_id" required
                            class="w-full h-11 bg-zinc-50 dark:bg-white/5 border border-transparent rounded-xl px-4 sm:px-5 text-base sm:text-xs font-bold text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-white/10 focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer">
                            <option value="" class="dark:bg-zinc-900">Selecciona una categoría</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" class="dark:bg-zinc-900">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 sm:right-5 top-1/2 -translate-y-1/2 text-zinc-500 dark:text-zinc-400 pointer-events-none text-[10px]"></i>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[8px] sm:text-[9px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-scale-balanced opacity-40"></i> Unidad de Medida
                    </label>
                    <div class="relative">
                        <select name="unidad_medida" required
                            class="w-full h-11 bg-zinc-50 dark:bg-white/5 border border-transparent rounded-xl px-4 sm:px-5 text-base sm:text-xs font-bold text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-white/10 focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer">
                            <option value="g" class="dark:bg-zinc-900">Gramos (g)</option>
                            <option value="ml" class="dark:bg-zinc-900">Mililitros (ml)</option>
                            <option value="pz" class="dark:bg-zinc-900">Piezas (pz)</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 sm:right-5 top-1/2 -translate-y-1/2 text-zinc-500 dark:text-zinc-400 pointer-events-none text-[10px]"></i>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[8px] sm:text-[9px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-[0.2em] ml-1">
                            <i class="fas fa-dollar-sign opacity-40"></i> Precio Compra
                        </label>
                        <input type="number" step="0.01" name="precio_compra" inputmode="decimal"
                            class="w-full h-11 bg-zinc-50 dark:bg-white/5 border border-transparent rounded-xl px-4 sm:px-5 text-base sm:text-xs font-black text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-white/10 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-500"
                            placeholder="0.00">
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[8px] sm:text-[9px] font-black text-zinc-500 dark:text-zinc-400 uppercase tracking-[0.2em] ml-1">
                            <i class="fas fa-bell opacity-40"></i> Stock Mínimo
                        </label>
                        <input type="number" name="stock_minimo" required inputmode="numeric"
                            class="w-full h-11 bg-zinc-50 dark:bg-white/5 border border-transparent rounded-xl px-4 sm:px-5 text-base sm:text-xs font-bold text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-white/10 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-500"
                            placeholder="0">
                    </div>
                </div>
            </div>

            {{-- Botones fijos abajo, fuera del área con scroll --}}
            <div class="flex items-center gap-3 sm:gap-4 px-5 sm:px-8 py-4 border-t border-zinc-100 dark:border-zinc-900/50 shrink-0" style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                <button type="button" onclick="closeCreateModal()"
                    class="flex-1 h-11 sm:h-12 rounded-xl text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-white/5 transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-[1.5] h-11 sm:h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-blue-600/20 transition-all active:scale-95 outline-none flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>