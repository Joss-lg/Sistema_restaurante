{{-- Modal de Promociones --}}
<div id="modal-promociones" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm px-4">
    <div class="bg-[#141417] border border-white/10 rounded-3xl p-8 max-w-md w-full shadow-2xl animate-in fade-in zoom-in-95 duration-200 max-h-[85vh] flex flex-col">
        
        <h2 class="text-2xl font-black text-white mb-6 text-center">Promociones Activas</h2>
        
        {{-- Contenedor con scroll propio --}}
        <div class="space-y-3 mb-6 overflow-y-auto pr-2 custom-scrollbar" id="promos-list">
            {{-- Ejemplo de estructura que tu JS debería inyectar: --}}
            <div class="flex items-center justify-center py-10">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-500"></div>
            </div>
        </div>
        
        <button type="button" id="btn-cerrar-modal-promos" class="w-full py-3 px-4 bg-white/5 hover:bg-white/10 text-white font-bold rounded-2xl border border-white/10 transition-all">
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
    /* Scrollbar minimalista para que no rompa el diseño */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
</style>