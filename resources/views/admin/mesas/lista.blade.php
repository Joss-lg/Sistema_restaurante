{{-- VISTA: GRID DE MESAS (Lista Normal) --}}
<div id="vista-lista-wrapper" class="hidden flex-1 overflow-y-auto hide-scroll pb-6">
    {{-- Este contenedor se poblará dinámicamente desde mesas.js --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3" 
         id="mesas-container">
        {{-- Aquí se inyectarán las tarjetas mediante renderizarVistaLista() --}}
    </div>
</div>