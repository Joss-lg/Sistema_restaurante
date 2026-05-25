<div id="modalCrearNomina" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all duration-300">
    
    <!-- Contenedor dinámico -->
    <div class="bg-[var(--card-color)] border border-black/5 w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0" id="createNominaContainer">
        
        <div class="p-8 pb-4 flex justify-between items-center border-b border-black/5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-600/10 flex items-center justify-center text-purple-600 border border-purple-600/20">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-[var(--text-color)] tracking-tighter uppercase">Pago de Nómina</h3>
                    <p class="text-[9px] text-[var(--text-muted)] font-bold uppercase tracking-[0.2em]">Registrar pago a empleado</p>
                </div>
            </div>
            <button onclick="closeCreateNominaModal()" class="w-9 h-9 rounded-xl flex items-center justify-center bg-black/5 text-[var(--text-muted)] hover:text-purple-500 hover:bg-purple-500/10 transition-all outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.pagos-nomina.store') }}" method="POST" class="p-8 pt-6 space-y-4 max-h-[80vh] overflow-y-auto">
            @csrf

            <!-- EMPLEADO -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-user opacity-40"></i> Empleado
                </label>
                <div class="relative">
                    <select name="user_id" required id="empleadoSelect" onchange="actualizarSueldo()"
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-purple-600 outline-none transition-all appearance-none cursor-pointer">
                        <option value="">Selecciona un empleado</option>
                        @foreach($empleados ?? [] as $empleado)
                            <option value="{{ $empleado->id }}" data-sueldo="{{ $empleado->sueldo_base ?? 0 }}" class="bg-[var(--card-color)]">
                                {{ $empleado->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <!-- PERÍODO -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-calendar opacity-40"></i> Período
                </label>
                <input type="text" name="periodo" required
                    class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-purple-600 focus:ring-4 focus:ring-purple-600/10 outline-none transition-all placeholder:text-[var(--text-muted)]"
                    placeholder="Ej: 1-15 Mayo 2026">
            </div>

            <!-- SUELDO BASE (LECTURA) -->
            <div class="grid grid-cols-3 gap-3">
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-wallet opacity-40"></i> Sueldo Base
                    </label>
                    <input type="number" step="0.01" name="sueldo_base" required id="sueldoBase"
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-purple-600 focus:ring-4 focus:ring-purple-600/10 outline-none transition-all"
                        placeholder="0.00" readonly>
                </div>

                <!-- BONOS -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-plus-circle opacity-40"></i> Bonos
                    </label>
                    <input type="number" step="0.01" name="bonos" value="0"
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-purple-600 focus:ring-4 focus:ring-purple-600/10 outline-none transition-all"
                        placeholder="0.00" onchange="calcularMonto()">
                </div>

                <!-- DEDUCCIONES -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-minus-circle opacity-40"></i> Deducciones
                    </label>
                    <input type="number" step="0.01" name="deducciones" value="0"
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-purple-600 focus:ring-4 focus:ring-purple-600/10 outline-none transition-all"
                        placeholder="0.00" onchange="calcularMonto()">
                </div>
            </div>

            <!-- MONTO NETO (LECTURA) -->
            <div class="bg-purple-500/10 border border-purple-500/20 rounded-xl p-4">
                <label class="flex items-center gap-2 text-[9px] font-black text-purple-600 uppercase tracking-[0.2em] ml-1 mb-2">
                    <i class="fas fa-calculator opacity-40"></i> Monto Neto a Pagar
                </label>
                <div class="text-3xl font-black text-purple-600">
                    S/ <span id="montoNeto">0.00</span>
                </div>
            </div>

            <!-- MÉTODO DE PAGO -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-credit-card opacity-40"></i> Método de Pago
                </label>
                <div class="relative">
                    <select name="metodo_pago" required
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-purple-600 outline-none transition-all appearance-none cursor-pointer">
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
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-purple-600 outline-none transition-all appearance-none cursor-pointer">
                        <option value="pendiente" class="bg-[var(--card-color)]">Pendiente de Pago</option>
                        <option value="pagado" class="bg-[var(--card-color)]">Pagado Ahora</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <!-- OBSERVACIONES (OPCIONAL) -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-comment-alt opacity-40"></i> Observaciones
                </label>
                <textarea name="observaciones" rows="2"
                    class="w-full bg-black/5 border border-transparent rounded-xl px-5 py-3 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-purple-600 focus:ring-4 focus:ring-purple-600/10 outline-none transition-all placeholder:text-[var(--text-muted)] resize-none"
                    placeholder="Notas adicionales..."></textarea>
            </div>

            <!-- BOTONES -->
            <div class="flex items-center gap-4 pt-4 pb-2 border-t border-black/5">
                <button type="button" onclick="closeCreateNominaModal()" 
                    class="flex-1 h-12 rounded-xl text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] hover:text-[var(--text-color)] hover:bg-black/5 transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit" 
                    class="flex-[1.5] h-12 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-purple-600/20 transition-all active:scale-95 outline-none">
                    Guardar Nómina
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function actualizarSueldo() {
        const select = document.getElementById('empleadoSelect');
        const option = select.options[select.selectedIndex];
        const sueldo = option.dataset.sueldo || 0;
        
        document.getElementById('sueldoBase').value = parseFloat(sueldo).toFixed(2);
        calcularMonto();
    }

    function calcularMonto() {
        const sueldo = parseFloat(document.getElementById('sueldoBase').value) || 0;
        const bonos = parseFloat(document.querySelector('input[name="bonos"]').value) || 0;
        const deducciones = parseFloat(document.querySelector('input[name="deducciones"]').value) || 0;
        
        const monto = sueldo + bonos - deducciones;
        document.getElementById('montoNeto').textContent = monto.toFixed(2);
    }
</script>
