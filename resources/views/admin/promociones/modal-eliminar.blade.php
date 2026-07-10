<div id="modalEliminar" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/80 backdrop-blur-sm transition-all duration-300 p-4">
    <div id="deleteContainer" class="relative !bg-white dark:!bg-[#1c1c1e] border !border-gray-200 dark:!border-white/5 rounded-[1.5rem] sm:rounded-[2rem] w-full max-w-md shadow-2xl scale-95 opacity-0 transition-all duration-300">

        {{-- Contenido del Aviso --}}
        <div class="p-6 sm:p-8 text-center space-y-3 sm:space-y-4">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl !bg-rose-50 dark:!bg-rose-500/10 border !border-rose-100 dark:!border-rose-500/20 flex items-center justify-center mx-auto shadow-inner">
                <i class="fas fa-trash-alt !text-rose-500 text-xl sm:text-2xl"></i>
            </div>
            <div class="space-y-2">
                <h2 class="text-lg sm:text-xl font-black !text-gray-900 dark:!text-white tracking-tight">¿Eliminar promoción?</h2>
                <p class="text-xs !text-gray-500 dark:!text-zinc-400 font-medium leading-relaxed max-w-xs mx-auto tracking-wide">
                    Vas a eliminar permanentemente la oferta <span id="delete_nombre_display" class="!text-rose-500 dark:!text-rose-400 font-bold"></span> del sistema. Esta acción no se puede revertir.
                </p>
            </div>
        </div>

        {{-- Formulario de Acción --}}
        <form id="formEliminar" method="POST" action="">
            @csrf
            @method('DELETE')
            
            <div class="flex flex-col-reverse sm:flex-row items-center gap-3 px-6 sm:px-8 pb-6 sm:pb-8">
                <button type="button" onclick="closeDeleteModal()"
                    class="w-full sm:flex-1 py-3.5 sm:py-4 !bg-gray-50 dark:!bg-white/5 hover:!bg-gray-200 dark:hover:!bg-white/10 active:scale-95 !text-gray-500 dark:!text-white/60 hover:!text-gray-900 dark:hover:!text-white font-black text-xs uppercase tracking-widest rounded-2xl transition-all border !border-gray-200 dark:!border-white/5 outline-none">
                    Cancelar
                </button>
                <button type="submit"
                    class="w-full sm:flex-1 py-3.5 sm:py-4 bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 active:scale-95 !text-white font-black text-xs uppercase tracking-widest rounded-2xl transition-all shadow-[0_8px_20px_rgba(244,63,94,0.2)] hover:shadow-[0_8px_25px_rgba(244,63,94,0.4)] outline-none">
                    Sí, eliminar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    /**
     * Abre el modal de eliminación inyectando el ID y el Nombre de la promoción de forma dinámica
     */
    function openDeleteModal(id, nombre) {
        const modal = document.getElementById('modalEliminar');
        const container = document.getElementById('deleteContainer');
        const form = document.getElementById('formEliminar');
        const txtNombre = document.getElementById('delete_nombre_display');

        // Configurar los datos dinámicos de la promoción
        form.action = `/promociones/${id}`;
        txtNombre.textContent = `"${nombre}"`;

        // Mostrar el fondo del modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Retraso de un microsegundo para que Tailwind procese la animación de entrada (FADE IN + SCALE)
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 20);
    }

    /**
     * Cierra el modal aplicando la animación inversa de salida
     */
    function closeDeleteModal() {
        const modal = document.getElementById('modalEliminar');
        const container = document.getElementById('deleteContainer');

        // Animación de salida (FADE OUT + SCALE DOWN)
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');

        // Esperar a que termine la transición (300ms) antes de ocultar por completo el contenedor principal
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }

    // Cerrar el modal de manera segura si se hace click fuera del contenedor principal
    document.getElementById('modalEliminar').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
</script>