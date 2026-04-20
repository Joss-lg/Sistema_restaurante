@extends('layouts.admin')

@section('title', 'Privilegios de Acceso | Ollintem Pro')

@section('content')
<div class="p-4 lg:p-12 max-w-[1500px] mx-auto w-full space-y-8">
    
    <div class="relative overflow-hidden bg-[var(--card-color)] p-8 rounded-[3rem] border border-[var(--border-color)] shadow-2xl modo-crema:bg-white">
        <div class="absolute top-0 right-0 w-64 h-64 bg-[#3B82F6]/5 blur-[100px] rounded-full -mr-20 -mt-20"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-8">
                <div class="relative">
                    <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-[#3B82F6] to-[#1E40AF] flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                        <i class="fas fa-key text-3xl"></i>
                    </div>
                    <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-emerald-500 border-4 border-[var(--card-color)] flex items-center justify-center text-white text-[10px]">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div>
                    <h1 class="text-4xl font-black text-[var(--text-color)] tracking-tighter uppercase leading-none">Matriz de Control</h1>
                    <div class="flex items-center gap-3 mt-3">
                        <span class="px-3 py-1 rounded-full bg-[#3B82F6]/10 text-[#3B82F6] text-[10px] font-black uppercase tracking-widest border border-[#3B82F6]/20">
                            {{ $empleado->nombre }}
                        </span>
                        <span class="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em] opacity-60">
                            Rol: {{ $empleado->rol }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.empleados.index') }}" class="group flex items-center gap-3 px-8 py-4 rounded-2xl bg-[var(--bg-color)] border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--text-color)] text-[10px] font-black uppercase tracking-[0.2em] transition-all hover:bg-black/5 modo-crema:bg-zinc-50">
                    <i class="fas fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
                    Regresar
                </a>
            </div>
        </div>
    </div>

    <div class="bg-[var(--card-color)] rounded-[3rem] border border-[var(--border-color)] shadow-2xl overflow-hidden modo-crema:bg-white">
        <form action="{{ route('admin.empleados.permisos.update', $empleado->id) }}" method="POST" id="permisosForm">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-black/20 modo-crema:bg-zinc-50/80 border-b border-[var(--border-color)]">
                            <th class="py-10 px-10 text-[11px] font-black text-[var(--text-color)] uppercase tracking-[0.4em] text-left">Módulos</th>
                            @php $permisos = ['Ver', 'Crear', 'Editar', 'Borrar', 'Gestionar']; @endphp
                            @foreach($permisos as $p)
                            <th class="py-10 px-4 text-[11px] font-black text-[var(--text-color)] uppercase tracking-[0.4em] text-center">{{ $p }}</th>
                            @endforeach
                            <th class="py-10 px-10 text-[11px] font-black text-[#3B82F6] uppercase tracking-[0.4em] text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border-color)]">
                        @php
                            $items = [
                                ['n' => 'Dashboard', 'i' => 'fa-chart-line'],
                                ['n' => 'Inventario', 'i' => 'fa-boxes-stacked'],
                                ['n' => 'Empleados', 'i' => 'fa-users-gear'],
                                ['n' => 'Alimentos', 'i' => 'fa-utensils'],
                                ['n' => 'Promociones', 'i' => 'fa-receipt'],
                                ['n' => 'Caja / POS', 'i' => 'fa-vault'],
                                ['n' => 'Configuración', 'i' => 'fa-sliders'],
                            ];
                        @endphp

                        @foreach($items as $item)
                        <tr class="modulo-row group hover:bg-[#3B82F6]/[0.03] transition-all duration-300">
                            <td class="py-8 px-10">
                                <div class="flex items-center gap-6">
                                    <div class="w-12 h-12 rounded-2xl bg-[var(--bg-color)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] group-hover:text-[#3B82F6] group-hover:border-[#3B82F6]/40 group-hover:shadow-lg transition-all duration-500">
                                        <i class="fas {{ $item['i'] }} text-lg"></i>
                                    </div>
                                    <span class="text-[13px] font-black text-[var(--text-color)] uppercase tracking-widest">{{ $item['n'] }}</span>
                                </div>
                            </td>

                            @foreach(['ver', 'crear', 'editar', 'eliminar', 'gestionar'] as $slug)
                            <td class="py-8 px-4">
                                <label class="relative flex items-center justify-center cursor-pointer group/check">
                                    <input type="checkbox" name="permisos[{{ strtolower($item['n']) }}][{{ $slug }}]" class="permiso-checkbox peer sr-only">
                                    <div class="w-9 h-9 rounded-xl border-2 border-[var(--border-color)] bg-[var(--input-bg)] transition-all duration-300
                                        peer-checked:bg-[#3B82F6] peer-checked:border-[#3B82F6] peer-checked:shadow-[0_0_20px_rgba(59,130,246,0.5)]
                                        group-hover/check:border-[#3B82F6]/50 flex items-center justify-center">
                                        <i class="fas fa-check text-white text-xs opacity-0 peer-checked:opacity-100 scale-50 peer-checked:scale-100 transition-all"></i>
                                    </div>
                                </label>
                            </td>
                            @endforeach

                            <td class="py-8 px-10 text-center">
                                <button type="button" class="toggle-row text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest hover:text-[#3B82F6] transition-colors outline-none">
                                    Todos
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-10 bg-black/20 modo-crema:bg-zinc-50 border-t border-[var(--border-color)] flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex items-center gap-4">
                    <div class="w-2 h-2 rounded-full bg-[#3B82F6] animate-pulse"></div>
                    <p class="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-[0.3em]">
                        Seguridad de Acceso Nivel: <span class="text-[var(--text-color)]">Empresarial</span>
                    </p>
                </div>
                
                <div class="flex gap-4 w-full md:w-auto">
                    <button type="reset" class="flex-1 md:flex-none px-10 py-5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] hover:text-rose-500 transition-all border border-transparent hover:border-rose-500/20">
                        Limpiar
                    </button>
                    <button type="submit" class="flex-1 md:flex-none px-14 py-5 bg-[#3B82F6] text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] shadow-[0_20px_40px_-10px_rgba(59,130,246,0.5)] hover:bg-blue-600 hover:-translate-y-1 active:scale-95 transition-all flex items-center justify-center gap-3">
                        <i class="fas fa-save text-sm"></i>
                        Confirmar Privilegios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    /* Estilos para el scrollbar premium */
    .overflow-x-auto::-webkit-scrollbar { height: 6px; }
    .overflow-x-auto::-webkit-scrollbar-track { background: transparent; }
    .overflow-x-auto::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }
</style>

<script>
    // Lógica para seleccionar toda la fila al dar clic en "Todos"
    document.querySelectorAll('.toggle-row').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const checkboxes = row.querySelectorAll('.permiso-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
            
            // Animación sutil de feedback
            this.style.color = '#3B82F6';
            setTimeout(() => { this.style.color = ''; }, 500);
        });
    });
</script>
@endsection