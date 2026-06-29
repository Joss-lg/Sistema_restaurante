{{-- Modal de Método de Pago --}}
<div id="modal-metodo" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
    <div class="bg-[#141417] border border-white/10 rounded-3xl p-8 max-w-sm w-full shadow-2xl animate-in fade-in zoom-in-95 duration-200">
        <h2 class="text-2xl font-black text-white mb-6 text-center">Método de Pago</h2>
        
        <div class="space-y-3 mb-6">
            {{-- Botones con clase base y atributos data --}}
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 border-white/10 hover:border-emerald-400/50 hover:bg-emerald-500/5 transition-all font-bold text-white group" data-metodo="Efectivo">
                <i class="fas fa-money-bill-wave text-emerald-400 mr-3 group-hover:scale-110 transition-transform"></i> Efectivo
            </button>
            
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 border-white/10 hover:border-sky-400/50 hover:bg-sky-500/5 transition-all font-bold text-white group" data-metodo="Transferencia">
                <i class="fas fa-bank text-sky-400 mr-3 group-hover:scale-110 transition-transform"></i> Transferencia
            </button>
            
            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 border-white/10 hover:border-violet-400/50 hover:bg-violet-500/5 transition-all font-bold text-white group" data-metodo="Tarjeta">
                <i class="fas fa-credit-card text-violet-400 mr-3 group-hover:scale-110 transition-transform"></i> Tarjeta Crédito
            </button>

            <button type="button" class="metodo-btn w-full text-left p-4 rounded-2xl border-2 border-white/10 hover:border-orange-400/50 hover:bg-orange-500/5 transition-all font-bold text-white group" data-metodo="Tarjeta Débito">
                <i class="fas fa-credit-card text-orange-400 mr-3 group-hover:scale-110 transition-transform"></i> Tarjeta Débito
            </button>
        </div>
        
        <button type="button" id="btn-cerrar-modal-metodo" class="w-full py-3 px-4 bg-white/5 hover:bg-white/10 text-white font-bold rounded-2xl border border-white/10 transition-all">
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