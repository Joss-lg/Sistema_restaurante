{{-- MODAL NIP CAPITÁN --}}
<div id="modalNip" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-sm rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-[var(--text-main)]">NIP Capitán</h2>
            <button type="button" onclick="cerrarModal('modalNip')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
        </div>
        <input type="password" id="nipInput" inputmode="numeric" autocomplete="off"
               class="w-full rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] p-4 text-xl font-black text-center text-[var(--text-main)] outline-none focus:border-[#3b82f6] transition-colors"
               placeholder="••••">
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="cerrarModal('modalNip')" class="px-6 py-2.5 rounded-xl border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] text-xs font-bold transition-all">Cancelar</button>
            <button type="button" onclick="confirmarNipCapitan()" class="px-6 py-2.5 rounded-xl bg-[#3b82f6] text-white text-xs font-bold transition-all hover:opacity-90">Aceptar</button>
        </div>
    </div>
</div>

{{-- MODAL CAPITÁN --}}
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

{{-- MODAL GRAMAJE (rediseñado: botones rápidos + preview de precio calculado) --}}
<div id="modalGramaje" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-sm rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
        <div class="flex items-center justify-between mb-4">
            <h2 id="modalGramajeTitulo" class="text-lg font-bold text-[var(--text-main)] truncate max-w-[200px]">Gramaje</h2>
            <button type="button" onclick="cerrarModalGramaje()" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
        </div>

        <div class="flex items-center gap-3 mb-2">
            <input id="gramajeInput" type="text" readonly class="flex-1 min-w-0 rounded-xl border border-[var(--border-color)] bg-[var(--input-bg)] p-4 text-2xl font-black text-[var(--text-main)] text-center outline-none" placeholder="0">
            <span class="shrink-0 text-[var(--text-muted)] font-bold text-lg">g</span>
        </div>

        {{-- Preview del precio calculado: solo se llena cuando el producto se vende por peso --}}
        <p id="gramajePrecioPreview" class="text-center text-sm font-black text-[#f97316] mb-4 h-5"></p>

        {{-- Botones rápidos de gramaje común --}}
        <div id="botonesRapidosGramaje" class="grid grid-cols-5 gap-1.5 mb-4">
            @foreach([['100','100g'],['250','250g'],['500','500g'],['700','700g'],['1000','1kg']] as [$valor, $etiqueta])
                <button type="button" onclick="seleccionarGramajeRapido({{ $valor }})" class="py-2 rounded-lg bg-orange-500/10 border border-orange-500/20 text-orange-500 hover:bg-orange-500 hover:text-white text-[11px] font-bold transition-colors">{{ $etiqueta }}</button>
            @endforeach
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

{{-- MODAL TIPO DE TRASPASO (Producto individual vs Pedido completo) --}}
<div id="modalTipoTraspaso" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-sm rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-indigo-500 font-bold">Traspaso</p>
                <h2 id="tituloTipoTraspaso" class="text-lg font-bold text-[var(--text-main)] mt-1">¿Qué deseas traspasar?</h2>
            </div>
            <button type="button" onclick="cerrarModal('modalTipoTraspaso')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div class="flex flex-col gap-3">
            <button type="button" onclick="elegirTraspasoProducto()" class="w-full text-left rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] px-4 py-4 hover:border-[#3b82f6]/50 transition-all">
                <p class="text-sm font-bold text-[var(--text-main)]">Producto Individual</p>
                <p class="text-[11px] text-[var(--text-muted)] mt-1">Elige uno o varios platillos específicos</p>
            </button>
            <button type="button" onclick="elegirTraspasoCompleto()" class="w-full text-left rounded-2xl border border-[var(--border-color)] bg-[var(--bg-base)] px-4 py-4 hover:border-[#3b82f6]/50 transition-all">
                <p class="text-sm font-bold text-[var(--text-main)]">Pedido Completo</p>
                <p class="text-[11px] text-[var(--text-muted)] mt-1">Envía toda la orden a la mesa destino</p>
            </button>
        </div>
    </div>
</div>

{{-- MODAL SELECCIÓN DE PRODUCTOS (para traspaso individual) --}}
<div id="modalSeleccionProductos" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-indigo-500 font-bold">Traspaso</p>
                <h2 class="text-lg font-bold text-[var(--text-main)] mt-1">Selecciona productos</h2>
            </div>
            <button type="button" onclick="cerrarModal('modalSeleccionProductos')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div id="listaProductosTraspaso" class="grid gap-2 max-h-[320px] overflow-y-auto hide-scroll pb-2"></div>
        <div class="mt-5 flex justify-end gap-3">
            <button type="button" onclick="cerrarModal('modalSeleccionProductos')" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-medium text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all">Cancelar</button>
            <button type="button" onclick="confirmarTraspasoProductos()" class="px-5 py-2.5 rounded-xl bg-[#3b82f6] text-white text-xs font-bold transition-all hover:opacity-90">Traspasar</button>
        </div>
    </div>
</div>

{{-- MODAL SELECCIÓN DE COMBO — el mesero elige cuáles productos del
     combo (de los que ya están en el ticket) quiere que cuenten para el
     descuento. Ya no se exige tener TODOS los productos vinculados. --}}
<div id="modalSeleccionCombo" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-blue-500 font-bold">Combo</p>
                <h2 id="tituloSeleccionCombo" class="text-lg font-bold text-[var(--text-main)] mt-1">Selecciona los productos</h2>
            </div>
            <button type="button" onclick="cerrarModal('modalSeleccionCombo')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
        </div>
        <p class="text-[11px] text-[var(--text-muted)] mb-3">Marca cuáles productos del ticket quieres que cuenten para este combo. Solo puedes marcar los que ya están agregados.</p>
        <div id="listaSeleccionCombo" class="grid gap-2 max-h-[320px] overflow-y-auto hide-scroll pb-2"></div>
        <div class="mt-5 flex justify-end gap-3">
            <button type="button" onclick="cerrarModal('modalSeleccionCombo')" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-medium text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all">Cancelar</button>
            <button type="button" onclick="confirmarSeleccionCombo()" class="px-5 py-2.5 rounded-xl bg-[#3b82f6] text-white text-xs font-bold transition-all hover:opacity-90">Aplicar combo</button>
        </div>
    </div>
</div>

{{-- MODAL PROMOCIONES (mesero) --}}
<div id="modalPromociones" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-[var(--card-shadow)]">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-blue-500 font-bold">Marketing y Ofertas</p>
                <h2 class="text-lg font-bold text-[var(--text-main)] mt-1">Promociones activas</h2>
            </div>
            <button type="button" onclick="cerrarModal('modalPromociones')" class="text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div id="listaPromocionesMesero" class="grid gap-2 max-h-[360px] overflow-y-auto hide-scroll pb-2"></div>
        <div class="mt-5 text-right">
            <button type="button" onclick="cerrarModal('modalPromociones')" class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] text-xs font-medium text-[var(--text-muted)] hover:text-[var(--text-main)] transition-all">Cerrar</button>
        </div>
    </div>
</div>