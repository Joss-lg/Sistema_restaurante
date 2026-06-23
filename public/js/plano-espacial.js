/**
 * Plano Espacial - Sistema de Gestión Interactivo de Mesas
 * Integra formulario modal, AJAX, y drag & drop en tiempo real
 * 
 * @author Ollintem Pro
 * @version 1.0.0
 */

class PlanoEspacialMesas {
    constructor(config = {}) {
        this.config = {
            apiBase: config.apiBase || '/admin/plano-espacial/api/mesas',
            apiGuardar: config.apiGuardar || '/admin/plano-espacial/api/guardar',
            apiStore: config.apiStore || '/admin/plano-espacial/api/store',
            apiEliminar: config.apiEliminar || '/admin/plano-espacial/api/eliminar',
            csrfToken: config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content,
            ...config,
        };

        this.estado = {
            modoEdicion: false,
            mesaSeleccionada: null,
            mesasOriginales: [],
            mesasActuales: [],
            arrastrando: null,
            offsetX: 0,
            offsetY: 0,
        };

        this.elementos = {
            contenedor: null,
            btnEditar: null,
            btnGuardar: null,
            btnCancelar: null,
            btnAgregar: null,
            modal: null,
        };
    }

    /**
     * Inicializar el sistema
     */
    async init() {
        this.cacheElementos();
        await this.cargarMesas();
        this.setupEventos();
        console.log('✓ PlanoEspacialMesas inicializado');
    }

    /**
     * Cachear referencias a elementos DOM
     */
    cacheElementos() {
        this.elementos = {
            contenedor: document.getElementById('planoContenedor'),
            btnEditar: document.getElementById('btnEditar'),
            btnGuardar: document.getElementById('btnGuardar'),
            btnCancelar: document.getElementById('btnCancelar'),
            btnAgregar: document.getElementById('btnAgregarMesa'),
            modal: document.getElementById('modalCrearMesa'),
            inputNumero: document.getElementById('newNumero'),
            inputCapacidad: document.getElementById('newCapacidad'),
            selectEstado: document.getElementById('newEstado'),
            btnConfirmar: document.getElementById('btnConfirmarNueva'),
            filtroZona: document.getElementById('filtroZona'),
            panelVacio: document.getElementById('panelVacio'),
            formularioPropiedades: document.getElementById('formularioMesa'),
            totalMesas: document.getElementById('totalMesas'),
        };
    }

    /**
     * Cargar mesas desde el servidor
     */
    async cargarMesas(zona = '') {
        try {
            const url = zona ? `${this.config.apiBase}?zona=${zona}` : this.config.apiBase;
            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                this.estado.mesasActuales = data.data;
                this.estado.mesasOriginales = JSON.parse(JSON.stringify(data.data));
                this.renderizar();
            }
        } catch (error) {
            console.error('Error al cargar mesas:', error);
            this.notificar('Error al cargar las mesas', 'error');
        }
    }

    /**
     * Renderizar todas las mesas en el lienzo
     */
    renderizar() {
        this.elementos.contenedor.innerHTML = '';
        this.estado.mesasActuales.forEach(mesa => {
            const elemento = this.crearElementoMesa(mesa);
            this.elementos.contenedor.appendChild(elemento);
        });
        this.actualizarConteo();
    }

    /**
     * Crear elemento DOM para una mesa
     */
    crearElementoMesa(mesa) {
        const div = document.createElement('div');
        div.className = 'mesa-elemento absolute cursor-move transition-all';
        div.dataset.id = mesa.id;
        div.dataset.numero = mesa.numero;

        const coloresEstado = {
            'blue': 'bg-blue-500 hover:bg-blue-600',
            'yellow': 'bg-yellow-500 hover:bg-yellow-600',
            'red': 'bg-red-500 hover:bg-red-600',
        };

        const colorClase = coloresEstado[mesa.estadoVisual] || 'bg-blue-500 hover:bg-blue-600';
        const borderRadius = mesa.forma === 'redonda' ? '50%' : '8px';

        div.style.left = mesa.posicion_x + 'px';
        div.style.top = mesa.posicion_y + 'px';
        div.style.width = mesa.ancho + 'px';
        div.style.height = mesa.alto + 'px';
        div.style.borderRadius = borderRadius;

        div.innerHTML = `
            <div class="w-full h-full ${colorClase} flex items-center justify-center rounded-inherit border-2 border-slate-900 shadow-lg">
                <div class="text-center text-white font-bold text-sm pointer-events-none">
                    <div class="text-lg">${mesa.numero}</div>
                    <div class="text-xs opacity-75">${mesa.capacidad} pax</div>
                </div>
            </div>
        `;

        div.addEventListener('mousedown', (e) => this.iniciarArrastre(e, mesa));
        div.addEventListener('click', (e) => {
            if (!this.estado.arrastrando && this.estado.modoEdicion) {
                e.stopPropagation();
                this.seleccionar(mesa);
            }
        });

        return div;
    }

    /**
     * Iniciar arrastre de una mesa
     */
    iniciarArrastre(e, mesa) {
        if (!this.estado.modoEdicion) return;

        this.estado.arrastrando = mesa;
        this.estado.offsetX = e.clientX - e.target.getBoundingClientRect().left;
        this.estado.offsetY = e.clientY - e.target.getBoundingClientRect().top;

        document.addEventListener('mousemove', (evt) => this.moverMesa(evt));
        document.addEventListener('mouseup', () => this.terminarArrastre());
    }

    /**
     * Mover mesa durante el arrastre
     */
    moverMesa(e) {
        if (!this.estado.arrastrando) return;

        const rect = this.elementos.contenedor.getBoundingClientRect();
        let x = e.clientX - rect.left - this.estado.offsetX;
        let y = e.clientY - rect.top - this.estado.offsetY;

        x = Math.max(0, Math.min(x, rect.width - this.estado.arrastrando.ancho));
        y = Math.max(0, Math.min(y, rect.height - this.estado.arrastrando.alto));

        this.estado.arrastrando.posicion_x = Math.round(x);
        this.estado.arrastrando.posicion_y = Math.round(y);

        const elemento = document.querySelector(`[data-id="${this.estado.arrastrando.id}"]`);
        if (elemento) {
            elemento.style.left = x + 'px';
            elemento.style.top = y + 'px';
        }
    }

    /**
     * Terminar arrastre de mesa
     */
    terminarArrastre() {
        if (this.estado.arrastrando?.id === this.estado.mesaSeleccionada?.id) {
            this.actualizarPropiedades();
        }
        this.estado.arrastrando = null;
    }

    /**
     * Seleccionar una mesa
     */
    seleccionar(mesa) {
        document.querySelectorAll('.mesa-elemento').forEach(el => {
            el.classList.remove('ring-4', 'ring-white');
        });

        this.estado.mesaSeleccionada = mesa;
        const elemento = document.querySelector(`[data-id="${mesa.id}"]`);
        if (elemento) {
            elemento.classList.add('ring-4', 'ring-white');
        }

        this.mostrarPropiedades(mesa);
    }

    /**
     * Mostrar propiedades de mesa seleccionada
     */
    mostrarPropiedades(mesa) {
        this.elementos.panelVacio.classList.add('hidden');
        this.elementos.formularioPropiedades.classList.remove('hidden');

        document.getElementById('propNumero').value = mesa.numero;
        document.getElementById('propCapacidad').value = mesa.capacidad;
        document.getElementById('propZona').value = mesa.zona || 'salon';
        document.getElementById('propForma').value = mesa.forma || 'redonda';
        document.getElementById('propAncho').value = mesa.ancho || 60;
        document.getElementById('propAlto').value = mesa.alto || 60;

        const botonesAccion = document.getElementById('botonesAccion');
        const btnActualizar = document.getElementById('btnActualizar');

        if (!this.estado.modoEdicion) {
            ['propCapacidad', 'propZona', 'propForma', 'propAncho', 'propAlto'].forEach(id => {
                document.getElementById(id).disabled = true;
            });
            botonesAccion.classList.add('hidden');
        } else {
            ['propCapacidad', 'propZona', 'propForma', 'propAncho', 'propAlto'].forEach(id => {
                document.getElementById(id).disabled = false;
            });
            botonesAccion.classList.remove('hidden');
            btnActualizar.classList.add('hidden');
        }
    }

    /**
     * Actualizar propiedades de mesa
     */
    actualizarPropiedades() {
        if (!this.estado.mesaSeleccionada) return;

        this.estado.mesaSeleccionada.capacidad = parseInt(document.getElementById('propCapacidad').value);
        this.estado.mesaSeleccionada.zona = document.getElementById('propZona').value;
        this.estado.mesaSeleccionada.forma = document.getElementById('propForma').value;
        this.estado.mesaSeleccionada.ancho = parseInt(document.getElementById('propAncho').value);
        this.estado.mesaSeleccionada.alto = parseInt(document.getElementById('propAlto').value);

        this.renderizar();
        this.seleccionar(this.estado.mesaSeleccionada);
    }

    /**
     * Activar modo edición
     */
    activarEdicion() {
        this.estado.modoEdicion = true;
        this.elementos.btnEditar.classList.add('hidden');
        this.elementos.btnGuardar.classList.remove('hidden');
        this.elementos.btnCancelar.classList.remove('hidden');
        document.getElementById('modosEdicion').classList.remove('hidden');
        this.elementos.contenedor.style.cursor = 'grab';

        if (this.estado.mesaSeleccionada) {
            this.mostrarPropiedades(this.estado.mesaSeleccionada);
        }
    }

    /**
     * Desactivar modo edición
     */
    desactivarEdicion() {
        this.estado.modoEdicion = false;
        this.elementos.btnEditar.classList.remove('hidden');
        this.elementos.btnGuardar.classList.add('hidden');
        this.elementos.btnCancelar.classList.add('hidden');
        document.getElementById('modosEdicion').classList.add('hidden');
        this.elementos.contenedor.style.cursor = 'default';

        this.estado.mesasActuales = JSON.parse(JSON.stringify(this.estado.mesasOriginales));
        this.renderizar();
        this.limpiarSeleccion();
    }

    /**
     * Guardar plano
     */
    async guardar() {
        try {
            const response = await fetch(this.config.apiGuardar, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                },
                body: JSON.stringify({ mesas: this.estado.mesasActuales }),
            });

            const data = await response.json();

            if (data.success) {
                this.notificar('✓ Plano guardado correctamente', 'success');
                this.estado.mesasOriginales = JSON.parse(JSON.stringify(this.estado.mesasActuales));
                this.desactivarEdicion();
            } else {
                this.notificar(data.message || 'Error al guardar', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.notificar('Error al guardar el plano', 'error');
        }
    }

    /**
     * Crear nueva mesa desde modal
     */
    async crearMesa() {
        const numero = this.elementos.inputNumero.value.trim();
        const capacidad = parseInt(this.elementos.inputCapacidad.value);
        const estado = this.elementos.selectEstado.value || 'disponible';

        if (!numero) {
            this.notificar('Por favor ingresa un número de mesa', 'error');
            return;
        }

        if (isNaN(capacidad) || capacidad < 1) {
            this.notificar('Capacidad debe ser un número válido', 'error');
            return;
        }

        try {
            const response = await fetch(this.config.apiStore, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                },
                body: JSON.stringify({
                    numero,
                    capacidad,
                    estado,
                    zona: 'salon',
                    forma: 'redonda',
                    posicion_x: 20,
                    posicion_y: 20,
                }),
            });

            const data = await response.json();

            if (data.success) {
                const nuevaMesa = data.data;

                this.estado.mesasActuales.push(nuevaMesa);
                this.estado.mesasOriginales.push(JSON.parse(JSON.stringify(nuevaMesa)));

                const elemento = this.crearElementoMesa(nuevaMesa);
                this.elementos.contenedor.appendChild(elemento);

                this.seleccionar(nuevaMesa);
                this.actualizarConteo();
                this.cerrarModal();
                this.notificar('✓ Mesa creada exitosamente', 'success');

                if (this.estado.modoEdicion) {
                    this.elementos.inputNumero.focus();
                }
            } else {
                const errorMsg = data.errors?.numero?.[0] || data.message || 'Error al crear';
                this.notificar(errorMsg, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.notificar('Error al crear la mesa', 'error');
        }
    }

    /**
     * Eliminar mesa del plano
     */
    async eliminarMesa() {
        if (!this.estado.mesaSeleccionada) return;

        if (!confirm('¿Estás seguro de que deseas eliminar esta mesa del plano?')) return;

        try {
            const url = this.config.apiEliminar.replace('ID', this.estado.mesaSeleccionada.id);
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                },
            });

            const data = await response.json();

            if (data.success) {
                this.estado.mesasActuales = this.estado.mesasActuales.filter(
                    m => m.id !== this.estado.mesaSeleccionada.id
                );
                this.renderizar();
                this.limpiarSeleccion();
                this.notificar('Mesa eliminada del plano', 'success');
            }
        } catch (error) {
            this.notificar('Error al eliminar', 'error');
        }
    }

    /**
     * Abrir modal
     */
    abrirModal() {
        this.elementos.modal.classList.remove('hidden');
        this.elementos.inputNumero.focus();
    }

    /**
     * Cerrar modal
     */
    cerrarModal() {
        this.elementos.modal.classList.add('hidden');
        this.elementos.inputNumero.value = '';
        this.elementos.inputCapacidad.value = '4';
        this.elementos.selectEstado.value = 'disponible';
    }

    /**
     * Limpiar selección
     */
    limpiarSeleccion() {
        this.estado.mesaSeleccionada = null;
        document.querySelectorAll('.mesa-elemento').forEach(el => {
            el.classList.remove('ring-4', 'ring-white');
        });
        this.elementos.formularioPropiedades.classList.add('hidden');
        this.elementos.panelVacio.classList.remove('hidden');
    }

    /**
     * Actualizar conteo de mesas
     */
    actualizarConteo() {
        this.elementos.totalMesas.textContent = `Mesas: ${this.estado.mesasActuales.length}`;
    }

    /**
     * Mostrar notificación
     */
    notificar(mensaje, tipo = 'info') {
        const notif = document.getElementById('notificacion');
        notif.textContent = mensaje;
        const clases = {
            'success': 'bg-green-600',
            'error': 'bg-red-600',
            'info': 'bg-blue-600',
        };
        notif.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg text-white text-sm font-semibold z-50 transition-all ${clases[tipo] || clases.info}`;
        notif.classList.remove('hidden');

        setTimeout(() => {
            notif.classList.add('hidden');
        }, 3000);
    }

    /**
     * Setup de eventos
     */
    setupEventos() {
        // Botones principales
        this.elementos.btnEditar?.addEventListener('click', () => this.activarEdicion());
        this.elementos.btnGuardar?.addEventListener('click', () => this.guardar());
        this.elementos.btnCancelar?.addEventListener('click', () => this.desactivarEdicion());
        this.elementos.btnAgregar?.addEventListener('click', () => this.abrirModal());

        // Modal
        this.elementos.btnConfirmar?.addEventListener('click', () => this.crearMesa());

        // Cerrar modal
        document.querySelectorAll('.btnCerrarModal').forEach(btn => {
            btn.addEventListener('click', () => this.cerrarModal());
        });

        // Tecla Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.elementos.modal.classList.contains('hidden')) {
                this.cerrarModal();
            }
        });

        // Enter en inputs del modal
        ['newNumero', 'newCapacidad', 'newEstado'].forEach(id => {
            document.getElementById(id)?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.crearMesa();
                }
            });
        });

        // Cerrar modal al hacer click fuera
        this.elementos.modal?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                this.cerrarModal();
            }
        });

        // Filtro zona
        this.elementos.filtroZona?.addEventListener('change', (e) => {
            this.cargarMesas(e.target.value);
        });

        // Propiedades en tiempo real
        ['propCapacidad', 'propZona', 'propForma', 'propAncho', 'propAlto'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', () => this.actualizarPropiedades());
        });

        // Botón eliminar
        document.getElementById('btnEliminar')?.addEventListener('click', () => this.eliminarMesa());

        // Click en lienzo para deseleccionar
        this.elementos.contenedor?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget && this.estado.modoEdicion) {
                this.limpiarSeleccion();
            }
        });

        console.log('✓ Eventos configurados');
    }
}

// Exportar para uso en HTML
window.PlanoEspacialMesas = PlanoEspacialMesas;
