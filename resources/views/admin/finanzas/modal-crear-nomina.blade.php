<div id="modalCrearNomina" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all duration-300">

   <div id="createNominaContainer" class="bg-zinc-950 modo-crema:bg-white border border-zinc-800 modo-crema:border-zinc-200 w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0">

        <div class="p-8 pb-5 flex justify-between items-center border-b border-zinc-800 modo-crema:border-zinc-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-600/10 flex items-center justify-center text-purple-600 border border-purple-600/20 shadow-sm">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-zinc-100 modo-crema:text-zinc-900 tracking-tighter uppercase">Pago de Nómina</h3>
                    <p class="text-[9px] text-zinc-400 modo-crema:text-zinc-500 font-bold uppercase tracking-[0.2em]">Registrar pago a empleado</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateNominaModal()" class="w-9 h-9 rounded-xl flex items-center justify-center bg-white/5 modo-crema:bg-zinc-100 text-zinc-400 modo-crema:text-zinc-500 hover:text-purple-500 modo-crema:hover:text-purple-600 hover:bg-purple-500/10 modo-crema:hover:bg-purple-50 transition-all outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.pagos-nomina.store') }}" method="POST" class="p-8 pt-6 space-y-5 max-h-[75vh] overflow-y-auto">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-user opacity-40"></i> Empleado
                    </label>
                    <div class="relative">
                        <select name="user_id" required id="empleadoSelect" onchange="actualizarSueldo()"
                            class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-purple-600 modo-crema:focus:border-purple-500 outline-none transition-all appearance-none cursor-pointer">
                            <option value="">Selecciona un empleado</option>
                            @foreach($empleados ?? [] as $empleado)
                                <option value="{{ $empleado->id }}" data-sueldo="{{ $empleado->sueldo_base ?? 0 }}">
                                    {{ $empleado->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-zinc-400 modo-crema:text-zinc-500 pointer-events-none text-[10px]"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-calendar opacity-40"></i> Período
                    </label>
                    <input type="text" name="periodo" required
                        class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-purple-600 modo-crema:focus:border-purple-500 focus:ring-4 focus:ring-purple-600/10 outline-none transition-all placeholder:text-zinc-600 modo-crema:placeholder:text-zinc-400"
                        placeholder="Ej: 1-15 Mayo 2026">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 bg-zinc-900/50 modo-crema:bg-zinc-50/80 p-3 rounded-2xl border border-transparent modo-crema:border-zinc-200/60">
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1 text-[8px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-wider ml-1">
                        Sueldo Base
                    </label>
                    <input type="number" step="0.01" name="sueldo_base" required id="sueldoBase"
                        class="w-full h-10 bg-zinc-950 modo-crema:bg-white border border-transparent modo-crema:border-zinc-200/60 rounded-lg px-3 text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:border-purple-600 modo-crema:focus:border-purple-500 focus:ring-2 focus:ring-purple-600/10 outline-none transition-all text-center"
                        placeholder="0.00">
                </div>

                <div class="space-y-1.5">
                    <label class="flex items-center gap-1 text-[8px] font-black text-emerald-600 uppercase tracking-wider ml-1 justify-center">
                        + Bonos
                    </label>
                    <input type="number" step="0.01" name="bonos" value="0"
                        class="w-full h-10 bg-zinc-950 modo-crema:bg-white border border-transparent modo-crema:border-zinc-200/60 rounded-lg px-3 text-xs font-bold text-emerald-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 outline-none transition-all text-center"
                        placeholder="0.00" oninput="calcularMonto()">
                </div>

                <div class="space-y-1.5">
                    <label class="flex items-center gap-1 text-[8px] font-black text-rose-600 uppercase tracking-wider ml-1 justify-center">
                        - Descuentos
                    </label>
                    <input type="number" step="0.01" name="deducciones" value="0"
                        class="w-full h-10 bg-zinc-950 modo-crema:bg-white border border-transparent modo-crema:border-zinc-200/60 rounded-lg px-3 text-xs font-bold text-rose-600 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/10 outline-none transition-all text-center"
                        placeholder="0.00" oninput="calcularMonto()">
                </div>
            </div>

            <div class="bg-purple-500/5 border border-purple-500/20 rounded-2xl p-4 flex items-center justify-between shadow-[0_4px_20px_rgba(147,51,234,0.03)]">
                <div>
                    <label class="flex items-center gap-2 text-[9px] font-black text-purple-600 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-calculator opacity-60"></i> Monto Neto a Pagar
                    </label>
                    <p class="text-[10px] text-zinc-400 modo-crema:text-zinc-500 font-medium ml-1 mt-0.5">Calculado en tiempo real</p>
                </div>
                <div class="text-3xl font-black text-purple-600 tracking-tight">
                    $ <span id="montoNeto">0.00</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-credit-card opacity-40"></i> Método de Pago
                    </label>
                    <div class="relative">
                        <select name="metodo_pago" required
                            class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-purple-600 modo-crema:focus:border-purple-500 outline-none transition-all appearance-none cursor-pointer">
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
                            class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-purple-600 modo-crema:focus:border-purple-500 outline-none transition-all appearance-none cursor-pointer">
                            <option value="pendiente">Pendiente de Pago</option>
                            <option value="pagado">Pagado Ahora</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-zinc-400 modo-crema:text-zinc-500 pointer-events-none text-[10px]"></i>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-comment-alt opacity-40"></i> Observaciones
                </label>
                <textarea name="observaciones" rows="2"
                    class="w-full bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 py-3 text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-purple-600 modo-crema:focus:border-purple-500 focus:ring-4 focus:ring-purple-600/10 outline-none transition-all placeholder:text-zinc-600 modo-crema:placeholder:text-zinc-400 resize-none"
                    placeholder="Notas o comentarios adicionales sobre este pago..."></textarea>
            </div>

            <div class="flex items-center gap-4 pt-5 pb-1 border-t border-zinc-800 modo-crema:border-zinc-100">
                <button type="button" onclick="closeCreateNominaModal()"
                    class="flex-1 h-12 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 modo-crema:text-zinc-500 hover:text-white modo-crema:hover:text-zinc-900 hover:bg-zinc-800 modo-crema:hover:bg-zinc-100 transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-[1.4] h-12 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-purple-600/20 transition-all active:scale-95 outline-none">
                    Guardar Nómina
                </button>
            </div>
        </form>
    </div>
</div>