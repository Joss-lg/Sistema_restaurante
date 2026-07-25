// resources/js/teclado-virtual.js
import Keyboard from "simple-keyboard";
import "simple-keyboard/build/css/index.css";
import "./teclado-virtual.css";

let inputActivo = null;
let keyboard = null;
let mayusculasActivas = false;
let posicionGuardada = null; // { x, y } si el usuario ya lo movió

const DISPLAY_BASE = {
    "{bksp}": "⌫",
    "{space}": "espacio",
    "{enter}": "Aceptar",
    "{shift}": "abc",
    "{sym}": "!#1",
    "{default}": "ABC"
};

// Tipos de <input> que NUNCA deben abrir el teclado virtual (selects,
// checkboxes, fechas nativas, archivos, etc. ya tienen su propio control).
const TIPOS_EXCLUIDOS = [
    "checkbox", "radio", "file", "hidden", "submit", "button",
    "range", "color", "date", "time", "datetime-local", "month",
    "week", "image", "reset"
];

/**
 * Decide si un elemento debe abrir el teclado virtual al enfocarse.
 * Por defecto aplica a cualquier <input> de texto/número y <textarea>
 * de toda la app (ya no hace falta agregar la clase "touch-input" a mano
 * en cada formulario). Para excluir un campo puntual, se le puede poner
 * el atributo `data-no-teclado`.
 */
function esCampoValido(el) {
    if (!el) return false;
    if (el.disabled || el.readOnly) return false;
    if (el.hasAttribute("data-no-teclado")) return false;

    if (el instanceof HTMLTextAreaElement) return true;

    if (el instanceof HTMLInputElement) {
        return !TIPOS_EXCLUIDOS.includes(el.type);
    }

    return false;
}

function crearTeclado() {
    const contenedor = document.getElementById("teclado-virtual-contenedor");
    if (!contenedor) return;

    // Header: manija para arrastrar + título del campo + botón de cerrar.
    // Se crea una sola vez, arriba del teclado.
    const header = document.createElement("div");
    header.className = "teclado-virtual-header";
    header.innerHTML = `
        <span class="teclado-drag-handle" title="Arrastra para mover · doble tap para regresar">⠿⠿⠿ Mover</span>
        <span class="teclado-virtual-titulo" id="teclado-virtual-titulo"></span>
        <button type="button" class="teclado-virtual-cerrar" id="teclado-virtual-cerrar" aria-label="Cerrar teclado">
            ✕ Cerrar
        </button>
    `;
    contenedor.insertBefore(header, contenedor.firstChild);

    habilitarArrastre(contenedor, header.querySelector(".teclado-drag-handle"));

    header.querySelector("#teclado-virtual-cerrar").addEventListener("click", () => {
        // Si venía de un campo real, le devolvemos el foco visualmente
        // cerrado (blur) para que quede claro que ya terminó de escribir.
        if (inputActivo) inputActivo.blur();
        ocultarTeclado();
    });

    keyboard = new Keyboard({
        onChange: input => {
            if (inputActivo) {
                inputActivo.value = input;
                inputActivo.dispatchEvent(new Event("input", { bubbles: true }));
            }
        },
        onKeyPress: button => {
            if (button === "{enter}") ocultarTeclado();
            if (button === "{shift}") manejarShift();
            if (button === "{sym}") manejarSimbolos();
            if (button === "{default}") volverALetras();
        },
        layoutName: "default",
        layout: {
            default: [
                "1 2 3 4 5 6 7 8 9 0 {bksp}",
                "q w e r t y u i o p {enter}",
                "a s d f g h j k l ñ",
                "{shift} z x c v b n m , .",
                "{sym} {space}"
            ],
            shift: [
                "! \" # $ % & / ( ) {bksp}",
                "Q W E R T Y U I O P {enter}",
                "A S D F G H J K L Ñ",
                "{shift} Z X C V B N M , .",
                "{sym} {space}"
            ],
            symbols: [
                "` ~ [ ] { } | \\ ; {bksp}",
                ": < > = + - _ * / {enter}",
                "¿ ? ¡ ! @ # ^ & ( )",
                "{default} , . \" ' \u2022",
                "{default} {space}"
            ],
            numerico: [
                "1 2 3",
                "4 5 6",
                "7 8 9",
                "{bksp} 0 {enter}"
            ]
        },
        display: DISPLAY_BASE
    });
}

/**
 * Arrastre con Pointer Events: funciona igual con dedo (touch) que con
 * mouse, sin duplicar listeners. setPointerCapture asegura que el drag
 * no se "pierda" aunque el dedo/cursor salga brevemente del elemento.
 */
function habilitarArrastre(contenedor, manija) {
    let arrastrando = false;
    let offsetX = 0;
    let offsetY = 0;

    manija.addEventListener("pointerdown", e => {
        arrastrando = true;
        manija.setPointerCapture(e.pointerId);
        const rect = contenedor.getBoundingClientRect();
        offsetX = e.clientX - rect.left;
        offsetY = e.clientY - rect.top;
        contenedor.classList.add("arrastrado");
    });

    manija.addEventListener("pointermove", e => {
        if (!arrastrando) return;

        let x = e.clientX - offsetX;
        let y = e.clientY - offsetY;

        // No dejar que se salga de la pantalla
        const maxX = window.innerWidth - contenedor.offsetWidth;
        const maxY = window.innerHeight - contenedor.offsetHeight;
        x = Math.max(0, Math.min(x, maxX));
        y = Math.max(0, Math.min(y, maxY));

        contenedor.style.left = x + "px";
        contenedor.style.top = y + "px";

        posicionGuardada = { x, y };
    });

    manija.addEventListener("pointerup", e => {
        arrastrando = false;
        manija.releasePointerCapture(e.pointerId);
    });

    // Doble tap/click en la manija: regresa el teclado a su posición original
    manija.addEventListener("dblclick", resetearPosicion);
    let ultimoTap = 0;
    manija.addEventListener("pointerup", () => {
        const ahora = Date.now();
        if (ahora - ultimoTap < 300) resetearPosicion();
        ultimoTap = ahora;
    });
}

function resetearPosicion() {
    posicionGuardada = null;
    const contenedor = document.getElementById("teclado-virtual-contenedor");
    contenedor.classList.remove("arrastrado");
    contenedor.style.left = "";
    contenedor.style.top = "";
}

function manejarShift() {
    mayusculasActivas = !mayusculasActivas;
    keyboard.setOptions({
        layoutName: mayusculasActivas ? "shift" : "default",
        display: { ...DISPLAY_BASE, "{shift}": mayusculasActivas ? "ABC" : "abc" }
    });
}

function manejarSimbolos() {
    keyboard.setOptions({ layoutName: "symbols" });
}

function volverALetras() {
    keyboard.setOptions({ layoutName: mayusculasActivas ? "shift" : "default" });
}

function mostrarTeclado(input) {
    inputActivo = input;
    if (!keyboard) crearTeclado();
    mayusculasActivas = false;

    // Numérico si se pidió explícitamente (data-teclado="numerico") o si
    // el propio campo ya es de tipo numérico/teléfono.
    const esNumerico = input.dataset.teclado === "numerico"
        || input.type === "number"
        || input.type === "tel";

    const contenedor = document.getElementById("teclado-virtual-contenedor");

    keyboard.setOptions({
        layoutName: esNumerico ? "numerico" : "default",
        display: DISPLAY_BASE
    });
    contenedor.classList.toggle("teclado-numerico", esNumerico);

    const tituloEl = document.getElementById("teclado-virtual-titulo");
    if (tituloEl) {
        tituloEl.textContent = input.placeholder
            || input.getAttribute("aria-label")
            || input.name
            || "";
    }

    // Si el usuario ya lo movió antes, respeta esa posición
    if (posicionGuardada) {
        contenedor.classList.add("arrastrado");
        contenedor.style.left = posicionGuardada.x + "px";
        contenedor.style.top = posicionGuardada.y + "px";
    }

    keyboard.setInput(input.value);
    contenedor.classList.remove("oculto");
}

function ocultarTeclado() {
    const contenedor = document.getElementById("teclado-virtual-contenedor");
    if (contenedor) contenedor.classList.add("oculto");
    inputActivo = null;
}

export function inicializarTecladoVirtual() {
    // Captura en fase "capture" para detectar el foco aunque el campo esté
    // dentro de un modal que se acaba de abrir, y para cualquier input de
    // texto/número de toda la app (ver esCampoValido más arriba).
    document.addEventListener("focus", e => {
        if (esCampoValido(e.target)) mostrarTeclado(e.target);
    }, true);

    // Si el campo activo se destruye/oculta (p. ej. se cerró el modal que
    // lo contenía) sin que el usuario haya cerrado el teclado a mano, lo
    // cerramos solos para que no se quede flotando "pegado" en pantalla.
    document.addEventListener("focusout", e => {
        if (e.target === inputActivo) {
            setTimeout(() => {
                if (!inputActivo) return;
                const desapareció = !document.body.contains(inputActivo) || inputActivo.offsetParent === null;
                if (desapareció) ocultarTeclado();
            }, 0);
        }
    }, true);

    document.addEventListener("click", e => {
        const contenedor = document.getElementById("teclado-virtual-contenedor");
        if (contenedor && !contenedor.classList.contains("oculto")) {
            if (!e.target.closest("#teclado-virtual-contenedor") && !esCampoValido(e.target)) {
                ocultarTeclado();
            }
        }
    });
}