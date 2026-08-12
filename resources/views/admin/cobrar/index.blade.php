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

{{-- ═══════════════════════════════════════════════════════
     MODAL NIP — Compartido para Descuento y Cancelar Cuenta
     ════════════════════════════════════════════════════════ --}}
<div id="modal-nip-caja" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="cerrarModalNipCaja()"></div>
    <div class="relative w-full max-w-xs bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">

        {{-- Header --}}
        <div id="mnc-header" class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div id="mnc-icono-wrap" class="w-9 h-9 rounded-xl flex items-center justify-center">
                    <i id="mnc-icono" class="fas fa-lock text-sm"></i>
                </div>
                <div>
                    <h3 id="mnc-titulo" class="text-sm font-black text-zinc-900 dark:text-white">Autorización</h3>
                    <p id="mnc-subtitulo" class="text-[11px] text-zinc-500 dark:text-zinc-400">Ingresa el NIP del Administrador</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalNipCaja()"
                class="w-8 h-8 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-500 hover:text-zinc-900 dark:hover:text-white flex items-center justify-center transition-colors">
                <i class="fas fa-xmark text-xs"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 space-y-4">
            {{-- Dots --}}
            <div class="flex justify-center gap-3 py-1">
                @for($i = 0; $i < 4; $i++)
                    <div class="mnc-dot w-4 h-4 rounded-full border-2 border-zinc-300 dark:border-zinc-600 bg-transparent transition-all duration-150"></div>
                @endfor
            </div>

            {{-- Teclado --}}
            <div class="grid grid-cols-3 gap-2">
                @foreach(['1','2','3','4','5','6','7','8','9'] as $k)
                    <button type="button" onclick="mncEscribir('{{ $k }}')"
                        class="h-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white font-black text-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 active:scale-95 transition-all">
                        {{ $k }}
                    </button>
                @endforeach
                <button type="button" onclick="mncBorrar()"
                    class="h-12 rounded-2xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-500 flex items-center justify-center hover:bg-red-100 dark:hover:bg-red-500/20 active:scale-95 transition-all">
                    <i class="fas fa-delete-left text-base"></i>
                </button>
                <button type="button" onclick="mncEscribir('0')"
                    class="h-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white font-black text-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 active:scale-95 transition-all">
                    0
                </button>
                <button type="button" id="mnc-btn-ok" onclick="mncConfirmar()"
                    class="h-12 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-black text-sm active:scale-95 transition-all flex items-center justify-center gap-1.5">
                    <i class="fas fa-check text-xs"></i> OK
                </button>
            </div>

            {{-- Error --}}
            <p id="mnc-error" class="hidden text-center text-xs font-bold text-red-500 bg-red-50 dark:bg-red-500/10 rounded-xl py-2 px-3"></p>
        </div>
    </div>
</div>

<script>
(function () {
    let _mncNip      = '';
    let _mncCallback = null;

    const dots    = () => document.querySelectorAll('.mnc-dot');
    const errorEl = () => document.getElementById('mnc-error');
    const btnOk   = () => document.getElementById('mnc-btn-ok');

    function actualizarDots() {
        dots().forEach((d, i) => {
            if (i < _mncNip.length) {
                d.classList.add('bg-zinc-900', 'dark:bg-white', 'border-zinc-900', 'dark:border-white');
                d.classList.remove('border-zinc-300', 'dark:border-zinc-600', 'bg-transparent');
            } else {
                d.classList.remove('bg-zinc-900', 'dark:bg-white', 'border-zinc-900', 'dark:border-white');
                d.classList.add('border-zinc-300', 'dark:border-zinc-600', 'bg-transparent');
            }
        });
    }

    window.abrirModalNipCaja = function ({ titulo, subtitulo, icono, colorIcono, onConfirm }) {
        _mncNip      = '';
        _mncCallback = onConfirm;

        document.getElementById('mnc-titulo').textContent    = titulo || 'Autorización';
        document.getElementById('mnc-subtitulo').textContent = subtitulo || 'Ingresa el NIP del Administrador';

        const wrap = document.getElementById('mnc-icono-wrap');
        const ico  = document.getElementById('mnc-icono');
        wrap.className = `w-9 h-9 rounded-xl flex items-center justify-center bg-${colorIcono || 'blue'}-500/15 border border-${colorIcono || 'blue'}-500/20`;
        ico.className  = `fas ${icono || 'fa-lock'} text-${colorIcono || 'blue'}-500 text-sm`;

        actualizarDots();
        if (errorEl()) errorEl().classList.add('hidden');
        document.getElementById('modal-nip-caja').classList.remove('hidden');
    };

    window.cerrarModalNipCaja = function () {
        document.getElementById('modal-nip-caja').classList.add('hidden');
        _mncNip = '';
        _mncCallback = null;
    };

    window.mncEscribir = function (digit) {
        if (_mncNip.length >= 4) return;
        _mncNip += digit;
        actualizarDots();
        if (errorEl()) errorEl().classList.add('hidden');
        if (_mncNip.length === 4) mncConfirmar();
    };

    window.mncBorrar = function () {
        _mncNip = _mncNip.slice(0, -1);
        actualizarDots();
    };

    window.mncConfirmar = async function () {
        if (!_mncNip || _mncNip.length < 1) return;
        if (!_mncCallback) return;

        const btn = btnOk();
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>'; }

        try {
            await _mncCallback(_mncNip);
        } catch (err) {
            const el = errorEl();
            if (el) { el.textContent = err.message || 'Error al verificar.'; el.classList.remove('hidden'); }
            _mncNip = '';
            actualizarDots();
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check text-xs"></i> OK'; }
        }
    };

    document.addEventListener('keydown', function (e) {
        const modal = document.getElementById('modal-nip-caja');
        if (!modal || modal.classList.contains('hidden')) return;
        if (e.key === 'Escape') cerrarModalNipCaja();
        else if (e.key === 'Backspace') mncBorrar();
        else if (/^[0-9]$/.test(e.key)) mncEscribir(e.key);
    });
})();
</script>

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
            btnDescuento.addEventListener('click', () => {
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

                // Pedir NIP antes de aplicar el descuento
                abrirModalNipCaja({
                    titulo: 'Autorizar descuento',
                    subtitulo: `Descuento del ${porcentaje}% — ingresa tu NIP`,
                    icono: 'fa-tag',
                    colorIcono: 'blue',
                    onConfirm: async (nip) => {
                        // Verificar NIP
                        const resNip = await fetch('/mesero/capitan/verify', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                            body: JSON.stringify({ nip })
                        });
                        const dataNip = await resNip.json().catch(() => null);
                        if (!resNip.ok || !dataNip?.success) {
                            throw new Error(dataNip?.message || 'NIP incorrecto.');
                        }

                        // Aplicar descuento
                        const res = await fetch(@json(route('admin.caja.cuenta.descuento')), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                            body: JSON.stringify({ mesa_id: {{ $mesa->id }}, porcentaje: porcentaje }),
                        });
                        const data = await res.json();
                        if (!res.ok || !data.success) throw new Error(data.message || 'No se pudo aplicar el descuento.');
                        window.location.reload();
                    }
                });
            });
        }
    });
</script>
@endpush