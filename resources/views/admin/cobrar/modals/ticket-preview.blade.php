{{-- Modal de vista previa del ticket: visible, estático, con Imprimir/Cerrar --}}
<div id="modal-ticket-preview" class="fixed inset-0 z-[200] hidden flex items-center justify-center bg-black/80 backdrop-blur-sm px-4 transition-all duration-300">
    <div class="relative !bg-white dark:!bg-[#1c1c1e] border !border-gray-200 dark:!border-white/5 rounded-[1.5rem] w-full max-w-md shadow-2xl flex flex-col" style="max-height: 90vh;">

        <div class="flex items-center justify-between px-5 py-4 border-b !border-gray-200 dark:!border-white/10 shrink-0">
            <h2 class="text-sm font-black uppercase tracking-widest !text-gray-900 dark:!text-white">
                <i class="fas fa-receipt mr-2 !text-blue-500"></i> Ticket
            </h2>
            <button type="button" id="btn-cerrar-x-ticket-preview" class="w-8 h-8 rounded-full flex items-center justify-center !bg-gray-100 dark:!bg-white/10 !text-gray-500 dark:!text-gray-300 hover:!bg-gray-200 dark:hover:!bg-white/20 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-3 bg-gray-100 dark:bg-black/30">
            <iframe id="ticket-preview-iframe" class="w-full bg-white rounded-lg shadow-inner" style="min-height: 60vh; border: none;"></iframe>
        </div>

        <div class="grid grid-cols-2 gap-3 p-4 border-t !border-gray-200 dark:!border-white/10 shrink-0">
            <button type="button" id="btn-cerrar-ticket-preview" class="py-3.5 px-4 !bg-gray-100 dark:!bg-white/5 hover:!bg-gray-200 dark:hover:!bg-white/10 !text-gray-900 dark:!text-white font-black text-xs uppercase tracking-widest rounded-2xl border !border-gray-200 dark:!border-white/10 transition-all">
                Cerrar
            </button>
            <button type="button" id="btn-imprimir-ticket-preview" class="py-3.5 px-4 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 !text-white font-black text-xs uppercase tracking-widest rounded-2xl transition-all shadow-[0_8px_20px_rgba(37,99,235,0.25)]">
                <i class="fas fa-print mr-1.5"></i> Imprimir
            </button>
        </div>
    </div>
</div>