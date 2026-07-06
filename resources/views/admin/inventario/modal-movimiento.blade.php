<div id="modalMovimiento" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-3 sm:p-4 transition-all duration-300">
    
    <div class="bg-[var(--card-color)] border border-zinc-200/60 dark:border-zinc-800/60 w-full max-w-md rounded-[1.5rem] sm:rounded-[2.5rem] shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)] dark:shadow-[0_0_50px_rgba(0,0,0,0.5)] overflow-hidden transform transition-all duration-500 scale-95 opacity-0 modo-crema:bg-white flex flex-col max-h-[95vh]" id="movimientoContainer">
        
        <div class="p-5 sm:p-8 pb-4 sm:pb-5 flex justify-between items-center border-b border-zinc-100 dark:border-zinc-900/50 modo-crema:border-zinc-100 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/15 flex items-center justify-center text-emerald-600 dark:text-emerald-400 border border-emerald-500/10 dark:border-emerald-500/20 shrink-0">
                    <i class="fas fa-exchange-alt text-base sm:text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-black text-[var(--text-color)] dark:text-zinc-100 tracking-tight uppercase m-0 leading-tight truncate max-w-[150px] sm:max-w-[200px]" id="movimientoNombreInsumo">Cargando...</h3>
                    <p class="text-[8px] sm:text-[9px] text-[var(--text-muted)] dark:text-zinc-500 font-bold uppercase tracking-[0.2em] mt-1">Registrar Entrada o Salida</p>
                </div>
            </div>
            
            <button type="button" onclick="closeModalMovimiento()" class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl flex items-center justify-center bg-zinc-100 dark:bg-white/5 text-zinc-400 dark:text-zinc-500 hover:text-rose-500 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all outline-none shrink-0">
                <i class="fas fa-times text-xs sm:text-sm"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.inventario.movimiento') }}" method="POST" class="p-5 sm:p-8 pt-4 sm:pt-6 space-y-4 sm:space-y-5 overflow-y-auto hide-scroll">
            @csrf
            
            <input type="hidden" name="insumo_id" id="movimientoInsumoId">

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[8px] sm:text-[9px] font-black text-[var(--text-muted)] dark:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-arrows-alt-v opacity-60"></i> Tipo de Movimiento
                </label>
                <div class="relative">
                    <select name="tipo" required
                        class="w-full h-11 sm:h-12 bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 rounded-xl sm:rounded-2xl px-4 sm:px-5 text-xs font-bold text-[var(--text-color)] dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all appearance-none cursor-pointer">
                        <option value="entrada" class="bg-white dark:bg-zinc-900 text-emerald-600 dark:text-emerald-400 font-bold">🟢 ENTRADA (Suma al stock)</option>
                        <option value="salida" class="bg-white dark:bg-zinc-900 text-rose-600 dark:text-rose-400 font-bold">🔴 SALIDA (Resta al stock)</option>
                        <option value="ajuste" class="bg-white dark:bg-zinc-900 text-orange-600 dark:text-orange-400 font-bold">🟠 MERMA / DESPERDICIO</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 sm:right-5 top-1/2 -translate-y-1/2 text-zinc-400 dark:text-zinc-500 pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[8px] sm:text-[9px] font-black text-[var(--text-muted)] dark:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-hashtag opacity-60"></i> Cantidad a mover
                    </label>
                    <input type="number" step="0.01" name="cantidad" required min="0.01"
                        class="w-full h-11 sm:h-12 bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 rounded-xl sm:rounded-2xl px-4 sm:px-5 text-xs font-black text-[var(--text-color)] dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-600"
                        placeholder="Ej: 50">
                </div>
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[8px] sm:text-[9px] font-black text-[var(--text-muted)] dark:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-comment-alt opacity-60"></i> Motivo o Justificación
                </label>
                <input type="text" name="motivo" required
                    class="w-full h-11 sm:h-12 bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 rounded-xl sm:rounded-2xl px-4 sm:px-5 text-xs font-bold text-[var(--text-color)] dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 outline-none transition-all placeholder:text-zinc-400 dark:placeholder:text-zinc-600"
                    placeholder="Ej: Factura #1234 / Se rompió">
            </div>

            <div class="flex items-center gap-3 sm:gap-4 pt-4 pb-2">
                <button type="button" onclick="closeModalMovimiento()" 
                    class="flex-1 h-11 sm:h-12 rounded-xl sm:rounded-2xl text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] dark:text-zinc-500 hover:text-[var(--text-color)] dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-white/5 transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit" 
                    class="flex-[1.5] h-11 sm:h-12 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl sm:rounded-2xl text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 transition-all active:scale-95 outline-none flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Registrar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModalMovimiento(id, nombre) {
        const modal = document.getElementById('modalMovimiento');
        const container = document.getElementById('movimientoContainer');
        
        document.getElementById('movimientoInsumoId').value = id;
        document.getElementById('movimientoNombreInsumo').innerText = nombre;

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModalMovimiento() {
        const modal = document.getElementById('modalMovimiento');
        const container = document.getElementById('movimientoContainer');
        
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.querySelector('form').reset();
        }, 300);
    }
</script>