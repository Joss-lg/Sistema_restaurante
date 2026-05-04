@extends('layouts.admin')

@section('title', 'Caja | Ollintem Pro')

@section('content')
<div class="p-6 lg:p-8 w-full max-w-[1600px] mx-auto space-y-8">
    
    <!-- ENCABEZADO Y RESUMEN -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white italic uppercase tracking-tighter">Panel de Caja</h1>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-widest">Selecciona una mesa para procesar el pago</p>
        </div>
        
        <div class="flex gap-4">
            <div class="bg-[#141417] border border-white/5 px-6 py-3 rounded-xl">
                <p class="text-[9px] font-black text-gray-600 uppercase mb-1">Total Abierto</p>
                <p class="text-xl font-bold text-green-500">$4,250.00</p>
            </div>
            <div class="bg-[#141417] border border-white/5 px-6 py-3 rounded-xl">
                <p class="text-[9px] font-black text-gray-600 uppercase mb-1">Mesas Activas</p>
                <p class="text-xl font-bold text-blue-500">4</p>
            </div>
        </div>
    </div>

    <!-- FILTROS RÁPIDOS (Estilo Ollintem Pro) -->
    <div class="flex gap-2 border-b border-white/5 pb-4">
        <button class="px-6 py-2 bg-blue-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest">Todas</button>
        <button class="px-6 py-2 bg-white/5 text-gray-500 hover:text-white text-[10px] font-black rounded-lg uppercase tracking-widest transition-colors">Solo Activas</button>
        <button class="px-6 py-2 bg-white/5 text-gray-500 hover:text-white text-[10px] font-black rounded-lg uppercase tracking-widest transition-colors">Solo Libres</button>
    </div>

    <!-- CUADRÍCULA DE TODAS LAS MESAS -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
        
        <!-- EJEMPLO MESA CON CONSUMO (La que te lleva a cobrar) -->
        <a href="{{ route('admin.caja.cobrar', 1) }}" class="group relative bg-[#141417] border border-blue-500/30 p-6 rounded-2xl transition-all hover:-translate-y-1 hover:border-blue-500 shadow-lg shadow-blue-900/10">
            <div class="absolute top-3 right-3">
                <span class="flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                </span>
            </div>
            
            <div class="text-center space-y-2">
                <i class="fas fa-couch text-2xl text-blue-500 mb-2"></i>
                <h3 class="text-white font-bold text-lg">Mesa 4T4</h3>
                <p class="text-green-500 font-black text-sm">$771.40</p>
                <p class="text-[9px] text-gray-500 font-bold uppercase tracking-tighter">Click para cobrar</p>
            </div>
        </a>

        <!-- MESA OCUPADA PERO NO SELECCIONADA -->
        @for ($i = 1; $i <= 3; $i++)
        <div class="bg-[#141417] border border-white/5 p-6 rounded-2xl opacity-80">
            <div class="text-center space-y-2">
                <i class="fas fa-couch text-2xl text-gray-700 mb-2"></i>
                <h3 class="text-gray-400 font-bold text-lg">Mesa {{ $i }}</h3>
                <p class="text-gray-600 font-black text-sm">$---.--</p>
                <span class="inline-block px-2 py-0.5 bg-white/5 rounded text-[8px] text-gray-600 font-bold uppercase">En espera</span>
            </div>
        </div>
        @endfor

        <!-- MESAS VACÍAS/LIBRES -->
        @for ($i = 4; $i <= 12; $i++)
        <div class="bg-[#0f0f12] border border-dashed border-white/5 p-6 rounded-2xl group hover:border-white/10 transition-colors">
            <div class="text-center space-y-2">
                <i class="fas fa-chair text-2xl text-gray-900 group-hover:text-gray-800 transition-colors mb-2"></i>
                <h3 class="text-gray-800 group-hover:text-gray-700 font-bold text-lg transition-colors italic">Mesa {{ $i }}</h3>
                <p class="text-[9px] text-gray-800 font-black uppercase tracking-widest group-hover:text-gray-700 transition-colors">Libre</p>
            </div>
        </div>
        @endfor

    </div>
</div>
@endsection