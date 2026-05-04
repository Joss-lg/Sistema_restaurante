{{-- Fondo Oscuro con Desenfoque --}}
<div id="modalNuevaMesa" class="fixed inset-0 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300 backdrop-blur-md bg-black/60">
    
    {{-- Contenedor del Modal --}}
    <div class="relative w-full max-w-[650px] mx-4 scale-95 opacity-0 transition-all duration-300 transform rounded-[2rem] bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-2xl overflow-hidden" id="modalCard">
        
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-transparent via-[#3B82F6] to-transparent opacity-60"></div>

        {{-- Cabecera --}}
        <div class="flex items-center justify-between px-8 pt-6 pb-4 border-b border-[var(--border-color)]">
            <h2 class="text-xl font-black text-[var(--text-main)] tracking-tight">Abrir Mesa</h2>
            <button onclick="cerrarModalMesa()" class="w-8 h-8 rounded-full bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-rose-500 hover:border-rose-500/30 transition-all outline-none">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <div class="p-6 md:p-8">
            
            {{-- 1. Cajas de Entrada (Foco Inteligente) --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                
                {{-- Caja Mesa --}}
                <div id="cajaMesa" onclick="setFoco('mesa')" class="relative flex flex-col items-center justify-center bg-[var(--bg-base)] border-2 border-[#3B82F6] rounded-2xl p-3 cursor-pointer transition-all shadow-[0_0_15px_rgba(59,130,246,0.1)]">
                    <span id="labelMesa" class="text-[10px] text-[#3B82F6] font-black uppercase tracking-widest transition-colors">Mesa / Taburete</span>
                    <input type="text" id="inputMesa" readonly placeholder="Ej. 12M" class="w-full bg-transparent text-center text-4xl font-black text-[var(--text-main)] outline-none uppercase placeholder:text-[var(--border-color)] mt-1 cursor-pointer">
                </div>

                {{-- Caja Personas (Entrada manual 100%) --}}
                <div id="cajaPersonas" onclick="setFoco('personas')" class="relative flex flex-col items-center justify-center bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl p-3 cursor-pointer transition-all opacity-70 hover:opacity-100">
                    <span id="labelPersonas" class="text-[10px] text-[var(--text-muted)] font-black uppercase tracking-widest transition-colors">Personas</span>
                    <input type="text" id="inputPersonas" readonly placeholder="Ej. 4" class="w-full bg-transparent text-center text-4xl font-black text-[var(--text-main)] outline-none cursor-pointer placeholder:text-[var(--border-color)] mt-1">
                </div>
            </div>

            {{-- 2. TECLADO UNIFICADO --}}
            <div class="flex flex-col gap-2 w-full mb-6">
                
                {{-- Fila de Números --}}
                <div class="flex justify-center gap-1.5 w-full">
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9, 0] as $num)
                        <button type="button" onclick="escribirPad('{{ $num }}')" class="flex-1 h-12 md:h-14 rounded-xl bg-[#3B82F6]/5 border border-[#3B82F6]/20 text-xl md:text-2xl font-black text-[#3B82F6] hover:bg-[#3B82F6]/20 active:scale-90 transition-all outline-none shadow-sm">{{ $num }}</button>
                    @endforeach
                </div>

                {{-- Fila QWERTY 1 --}}
                <div class="flex justify-center gap-1.5 w-full mt-1">
                    @foreach(str_split('QWERTYUIOP') as $letra)
                        <button type="button" onclick="escribirPad('{{ $letra }}')" class="flex-1 h-12 md:h-14 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] text-lg md:text-xl font-black text-[var(--text-main)] hover:bg-[#3B82F6]/10 hover:text-[#3B82F6] active:scale-90 transition-all outline-none shadow-sm">{{ $letra }}</button>
                    @endforeach
                </div>
                
                {{-- Fila QWERTY 2 --}}
                <div class="flex justify-center gap-1.5 w-[95%] mx-auto">
                    @foreach(str_split('ASDFGHJKL') as $letra)
                        <button type="button" onclick="escribirPad('{{ $letra }}')" class="flex-1 h-12 md:h-14 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] text-lg md:text-xl font-black text-[var(--text-main)] hover:bg-[#3B82F6]/10 hover:text-[#3B82F6] active:scale-90 transition-all outline-none shadow-sm">{{ $letra }}</button>
                    @endforeach
                </div>
                
                {{-- Fila QWERTY 3 y Controles --}}
                <div class="flex justify-center gap-1.5 w-[90%] mx-auto">
                    @foreach(str_split('ZXCVBNM') as $letra)
                        <button type="button" onclick="escribirPad('{{ $letra }}')" class="flex-1 h-12 md:h-14 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] text-lg md:text-xl font-black text-[var(--text-main)] hover:bg-[#3B82F6]/10 hover:text-[#3B82F6] active:scale-90 transition-all outline-none shadow-sm">{{ $letra }}</button>
                    @endforeach
                    <button type="button" onclick="borrarPad()" class="flex-[1.5] h-12 md:h-14 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] text-xl text-[var(--text-muted)] hover:text-amber-500 hover:border-amber-500/30 active:scale-90 transition-all outline-none shadow-sm flex items-center justify-center">
                        <i class="fas fa-backspace"></i>
                    </button>
                </div>

                {{-- Espacio y Limpiar --}}
                <div class="flex justify-center gap-2 mt-1 w-[80%] mx-auto">
                    <button type="button" onclick="limpiarPad()" class="flex-1 h-10 md:h-12 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] text-xs font-black text-[var(--text-muted)] hover:bg-rose-500/10 hover:text-rose-500 active:scale-95 transition-all outline-none shadow-sm uppercase tracking-widest">
                        Limpiar
                    </button>
                    <button type="button" onclick="escribirPad(' ')" class="flex-[3] h-10 md:h-12 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] text-xs font-black text-[var(--text-muted)] hover:bg-[#3B82F6]/10 hover:text-[#3B82F6] active:scale-95 transition-all outline-none shadow-sm tracking-widest">
                        ESPACIO
                    </button>
                </div>
            </div>

            {{-- 3. Botón de Confirmación (¡AQUÍ ESTÁ LA MAGIA CON EL ONCLICK!) --}}
            <button id="btnConfirmarMesa" onclick="abrirComanda()" disabled class="w-full h-16 rounded-2xl bg-[#3B82F6] text-white text-[15px] font-black tracking-widest uppercase transition-all shadow-[0_8px_20px_-8px_rgba(59,130,246,0.8)] hover:bg-[#2563EB] disabled:opacity-40 disabled:grayscale disabled:shadow-none disabled:cursor-not-allowed outline-none flex items-center justify-center gap-3">
                <span>Confirmar y Abrir</span>
                <i class="fas fa-arrow-right"></i>
            </button>

        </div>
    </div>
</div>

<script>
    const modalWrap = document.getElementById('modalNuevaMesa');
    const modalCard = document.getElementById('modalCard');
    const inputMesa = document.getElementById('inputMesa');
    const inputPersonas = document.getElementById('inputPersonas');
    const cajaMesa = document.getElementById('cajaMesa');
    const cajaPersonas = document.getElementById('cajaPersonas');
    const labelMesa = document.getElementById('labelMesa');
    const labelPersonas = document.getElementById('labelPersonas');
    const btnConfirmar = document.getElementById('btnConfirmarMesa');
    
    let focoActual = 'mesa';

    // Función para mandar al usuario a la comanda
    function abrirComanda() {
        btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Abriendo...';
        window.location.href = '/mesero/comanda';
    }

    function abrirModalMesa() {
        modalWrap.classList.remove('hidden');
        setTimeout(() => {
            modalWrap.classList.remove('opacity-0');
            modalCard.classList.remove('scale-95', 'opacity-0');
            modalCard.classList.add('scale-100', 'opacity-100');
        }, 10);
        
        inputMesa.value = '';
        inputPersonas.value = ''; 
        setFoco('mesa');
        validarBoton();
    }

    function cerrarModalMesa() {
        modalWrap.classList.add('opacity-0');
        modalCard.classList.remove('scale-100', 'opacity-100');
        modalCard.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modalWrap.classList.add('hidden');
        }, 300);
    }

    function setFoco(tipo) {
        focoActual = tipo;
        
        if (tipo === 'mesa') {
            cajaMesa.classList.add('border-2', 'border-[#3B82F6]', 'shadow-[0_0_15px_rgba(59,130,246,0.1)]', 'opacity-100');
            cajaMesa.classList.remove('border', 'border-[var(--border-color)]', 'opacity-70');
            labelMesa.classList.add('text-[#3B82F6]');
            labelMesa.classList.remove('text-[var(--text-muted)]');
            
            cajaPersonas.classList.remove('border-2', 'border-[#3B82F6]', 'shadow-[0_0_15px_rgba(59,130,246,0.1)]', 'opacity-100');
            cajaPersonas.classList.add('border', 'border-[var(--border-color)]', 'opacity-70');
            labelPersonas.classList.remove('text-[#3B82F6]');
            labelPersonas.classList.add('text-[var(--text-muted)]');
        } else {
            cajaPersonas.classList.add('border-2', 'border-[#3B82F6]', 'shadow-[0_0_15px_rgba(59,130,246,0.1)]', 'opacity-100');
            cajaPersonas.classList.remove('border', 'border-[var(--border-color)]', 'opacity-70');
            labelPersonas.classList.add('text-[#3B82F6]');
            labelPersonas.classList.remove('text-[var(--text-muted)]');
            
            cajaMesa.classList.remove('border-2', 'border-[#3B82F6]', 'shadow-[0_0_15px_rgba(59,130,246,0.1)]', 'opacity-100');
            cajaMesa.classList.add('border', 'border-[var(--border-color)]', 'opacity-70');
            labelMesa.classList.remove('text-[#3B82F6]');
            labelMesa.classList.add('text-[var(--text-muted)]');
        }
    }

    function escribirPad(char) {
        const input = focoActual === 'mesa' ? inputMesa : inputPersonas;
        
        if(focoActual === 'mesa') {
            if(input.value.length >= 8) return; 
        } else if(focoActual === 'personas') {
            if(input.value.length >= 3) return; 
            if(isNaN(char) || char === ' ') return; 
        }

        input.value += char;
        validarBoton();
    }

    function borrarPad() {
        const input = focoActual === 'mesa' ? inputMesa : inputPersonas;
        input.value = input.value.slice(0, -1);
        validarBoton();
    }

    function limpiarPad() {
        const input = focoActual === 'mesa' ? inputMesa : inputPersonas;
        input.value = '';
        validarBoton();
    }

    function validarBoton() {
        const mesaOk = inputMesa.value.trim().length > 0;
        const personasOk = parseInt(inputPersonas.value) > 0;

        if(mesaOk && personasOk) {
            btnConfirmar.removeAttribute('disabled');
        } else {
            btnConfirmar.setAttribute('disabled', 'true');
        }
    }
</script>