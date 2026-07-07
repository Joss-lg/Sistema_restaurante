{{-- MODAL CONFIRMACIÓN ELIMINAR PLATILLO --}}
<div id="modal-eliminar-alimento" class="fixed inset-0 z-[100] hidden opacity-0 transition-all duration-300 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/60 dark:bg-black/80" onclick="cerrarModalEliminar()"></div>
    <div class="relative bg-white/95 dark:bg-zinc-800/95 backdrop-blur-xl border border-zinc-200 dark:border-zinc-700 w-full max-w-md rounded-[2rem] shadow-2xl transform opacity-0 scale-95 transition-all duration-300" id="modal-eliminar-panel">
        <div class="p-8 sm:p-10 text-center">
            {{-- Ícono de advertencia --}}
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-500"></i>
                </div>
            </div>

            {{-- Título y descripción --}}
            <h3 class="text-2xl font-black text-zinc-900 dark:text-white mb-2 tracking-tight">¿Eliminar Platillo?</h3>
            <p class="text-zinc-500 dark:text-zinc-400 mb-8">
                Estás a punto de eliminar a
                <span class="font-bold text-zinc-900 dark:text-white block mt-2" id="nombre-platillo-eliminar">Platillo</span>
            </p>

            {{-- Botones de acción --}}
            <div class="flex gap-4">
                <button type="button" onclick="cerrarModalEliminar()" class="flex-1 px-6 py-3 bg-zinc-100 dark:bg-zinc-700/50 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-500 dark:text-zinc-400 font-black rounded-xl transition">
                    CANCELAR
                </button>
                <button type="button" onclick="confirmarEliminacion()" class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-500 text-white font-black rounded-xl transition shadow-lg shadow-red-900/20 dark:shadow-red-900/30">
                    <i class="fas fa-trash-alt mr-2"></i> SÍ, ELIMINAR
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Variable global para almacenar el ID del producto a eliminar
    let idProductoAEliminar = null;

    // Abrir modal de eliminación
    function abrirModalEliminar(id, nombreProducto) {
        idProductoAEliminar = id;
        document.getElementById('nombre-platillo-eliminar').textContent = nombreProducto;

        const modal = document.getElementById('modal-eliminar-alimento');
        const panel = document.getElementById('modal-eliminar-panel');

        modal.classList.remove('hidden');
        modal.classList.add('opacity-100');

        setTimeout(() => {
            panel.classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    // Cerrar modal de eliminación
    function cerrarModalEliminar() {
        const modal = document.getElementById('modal-eliminar-alimento');
        const panel = document.getElementById('modal-eliminar-panel');

        panel.classList.remove('opacity-100', 'scale-100');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('opacity-100');
            idProductoAEliminar = null;
        }, 300);
    }

    // Confirmar y ejecutar eliminación
    function confirmarEliminacion() {
        if (!idProductoAEliminar) return;

        fetch(`/alimentos/api/${idProductoAEliminar}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la solicitud');
            return response.json();
        })
        .then(resultado => {
            cerrarModalEliminar();
            cargarProductos();
            cargarEstadisticas();
            mostrarNotificacion(resultado.message, 'success');
        })
        .catch(error => {
            mostrarNotificacion('Error al eliminar el platillo', 'error');
            console.error(error);
        });
    }
</script>