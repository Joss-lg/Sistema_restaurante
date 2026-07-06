{{-- resources/views/admin/roles/modal-eliminar.blade.php --}}
<div id="modalEliminarRol" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-all duration-300">
    
    {{-- Capa de cierre al hacer clic fuera --}}
    <div class="absolute inset-0" onclick="cerrarModalEliminar()"></div>

    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-3xl shadow-2xl max-w-md w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300 dynamic-modal-content z-10">
        
        {{-- Header con aviso de peligro --}}
        <div class="p-6 sm:p-8 border-b border-rose-100 dark:border-rose-900/20 bg-rose-50/50 dark:bg-rose-500/5">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center flex-shrink-0 text-rose-600 dark:text-rose-400">
                    <i class="fas fa-exclamation-triangle text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">¿Eliminar Puesto?</h2>
                    <p class="text-xs text-rose-600 dark:text-rose-400 mt-1 font-semibold">Esta acción es irreversible</p>
                </div>
            </div>
        </div>

        {{-- Cuerpo --}}
        <div class="p-6 sm:p-8 space-y-5">
            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                Estás a punto de eliminar el puesto 
                <span class="font-bold text-gray-900 dark:text-white px-2 py-0.5 rounded bg-gray-100 dark:bg-zinc-800" id="nombreRolEliminar"></span>.
            </p>
            
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200/50 dark:border-amber-500/20">
                <p class="text-xs text-amber-700 dark:text-amber-400 font-semibold flex items-start gap-2 leading-relaxed">
                    <i class="fas fa-info-circle mt-0.5"></i>
                    Asegúrate de que no haya empleados activos asignados a este nivel antes de proceder.
                </p>
            </div>
        </div>

        {{-- Footer con acciones --}}
        <div class="p-6 sm:p-8 border-t border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-950 flex justify-end gap-3">
            <button type="button" onclick="cerrarModalEliminar()" 
                    class="px-5 py-2.5 rounded-full text-xs font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-zinc-800 transition-colors">
                Cancelar
            </button>
            
            <form id="formEliminarRol" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-6 py-2.5 bg-rose-600 text-white rounded-full text-xs font-bold hover:bg-rose-700 transition-all shadow-lg shadow-rose-500/20 flex items-center gap-2 active:scale-[0.98]">
                    <i class="fas fa-trash-alt"></i> Confirmar Eliminación
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    window.abrirModalEliminar = function(btn) {
        const modal = document.getElementById('modalEliminarRol');
        const content = modal.querySelector('.dynamic-modal-content');
        
        // Asignar acción al formulario y nombre al span
        const form = document.getElementById('formEliminarRol');
        form.action = `{{ url('roles') }}/${btn.getAttribute('data-id')}`;
        document.getElementById('nombreRolEliminar').innerText = btn.getAttribute('data-nombre');
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        requestAnimationFrame(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        });
    }

    window.cerrarModalEliminar = function() {
        const modal = document.getElementById('modalEliminarRol');
        const content = modal.querySelector('.dynamic-modal-content');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }
</script>