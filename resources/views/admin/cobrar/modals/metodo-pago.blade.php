{{-- Modal de Método de Pago --}}
<div id="modal-metodo" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/80 backdrop-blur-sm px-4 transition-all duration-300">
    <div class="relative !bg-white dark:!bg-[#1c1c1e] border !border-gray-200 dark:!border-white/5 rounded-[2rem] p-8 max-w-sm w-full shadow-2xl animate-in fade-in zoom-in-95 duration-200 overflow-hidden">
        
        {{-- Resplandor decorativo sutil en el fondo --}}
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/5 dark:bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <h2 class="text-2xl font-black !text-gray-900 dark:!text-white mb-6 text-center tracking-tight relative z-10">Método de Pago</h2>
        
        <div class="space-y-3 mb-6 relative z-10">
            {{-- Botón Efectivo --}}
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 !border-gray-200 dark:!border-white/5 !bg-gray-50 dark:!bg-black/20 hover:!border-emerald-500/50 hover:!bg-emerald-50 dark:hover:!bg-emerald-500/10 transition-all font-bold !text-gray-900 dark:!text-white group outline-none" data-metodo="Efectivo">
                <i class="fas fa-money-bill-wave !text-emerald-500 dark:!text-emerald-400 mr-3 group-hover:scale-110 transition-transform"></i> Efectivo
            </button>
            
            {{-- Botón Transferencia --}}
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 !border-gray-200 dark:!border-white/5 !bg-gray-50 dark:!bg-black/20 hover:!border-sky-500/50 hover:!bg-sky-50 dark:hover:!bg-sky-500/10 transition-all font-bold !text-gray-900 dark:!text-white group outline-none" data-metodo="Transferencia">
                <i class="fas fa-bank !text-sky-500 dark:!text-sky-400 mr-3 group-hover:scale-110 transition-transform"></i> Transferencia
            </button>
            
            {{-- Botón Tarjeta de Crédito --}}
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 !border-gray-200 dark:!border-white/5 !bg-gray-50 dark:!bg-black/20 hover:!border-violet-500/50 hover:!bg-violet-50 dark:hover:!bg-violet-500/10 transition-all font-bold !text-gray-900 dark:!text-white group outline-none" data-metodo="Tarjeta">
                <i class="fas fa-credit-card !text-violet-500 dark:!text-violet-400 mr-3 group-hover:scale-110 transition-transform"></i> Tarjeta Crédito
            </button>

            {{-- Botón Tarjeta de Débito --}}
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 !border-gray-200 dark:!border-white/5 !bg-gray-50 dark:!bg-black/20 hover:!border-orange-500/50 hover:!bg-orange-50 dark:hover:!bg-orange-500/10 transition-all font-bold !text-gray-900 dark:!text-white group outline-none" data-metodo="Tarjeta Débito">
                <i class="fas fa-credit-card !text-orange-500 dark:!text-orange-400 mr-3 group-hover:scale-110 transition-transform"></i> Tarjeta Débito
            </button>
        </div>
        
        <button type="button" id="btn-cerrar-modal-metodo" class="w-full py-4 px-4 !bg-gray-100 dark:!bg-white/5 hover:!bg-gray-200 dark:hover:!bg-white/10 !text-gray-500 dark:!text-white/60 hover:!text-gray-900 dark:hover:!text-white font-black text-xs uppercase tracking-widest rounded-2xl border !border-gray-200 dark:!border-white/5 transition-all outline-none relative z-10">
            Cancelar
        </button>
    </div>
</div>

<script>
    // Lógica para cerrar el modal
    document.getElementById('btn-cerrar-modal-metodo').addEventListener('click', () => {
        document.getElementById('modal-metodo').classList.add('hidden');
    });

    // Lógica para capturar el método seleccionado
    document.querySelectorAll('.metodo-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const metodo = this.getAttribute('data-metodo');
            console.log('Método seleccionado:', metodo);
            // Aquí puedes llamar a tu función de procesar pago:
            // procesarPago(metodo);
            document.getElementById('modal-metodo').classList.add('hidden');
        });
    });
</script>