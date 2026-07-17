{{-- resources/views/admin/roles/modal-editar.blade.php --}}
<style>
    /* Efecto para que el teclado no tape el modal en pantallas grandes */
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalEditarRol {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        body.teclado-virtual-abierto #modalEditarRol .dynamic-modal-content {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important; 
        }
    }
</style>

<div id="modalEditarRol" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-all duration-300">
    
    {{-- Capa de cierre al hacer clic fuera --}}
    <div class="absolute inset-0" onclick="cerrarModalEditar()"></div>

    {{-- Contenido del Modal --}}
    <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-2xl sm:rounded-3xl shadow-2xl max-w-md w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300 dynamic-modal-content z-10 max-h-[92vh] flex flex-col">
        
        {{-- Header --}}
        <div class="p-5 sm:p-8 border-b border-gray-100 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-900/50 flex justify-between items-center shrink-0">
            <div>
                <h2 class="text-lg sm:text-xl font-black text-gray-900 dark:text-white tracking-tight">Editar Puesto</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Actualiza los datos del puesto en el sistema</p>
            </div>
            <button type="button" onclick="cerrarModalEditar()" 
                    class="w-9 h-9 flex items-center justify-center shrink-0 rounded-full bg-gray-100 dark:bg-zinc-800 text-gray-500 hover:text-gray-900 dark:hover:text-white active:scale-95 transition-colors outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Formulario --}}
        <form id="formEditarRol" method="POST" class="p-5 sm:p-8 space-y-5 overflow-y-auto">
            @csrf
            @method('PUT')

            <div>
                <label for="editNombre" class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 block">Nombre del Puesto</label>
                {{-- AQUÍ SE AGREGÓ data-teclado="texto" --}}
                <input type="text" id="editNombre" name="nombre" required data-teclado="texto"
                       class="w-full h-12 bg-gray-50 dark:bg-zinc-950 border border-gray-200 dark:border-zinc-800 rounded-xl px-4 text-base font-medium text-gray-900 dark:text-white outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
            </div>

            <div class="pt-6 mt-2 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="button" onclick="cerrarModalEditar()" 
                        class="w-full sm:w-auto px-5 py-3 sm:py-2.5 rounded-full text-xs font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-800 active:scale-95 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto px-6 py-3 sm:py-2.5 bg-blue-600 dark:bg-blue-500 text-white rounded-full text-xs font-bold hover:bg-blue-700 dark:hover:bg-blue-600 transition-all shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2 active:scale-[0.98]">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Se asegura de que la animación sea consistente con el modal de creación
    // Estas funciones ya están en el stack de scripts, pero asegúrate de que el selector sea este:
    window.abrirModalEditar = function(btn) {
        const modal = document.getElementById('modalEditarRol');
        const content = modal.querySelector('.dynamic-modal-content');
        
        // Asignar acción al form
        const form = document.getElementById('formEditarRol');
        form.action = `{{ url('roles') }}/${btn.getAttribute('data-id')}`;
        
        // Asignar valor
        document.getElementById('editNombre').value = btn.getAttribute('data-nombre');
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        requestAnimationFrame(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        });
    }

    window.cerrarModalEditar = function() {
        const modal = document.getElementById('modalEditarRol');
        const content = modal.querySelector('.dynamic-modal-content');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }
</script>