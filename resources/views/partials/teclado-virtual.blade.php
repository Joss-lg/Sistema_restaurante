<!-- resources/views/partials/teclado-virtual.blade.php -->
<style>
    /* Variables de color — modo claro (default) */
    #tecladoVirtualSheet {
        --tv-bg: #ffffff;
        --tv-border: #e5e7eb;
        --tv-text: #1f2937;
        --tv-text-muted: #6b7280;
        --input-bg: #f3f4f6;
        --border-color: #d1d5db;
        --hover-bg: #e5e7eb;
        --text-main: #1f2937;
    }

    /* Variables de color — modo oscuro */
    .dark #tecladoVirtualSheet {
        --tv-bg: #111315;
        --tv-border: #2a2d30;
        --tv-text: #f9fafb;
        --tv-text-muted: #9ca3af;
        --input-bg: #1a1d20;
        --border-color: #2f3336;
        --hover-bg: #23272a;
        --text-main: #f9fafb;
    }

    #tecladoVirtualSheet {
        transition: max-width 0.15s ease;
    }
</style>

<div id="tecladoVirtualOverlay" class="fixed bottom-0 left-0 right-0 z-[999999] hidden pointer-events-none flex justify-center pb-2 sm:pb-3 px-2 transition-all">

    <!-- Contenedor principal del teclado (más ancho, menos alto) -->
    <div id="tecladoVirtualSheet" class="pointer-events-auto bg-[var(--tv-bg)] border border-[var(--tv-border)] rounded-2xl sm:rounded-3xl shadow-[0_-4px_40px_rgba(0,0,0,0.15)] dark:shadow-[0_0_50px_rgba(0,0,0,0.8)] p-2.5 sm:p-3 w-full max-w-md">

        <!-- Encabezado: Título y botones superiores -->
        <div class="flex items-center justify-between mb-1.5 sm:mb-2 px-1">
            <h3 id="tecladoVirtualTitulo" class="text-[var(--tv-text-muted)] text-[11px] sm:text-xs font-black tracking-wider uppercase">
                Teclado
            </h3>
            <div class="flex gap-1.5">
                <button type="button" id="tecladoVirtualMayus"
                    class="px-3 py-1 rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--tv-text)] text-[10px] font-bold active:scale-95 transition-transform shadow-sm min-w-[38px]">
                    ABC
                </button>
                <button type="button" id="tecladoVirtualToggle"
                    class="px-3 py-1 rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--tv-text)] text-[10px] font-bold active:scale-95 transition-transform shadow-sm">
                    123
                </button>
                <button type="button" id="tecladoVirtualCerrar"
                    class="px-3 py-1 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-600 dark:text-red-500 hover:bg-red-100 dark:hover:bg-red-500/20 text-[10px] font-bold active:scale-95 transition-transform">
                    Cerrar
                </button>
            </div>
        </div>

        <!-- Aquí el JS insertará dinámicamente las teclas -->
        <div id="tecladoVirtualGrid" class="mb-1.5"></div>

        <!-- Controles inferiores fijos -->
        <div id="tecladoVirtualControles" class="grid grid-cols-4 gap-1.5 mt-1.5">
            <button type="button" id="tecladoVirtualLimpiar"
                class="col-span-1 py-1.5 sm:py-2 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-500 dark:text-red-400 font-bold active:scale-95 transition-transform text-[10px] shadow-sm uppercase tracking-wider">
                Limpiar
            </button>

            <button type="button" id="tecladoVirtualEspacio"
                class="col-span-1 py-1.5 sm:py-2 rounded-xl bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--tv-text)] font-bold active:scale-95 transition-transform shadow-sm text-[10px] uppercase tracking-wider">
                Espacio
            </button>

            <button type="button" id="tecladoVirtualBorrar"
                class="col-span-1 py-1.5 sm:py-2 rounded-xl bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-300 dark:border-yellow-500/20 text-yellow-600 dark:text-yellow-500 font-bold active:scale-95 transition-transform text-xs shadow-sm flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path>
                    <line x1="18" y1="9" x2="12" y2="15"></line>
                    <line x1="12" y1="9" x2="18" y2="15"></line>
                </svg>
            </button>

            <button type="button" id="tecladoVirtualListo"
                class="col-span-1 py-1.5 sm:py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold active:scale-95 transition-transform text-[10px] shadow-md uppercase tracking-wider">
                Listo
            </button>
        </div>

    </div>
</div>