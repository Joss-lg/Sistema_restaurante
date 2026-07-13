/**
 * comanda-catalogo.js
 * Renderiza el catálogo de productos (categorías + tarjetas) del panel
 * derecho. Depende de categoriasDB / productosDB (declaradas en
 * comanda-core.js) y de window.agregarAlTicket (comanda-ticket.js, se
 * llama desde el HTML generado aquí mismo vía onclick).
 */
(function () {
    function renderizarMenu() {
        const menuCat = document.getElementById('menuCategorias');
        const gridProd = document.getElementById('gridProductos');

        menuCat.innerHTML = `<button type="button" onclick="filtrarCategoria('Todos', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[var(--text-main)] text-[var(--bg-base)] text-[11px] font-bold tracking-wide shadow-sm transition-all outline-none border border-transparent">Todos</button>`;

        if (categoriasDB.length > 0) {
            categoriasDB.forEach(cat => {
                menuCat.innerHTML += `<button type="button" onclick="filtrarCategoria('${cat.nombre}', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--border-highlight)] text-[11px] font-semibold tracking-wide shadow-sm transition-all outline-none">${cat.nombre}</button>`;
            });
        }

        gridProd.innerHTML = '';

        if (productosDB.length > 0) {
            productosDB.forEach(prod => {
                const catNombre = prod.categoria ? prod.categoria.nombre : '';
                const precioNum = parseFloat(prod.precio) || 0;
                const sePorPeso = !!prod.se_vende_por_peso;
                const precioPor100g = parseFloat(prod.precio_por_100g) || 0;
                const modsJSON = prod.modificadores ? JSON.stringify(prod.modificadores).replace(/'/g, "\\'") : '[]';
                const letraInicial = prod.nombre.charAt(0).toUpperCase();

                const etiquetaPrecio = sePorPeso
                    ? `$${precioPor100g.toFixed(2)} <span class="text-[9px] font-bold opacity-70">/100g</span>`
                    : `$${precioNum.toFixed(2)}`;

                const badgePorPeso = sePorPeso
                    ? `<span class="absolute top-2.5 left-2.5 text-[7px] font-black uppercase tracking-widest text-white bg-orange-500 px-1.5 py-0.5 rounded-md shadow-sm z-10"><i class="fas fa-weight-hanging mr-0.5"></i>Peso</span>`
                    : '';

                gridProd.innerHTML += `
                    <div data-categoria-item="${catNombre}" onclick='agregarAlTicket(${prod.id}, "${prod.nombre}", ${precioNum}, "${catNombre}", ${modsJSON}, ${sePorPeso ? 'true' : 'false'}, ${precioPor100g}); event.stopPropagation();'
                         class="producto-card rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-[var(--card-shadow)] overflow-hidden hover:border-[#3b82f6]/50 hover:-translate-y-1 transition-all duration-300 group cursor-pointer flex flex-col h-[150px] xl:h-[170px] outline-none relative">

                        <div class="h-[50%] bg-[var(--input-bg)] flex items-center justify-center relative overflow-hidden border-b border-[var(--border-color)]">
                            ${badgePorPeso}
                            <span class="absolute top-2.5 right-2.5 text-[8px] font-bold uppercase tracking-widest text-[var(--text-muted)] bg-[var(--bg-panel)] border border-[var(--border-color)] px-2 py-0.5 rounded-md shadow-sm z-10">${catNombre}</span>
                            <span class="text-5xl font-black text-[var(--text-muted)] opacity-10 group-hover:opacity-20 transition-all duration-500 transform group-hover:scale-110 select-none">${letraInicial}</span>
                        </div>

                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <h3 class="text-[12px] xl:text-[13px] font-bold text-[var(--text-main)] leading-snug line-clamp-2">${prod.nombre}</h3>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-[14px] font-black text-[var(--text-main)] tracking-tight">${etiquetaPrecio}</span>
                                <div class="w-6 h-6 rounded-full bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] group-hover:bg-[#3b82f6] group-hover:text-white group-hover:border-transparent transition-all duration-300 shadow-sm">
                                    <i class="fas fa-plus text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            gridProd.innerHTML = `
                <div class="col-span-full flex flex-col items-center justify-center text-[var(--text-muted)] mt-20">
                    <i class="fas fa-box-open text-4xl mb-4 opacity-50"></i>
                    <p class="text-xs font-medium">Catálogo vacío</p>
                </div>`;
        }
    }

    document.addEventListener('DOMContentLoaded', renderizarMenu);

    window.filtrarCategoria = function (nombreCat, btn) {
        document.querySelectorAll('.cat-btn').forEach(el => el.className = "cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--border-highlight)] text-[11px] font-semibold tracking-wide shadow-sm transition-all outline-none");
        btn.className = "cat-btn px-6 py-2.5 rounded-full bg-[var(--text-main)] text-[var(--bg-base)] text-[11px] font-bold tracking-wide shadow-sm transition-all outline-none border border-transparent";

        document.querySelectorAll('.producto-card').forEach(card => {
            card.style.display = (nombreCat === 'Todos' || card.getAttribute('data-categoria-item') === nombreCat) ? 'flex' : 'none';
        });
    };
})();