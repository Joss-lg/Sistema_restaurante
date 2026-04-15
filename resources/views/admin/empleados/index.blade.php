<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ollintem Pro - Gestión de Empleados</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        'luxury-bg': 'var(--bg-color)',      
                        'luxury-card': 'var(--card-color)',    
                        'luxury-border': 'var(--border-color)',  
                        'luxury-accent': '#3B82F6',  
                        'luxury-text': 'var(--text-color)',    
                        'luxury-muted': 'var(--text-muted)',   
                    }
                }
            }
        }
    </script>

    <style>

        :root {
            --bg-color: #030303; 
            --sidebar-bg: rgba(6, 6, 8, 0.8);
            --header-bg: rgba(3, 3, 3, 0.7);
            --card-color: #0e0e11;
            --text-color: #FAFAFA;
            --text-muted: #81818a; 
            --border-color: rgba(255, 255, 255, 0.04);
            
            --glass-bg: linear-gradient(145deg, rgba(20, 20, 23, 0.8) 0%, rgba(10, 10, 12, 0.6) 100%);
            --glass-hover: linear-gradient(145deg, rgba(25, 25, 29, 0.9) 0%, rgba(12, 12, 14, 0.8) 100%);
            
            --input-bg: #111114; 
        }

        body.modo-crema {
            --bg-color: #FAF9F6; 
            --sidebar-bg: rgba(255, 255, 255, 0.9);
            --header-bg: rgba(250, 249, 246, 0.8);
            --card-color: #ffffff; 
            --text-color: #0F1012; 
            --text-muted: #6B7280; 
            --border-color: rgba(0, 0, 0, 0.06);

            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-hover: #ffffff;
            
            --input-bg: #F4F4F6; 
        }

 
        body { 
            background-color: var(--bg-color); 
            font-family: 'Inter', sans-serif; 
            -webkit-font-smoothing: antialiased; 
            color: var(--text-color); 
            overflow-x: hidden; 
            margin: 0; 
            padding: 0; 
            transition: background-color 0.4s ease, color 0.4s ease; 
        }
        
        .glass-card { 
            backdrop-filter: blur(20px); 
            border: 1px solid var(--border-color); 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        
        body:not(.modo-crema) .glass-card { 
            background: var(--glass-bg);
            box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.05), 0 10px 30px -10px rgba(0, 0, 0, 0.7); 
        }
        
        body.modo-crema .glass-card {
            background: var(--glass-bg);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        }

        .glass-card:hover { transform: translateY(-4px); border-color: var(--border-color); }
        
        body.modo-crema .glass-card:hover { 
            background: var(--glass-hover);
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.05); 
        }
        
        .text-metallic {
            background: linear-gradient(180deg, var(--text-color) 0%, var(--text-muted) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        #sidebar { transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1); background-color: var(--sidebar-bg); border-color: var(--border-color); }
        header { background-color: var(--header-bg); border-color: var(--border-color); }
        .sidebar-text { transition: opacity 0.2s ease; white-space: nowrap; }
    </style>
</head>
<body class="selection:bg-luxury-accent/30 selection:text-luxury-text">

    <script>
        if (localStorage.getItem('tema-ollintem') === 'crema') {
            document.body.classList.add('modo-crema');
        }
    </script>

    <div class="fixed top-[-20%] left-[-10%] w-[50vw] h-[50vw] rounded-full bg-blue-600/5 blur-[150px] pointer-events-none z-0 modo-crema:hidden"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[40vw] h-[40vw] rounded-full bg-orange-600/5 blur-[150px] pointer-events-none z-0 modo-crema:hidden"></div>

    <div class="flex h-screen overflow-hidden relative z-10">

        <aside id="sidebar" class="w-[260px] backdrop-blur-2xl flex flex-col justify-between z-30 border-r shadow-2xl shrink-0">
            <div>
                <div class="h-24 flex items-center justify-between px-6 border-b border-luxury-border">
                    <div class="flex items-center gap-3.5 overflow-hidden">
                        <div id="logo-icon" class="w-9 h-9 bg-gradient-to-br from-luxury-accent/30 to-transparent border border-luxury-accent/40 rounded-xl flex items-center justify-center shadow-[0_0_20px_rgba(59,130,246,0.2)] shrink-0">
                            <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="w-full h-full object-contain rounded-lg p-0.5">
                        </div>
                        <div class="flex flex-col sidebar-text">
                            <span class="font-black tracking-widest text-[15px] text-luxury-text leading-none">OLLINTEM <span class="font-normal text-luxury-accent">PRO</span></span>
                            <span class="text-[7.5px] text-luxury-muted uppercase tracking-[0.25em] mt-1.5 opacity-80">Management System</span>
                        </div>
                    </div>
                    <button id="toggleSidebar" class="w-8 h-8 rounded-lg bg-black/5 modo-crema:bg-black/5 flex items-center justify-center text-luxury-muted hover:text-luxury-text transition-all shrink-0">
                        <i class="fas fa-bars text-xs"></i>
                    </button>
                </div>

                <nav class="px-5 py-8 space-y-1.5" id="nav-container">
                    <p class="sidebar-text px-3 text-[8.5px] font-black text-luxury-muted/70 uppercase tracking-[0.25em] mb-5">Principal</p>
                    
                    <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center gap-4 text-luxury-muted hover:text-luxury-text px-3 py-3 rounded-xl font-medium transition-all group border border-transparent hover:bg-black/5 modo-crema:hover:bg-zinc-100/70">
                        <i class="fas fa-th-large w-5 text-center transition-all group-hover:scale-110 shrink-0"></i>
                            <span class="sidebar-text">Dashboard</span>
                    </a>
                    
                    <a href="#" class="nav-link flex items-center gap-4 text-luxury-muted hover:text-luxury-text px-3 py-3 rounded-xl font-medium transition-all group border border-transparent hover:bg-black/5 modo-crema:hover:bg-zinc-100/70">
                        <i class="fas fa-cube w-5 text-center transition-all group-hover:scale-110 shrink-0"></i>
                        <span class="sidebar-text">Inventario</span>
                    </a>
                    
                    <a href="#" class="nav-link relative flex items-center gap-4 bg-gradient-to-r from-luxury-accent/10 to-transparent text-luxury-text px-3 py-3 rounded-xl font-semibold transition-all border border-luxury-border border-l-0 group">
                        <div class="sidebar-text absolute left-0 top-0 bottom-0 w-[3px] bg-luxury-accent rounded-r-md shadow-[0_0_12px_rgba(59,130,246,0.9)]"></div>
                        <i class="fas fa-users w-5 text-center text-luxury-accent drop-shadow-[0_0_8px_rgba(59,130,246,0.6)] group-hover:scale-110 transition-transform shrink-0"></i>
                        <span class="sidebar-text">Empleados</span>
                    </a>
                    
                    <a href="#" class="nav-link flex items-center gap-4 text-luxury-muted hover:text-luxury-text px-3 py-3 rounded-xl font-medium transition-all group border border-transparent hover:bg-black/5 modo-crema:hover:bg-zinc-100/70">
                        <i class="fas fa-utensils w-5 text-center transition-all group-hover:scale-110 shrink-0"></i>
                        <span class="sidebar-text">Alimentos</span>
                    </a>
                    
                    <a href="#" class="nav-link flex items-center gap-4 text-luxury-muted hover:text-luxury-text px-3 py-3 rounded-xl font-medium transition-all group border border-transparent hover:bg-black/5 modo-crema:hover:bg-zinc-100/70">
                        <i class="fas fa-tag w-5 text-center transition-all group-hover:scale-110 shrink-0"></i>
                        <span class="sidebar-text">Promociones</span>
                    </a>
                </nav>
            </div>

            <div class="p-6 border-t border-luxury-border">
                <form action="#" method="POST">
                    @csrf
                    <button type="submit" class="nav-link w-full flex items-center justify-center gap-2.5 bg-rose-500/5 hover:bg-rose-500/10 border border-rose-500/10 text-rose-500/80 hover:text-rose-500 py-3 rounded-xl transition-all duration-300 text-xs font-bold group shadow-sm">
                        <i class="fas fa-sign-out-alt transition-transform group-hover:-translate-x-1 shrink-0"></i>
                        <span class="sidebar-text">Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto relative z-10 flex flex-col">
            
            <header class="backdrop-blur-2xl border-b sticky top-0 z-30 px-10 py-5 flex justify-between items-center modo-crema:shadow-sm">
                <div class="flex flex-col">
                    <h1 class="text-[22px] font-black text-luxury-text tracking-tight">Gestión de Personal</h1>
                    <p class="text-[11px] text-luxury-muted font-medium mt-1 opacity-80">Administra roles y permisos del equipo</p>
                </div>
                
                <div class="flex items-center gap-6">
                    <button onclick="toggleTheme()" class="w-9 h-9 rounded-full bg-luxury-border flex items-center justify-center text-luxury-muted hover:text-luxury-text transition-all shadow-inner modo-crema:shadow">
                        <i id="dashThemeIcon" class="fas fa-sun text-sm"></i>
                    </button>
                </div>
            </header>

            <div class="p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col">
                
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
                    <div>
                        <h1 class="text-3xl font-black text-luxury-text tracking-tight">Empleados</h1>
                    </div>
                    <button onclick="openModal()" class="relative flex items-center gap-2.5 bg-luxury-accent border border-luxury-accent/50 text-white hover:bg-luxury-accent/90 px-7 py-3 rounded-xl text-xs font-bold transition-all duration-300 shadow-[0_8px_20px_-6px_rgba(59,130,246,0.5)] hover:shadow-[0_12px_25px_-4px_rgba(59,130,246,0.6)] hover:-translate-y-0.5 group">
                        <i class="fas fa-plus"></i> 
                        <span>Agregar Empleado</span>
                    </button>
                </div>

                @php
                    $totalAdmin = 0;
                    $totalCapitan = 0;
                    $totalMesero = 0;
                    $totalCocinero = 0;
                    $totalCajero = 0;

                    if(isset($empleados)) {
                        foreach($empleados as $emp) {
                            $rol = strtolower($emp->rol);
                            if($rol == 'admin' || $rol == 'administrador') $totalAdmin++;
                            elseif($rol == 'capitan' || $rol == 'capitán') $totalCapitan++;
                            elseif($rol == 'mesero') $totalMesero++;
                            elseif($rol == 'cocinero' || $rol == 'cocina') $totalCocinero++;
                            elseif($rol == 'cajero') $totalCajero++;
                        }
                    }
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-6">
                    <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-rose-500/50">
                        <div class="flex justify-between items-start">
                            <h3 class="text-[11px] font-bold text-luxury-muted uppercase tracking-[0.1em]">Administradores</h3>
                            <div class="w-10 h-10 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500 group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-user-shield text-lg"></i>
                            </div>
                        </div>
                        <p class="text-4xl font-black text-luxury-text tracking-tighter">{{ $totalAdmin }}</p>
                    </div>
                    
                    <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-blue-400/50">
                        <div class="flex justify-between items-start">
                            <h3 class="text-[11px] font-bold text-luxury-muted uppercase tracking-[0.1em]">Capitanes</h3>
                            <div class="w-10 h-10 rounded-2xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-clipboard-list text-lg"></i>
                            </div>
                        </div>
                        <p class="text-4xl font-black text-luxury-text tracking-tighter">{{ $totalCapitan }}</p>
                    </div>
                    
                    <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-emerald-400/50">
                        <div class="flex justify-between items-start">
                            <h3 class="text-[11px] font-bold text-luxury-muted uppercase tracking-[0.1em]">Meseros</h3>
                            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-concierge-bell text-lg"></i>
                            </div>
                        </div>
                        <p class="text-4xl font-black text-luxury-text tracking-tighter">{{ $totalMesero }}</p>
                    </div>
                    
                    <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-orange-400/50">
                        <div class="flex justify-between items-start">
                            <h3 class="text-[11px] font-bold text-luxury-muted uppercase tracking-[0.1em]">Cocineros</h3>
                            <div class="w-10 h-10 rounded-2xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-orange-500 group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-fire-burner text-lg"></i>
                            </div>
                        </div>
                        <p class="text-4xl font-black text-luxury-text tracking-tighter">{{ $totalCocinero }}</p>
                    </div>

                    <div class="glass-card rounded-[1.5rem] p-6 flex flex-col justify-between h-32 group relative hover:border-purple-400/50">
                         <div class="flex justify-between items-start">
                             <h3 class="text-[11px] font-bold text-luxury-muted uppercase tracking-[0.1em]">Cajeros</h3>
                             <div class="w-10 h-10 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-500 group-hover:scale-110 transition-transform duration-300">
                                 <i class="fas fa-cash-register text-lg"></i>
                             </div>
                         </div>
                        <p class="text-4xl font-black text-luxury-text tracking-tighter">{{ $totalCajero }}</p>
                    </div>
                </div>

                <div class="glass-card rounded-[2rem] p-8 lg:p-10 w-full flex-1 flex flex-col modo-crema:shadow-2xl">
                    <div class="mb-8 flex justify-between items-center">
                        <div class="flex flex-col">
                            <h2 class="text-xl font-bold text-luxury-text tracking-tight">Lista de Empleados</h2>
                            <p class="text-xs text-luxury-muted mt-1 font-medium">{{ count($empleados ?? []) }} registrados en el sistema</p>
                        </div>
                        <div class="relative w-64">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-luxury-muted text-xs"></i>
                            <input type="text" placeholder="Buscar empleado..." class="w-full h-10 bg-[var(--input-bg)] border border-luxury-border rounded-lg pl-10 pr-4 text-xs font-medium text-luxury-text focus:ring-1 focus:ring-luxury-accent outline-none transition-all shadow-inner">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-luxury-border">
                                    <th class="pb-5 px-4 text-[10px] font-black text-metallic opacity-80 uppercase tracking-widest">Nombre</th>
                                    <th class="pb-5 px-4 text-[10px] font-black text-metallic opacity-80 uppercase tracking-widest">PIN</th>
                                    <th class="pb-5 px-4 text-[10px] font-black text-metallic opacity-80 uppercase tracking-widest">Rol</th>
                                    <th class="pb-5 px-4 text-[10px] font-black text-metallic opacity-80 uppercase tracking-widest">Permisos Asignados</th>
                                    <th class="pb-5 px-4 text-[10px] font-black text-metallic opacity-80 uppercase tracking-widest text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($empleados ?? [] as $empleado)
                                <tr class="group hover:bg-black/[0.03] modo-crema:hover:bg-zinc-100/50 transition-colors border-b border-luxury-border/50">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-9 h-9 rounded-xl bg-luxury-accent/10 border border-luxury-accent/20 flex items-center justify-center text-luxury-accent font-black text-xs shrink-0">
                                                {{ substr($empleado->nombre, 0, 1) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-sm text-luxury-text">{{ $empleado->nombre }}</span>
                                                <span class="text-[10px] text-luxury-muted font-medium mt-0.5">ID: EMP-00{{ $empleado->id }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="py-4 px-4 tabular-nums">
                                        <span class="text-xs font-medium text-luxury-muted">{{ $empleado->codigo_empleado }}</span>
                                    </td>
                                    
                                    <td class="py-4 px-4">
                                        @php
                                            $rolDB = strtolower($empleado->rol);
                                            $roleClass = '';
                                            $roleName = ucfirst($empleado->rol);

                                            if($rolDB == 'admin' || $rolDB == 'administrador') {
                                                $roleClass = 'bg-[#1E1214] border-rose-500/20 text-rose-400 modo-crema:bg-rose-50 modo-crema:border-rose-200 modo-crema:text-rose-600';
                                                $roleName = 'Administrador';
                                            } elseif($rolDB == 'capitan' || $rolDB == 'capitán') {
                                                $roleClass = 'bg-[#11141A] border-blue-500/20 text-blue-400 modo-crema:bg-blue-50 modo-crema:border-blue-200 modo-crema:text-blue-600';
                                                $roleName = 'Capitán';
                                            } elseif($rolDB == 'mesero') {
                                                $roleClass = 'bg-[#111613] border-emerald-500/20 text-emerald-400 modo-crema:bg-zinc-100 modo-crema:border-zinc-200 modo-crema:text-zinc-600';
                                                $roleName = 'Mesero';
                                            } elseif($rolDB == 'cocinero' || $rolDB == 'cocina') {
                                                $roleClass = 'bg-[#1A1411] border-orange-500/20 text-orange-400 modo-crema:bg-orange-50 modo-crema:border-orange-200 modo-crema:text-orange-600';
                                                $roleName = 'Cocinero';
                                            } else {
                                                $roleClass = 'bg-[#15121A] border-purple-500/20 text-purple-400 modo-crema:bg-purple-50 modo-crema:border-purple-200 modo-crema:text-purple-600';
                                                if($rolDB == 'cajero') $roleName = 'Cajero';
                                            }
                                        @endphp
                                        
                                        <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $roleClass }}">
                                            {{ $roleName }}
                                        </span>
                                    </td>
                                    
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-x-4 gap-y-2.5 max-w-sm">
                                            @foreach($permisos ?? [] as $permiso)
                                                <div class="flex items-center gap-2">
                                                    <input type="checkbox" 
                                                           name="permisos[{{ $empleado->id }}][]"
                                                           value="{{ $permiso->id }}"
                                                           id="p-{{ $empleado->id }}-{{ $permiso->id }}"
                                                           class="w-3.5 h-3.5 rounded-sm border-luxury-border modo-crema:border-zinc-300 bg-transparent text-luxury-accent cursor-pointer" 
                                                           style="accent-color: #3B82F6;"
                                                           {{ $empleado->tienePermiso($permiso->slug) ? 'checked' : '' }}>
                                                    <label for="p-{{ $empleado->id }}-{{ $permiso->id }}" class="text-[10px] font-medium text-luxury-muted cursor-pointer select-none hover:text-luxury-text transition-colors">
                                                        {{ $permiso->nombre }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>

                                    <td class="py-4 px-4 text-right">
                                        <div class="flex items-center justify-end gap-2.5">
                                            <a href="#" 
                                               class="w-8 h-8 rounded-full bg-black/5 hover:bg-black/10 modo-crema:bg-zinc-100 modo-crema:hover:bg-zinc-200 flex items-center justify-center text-luxury-muted hover:text-luxury-accent transition-all outline-none">
                                                <i class="fas fa-edit text-xs"></i>
                                            </a>
                                            
                                            <form id="delete-form-{{ $empleado->id }}" action="{{ route('admin.empleados.destroy', $empleado->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                    onclick="confirmarEliminacion('{{ $empleado->id }}', '{{ $empleado->nombre }}')" 
                                                    class="w-8 h-8 rounded-full bg-black/5 hover:bg-black/10 modo-crema:bg-zinc-100 modo-crema:hover:bg-zinc-200 flex items-center justify-center text-luxury-muted hover:text-rose-500 transition-all outline-none">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-16 text-center">
                                        <div class="flex flex-col items-center justify-center opacity-50">
                                            <i class="fas fa-users-slash text-4xl text-luxury-muted mb-5"></i>
                                            <p class="text-sm font-medium text-luxury-muted mb-1">No hay empleados registrados</p>
                                            <p class="text-xs text-luxury-muted">Comienza agregando un nuevo miembro al equipo</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-10 pt-6 border-t border-luxury-border flex justify-end">
                        <button class="flex items-center gap-2.5 bg-gradient-to-r from-[#3B82F6] to-[#2563EB] hover:from-[#2563EB] hover:to-[#1D4ED8] text-white px-10 py-3.5 rounded-xl text-xs font-bold transition-all duration-300 shadow-[0_10px_25px_-5px_rgba(59,130,246,0.5)] hover:shadow-[0_15px_30px_rgba(59,130,246,0.6)] hover:-translate-y-1">
                            <i class="fas fa-save text-sm"></i> 
                            <span>Guardar Cambios</span>
                        </button>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <div id="employeeModal" class="fixed inset-0 bg-black/80 modo-crema:bg-zinc-900/70 backdrop-blur-md z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300">
        
        <div class="relative bg-[var(--card-color)] border border-luxury-border rounded-[2.5rem] p-10 w-full max-w-[460px] shadow-[0_30px_70px_-10px_rgba(0,0,0,0.8)] modo-crema:shadow-[0_20px_40px_-5px_rgba(0,0,0,0.08)] transform scale-95 transition-all duration-300 overflow-visible" id="modalContent">
            
            <div class="absolute top-[-10%] left-[-10%] w-52 h-52 rounded-full bg-blue-600/10 modo-crema:bg-blue-600/5 blur-[90px] pointer-events-none z-0"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-48 h-48 rounded-full bg-orange-600/10 modo-crema:bg-orange-600/5 blur-[90px] pointer-events-none z-0"></div>

            <div class="flex justify-between items-start mb-10 z-10 relative">
                <div class="flex flex-col">
                    <h2 class="text-2xl font-black text-luxury-text tracking-tight">Nuevo Empleado</h2>
                    <p class="text-xs text-luxury-muted font-medium mt-1">Agrega un nuevo empleado al sistema</p>
                </div>
                <button onclick="closeModal()" class="w-9 h-9 rounded-full bg-luxury-border flex items-center justify-center text-luxury-muted hover:text-luxury-text transition-colors outline-none shadow-inner modo-crema:bg-zinc-100 modo-crema:shadow">
                    <i class="fas fa-xmark text-sm"></i>
                </button>
            </div>

            <form action="#" method="POST" class="space-y-7 z-10 relative">
                @csrf
                
                <div>
                    <label for="nombre_completo" class="text-[10px] font-black text-metallic opacity-80 uppercase tracking-widest mb-3 block">Nombre Completo</label>
                    <input type="text" id="nombre_completo" name="nombre" placeholder="Ej: Juan Pérez" required class="w-full h-12 bg-[var(--input-bg)] border border-luxury-border rounded-xl px-5 text-sm font-medium text-luxury-text placeholder:text-luxury-muted focus:border-luxury-accent focus:ring-1 focus:ring-luxury-accent outline-none transition-all shadow-inner">
                </div>

                <div>
                    <label for="pin" class="text-[10px] font-black text-metallic opacity-80 uppercase tracking-widest mb-3 block">PIN de Acceso</label>
                    <input type="text" id="pin" name="codigo_empleado" placeholder="Escribe el código..." required class="w-full h-12 bg-[var(--input-bg)] border border-luxury-border rounded-xl px-5 text-sm font-semibold tracking-widest tabular-nums text-luxury-text placeholder:text-luxury-muted focus:border-luxury-accent focus:ring-1 focus:ring-luxury-accent outline-none transition-all shadow-inner">
                </div>

                <div class="relative group" id="dropdownContainer">
                    <label class="text-[10px] font-black text-metallic opacity-80 uppercase tracking-widest mb-3 block">Rol</label>
                    
                    <input type="hidden" name="rol" id="rol_input" value="mesero">

                    <button type="button" onclick="toggleDropdown()" id="dropdownBtn" class="flex items-center justify-between w-full h-12 bg-[var(--input-bg)] border border-luxury-border rounded-xl px-5 text-sm font-bold text-luxury-text focus:border-luxury-accent focus:ring-1 focus:ring-luxury-accent transition-all outline-none shadow-inner">
                        <span id="dropdownSelected">Mesero</span>
                        <i class="fas fa-chevron-down text-luxury-muted transition-transform" id="dropdownIcon"></i>
                    </button>
                    
                    <div id="dropdownMenu" class="absolute top-full left-0 mt-2 w-full max-h-48 overflow-y-auto bg-[var(--card-color)] border border-luxury-border rounded-xl shadow-2xl z-50 py-2.5 opacity-0 pointer-events-none transform -translate-y-2 transition-all duration-200">
                        @php
                            $rolesList = ['Administrador' => 'admin', 'Capitán' => 'capitan', 'Mesero' => 'mesero', 'Cocinero' => 'cocina', 'Cajero' => 'cajero'];
                        @endphp
                        @foreach($rolesList as $name => $tech)
                        <button type="button" onclick="selectRole('{{ $name }}', '{{ $tech }}')" class="role-option flex items-center justify-between w-full text-left px-5 py-3 text-sm font-medium text-luxury-muted hover:text-luxury-text hover:bg-black/5 modo-crema:hover:bg-zinc-100 transition-all">
                            <span>{{ $name }}</span>
                            <i class="fas fa-check text-luxury-accent {{ $name == 'Mesero' ? 'opacity-100' : 'opacity-0' }} ml-3"></i>
                        </button>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-10 pt-4 border-t border-luxury-border">
                    <button type="button" onclick="closeModal()" class="px-7 py-3 rounded-xl text-xs font-bold text-luxury-muted hover:text-luxury-text bg-luxury-border hover:bg-black/10 transition-all outline-none shadow-inner modo-crema:bg-zinc-100 modo-crema:hover:bg-zinc-200">
                        Cancelar
                    </button>
                    <button type="submit" class="px-8 py-3 rounded-xl text-xs font-bold bg-luxury-accent text-white hover:bg-luxury-accent/90 shadow-[0_8px_20px_-6px_rgba(59,130,246,0.5)] hover:-translate-y-0.5 transition-all outline-none">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 bg-black/80 modo-crema:bg-zinc-900/70 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden opacity-100 pointer-events-auto transition-all duration-300">
        <div class="relative bg-[var(--card-color)] border border-luxury-border rounded-[2.5rem] p-10 w-full max-w-[400px] shadow-[0_30px_70px_-10px_rgba(0,0,0,0.8)] modo-crema:shadow-[0_20px_40px_-5px_rgba(0,0,0,0.08)] transform transition-all duration-300">
            
            <div class="absolute top-[-10%] left-[-10%] w-48 h-48 rounded-full bg-rose-600/10 modo-crema:bg-rose-600/5 blur-[80px] pointer-events-none z-0"></div>

            <div class="flex flex-col items-center text-center relative z-10">
                <div class="w-16 h-16 rounded-full bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500 mb-6 shadow-inner">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                
                <h2 class="text-xl font-black text-luxury-text tracking-tight mb-2">¿Eliminar Empleado?</h2>
                <p class="text-xs text-luxury-muted font-medium mb-6 leading-relaxed">
                    Estás a punto de eliminar a <br>
                    <span id="nombreEmpleadoEliminar" class="font-bold text-luxury-text text-sm block mt-1"></span>
                </p>
                <p class="text-[10px] text-rose-500/80 font-bold uppercase tracking-widest bg-rose-500/5 px-4 py-2 rounded-lg mb-8 border border-rose-500/10">Esta acción no se puede deshacer</p>

                <div class="flex w-full gap-3">
                    <button type="button" onclick="cerrarModalEliminar()" class="flex-1 py-3.5 rounded-xl text-xs font-bold text-luxury-muted hover:text-luxury-text bg-luxury-border hover:bg-black/10 transition-all outline-none shadow-inner modo-crema:bg-zinc-100 modo-crema:hover:bg-zinc-200">
                        Cancelar
                    </button>
                    <button type="button" id="btnConfirmarEliminar" class="flex-1 py-3.5 rounded-xl text-xs font-bold bg-rose-500 text-white hover:bg-rose-600 shadow-[0_8px_20px_-6px_rgba(244,63,94,0.5)] hover:-translate-y-0.5 transition-all outline-none">
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sidebar Toggle
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const texts = document.querySelectorAll('.sidebar-text');
            sidebar.classList.toggle('w-[260px]');
            sidebar.classList.toggle('w-[88px]');
            texts.forEach(el => { el.classList.toggle('hidden'); el.classList.toggle('opacity-0'); });
        });

        // Theme Logic
        function toggleTheme() {
            const body = document.body;
            body.classList.toggle('modo-crema');
            const esCrema = body.classList.contains('modo-crema');
            localStorage.setItem('tema-ollintem', esCrema ? 'crema' : 'negro');
            actualizarIcono(esCrema);
        }

        function actualizarIcono(esCrema) {
            const icon = document.getElementById('dashThemeIcon');
            if (icon) {
                if (esCrema) {
                    icon.classList.replace('fa-sun', 'fa-moon');
                    icon.classList.add('text-blue-500');
                    icon.classList.remove('text-yellow-400');
                } else {
                    icon.classList.replace('fa-moon', 'fa-sun');
                    icon.classList.add('text-yellow-400');
                    icon.classList.remove('text-blue-500');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const esCrema = document.body.classList.contains('modo-crema');
            actualizarIcono(esCrema);
        });

        // Modal Logic
        const modal = document.getElementById('employeeModal');
        const modalContent = document.getElementById('modalContent');
        const dropdownMenu = document.getElementById('dropdownMenu');
        const dropdownIcon = document.getElementById('dropdownIcon');
        const dropdownBtn = document.getElementById('dropdownBtn');

        function openModal() {
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalContent.classList.replace('scale-95', 'scale-100');
        }

        function closeModal() {
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalContent.classList.replace('scale-100', 'scale-95');
            if (!dropdownMenu.classList.contains('opacity-0')) toggleDropdown();
        }

        // Dropdown Logic
        function toggleDropdown() {
            const isOpen = !dropdownMenu.classList.contains('opacity-0');
            if (isOpen) {
                dropdownMenu.classList.add('opacity-0', 'pointer-events-none', '-translate-y-2');
                dropdownIcon.classList.remove('rotate-180', 'text-luxury-accent');
                dropdownBtn.classList.remove('border-luxury-accent', 'ring-1', 'ring-luxury-accent');
            } else {
                dropdownMenu.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-2');
                dropdownIcon.classList.add('rotate-180', 'text-luxury-accent');
                dropdownBtn.classList.add('border-luxury-accent', 'ring-1', 'ring-luxury-accent');
            }
        }

        function selectRole(name, techValue) {
            document.getElementById('dropdownSelected').innerText = name;
            document.getElementById('rol_input').value = techValue;
            
            const options = document.querySelectorAll('.role-option');
            options.forEach(opt => {
                const spanText = opt.querySelector('span').innerText;
                const check = opt.querySelector('.fa-check');
                if (spanText === name) {
                    opt.classList.replace('font-medium', 'font-bold');
                    opt.classList.replace('text-luxury-muted', 'text-luxury-text');
                    check.classList.replace('opacity-0', 'opacity-100');
                } else {
                    opt.classList.replace('font-bold', 'font-medium');
                    opt.classList.replace('text-luxury-text', 'text-luxury-muted');
                    check.classList.replace('opacity-100', 'opacity-0');
                }
            });
            toggleDropdown();
        }

        document.addEventListener('click', (e) => {
            const container = document.getElementById('dropdownContainer');
            if (container && !container.contains(e.target) && !dropdownMenu.classList.contains('opacity-0')) toggleDropdown();
        });

        let formIdParaEliminar = null;

        function confirmarEliminacion(id, nombre) {
            formIdParaEliminar = 'delete-form-' + id;
            
            const txtNombre = document.getElementById('nombreEmpleadoEliminar');
            if(txtNombre) txtNombre.innerText = nombre;
            
            const delModal = document.getElementById('deleteModal');
            if(delModal) delModal.classList.remove('hidden');
        }

        function cerrarModalEliminar() {
            const delModal = document.getElementById('deleteModal');
            if(delModal) delModal.classList.add('hidden');
            formIdParaEliminar = null;
        }

        const btnConfirmar = document.getElementById('btnConfirmarEliminar');
        if(btnConfirmar) {
            btnConfirmar.addEventListener('click', function() {
                if (formIdParaEliminar) {
                    document.getElementById(formIdParaEliminar).submit();
                }
            });
        }

        function openEditModal(empleado) {
            console.log("Editando a:", empleado.nombre);
        }
    </script>
</body>
</html>