@extends('layouts.admin')

@section('title', 'Cobrar Mesa | Ollintem Pro')
@section('no-sidebar', 'true')

@section('content')
@php
    // Cuando se divide "por consumo" los contadores +/- por persona
    // necesitan más ancho horizontal, así que le damos más espacio al
    // panel de la izquierda en pantallas grandes.
    $esPorProducto = ($division['tipo'] ?? null) === 'por_producto';
    $anchoIzquierda = $esPorProducto ? 'lg:w-3/5' : 'lg:w-2/5';
    $anchoDerecha   = $esPorProducto ? 'lg:w-2/5' : 'lg:w-3/5';
@endphp
<div class="flex flex-col lg:flex-row min-h-screen lg:h-screen lg:overflow-hidden bg-zinc-100 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
    
    {{-- IZQUIERDA: Detalle --}}
    <div class="w-full {{ $anchoIzquierda }} border-r border-zinc-200 dark:border-white/10 bg-white dark:bg-zinc-900 flex flex-col border-b lg:border-b-0 lg:overflow-hidden lg:min-h-0 shadow-sm">
        <div class="p-4 sm:p-5 border-b border-zinc-200 dark:border-white/10">
            <a href="{{ route('admin.caja.index') }}" class="text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white text-[10px] font-black flex items-center gap-2 mb-1 transition-all hover:translate-x-1 uppercase tracking-widest">
                <i class="fas fa-arrow-left"></i> VOLVER A CAJA
            </a>
            
            <h1 class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white italic tracking-tighter uppercase break-words">
                @if($mesa->esDelivery())
                    <i class="fas fa-motorcycle text-orange-500 mr-1"></i> {{ $mesa->plataformaDelivery->nombre ?? 'Delivery' }} · {{ $mesa->numero }}
                @else
                    Mesa {{ $mesa->numero }}
                @endif
            </h1>
            
            <p class="text-[11px] sm:text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wide mt-0.5">
                {{ $orden->numero_orden ?? 'ORDEN SIN NÚMERO' }} • {{ $orden->mesero->nombre ?? 'MESERO NO ASIGNADO' }}
            </p>

            {{-- DESCUENTO DE LA CUENTA
                 Se movió aquí desde el módulo de Mesas: ahora lo autoriza
                 quien cobra. Requiere permiso de EDITAR en Caja; la ruta lo
                 vuelve a validar en el servidor. --}}
            @if(auth()->user()->tienePermiso('Caja', 'editar'))
                <div class="mt-3 flex items-center gap-2">
                    <div class="relative">
                        <input type="text" inputmode="decimal" data-teclado="numerico"
                               id="input-descuento-caja"
                               value="{{ ($descuentoPorcentaje ?? 0) > 0 ? rtrim(rtrim(number_format($descuentoPorcentaje, 2, '.', ''), '0'), '.') : '' }}"
                               placeholder="0"
                               class="w-20 pl-3 pr-6 py-1.5 rounded-lg border border-zinc-300 dark:border-white/10 bg-white dark:bg-zinc-950 text-sm font-bold text-zinc-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-bold text-zinc-400">%</span>
                    </div>
                    <button type="button" id="btn-aplicar-descuento-caja"
                        class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-[10px] font-black uppercase tracking-wider transition-colors">
                        Aplicar descuento
                    </button>
                    <span id="msg-descuento-caja" class="hidden text-[11px] font-bold"></span>
                </div>
            @endif

            {{-- CANCELAR CUENTA SIN COBRAR
                 Solo se muestra a quien tenga permiso de ELIMINAR en Caja.
                 Ocultarlo es comodidad; el bloqueo real lo hace el middleware
                 'permiso:Caja,eliminar' de la ruta. --}}
            @if(auth()->user()->tienePermiso('Caja', 'eliminar'))
                <button type="button" id="btn-abrir-cancelar-cuenta"
                    class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-rose-300 dark:border-rose-900/60 text-rose-600 dark:text-rose-400 text-[10px] font-black uppercase tracking-wider hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors">
                    <i class="fas fa-ban"></i> Cancelar cuenta sin cobrar
                </button>
            @endif
        </div>

        <div class="flex-1 lg:min-h-0 lg:overflow-y-auto custom-scrollbar">
            @include('admin.cobrar.partials.detalle-cuenta')
        </div>
    </div>

    {{-- DERECHA: Pago --}}
    @include('admin.cobrar.partials.panel-pago')
</div>

{{-- Modales integrados --}}
@include('admin.cobrar.modals.metodo-pago')
@include('admin.cobrar.modals.exito')
@include('admin.cobrar.modals.error')
@include('admin.cobrar.modals.ticket-preview')
@if(auth()->user()->tienePermiso('Caja', 'eliminar'))
    @include('admin.cobrar.modals.cancelar-cuenta')
@endif
@endsection

@push('scripts')
@vite(['resources/js/cobro.js'])
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.COBRO_CONFIG = {
            mesaId: {{ $mesa->id }},
            // AJUSTE: se deja de mandar ordenId (solo tomaba la primera orden
            // de la mesa y se perdían productos/total si había más de una).
            // El ticket ahora se imprime por MESA completa, agregando todas
            // sus órdenes activas — la misma unidad que ya usa el desglose
            // que ves en pantalla (subtotal, IVA, propina, total).
            urlTicket: "{{ route('admin.caja.ticket.imprimir', $mesa->id) }}",
            total: {{ $totalPagar ?? 0 }},
            csrfToken: "{{ csrf_token() }}",
            urlPago: "{{ route('admin.caja.procesar-pago') }}",
            // NUEVO: endpoints y datos para la división de cuenta
            urlDivisionIniciar: "{{ route('admin.caja.division.iniciar') }}",
            urlDivisionAsignar: "{{ route('admin.caja.division.asignar') }}",
            urlDivisionCancelar: "{{ route('admin.caja.division.cancelar') }}",
            division: @json($division ?? null)
        };

        // --- DESCUENTO DE LA CUENTA (movido desde el módulo de Mesas) ---
        const btnDescuento = document.getElementById('btn-aplicar-descuento-caja');
        const inputDescuento = document.getElementById('input-descuento-caja');
        const msgDescuento = document.getElementById('msg-descuento-caja');

        if (btnDescuento && inputDescuento) {
            btnDescuento.addEventListener('click', async () => {
                // El campo es de texto para que el teclado táctil escriba el
                // punto decimal; se acepta también la coma.
                const crudo = (inputDescuento.value || '').trim().replace(',', '.');
                const porcentaje = crudo === '' ? 0 : parseFloat(crudo);

                const avisar = (texto, ok) => {
                    msgDescuento.textContent = texto;
                    msgDescuento.className = 'text-[11px] font-bold ' + (ok ? 'text-emerald-500' : 'text-rose-500');
                    msgDescuento.classList.remove('hidden');
                };

                if (isNaN(porcentaje) || porcentaje < 0 || porcentaje > 100) {
                    avisar('Escribe un porcentaje entre 0 y 100', false);
                    return;
                }

                btnDescuento.disabled = true;
                const textoOriginal = btnDescuento.textContent;
                btnDescuento.textContent = 'Aplicando...';

                try {
                    const res = await fetch(@json(route('admin.caja.cuenta.descuento')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ mesa_id: {{ $mesa->id }}, porcentaje: porcentaje }),
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        // Se recarga para que el desglose, el total y las
                        // partes de la división queden con el nuevo importe:
                        // todos esos números salen del servidor.
                        window.location.reload();
                        return;
                    }

                    avisar(data.message || 'No se pudo aplicar el descuento.', false);
                } catch (e) {
                    console.error('Error al aplicar descuento:', e);
                    avisar('Error de conexión.', false);
                } finally {
                    btnDescuento.disabled = false;
                    btnDescuento.textContent = textoOriginal;
                }
            });
        }
    });
</script>
@endpush