{{-- Modal de Pago Exitoso --}}
<div id="modal-exito" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
    <div class="bg-gradient-to-br from-[#141417] to-[#0a0a0c] border border-emerald-500/20 rounded-3xl p-8 max-w-md w-full shadow-2xl animate-in fade-in zoom-in-95 duration-300">
        
        {{-- Icono Animado --}}
        <div class="flex justify-center mb-6">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/10 border border-emerald-500/30 animate-pulse">
                <i class="fas fa-check text-3xl text-emerald-400"></i>
            </div>
        </div>
        
        <h2 id="modal-titulo" class="text-2xl font-black text-center text-white mb-2">¡Pago Exitoso!</h2>
        
        <p id="modal-descripcion" class="text-center text-gray-400 text-sm mb-6">
            Se procesó correctamente el pago de <strong id="modal-nombre-persona" class="text-emerald-400"></strong>
        </p>
        
        {{-- Resumen de la Operación --}}
        <div class="bg-white/5 border border-white/10 rounded-2xl p-4 mb-6 space-y-2">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-400">Monto pagado</span>
                <span class="text-white font-black" id="modal-monto-pagado">$0.00</span>
            </div>
            <div class="flex justify-between items-center text-sm pt-2 border-t border-white/10">
                <span class="text-gray-400" id="modal-etiqueta-total">Nuevo total en mesa</span>
                <span class="text-emerald-400 font-black" id="modal-nuevo-total">$0.00</span>
            </div>
        </div>
        
        {{-- Botones de Acción --}}
        <div class="flex gap-3">
            <button type="button" id="btn-cerrar-modal-exito" class="flex-1 py-3 px-4 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-black font-black rounded-2xl transition-all shadow-lg shadow-emerald-500/20">
                Continuar
            </button>
            <button type="button" id="btn-liberar-mesa-modal" class="flex-1 py-3 px-4 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-400 hover:to-red-500 text-black font-black rounded-2xl transition-all shadow-lg shadow-orange-500/20 hidden">
                <i class="fas fa-door-open mr-2"></i> Liberar Mesa
            </button>
        </div>
    </div>
</div>

<script>
    // Cierre básico del modal
    document.getElementById('btn-cerrar-modal-exito').addEventListener('click', () => {
        document.getElementById('modal-exito').classList.add('hidden');
    });
</script>