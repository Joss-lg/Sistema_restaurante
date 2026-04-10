<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                        'ol-bg': '#09090b',       /* Fondo mate del dashboard */
                        'ol-card': '#18181b',     /* Gris oscuro premium */
                        'ol-blue': '#3B82F6',     /* El azul de tu marca */
                        'ol-text-muted': '#a1a1aa'
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #09090b; color: #FAFAFA; font-family: 'Inter', sans-serif; overflow: hidden; margin: 0; padding: 0; }

        /* Visor de PIN hundido y elegante */
        .visor-screen {
            background-color: #000000;
            border: 1px solid #27272a;
            box-shadow: inset 0 6px 15px rgba(0, 0, 0, 0.8);
        }

        /* Botones numéricos individuales con volumen */
        .key-btn {
            background-color: #18181b;
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-top: 1px solid rgba(255, 255, 255, 0.08); 
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .key-btn:hover {
            background-color: #27272a;
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.6);
        }
        .key-btn:active {
            transform: scale(0.95);
            box-shadow: inset 0 4px 8px rgba(0, 0, 0, 0.8);
        }

        /* Botón OK: Más grande y vibrante */
        .btn-ok {
            background: linear-gradient(180deg, #3B82F6 0%, #2563EB 100%);
            border: 1px solid #2563EB;
            border-top: 1px solid #93C5FD;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
            transition: all 0.15s ease;
        }
        .btn-ok:hover {
            filter: brightness(1.1);
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(59, 130, 246, 0.4);
        }

        /* Animación para el cursor */
        .cursor-blink {
            animation: blink 1s infinite;
        }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
    </style>
</head>
<body class="min-h-screen w-full flex flex-col items-center justify-center relative">

    <div class="w-full max-w-[440px] px-6 flex flex-col items-center">
        
        <div class="flex flex-col items-center mb-12">
            <div class="w-20 h-20 bg-ol-card border border-white/10 rounded-[1.5rem] flex items-center justify-center shadow-2xl mb-6 p-2">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Logo" class="w-full h-full object-contain rounded-xl">
            </div>
            <h1 class="text-4xl font-black tracking-widest text-white leading-none uppercase">
                Ollintem <span class="text-ol-blue">Pro</span>
            </h1>
            <p class="text-[11px] uppercase tracking-[0.4em] text-ol-text-muted font-bold mt-4">Punto de Venta</p>
        </div>

        @if($errors->any())
            <div class="w-full bg-rose-500/10 border border-rose-500/20 py-4 rounded-2xl mb-8 flex items-center justify-center">
                <p class="text-rose-500 text-xs font-black uppercase tracking-widest">NIP Incorrecto</p>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" id="pinForm" class="hidden">
            @csrf
            <input type="password" name="codigo_empleado" id="pinHidden">
        </form>

        <div class="w-full h-24 visor-screen rounded-3xl mb-10 flex items-center justify-center gap-3 relative overflow-hidden">
            <span id="pinDisplay" class="text-[48px] font-black tracking-[0.5em] text-white mt-2"></span>
            <span class="cursor-blink w-[3px] h-10 bg-ol-blue rounded-full"></span>
        </div>

        <div class="grid grid-cols-4 gap-5 w-full">
            
            <button type="button" onclick="appendNumber('1')" class="key-btn h-20 rounded-3xl text-3xl font-bold text-white">1</button>
            <button type="button" onclick="appendNumber('2')" class="key-btn h-20 rounded-3xl text-3xl font-bold text-white">2</button>
            <button type="button" onclick="appendNumber('3')" class="key-btn h-20 rounded-3xl text-3xl font-bold text-white">3</button>
            
            <button type="button" onclick="submitForm()" class="btn-ok col-span-1 row-span-2 rounded-3xl flex flex-col items-center justify-center text-white group">
                <span class="font-black text-3xl leading-tight tracking-wider">OK</span>
                <span class="text-[10px] font-bold uppercase tracking-widest opacity-90 mt-2">Entrar</span>
            </button>

            <button type="button" onclick="appendNumber('4')" class="key-btn h-20 rounded-3xl text-3xl font-bold text-white">4</button>
            <button type="button" onclick="appendNumber('5')" class="key-btn h-20 rounded-3xl text-3xl font-bold text-white">5</button>
            <button type="button" onclick="appendNumber('6')" class="key-btn h-20 rounded-3xl text-3xl font-bold text-white">6</button>

            <button type="button" onclick="appendNumber('7')" class="key-btn h-20 rounded-3xl text-3xl font-bold text-white">7</button>
            <button type="button" onclick="appendNumber('8')" class="key-btn h-20 rounded-3xl text-3xl font-bold text-white">8</button>
            <button type="button" onclick="appendNumber('9')" class="key-btn h-20 rounded-3xl text-3xl font-bold text-white">9</button>
            
            <button type="button" onclick="deleteNumber()" class="key-btn col-span-1 h-20 rounded-3xl flex flex-col items-center justify-center group">
                <i class="fas fa-backspace text-rose-500 text-2xl mb-1"></i>
                <span class="text-[9px] font-black text-rose-500 tracking-tighter uppercase">Borrar</span>
            </button>

            <div class="col-span-1"></div>
            <button type="button" onclick="appendNumber('0')" class="key-btn h-20 rounded-3xl text-3xl font-bold text-white">0</button>
            <div class="col-span-2"></div>
        </div>
    </div>

    <div class="absolute bottom-8 w-full text-center">
        <p class="text-[10px] text-ol-text-muted font-bold tracking-[0.4em] uppercase opacity-40">© 2026 Softrestaurant Ollintem</p>
    </div>

    <script>
        const pinHidden = document.getElementById('pinHidden');
        const pinDisplay = document.getElementById('pinDisplay');
        const pinForm = document.getElementById('pinForm');

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
            if (pinHidden.value.length >= 2) {
                pinForm.submit();
            }
        }

        document.addEventListener('keydown', (e) => {
            if (e.key >= '0' && e.key <= '9') appendNumber(e.key);
            if (e.key === 'Backspace') deleteNumber();
            if (e.key === 'Enter') submitForm();
        });
    </script>
</body>
</html>