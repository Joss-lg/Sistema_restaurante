<div id="modalEditar-{{ $categoria->id }}" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/60 backdrop-blur-sm">
    <div id="modalContainer-{{ $categoria->id }}" class="relative bg-[#1c1c1e] rounded-2xl w-full max-w-md mx-4 shadow-2xl scale-95 opacity-0 transition-all duration-200 overflow-hidden">

        <div class="flex items-center gap-4 p-7 pb-5">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                style="background-color: {{ $categoria->color ?? '#3B82F6' }}20">
                <i class="fas fa-pen" style="color: {{ $categoria->color ?? '#3B82F6' }}"></i>
            </div>
            <div>
                <h2 class="text-xl font-black text-white">Editar Categoría</h2>
                <p class="text-xs text-white/30 uppercase tracking-widest mt-0.5">{{ strtoupper($categoria->nombre) }}</p>
            </div>
            <button onclick="cerrarModalEspecifico('modalEditar-{{ $categoria->id }}')"
                class="ml-auto w-8 h-8 flex items-center justify-center rounded-lg text-white/30 hover:text-white hover:bg-white/10 transition-colors outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <div class="mx-7 border-t border-white/5"></div>

        <form action="{{ route('admin.categorias.update', $categoria->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="p-7 space-y-5">

                {{-- Input: Nombre --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                        <i class="fas fa-tag text-[#3B82F6] text-[10px]"></i> Nombre
                    </label>
                    <input type="text" name="nombre" value="{{ $categoria->nombre }}" required
                        class="w-full h-12 bg-[#111113] border border-white/8 rounded-xl px-4 text-sm font-semibold text-white outline-none focus:border-[#3B82F6]/60 focus:ring-2 focus:ring-[#3B82F6]/10 transition-all">
                </div>

                {{-- Input: Área de Impresión (NUEVO) --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                        <i class="fas fa-print text-[#3B82F6] text-[10px]"></i> Área de Impresión / Comanda
                    </label>
                    <select name="area_impresion" required
                        class="w-full h-12 bg-[#111113] border border-white/8 rounded-xl px-4 text-sm font-semibold text-white outline-none focus:border-[#3B82F6]/60 focus:ring-2 focus:ring-[#3B82F6]/10 transition-all appearance-none cursor-pointer">
                        <option value="Cocina" class="bg-[#1c1c1e] text-white" @selected($categoria->area_impresion == 'Cocina')>🍳 Cocina / Calientes</option>
                        <option value="Barra" class="bg-[#1c1c1e] text-white" @selected($categoria->area_impresion == 'Barra')>🍹 Barra / Bebidas</option>
                        <option value="Parrilla" class="bg-[#1c1c1e] text-white" @selected($categoria->area_impresion == 'Parrilla')>🔥 Parrilla</option>
                    </select>
                </div>

                {{-- Grid de Color y Orden --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                            <i class="fas fa-palette text-[#3B82F6] text-[10px]"></i> Color
                        </label>
                        <div class="flex items-center gap-3 h-12 bg-[#111113] border border-white/8 rounded-xl px-4">
                            <input type="color" name="color" value="{{ $categoria->color ?? '#3B82F6' }}"
                                class="w-7 h-7 rounded-md cursor-pointer border-0 bg-transparent outline-none">
                            <span class="text-xs text-white/40 font-mono">{{ $categoria->color ?? '#3B82F6' }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[10px] font-black text-white/40 uppercase tracking-widest">
                            <i class="fas fa-sort text-[#3B82F6] text-[10px]"></i> Orden
                        </label>
                        <input type="number" name="orden_visualizacion" value="{{ $categoria->orden_visualizacion ?? 0 }}" min="0"
                            class="w-full h-12 bg-[#111113] border border-white/8 rounded-xl px-4 text-sm font-semibold text-white outline-none focus:border-[#3B82F6]/60 focus:ring-2 focus:ring-[#3B82F6]/10 transition-all">
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-between px-7 pb-7 gap-4">
                <button type="button" onclick="cerrarModalEspecifico('modalEditar-{{ $categoria->id }}')"
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