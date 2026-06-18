<style>
    /* Mejoras de contraste y profundidad para la Barra Superior */
    .premium-header {
        background-color: var(--bg-panel);
        border-bottom: 1px solid var(--border-color);
        z-index: 40;
        position: relative;
    }
    
    /* Ajustes específicos para el Modo Claro (Crema) */
    body.modo-crema .premium-header {
        background-color: #ffffff;
        border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        /* Aquí está la magia: Una sombra suave que separa la cabecera del resto */
        box-shadow: 0 4px 25px -10px rgba(0, 0, 0, 0.08); 
    }
    
    .btn-nav-glass {
        background-color: var(--bg-base);
        border: 1px solid var(--border-color);
    }
    
    /* Botones con relieve suave en modo claro */
    body.modo-crema .btn-nav-glass {
        background-color: #f8fafc; /* Gris titanio muy sutil */
        border: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }
    
    body.modo-crema .btn-nav-glass:hover {
        background-color: #f1f5f9;
        border-color: rgba(0, 0, 0, 0.12);
    }

    /* Botón de salir con contraste perfecto para no lastimar la vista */
    .btn-salir-premium {
        background-color: rgba(244, 63, 94, 0.08);
        border: 1px solid rgba(244, 63, 94, 0.2);
        color: #f43f5e;
    }
    
    body.modo-crema .btn-salir-premium {
        background-color: #fff1f2;
        border-color: #ffe4e6;
        color: #e11d48;
    }

    .btn-salir-premium:hover {
        background-color: #f43f5e !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 4px 15px rgba(244, 63, 94, 0.3);
    }
</style>

<header class="h-[80px] flex-none flex justify-between items-center px-8 premium-header transition-all duration-300">
    
    {{-- Branding Tipográfico Premium --}}
    <div class="flex items-center hover:opacity-80 transition-opacity cursor-pointer">
        <div class="flex flex-col justify-center">
            <h1 class="font-black tracking-[0.05em] text-2xl text-[var(--text-main)] leading-none flex items-center gap-1.5">
                OLLINTEM <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500 drop-shadow-sm">PRO</span>
            </h1>
            <span class="text-[9px] text-[var(--text-muted)] font-bold uppercase tracking-[0.4em] mt-1.5 opacity-80">Punto de Venta</span>
        </div>
    </div>

    {{-- Controles de Usuario --}}
    <div class="flex items-center gap-5">
        
        {{-- Botón de Tema (Con relieve) --}}
        <button onclick="toggleTheme()" class="w-10 h-10 rounded-[12px] btn-nav-glass text-[var(--text-muted)] hover:text-blue-500 flex items-center justify-center transition-all outline-none active:scale-95 group">
            <i id="dashThemeIcon" class="fas fa-moon text-[14px] group-hover:-rotate-12 transition-transform"></i>
        </button>

        {{-- Separador sutil --}}
        <div class="w-[1px] h-8 bg-[var(--border-color)] opacity-50 mx-1"></div>

        {{-- Info de Usuario --}}
        <div class="flex items-center gap-4">
            <div class="flex flex-col text-right">
                <span class="text-[13px] font-bold text-[var(--text-main)] tracking-tight leading-tight">{{ auth()->user()->nombre ?? 'Mesero' }}</span>
                <div class="flex items-center justify-end gap-1.5 mt-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                    <span class="text-[9px] text-[var(--text-muted)] font-bold uppercase tracking-widest">Activo</span>
                </div>
            </div>
            
            {{-- Avatar (Con relieve) --}}
            <div class="w-10 h-10 rounded-[12px] btn-nav-glass flex items-center justify-center text-[14px] font-black text-[var(--text-main)] uppercase">
                {{ substr(auth()->user()->nombre ?? 'M', 0, 1) }}
            </div>
        </div>

        {{-- Botón Salir (Contraste Mejorado) --}}
        <form method="POST" action="{{ route('logout') }}" class="ml-1">
            @csrf
            <button type="submit" class="btn-salir-premium flex items-center gap-2 px-5 py-2.5 rounded-[12px] transition-all duration-300 outline-none group active:scale-95">
                <i class="fas fa-power-off text-[11px] group-hover:scale-110 transition-transform"></i>
                <span class="text-[10px] font-bold uppercase tracking-widest">Salir</span>
            </button>
        </form>
        
    </div>
</header>