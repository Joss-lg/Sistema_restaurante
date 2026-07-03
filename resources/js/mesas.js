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
    document.getElementById('btnCancelar')?.addEventListener('click', cancelarEdicion);

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
        const res = await fetch('/plano-espacial/api/mesas');
        if (!res.ok) throw new Error('Error en API');
        
        const response = await res.json();
        
        // CORRECCIÓN: Si recibes un objeto único, conviértelo en array.
        // Si ya es un array, mantén el array.
       estadoGlobal.mesas = response.data || [];
        
       renderizarMapaMesas();
    } catch (e) { console.error(e); }
}

function renderizarMapaMesas() {
    const contenedor = document.getElementById('planoContenedor');
    if (!contenedor) return;
    contenedor.innerHTML = '';

    const mesasFiltradas = estadoGlobal.zonaActual === 'Todas' 
        ? estadoGlobal.mesas 
        : estadoGlobal.mesas.filter(m => m.zona === estadoGlobal.zonaActual);

    mesasFiltradas.forEach(mesa => {
        const div = document.createElement('div');
        div.className = `mesa-elemento mesa-ui absolute rounded-lg cursor-move flex items-center justify-center font-bold border-2 text-[var(--text-color)] border-[var(--text-color)] mesa-${mesa.estado}`;
        
        div.dataset.id = mesa.id;
        div.style.left = (mesa.posicion_x || 50) + 'px';
        div.style.top = (mesa.posicion_y || 50) + 'px';
        div.style.width = (mesa.ancho || 80) + 'px';
        div.style.height = (mesa.alto || 80) + 'px';
        div.innerHTML = mesa.numero;

        // --- CORRECCIÓN AQUÍ ---
        div.addEventListener('pointerdown', (e) => {
            if (estadoGlobal.modoEdicion) iniciarArrastre(e, div, mesa);
        });

        div.addEventListener('click', (e) => {
            if (estadoGlobal.modoEdicion) { 
                e.stopPropagation(); 
                seleccionarMesa(mesa); 
            } else {
                // Navegación para no-admin/meseros
                window.location.href = `/mesero/comanda/${mesa.id}`;
            }
        });

        contenedor.appendChild(div);
    });
    
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

// --- ACCIONES ---
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

window.toggleModoEdicion = () => {
    estadoGlobal.modoEdicion = !estadoGlobal.modoEdicion;
    document.getElementById('modosEdicion')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
    document.getElementById('btnGuardar')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
    document.getElementById('btnCancelar')?.classList.toggle('hidden', !estadoGlobal.modoEdicion);
};

window.abrirModalNuevaMesa = () => document.getElementById('modalCrearMesa').classList.remove('hidden');
window.cerrarModalNuevaMesa = () => document.getElementById('modalCrearMesa').classList.add('hidden');

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
        cargarMesas();
    } else {
        alert('Error al crear la mesa');
    }
};

function seleccionarMesa(mesa) {
    estadoGlobal.mesaSeleccionada = mesa;
    document.getElementById('panelVacio')?.classList.add('hidden');
    document.getElementById('formularioMesa')?.classList.remove('hidden');
    document.getElementById('propNumero').value = mesa.numero;
    document.getElementById('propCapacidad').value = mesa.capacidad;
    document.getElementById('btnActualizar')?.classList.remove('hidden');
}

// --- ACCIONES DE GUARDADO Y CANCELAR ---

async function guardarPlanoEnServidor() {
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
            return;
        }

        const result = await res.json();
        console.log('Guardado exitoso:', result);
    } catch (e) {
        console.error('Error en la petición:', e);
    }
}

function cancelarEdicion() {
    if (confirm('¿Deseas descartar los cambios sin guardar?')) {
        estadoGlobal.modoEdicion = false;
        document.getElementById('btnGuardar')?.classList.add('hidden');
        document.getElementById('btnCancelar')?.classList.add('hidden');
        document.getElementById('modosEdicion')?.classList.add('hidden');
        cargarMesas(); 
    }
}

window.actualizarPropiedadesMesa = async () => {
    if (!estadoGlobal.mesaSeleccionada) return;

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
            alert('Propiedades actualizadas');
            cargarMesas();
        } else {
            alert('Error al actualizar la mesa');
        }
    } catch (e) {
        console.error('Error:', e);
    }
};

window.eliminarMesaDelPlano = async () => {
    if (!estadoGlobal.mesaSeleccionada) return;
    
    if (confirm('¿Estás seguro de eliminar esta mesa?')) {
        // ... (tu código de fetch)
    }
};