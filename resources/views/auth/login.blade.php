<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Ollintem Pro - Acceso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        'ol-bg': '#09090b',
                        'ol-card': '#18181b',
                        'ol-blue': '#3B82F6',
                        'ol-text-muted': '#a1a1aa'
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --bg-color: #09090b;
            --card-color: #18181b;
            --text-color: #FAFAFA;
            --visor-bg: #000000;
            --border-color: rgba(255, 255, 255, 0.1);
        }

        body.modo-crema {
            --bg-color: #FDFBF7;
            --card-color: #ffffff;
            --text-color: #18181b;
            --visor-bg: #ffffff;
            --border-color: rgba(0, 0, 0, 0.1);
        }

        html, body {
            height: 100%;
        }

        body { 
            background-color: var(--bg-color); 
            color: var(--text-color); 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            padding: 0; 
            transition: all 0.4s ease; 
            min-height: 100vh;
            min-height: 100dvh;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .logo-claro { display: none; }
        body.modo-crema .logo-oscuro { display: none; }
        body.modo-crema .logo-claro { display: block; }

        .visor-screen {
            background-color: var(--visor-bg);
            border: 1px solid var(--border-color);
            box-shadow: inset 0 6px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.4s ease;
        }

        .key-btn {
            background-color: var(--card-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-top: 1px solid rgba(255, 255, 255, 0.08); 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body.modo-crema .key-btn {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .key-btn:hover { transform: translateY(-2px); opacity: 0.9; }
        .key-btn:active { transform: scale(0.95); box-shadow: inset 0 4px 8px rgba(0, 0, 0, 0.2); }

        .btn-ok {
            background: linear-gradient(180deg, #3B82F6 0%, #2563EB 100%);
            border: 1px solid #2563EB;
            border-top: 1px solid #93C5FD;
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            color: white !important;
        }

        .action-toggle {
            background: var(--card-color);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .cursor-blink { animation: blink 1s infinite; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

        @media (max-height: 700px) {
            .compact-header { margin-bottom: 0.5rem !important; }
            .compact-header img { width: 3rem !important; height: 3rem !important; margin-bottom: 0.25rem !important; }
            .compact-header h1 { font-size: 1.25rem !important; }
            .compact-header p { margin-top: 0.25rem !important; }
            .compact-visor { height: 3rem !important; margin-bottom: 0.75rem !important; }
            .compact-visor span:first-child { font-size: 1.5rem !important; }
            .keypad-grid { gap: 0.5rem !important; }
        }
    </style>
</head>
<body class="w-full flex flex-col items-center justify-center relative p-4" style="padding-top: max(1rem, env(safe-area-inset-top)); padding-bottom: max(1rem, env(safe-area-inset-bottom));">

    <script>
        if (localStorage.getItem('tema-ollintem') === 'crema') {
            document.body.classList.add('modo-crema');
        }
    </script>

    {{-- Botón Salir (Arriba a la Izquierda) --}}
    <div class="absolute z-50" style="top: max(1rem, env(safe-area-inset-top)); left: max(1rem, env(safe-area-inset-left));">
        <button onclick="cerrarAplicacion()" class="action-toggle px-3.5 py-2 rounded-full flex items-center gap-2 text-[9px] sm:text-[10px] font-black tracking-widest uppercase shadow-lg text-rose-500 hover:bg-rose-500 hover:text-white border-rose-500/20 active:scale-95">
            <i class="fas fa-power-off text-xs"></i>
            <span class="hidden sm:inline">Salir de Ollinrest</span>
            <span class="sm:hidden">Salir</span>
        </button>
    </div>

    {{-- Botón Modo Claro/Oscuro (Arriba a la Derecha) --}}
    <div class="absolute z-50" style="top: max(1rem, env(safe-area-inset-top)); right: max(1rem, env(safe-area-inset-right));">
        <button onclick="toggleTheme()" class="action-toggle px-4 py-2 rounded-full flex items-center gap-2 text-[9px] sm:text-[10px] font-black tracking-widest uppercase shadow-lg active:scale-95">
            <i id="themeIcon" class="fas fa-moon text-ol-blue"></i>
            <span id="themeText" class="hidden sm:inline">Modo Claro</span>
        </button>
    </div>

    {{-- Contenedor principal --}}
    <div class="w-full max-w-[320px] sm:max-w-[360px] flex flex-col items-center my-auto py-2">
        
        {{-- Cabecera --}}
        <div class="compact-header flex flex-col items-center mb-4 sm:mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Ollintem" class="logo-oscuro mx-auto w-16 h-16 sm:w-20 sm:h-20 mb-2 object-contain">
            <img src="{{ asset('images/logo2.png') }}" alt="Logo Ollintem" class="logo-claro mx-auto w-16 h-16 sm:w-20 sm:h-20 mb-2 object-contain">
            
            <h1 class="text-2xl sm:text-3xl font-black tracking-widest leading-none uppercase">
                OLLINTEM <span class="text-ol-blue">PRO</span>
            </h1>
            <p class="text-[9px] sm:text-[10px] uppercase tracking-[0.4em] font-bold mt-2 opacity-60">Punto de Venta</p>
        </div>

        @if($errors->any())
            <div class="w-full bg-rose-500/10 border border-rose-500/20 py-2 sm:py-3 rounded-xl mb-4 flex items-center justify-center">
                <p class="text-rose-500 text-[10px] font-black uppercase tracking-widest">NIP Incorrecto</p>
            </div>
        @endif

        <form action="{{ route('login.pin') }}" method="POST" id="pinForm" class="hidden">
            @csrf
            <input type="password" name="codigo_empleado" id="pinHidden">
        </form>

        {{-- Visor --}}
        <div class="compact-visor w-full h-14 sm:h-16 visor-screen rounded-2xl mb-5 sm:mb-6 flex items-center justify-center gap-2 relative overflow-hidden">
            <span id="pinDisplay" class="text-3xl sm:text-4xl font-black tracking-[0.4em] mt-1"></span>
            <span class="cursor-blink w-[2px] h-8 bg-ol-blue rounded-full"></span>
        </div>

        {{-- GRID TECLADO NUMÉRICO --}}
        <div class="keypad-grid grid grid-cols-4 gap-3 sm:gap-4 w-full">
            <!-- Fila 1 -->
            <button type="button" onclick="appendNumber('1')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">1</button>
            <button type="button" onclick="appendNumber('2')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">2</button>
            <button type="button" onclick="appendNumber('3')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">3</button>
            
            <!-- Botón OK (Ocupa columna 4, filas 1 y 2) -->
            <button type="button" onclick="submitForm()" class="btn-ok row-span-2 rounded-2xl flex flex-col items-center justify-center w-full h-full active:scale-95 transition-transform">
                <span class="font-black text-2xl sm:text-3xl leading-tight">OK</span>
                <span class="text-[8px] sm:text-[9px] font-bold uppercase mt-1 opacity-90">Entrar</span>
            </button>

            <!-- Fila 2 -->
            <button type="button" onclick="appendNumber('4')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">4</button>
            <button type="button" onclick="appendNumber('5')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">5</button>
            <button type="button" onclick="appendNumber('6')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">6</button>

            <!-- Fila 3 -->
            <button type="button" onclick="appendNumber('7')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">7</button>
            <button type="button" onclick="appendNumber('8')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">8</button>
            <button type="button" onclick="appendNumber('9')" class="key-btn aspect-square rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">9</button>
            
            <!-- Botón Borrar (Completamente Rojo) -->
            <button type="button" onclick="deleteNumber()" class="bg-rose-500 hover:bg-rose-600 active:scale-95 text-white aspect-square rounded-2xl flex items-center justify-center transition-all shadow-lg border border-rose-400/30">
                <i class="fas fa-backspace text-xl sm:text-2xl"></i>
            </button>

            <!-- Fila 4: '0' centrado bajo el 8 -->
            <div class="col-start-2">
                <button type="button" onclick="appendNumber('0')" class="key-btn aspect-square w-full rounded-2xl text-2xl sm:text-3xl font-bold flex items-center justify-center">0</button>
            </div>
        </div>
    </div>

    <script>
        const body = document.body;
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');
        const pinHidden = document.getElementById('pinHidden');
        const pinDisplay = document.getElementById('pinDisplay');
        const pinForm = document.getElementById('pinForm');

        document.addEventListener('DOMContentLoaded', () => {
            if (body.classList.contains('modo-crema')) {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
                themeIcon.classList.replace('text-ol-blue', 'text-orange-500');
                if(themeText) themeText.innerText = "Modo Oscuro";
            }
        });

        function toggleTheme() {
            body.classList.toggle('modo-crema');
            const esCrema = body.classList.contains('modo-crema');
            localStorage.setItem('tema-ollintem', esCrema ? 'crema' : 'negro');

            if (esCrema) {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
                themeIcon.classList.replace('text-ol-blue', 'text-orange-500');
                if(themeText) themeText.innerText = "Modo Oscuro";
            } else {
                themeIcon.classList.replace('fa-sun', 'fa-moon');
                themeIcon.classList.replace('text-orange-500', 'text-ol-blue');
                if(themeText) themeText.innerText = "Modo Claro";
            }
        }

        function updateDisplay() {
            if (navigator.vibrate) navigator.vibrate(10); 
            pinDisplay.textContent = '•'.repeat(pinHidden.value.length);
        }

        function appendNumber(number) {
            if (pinHidden.value.length < 8) { 
                pinHidden.value += number;
                updateDisplay();
            }
        }

        function deleteNumber() {
            pinHidden.value = pinHidden.value.slice(0, -1);
            updateDisplay();
        }

        function submitForm() {
            if (pinHidden.value.length >= 2) pinForm.submit();
        }

        function cerrarAplicacion() {
            // Intenta cerrar la ventana/pestaña actual
            window.close();
            
            // Truco para obligar el cierre en pestañas estándar de navegador
            setTimeout(() => {
                window.open('about:blank', '_self').close();
            }, 100);
        }

        document.addEventListener('keydown', (e) => {
            if (e.key >= '0' && e.key <= '9') appendNumber(e.key);
            if (e.key === 'Backspace') deleteNumber();
            if (e.key === 'Enter') submitForm();
        });
    </script>
</body>
</html>