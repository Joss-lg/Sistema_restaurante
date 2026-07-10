@extends('layouts.admin')

@section('title', 'Privilegios de Acceso | Ollintem Pro')

@section('content')
<div class="p-3 sm:p-6 lg:p-12 max-w-[1500px] mx-auto w-full space-y-6 sm:space-y-8">
    
    <div class="rounded-2xl sm:rounded-[2rem] lg:rounded-[3rem] shadow-2xl overflow-hidden border" style="background-color: var(--card-color); border-color: var(--border-color);">
        <form action="{{ route('admin.empleados.permisos.update', $empleado->id) }}" method="POST" id="permisosForm">
            @csrf
            
            <div class="overflow-x-auto [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:rounded-full" style="scrollbar-color: var(--border-color) transparent; -webkit-overflow-scrolling: touch;">
                <table class="w-full min-w-[640px] sm:min-w-[760px] border-collapse">
                    <thead>
                        <tr class="border-b" style="background-color: var(--input-bg); border-color: var(--border-color);">
                            <th class="py-4 px-4 sm:py-6 sm:px-6 lg:py-10 lg:px-10 text-[8px] sm:text-[9px] lg:text-[11px] font-black uppercase tracking-[0.15em] sm:tracking-[0.25em] lg:tracking-[0.4em] text-left whitespace-nowrap" style="color: var(--text-color);">Módulos</th>
                            @php $permisosHeader = ['Mostrar', 'Crear', 'Editar', 'Eliminar', 'Gestionar']; @endphp
                            @foreach($permisosHeader as $p)
                            <th class="py-4 px-2 sm:py-6 sm:px-3 lg:py-10 lg:px-4 text-[8px] sm:text-[9px] lg:text-[11px] font-black uppercase tracking-[0.1em] sm:tracking-[0.2em] lg:tracking-[0.4em] text-center whitespace-nowrap" style="color: var(--text-color);">{{ $p }}</th>
                            @endforeach
                            <th class="py-4 px-4 sm:py-6 sm:px-6 lg:py-10 lg:px-10 text-[8px] sm:text-[9px] lg:text-[11px] font-black text-blue-500 uppercase tracking-[0.15em] sm:tracking-[0.25em] lg:tracking-[0.4em] text-center whitespace-nowrap">Acción</th>
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
                                <td class="py-3 px-4 sm:py-5 sm:px-6 lg:py-8 lg:px-10">
                                    <div class="flex items-center gap-2.5 sm:gap-4 lg:gap-6">
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 rounded-xl lg:rounded-2xl border flex items-center justify-center shrink-0 group-hover:text-blue-500 group-hover:border-blue-500/40 group-hover:shadow-lg transition-all duration-500" style="background-color: var(--bg-color); border-color: var(--border-color); color: var(--text-muted);">
                                            <i class="fas {{ $icono }} text-xs sm:text-sm lg:text-lg"></i>
                                        </div>
                                        <span class="text-[10px] sm:text-[11px] lg:text-[13px] font-black uppercase tracking-wide sm:tracking-widest whitespace-nowrap" style="color: var(--text-color);">{{ $modulo->nombre }}</span>
                                    </div>
                                </td>

                                @php
                                    // Validamos qué permisos tiene ya guardados este usuario para el módulo actual
                                    $permisoActual = $empleado->permisos->where('modulo_id', $modulo->id)->first();
                                @endphp

                                @foreach(['mostrar', 'crear', 'editar', 'eliminar', 'gestionar'] as $accion)
                                    <td class="py-3 px-2 sm:py-5 sm:px-3 lg:py-8 lg:px-4">
                                        <label class="relative flex items-center justify-center cursor-pointer group/check">
                                            <input type="checkbox" 
                                                name="permisos[{{ $modulo->id }}][{{ $accion }}]" 
                                                value="1"
                                                class="permiso-checkbox peer sr-only"
                                                {{ ($permisoActual && $permisoActual->$accion) ? 'checked' : '' }}>
                                            
                                            {{-- Contenedor con alto contraste adaptativo y efecto Glow --}}
                                            <div class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 rounded-lg lg:rounded-xl border-2 transition-all duration-300 ease-out 
                                                flex items-center justify-center
                                                bg-slate-500/10 border-slate-400/40 
                                                peer-checked:bg-blue-500 peer-checked:border-blue-400 
                                                peer-checked:shadow-[0_0_15px_rgba(59,130,246,0.6)] 
                                                peer-checked:scale-110 
                                                group-hover/check:border-blue-400/80">
                                                
                                                {{-- Icono Check animado con escala --}}
                                                <i class="fas fa-check text-white text-[10px] sm:text-xs lg:text-sm font-black opacity-0 scale-50 transition-all duration-300 peer-checked:opacity-100 peer-checked:scale-100"></i>
                                            </div>
                                        </label>
                                    </td>
                                @endforeach

                                <td class="py-3 px-4 sm:py-5 sm:px-6 lg:py-8 lg:px-10 text-center">
                                    <button type="button" class="toggle-row text-[8px] sm:text-[9px] font-black uppercase tracking-wide sm:tracking-widest hover:text-blue-500 transition-colors outline-none whitespace-nowrap" style="color: var(--text-muted);">
                                        Todos
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 sm:p-6 lg:p-10 border-t flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 sm:gap-6 lg:gap-8" style="background-color: var(--input-bg); border-color: var(--border-color);">
                <div class="flex items-center gap-3 sm:gap-4 order-2 md:order-1">
                    <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse shrink-0"></div>
                    <p class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] sm:tracking-[0.3em]" style="color: var(--text-muted);">
                        Seguridad de Acceso Nivel: <span style="color: var(--text-color);">Granular</span>
                    </p>
                </div>
                
                <div class="flex gap-3 sm:gap-4 w-full md:w-auto order-1 md:order-2">
                    <button type="reset" class="flex-1 md:flex-none px-6 py-3.5 sm:px-10 sm:py-4 lg:px-10 lg:py-5 rounded-xl lg:rounded-2xl text-[9px] sm:text-[10px] font-black uppercase tracking-wide sm:tracking-widest hover:text-rose-500 transition-all border border-transparent hover:border-rose-500/20" style="color: var(--text-muted);">
                        Limpiar
                    </button>
                    <button type="submit" class="flex-1 md:flex-none px-6 py-3.5 sm:px-12 sm:py-4 lg:px-14 lg:py-5 bg-blue-500 text-white rounded-xl lg:rounded-2xl text-[10px] sm:text-[11px] font-black uppercase tracking-[0.1em] sm:tracking-[0.2em] shadow-[0_20px_40px_-10px_rgba(59,130,246,0.5)] hover:bg-blue-600 hover:-translate-y-1 active:scale-95 transition-all flex items-center justify-center gap-2 sm:gap-3">
                        <i class="fas fa-save text-xs sm:text-sm"></i>
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