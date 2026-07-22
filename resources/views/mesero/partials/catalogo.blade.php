{{-- BACKDROP PARA MÓVIL (Fondo oscuro al abrir el carrito flotante) --}}
<div id="backdropOrdenMobile" onclick="toggleOrdenMobile()"
     class="md:hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-40 opacity-0 pointer-events-none transition-opacity duration-300">
</div>

{{-- ============================================================
     CATÁLOGO — Panel de productos (adaptable, sin encimado)
     ============================================================ --}}
<section id="col-catalogo"
    class="col-mobile-panel flex w-full md:flex-1 md:min-w-[320px] h-full flex-col bg-[var(--bg-base)] border-l md:border-l-0 md:border-r border-[var(--border-color)] z-20">

    {{-- Encabezado: título + categorías --}}
    <div class="p-3 sm:p-4 border-b border-[var(--border-color)] bg-[var(--bg-base)]/95 backdrop-blur supports-[backdrop-filter]:bg-[var(--bg-base)]/80 sticky top-0 z-10">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[var(--text-muted)]">Menú</p>
                <h2 class="text-base sm:text-lg font-black text-[var(--text-main)] leading-tight truncate">Catálogo</h2>
            </div>
            <div class="flex-shrink-0 rounded-full border border-[var(--border-color)] bg-[var(--bg-panel)] px-3 py-1.5 text-[10px] font-semibold text-[var(--text-muted)] whitespace-nowrap">
                <i class="fas fa-utensils mr-1"></i> {{ count($productos ?? []) }} Productos
            </div>
        </div>

        {{-- Categorías --}}
        <div id="menuCategorias"
             class="mt-3 flex items-center gap-2 overflow-x-auto hide-scroll pb-1 -mx-1 px-1 snap-x snap-mandatory">
            {{-- Los botones de categoría se inyectan aquí por JavaScript --}}
        </div>
    </div>

    {{-- Cuadrícula de productos --}}
    <div id="gridProductos"
         class="flex-1 min-h-0 overflow-y-auto hide-scroll overscroll-contain
                p-3 sm:p-4 pb-[calc(6.5rem+env(safe-area-inset-bottom))] md:pb-4
                grid grid-cols-2 xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-[repeat(auto-fill,minmax(150px,1fr))]
                gap-3 sm:gap-3.5
                content-start auto-rows-min">

        @forelse($productos ?? [] as $producto)
        @php
            $categoriaRel = $producto->categoria ?? null;
            if (is_object($categoriaRel)) {
                $categoriaNombre = $categoriaRel->nombre ?? $categoriaRel->name ?? '';
            } else {
                $categoriaNombre = $categoriaRel ?? '';
            }

            $precioMostrar = $producto->precio
                ?? $producto->precio_100g
                ?? $producto->precio_gramaje
                ?? $producto->precio_kg
                ?? $producto->costo
                ?? 0;

            $esPorPeso = !empty($producto->unidad);
        @endphp
        
        {{-- MODIFICACIÓN IMPORTANTE: Se eliminó el onclick directo y se agregó data-producto-id y la clase btn-producto --}}
        <button type="button"
            data-producto-id="{{ $producto->id ?? 0 }}"
            class="btn-producto group relative flex flex-col text-left rounded-2xl border border-[var(--border-color)]
                   bg-[var(--bg-panel)] overflow-hidden isolate
                   shadow-sm shadow-black/5
                   hover:border-blue-500/40 hover:shadow-lg hover:shadow-black/20 hover:-translate-y-0.5
                   active:scale-[0.97] active:translate-y-0
                   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/60 focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--bg-base)]
                   transition-all duration-150">

            {{-- Imagen --}}
            <div class="relative w-full aspect-[4/3] bg-gradient-to-br from-[var(--hover-bg)] to-[var(--hover-bg)]/60 flex items-center justify-center overflow-hidden">
                @if(!empty($producto->imagen))
                    <img src="{{ $producto->imagen }}" alt="{{ $producto->nombre }}" loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                    <span class="text-4xl font-black text-[var(--text-muted)]/25 select-none">
                        {{ strtoupper(substr($producto->nombre ?? '?', 0, 1)) }}
                    </span>
                @endif

                <div class="absolute inset-x-0 bottom-0 h-8 bg-gradient-to-t from-black/25 to-transparent pointer-events-none"></div>

                @if($esPorPeso)
                <span class="absolute top-2 left-2 flex items-center gap-1 rounded-full bg-orange-500 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm shadow-black/20">
                    <i class="fas fa-weight-hanging text-[9px]"></i>PESO
                </span>
                @endif

                @if($categoriaNombre)
                <span class="absolute top-2 right-2 rounded-full bg-black/60 backdrop-blur-sm px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-white max-w-[45%] truncate shadow-sm">
                    {{ $categoriaNombre }}
                </span>
                @endif
            </div>

            {{-- Información del producto --}}
            <div class="flex flex-col gap-1.5 p-2.5 sm:p-3 flex-1">
                <h3 class="text-[13px] sm:text-[14px] font-bold text-[var(--text-main)] leading-snug line-clamp-2 min-h-[2.4em]">
                    {{ $producto->nombre }}
                </h3>

                <div class="mt-auto flex items-end justify-between gap-2">
                    <p class="text-[13px] sm:text-sm font-black text-[var(--text-main)] leading-none">
                        ${{ number_format($precioMostrar, 2) }}
                        @if(!empty($producto->unidad))
                            <span class="text-[10px] font-semibold text-[var(--text-muted)]">/{{ $producto->unidad }}</span>
                        @endif
                    </p>

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

    {{-- MINI-BARRA DE ORDEN ACTIVA (solo móvil) --}}
    <button type="button" id="miniCartBar" onclick="toggleOrdenMobile()"
        class="hidden md:hidden fixed left-3 right-3 z-40 items-center justify-between gap-3
               rounded-2xl bg-[var(--text-main)] text-[var(--bg-base)]
               px-4 py-3 shadow-[0_10px_30px_-8px_rgba(0,0,0,0.4)]
               active:scale-[0.98] transition-transform duration-150"
        style="bottom: calc(68px + env(safe-area-inset-bottom));">
        <span class="flex items-center gap-2 min-w-0">
            <span id="miniCartCount" class="shrink-0 w-6 h-6 rounded-full bg-[var(--bg-base)] text-[var(--text-main)] text-[11px] font-black flex items-center justify-center">0</span>
            <span class="text-[12px] font-bold truncate">Ver orden</span>
        </span>
        <span class="flex items-center gap-2 shrink-0">
            <span id="miniCartTotal" class="text-[13px] font-black">$0.00</span>
            <i class="fas fa-chevron-up text-[11px] opacity-70"></i>
        </span>
    </button>
</section>

{{-- ============================================================
     SECCIÓN ORDEN / TICKET (Adaptable: Columna en PC / Panel inferior en móvil)
     ============================================================ --}}
<section id="col-ticket"
    class="
        /* --- ESTILOS PARA LAPTOP (Se mantiene como tu columna normal a la derecha) --- */
        md:relative md:flex md:w-[350px] lg:w-[400px] md:h-full md:translate-y-0 md:rounded-none md:z-20 md:shadow-none
        
        /* --- ESTILOS PARA TELÉFONO (Se convierte en Panel Inferior) --- */
        fixed inset-x-0 bottom-0 z-50 flex-col bg-[var(--bg-base)] rounded-t-[24px]
        h-[85vh] shadow-[0_-10px_40px_rgba(0,0,0,0.3)]
        transition-transform duration-300 translate-y-full
    ">
    
    {{-- Manija de arrastre (solo visible en teléfono) --}}
    <div class="md:hidden w-full flex justify-center pt-3 pb-2 cursor-pointer" onclick="toggleOrdenMobile()">
        <div class="w-12 h-1.5 rounded-full bg-[var(--border-color)]"></div>
    </div>

    {{-- AQUÍ VA EL CÓDIGO ORIGINAL DE TU ORDEN (Lista de productos, subtotales, botón enviar) --}}
    <!-- Pega aquí el interior de tu sección de ticket actual -->

</section>

{{-- ============================================================
     MODAL NOTAS — teclado táctil mejorado
     ============================================================ --}}
<div id="modalNota" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="w-full sm:max-w-md max-h-[92vh] sm:max-h-[94vh] overflow-y-auto hide-scroll
                rounded-t-[24px] sm:rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)]
                p-4 sm:p-6 shadow-2xl ring-1 ring-black/5
                pb-[calc(1rem+env(safe-area-inset-bottom))] sm:pb-6">

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

        <div class="flex items-center justify-between mt-4 mb-2.5 px-0.5">
            <span class="text-[10px] font-black uppercase tracking-[0.15em] text-[var(--text-muted)]">Teclado</span>
            <button type="button" id="btnToggleTecladoNota" onclick="toggleTecladoNota()"
                class="min-w-[52px] min-h-[36px] px-3.5 py-2 rounded-full bg-gradient-to-b from-[var(--input-bg)] to-[var(--hover-bg)] border border-[var(--border-color)] text-[var(--text-main)] hover:border-blue-500/40 hover:shadow-sm active:scale-90 select-none text-[11px] font-black transition-all duration-150">
                123
            </button>
        </div>

        <div id="tecladoNotasLetras" class="grid grid-cols-10 gap-1 sm:gap-1.5">
            @foreach(['1','2','3','4','5','6','7','8','9','0','Q','W','E','R','T','Y','U','I','O','P','A','S','D','F','G','H','J','K','L','Ñ','Z','X','C','V','B','N','M','/','.'] as $key)
                <button type="button" onclick="insertarNotaCaracter('{{ $key }}')"
                    class="min-h-[40px] sm:min-h-[44px] py-2.5 rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-blue-500/30 hover:bg-[var(--hover-bg)] active:scale-90 active:bg-blue-500/10 select-none text-[var(--text-main)] text-[11px] sm:text-xs font-bold shadow-sm transition-all duration-100">
                    {{ $key }}
                </button>
            @endforeach
        </div>

        <div id="tecladoNotasSimbolos" class="hidden grid-cols-10 gap-1 sm:gap-1.5">
            @foreach(['!','"','#','$','%','&','/','(',')','=','¿','?','+','-','*','@',':',';',"'",'_','<','>','[',']','{','}','^','~','°','|'] as $key)
                <button type="button" onclick="insertarNotaCaracter('{{ $key }}')"
                    class="min-h-[40px] sm:min-h-[44px] py-2.5 rounded-lg bg-blue-500/[0.07] border border-blue-500/15 hover:border-blue-500/40 hover:bg-blue-500/[0.12] active:scale-90 select-none text-[var(--text-main)] text-[11px] sm:text-xs font-bold shadow-sm transition-all duration-100">
                    {{ $key }}
                </button>
            @endforeach
        </div>

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
    // --- Función para abrir/cerrar el carrito flotante en móvil ---
    function toggleOrdenMobile() {
        var ticket = document.getElementById('col-ticket'); 
        var backdrop = document.getElementById('backdropOrdenMobile');
        var body = document.body;

        if (!ticket || !backdrop) return;

        if (ticket.classList.contains('translate-y-full')) {
            ticket.classList.remove('translate-y-full');
            backdrop.classList.remove('opacity-0', 'pointer-events-none');
            backdrop.classList.add('opacity-100', 'pointer-events-auto');
            body.style.overflow = 'hidden'; // Bloquea el scroll de fondo
        } else {
            ticket.classList.add('translate-y-full');
            backdrop.classList.remove('opacity-100', 'pointer-events-auto');
            backdrop.classList.add('opacity-0', 'pointer-events-none');
            body.style.overflow = ''; // Restaura el scroll
        }
    }

    // --- Función para cerrar modales (Agregada para solucionar el bloqueo) ---
    function cerrarModal(idModal) {
        var modal = document.getElementById(idModal);
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // --- Script del catálogo adaptado para Toque Largo y Toque Corto ---
    (function () {
        var grid = document.getElementById('gridProductos');
        if (grid) {
            var pressTimer;
            var isLongPress = false;
            var startY = 0;
            var threshold = 10; // Píxeles de tolerancia para cancelar si hacen scroll

            // 1. Detectar cuando el mesero presiona el botón
            grid.addEventListener('touchstart', function (e) {
                var card = e.target.closest('.btn-producto');
                if (!card) return;
                
                isLongPress = false;
                startY = e.touches[0].clientY;

                // Iniciar contador de medio segundo (500ms)
                pressTimer = setTimeout(function () {
                    isLongPress = true;
                    
                    // Pequeña vibración en el teléfono para indicar que se activó la nota
                    if (navigator.vibrate) navigator.vibrate(50);
                    
                    var prodId = card.getAttribute('data-producto-id');
                    
                    // Agrega el producto a la cuenta
                    if (typeof agregarProducto === 'function' && prodId) {
                        agregarProducto(prodId);
                    }
                    mostrarToastAgregado(card);

                    // Abre directamente el modal de notas tras una mínima pausa
                    if (typeof agregarNota === 'function') {
                        setTimeout(agregarNota, 150);
                    } else {
                        var modal = document.getElementById('modalNota');
                        if (modal) {
                            modal.classList.remove('hidden');
                            modal.classList.add('flex');
                        }
                    }
                }, 500); 
            }, { passive: true });

            // 2. Si el dedo se mueve (hace scroll), cancelamos el toque largo
            grid.addEventListener('touchmove', function (e) {
                var currentY = e.touches[0].clientY;
                if (Math.abs(currentY - startY) > threshold) {
                    clearTimeout(pressTimer);
                }
            }, { passive: true });

            // 3. Si levanta el dedo antes de tiempo, cancelamos el toque largo
            grid.addEventListener('touchend', function (e) {
                clearTimeout(pressTimer);
            });

            // 4. Lógica para el clic normal (toque rápido en móvil o clic en PC)
            grid.addEventListener('click', function (e) {
                var card = e.target.closest('.btn-producto');
                if (!card) return;

                // Si fue un toque largo, ignoramos este clic para no agregar 2 veces el platillo
                if (isLongPress) {
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }

                // Fue un toque corto normal: solo agregamos el producto
                var prodId = card.getAttribute('data-producto-id');
                if (typeof agregarProducto === 'function' && prodId) {
                    agregarProducto(prodId);
                }
                mostrarToastAgregado(card);
            });

            // Función para la palomita verde
            function mostrarToastAgregado(card) {
                var check = document.createElement('div');
                check.className = 'toast-agregado';
                check.innerHTML = '<i class="fas fa-check"></i>';
                check.style.cssText = 'position:absolute;inset:0;display:flex;align-items:center;justify-content:center;' +
                    'background:rgba(16,185,129,0.85);color:#fff;font-size:22px;border-radius:16px;' +
                    'animation:toastPop .5s ease-out forwards;pointer-events:none;z-index:5;';
                card.style.position = 'relative';
                card.appendChild(check);
                setTimeout(function () { check.remove(); }, 500);
            }
        }

        // --- Lógica del Carrito Flotante ---
        var bar = document.getElementById('miniCartBar');
        var barCount = document.getElementById('miniCartCount');
        var barTotal = document.getElementById('miniCartTotal');
        var navBadge = document.getElementById('navBadgeOrden');
        var subtotalEl = document.getElementById('txtSubtotal');
        var listaTicket = document.getElementById('listaTicket');

        function refrescarMiniCart() {
            if (!bar) return;
            var cantidad = listaTicket ? listaTicket.children.length : 0;
            var total = subtotalEl ? subtotalEl.textContent.trim() : '$0.00';

            if (cantidad > 0) {
                bar.classList.remove('hidden');
                bar.classList.add('flex');
            } else {
                bar.classList.add('hidden');
                bar.classList.remove('flex');
            }
            if (barCount) barCount.textContent = cantidad;
            if (barTotal) barTotal.textContent = total;

            if (navBadge) {
                if (cantidad > 0) {
                    navBadge.textContent = cantidad;
                    navBadge.classList.remove('hidden');
                } else {
                    navBadge.classList.add('hidden');
                }
            }
        }

        if (listaTicket) {
            new MutationObserver(refrescarMiniCart).observe(listaTicket, { childList: true, subtree: false });
        }
        if (subtotalEl) {
            new MutationObserver(refrescarMiniCart).observe(subtotalEl, { characterData: true, childList: true, subtree: true });
        }
        document.addEventListener('DOMContentLoaded', refrescarMiniCart);
        refrescarMiniCart();
    })();

    // --- Script del teclado numérico/alfabético ---
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

<style>
    @keyframes toastPop {
        0% { opacity: 0; transform: scale(0.85); }
        20% { opacity: 1; transform: scale(1); }
        75% { opacity: 1; }
        100% { opacity: 0; }
    }
</style>