@extends('layouts.admin')

@section('titulo', 'Gestión de Categorías')

@section('contenido')
<div class="p-8 space-y-6">
    
    {{-- ENCABEZADO Y BOTÓN NUEVO --}}
    <div class="flex justify-between items-center bg-[var(--card-color)] p-6 rounded-3xl border border-black/5 shadow-sm">
        <div>
            <h1 class="text-2xl font-black text-[var(--text-color)] tracking-tighter uppercase">Categorías del Menú</h1>
            <p class="text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-[0.2em] mt-1">Organiza tus platillos y bebidas</p>
        </div>
        <button onclick="openModal('modalCrearCategoria')" class="bg-[#3B82F6] hover:bg-[#2563EB] text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-[#3B82F6]/20 transition-all active:scale-95 flex items-center gap-2">
            <i class="fas fa-plus"></i> Nueva Categoría
        </button>
    </div>

    {{-- ALERTAS (Éxito / Error) --}}
    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 p-4 rounded-xl text-sm font-bold flex items-center gap-3">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-500/10 border border-rose-500/20 text-rose-500 p-4 rounded-xl text-sm font-bold flex items-center gap-3">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- TABLA DE CATEGORÍAS --}}
    <div class="bg-[var(--card-color)] rounded-3xl border border-black/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-black/5 text-[9px] uppercase tracking-[0.2em] text-[var(--text-muted)]">
                        <th class="px-6 py-4 font-black">Color</th>
                        <th class="px-6 py-4 font-black">Nombre de Categoría</th>
                        <th class="px-6 py-4 font-black">Enlace (Slug)</th>
                        <th class="px-6 py-4 font-black text-center">Orden</th>
                        <th class="px-6 py-4 font-black text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 text-sm">
                    @forelse($categorias as $categoria)
                        <tr class="hover:bg-black/5 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="w-8 h-8 rounded-lg shadow-inner border border-black/10" style="background-color: {{ $categoria->color ?? '#3B82F6' }}"></div>
                            </td>
                            <td class="px-6 py-4 font-bold text-[var(--text-color)]">{{ $categoria->nombre }}</td>
                            <td class="px-6 py-4 text-[var(--text-muted)] font-mono text-xs">{{ $categoria->slug }}</td>
                            <td class="px-6 py-4 text-center font-black text-[var(--text-muted)]">{{ $categoria->orden_visualizacion }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    {{-- Botón Eliminar --}}
                                    <form action="{{ route('admin.categorias.destroy', $categoria->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta categoría?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white transition-all" title="Eliminar">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-[var(--text-muted)]">
                                <i class="fas fa-folder-open text-4xl mb-3 opacity-20 block"></i>
                                <p class="text-sm font-bold">No hay categorías registradas</p>
                                <p class="text-xs mt-1">Crea una nueva categoría para empezar a organizar tu menú.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL CREAR CATEGORÍA --}}
<div id="modalCrearCategoria" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-all duration-300">
    <div class="bg-[var(--card-color)] border border-black/5 w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden transform transition-all duration-500 scale-95 opacity-0" id="modalContainer">
        
        <div class="p-8 pb-4 flex justify-between items-center border-b border-black/5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#3B82F6]/10 flex items-center justify-center text-[#3B82F6]">
                    <i class="fas fa-layer-group text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-[var(--text-color)] tracking-tighter uppercase">Nueva Categoría</h3>
                    <p class="text-[9px] text-[var(--text-muted)] font-bold uppercase tracking-[0.2em]">Configuración de Menú</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modalCrearCategoria')" class="w-9 h-9 rounded-xl flex items-center justify-center bg-black/5 text-[var(--text-muted)] hover:text-rose-500 hover:bg-rose-500/10 transition-all outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.categorias.store') }}" method="POST" class="p-8 pt-6 space-y-5">
            @csrf

            <div class="space-y-2">
                <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                    <i class="fas fa-tag opacity-40"></i> Nombre (Ej: Bebidas)
                </label>
                <input type="text" name="nombre" required
                    class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10 outline-none transition-all"
                    placeholder="Escribe el nombre de la categoría">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1">
                        <i class="fas fa-palette opacity-40"></i> Color Visual
                    </label>
                    {{-- Input tipo color nativo de HTML5, muy útil para el diseño de restaurantes --}}
                    <input type="color" name="color" value="#3B82F6" required
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl p-1 cursor-pointer focus:ring-4 focus:ring-[#3B82F6]/10 outline-none transition-all">
                </div>

                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] ml-1" title="El número 1 aparecerá primero en la pantalla del mesero">
                        <i class="fas fa-sort-numeric-down opacity-40"></i> Orden <span class="text-gray-400 font-normal lowercase">(opcional)</span>
                    </label>
                    <input type="number" name="orden_visualizacion" value="0" min="0"
                        class="w-full h-11 bg-black/5 border border-transparent rounded-xl px-5 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10 outline-none transition-all"
                        placeholder="0">
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="button" onclick="closeModal('modalCrearCategoria')" 
                    class="flex-1 h-12 rounded-xl text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] hover:text-[var(--text-color)] hover:bg-black/5 transition-all outline-none">
                    Cancelar
                </button>
                <button type="submit" 
                    class="flex-[1.5] h-12 bg-[#3B82F6] hover:bg-[#2563EB] text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-[#3B82F6]/20 transition-all active:scale-95 outline-none">
                    Guardar Categoría
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Pequeño script para abrir y cerrar el modal con animaciones suaves
    function openModal(id) {
        const modal = document.getElementById(id);
        const container = modal.querySelector('#modalContainer');
        modal.classList.remove('hidden');
        // Pequeño retraso para que la animación funcione
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        const container = modal.querySelector('#modalContainer');
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>
@endsection