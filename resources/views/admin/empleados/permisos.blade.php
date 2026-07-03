@extends('layouts.admin')

@section('title', 'Privilegios de Acceso | Ollintem Pro')

@section('content')
<div class="p-4 lg:p-12 max-w-[1500px] mx-auto w-full space-y-8">
    
    <div class="bg-zinc-900 border border-zinc-800 rounded-[3rem] shadow-2xl overflow-hidden modo-crema:bg-white modo-crema:border-zinc-200">
        <form action="{{ route('admin.empleados.permisos.update', $empleado->id) }}" method="POST" id="permisosForm">
            @csrf
            <div class="overflow-x-auto [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-zinc-700 modo-crema:[&::-webkit-scrollbar-thumb]:bg-zinc-300 [&::-webkit-scrollbar-thumb]:rounded-full">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-black/20 border-b border-zinc-800 modo-crema:bg-zinc-50/80 modo-crema:border-zinc-200">
                            <th class="py-10 px-10 text-[11px] font-black text-zinc-100 modo-crema:text-zinc-800 uppercase tracking-[0.4em] text-left">Módulos</th>
                            @php $permisosHeader = ['Ver', 'Crear', 'Editar', 'Borrar', 'Gestión']; @endphp
                            @foreach($permisosHeader as $p)
                            <th class="py-10 px-4 text-[11px] font-black text-zinc-100 modo-crema:text-zinc-800 uppercase tracking-[0.4em] text-center">{{ $p }}</th>
                            @endforeach
                            <th class="py-10 px-10 text-[11px] font-black text-blue-500 uppercase tracking-[0.4em] text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800 modo-crema:divide-zinc-200">
                        @php
                            // Catálogo de módulos con sus IDs correspondientes a tu tabla 'modulos'
                            $items = [
                                ['id' => 1,  'n' => 'Dashboard',   'i' => 'fa-th-large'],
                                ['id' => 2,  'n' => 'Inventario',  'i' => 'fa-cube'],
                                ['id' => 3,  'n' => 'Empleados',   'i' => 'fa-users'],
                                ['id' => 4,  'n' => 'Productos',   'i' => 'fa-utensils'],
                                ['id' => 5,  'n' => 'Categorías',  'i' => 'fa-layer-group'],
                                ['id' => 6,  'n' => 'Mesas',       'i' => 'fa-chair'],
                                ['id' => 7,  'n' => 'Promociones', 'i' => 'fa-tags'],
                                ['id' => 8,  'n' => 'Cocina',      'i' => 'fa-fire-burner'],
                                ['id' => 9,  'n' => 'Caja',        'i' => 'fa-cash-register'],
                                ['id' => 10, 'n' => 'Finanzas',    'i' => 'fa-chart-line'],
                            ];
                        @endphp

                        @foreach($items as $item)
                            <tr class="modulo-row group hover:bg-blue-500/[0.03] transition-all duration-300">
                                <td class="py-8 px-10">
                                    <div class="flex items-center gap-6">
                                        <div class="w-12 h-12 rounded-2xl bg-zinc-950 border border-zinc-800 flex items-center justify-center text-zinc-400 group-hover:text-blue-500 group-hover:border-blue-500/40 group-hover:shadow-lg modo-crema:bg-zinc-50 modo-crema:border-zinc-200 modo-crema:text-zinc-500 transition-all duration-500">
                                            <i class="fas {{ $item['i'] }} text-lg"></i>
                                        </div>
                                        <span class="text-[13px] font-black text-zinc-200 modo-crema:text-zinc-700 uppercase tracking-widest">{{ $item['n'] }}</span>
                                    </div>
                                </td>

                                @php
                                    // Validamos qué permisos tiene ya guardados este usuario para el módulo actual
                                    $permisoActual = $empleado->permisos->where('modulo_id', $item['id'])->first();
                                @endphp

                                @foreach(['mostrar', 'crear', 'editar', 'eliminar', 'gestionar'] as $accion)
                                    <td class="py-8 px-4">
                                        <label class="relative flex items-center justify-center cursor-pointer group/check">
                                            <input type="checkbox" 
                                                name="permisos[{{ $item['id'] }}][{{ $accion }}]" 
                                                value="1"
                                                class="permiso-checkbox peer sr-only"
                                                {{ ($permisoActual && $permisoActual->$accion) ? 'checked' : '' }}>
                                            
                                            <div class="w-9 h-9 rounded-xl border-2 border-zinc-700 bg-zinc-950 transition-all duration-300 peer-checked:bg-blue-500 peer-checked:border-blue-500 peer-checked:shadow-[0_0_20px_rgba(59,130,246,0.5)] group-hover/check:border-blue-500/50 flex items-center justify-center modo-crema:bg-white modo-crema:border-zinc-300">
                                                <i class="fas fa-check text-white text-xs opacity-0 peer-checked:opacity-100 scale-50 peer-checked:scale-100 transition-all"></i>
                                            </div>
                                        </label>
                                    </td>
                                @endforeach

                                <td class="py-8 px-10 text-center">
                                    <button type="button" class="toggle-row text-[9px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-widest hover:text-blue-500 transition-colors outline-none">
                                        Todos
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-10 bg-black/20 border-t border-zinc-800 flex flex-col md:flex-row justify-between items-center gap-8 modo-crema:bg-zinc-50 modo-crema:border-zinc-200">
                <div class="flex items-center gap-4">
                    <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                    <p class="text-[10px] font-black text-zinc-400 modo-crema:text-zinc-500 uppercase tracking-[0.3em]">
                        Seguridad de Acceso Nivel: <span class="text-zinc-200 modo-crema:text-zinc-800">Granular</span>
                    </p>
                </div>
                
                <div class="flex gap-4 w-full md:w-auto">
                    <button type="reset" class="flex-1 md:flex-none px-10 py-5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-zinc-400 modo-crema:text-zinc-500 hover:text-rose-500 transition-all border border-transparent hover:border-rose-500/20">
                        Limpiar
                    </button>
                    <button type="submit" class="flex-1 md:flex-none px-14 py-5 bg-blue-500 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] shadow-[0_20px_40px_-10px_rgba(59,130,246,0.5)] hover:bg-blue-600 hover:-translate-y-1 active:scale-95 transition-all flex items-center justify-center gap-3">
                        <i class="fas fa-save text-sm"></i>
                        Confirmar Privilegios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.toggle-row').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const checkboxes = row.querySelectorAll('.permiso-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
            
            this.classList.add('text-blue-500');
            setTimeout(() => { this.classList.remove('text-blue-500'); }, 500);
        });
    });
</script>
@endsection