<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Comanda | Ollintem Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            /* MODO OSCURO: Estilo Pro Studio (Profundo y elegante) */
            --bg-base: #09090b;
            --bg-panel: #18181b;
            --border-color: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(255, 255, 255, 0.12);
            --text-main: #f4f4f5;
            --text-muted: #a1a1aa;
            --accent: #3b82f6;
            --input-bg: #09090b;
            --card-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.7);
            --hover-bg: rgba(255, 255, 255, 0.04);
        }

        body.modo-crema {
            /* MODO CREMA: Fresco, nítido y con alto contraste */
            --bg-base: #f3f4f6;
            --bg-panel: #ffffff;
            --border-color: rgba(0, 0, 0, 0.08);
            --border-highlight: rgba(0, 0, 0, 0.15);
            --text-main: #111827;
            --text-muted: #6b7280;
            --accent: #2563eb;
            --input-bg: #f9fafb;
            --card-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05), 0 2px 6px -2px rgba(0, 0, 0, 0.025);
            --hover-bg: rgba(0, 0, 0, 0.03);
        }

        body {
            background-color: var(--bg-base);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            transition: background-color 0.4s ease, color 0.4s ease;
        }

        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        .hide-scroll::-webkit-scrollbar { width: 0; height: 0; display: none !important; }

        @keyframes fade-in-up { 0% { opacity: 0; transform: translateY(5px); } 100% { opacity: 1; transform: translateY(0); } }
        .animate-item { animation: fade-in-up 0.2s ease-out forwards; }

        /* Toast Premium */
        .toast-wrapper { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.75rem; align-items: flex-end; pointer-events: none; }
        .toast-panel { min-width: 18rem; max-width: 28rem; pointer-events: auto; background: var(--bg-panel); color: var(--text-main); border: 1px solid var(--border-color); border-radius: 16px; box-shadow: var(--card-shadow); padding: 1rem 1.25rem; opacity: 0; transform: translateY(10px); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); display: flex; gap: 0.85rem; align-items: center; }
        .toast-panel.show { opacity: 1; transform: translateY(0); }
        .toast-panel .toast-icon { display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .toast-panel.success .toast-icon { color: #10b981; }
        .toast-panel.error .toast-icon { color: #ef4444; }
        .toast-panel.info .toast-icon { color: #3b82f6; }
        .toast-panel strong { display: block; font-weight: 700; font-size: 0.95rem; }
        .toast-panel span { color: var(--text-muted); font-size: 0.85rem; }

        button { outline: none !important; -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="h-screen w-full flex selection:bg-[#3b82f6]/30">

    <script>if (localStorage.getItem('tema-ollintem') === 'crema') document.body.classList.add('modo-crema');</script>
    <div id="toastContainer" class="toast-wrapper" aria-live="polite" aria-atomic="true"></div>

    @include('mesero.partials.ticket-sidebar')
    @include('mesero.partials.catalogo')
    @include('mesero.partials.modales')

    @php
        $platillosEnviadosParaJs = ($platillosEnviados ?? collect())->map(function ($item) {
            return [
                'nombre'   => $item->nombre ?? 'Platillo',
                'cantidad' => $item->cantidad ?? 1,
                'precio'   => $item->precio ?? 0,
                'estado'   => $item->estado ?? 'enviado',
            ];
        })->values();

        $rutaPromociones = \Illuminate\Support\Facades\Route::has('admin.promociones.index')
            ? route('admin.promociones.index')
            : '#';
    @endphp
    <script>
        window.ComandaConfig = {
            csrfToken: @json(csrf_token()),
            mesa: {
                id: {{ $mesa->id ?? 1 }},
                numero: @json($mesa->numero ?? '12M'),
                capacidad: {{ $mesa->capacidad ?? 4 }}
            },
            esCapitan: @json($esCapitan ?? false),
            categorias: @json($categorias ?? []),
            productos: @json($productos ?? []),
            platillosEnviados: @json($platillosEnviadosParaJs),
            rutas: {
                dashboard: @json(route('mesero.dashboard')),
                promociones: @json($rutaPromociones),
                capitanVerify: '/mesero/capitan/verify',
                comandaEnviar: '/mesero/comanda/enviar',
            }
        };
    </script>
    <script src="{{ asset('js/comanda-pos.js') }}"></script>
</body>
</html>