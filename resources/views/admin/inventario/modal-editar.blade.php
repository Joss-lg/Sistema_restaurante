{{-- modal-editar.blade.php --}}
<div id="modalEditar-{{ $item->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 transition-all duration-300">
    <div id="modalContainer-{{ $item->id }}" class="relative bg-white dark:bg-zinc-950 border border-zinc-200/80 dark:border-zinc-800/80 rounded-[2.5rem] w-full max-w-md mx-4 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)] dark:shadow-[0_0_50px_rgba(0,0,0,0.5)] scale-95 opacity-0 overflow-hidden transform transition-all duration-500">

        {{-- Header --}}
        <div class="p-8 pb-5 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 dark:bg-blue-500/15 flex items-center justify-center text-blue-600 dark:text-blue-400 border border-blue-500/10 dark:border-blue-500/20 shrink-0">
                    <i class="fas fa-pen text-lg"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-zinc-900 dark:text-zinc-100 tracking-tight uppercase">Editar Insumo</h3>
                    <p class="text-[9px] text-zinc-400 dark:text-zinc-500 font-bold uppercase tracking-[0.2em] mt-0.5">{{ strtoupper($item->nombre) }}</p>
                </div>
            </div>
            <button onclick="cerrarModalEspecifico('modalEditar-{{ $item->id }}')"
                class="w-9 h-9 rounded-xl flex items-center justify-center bg-zinc-100 dark:bg-white/5 text-zinc-400 dark:text-zinc-500 hover:text-rose-500 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Divisor --}}
        <div class="mx-8 border-t border-zinc-100 dark:border-zinc-900"></div>

        {{-- Formulario --}}
        <form action="{{ route('admin.inventario.update', $item->id) }}" method="POST" class="p-8 pt-6 space-y-5">
            @csrf
            @method('PUT')

            {{-- Nombre --}}
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-tag opacity-60"></i> Nombre del Artículo
                </label>
                <input type="text" name="nombre" value="{{ $item->nombre }}" required
                    class="w-full h-12 bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 rounded-2xl px-5 text-xs font-bold text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-600">
            </div>

            {{-- Unidad de Medida --}}
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-scale-balanced opacity-60"></i> Unidad de Medida
                </label>
                <div class="relative">
                    <select name="unidad_medida" required
                        class="w-full h-12 bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 rounded-2xl px-5 text-xs font-bold text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all appearance-none cursor-pointer">
                        @foreach(['kg' => 'Kilogramos (kg)', 'g' => 'Gramos (g)', 'l' => 'Litros (l)', 'ml' => 'Mililitros (ml)', 'pz' => 'Piezas (pz)', 'caja' => 'Caja', 'bolsa' => 'Bolsa'] as $val => $label)
                            <option value="{{ $val }}" class="bg-white dark:bg-zinc-900" {{ $item->unidad_medida === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-zinc-400 dark:text-zinc-500 pointer-events-none text-[10px]"></i>
                </div>
            </div>

            {{-- Categoría --}}
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-folder opacity-60"></i> Categoría
                </label>
                <div class="relative">
                    <select name="categoria_id"
                        class="w-full h-12 bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 rounded-2xl px-5 text-xs font-bold text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all appearance-none cursor-pointer">
                        <option value="" class="bg-white dark:bg-zinc-900">Sin categoría</option>
                        @foreach($categorias ?? [] as $cat)
                            <option value="{{ $cat->id }}" class="bg-white dark:bg-zinc-900" {{ $item->categoria_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-zinc-400 dark:text-zinc-500 pointer-events-none text-[10px]"></i>
                </div>
            </div>

            {{-- Stock Actual y Mínimo --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-box opacity-60"></i> Stock Actual
                    </label>
                    <div class="h-12 bg-zinc-100/60 dark:bg-zinc-900/30 border border-zinc-200/40 dark:border-zinc-800/40 rounded-2xl px-5 flex items-center cursor-not-allowed">
                        <span class="text-xs font-black text-blue-600 dark:text-blue-400">{{ number_format($item->stock_actual, 2) }}</span>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-bell opacity-60"></i> Stock Mínimo
                    </label>
                    <input type="number" name="stock_minimo" value="{{ $item->stock_minimo }}" step="0.01" min="0" required
                        class="w-full h-12 bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 rounded-2xl px-5 text-xs font-bold text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-500">
                </div>
            </div>

            {{-- Footer (Botones) --}}
            <div class="flex items-center gap-4 pt-4 pb-2">
                <button type="button" onclick="cerrarModalEspecifico('modalEditar-{{ $item->id }}')"
                    class="flex-1 h-12 rounded-2xl text-[9px] font-black uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-white/5 transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-[1.5] h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-blue-600/20 hover:shadow-blue-600/30 transition-all active:scale-95 outline-none flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>