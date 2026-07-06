{{-- MODAL CAPITÁN --}}
@if($esCapitan ?? false)
    <div id="modalCapitan" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        @php $mesasAbiertas = $mesasAbiertas ?? collect(); @endphp
        <div class="w-full max-w-md rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-indigo-500 font-bold">Autorización</p>
                    <h2 class="text-xl font-semibold text-[var(--text-main)] mt-1">Selecciona mesa destino</h2>
                </div>
                <button type="button" onclick="cerrarModal('modalCapitan')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
            </div>
            <div id="capitanMesasContainer" class="grid gap-2 max-h-[300px] overflow-y-auto hide-scroll pb-2"></div>
            <div class="mt-5 text-right">
                <button type="button" onclick="cerrarModal('modalCapitan')" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-medium text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all">Cancelar</button>
            </div>
        </div>
    </div>
@endif

{{-- MODAL NOTAS --}}
<div id="modalNota" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-[var(--text-main)]">Instrucción Especial</h2>
            <button type="button" onclick="cerrarModal('modalNota')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
        </div>
        <textarea id="notaTextarea" rows="3" readonly class="w-full rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] p-4 text-sm font-medium text-[var(--text-main)] outline-none resize-none focus:border-[#3b82f6] transition-colors" placeholder="Ej. Sin cebolla..."></textarea>

        <div id="tecladoNotas" class="grid grid-cols-10 gap-1.5 mt-4">
            @foreach(['1','2','3','4','5','6','7','8','9','0','Q','W','E','R','T','Y','U','I','O','P','A','S','D','F','G','H','J','K','L','Ñ','Z','X','C','V','B','N','M',',','.'] as $key)
                <button type="button" onclick="insertarNotaCaracter('{{ $key }}')" class="px-1 py-2.5 rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-[var(--border-highlight)] hover:bg-[var(--hover-bg)] text-[var(--text-main)] text-xs font-bold transition-colors">{{ $key }}</button>
            @endforeach
            <button type="button" onclick="insertarNotaEspacio()" class="col-span-4 px-1 py-2.5 rounded-lg bg-[#3b82f6] text-white text-xs font-bold transition-colors hover:opacity-90">Espacio</button>
            <button type="button" onclick="borrarNotaCaracter()" class="col-span-3 px-1 py-2.5 rounded-lg bg-red-500 text-white text-xs font-bold transition-colors hover:opacity-90"><i class="fas fa-backspace"></i></button>
            <button type="button" onclick="limpiarNota()" class="col-span-3 px-1 py-2.5 rounded-lg bg-[var(--text-muted)] text-white text-xs font-bold transition-colors hover:opacity-90">Limpiar</button>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="cerrarModal('modalNota')" class="px-6 py-2.5 rounded-xl border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] text-xs font-bold transition-all">Cancelar</button>
            <button type="button" onclick="guardarNota()" class="px-6 py-2.5 rounded-xl bg-[var(--text-main)] text-[var(--bg-base)] text-xs font-bold transition-all hover:opacity-90">Confirmar</button>
        </div>
    </div>
</div>

{{-- MODAL DESCUENTO --}}
<div id="modalDescuento" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-sm rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-[var(--text-main)]">Descuento (%)</h2>
            <button type="button" onclick="cerrarModal('modalDescuento')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
        </div>
        <input id="descuentoInput" type="number" min="0" max="100" class="w-full rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] p-4 text-xl font-black text-center text-[var(--text-main)] outline-none focus:border-[#3b82f6] transition-colors" placeholder="0">
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="guardarDescuento()" class="w-full px-6 py-3 rounded-xl bg-[#3b82f6] text-white text-sm font-bold transition-all hover:opacity-90">Aplicar</button>
        </div>
    </div>
</div>

{{-- MODAL PERSONAS --}}
<div id="modalPersonas" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-sm rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-[var(--text-main)]">Personas en Mesa</h2>
            <button type="button" onclick="cerrarModal('modalPersonas')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
        </div>
        <input id="personasInput" type="number" min="1" class="w-full rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] p-4 text-xl font-black text-center text-[var(--text-main)] outline-none focus:border-[#3b82f6] transition-colors">
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="guardarPersonas()" class="w-full px-6 py-3 rounded-xl bg-[var(--text-main)] text-[var(--bg-base)] text-sm font-bold transition-all hover:opacity-90">Guardar</button>
        </div>
    </div>
</div>

{{-- MODAL GRAMAJE --}}
<div id="modalGramaje" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-sm rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
        <div class="flex items-center justify-between mb-4">
            <h2 id="modalGramajeTitulo" class="text-lg font-bold text-[var(--text-main)] truncate max-w-[200px]">Gramaje</h2>
            <button type="button" onclick="cerrarModal('modalGramaje')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div class="flex items-center gap-3 mb-4">
            <input id="gramajeInput" type="text" readonly class="flex-1 rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] p-4 text-2xl font-black text-[var(--text-main)] text-center outline-none" placeholder="0">
            <span class="text-[var(--text-muted)] font-bold text-lg">g</span>
        </div>
        <div id="tecladoGramaje" class="grid grid-cols-3 gap-2">
            @foreach(['1','2','3','4','5','6','7','8','9','.','0'] as $key)
                <button type="button" onclick="anadirNumeroGramaje('{{ $key }}')" class="py-3 rounded-xl bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-[var(--border-highlight)] hover:bg-[var(--hover-bg)] text-[var(--text-main)] text-lg font-bold transition-colors">{{ $key }}</button>
            @endforeach
            <button type="button" onclick="borrarNumeroGramaje()" class="py-3 rounded-xl bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white text-sm font-bold transition-colors"><i class="fas fa-backspace"></i></button>
        </div>
        <div class="mt-6">
            <button type="button" onclick="guardarGramajeDelItem()" class="w-full px-6 py-3 rounded-xl bg-[#f97316] text-white text-sm font-bold transition-all hover:opacity-90">Confirmar</button>
        </div>
    </div>
</div>