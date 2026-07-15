{{-- Estilos para manejo de teclado virtual en PC --}}
<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto #modalCrear {
            align-items: flex-start !important;
            padding-top: 10px !important;
        }
        
        body.teclado-virtual-abierto #createContainer {
            max-height: 45dvh !important;
            transform: translateY(0) scale(0.95) !important;
        }
    }
</style>

<div id="modalCrear" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/40 dark:bg-zinc-950/80 backdrop-blur-sm p-4 transition-all duration-300">
    <div class="fixed inset-0 bg-transparent" onclick="closeCreateModal()"></div>
    
    <div id="createContainer" class="relative bg-white dark:bg-zinc-900 rounded-[24px] w-full max-w-md mx-auto shadow-2xl scale-95 opacity-0 transition-all duration-200 flex flex-col overflow-hidden border border-slate-100 dark:border-zinc-800 max-h-[92vh]">

        {{-- Encabezado del Modal --}}
        <div class="flex items-center gap-4 p-5 sm:p-6 pb-5 bg-white dark:bg-zinc-900 shrink-0">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0 border border-blue-100/50 dark:border-transparent">
                <i class="fas fa-plus text-blue-600 dark:text-blue-400 text-lg"></i>
            </div>
            <div>
                <h2 class="text-[17px] sm:text-[19px] font-black text-slate-800 dark:text-zinc-100 tracking-tight leading-tight">Nueva Categoría</h2>
                <p class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest mt-1">Agregar al menú</p>
            </div>
            <button type="button" onclick="closeCreateModal()" class="ml-auto w-9 h-9 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:text-zinc-500 dark:hover:text-zinc-300 dark:hover:bg-zinc-800 active:scale-95 transition-colors outline-none shrink-0">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="mx-6 border-t border-slate-100 dark:border-zinc-800/80 shrink-0"></div>

        {{-- Formulario --}}
        <form action="{{ route('admin.categorias.store') }}" method="POST" class="px-5 sm:px-6 py-6 space-y-6 overflow-y-auto">
            @csrf

            {{-- Input: Nombre con teclado virtual --}}
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[11px] font-black text-slate-600 dark:text-zinc-400 uppercase tracking-wider">
                    <i class="fas fa-tag text-blue-500 dark:text-blue-400 text-[10px]"></i> Nombre de Categoría
                </label>
                <input type="text" name="nombre" placeholder="Ej: Platos Fuertes..." required readonly data-teclado="texto"
                    class="w-full h-12 bg-slate-50/30 dark:bg-zinc-950/60 border border-slate-200 dark:border-zinc-800 rounded-2xl px-4 text-base font-semibold text-slate-800 dark:text-zinc-100 placeholder:text-slate-400 dark:placeholder:text-zinc-600 outline-none focus:border-blue-500 transition-all">
            </div>

            {{-- Input: Área de Impresión --}}
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[11px] font-black text-slate-600 dark:text-zinc-400 uppercase tracking-wider">
                    <i class="fas fa-print text-blue-500 dark:text-blue-400 text-[10px]"></i> Área de Impresión
                </label>
                <select name="area_impresion" required
                    class="w-full h-12 bg-slate-50/30 dark:bg-zinc-950/60 border border-slate-200 dark:border-zinc-800 rounded-2xl px-4 text-base font-semibold text-slate-800 dark:text-zinc-100 outline-none focus:border-blue-500 transition-all appearance-none cursor-pointer">
                    <option value="Cocina" class="bg-white text-slate-800 dark:bg-zinc-900 dark:text-white">Cocina / Calientes</option>
                    <option value="Barra" class="bg-white text-slate-800 dark:bg-zinc-900 dark:text-white">Barra / Bebidas</option>
                </select>
            </div>

            {{-- Selector de Color --}}
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[11px] font-black text-slate-600 dark:text-zinc-400 uppercase tracking-wider">
                    <i class="fas fa-palette text-blue-500 dark:text-blue-400 text-[10px]"></i> Color
                </label>
                <div class="flex items-center gap-3 w-full h-12 bg-slate-50/30 dark:bg-zinc-950/60 border border-slate-200 dark:border-zinc-800 rounded-2xl px-3">
                    <input type="color" name="color" value="#3B82F6" class="w-8 h-8 rounded-xl cursor-pointer border-0 bg-transparent outline-none">
                    <span class="text-[13px] text-slate-500 dark:text-zinc-500 font-bold">Elige un color</span>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex flex-col-reverse sm:flex-row items-center justify-between pt-2 gap-4">
                <button type="button" onclick="closeCreateModal()"
                    class="w-full sm:w-auto text-center text-[11px] font-black text-slate-500 hover:text-slate-800 dark:text-zinc-500 dark:hover:text-zinc-300 uppercase tracking-widest outline-none py-3 cursor-pointer">
                    Cancelar
                </button>
                <button type="submit"
                    class="w-full sm:flex-1 h-12 bg-blue-600 hover:bg-blue-700 text-white font-black text-[11px] uppercase tracking-widest rounded-2xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-blue-600/20">
                    <i class="fas fa-save text-sm"></i> Guardar Categoría
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TecladoVirtual !== 'undefined') {
            TecladoVirtual.attachAll();
        }
    });
</script>