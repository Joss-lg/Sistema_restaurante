<div id="modalEditar" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm overflow-y-auto overflow-x-hidden p-3 sm:p-4">
    <div class="relative !bg-white dark:!bg-[#121318] border !border-transparent dark:!border-white/5 w-full max-w-2xl rounded-[1.5rem] sm:rounded-[2.5rem] p-5 sm:p-8 lg:p-10 shadow-2xl dark:shadow-[0_20px_50px_rgba(0,0,0,0.5)] my-4 sm:my-8 unique-scrollbar max-h-[92vh] sm:max-h-[90vh] overflow-y-auto overflow-x-hidden">

        {{-- Resplandor decorativo de fondo --}}
        <div class="absolute -top-32 -right-32 w-64 h-64 bg-amber-500/10 dark:bg-amber-500/20 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Cabecera del Modal --}}
        <div class="flex items-center gap-3 sm:gap-4 mb-6 sm:mb-8 relative z-10">
            <div class="!bg-amber-50 dark:!bg-amber-500/20 w-11 h-11 sm:w-12 sm:h-12 rounded-2xl flex items-center justify-center !text-amber-600 dark:!text-amber-500 text-lg sm:text-xl font-black shadow-inner border !border-amber-100 dark:!border-transparent shrink-0">
                <i class="fas fa-pen"></i>
            </div>
            <div>
                <h2 class="text-xl sm:text-2xl font-black !text-gray-900 dark:!text-white tracking-tight">Modificar Promoción</h2>
                <p class="text-[11px] sm:text-xs font-medium !text-gray-500 dark:!text-gray-400 tracking-wide mt-0.5">Modifica los parámetros y restricciones de la oferta.</p>
            </div>
        </div>

        {{-- El campo esta_activa sigue enviándose, oculto, para no perder el dato al guardar --}}
        <input type="checkbox" name="esta_activa" value="1" id="edit_esta_activa" class="hidden" form="formEditarPromocion">

        {{-- Formulario de Edición --}}
        <form action="" method="POST" id="formEditarPromocion" class="relative z-10">
            @csrf
            @method('PUT')

            <div class="space-y-5 sm:space-y-6">

                {{-- Nombre de la Promo --}}
                <div class="group">
                    <label class="block !text-gray-500 dark:!text-gray-400 uppercase text-[10px] font-black tracking-[0.2em] mb-2">Nombre de la Promoción</label>
                    <input type="text" name="nombre" id="edit_nombre" required class="w-full !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl py-3.5 px-4 text-base font-bold !text-gray-900 dark:!text-white placeholder-gray-400 dark:placeholder-gray-600 focus:outline-none focus:!border-amber-500/50 focus:!ring-2 focus:!ring-amber-500/20 transition-all shadow-inner" placeholder="Ej: Jueves de Alitas 2x1">
                </div>

                {{-- Descripción --}}
                <div class="group">
                    <label class="block !text-gray-500 dark:!text-gray-400 uppercase text-[10px] font-black tracking-[0.2em] mb-2">Descripción de la Oferta</label>
                    <textarea name="descripcion" id="edit_descripcion" rows="2" class="w-full !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl py-3.5 px-4 text-base font-bold !text-gray-900 dark:!text-white placeholder-gray-400 dark:placeholder-gray-600 focus:outline-none focus:!border-amber-500/50 focus:!ring-2 focus:!ring-amber-500/20 transition-all shadow-inner resize-none" placeholder="Breve nota explicativa para los meseros o clientes..."></textarea>
                </div>

                {{-- Fila: Tipo y Valor --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="group">
                        <label class="block !text-gray-500 dark:!text-gray-400 uppercase text-[10px] font-black tracking-[0.2em] mb-2">Tipo de Promoción</label>
                        <div class="relative">
                            <select name="tipo_promocion" id="edit_tipo_promocion" required class="w-full appearance-none !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl py-3.5 pl-4 pr-10 text-base font-bold !text-gray-900 dark:!text-white focus:outline-none focus:!border-amber-500/50 focus:!ring-2 focus:!ring-amber-500/20 transition-all shadow-inner cursor-pointer">
                                <option value="porcentaje">Porcentaje (%)</option>
                                <option value="descuento_fijo">Descuento fijo ($)</option>
                                <option value="dos_por_uno">2 x 1</option>
                                <option value="combo">Combo</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 !text-gray-400 dark:!text-gray-500 pointer-events-none text-xs"></i>
                        </div>
                    </div>
                    <div class="group">
                        <label class="block !text-gray-500 dark:!text-gray-400 uppercase text-[10px] font-black tracking-[0.2em] mb-2">Valor Descuento / Cantidad</label>
                        <input type="number" name="valor_descuento" id="edit_valor_descuento" required step="0.01" min="0" class="w-full !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl py-3.5 px-4 text-base font-bold !text-gray-900 dark:!text-white placeholder-gray-400 dark:placeholder-gray-600 focus:outline-none focus:!border-amber-500/50 focus:!ring-2 focus:!ring-amber-500/20 transition-all shadow-inner" placeholder="Ej: 15.00">
                    </div>
                </div>

                {{-- Fila: Vigencia de Fechas --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="group">
                        <label class="block !text-gray-500 dark:!text-gray-400 uppercase text-[10px] font-black tracking-[0.2em] mb-2">Fecha Inicio Vigencia</label>
                        <div class="relative">
                            <input type="date" name="fecha_inicio" id="edit_fecha_inicio" required class="date-input-icon w-full !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl py-3.5 pl-4 pr-10 text-base font-bold !text-gray-900 dark:!text-white focus:outline-none focus:!border-amber-500/50 focus:!ring-2 focus:!ring-amber-500/20 transition-all shadow-inner cursor-pointer">
                            <i onclick="abrirCalendario('edit_fecha_inicio')" class="fas fa-calendar-days absolute right-4 top-1/2 -translate-y-1/2 !text-amber-500 dark:!text-amber-400 cursor-pointer text-sm z-10"></i>
                        </div>
                    </div>
                    <div class="group">
                        <label class="block !text-gray-500 dark:!text-gray-400 uppercase text-[10px] font-black tracking-[0.2em] mb-2">Fecha Fin Vigencia</label>
                        <div class="relative">
                            <input type="date" name="fecha_fin" id="edit_fecha_fin" required class="date-input-icon w-full !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl py-3.5 pl-4 pr-10 text-base font-bold !text-gray-900 dark:!text-white focus:outline-none focus:!border-amber-500/50 focus:!ring-2 focus:!ring-amber-500/20 transition-all shadow-inner cursor-pointer">
                            <i onclick="abrirCalendario('edit_fecha_fin')" class="fas fa-calendar-days absolute right-4 top-1/2 -translate-y-1/2 !text-amber-500 dark:!text-amber-400 cursor-pointer text-sm z-10"></i>
                        </div>
                    </div>
                </div>

                {{-- Días de la Semana --}}
                <div>
                    <label class="block !text-gray-500 dark:!text-gray-400 uppercase text-[10px] font-black tracking-[0.2em] mb-3">Días de Aplicación Semanal</label>
                    <div class="grid grid-cols-4 sm:flex gap-2 flex-wrap">
                        @php
                            $mapeoDiasEdit = [
                                ['L' => 1], ['M' => 2], ['M' => 3], ['J' => 4], ['V' => 5], ['S' => 6], ['D' => 7]
                            ];
                        @endphp
                        @foreach($mapeoDiasEdit as $diaData)
                            @php
                                $letra = key($diaData);
                                $num = $diaData[$letra];
                            @endphp
                            <label class="flex-1 min-w-[55px] flex flex-col items-center justify-center p-3 rounded-2xl border !border-gray-200 dark:!border-white/5 !bg-gray-50 dark:!bg-black/40 cursor-pointer hover:!border-amber-500/50 hover:!bg-amber-50 dark:hover:!bg-amber-500/10 active:scale-95 transition-all select-none group/day relative">
                                <input type="checkbox" name="dias_semana[]" value="{{ $num }}" id="edit_dia_{{ $num }}" class="edit-dia-checkbox peer sr-only">
                                <div class="w-5 h-5 rounded-lg border-2 !border-gray-300 dark:!border-gray-600 peer-checked:!border-amber-500 peer-checked:!bg-amber-500 flex items-center justify-center transition-all mb-1">
                                    <i class="fas fa-check text-[10px] text-white opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                </div>
                                <span class="!text-gray-900 dark:!text-white font-black text-[11px] uppercase tracking-wider group-hover/day:!text-amber-500 transition-colors">{{ $letra }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Selección de Productos --}}
                <div>
                    <label class="block !text-gray-500 dark:!text-gray-400 uppercase text-[10px] font-black tracking-[0.2em] mb-2">Productos Vinculados</label>
                    <div class="max-h-52 overflow-y-auto border !border-gray-200 dark:!border-white/5 rounded-2xl p-2 !bg-gray-50 dark:!bg-black/40 shadow-inner unique-scrollbar divide-y !divide-gray-200 dark:!divide-white/5 transition-colors">
                        @foreach($productos as $producto)
                            <label class="flex items-center gap-4 p-3 rounded-xl hover:!bg-gray-200 dark:hover:!bg-white/5 cursor-pointer transition-colors select-none group/prod">
                                <input type="checkbox" name="productos[]" value="{{ $producto->id }}" id="edit_prod_{{ $producto->id }}" class="edit-prod-checkbox w-5 h-5 rounded-lg !border-gray-300 dark:!border-gray-600 text-amber-500 focus:ring-amber-500/20 cursor-pointer accent-amber-500 bg-white dark:bg-zinc-800 shrink-0">
                                <div class="flex-1">
                                    <p class="text-[13px] font-bold !text-gray-900 dark:!text-white group-hover/prod:!text-amber-600 dark:group-hover/prod:!text-amber-400 transition-colors leading-snug flex items-center gap-2">
                                        {{ $producto->nombre }}
                                        @if($producto->se_vende_por_peso)
                                            <span class="text-[8px] font-black uppercase tracking-widest !text-orange-500 !bg-orange-500/10 border !border-orange-500/20 px-1.5 py-0.5 rounded-md">Por peso</span>
                                        @endif
                                    </p>
                                    @if($producto->se_vende_por_peso)
                                        <p class="!text-gray-500 dark:!text-gray-400 text-[11px] font-medium mt-0.5">${{ number_format($producto->precio_por_100g ?? 0, 2) }} MXN /100g</p>
                                    @else
                                        <p class="!text-gray-500 dark:!text-gray-400 text-[11px] font-medium mt-0.5">${{ number_format($producto->precio, 2) }} MXN</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Botonera Final --}}
                <div class="flex flex-col-reverse sm:flex-row gap-3 pt-4 border-t !border-gray-100 dark:!border-white/5">
                    <button type="button" onclick="closeModal('modalEditar')" class="w-full sm:flex-1 !bg-gray-50 dark:!bg-black/40 hover:!bg-gray-200 dark:hover:!bg-white/10 active:scale-95 border !border-gray-200 dark:!border-white/5 !text-gray-600 dark:!text-gray-300 py-3.5 sm:py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition-colors outline-none">
                        Cancelar Cambios
                    </button>
                    <button type="submit" class="w-full sm:flex-1 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 active:scale-95 !text-white py-3.5 sm:py-4 rounded-2xl text-xs font-black uppercase tracking-widest shadow-[0_10px_25px_-5px_rgba(245,158,11,0.4)] transition-all outline-none">
                        Actualizar Promoción
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<style>
    /* Oculta el ícono nativo del navegador para los inputs de fecha y deja solo nuestro ícono personalizado.
       Esto evita que en modo oscuro el ícono nativo se pierda por contraste. */
    .date-input-icon::-webkit-calendar-picker-indicator {
        opacity: 0;
        position: absolute;
        right: 0;
        width: 2.5rem;
        height: 100%;
        cursor: pointer;
    }
</style>

<script>
    /**
     * Abre el selector de fecha nativo al hacer clic en el ícono de calendario personalizado.
     * (Si esta función ya fue declarada por modal-crear.blade.php en la misma página, no hay conflicto:
     * JavaScript permite redeclarar funciones con "function" sin generar error.)
     */
    function abrirCalendario(inputId) {
        const input = document.getElementById(inputId);
        if (input && typeof input.showPicker === 'function') {
            input.showPicker();
        } else if (input) {
            input.focus();
        }
    }
</script>

{{--
    NOTA: El bloque de abajo era código PHP de un controlador Laravel
    (function edit(Promocion $promocion) {...}) pegado por error dentro
    de un <script> JS, lo cual no es válido y nunca se ejecutará en el
    navegador. Debe vivir en tu controlador, por ejemplo:

    // app/Http/Controllers/PromocionController.php
    public function edit(Promocion $promocion)
    {
        return response()->json([
            'success' => true,
            'promocion' => $promocion->load('productos'),
        ]);
    }
--}}