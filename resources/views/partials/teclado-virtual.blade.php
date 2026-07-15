<!-- resources/views/partials/teclado-virtual.blade.php -->
<!-- Se quitó inset-0 y bg-black/40. Se ancló a bottom-0 con pointer-events-none -->
<div id="tecladoVirtualOverlay" class="fixed bottom-0 left-0 right-0 z-[999999] hidden pointer-events-none flex justify-center pb-2 sm:pb-4 px-2 transition-all">
    
    <!-- Contenedor principal del teclado (max-w-md para igualar al modal de empleados) -->
    <div id="tecladoVirtualSheet" class="pointer-events-auto bg-[#111315] border border-gray-800 rounded-2xl sm:rounded-3xl shadow-[0_0_50px_rgba(0,0,0,0.8)] p-2 sm:p-3 w-full max-w-md">

        <!-- Encabezado: Título y botones superiores -->
        <div class="flex items-center justify-between mb-2 px-1">
            <h3 id="tecladoVirtualTitulo" class="text-gray-400 text-[11px] sm:text-xs font-black tracking-wider uppercase">Teclado</h3>
            <div class="flex gap-2">
                <button type="button" id="tecladoVirtualToggle" class="px-3 py-1.5 rounded-lg bg-[#1a1d20] border border-gray-700 text-white text-[10px] font-bold active:scale-95 transition-transform shadow-sm">
                    123
                </button>
                <button type="button" id="tecladoVirtualCerrar" class="px-3 py-1.5 rounded-lg bg-red-500/10 border border-red-500/20 text-red-500 hover:bg-red-500/20 text-[10px] font-bold active:scale-95 transition-transform">
                    Cerrar
                </button>
            </div>
        </div>

        <!-- Aquí el JS insertará dinámicamente las teclas (letras o números) -->
        <div id="tecladoVirtualGrid" class="mb-2"></div>

        <!-- Controles inferiores fijos -->
        <div class="grid grid-cols-4 gap-1 mt-1">
            <button type="button" id="tecladoVirtualLimpiar" class="col-span-1 py-2 sm:py-2.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 font-bold active:scale-95 transition-transform text-[10px] shadow-sm uppercase tracking-wider">
                Limpiar
            </button>
            
            <button type="button" id="tecladoVirtualEspacio" class="col-span-1 py-2 sm:py-2.5 rounded-xl bg-[#1a1d20] border border-gray-700 text-white font-bold active:scale-95 transition-transform shadow-sm text-[10px] uppercase tracking-wider">
                Espacio
            </button>
            
            <button type="button" id="tecladoVirtualBorrar" class="col-span-1 py-2 sm:py-2.5 rounded-xl bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 font-bold active:scale-95 transition-transform text-xs shadow-sm flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path>
                    <line x1="18" y1="9" x2="12" y2="15"></line>
                    <line x1="12" y1="9" x2="18" y2="15"></line>
                </svg>
            </button>
            
            <button type="button" id="tecladoVirtualListo" class="col-span-1 py-2 sm:py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold active:scale-95 transition-transform text-[10px] shadow-md uppercase tracking-wider">
                Listo
            </button>
        </div>

    </div>
</div>