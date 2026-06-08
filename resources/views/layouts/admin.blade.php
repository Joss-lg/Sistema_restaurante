<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ollintem Pro')</title>

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
            --panel-card: rgba(15, 23, 42, 0.75);
            --panel-border: rgba(59, 130, 246, 0.20);
            --panel-secondary: rgba(148, 163, 184, 0.12);
        }

        body.modo-crema {
            --bg-color: #F5F5F7;
            --sidebar-bg: rgba(245, 243, 239, 0.92);
            --header-bg: rgba(250, 247, 243, 0.92);
            --card-color: #F7F4EF;
            --text-color: #111111;
            --text-muted: #4F5258;
            --border-color: rgba(79, 84, 94, 0.14);
            --glass-bg: rgba(255, 255, 255, 0.80);
            --glass-hover: rgba(255, 255, 255, 0.94);
            --input-bg: #ECE9E4;
            --panel-card: rgba(250, 247, 242, 0.94);
            --panel-border: rgba(59, 130, 246, 0.18);
            --panel-secondary: rgba(115, 123, 149, 0.12);
        }

        body { background-color: var(--bg-color); font-family: 'Inter', sans-serif; color: var(--text-color); overflow-x: hidden; margin: 0; padding: 0; transition: background-color 0.4s ease, color 0.4s ease; }
        
        /* Animaciones Premium */
        @keyframes shrink { from { width: 100%; } to { width: 0%; } }
        @keyframes toastPop { 
            0% { transform: translateX(100%) scale(0.8); opacity: 0; } 
            45% { transform: translateX(-10px) scale(1.02); opacity: 1; } 
            100% { transform: translateX(0) scale(1); opacity: 1; } 
        }
        @keyframes toastFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-3px); } }
        @keyframes progress { from { width: 100%; } to { width: 0%; } }

        .toast-card { transition: transform 0.25s ease, box-shadow 0.25s ease; }
        .toast-card:hover { transform: translateY(-3px); box-shadow: 0 40px 90px -45px rgba(15, 23, 42, 0.85); }

        .glass-card { backdrop-filter: blur(20px); border: 1px solid var(--border-color); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background-color: var(--glass-bg); }
        body:not(.modo-crema) .glass-card { background-color: var(--glass-bg); box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.03), 0 10px 30px -10px rgba(0, 0, 0, 0.5); }
        body.modo-crema .glass-card { background-color: rgba(255, 255, 255, 0.9); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.01), 0 2px 4px -1px rgba(0, 0, 0, 0.005); }
        
        .filter-button { padding: 0.65rem 1.1rem; border-radius: 0.85rem; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; border: 1px solid transparent; color: var(--text-color); background-color: var(--card-color); transition: all 0.25s ease; }
        .filter-button:hover { background-color: rgba(59, 130, 246, 0.18); border-color: rgba(59, 130, 246, 0.35); color: #ffffff; transform: translateY(-1px); }
        .filter-button--active { background: linear-gradient(135deg, rgba(59, 130, 246, 0.95), rgba(14, 165, 233, 0.85)); border-color: rgba(59, 130, 246, 0.85); color: #ffffff; box-shadow: 0 16px 35px -20px rgba(59, 130, 246, 0.75); }
        
        .header-action-btn { display: inline-flex; align-items: center; gap: 0.75rem; padding: 0.8rem 1.2rem; border-radius: 9999px; border: 1px solid rgba(255,255,255,0.12); background: rgba(59, 130, 246, 0.12); color: #EFF6FF; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease; }
        .header-action-btn:hover { transform: translateY(-2px); background: rgba(59, 130, 246, 0.22); box-shadow: 0 18px 40px -30px rgba(59, 130, 246, 0.8); }

        ::-webkit-scrollbar { width: 4px; } ::-webkit-scrollbar-track { background: transparent; } ::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }
    </style>
</head>
<body class="selection:bg-[#3B82F6]/30 selection:text-[var(--text-color)]">

    <script>
        if (localStorage.getItem('tema-ollintem') === 'crema') {
            document.body.classList.add('modo-crema');
        }
    </script>

    <div class="fixed top-[-20%] left-[-10%] w-[50vw] h-[50vw] rounded-full bg-blue-600/5 blur-[150px] pointer-events-none z-0 modo-crema:hidden"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[40vw] h-[40vw] rounded-full bg-orange-600/5 blur-[150px] pointer-events-none z-0 modo-crema:hidden"></div>

    @hasSection('no-sidebar')
        <main class="flex-1 relative z-10 flex flex-col min-h-screen">
            @yield('content')
        </main>
    @else
        <div class="flex h-screen overflow-hidden relative z-10">
            @include('layouts.sidebar')

            <main class="flex-1 overflow-y-auto relative z-10 flex flex-col">
                <header class="backdrop-blur-2xl border-b sticky top-0 z-30 px-10 py-5 flex justify-between items-center bg-[var(--header-bg)] border-[var(--border-color)]">
                    <div class="flex flex-col">
                        <h1 class="text-[22px] font-black text-[var(--text-color)] tracking-tight">@yield('header-title', 'Gestión de Personal')</h1>
                        <p class="text-[11px] text-[var(--text-muted)] font-medium mt-1 opacity-80">@yield('header-subtitle', 'Administra tu sistema')</p>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        @hasSection('header-actions')
                            <div class="hidden md:flex items-center gap-3">
                                @yield('header-actions')
                            </div>
                        @endif
                        <div class="w-10 h-10 rounded-xl border border-[#3B82F6]/30 shadow-[0_0_15px_rgba(59,130,246,0.15)] shrink-0 overflow-hidden">
                            <img src="{{ asset('images/logo.png') }}" alt="Perfil" class="w-full h-full object-cover">
                        </div>
                        <button onclick="toggleTheme()" class="w-9 h-9 rounded-full bg-[var(--sidebar-bg)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-color)] transition-all shadow-inner">
                            <i id="dashThemeIcon" class="fas fa-sun text-sm"></i>
                        </button>
                    </div>
                </header>

                @yield('content')
            </main>
        </div>
    @endif

    @yield('modals')

    {{-- LÓGICA DE TEMA E ICONOS --}}
    <script>
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
    </script>

    {{-- NOTIFICACIONES "ULTRA PREMIUM" --}}
    @if(session('success'))
        <div id="toast-success" class="fixed top-6 right-6 z-[9999] w-full max-w-[17rem]">
            <div role="status" aria-live="polite" class="toast-card overflow-hidden rounded-[24px] border border-white/10 bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.14),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(34,197,94,0.1),_transparent_30%),#020617] shadow-[0_24px_60px_-30px_rgba(0,0,0,0.75)] backdrop-blur-3xl animate-[toastPop_0.55s_cubic-bezier(0.16,1,0.3,1),toastFloat_3.8s_ease-in-out_1] relative ring-1 ring-inset ring-white/5">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 via-cyan-400 to-sky-500"></div>
                <div class="relative flex items-start gap-2 p-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-3xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 shadow-[0_0_18px_rgba(52,211,153,0.2)]">
                        <i class="fas fa-check text-sm"></i>
                    </div>
                    <div class="flex-1 pt-0.5">
                        <p class="text-[9px] font-black uppercase tracking-[0.45em] text-emerald-200/80">Operación Exitosa</p>
                        <p class="mt-1 text-[13px] font-semibold text-[var(--text-color)] leading-5">{{ session('success') }}</p>
                    </div>
                    <button onclick="document.getElementById('toast-success')?.remove()" class="text-[var(--text-muted)] hover:text-white transition-colors rounded-full p-1 focus:outline-none focus:ring-2 focus:ring-emerald-400/50">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </div>
                <div class="absolute bottom-0 left-0 h-1 w-full bg-white/10">
                    <div class="h-full bg-gradient-to-r from-emerald-400 via-cyan-400 to-sky-400 animate-[progress_3.8s_linear_forwards]"></div>
                </div>
            </div>
        </div>
        <script>
            setTimeout(() => { document.getElementById('toast-success')?.remove(); }, 3800);
        </script>
    @endif

    @stack('scripts')
</body>
</html>