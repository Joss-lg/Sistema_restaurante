{{-- ============================================================
     CATÁLOGO — Panel de productos (responsive, sin encimado)
     Mejoras: contraste de tarjetas, chips de categoría más claros,
     áreas táctiles ≥44px en móvil, grid que no se aprieta en
     pantallas chicas, safe-area inferior, mejor feedback visual.
     ============================================================ --}}
<section id="col-catalogo"
    class="col-mobile-panel hidden md:flex w-full md:flex-1 md:min-w-[320px] h-full flex-col bg-[var(--bg-base)] border-l md:border-l-0 md:border-r border-[var(--border-color)] z-20">

    {{-- Header: título + categorías --}}
    <div class="p-3 sm:p-4 border-b border-[var(--border-color)] bg-[var(--bg-base)]/95 backdrop-blur supports-[backdrop-filter]:bg-[var(--bg-base)]/80 sticky top-0 z-10">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="text-[9px] font-bold uppercase tracking-[0.2em] text-[var(--text-muted)]">Menú</p>
                <h2 class="text-base sm:text-lg font-black text-[var(--text-main)] leading-tight truncate">Catálogo</h2>
            </div>
            <div class="flex-shrink-0 rounded-full border border-[var(--border-color)] bg-[var(--bg-panel)] px-3 py-1.5 text-[9px] font-semibold text-[var(--text-muted)] whitespace-nowrap">
                <i class="fas fa-utensils mr-1"></i> {{ count($productos ?? []) }} Productos
            </div>
        </div>

        {{-- Categorías: scroll horizontal, chips con más aire y mejor contraste activo/inactivo --}}
        <div id="menuCategorias"
             class="mt-3 flex items-center gap-2 overflow-x-auto hide-scroll pb-1 -mx-1 px-1 snap-x snap-mandatory">
            {{-- Los botones de categoría se inyectan aquí por JS.
                 Clase sugerida por botón (min-h 36px para tacto cómodo):

                 Activo:
                 class="snap-start shrink-0 min-h-[36px] px-4 py-1.5 rounded-full text-[11px] font-bold whitespace-nowrap
                        bg-[var(--text-main)] text-[var(--bg-base)] border border-transparent
                        shadow-sm transition-all duration-150 active:scale-95"

                 Inactivo:
                 class="snap-start shrink-0 min-h-[36px] px-4 py-1.5 rounded-full text-[11px] font-bold whitespace-nowrap
                        bg-[var(--bg-panel)] text-[var(--text-muted)] border border-[var(--border-color)]
                        hover:border-blue-500/30 hover:text-[var(--text-main)]
                        transition-all duration-150 active:scale-95" --}}
        </div>
    </div>

    {{-- Grid de productos: scroll independiente, columnas que no se aprietan en móvil --}}
    <div id="gridProductos"
         class="flex-1 min-h-0 overflow-y-auto hide-scroll overscroll-contain
                p-3 sm:p-4 pb-[calc(1.5rem+env(safe-area-inset-bottom))] md:pb-4
                grid grid-cols-2 xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-[repeat(auto-fill,minmax(150px,1fr))]
                gap-3 sm:gap-3.5
                content-start auto-rows-min">

        @forelse($productos ?? [] as $producto)
        @php
            // --- Categoría: nunca imprimir el objeto/relación completo ---
            $categoriaRel = $producto->categoria ?? null;
            if (is_object($categoriaRel)) {
                $categoriaNombre = $categoriaRel->nombre ?? $categoriaRel->name ?? '';
            } else {
                $categoriaNombre = $categoriaRel ?? '';
            }

            // --- Precio: soporta que el precio de productos por peso venga en
            // un campo distinto a "precio". Ajusta este orden a tu columna real. ---
            $precioMostrar = $producto->precio
                ?? $producto->precio_100g
                ?? $producto->precio_gramaje
                ?? $producto->precio_kg
                ?? $producto->costo
                ?? 0;

            // --- Badge "PESO": se muestra si el producto tiene unidad de venta por peso. ---
            $esPorPeso = !empty($producto->unidad);
        @endphp
        <button type="button"
            onclick="agregarProducto({{ $producto->id ?? 0 }})"
            class="group relative flex flex-col text-left rounded-2xl border border-[var(--border-color)]
                   bg-[var(--bg-panel)] overflow-hidden isolate
                   shadow-sm shadow-black/5
                   hover:border-blue-500/40 hover:shadow-lg hover:shadow-black/20 hover:-translate-y-0.5
                   active:scale-[0.97] active:translate-y-0
                   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/60 focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--bg-base)]
                   transition-all duration-150">

            {{-- Imagen / placeholder con relación de aspecto fija --}}
            <div class="relative w-full aspect-[4/3] bg-gradient-to-br from-[var(--hover-bg)] to-[var(--hover-bg)]/60 flex items-center justify-center overflow-hidden">
                @if(!empty($producto->imagen))
                    <img src="{{ $producto->imagen }}" alt="{{ $producto->nombre }}" loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                    <span class="text-4xl font-black text-[var(--text-muted)]/25 select-none">
                        {{ strtoupper(substr($producto->nombre ?? '?', 0, 1)) }}
                    </span>
                @endif

                {{-- degradado inferior para que los badges/texto siempre se lean --}}
                <div class="absolute inset-x-0 bottom-0 h-8 bg-gradient-to-t from-black/25 to-transparent pointer-events-none"></div>

                {{-- Badge de peso, esquina superior izquierda --}}
                @if($esPorPeso)
                <span class="absolute top-2 left-2 flex items-center gap-1 rounded-full bg-orange-500 px-2 py-0.5 text-[9px] font-bold text-white shadow-sm shadow-black/20">
                    <i class="fas fa-weight-hanging text-[8px]"></i>PESO
                </span>
                @endif

                {{-- Categoría, esquina superior derecha --}}
                @if($categoriaNombre)
                <span class="absolute top-2 right-2 rounded-full bg-black/60 backdrop-blur-sm px-2 py-0.5 text-[8px] font-bold uppercase tracking-wide text-white max-w-[45%] truncate shadow-sm">
                    {{ $categoriaNombre }}
                </span>
                @endif
            </div>

            {{-- Info del producto --}}
            <div class="flex flex-col gap-1.5 p-2.5 sm:p-3 flex-1">
                <h3 class="text-[12px] sm:text-[13px] font-bold text-[var(--text-main)] leading-snug line-clamp-2 min-h-[2.4em]">
                    {{ $producto->nombre }}
                </h3>

                <div class="mt-auto flex items-end justify-between gap-2">
                    <p class="text-[13px] sm:text-sm font-black text-[var(--text-main)] leading-none">
                        ${{ number_format($precioMostrar, 2) }}
                        @if(!empty($producto->unidad))
                            <span class="text-[9px] font-semibold text-[var(--text-muted)]">/{{ $producto->unidad }}</span>
                        @endif
                    </p>

                    {{-- Botón de agregar: 44px real en móvil para tacto cómodo, escala en hover/active --}}
                    <span class="flex-shrink-0 w-9 h-9 sm:w-7 sm:h-7 rounded-full bg-[var(--text-main)] text-[var(--bg-base)]
                                 flex items-center justify-center text-xs font-black
                                 shadow-sm
                                 group-hover:scale-110 group-active:scale-90
                                 transition-transform duration-150">
                        <i class="fas fa-plus"></i>
                    </span>
                </div>
            </div>
        </button>
        @empty
        {{-- Estado vacío: invita a la acción en vez de solo informar --}}
        <div class="col-span-full flex flex-col items-center justify-center gap-3 py-16 text-center">
            <span class="w-14 h-14 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] flex items-center justify-center">
                <i class="fas fa-box-open text-xl text-[var(--text-muted)]"></i>
            </span>
            <div>
                <p class="text-sm font-bold text-[var(--text-main)]">Sin productos en esta categoría</p>
                <p class="text-xs text-[var(--text-muted)] mt-1">Prueba con otra categoría del menú superior.</p>
            </div>
        </div>
        @endforelse
    </div>
</section>

{{-- ============================================================
     MODAL NOTAS — teclado táctil mejorado
     ============================================================ --}}
<div id="modalNota" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="w-full sm:max-w-md max-h-[92vh] sm:max-h-[94vh] overflow-y-auto hide-scroll
                rounded-t-[24px] sm:rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)]
                p-4 sm:p-6 shadow-2xl ring-1 ring-black/5
                pb-[calc(1rem+env(safe-area-inset-bottom))] sm:pb-6">

        {{-- Manija de arrastre solo en mobile --}}
        <div class="sm:hidden w-10 h-1.5 rounded-full bg-[var(--border-color)] mx-auto mb-4"></div>

        <div class="flex items-center justify-between mb-4 sm:mb-5">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500/15 to-blue-500/5 border border-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-pen text-blue-500 text-xs"></i>
                </span>
                <h2 class="text-base sm:text-lg font-bold text-[var(--text-main)] tracking-tight">Instrucción Especial</h2>
            </div>
            <button type="button" onclick="cerrarModal('modalNota')"
                class="text-[var(--text-muted)] hover:text-[var(--text-main)] w-10 h-10 -m-1 rounded-full hover:bg-[var(--hover-bg)] flex items-center justify-center transition-all duration-200 active:scale-90">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <textarea id="notaTextarea" rows="3" readonly
            class="w-full rounded-2xl border border-[var(--border-color)] bg-[var(--input-bg)] shadow-inner p-4 text-sm font-medium text-[var(--text-main)] outline-none resize-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200"
            placeholder="Ej. Sin cebolla..."></textarea>

        {{-- Toggle Letras / Números y símbolos --}}
        <div class="flex items-center justify-between mt-4 mb-2.5 px-0.5">
            <span class="text-[9px] font-black uppercase tracking-[0.15em] text-[var(--text-muted)]">Teclado</span>
            <button type="button" id="btnToggleTecladoNota" onclick="toggleTecladoNota()"
                class="min-w-[52px] min-h-[36px] px-3.5 py-2 rounded-full bg-gradient-to-b from-[var(--input-bg)] to-[var(--hover-bg)] border border-[var(--border-color)] text-[var(--text-main)] hover:border-blue-500/40 hover:shadow-sm active:scale-90 select-none text-[11px] font-black transition-all duration-150">
                123
            </button>
        </div>

        {{-- Teclado de letras (la coma se reemplaza por "/") --}}
        <div id="tecladoNotasLetras" class="grid grid-cols-10 gap-1 sm:gap-1.5">
            @foreach(['1','2','3','4','5','6','7','8','9','0','Q','W','E','R','T','Y','U','I','O','P','A','S','D','F','G','H','J','K','L','Ñ','Z','X','C','V','B','N','M','/','.'] as $key)
                <button type="button" onclick="insertarNotaCaracter('{{ $key }}')"
                    class="min-h-[40px] sm:min-h-[44px] py-2.5 rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-blue-500/30 hover:bg-[var(--hover-bg)] active:scale-90 active:bg-blue-500/10 select-none text-[var(--text-main)] text-[11px] sm:text-xs font-bold shadow-sm transition-all duration-100">
                    {{ $key }}
                </button>
            @endforeach
        </div>

        {{-- Teclado de números y símbolos (oculto por defecto) --}}
        <div id="tecladoNotasSimbolos" class="hidden grid-cols-10 gap-1 sm:gap-1.5">
            @foreach(['!','"','#','$','%','&','/','(',')','=','¿','?','+','-','*','@',':',';',"'",'_','<','>','[',']','{','}','^','~','°','|'] as $key)
                <button type="button" onclick="insertarNotaCaracter('{{ $key }}')"
                    class="min-h-[40px] sm:min-h-[44px] py-2.5 rounded-lg bg-blue-500/[0.07] border border-blue-500/15 hover:border-blue-500/40 hover:bg-blue-500/[0.12] active:scale-90 select-none text-[var(--text-main)] text-[11px] sm:text-xs font-bold shadow-sm transition-all duration-100">
                    {{ $key }}
                </button>
            @endforeach
        </div>

        {{-- Fila de acciones --}}
        <div class="grid grid-cols-10 gap-1 sm:gap-1.5 mt-2">
            <button type="button" onclick="insertarNotaEspacio()"
                class="col-span-4 min-h-[44px] rounded-lg bg-gradient-to-b from-blue-500 to-blue-600 text-white text-[11px] sm:text-xs font-bold active:scale-95 select-none shadow-md shadow-blue-500/20 transition-all duration-150 hover:shadow-lg hover:shadow-blue-500/25">
                Espacio
            </button>
            <button type="button" onclick="borrarNotaCaracter()"
                class="col-span-3 min-h-[44px] rounded-lg bg-red-500/10 border border-red-500/15 text-red-500 hover:bg-red-500 hover:text-white hover:border-red-500 active:scale-95 select-none text-sm font-bold transition-all duration-150">
                <i class="fas fa-backspace"></i>
            </button>
            <button type="button" onclick="limpiarNota()"
                class="col-span-3 min-h-[44px] rounded-lg bg-[var(--text-muted)]/10 border border-[var(--border-color)] text-[var(--text-muted)] hover:bg-[var(--text-muted)] hover:text-white active:scale-95 select-none text-[11px] sm:text-xs font-bold transition-all duration-150">
                Limpiar
            </button>
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2.5 sm:gap-3">
            <button type="button" onclick="cerrarModal('modalNota')"
                class="w-full sm:w-auto px-6 py-3 sm:py-2.5 rounded-xl border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--hover-bg)] active:scale-95 text-xs font-bold transition-all duration-150">
                Cancelar
            </button>
            <button type="button" onclick="guardarNota()"
                class="w-full sm:w-auto px-6 py-3 sm:py-2.5 rounded-xl bg-gradient-to-b from-[var(--text-main)] to-[var(--text-main)] text-[var(--bg-base)] text-xs font-bold active:scale-95 shadow-md transition-all duration-150 hover:opacity-90 hover:shadow-lg">
                Confirmar
            </button>
        </div>
    </div>
</div>

<script>
    // Alterna entre teclado de letras y de números/símbolos.
    function toggleTecladoNota() {
        var letras = document.getElementById('tecladoNotasLetras');
        var simbolos = document.getElementById('tecladoNotasSimbolos');
        var btn = document.getElementById('btnToggleTecladoNota');
        var mostrandoLetras = !letras.classList.contains('hidden');

        if (mostrandoLetras) {
            letras.classList.add('hidden');
            simbolos.classList.remove('hidden');
            simbolos.classList.add('grid');
            btn.textContent = 'ABC';
        } else {
            simbolos.classList.add('hidden');
            simbolos.classList.remove('grid');
            letras.classList.remove('hidden');
            btn.textContent = '123';
        }
    }
</script>