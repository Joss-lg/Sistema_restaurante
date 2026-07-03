{{-- Modal de Pago Exitoso --}}
<div id="modal-exito" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/80 backdrop-blur-sm px-4 transition-all duration-300">
    <div class="relative !bg-white dark:!bg-[#1c1c1e] border !border-gray-200 dark:!border-white/5 rounded-[2rem] p-8 max-w-md w-full shadow-2xl animate-in fade-in zoom-in-95 duration-300">
        
        {{-- Resplandor decorativo de fondo --}}
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-emerald-500/10 dark:bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Icono Animado --}}
        <div class="flex justify-center mb-6 relative z-10">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl !bg-emerald-50 dark:!bg-emerald-500/10 border !border-emerald-100 dark:!border-emerald-500/20 shadow-inner animate-pulse">
                <i class="fas fa-check text-3xl !text-emerald-500 dark:!text-emerald-400"></i>
            </div>
        </div>
        
        <h2 id="modal-titulo" class="text-2xl font-black text-center !text-gray-900 dark:!text-white mb-2 tracking-tight relative z-10">¡Pago Exitoso!</h2>
        
        <p id="modal-descripcion" class="text-center !text-gray-500 dark:!text-gray-400 text-sm mb-6 font-medium relative z-10">
            Se procesó correctamente el pago de <strong id="modal-nombre-persona" class="!text-emerald-600 dark:!text-emerald-400 font-black"></strong>
        </p>
        
        {{-- Resumen de la Operación --}}
        <div class="!bg-gray-50 dark:!bg-white/5 border !border-gray-200 dark:!border-white/10 rounded-2xl p-5 mb-6 space-y-3 relative z-10 transition-colors">
            <div class="flex justify-between items-center">
                <span class="!text-gray-400 dark:!text-gray-500 text-[10px] font-black uppercase tracking-widest">Monto pagado</span>
                <span class="!text-gray-900 dark:!text-white font-black text-sm" id="modal-monto-pagado">$0.00</span>
            </div>
            
            {{-- Línea divisoria sutil --}}
            <div class="w-full h-px !bg-gray-200 dark:!bg-white/5"></div>
            
            <div class="flex justify-between items-center">
                <span class="!text-gray-400 dark:!text-gray-500 text-[10px] font-black uppercase tracking-widest" id="modal-etiqueta-total">Nuevo total en mesa</span>
                <span class="!text-emerald-600 dark:!text-emerald-400 font-black text-sm" id="modal-nuevo-total">$0.00</span>
            </div>
        </div>
        
        {{-- Botones de Acción --}}
        <div class="flex gap-3 relative z-10">
            <button type="button" id="btn-cerrar-modal-exito" class="flex-1 py-4 px-4 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 !text-white font-black text-xs uppercase tracking-widest rounded-2xl transition-all shadow-[0_8px_20px_rgba(16,185,129,0.2)] hover:shadow-[0_8px_25px_rgba(16,185,129,0.4)] outline-none">
                Continuar
            </button>
            <button type="button" id="btn-liberar-mesa-modal" class="flex-1 py-4 px-4 bg-gradient-to-r from-orange-500 to-rose-500 hover:from-orange-400 hover:to-rose-400 !text-white font-black text-xs uppercase tracking-widest rounded-2xl transition-all shadow-[0_8px_20px_rgba(249,115,22,0.2)] hover:shadow-[0_8px_25px_rgba(249,115,22,0.4)] outline-none hidden">
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