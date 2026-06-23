# Integración Modal → Plano Espacial: Guía Rápida

## 📋 Resumen de Cambios

### Archivos Modificados

1. **PlanoEspacialController.php**
   - ✅ Método `store()` agregado
   - ✅ Método `crearMesa()` mejorado
   - ✅ Validación robusta de campos

2. **routes/web.php**
   - ✅ Ruta `/admin/plano-espacial/api/store` agregada

3. **plano-espacial.blade.php**
   - ✅ Modal mejorado con campo "Estado Inicial"
   - ✅ JavaScript optimizado para inyección en DOM
   - ✅ Función `crearNuevaMesa()` mejorada

### Archivos Nuevos

1. **public/js/plano-espacial.js**
   - Clase modular `PlanoEspacialMesas` reutilizable
   - Separación de responsabilidades
   - Fácil mantenimiento y extensión

2. **database/migrations/2026_06_23_000000_add_plano_espacial_columns_to_mesas_table.php**
   - Migración para campos del plano

---

## 🚀 Flujo Paso a Paso

### 1️⃣ Usuario abre el Plano Espacial
```
http://tuapp.local/admin/plano-espacial
```
✓ Carga todas las mesas guardadas del servidor
✓ Las mesas aparecen en sus coordenadas previas

### 2️⃣ Usuario hace clic en "Agregar Mesa"
```
[Botón "Agregar"] → Modal se abre
```

### 3️⃣ Usuario completa el formulario
```
┌─ Formulario Modal ────────────┐
│ Número de Mesa:  [M1      ]  │
│ Capacidad:       [4       ]  │
│ Estado Inicial:  [Disponible]│
│                              │
│    [Cancelar]  [CREAR MESA] │
└──────────────────────────────┘
```

### 4️⃣ JavaScript intercepta el submit
```javascript
// Se ejecuta crearNuevaMesa()
// Valida campos en frontend
// Si OK → Envía POST a /admin/plano-espacial/api/store
```

### 5️⃣ Backend procesa la petición
```php
// PlanoEspacialController::store()
1. Valida datos (unique, tipos, etc.)
2. Crea mesa en BD
3. Retorna JSON con mesa creada
```

### 6️⃣ Respuesta JSON exitosa
```json
{
    "success": true,
    "data": {
        "id": 123,
        "numero": "M1",
        "capacidad": 4,
        "estado": "disponible",
        "posicion_x": 20,
        "posicion_y": 20,
        ...
    }
}
```

### 7️⃣ Frontend inyecta en el DOM
```javascript
// JavaScript:
1. Crea elemento HTML para la mesa
2. Lo agrega al contenedor #planoContenedor
3. Selecciona la mesa automáticamente
4. Habilita drag & drop
5. Cierra el modal
6. Muestra notificación "✓ Mesa creada"
```

### 8️⃣ Usuario ve la mesa en el plano
```
┌─ PLANO ESPACIAL ──────────────────┐
│                                   │
│   ◯ M1          ┌─ Propiedades ──┐
│  (4 pax)        │ Número: M1     │
│                 │ Capacidad: 4   │
│                 │ Zona: Salón    │
│                 │ ...            │
│   ◯ M2          │ [Eliminar]     │
│  (2 pax)        └────────────────┘
│                                   │
└───────────────────────────────────┘
```

### 9️⃣ Usuario puede arrastrar la mesa
```
✓ La mesa está seleccionada (anillo blanco)
✓ Modo edición debe estar activo
✓ El usuario arrastra a la posición deseada
✓ La posición se actualiza en tiempo real
```

### 🔟 Usuario guarda el plano
```
[Botón "Guardar"] → POST a /admin/plano-espacial/api/guardar
↓
Backend actualiza posiciones en BD
↓
Notificación: "✓ Plano guardado correctamente"
↓
Desactiva modo edición
```

---

## 💻 Código del Controlador

```php
// App/Http/Controllers/Admin/PlanoEspacialController.php

public function store(Request $request): JsonResponse
{
    try {
        $validated = $request->validate([
            'numero' => 'required|string|unique:mesas,numero',
            'capacidad' => 'required|integer|min:1|max:20',
            'estado' => 'nullable|in:disponible,ocupada,reservada,limpieza',
            'zona' => 'nullable|in:salon,terraza,vip',
            'forma' => 'nullable|in:redonda,cuadrada',
            'posicion_x' => 'nullable|numeric|min:0',
            'posicion_y' => 'nullable|numeric|min:0',
            'ancho' => 'nullable|integer|min:30|max:200',
            'alto' => 'nullable|integer|min:30|max:200',
        ]);

        $mesa = Mesa::create([
            'numero' => $validated['numero'],
            'capacidad' => $validated['capacidad'],
            'estado' => $validated['estado'] ?? 'disponible',
            'zona' => $validated['zona'] ?? 'salon',
            'forma' => $validated['forma'] ?? 'redonda',
            'posicion_x' => $validated['posicion_x'] ?? 20,
            'posicion_y' => $validated['posicion_y'] ?? 20,
            'ancho' => $validated['ancho'] ?? 60,
            'alto' => $validated['alto'] ?? 60,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mesa creada exitosamente.',
            'data' => [
                'id' => $mesa->id,
                'numero' => $mesa->numero,
                'capacidad' => $mesa->capacidad,
                'estado' => $mesa->estado,
                'zona' => $mesa->zona,
                'forma' => $mesa->forma,
                'posicion_x' => $mesa->posicion_x,
                'posicion_y' => $mesa->posicion_y,
                'ancho' => $mesa->ancho,
                'alto' => $mesa->alto,
                'estadoVisual' => $mesa->estado_visual,
                'mesero' => 'Sin asignar',
                'totalConsumo' => 0,
                'ordenesActivas' => 0,
            ],
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Errores de validación.',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al crear la mesa: ' . $e->getMessage(),
        ], 500);
    }
}
```

---

## 📝 Código JavaScript (Vista Blade)

```javascript
async function crearNuevaMesa() {
    const numero = document.getElementById('newNumero').value.trim();
    const capacidad = parseInt(document.getElementById('newCapacidad').value);
    const estado = document.getElementById('newEstado').value || 'disponible';

    if (!numero) {
        mostrarNotificacion('Por favor ingresa un número de mesa', 'error');
        return;
    }

    if (isNaN(capacidad) || capacidad < 1) {
        mostrarNotificacion('Capacidad debe ser un número válido', 'error');
        return;
    }

    try {
        const response = await fetch(config.apiStore, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
            
            // 1. Agregar a lista de mesas en memoria
            estado.mesasActuales.push(nuevaMesa);
            estado.mesasOriginales.push(JSON.parse(JSON.stringify(nuevaMesa)));
            
            // 2. Crear elemento en DOM
            const elemento = crearElementoMesa(nuevaMesa);
            document.getElementById('planoContenedor').appendChild(elemento);
            
            // 3. Seleccionar automáticamente
            seleccionarMesa(nuevaMesa);
            
            // 4. Actualizar conteo
            actualizarTotalMesas();
            
            // 5. Cerrar modal
            cerrarModalCrear();
            
            // 6. Notificación
            mostrarNotificacion('✓ Mesa creada exitosamente', 'success');
            
            // 7. Habilitar interacción si estamos en edición
            if (estado.modoEdicion) {
                habilitarInteraccionMesa(elemento, nuevaMesa);
            }
        } else {
            const errorMsg = data.errors?.numero?.[0] || data.message || 'Error al crear';
            mostrarNotificacion(errorMsg, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarNotificacion('Error al crear la mesa', 'error');
    }
}
```

---

## ✅ Checklist de Instalación

- [ ] Ejecutar: `php artisan migrate`
- [ ] Verificar que las rutas estén registradas
- [ ] Abrir: `http://localhost/admin/plano-espacial`
- [ ] Probar: Crear una mesa desde el modal
- [ ] Verificar: Mesa aparece en el plano
- [ ] Verificar: Arrastrar la mesa (solo en modo edición)
- [ ] Verificar: Guardar plano persiste datos

---

## 🎯 Puntos Clave de la Integración

1. **Posición Inicial (20, 20)**
   - Asegura que la nueva mesa sea visible
   - El usuario puede arrastrarla inmediatamente

2. **Sin Recargar Página**
   - Todo se hace con AJAX
   - La experiencia es fluida

3. **Inyección Dinámico en DOM**
   - El elemento nuevo tiene todos los eventos
   - Drag & drop funciona al instante

4. **Validación Doble**
   - Frontend: Feedback rápido
   - Backend: Seguridad y persistencia

5. **Estados en Tiempo Real**
   - La mesa aparece seleccionada
   - Panel lateral muestra propiedades
   - Listo para editar o arrastrar

---

## 🐛 Solución de Problemas

### La mesa no aparece en el plano
```
✓ Verificar que la respuesta sea success: true
✓ Revisar errores en consola del navegador
✓ Comprobar que el contenedor tenga ID correcto
```

### El drag & drop no funciona
```
✓ Verificar que el modo edición esté activo
✓ Comprobar que el elemento tenga clase "mesa-elemento"
✓ Revisar listeners en consola
```

### Error de validación "numero already exists"
```
✓ El número ya existe en BD
✓ Usar un número diferente
✓ Verificar tabla de mesas en BD
```

### Modal no se cierra después de crear
```
✓ Revisar función cerrarModalCrear()
✓ Verificar que modal tenga ID "modalCrearMesa"
✓ Comprobar clases de ocultar/mostrar
```

---

## 📚 Referencias Útiles

- Controlador: [PlanoEspacialController.php](vsls:/app/Http/Controllers/Admin/PlanoEspacialController.php)
- Vista: [plano-espacial.blade.php](vsls:/resources/views/admin/mesas/plano-espacial.blade.php)
- Rutas: [web.php](vsls:/routes/web.php) (sección Plano Espacial)
- Modelo: [Mesa.php](vsls:/app/Models/Mesa.php)
- JS Modular: [public/js/plano-espacial.js](vsls:/public/js/plano-espacial.js)
