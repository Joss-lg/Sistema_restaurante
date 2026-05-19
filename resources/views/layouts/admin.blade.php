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
            --panel-card: rgba(255, 255, 255, 0.90);
            --panel-border: rgba(59, 130, 246, 0.16);
            --panel-secondary: rgba(148, 163, 184, 0.08);
        }

        body { background-color: var(--bg-color); font-family: 'Inter', sans-serif; color: var(--text-color); overflow-x: hidden; margin: 0; padding: 0; transition: background-color 0.4s ease, color 0.4s ease; }
        
        .glass-card { backdrop-filter: blur(20px); border: 1px solid var(--border-color); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background-color: var(--glass-bg); }
        
        body:not(.modo-crema) .glass-card { background-color: var(--glass-bg); box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.03), 0 10px 30px -10px rgba(0, 0, 0, 0.5); }
        body.modo-crema .glass-card { background-color: rgba(255, 255, 255, 0.9); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.01), 0 2px 4px -1px rgba(0, 0, 0, 0.005); }
        
        .glass-card:hover { transform: translateY(-4px); border-color: rgba(59, 130, 246, 0.2); }
        body.modo-crema .glass-card:hover { background-color: #ffffff; box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.03); }

        .filter-button {
            padding: 0.65rem 1.1rem;
            border-radius: 0.85rem;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            border: 1px solid transparent;
            color: var(--text-color);
            background-color: var(--card-color);
            transition: all 0.25s ease;
        }

        .filter-button:hover {
            background-color: rgba(59, 130, 246, 0.18);
            border-color: rgba(59, 130, 246, 0.35);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .filter-button--active {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.95), rgba(14, 165, 233, 0.85));
            border-color: rgba(59, 130, 246, 0.85);
            color: #ffffff;
            box-shadow: 0 16px 35px -20px rgba(59, 130, 246, 0.75);
        }

        .filter-button--active:hover {
            transform: translateY(-1px);
        }

        .filter-button {
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.06), 0 8px 24px -18px rgba(0,0,0,0.5);
        }

        .header-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.8rem 1.2rem;
            border-radius: 9999px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(59, 130, 246, 0.12);
            color: #EFF6FF;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        .header-action-btn:hover {
            transform: translateY(-2px);
            background: rgba(59, 130, 246, 0.22);
            box-shadow: 0 18px 40px -30px rgba(59, 130, 246, 0.8);
        }

        .mesa-card {
            will-change: transform, opacity;
            position: relative;
            z-index: 0;
        }

        .mesa-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.04) 0%, transparent 100%);
            opacity: 0.4;
            pointer-events: none;
            border-radius: inherit;
            z-index: -1;
        }

        body:not(.modo-crema) .glass-card.kitchen-card { background: var(--panel-card); border-color: var(--panel-border); }
        body.modo-crema .glass-card.kitchen-card { background: var(--panel-card); border-color: var(--panel-border); }
        .kitchen-card:hover { transform: translateY(-2px); }
        .accent-title { color: #38bdf8; }
        .kitchen-pill { background: rgba(59, 130, 246, 0.12); border: 1px solid rgba(59, 130, 246, 0.18); color: #0ea5e9; }
        body:not(.modo-crema) .kitchen-pill { background: rgba(59, 130, 246, 0.16); color: #bfdbfe; border-color: rgba(147, 197, 253, 0.2); }
        .product-item { background: var(--panel-secondary); }
        body.modo-crema .product-item { background: rgba(148, 163, 184, 0.05); }
        
        ::-webkit-scrollbar { width: 4px; } ::-webkit-scrollbar-track { background: transparent; } ::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; } ::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }
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

    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const texts = document.querySelectorAll('.sidebar-text');
            
            if(sidebar.classList.contains('w-[260px]')) {
                sidebar.classList.replace('w-[260px]', 'w-[88px]');
                texts.forEach(el => el.classList.add('hidden')); 
            } else {
                sidebar.classList.replace('w-[88px]', 'w-[260px]');
                texts.forEach(el => el.classList.remove('hidden')); 
            }
        });

        // === LÓGICA DEL TEMA CLARO/OSCURO ===
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

    @stack('scripts')

</body>
</html>