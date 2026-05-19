@extends('layouts.admin')

@section('title', 'Promociones | Ollintem Pro')

@section('content')
<div class="min-h-screen bg-[var(--bg-color)] text-[var(--text-color)] p-8 font-sans">
    
    <div class="flex justify-between items-center mb-10">
        <div>
            <h1 class="text-4xl font-bold tracking-tight text-[var(--text-color)]">Promociones y Descuentos</h1>
            <p class="text-[var(--text-muted)] mt-2 text-lg">Control de ofertas, paquetes y descuentos activos</p>
        </div>
        
        <div class="flex gap-4">
            <button class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-emerald-700 transition shadow-lg shadow-emerald-900/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Historial
            </button>

            @if(auth()->user()->tienePermiso('promociones', 'crear'))
                <button onclick="openModal('modalCrear')" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-semibold flex items-center gap-2 hover:bg-blue-700 transition shadow-lg shadow-blue-900/40">
                    <span class="text-2xl leading-none">+</span> Nueva Promo
                </button>
            @endif
        </div>
    </div>

    <div class="relative max-w-md mb-8">
        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-[var(--text-muted)]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </span>
        <input type="text" placeholder="Buscar promoción por nombre..." class="w-full bg-[var(--card-color)] border border-[var(--border-color)] rounded-2xl py-3 pl-12 pr-4 text-[var(--text-color)] placeholder-[var(--text-muted)] focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
    </div>

    <div class="bg-[var(--card-color)] border border-[var(--border-color)] rounded-[2.5rem] p-8">
        <div class="flex items-center gap-3 mb-8 border-b border-[var(--border-color)] pb-6">
            <div class="bg-blue-600/20 p-2 rounded-lg text-blue-500">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M17.707 9.293l-5-5a1 1 0 00-1.414 0l-5 5A1 1 0 007 11h1v3a2 2 0 002 2h4a2 2 0 002-2v-3h1a1 1 0 00.707-1.707z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-[var(--text-color)]">Existencias <span class="text-[var(--text-muted)] font-normal ml-2">| {{ $promociones->count() }} registradas</span></h2>
        </div>

        @if($promociones->isEmpty())
            <div class="text-center py-20">
                <p class="text-[var(--text-muted)] text-xl italic">No hay promociones registradas en el sistema.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($promociones as $promo)
                <div class="bg-[var(--card-color)]/50 border border-[var(--border-color)] rounded-[2rem] p-6 hover:border-blue-500/40 transition relative group backdrop-blur-sm">
                    
                    <div class="absolute top-6 right-6">
                        @if(auth()->user()->tienePermiso('promociones', 'gestionar'))
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" {{ $promo->esta_activa ? 'checked' : '' }} onchange="togglePromo({{ $promo->id }})">
                                <div class="w-11 h-6 bg-[var(--border-color)] rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                            </label>
                        @endif
                    </div>

                    <div class="flex items-center gap-4 mb-4">
                        <div class="bg-blue-900/20 p-3 rounded-2xl text-blue-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 7h.01M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-[var(--text-color)]">{{ $promo->nombre }}</h3>
                            <span class="text-blue-400 text-sm font-semibold">{{ $promo->tipo_promocion == 'porcentaje' ? $promo->valor_descuento.'%' : $promo->tipo_promocion }}</span>
                        </div>
                    </div>

                    <div class="flex gap-1.5 mb-6">
                        @php $dias = json_decode($promo->dias_semana) ?? []; @endphp
                        @foreach(['L','M','M','J','V','S','D'] as $i => $d)
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[10px] font-bold border {{ in_array($i+1, $dias) ? 'bg-blue-600 border-blue-500 text-white' : 'border-[var(--border-color)] text-[var(--text-muted)]' }}">
                                {{ $d }}
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-between items-center pt-4 border-t border-[var(--border-color)]">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase {{ $promo->esta_activa ? 'bg-emerald-900/20 text-emerald-500' : 'bg-red-900/20 text-red-500' }}">
                            {{ $promo->esta_activa ? '● Activa' : '○ Inactiva' }}
                        </span>
                        
                        <div class="flex gap-2">
                            @if(auth()->user()->tienePermiso('promociones', 'editar'))
                                <button onclick="editPromo({{ $promo->id }})" class="p-2 bg-[var(--border-color)] hover:bg-blue-600/20 rounded-xl transition text-[var(--text-muted)] hover:text-blue-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@include('admin.promociones.modal-crear')
@include('admin.promociones.modal-editar')

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
        // Aquí iría tu fetch para activar/desactivar rápido
        console.log("Toggle promo: " + id);
    }
</script>
@endsection