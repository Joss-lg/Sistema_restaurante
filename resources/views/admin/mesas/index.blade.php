@extends('layouts.admin')

@section('title', 'Mesas | Ollintem Pro')

@section('header-title', 'Gestión de Mesas')
@section('header-subtitle', 'Visualiza y gestiona el estado de las mesas del restaurante')

@section('content')
<div class="p-6 lg:p-8 xl:p-10 max-w-[1400px] mx-auto w-full space-y-6 flex-1 flex flex-col bg-[var(--bg-color)] text-[var(--text-color)]">

    {{-- CABECERA --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-black text-[var(--text-color)] tracking-tight">Mesas del Restaurante</h1>
            <p class="text-[var(--text-muted)] mt-1">Haz clic en una mesa para cobrar o gestionar su estado</p>
        </div>
    </div>

    {{-- FILTRO POR ESTADO --}}
    <div class="flex gap-2 flex-wrap">
        <button onclick="filtrarMesas('todos')" class="px-4 py-2 rounded-xl font-bold transition bg-blue-600 hover:bg-blue-500 text-white filtro-btn" data-filtro="todos">
            Todas
        </button>
        <button onclick="filtrarMesas('libre')" class="px-4 py-2 rounded-xl font-bold transition bg-[var(--card-color)] border border-[var(--border-color)] hover:bg-blue-600 hover:text-white text-[var(--text-color)] filtro-btn" data-filtro="libre">
            <i class="fas fa-chair mr-2"></i>Libres
        </button>
        <button onclick="filtrarMesas('ocupada')" class="px-4 py-2 rounded-xl font-bold transition bg-[var(--card-color)] border border-[var(--border-color)] hover:bg-blue-600 hover:text-white text-[var(--text-color)] filtro-btn" data-filtro="ocupada">
            <i class="fas fa-users mr-2"></i>Ocupadas
        </button>
    </div>

    {{-- GRID DE MESAS --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-5" id="mesas-container">
        {{-- Se llena dinámicamente con JavaScript --}}
    </div>

    <div id="modalEditarMesa" class="fixed inset-0 z-50 flex items-center justify-center hidden opacity-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-3xl shadow-2xl w-full max-w-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-color)]">Editar Mesa</h2>
                    <p class="text-sm text-[var(--text-muted)]">Actualiza el número o la capacidad de la mesa.</p>
                </div>
                <button type="button" onclick="cerrarModalEditarMesa()" class="text-[var(--text-muted)] hover:text-white">Cerrar</button>
            </div>
            <input type="hidden" id="editarMesaId">
            <div class="grid gap-4">
                <label class="block">
                    <span class="text-xs uppercase tracking-[0.3em] text-[var(--text-muted)]">Número de mesa</span>
                    <input id="editarMesaNumero" type="text" class="mt-2 w-full rounded-2xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-[var(--text-color)] outline-none" placeholder="Ej. 12M">
                </label>
                <label class="block">
                    <span class="text-xs uppercase tracking-[0.3em] text-[var(--text-muted)]">Capacidad</span>
                    <input id="editarMesaCapacidad" type="number" min="1" class="mt-2 w-full rounded-2xl border border-[var(--border-color)] bg-[var(--bg-color)] px-4 py-3 text-[var(--text-color)] outline-none" placeholder="Número de personas">
                </label>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="cerrarModalEditarMesa()" class="px-5 py-3 rounded-2xl border border-[var(--border-color)] text-[var(--text-color)] hover:bg-white/5 transition">Cancelar</button>
                <button type="button" onclick="guardarMesaEditada()" class="px-5 py-3 rounded-2xl bg-blue-600 text-white hover:bg-blue-500 transition">Guardar cambios</button>
            </div>
        </div>
    </div>

    <div id="modalEliminarMesa" class="fixed inset-0 z-50 flex items-center justify-center hidden opacity-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-3xl shadow-2xl w-full max-w-lg p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-black text-red-500">Eliminar Mesa</h2>
                    <p class="text-sm text-[var(--text-muted)]">Esta acción no se puede deshacer.</p>
                </div>
                <button type="button" onclick="cerrarModalEliminarMesa()" class="text-[var(--text-muted)] hover:text-white">✕</button>
            </div>
            <input type="hidden" id="eliminarMesaId">
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-2xl">
                <p class="text-[var(--text-color)] font-semibold">¿Deseas eliminar esta mesa?</p>
                <p class="text-sm text-[var(--text-muted)] mt-2">La mesa <span id="eliminarMesaNumero" class="font-bold text-red-400"></span> será eliminada permanentemente del sistema.</p>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="cerrarModalEliminarMesa()" class="px-5 py-3 rounded-2xl border border-[var(--border-color)] text-[var(--text-color)] hover:bg-white/5 transition">Cancelar</button>
                <button type="button" onclick="confirmarEliminarMesa()" class="px-5 py-3 rounded-2xl bg-red-600 text-white hover:bg-red-500 transition">Eliminar mesa</button>
            </div>
        </div>
    </div>

</div>

<script>
    let estadoGlobal = {
        mesas: [],
        filtroActual: 'todos'
    };

    // Cargar mesas al inicializar
    document.addEventListener('DOMContentLoaded', function() {
        cargarMesas();
        // Recargar cada 5 segundos para actualizar estados
        setInterval(cargarMesas, 5000);
    });

    // Cargar mesas desde API
    function cargarMesas() {
        fetch('/admin/mesas/api/mesas')
            .then(response => response.json())
            .then(data => {
                estadoGlobal.mesas = data;
                renderizarMesas();
            })
            .catch(error => console.error('Error cargando mesas:', error));
    }

    // Renderizar mesas en el grid
    function renderizarMesas() {
        const container = document.getElementById('mesas-container');
        container.innerHTML = '';

        if (estadoGlobal.mesas.length === 0) {
            container.innerHTML = '<p class="col-span-full text-center text-[var(--text-muted)] py-12 font-bold">No hay mesas registradas.</p>';
            return;
        }

        estadoGlobal.mesas.forEach(mesa => {
            // Aplicar filtro
            if (estadoGlobal.filtroActual !== 'todos') {
                const estado = mesa.ocupada ? 'ocupada' : 'libre';
                if (estado !== estadoGlobal.filtroActual) return;
            }

            const card = crearCardMesa(mesa);
            container.appendChild(card);
        });
    }

    // Crear tarjeta de mesa
    function crearCardMesa(mesa) {
        const card = document.createElement('button');
        const estaOcupada = mesa.ocupada;
        
        card.className = `relative group transition-all duration-300 transform hover:scale-105 focus:outline-none`;
        card.onclick = () => irACobrar(mesa.id);

        // Colores según estado
        const colorBg = estaOcupada ? 'from-red-900 to-red-800' : 'from-green-900 to-green-800';
        const colorBorde = estaOcupada ? 'border-red-500' : 'border-green-500';
        const colorIcono = estaOcupada ? 'text-red-300' : 'text-green-300';
        const estadoTexto = estaOcupada ? 'OCUPADA' : 'LIBRE';

        card.innerHTML = `
            <div class="glass-card bg-gradient-to-br ${colorBg} border-2 ${colorBorde} rounded-2xl p-4 shadow-lg hover:shadow-2xl transition-all h-full flex flex-col justify-between group-hover:border-blue-400">
                {{-- Número de mesa --}}
                <div class="text-center mb-2">
                    <div class="text-4xl font-black text-white tracking-tighter">${mesa.numero}</div>
                    <p class="text-[9px] uppercase tracking-widest text-white/70 mt-0.5">Mesa</p>
                </div>

                {{-- Ícono de estado --}}
                <div class="flex justify-center mb-2">
                    <div class="w-10 h-10 rounded-full ${estaOcupada ? 'bg-red-500/20' : 'bg-green-500/20'} flex items-center justify-center">
                        <i class="fas ${estaOcupada ? 'fa-users' : 'fa-chair'} text-lg ${colorIcono}"></i>
                    </div>
                </div>

                {{-- Info --}}
                <div class="text-center space-y-1">
                    <p class="text-xs font-bold text-white uppercase tracking-tighter">${estadoTexto}</p>
                    <p class="text-sm font-bold text-white/80">Cap: <span class="text-base text-white">${mesa.capacidad}</span></p>
                    ${estaOcupada ? `<p class="text-[8px] text-white/80 font-bold"><i class="fas fa-receipt mr-1"></i>${mesa.ordenes_activas} orden${mesa.ordenes_activas !== 1 ? 'es' : ''}</p>` : ''}
                </div>

                {{-- Botón CTA --}}
                <div class="mt-4 pt-4 border-t border-white/20 space-y-3">
                    <p class="text-xs font-bold text-white/80 uppercase tracking-widest group-hover:text-blue-300 transition text-center">
                        ${estaOcupada ? '→ Cobrar' : '+ Nueva orden'}
                    </p>
                    <div class="flex gap-2 flex-wrap justify-center">
                        <button type="button" onclick="event.stopPropagation(); abrirModalEditarMesa(${mesa.id})" class="flex-1 min-w-16 px-2 py-2 rounded-lg bg-blue-500/20 text-blue-300 text-[9px] font-black uppercase tracking-wider hover:bg-blue-500/40 transition border border-blue-500/30">Editar</button>
                        <button type="button" onclick="event.stopPropagation(); eliminarMesa(${mesa.id})" class="flex-1 min-w-16 px-2 py-2 rounded-lg bg-red-500/20 text-red-300 text-[9px] font-black uppercase tracking-wider hover:bg-red-500/40 transition border border-red-500/30">Eliminar</button>
                    </div>
                </div>
            </div>
        `;

        return card;
    }

    // Abrir modal de edición de mesa
    function abrirModalEditarMesa(id) {
        const mesa = estadoGlobal.mesas.find(m => m.id === id);
        if (!mesa) return;

        document.getElementById('editarMesaId').value = mesa.id;
        document.getElementById('editarMesaNumero').value = mesa.numero;
        document.getElementById('editarMesaCapacidad').value = mesa.capacidad;
        document.getElementById('modalEditarMesa').classList.remove('hidden', 'opacity-0');
    }

    // Cerrar modal de edición de mesa
    function cerrarModalEditarMesa() {
        document.getElementById('modalEditarMesa').classList.add('hidden', 'opacity-0');
    }

    async function guardarMesaEditada() {
        const id = document.getElementById('editarMesaId').value;
        const numero = document.getElementById('editarMesaNumero').value.trim();
        const capacidad = parseInt(document.getElementById('editarMesaCapacidad').value, 10);
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (!numero || !capacidad) {
            alert('Completa el número y la capacidad de la mesa.');
            return;
        }

        const response = await fetch(`/admin/mesas/api/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ numero, capacidad })
        });

        const data = await response.json();
        if (response.ok && data.success) {
            cerrarModalEditarMesa();
            cargarMesas();
        } else {
            alert(data.message || 'No se pudo actualizar la mesa.');
        }
    }

    async function eliminarMesa(id) {
        const mesa = estadoGlobal.mesas.find(m => m.id === id);
        if (!mesa) return;

        document.getElementById('eliminarMesaId').value = mesa.id;
        document.getElementById('eliminarMesaNumero').textContent = mesa.numero;
        document.getElementById('modalEliminarMesa').classList.remove('hidden', 'opacity-0');
    }

    // Cerrar modal de eliminación de mesa
    function cerrarModalEliminarMesa() {
        document.getElementById('modalEliminarMesa').classList.add('hidden', 'opacity-0');
    }

    async function confirmarEliminarMesa() {
        const id = document.getElementById('eliminarMesaId').value;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const response = await fetch(`/admin/mesas/api/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            }
        });

        const data = await response.json();
        if (response.ok && data.success) {
            cerrarModalEliminarMesa();
            cargarMesas();
        } else {
            alert(data.message || 'No se pudo eliminar la mesa.');
        }
    }

    // Filtrar mesas
    function filtrarMesas(filtro) {
        estadoGlobal.filtroActual = filtro;
        
        // Actualizar botones
        document.querySelectorAll('.filtro-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-500', 'text-white');
            btn.classList.add('bg-gray-700', 'hover:bg-gray-600', 'text-gray-300');
        });
        
        document.querySelector(`[data-filtro="${filtro}"]`).classList.remove('bg-gray-700', 'hover:bg-gray-600', 'text-gray-300');
        document.querySelector(`[data-filtro="${filtro}"]`).classList.add('bg-blue-600', 'hover:bg-blue-500', 'text-white');
        
        renderizarMesas();
    }

    // Ir a cobrar
    function irACobrar(mesaId) {
        window.location.href = `/admin/caja/cobrar/${mesaId}`;
    }
</script>

@endsection
