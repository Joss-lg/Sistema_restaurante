# 🚀 SISTEMA DE GRAMAJE - GUÍA DE IMPLEMENTACIÓN

## ✅ Cambios Realizados

Se ha agregado un **sistema completo de gramaje (peso)** a la interfaz de comanda. Ahora puedes especificar el peso de cada producto (ej: 700g de carnitas).

---

## 📋 PASOS PARA ACTIVAR

### 1️⃣ EJECUTAR MIGRACIÓN (Importante)

En tu terminal del proyecto Laravel, ejecuta:

```bash
php artisan migrate
```

Esto agregará la columna `gramaje` a la tabla `detalles_orden`.

### 2️⃣ ACTUALIZACIÓN DISPONIBLE INMEDIATAMENTE

El frontend ya está listo. Simplemente recarga la página en el navegador.

---

## 🎯 CÓMO FUNCIONA

### Flujo del Usuario:

1. **Hace clic en un producto** 
   - ✨ Se abre un modal pidiendo el **gramaje**

2. **Ingresa el peso** (Ej: 700 para 0.700kg)
   - Ej válidos: 250, 500, 700, 1000, 0.5, etc.
   - Puedes dejar vacío si no quieres especificar peso

3. **Presiona Enter o Click "Agregar"**
   - El producto se agrega al ticket
   - Se muestra con etiqueta de peso: **⚖️ 700g**

4. **El gramaje se guarda cuando envías a cocina**
   - Se almacena en la BD en `detalles_orden.gramaje`

---

## 📱 CARACTERÍSTICAS

| Característica | Descripción |
|---|---|
| **Modal Gramaje** | Pide peso cada vez que agregas producto |
| **Etiqueta Visual** | Muestra "⚖️ 700g" en naranja |
| **Opcional** | Puedes dejar en blanco (omitir) |
| **Divide con Gramaje** | Al usar botón DIVIDIR, se preserva |
| **BD Almacena** | Se guarda en tabla `detalles_orden` |
| **Enter Rápido** | Presionar Enter confirma sin esperar clic |

---

## 🔧 ARCHIVOS MODIFICADOS

| Archivo | Cambios |
|---|---|
| `resources/views/mesero/comanda.blade.php` | +Modal +Funciones de gramaje |
| `app/Models/DetalleOrden.php` | +Campo 'gramaje' al fillable |
| `app/Http/Controllers/ComandaController.php` | +Validación +Almacenamiento |
| `database/migrations/2026_05_12_...` | +Nueva migración (NUEVA) |

---

## 📊 EJEMPLOS DE USO

### Ejemplo 1: Carnitas
- Haces clic en "Carnitas"
- Modal: Ingresa **700** (para 0.700kg)
- Click "Agregar" → En ticket aparece: "Carnitas ⚖️ 700g"

### Ejemplo 2: Carne Asada
- Haces clic en "Carne Asada"
- Modal: Ingresa **500** (para 0.500kg)
- Ticket: "Carne Asada ⚖️ 500g"

### Ejemplo 3: Complemento sin Gramaje
- Haces clic en "Verduras"
- Modal: Dejas en blanco o presionas Enter
- Ticket: "Verduras" (sin etiqueta de peso)

---

## ⚠️ NOTAS IMPORTANTES

1. **La migración es obligatoria** antes de usar gramaje
2. **El gramaje es OPCIONAL** - puedes no ingresarlo
3. **Se almacena en BD** - podrás consultarlo después
4. **Compatible con todo** - notas, modificadores, dividir, etc.

---

## 🎨 VISUAL EN LA INTERFAZ

```
┌─────────────────────────────────────────┐
│  COMANDA - Mesa 5                       │
├─────────────────────────────────────────┤
│ 1x  Carnitas        ⚖️ 700g  $125.00    │
│ 2x  Carne Asada     ⚖️ 500g  $240.00    │
│ 1x  Verduras                 $45.00     │
├─────────────────────────────────────────┤
│ Subtotal: $410.00                       │
│ IVA (16%): $65.60                       │
│ TOTAL: $475.60                          │
└─────────────────────────────────────────┘
```

---

## 🚨 EN CASO DE PROBLEMAS

Si ves error en consola después de migrar:

```php
// Asegúrate que el modelo tiene en fillable:
protected $fillable = [
    ...
    'gramaje',
];
```

---

## ✨ PRÓXIMAS MEJORAS (Opcionales)

- [ ] Historial de pesos más comunes
- [ ] Presets rápidos (250g, 500g, 1kg)
- [ ] Sugerencia de precio por gramaje
- [ ] Reportes por peso de productos
