<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    {{-- ========================================== --}}
    {{-- COLUMNA 1: ACCIONES RÁPIDAS                  --}}
    {{-- ========================================== --}}
    <aside class="w-[260px] h-full flex flex-col bg-[var(--bg-base)] border-r border-[var(--border-color)] p-4 z-20">
        
        <button onclick="window.location.href='{{ route('mesero.dashboard') }}'" class="w-full h-12 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-main)] font-black text-xs uppercase tracking-widest flex items-center justify-center gap-2 hover:bg-rose-500/10 hover:text-rose-500 hover:border-rose-500/30 transition-all active:scale-95 outline-none mb-6">
            <i class="fas fa-times"></i> Cerrar
        </button>

        <div class="flex items-center justify-between px-1 mb-5">
            <h3 class="text-2xl font-black tracking-tighter text-[var(--text-main)]">Mesa 12M</h3>
            
            <div class="flex gap-1">
                {{-- BOTÓN TEMA CLARO/OSCURO --}}
                <button onclick="toggleTheme()" class="w-9 h-9 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] flex items-center justify-center hover:border-[#3B82F6]/50 transition-all outline-none shadow-sm group">
                    <i id="themeIcon" class="fas fa-sun text-sm text-[var(--text-muted)] group-hover:scale-110 transition-transform"></i>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 flex-1 overflow-y-auto hide-scroll pb-4 pr-1">
            <button class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#3B82F6]/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-user-friends text-[#3B82F6] mb-2 text-xl drop-shadow-[0_0_8px_rgba(59,130,246,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-[#3B82F6]">Personas</span>
                <span class="text-[10px] font-bold text-[#3B82F6] mt-0.5">4</span>
            </button>
            <button class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-emerald-500/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-credit-card text-emerald-500 mb-2 text-xl drop-shadow-[0_0_8px_rgba(16,185,129,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-emerald-500">Pagar</span>
            </button>
            <button class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#8B5CF6]/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-comment-alt text-[#8B5CF6] mb-2 text-xl drop-shadow-[0_0_8px_rgba(139,92,246,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-[#8B5CF6]">Nota</span>
            </button>
            <button class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-rose-500/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-percent text-rose-500 mb-2 text-xl drop-shadow-[0_0_8px_rgba(244,63,94,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-rose-500">Desc.</span>
            </button>
            <button class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-amber-400/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-cash-register text-amber-400 mb-2 text-xl drop-shadow-[0_0_8px_rgba(251,191,36,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-amber-400">Cajón</span>
            </button>
            <button class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[#3B82F6]/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-tag text-[#3B82F6] mb-2 text-xl drop-shadow-[0_0_8px_rgba(59,130,246,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-[#3B82F6]">Promos</span>
            </button>
            <button class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-[var(--text-muted)] transition-all active:scale-95 group outline-none">
                <i class="fas fa-cut text-[var(--text-muted)] mb-2 text-xl group-hover:text-[var(--text-main)] transition-colors"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)] group-hover:text-[var(--text-main)]">Dividir</span>
            </button>
            <button class="flex flex-col items-center justify-center h-[95px] rounded-2xl bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-rose-500/50 transition-all active:scale-95 group outline-none">
                <i class="fas fa-trash text-rose-500 mb-2 text-xl drop-shadow-[0_0_8px_rgba(244,63,94,0.4)]"></i>
                <span class="text-[9px] font-black uppercase tracking-widest text-rose-500">Eliminar</span>
            </button>
        </div>

        <button onclick="limpiarTicket()" class="mt-2 w-full h-[55px] rounded-xl border border-rose-500/30 bg-[var(--bg-panel)] hover:bg-rose-500 hover:text-white text-rose-500 transition-all active:scale-95 outline-none">
            <span class="text-[10px] font-black uppercase tracking-widest">Eliminar Todo</span>
        </button>
    </aside>

    {{-- ========================================== --}}
    {{-- COLUMNA 2: TICKET / COMANDA                  --}}
    {{-- ========================================== --}}
    <section class="w-[380px] h-full flex flex-col bg-[var(--bg-panel)] border-r border-[var(--border-color)] relative z-10 shadow-2xl">
        <div class="p-5 border-b border-[var(--border-color)]">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-2xl font-black tracking-tighter">Mesa 12M</h2>
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

            <button class="w-full h-[55px] rounded-xl bg-[#3B82F6] text-white text-[14px] font-black tracking-widest uppercase transition-all shadow-[0_10px_20px_-10px_rgba(59,130,246,0.6)] hover:bg-[#2563EB] active:scale-95 outline-none flex items-center justify-center gap-3">
                <i class="fas fa-paper-plane"></i>
                <span>Enviar a Cocina</span>
            </button>
        </div>
    </section>

    {{-- ========================================== --}}
    {{-- COLUMNA 3: CATÁLOGO DE PRODUCTOS             --}}
    {{-- ========================================== --}}
    <main class="flex-1 flex flex-col bg-[var(--bg-base)] relative overflow-hidden">
        
        <div class="px-8 pt-8 pb-5 flex gap-3 overflow-x-auto hide-scroll relative z-10 border-b border-[var(--border-color)] bg-[var(--bg-panel)]" id="menuCategorias">
            <!-- Categorías generadas dinámicamente por JS desde Laravel -->
        </div>

        {{-- Grid de Productos --}}
        <div class="flex-1 p-8 overflow-y-auto hide-scroll grid grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-5 content-start relative z-10" onclick="deseleccionarTicket()" id="gridProductos">
            <!-- Productos generados dinámicamente por JS desde Laravel -->
        </div>

        {{-- BARRA DINÁMICA DE MODIFICADORES --}}
        <div id="barraModificadores" class="hidden h-[80px] bg-[#3B82F6]/5 border-t border-[#3B82F6]/20 flex items-center px-6 relative z-10 transition-all">
            <span class="text-[9px] font-black uppercase tracking-[0.2em] text-[#3B82F6] mr-4 whitespace-nowrap">Opciones:</span>
            
            <div id="contenedorBotonesModificadores" class="flex gap-2 overflow-x-auto hide-scroll flex-1">
                <!-- Se llena con JS basado en la tabla pivote de modificadores -->
            </div>
            
            <button onclick="deseleccionarTicket()" class="ml-4 w-10 h-10 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-rose-500 hover:border-rose-500/50 transition-all outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </main>

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

        // ==========================================
        // CONEXIÓN REAL A LA BASE DE DATOS DE LARAVEL
        // ==========================================
        // Aquí tomamos la variable $categorias y $productos que mandaste desde web.php
        const categoriasDB = @json($categorias ?? []);
        const productosDB = @json($productos ?? []);

        function renderizarMenu() {
            const menuCat = document.getElementById('menuCategorias');
            const gridProd = document.getElementById('gridProductos');
            
            // 1. Renderizar Categorías
            menuCat.innerHTML = `<button onclick="filtrarCategoria('Todos', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[#3B82F6] text-white text-[10px] font-black uppercase tracking-widest shadow-[0_0_15px_rgba(59,130,246,0.4)] whitespace-nowrap outline-none transition-all">Todos</button>`;
            
            if(categoriasDB.length > 0) {
                categoriasDB.forEach(cat => {
                    menuCat.innerHTML += `<button onclick="filtrarCategoria('${cat.nombre}', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-muted)] text-[10px] font-bold uppercase tracking-widest hover:border-[#3B82F6]/50 hover:text-[var(--text-main)] transition-all whitespace-nowrap outline-none">${cat.nombre}</button>`;
                });
            }

            // 2. Renderizar Productos y preparar sus Modificadores
            gridProd.innerHTML = '';
            
            if(productosDB.length > 0) {
                productosDB.forEach(prod => {
                    // Extraemos el nombre de la categoría por relación (prod.categoria.nombre)
                    const catNombre = prod.categoria ? prod.categoria.nombre : 'Sin Categoría';
                    
                    // Aseguramos que el precio sea un número válido
                    const precioNum = parseFloat(prod.precio) || 0;
                    
                    // Convertimos la relación de modificadores en un JSON string para poder pasarlo en el onclick
                    const modsJSON = prod.modificadores ? JSON.stringify(prod.modificadores).replace(/'/g, "\\'") : '[]';

                    gridProd.innerHTML += `
                        <div data-categoria-item="${catNombre}" onclick='agregarAlTicket("${prod.nombre}", ${precioNum}, "${catNombre}", ${modsJSON}); event.stopPropagation();' class="producto-card group bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-2xl p-4 flex flex-col hover:border-[#3B82F6]/50 transition-all cursor-pointer h-[200px]">
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
        
        // Arrancar pintando el catálogo
        document.addEventListener('DOMContentLoaded', renderizarMenu);

        // Lógica para el filtro de categorías
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

        // ==========================================
        // LÓGICA DEL TICKET
        // ==========================================
        let ticketSubtotal = 0;
        let itemActivo = null; 
        let contadorItems = 0; 

        const listaTicket = document.getElementById('listaTicket');
        const estadoVacio = document.getElementById('estadoVacio');
        const barraModificadores = document.getElementById('barraModificadores');
        const contenedorBotonesModificadores = document.getElementById('contenedorBotonesModificadores');
        
        function agregarAlTicket(nombre, precio, categoria, arrayModificadores = []) {
            estadoVacio.classList.add('hidden');
            contadorItems++;
            const itemId = 'ticket-item-' + contadorItems;
            
            // Re-convertimos el array a string seguro para incrustarlo en el HTML
            const modsString = JSON.stringify(arrayModificadores).replace(/'/g, "&#39;").replace(/"/g, "&quot;");
            
            const itemHTML = `
                <div id="${itemId}" data-modificadores="${modsString}" class="ticket-item animate-item bg-[var(--bg-panel)] border border-[var(--border-color)] rounded-xl p-3 grid grid-cols-[40px_1fr_60px] gap-2 items-start cursor-pointer transition-all" onclick="seleccionarItem('${itemId}')">
                    <div class="flex justify-center mt-1">
                        <span class="w-6 h-6 rounded bg-[#3B82F6]/10 text-[#3B82F6] text-[10px] font-black flex items-center justify-center">1</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-[var(--text-main)] leading-tight">${nombre}</span>
                        <div class="modificadores-lista flex flex-wrap gap-1 mt-1 empty:hidden"></div>
                        <button onclick="eliminarItemFila(this, ${precio}); event.stopPropagation();" class="text-[9px] text-rose-500 font-bold uppercase tracking-widest text-left mt-2 hidden hover:underline btn-eliminar">Quitar platillo</button>
                    </div>
                    <div class="text-right mt-1">
                        <span class="text-sm font-black text-[var(--text-main)]">$${parseFloat(precio).toFixed(2)}</span>
                    </div>
                </div>
            `;
            
            listaTicket.insertAdjacentHTML('beforeend', itemHTML);
            ticketSubtotal += parseFloat(precio);
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
                
                // Extraemos los modificadores reales de la base de datos
                const modsString = itemActivo.getAttribute('data-modificadores');
                const modificadoresParaPintar = JSON.parse(modsString || '[]');
                
                contenedorBotonesModificadores.innerHTML = '';
                
                if(modificadoresParaPintar.length > 0) {
                    modificadoresParaPintar.forEach(mod => {
                        // Aquí asumimos que en tu tabla de modificadores la columna se llama 'nombre'.
                        // Si se llama 'descripcion', cambialo a mod.descripcion
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

        function eliminarItemFila(btn, precio) {
            const fila = btn.closest('.ticket-item');
            fila.remove();
            ticketSubtotal -= parseFloat(precio);
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
            actualizarTotales();
            deseleccionarTicket();
        }
    </script>
</body>
</html>