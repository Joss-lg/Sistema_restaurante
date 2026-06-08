<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta | Ollintem Pro</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
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

    <div class="flex-1 min-h-0 flex overflow-hidden">
        @include('mesero.sidebar')
        <div class="flex-1 min-h-0 flex flex-col relative bg-[var(--bg-base)]">
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
    <script>
        // Poll para refrescar mesas abiertas y lista del sidebar cada 5s
        async function refrescarMesas() {
            try {
                const res = await fetch('/mesero/mesas/abiertas', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                const data = await res.json().catch(() => null);
                if (!res.ok || !data || !data.success) return;

                // Actualizar contadores en central
                const totalElem = document.getElementById('txtTotalMesas');
                const abiertasElem = document.getElementById('txtMesasAbiertas');
                if (totalElem) totalElem.innerText = data.conteo_total;
                if (abiertasElem) abiertasElem.innerText = data.conteo_abiertas;

                // Actualizar badge en sidebar
                const badge = document.getElementById('sidebarMesasActivas');
                if (badge) badge.innerText = data.conteo_abiertas;

                // Actualizar lista de mesas en sidebar
                const list = document.getElementById('sidebarMesasList');
                if (list) {
                    list.innerHTML = '';
                    const mesas = data.mesas_abiertas || [];
                    if (mesas.length === 0) {
                        list.innerHTML = `<div class="flex-1 flex flex-col items-center justify-center p-4 lg:p-8 text-center"><h3 class="text-sm lg:text-lg font-black text-[var(--text-main)] tracking-tight mb-1 lg:mb-2">Bandeja Vacía</h3><p class="text-[8px] lg:text-xs text-[var(--text-muted)] font-medium leading-relaxed max-w-[150px] lg:max-w-[200px]">Inicia una nueva comanda desde el panel central.</p></div>`;
                    } else {
                        mesas.forEach(m => {
                            const a = document.createElement('a');
                            a.href = `/mesero/comanda/${m.id}`;
                            a.className = 'mesa-item block p-3 lg:p-4 rounded-2xl lg:rounded-3xl border border-[var(--border-color)] bg-[var(--bg-panel)] hover:border-[#3B82F6]/40 transition-all';
                            const totalCons = (m.total_consumo || 0).toFixed(2);
                            a.innerHTML = `
                                <div class="flex items-start justify-between gap-3 lg:gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <h3 class="text-base lg:text-xl font-black text-[var(--text-main)]">Mesa ${m.numero}</h3>
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-700/10 text-emerald-300 text-[10px] font-black uppercase">ACTIVA</span>
                                        </div>
                                        <div class="mt-2 flex items-center gap-3 text-[12px] text-[var(--text-muted)]">
                                            <span class="flex items-center gap-1"><i class="fas fa-user-friends text-[12px]"></i> ${m.capacidad ?? ''} personas</span>
                                            <span class="text-[var(--text-muted)]">• 0m</span>
                                            <span class="text-emerald-400 font-black">$ ${totalCons}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col justify-between text-right">
                                        <span class="text-[#3B82F6] font-bold">Ver comanda ›</span>
                                    </div>
                                </div>
                            `;
                            list.appendChild(a);
                        });
                    }
                }
            } catch (err) {
                console.error('Error refrescando mesas:', err);
            }
        }

        // Iniciar polling cuando la página cargue
        document.addEventListener('DOMContentLoaded', () => {
            refrescarMesas();
            setInterval(refrescarMesas, 5000);
        });
    </script>
@include('mesero.modal-nueva-mesa')
</body>
</html>