<div id="modalMovimiento" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all duration-300">
    
    <!-- Contenedor dinámico principal con los colores de tu tema global -->
    <div class="bg-[var(--card-color)] border border-black/5 w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0 modo-crema:bg-white" id="movimientoContainer">
        
        <!-- ENCABEZADO DEL MODAL (Color Verde/Esmeralda indicando dinero/stock) -->
        <div class="p-8 pb-4 flex justify-between items-center border-b border-black/5 modo-crema:border-zinc-200">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 border border-emerald-500/20">
                    <i class="fas fa-exchange-alt text-xl"></i>
                </div>
                <div>
                    <!-- El Nombre cambia mágicamente con Javascript al darle clic a una fila -->
                    <h3 class="text-xl font-black text-[var(--text-color)] tracking-tighter uppercase" id="movimientoNombreInsumo">Cargando...</h3>
                    <p class="text-[9px] text-[var(--text-muted)] font-bold uppercase tracking-[0.2em]">Registrar Entrada o Salida</p>
                </div>
            </div>
            
            <!-- Botón Cerrar (X) -->
            <button type="button" onclick="closeModalMovimiento()" class="w-9 h-9 rounded-xl flex items-center justify-center bg-black/5 text-[var(--text-muted)] hover:text-rose-500 hover:bg-rose-500/10 transition-all outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        
        <!-- FORMULARIO DIRECTO AL CONTROLADOR -->
        <form action="{{ route('admin.inventario.movimiento') }}" method="POST" class="p-8 pt-6 space-y-5">
            @csrf
            
            <!-- 🔑 EL DATO MÁS IMPORTANTE: El ID del Insumo Oculto -->
            <!-- JavaScript rellena este input antes de mandar el formulario al servidor -->
            <input type="hidden" name="insumo_id" id="movimientoInsumoId">

            <!-- TIPO DE MOVIMIENTO -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-arrows-alt-v opacity-40"></i> Tipo de Movimiento
                </label>
                <div class="relative">
                    <select name="tipo" required
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-emerald-500 outline-none transition-all appearance-none cursor-pointer">
                        <option value="entrada" class="bg-[var(--card-color)] text-emerald-500">🟢 ENTRADA (Suma al stock)</option>
                        <option value="salida" class="bg-[var(--card-color)] text-rose-500">🔴 SALIDA (Resta al stock)</option>
                        <option value="ajuste" class="bg-[var(--card-color)] text-orange-500">🟠 MERMA / DESPERDICIO</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <!-- CANTIDAD -->
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2 col-span-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-hashtag opacity-40"></i> Cantidad a mover
                    </label>
                    <input type="number" step="0.01" name="cantidad" required min="0.01"
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-black text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all"
                        placeholder="Ej: 50">
                </div>
            </div>

            <!-- MOTIVO / AUDITORÍA -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-comment-alt opacity-40"></i> Motivo o Justificación
                </label>
                <input type="text" name="motivo" required
                    class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all placeholder:text-[var(--text-muted)]"
                    placeholder="Ej: Factura #1234 / Se rompió en cocina">
            </div>

            <!-- BOTONES -->
            <div class="flex items-center gap-4 pt-4 pb-2">
                <button type="button" onclick="closeModalMovimiento()" 
                    class="flex-1 h-12 rounded-xl text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] hover:text-[var(--text-color)] hover:bg-black/5 transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit" 
                    class="flex-[1.5] h-12 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/20 transition-all active:scale-95 outline-none flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Registrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================================= -->
<!-- SCRIPT LOCAL: CONTROL DE ANIMACIONES E INYECCIÓN DE DATOS             -->
<!-- ======================================================================= -->
<script>
    // Esta función es llamada desde el botón verde (<i class="fas fa-exchange-alt"></i>) de tu tabla principal.
    function openModalMovimiento(id, nombre) {
        const modal = document.getElementById('modalMovimiento');
        const container = document.getElementById('movimientoContainer');
        
        // 1. Inyectamos los datos del producto seleccionado en los inputs del modal
        document.getElementById('movimientoInsumoId').value = id;
        document.getElementById('movimientoNombreInsumo').innerText = nombre;

        // 2. Animación de entrada suave
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    // Animación de salida al presionar "Cancelar" o la "X"
    function closeModalMovimiento() {
        const modal = document.getElementById('modalMovimiento');
        const container = document.getElementById('movimientoContainer');
        
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            
            // Limpiamos el formulario para la próxima vez que se abra
            modal.querySelector('form').reset();
        }, 300);
    }
</script>