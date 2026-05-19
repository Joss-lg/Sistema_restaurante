<aside id="sidebar" class="w-[220px] backdrop-blur-2xl flex flex-col justify-between z-30 border-r shadow-2xl shrink-0 bg-[var(--sidebar-bg)] border-[var(--border-color)] transition-all duration-300">
    <div>
        {{-- Header del Logo --}}
        <div class="h-20 flex items-center justify-between px-4 border-b border-[var(--border-color)]">
            <div class="flex items-center gap-3 overflow-hidden">
                <div id="logo-icon" class="w-9 h-9 rounded-2xl bg-gradient-to-br from-[#3B82F6]/25 via-transparent to-[#a5f3fc]/10 border border-[#3B82F6]/30 flex items-center justify-center shadow-[0_0_20px_rgba(59,130,246,0.15)] shrink-0">
                    <img src="{{ asset('images/logo2.png') }}" alt="Logo" class="w-7 h-7 object-contain rounded-lg">
                </div>
                <div class="flex flex-col sidebar-text transition-opacity duration-200">
                    <span class="font-black tracking-[0.16em] text-[14px] text-[var(--text-color)] leading-none">OLLINTEM <span class="font-normal text-[#3B82F6]">PRO</span></span>
                    <span class="text-[8px] text-[var(--text-muted)] uppercase tracking-[0.3em] mt-1 opacity-90">Sistema POS</span>
                </div>
            </div>
            <button id="toggleSidebar" class="text-[var(--text-muted)] hover:text-[var(--text-color)] transition-all outline-none shrink-0 cursor-pointer z-50">
                <i class="fas fa-bars text-base"></i>
            </button>
        </div>

        <nav class="py-6 space-y-1 overflow-y-auto max-h-[calc(100vh-14rem)] scrollbar-hide" id="nav-container">
            
            {{-- Definimos todos los módulos con estilo moderno --}}
            @php
                $seccionesMenu = [
                    ['route' => 'admin.dashboard', 'icon' => 'fas fa-th-large', 'label' => 'Dashboard', 'permission' => 'dashboard.ver'],
                    ['route' => 'admin.caja.index', 'icon' => 'fas fa-cash-register', 'label' => 'Caja', 'permission' => 'caja.ver'],
                    ['route' => 'admin.mesas.index', 'icon' => 'fas fa-chair', 'label' => 'Mesas', 'permission' => 'mesas.ver'],
                    ['route' => 'admin.cocina.index', 'icon' => 'fas fa-fire-burner', 'label' => 'Cocina', 'permission' => 'cocina.ver'],
                    ['route' => 'admin.inventario.index', 'icon' => 'fas fa-cube', 'label' => 'Inventario', 'permission' => 'inventario.ver'],
                    ['route' => 'admin.empleados.index', 'icon' => 'fas fa-users', 'label' => 'Empleados', 'permission' => 'empleados.ver'],
                    ['route' => 'admin.productos.index', 'icon' => 'fas fa-utensils', 'label' => 'Menu', 'permission' => 'productos.ver'],
                    ['route' => 'admin.categorias.index', 'icon' => 'fas fa-layer-group', 'label' => 'Categorías', 'permission' => 'categorias.ver'],
                    ['route' => 'admin.promociones.index', 'icon' => 'fas fa-tags', 'label' => 'Promociones', 'permission' => 'promociones.ver'],
                   ['route' => 'roles.index', 'icon' => 'fas fa-id-badge', 'label' => 'Roles', 'permission' => 'roles.ver'],
                ];
            @endphp

            @foreach($seccionesMenu as $item)
                @if(auth()->user()->tienePermiso($item['permission']))
                    @php
                        try {
                            $url = route($item['route']);
                            $isActive = request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route']);
                        } catch (Exception $e) {
                            $url = '#';
                            $isActive = false;
                        }
                    @endphp

                    <a href="{{ $url }}"
                       class="relative flex items-center gap-3 px-4 py-2.5 mx-3 rounded-3xl transition-all duration-300 overflow-hidden group mb-2
                       {{ $isActive ? 'bg-gradient-to-r from-sky-500/15 via-sky-500/10 to-transparent border-l-4 border-sky-400 shadow-[0_12px_32px_-20px_rgba(56,189,248,0.35)]' : 'border-l-4 border-transparent hover:bg-white/5 hover:border-sky-400/30' }}">

                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl transition-all duration-300
                            {{ $isActive ? 'bg-sky-500/10 text-sky-300' : 'bg-white/5 text-[var(--text-muted)] group-hover:bg-sky-500/10 group-hover:text-sky-300' }}">
                            <i class="{{ $item['icon'] }} text-sm"></i>
                        </span>

                        <span class="sidebar-text text-xs transition-all duration-300
                            {{ $isActive ? 'text-[var(--text-color)] font-semibold' : 'text-[var(--text-muted)] font-medium group-hover:text-[var(--text-color)]' }}">
                            {{ $item['label'] }}
                        </span>

                        @if($isActive)
                            <span class="absolute inset-y-0 left-0 w-1 bg-gradient-to-b from-sky-400 to-transparent"></span>
                        @endif
                    </a>
                @endif
            @endforeach

        </nav>
    </div>

    {{-- Footer --}}
    <div class="p-5 border-t border-[var(--border-color)] flex flex-col gap-4">
        <div class="flex items-center gap-2.5 overflow-hidden">
            <div class="w-9 h-9 rounded-full bg-white text-black flex items-center justify-center font-black text-xs shrink-0 modo-crema:bg-black modo-crema:text-white">
                {{ substr(auth()->user()->nombre, 0, 2) }}
            </div>
            <div class="sidebar-text whitespace-nowrap transition-opacity duration-200">
                <p class="text-sm font-bold text-[var(--text-color)] leading-none mb-1">{{ auth()->user()->nombre }}</p>
                <p class="text-[8px] text-[var(--text-muted)] uppercase tracking-widest font-black">{{ auth()->user()->rol }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-[var(--input-bg)] border border-[var(--border-color)] hover:bg-black/10 text-[var(--text-muted)] hover:text-[var(--text-color)] py-2.5 rounded-xl transition-all duration-300 text-xs font-bold group">
                <i class="fas fa-sign-out-alt transition-transform group-hover:-translate-x-1 shrink-0"></i>
                <span class="sidebar-text transition-opacity duration-200">Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>