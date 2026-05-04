<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta | Ollintem Pro</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    {{-- Motor de Animaciones Premium y Colores --}}
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-soft': 'pulseSoft 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'glow': 'glow 4s ease-in-out infinite alternate',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-12px)' },
                        },
                        pulseSoft: {
                            '0%, 100%': { opacity: 1 },
                            '50%': { opacity: 0.6 },
                        },
                        glow: {
                            '0%': { boxShadow: '0 0 40px -10px rgba(59,130,246,0.3)' },
                            '100%': { boxShadow: '0 0 60px 10px rgba(59,130,246,0.6)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --bg-base: #030305; 
            --bg-panel: #0a0a0c;
            --border-color: rgba(255, 255, 255, 0.05);
            --border-highlight: rgba(255, 255, 255, 0.1);
            --text-main: #FFFFFF;
            --text-muted: #82828c;
            --glass-bg: rgba(10, 10, 12, 0.65);
            --glow-color: rgba(59, 130, 246, 0.12);
        }

        body.modo-crema {
            --bg-base: #F7F7F9; 
            --bg-panel: #FFFFFF;
            --border-color: rgba(0, 0, 0, 0.06);
            --border-highlight: rgba(0, 0, 0, 0.12);
            --text-main: #09090b;
            --text-muted: #6b7280;
            --glass-bg: rgba(255, 255, 255, 0.8);
            --glow-color: rgba(59, 130, 246, 0.06);
        }

        body { 
            background-color: var(--bg-base);
            color: var(--text-main);
            overflow: hidden;
            transition: background-color 0.4s ease, color 0.4s ease;
        }

        /* Utilidad para cristal hiperrealista */
        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-color);
        }
    </style>
</head>
<body class="selection:bg-[#3B82F6]/30 h-screen w-full flex flex-col">

    <script>
        if (localStorage.getItem('tema-ollintem') === 'crema') document.body.classList.add('modo-crema');
    </script>

    @include('mesero.navbar')

    <div class="flex-1 flex overflow-hidden">
        @include('mesero.sidebar')
        <div class="flex-1 flex flex-col relative bg-[var(--bg-base)]">
            @include('mesero.central')
            @include('mesero.footer')
        </div>
    </div>

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
                    icon.className = 'fas fa-moon text-blue-500 text-sm drop-shadow-sm';
                } else {
                    icon.className = 'fas fa-sun text-amber-400 text-sm drop-shadow-sm';
                }
            }
        }
        document.addEventListener('DOMContentLoaded', () => actualizarIcono(document.body.classList.contains('modo-crema')));
    </script>
@include('mesero.modal-nueva-mesa')
</body>
</html>