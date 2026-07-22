{{-- Estilos para manejo de teclado virtual en PC --}}
<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalCrearGasto {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }
        
        body.teclado-virtual-abierto #createGastoContainer {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important; 
        }
    }
</style>

<div id="modalCrearGasto" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-3 sm:p-4 transition-all duration-300">
    
    <div id="createGastoContainer" class="bg-zinc-950 modo-crema:bg-white border border-zinc-800 modo-crema:border-zinc-200 w-full max-w-md rounded-2xl sm:rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0 flex flex-col max-h-[92dvh]">

        <div class="p-5 sm:p-8 pb-4 flex justify-between items-center border-b border-zinc-800 modo-crema:border-zinc-100 flex-shrink-0 gap-3">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-rose-600/10 flex items-center justify-center text-rose-500 border border-rose-500/20 shrink-0">
                    <i class="fas fa-money-bill-wave text-lg sm:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-black text-zinc-100 modo-crema:text-zinc-900 tracking-tight uppercase truncate">Nuevo Gasto</h3>
                    <p class="text-[10px] sm:text-xs text-zinc-400 modo-crema:text-zinc-500 font-bold uppercase tracking-[0.2em]">Registrar egreso</p>
                </div>
            </div>
            <button onclick="closeCreateGastoModal()" class="w-9 h-9 rounded-xl flex items-center justify-center bg-white/5 modo-crema:bg-zinc-100 text-zinc-400 modo-crema:text-zinc-500 hover:text-rose-500 modo-crema:hover:text-rose-600 hover:bg-rose-500/10 modo-crema:hover:bg-rose-50 transition-all outline-none flex-shrink-0">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form action="{{ route('admin.gastos.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
            @csrf

            <div class="p-5 sm:p-8 pt-5 sm:pt-6 space-y-4 overflow-y-auto flex-1 overscroll-contain" style="-webkit-overflow-scrolling: touch;">

                {{-- Campo Concepto: Teclado Texto --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-pen opacity-40"></i> Concepto
                    </label>
                    <input type="text" name="concepto" required readonly data-teclado="texto"
                        class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-base sm:text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-rose-600 modo-crema:focus:border-rose-500 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all placeholder:text-zinc-600 modo-crema:placeholder:text-zinc-400"
                        placeholder="Ej: Compra de tomates">
                </div>

                {{-- Selects (no llevan teclado virtual) --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-folder opacity-40"></i> Categoría
                    </label>
                    <select name="categoria" required class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-base sm:text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-rose-600 modo-crema:focus:border-rose-500 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all appearance-none cursor-pointer">
                        <option value="">Selecciona una categoría</option>
                        <option value="Compra Insumos">Compra de Insumos</option>
                        <option value="Servicios">Servicios</option>
                        <option value="Renta">Renta</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                {{-- Campo Monto: Teclado Numérico --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-dollar-sign opacity-40"></i> Monto
                    </label>
                    <input type="text" name="monto" required readonly data-teclado="numerico" data-teclado-decimales="true"
                        class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-base sm:text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-rose-600 modo-crema:focus:border-rose-500 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all"
                        placeholder="0.00">
                </div>

                {{-- Otros campos... --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-credit-card opacity-40"></i> Método de Pago
                    </label>
                    <select name="metodo_pago" required class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-base sm:text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 focus:bg-zinc-800 modo-crema:focus:bg-white focus:border-rose-600 modo-crema:focus:border-rose-500 focus:ring-4 focus:ring-rose-600/10 outline-none transition-all appearance-none cursor-pointer">
                        <option value="">Selecciona método</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta">Tarjeta</option>
                        <option value="Transferencia">Transferencia</option>
                    </select>
                </div>
              {{-- Campo Estado del Pago (independiente) --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-check-circle opacity-40"></i> Estado del Pago
                    </label>
                    <select name="estado" required class="w-full h-11 bg-zinc-900 modo-crema:bg-zinc-50 border border-transparent modo-crema:border-zinc-200/60 rounded-xl px-5 text-base sm:text-xs font-bold text-zinc-100 modo-crema:text-zinc-900 outline-none transition-all appearance-none cursor-pointer">
                        <option value="pagado">Pagado (afecta caja hoy)</option>
                        <option value="pendiente">Pendiente de pago</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 sm:gap-4 px-5 sm:px-8 py-4 border-t border-zinc-800 modo-crema:border-zinc-100 flex-shrink-0" style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                <button type="button" onclick="closeCreateGastoModal()" class="flex-1 h-12 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 modo-crema:text-zinc-500 hover:text-white transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit" class="flex-[1.5] h-12 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-rose-600/20 transition-all active:scale-95 outline-none">
                    Guardar Gasto
                </button>
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

    function closeCreateGastoModal() {
        const modal = document.getElementById('modalCrearGasto');
        const container = document.getElementById('createGastoContainer');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
</script>