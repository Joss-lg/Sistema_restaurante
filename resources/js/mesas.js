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
    zonaActual: 'Todas',
    primeraCarga: true
};

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
    cargarMesas();
    
    // Auto-sincronización
    setInterval(() => {
        if (!estadoGlobal.modoEdicion && !estadoGlobal.modoFusion) cargarMesas();
    }, 5000);

    // Eventos UI
    document.getElementById('btnEditar')?.addEventListener('click', toggleModoEdicion);
    document.getElementById('btnGuardar')?.addEventListener('click', guardarPlanoEnServidor);
    document.getElementById('btnCancelar')?.addEventListener('click', () => {
        estadoGlobal.modoEdicion = false;
        renderizarMapaMesas();
    });

    document.getElementById('btnAgregarMesa')?.addEventListener('click', abrirModalNuevaMesa);
    document.getElementById('btnConfirmarNueva')?.addEventListener('click', crearNuevaMesa);
    document.getElementById('btnEliminar')?.addEventListener('click', eliminarMesaDelPlano);
    document.getElementById('btnActualizar')?.addEventListener('click', actualizarPropiedadesMesa);
    
    document.getElementById('filtroZona')?.addEventListener('change', (e) => {
        estadoGlobal.zonaActual = e.target.value || 'Todas';
        renderizarMapaMesas();
    });

    document.querySelectorAll('.btnCerrarModal').forEach(btn => {
        btn.addEventListener('click', cerrarModalNuevaMesa);
    });
});

// --- CARGA Y RENDERIZADO ---
async function cargarMesas() {
    try {
        // CAMBIO: Apuntando a tu ruta de PlanoEspacialController
        const res = await fetch('/plano-espacial/api/mesas');
        if (!res.ok) throw new Error('Error en API');
        estadoGlobal.mesas = await res.json();
        renderizarMapaMesas();
    } catch (e) { console.error(e); }
}

function renderizarMapaMesas() {
    // CAMBIO: Apuntando al ID de tu vista plano-espacial.blade.php
    const contenedor = document.getElementById('planoContenedor');
    if (!contenedor) return;
    contenedor.innerHTML = '';

    const mesasFiltradas = estadoGlobal.zonaActual === 'Todas' 
        ? estadoGlobal.mesas 
        : estadoGlobal.mesas.filter(m => m.zona === estadoGlobal.zonaActual);

    mesasFiltradas.forEach(mesa => {
        const div = document.createElement('div');
        div.className = 'mesa-elemento absolute bg-blue-500 rounded-lg cursor-move flex items-center justify-center text-white font-bold border-2 border-slate-900';
        div.dataset.id = mesa.id;
        div.style.left = (mesa.posicion_x || 50) + 'px';
        div.style.top = (mesa.posicion_y || 50) + 'px';
        div.style.width = (mesa.ancho || 80) + 'px';
        div.style.height = (mesa.alto || 80) + 'px';
        
        div.innerHTML = mesa.numero;

        div.addEventListener('pointerdown', (e) => iniciarArrastre(e, div, mesa));
        div.addEventListener('click', (e) => {
            if (estadoGlobal.modoEdicion) { e.stopPropagation(); seleccionarMesa(mesa); }
        });
        contenedor.appendChild(div);
    });
    
    // Actualizar contador visual
    const total = document.getElementById('totalMesas');
    if (total) total.innerText = `Mesas: ${mesasFiltradas.length}`;
}

// --- LÓGICA DE ARRASTRE ---
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
}

function manejarArrastre(e) {
    if (!dragState.activo) return;
    const x = e.clientX - dragState.startX + dragState.originX;
    const y = e.clientY - dragState.startY + dragState.originY;
    dragState.elemento.style.left = x + 'px';
    dragState.elemento.style.top = y + 'px';
}

function detenerArrastre() {
    if (!dragState.activo) return;
    const mesa = estadoGlobal.mesas.find(m => m.id === dragState.mesaId);
    if (mesa) {
        mesa.posicion_x = parseInt(dragState.elemento.style.left);
        mesa.posicion_y = parseInt(dragState.elemento.style.top);
    }
    dragState.activo = false;
}

// --- ACCIONES ---
window.toggleModoEdicion = () => {
    estadoGlobal.modoEdicion = !estadoGlobal.modoEdicion;
    document.getElementById('modosEdicion')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
    document.getElementById('btnGuardar')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
    document.getElementById('btnCancelar')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
};

window.abrirModalNuevaMesa = () => {
    const modal = document.getElementById('modalNuevaMesa');
    modal.classList.remove('hidden');
    // Forzamos un reflow para que la transición de opacidad funcione
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.classList.add('opacity-100');
    }, 10);
};

window.cerrarModalNuevaMesa = () => {
    const modal = document.getElementById('modalNuevaMesa');
    modal.classList.remove('opacity-100');
    modal.classList.add('opacity-0');
    // Esperamos a que termine la animación antes de ocultar
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
};

window.crearNuevaMesa = async () => {
    const data = {
        numero: document.getElementById('newNumero').value,
        capacidad: document.getElementById('newCapacidad').value,
        estado: document.getElementById('newEstado').value
    };

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
        cargarMesas(); // Refresca el plano
    } else {
        alert('Error al crear la mesa');
    }
};

function toggleModoEdicion() {
    estadoGlobal.modoEdicion = !estadoGlobal.modoEdicion;
    document.getElementById('modosEdicion')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
    document.getElementById('btnGuardar')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
    document.getElementById('btnCancelar')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
}

function seleccionarMesa(mesa) {
    estadoGlobal.mesaSeleccionada = mesa;
    document.getElementById('panelVacio')?.classList.add('hidden');
    document.getElementById('formularioMesa')?.classList.remove('hidden');
    document.getElementById('propNumero').value = mesa.numero;
    document.getElementById('propCapacidad').value = mesa.capacidad;
    document.getElementById('btnActualizar')?.classList.remove('hidden');
}

// Exportación para funciones globales en HTML
window.abrirModalNuevaMesa = () => document.getElementById('modalCrearMesa').classList.remove('hidden');
window.cerrarModalNuevaMesa = () => document.getElementById('modalCrearMesa').classList.add('hidden');