@extends('layouts.admin')

@section('title', 'Caja | Ollintem Pro')

@section('content')
<div class="p-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-8">
    
    <!-- ENCABEZADO Y RESUMEN DINÁMICO -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[var(--text-color)] italic uppercase tracking-tighter">Panel de Caja</h1>
            <p class="text-[var(--text-muted)] text-xs font-bold uppercase tracking-widest">Selecciona una mesa para procesar el pago</p>
        </div>
        
        <div class="flex gap-4 flex-wrap">
            <div class="bg-[var(--card-color)] border border-[var(--border-color)] px-6 py-3 rounded-2xl shadow-lg shadow-blue-900/10">
                <p class="text-[9px] font-black text-[var(--text-muted)] uppercase mb-1">Total Abierto</p>
                <p class="text-xl font-bold text-green-500">${{ number_format($totalAbierto ?? 0, 2) }}</p>
            </div>
            <div class="bg-[var(--card-color)] border border-[var(--border-color)] px-6 py-3 rounded-2xl shadow-lg shadow-slate-900/10">
                <p class="text-[9px] font-black text-[var(--text-muted)] uppercase mb-1">Mesas Activas</p>
                <p class="text-xl font-bold text-blue-500">{{ $mesasActivas ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- FILTROS RÁPIDOS -->
    <div class="flex gap-2 border-b border-[var(--border-color)] pb-4">
        <button class="px-6 py-2 bg-blue-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest">Todas</button>
        <button class="px-6 py-2 bg-[var(--card-color)] border border-[var(--border-color)] text-[var(--text-color)] hover:text-white text-[10px] font-black rounded-lg uppercase tracking-widest transition-colors">Solo Activas</button>
        <button class="px-6 py-2 bg-[var(--card-color)] border border-[var(--border-color)] text-[var(--text-color)] hover:text-white text-[10px] font-black rounded-lg uppercase tracking-widest transition-colors">Solo Libres</button>
    </div>

    <!-- CUADRÍCULA DE MESAS REALES -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
        
        @forelse ($mesas as $mesa)
            
            @if($mesa->estado === 'ocupada')
                <!-- MESA OCUPADA / ACTIVA -->
                <a href="{{ route('admin.caja.cobrar', $mesa->id) }}" class="group relative bg-[var(--card-color)] border border-blue-500/30 p-6 rounded-2xl transition-all hover:-translate-y-1 hover:border-blue-500 shadow-xl shadow-blue-900/10 cursor-pointer">
                    <div class="absolute top-3 right-3">
                        <span class="flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                        </span>
                    </div>
                    
                    <div class="text-center space-y-3">
                        <i class="fas fa-couch text-2xl text-blue-400 mb-2"></i>
                        <h3 class="text-[var(--text-color)] font-bold text-lg">Mesa {{ $mesa->numero }}</h3>
                        
                        <!-- TOTAL DINÁMICO -->
                        <p class="text-green-400 font-black text-base">${{ number_format($mesa->total_consumo, 2) }}</p>
                        
                        <!-- PRODUCTOS PENDIENTES -->
                        @if($mesa->productos && count($mesa->productos) > 0)
                            <div class="bg-[var(--card-color)]/70 rounded-lg p-2 mt-2 border border-blue-500/20 text-left max-h-[80px] overflow-y-auto">
                                <p class="text-[8px] text-[var(--text-muted)] font-bold uppercase tracking-tight mb-1">Productos:</p>
                                @foreach($mesa->productos as $detalle)
                                    <div class="text-[9px] text-[var(--text-color)] py-0.5 border-b border-[var(--border-color)] last:border-0">
                                        <span class="font-semibold text-blue-400">{{ $detalle->cantidad }}x</span>
                                        <span class="truncate">{{ $detalle->producto->nombre ?? 'Sin nombre' }}</span>
                                        @if($detalle->notas)
                                            <p class="text-[8px] text-yellow-400 italic">{{ $detalle->notas }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        
                        <p class="text-[9px] text-[var(--text-muted)] font-bold uppercase tracking-tighter mt-2">Click para cobrar</p>
                    </div>
                </a>
            @else
                <!-- MESA VACÍA / LIBRE -->
                <div class="bg-[var(--card-color)] border border-dashed border-[var(--border-color)] p-6 rounded-2xl group hover:border-blue-500 transition-colors">
                    <div class="text-center space-y-2">
                        <i class="fas fa-chair text-2xl text-blue-400 group-hover:text-blue-500 transition-colors mb-2"></i>
                        <h3 class="text-[var(--text-color)] group-hover:text-[var(--text-color)] font-bold text-lg transition-colors italic">Mesa {{ $mesa->numero }}</h3>
                        <p class="text-[9px] text-[var(--text-muted)] font-black uppercase tracking-widest group-hover:text-[var(--text-muted)] transition-colors">Libre</p>
                    </div>
                </div>
            @endif

        @empty
            <div class="col-span-full text-center py-10">
                <p class="text-[var(--text-muted)] font-bold uppercase tracking-widest text-xs">No tienes mesas registradas en la base de datos.</p>
            </div>
        @endforelse

    </div>
</div>
@endsection