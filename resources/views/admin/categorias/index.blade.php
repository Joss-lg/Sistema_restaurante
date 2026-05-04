{{-- resources/views/admin/categorias/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Categorías | Ollintem Pro')

@section('content')
<div class="p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-2">
        <div>
            <h1 class="text-3xl font-black text-[var(--text-color)] tracking-tight">Categorías del Menú</h1>
            <p class="text-sm text-[var(--text-muted)] mt-1">Organiza y clasifica los platillos del restaurante</p>
        </div>

        <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
            <div class="relative w-full md:w-72 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-search text-[var(--text-muted)] text-sm"></i>
                </div>
                <input type="text" id="buscadorCategorias" placeholder="Buscar categoría..."
                    class="w-full h-12 bg-black/5 border border-transparent rounded-xl pl-11 pr-4 text-xs font-bold text-[var(--text-color)] focus:bg-[var(--card-color)] focus:border-[#3B82F6] focus:ring-4 focus:ring-[#3B82F6]/10 outline-none transition-all">
            </div>
            <button onclick="openModalCrear()"
                class="w-full md:w-auto bg-[#3B82F6] hover:bg-[#2563EB] text-white px-7 py-3 rounded-xl text-sm font-bold transition-all shadow-lg shadow-[#3B82F6]/20 outline-none flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i> Nueva Categoría
            </button>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-5 py-4 rounded-xl text-sm font-semibold">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 px-5 py-4 rounded-xl text-sm font-semibold">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Tabla --}}
    <div class="bg-[var(--card-color)] rounded-[1.5rem] shadow-sm p-6 lg:p-8 w-full">

        <div class="mb-6 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#3B82F6]/10 flex items-center justify-center text-[#3B82F6]">
                <i class="fas fa-tags text-lg"></i>
            </div>
            <h2 class="text-xl font-bold text-[var(--text-color)]">
                Listado de Categorías |
                <span class="text-[var(--text-muted)] font-normal text-sm">{{ count($categorias) }} registradas</span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Color</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Nombre</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Slug</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Orden</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider">Platillos</th>
                        <th class="pb-4 px-4 text-xs font-black text-[var(--text-muted)] uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaCategorias">
                    @forelse($categorias as $categoria)
                    <tr class="fila-categoria border-none hover:bg-black/5 transition-colors rounded-xl">

                        <td class="py-4 px-4 rounded-l-xl">
                            <div class="w-8 h-8 rounded-lg shadow-sm border border-white/10"
                                style="background-color: {{ $categoria->color ?? '#3B82F6' }}"></div>
                        </td>

                        <td class="py-4 px-4 nombre-celda">
                            <span class="text-sm font-bold text-[var(--text-color)]">{{ $categoria->nombre }}</span>
                        </td>

                        <td class="py-4 px-4">
                            <span class="px-3 py-1 bg-black/5 rounded-md text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-widest font-mono">
                                {{ $categoria->slug }}
                            </span>
                        </td>

                        <td class="py-4 px-4 text-sm font-medium text-[var(--text-muted)]">
                            {{ $categoria->orden_visualizacion ?? 0 }}
                        </td>

                        <td class="py-4 px-4">
                            <span class="px-3 py-1.5 bg-[#3B82F6]/10 text-[#3B82F6] rounded-full text-[11px] font-black uppercase tracking-wider">
                                {{ $categoria->productos_count ?? $categoria->productos()->count() }} platillos
                            </span>
                        </td>

                        <td class="py-4 px-4 text-right rounded-r-xl">
                            <div class="flex items-center justify-end gap-3">
                                <button type="button" title="Editar"
                                    onclick="abrirModalEspecifico('modalEditar-{{ $categoria->id }}')"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-[#3B82F6] hover:bg-[#3B82F6]/10 transition-colors outline-none">
                                    <i class="fas fa-cog text-sm"></i>
                                </button>
                                <button type="button" title="Eliminar"
                                    onclick="confirmarEliminacion('{{ $categoria->id }}', '{{ $categoria->nombre }}')"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-rose-500 hover:bg-rose-500/10 transition-colors outline-none">
                                    <i class="far fa-trash-alt text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Modal editar (dentro del loop) --}}
                    @include('admin.categorias.modal-editar', ['categoria' => $categoria])

                    @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-[var(--text-muted)]">
                                <i class="fas fa-tags text-4xl opacity-20"></i>
                                <p class="text-sm font-medium">No hay categorías registradas aún.</p>
                                <button onclick="openModalCrear()" class="text-[#3B82F6] text-sm font-bold hover:underline">
                                    Crear la primera categoría
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script>
    // Buscador en tiempo real
    document.getElementById('buscadorCategorias')?.addEventListener('input', function () {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('.fila-categoria').forEach(fila => {
            const nombre = fila.querySelector('.nombre-celda')?.textContent.toLowerCase();
            fila.style.display = nombre.includes(term) ? '' : 'none';
        });
    });

    // Abrir/cerrar modales específicos (editar)
    function abrirModalEspecifico(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        const container = modal.querySelector('div[id^="modalContainer-"]');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            if (container) {
                container.classList.remove('scale-95', 'opacity-0');
                container.classList.add('scale-100', 'opacity-100');
            }
        }, 10);
    }

    function cerrarModalEspecifico(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        const container = modal.querySelector('div[id^="modalContainer-"]');
        if (container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }

    // Modal Crear
    function openModalCrear() {
        const modal = document.getElementById('modalCrear');
        const container = document.getElementById('createContainer');
        if (!modal || !container) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeCreateModal() {
        const modal = document.getElementById('modalCrear');
        const container = document.getElementById('createContainer');
        if (container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }

    // Modal Eliminar
    function confirmarEliminacion(id, nombre) {
        const modal = document.getElementById('modalEliminar');
        const container = document.getElementById('deleteContainer');
        const form = document.getElementById('formEliminar');
        const display = document.getElementById('delete_nombre_display');
        if (!modal || !container) return;
        if (display) display.innerText = nombre;
        if (form) form.action = `/admin/categorias/${id}`;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('modalEliminar');
        const container = document.getElementById('deleteContainer');
        if (container) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
        }
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }
</script>

{{-- Modales --}}
@include('admin.categorias.modal-crear')
@include('admin.categorias.modal-eliminar')

@endsection