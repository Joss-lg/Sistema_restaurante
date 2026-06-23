# 🎯 Instrucciones de Prueba: Modal → Mapa de Mesas

## ✅ Lo que está listo

Tu sistema de **creación de mesas con inyección inmediata en el mapa** está completamente configurado.

---

## 🚀 Antes de Empezar

### 1. Ejecutar Migración (si es necesario)

```bash
php artisan migrate
```

Esto agregará los campos necesarios:
- `posicion_x` (posición X del mapa)
- `posicion_y` (posición Y del mapa)
- `zona` (Salón/Terraza/VIP)

---

## 🧪 Prueba la Funcionalidad Paso a Paso

### Paso 1: Abre la vista `/admin/mesas`

```
http://localhost/admin/mesas
```

Deberías ver:
- 📊 Un mapa grande (lienzo) en la parte central
- 🎮 Botones de control: "Ver Lista", "Editar", "Unir"
- 📍 Mesas distribuidas en el mapa (si existen)
- 🎨 Colores según estado (verde=libre, azul/amarillo=ocupada, púrpura=reservada)
- 🔘 Filtros por zona: TODAS, SALÓN, TERRAZA, VIP

### Paso 2: Haz clic en **"+ MESA"** (esquina superior derecha)

Aparecerá un modal elegante con:
```
┌─────────────────────────┐
│  Nueva Mesa             │
│  ─────────────────────  │
│                         │
│  Número/Nombre:         │
│  [_________________]    │
│                         │
│  Capacidad (Personas):  │
│  [_________________]    │
│                         │
│  Estado Inicial:        │
│  [Disponible  ▼]        │
│                         │
│  [Cancelar]  [Crear]    │
└─────────────────────────┘
```

### Paso 3: Completa el formulario

Ejemplo:
```
Número:      M-05
Capacidad:   6
Estado:      Disponible
```

### Paso 4: Presiona **"CREAR MESA"**

¡**MAGIA QUE SUCEDE AQUÍ:**

```
⚡ Validación en el navegador
   ✓ Número no vacío
   ✓ Capacidad numérica

📡 Se envía POST a /admin/mesas/api
   └─ Laravel valida
   └─ Se crea en BD
   └─ Se retorna JSON

✨ **INYECCIÓN INMEDIATA**
   1️⃣  Se agrega a la lista de mesas
   2️⃣  Se cambia vista a "Todas" (para ver)
   3️⃣  Se re-renderiza el mapa
   4️⃣  Se cierra el modal

🎉 LA MESA APARECE EN EL MAPA
   ✅ Con animación suave
   ✅ En posición visible (50, 50)
   ✅ En color VERDE (disponible)
   ✅ LISTA PARA ARRASTRAR
```

---

## 🎮 Lo que Puedes Hacer Inmediatamente

### ✋ Arrastra la Mesa

1. Haz clic en la mesa nueva y **mantén presionado**
2. Arrastra por el mapa
3. Verás que se alinea a una grilla de 30px
4. **Suelta** para guardar posición

```
Cursor: grab → grabbing → grab
```

### 📝 Edita la Mesa

1. Haz clic derecho en la mesa → Aparece menú
2. Opción **"Editar"** → Abre modal
3. Cambia número, capacidad o estado
4. Presiona **"Guardar"**

### 🔗 Abre Cobrar

1. Menú → **"Cobrar"**
2. Te lleva a la pantalla de cobranza

### ❌ Elimina la Mesa

1. Menú → **"Eliminar"**
2. Confirmación de seguridad
3. Se borra de la BD y del mapa

### 🔄 Modo Edición (Drag & Drop)

1. Haz clic en botón **"Editar"** (toolbar)
2. Botón cambia a color azul
3. Ahora puedes arrastrar mesas
4. Haz clic nuevamente para **"Guardar"**

---

## 📊 Ver la Mesa en Diferentes Vistas

### Mapa Visual (Por Defecto)
```
Clic en "Ver Mapa"
├─ Ve la mesa visualmente
├─ Posicionada espacialmente
├─ Con indicador visual de estado
└─ Arrastra a cualquier posición
```

### Vista Lista
```
Clic en "Ver Lista"
├─ Ve las mesas en una cuadrícula
├─ Información: Número, Estado, Capacidad
├─ Mismo menú (Editar/Cobrar/Eliminar)
└─ Haz clic derecha para acciones
```

### Filtros por Zona
```
Botones: TODAS | SALÓN | TERRAZA | VIP

- TODAS: Muestra todas las mesas
- SALÓN: Solo mesas del salón
- TERRAZA: Solo mesas de terraza
- VIP: Solo mesas VIP

💡 Cuando creas una mesa, automáticamente
   va a zona "Salón" y se muestra en "TODAS"
```

---

## ✨ Animaciones y Efectos

### Al Crear:
```css
Entrada suave con transición
Elevación (scale-up) al seleccionar
Brillo alrededor según estado
```

### Al Arrastrar:
```css
Cursor cambia (grab → grabbing)
Sombra aumenta
Se eleva sobre otras mesas (z-index)
Snaps a grilla cada 30px
```

### Notificación:
```
Toast en esquina superior derecha
Desaparece automáticamente en 4s
Color verde ✓ (éxito)
Ícono: ✨ Mesa creada e inyectada
```

---

## 🔍 Verificación en Consola (F12)

Abre Developer Tools (F12) → Console

Si ves esto, todo está funcionando:
```javascript
// Debería haber console.log de:
Mesa creada: {id: 123, numero: "M-05", ...}
Mapa re-renderizado
```

---

## 📱 Datos que se Guardan

Cuando creas una mesa, en BD se almacena:

| Campo | Valor | Detalle |
|-------|-------|--------|
| id | Auto | ID único |
| numero | "M-05" | Único en BD |
| capacidad | 6 | Personas |
| estado | disponible | libre/ocupada/reservada |
| posicion_x | 50 | Posición horizontal (px) |
| posicion_y | 50 | Posición vertical (px) |
| zona | Salón | Area (por defecto) |

---

## 🐛 Posibles Problemas

### ❌ "Mesa no aparece en el mapa"
**Solución:**
- Verifica estar en vista "Mapa" (no Lista)
- Haz clic en filtro "TODAS"
- Scroll en el lienzo (espacio es grande)
- Abre F12 y revisa si hay errores en Console

### ❌ "Error: Número duplicado"
**Solución:**
- Ese número ya existe en la BD
- Usa un número diferente, ej: "M-Nueva-5"
- En la vista Lista puedes ver todos los números

### ❌ "No puedo arrastrar la mesa"
**Solución:**
- Debes estar en **modo EDITAR**
- Haz clic en botón "Editar" (se pone azul)
- Ahora puedes arrastrar todas las mesas
- Vuelve a hacer clic para "Guardar"

### ❌ "Modal no se cierra"
**Solución:**
- Revisa que los campos estén completos
- F12 → Console para ver el error exacto
- Recarga la página (Ctrl+R)

### ❌ "Posición no se guarda"
**Solución:**
- Asegúrate de SOLTAR después de arrastrar
- En modo EDITAR, debes presionar botón "Guardar" al final
- Recarga la página para verificar

---

## 📚 API Endpoints (Para Referencia)

```bash
# Crear mesa
POST /admin/mesas/api
Content-Type: application/json
{
    "numero": "M-05",
    "capacidad": 6,
    "estado": "disponible"  // opcional
}

# Obtener todas
GET /admin/mesas/api/mesas

# Actualizar posición
PATCH /admin/mesas/api/{id}/posicion
{
    "posicion_x": 100,
    "posicion_y": 200
}

# Editar mesa
PUT /admin/mesas/api/{id}
{
    "numero": "M-05-A",
    "capacidad": 8,
    "estado": "reservada"
}

# Eliminar
DELETE /admin/mesas/api/{id}

# Guardar todas las posiciones
POST /admin/mesas/api/posiciones
{
    "coordenadas": [
        {"id": 1, "x": 100, "y": 150},
        {"id": 2, "x": 300, "y": 200}
    ]
}
```

---

## 🎯 Resumen del Flujo Técnico

```
Usuario
  ↓
[Clic "+ MESA"] → Modal abierto
  ↓
[Completa formulario]
  ↓
[Clic "CREAR MESA"]
  ↓
⚡ JAVASCRIPT
  • Valida campos
  • POST a /admin/mesas/api
  ↓
🗄️ SERVIDOR
  • MesaController::store()
  • Valida en BD
  • Crea registro
  • Retorna JSON
  ↓
⚡ JAVASCRIPT
  • Recibe éxito
  • Agrega a estadoGlobal.mesas
  • Re-renderiza mapaMesas()
  • Cierra modal
  • Muestra toast
  ↓
✨ PANTALLA
  • Mesa aparece con animación
  • Posición (50, 50)
  • Color verde
  • Arrastrable
  ↓
⏱️ 3 segundos después
  • Recarga mesas del servidor
  • Estado sincronizado 100%
```

---

## 🚀 ¡Listo para Usar!

**Tu sistema está completamente configurado y funcional.**

Ahora cuando crees una mesa:
✅ Aparece inmediatamente  
✅ Con animación  
✅ Se puede arrastrar  
✅ Se puede editar  
✅ Se guarda en la BD  
✅ Sincroniza en tiempo real  

---

**¿Preguntas? Revisa el archivo `INTEGRACION_MODAL_MAPA_MESAS.md` para documentación completa.**
