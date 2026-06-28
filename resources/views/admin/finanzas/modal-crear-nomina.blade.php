<div id="modalCrearNomina" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all duration-300">
    
   <div class="bg-[var(--card-color)] border border-[var(--border-color)] w-full max-w-lg rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0" id="createNominaContainer">
    
        <div class="p-8 pb-5 flex justify-between items-center border-b border-[var(--border-color)] bg-black/[0.01]">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-600/10 flex items-center justify-center text-purple-600 border border-purple-600/20 shadow-sm">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-[var(--text-color)] tracking-tighter uppercase">Pago de Nómina</h3>
                    <p class="text-[9px] text-[var(--text-muted)] font-bold uppercase tracking-[0.2em]">Registrar pago a empleado</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateNominaModal()" class="w-9 h-9 rounded-xl flex items-center justify-center bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-muted)] hover:text-purple-500 hover:bg-purple-500/10 transition-all outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.pagos-nomina.store') }}" method="POST" class="p-8 pt-6 space-y-5 max-h-[75vh] overflow-y-auto scrollbar-hide">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-user opacity-40"></i> Empleado
                    </label>
                    <div class="relative">
                        <select name="user_id" required id="empleadoSelect" onchange="actualizarSueldo()"
                            class="w-full h-11 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-purple-600 outline-none transition-all appearance-none cursor-pointer">
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

                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-calendar opacity-40"></i> Período
                    </label>
                    <input type="text" name="periodo" required
                        class="w-full h-11 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-purple-600 focus:ring-4 focus:ring-purple-600/10 outline-none transition-all placeholder:text-[var(--text-muted)]"
                        placeholder="Ej: 1-15 Mayo 2026">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 bg-black/[0.02] p-3 rounded-2xl border border-[var(--border-color)]">
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1 text-[8px] font-black text-[var(--text-muted)] uppercase tracking-wider ml-1">
                        Sueldo Base
                    </label>
                    <input type="number" step="0.01" name="sueldo_base" required id="sueldoBase"
                        class="w-full h-10 bg-[var(--card-color)] border border-[var(--border-color)] rounded-lg px-3 text-xs font-bold text-[var(--text-color)] focus:border-purple-600 focus:ring-2 focus:ring-purple-600/10 outline-none transition-all text-center"
                        placeholder="0.00">
                </div>

                <div class="space-y-1.5">
                    <label class="flex items-center gap-1 text-[8px] font-black text-emerald-600 uppercase tracking-wider ml-1 justify-center">
                        + Bonos
                    </label>
                    <input type="number" step="0.01" name="bonos" value="0"
                        class="w-full h-10 bg-[var(--card-color)] border border-[var(--border-color)] rounded-lg px-3 text-xs font-bold text-emerald-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 outline-none transition-all text-center"
                        placeholder="0.00" oninput="calcularMonto()">
                </div>

                <div class="space-y-1.5">
                    <label class="flex items-center gap-1 text-[8px] font-black text-rose-600 uppercase tracking-wider ml-1 justify-center">
                        - Descuentos
                    </label>
                    <input type="number" step="0.01" name="deducciones" value="0"
                        class="w-full h-10 bg-[var(--card-color)] border border-[var(--border-color)] rounded-lg px-3 text-xs font-bold text-rose-600 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/10 outline-none transition-all text-center"
                        placeholder="0.00" oninput="calcularMonto()">
                </div>
            </div>

            <div class="bg-purple-500/5 border border-purple-500/20 rounded-2xl p-4 flex items-center justify-between shadow-[0_4px_20px_rgba(147,51,234,0.03)]">
                <div>
                    <label class="flex items-center gap-2 text-[9px] font-black text-purple-600 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-calculator opacity-60"></i> Monto Neto a Pagar
                    </label>
                    <p class="text-[10px] text-[var(--text-muted)] font-medium ml-1 mt-0.5">Calculado en tiempo real</p>
                </div>
                <div class="text-3xl font-black text-purple-600 tracking-tight">
                    $ <span id="montoNeto">0.00</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-credit-card opacity-40"></i> Método de Pago
                    </label>
                    <div class="relative">
                        <select name="metodo_pago" required
                            class="w-full h-11 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-purple-600 outline-none transition-all appearance-none cursor-pointer">
                            <option value="">Selecciona método</option>
                            <option value="Efectivo" class="bg-[var(--card-color)]">Efectivo</option>
                            <option value="Tarjeta" class="bg-[var(--card-color)]">Tarjeta</option>
                            <option value="Transferencia" class="bg-[var(--card-color)]">Transferencia</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none text-[10px]"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-circle-notch opacity-40"></i> Estado
                    </label>
                    <div class="relative">
                        <select name="estado" required
                            class="w-full h-11 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-purple-600 outline-none transition-all appearance-none cursor-pointer">
                            <option value="pendiente" class="bg-[var(--card-color)]">Pendiente de Pago</option>
                            <option value="pagado" class="bg-[var(--card-color)]">Pagado Ahora</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-[var(--text-muted)] pointer-events-none text-[10px]"></i>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-comment-alt opacity-40"></i> Observaciones
                </label>
                <textarea name="observaciones" rows="2"
                    class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 py-3 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-purple-600 focus:ring-4 focus:ring-purple-600/10 outline-none transition-all placeholder:text-[var(--text-muted)] resize-none"
                    placeholder="Notas o comentarios adicionales sobre este pago..."></textarea>
            </div>

            <div class="flex items-center gap-4 pt-5 pb-1 border-t border-[var(--border-color)]">
                <button type="button" onclick="closeCreateNominaModal()" 
                    class="flex-1 h-12 rounded-xl text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] hover:text-[var(--text-color)] hover:bg-black/5 transition-all outline-none">
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

    // NUEVO: Escuchar los cambios en vivo cuando el usuario escribe directamente
    document.addEventListener('DOMContentLoaded', function() {
        const inputSueldo = document.getElementById('sueldoBase');
        const inputBonos = document.querySelector('input[name="bonos"]');
        const inputDeducciones = document.querySelector('input[name="deducciones"]');

        // Escucha si el usuario altera el sueldo base manualmente
        if (inputSueldo) {
            inputSueldo.addEventListener('input', calcularMonto);
        }

        // Por si acaso tus inputs de bonos/deducciones no tenían el atributo oninput en el HTML
        if (inputBonos) inputBonos.addEventListener('input', calcularMonto);
        if (inputDeducciones) inputDeducciones.addEventListener('input', calcularMonto);
    });
</script>
