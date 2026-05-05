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
        <button onclick="filtrarMesas('libre')" class="px-4 py-2 rounded-xl font-bold transition bg-gray-700 hover:bg-gray-600 text-gray-300 filtro-btn" data-filtro="libre">
            <i class="fas fa-chair mr-2"></i>Libres
        </button>
        <button onclick="filtrarMesas('ocupada')" class="px-4 py-2 rounded-xl font-bold transition bg-gray-700 hover:bg-gray-600 text-gray-300 filtro-btn" data-filtro="ocupada">
            <i class="fas fa-users mr-2"></i>Ocupadas
        </button>
    </div>

    {{-- GRID DE MESAS --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4" id="mesas-container">
        {{-- Se llena dinámicamente con JavaScript --}}
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
            <div class="glass-card bg-gradient-to-br ${colorBg} border-2 ${colorBorde} rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all h-full flex flex-col justify-between group-hover:border-blue-400">
                {{-- Número de mesa --}}
                <div class="text-center mb-4">
                    <div class="text-5xl font-black text-white tracking-tighter">${mesa.numero}</div>
                    <p class="text-xs uppercase tracking-widest text-white/70 mt-1">Mesa</p>
                </div>

                {{-- Ícono de estado --}}
                <div class="flex justify-center mb-4">
                    <div class="w-12 h-12 rounded-full ${estaOcupada ? 'bg-red-500/20' : 'bg-green-500/20'} flex items-center justify-center">
                        <i class="fas ${estaOcupada ? 'fa-users' : 'fa-chair'} text-2xl ${colorIcono}"></i>
                    </div>
                </div>

                {{-- Info --}}
                <div class="text-center space-y-2">
                    <p class="text-sm font-bold text-white uppercase tracking-tighter">${estadoTexto}</p>
                    <p class="text-xs text-white/60">Cap: ${mesa.capacidad} personas</p>
                    ${estaOcupada ? `<p class="text-xs text-white/80 font-bold"><i class="fas fa-receipt mr-1"></i>${mesa.ordenes_activas} orden${mesa.ordenes_activas !== 1 ? 'es' : ''}</p>` : ''}
                </div>

                {{-- Botón CTA --}}
                <div class="mt-4 pt-4 border-t border-white/20">
                    <p class="text-xs font-bold text-white/80 uppercase tracking-widest group-hover:text-blue-300 transition">
                        ${estaOcupada ? '→ Cobrar' : '+ Nueva orden'}
                    </p>
                </div>
            </div>
        `;

        return card;
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
