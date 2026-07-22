@extends('layouts.admin') {{-- Reemplaza con tu layout base si es necesario --}}

@section('content')
<style>
    /* Solo aplicamos el truco de subir la tarjeta en pantallas grandes (computadoras/punto de venta) */
    @media (min-width: 768px) {
        /* 1. Mandamos la tarjeta a la parte de arriba de la pantalla */
        body.teclado-virtual-abierto #aperturaCajaWrapper {
            align-items: flex-start !important;
            padding-top: 15px !important;
        }

        /* 2. Hacemos que la tarjeta sea más corta para que no choque con el teclado y active el scroll interno */
        body.teclado-virtual-abierto #aperturaCajaCard {
            max-height: calc(100dvh - 340px) !important;
            overflow-y: auto !important;
        }
    }
</style>

<div id="aperturaCajaWrapper" class="flex items-center justify-center min-h-[80vh] bg-gray-100 dark:bg-gray-900 px-4 py-8">
    <div id="aperturaCajaCard" class="max-w-md w-full bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 sm:p-8 border border-gray-200 dark:border-gray-700 transition-all duration-300">
        
        <div class="text-center mb-6">
            <div class="inline-flex p-3 bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300 rounded-full mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Apertura de Caja</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Para comenzar a gestionar mesas y registrar cobros, es necesario iniciar un turno operativo.
            </p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm dark:bg-red-900 dark:text-red-200 dark:border-red-800">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm dark:bg-green-900 dark:text-green-200 dark:border-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.caja.abrir') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="turno" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seleccionar Turno</label>
                <select name="turno" id="turno" required 
                    class="w-full h-12 text-base rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('turno') border-red-500 @enderror">
                    <option value="" disabled selected>-- Elige el turno actual --</option>
                    <option value="Matutino" {{ old('turno') == 'Matutino' ? 'selected' : '' }}>☀️ Matutino</option>
                    <option value="Vespertino" {{ old('turno') == 'Vespertino' ? 'selected' : '' }}>🌙 Vespertino</option>
                </select>
                @error('turno')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="monto_inicial" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monto Inicial (Fondo de Caja)</label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
                    </div>
                    {{-- TECLADO VIRTUAL NUMÉRICO: type=text (no number) para que el teclado personalizado pueda escribir el valor --}}
                    <input type="text" name="monto_inicial" id="monto_inicial" pattern="[0-9]*\.?[0-9]*" required readonly data-teclado="numerico" data-teclado-titulo="Monto Inicial" inputmode="none"
                        value="{{ old('monto_inicial', '0.00') }}"
                        class="w-full h-12 pl-7 text-base rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500 @error('monto_inicial') border-red-500 @enderror"
                        placeholder="0.00"
                        onfocus="this.select()">
                </div>
                @error('monto_inicial')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" 
                class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 font-semibold">
                Iniciar Turno e Ir a Mesas
            </button>
        </form>

    </div>
</div>


<script>
    // Nos aseguramos de que el teclado virtual detecte el campo numérico de esta vista.
    // Si tu layout ya llama a esto globalmente, esta llamada es redundante pero inofensiva.
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TecladoVirtual !== 'undefined') {
            TecladoVirtual.attachAll();
        }
    });
</script>
@endsection