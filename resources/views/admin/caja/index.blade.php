@extends('layouts.admin')

@section('title', 'Caja | Ollintem Pro')

@section('content')
@php
    // $mesasLibres ahora llega desde CajaController: ya NO se puede calcular
    // aquí porque $mesas viene filtrada y solo trae las mesas con cuenta
    // abierta, así que este conteo siempre daría 0.
    $mesasLibres = $mesasLibres ?? 0;
@endphp

<div id="toastContainer" class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-8 sm:bottom-8 z-[9999] flex flex-col gap-3 items-center sm:items-end" aria-live="polite" aria-atomic="true"></div>

{{-- Contenedor principal --}}
<div class="px-3 py-4 sm:px-4 sm:py-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-5 sm:space-y-8 relative z-10 font-sans overflow-x-hidden min-h-screen bg-white dark:bg-[#15171c] transition-colors duration-300">
    
    {{-- ALERTAS DE SESIÓN --}}
    @if(session('error'))
        <div class="p-3 sm:p-4 mb-4 text-xs sm:text-sm text-red-700 bg-red-100 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 rounded-2xl">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="p-3 sm:p-4 mb-4 text-xs sm:text-sm text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800 rounded-2xl">
            {{ session('success') }}
        </div>
    @endif
    
    {{-- HEADER Y PANEL FINANCIERO --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-5 sm:gap-6 w-full">
        <div class="space-y-2.5 sm:space-y-3 w-full xl:w-auto flex flex-col sm:flex-row sm:items-center sm:justify-between xl:flex-col xl:items-start">
            <div class="w-full">
                <div class="inline-flex items-center gap-2 rounded-full px-2.5 sm:px-3 py-1 sm:py-1.5 text-[10px] sm:text-xs font-bold uppercase tracking-wider shadow-sm transition-colors bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400 max-w-full flex-wrap">
                    <span class="h-2 w-2 rounded-full bg-blue-600 animate-pulse shrink-0"></span>
                    <span class="truncate">Panel Financiero [Turno: {{ $cajaActiva->turno ?? 'N/A' }}]</span>
                </div>
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black tracking-tighter break-words text-gray-900 dark:text-slate-100 mt-1">Panel de Caja</h1>
            </div>
        </div>
        
        {{-- TARJETAS ESTADÍSTICAS --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-4 w-full xl:w-auto">
    <!-- TARJETA TOTAL ABIERTO -->
    <div class="col-span-2 sm:col-span-1 p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl border border-gray-100 dark:border-slate-700/50 bg-gray-50/50 dark:bg-[#1e2026]/40 shadow-sm flex flex-col justify-center w-full transition-colors duration-300">
        
        <!-- Contenedor Flex para Título y Botón del Ojito -->
        <div class="flex justify-between items-center w-full">
            <p class="text-gray-500 dark:text-slate-400 text-[10px] sm:text-xs font-bold uppercase tracking-widest">Total abierto</p>
            
            <button id="btn-toggle-total" class="text-gray-400 hover:text-emerald-500 transition-colors focus:outline-none p-1 rounded-md" aria-label="Mostrar/Ocultar total">
                <!-- Ojo cerrado -->
                <svg id="icon-eye-closed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 sm:w-5 sm:h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
                <!-- Ojo abierto -->
                <svg id="icon-eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 sm:w-5 sm:h-5 hidden">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </button>
        </div>

        <p class="mt-1 sm:mt-2 text-xl sm:text-4xl font-black text-emerald-500 tracking-tighter" 
           id="total-abierto-display" 
           data-real-value="${{ number_format($totalAbierto ?? 0, 2) }}">
            $***
        </p>
    </div>

    <!-- TARJETA MESAS ACTIVAS -->
    <div class="p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl border border-gray-100 dark:border-slate-700/50 bg-gray-50/50 dark:bg-[#1e2026]/40 shadow-sm flex flex-col justify-center w-full transition-colors duration-300">
        <p class="text-gray-500 dark:text-slate-400 text-[10px] sm:text-xs font-bold uppercase tracking-widest">Mesas activas</p>
        <p class="mt-1 sm:mt-2 text-xl sm:text-4xl font-black tracking-tighter text-gray-900 dark:text-slate-100" id="mesas-activas-display">{{ $mesasActivas ?? 0 }}</p>
    </div>

    <!-- TARJETA MESAS LIBRES -->
    <div class="p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl border border-gray-100 dark:border-slate-700/50 bg-gray-50/50 dark:bg-[#1e2026]/40 shadow-sm flex flex-col justify-center w-full transition-colors duration-300">
        <p class="text-gray-500 dark:text-slate-400 text-[10px] sm:text-xs font-bold uppercase tracking-widest">Mesas libres</p>
        <p class="mt-1 sm:mt-2 text-xl sm:text-4xl font-black tracking-tighter text-gray-500 dark:text-slate-400" id="mesas-libres-display">{{ $mesasLibres }}</p>
    </div>
</div>
</div> <!-- ESTE ES EL DIV CLAVE QUE FALTABA PARA QUE NO SE ROMPA TU DISEÑO -->

<!-- SCRIPT JS -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btnToggle = document.getElementById('btn-toggle-total');
        const displayTotal = document.getElementById('total-abierto-display');
        const iconClosed = document.getElementById('icon-eye-closed');
        const iconOpen = document.getElementById('icon-eye-open');

        let isHidden = true; 

        btnToggle.addEventListener('click', () => {
            isHidden = !isHidden; 
            if (isHidden) {
                displayTotal.textContent = '$***';
                iconClosed.classList.remove('hidden');
                iconOpen.classList.add('hidden');
            } else {
                displayTotal.textContent = displayTotal.getAttribute('data-real-value');
                iconClosed.classList.add('hidden');
                iconOpen.classList.remove('hidden');
            }
        });
    });
</script>

    {{-- GRID DE MESAS --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-6 pt-1 sm:pt-4 w-full" id="mesas-container">
        @include('admin.caja.partials.mesas')
    </div>
</div>

<script>
    window.mostrarToast = function(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        
        const toast = document.createElement('div');
        const typeClasses = type === 'success' ? 'border-l-4 border-emerald-500' : 'border-l-4 border-red-500';

        toast.className = `w-full sm:min-w-[300px] sm:w-auto p-4 rounded-2xl bg-white dark:bg-[#1e2026] border border-gray-200 dark:border-slate-700 shadow-xl flex items-center gap-3 opacity-0 translate-y-3 sm:translate-y-0 sm:translate-x-5 transition-all duration-300 ${typeClasses}`;
        toast.innerHTML = `<div><strong class="block text-sm font-bold text-gray-900 dark:text-white">${type === 'success' ? 'Éxito' : 'Error'}</strong><span class="text-xs text-gray-500 dark:text-slate-400">${message}</span></div>`;
        
        container.appendChild(toast);
        
        setTimeout(() => { toast.classList.remove('opacity-0', 'translate-y-3', 'sm:translate-x-5'); }, 50);
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-3', 'sm:translate-x-5');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    };

    document.addEventListener('DOMContentLoaded', function () {
        // --- FILTROS POR ESTADO ---
        // Se usa delegación en el contenedor (y no querySelectorAll una sola
        // vez) porque las tarjetas se reemplazan cada 5s con el auto-refresco:
        // las referencias capturadas al cargar apuntarían a elementos que ya
        // no existen y el filtro dejaría de funcionar tras el primer refresco.
        const botonesFiltro = document.querySelectorAll('[data-filter]');
        let filtroActivo = 'all';

        const aplicarFiltro = () => {
            document.querySelectorAll('[data-mesa-status]').forEach(card => {
                const coincide = filtroActivo === 'all' || card.dataset.mesaStatus === filtroActivo;
                card.style.display = coincide ? 'flex' : 'none';
                card.style.opacity = coincide ? '1' : '0';
            });
        };

        botonesFiltro.forEach(boton => {
            boton.addEventListener('click', () => {
                botonesFiltro.forEach(b => b.classList.remove('filter-button--active'));
                boton.classList.add('filter-button--active');
                filtroActivo = boton.dataset.filter;
                aplicarFiltro();
            });
        });

        // ---------------------------------------------------------------
        // AUTO-REFRESCO (mismo patrón que Cocina)
        // Consulta un endpoint que devuelve solo las tarjetas y los
        // contadores. Antes se descargaba la página entera cada 4s y se
        // recortaba con DOMParser: mucho más pesado y, si fallaba, el error
        // se tragaba en silencio.
        // ---------------------------------------------------------------
        const URL_API_MESAS = @json(route('admin.caja.api.mesas'));
        let refrescando = false;

        async function actualizarMesas() {
            // Evita que se encimen dos consultas si el servidor va lento.
            if (refrescando || document.hidden) return;
            refrescando = true;

            try {
                const res = await fetch(URL_API_MESAS, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });

                if (!res.ok) {
                    console.error('apiMesas falló:', res.status);
                    return;
                }

                const data = await res.json();

                // La caja se cerró desde otra terminal: recargamos para que
                // aparezca la pantalla de apertura en vez de dejar tarjetas
                // de mesas que ya no se pueden cobrar.
                if (data && data.caja_cerrada) {
                    window.location.reload();
                    return;
                }

                if (!data || !data.success) {
                    console.error('apiMesas respondió sin success:', data);
                    return;
                }

                const contenedor = document.getElementById('mesas-container');
                if (contenedor && contenedor.innerHTML.trim() !== data.html.trim()) {
                    contenedor.innerHTML = data.html;
                    aplicarFiltro(); // reaplicar el filtro a las tarjetas nuevas
                }

                const setTexto = (id, valor) => {
                    const el = document.getElementById(id);
                    if (el && el.innerText !== String(valor)) el.innerText = valor;
                };
                setTexto('total-abierto-display', data.totalAbierto);
                setTexto('mesas-activas-display', data.mesasActivas);
                setTexto('mesas-libres-display', data.mesasLibres);

            } catch (err) {
                console.error('Error actualizando mesas de caja:', err);
            } finally {
                refrescando = false;
            }
        }

        setInterval(actualizarMesas, 5000);

        // Al volver a la pestaña se actualiza de inmediato, sin esperar los 5s.
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) actualizarMesas();
        });
    });
</script>
@endsection