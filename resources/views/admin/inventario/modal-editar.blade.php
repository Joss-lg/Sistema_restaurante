{{-- modal-editar.blade.php --}}
<div id="modalEditar-{{ $item->id }}" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/60 backdrop-blur-sm">
    <div id="modalContainer-{{ $item->id }}" class="relative bg-[#1c1c1e] rounded-2xl w-full max-w-md mx-4 shadow-2xl scale-95 opacity-0 transition-all duration-200 overflow-hidden">

        <div class="flex items-center gap-4 p-7 pb-5">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 bg-[#3B82F6]/20">
                <i class="fas fa-pen text-[#3B82F6]"></i>
            </div>
            <div>
                <h2 class="text-xl font-black text-white">Editar Insumo</h2>
                <p class="text-xs text-white/30 uppercase tracking-widest mt-0.5">{{ strtoupper($item->nombre) }}</p>
            </div>
            <button onclick="cerrarModalEspecifico('modalEditar-{{ $item->id }}')"
                class="ml-auto w-8 h-8 flex items-center justify-center rounded-lg text-white/30 hover:text-white hover:bg-white/10 transition-colors outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <div class="mx-7 border-t border-white/5"></div>

        <form action="{{ route('admin.inventario.update', $item->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="p-7 space-y-5">

                {{-- Input: Nombre --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                        <i class="fas fa-tag text-[#3B82F6] text-[10px]"></i> Nombre del Artículo
                    </label>
                    <input type="text" name="nombre" value="{{ $item->nombre }}" required
                        class="w-full h-12 bg-[#111113] border border-white/8 rounded-xl px-4 text-sm font-semibold text-white outline-none focus:border-[#3B82F6]/60 focus:ring-2 focus:ring-[#3B82F6]/10 transition-all">
                </div>

                {{-- Select: Unidad de Medida --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                        <i class="fas fa-scale-balanced text-[#3B82F6] text-[10px]"></i> Unidad de Medida
                    </label>
                    <select name="unidad_medida" required
                        class="w-full h-12 bg-[#111113] border border-white/8 rounded-xl px-4 text-sm font-semibold text-white outline-none focus:border-[#3B82F6]/60 focus:ring-2 focus:ring-[#3B82F6]/10 transition-all appearance-none cursor-pointer">
                        <option value="g" class="bg-[#1c1c1e] text-white" {{ $item->unidad_medida === 'g' ? 'selected' : '' }}>Gramos (g)</option>
                        <option value="ml" class="bg-[#1c1c1e] text-white" {{ $item->unidad_medida === 'ml' ? 'selected' : '' }}>Mililitros (ml)</option>
                        <option value="pz" class="bg-[#1c1c1e] text-white" {{ $item->unidad_medida === 'pz' ? 'selected' : '' }}>Piezas (pz)</option>
                    </select>
                </div>

                {{-- Select: Categoría --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                        <i class="fas fa-folder text-[#3B82F6] text-[10px]"></i> Categoría
                    </label>
                    <select name="categoria_id"
                        class="w-full h-12 bg-[#111113] border border-white/8 rounded-xl px-4 text-sm font-semibold text-white outline-none focus:border-[#3B82F6]/60 focus:ring-2 focus:ring-[#3B82F6]/10 transition-all appearance-none cursor-pointer">
                        <option value="" class="bg-[#1c1c1e] text-white">Sin categoría</option>
                        @foreach($categorias ?? [] as $cat)
                            <option value="{{ $cat->id }}" class="bg-[#1c1c1e] text-white" {{ $item->categoria_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Grid: Stock Actual y Stock Mínimo --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                            <i class="fas fa-box text-[#3B82F6] text-[10px]"></i> Stock Actual
                        </label>
                        <div class="flex items-center h-12 bg-[#111113]/60 border border-white/5 rounded-xl px-4 cursor-not-allowed">
                            <span class="text-sm font-black text-[#3B82F6]">{{ number_format($item->stock_actual, 2) }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                            <i class="fas fa-bell text-[#3B82F6] text-[10px]"></i> Stock Mínimo
                        </label>
                        <input type="number" name="stock_minimo" value="{{ $item->stock_minimo }}" step="0.01" min="0" required
                            class="w-full h-12 bg-[#111113] border border-white/8 rounded-xl px-4 text-sm font-semibold text-white outline-none focus:border-[#3B82F6]/60 focus:ring-2 focus:ring-[#3B82F6]/10 transition-all">
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-between px-7 pb-7 gap-4">
                <button type="button" onclick="cerrarModalEspecifico('modalEditar-{{ $item->id }}')"
                    class="text-sm font-bold text-white/30 hover:text-white transition-colors uppercase tracking-widest outline-none px-2 py-3">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-1 h-12 bg-[#3B82F6] hover:bg-[#2563EB] text-white font-black text-sm uppercase tracking-widest rounded-xl flex items-center justify-center gap-2 transition-colors shadow-lg shadow-[#3B82F6]/20 outline-none">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>