{{-- Modal de Promociones --}}
<div id="modal-promociones" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/80 backdrop-blur-sm px-4 transition-all duration-300">
    <div class="relative !bg-white dark:!bg-[#1c1c1e] border !border-gray-200 dark:!border-white/5 rounded-[2rem] p-8 max-w-md w-full shadow-2xl animate-in fade-in zoom-in-95 duration-200 max-h-[85vh] flex flex-col overflow-hidden">
        
        {{-- Resplandor decorativo sutil en el fondo --}}
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-blue-500/5 dark:bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <h2 class="text-2xl font-black !text-gray-900 dark:!text-white mb-6 text-center tracking-tight relative z-10">Promociones Activas</h2>
        
        {{-- Contenedor con scroll propio --}}
        <div class="space-y-3 mb-6 overflow-y-auto pr-2 custom-scrollbar relative z-10" id="promos-list">
            {{-- Ejemplo de estructura que tu JS debería inyectar: --}}
            <div class="flex items-center justify-center py-10">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 !border-blue-500"></div>
            </div>
        </div>
        
        <button type="button" id="btn-cerrar-modal-promos" class="w-full py-4 px-4 !bg-gray-100 dark:!bg-white/5 hover:!bg-gray-200 dark:hover:!bg-white/10 !text-gray-500 dark:!text-white/60 hover:!text-gray-900 dark:hover:!text-white font-black text-xs uppercase tracking-widest rounded-2xl border !border-gray-200 dark:!border-white/5 transition-all outline-none relative z-10">
            Cerrar
        </button>
    </div>
</div>

<script>
    // Cierre del modal
    document.getElementById('btn-cerrar-modal-promos').addEventListener('click', () => {
        document.getElementById('modal-promociones').classList.add('hidden');
    });
</script>

<style>
    /* Scrollbar minimalista adaptativo para ambos modos */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
    
    @media (prefers-color-scheme: dark) {
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
    }
</style>