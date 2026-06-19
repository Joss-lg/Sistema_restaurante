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
            /* MODO OSCURO: Estilo Pro Studio */
            --bg-base: #09090b; 
            --bg-panel: #18181b; 
            --border-color: rgba(255, 255, 255, 0.08); 
            --border-highlight: rgba(255, 255, 255, 0.12);
            --text-main: #f4f4f5; 
            --text-muted: #a1a1aa; 
            --accent: #3b82f6; 
            --input-bg: #09090b;
        }

        body.modo-crema {
            /* MODO CREMA */
            --bg-base: #f3f4f6; 
            --bg-panel: #ffffff; 
            --border-color: rgba(0, 0, 0, 0.08);
            --border-highlight: rgba(0, 0, 0, 0.15);
            --text-main: #111827; 
            --text-muted: #6b7280; 
            --accent: #2563eb; 
            --input-bg: #f9fafb; 
        }

        /* Aniquilación total de las barras de scroll */
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

        aside, main, footer { border-color: var(--border-color) !important; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px) translateX(-20px); }
            to { opacity: 1; transform: translateY(0) translateX(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0) translateX(0); }
            to { opacity: 0; transform: translateY(20px) translateX(-20px); }
        }
        .animate-fade-in { animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
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
                icon.className = esCrema 
                    ? 'fas fa-moon text-blue-500 text-[14px] drop-shadow-sm' 
                    : 'fas fa-sun text-amber-400 text-[14px] drop-shadow-sm';
            }
        }
        document.addEventListener('DOMContentLoaded', () => actualizarIcono(document.body.classList.contains('modo-crema')));
    </script>

    <script>
        let estadoMesasAnterior = {};

        // ========================================================
        // FUNCIÓN JS ACTUALIZADA AL DISEÑO LUJO MÁXIMO (Sin Mesas Libres)
        // ========================================================
        function crearTarjetaMesa(m) {
            const a = document.createElement('a');
            a.href = `/mesero/comanda/${m.id}`;
            
            const totalCons = (m.total_consumo || 0).toFixed(2);
            
            let colorEstado = 'bg-emerald-500';
            let sombraEstado = 'shadow-[0_0_8px_rgba(16,185,129,0.6)]';
            let colorTexto = 'group-hover:text-emerald-400';
            let colorTotal = 'text-emerald-400 dark:text-emerald-300';
            let hoverBorde = 'hover:border-emerald-500/40';
            
            if(m.estado === 'sucia') {
                colorEstado = 'bg-amber-500';
                sombraEstado = 'shadow-[0_0_8px_rgba(245,158,11,0.6)]';
                colorTexto = 'group-hover:text-amber-400';
                hoverBorde = 'hover:border-amber-500/40';
            } else if(m.estado === 'por_cobrar') {
                colorEstado = 'bg-purple-500';
                sombraEstado = 'shadow-[0_0_8px_rgba(168,85,247,0.6)]';
                colorTexto = 'group-hover:text-purple-400';
                hoverBorde = 'hover:border-purple-500/40';
            }

            a.className = `group block relative rounded-[20px] bg-[var(--bg-panel)] border border-[var(--border-color)] p-5 transition-all duration-300 ${hoverBorde} hover:-translate-y-1 hover:shadow-2xl outline-none active:scale-[0.98] z-10 mb-4`;
            
            a.innerHTML = `
                <div class="absolute inset-0 rounded-[20px] padding-[1px] bg-gradient-to-b from-white/5 to-black/10 -z-10 [mask-image:linear-gradient(#fff_0_0)_content-box,linear-gradient(#fff_0_0)] [mask-composite:xor] pointer-events-none opacity-50 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex items-center justify-between mb-5 gap-3 relative z-10">
                    <div class="flex flex-col overflow-hidden">
                        <div class="flex items-center gap-3.5">
                            <div class="relative flex h-2 w-2 shrink-0 items-center justify-center">
                                <span class="animate-ping absolute inline-flex h-4 w-4 rounded-full ${colorEstado} opacity-30"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 ${colorEstado} ${sombraEstado}"></span>
                            </div>
                            <h3 class="text-[16px] font-black text-[var(--text-main)] tracking-tight leading-none ${colorTexto} transition-colors duration-300 uppercase">Mesa ${m.numero}</h3>
                        </div>
                    </div>
                    <div class="w-8 h-8 shrink-0 rounded-[10px] bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] group-hover:${colorEstado} group-hover:text-white group-hover:border-transparent transition-all duration-400 shadow-inner group-active:scale-90">
                        <i class="fas fa-chevron-right text-[10px] transform group-hover:translate-x-0.5 transition-transform"></i>
                    </div>
                </div>
                <div class="pt-4 border-t border-[var(--border-color)] flex items-center justify-between relative z-10">
                    <div class="flex items-center gap-6">
                        <div class="flex flex-col">
                            <span class="text-[8px] font-bold uppercase tracking-[0.25em] text-[var(--text-muted)] opacity-70 mb-1.5 transition-colors duration-300">Pax</span>
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-user text-[var(--text-muted)] opacity-50 text-[10px] transition-colors duration-300"></i>
                                <span class="text-[12px] font-black text-[var(--text-main)] transition-colors duration-300">${m.capacidad ?? 0}</span>
                            </div>
                        </div>
                        <div class="w-[1px] h-6 bg-[var(--border-color)] transition-colors duration-300"></div>
                        <div class="flex flex-col">
                            <span class="text-[8px] font-bold uppercase tracking-[0.25em] text-[var(--text-muted)] opacity-70 mb-1.5 transition-colors duration-300">Tiempo</span>
                            <div class="flex items-center gap-1.5">
                                <i class="far fa-clock text-[var(--text-muted)] opacity-50 text-[10px] transition-colors duration-300"></i>
                                <span class="text-[12px] font-black text-[var(--text-main)] transition-colors duration-300">0m</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-[8px] font-bold uppercase tracking-[0.25em] text-[var(--text-muted)] opacity-70 mb-1.5 transition-colors duration-300">Total</span>
                        <span class="text-[15px] font-black ${colorTotal} tracking-tighter leading-none transition-colors duration-300">$ ${totalCons}</span>
                    </div>
                </div>
            `;
            return a;
        }

        function mostrarNotificacion(mensaje, tipo = 'info') {
            const toast = document.createElement('div');
            const colorBg = tipo === 'success' ? 'bg-emerald-500' : 'bg-blue-600';
            
            toast.className = `fixed bottom-6 right-6 px-6 py-4 rounded-[16px] ${colorBg} text-white font-bold text-sm shadow-[0_10px_30px_rgba(0,0,0,0.5)] animate-fade-in z-50 pointer-events-none`;
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

        async function refrescarMesas() {
            try {
                const res = await fetch('/mesero/mesas/abiertas', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                const data = await res.json().catch(() => null);
                if (!res.ok || !data || !data.success) return;

                const totalElem = document.getElementById('txtTotalMesas');
                const abiertasElem = document.getElementById('txtMesasAbiertas');
                
                const list = document.getElementById('sidebarMesasList');
                if (list) {
                    list.innerHTML = '';
                    const mesasAbiertas = data.mesas_abiertas || [];
                    const mesasLibres = data.mesas_libres || [];
                    
                    // Actualizar métricas del panel principal
                    if (totalElem) totalElem.innerText = mesasAbiertas.length;
                    if (abiertasElem) abiertasElem.innerText = mesasAbiertas.length;
                    
                    // Notificación silenciosa cuando se desocupa una mesa tuya
                    mesasLibres.forEach(m => {
                        const estadoAnterior = estadoMesasAnterior[m.id];
                        if (estadoAnterior === 'ocupada') {
                            mostrarNotificacion(`✓ Mesa ${m.numero} liberada con éxito.`, 'success');
                        }
                    });
                    
                    [...mesasAbiertas, ...mesasLibres].forEach(m => { estadoMesasAnterior[m.id] = m.estado; });
                    
                    // Mostrar estado vacío SOLO si no tienes mesas activas
                    if (mesasAbiertas.length === 0) {
                        list.innerHTML = `
                            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center bg-[var(--bg-base)] transition-colors duration-300 h-full">
                                <div class="w-24 h-24 rounded-full bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-inner flex items-center justify-center mb-6 opacity-60 transition-all duration-300">
                                    <i class="fas fa-folder-open text-3xl text-[var(--text-muted)] transition-colors duration-300"></i>
                                </div>
                                <h3 class="text-base font-black text-[var(--text-main)] mb-1.5 tracking-tight transition-colors duration-300">Sala Despejada</h3>
                                <p class="text-[12px] text-[var(--text-muted)] leading-relaxed max-w-[220px] font-medium opacity-80 transition-colors duration-300">Actualmente no tienes mesas activas en tu turno.</p>
                                <button onclick="abrirModalMesa()" class="mt-6 rounded-[14px] bg-[var(--text-main)] text-[var(--bg-panel)] px-6 py-3 text-[11px] font-black uppercase tracking-widest hover:opacity-80 transition-opacity outline-none shadow-md">Abrir mesa nueva</button>
                            </div>
                        `;
                    } else {
                        // SOLAMENTE dibujamos tus mesas activas. Cero mesas disponibles.
                        mesasAbiertas.forEach(m => { list.appendChild(crearTarjetaMesa(m)); });
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