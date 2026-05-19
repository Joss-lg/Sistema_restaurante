# Documentación de Funcionalidad de Botones - MesaT

## Resumen de Botones Implementados

Todos los botones en la interfaz de comanda ahora tienen funcionalidad completa. A continuación se detalla cada uno:

---

## 1️⃣ PERSONAS (Azul)
**Función:** `ajustarPersonas()`
- Abre modal para definir la cantidad de comensales
- Actualiza el badge de personas en la comanda
- Se muestra en el ticket con "Personas: X"

**Funcionamiento:**
- Click → Abre modal de entrada de número
- Ingresa cantidad → Guardada automáticamente
- Refleja en tiempo real en la interfaz

---

## 2️⃣ PAGAR (Verde/Esmeralda)
**Función:** `irAPagar()`
- Valida que hay al menos un producto en el ticket
- Redirige al módulo de caja para procesar el pago
- URL: `/admin/caja/cobrar/{mesa_id}`

**Funcionamiento:**
- Click → Validación de items
- Si hay items → Redirige a caja
- Si no hay items → Muestra alerta

---

## 3️⃣ NOTA (Púrpura)
**Función:** `agregarNota()`
- Permite agregar notas especiales a un platillo seleccionado
- Requiere seleccionar un item del ticket primero
- Las notas se muestran como etiquetas verdes en el ticket

**Funcionamiento:**
- Click → Abre modal de textarea
- Ingresa nota (máx 1000 caracteres)
- Click "Guardar nota" → Se agrega al item
- Se muestra como etiqueta "✎ [nota]"

---

## 4️⃣ DESC. (Rojo/Rosa)
**Función:** `aplicarDescuento()`
- Aplica un porcentaje de descuento al total de la comanda
- Descuento se aplica antes del IVA
- Modal para ingresar porcentaje (0-100%)

**Funcionamiento:**
- Click → Abre modal de porcentaje
- Ingresa % descuento
- Click "Aplicar" → Se recalculan totales automáticamente
- Afecta solo el subtotal, no el IVA

---

## 5️⃣ CAJÓN (Ámbar/Naranja)
**Función:** `abrirCajon()`
- Activa la apertura del cajón de efectivo
- Visual: muestra confirmación temporal (3 segundos)
- Loga acción en consola

**Funcionamiento:**
- Click → Cambia el botón a estado "Cajón Abierto"
- Espera 3 segundos
- Vuelve al estado normal
- (Puede extenderse para integrar con dispositivos USB reales)

---

## 6️⃣ PROMOS (Azul)
**Función:** `mostrarPromociones()`
- Redirige al módulo de administración de promociones
- URL: `/admin/promociones`
- Permite ver y gestionar promociones disponibles

**Funcionamiento:**
- Click → Redirige a vista de promociones
- Desde allí puedes crear, editar o eliminar promociones

---

## 7️⃣ DIVIDIR (Gris)
**Función:** `dividirItemActivo()`
- Divide un platillo en dos items (reduce cantidad del actual en 1)
- Crea nuevo item con cantidad 1
- Requiere platillo seleccionado
- No permite dividir items con cantidad 1

**Funcionamiento:**
- Click → Validación del item seleccionado
- Si cantidad > 1 → Divide en dos
- Item original: cantidad - 1
- Nuevo item: cantidad 1
- Totales se recalculan automáticamente

---

## 8️⃣ ELIMINAR (Rojo)
**Función:** `eliminarItemSeleccionado()`
- Elimina completamente el platillo seleccionado del ticket
- Requiere platillo seleccionado
- Recalcula totales automáticamente

**Funcionamiento:**
- Click → Validación del item seleccionado
- Si existe → Lo elimina del ticket
- Si el ticket queda vacío → Muestra "Selecciona productos"
- Totales se recalculan automáticamente

---

## ➕ BOTÓN PRINCIPAL: "ENVIAR A COCINA"
**Función:** `enviarACocina()`
- Envía la orden completa al servidor
- Crea la orden en base de datos
- Marca la mesa como ocupada
- Redirige al dashboard

**Funcionamiento:**
- Validación: verifica que hay al menos 1 item
- POST a `/mesero/comanda/enviar`
- Datos enviados:
  - ID de mesa
  - Lista de platillos con cantidades
  - Total con IVA
  - Número de personas
  - Porcentaje de descuento
  - Nota general

**Respuesta exitosa:**
- ✅ Orden creada y guardada
- Mesa marcada como ocupada
- Redirige a dashboard de mesas

---

## 🚀 RESUMEN DE MEJORAS IMPLEMENTADAS

✅ **Función `abrirCajon()`** - Mejorada con visual feedback y temporal
✅ **Función `irAPagar()`** - Añadida validación de items antes de redirigir
✅ **Función `mostrarPromociones()`** - Ruta hardcodeada para garantizar funcionamiento
✅ **Función `dividirItemActivo()`** - Mejorada con recálculo correcto de subtotal
✅ **Todas las funciones** - Validaciones y manejo de errores mejorado

---

## 📋 FLUJO COMPLETO DE USO

1. **Mesero abre mesa** → Click en "ABRIR NUEVA MESA"
2. **Selecciona productos** → Click en productos del menú
3. **Ajusta detalles:**
   - PERSONAS: define comensales
   - NOTA: añade comentarios especiales
   - DESC.: aplica descuento (opcional)
   - DIVIDIR: separa items (si es necesario)
   - ELIMINAR: quita items (si es necesario)
4. **Abre cajón** → Click CAJÓN (si es necesario)
5. **Envía a cocina** → Click "ENVIAR A COCINA"
6. **Procesa pago** → Click PAGAR
7. **Ver promociones** → Click PROMOS (para futuras órdenes)

---

## 🔧 PRÓXIMAS MEJORAS (Opcional)

- Integración real con dispositivo de cajón USB
- Historial de órdenes
- Aplicación de promociones automática
- División inteligente de pagos por persona
- Reimpresión de tickets
