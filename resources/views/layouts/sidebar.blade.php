<aside id="sidebar" class="w-[260px] backdrop-blur-2xl flex flex-col justify-between z-30 border-r shadow-2xl shrink-0 bg-[var(--sidebar-bg)] border-[var(--border-color)] transition-all duration-300">
    <div>
        {{-- Header del Logo --}}
        <div class="h-24 flex items-center justify-between px-6 border-b border-[var(--border-color)]">
            <div class="flex items-center gap-3.5 overflow-hidden">
                <div id="logo-icon" class="w-9 h-9 bg-gradient-to-br from-[#3B82F6]/30 to-transparent border border-[#3B82F6]/40 rounded-xl flex items-center justify-center shadow-[0_0_20px_rgba(59,130,246,0.2)] shrink-0">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="w-full h-full object-contain rounded-lg p-0.5">
                </div>
                <div class="flex flex-col sidebar-text transition-opacity duration-200">
                    <span class="font-black tracking-widest text-[15px] text-[var(--text-color)] leading-none">OLLINTEM <span class="font-normal text-[#3B82F6]">PRO</span></span>
                    <span class="text-[7.5px] text-[var(--text-muted)] uppercase tracking-[0.25em] mt-1.5 opacity-80">Management System</span>
                </div>
            </div>
            <button id="toggleSidebar" class="text-[var(--text-muted)] hover:text-[var(--text-color)] transition-all outline-none shrink-0 cursor-pointer z-50">
                <i class="fas fa-bars text-lg"></i>
            </button>
        </div>

        <nav class="py-6 space-y-1 overflow-y-auto max-h-[calc(100vh-14rem)] scrollbar-hide" id="nav-container">
            
            {{-- Definimos todos los módulos separados por secciones --}}
            @php
                $seccionesMenu = [
                    'Principal' => [
                        ['route' => 'admin.dashboard', 'icon' => 'fas fa-th-large', 'label' => 'Dashboard', 'permission' => 'dashboard.ver'],
                        ['route' => 'admin.caja.index', 'icon' => 'fas fa-cash-register', 'label' => 'Caja', 'permission' => 'caja.ver'],
                        ['route' => 'admin.mesas.index', 'icon' => 'fas fa-chair', 'label' => 'Mesas', 'permission' => 'mesas.ver'],
                        ['route' => 'admin.cocina.index', 'icon' => 'fas fa-fire-burner', 'label' => 'Cocina', 'permission' => 'cocina.ver'],
                    ],
                    'Administración' => [
                        ['route' => 'admin.inventario.index', 'icon' => 'fas fa-cube', 'label' => 'Inventario', 'permission' => 'inventario.ver'],
                        ['route' => 'admin.empleados.index', 'icon' => 'fas fa-users', 'label' => 'Empleados', 'permission' => 'empleados.ver'],
                    ],
                    'Catálogos' => [
                        ['route' => 'admin.productos.index', 'icon' => 'fas fa-utensils', 'label' => 'Alimentos', 'permission' => 'productos.ver'],
                        ['route' => 'admin.categorias.index', 'icon' => 'fas fa-layer-group', 'label' => 'Categorías', 'permission' => 'categorias.ver'],
                        ['route' => 'admin.promociones.index', 'icon' => 'fas fa-tags', 'label' => 'Promociones', 'permission' => 'promociones.ver'],
                    ]
                ];
            @endphp

            {{-- Renderizamos las secciones dinámicamente con tus estilos brillantes --}}
            @foreach($seccionesMenu as $tituloSeccion => $modulos)
                {{-- Título de la sección (Principal, Adminsitración, Catálogos) --}}
                <p class="sidebar-text px-8 text-[8.5px] font-black text-[var(--text-muted)]/70 uppercase tracking-[0.25em] mb-4 {{ $loop->first ? '' : 'mt-6' }} transition-opacity duration-200">
                    {{ $tituloSeccion }}
                </p>

                @foreach($modulos as $item)
                    @if(auth()->user()->tienePermiso($item['permission']))
                        {{-- Manejo de rutas opcionales temporalmente con '#' si no existen aún --}}
                        @php 
                            try {
                                $url = route($item['route']);
                                // Solo marcamos como activo si la ruta coincide y no es '#'
                                $isActive = request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route']);
                            } catch (Exception $e) {
                                $url = '#';
                                $isActive = false;
                            }
                        @endphp

                        <a href="{{ $url }}" 
                           class="relative flex items-center gap-4 px-4 py-3 mx-4 rounded-xl transition-all duration-300 group mb-1
                           {{ $isActive 
                              ? 'bg-gradient-to-r from-[#3B82F6]/15 via-[#3B82F6]/5 to-transparent border-l-4 border-[#3B82F6] shadow-[0_4px_15px_-3px_rgba(59,130,246,0.2)]' 
                              : 'border-l-4 border-transparent hover:bg-black/5 hover:border-l-zinc-300' }}">
                            
                            <i class="{{ $item['icon'] }} w-5 text-center transition-all duration-300 group-hover:scale-110 shrink-0 
                               {{ $isActive ? 'text-[#3B82F6] drop-shadow-[0_0_8px_rgba(59,130,246,0.5)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text-color)]' }}"></i>
                            
                            <span class="sidebar-text text-sm transition-opacity duration-200 
                               {{ $isActive ? 'text-[var(--text-color)] font-black' : 'text-[var(--text-muted)] font-medium group-hover:text-[var(--text-color)]' }}">
                               {{ $item['label'] }}
                            </span>

                            {{-- Reflejo de luz en la parte superior del botón activo --}}
                            @if($isActive)
                                <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-[#3B82F6]/30 to-transparent"></div>
                            @endif
                        </a>
                    @endif
                @endforeach
            @endforeach

        </nav>
    </div>

    {{-- Footer --}}
    <div class="p-6 border-t border-[var(--border-color)] flex flex-col gap-6">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-10 h-10 rounded-full bg-white text-black flex items-center justify-center font-black text-xs shrink-0 modo-crema:bg-black modo-crema:text-white">
                {{ substr(auth()->user()->nombre, 0, 2) }}
            </div>
            <div class="sidebar-text whitespace-nowrap transition-opacity duration-200">
                <p class="text-sm font-bold text-[var(--text-color)] leading-none mb-1">{{ auth()->user()->nombre }}</p>
                <p class="text-[8px] text-[var(--text-muted)] uppercase tracking-widest font-black">{{ auth()->user()->rol }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] hover:bg-black/10 text-[var(--text-muted)] hover:text-[var(--text-color)] py-3 rounded-xl transition-all duration-300 text-xs font-bold group">
                <i class="fas fa-sign-out-alt transition-transform group-hover:-translate-x-1 shrink-0"></i>
                <span class="sidebar-text transition-opacity duration-200">Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>