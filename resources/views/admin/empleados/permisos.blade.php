@extends('layouts.admin')

@section('title', 'Privilegios de Acceso | Ollintem Pro')

@section('content')
<div class="p-4 lg:p-12 max-w-[1500px] mx-auto w-full space-y-8">
    
    <div class="rounded-[3rem] shadow-2xl overflow-hidden border" style="background-color: var(--card-color); border-color: var(--border-color);">
        <form action="{{ route('admin.empleados.permisos.update', $empleado->id) }}" method="POST" id="permisosForm">
            @csrf
            
            <div class="overflow-x-auto [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:rounded-full" style="scrollbar-color: var(--border-color) transparent;">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b" style="background-color: var(--input-bg); border-color: var(--border-color);">
                            <th class="py-10 px-10 text-[11px] font-black uppercase tracking-[0.4em] text-left" style="color: var(--text-color);">Módulos</th>
                            @php $permisosHeader = ['Mostrar', 'Crear', 'Editar', 'Eliminar', 'Gestionar']; @endphp
                            @foreach($permisosHeader as $p)
                            <th class="py-10 px-4 text-[11px] font-black uppercase tracking-[0.4em] text-center" style="color: var(--text-color);">{{ $p }}</th>
                            @endforeach
                            <th class="py-10 px-10 text-[11px] font-black text-blue-500 uppercase tracking-[0.4em] text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--border-color);">
                        @php
                            // Mapeo dinámico de iconos basado en el nombre del módulo (en minúsculas)
                            $iconos = [
                                'dashboard'          => 'fa-th-large',
                                'inventario'         => 'fa-cube',
                                'empleados'          => 'fa-users',
                                'productos'          => 'fa-utensils',
                                'categorías'         => 'fa-layer-group',
                                'categorias'         => 'fa-layer-group',
                                'mesas'              => 'fa-chair',
                                'promociones'        => 'fa-tags',
                                'cocina'             => 'fa-fire-burner',
                                'caja'               => 'fa-cash-register',
                                'finanzas'           => 'fa-chart-line',
                                'roles'              => 'fa-id-badge',
                                'historial de cajas' => 'fa-history'
                            ];
                        @endphp

                        {{-- Iteramos sobre los módulos pasados desde el EmpleadoController --}}
                        @foreach($modulos as $modulo)
                            @php
                                $nombreModulo = strtolower($modulo->nombre);
                                $icono = $iconos[$nombreModulo] ?? 'fa-circle'; // Fallback a fa-circle si no encuentra el icono
                            @endphp

                            <tr class="modulo-row group hover:bg-blue-500/[0.03] transition-all duration-300">
                                <td class="py-8 px-10">
                                    <div class="flex items-center gap-6">
                                        <div class="w-12 h-12 rounded-2xl border flex items-center justify-center group-hover:text-blue-500 group-hover:border-blue-500/40 group-hover:shadow-lg transition-all duration-500" style="background-color: var(--bg-color); border-color: var(--border-color); color: var(--text-muted);">
                                            <i class="fas {{ $icono }} text-lg"></i>
                                        </div>
                                        <span class="text-[13px] font-black uppercase tracking-widest" style="color: var(--text-color);">{{ $modulo->nombre }}</span>
                                    </div>
                                </td>

                                @php
                                    // Validamos qué permisos tiene ya guardados este usuario para el módulo actual
                                    $permisoActual = $empleado->permisos->where('modulo_id', $modulo->id)->first();
                                @endphp

                                @foreach(['mostrar', 'crear', 'editar', 'eliminar', 'gestionar'] as $accion)
                                    <td class="py-8 px-4">
                                        <label class="relative flex items-center justify-center cursor-pointer group/check">
                                            <input type="checkbox" 
                                                name="permisos[{{ $modulo->id }}][{{ $accion }}]" 
                                                value="1"
                                                class="permiso-checkbox peer sr-only"
                                                {{ ($permisoActual && $permisoActual->$accion) ? 'checked' : '' }}>
                                            
                                            {{-- Contenedor con alto contraste adaptativo y efecto Glow --}}
                                            <div class="w-10 h-10 rounded-xl border-2 transition-all duration-300 ease-out 
                                                flex items-center justify-center
                                                bg-slate-500/10 border-slate-400/40 
                                                peer-checked:bg-blue-500 peer-checked:border-blue-400 
                                                peer-checked:shadow-[0_0_15px_rgba(59,130,246,0.6)] 
                                                peer-checked:scale-110 
                                                group-hover/check:border-blue-400/80">
                                                
                                                {{-- Icono Check animado con escala --}}
                                                <i class="fas fa-check text-white text-sm font-black opacity-0 scale-50 transition-all duration-300 peer-checked:opacity-100 peer-checked:scale-100"></i>
                                            </div>
                                        </label>
                                    </td>
                                @endforeach

                                <td class="py-8 px-10 text-center">
                                    <button type="button" class="toggle-row text-[9px] font-black uppercase tracking-widest hover:text-blue-500 transition-colors outline-none" style="color: var(--text-muted);">
                                        Todos
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-10 border-t flex flex-col md:flex-row justify-between items-center gap-8" style="background-color: var(--input-bg); border-color: var(--border-color);">
                <div class="flex items-center gap-4">
                    <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em]" style="color: var(--text-muted);">
                        Seguridad de Acceso Nivel: <span style="color: var(--text-color);">Granular</span>
                    </p>
                </div>
                
                <div class="flex gap-4 w-full md:w-auto">
                    <button type="reset" class="flex-1 md:flex-none px-10 py-5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:text-rose-500 transition-all border border-transparent hover:border-rose-500/20" style="color: var(--text-muted);">
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
            setTimeout(() => { this.classList.remove('text-blue-500'); }, 300);
        });
    });
</script>
@endsection