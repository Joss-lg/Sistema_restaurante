{{--
    resources/views/admin/caja/partials/mesas.blade.php

    Tarjetas de las mesas/pedidos con cuenta abierta.

    Vive en un parcial porque lo usan DOS lugares: el render inicial de la
    pantalla y el endpoint admin.caja.api.mesas, que devuelve este mismo HTML
    para el auto-refresco cada 5s. Si estuviera duplicado, cualquier cambio
    de diseño habría que hacerlo en dos sitios y tarde o temprano se
    desincronizan.

    Recibe: $mesas
--}}
@forelse ($mesas as $mesa)
            @php
                $cuenta = $mesa->ordenesActivas->first() ?? null; 
            @endphp

            @if($cuenta)
                {{-- MESA CON ORDEN ACTIVA --}}
                <a href="{{ route('admin.caja.cobrar', $mesa->id) }}" 
                   data-mesa-status="{{ $mesa->estado }}"
                   class="group relative flex flex-col w-full rounded-2xl sm:rounded-3xl border border-emerald-200 dark:border-emerald-800/60 bg-emerald-50/10 dark:bg-emerald-950/10 shadow-sm hover:shadow-md active:scale-[0.98] cursor-pointer transition-all duration-300 hover:-translate-y-1 overflow-hidden p-3.5 sm:p-6">
                    <div class="relative z-10 flex-1 flex flex-col w-full">
                        <div class="flex justify-between items-start mb-3.5 sm:mb-6 w-full">
                            <div class="w-full">
                               <h3 class="text-base sm:text-2xl font-black tracking-tight text-gray-900 dark:text-slate-100 transition-colors group-hover:text-emerald-500 truncate">{{ $mesa->numero }}</h3>
                                @if($mesa->esDelivery())
                                    <p class="inline-flex items-center gap-1 text-[9px] sm:text-[10px] font-black uppercase tracking-wide px-1.5 py-0.5 rounded-md text-white mt-0.5"
                                       style="background-color: {{ $mesa->plataformaDelivery->color ?? '#f97316' }}">
                                        <i class="fas fa-motorcycle"></i> {{ $mesa->plataformaDelivery->nombre ?? 'Delivery' }}
                                    </p>
                                @else
                                    <p class="text-gray-500 dark:text-slate-400 text-[10px] sm:text-xs font-semibold">Cap. {{ $mesa->capacidad }} p.</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-auto w-full space-y-2 sm:space-y-3">
                            <div class="rounded-xl sm:rounded-2xl p-2.5 sm:p-4 flex justify-between items-center w-full border border-emerald-200 dark:border-emerald-800/50 bg-white dark:bg-[#15171c]">
                                <p class="text-sm sm:text-xl font-black text-emerald-600 dark:text-emerald-400">${{ number_format($mesa->total_real ?? 0, 2) }}</p>
                            </div>
                            <div class="w-full py-2.5 sm:py-3.5 flex items-center justify-center rounded-xl sm:rounded-2xl font-bold text-xs sm:text-base bg-emerald-600 dark:bg-emerald-600 text-white shadow-sm transition-all duration-200 group-hover:bg-emerald-700">
                                 <span class="hidden sm:inline ml-1">Cobrar</span>
                            </div>
                        </div>
                    </div>
                </a>
            @else
                {{--  MESA SIN ORDEN --}}
                <div data-mesa-status="{{ $mesa->estado }}"
                   class="relative flex flex-col w-full rounded-2xl sm:rounded-3xl border border-red-200/50 dark:border-red-950/60 bg-red-50/5 dark:bg-red-950/5 shadow-sm overflow-hidden p-3.5 sm:p-6">
                    <div class="relative z-10 flex-1 flex flex-col w-full">
                        <div class="flex justify-between items-start mb-3.5 sm:mb-6 w-full">
                            <div class="w-full">
                               <h3 class="text-base sm:text-2xl font-black tracking-tight text-gray-400 dark:text-slate-500 truncate">{{ $mesa->numero }}</h3>
                                <p class="text-gray-400 dark:text-slate-500 text-[10px] sm:text-xs font-semibold">Cap. {{ $mesa->capacidad }} p.</p>
                            </div>
                        </div>
                        <div class="mt-auto w-full">
                            <div class="w-full py-2.5 sm:py-3.5 flex items-center justify-center rounded-xl sm:rounded-2xl font-bold text-[10px] sm:text-base border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-950/20 text-red-500 dark:text-red-400 cursor-not-allowed select-none text-center leading-tight px-1">
                                <i class="fas fa-ban mr-1"></i> <span class="hidden sm:inline ml-1">Mesa sin orden</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="col-span-2 lg:col-span-3 xl:col-span-4 text-center w-full py-14">
                <i class="fas fa-mug-hot text-4xl text-gray-300 dark:text-slate-700 mb-3"></i>
                <p class="text-gray-500 dark:text-slate-400 font-bold">No hay cuentas abiertas</p>
                <p class="text-gray-400 dark:text-slate-500 text-sm mt-1">
                    Aquí aparecerán las mesas y los pedidos de delivery en cuanto tengan consumo.
                </p>
            </div>
        @endforelse