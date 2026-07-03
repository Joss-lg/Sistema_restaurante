<div id="modalCrearRol" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-all duration-300">
    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2rem] w-full max-w-lg p-8 shadow-2xl relative overflow-hidden transform scale-95 opacity-0 transition-all duration-300 dynamic-modal-content">
        
        <div class="absolute top-[-10%] left-[-10%] w-32 h-32 rounded-full bg-blue-600/10 blur-[60px] pointer-events-none z-0"></div>

        <div class="flex justify-between items-center mb-6 relative z-10">
            <h2 class="text-xl font-black text-[var(--text-color)] tracking-tight">Crear Puesto Nuevo</h2>
            <button type="button" onclick="cerrarModalCrear()" class="text-[var(--text-muted)] hover:text-[var(--text-color)] transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-6 relative z-10">
            @csrf
            
            <div>
                <label for="nombre" class="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.25em] mb-3 block">Nombre del Puesto</label>
                <input type="text" id="nombre" name="nombre" placeholder="Ej: Guardia Nocturno" required 
                       class="w-full h-12 bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl px-5 text-sm text-[var(--text-color)] outline-none transition-all focus:border-[#3B82F6]">
                @error('nombre') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4 border-t border-[var(--border-color)] flex justify-end gap-3">
                <button type="button" onclick="cerrarModalCrear()" class="px-5 py-3 rounded-xl text-xs font-bold uppercase text-[var(--text-muted)] hover:bg-black/5 dark:hover:bg-white/5 transition-all">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-3 rounded-xl text-xs font-bold bg-[#3B82F6] text-white hover:bg-[#3B82F6]/90 transition-all outline-none shadow-md">
                    <i class="fas fa-save mr-2"></i> Guardar Puesto
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalCrear() {
        const modal = document.getElementById('modalCrearRol');
        const content = modal.querySelector('.dynamic-modal-content');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function cerrarModalCrear() {
        const modal = document.getElementById('modalCrearRol');
        const content = modal.querySelector('.dynamic-modal-content');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }
</script>