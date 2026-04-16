<aside id="sidebar" class="w-[260px] backdrop-blur-2xl flex flex-col justify-between z-30 border-r shadow-2xl shrink-0 bg-[var(--sidebar-bg)] border-[var(--border-color)] transition-all duration-300">
    <div>
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

        <nav class="px-5 py-8 space-y-4" id="nav-container">
            <p class="sidebar-text px-3 text-[8.5px] font-black text-[var(--text-muted)]/70 uppercase tracking-[0.25em] mb-5 transition-opacity duration-200">Principal</p>
            
            <a href="{{ route('admin.dashboard') ?? '#' }}" class="relative flex items-center gap-4 px-3 py-1 transition-all group">
                @if(request()->routeIs('admin.dashboard'))
                    <div class="absolute left-[-20px] top-[-4px] bottom-[-4px] w-[3px] bg-[#3B82F6] rounded-r-md shadow-[0_0_10px_rgba(59,130,246,0.8)]"></div>
                @endif
                <i class="fas fa-th-large w-5 text-center transition-all group-hover:scale-110 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-[#3B82F6] drop-shadow-[0_0_8px_rgba(59,130,246,0.6)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text-color)]' }}"></i>
                <span class="sidebar-text text-sm {{ request()->routeIs('admin.dashboard') ? 'text-[#3B82F6] font-bold' : 'text-[var(--text-muted)] font-medium group-hover:text-[var(--text-color)]' }} transition-opacity duration-200">Dashboard</span>
            </a>

            <a href="#" class="relative flex items-center gap-4 px-3 py-1 transition-all group">
                <i class="fas fa-cube w-5 text-center transition-all group-hover:scale-110 shrink-0 text-[var(--text-muted)] group-hover:text-[var(--text-color)]"></i>
                <span class="sidebar-text text-sm font-medium text-[var(--text-muted)] group-hover:text-[var(--text-color)] transition-opacity duration-200">Inventario</span>
            </a>
            
            <a href="{{ route('admin.empleados.index') ?? '#' }}" class="relative flex items-center gap-4 px-3 py-1 transition-all group">
                @if(request()->routeIs('admin.empleados.*'))
                    <div class="absolute left-[-20px] top-[-4px] bottom-[-4px] w-[3px] bg-[#3B82F6] rounded-r-md shadow-[0_0_10px_rgba(59,130,246,0.8)]"></div>
                @endif
                <i class="fas fa-users w-5 text-center transition-all group-hover:scale-110 shrink-0 {{ request()->routeIs('admin.empleados.*') ? 'text-[#3B82F6] drop-shadow-[0_0_8px_rgba(59,130,246,0.6)]' : 'text-[var(--text-muted)] group-hover:text-[var(--text-color)]' }}"></i>
                <span class="sidebar-text text-sm {{ request()->routeIs('admin.empleados.*') ? 'text-[#3B82F6] font-bold' : 'text-[var(--text-muted)] font-medium group-hover:text-[var(--text-color)]' }} transition-opacity duration-200">Empleados</span>
            </a>
            
            <a href="#" class="relative flex items-center gap-4 px-3 py-1 transition-all group">
                <i class="fas fa-utensils w-5 text-center transition-all group-hover:scale-110 shrink-0 text-[var(--text-muted)] group-hover:text-[var(--text-color)]"></i>
                <span class="sidebar-text text-sm font-medium text-[var(--text-muted)] group-hover:text-[var(--text-color)] transition-opacity duration-200">Alimentos</span>
            </a>
        </nav>
    </div>

    <div class="p-6 border-t border-[var(--border-color)]">
        <a href="/login" class="w-full flex items-center justify-center gap-2.5 text-[var(--text-muted)] hover:text-rose-500 py-3 transition-all duration-300 text-xs font-bold group">
            <i class="fas fa-sign-out-alt transition-transform group-hover:-translate-x-1 shrink-0"></i>
            <span class="sidebar-text transition-opacity duration-200">Cerrar Sesión</span>
        </a>
    </div>
</aside>