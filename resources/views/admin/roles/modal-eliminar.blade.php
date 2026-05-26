<!-- Modal Eliminar Rol -->
<div id="modalEliminarRol" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2rem] shadow-2xl max-w-md w-full modal-panel opacity-0 translate-y-4 transition-all duration-300">
        
        <div class="p-8 border-b border-rose-500/20 bg-rose-500/5">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-rose-500/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-rose-400 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-[var(--text-color)] tracking-tight">¿Eliminar Puesto?</h2>
                    <p class="text-xs text-[var(--text-muted)] mt-1">Esta acción no se puede deshacer</p>
                </div>
            </div>
        </div>

        <div class="p-8 space-y-4">
            <p class="text-sm text-[var(--text-muted)] leading-relaxed">
                Estás a punto de eliminar el puesto <span class="font-bold text-[var(--text-color)]" id="nomRolEliminar">""</span>.
            </p>
            
            <div class="p-4 rounded-xl bg-rose-500/5 border border-rose-500/20">
                <p class="text-xs text-rose-400 font-semibold">
                    <i class="fas fa-info-circle mr-2"></i>
                    Asegúrate de que no haya empleados asignados a este puesto.
                </p>
            </div>
        </div>

        <div class="p-8 border-t border-[var(--border-color)] flex justify-end gap-3">
            <button type="button" onclick="cerrarModalEliminar()" 
                    class="px-6 py-3 rounded-xl text-xs font-bold text-[var(--text-muted)] hover:text-[var(--text-color)] transition-all outline-none">
                Cancelar
            </button>
            <form id="formEliminarRol" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-6 py-3 bg-rose-500/80 hover:bg-rose-600 text-white rounded-xl text-xs font-bold transition-all outline-none shadow-md">
                    <i class="fas fa-trash-alt mr-2"></i> Eliminar Puesto
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function abrirModalEliminar(id, nombre) {
        const modal = document.getElementById('modalEliminarRol');
        const form = document.getElementById('formEliminarRol');
        
        form.action = `/admin/roles/${id}`;
        document.getElementById('nomRolEliminar').textContent = nombre;
        
        setTimeout(() => {
            modal.classList.remove('hidden');
            modal.querySelector('.modal-panel').classList.add('opacity-100', '-translate-y-0');
        }, 10);
    }

    function cerrarModalEliminar() {
        const modal = document.getElementById('modalEliminarRol');
        const panel = modal.querySelector('.modal-panel');
        
        panel.classList.remove('opacity-100', '-translate-y-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Cerrar modal al hacer click fuera
    document.getElementById('modalEliminarRol')?.addEventListener('click', (e) => {
        if (e.target.id === 'modalEliminarRol') cerrarModalEliminar();
    });
</script>
