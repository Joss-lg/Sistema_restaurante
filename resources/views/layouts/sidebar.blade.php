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

        <nav class="py-6 space-y-1" id="nav-container">
            <p class="sidebar-text px-8 text-[8.5px] font-black text-[var(--text-muted)]/70 uppercase tracking-[0.25em] mb-4 transition-opacity duration-200">Principal</p>
            
            {{-- DASHBOARD --}}
            @if(auth()->user()->tienePermiso('dashboard.ver'))
            <a href="{{ route('admin.dashboard') }}" class="relative flex items-center gap-4 px-4 py-3 mx-4 rounded-xl transition-all group {{ request()->routeIs('admin.dashboard') ? 'bg-[#111114] border border-[var(--border-color)] border-l-[3px] border-l-[#3B82F6] shadow-md modo-crema:bg-white' : 'border border-transparent' }}">
                <i class="fas fa-th-large w-5 text-center transition-all group-hover:scale-110 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-[#3B82F6]' : 'text-[var(--text-muted)] group-hover:text-[var(--text-color)]' }}"></i>
                <span class="sidebar-text text-sm transition-opacity duration-200 {{ request()->routeIs('admin.dashboard') ? 'text-[var(--text-color)] font-bold' : 'text-[var(--text-muted)] font-medium group-hover:text-[var(--text-color)]' }}">Dashboard</span>
            </a>
            @endif

            {{-- INVENTARIO --}}
            @if(auth()->user()->tienePermiso('inventario.ver'))
            <a href="{{ route('admin.inventario.index') ?? '#' }}" class="relative flex items-center gap-4 px-4 py-3 mx-4 rounded-xl transition-all group {{ request()->routeIs('admin.inventario.*') ? 'active-class-style' : 'border border-transparent' }}">
                <i class="fas fa-cube w-5 text-center transition-all group-hover:scale-110 shrink-0 text-[var(--text-muted)] group-hover:text-[var(--text-color)]"></i>
                <span class="sidebar-text text-sm font-medium text-[var(--text-muted)] group-hover:text-[var(--text-color)] transition-opacity duration-200">Inventario</span>
            </a>
            @endif
            
            {{-- EMPLEADOS --}}
            @if(auth()->user()->tienePermiso('empleados.ver'))
            <a href="{{ route('admin.empleados.index') }}" class="relative flex items-center gap-4 px-4 py-3 mx-4 rounded-xl transition-all group {{ request()->routeIs('admin.empleados.*') ? 'bg-[#111114] border border-[var(--border-color)] border-l-[3px] border-l-[#3B82F6] shadow-md modo-crema:bg-white' : 'border border-transparent' }}">
                <i class="fas fa-users w-5 text-center transition-all group-hover:scale-110 shrink-0 {{ request()->routeIs('admin.empleados.*') ? 'text-[#3B82F6]' : 'text-[var(--text-muted)] group-hover:text-[var(--text-color)]' }}"></i>
                <span class="sidebar-text text-sm transition-opacity duration-200 {{ request()->routeIs('admin.empleados.*') ? 'text-[var(--text-color)] font-bold' : 'text-[var(--text-muted)] font-medium group-hover:text-[var(--text-color)]' }}">Empleados</span>
            </a>
            @endif
            
            {{-- ALIMENTOS (PRODUCTOS) --}}
            @if(auth()->user()->tienePermiso('productos.ver'))
            <a href="{{ route('admin.productos.index') }}" 
                class="relative flex items-center gap-4 px-4 py-3 mx-4 rounded-xl transition-all duration-300 group {{ request()->routeIs('admin.productos.*') ? 'bg-[#111114] border border-[var(--border-color)] border-l-[3px] border-l-[#3B82F6] shadow-lg modo-crema:bg-white scale-[1.02]' : 'border border-transparent hover:bg-white/5' }}">
{{-- Icono con color reforzado --}}
                <i class="fas fa-utensils w-5 text-center transition-all duration-300 group-hover:scale-110 shrink-0 {{ request()->routeIs('admin.productos.*') ? 'text-[#3B82F6] drop-shadow-[0_0_8px_rgba(59,130,246,0.5)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text-color)]' }}"></i>
    {{-- Texto con peso de fuente corregido --}}
                <span class="sidebar-text text-sm transition-opacity duration-200 {{ request()->routeIs('admin.productos.*') ? 'text-[var(--text-color)] font-bold' : 'text-[var(--text-muted)] font-medium group-hover:text-[var(--text-color)]' }}">
        Alimentos
    </span>
</a>    
@endif

            {{-- PROMOCIONES --}}
            @if(auth()->user()->tienePermiso('promociones.ver'))
            <a href="{{ route('admin.promociones.index') ?? '#' }}" class="relative flex items-center gap-4 px-4 py-3 mx-4 rounded-xl transition-all group border border-transparent">
                <i class="fas fa-tags w-5 text-center transition-all group-hover:scale-110 shrink-0 text-[var(--text-muted)] group-hover:text-[var(--text-color)]"></i>
                <span class="sidebar-text text-sm font-medium text-[var(--text-muted)] group-hover:text-[var(--text-color)] transition-opacity duration-200">Promociones</span>
            </a>
            @endif
        </nav>
    </div>

    {{-- Footer con datos del usuario real --}}
    <div class="p-6 border-t border-[var(--border-color)] flex flex-col gap-6">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-10 h-10 rounded-full bg-white text-black flex items-center justify-center font-black text-xs shrink-0 modo-crema:bg-black modo-crema:text-white">
                {{ substr(auth()->user()->nombre, 0, 2) }} {{-- Muestra las primeras 2 letras --}}
            </div>
            <div class="sidebar-text whitespace-nowrap transition-opacity duration-200">
                <p class="text-sm font-bold text-[var(--text-color)] leading-none mb-1">{{ auth()->user()->nombre }}</p>
                <p class="text-[8px] text-[var(--text-muted)] uppercase tracking-widest font-black">{{ auth()->user()->rol }}</p>
            </div>
        </div>

        {{-- Botón de Logout Seguro --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2.5 bg-[var(--input-bg)] border border-[var(--border-color)] hover:bg-black/20 text-[var(--text-muted)] hover:text-white py-3 rounded-xl transition-all duration-300 text-xs font-bold group modo-crema:bg-zinc-100 modo-crema:hover:bg-zinc-200">
                <i class="fas fa-sign-out-alt transition-transform group-hover:-translate-x-1 shrink-0"></i>
                <span class="sidebar-text transition-opacity duration-200">Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>