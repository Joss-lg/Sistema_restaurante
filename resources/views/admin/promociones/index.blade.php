@extends('layouts.admin')

@section('title', 'Promociones | Ollintem Pro')

@section('content')
<div class="p-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-8 relative z-10">
    
    {{-- ENCABEZADO PREMIUM --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">
        <div class="space-y-3 max-w-2xl">
            <div class="inline-flex items-center gap-2 rounded-full bg-blue-500/10 px-4 py-2 text-[10px] font-black uppercase tracking-[0.35em] text-blue-500 shadow-inner">
                <i class="fas fa-tags"></i> Marketing y Ofertas
            </div>
            <h1 class="text-4xl font-black text-[var(--text-color)] tracking-tighter drop-shadow-sm">Promociones y Descuentos</h1>
            <p class="text-sm font-medium text-[var(--text-muted)] tracking-wide">Controla las ofertas activas, paquetes especiales y descuentos para tus clientes.</p>
        </div>
        
        <div class="flex flex-wrap gap-4 w-full xl:w-auto">
            <button class="group relative flex items-center gap-2 rounded-2xl bg-[var(--card-color)] border border-[var(--border-color)] px-6 py-3.5 text-xs font-black uppercase tracking-widest text-[var(--text-color)] transition-all hover:border-emerald-500/50 hover:bg-emerald-500/10 hover:text-emerald-500 shadow-sm">
                <i class="fas fa-history transition-transform group-hover:-rotate-45"></i>
                Historial
            </button>

            {{-- CORREGIDO: .agregar coincide con tu seeder --}}
            @if(auth()->user()->tienePermiso('promociones.agregar'))
                <button onclick="openModal('modalCrear')" class="group relative flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-500 px-6 py-3.5 text-xs font-black uppercase tracking-widest text-white transition-all hover:from-blue-500 hover:to-blue-400 shadow-[0_8px_20px_rgba(59,130,246,0.3)] hover:shadow-[0_8px_25px_rgba(59,130,246,0.5)] hover:-translate-y-0.5">
                    <i class="fas fa-plus transition-transform group-hover:rotate-90"></i>
                    Nueva Promo
                </button>
            @endif
        </div>
    </div>

    {{-- BARRA DE BÚSQUEDA Y ESTADÍSTICAS --}}
    <div class="glass-card rounded-[24px] p-4 flex flex-col md:flex-row justify-between items-center gap-4 border border-[var(--border-color)] shadow-lg">
        <div class="relative w-full md:max-w-md group">
            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-[var(--text-muted)] group-focus-within:text-blue-500 transition-colors">
                <i class="fas fa-search text-sm"></i>
            </div>
            <input type="text" placeholder="Buscar promoción por nombre..." class="w-full bg-[var(--input-bg)] border border-[var(--border-color)] rounded-xl py-3.5 pl-12 pr-4 text-sm font-medium text-[var(--text-color)] placeholder-[var(--text-muted)] focus:outline-none focus:border-blue-500/50 focus:ring-2 focus:ring-blue-500/20 transition-all shadow-inner">
        </div>

        <div class="flex items-center gap-6 px-4">
            <div class="text-right">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">Total Registradas</p>
                <p class="text-2xl font-black text-[var(--text-color)] leading-none mt-1">{{ $promociones->count() }}</p>
            </div>
            <div class="w-px h-10 bg-[var(--border-color)]"></div>
            <div class="text-left">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">Activas ahora</p>
                <p class="text-2xl font-black text-emerald-500 leading-none mt-1">{{ $promociones->where('esta_activa', true)->count() }}</p>
            </div>
        </div>
    </div>

    {{-- TABLERO DE PROMOCIONES --}}
    @if($promociones->isEmpty())
        <div class="glass-card rounded-[32px] p-20 text-center border border-[var(--border-color)] shadow-2xl flex flex-col items-center justify-center mt-8 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-blue-500/5 to-transparent pointer-events-none"></div>
            <div class="relative mb-6">
                <div class="absolute inset-0 bg-blue-500/20 blur-[40px] rounded-full"></div>
                <div class="w-24 h-24 bg-[var(--card-color)] rounded-3xl flex items-center justify-center text-[var(--text-muted)] border border-[var(--border-color)] shadow-inner relative z-10 group hover:border-blue-500/30 transition-colors">
                    <i class="fas fa-ticket-alt text-5xl text-blue-500/80 group-hover:text-blue-500 transition-colors"></i>
                </div>
            </div>
            <h2 class="text-3xl font-black text-[var(--text-color)] tracking-tight">Sin promociones activas</h2>
            <p class="mt-3 text-sm text-[var(--text-muted)] font-medium max-w-md">No tienes ninguna oferta configurada en el sistema. Crea una nueva promo para atraer más clientes.</p>
            
            {{-- CORREGIDO: .agregar --}}
            @if(auth()->user()->tienePermiso('promociones.agregar'))
                <button onclick="openModal('modalCrear')" class="mt-8 rounded-2xl bg-[var(--input-bg)] border border-[var(--border-color)] px-6 py-3 text-xs font-black uppercase tracking-widest text-[var(--text-color)] transition-all hover:bg-[var(--border-color)]">
                    Comenzar ahora
                </button>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($promociones as $promo)
                <article class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[24px] p-6 relative group transition-all duration-300 hover:-translate-y-1 hover:border-blue-500/30 hover:shadow-[0_20px_40px_-15px_rgba(0,0,0,0.3)] flex flex-col overflow-hidden">
                    
                    {{-- Brillo superior condicional --}}
                    <div class="absolute inset-x-0 top-0 h-1 {{ $promo->esta_activa ? 'bg-gradient-to-r from-emerald-400 to-teal-500' : 'bg-[var(--border-color)]' }}"></div>

                    {{-- HEADER DE LA TARJETA (Icono e Interruptor iOS) --}}
                    <div class="flex justify-between items-start mb-6 pt-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $promo->esta_activa ? 'bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-[0_0_20px_rgba(59,130,246,0.3)]' : 'bg-[var(--input-bg)] text-[var(--text-muted)] border border-[var(--border-color)]' }} transition-all duration-300">
                            <i class="fas fa-ticket-alt text-xl"></i>
                        </div>
                        
                        {{-- AJUSTADO: Se cambia .gestionar por .editar porque .gestionar no existe en tu seeder --}}
                        @if(auth()->user()->tienePermiso('promociones.reporte'))
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input id="togglePromo{{ $promo->id }}" type="checkbox" class="sr-only peer" {{ $promo->esta_activa ? 'checked' : '' }} onchange="togglePromo({{ $promo->id }})">
                                <div class="w-11 h-6 rounded-full bg-[var(--input-bg)] border border-[var(--border-color)] peer-checked:border-emerald-500 peer-checked:bg-emerald-500 transition-all duration-300 shadow-inner relative">
                                    <span class="absolute left-1 top-[2px] h-5 w-5 rounded-full bg-[var(--text-muted)] transition-all duration-300 peer-checked:left-[1.25rem] peer-checked:bg-white"></span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- CONTENIDO --}}
                    <div class="mb-6 flex-1">
                        <h3 class="text-xl font-black text-[var(--text-color)] tracking-tight leading-tight mb-1">{{ $promo->nombre }}</h3>
                        
                        @if($promo->descripcion)
                            <p class="text-xs font-medium text-[var(--text-muted)] line-clamp-2 mt-1 tracking-wide leading-relaxed">{{ $promo->descripcion }}</p>
                        @endif
                        
                        {{-- Valor Masivo --}}
                        <div class="mt-4 flex items-end gap-2">
                            <span class="bg-clip-text text-transparent bg-gradient-to-r {{ $promo->esta_activa ? 'from-blue-400 to-indigo-500' : 'from-[var(--text-muted)] to-gray-500' }} font-black text-4xl tracking-tighter">
                                {{ $promo->tipo_promocion == 'porcentaje' ? $promo->valor_descuento.'%' : '$'.$promo->valor_descuento }}
                            </span>
                            <span class="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1.5 pb-0.5">Descuento</span>
                        </div>
                    </div>

                    {{-- DÍAS DE LA SEMANA (Píldoras Premium) --}}
                    <div class="mb-6">
                        <p class="text-[9px] font-black uppercase tracking-[0.3em] text-[var(--text-muted)] mb-3">Días aplicables</p>
                        <div class="flex justify-between gap-1">
                            @php $dias = $promo->dias_semana ?? []; @endphp
                            @foreach(['L','M','M','J','V','S','D'] as $i => $d)
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-black transition-colors {{ in_array($i+1, $dias) ? 'bg-blue-500 text-white shadow-[0_0_10px_rgba(59,130,246,0.4)]' : 'bg-[var(--input-bg)] text-[var(--text-muted)] border border-[var(--border-color)]' }}">
                                    {{ $d }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- FOOTER DE TARJETA --}}
                    <div class="flex justify-between items-center pt-4 border-t border-[var(--border-color)]">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest {{ $promo->esta_activa ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-[var(--input-bg)] text-[var(--text-muted)] border border-[var(--border-color)]' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $promo->esta_activa ? 'bg-emerald-500 animate-pulse' : 'bg-[var(--text-muted)]' }}"></span>
                            {{ $promo->esta_activa ? 'Activa' : 'Inactiva' }}
                        </span>
                        
                        {{-- VALIDACIÓN COMPLETA DE EDICIÓN Y ELIMINACIÓN --}}
                        @if(auth()->user()->tienePermiso('promociones.editar'))
                            <button onclick="editPromo({{ $promo->id }})" class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-muted)] transition-all hover:bg-blue-500/10 hover:border-blue-500/30 hover:text-blue-500">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                        @endif

                        @if(auth()->user()->tienePermiso('promociones.eliminar'))
                            <button onclick="openDeleteModal({{ $promo->id }}, '{{ addslashes($promo->nombre) }}')" class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] text-[var(--text-muted)] transition-all hover:bg-rose-500/10 hover:border-rose-500/30 hover:text-rose-500">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        @endif
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

    function togglePromo(id) {
        console.log("Toggle promo: " + id);
    }
</script>
@endsection