<div id="modalEditar" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm overflow-y-auto p-4">
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] w-full max-w-2xl rounded-[2.5rem] p-8 lg:p-10 shadow-2xl my-8 unique-scrollbar max-h-[90vh] overflow-y-auto">
        
        {{-- Cabecera del Modal --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-amber-500/20 w-12 h-12 rounded-2xl flex items-center justify-center text-amber-500 text-xl font-black shadow-inner">
                    <i class="fas fa-pen"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-[var(--text-color)] tracking-tight">Modificar Promoción</h2>
                    <p class="text-xs font-medium text-[var(--text-muted)] tracking-wide mt-0.5">Modifica los parámetros y restricciones de la oferta.</p>
                </div>
            </div>
            
            {{-- Interruptor de Estado Premium (iOS) --}}
            <div class="flex flex-col items-end gap-1 bg-[var(--input-bg)] border border-[var(--border-color)] px-4 py-2 rounded-2xl">
                <span class="text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">Estado actual</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="esta_activa" value="1" id="edit_esta_activa" class="sr-only peer" form="formEditarPromocion">
                    <div class="w-10 h-5 rounded-full bg-black/20 dark:bg-zinc-700 border border-[var(--border-color)] peer-checked:border-emerald-500 peer-checked:bg-emerald-500 transition-all duration-300 relative shadow-inner">
                        <span class="absolute left-[2px] top-[1px] h-4 w-4 rounded-full bg-[var(--text-muted)] transition-all duration-300 peer-checked:left-[1.35rem] peer-checked:bg-white"></span>
                    </div>
                </label>
            </div>
        </div>

        {{-- Formulario de Edición --}}
        <form action="" method="POST" id="formEditarPromocion">
            @csrf
            @method('PUT') {{-- Requerido para la ruta update del controlador --}}
            
            <div class="space-y-6">
                
                {{-- Nombre de la Promo --}}
                <div>
                    <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-2">Nombre de la Promoción</label>
                    <input type="text" name="nombre" id="edit_nombre" required class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl py-3.5 px-4 text-sm font-medium text-[var(--text-color)] placeholder-[var(--text-muted)] focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition shadow-inner" placeholder="Ej: Jueves de Alitas 2x1">
                </div>

                {{-- Descripción --}}
                <div>
                    <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-2">Descripción de la Oferta</label>
                    <textarea name="descripcion" id="edit_descripcion" rows="2" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl py-3.5 px-4 text-sm font-medium text-[var(--text-color)] placeholder-[var(--text-muted)] focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition shadow-inner resize-none" placeholder="Breve nota explicativa para los meseros o clientes..."></textarea>
                </div>

                {{-- Fila: Tipo y Valor --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-2">Tipo de Promoción</label>
                        <select name="tipo_promocion" id="edit_tipo_promocion" required class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl py-3.5 px-4 text-sm font-medium text-[var(--text-color)] focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition shadow-inner cursor-pointer">
                            <option value="porcentaje">Porcentaje (%)</option>
                            <option value="2x1">Paquete 2 x 1</option>
                            <option value="fijo">Descuento Fijo ($)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-2">Valor Descuento / Cantidad</label>
                        <input type="number" name="valor_descuento" id="edit_valor_descuento" required step="0.01" min="0" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl py-3.5 px-4 text-sm font-medium text-[var(--text-color)] placeholder-[var(--text-muted)] focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition shadow-inner" placeholder="Ej: 15.00">
                    </div>
                </div>

                {{-- Fila: Vigencia de Fechas --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-2">Fecha Inicio Vigencia</label>
                        <input type="date" name="fecha_inicio" id="edit_fecha_inicio" required class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl py-3.5 px-4 text-sm font-medium text-[var(--text-color)] focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition shadow-inner cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-2">Fecha Fin Vigencia</label>
                        <input type="date" name="fecha_fin" id="edit_fecha_fin" required class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl py-3.5 px-4 text-sm font-medium text-[var(--text-color)] focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition shadow-inner cursor-pointer">
                    </div>
                </div>

                {{-- Días de la Semana --}}
                <div>
                    <label class="block text-[var(--text-muted)] uppercase text-[10px] font-black tracking-[0.2em] mb-3">Días de Aplicación Semanal</label>
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
                            <label class="flex-1 min-w-[55px] flex flex-col items-center justify-center p-3 rounded-2xl border border-[var(--border-color)] bg-[var(--input-bg)] cursor-pointer hover:border-blue-500/50 hover:bg-blue-500/[0.02] transition select-none group/day relative">
                                <input type="checkbox" name="dias_semana[]" value="{{ $num }}" id="edit_dia_{{ $num }}" class="edit-dia-checkbox peer sr-only">
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
                                <input type="checkbox" name="productos[]" value="{{ $producto->id }}" id="edit_prod_{{ $producto->id }}" class="edit-prod-checkbox w-5 h-5 rounded-lg border-[var(--border-color)] text-blue-600 focus:ring-blue-500/20 cursor-pointer accent-blue-500">
                                <div class="flex-1">
                                    <p class="text-[13px] font-bold text-[var(--text-color)] group-hover/prod:text-blue-500 transition-colors leading-snug">{{ $producto->nombre }}</p>
                                    <p class="text-[var(--text-muted)] text-[11px] font-medium mt-0.5">${{ number_format($producto->precio, 2) }} MXN</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Botonera Final --}}
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-[var(--border-color)]">
                    <button type="button" onclick="closeModal('modalEditar')" class="w-full sm:flex-1 bg-[var(--input-bg)] hover:bg-[var(--border-color)]/50 border border-[var(--border-color)] text-[var(--text-color)] py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition outline-none">
                        Cancelar Cambios
                    </button>
                    <button type="submit" class="w-full sm:flex-1 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white py-4 rounded-2xl text-xs font-black uppercase tracking-widest shadow-[0_10px_25px_-5px_rgba(245,158,11,0.4)] transition outline-none">
                        Actualizar Promoción
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    /**
     * Carga de forma asíncrona los datos de la promoción y abre el modal
     */
    function editPromo(id) {
        const form = document.getElementById('formEditarPromocion');
        // Seteamos la url dinámica de Laravel para el Update: /admin/promociones/{id}
        form.action = `/admin/promociones/${id}`;

        // Realizamos el fetch al endpoint del controlador (edit) que retorna la respuesta en JSON
        fetch(`/admin/promociones/${id}/edit`)
            .then(response => {
                if (!response.ok) throw new Error('Error al recuperar los datos.');
                return response.json();
            })
            .then(data => {
                // Inyectamos los valores a los inputs planos
                document.getElementById('edit_nombre').value = data.promocion.nombre;
                document.getElementById('edit_descripcion').value = data.promocion.descripcion || '';
                document.getElementById('edit_tipo_promocion').value = data.promocion.tipo_promocion;
                document.getElementById('edit_valor_descuento').value = data.promocion.valor_descuento;
                document.getElementById('edit_fecha_inicio').value = data.promocion.fecha_inicio;
                document.getElementById('edit_fecha_fin').value = data.promocion.fecha_fin;

                // Controlar el Switch de estado de iOS
                document.getElementById('edit_esta_activa').checked = parseInt(data.promocion.esta_activa) === 1;

                // Limpiar todos los checkboxes de días antes de marcar los de la promoción
                document.querySelectorAll('.edit-dia-checkbox').forEach(cb => cb.checked = false);
                
                // Marcar días correspondientes
                if (data.promocion.dias_semana && Array.isArray(data.promocion.dias_semana)) {
                    data.promocion.dias_semana.forEach(dia => {
                        const checkboxDia = document.getElementById(`edit_dia_${dia}`);
                        if (checkboxDia) checkboxDia.checked = true;
                    });
                }

                // Limpiar todos los checkboxes de productos vinculados
                document.querySelectorAll('.edit-prod-checkbox').forEach(cb => cb.checked = false);

                // Marcar los productos asociados a la promoción actual
                if (data.productos_vinculados && Array.isArray(data.productos_vinculados)) {
                    data.productos_vinculados.forEach(prodId => {
                        const checkboxProd = document.getElementById(`edit_prod_${prodId}`);
                        if (checkboxProd) checkboxProd.checked = true;
                    });
                }

                // Desplegar el modal usando la función global de tu index
                openModal('modalEditar');
            })
            .catch(error => {
                console.error(error);
                alert('No se pudo cargar la información de la promoción seleccionada.');
            });
    }
</script>