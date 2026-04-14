<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ollintem Pro - Dashboard</title>

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
        /* =========================================
           VARIABLES DE TEMA (OSCURO POR DEFECTO)
           ========================================= */
        :root {
            --bg-color: #050505;
            --sidebar-bg: rgba(7, 7, 9, 0.8);
            --header-bg: rgba(5, 5, 5, 0.7);
            --card-color: #0e0e11;
            --text-color: #FAFAFA;
            --text-muted: #8a8a93;
            --border-color: rgba(255, 255, 255, 0.05);
            --glass-bg: linear-gradient(145deg, rgba(20, 20, 23, 0.8) 0%, rgba(10, 10, 12, 0.6) 100%);
            --glass-hover: linear-gradient(145deg, rgba(25, 25, 29, 0.9) 0%, rgba(12, 12, 14, 0.8) 100%);
        }

        /* =========================================
           TEMA CREMOSO (MODO CLARO)
           ========================================= */
        body.modo-crema {
            --bg-color: #FDFBF7;
            --sidebar-bg: rgba(245, 242, 237, 0.9);
            --header-bg: rgba(253, 251, 247, 0.8);
            --card-color: #ffffff;
            --text-color: #18181b;
            --text-muted: #71717a;
            --border-color: rgba(0, 0, 0, 0.08);
            --glass-bg: linear-gradient(145deg, rgba(255, 255, 255, 0.9) 0%, rgba(245, 245, 245, 0.6) 100%);
            --glass-hover: linear-gradient(145deg, rgba(255, 255, 255, 1) 0%, rgba(250, 250, 250, 0.9) 100%);
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
        
        .text-metallic {
            background: linear-gradient(180deg, var(--text-color) 0%, var(--text-muted) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.08), 0 10px 30px -10px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Sombras profundas solo en modo oscuro */
        body:not(.modo-crema) .glass-card {
            box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.08), 0 10px 30px -10px rgba(0, 0, 0, 0.8);
        }

        .glass-card:hover {
            transform: translateY(-4px);
            border-color: var(--border-color);
            background: var(--glass-hover);
        }

        .icon-wrapper { box-shadow: inset 0 1px 2px rgba(255,255,255,0.1), 0 4px 10px rgba(0,0,0,0.1); }
        
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

    <div class="fixed top-[-20%] left-[-10%] w-[50vw] h-[50vw] rounded-full bg-blue-600/5 blur-[150px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[40vw] h-[40vw] rounded-full bg-orange-600/5 blur-[150px] pointer-events-none z-0"></div>

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
                    <button id="toggleSidebar" class="w-8 h-8 rounded-lg border border-luxury-border flex items-center justify-center text-luxury-muted hover:text-luxury-text transition-all shrink-0 shadow-sm">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <nav class="px-5 py-8 space-y-1.5" id="nav-container">
                    <p class="sidebar-text px-3 text-[8.5px] font-black text-luxury-muted/70 uppercase tracking-[0.25em] mb-5">Principal</p>
                    
                    <a href="#" class="nav-link relative flex items-center gap-4 bg-gradient-to-r from-luxury-accent/10 to-transparent text-luxury-text px-3 py-3 rounded-xl font-semibold transition-all border border-luxury-border border-l-0 group">
                        <div class="sidebar-text absolute left-0 top-0 bottom-0 w-[3px] bg-luxury-accent rounded-r-md shadow-[0_0_12px_rgba(59,130,246,0.9)]"></div>
                        <i class="fas fa-th-large w-5 text-center text-luxury-accent drop-shadow-[0_0_8px_rgba(59,130,246,0.6)] group-hover:scale-110 transition-transform shrink-0"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                    
                    <a href="#" class="nav-link flex items-center gap-4 text-luxury-muted hover:text-luxury-text px-3 py-3 rounded-xl font-medium transition-all group border border-transparent hover:border-luxury-border">
                        <i class="fas fa-cube w-5 text-center transition-all group-hover:scale-110 shrink-0"></i>
                        <span class="sidebar-text">Inventario</span>
                    </a>
                    
                    <a href="{{ route('admin.empleados.index') }}" class="nav-link flex items-center gap-4 text-luxury-muted hover:text-luxury-text px-3 py-3 rounded-xl font-medium transition-all group border border-transparent hover:border-luxury-border">
                        <i class="fas fa-users w-5 text-center transition-all group-hover:scale-110 shrink-0"></i>
                        <span class="sidebar-text">Empleados</span>
                    </a>
                    
                    <a href="#" class="nav-link flex items-center gap-4 text-luxury-muted hover:text-luxury-text px-3 py-3 rounded-xl font-medium transition-all group border border-transparent hover:border-luxury-border">
                        <i class="fas fa-utensils w-5 text-center transition-all group-hover:scale-110 shrink-0"></i>
                        <span class="sidebar-text">Alimentos</span>
                    </a>
                    
                    <a href="#" class="nav-link flex items-center gap-4 text-luxury-muted hover:text-luxury-text px-3 py-3 rounded-xl font-medium transition-all group border border-transparent hover:border-luxury-border">
                        <i class="fas fa-tag w-5 text-center transition-all group-hover:scale-110 shrink-0"></i>
                        <span class="sidebar-text">Promociones</span>
                    </a>
                </nav>
            </div>

            <div class="p-6 border-t border-luxury-border">
                <div class="flex items-center gap-3.5 mb-6 px-2 overflow-hidden">
                    <div class="w-10 h-10 rounded-full border border-luxury-border flex items-center justify-center text-luxury-text font-black text-sm shadow-inner shrink-0 bg-black/10">
                        AD
                    </div>
                    <div class="flex flex-col justify-center sidebar-text">
                        <p class="font-bold text-sm text-luxury-text leading-none">Admin Principal</p>
                        <p class="text-[8.5px] text-luxury-muted font-bold tracking-[0.15em] mt-1.5 uppercase opacity-80">Gerencia</p>
                    </div>
                </div>
                
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-link w-full flex items-center justify-center gap-2.5 border border-luxury-border hover:bg-rose-500/10 hover:border-rose-500/30 text-luxury-muted hover:text-rose-500 py-3 rounded-xl transition-all duration-300 text-xs font-bold group shadow-sm bg-black/5">
                        <i class="fas fa-sign-out-alt transition-transform group-hover:-translate-x-1 shrink-0"></i>
                        <span class="sidebar-text">Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto relative z-10 flex flex-col">
            
            <header class="backdrop-blur-2xl border-b sticky top-0 z-30 px-10 py-5 flex justify-between items-center">
                <div class="flex flex-col">
                    <h1 class="text-[22px] font-black text-luxury-text tracking-tight">Dashboard Financiero</h1>
                    <p class="text-[11px] text-luxury-muted font-medium mt-1 opacity-80">Visión analítica de operaciones</p>
                </div>
                
                <div class="flex items-center gap-6">
                    <button onclick="toggleTheme()" class="w-9 h-9 rounded-full border border-luxury-border flex items-center justify-center text-luxury-muted hover:text-luxury-text transition-all shadow-sm">
                        <i id="dashThemeIcon" class="fas fa-sun"></i>
                    </button>

                    <div class="hidden md:flex flex-col items-end border-r border-luxury-border pr-6">
                        <span class="text-[9px] font-black text-luxury-muted uppercase tracking-[0.2em]">{{ now()->format('d M, Y') }}</span>
                        <span class="text-sm font-black text-luxury-text tabular-nums tracking-widest mt-0.5">{{ now()->format('H:i:s') }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 bg-emerald-500/5 border border-emerald-500/20 px-4 py-2 rounded-xl shadow-[0_0_20px_rgba(16,185,129,0.05)]">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500 shadow-[0_0_8px_#10b981]"></span>
                        </span>
                        <span class="text-[9px] font-black text-emerald-400 uppercase tracking-[0.25em]">Online</span>
                    </div>
                </div>
            </header>

            <div class="p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col">
                
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    <div class="glass-card rounded-[1.5rem] p-7 flex flex-col justify-between h-44 group">
                        <div class="flex justify-between items-start">
                            <h3 class="text-[10px] font-bold text-luxury-muted uppercase tracking-[0.2em]">Ingresos Brutos</h3>
                            <div class="w-9 h-9 rounded-xl border border-luxury-border flex items-center justify-center icon-wrapper group-hover:border-emerald-500/40 transition-all duration-300">
                                <i class="fas fa-wallet text-luxury-muted text-sm group-hover:text-emerald-400 group-hover:scale-110 transition-all duration-300"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-[2.5rem] leading-none font-black text-metallic tracking-tighter">${{ number_format($stats['ventas_dia'] ?? 0, 2) }}</p>
                            <p class="text-[11px] font-bold text-emerald-400 mt-3 flex items-center gap-1.5 opacity-90 tracking-wide">
                                <i class="fas fa-arrow-trend-up"></i> +4.2% vs ayer
                            </p>
                        </div>
                    </div>

                    <div class="glass-card rounded-[1.5rem] p-7 flex flex-col justify-between h-44 group">
                        <div class="flex justify-between items-start">
                            <h3 class="text-[10px] font-bold text-luxury-muted uppercase tracking-[0.2em]">Volumen de Órdenes</h3>
                            <div class="w-9 h-9 rounded-xl border border-luxury-border flex items-center justify-center icon-wrapper group-hover:border-luxury-accent/40 transition-all duration-300">
                                <i class="fas fa-receipt text-luxury-muted text-sm group-hover:text-luxury-accent group-hover:scale-110 transition-all duration-300"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-[2.5rem] leading-none font-black text-metallic tracking-tighter">{{ $stats['ordenes_dia'] ?? 0 }}</p>
                            <p class="text-[10px] font-semibold text-luxury-muted mt-3 uppercase tracking-wide">Transacciones</p>
                        </div>
                    </div>

                    <div class="glass-card rounded-[1.5rem] p-7 flex flex-col justify-between h-44 group">
                        <div class="flex justify-between items-start">
                            <h3 class="text-[10px] font-bold text-luxury-muted uppercase tracking-[0.2em]">Ticket Promedio</h3>
                            <div class="w-9 h-9 rounded-xl border border-luxury-border flex items-center justify-center icon-wrapper group-hover:border-indigo-400/40 transition-all duration-300">
                                <i class="fas fa-tag text-luxury-muted text-sm group-hover:text-indigo-400 group-hover:scale-110 transition-all duration-300"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-[2.5rem] leading-none font-black text-metallic tracking-tighter">$0<span class="text-2xl text-luxury-muted/50 font-bold">.00</span></p>
                            <p class="text-[10px] font-semibold text-luxury-muted mt-3 uppercase tracking-wide">Por Comensal</p>
                        </div>
                    </div>

                    <div class="glass-card rounded-[1.5rem] p-7 flex flex-col justify-between h-44 group">
                        <div class="flex justify-between items-start">
                            <h3 class="text-[10px] font-bold text-luxury-muted uppercase tracking-[0.2em]">Afluencia Total</h3>
                            <div class="w-9 h-9 rounded-xl border border-luxury-border flex items-center justify-center icon-wrapper group-hover:border-orange-400/40 transition-all duration-300">
                                <i class="fas fa-user-friends text-luxury-muted text-sm group-hover:text-orange-400 group-hover:scale-110 transition-all duration-300"></i>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-[2.5rem] leading-none font-black text-metallic tracking-tighter">0</p>
                            <p class="text-[10px] font-semibold text-luxury-muted mt-3 uppercase tracking-wide">Registros</p>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-[2rem] p-8 lg:p-10 w-full flex-1 flex flex-col min-h-[450px]">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h2 class="text-xl font-black text-luxury-text tracking-tight">Análisis de Flujo</h2>
                            <p class="text-xs text-luxury-muted font-medium mt-1.5">Métricas de rendimiento a lo largo de la jornada</p>
                        </div>
                        <button class="flex items-center gap-2.5 text-[11px] font-bold text-luxury-text border border-luxury-border px-5 py-2.5 rounded-xl hover:bg-black/5 transition-all shadow-lg group">
                            <i class="fas fa-cloud-download-alt text-luxury-accent group-hover:scale-110 transition-transform"></i> Exportar
                        </button>
                    </div>
                    
                    <div class="w-full relative flex-1 min-h-[300px]">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const texts = document.querySelectorAll('.sidebar-text');
            const navLinks = document.querySelectorAll('.nav-link');
            const logoIcon = document.getElementById('logo-icon');
            
            sidebar.classList.toggle('w-[260px]');
            sidebar.classList.toggle('w-[88px]');
            
            texts.forEach(el => {
                el.classList.toggle('hidden');
                el.classList.toggle('opacity-0');
            });

            navLinks.forEach(el => {
                el.classList.toggle('justify-center');
                el.classList.toggle('px-3');
            });

            logoIcon.classList.toggle('hidden');
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let myChart; 

        function initChart(esCrema) {
            const textColor = esCrema ? '#52525b' : '#71717A';
            const gridColor = esCrema ? 'rgba(0, 0, 0, 0.05)' : 'rgba(255, 255, 255, 0.02)';
            const tooltipBg = esCrema ? 'rgba(255, 255, 255, 0.95)' : 'rgba(10, 10, 12, 0.95)';
            const tooltipText = esCrema ? '#18181b' : '#FAFAFA';

            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = textColor; 

            const ctx = document.getElementById('salesChart').getContext('2d');
            
            const orangeGlow = ctx.createLinearGradient(0, 0, 0, 400);
            orangeGlow.addColorStop(0, 'rgba(249, 115, 22, 0.25)');
            orangeGlow.addColorStop(1, 'rgba(249, 115, 22, 0)');

            if (myChart) {
                myChart.destroy();
            }

            myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'],
                    datasets: [{
                        label: 'Ingresos Brutos',
                        data: [0, 800, 4863, 2100, 900, 0, 0, 0],
                        borderColor: '#F97316', 
                        backgroundColor: orangeGlow,
                        borderWidth: 3,
                        pointBackgroundColor: esCrema ? '#ffffff' : '#0A0A0C', 
                        pointBorderColor: '#F97316',
                        pointBorderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.45 
                    }, {
                        label: 'Transacciones',
                        data: [0, 4, 15, 8, 3, 0, 0, 0],
                        borderColor: '#3B82F6', 
                        borderWidth: 2.5,
                        borderDash: [5, 5], 
                        pointBackgroundColor: esCrema ? '#ffffff' : '#0A0A0C',
                        pointBorderColor: '#3B82F6',
                        pointBorderWidth: 2,
                        pointRadius: 0, 
                        pointHoverRadius: 6,
                        tension: 0.45
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { 
                            position: 'top',
                            align: 'end',
                            labels: { usePointStyle: true, boxWidth: 8, font: { size: 12, weight: '700' }, color: textColor, padding: 25 }
                        },
                        tooltip: {
                            backgroundColor: tooltipBg, 
                            titleColor: tooltipText,
                            bodyColor: tooltipText,
                            borderColor: gridColor,
                            borderWidth: 1,
                            padding: 16,
                            cornerRadius: 16,
                            titleFont: { size: 13, weight: '600', family: 'Inter' },
                            bodyFont: { size: 14, weight: 'bold', family: 'Inter' },
                            displayColors: true,
                            boxPadding: 8,
                            usePointStyle: true
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, border: { display: false }, ticks: { font: { weight: '600', size: 11 }, padding: 10 } },
                        y: { beginAtZero: true, grid: { color: gridColor, borderDash: [6, 6] }, border: { display: false }, ticks: { font: { weight: '600', size: 11 }, padding: 15 } }
                    }
                }
            });
        }

        // Función para cambiar de tema desde el Dashboard
        function toggleTheme() {
            const body = document.body;
            body.classList.toggle('modo-crema');
            
            const esCrema = body.classList.contains('modo-crema');
            localStorage.setItem('tema-ollintem', esCrema ? 'crema' : 'negro');
            
            actualizarIcono(esCrema);
            initChart(esCrema); // Redibujar gráfica
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

        // Inicialización al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            const esCrema = document.body.classList.contains('modo-crema');
            actualizarIcono(esCrema);
            initChart(esCrema);
        });
    </script>
</body>
</html>