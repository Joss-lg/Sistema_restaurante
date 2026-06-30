<div id="modalEditarRol" class="hidden fixed inset-0 bg-gray-900/40 dark:bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity duration-300">
    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-3xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all duration-300 scale-100">
        
        <div class="p-6 sm:p-8 border-b border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-900/50 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Editar Puesto</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Actualiza los datos del puesto en el sistema</p>
            </div>
            <button type="button" onclick="cerrarModalEditar()" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-800 text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formEditarRol" method="POST" class="p-6 sm:p-8 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="editNombre" class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 block">Nombre del Puesto</label>
                <input type="text" id="editNombre" name="nombre" required
                       class="w-full h-12 bg-gray-50 dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-xl px-4 text-sm font-medium text-gray-900 dark:text-white outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
            </div>

            <div>
                <label for="editDescripcion" class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 block">Descripción / Notas</label>
                <textarea id="editDescripcion" name="descripcion" rows="3"
                          class="w-full bg-gray-50 dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-xl p-4 text-sm font-medium text-gray-900 dark:text-white outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 resize-none"></textarea>
            </div>

            <div>
                <label for="editPuedePOS" class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 block">Acceso al Sistema POS</label>
                <select id="editPuedePOS" name="puede_acceder_pos" required
                        class="w-full h-12 bg-gray-50 dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-xl px-4 text-sm font-bold text-gray-900 dark:text-white outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 appearance-none">
                    <option value="1">Sí, permitir Punto de Venta</option>
                    <option value="0">No, Acceso Administrativo</option>
                </select>
            </div>

            <div class="pt-6 mt-2 flex justify-end gap-3">
                <button type="button" onclick="cerrarModalEditar()" 
                        class="px-5 py-2.5 rounded-full text-xs font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors outline-none">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-6 py-2.5 bg-blue-600 dark:bg-blue-500 text-white rounded-full text-xs font-bold hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors shadow-lg shadow-blue-500/20 outline-none flex items-center gap-2">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>