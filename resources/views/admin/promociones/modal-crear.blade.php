<div id="modalCrear" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm overflow-y-auto overflow-x-hidden p-4">
    <div class="relative !bg-white dark:!bg-[#121318] border !border-transparent dark:!border-white/5 w-full max-w-2xl rounded-[2.5rem] p-8 lg:p-10 shadow-2xl dark:shadow-[0_20px_50px_rgba(0,0,0,0.5)] my-8 unique-scrollbar max-h-[90vh] overflow-y-auto overflow-x-hidden">

        {{-- Resplandor decorativo de fondo --}}
        <div class="absolute -top-32 -right-32 w-64 h-64 bg-blue-500/10 dark:bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Cabecera del Modal --}}
        <div class="flex items-center gap-4 mb-8 relative z-10">
            <div class="!bg-blue-50 dark:!bg-blue-500/20 w-12 h-12 rounded-2xl flex items-center justify-center !text-blue-600 dark:!text-blue-400 text-xl font-black shadow-inner border !border-blue-100 dark:!border-transparent">
                <i class="fas fa-plus"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black !text-gray-900 dark:!text-white tracking-tight">Crear Nueva Promoción</h2>
                <p class="text-xs font-medium !text-gray-500 dark:!text-gray-400 tracking-wide mt-0.5">Configura una oferta especial para el menú.</p>
            </div>
        </div>

        {{-- La promoción siempre se crea activa; se mantiene oculto para no perder el dato al guardar --}}
        <input type="checkbox" name="esta_activa" value="1" checked class="hidden" form="formCrearPromocion">

        <form id="formCrearPromocion" onsubmit="guardarPromocion(event)" class="relative z-10">
            @csrf
            <div class="space-y-6">

                {{-- Nombre de Promoción --}}
                <div class="group">
                    <label class="block !text-gray-500 dark:!text-gray-400 uppercase text-[10px] font-black tracking-[0.2em] mb-2">Nombre de la Promoción</label>
                    <input type="text" name="nombre" required class="w-full !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl py-3.5 px-4 text-sm font-bold !text-gray-900 dark:!text-white placeholder-gray-400 dark:placeholder-gray-600 focus:outline-none focus:!border-blue-500/50 focus:!ring-2 focus:!ring-blue-500/20 transition-all shadow-inner">
                </div>

                {{-- Fila: Tipo y Valor --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="group">
                        <label class="block !text-gray-500 dark:!text-gray-400 uppercase text-[10px] font-black tracking-[0.2em] mb-2">Tipo de Promoción</label>
                        <div class="relative">
                            <select name="tipo_promocion" id="edit_tipo_promocion" required class="w-full appearance-none !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl py-3.5 pl-4 pr-10 text-sm font-bold !text-gray-900 dark:!text-white focus:outline-none focus:!border-blue-500/50 focus:!ring-2 focus:!ring-blue-500/20 transition-all shadow-inner cursor-pointer">
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
                        <input type="number" name="valor_descuento" required step="0.01" min="0" class="w-full !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl py-3.5 px-4 text-sm font-bold !text-gray-900 dark:!text-white placeholder-gray-400 dark:placeholder-gray-600 focus:outline-none focus:!border-blue-500/50 focus:!ring-2 focus:!ring-blue-500/20 transition-all shadow-inner" placeholder="Ej: 15.00">
                    </div>
                </div>

                {{-- Fila: Vigencia de Fechas --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="group">
                        <label class="block !text-gray-500 dark:!text-gray-400 uppercase text-[10px] font-black tracking-[0.2em] mb-2">Fecha Inicio Vigencia</label>
                        <div class="relative">
                            <input type="date" name="fecha_inicio" id="crear_fecha_inicio" required value="{{ date('Y-m-d') }}" class="date-input-icon w-full !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl py-3.5 pl-4 pr-10 text-sm font-bold !text-gray-900 dark:!text-white focus:outline-none focus:!border-blue-500/50 focus:!ring-2 focus:!ring-blue-500/20 transition-all shadow-inner cursor-pointer">
                            <i onclick="abrirCalendario('crear_fecha_inicio')" class="fas fa-calendar-days absolute right-4 top-1/2 -translate-y-1/2 !text-blue-500 dark:!text-blue-400 cursor-pointer text-sm z-10"></i>
                        </div>
                    </div>
                    <div class="group">
                        <label class="block !text-gray-500 dark:!text-gray-400 uppercase text-[10px] font-black tracking-[0.2em] mb-2">Fecha Fin Vigencia</label>
                        <div class="relative">
                            <input type="date" name="fecha_fin" id="crear_fecha_fin" required value="{{ date('Y-m-d', strtotime('+1 month')) }}" class="date-input-icon w-full !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl py-3.5 pl-4 pr-10 text-sm font-bold !text-gray-900 dark:!text-white focus:outline-none focus:!border-blue-500/50 focus:!ring-2 focus:!ring-blue-500/20 transition-all shadow-inner cursor-pointer">
                            <i onclick="abrirCalendario('crear_fecha_fin')" class="fas fa-calendar-days absolute right-4 top-1/2 -translate-y-1/2 !text-blue-500 dark:!text-blue-400 cursor-pointer text-sm z-10"></i>
                        </div>
                    </div>
                </div>

                {{-- Días de la Semana --}}
                <div>
                    <label class="block !text-gray-500 dark:!text-gray-400 uppercase text-[10px] font-black tracking-[0.2em] mb-3">Días de Aplicación Semanal</label>
                    <div class="grid grid-cols-4 sm:flex gap-2 flex-wrap">
                        @php
                            $mapeoDias = [
                                ['L' => 1, 'nombre' => 'Lunes'],
                                ['M' => 2, 'nombre' => 'Martes'],
                                ['M' => 3, 'nombre' => 'Miércoles'],
                                ['J' => 4, 'nombre' => 'Jueves'],
                                ['V' => 5, 'nombre' => 'Viernes'],
                                ['S' => 6, 'nombre' => 'Sábado'],
                                ['D' => 7, 'nombre' => 'Domingo']
                            ];
                        @endphp
                        @foreach($mapeoDias as $diaData)
                            @php
                                $letra = key($diaData);
                                $num = $diaData[$letra];
                            @endphp
                            <label class="flex-1 min-w-[55px] flex flex-col items-center justify-center p-3 rounded-2xl border !border-gray-200 dark:!border-white/5 !bg-gray-50 dark:!bg-black/40 cursor-pointer hover:!border-blue-500/50 hover:!bg-blue-50 dark:hover:!bg-blue-500/10 transition-colors select-none group/day relative">
                                <input type="checkbox" name="dias_semana[]" value="{{ $num }}" checked class="peer sr-only">
                                <div class="w-5 h-5 rounded-lg border-2 !border-gray-300 dark:!border-gray-600 peer-checked:!border-blue-500 peer-checked:!bg-blue-500 flex items-center justify-center transition-all mb-1">
                                    <i class="fas fa-check text-[10px] text-white opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                </div>
                                <span class="!text-gray-900 dark:!text-white font-black text-[11px] uppercase tracking-wider group-hover/day:!text-blue-500 transition-colors">{{ $letra }}</span>
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
                                <input type="checkbox" name="productos[]" value="{{ $producto->id }}" class="w-5 h-5 rounded-lg !border-gray-300 dark:!border-gray-600 text-blue-600 focus:ring-blue-500/20 cursor-pointer accent-blue-500 bg-white dark:bg-zinc-800">
                                <div class="flex-1">
                                    <p class="text-[13px] font-bold !text-gray-900 dark:!text-white group-hover/prod:!text-blue-600 dark:group-hover/prod:!text-blue-400 transition-colors leading-snug">{{ $producto->nombre }}</p>
                                    <p class="!text-gray-500 dark:!text-gray-400 text-[11px] font-medium mt-0.5">${{ number_format($producto->precio, 2) }} MXN</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Botonera Final --}}
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t !border-gray-100 dark:!border-white/5">
                    <button type="button" onclick="closeModal('modalCrear')" class="w-full sm:flex-1 !bg-gray-50 dark:!bg-black/40 hover:!bg-gray-200 dark:hover:!bg-white/10 border !border-gray-200 dark:!border-white/5 !text-gray-600 dark:!text-gray-300 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition-colors outline-none">
                        Cancelar
                    </button>

                    <button type="submit" id="btn-guardar-promocion" class="w-full sm:flex-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white py-4 rounded-2xl text-xs font-black uppercase tracking-widest shadow-[0_10px_25px_-5px_rgba(59,130,246,0.4)] transition-all outline-none">
                        Guardar Promoción
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    /* Scrollbar estilizado para adaptarse a ambos modos de color */
    .unique-scrollbar::-webkit-scrollbar { width: 6px; }
    .unique-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .unique-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

    @media (prefers-color-scheme: dark) {
        .unique-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); }
        .unique-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.25); }
    }

    /* Oculta el ícono nativo del navegador para los inputs de fecha y deja solo nuestro ícono personalizado */
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
     * Abre el selector de fecha nativo al hacer clic en el ícono de calendario personalizado
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