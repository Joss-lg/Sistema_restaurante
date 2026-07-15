/**
 * ============================================================
 * TECLADO VIRTUAL REUTILIZABLE 
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
        vistaSimbolos: false
    };

    var el = {}; 

    function init() {
        el.overlay = document.getElementById('tecladoVirtualOverlay');
        el.sheet = document.getElementById('tecladoVirtualSheet');
        el.titulo = document.getElementById('tecladoVirtualTitulo');
        el.grid = document.getElementById('tecladoVirtualGrid');
        el.btnToggle = document.getElementById('tecladoVirtualToggle');
        el.btnCerrar = document.getElementById('tecladoVirtualCerrar');
        el.btnListo = document.getElementById('tecladoVirtualListo');
        el.btnEspacio = document.getElementById('tecladoVirtualEspacio');
        el.btnBorrar = document.getElementById('tecladoVirtualBorrar');
        el.btnLimpiar = document.getElementById('tecladoVirtualLimpiar');

        if (!el.overlay) return; 

        // Aseguramos que empiece invisible a los clics
        el.overlay.style.pointerEvents = 'none';

        el.btnCerrar.addEventListener('click', cerrar);
        el.btnListo.addEventListener('click', cerrar);
        el.btnToggle.addEventListener('click', toggleVistaSimbolos);
        el.btnEspacio.addEventListener('click', function () { insertar(' '); });
        el.btnBorrar.addEventListener('click', borrar);
        el.btnLimpiar.addEventListener('click', limpiar);

        attachAll();

        // Cierra el teclado si se hace clic/touch fuera del panel y fuera del campo activo
        document.addEventListener('mousedown', cerrarSiClickFuera, true);
        document.addEventListener('touchstart', cerrarSiClickFuera, true);

        // Cierra con la tecla Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && estado.target) cerrar();
        });
    }

    function cerrarSiClickFuera(e) {
        if (!estado.target) return; // el teclado no está abierto
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

    function attach(campo) {
        if (campo.dataset.tecladoAtado) return; 
        campo.dataset.tecladoAtado = '1';

        var esMovil = /Mobi|Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        if (esMovil) {
            campo.removeAttribute('readonly');
            if(campo.getAttribute('inputmode') === 'none') {
                campo.removeAttribute('inputmode');
            }
            return; 
        }

        var abrir = function (e) {
            e.preventDefault();
            abrirPara(campo);
        };
        campo.addEventListener('click', abrir);
        campo.addEventListener('touchstart', abrir, { passive: false });
    }

    function abrirPara(campo) {
        estado.target = campo;
        estado.modo = campo.dataset.teclado === 'numerico' ? 'numerico' : 'texto';
        estado.maxLength = campo.dataset.tecladoMax ? parseInt(campo.dataset.tecladoMax, 10) : null;
        estado.decimales = campo.dataset.tecladoDecimales === 'true';
        estado.vistaSimbolos = false;

        el.titulo.textContent = campo.dataset.tecladoTitulo || (estado.modo === 'numerico' ? 'Teclado numérico' : 'Teclado');
        el.btnToggle.classList.toggle('hidden', estado.modo === 'numerico');

        renderGrid();
        el.overlay.classList.remove('hidden');
        el.overlay.style.pointerEvents = 'auto'; // ACTIVAMOS los clics para el teclado
        document.body.classList.add('teclado-virtual-abierto');
    }

    function cerrar() {
        el.overlay.classList.add('hidden');
        el.overlay.style.pointerEvents = 'none'; // DESACTIVAMOS los clics para que atraviesen al modal de abajo
        document.body.classList.remove('teclado-virtual-abierto');
        estado.target = null;
    }

    function toggleVistaSimbolos() {
        estado.vistaSimbolos = !estado.vistaSimbolos;
        el.btnToggle.textContent = estado.vistaSimbolos ? 'ABC' : '123';
        renderGrid();
    }

    function renderGrid() {
        var teclas = estado.modo === 'numerico'
            ? LAYOUT_NUMERICO.concat(estado.decimales ? ['.'] : [])
            : (estado.vistaSimbolos ? LAYOUT_SIMBOLOS : LAYOUT_LETRAS);

        el.grid.className = 'grid gap-1 sm:gap-1.5 ' + (estado.modo === 'numerico' ? 'grid-cols-3' : 'grid-cols-10');
        el.grid.innerHTML = '';

        teclas.forEach(function (tecla) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = tecla;
            btn.className = estado.modo === 'numerico'
                ? 'min-h-[48px] py-3 rounded-xl bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-blue-500/30 hover:bg-[var(--hover-bg)] active:scale-90 active:bg-blue-500/10 select-none text-[var(--text-main)] text-lg font-bold shadow-sm transition-all duration-100'
                : 'min-h-[40px] sm:min-h-[44px] py-2.5 rounded-lg bg-[var(--input-bg)] border border-[var(--border-color)] hover:border-blue-500/30 hover:bg-[var(--hover-bg)] active:scale-90 active:bg-blue-500/10 select-none text-[var(--text-main)] text-[11px] sm:text-xs font-bold shadow-sm transition-all duration-100';
            btn.addEventListener('click', function () { insertar(tecla); });
            el.grid.appendChild(btn);
        });
    }

    function insertar(caracter) {
        var campo = estado.target;
        if (!campo) return;
        var valor = campo.value || '';
        var inicio = campo.selectionStart != null ? campo.selectionStart : valor.length;
        var fin = campo.selectionEnd != null ? campo.selectionEnd : valor.length;
        var nuevoValor = valor.slice(0, inicio) + caracter + valor.slice(fin);
        if (estado.maxLength && nuevoValor.length > estado.maxLength) return;
        campo.value = nuevoValor;
        var nuevaPos = inicio + caracter.length;
        if (campo.setSelectionRange) campo.setSelectionRange(nuevaPos, nuevaPos);
        disparaInput(campo);
    }

    function borrar() {
        var campo = estado.target;
        if (!campo) return;
        var valor = campo.value || '';
        var inicio = campo.selectionStart != null ? campo.selectionStart : valor.length;
        var fin = campo.selectionEnd != null ? campo.selectionEnd : valor.length;
        if (inicio === fin && inicio > 0) {
            campo.value = valor.slice(0, inicio - 1) + valor.slice(fin);
            inicio -= 1;
        } else {
            campo.value = valor.slice(0, inicio) + valor.slice(fin);
        }
        if (campo.setSelectionRange) campo.setSelectionRange(inicio, inicio);
        disparaInput(campo);
    }

    function limpiar() {
        var campo = estado.target;
        if (!campo) return;
        campo.value = '';
        if (campo.setSelectionRange) campo.setSelectionRange(0, 0);
        disparaInput(campo);
    }

    function disparaInput(campo) {
        campo.dispatchEvent(new Event('input', { bubbles: true }));
    }

    document.addEventListener('DOMContentLoaded', init);

    window.TecladoVirtual = {
        attach: attach,
        attachAll: attachAll,
        abrirPara: abrirPara,
        cerrar: cerrar
    };
})(window, document);