<div id="modalCrearGasto" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all duration-300">
    
    <!-- Contenedor dinámico -->
    <div class="bg-[var(--card-color)] border border-black/5 w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0" id="createGastoContainer">
        
        <div class="p-8 pb-4 flex justify-between items-center border-b border-black/5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-600/10 flex items-center justify-center text-rose-600 border border-rose-600/20">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-[var(--text-color)] tracking-tighter uppercase">Nuevo Gasto</h3>
                    <p class="text-[9px] text-[var(--text-muted)] font-bold uppercase tracking-[0.2em]">Registrar egreso</p>
                </div>
            </div>
            <button onclick="closeCreateGastoModal()" class="w-9 h-9 rounded-xl flex items-center justify-center bg-black/5 text-[var(--text-muted)] hover:text-rose-500 hover:bg-rose-500/10 transition-all outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.gastos.store') }}" method="POST" class="p-8 pt-6 space-y-4">
            @csrf

            <!-- CONCEPTO -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-pen opacity-40"></i> Concepto
                </label>
                <input type="text" name="concepto" required
                    class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-rose-600 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all placeholder:text-[var(--text-muted)]"
                    placeholder="Ej: Compra de tomates">
            </div>

            <!-- CATEGORÍA -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-folder opacity-40"></i> Categoría
                </label>
                <div class="relative">
                    <select name="categoria" required
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-rose-600 outline-none transition-all appearance-none cursor-pointer">
                        <option value="">Selecciona una categoría</option>
                        <option value="Compra Insumos" class="bg-[var(--card-color)]">Compra de Insumos</option>
                        <option value="Servicios" class="bg-[var(--card-color)]">Servicios</option>
                        <option value="Renta" class="bg-[var(--card-color)]">Renta</option>
                        <option value="Mantenimiento" class="bg-[var(--card-color)]">Mantenimiento</option>
                        <option value="Otro" class="bg-[var(--card-color)]">Otro</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <!-- MONTO -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-dollar-sign opacity-40"></i> Monto
                </label>
                <input type="number" step="0.01" name="monto" required
                    class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-rose-600 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all"
                    placeholder="0.00">
            </div>

            <!-- MÉTODO DE PAGO -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-credit-card opacity-40"></i> Método de Pago
                </label>
                <div class="relative">
                    <select name="metodo_pago" required
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-rose-600 outline-none transition-all appearance-none cursor-pointer">
                        <option value="">Selecciona método</option>
                        <option value="Efectivo" class="bg-[var(--card-color)]">Efectivo</option>
                        <option value="Tarjeta" class="bg-[var(--card-color)]">Tarjeta</option>
                        <option value="Transferencia" class="bg-[var(--card-color)]">Transferencia</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <!-- ESTADO -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-circle-notch opacity-40"></i> Estado
                </label>
                <div class="relative">
                    <select name="estado" required
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-rose-600 outline-none transition-all appearance-none cursor-pointer">
                        <option value="pendiente" class="bg-[var(--card-color)]">Pendiente de Pago</option>
                        <option value="pagado" class="bg-[var(--card-color)]">Pagado</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <!-- DOCUMENTO (OPCIONAL) -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-file-alt opacity-40"></i> Número de Documento
                </label>
                <input type="text" name="documento"
                    class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-rose-600 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all placeholder:text-[var(--text-muted)]"
                    placeholder="Ej: Factura #1234 (Opcional)">
            </div>

            <!-- DESCRIPCIÓN (OPCIONAL) -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-comment-alt opacity-40"></i> Descripción Adicional
                </label>
                <textarea name="descripcion" rows="3"
                    class="w-full bg-black/5 border border-transparent rounded-xl px-5 py-3 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-rose-600 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all placeholder:text-[var(--text-muted)] resize-none"
                    placeholder="Detalles adicionales del gasto..."></textarea>
            </div>

            <!-- BOTONES -->
            <div class="flex items-center gap-4 pt-4 pb-2">
                <button type="button" onclick="closeCreateGastoModal()" 
                    class="flex-1 h-12 rounded-xl text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] hover:text-[var(--text-color)] hover:bg-black/5 transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit" 
                    class="flex-[1.5] h-12 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-rose-600/20 transition-all active:scale-95 outline-none">
                    Guardar Gasto
                </button>
            </div>
        </form>
    </div>
</div>
