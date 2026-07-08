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
            darkMode: 'class',
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
        .glass-card { backdrop-filter: blur(20px); border: 1px solid var(--border-color); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background-color: var(--glass-bg); }
        body:not(.modo-crema) .glass-card { background-color: var(--glass-bg); box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.03), 0 10px 30px -10px rgba(0, 0, 0, 0.5); }
        body.modo-crema .glass-card { background-color: rgba(255, 255, 255, 0.9); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.01), 0 2px 4px -1px rgba(0, 0, 0, 0.005); }
        ::-webkit-scrollbar { width: 4px; } ::-webkit-scrollbar-track { background: transparent; } ::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }
    </style>
</head>
<body class="selection:bg-[#3B82F6]/30 selection:text-[var(--text-color)]">

    <script>
        const temaGuardado = localStorage.getItem('tema-ollintem');
        if (temaGuardado === 'crema') {
            document.body.classList.add('modo-crema');
            document.documentElement.classList.remove('dark');
        } else {
            document.body.classList.remove('modo-crema');
            document.documentElement.classList.add('dark');
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

            {{-- MODIFICADO AQUÍ: Se añadió 'min-w-0' para salvaguardar el Sidebar --}}
            <main class="flex-1 overflow-y-auto min-w-0 relative z-10 flex flex-col">
                <header class="backdrop-blur-2xl border-b sticky top-0 z-30 px-10 py-5 flex justify-between items-center bg-[var(--header-bg)] border-[var(--border-color)]">
                    <div class="flex flex-col">
                        <h1 class="text-[22px] font-black text-[var(--text-color)] tracking-tight">@yield('header-title', 'Gestión de Personal')</h1>
                        <p class="text-[11px] text-[var(--text-muted)] font-medium mt-1 opacity-80">@yield('header-subtitle', 'Administra tu sistema')</p>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        @hasSection('header-actions')
                            <div class="hidden md:flex items-center gap-3">@yield('header-actions')</div>
                        @endif
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

    <script>
        function toggleTheme() {
            const body = document.body;
            const html = document.documentElement;
            body.classList.toggle('modo-crema');
            const esCrema = body.classList.contains('modo-crema');
            if (esCrema) {
                html.classList.remove('dark');
                localStorage.setItem('tema-ollintem', 'crema');
            } else {
                html.classList.add('dark');
                localStorage.setItem('tema-ollintem', 'negro');
            }
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