{{-- Fondo Oscuro con Desenfoque --}}
<div id="modalNuevaMesa" class="fixed inset-0 z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300 backdrop-blur-md bg-black/70 p-4 sm:p-6">
    
    {{-- Contenedor del Modal --}}
    <div class="relative w-full max-w-[min(95vw,420px)] sm:max-w-[min(92vw,540px)] md:max-w-[min(88vw,720px)] lg:max-w-[min(80vw,820px)] xl:max-w-[min(75vw,900px)] scale-95 opacity-0 transition-all duration-300 transform rounded-[2rem] lg:rounded-[2.5rem] bg-[var(--bg-panel)] border border-[var(--border-color)] shadow-2xl overflow-hidden flex flex-col max-h-[95vh]" id="modalCard">
        
        {{-- Cabecera --}}
        <div class="flex items-center justify-between px-8 pt-8 pb-5 border-b border-[var(--border-color)] bg-[var(--bg-panel)] flex-shrink-0">
            <div>
                <p class="text-[10px] uppercase tracking-[0.3em] text-[#3B82F6] font-black mb-1">Apertura</p>
                <h2 class="text-2xl font-black text-[var(--text-main)] tracking-tight leading-none">ABRIR MESA</h2>
            </div>
            <button onclick="cerrarModalMesa()" class="w-10 h-10 rounded-[12px] bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--text-main)] hover:border-[var(--border-highlight)] transition-all outline-none active:scale-95 shadow-inner">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        {{-- Cuerpo del Modal (Split Screen en LG) --}}
        <div class="flex flex-col lg:flex-row flex-1 overflow-y-auto hide-scroll min-h-[520px] lg:min-h-[420px] bg-[var(--bg-base)]">
            
            {{-- COLUMNA IZQUIERDA: Inputs y Controles --}}
            <div class="w-full lg:w-[40%] p-6 lg:p-8 flex flex-col gap-5 border-b lg:border-b-0 lg:border-r border-[var(--border-color)] bg-[var(--bg-panel)]">
                
                {{-- SECCIÓN: Mesas Disponibles --}}
                <div id="seccionMesasDisponibles" class="hidden">
                    <p class="text-[10px] font-black uppercase tracking-[0.25em] text-[var(--text-muted)] mb-3 pl-1">Disponibles</p>
                    <div id="gridMesasDisponibles" class="grid grid-cols-3 sm:grid-cols-4 gap-2 mb-5">
                        {{-- Generado por JavaScript --}}
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    {{-- Caja Mesa --}}
                    <div id="cajaMesa" onclick="setFoco('mesa')" class="relative flex flex-col items-center justify-center bg-[var(--bg-base)] border border-blue-500/50 rounded-[18px] p-4 cursor-pointer transition-all shadow-[0_0_15px_rgba(59,130,246,0.1)] group">
                        <span id="labelMesa" class="text-[10px] text-blue-500 font-black uppercase tracking-widest transition-colors">Mesa / Taburete</span>
                        <input type="text" id="inputMesa" readonly placeholder="EJ. 12M" class="w-full bg-transparent text-center text-2xl sm:text-3xl lg:text-4xl font-black text-[var(--text-main)] outline-none uppercase placeholder:opacity-30 mt-1 cursor-pointer">
                    </div>

                    {{-- Caja Personas --}}
                    <div id="cajaPersonas" onclick="setFoco('personas')" class="relative flex flex-col items-center justify-center bg-[var(--bg-base)] border border-[var(--border-color)] rounded-[18px] p-4 cursor-pointer transition-all opacity-60 hover:opacity-100">
                        <span id="labelPersonas" class="text-[10px] text-[var(--text-muted)] font-black uppercase tracking-widest transition-colors">Personas</span>
                        <input type="text" id="inputPersonas" readonly placeholder="Ej. 4" class="w-full bg-transparent text-center text-2xl sm:text-3xl lg:text-4xl font-black text-[var(--text-main)] outline-none cursor-pointer placeholder:opacity-30 mt-1">
                    </div>
                </div>

                {{-- SECCIÓN DIVISIÓN DE CUENTAS --}}
                <div class="mt-2 p-5 rounded-[18px] bg-[var(--bg-base)] border border-[var(--border-color)]">
                    <label class="flex items-center gap-3 cursor-pointer group w-max">
                        <div class="relative flex items-center justify-center">
                            <input type="checkbox" id="dividirCuenta" onchange="toggleDivisionCuentas()" class="peer appearance-none w-6 h-6 rounded-lg bg-[var(--bg-panel)] border border-[var(--border-color)] checked:bg-[#3B82F6] checked:border-[#3B82F6] transition-all cursor-pointer">
                            <i class="fas fa-check absolute text-[12px] text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity"></i>
                        </div>
                        <span class="text-sm font-bold text-[var(--text-muted)] group-hover:text-[var(--text-main)] transition-colors select-none">Dividir cuenta inicial</span>
                    </label>
                    
                    <div id="divisionOptions" class="hidden mt-4 pt-4 border-t border-[var(--border-color)]">
                        <label class="text-[10px] font-black uppercase tracking-widest text-[#3B82F6] block mb-2 pl-1">¿Entre cuántas personas?</label>
                        <input type="number" id="inputDivisionPersonas" min="2" placeholder="Ej. 4" class="w-full h-14 px-4 rounded-xl text-center text-2xl font-black bg-[var(--bg-panel)] border border-[var(--border-color)] focus:border-[#3B82F6] focus:outline-none text-[var(--text-main)] transition-colors" oninput="actualizarDivision()">
                        <div id="mensajeSeleccion" class="text-xs text-[#3B82F6] font-bold mt-2 pl-1 hidden text-center">
                            El total se dividirá en <span id="numeroPartes" class="text-[var(--text-main)]">0</span> partes
                        </div>
                    </div>
                </div>

                <div class="mt-auto pt-4">
                    <p id="errorMesa" class="mb-3 text-xs font-bold text-rose-500 text-center hidden"></p>
                    <button id="btnConfirmarMesa" onclick="abrirComanda()" disabled class="w-full h-14 lg:h-16 rounded-[16px] bg-gradient-to-r from-blue-600 to-cyan-500 text-white text-sm lg:text-base font-black tracking-widest uppercase transition-all shadow-[0_8px_20px_-5px_rgba(59,130,246,0.5)] hover:shadow-[0_8px_25px_-5px_rgba(59,130,246,0.6)] disabled:opacity-30 disabled:grayscale disabled:shadow-none disabled:cursor-not-allowed outline-none flex items-center justify-center gap-3 active:scale-95">
                        <span>Confirmar y Abrir</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            {{-- COLUMNA DERECHA: Teclado QWERTY Amplio --}}
            <div class="w-full lg:w-[60%] p-6 lg:p-8 flex flex-col justify-center bg-[var(--bg-base)]">
                <div class="flex flex-col gap-2 w-full max-w-[min(98vw,520px)] mx-auto">
                    
                    {{-- Fila Números --}}
                    <div class="flex flex-wrap justify-center gap-1.5 w-full">
                        @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9, 0] as $num)
                            <button type="button" onclick="escribirPad('{{ $num }}')" class="flex-1 min-w-[36px] max-w-[45px] sm:max-w-[52px] md:max-w-[60px] h-10 sm:h-12 lg:h-14 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-sm sm:text-base lg:text-lg font-black text-[var(--text-main)] hover:bg-[#3B82F6] hover:text-white hover:border-[#3B82F6] active:scale-95 transition-all outline-none shadow-sm">{{ $num }}</button>
                        @endforeach
                    </div>

                    {{-- Fila QWERTY 1 --}}
                    <div class="flex justify-center gap-1.5 w-full mt-2">
                        @foreach(str_split('QWERTYUIOP') as $letra)
                            <button type="button" onclick="escribirPad('{{ $letra }}')" class="flex-1 max-w-[40px] sm:max-w-[48px] md:max-w-[52px] h-10 sm:h-12 lg:h-14 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-xs sm:text-sm lg:text-base font-black text-[var(--text-main)] hover:bg-[#3B82F6] hover:text-white hover:border-[#3B82F6] active:scale-95 transition-all outline-none shadow-sm">{{ $letra }}</button>
                        @endforeach
                    </div>
                    
                    {{-- Fila QWERTY 2 (Sangría) --}}
                    <div class="flex justify-center gap-1.5 w-[92%] mx-auto">
                        @foreach(str_split('ASDFGHJKL') as $letra)
                            <button type="button" onclick="escribirPad('{{ $letra }}')" class="flex-1 max-w-[40px] sm:max-w-[48px] md:max-w-[52px] h-10 sm:h-12 lg:h-14 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-xs sm:text-sm lg:text-base font-black text-[var(--text-main)] hover:bg-[#3B82F6] hover:text-white hover:border-[#3B82F6] active:scale-95 transition-all outline-none shadow-sm">{{ $letra }}</button>
                        @endforeach
                    </div>
                    
                    {{-- Fila QWERTY 3 y DEL (Sangría) --}}
                    <div class="flex justify-center gap-1.5 w-[85%] mx-auto">
                        @foreach(str_split('ZXCVBNM') as $letra)
                            <button type="button" onclick="escribirPad('{{ $letra }}')" class="flex-1 max-w-[40px] sm:max-w-[48px] md:max-w-[52px] h-10 sm:h-12 lg:h-14 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-xs sm:text-sm lg:text-base font-black text-[var(--text-main)] hover:bg-[#3B82F6] hover:text-white hover:border-[#3B82F6] active:scale-95 transition-all outline-none shadow-sm">{{ $letra }}</button>
                        @endforeach
                        <button type="button" onclick="borrarPad()" class="flex-[1.5] max-w-[72px] h-10 sm:h-12 lg:h-14 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-base sm:text-lg lg:text-lg text-[var(--text-muted)] hover:text-rose-500 hover:border-rose-500/50 hover:bg-rose-500/10 active:scale-95 transition-all outline-none shadow-sm flex items-center justify-center">
                            <i class="fas fa-backspace"></i>
                        </button>
                    </div>

                    {{-- Espacio y Limpiar --}}
                    <div class="flex justify-center gap-3 w-[85%] mx-auto mt-2">
                        <button type="button" onclick="limpiarPad()" class="flex-1 h-12 rounded-xl bg-[var(--bg-panel)] border border-[var(--border-color)] text-[10px] lg:text-xs font-black text-[var(--text-muted)] hover:bg-rose-500/10 hover:text-rose-500 hover:border-rose-500/30 active:scale-95 transition-all outline-none shadow-sm uppercase tracking-widest">
                            Limpiar
                        </button>
                        <button type="button" onclick="escribirPad(' ')" class="flex-[2] h-12 rounded-xl bg-[#3B82F6] text-[10px] lg:text-xs font-black text-white hover:opacity-90 active:scale-95 transition-all outline-none shadow-sm uppercase tracking-widest">
                            Espacio
                        </button>
                    </div>
                </div>
            </div>

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
    const errorMesa = document.getElementById('errorMesa');
    const dividirCuentaCheckbox = document.getElementById('dividirCuenta');
    const divisionOptions = document.getElementById('divisionOptions');
    
    let focoActual = 'mesa';
    let totalCuentasDivididas = 0;
    const inputDivisionPersonas = document.getElementById('inputDivisionPersonas');

    function toggleDivisionCuentas() {
        if (dividirCuentaCheckbox.checked) {
            divisionOptions.classList.remove('hidden');
            inputDivisionPersonas.focus();
        } else {
            divisionOptions.classList.add('hidden');
            totalCuentasDivididas = 0;
            inputDivisionPersonas.value = '';
            document.getElementById('mensajeSeleccion').classList.add('hidden');
        }
    }

    function actualizarDivision() {
        const valor = parseInt(inputDivisionPersonas.value) || 0;
        if (valor >= 2) {
            totalCuentasDivididas = valor;
            document.getElementById('numeroPartes').textContent = valor;
            document.getElementById('mensajeSeleccion').classList.remove('hidden');
        } else {
            totalCuentasDivididas = 0;
            document.getElementById('mensajeSeleccion').classList.add('hidden');
        }
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
        dividirCuentaCheckbox.checked = false;
        toggleDivisionCuentas();
        errorMesa.textContent = '';
        errorMesa.classList.add('hidden');
        setFoco('mesa');
        validarBoton();

        // Cargar las mesas disponibles (reabiertos)
        cargarMesasDisponibles();
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
            cajaMesa.classList.add('border-blue-500/50', 'shadow-[0_0_15px_rgba(59,130,246,0.1)]', 'opacity-100');
            cajaMesa.classList.remove('border-[var(--border-color)]', 'opacity-60');
            labelMesa.classList.add('text-blue-500');
            labelMesa.classList.remove('text-[var(--text-muted)]');
            
            cajaPersonas.classList.remove('border-blue-500/50', 'shadow-[0_0_15px_rgba(59,130,246,0.1)]', 'opacity-100');
            cajaPersonas.classList.add('border-[var(--border-color)]', 'opacity-60');
            labelPersonas.classList.remove('text-blue-500');
            labelPersonas.classList.add('text-[var(--text-muted)]');
        } else {
            cajaPersonas.classList.add('border-blue-500/50', 'shadow-[0_0_15px_rgba(59,130,246,0.1)]', 'opacity-100');
            cajaPersonas.classList.remove('border-[var(--border-color)]', 'opacity-60');
            labelPersonas.classList.add('text-blue-500');
            labelPersonas.classList.remove('text-[var(--text-muted)]');
            
            cajaMesa.classList.remove('border-blue-500/50', 'shadow-[0_0_15px_rgba(59,130,246,0.1)]', 'opacity-100');
            cajaMesa.classList.add('border-[var(--border-color)]', 'opacity-60');
            labelMesa.classList.remove('text-blue-500');
            labelMesa.classList.add('text-[var(--text-muted)]');
        }
    }

    function escribirPad(char) {
        const input = focoActual === 'mesa' ? inputMesa : inputPersonas;
        if(focoActual === 'mesa' && input.value.length >= 12) return; 
        if(focoActual === 'personas') {
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

    async function abrirComanda() {
        btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        btnConfirmar.disabled = true;

        const mesaNumero = inputMesa.value.trim();
        const personas = parseInt(inputPersonas.value) || 0;
        const cuentaDividida = dividirCuentaCheckbox.checked;
        
        // Construir payload dinámicamente
        const payload = {
            numero: mesaNumero,
            capacidad: personas,
            cuenta_dividida: cuentaDividida
        };
        
        // Solo incluir total_cuentas_division si es dividida
        if (cuentaDividida) {
            payload.total_cuentas_division = totalCuentasDivididas;
        }

        console.log('📤 Enviando solicitud de mesa:', payload);

        try {
            const response = await fetch('/mesero/mesa/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify(payload)
            });

            console.log('📥 Respuesta recibida - Status:', response.status, 'OK:', response.ok);

            const data = await response.json();
            console.log('📋 Datos recibidos:', data);

            if (!response.ok) {
                const errorMessage = data.message || `Error HTTP ${response.status}`;
                console.error('❌ Error de respuesta:', errorMessage, data);
                errorMesa.textContent = errorMessage;
                errorMesa.classList.remove('hidden');
                btnConfirmar.innerHTML = '<span>Confirmar y Abrir</span> <i class="fas fa-arrow-right"></i>';
                btnConfirmar.disabled = false;
                return;
            }

            if (!data.success) {
                const errorMessage = data.message || 'Error desconocido al abrir la mesa.';
                console.error('❌ Respuesta sin éxito:', errorMessage, data);
                errorMesa.textContent = errorMessage;
                errorMesa.classList.remove('hidden');
                btnConfirmar.innerHTML = '<span>Confirmar y Abrir</span> <i class="fas fa-arrow-right"></i>';
                btnConfirmar.disabled = false;
                return;
            }

            console.log('✅ Mesa creada exitosamente:', data.mesa);
            cerrarModalMesa();
            await new Promise(resolve => setTimeout(resolve, 250));
            window.location.href = `/mesero/comanda/${data.mesa.id}`;
        } catch (error) {
            console.error('💥 Error en abrirComanda:', error.message, error);
            errorMesa.textContent = 'Error al procesar la solicitud: ' + error.message;
            errorMesa.classList.remove('hidden');
            btnConfirmar.innerHTML = '<span>Confirmar y Abrir</span> <i class="fas fa-arrow-right"></i>';
            btnConfirmar.disabled = false;
        }
    }

    // Cargar mesas disponibles desde el API
    async function cargarMesasDisponibles() {
        try {
            const res = await fetch('/mesero/mesas/abiertas', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            
            // CORRECCIÓN LÓGICA: Filtramos manualmente por estado 'libre' o 'disponible' para asegurarnos
            // de que mesas ocupadas o abiertas no se muestren aquí.
            let mesasRealmenteLibres = [];
            if(data.mesas_libres && data.mesas_libres.length > 0){
                mesasRealmenteLibres = data.mesas_libres.filter(m => {
                    const est = m.estado ? m.estado.toLowerCase() : '';
                    return est === 'libre' || est === 'disponible';
                });
            }

            if (data.success && mesasRealmenteLibres.length > 0) {
                const seccion = document.getElementById('seccionMesasDisponibles');
                const grid = document.getElementById('gridMesasDisponibles');
                
                grid.innerHTML = '';
                
                // Crear botón para cada mesa disponible real
                mesasRealmenteLibres.forEach(mesa => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'relative flex flex-col items-center justify-center p-3 rounded-[12px] bg-[var(--bg-panel)] border border-[var(--border-color)] hover:border-blue-500/40 active:scale-95 transition-all outline-none shadow-sm group';
                    
                    btn.innerHTML = `
                        <span class="text-xl sm:text-2xl font-black text-[var(--text-main)] group-hover:text-blue-500 transition-colors">${mesa.numero}</span>
                        <span class="text-[9px] font-bold text-[var(--text-muted)] uppercase mt-1 tracking-widest">Libre</span>
                    `;
                    
                    btn.onclick = (e) => {
                        e.preventDefault();
                        inputMesa.value = mesa.numero;
                        inputPersonas.value = mesa.capacidad || '';
                        setFoco('personas');
                        validarBoton();
                    };
                    
                    grid.appendChild(btn);
                });
                
                seccion.classList.remove('hidden');
            } else {
                document.getElementById('seccionMesasDisponibles').classList.add('hidden');
            }
        } catch (error) {
            console.error('Error cargando mesas:', error);
            document.getElementById('seccionMesasDisponibles').classList.add('hidden');
        }
    }
</script>