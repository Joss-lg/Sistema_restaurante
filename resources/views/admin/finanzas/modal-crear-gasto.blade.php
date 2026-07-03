<div id="modalCrearGasto" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all duration-300">

    <div id="createGastoContainer" class="bg-zinc-950 modo-crema:bg-white border border-zinc-800 modo-crema:border-zinc-200 w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0">

        <div class="p-8 pb-4 flex justify-between items-center border-b border-zinc-800 modo-crema:border-zinc-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-600/10 flex items-center justify-center text-rose-500 border border-rose-500/20">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-zinc-100 modo-crema:text-zinc-900 tracking-tight uppercase">Nuevo Gasto</h3>
                    <p class="text-xs text-zinc-400 modo-crema:text-zinc-500 font-bold uppercase tracking-[0.2em]">Registrar egreso</p>
                </div>
            </div>
            <button onclick="closeCreateGastoModal()" class="w-9 h-9 rounded-xl flex items-center justify-center bg-white/5 modo-crema:bg-zinc-100 text-zinc-400 modo-crema:text-zinc-500 hover:text-rose-500 modo-crema:hover:text-rose-600 hover:bg-rose-500/10 modo-crema:hover:bg-rose-50 transition-all outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form action="{{ route('admin.gastos.store') }}" method="POST" class="p-8 pt-6 space-y-4">
            @csrf

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-pen opacity-40"></i> Concepto
                </label>
                <input type="text" name="concepto" required
                    class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-rose-600 modo-crema:focus:border-rose-500 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all placeholder:text-zinc-600 modo-crema:placeholder:text-zinc-400"
                    placeholder="Ej: Compra de tomates">
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-folder opacity-40"></i> Categoría
                </label>
                <div class="relative">
                    <select name="categoria" required
                        class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-rose-600 modo-crema:focus:border-rose-500 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all appearance-none cursor-pointer">
                        <option value="">Selecciona una categoría</option>
                        <option value="Compra Insumos">Compra de Insumos</option>
                        <option value="Servicios">Servicios</option>
                        <option value="Renta">Renta</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Otro">Otro</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-zinc-400 modo-crema:text-zinc-500 pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-dollar-sign opacity-40"></i> Monto
                </label>
                <input type="number" step="0.01" name="monto" required
                    class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-rose-600 modo-crema:focus:border-rose-500 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all placeholder:text-zinc-600 modo-crema:placeholder:text-zinc-400"
                    placeholder="0.00">
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-credit-card opacity-40"></i> Método de Pago
                </label>
                <div class="relative">
                    <select name="metodo_pago" required
                        class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-rose-600 modo-crema:focus:border-rose-500 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all appearance-none cursor-pointer">
                        <option value="">Selecciona método</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta">Tarjeta</option>
                        <option value="Transferencia">Transferencia</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-zinc-400 modo-crema:text-zinc-500 pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-circle-notch opacity-40"></i> Estado
                </label>
                <div class="relative">
                    <select name="estado" required
                        class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-rose-600 modo-crema:focus:border-rose-500 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all appearance-none cursor-pointer">
                        <option value="pendiente">Pendiente de Pago</option>
                        <option value="pagado">Pagado</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-zinc-400 modo-crema:text-zinc-500 pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-file-alt opacity-40"></i> Número de Documento
                </label>
                <input type="text" name="documento"
                    class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-rose-600 modo-crema:focus:border-rose-500 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all placeholder:text-zinc-600 modo-crema:placeholder:text-zinc-400"
                    placeholder="Ej: Factura #1234 (Opcional)">
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-comment-alt opacity-40"></i> Descripción Adicional
                </label>
                <textarea name="descripcion" rows="3"
                    class="w-full bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 py-3 text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-rose-600 modo-crema:focus:border-rose-500 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all placeholder:text-zinc-600 modo-crema:placeholder:text-zinc-400 resize-none"
                    placeholder="Detalles adicionales del gasto..."></textarea>
            </div>

            <div class="flex items-center gap-4 pt-4 pb-2">
                <button type="button" onclick="closeCreateGastoModal()"
                    class="flex-1 h-12 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 modo-crema:text-zinc-500 hover:text-white modo-crema:hover:text-zinc-900 hover:bg-zinc-800 modo-crema:hover:bg-zinc-100 transition-all outline-none">
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