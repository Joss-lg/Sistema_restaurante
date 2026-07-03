{{-- Modal de Error / Alerta - Reutilizable --}}
<div id="modal-error" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/80 backdrop-blur-sm px-4 transition-all duration-300">
    <div class="relative !bg-white dark:!bg-[#1c1c1e] border !border-gray-200 dark:!border-white/5 rounded-[2rem] shadow-2xl p-8 max-w-sm w-full animate-in fade-in zoom-in-95 duration-300">
        
        {{-- Icono --}}
        <div class="flex items-center justify-center w-16 h-16 rounded-2xl !bg-rose-50 dark:!bg-rose-500/10 border !border-rose-100 dark:!border-rose-500/20 mx-auto mb-6 shadow-inner">
            <i class="fas fa-exclamation-triangle !text-rose-500 text-2xl"></i>
        </div>

        {{-- Título --}}
        <h2 id="modal-error-titulo" class="text-2xl font-black text-center !text-gray-900 dark:!text-white mb-2 tracking-tight">Error</h2>
        
        {{-- Mensaje --}}
        <p class="text-center !text-gray-500 dark:!text-gray-400 text-sm mb-6 font-medium leading-relaxed">
            <span id="modal-error-mensaje">Ocurrió un problema, inténtalo de nuevo.</span>
        </p>

        {{-- Panel de Datos (Opcional) --}}
        <div class="!bg-gray-50 dark:!bg-rose-500/5 border !border-gray-200 dark:!border-rose-500/20 rounded-2xl p-5 mb-6">
            <div class="flex justify-between items-center mb-3">
                <span class="!text-gray-400 dark:!text-gray-500 text-[10px] font-black uppercase tracking-widest">Requerido:</span>
                <span class="!text-rose-600 dark:!text-rose-400 font-black text-sm" id="modal-error-requerido">$0.00</span>
            </div>
            
            {{-- Línea divisoria sutil --}}
            <div class="w-full h-px !bg-gray-200 dark:!bg-white/5 mb-3"></div>
            
            <div class="flex justify-between items-center">
                <span class="!text-gray-400 dark:!text-gray-500 text-[10px] font-black uppercase tracking-widest">Ingresado:</span>
                <span class="!text-amber-500 dark:!text-amber-400 font-black text-sm" id="modal-error-ingresado">$0.00</span>
            </div>
        </div>

        {{-- Botón de cierre --}}
        <button type="button" id="btn-cerrar-modal-error" class="w-full py-4 px-4 bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 !text-white font-black text-xs uppercase tracking-widest rounded-2xl shadow-[0_8px_20px_rgba(244,63,94,0.2)] hover:shadow-[0_8px_25px_rgba(244,63,94,0.4)] transition-all outline-none">
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