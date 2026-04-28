<div id="modalCrear" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all duration-300">
    
    <!-- Contenedor dinámico (var(--card-color)) -->
    <div class="bg-[var(--card-color)] border border-black/5 w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0" id="createContainer">
        
        <div class="p-8 pb-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#3B82F6]/10 flex items-center justify-center text-[#3B82F6] border border-[#3B82F6]/20">
                    <i class="fas fa-layer-group text-xl"></i>
                </div>
                <div>
                    <!-- Texto dinámico -->
                    <h3 class="text-xl font-black text-[var(--text-color)] tracking-tighter uppercase">Nuevo Artículo</h3>
                    <p class="text-[9px] text-[var(--text-muted)] font-bold uppercase tracking-[0.2em]">Añadir al Inventario</p>
                </div>
            </div>
            <button onclick="closeCreateModal()" class="w-9 h-9 rounded-xl flex items-center justify-center bg-black/5 text-[var(--text-muted)] hover:text-rose-500 hover:bg-rose-500/10 transition-all outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.inventario.store') }}" method="POST" class="p-8 pt-2 space-y-5">
            @csrf

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-tag opacity-40"></i> Nombre del Artículo
                </label>
                <!-- name="nombre" (Este estaba bien) -->
                <input type="text" name="nombre" required
                    class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10 outline-none transition-all placeholder:text-[var(--text-muted)]"
                    placeholder="Ej: Harina">
            </div>

            <!-- NUEVO CAMPO: Categoría (Requerido por tu controlador) -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-folder opacity-40"></i> Categoría
                </label>
                <div class="relative">
                    <select name="categoria_id" required
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-[#3B82F6] outline-none transition-all appearance-none cursor-pointer">
                        <option value="">Selecciona una categoría</option>
                        <!-- Asumiendo que mandas $categorias desde el index de tu controlador -->
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" class="bg-[var(--card-color)]">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-scale-balanced opacity-40"></i> Unidad de Medida
                </label>
                <div class="relative">
                    <!-- CAMBIADO: name="unidad_medida" -->
                    <select name="unidad_medida" required
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-[#3B82F6] outline-none transition-all appearance-none cursor-pointer">
                        <option value="gramos" class="bg-[var(--card-color)]">Gramos (g)</option>
                        <option value="litros" class="bg-[var(--card-color)]">Litros (l)</option>
                        <option value="piezas" class="bg-[var(--card-color)]">Piezas (pz)</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- CAMBIADO: name="precio_compra" (Cambiamos Cantidad por Precio, ya que el stock inicia en 0) -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-dollar-sign opacity-40"></i> Precio Compra
                    </label>
                    <input type="number" step="0.01" name="precio_compra"
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-black text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10 outline-none transition-all"
                        placeholder="0.00">
                </div>

                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-bell opacity-40"></i> Stock Mínimo
                    </label>
                    <!-- CAMBIADO: name="stock_minimo" -->
                    <input type="number" name="stock_minimo" required
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10 outline-none transition-all"
                        placeholder="0">
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 pb-2">
                <button type="button" onclick="closeCreateModal()" 
                    class="flex-1 h-12 rounded-xl text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] hover:text-[var(--text-color)] hover:bg-black/5 transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit" 
                    class="flex-[1.5] h-12 bg-[#3B82F6] hover:bg-[#2563EB] text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-[#3B82F6]/20 transition-all active:scale-95 outline-none">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>