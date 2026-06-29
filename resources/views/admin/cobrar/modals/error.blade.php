{{-- Modal de Error / Alerta - Reutilizable --}}
<div id="modal-error" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
    <div class="bg-gradient-to-br from-[#1a1a1e] to-[#0f0f12] rounded-3xl border border-red-500/20 shadow-2xl shadow-red-500/10 p-8 max-w-sm w-full animate-in fade-in zoom-in-95 duration-300">
        
        {{-- Icono --}}
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-500/10 border border-red-500/30 mx-auto mb-6">
            <i class="fas fa-exclamation-triangle text-red-500 text-lg"></i>
        </div>

        {{-- Título --}}
        <h2 id="modal-error-titulo" class="text-2xl font-black text-center text-white mb-2">Error</h2>
        
        {{-- Mensaje --}}
        <p class="text-center text-gray-400 text-sm mb-6">
            <span id="modal-error-mensaje">Ocurrió un problema, inténtalo de nuevo.</span>
        </p>

        {{-- Panel de Datos (Opcional) --}}
        <div class="bg-red-500/5 border border-red-500/20 rounded-xl p-4 mb-6">
            <div class="flex justify-between mb-2">
                <span class="text-gray-500 text-xs font-bold uppercase">Requerido:</span>
                <span class="text-red-400 font-black text-sm" id="modal-error-requerido">$0.00</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500 text-xs font-bold uppercase">Ingresado:</span>
                <span class="text-yellow-400 font-black text-sm" id="modal-error-ingresado">$0.00</span>
            </div>
        </div>

        {{-- Botón de cierre --}}
        <button type="button" id="btn-cerrar-modal-error" class="w-full py-3 px-4 bg-red-500/20 hover:bg-red-500/30 text-red-300 font-black rounded-2xl border border-red-500/30 transition-all">
            <i class="fas fa-check mr-2"></i> Entendido
        </button>
    </div>
</div>

<script>
    // Script para cerrar el modal
    document.getElementById('btn-cerrar-modal-error').addEventListener('click', () => {
        document.getElementById('modal-error').classList.add('hidden');
    });
</script>