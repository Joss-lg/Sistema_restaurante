{{-- Estilos para manejo de teclado virtual en PC --}}
<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalCrearNomina {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        
        body.teclado-virtual-abierto #createNominaContainer {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important; 
        }
    }
</style>

<div id="modalCrearNomina" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/90 backdrop-blur-md p-3 sm:p-4 transition-all duration-300">
    <div id="createNominaContainer" class="bg-zinc-950 modo-crema:bg-white border border-zinc-800 modo-crema:border-zinc-200 w-full max-w-lg rounded-2xl sm:rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0 flex flex-col max-h-[92dvh]">

        <div class="p-5 sm:p-8 pb-4 sm:pb-5 flex justify-between items-center border-b border-zinc-800 modo-crema:border-zinc-100 flex-shrink-0 gap-3">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-purple-600/10 flex items-center justify-center text-purple-600 border border-purple-600/20 shadow-sm shrink-0">
                    <i class="fas fa-users text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-black text-zinc-100 modo-crema:text-zinc-900 tracking-tighter uppercase truncate">Pago de Nómina</h3>
                    <p class="text-[8px] sm:text-[9px] text-zinc-400 modo-crema:text-zinc-500 font-bold uppercase tracking-[0.2em]">Registrar pago a empleado</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateNominaModal()" class="w-9 h-9 rounded-xl flex items-center justify-center bg-white/5 modo-crema:bg-zinc-100 text-zinc-400 modo-crema:text-zinc-500 hover:text-purple-500 modo-crema:hover:text-purple-600 hover:bg-purple-500/10 modo-crema:hover:bg-purple-50 transition-all outline-none flex-shrink-0">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.pagos-nomina.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
            @csrf
            <div class="p-5 sm:p-8 pt-5 sm:pt-6 space-y-4 sm:space-y-5 overflow-y-auto flex-1 overscroll-contain" style="-webkit-overflow-scrolling: touch;">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                            <i class="fas fa-user opacity-40"></i> Empleado
                        </label>
                        <select name="user_id" required id="empleadoSelect" onchange="actualizarSueldo()"
                            class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-base sm:text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 outline-none transition-all appearance-none cursor-pointer">
                            <option value="">Selecciona un empleado</option>
                            @foreach($empleados ?? [] as $empleado)
                                <option value="{{ $empleado->id }}" data-sueldo="{{ $empleado->sueldo_base ?? 0 }}">
                                    {{ $empleado->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                            <i class="fas fa-calendar opacity-40"></i> Período
                        </label>
                        <input type="text" name="periodo" required readonly data-teclado="texto"
                            class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-base sm:text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 outline-none transition-all"
                            placeholder="Ej: 1-15 Mayo 2026">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 sm:gap-3 bg-zinc-900/50 modo-crema:bg-zinc-50/80 p-2.5 sm:p-3 rounded-xl sm:rounded-2xl border border-transparent modo-crema:border-zinc-200/60">
                    <div class="space-y-1.5 min-w-0">
                        <label class="text-[7px] sm:text-[8px] font-black text-zinc-400 uppercase tracking-wider text-center block">Sueldo Base</label>
                        <input type="text" name="sueldo_base" required id="sueldoBase" readonly data-teclado="numerico" data-teclado-decimales="true"
                            class="w-full h-11 sm:h-10 bg-zinc-950 modo-crema:bg-white rounded-lg text-sm sm:text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 text-center outline-none" placeholder="0.00">
                    </div>
                    <div class="space-y-1.5 min-w-0">
                        <label class="text-[7px] sm:text-[8px] font-black text-emerald-600 uppercase tracking-wider text-center block">+ Bonos</label>
                        <input type="text" name="bonos" value="0" readonly data-teclado="numerico" data-teclado-decimales="true"
                            class="w-full h-11 sm:h-10 bg-zinc-950 modo-crema:bg-white rounded-lg text-sm sm:text-xs font-bold text-emerald-600 text-center outline-none" placeholder="0.00" oninput="calcularMonto()">
                    </div>
                    <div class="space-y-1.5 min-w-0">
                        <label class="text-[7px] sm:text-[8px] font-black text-rose-600 uppercase tracking-wider text-center block">- Descuentos</label>
                        <input type="text" name="deducciones" value="0" readonly data-teclado="numerico" data-teclado-decimales="true"
                            class="w-full h-11 sm:h-10 bg-zinc-950 modo-crema:bg-white rounded-lg text-sm sm:text-xs font-bold text-rose-600 text-center outline-none" placeholder="0.00" oninput="calcularMonto()">
                    </div>
                </div>

                {{-- El pago de nómina siempre se registra como PAGADO (afecta caja hoy). No se maneja estado pendiente. --}}
                <input type="hidden" name="estado" value="pagado">

                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-credit-card opacity-40"></i> Método de Pago
                    </label>
                    <select name="metodo_pago" required class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-base sm:text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 outline-none transition-all appearance-none cursor-pointer">
                        <option value="">Selecciona método</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta">Tarjeta</option>
                        <option value="Transferencia">Transferencia</option>
                    </select>
                </div>

                {{-- Tarjeta de Monto Neto (limpia, sin el select adentro) --}}
                <div class="bg-purple-500/5 border border-purple-500/20 rounded-xl sm:rounded-2xl p-3.5 sm:p-4 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <label class="text-[8px] sm:text-[9px] font-black text-purple-600 uppercase tracking-[0.2em] ml-1">Monto Neto a Pagar</label>
                    </div>
                    <div class="text-xl sm:text-3xl font-black text-purple-600 tracking-tight">$ <span id="montoNeto">0.00</span></div>
                </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-4 px-5 sm:px-8 py-4 border-t border-zinc-800 modo-crema:border-zinc-100 flex-shrink-0" style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                <button type="button" onclick="closeCreateNominaModal()" class="flex-1 h-12 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 hover:text-white transition-all outline-none">Cancelar</button>
                <button type="submit" class="flex-[1.4] h-12 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-purple-600/20 transition-all active:scale-95 outline-none">Guardar Nómina</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TecladoVirtual !== 'undefined') {
            TecladoVirtual.attachAll();
        }
    });

    function closeCreateNominaModal() {
        const modal = document.getElementById('modalCrearNomina');
        const container = document.getElementById('createNominaContainer');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
</script>