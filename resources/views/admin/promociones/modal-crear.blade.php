<div id="modalCrear" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm">
    <div class="bg-[#111111] border border-gray-800 w-full max-w-lg rounded-[2.5rem] p-10 shadow-2xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="bg-blue-600/20 p-3 rounded-2xl text-blue-500 text-2xl font-bold">+</div>
            <h2 class="text-3xl font-bold">Crear Nueva Promoción</h2>
        </div>

        <form action="{{ route('admin.promociones.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label class="block text-gray-500 uppercase text-xs font-bold tracking-widest mb-2">Nombre de la Promoción</label>
                    <input type="text" name="nombre" class="w-full bg-[#050505] border border-gray-800 rounded-xl py-4 px-4 text-white focus:border-blue-500 transition" placeholder="Ej: Jueves 2x1 Bebidas">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-500 uppercase text-xs font-bold tracking-widest mb-2">Descuento (%)</label>
                        <input type="number" name="valor_descuento" class="w-full bg-[#050505] border border-gray-800 rounded-xl py-4 px-4 text-white">
                    </div>
                    <div>
                        <label class="block text-gray-500 uppercase text-xs font-bold tracking-widest mb-2">Estado</label>
                        <select name="esta_activa" class="w-full bg-[#050505] border border-gray-800 rounded-xl py-4 px-4 text-white">
                            <option value="1">Activa</option>
                            <option value="0">Inactiva</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-4 pt-6">
                    <button type="button" onclick="closeModal('modalCrear')" class="flex-1 bg-gray-900 text-white py-4 rounded-2xl font-bold hover:bg-gray-800 transition">Cancelar</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-bold hover:bg-blue-700 transition">GUARDAR PROMOCIÓN</button>
                </div>
            </div>
        </form>
    </div>
</div>