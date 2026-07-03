@extends('layouts.admin')

@section('title', 'Promociones | Ollintem Pro')

@section('content')
<div class="p-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-8 relative z-10">
    
    {{-- ENCABEZADO PREMIUM --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">
        <div class="space-y-3 max-w-2xl">
            <div class="inline-flex items-center gap-2 rounded-full !bg-blue-500/10 px-4 py-2 text-[10px] font-black uppercase tracking-[0.35em] !text-blue-600 dark:!text-blue-400 shadow-inner">
                <i class="fas fa-tags"></i> Marketing y Ofertas
            </div>
            <h1 class="text-3xl md:text-4xl font-black !text-gray-900 dark:!text-white tracking-tighter drop-shadow-sm">Promociones y Descuentos</h1>
            <p class="text-sm font-medium !text-gray-500 dark:!text-gray-400 tracking-wide">Controla las ofertas activas, paquetes especiales y descuentos para tus clientes.</p>
        </div>

        <div>
            @if(auth()->user()->tienePermiso('promociones.agregar'))
                <button onclick="openModal('modalCrear')" class="group relative flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-3.5 text-xs font-black uppercase tracking-widest !text-white transition-all hover:from-blue-500 hover:to-blue-400 shadow-[0_8px_20px_rgba(59,130,246,0.3)] hover:shadow-[0_8px_25px_rgba(59,130,246,0.5)] hover:-translate-y-0.5 outline-none border-0">
                    <i class="fas fa-plus transition-transform group-hover:rotate-90"></i>
                    Nueva Promo
                </button>
            @endif
        </div>
    </div>

    {{-- BARRA DE BÚSQUEDA Y ESTADÍSTICAS --}}
    <div class="!bg-white dark:!bg-[#121318] rounded-[24px] p-4 flex flex-col md:flex-row justify-between items-center gap-4 border !border-gray-200 dark:!border-white/5 shadow-sm dark:shadow-lg transition-colors">
        <div class="relative w-full md:max-w-md group">
            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none !text-gray-400 group-focus-within:!text-blue-500 transition-colors">
                <i class="fas fa-search text-sm"></i>
            </div>
            <input type="text" placeholder="Buscar promoción por nombre..." class="w-full !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 rounded-xl py-3.5 pl-12 pr-4 text-sm font-medium !text-gray-900 dark:!text-white placeholder-gray-400 dark:placeholder-gray-600 focus:outline-none focus:!border-blue-500/50 transition-all shadow-inner">
        </div>

        <div class="flex items-center gap-6 px-4">
            <div class="text-right">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] !text-gray-500 dark:!text-gray-400">Total Registradas</p>
                <p class="text-2xl font-black !text-gray-900 dark:!text-white leading-none mt-1">{{ $promociones->count() }}</p>
            </div>
            <div class="w-px h-10 !bg-gray-200 dark:!bg-white/10"></div>
            <div class="text-left">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] !text-gray-500 dark:!text-gray-400">Activas ahora</p>
                <p class="text-2xl font-black !text-emerald-500 leading-none mt-1">{{ $promociones->where('esta_activa', true)->count() }}</p>
            </div>
        </div>
    </div>

    {{-- TABLERO DE PROMOCIONES --}}
    @if($promociones->isEmpty())
        <div class="!bg-white dark:!bg-[#121318] rounded-[32px] p-20 text-center border !border-gray-200 dark:!border-white/5 shadow-sm dark:shadow-2xl flex flex-col items-center justify-center mt-8 relative overflow-hidden transition-colors">
            <div class="absolute inset-0 bg-gradient-to-b from-blue-500/5 to-transparent pointer-events-none"></div>
            <div class="relative mb-6">
                <div class="absolute inset-0 bg-blue-500/20 blur-[40px] rounded-full"></div>
                <div class="w-24 h-24 !bg-blue-50 dark:!bg-[#1e2028] rounded-3xl flex items-center justify-center border !border-blue-100 dark:!border-white/5 shadow-inner relative z-10 group hover:!border-blue-500/30 transition-colors">
                    <i class="fas fa-ticket-alt text-5xl !text-blue-500/80 group-hover:!text-blue-500 transition-colors"></i>
                </div>
            </div>
            <h2 class="text-3xl font-black !text-gray-900 dark:!text-white tracking-tight">Sin promociones activas</h2>
            <p class="mt-3 text-sm !text-gray-500 dark:!text-gray-400 font-medium max-w-md">No tienes ninguna oferta configurada en el sistema. Crea una nueva promo para atraer más clientes.</p>
            
            @if(auth()->user()->tienePermiso('promociones.agregar'))
                <button onclick="openModal('modalCrear')" class="mt-8 rounded-2xl !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 px-6 py-3 text-xs font-black uppercase tracking-widest !text-gray-900 dark:!text-white transition-all hover:!bg-gray-100 dark:hover:!bg-white/10 outline-none">
                    Comenzar ahora
                </button>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($promociones as $promo)
                <article class="!bg-white dark:!bg-[#121318] border !border-gray-200 dark:!border-white/5 rounded-[24px] p-6 relative group transition-all duration-300 hover:-translate-y-1 hover:!border-blue-500/30 hover:shadow-xl dark:hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.3)] flex flex-col overflow-hidden">
                    
                    <div class="absolute inset-x-0 top-0 h-1 {{ $promo->esta_activa ? 'bg-gradient-to-r from-emerald-400 to-teal-500' : '!bg-gray-200 dark:!bg-white/5' }}"></div>

                    {{-- HEADER DE LA TARJETA --}}
                    <div class="flex justify-between items-start mb-6 pt-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $promo->esta_activa ? 'bg-gradient-to-br from-blue-500 to-indigo-600 !text-white shadow-[0_0_20px_rgba(59,130,246,0.3)]' : '!bg-gray-50 dark:!bg-black/40 !text-gray-400 border !border-gray-200 dark:!border-white/5' }} transition-all duration-300">
                            <i class="fas fa-ticket-alt text-xl"></i>
                        </div>
                        
                        @if(auth()->user()->tienePermiso('promociones.editar'))
                            {{-- SWITCH TIPO iOS PREMIUM --}}
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input id="togglePromo{{ $promo->id }}" type="checkbox" class="sr-only peer" {{ $promo->esta_activa ? 'checked' : '' }} onchange="togglePromo({{ $promo->id }})">
                                <div class="w-11 h-6 !bg-gray-300 dark:!bg-zinc-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:!border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:!bg-white after:border !border-gray-300 dark:after:!border-zinc-600 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:!bg-emerald-500 shadow-inner"></div>
                            </label>
                        @endif
                    </div>

                    {{-- CONTENIDO --}}
                    <div class="mb-6 flex-1">
                        <h3 class="text-xl font-black !text-gray-900 dark:!text-white tracking-tight leading-tight mb-1">{{ $promo->nombre }}</h3>
                        
                        @if($promo->descripcion)
                            <p class="text-xs font-medium !text-gray-500 dark:!text-gray-400 line-clamp-2 mt-1 tracking-wide leading-relaxed">{{ $promo->descripcion }}</p>
                        @endif
                        
                        <div class="mt-4 flex items-end gap-2">
                            <span class="bg-clip-text text-transparent bg-gradient-to-r {{ $promo->esta_activa ? 'from-blue-500 to-indigo-500 dark:from-blue-400 dark:to-indigo-500' : 'from-gray-400 to-gray-500 dark:from-gray-500 dark:to-gray-600' }} font-black text-4xl tracking-tighter">
                                @if($promo->tipo_promocion === 'dos_por_uno')
                                    2x1
                                @elseif($promo->tipo_promocion === 'combo')
                                    Combo
                                @elseif($promo->tipo_promocion === 'porcentaje')
                                    {{ (int)$promo->valor_descuento }}%
                                @else
                                    ${{ number_format($promo->valor_descuento, 2) }}
                                @endif
                            </span>
                            <span class="text-[10px] font-black uppercase tracking-widest !text-gray-400 dark:!text-gray-500 mb-1.5 pb-0.5">Beneficio</span>
                        </div>
                    </div>

                    {{-- DÍAS DE LA SEMANA --}}
                    <div class="mb-6">
                        <p class="text-[9px] font-black uppercase tracking-[0.3em] !text-gray-400 dark:!text-gray-500 mb-3">Días aplicables</p>
                        <div class="flex justify-between gap-1">
                            @php
                                $dias = $promo->dias_semana;
                                if (is_string($dias)) {
                                    $decode = json_decode($dias, true);
                                    if (is_string($decode)) {
                                        $decode = json_decode($decode, true);
                                    }
                                    $dias = $decode;
                                }
                                $dias = is_array($dias) ? $dias : [];
                            @endphp
                            @foreach(['L','M','M','J','V','S','D'] as $i => $d)
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-black transition-colors {{ in_array($i+1, $dias) ? '!bg-blue-500 !text-white shadow-[0_0_10px_rgba(59,130,246,0.4)]' : '!bg-gray-50 dark:!bg-black/40 !text-gray-400 dark:!text-gray-500 border !border-gray-200 dark:!border-white/5' }}">
                                    {{ $d }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- FOOTER DE TARJETA --}}
                    <div class="flex justify-between items-center pt-4 border-t !border-gray-100 dark:!border-white/5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest {{ $promo->esta_activa ? '!bg-emerald-50 dark:!bg-emerald-500/10 !text-emerald-600 dark:!text-emerald-500 border !border-emerald-200 dark:!border-emerald-500/20' : '!bg-gray-50 dark:!bg-black/40 !text-gray-500 border !border-gray-200 dark:!border-white/5' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $promo->esta_activa ? '!bg-emerald-500 animate-pulse' : '!bg-gray-400 dark:!bg-gray-500' }}"></span>
                            {{ $promo->esta_activa ? 'Activa' : 'Inactiva' }}
                        </span>
                        
                        <div class="flex items-center gap-2">
                            @if(auth()->user()->tienePermiso('promociones.editar'))
                                <button onclick="editPromo({{ $promo->id }})" class="flex h-8 w-8 items-center justify-center rounded-lg !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 !text-gray-400 dark:!text-gray-500 transition-all hover:!bg-blue-50 dark:hover:!bg-blue-500/10 hover:!border-blue-200 dark:hover:!border-blue-500/30 hover:!text-blue-500 outline-none">
                                    <i class="fas fa-pen text-xs"></i>
                                </button>
                            @endif

                            @if(auth()->user()->tienePermiso('promociones.eliminar'))
                                <button onclick="openDeleteModal({{ $promo->id }}, '{{ addslashes($promo->nombre) }}')" class="flex h-8 w-8 items-center justify-center rounded-lg !bg-gray-50 dark:!bg-black/40 border !border-gray-200 dark:!border-white/5 !text-gray-400 dark:!text-gray-500 transition-all hover:!bg-rose-50 dark:hover:!bg-rose-500/10 hover:!border-rose-200 dark:hover:!border-rose-500/30 hover:!text-rose-500 outline-none">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

@include('admin.promociones.modal-crear')
@include('admin.promociones.modal-editar')
@include('admin.promociones.modal-eliminar')

<script>
    function openModal(id) {
        const m = document.getElementById(id);
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function closeModal(id) {
        const m = document.getElementById(id);
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

    function guardarPromocion(event) {
        event.preventDefault();

        const form = document.getElementById('formCrearPromocion');
        const btn = document.getElementById('btn-guardar-promocion');
        const formData = new FormData(form);

        btn.disabled = true;
        btn.textContent = 'GUARDANDO...';

        fetch("{{ route('admin.promociones.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('modalCrear');
                form.reset();
                window.location.reload();
            } else {
                alert(data.message || 'Error al guardar.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error inesperado.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'GUARDAR PROMOCIÓN';
        });
    }

    function togglePromo(id) {
        const checkbox = document.getElementById(`togglePromo${id}`);
        
        const formData = new FormData();
        formData.append('_method', 'PUT'); 
        formData.append('toggle_status', '1');
        formData.append('esta_activa', checkbox.checked ? '1' : '0');

       fetch(`/promociones/${id}`, {
            method: 'POST', 
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en el servidor');
            return response.json();
        })
        .then(data => {
            if(data.success){
                window.location.reload(); 
            }
        })
        .catch(error => {
            console.error(error);
            checkbox.checked = !checkbox.checked;
            alert('No se pudo cambiar el estado de la promoción.');
        });
    }

    function editPromo(id) {
        fetch(`/promociones/${id}/edit`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            const promo = data.promocion;
            const form = document.getElementById('formEditarPromocion');
            
            form.action = `/promociones/${id}`;
            form.querySelector('[name="nombre"]').value = promo.nombre;
            form.querySelector('[name="descripcion"]').value = promo.descripcion || '';
            form.querySelector('[name="tipo_promocion"]').value = promo.tipo_promocion;
            form.querySelector('[name="valor_descuento"]').value = promo.valor_descuento;
            form.querySelector('[name="fecha_inicio"]').value = promo.fecha_inicio;
            form.querySelector('[name="fecha_fin"]').value = promo.fecha_fin;
            document.getElementById('edit_esta_activa').checked = (promo.esta_activa == 1);
            
            openModal('modalEditar');
        });
    }

    document.getElementById('formEditarPromocion').addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const data = await response.json();
            if (data.success) location.reload();
            else alert(data.message);
        } catch (err) { alert('Error al actualizar'); }
    });
</script>
@endsection