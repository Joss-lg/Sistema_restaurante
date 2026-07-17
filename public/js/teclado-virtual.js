/**
 * ============================================================
 * TECLADO VIRTUAL REUTILIZABLE (SOPORTE DUAL: FÍSICO Y VIRTUAL)
 * ============================================================
 */
(function (window, document) {
    'use strict';

    var LAYOUT_LETRAS = ['1','2','3','4','5','6','7','8','9','0','Q','W','E','R','T','Y','U','I','O','P','A','S','D','F','G','H','J','K','L','Ñ','Z','X','C','V','B','N','M','/','.'];
    var LAYOUT_SIMBOLOS = ['!','"','#','$','%','&','/','(',')','=','¿','?','+','-','*','@',':',';',"'",'_','<','>','[',']','{','}','^','~','°','|'];
    var LAYOUT_NUMERICO = ['1','2','3','4','5','6','7','8','9','0'];

    var estado = {
        target: null,
        modo: 'texto',
        maxLength: null,
        decimales: false,
        vistaSimbolos: false,
        mayusculas: true
    };

    var el = {};

    /**
     * Adjunta un "tap" confiable a un botón, funcionando tanto con mouse
     * como con touch.
     *
     * Por qué existe esto: si haces preventDefault() en touchstart (para
     * evitar el zoom/scroll/ghost-click del navegador), el navegador NO
     * dispara el evento "click" sintético después -- así que un botón que
     * solo escucha "click" deja de responder en pantallas touch.
     * Aquí ejecutamos la acción en touchend (confiable en touch) y dejamos
     * click como respaldo solo para mouse/trackpad, con una bandera para
     * que nunca se ejecute doble.
     */
    function agregarTap(elemento, callback) {
        var procesando = false;
        var marcarProcesando = function () {
            if (procesando) return false;
            procesando = true;
            setTimeout(function () { procesando = false; }, 80);
            return true;
        };

        var evitarDefault = function (e) { e.preventDefault(); };

        elemento.addEventListener('touchstart', evitarDefault, { passive: false });
        elemento.addEventListener('touchend', function (e) {
            e.preventDefault();
            if (!marcarProcesando()) return;
            callback();
        }, { passive: false });
        elemento.addEventListener('touchcancel', function () {
            procesando = false;
        });

        elemento.addEventListener('mousedown', evitarDefault);
        elemento.addEventListener('click', function () {
            if (!marcarProcesando()) return;
            callback();
        });
    }

    function init() {
        el.overlay = document.getElementById('tecladoVirtualOverlay');
        el.sheet = document.getElementById('tecladoVirtualSheet');
        el.titulo = document.getElementById('tecladoVirtualTitulo');
        el.grid = document.getElementById('tecladoVirtualGrid');
        el.btnToggle = document.getElementById('tecladoVirtualToggle');
        el.btnMayus = document.getElementById('tecladoVirtualMayus');
        el.btnCerrar = document.getElementById('tecladoVirtualCerrar');
        el.btnListo = document.getElementById('tecladoVirtualListo');
        el.btnEspacio = document.getElementById('tecladoVirtualEspacio');
        el.btnBorrar = document.getElementById('tecladoVirtualBorrar');
        el.btnLimpiar = document.getElementById('tecladoVirtualLimpiar');
        el.controlesInferiores = document.getElementById('tecladoVirtualControles');

        if (!el.overlay) return;

        el.overlay.style.pointerEvents = 'none';

        if (el.btnCerrar) agregarTap(el.btnCerrar, cerrar);
        if (el.btnListo) agregarTap(el.btnListo, cerrar);
        if (el.btnToggle) agregarTap(el.btnToggle, toggleVistaSimbolos);
        if (el.btnMayus) agregarTap(el.btnMayus, toggleMayusculas);
        if (el.btnEspacio) agregarTap(el.btnEspacio, function () { insertar(' '); });
        if (el.btnBorrar) agregarTap(el.btnBorrar, borrar);
        if (el.btnLimpiar) agregarTap(el.btnLimpiar, limpiar);

        attachAll();

        document.addEventListener('mousedown', cerrarSiClickFuera, true);
        document.addEventListener('touchstart', cerrarSiClickFuera, true);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && estado.target) cerrar();
        });

        window.addEventListener('resize', function () {
            if (estado.target) cerrar();
        });
    }

    function cerrarSiClickFuera(e) {
        if (!estado.target) return;
        if (el.overlay.classList.contains('hidden')) return;

        var dentroDelTeclado = el.sheet.contains(e.target);
        var esElCampoActivo = e.target === estado.target;

        if (!dentroDelTeclado && !esElCampoActivo) {
            cerrar();
        }
    }

    function attachAll() {
        document.querySelectorAll('[data-teclado]').forEach(attach);
    }

    /**
     * Detecta si es un celular/tablet REAL (donde queremos dejar el teclado
     * nativo del sistema) en lugar de un monitor/kiosco touch de escritorio
     * (donde queremos que se abra nuestro teclado virtual).
     *
     * Ya NO usamos window.innerWidth como criterio -- un kiosco touch de
     * escritorio puede tener pantalla chica y antes eso lo hacía pasar
     * como "celular" por error.
     */
    function esCelularReal() {
        if (navigator.userAgentData && typeof navigator.userAgentData.mobile === 'boolean') {
            return navigator.userAgentData.mobile;
        }
        return /iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    function attach(campo) {
        if (campo.dataset.tecladoAtado) return;
        campo.dataset.tecladoAtado = '1';

        if (esCelularReal()) {
            // Celular real: nunca mostramos nuestro overlay, dejamos el teclado nativo del sistema.
            campo.removeAttribute('readonly');
            campo.removeAttribute('inputmode');
            return;
        }

        // Desktop / kiosco touch (con o sin teclado físico conectado):
        // NO usamos readonly -- así el teclado FÍSICO escribe normal, siempre.
        // inputmode="none" bloquea SOLO el teclado táctil nativo del sistema.
        campo.removeAttribute('readonly');
        campo.setAttribute('inputmode', 'none');
        campo.style.cursor = 'pointer';
        campo.style.touchAction = 'manipulation';

        var abriendo = false;

        var abrirTeclado = function () {
            if (abriendo) return;
            abriendo = true;
            abrirPara(campo);
            campo.focus();
            setTimeout(function () { abriendo = false; }, 80);
        };

        campo.addEventListener('touchstart', function (e) {
            e.preventDefault();
            abrirTeclado();
        }, { passive: false });

        campo.addEventListener('click', abrirTeclado);
    }

    function abrirPara(campo) {
        estado.target = campo;
        estado.modo = campo.dataset.teclado === 'numerico' ? 'numerico' : 'texto';
        estado.maxLength = campo.dataset.tecladoMax ? parseInt(campo.dataset.tecladoMax, 10) : null;
        estado.decimales = campo.dataset.tecladoDecimales === 'true';
        estado.vistaSimbolos = false;
        estado.mayusculas = true;

        el.titulo.textContent = campo.dataset.tecladoTitulo || (estado.modo === 'numerico' ? 'Teclado numérico' : 'Teclado');
        el.btnToggle.classList.toggle('hidden', estado.modo === 'numerico');
        el.btnToggle.textContent = '123';

        if (el.btnMayus) {
            el.btnMayus.classList.toggle('hidden', estado.modo === 'numerico');
            actualizarBotonMayus();
        }

        if (el.sheet) {
            el.sheet.classList.toggle('max-w-[300px]', estado.modo === 'numerico');
            el.sheet.classList.toggle('max-w-2xl', estado.modo !== 'numerico');
        }

        [el.btnLimpiar, el.btnEspacio, el.btnBorrar, el.btnListo].forEach(function (btn) {
            if (!btn) return;
            btn.classList.toggle('py-2', estado.modo === 'numerico');
            btn.classList.toggle('py-2.5', estado.modo !== 'numerico');
            btn.classList.toggle('sm:py-2.5', estado.modo === 'numerico');
            btn.classList.toggle('sm:py-3', estado.modo !== 'numerico');
        });

        if (el.btnEspacio) el.btnEspacio.classList.toggle('hidden', estado.modo === 'numerico');
        if (el.controlesInferiores) {
            el.controlesInferiores.classList.toggle('grid-cols-3', estado.modo === 'numerico');
            el.controlesInferiores.classList.toggle('grid-cols-4', estado.modo !== 'numerico');
        }

        renderGrid();
        el.overlay.classList.remove('hidden');
        el.overlay.style.pointerEvents = 'auto';
        document.body.classList.add('teclado-virtual-abierto');

        document.body.appendChild(el.overlay);
    }

    function cerrar() {
        el.overlay.classList.add('hidden');
        el.overlay.style.pointerEvents = 'none';
        document.body.classList.remove('teclado-virtual-abierto');
        estado.target = null;
    }

    function toggleVistaSimbolos() {
        estado.vistaSimbolos = !estado.vistaSimbolos;
        el.btnToggle.textContent = estado.vistaSimbolos ? 'ABC' : '123';
        renderGrid();
    }

    function toggleMayusculas() {
        estado.mayusculas = !estado.mayusculas;
        actualizarBotonMayus();
        renderGrid();
    }

    function actualizarBotonMayus() {
        if (!el.btnMayus) return;
        el.btnMayus.textContent = estado.mayusculas ? 'ABC' : 'abc';
        el.btnMayus.classList.toggle('bg-blue-600', estado.mayusculas === false);
    }

    function aplicarCaso(caracter) {
        if (/[a-zA-ZñÑ]/.test(caracter)) {
            return estado.mayusculas ? caracter.toUpperCase() : caracter.toLowerCase();
        }
        return caracter;
    }

    function renderGrid() {
        var teclasBase = estado.modo === 'numerico'
            ? LAYOUT_NUMERICO.concat(estado.decimales ? ['.'] : [])
            : (estado.vistaSimbolos ? LAYOUT_SIMBOLOS : LAYOUT_LETRAS);

        el.grid.className = estado.modo === 'numerico'
            ? 'grid grid-cols-3 gap-1.5'
            : 'grid grid-cols-10 gap-1 sm:gap-1.5';
        el.grid.innerHTML = '';

        teclasBase.forEach(function (teclaOriginal) {
            var teclaMostrada = (estado.modo === 'numerico' || estado.vistaSimbolos)
                ? teclaOriginal
                : aplicarCaso(teclaOriginal);

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = teclaMostrada;
            btn.className = estado.modo === 'numerico'
                ? 'min-h-[38px] py-2 rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-blue-500/40 hover:bg-[var(--hover-bg)] active:scale-90 active:bg-blue-500/10 select-none text-[var(--text-main)] text-sm font-bold shadow-sm transition-all duration-100'
                : 'min-h-[40px] sm:min-h-[44px] py-2.5 rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-blue-500/30 hover:bg-[var(--hover-bg)] active:scale-90 active:bg-blue-500/10 select-none text-[var(--text-main)] text-[11px] sm:text-xs font-bold shadow-sm transition-all duration-100';

            agregarTap(btn, function () { insertar(teclaMostrada); });

            el.grid.appendChild(btn);
        });
    }

    function insertar(caracter) {
        var campo = estado.target;
        if (!campo) return;

        campo.focus();
        if (estado.maxLength && campo.value.length >= estado.maxLength) return;

        var inicio = campo.selectionStart !== null ? campo.selectionStart : campo.value.length;
        var fin = campo.selectionEnd !== null ? campo.selectionEnd : campo.value.length;

        if (typeof campo.setRangeText === 'function') {
            campo.setRangeText(caracter, inicio, fin, 'end');
        } else {
            var valor = campo.value;
            campo.value = valor.slice(0, inicio) + caracter + valor.slice(fin);
            campo.setSelectionRange(inicio + caracter.length, inicio + caracter.length);
        }

        disparaInput(campo);
    }

    function borrar() {
        var campo = estado.target;
        if (!campo) return;

        campo.focus();
        var inicio = campo.selectionStart !== null ? campo.selectionStart : campo.value.length;
        var fin = campo.selectionEnd !== null ? campo.selectionEnd : campo.value.length;

        if (inicio === fin && inicio > 0) {
            if (typeof campo.setRangeText === 'function') {
                campo.setRangeText('', inicio - 1, fin, 'end');
            } else {
                campo.value = campo.value.slice(0, inicio - 1) + campo.value.slice(fin);
                campo.setSelectionRange(inicio - 1, inicio - 1);
            }
        } else if (inicio !== fin) {
            if (typeof campo.setRangeText === 'function') {
                campo.setRangeText('', inicio, fin, 'end');
            } else {
                campo.value = campo.value.slice(0, inicio) + campo.value.slice(fin);
                campo.setSelectionRange(inicio, inicio);
            }
        }

        disparaInput(campo);
    }

    function limpiar() {
        var campo = estado.target;
        if (!campo) return;

        campo.focus();
        campo.value = '';
        if (campo.setSelectionRange) {
            campo.setSelectionRange(0, 0);
        }
        disparaInput(campo);
    }

    function disparaInput(campo) {
        var posicionCorrecta = campo.selectionStart;
        campo.dispatchEvent(new Event('input', { bubbles: true }));

        setTimeout(function () {
            if (document.activeElement === campo) {
                campo.setSelectionRange(posicionCorrecta, posicionCorrecta);
            }
        }, 1);
    }

    document.addEventListener('DOMContentLoaded', init);

    window.TecladoVirtual = {
        attach: attach,
        attachAll: attachAll,
        abrirPara: abrirPara,
        cerrar: cerrar
    };
})(window, document);