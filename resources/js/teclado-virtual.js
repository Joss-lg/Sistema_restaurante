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

function crearTeclado() {
    const contenedor = document.getElementById("teclado-virtual-contenedor");

    // Manija de arrastre (se crea una sola vez, arriba del teclado)
    const manija = document.createElement("div");
    manija.className = "teclado-drag-handle";
    manija.innerHTML = "⠿⠿⠿ &nbsp;Mover&nbsp; ⠿⠿⠿";
    contenedor.insertBefore(manija, contenedor.firstChild);

    habilitarArrastre(contenedor, manija);

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

    const esNumerico = input.dataset.teclado === "numerico";
    const contenedor = document.getElementById("teclado-virtual-contenedor");

    keyboard.setOptions({
        layoutName: esNumerico ? "numerico" : "default",
        display: DISPLAY_BASE
    });
    contenedor.classList.toggle("teclado-numerico", esNumerico);

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
    document.addEventListener("focus", e => {
        if (e.target.matches(".touch-input")) mostrarTeclado(e.target);
    }, true);

    document.addEventListener("click", e => {
        const contenedor = document.getElementById("teclado-virtual-contenedor");
        if (contenedor && !contenedor.classList.contains("oculto")) {
            if (!e.target.closest("#teclado-virtual-contenedor") && !e.target.matches(".touch-input")) {
                ocultarTeclado();
            }
        }
    });
}