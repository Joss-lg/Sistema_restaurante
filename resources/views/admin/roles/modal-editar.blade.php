<!-- Modal Editar Rol -->
<div id="modalEditarRol" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2rem] shadow-2xl max-w-md w-full modal-panel opacity-0 translate-y-4 transition-all duration-300">
        
        <div class="p-8 border-b border-[var(--border-color)]">
            <h2 class="text-xl font-black text-[var(--text-color)] tracking-tight">Editar Puesto</h2>
            <p class="text-xs text-[var(--text-muted)] mt-2">Actualiza los datos del puesto</p>
        </div>

        <form id="formEditarRol" method="POST" class="p-8 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="editNombre" class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em] mb-3 block">Nombre del Puesto</label>
                <input type="text" id="editNombre" name="nombre" required
                       class="w-full h-12 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-sm text-[var(--text-color)] outline-none transition-all focus:border-[#3B82F6]">
            </div>

            <div>
                <label for="editDescripcion" class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em] mb-3 block">Descripción / Notas</label>
                <textarea id="editDescripcion" name="descripcion" rows="3"
                          class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl p-5 text-sm text-[var(--text-color)] outline-none transition-all focus:border-[#3B82F6] resize-none"></textarea>
            </div>

            <div>
                <label for="editPuedePOS" class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em] mb-3 block">Acceso al POS</label>
                <select id="editPuedePOS" name="puede_acceder_pos" required
                        class="w-full h-12 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-sm font-medium text-[var(--text-color)] outline-none transition-all focus:border-[#3B82F6]">
                    <option value="1">Sí (Punto de Venta)</option>
                    <option value="0">No (Solo Administrativo)</option>
                </select>
            </div>

            <div class="pt-4 border-t border-[var(--border-color)] flex justify-end gap-3">
                <button type="button" onclick="cerrarModalEditar()" 
                        class="px-6 py-3 rounded-xl text-xs font-bold text-[var(--text-muted)] hover:text-[var(--text-color)] transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-6 py-3 bg-[#3B82F6] text-white rounded-xl text-xs font-bold hover:bg-blue-600 transition-all outline-none shadow-md">
                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalEditar(id, nombre, descripcion, puede_pos) {
        const modal = document.getElementById('modalEditarRol');
        const form = document.getElementById('formEditarRol');
        
        form.action = `/admin/roles/${id}`;
        document.getElementById('editNombre').value = nombre;
        document.getElementById('editDescripcion').value = descripcion || '';
        document.getElementById('editPuedePOS').value = puede_pos ? '1' : '0';
        
        setTimeout(() => {
            modal.classList.remove('hidden');
            modal.querySelector('.modal-panel').classList.add('opacity-100', '-translate-y-0');
        }, 10);
    }

    function cerrarModalEditar() {
        const modal = document.getElementById('modalEditarRol');
        const panel = modal.querySelector('.modal-panel');
        
        panel.classList.remove('opacity-100', '-translate-y-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Cerrar modal al hacer click fuera
    document.getElementById('modalEditarRol')?.addEventListener('click', (e) => {
        if (e.target.id === 'modalEditarRol') cerrarModalEditar();
    });
</script>
