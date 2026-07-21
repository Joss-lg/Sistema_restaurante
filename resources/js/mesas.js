/**
 * mesas.js - Sistema centralizado de gestión de mesas (Ajustado para Plano Espacial)
 */

let estadoGlobal = {
    mesas: [],
    filtroActual: 'todos',
    vista: 'mapa',
    modoEdicion: false,
    modoFusion: false,
    mesaSeleccionada: null,
    primeraCarga: true,
    zoom: 1
};

const ZOOM_MIN = 0.4;
const ZOOM_MAX = 1.5;
const ZOOM_STEP = 0.15;

// Permisos del usuario actual sobre el módulo Mesas (inyectados desde
// plano-espacial.blade.php vía window.permisosMesas antes de cargar este script).
const permisosMesas = window.permisosMesas || { crear: false, editar: false, eliminar: false };

let dragState = {
    activo: false,
    elemento: null,
    mesaId: null,
    originX: 0,
    originY: 0,
    startX: 0,
    startY: 0,
    contenedor: null
};

// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', () => {
    // En pantallas pequeñas arrancamos con menos zoom para ver el plano completo
    if (window.innerWidth < 640) {
        estadoGlobal.zoom = 0.6;
    }
    aplicarZoom();

    cargarMesas();

    setInterval(() => {
        if (!estadoGlobal.modoEdicion && !estadoGlobal.modoFusion) cargarMesas();
    }, 5000);

    document.getElementById('btnEditar')?.addEventListener('click', toggleModoEdicion);
    document.getElementById('btnGuardar')?.addEventListener('click', guardarPlanoEnServidor);
    document.getElementById('btnCancelar')?.addEventListener('click', cancelarEdicion);

    document.getElementById('btnAgregarMesa')?.addEventListener('click', abrirModalNuevaMesa);
    document.getElementById('btnConfirmarNueva')?.addEventListener('click', crearNuevaMesa);
    document.getElementById('btnEliminar')?.addEventListener('click', eliminarMesaDelPlano);
    document.getElementById('btnActualizar')?.addEventListener('click', actualizarPropiedadesMesa);

    document.querySelectorAll('.btnCerrarModal').forEach(btn => {
        btn.addEventListener('click', cerrarModalNuevaMesa);
    });

    document.getElementById('btnCerrarPanelMovil')?.addEventListener('click', cerrarPanelPropiedadesMovil);
    document.getElementById('panelBackdrop')?.addEventListener('click', cerrarPanelPropiedadesMovil);

    // Zoom
    document.getElementById('btnZoomIn')?.addEventListener('click', () => cambiarZoom(ZOOM_STEP));
    document.getElementById('btnZoomOut')?.addEventListener('click', () => cambiarZoom(-ZOOM_STEP));
    document.getElementById('btnZoomReset')?.addEventListener('click', () => {
        estadoGlobal.zoom = window.innerWidth < 640 ? 0.6 : 1;
        aplicarZoom();
    });
});

// --- ZOOM ---
function cambiarZoom(delta) {
    const nuevoZoom = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, estadoGlobal.zoom + delta));
    estadoGlobal.zoom = Math.round(nuevoZoom * 100) / 100;
    aplicarZoom();
}

function aplicarZoom() {
    const lienzo = document.getElementById('planoLienzo');
    const label = document.getElementById('zoomLabel');
    if (lienzo) lienzo.style.transform = `scale(${estadoGlobal.zoom})`;
    if (label) label.innerText = `${Math.round(estadoGlobal.zoom * 100)}%`;
}

// --- CARGA Y RENDERIZADO ---
async function cargarMesas() {
    try {
        const res = await fetch('/plano-espacial/api/mesas');
        if (!res.ok) throw new Error('Error en API');

        const response = await res.json();
        estadoGlobal.mesas = response.data || [];
        renderizarMapaMesas();
    } catch (e) {
        console.error(e);
    }
}

function renderizarMapaMesas() {
    const lienzo = document.getElementById('planoLienzo');
    if (!lienzo) return;

    // Limpiamos solo las mesas, conservamos el div de estado vacío
    lienzo.querySelectorAll('.mesa-elemento').forEach(el => el.remove());

    const mesasFiltradas = estadoGlobal.mesas;
    const planoVacio = document.getElementById('planoVacio');

    if (mesasFiltradas.length === 0) {
        planoVacio?.classList.remove('hidden');
    } else {
        planoVacio?.classList.add('hidden');
    }

    mesasFiltradas.forEach(mesa => {
        const div = document.createElement('div');
        div.className = `mesa-elemento mesa-ui absolute rounded-lg cursor-move flex items-center justify-center font-bold border-2 text-[var(--text-color)] border-[var(--text-color)] mesa-${mesa.estado} select-none`;

        div.dataset.id = mesa.id;
        div.style.left = (mesa.posicion_x || 50) + 'px';
        div.style.top = (mesa.posicion_y || 50) + 'px';
        div.style.width = (mesa.ancho || 80) + 'px';
        div.style.height = (mesa.alto || 80) + 'px';
        // IMPORTANTE: solo bloqueamos los gestos táctiles nativos (touch-action: none)
        // cuando estamos en modo edición, que es cuando el usuario arrastra la mesa.
        // Fuera de modo edición dejamos "pan-x pan-y" para que, si el dedo empieza
        // el gesto justo sobre una mesa, el navegador SÍ pueda hacer scroll/pan del
        // contenedor. Antes esto estaba fijo en 'none' y por eso en el teléfono no
        // se podía deslizar el plano al tocar sobre las mesas.
        div.style.touchAction = estadoGlobal.modoEdicion ? 'none' : 'pan-x pan-y';
        div.innerHTML = mesa.numero;

        div.addEventListener('pointerdown', (e) => {
            if (estadoGlobal.modoEdicion && permisosMesas.editar) iniciarArrastre(e, div, mesa);
        });

        div.addEventListener('click', (e) => {
            if (estadoGlobal.modoEdicion) {
                e.stopPropagation();
                seleccionarMesa(mesa);
            } else {
                window.location.href = `/mesero/comanda/${mesa.id}`;
            }
        });

        lienzo.appendChild(div);
    });

    const total = document.getElementById('totalMesas');
    if (total) total.innerText = `Mesas: ${mesasFiltradas.length}`;
}

// --- LÓGICA DE ARRASTRE ---
// Nota: dividimos los deltas de movimiento entre estadoGlobal.zoom para que
// el arrastre se sienta "1 a 1" con el dedo/mouse, incluso con zoom aplicado.
function iniciarArrastre(e, el, mesa) {
    if (!estadoGlobal.modoEdicion) return;
    dragState = {
        activo: true,
        elemento: el,
        mesaId: mesa.id,
        originX: parseInt(el.style.left),
        originY: parseInt(el.style.top),
        startX: e.clientX,
        startY: e.clientY,
        contenedor: document.getElementById('planoContenedor')
    };
    el.setPointerCapture(e.pointerId);
    el.addEventListener('pointermove', manejarArrastre);
    el.addEventListener('pointerup', detenerArrastre);
    el.addEventListener('pointercancel', detenerArrastre);
}

function manejarArrastre(e) {
    if (!dragState.activo) return;
    const zoom = estadoGlobal.zoom || 1;
    const x = (e.clientX - dragState.startX) / zoom + dragState.originX;
    const y = (e.clientY - dragState.startY) / zoom + dragState.originY;
    dragState.elemento.style.left = Math.max(0, x) + 'px';
    dragState.elemento.style.top = Math.max(0, y) + 'px';
}

function detenerArrastre() {
    if (!dragState.activo) return;
    const mesa = estadoGlobal.mesas.find(m => m.id === dragState.mesaId);
    if (mesa) {
        mesa.posicion_x = parseInt(dragState.elemento.style.left);
        mesa.posicion_y = parseInt(dragState.elemento.style.top);
    }
    dragState.elemento?.removeEventListener('pointermove', manejarArrastre);
    dragState.elemento?.removeEventListener('pointerup', detenerArrastre);
    dragState.elemento?.removeEventListener('pointercancel', detenerArrastre);
    dragState.activo = false;
}

// Sincroniza el touch-action de las mesas ya pintadas en pantalla sin tener
// que esperar al próximo cargarMesas() (que solo corre fuera de modo edición).
function actualizarTouchActionMesas() {
    document.querySelectorAll('.mesa-elemento').forEach(el => {
        el.style.touchAction = estadoGlobal.modoEdicion ? 'none' : 'pan-x pan-y';
    });
}

window.toggleModoEdicion = () => {
    estadoGlobal.modoEdicion = !estadoGlobal.modoEdicion;
    document.getElementById('modosEdicion')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
    document.getElementById('btnGuardar')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
    document.getElementById('btnCancelar')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
    actualizarTouchActionMesas();

    // Si se sale de modo edición sin haber seleccionado nada, cerramos la hoja
    // inferior de propiedades en móvil.
    if (!estadoGlobal.modoEdicion) cerrarPanelPropiedadesMovil();
};

window.abrirModalNuevaMesa = () => document.getElementById('modalCrearMesa').classList.remove('hidden');
window.cerrarModalNuevaMesa = () => document.getElementById('modalCrearMesa').classList.add('hidden');

window.crearNuevaMesa = async () => {
    if (!permisosMesas.crear) {
        showToast('No tienes permiso para crear mesas', 'error');
        return;
    }

    const estadoRaw = document.getElementById('newEstado')?.value || 'disponible';

    const data = {
        numero: document.getElementById('newNumero')?.value?.trim(),
        capacidad: parseInt(document.getElementById('newCapacidad')?.value, 10) || 1,
        estado: estadoRaw.toLowerCase()
    };

    try {
        const res = await fetch('/plano-espacial/api/crear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        if (res.ok) {
            window.cerrarModalNuevaMesa();
            cargarMesas();
            showToast('Mesa creada correctamente', 'success');
        } else {
            const errorData = await res.json().catch(() => null);
            const msg = errorData?.message || 'Error al crear la mesa';
            showToast(msg, 'error');
        }
    } catch (e) {
        console.error('Error al crear mesa:', e);
        showToast('Error al crear la mesa', 'error');
    }
};

function seleccionarMesa(mesa) {
    estadoGlobal.mesaSeleccionada = mesa;
    document.getElementById('panelVacio')?.classList.add('hidden');
    document.getElementById('formularioMesa')?.classList.remove('hidden');
    document.getElementById('propNumero').value = mesa.numero;
    document.getElementById('propCapacidad').value = mesa.capacidad;
    document.getElementById('btnActualizar')?.classList.remove('hidden');
    document.getElementById('btnEliminar')?.classList.remove('hidden');
    abrirPanelPropiedadesMovil();
}

// --- HOJA INFERIOR DE PROPIEDADES (SOLO AFECTA MÓVIL, EN ESCRITORIO EL PANEL
//     SIEMPRE ES VISIBLE COMO COLUMNA LATERAL GRACIAS A LAS CLASES lg:*) ---
function abrirPanelPropiedadesMovil() {
    const panel = document.getElementById('panelPropiedades');
    const backdrop = document.getElementById('panelBackdrop');
    panel?.classList.remove('translate-y-full');
    panel?.classList.add('translate-y-0');
    backdrop?.classList.remove('hidden');
}

function cerrarPanelPropiedadesMovil() {
    const panel = document.getElementById('panelPropiedades');
    const backdrop = document.getElementById('panelBackdrop');
    panel?.classList.add('translate-y-full');
    panel?.classList.remove('translate-y-0');
    backdrop?.classList.add('hidden');
}
window.cerrarPanelPropiedadesMovil = cerrarPanelPropiedadesMovil;

// --- ACCIONES DE GUARDADO Y CANCELAR ---

async function guardarPlanoEnServidor() {
    if (!permisosMesas.editar) {
        showToast('No tienes permiso para editar mesas', 'error');
        return;
    }

    try {
        const res = await fetch('/plano-espacial/api/guardar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ mesas: estadoGlobal.mesas })
        });

        if (!res.ok) {
            const errorText = await res.text();
            console.error('Respuesta del servidor no exitosa:', errorText);
            showToast('Error al guardar el plano', 'error');
            return;
        }

        const result = await res.json();
        console.log('Guardado exitoso:', result);
        showToast('Plano guardado correctamente', 'success');
    } catch (e) {
        console.error('Error en la petición:', e);
        showToast('Error al guardar el plano', 'error');
    }
}

function cancelarEdicion() {
    showConfirm('¿Deseas descartar los cambios sin guardar?', () => {
        estadoGlobal.modoEdicion = false;
        document.getElementById('btnGuardar')?.classList.add('hidden');
        document.getElementById('btnCancelar')?.classList.add('hidden');
        document.getElementById('modosEdicion')?.classList.add('hidden');
        actualizarTouchActionMesas();
        cerrarPanelPropiedadesMovil();
        cargarMesas();
    }, { titulo: '¿Descartar cambios?', textoConfirmar: 'Descartar' });
}

window.actualizarPropiedadesMesa = async () => {
    if (!estadoGlobal.mesaSeleccionada) return;

    if (!permisosMesas.editar) {
        showToast('No tienes permiso para editar mesas', 'error');
        return;
    }

    const data = {
        numero: document.getElementById('propNumero').value,
        capacidad: document.getElementById('propCapacidad').value
    };

    try {
        const res = await fetch(`/plano-espacial/api/actualizar/${estadoGlobal.mesaSeleccionada.id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        if (res.ok) {
            showToast('Propiedades actualizadas', 'success');
            cargarMesas();
        } else {
            showToast('Error al actualizar la mesa', 'error');
        }
    } catch (e) {
        console.error('Error:', e);
        showToast('Error al actualizar la mesa', 'error');
    }
};

// --- ELIMINAR MESA ---
window.eliminarMesaDelPlano = () => {
    if (!estadoGlobal.mesaSeleccionada) return;

    if (!permisosMesas.eliminar) {
        showToast('No tienes permiso para eliminar mesas', 'error');
        return;
    }

    showConfirm('¿Estás seguro de eliminar esta mesa?', async () => {
        try {
            const res = await fetch(`/plano-espacial/api/eliminar/${estadoGlobal.mesaSeleccionada.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (res.ok) {
                showToast('Mesa eliminada correctamente', 'success');
                estadoGlobal.mesaSeleccionada = null;
                document.getElementById('formularioMesa')?.classList.add('hidden');
                document.getElementById('panelVacio')?.classList.remove('hidden');
                cerrarPanelPropiedadesMovil();
                cargarMesas();
            } else {
                const errorData = await res.json().catch(() => null);
                showToast(errorData?.message || 'Error al eliminar la mesa', 'error');
            }
        } catch (e) {
            console.error('Error al eliminar mesa:', e);
            showToast('Error al eliminar la mesa', 'error');
        }
    }, { titulo: 'Eliminar mesa', textoConfirmar: 'Eliminar' });
};