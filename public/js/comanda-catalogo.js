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

        if (!menuCat || !gridProd) {
            return; 
        }

        menuCat.innerHTML = `<button type="button" onclick="filtrarCategoria('Todos', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[var(--text-main)] text-[var(--bg-base)] text-[11px] font-bold tracking-wide shadow-sm transition-all outline-none border border-transparent">Todos</button>`;

        if (categoriasDB.length > 0) {
            categoriasDB.forEach(cat => {
                menuCat.innerHTML += `<button type="button" onclick="filtrarCategoria('${cat.nombre}', this)" class="cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--border-highlight)] text-[11px] font-semibold tracking-wide shadow-sm transition-all outline-none">${cat.nombre}</button>`;
            });
        }

        gridProd.innerHTML = '';

        // FILTRO: Solo tomamos productos activos/disponibles
        const productosVisibles = productosDB.filter(prod => prod.esta_disponible == 1 || prod.esta_disponible === true);

        if (productosVisibles.length > 0) {
            productosVisibles.forEach(prod => {
                const catNombre = prod.categoria ? prod.categoria.nombre : '';
                const precioNum = parseFloat(prod.precio) || 0;
                const sePorPeso = !!prod.se_vende_por_peso;
                const precioPor100g = parseFloat(prod.precio_por_100g) || 0;
                const modsJSON = prod.modificadores ? JSON.stringify(prod.modificadores).replace(/'/g, "\\'") : '[]';

                // Etiqueta de precio adaptada al nuevo diseño limpio
                const etiquetaPrecio = sePorPeso
                    ? `$${precioPor100g.toFixed(2)} <span class="text-[11px] font-semibold text-slate-500">/100g</span>`
                    : `$${precioNum.toFixed(2)}`;

                gridProd.innerHTML += `
                    <button type="button"
                         data-categoria-item="${catNombre}" 
                         data-nombre-item="${(prod.nombre || '').toLowerCase()}" 
                         onclick='agregarAlTicket(${prod.id}, "${prod.nombre}", ${precioNum}, "${catNombre}", ${modsJSON}, ${sePorPeso ? 'true' : 'false'}, ${precioPor100g}); event.stopPropagation();'
                         class="producto-card group relative flex flex-col justify-between text-left rounded-[20px] border border-blue-200/60 bg-white p-4 shadow-sm min-h-[130px] hover:border-blue-500 hover:shadow-md hover:-translate-y-0.5 active:scale-[0.97] active:translate-y-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/60 transition-all duration-150">

                        <h3 class="text-[14px] sm:text-[15px] font-black text-slate-900 leading-tight uppercase mb-4 pr-2">
                            ${prod.nombre}
                        </h3>

                        <div class="mt-auto flex items-center justify-between gap-2 w-full">
                            <p class="text-[16px] sm:text-[18px] font-black text-slate-900 leading-none tracking-tight">
                                ${etiquetaPrecio}
                            </p>

                            <span class="flex-shrink-0 w-9 h-9 rounded-full bg-[#3b82f6] text-white flex items-center justify-center text-sm font-bold shadow-sm group-hover:bg-blue-600 group-active:scale-90 transition-all duration-150">
                                <i class="fas fa-plus"></i>
                            </span>
                        </div>
                    </button>
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

    // Categoria activa y texto buscado. Se guardan aparte porque los DOS
    // filtros se aplican juntos: si el mesero busca "coca" dentro de Bebidas,
    // debe seguir viendo solo bebidas.
    let categoriaActiva = 'Todos';
    let textoBuscado = '';

    /**
     * Quita acentos y pasa a minusculas.
     * Sin esto, buscar "cafe" no encontraria "Café", que es justo lo que
     * escribe el mesero con prisa.
     */
    function normalizar(texto) {
        return (texto || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function aplicarFiltrosCatalogo() {
        const buscado = normalizar(textoBuscado).trim();
        let visibles = 0;

        document.querySelectorAll('.producto-card').forEach(card => {
            const coincideCategoria = categoriaActiva === 'Todos'
                || card.getAttribute('data-categoria-item') === categoriaActiva;

            const nombre = normalizar(card.getAttribute('data-nombre-item'));
            const coincideTexto = buscado === '' || nombre.includes(buscado);

            const mostrar = coincideCategoria && coincideTexto;
            // Se cambia a 'flex' para respetar la estructura del botón
            card.style.display = mostrar ? 'flex' : 'none';
            if (mostrar) visibles++;
        });

        // Aviso de "sin resultados": sin esto, una busqueda sin coincidencias
        // deja la pantalla en blanco y parece que el sistema se trabo.
        const aviso = document.getElementById('catalogoSinResultados');
        if (aviso) aviso.classList.toggle('hidden', visibles > 0);
    }

    window.filtrarCategoria = function (nombreCat, btn) {
        if (!btn) return;

        document.querySelectorAll('.cat-btn').forEach(el => el.className = "cat-btn px-6 py-2.5 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--border-highlight)] text-[11px] font-semibold tracking-wide shadow-sm transition-all outline-none");
        btn.className = "cat-btn px-6 py-2.5 rounded-full bg-[var(--text-main)] text-[var(--bg-base)] text-[11px] font-bold tracking-wide shadow-sm transition-all outline-none border border-transparent";

        categoriaActiva = nombreCat;
        aplicarFiltrosCatalogo();
    };

    // --- BUSCADOR ---
    document.addEventListener('DOMContentLoaded', () => {
        const buscador = document.getElementById('buscadorProductos');
        const btnLimpiar = document.getElementById('limpiarBusquedaProductos');
        if (!buscador) return;

        const buscar = () => {
            textoBuscado = buscador.value || '';
            if (btnLimpiar) btnLimpiar.classList.toggle('hidden', textoBuscado === '');
            aplicarFiltrosCatalogo();
        };

        buscador.addEventListener('input', buscar);

        // El teclado tactil escribe con .value y no siempre dispara 'input',
        // asi que tambien se revisa mientras el campo tiene el foco.
        let vigilante = null;
        buscador.addEventListener('focus', () => {
            let previo = buscador.value;
            vigilante = setInterval(() => {
                if (buscador.value !== previo) { previo = buscador.value; buscar(); }
            }, 250);
        });
        buscador.addEventListener('blur', () => clearInterval(vigilante));

        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', () => {
                buscador.value = '';
                buscar();
                buscador.focus();
            });
        }
    });
})();