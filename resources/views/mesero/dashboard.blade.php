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
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>

    <style>
        :root {
            --bg-base: #050505; 
            --bg-panel: #0E0E12; 
            --border-color: #1F1F24; 
            --border-highlight: rgba(255, 255, 255, 0.05);
            --text-main: #FFFFFF;
            --text-muted: #71717A;
            --accent: #3B82F6;
            --input-bg: #141418;
        }

        body.modo-crema {
            --bg-base: #F4F4F5; 
            --bg-panel: #FFFFFF;
            --border-color: rgba(0, 0, 0, 0.08);
            --border-highlight: rgba(0, 0, 0, 0.04);
            --text-main: #09090B;
            --text-muted: #A1A1AA;
            --input-bg: #E4E4E7;
        }

        /* Aniquilación total de las barras de scroll en todos los navegadores */
        html, body {
            overflow: hidden !important;
            width: 100vw;
            height: 100vh;
            margin: 0;
            padding: 0;
            background-color: var(--bg-base);
            color: var(--text-main);
            transition: background-color 0.4s ease, color 0.4s ease;
        }

        ::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
        * { -ms-overflow-style: none !important; scrollbar-width: none !important; }

        /* Ajustes para evitar líneas divisorias no deseadas */
        aside, main, footer { border-color: var(--border-color) !important; }

        /* Animaciones para notificaciones */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px) translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0) translateX(0);
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateY(0) translateX(0);
            }
            to {
                opacity: 0;
                transform: translateY(20px) translateX(-20px);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
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
        // Almacenar estado anterior para detectar cambios
        let estadoMesasAnterior = {};

        // Función para crear tarjeta de mesa
        function crearTarjetaMesa(m, esLibre = false) {
            const a = document.createElement('a');
            a.href = `/mesero/comanda/${m.id}`;
            
            const totalCons = (m.total_consumo || 0).toFixed(2);
            const indicadorColor = esLibre ? 'bg-blue-400' : 'bg-emerald-400';
            const indicadorSombra = esLibre ? 'rgba(59,130,246,0.8)' : 'rgba(52,211,153,0.8)';
            const etiqueta = esLibre ? '⏱ REABIERTA' : 'ACTIVA';
            const etiquetaColor = esLibre ? 'text-blue-400' : 'text-emerald-400';
            
            a.className = 'group block relative rounded-[24px] bg-gradient-to-b from-[var(--bg-panel)] to-[var(--bg-base)] border border-white/[0.03] p-1.5 transition-all duration-500 hover:border-[#3B82F6]/30 hover:shadow-[0_12px_30px_-10px_rgba(59,130,246,0.2)] outline-none transform hover:-translate-y-1 mb-4';
            
            a.innerHTML = `
                <div class="absolute inset-0 rounded-[24px] shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] pointer-events-none"></div>
                <div class="bg-[var(--bg-panel)] rounded-[18px] p-5 h-full relative z-10 shadow-sm flex flex-col group-hover:bg-[#121216] transition-colors duration-500">
                    <div class="flex items-center justify-between mb-5 gap-3">
                        <div class="flex items-center gap-3.5 overflow-hidden">
                            <div class="relative flex h-2 w-2 shrink-0 items-center justify-center">
                                <span class="animate-ping absolute inline-flex h-4 w-4 rounded-full ${indicadorColor} opacity-20"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 ${indicadorColor} shadow-[0_0_10px_${indicadorSombra}]"></span>
                            </div>
                            <div class="flex flex-col">
                                <h3 class="text-[16px] font-black text-transparent bg-clip-text bg-gradient-to-r from-white to-white/70 tracking-tight leading-none truncate group-hover:to-[#3B82F6] transition-all duration-300">Mesa ${m.numero}</h3>
                                <span class="text-[8px] font-bold ${etiquetaColor} uppercase tracking-wider mt-0.5">${etiqueta}</span>
                            </div>
                        </div>
                        <div class="w-8 h-8 shrink-0 rounded-[10px] bg-white/[0.02] border border-white/[0.04] flex items-center justify-center text-[var(--text-muted)] group-hover:bg-[#3B82F6] group-hover:text-white group-hover:border-transparent transition-all duration-400 shadow-sm">
                            <i class="fas fa-chevron-right text-[10px] transform group-hover:translate-x-0.5 transition-transform"></i>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-white/[0.04] flex items-center justify-between">
                        <div class="flex items-center gap-6">
                            <div class="flex flex-col">
                                <span class="text-[8px] font-bold uppercase tracking-[0.25em] text-[var(--text-muted)] opacity-50 mb-1">Pax</span>
                                <div class="flex items-center gap-1.5">
                                    <i class="fas fa-user text-[var(--text-muted)] opacity-40 text-[10px]"></i>
                                    <span class="text-[12px] font-black text-[var(--text-main)]">${m.capacidad ?? 0}</span>
                                </div>
                            </div>
                            <div class="w-[1px] h-6 bg-white/[0.04]"></div>
                            <div class="flex flex-col">
                                <span class="text-[8px] font-bold uppercase tracking-[0.25em] text-[var(--text-muted)] opacity-50 mb-1">Tiempo</span>
                                <div class="flex items-center gap-1.5">
                                    <i class="far fa-clock text-[var(--text-muted)] opacity-40 text-[10px]"></i>
                                    <span class="text-[12px] font-black text-[var(--text-main)]">0m</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-[8px] font-bold uppercase tracking-[0.25em] text-[var(--text-muted)] opacity-50 mb-1">Total</span>
                            <span class="text-[15px] font-black ${esLibre ? 'text-blue-400' : 'text-emerald-400'} tracking-tighter leading-none drop-shadow-[0_0_10px_rgba(52,211,153,0.15)]">$ ${totalCons}</span>
                        </div>
                    </div>
                </div>
            `;
            return a;
        }

        // Función para mostrar notificación toast
        function mostrarNotificacion(mensaje, tipo = 'info') {
            const toast = document.createElement('div');
            const colorBg = tipo === 'success' ? 'bg-emerald-500/90' : 'bg-blue-500/90';
            const colorBorder = tipo === 'success' ? 'border-emerald-400' : 'border-blue-400';
            
            toast.className = `fixed bottom-6 right-6 px-6 py-4 rounded-[16px] ${colorBg} border ${colorBorder} text-white font-semibold text-sm shadow-lg animate-fade-in z-50 pointer-events-none`;
            toast.innerHTML = `
                <div class="flex items-center gap-3">
                    <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-info-circle'} text-lg"></i>
                    <span>${mensaje}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'fadeOut 0.3s ease-out forwards';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Poll para refrescar mesas abiertas y lista del sidebar cada 5s
        async function refrescarMesas() {
            try {
                const res = await fetch('/mesero/mesas/abiertas', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                const data = await res.json().catch(() => null);
                if (!res.ok || !data || !data.success) return;

                const totalElem = document.getElementById('txtTotalMesas');
                const abiertasElem = document.getElementById('txtMesasAbiertas');
                if (totalElem) totalElem.innerText = data.conteo_total;
                if (abiertasElem) abiertasElem.innerText = data.conteo_abiertas;

                const badge = document.getElementById('sidebarMesasActivas');
                if (badge) badge.innerText = data.conteo_abiertas;

                const list = document.getElementById('sidebarMesasList');
                if (list) {
                    list.innerHTML = '';
                    const mesasAbiertas = data.mesas_abiertas || [];
                    const mesasLibres = data.mesas_libres || [];
                    
                    // Detectar mesas recientemente liberadas
                    mesasLibres.forEach(m => {
                        const estadoAnterior = estadoMesasAnterior[m.id];
                        if (estadoAnterior === 'ocupada') {
                            // La mesa pasó de ocupada a disponible ¡Acaba de ser liberada!
                            mostrarNotificacion(`✓ Mesa ${m.numero} se abrió nuevamente con saldo $${(m.total_consumo || 0).toFixed(2)}`, 'success');
                        }
                    });
                    
                    // Actualizar estado anterior
                    [...mesasAbiertas, ...mesasLibres].forEach(m => {
                        estadoMesasAnterior[m.id] = m.estado;
                    });
                    
                    if (mesasAbiertas.length === 0 && mesasLibres.length === 0) {
                        // DISEÑO VACÍO PREMIUM
                        list.innerHTML = `
                            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center bg-[var(--bg-base)]">
                                <div class="w-20 h-20 rounded-[24px] bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-inner flex items-center justify-center mb-5 relative overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-b from-white/[0.04] to-transparent"></div>
                                    <i class="fas fa-layer-group text-2xl text-[var(--text-muted)] opacity-30 relative z-10"></i>
                                </div>
                                <h3 class="text-[14px] font-black text-[var(--text-main)] mb-1.5 tracking-tight">Sala Despejada</h3>
                                <p class="text-[10px] text-[var(--text-muted)] leading-relaxed max-w-[180px] font-medium opacity-70">El área se encuentra sin mesas activas.</p>
                            </div>
                        `;
                    } else {
                        // Mostrar PRIMERO mesas ocupadas (activas)
                        mesasAbiertas.forEach(m => {
                            list.appendChild(crearTarjetaMesa(m, false));
                        });
                        
                        // Luego mostrar mesas disponibles (recientemente liberadas) en AZUL
                        if (mesasLibres.length > 0) {
                            const seccionLibres = document.createElement('div');
                            seccionLibres.className = 'mt-2 pt-4 border-t border-white/[0.05]';
                            seccionLibres.innerHTML = '<p class="text-[10px] font-bold uppercase tracking-widest text-blue-400/60 px-2 mb-3">Mesas Reabierta</p>';
                            mesasLibres.forEach(m => {
                                seccionLibres.appendChild(crearTarjetaMesa(m, true));
                            });
                            list.appendChild(seccionLibres);
                        }
                    }
                }
            } catch (err) {
                console.error('Error refrescando mesas:', err);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            refrescarMesas();
            setInterval(refrescarMesas, 5000);
        });
    </script>
@include('mesero.modal-nueva-mesa')
</body>
</html>