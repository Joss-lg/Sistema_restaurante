{{-- modal-editar.blade.php --}}
<div id="modalEditar-{{ $item->id }}" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/60 backdrop-blur-sm">
    <div id="modalContainer-{{ $item->id }}" class="relative bg-[#1c1c1e] rounded-2xl w-full max-w-md mx-4 shadow-2xl scale-95 opacity-0 transition-all duration-200">

        {{-- Header --}}
        <div class="flex items-center gap-4 p-7 pb-5">
            <div class="w-12 h-12 rounded-xl bg-[#3B82F6]/15 flex items-center justify-center shrink-0">
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

        {{-- Divisor --}}
        <div class="mx-7 border-t border-white/5"></div>

        {{-- Formulario --}}
        <form action="{{ route('admin.inventario.update', $item->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-7 space-y-6">

                {{-- Nombre --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                        <i class="fas fa-tag text-[#3B82F6] text-[10px]"></i>
                        Nombre del Artículo
                    </label>
                    <input type="text" name="nombre" value="{{ $item->nombre }}"
                        class="w-full h-12 bg-[#111113] border border-white/8 rounded-xl px-4 text-sm font-semibold text-white placeholder:text-white/20 outline-none focus:border-[#3B82F6]/60 focus:ring-2 focus:ring-[#3B82F6]/10 transition-all">
                </div>

                {{-- Unidad de Medida --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                        <i class="fas fa-balance-scale text-[#3B82F6] text-[10px]"></i>
                        Unidad de Medida
                    </label>
                    <div class="relative">
                        <select name="unidad_medida"
                            class="w-full h-12 bg-[#111113] border border-white/8 rounded-xl px-4 pr-10 text-sm font-semibold text-white appearance-none outline-none focus:border-[#3B82F6]/60 focus:ring-2 focus:ring-[#3B82F6]/10 transition-all cursor-pointer">
                            @foreach(['kg' => 'Kilogramos (kg)', 'g' => 'Gramos (g)', 'l' => 'Litros (l)', 'ml' => 'Mililitros (ml)', 'pz' => 'Piezas (pz)', 'caja' => 'Caja', 'bolsa' => 'Bolsa'] as $val => $label)
                                <option value="{{ $val }}" {{ $item->unidad_medida === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-white/30">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- Categoría --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                        <i class="fas fa-layer-group text-[#3B82F6] text-[10px]"></i>
                        Categoría
                    </label>
                    <div class="relative">
                        <select name="categoria_id"
                            class="w-full h-12 bg-[#111113] border border-white/8 rounded-xl px-4 pr-10 text-sm font-semibold text-white appearance-none outline-none focus:border-[#3B82F6]/60 focus:ring-2 focus:ring-[#3B82F6]/10 transition-all cursor-pointer">
                            <option value="">Sin categoría</option>
                            @foreach($categorias ?? [] as $cat)
                                <option value="{{ $cat->id }}" {{ $item->categoria_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-white/30">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- Stock Actual y Mínimo --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                            <i class="fas fa-layer-group text-[#3B82F6] text-[10px]"></i>
                            Stock Actual
                        </label>
                        <div class="h-12 bg-[#111113] border border-white/5 rounded-xl px-4 flex items-center">
                            <span class="text-sm font-bold text-[#3B82F6]">{{ number_format($item->stock_actual, 2) }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                            <i class="fas fa-bell text-rose-400 text-[10px]"></i>
                            Stock Mínimo
                        </label>
                        <input type="number" name="stock_minimo" value="{{ $item->stock_minimo }}" step="0.01" min="0"
                            class="w-full h-12 bg-[#111113] border border-white/8 rounded-xl px-4 text-sm font-semibold text-white outline-none focus:border-[#3B82F6]/60 focus:ring-2 focus:ring-[#3B82F6]/10 transition-all">
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between px-7 pb-7 gap-4">
                <button type="button" onclick="cerrarModalEspecifico('modalEditar-{{ $item->id }}')"
                    class="text-sm font-bold text-white/30 hover:text-white transition-colors uppercase tracking-widest outline-none px-2 py-3">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-1 h-12 bg-[#3B82F6] hover:bg-[#2563EB] text-white font-black text-sm uppercase tracking-widest rounded-xl flex items-center justify-center gap-2 transition-colors shadow-lg shadow-[#3B82F6]/20 outline-none">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>