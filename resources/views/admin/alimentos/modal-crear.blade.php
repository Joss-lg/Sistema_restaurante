{{-- MODAL AGREGAR/EDITAR ALIMENTO --}}
<div id="modal-nuevo-alimento" class="fixed inset-0 z-[100] overflow-y-auto hidden opacity-0 transition-all duration-300 backdrop-blur-sm">
    <div class="fixed inset-0 bg-black/80" onclick="closeModalAlimento()"></div>
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative glass-card bg-[var(--card-color)] border border-[var(--border-color)] w-full max-w-2xl rounded-[2.5rem] shadow-2xl transform opacity-0 translate-y-8 transition-all duration-300" id="modal-panel">
            <div class="p-10">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h2 class="text-3xl font-black text-[var(--text-color)] tracking-tight" id="modal-title">Nuevo Platillo</h2>
                        <p class="text-[var(--text-muted)] mt-1" id="modal-subtitle">Configuración estética del menú</p>
                    </div>
                    <button onclick="closeModalAlimento()" class="text-gray-500 hover:text-white transition"><i class="fas fa-times text-2xl"></i></button>
                </div>

                <form id="formulario-alimento" onsubmit="guardarAlimento(event)">
                    <div class="grid grid-cols-2 gap-6">
                        
                        {{-- Nombre --}}
                        <div class="col-span-2">
                            <label class="text-xs font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Nombre del Platillo</label>
                            <input type="text" id="nombre" name="nombre" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-4 mt-2 text-[var(--text-color)] placeholder:text-[var(--text-muted)] focus:ring-2 focus:ring-blue-500 transition" placeholder="Ej: Lasagna de la Casa" required>
                        </div>
                        
                        {{-- Precio --}}
                        <div class="col-span-1">
                            <label class="text-xs font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Precio</label>
                            <input type="number" id="precio" name="precio" step="0.01" min="0" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-4 mt-2 text-[var(--text-color)] focus:ring-2 focus:ring-blue-500 transition" placeholder="0.00" required>
                        </div>
                        
                        {{-- Categoría HÍBRIDA (Escribir o Seleccionar) --}}
                        <div class="col-span-1 relative">
                            <label class="text-xs font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Categoría</label>
                            <input type="text" id="categoria_nombre" name="categoria_nombre" list="lista-categorias" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-4 mt-2 text-[var(--text-color)] focus:ring-2 focus:ring-blue-500 transition" placeholder="Escribe o selecciona..." autocomplete="off" required>
                            <input type="hidden" id="categoria_id" name="categoria_id">
                            <datalist id="lista-categorias">
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->nombre }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        {{-- MODIFICADORES RÁPIDOS --}}
                        <div class="col-span-2">
                            <label class="text-xs font-black text-[var(--text-muted)] uppercase tracking-widest ml-1">Modificadores Rápidos</label>
                            <input type="text" id="modificadores_input" name="modificadores_input" class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-2xl p-4 mt-2 text-[var(--text-color)] placeholder:text-[var(--text-muted)] focus:ring-2 focus:ring-blue-500 transition" placeholder="Ej: Término medio, Bien cocido, Sin sal">
                            <p class="text-[9px] text-[#3B82F6] font-bold mt-2 ml-1 tracking-wide uppercase"><i class="fas fa-info-circle mr-1"></i> Sepáralos con una coma (,). Aparecerán como botones en la comanda.</p>
                        </div>

                    </div>

                    <div class="mt-10 flex gap-4">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-black py-4 rounded-2xl transition shadow-lg shadow-blue-900/40" id="btn-guardar">GUARDAR CAMBIOS</button>
                        <button type="button" onclick="closeModalAlimento()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-400 font-black py-4 rounded-2xl transition">CANCELAR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
