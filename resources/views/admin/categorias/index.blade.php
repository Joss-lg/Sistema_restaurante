{{-- resources/views/admin/categorias/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Categorías | Ollintem Pro')

@section('content')
<div class="p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col">

    <div class="relative overflow-hidden rounded-[2.5rem] border border-white/10 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.18),_transparent_20%),radial-gradient(circle_at_bottom_right,_rgba(16,185,129,0.12),_transparent_25%),rgba(7,10,19,0.96)] shadow-[0_45px_120px_-50px_rgba(0,0,0,0.75)]">
        <div class="pointer-events-none absolute -top-16 left-1/2 h-44 w-44 -translate-x-1/2 rounded-full bg-cyan-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-20 right-12 h-56 w-56 rounded-full bg-emerald-500/15 blur-3xl"></div>
        <div class="relative p-8 lg:p-10 xl:p-12 space-y-8">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8">
                <div class="max-w-2xl">
                    <p class="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-500/10 px-4 py-2 text-xs font-black uppercase tracking-[0.35em] text-cyan-300 shadow-[0_10px_40px_-25px_rgba(56,189,248,0.65)]">
                        <i class="fas fa-layer-group text-[10px]"></i> Categorías Premium
                    </p>
                    <h1 class="mt-6 text-4xl md:text-5xl font-black tracking-tight text-white">Gestión de Categorías</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300/90">Diseña y organiza el menú con un tablero visual moderno, rápido y con toda la información que necesitas al primer vistazo.</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-4 w-full sm:w-auto">
                    <div class="relative w-full sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-search text-sm"></i>
                        </div>
                        <input type="text" id="buscadorCategorias" placeholder="Buscar categoría..."
                            class="w-full h-12 rounded-2xl border border-white/10 bg-slate-950/80 pl-12 pr-4 text-sm font-semibold text-white placeholder:text-slate-500 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 outline-none transition-all" />
                    </div>
                    <button onclick="openModalCrear()"
                        class="inline-flex items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-sky-500 to-cyan-400 px-6 py-3 text-sm font-black uppercase tracking-[0.15em] text-slate-950 shadow-[0_20px_50px_-30px_rgba(56,189,248,0.8)] transition-transform duration-200 hover:-translate-y-0.5 hover:shadow-[0_25px_60px_-35px_rgba(56,189,248,0.9)]">
                        <i class="fas fa-plus"></i> Nueva categoría
                    </button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-[1.75rem] border border-white/10 bg-slate-950/80 p-6 shadow-[0_20px_70px_-50px_rgba(0,0,0,0.8)] backdrop-blur-xl">
                    <span class="text-xs uppercase tracking-[0.32em] text-slate-400">Categorías</span>
                    <p class="mt-4 text-4xl font-black text-white">{{ count($categorias) }}</p>
                    <p class="mt-2 text-sm text-slate-400">Total de bloques en el menú.</p>
                </div>
                <div class="rounded-[1.75rem] border border-white/10 bg-slate-950/80 p-6 shadow-[0_20px_70px_-50px_rgba(0,0,0,0.8)] backdrop-blur-xl">
                    <span class="text-xs uppercase tracking-[0.32em] text-slate-400">Platillos</span>
                    <p class="mt-4 text-4xl font-black text-white">{{ $categorias->sum('productos_count') }}</p>
                    <p class="mt-2 text-sm text-slate-400">Platos asignados por categoría.</p>
                </div>
                <div class="rounded-[1.75rem] border border-white/10 bg-slate-950/80 p-6 shadow-[0_20px_70px_-50px_rgba(0,0,0,0.8)] backdrop-blur-xl">
                    <span class="text-xs uppercase tracking-[0.32em] text-slate-400">Orden</span>
                    <p class="mt-4 text-4xl font-black text-white">{{ $categorias->max('orden_visualizacion') ?? 0 }}</p>
                    <p class="mt-2 text-sm text-slate-400">Mayor prioridad visible.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
    <div class="fixed right-6 top-6 z-50 max-w-lg w-[min(95vw,420px)] rounded-3xl border border-emerald-300/20 bg-slate-950/95 px-5 py-4 shadow-[0_30px_80px_-35px_rgba(0,0,0,0.55)] backdrop-blur-xl text-white text-sm font-semibold flex items-start gap-3">
        <div class="grid h-11 w-11 place-items-center rounded-3xl bg-emerald-500/10 text-emerald-300 shadow-[0_0_25px_rgba(16,185,129,0.18)]">
            <i class="fas fa-check"></i>
        </div>
        <div>
            <strong class="block text-sm">Éxito</strong>
            <p class="mt-1 text-[0.95rem] text-slate-300">{{ session('success') }}</p>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="fixed right-6 top-6 z-50 max-w-lg w-[min(95vw,420px)] rounded-3xl border border-rose-300/20 bg-slate-950/95 px-5 py-4 shadow-[0_30px_80px_-35px_rgba(0,0,0,0.55)] backdrop-blur-xl text-white text-sm font-semibold flex items-start gap-3">
        <div class="grid h-11 w-11 place-items-center rounded-3xl bg-rose-500/10 text-rose-300 shadow-[0_0_25px_rgba(244,63,94,0.18)]">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div>
            <strong class="block text-sm">Error</strong>
            <p class="mt-1 text-[0.95rem] text-slate-300">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <div class="rounded-[2rem] border border-white/10 bg-slate-950/90 shadow-[0_45px_120px_-50px_rgba(0,0,0,0.8)] overflow-hidden">
        <div class="flex flex-col gap-6 p-6 lg:p-8 border-b border-white/10 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-cyan-300/80">Listado de Categorías</p>
                <h2 class="mt-3 text-2xl font-black text-white">Catálogo premium</h2>
                <p class="mt-2 text-sm text-slate-400">Gestiona tus categorías con un estilo limpio y funcional.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">
                <div class="rounded-2xl border border-white/10 bg-slate-950/80 px-4 py-3 text-sm text-slate-300">
                    <span class="block text-[0.8rem] uppercase tracking-[0.3em] text-slate-500">Registradas</span>
                    <span class="mt-1 block text-xl font-black text-white">{{ count($categorias) }}</span>
                </div>
                <button onclick="openModalCrear()"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-500 to-cyan-400 px-5 py-3 text-sm font-black uppercase tracking-[0.15em] text-slate-950 shadow-[0_20px_50px_-25px_rgba(56,189,248,0.8)] transition duration-200 hover:-translate-y-0.5">
                    <i class="fas fa-plus"></i> Nueva categoría
                </button>
            </div>
        </div>

        <div class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full border-separate border-spacing-0 text-left">
                    <thead class="bg-white/5 text-[0.72rem] uppercase tracking-[0.35em] text-slate-400">
                        <tr>
                            <th class="px-6 py-4">Categoría</th>
                            <th class="px-6 py-4">Slug</th>
                            <th class="px-6 py-4">Orden</th>
                            <th class="px-6 py-4">Platillos</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCategorias" class="divide-y divide-white/5 bg-slate-950/70">
                        @forelse($categorias as $categoria)
                        <tr class="fila-categoria group transition duration-300 hover:bg-slate-900/90">
                            <td class="px-6 py-5 nombre-celda">
                                <div class="flex items-center gap-4">
                                    <div class="h-11 w-11 rounded-3xl border border-white/10 shadow-[0_0_20px_rgba(59,130,246,0.15)]"
                                        style="background-color: {{ $categoria->color ?? '#3B82F6' }}"></div>
                                    <div>
                                        <p class="text-sm font-bold text-white">{{ $categoria->nombre }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Orden {{ $categoria->orden_visualizacion ?? 0 }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center rounded-full bg-white/5 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-300 ring-1 ring-white/10">
                                    {{ $categoria->slug }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-sm font-semibold text-slate-300">{{ $categoria->orden_visualizacion ?? 0 }}</td>
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center rounded-full bg-cyan-500/10 px-3 py-2 text-[11px] font-black uppercase tracking-[0.2em] text-cyan-200 ring-1 ring-cyan-400/10">
                                    {{ $categoria->productos_count ?? $categoria->productos()->count() }} platillos
                                </span>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <button type="button" title="Editar"
                                        onclick="abrirModalEspecifico('modalEditar-{{ $categoria->id }}')"
                                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-cyan-300 transition hover:bg-cyan-500/12 hover:text-cyan-100">
                                        <i class="fas fa-cog text-sm"></i>
                                    </button>
                                    <button type="button" title="Eliminar"
                                        onclick="confirmarEliminacion('{{ $categoria->id }}', '{{ $categoria->nombre }}')"
                                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-rose-400 transition hover:bg-rose-500/12 hover:text-rose-200">
                                        <i class="far fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @include('admin.categorias.modal-editar', ['categoria' => $categoria])
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="mx-auto flex max-w-md flex-col items-center gap-4 text-slate-400">
                                    <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/5 text-slate-400">
                                        <i class="fas fa-tags text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-semibold">No hay categorías registradas aún.</p>
                                    <button onclick="openModalCrear()" class="rounded-2xl bg-slate-900/80 px-5 py-3 text-sm font-black uppercase tracking-[0.15em] text-white shadow-[0_20px_50px_-30px_rgba(0,0,0,0.6)] hover:bg-slate-800/90">Crear la primera categoría</button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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