{{-- resources/views/admin/categorias/modal-editar.blade.php --}}
{{-- Se incluye una vez por cada $categoria dentro del @forelse de index.blade.php --}}

{{-- Estilos para manejo de teclado virtual en PC (mismo comportamiento que modal-crear) --}}
<style>
    @media (min-width: 768px) {
        body.teclado-virtual-abierto [id^="modalEditar-"] {
            align-items: flex-start !important;
            padding-top: 10px !important;
        }

        body.teclado-virtual-abierto [id^="modalContainer-"] {
            transform: translateY(0) scale(0.98) !important;
            max-height: calc(100dvh - 340px) !important;
        }
    }
</style>

<div id="modalEditar-{{ $categoria->id }}" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] hidden items-center justify-center p-4">

    <div id="modalContainer-{{ $categoria->id }}"
         class="relative bg-white dark:bg-[#121318] border border-slate-200 dark:border-white/5 rounded-2xl p-6 sm:p-8 w-full max-w-md shadow-2xl transform scale-95 opacity-0 transition-all duration-200">

        {{-- Botón cerrar --}}
        <button type="button" onclick="cerrarModalEspecifico('modalEditar-{{ $categoria->id }}')"
            class="absolute top-5 right-5 text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors outline-none">
            <i class="fas fa-times text-lg"></i>
        </button>

        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Editar Categoría</h2>
            <p class="text-xs text-slate-400 dark:text-zinc-500 mt-1">Actualiza los datos de "{{ $categoria->nombre }}"</p>
        </div>

        {{-- Ajusta el nombre de la ruta si en tu web.php no se llama 'admin.categorias.update' --}}
        <form action="{{ route('admin.categorias.update', $categoria->id) }}" method="POST" class="space-y-5 overflow-y-auto">
            @csrf
            @method('PUT')

            {{-- Nombre --}}
            <div>
                <label for="edit_nombre_{{ $categoria->id }}" class="text-[10px] font-bold text-slate-600 dark:text-zinc-300 uppercase tracking-wider mb-2 block">
                    Nombre de la Categoría
                </label>
                <input type="text" id="edit_nombre_{{ $categoria->id }}" name="nombre" required readonly
                    data-teclado="texto" data-teclado-titulo="Nombre de Categoría" autocomplete="off"
                    value="{{ $categoria->nombre }}"
                    class="w-full h-11 rounded-xl bg-slate-50 dark:bg-black/40 border border-slate-200 dark:border-white/10 px-4 text-sm font-medium text-slate-800 dark:text-white outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
            </div>

            {{-- Área de impresión --}}
            <div>
                <label for="edit_area_{{ $categoria->id }}" class="text-[10px] font-bold text-slate-600 dark:text-zinc-300 uppercase tracking-wider mb-2 block">
                    Área de Impresión
                </label>
                <select id="edit_area_{{ $categoria->id }}" name="area_impresion"
                    class="w-full h-11 rounded-xl bg-slate-50 dark:bg-black/40 border border-slate-200 dark:border-white/10 px-4 text-sm font-medium text-slate-800 dark:text-white outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all">
                    <option value="" {{ !$categoria->area_impresion ? 'selected' : '' }}>Sin asignar</option>
                    <option value="Cocina" {{ $categoria->area_impresion == 'Cocina' ? 'selected' : '' }}>Cocina</option>
                    <option value="Barra" {{ $categoria->area_impresion == 'Barra' ? 'selected' : '' }}>Barra</option>
                    <option value="Parrilla" {{ $categoria->area_impresion == 'Parrilla' ? 'selected' : '' }}>Parrilla</option>
                </select>
            </div>

            {{-- Color --}}
            <div>
                <label for="edit_color_{{ $categoria->id }}" class="text-[10px] font-bold text-slate-600 dark:text-zinc-300 uppercase tracking-wider mb-2 block">
                    Color de Identificación
                </label>
                <div class="flex items-center gap-3">
                    <input type="color" id="edit_color_{{ $categoria->id }}" name="color"
                        value="{{ $categoria->color ?? '#3B82F6' }}"
                        class="h-11 w-14 rounded-xl border border-slate-200 dark:border-white/10 cursor-pointer bg-transparent">
                    <span class="text-xs text-slate-400 dark:text-zinc-500">Se usa en el ícono de la lista</span>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex justify-between items-center pt-4 border-t border-slate-100 dark:border-white/5">
                <button type="button" onclick="cerrarModalEspecifico('modalEditar-{{ $categoria->id }}')"
                    class="px-4 py-2.5 text-xs font-bold uppercase tracking-wide text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors outline-none">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wide bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-500/20 transition-all outline-none active:scale-95">
                    Guardar Cambios
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