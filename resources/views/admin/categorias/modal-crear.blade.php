<div id="modalCrear" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-zinc-950/80 backdrop-blur-sm p-4 transition-colors duration-300">
    {{-- Backdrop de Cierre al Hacer Click Fuera --}}
    <div class="fixed inset-0 bg-transparent" onclick="closeCreateModal()"></div>
    
    {{-- Contenedor de Tarjeta Premium --}}
    <div id="createContainer" class="relative bg-zinc-900 modo-crema:bg-white rounded-2xl w-full max-w-md mx-auto shadow-2xl scale-95 opacity-0 transition-all duration-200 max-h-[90vh] flex flex-col overflow-hidden border border-zinc-800 modo-crema:border-zinc-200">

        {{-- Encabezado del Modal --}}
        <div class="flex items-center gap-4 p-6 pb-4">
            <div class="w-11 h-11 rounded-xl bg-blue-500/10 flex items-center justify-center shrink-0">
                <i class="fas fa-plus text-blue-400 modo-crema:text-blue-600 text-sm"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-zinc-100 modo-crema:text-zinc-900 tracking-tight">Nueva Categoría</h2>
                <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider mt-0.5">Agregar al menú del restaurante</p>
            </div>
            <button onclick="closeCreateModal()" class="ml-auto w-8 h-8 flex items-center justify-center rounded-lg text-zinc-500 hover:text-zinc-300 modo-crema:hover:text-zinc-700 hover:bg-zinc-800 modo-crema:hover:bg-zinc-100 transition-colors outline-none cursor-pointer">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <div class="mx-6 border-t border-zinc-800 modo-crema:border-zinc-200/80"></div>

        {{-- Formulario con scroll optimizado vía utilidades nativas --}}
        <form action="{{ route('admin.categorias.store') }}" method="POST" 
            class="overflow-y-auto flex-1 px-6 py-5 space-y-5 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-zinc-800/60 modo-crema:[&::-webkit-scrollbar-thumb]:bg-zinc-300/80 [&::-webkit-scrollbar-thumb]:rounded-full">
            @csrf

            {{-- Input: Nombre --}}
            <div class="space-y-1.5">
                <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-wider">
                    <i class="fas fa-tag text-blue-400 modo-crema:text-blue-500 text-[9px]"></i> Nombre de Categoría
                </label>
                <input type="text" name="nombre" placeholder="Ej: Platos Fuertes, Bebidas..." required
                    class="w-full h-11 bg-zinc-950/60 modo-crema:bg-zinc-50 border border-zinc-800 modo-crema:border-zinc-200 rounded-xl px-4 text-xs font-semibold text-zinc-100 modo-crema:text-zinc-800 placeholder:text-zinc-600 placeholder:opacity-80 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 shadow-inner transition-all">
            </div>

            {{-- Input: Área de Impresión (NUEVO) --}}
            <div class="space-y-1.5">
                <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-wider">
                    <i class="fas fa-print text-blue-400 modo-crema:text-blue-500 text-[9px]"></i> Área de Impresión / Comanda
                </label>
                <select name="area_impresion" required
                    class="w-full h-11 bg-zinc-950/60 modo-crema:bg-zinc-50 border border-zinc-800 modo-crema:border-zinc-200 rounded-xl px-4 text-xs font-semibold text-zinc-100 modo-crema:text-zinc-800 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 shadow-inner transition-all appearance-none cursor-pointer">
                    <option value="Cocina" class="bg-zinc-900 text-white modo-crema:bg-white modo-crema:text-zinc-800">🍳 Cocina / Calientes</option>
                    <option value="Barra" class="bg-zinc-900 text-white modo-crema:bg-white modo-crema:text-zinc-800">🍹 Barra / Bebidas</option>
                    <option value="Parrilla" class="bg-zinc-900 text-white modo-crema:bg-white modo-crema:text-zinc-800">🔥 Parrilla</option>
                </select>
            </div>

            {{-- Grid de Color y Orden --}}
            <div class="grid grid-cols-2 gap-4">
                {{-- Selector de Color --}}
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-wider">
                        <i class="fas fa-palette text-blue-400 modo-crema:text-blue-500 text-[9px]"></i> Identificador Visual
                    </label>
                    <div class="flex items-center gap-3 h-11 bg-zinc-950/60 modo-crema:bg-zinc-50 border border-zinc-800 modo-crema:border-zinc-200 rounded-xl px-3 shadow-inner">
                        <input type="color" name="color" value="#3B82F6"
                            class="w-7 h-7 rounded-lg cursor-pointer border-0 bg-transparent outline-none ring-1 ring-zinc-800/80 modo-crema:ring-zinc-200">
                        <span class="text-[11px] text-zinc-500 font-bold">Elige un color</span>
                    </div>
                </div>
                
                {{-- Prioridad de Orden --}}
                <div class="space-y-1.5 col-span-2 sm:col-span-1">
                    <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-wider">
                        <i class="fas fa-sort text-blue-400 modo-crema:text-blue-500 text-[9px]"></i> Orden Visual
                    </label>
                    <input type="number" name="orden_visualizacion" placeholder="0" min="0"
                        class="w-full h-11 bg-zinc-950/60 modo-crema:bg-zinc-50 border border-zinc-800 modo-crema:border-zinc-200 rounded-xl px-4 text-xs font-semibold text-zinc-100 modo-crema:text-zinc-800 placeholder:text-zinc-600 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 shadow-inner transition-all">
                </div>
            </div>

            {{-- Zona de Botones de Control Inferior --}}
            <div class="flex flex-col-reverse sm:flex-row items-center justify-between pt-4 gap-3">
                <button type="button" onclick="closeCreateModal()"
                    class="w-full sm:w-auto text-center text-xs font-bold text-zinc-500 hover:text-zinc-300 modo-crema:hover:text-zinc-800 transition-colors uppercase tracking-wider outline-none py-2.5 cursor-pointer">
                    Cancelar
                </button>
                <button type="submit"
                    class="w-full sm:flex-1 h-11 bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white font-bold text-xs uppercase tracking-wider rounded-xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-blue-600/10 outline-none cursor-pointer">
                    <i class="fas fa-save text-[10px]"></i> Guardar Categoría
                </button>
            </div>
        </form>
    </div>
</div>