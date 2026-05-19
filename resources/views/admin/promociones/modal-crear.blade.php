<div id="modalCrear" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm overflow-y-auto">
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] w-full max-w-2xl rounded-[2.5rem] p-10 shadow-2xl my-8">
        <div class="flex items-center gap-4 mb-8">
            <div class="bg-blue-600/20 p-3 rounded-2xl text-blue-500 text-2xl font-bold">+</div>
            <h2 class="text-3xl font-bold text-[var(--text-color)]">Crear Nueva Promoción</h2>
        </div>

        <form action="{{ route('admin.promociones.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <!-- Nombre -->
                <div>
                    <label class="block text-[var(--text-muted)] uppercase text-xs font-bold tracking-widest mb-2">Nombre de la Promoción</label>
                    <input type="text" name="nombre" required class="w-full bg-[var(--card-color)] border border-[var(--border-color)] rounded-xl py-4 px-4 text-[var(--text-color)] placeholder-[var(--text-muted)] focus:border-blue-500 transition" placeholder="Ej: Jueves 2x1 Bebidas">
                </div>

                <!-- Tipo de Promoción -->
                <div>
                    <label class="block text-[var(--text-muted)] uppercase text-xs font-bold tracking-widest mb-2">Tipo de Promoción</label>
                    <select name="tipo_promocion" required class="w-full bg-[var(--card-color)] border border-[var(--border-color)] rounded-xl py-4 px-4 text-[var(--text-color)] focus:border-blue-500 transition">
                        <option value="">-- Selecciona tipo --</option>
                        <option value="porcentaje">Porcentaje (%)</option>
                        <option value="2x1">2 x 1</option>
                        <option value="fijo">Descuento Fijo ($)</option>
                    </select>
                </div>

                <!-- Valor Descuento -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[var(--text-muted)] uppercase text-xs font-bold tracking-widest mb-2">Valor Descuento</label>
                        <input type="number" name="valor_descuento" required step="0.01" class="w-full bg-[var(--card-color)] border border-[var(--border-color)] rounded-xl py-4 px-4 text-[var(--text-color)] placeholder-[var(--text-muted)] focus:border-blue-500 transition" placeholder="Ej: 15">
                    </div>
                    <div>
                        <label class="block text-[var(--text-muted)] uppercase text-xs font-bold tracking-widest mb-2">Estado</label>
                        <select name="esta_activa" class="w-full bg-[var(--card-color)] border border-[var(--border-color)] rounded-xl py-4 px-4 text-[var(--text-color)] focus:border-blue-500 transition">
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>
                </div>

                <!-- Días de la Semana -->
                <div>
                    <label class="block text-[var(--text-muted)] uppercase text-xs font-bold tracking-widest mb-3">Días Disponibles</label>
                    <div class="flex gap-2 flex-wrap">
                        @php $dias = ['Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Jueves' => 4, 'Viernes' => 5, 'Sábado' => 6, 'Domingo' => 7]; @endphp
                        @foreach(['L' => 1, 'M' => 2, 'M' => 3, 'J' => 4, 'V' => 5, 'S' => 6, 'D' => 7] as $letra => $num)
                            <label class="flex items-center gap-2 p-3 rounded-lg border border-[var(--border-color)] cursor-pointer hover:border-blue-500 transition">
                                <input type="checkbox" name="dias_semana[]" value="{{ $num }}" class="w-5 h-5 rounded cursor-pointer">
                                <span class="text-[var(--text-color)] font-bold text-sm">{{ $letra }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Seleccionar Productos -->
                <div>
                    <label class="block text-[var(--text-muted)] uppercase text-xs font-bold tracking-widest mb-2">Productos Incluidos</label>
                    <div class="max-h-64 overflow-y-auto border border-[var(--border-color)] rounded-xl p-4 bg-[var(--card-color)]/50">
                        @foreach($productos as $producto)
                            <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-[var(--card-color)] cursor-pointer transition">
                                <input type="checkbox" name="productos[]" value="{{ $producto->id }}" class="w-5 h-5 rounded cursor-pointer">
                                <div class="flex-1">
                                    <p class="text-[var(--text-color)] font-semibold">{{ $producto->nombre }}</p>
                                    <p class="text-[var(--text-muted)] text-xs">${{ number_format($producto->precio, 2) }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-4 pt-6">
                    <button type="button" onclick="closeModal('modalCrear')" class="flex-1 bg-[var(--border-color)] hover:bg-[var(--border-color)]/80 text-[var(--text-color)] py-4 rounded-2xl font-bold transition">Cancelar</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-bold hover:bg-blue-700 transition">GUARDAR PROMOCIÓN</button>
                </div>
            </div>
        </form>
    </div>
</div>