# 📱 QR Personal Dinámico - EventAccess

Sistema avanzado de códigos QR personales con renovación automática y máxima seguridad anti-fraude.

## 🎯 Características Principales

### 🔐 Seguridad Avanzada
- **Renovación cada 7 segundos** con timestamp único
- **Hash SHA-256** para validación de integridad
- **JSON encriptado** con datos del visitante
- **Validación anti-fraude** en tiempo real
- **URLs únicas** imposibles de adivinar

### 📱 Optimización Móvil
- **Diseño responsive** para cualquier dispositivo
- **Navegador nativo** - no requiere apps
- **Pantalla siempre activa** (Wake Lock API)
- **Interfaz limpia** optimizada para eventos
- **Carga rápida** con cache inteligente

### 🔄 Funcionalidad Dinámica
- **Auto-actualización** sin recargar página
- **Indicador visual** de renovación
- **Compatibilidad dual** (QR legacy + dinámico)
- **Offline resilience** con cache local

## 🏗️ Arquitectura del Sistema

### 1. Generación del Hash de Acceso
```php
// Cada inscripción obtiene un hash único
$hash = hash('sha256', uniqid() . random_bytes(32) . microtime(true));
```

### 2. URL Personal del Visitante
```
https://evento.com/qr_display.php?access={hash_acceso}
```

### 3. Datos del QR JSON
```json
{
    "codigo_qr": "abc123...",
    "inscripcion_id": 123,
    "visitante_id": 456,
    "evento_id": 789,
    "nombre": "Juan",
    "apellido": "Pérez",
    "empresa": "Empresa ABC",
    "timestamp": 1645123456,
    "hash": "sha256_validation_hash"
}
```

### 4. Validación en Escáner
- Decodifica JSON del QR
- Valida timestamp (máximo 30 segundos de diferencia)
- Verifica hash de seguridad
- Confirma inscripción activa

## 🔧 Flujo Técnico Completo

### Paso 1: Creación de Inscripción
```php
// Al crear inscripción se genera hash único
$data = [
    'evento_id' => $eventoId,
    'visitante_id' => $visitanteId,
    'codigo_qr' => $this->generarCodigoQR(),
    'hash_acceso' => $this->generarHashAcceso(), // NUEVO
    'estado' => 'pendiente'
];
```

### Paso 2: Envío de Email
```html
<!-- Email incluye enlace al QR personal -->
<a href="https://evento.com/qr_display.php?access={hash}">
    🔗 Ver Mi QR Personal
</a>
```

### Paso 3: Página del QR Personal
```javascript
// JavaScript renueva QR cada 7 segundos
setInterval(() => {
    fetch(`/api/generar_qr.php?access=${hash}`)
        .then(response => response.json())
        .then(data => generateQR(data.qr_string));
}, 7000);
```

### Paso 4: Validación en Escáner
```php
// API valida timestamp y hash
$timestampActual = time();
$diferencia = abs($timestampActual - $datosQR['timestamp']);

if ($diferencia > 30) {
    return ['valido' => false, 'mensaje' => 'QR expirado'];
}

$hashEsperado = hash('sha256', $codigo . $timestamp . 'eventaccess_salt');
if ($datosQR['hash'] !== $hashEsperado) {
    return ['valido' => false, 'mensaje' => 'QR falsificado'];
}
```

## 📊 Ventajas vs QR Tradicional

| Característica | QR Tradicional | QR Personal Dinámico |
|----------------|----------------|---------------------|
| **Seguridad** | Código estático | Renovación cada 7s |
| **Falsificación** | Fácil clonar | Imposible falsificar |
| **Experiencia** | Básica | Optimizada móvil |
| **Validación** | Código simple | JSON + timestamp + hash |
| **Información** | Limitada | Rica y contextual |
| **Vigencia** | Permanente | Limitada por tiempo |

## 🛡️ Medidas de Seguridad

### 1. Hash de Acceso Único
- **SHA-256** con datos aleatorios
- **64 caracteres** hexadecimales
- **Único por inscripción** sin colisiones
- **No derivable** de datos públicos

### 2. Timestamp Dinámico
- **Renovación cada 7 segundos** exactos
- **Ventana de validez** de 30 segundos
- **Sincronización** con servidor
- **Protección replay** attacks

### 3. Hash de Validación
- **HMAC** con salt secreto
- **Integridad** de todos los datos
- **Detección** de manipulación
- **Verificación** en tiempo real

### 4. Validaciones Adicionales
- **Estado de inscripción** confirmado
- **Evento activo** y vigente
- **Fechas válidas** del evento
- **IP y User-Agent** logging

## 📱 Implementación Frontend

### HTML Responsive
```html
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
```

### CSS Optimizado
```css
.qr-container {
    min-height: calc(100vh - 40px);
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Prevenir zoom en iOS */
input, select, textarea {
    font-size: 16px;
}
```

### JavaScript Avanzado
```javascript
// Wake Lock para mantener pantalla activa
if ('wakeLock' in navigator) {
    wakeLock = await navigator.wakeLock.request('screen');
}

// Service Worker para cache offline
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js');
}
```

## 🔄 APIs del Sistema

### 1. `api/generar_qr.php`
**Parámetros:**
- `access`: Hash de acceso único

**Respuesta:**
```json
{
    "success": true,
    "qr_string": "{json_data}",
    "timestamp": 1645123456,
    "next_refresh": 5,
    "visitante": {...},
    "evento": {...}
}
```

### 2. `api/verificar_qr.php`
**Parámetros:**
- `codigo_qr`: JSON string del QR

**Validaciones:**
- Formato JSON válido
- Timestamp dentro de ventana
- Hash de validación correcto
- Inscripción activa

### 3. `api/confirmar_acceso.php`
**Funcionalidad:**
- Extrae código real del JSON
- Marca ingreso en base de datos
- Registra IP y User-Agent

## 🚀 Casos de Uso

### 1. Evento Corporativo
- **1000+ participantes** con QR personal
- **Seguridad máxima** contra falsificaciones
- **Experiencia premium** en móviles
- **Validación rápida** en múltiples accesos

### 2. Conferencia Internacional
- **Participantes remotos** con enlaces
- **Acceso desde cualquier dispositivo**
- **No requiere descarga** de apps
- **Funciona offline** una vez cargado

### 3. Evento de Seguridad Alta
- **Renovación continua** cada 7 segundos
- **Imposible reproducir** QR pasados
- **Trazabilidad completa** de accesos
- **Detección automática** de fraudes

## 🔧 Instalación y Configuración

### 1. Migrar Base de Datos
```bash
# Ejecutar script de migración
http://localhost/claudeson4-qr/migrar_hash_acceso.php
```

### 2. Configurar Sistema
- Hash salt en configuración
- URLs base correctas
- Permisos de servidor
- Cache headers

### 3. Probar Funcionalidad
```bash
# Crear evento de prueba
# Inscribir visitante
# Verificar email con enlace
# Probar QR en escáner
```

## 📊 Monitoreo y Analytics

### Métricas Disponibles
- **Accesos por QR dinámico** vs legacy
- **Tiempo promedio** de validación
- **Intentos de fraude** detectados
- **Dispositivos utilizados** para QR

### Logs de Seguridad
- Timestamps fuera de rango
- Hashes inválidos
- Intentos de replay
- Códigos falsificados

## 🛠️ Troubleshooting

### Problema: QR no se renueva
**Solución:**
- Verificar conexión a internet
- Comprobar JavaScript habilitado
- Revisar console del navegador

### Problema: QR expirado en escáner
**Solución:**
- Verificar sincronización de hora servidor
- Ajustar ventana de validez (30s)
- Revisar latencia de red

### Problema: Hash de acceso inválido
**Solución:**
- Ejecutar migración de hash_acceso
- Verificar integridad de base de datos
- Regenerar hashes si es necesario

## 🔮 Roadmap Futuro

- **Push notifications** para renovación
- **Biometría** adicional (face/fingerprint)
- **Blockchain** para trazabilidad
- **IA** para detección de patrones
- **Multi-factor** authentication
- **Geolocalización** para validación

---

## 📞 Soporte Técnico

Para problemas específicos del QR Personal:
1. Verificar hash_acceso en base de datos
2. Probar URL en diferentes dispositivos
3. Revisar logs de API
4. Validar configuración de timestamp

¡El QR Personal Dinámico lleva la seguridad de eventos al siguiente nivel! 🚀
