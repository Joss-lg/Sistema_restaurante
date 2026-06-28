<div id="modalCrear" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm overflow-y-auto p-4">
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] w-full max-w-2xl rounded-[2.5rem] p-8 lg:p-10 shadow-2xl my-8 unique-scrollbar max-h-[90vh] overflow-y-auto">
        
        {{-- Cabecera del Modal --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-blue-600/20 w-12 h-12 rounded-2xl flex items-center justify-center text-blue-500 text-xl font-black shadow-inner">
                    <i class="fas fa-plus"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-color)] tracking-tight">Crear Nueva Promoción</h2>
                    <p class="text-xs font-medium text-[var(--text-muted)] tracking-wide mt-0.5">Configura una oferta especial para el menú.</p>
                </div>
            </div>
            
            {{-- Interruptor de Estado Premium (Sustituye al select viejo) --}}
            <div class="flex flex-col items-end gap-1 bg-[var(--input-bg)] border border-[var(--border-color)] px-4 py-2 rounded-2xl">
                <span class="text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">Estado inicial</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="esta_activa" value="1" checked class="sr-only peer" form="formCrearPromocion">
                    <div class="w-10 h-5 rounded-full bg-black/20 dark:bg-zinc-700 border border-[var(--border-color)] peer-checked:border-emerald-500 peer-checked:bg-emerald-500 transition-all duration-300 relative shadow-inner">
                        <span class="absolute left-[2px] top-[1px] h-4 w-4 rounded-full bg-[var(--text-muted)] transition-all duration-300 peer-checked:left-[1.35rem] peer-checked:bg-white"></span>
                    </div>
                </label>
            </div>
        </div>

       <form id="formCrearPromocion" onsubmit="guardarPromocion(event)">
    @csrf
    <div class="space-y-6">
        {{-- ¡FALTABA ESTE INPUT! --}}
        <div>
            <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-2">Nombre de la Promoción</label>
            <input type="text" name="nombre" required class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl py-3.5 px-4 text-sm font-medium text-[var(--text-color)] placeholder-[var(--text-muted)] focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition shadow-inner">
        </div>

        {{-- ... (Descripción igual) ... --}}

        {{-- Fila: Tipo y Valor (AJUSTADOS PARA COINCIDIR CON LA BD) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-2">Tipo de Promoción</label>
                {{-- CAMBIADO: de name="tipo" a name="tipo_promocion" --}}
                <select name="tipo_promocion" required class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl py-3.5 px-4 text-sm font-medium text-[var(--text-color)] focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition shadow-inner cursor-pointer">
                    <option value="">-- Selecciona tipo --</option>
                    <option value="porcentaje">Porcentaje (%)</option>
                    <option value="dos_por_uno">Paquete 2 x 1</option>
                    <option value="descuento_fijo">Descuento Fijo ($)</option>
                    <option value="combo">Combo</option>
                </select>
            </div>
            <div>
                <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-2">Valor Descuento / Cantidad</label>
                {{-- CAMBIADO: de name="valor" a name="valor_descuento" --}}
                <input type="number" name="valor_descuento" required step="0.01" min="0" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl py-3.5 px-4 text-sm font-medium text-[var(--text-color)] placeholder-[var(--text-muted)] focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition shadow-inner" placeholder="Ej: 15.00">
            </div>
        </div>

                {{-- Fila: Vigencia de Fechas (Campos nuevos desde cero) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-2">Fecha Inicio Vigencia</label>
                        <input type="date" name="fecha_inicio" required value="{{ date('Y-m-d') }}" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl py-3.5 px-4 text-sm font-medium text-[var(--text-color)] focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition shadow-inner cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-2">Fecha Fin Vigencia</label>
                        <input type="date" name="fecha_fin" required value="{{ date('Y-m-d', strtotime('+1 month')) }}" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl py-3.5 px-4 text-sm font-medium text-[var(--text-color)] focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition shadow-inner cursor-pointer">
                    </div>
                </div>

                {{-- Días de la Semana (Solución al bug de llaves duplicadas de PHP) --}}
                <div>
                    <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-3">Días de Aplicación Semanal</label>
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
                            <label class="flex-1 min-w-[55px] flex flex-col items-center justify-center p-3 rounded-2xl border border-[var(--border-color)] bg-[var(--input-bg)] cursor-pointer hover:border-blue-500/50 hover:bg-blue-500/[0.02] transition select-none group/day relative">
                                <input type="checkbox" name="dias_semana[]" value="{{ $num }}" checked class="peer sr-only">
                                <div class="w-5 h-5 rounded-lg border-2 border-[var(--border-color)] peer-checked:border-blue-500 peer-checked:bg-blue-500 flex items-center justify-center transition mb-1">
                                    <i class="fas fa-check text-[10px] text-white opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                </div>
                                <span class="text-[var(--text-color)] font-black text-[11px] uppercase tracking-wider group-hover/day:text-blue-500 transition-colors">{{ $letra }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Selección de Productos --}}
                <div>
                    <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-2">Productos Vinculados</label>
                    <div class="max-h-52 overflow-y-auto border border-[var(--border-color)] rounded-2xl p-2 bg-[var(--input-bg)] shadow-inner unique-scrollbar divide-y divide-[var(--border-color)]/50">
                        @foreach($productos as $producto)
                            <label class="flex items-center gap-4 p-3 rounded-xl hover:bg-[var(--card-color)] cursor-pointer transition select-none group/prod">
                                <input type="checkbox" name="productos[]" value="{{ $producto->id }}" class="w-5 h-5 rounded-lg border-[var(--border-color)] text-blue-600 focus:ring-blue-500/20 cursor-pointer accent-blue-500">
                                <div class="flex-1">
                                    <p class="text-[13px] font-bold text-[var(--text-color)] group-hover/prod:text-blue-500 transition-colors leading-snug">{{ $producto->nombre }}</p>
                                    <p class="text-[var(--text-muted)] text-[11px] font-medium mt-0.5">${{ number_format($producto->precio, 2) }} MXN</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Botonera Final Modificada --}}
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-[var(--border-color)]">
                    <button type="button" onclick="closeModal('modalCrear')" class="w-full sm:flex-1 bg-[var(--input-bg)] hover:bg-[var(--border-color)]/50 border border-[var(--border-color)] text-[var(--text-color)] py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition outline-none">
                        Cancelar
                    </button>
                    
                    {{-- Botón con ID para el script --}}
                    <button type="submit" id="btn-guardar-promocion" class="w-full sm:flex-1 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white py-4 rounded-2xl text-xs font-black uppercase tracking-widest shadow-[0_10px_25px_-5px_rgba(59,130,246,0.4)] transition outline-none">
                        Guardar Promoción
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    /* Scrollbar estilizado interno para mantener la estética limpia */
    .unique-scrollbar::-webkit-scrollbar { width: 5px; }
    .unique-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .unique-scrollbar::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 99px; }
</style>