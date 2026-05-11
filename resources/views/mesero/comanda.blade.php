<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- MUY IMPORTANTE: El token de seguridad para poder mandar datos a Laravel --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Comanda | Ollintem Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-base: #050505; 
            --bg-panel: #0E0E12; 
            --border-color: #1F1F24; 
            --text-main: #FFFFFF;
            --text-muted: #71717A;
        }

        body.modo-crema {
            --bg-base: #F4F4F5; 
            --bg-panel: #FFFFFF;
            --border-color: rgba(0, 0, 0, 0.08);
            --text-main: #09090B;
            --text-muted: #A1A1AA;
        }

        body { 
            background-color: var(--bg-base);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            overflow: hidden; 
            transition: background-color 0.4s ease, color 0.4s ease;
        }
        
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        
        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(5px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-item { animation: fade-in-up 0.2s ease-out forwards; }
        
        aside, section, main, div, button {
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
    </style>
</head>
<body class="h-screen w-full flex selection:bg-[#3B82F6]/30">

    <script>if (localStorage.getItem('tema-ollintem') === 'crema') document.body.classList.add('modo-crema');</script>

    {{-- COLUMNA 1: ACCIONES RÁPIDAS --}}
    <aside class="w-[260px] h-full flex flex-col bg-[var(--bg-base)] border-r border-[var(--border-color)] p-4 z-20">
        <button onclick="window.location.href='{{ route('mesero.dashboard') ?? '#' }}'" class="w-full h-12 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-rose-500/10 hover:text-rose-500 hover:border-rose-500/30 transition-all active:scale-95 outline-none mb-6">
            <i class="fas fa-times"></i> Cerrar
        </button>

        <div class="flex items-center justify-between px-1 mb-5">
            {{-- Asumimos que le pasas la variable $mesa desde tu controlador --}}
            <h3 class="text-2xl font-black tracking-tighter text-[var(--text-main)]">Mesa {{ $mesa->numero ?? '12M' }}</h3>
            <div class="flex gap-1">
                <button onclick="toggleTheme()" class="w-9 h-9 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] flex items-center justify-center hover:border-[#3B82F6]/50 transition-all outline-none shadow-sm group">
                    <i id="themeIcon" class="fas fa-sun text-sm text-[var(--text-muted)] group-hover:scale-110 transition-transform"></i>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 flex-1 overflow-y-auto hide-scroll pb-4 pr-1">
            <button id="btn-personas" onclick="ajustarPersonas()" class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#3B82F6]/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-user-friends text-[#3B82F6] mb-2 text-xl drop-shadow-[0_0_8px_rgba(59,130,246,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-[#3B82F6]">Personas</span>
                <span id="txtPersonas" class="text-[10px] font-bold text-[#3B82F6] mt-0.5">4</span>
            </button>
            <button onclick="irAPagar()" class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-emerald-500/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-credit-card text-emerald-500 mb-2 text-xl drop-shadow-[0_0_8px_rgba(16,185,129,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-emerald-500">Pagar</span>
            </button>
            <button onclick="agregarNota()" class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#8B5CF6]/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-comment-alt text-[#8B5CF6] mb-2 text-xl drop-shadow-[0_0_8px_rgba(139,92,246,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-[#8B5CF6]">Nota</span>
            </button>
            <button onclick="aplicarDescuento()" class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-rose-500/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-percent text-rose-500 mb-2 text-xl drop-shadow-[0_0_8px_rgba(244,63,94,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-rose-500">Desc.</span>
            </button>
            <button onclick="abrirCajon()" class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-amber-400/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-cash-register text-amber-400 mb-2 text-xl drop-shadow-[0_0_8px_rgba(251,191,36,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-amber-400">Cajón</span>
            </button>
            <button onclick="mostrarPromociones()" class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#3B82F6]/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-tag text-[#3B82F6] mb-2 text-xl drop-shadow-[0_0_8px_rgba(59,130,246,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-[#3B82F6]">Promos</span>
            </button>
            <button onclick="dividirItemActivo()" class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[var(--text-muted)] transition-all active:scale-95 group outline-none">
                <i class="fas fa-cut text-[var(--text-muted)] mb-2 text-xl group-hover:text-[var(--text-main)] transition-colors"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)] group-hover:text-[var(--text-main)]">Dividir</span>
            </button>
            <button onclick="eliminarItemSeleccionado()" class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-rose-500/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-trash text-rose-500 mb-2 text-xl drop-shadow-[0_0_8px_rgba(244,63,94,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-rose-500">Eliminar</span>
            </button>
        </div>

        <button onclick="limpiarTicket()" class="mt-2 w-full h-[55px] rounded-xl border border-rose-500/30 bg-[var(--bg-panel)] hover:bg-rose-500 hover:text-white text-rose-500 transition-all active:scale-95 outline-none">
            <span class="text-[10px] font-black uppercase tracking-widest">Eliminar Todo</span>
        </button>
    </aside>

    {{-- COLUMNA 2: TICKET / COMANDA --}}
    <section class="w-[380px] h-full flex flex-col bg-[var(--bg-panel)] border-r border-[var(--border-color)] relative z-10 shadow-2xl">
        <div class="p-5 border-b border-[var(--border-color)]">
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h2 class="text-2xl font-black tracking-tighter">Mesa {{ $mesa->numero ?? '12M' }}</h2>
                    <span id="badgePersonas" class="inline-flex items-center gap-2 mt-2 rounded-full bg-[#1f2937] px-3 py-1 text-[10px] uppercase tracking-[0.2em] text-[#93c5fd] font-black">Personas: 4</span>
                </div>
                <span class="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">{{ auth()->user()->nombre ?? 'GAEL' }}</span>
            </div>

            <div class="flex gap-1 bg-[var(--bg-base)] p-1 rounded-xl border border-[var(--border-color)]">
                <button class="tab-btn flex-1 py-2.5 rounded-lg bg-[#3B82F6] text-white text-[9px] font-black uppercase tracking-widest shadow-sm outline-none transition-all">
                    <i class="fas fa-plus mr-1"></i> Nueva Orden
                </button>
                <button class="tab-btn flex-1 py-2.5 rounded-lg text-[var(--text-muted)] text-[9px] font-black uppercase tracking-widest outline-none hover:text-[var(--text-main)]">
                    Enviados
                </button>
                <button class="tab-btn flex-1 py-2.5 rounded-lg text-[var(--text-muted)] text-[9px] font-black uppercase tracking-widest outline-none hover:text-[var(--text-main)]">
                    Comanda
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto hide-scroll p-5 flex flex-col relative bg-[var(--bg-base)]" id="contenedorTicket">
            <div class="grid grid-cols-[40px_1fr_60px] gap-2 text-[9px] font-black uppercase tracking-[0.15em] text-[var(--text-muted)] mb-4 pb-3 border-b border-[var(--border-color)]">
                <span class="text-center">Cant.</span>
                <span>Descripción</span>
                <span class="text-right">Importe</span>
            </div>
            <div id="listaTicket" class="flex flex-col gap-2"></div>
            <div id="estadoVacio" class="flex-1 flex flex-col items-center justify-center opacity-30 mt-10 transition-opacity duration-300">
                <i class="fas fa-utensils text-4xl mb-4 text-[var(--text-muted)]"></i>
                <p class="text-xs font-bold text-[var(--text-muted)] text-center leading-relaxed">Selecciona productos<br>del catálogo</p>
            </div>
        </div>

        <div class="p-6 bg-[var(--bg-panel)] border-t border-[var(--border-color)] mt-auto relative z-20">
            <div class="flex justify-between items-center mb-2">
                <span class="text-[11px] font-bold text-[var(--text-muted)]">Subtotal:</span>
                <span class="text-sm font-bold text-[var(--text-main)]" id="txtSubtotal">$0.00</span>
            </div>
            <div class="flex justify-between items-center mb-4">
                <span class="text-[11px] font-bold text-[var(--text-muted)]">IVA (16%):</span>
                <span class="text-sm font-bold text-[var(--text-main)]" id="txtIva">$0.00</span>
            </div>
            <div class="flex justify-between items-end mb-6">
                <span class="text-base font-black tracking-widest uppercase text-[var(--text-main)]">Total:</span>
                <span class="text-4xl font-black text-[#3B82F6] tracking-tighter leading-none" id="txtTotal">$0.00</span>
            </div>

            {{-- BOTÓN ACTUALIZADO CON ID Y ONCLICK --}}
            <button id="btn-enviar" onclick="enviarACocina()" class="w-full h-[55px] rounded-xl bg-[#3B82F6] text-white text-[14px] font-black tracking-widest uppercase transition-all shadow-[0_10px_20px_-10px_rgba(59,130,246,0.6)] hover:bg-[#2563EB] active:scale-95 outline-none flex items-center justify-center gap-3">
                <i class="fas fa-paper-plane"></i>
                <span>Enviar a Cocina</span>
            </button>
        </div>
    </section>

    {{-- COLUMNA 3: CATÁLOGO DE PRODUCTOS --}}
    <main class="flex-1 flex flex-col bg-[var(--bg-base)] relative overflow-hidden">
        <div class="px-8 pt-8 pb-5 flex gap-3 overflow-x-auto hide-scroll relative z-10 border-b border-[var(--border-color)] bg-[var(--bg-panel)]" id="menuCategorias"></div>
        <div class="flex-1 p-8 overflow-y-auto hide-scroll grid grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-5 content-start relative z-10" onclick="deseleccionarTicket()" id="gridProductos"></div>

        <div id="barraModificadores" class="hidden h-[80px] bg-[#3B82F6]/5 border-t border-[#3B82F6]/20 flex items-center px-6 relative z-10 transition-all">
            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-[#3B82F6] mr-4 whitespace-nowrap">Opciones:</span>
            <div id="contenedorBotonesModificadores" class="flex gap-2 overflow-x-auto hide-scroll flex-1"></div>
            <button onclick="deseleccionarTicket()" class="ml-4 w-10 h-10 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-rose-500 hover:border-rose-500/50 transition-all outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </main>

    {{-- MODAL NOTA --}}
    <div id="modalNota" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-[#3B82F6] font-black">Nota de platillo</p>
                    <h2 class="text-xl font-black mt-2">Agrega un comentario</h2>
                </div>
                <button onclick="cerrarModal('modalNota')" class="text-[var(--text-muted)] hover:text-white outline-none"><i class="fas fa-times"></i></button>
            </div>
            <textarea id="notaTextarea" rows="5" class="w-full rounded-3xl border border-[var(--border-color)] bg-[var(--bg-base)] p-4 text-sm text-[var(--text-main)] outline-none resize-none" placeholder="Ej. Sin cebolla, extra salsa, cocinar bien"></textarea>
            <div class="mt-5 flex gap-3 justify-end">
                <button onclick="cerrarModal('modalNota')" class="px-5 py-3 rounded-2xl border border-[var(--border-color)] text-[var(--text-muted)] hover:text-white transition-all">Cancelar</button>
                <button onclick="guardarNota()" class="px-5 py-3 rounded-2xl bg-[#3B82F6] text-white font-black uppercase tracking-[0.15em] transition-all">Guardar nota</button>
            </div>
        </div>
    </div>

    {{-- MODAL DESCUENTO --}}
    <div id="modalDescuento" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-[#ef4444] font-black">Descuento</p>
                    <h2 class="text-xl font-black mt-2">Aplicar porcentaje</h2>
                </div>
                <button onclick="cerrarModal('modalDescuento')" class="text-[var(--text-muted)] hover:text-white outline-none"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-4">
                <label class="block text-[11px] uppercase tracking-[0.2em] text-[var(--text-muted)]">Porcentaje</label>
                <input id="descuentoInput" type="number" min="0" max="100" step="0.1" class="w-full rounded-3xl border border-[var(--border-color)] bg-[var(--bg-base)] p-4 text-sm text-[var(--text-main)] outline-none" placeholder="10">
                <p class="text-[11px] text-[var(--text-muted)]">Se aplicará sobre el subtotal antes del IVA.</p>
            </div>
            <div class="mt-5 flex gap-3 justify-end">
                <button onclick="cerrarModal('modalDescuento')" class="px-5 py-3 rounded-2xl border border-[var(--border-color)] text-[var(--text-muted)] hover:text-white transition-all">Cancelar</button>
                <button onclick="guardarDescuento()" class="px-5 py-3 rounded-2xl bg-[#ef4444] text-white font-black uppercase tracking-[0.15em] transition-all">Aplicar</button>
            </div>
        </div>
    </div>

    {{-- MODAL PERSONAS --}}
    <div id="modalPersonas" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-3xl bg-[var(--bg-panel)] border border-[var(--border-color)] p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.25em] text-[#3B82F6] font-black">Personas</p>
                    <h2 class="text-xl font-black mt-2">Ajustar comensales</h2>
                </div>
                <button onclick="cerrarModal('modalPersonas')" class="text-[var(--text-muted)] hover:text-white outline-none"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-4">
                <label class="block text-[11px] uppercase tracking-[0.2em] text-[var(--text-muted)]">Cantidad</label>
                <input id="personasInput" type="number" min="1" class="w-full rounded-3xl border border-[var(--border-color)] bg-[var(--bg-base)] p-4 text-sm text-[var(--text-main)] outline-none" placeholder="4">
                <p class="text-[11px] text-[var(--text-muted)]">Define cuántos comensales tiene la mesa actualmente.</p>
            </div>
            <div class="mt-5 flex gap-3 justify-end">
                <button onclick="cerrarModal('modalPersonas')" class="px-5 py-3 rounded-2xl border border-[var(--border-color)] text-[var(--text-muted)] hover:text-white transition-all">Cancelar</button>
                <button onclick="guardarPersonas()" class="px-5 py-3 rounded-2xl bg-[#3B82F6] text-white font-black uppercase tracking-[0.15em] transition-all">Guardar</button>
            </div>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const body = document.body;
            body.classList.toggle('modo-crema');
            const esCrema = body.classList.contains('modo-crema');
            localStorage.setItem('tema-ollintem', esCrema ? 'crema' : 'negro');
            actualizarIconoTema(esCrema);
        }

        function actualizarIconoTema(esCrema) {
            const icon = document.getElementById('themeIcon');
            if (icon) {
                icon.className = esCrema 
                    ? 'fas fa-moon text-sm text-[#3B82F6] group-hover:scale-110 transition-transform'
                    : 'fas fa-sun text-sm text-amber-400 group-hover:scale-110 transition-transform';
            }
        }
        document.addEventListener('DOMContentLoaded', () => actualizarIconoTema(document.body.classList.contains('modo-crema')));

        const categoriasDB = @json($categorias ?? []);
        const productosDB = @json($productos ?? []);

        function renderizarMenu() {
            const menuCat = document.getElementById('menuCategorias');
            const gridProd = document.getElementById('gridProductos');
            
            menuCat.innerHTML = `<button onclick="filtrarCategoria('Todos', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[#3B82F6] text-white text-[10px] font-black uppercase tracking-widest shadow-[0_0_15px_rgba(59,130,246,0.4)] whitespace-nowrap outline-none transition-all">Todos</button>`;
            
            if(categoriasDB.length > 0) {
                categoriasDB.forEach(cat => {
                    menuCat.innerHTML += `<button onclick="filtrarCategoria('${cat.nombre}', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-muted)] text-[10px] font-bold uppercase tracking-widest hover:border-[#3B82F6]/50 hover:text-[var(--text-main)] transition-all whitespace-nowrap outline-none">${cat.nombre}</button>`;
                });
            }

            gridProd.innerHTML = '';
            
            if(productosDB.length > 0) {
                productosDB.forEach(prod => {
                    const catNombre = prod.categoria ? prod.categoria.nombre : 'Sin Categoría';
                    const precioNum = parseFloat(prod.precio) || 0;
                    const modsJSON = prod.modificadores ? JSON.stringify(prod.modificadores).replace(/'/g, "\\'") : '[]';

                    gridProd.innerHTML += `
                        <div data-categoria-item="${catNombre}" onclick='agregarAlTicket(${prod.id}, "${prod.nombre}", ${precioNum}, "${catNombre}", ${modsJSON}); event.stopPropagation();' class="producto-card group bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-2xl p-4 flex flex-col hover:border-[#3B82F6]/50 transition-all cursor-pointer h-[200px]">
                            <div class="w-full flex-1 rounded-xl bg-[var(--bg-base)] mb-4 flex flex-col items-center justify-center relative">
                                <span class="absolute top-2 right-2 text-[8px] font-black uppercase tracking-widest text-[var(--text-muted)] border border-[var(--border-color)] px-2 py-1 rounded">${catNombre}</span>
                                <i class="fas fa-utensils text-5xl text-[var(--border-color)] group-hover:text-[#3B82F6]/20 transition-colors"></i>
                            </div>
                            <h3 class="text-sm font-bold text-[var(--text-main)] tracking-tight mb-1">${prod.nombre}</h3>
                            <span class="text-lg font-black text-[#3B82F6]">$${precioNum.toFixed(2)}</span>
                        </div>
                    `;
                });
            } else {
                gridProd.innerHTML = `<div class="col-span-full text-center text-[var(--text-muted)] text-sm font-bold mt-10">No hay productos registrados en el menú aún.</div>`;
            }
        }
        
        document.addEventListener('DOMContentLoaded', renderizarMenu);

        function filtrarCategoria(nombreCat, btn) {
            document.querySelectorAll('.cat-btn').forEach(el => el.className = "cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-muted)] text-[10px] font-bold uppercase tracking-widest hover:border-[#3B82F6]/50 hover:text-[var(--text-main)] transition-all whitespace-nowrap outline-none");
            btn.className = "cat-btn px-6 py-2.5 rounded-full bg-[#3B82F6] text-white text-[10px] font-black uppercase tracking-widest shadow-[0_0_15px_rgba(59,130,246,0.4)] whitespace-nowrap outline-none transition-all";
            
            const todasLasCards = document.querySelectorAll('.producto-card');
            todasLasCards.forEach(card => {
                if (nombreCat === 'Todos' || card.getAttribute('data-categoria-item') === nombreCat) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        let ticketSubtotal = 0;
        let itemActivo = null; 
        let contadorItems = 0; 

        const listaTicket = document.getElementById('listaTicket');
        const estadoVacio = document.getElementById('estadoVacio');
        const barraModificadores = document.getElementById('barraModificadores');
        const contenedorBotonesModificadores = document.getElementById('contenedorBotonesModificadores');
        
        function agregarAlTicket(id, nombre, precio, categoria, arrayModificadores = []) {
            estadoVacio.classList.add('hidden');

            const modsString = JSON.stringify(arrayModificadores).replace(/'/g, "&#39;").replace(/"/g, "&quot;");
            const precioUnitario = parseFloat(precio);

            const existingItem = Array.from(listaTicket.querySelectorAll('.ticket-item')).find(item => {
                return parseInt(item.dataset.productoId, 10) === id
                    && item.dataset.modificadores === modsString;
            });

            if (existingItem) {
                const cantidadSpan = existingItem.querySelector('.cantidad-platillo');
                const cantidad = parseInt(cantidadSpan.innerText, 10) + 1;
                cantidadSpan.innerText = cantidad;
                existingItem.dataset.cantidad = cantidad;

                const precioSpan = existingItem.querySelector('.precio-platillo');
                precioSpan.innerText = '$' + (precioUnitario * cantidad).toFixed(2);
                existingItem.dataset.precio = precioUnitario;

                ticketSubtotal += precioUnitario;
                actualizarTotales();
                seleccionarItem(existingItem.id);
                return;
            }

            contadorItems++;
            const itemId = 'ticket-item-' + contadorItems;

            const itemHTML = `
                <div id="${itemId}" data-producto-id="${id}" data-cantidad="1" data-precio="${precioUnitario}" data-modificadores="${modsString}" class="ticket-item animate-item bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-xl p-3 grid grid-cols-[40px_1fr_60px] gap-2 items-start cursor-pointer transition-all" onclick="seleccionarItem('${itemId}')">
                    <div class="flex justify-center mt-1">
                        <span class="cantidad-platillo w-6 h-6 rounded bg-[#3B82F6]/10 text-[#3B82F6] text-[10px] font-black flex items-center justify-center">1</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-[var(--text-main)] leading-tight nombre-platillo">${nombre}</span>
                        <div class="modificadores-lista flex flex-wrap gap-1 mt-1 empty:hidden"></div>
                        <button onclick="eliminarItemFila(this); event.stopPropagation();" class="text-[9px] text-rose-500 font-bold uppercase tracking-widest text-left mt-2 hidden hover:underline btn-eliminar">Quitar platillo</button>
                    </div>
                    <div class="text-right mt-1">
                        <span class="text-sm font-black text-[var(--text-main)] precio-platillo">$${precioUnitario.toFixed(2)}</span>
                    </div>
                </div>
            `;

            listaTicket.insertAdjacentHTML('beforeend', itemHTML);
            ticketSubtotal += precioUnitario;
            actualizarTotales();

            seleccionarItem(itemId);
            listaTicket.parentElement.scrollTop = listaTicket.parentElement.scrollHeight;
        }

        function seleccionarItem(id) {
            deseleccionarTicket(); 
            
            itemActivo = document.getElementById(id);
            if(itemActivo) {
                itemActivo.classList.add('border-[#3B82F6]', 'shadow-[0_0_15px_rgba(59,130,246,0.15)]');
                itemActivo.querySelector('.btn-eliminar').classList.remove('hidden');
                
                const modsString = itemActivo.getAttribute('data-modificadores');
                const modificadoresParaPintar = JSON.parse(modsString || '[]');
                
                contenedorBotonesModificadores.innerHTML = '';
                
                if(modificadoresParaPintar.length > 0) {
                    modificadoresParaPintar.forEach(mod => {
                        const nombreMod = mod.nombre || mod.descripcion || mod; 
                        const btnHTML = `<button onclick="agregarModificadorFijo('${nombreMod}')" class="px-4 py-2 rounded-xl border border-[#3B82F6]/30 bg-[var(--bg-panel)] text-[10px] font-bold text-[#3B82F6] hover:bg-[#3B82F6] hover:text-white transition-all whitespace-nowrap outline-none active:scale-95 shadow-sm">${nombreMod}</button>`;
                        contenedorBotonesModificadores.insertAdjacentHTML('beforeend', btnHTML);
                    });
                    barraModificadores.classList.remove('hidden');
                } else {
                    barraModificadores.classList.add('hidden');
                }
            }
        }

        function deseleccionarTicket() {
            document.querySelectorAll('.ticket-item').forEach(el => {
                el.classList.remove('border-[#3B82F6]', 'shadow-[0_0_15px_rgba(59,130,246,0.15)]');
                if(el.querySelector('.btn-eliminar')) {
                    el.querySelector('.btn-eliminar').classList.add('hidden');
                }
            });
            itemActivo = null;
            barraModificadores.classList.add('hidden');
        }

        function agregarModificadorFijo(texto) {
            if (itemActivo) {
                const contenedorList = itemActivo.querySelector('.modificadores-lista');
                const pillHTML = `<span class="text-[9px] bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-muted)] px-1.5 py-0.5 rounded shadow-sm">✓ ${texto}</span>`;
                contenedorList.insertAdjacentHTML('beforeend', pillHTML);
            }
        }

        function eliminarItemFila(btn) {
            const fila = btn.closest('.ticket-item');
            const cantidad = parseInt(fila.dataset.cantidad, 10) || 1;
            const precioUnitario = parseFloat(fila.dataset.precio) || 0;
            const totalFila = cantidad * precioUnitario;
            fila.remove();
            ticketSubtotal -= totalFila;
            actualizarTotales();
            deseleccionarTicket();
            
            if(listaTicket.children.length === 0) {
                estadoVacio.classList.remove('hidden');
            }
        }

        function actualizarTotales() {
            const iva = ticketSubtotal * 0.16;
            const total = ticketSubtotal + iva;
            document.getElementById('txtSubtotal').innerText = '$' + ticketSubtotal.toFixed(2);
            document.getElementById('txtIva').innerText = '$' + iva.toFixed(2);
            document.getElementById('txtTotal').innerText = '$' + total.toFixed(2);
        }

        function limpiarTicket() {
            listaTicket.innerHTML = '';
            estadoVacio.classList.remove('hidden');
            ticketSubtotal = 0;
            descuentoPorcentaje = 0;
            notaGeneral = '';
            actualizarTotales();
            deseleccionarTicket();
        }

        function ajustarPersonas() {
            document.getElementById('personasInput').value = numeroPersonas;
            document.getElementById('modalPersonas').classList.remove('hidden');
            document.getElementById('personasInput').focus();
        }

        function guardarPersonas() {
            const valor = parseInt(document.getElementById('personasInput').value, 10);
            if (isNaN(valor) || valor <= 0) {
                alert('Ingresa una cantidad de personas válida.');
                return;
            }
            numeroPersonas = valor;
            document.getElementById('txtPersonas').innerText = numeroPersonas;
            document.getElementById('badgePersonas').innerText = 'Personas: ' + numeroPersonas;
            cerrarModal('modalPersonas');
        }

        function irAPagar() {
            window.location.href = '{{ route('admin.caja.cobrar', $mesa->id) }}';
        }

        function agregarNota() {
            if (!itemActivo) {
                alert('Selecciona un platillo para agregar una nota.');
                return;
            }
            document.getElementById('notaTextarea').value = '';
            document.getElementById('modalNota').classList.remove('hidden');
            document.getElementById('notaTextarea').focus();
        }

        function aplicarDescuento() {
            document.getElementById('descuentoInput').value = descuentoPorcentaje;
            document.getElementById('modalDescuento').classList.remove('hidden');
            document.getElementById('descuentoInput').focus();
        }

        function abrirCajon() {
            alert('Cajón de efectivo activado (simulado).');
        }

        function mostrarPromociones() {
            window.location.href = '{{ route('admin.promociones.index') }}';
        }

        function dividirItemActivo() {
            if (!itemActivo) {
                alert('Selecciona un platillo para dividir.');
                return;
            }
            const cantidadActual = parseInt(itemActivo.dataset.cantidad, 10) || 1;
            if (cantidadActual <= 1) {
                alert('No se puede dividir un platillo con cantidad 1.');
                return;
            }
            const precioUnitario = parseFloat(itemActivo.dataset.precio) || 0;
            itemActivo.dataset.cantidad = cantidadActual - 1;
            itemActivo.querySelector('.cantidad-platillo').innerText = cantidadActual - 1;
            itemActivo.querySelector('.precio-platillo').innerText = '$' + ((cantidadActual - 1) * precioUnitario).toFixed(2);

            contadorItems++;
            const itemId = 'ticket-item-' + contadorItems;
            const nombre = itemActivo.querySelector('.nombre-platillo').innerText;
            const modificadores = itemActivo.querySelector('.modificadores-lista').innerHTML;
            const itemHTML = `
                <div id="${itemId}" data-producto-id="${itemActivo.dataset.productoId}" data-cantidad="1" data-precio="${precioUnitario}" data-modificadores="${itemActivo.dataset.modificadores}" class="ticket-item animate-item bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-xl p-3 grid grid-cols-[40px_1fr_60px] gap-2 items-start cursor-pointer transition-all" onclick="seleccionarItem('${itemId}')">
                    <div class="flex justify-center mt-1">
                        <span class="cantidad-platillo w-6 h-6 rounded bg-[#3B82F6]/10 text-[#3B82F6] text-[10px] font-black flex items-center justify-center">1</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-[var(--text-main)] leading-tight nombre-platillo">${nombre}</span>
                        <div class="modificadores-lista flex flex-wrap gap-1 mt-1 empty:hidden">${modificadores}</div>
                        <button onclick="eliminarItemFila(this); event.stopPropagation();" class="text-[9px] text-rose-500 font-bold uppercase tracking-widest text-left mt-2 hidden hover:underline btn-eliminar">Quitar platillo</button>
                    </div>
                    <div class="text-right mt-1">
                        <span class="text-sm font-black text-[var(--text-main)] precio-platillo">$${precioUnitario.toFixed(2)}</span>
                    </div>
                </div>
            `;
            listaTicket.insertAdjacentHTML('beforeend', itemHTML);
            actualizarTotales();
        }

        function eliminarItemSeleccionado() {
            if (!itemActivo) {
                alert('Selecciona un platillo para eliminar.');
                return;
            }
            const cantidad = parseInt(itemActivo.dataset.cantidad, 10) || 1;
            const precioUnitario = parseFloat(itemActivo.dataset.precio) || 0;
            const totalFila = cantidad * precioUnitario;
            itemActivo.remove();
            ticketSubtotal -= totalFila;
            actualizarTotales();
            deseleccionarTicket();
            if (listaTicket.children.length === 0) {
                estadoVacio.classList.remove('hidden');
            }
        }

        let numeroPersonas = 4;
        let descuentoPorcentaje = 0;
        let notaGeneral = '';

        document.addEventListener('DOMContentLoaded', function() {
            const badge = document.getElementById('badgePersonas');
            if (badge) {
                badge.innerText = 'Personas: ' + numeroPersonas;
            }
        });

        function cerrarModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function guardarNota() {
            const nota = document.getElementById('notaTextarea').value.trim();
            if (!itemActivo) {
                cerrarModal('modalNota');
                return;
            }
            if (nota.length === 0) {
                alert('La nota no puede estar vacía.');
                return;
            }
            const notaPill = `<span class="text-[9px] bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-muted)] px-1.5 py-0.5 rounded shadow-sm">✎ ${nota}</span>`;
            const contenedorList = itemActivo.querySelector('.modificadores-lista');
            contenedorList.insertAdjacentHTML('beforeend', notaPill);
            itemActivo.dataset.nota = nota;
            notaGeneral = nota;
            cerrarModal('modalNota');
        }

        function guardarDescuento() {
            const valor = parseFloat(document.getElementById('descuentoInput').value);
            if (isNaN(valor) || valor < 0 || valor > 100) {
                alert('Ingresa un porcentaje válido entre 0 y 100.');
                return;
            }
            descuentoPorcentaje = valor;
            actualizarTotales();
            cerrarModal('modalDescuento');
        }

        function obtenerSubtotalConDescuento() {
            const subtotalConDescuento = Math.max(0, ticketSubtotal - (ticketSubtotal * (descuentoPorcentaje / 100)));
            return subtotalConDescuento;
        }

        function actualizarTotales() {
            const subtotalConDescuento = obtenerSubtotalConDescuento();
            const iva = subtotalConDescuento * 0.16;
            const total = subtotalConDescuento + iva;
            document.getElementById('txtSubtotal').innerText = '$' + subtotalConDescuento.toFixed(2);
            document.getElementById('txtIva').innerText = '$' + iva.toFixed(2);
            document.getElementById('txtTotal').innerText = '$' + total.toFixed(2);
        }

        // ==========================================
        // LA MAGIA: ENVIAR A COCINA Y OCUPAR MESA
        // ==========================================
        function enviarACocina() {
            const itemsHTML = document.querySelectorAll('.ticket-item');
            if(itemsHTML.length === 0) {
                alert("¡Debes agregar al menos un platillo a la orden!");
                return;
            }

            // 1. Recolectamos todo lo que está en el ticket
            const platillosData = [];
            itemsHTML.forEach(item => {
                const nombre = item.querySelector('.nombre-platillo').innerText;
                const precioUnitario = parseFloat(item.dataset.precio);
                const cantidad = parseInt(item.dataset.cantidad, 10);
                const productoId = parseInt(item.dataset.productoId, 10);

                const modsElementos = item.querySelectorAll('.modificadores-lista span');
                const mods = [];
                modsElementos.forEach(m => mods.push(m.innerText.replace('✓ ', '')));

                platillosData.push({ id: productoId, nombre: nombre, cantidad: cantidad, precio: precioUnitario, notas: mods.join(', '), modificadores: mods });
            });

            // Cambiamos el botón para que se vea pro
            const btn = document.getElementById('btn-enviar');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Mandando orden...</span>';
            btn.disabled = true;

            // Tomamos el ID de la mesa directamente de la vista
            const mesaId = {{ $mesa->id ?? 1 }};

            // 2. Mandamos la info al Controlador en Laravel
            fetch('/mesero/comanda/enviar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    mesa_id: mesaId,
                    platillos: platillosData,
                    total: obtenerSubtotalConDescuento() + (obtenerSubtotalConDescuento() * 0.16),
                    personas: numeroPersonas,
                    descuento_porcentaje: descuentoPorcentaje,
                    nota_general: notaGeneral
                })
            })
            .then(async res => {
                const data = await res.json().catch(() => null);
                if (!res.ok) {
                    const errorMessage = data?.message || 'Error al enviar la orden. Intenta de nuevo.';
                    throw new Error(errorMessage);
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    alert("✅ ¡Orden enviada a cocina! La mesa ya está ocupada.");
                    window.location.href = '/mesero/dashboard';
                } else {
                    throw new Error(data.message || 'Error al enviar la orden.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || "Hubo un problema al enviar la orden. Revisa la consola.");
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> <span>Enviar a Cocina</span>';
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>