@extends('layouts.admin')

@section('title', 'Alimentos | Ollintem Pro')

@section('header-title', 'Gestión de Alimentos')
@section('header-subtitle', 'Administra el menú y las recetas de los platillos')

@section('content')
{{-- CONTENEDOR PRINCIPAL --}}
<div class="p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col bg-gray-950">

    {{-- CABECERA Y BOTÓN --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-100 tracking-tight">Menú de Alimentos</h1>
            <p class="text-gray-400 mt-1">Gestiona los platillos del restaurante</p>
        </div>

        <div class="relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl blur opacity-30 group-hover:opacity-60 transition duration-150"></div>
            <button onclick="openModalAlimento()" class="relative flex items-center gap-2.5 bg-[#3B82F6] hover:bg-blue-600 text-white px-6 py-3 rounded-xl font-bold transition duration-150 shadow-lg shadow-blue-900/20">
                <i class="fas fa-plus text-sm"></i>
                <span>Agregar Platillo</span>
            </button>
        </div>
    </div>

    {{-- CARDS DE ESTADÍSTICAS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-[#111827] rounded-[2rem] p-8 shadow-xl border border-blue-900/30 flex flex-col justify-between min-h-[160px] relative overflow-hidden group">
            <div class="absolute right-6 top-6 text-blue-900/20 text-5xl group-hover:scale-110 transition-transform"><i class="fas fa-utensils"></i></div>
            <span class="text-sm font-bold text-gray-500 uppercase tracking-widest">Total Platillos</span>
            <span class="text-6xl font-black text-blue-500 tracking-tighter">16</span>
        </div>

        <div class="bg-[#111827] rounded-[2rem] p-8 shadow-xl border border-green-900/30 flex flex-col justify-between min-h-[160px] relative overflow-hidden group">
            <div class="absolute right-6 top-6 text-green-900/20 text-5xl group-hover:scale-110 transition-transform"><i class="fas fa-check-circle"></i></div>
            <span class="text-sm font-bold text-gray-500 uppercase tracking-widest">Disponibles</span>
            <span class="text-6xl font-black text-green-500 tracking-tighter">14</span>
        </div>

        <div class="bg-[#111827] rounded-[2rem] p-8 shadow-xl border border-purple-900/30 flex flex-col justify-between min-h-[160px] relative overflow-hidden group">
            <div class="absolute right-6 top-6 text-purple-900/20 text-5xl group-hover:scale-110 transition-transform"><i class="fas fa-tags"></i></div>
            <span class="text-sm font-bold text-gray-500 uppercase tracking-widest">Categorías</span>
            <span class="text-6xl font-black text-purple-500 tracking-tighter">7</span>
        </div>
    </div>

    {{-- LISTADO DE PLATILLOS --}}
    <div class="bg-[#111827] rounded-[2.5rem] p-8 shadow-2xl border border-gray-900/50 min-h-[500px]">
        
        {{-- SECCIÓN: PIZZAS --}}
        <div class="mb-12">
            <div class="flex items-center gap-4 mb-6 border-b border-gray-800 pb-4">
                <div class="w-12 h-12 bg-orange-500/10 rounded-2xl flex items-center justify-center text-orange-500 text-2xl">
                    <i class="fas fa-pizza-slice"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-100">Pizzas</h2>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">3 PLATILLOS REGISTRADOS</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                {{-- ITEM 1 --}}
                <div class="bg-[#1F2937]/50 border border-gray-800 hover:border-gray-700 rounded-3xl p-6 transition-all group">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-black text-gray-50 tracking-tight group-hover:text-blue-400 transition">Pizza Margarita</h3>
                            <div class="flex gap-2 mt-2">
                                <span class="bg-black/50 text-green-400 text-[10px] font-black px-2 py-0.5 rounded-md uppercase tracking-tighter border border-green-900/50">Disponible</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="w-10 h-10 rounded-xl bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white transition flex items-center justify-center"><i class="fas fa-edit text-sm"></i></button>
                            <button class="w-10 h-10 rounded-xl bg-red-900/20 text-red-500 hover:bg-red-900/40 transition flex items-center justify-center"><i class="fas fa-trash-alt text-sm"></i></button>
                        </div>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6">Pizza clásica con salsa de tomate hecha en casa, mozzarella fresco y hojas de albahaca.</p>
                    
                    <div class="flex flex-wrap gap-2 mb-6">
                        <span class="text-[11px] font-bold px-3 py-1 bg-gray-900 text-orange-400 rounded-lg border border-gray-800">Harina: 250g</span>
                        <span class="text-[11px] font-bold px-3 py-1 bg-gray-900 text-orange-400 rounded-lg border border-gray-800">Salsa: 100g</span>
                        <span class="text-[11px] font-bold px-3 py-1 bg-gray-900 text-orange-400 rounded-lg border border-gray-800">Queso: 150g</span>
                    </div>

                    <div class="flex justify-between items-center pt-4 border-t border-gray-800/50">
                        <span class="text-2xl font-black text-white">$120.<span class="text-sm">00</span> <span class="text-xs text-gray-500 font-medium ml-1 uppercase">MXN</span></span>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Activo</span>
                            <div class="w-10 h-5 bg-blue-600 rounded-full relative"><div class="absolute right-1 top-1 w-3 h-3 bg-white rounded-full"></div></div>
                        </div>
                    </div>
                </div>

                {{-- ITEM 2 --}}
                <div class="bg-[#1F2937]/50 border border-gray-800 hover:border-gray-700 rounded-3xl p-6 transition-all group">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-black text-gray-50 tracking-tight group-hover:text-blue-400 transition">Pizza Pepperoni</h3>
                            <div class="flex gap-2 mt-2">
                                <span class="bg-black/50 text-green-400 text-[10px] font-black px-2 py-0.5 rounded-md uppercase tracking-tighter border border-green-900/50">Disponible</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="w-10 h-10 rounded-xl bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white transition flex items-center justify-center"><i class="fas fa-edit text-sm"></i></button>
                            <button class="w-10 h-10 rounded-xl bg-red-900/20 text-red-500 hover:bg-red-900/40 transition flex items-center justify-center"><i class="fas fa-trash-alt text-sm"></i></button>
                        </div>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6">Nuestra pizza más vendida con abundante pepperoni premium y queso fundido.</p>
                    
                    <div class="flex flex-wrap gap-2 mb-6">
                        <span class="text-[11px] font-bold px-3 py-1 bg-gray-900 text-orange-400 rounded-lg border border-gray-800">Harina: 250g</span>
                        <span class="text-[11px] font-bold px-3 py-1 bg-gray-900 text-orange-400 rounded-lg border border-gray-800">Pepperoni: 80g</span>
                    </div>

                    <div class="flex justify-between items-center pt-4 border-t border-gray-800/50">
                        <span class="text-2xl font-black text-white">$145.<span class="text-sm">00</span> <span class="text-xs text-gray-500 font-medium ml-1 uppercase">MXN</span></span>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Activo</span>
                            <div class="w-10 h-5 bg-blue-600 rounded-full relative"><div class="absolute right-1 top-1 w-3 h-3 bg-white rounded-full"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL (FRONT-ONLY) --}}
<div id="modal-nuevo-alimento" class="fixed inset-0 z-[100] overflow-y-auto hidden opacity-0 transition-all duration-300 backdrop-blur-sm">
    <div class="fixed inset-0 bg-black/80" onclick="closeModalAlimento()"></div>
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative bg-[#111827] border border-gray-800 w-full max-w-2xl rounded-[2.5rem] shadow-2xl transform opacity-0 translate-y-8 transition-all duration-300" id="modal-panel">
            <div class="p-10">
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h2 class="text-3xl font-black text-white tracking-tight">Nuevo Platillo</h2>
                        <p class="text-gray-500 mt-1">Configuración estética del menú</p>
                    </div>
                    <button onclick="closeModalAlimento()" class="text-gray-500 hover:text-white transition"><i class="fas fa-times text-2xl"></i></button>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest ml-1">Nombre del Platillo</label>
                        <input type="text" class="w-full bg-[#1F2937] border-none rounded-2xl p-4 mt-2 text-white placeholder:text-gray-600 focus:ring-2 focus:ring-blue-500 transition" placeholder="Ej: Lasagna de la Casa">
                    </div>
                    <div class="col-span-1">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest ml-1">Precio</label>
                        <input type="text" class="w-full bg-[#1F2937] border-none rounded-2xl p-4 mt-2 text-white focus:ring-2 focus:ring-blue-500 transition" placeholder="$ 0.00">
                    </div>
                    <div class="col-span-1">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-widest ml-1">Categoría</label>
                        <select class="w-full bg-[#1F2937] border-none rounded-2xl p-4 mt-2 text-white focus:ring-2 focus:ring-blue-500 transition appearance-none">
                            <option>Pizzas</option>
                            <option>Pastas</option>
                            <option>Bebidas</option>
                        </select>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-black py-4 rounded-2xl transition shadow-lg shadow-blue-900/40">GUARDAR CAMBIOS</button>
                    <button onclick="closeModalAlimento()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-400 font-black py-4 rounded-2xl transition">CANCELAR</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openModalAlimento() {
        const modal = document.getElementById('modal-nuevo-alimento');
        const panel = document.getElementById('modal-panel');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            panel.classList.add('opacity-100', 'translate-y-0');
        }, 10);
    }
    function closeModalAlimento() {
        const modal = document.getElementById('modal-nuevo-alimento');
        const panel = document.getElementById('modal-panel');
        modal.classList.remove('opacity-100');
        panel.classList.remove('opacity-100', 'translate-y-0');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
</script>
@endsection