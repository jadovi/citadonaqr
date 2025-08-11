# 📊 Importador Batch de Visitantes - EventAccess

Guía completa para la importación masiva de visitantes desde archivos CSV/Excel.

## 🚀 Acceso al Importador

**URL:** `http://localhost/claudeson4-qr/admin/importar.php`

**Menú:** Admin Panel → Importar Visitantes

## 📋 Formatos Soportados

- **CSV** (Comma Separated Values)
- **TXT** con separadores
- **Codificación:** UTF-8 recomendada

## 🗂️ Estructura del Archivo

### Campos Disponibles

| Campo | Requerido | Descripción | Ejemplo |
|-------|-----------|-------------|---------|
| `nombre` | ✅ Sí | Nombre del visitante | Juan |
| `apellido` | ✅ Sí | Apellido del visitante | Pérez |
| `email` | ✅ Sí | Email válido (único) | juan.perez@empresa.com |
| `telefono` | ❌ No | Teléfono con código país | +56912345678 |
| `empresa` | ❌ No | Nombre de la empresa | Empresa ABC |
| `cargo` | ❌ No | Cargo en la empresa | Gerente |
| `rut` | ❌ No | RUT chileno válido | 12345678-9 |

### Ejemplo de Archivo CSV

```csv
nombre,apellido,email,telefono,empresa,cargo,rut
Juan,Pérez,juan.perez@empresa.com,+56912345678,Empresa ABC,Gerente,12345678-9
María,González,maria.gonzalez@corp.cl,56987654321,Corporación XYZ,Directora,98765432-1
Carlos,López,carlos.lopez@startup.cl,912345678,Startup Tech,Desarrollador,11111111-1
```

## ⚙️ Configuraciones de Importación

### 1. **Separadores Soportados**
- **Coma (,)** - Estándar CSV
- **Punto y coma (;)** - Excel europeo
- **Tabulación** - Archivos TSV
- **Pipe (|)** - Separador alternativo

### 2. **Opciones Avanzadas**
- ✅ **Saltar primera fila:** Si contiene cabeceras
- ✅ **Actualizar existentes:** Actualizar datos si email ya existe
- ✅ **Inscripción automática:** Inscribir a evento específico

### 3. **Mapeo de Columnas**
- **Flexible:** Cualquier orden de columnas
- **Inteligente:** Detección automática de separadores
- **Visual:** Preview de datos antes de procesar

## 🔧 Proceso de Importación

### Paso 1: Preparar Archivo
1. Crear archivo CSV con datos de visitantes
2. Verificar que emails sean únicos y válidos
3. Formato RUT: `12345678-9` (con guión)
4. Codificar en UTF-8 para caracteres especiales

### Paso 2: Subir y Configurar
1. Arrastar archivo o seleccionar desde disco
2. Configurar separador de columnas
3. Elegir evento para inscripción automática (opcional)
4. Configurar opciones de actualización

### Paso 3: Mapear Columnas
1. Asignar cada columna del CSV a un campo del sistema
2. Campos obligatorios: nombre, apellido, email
3. Campos opcionales: teléfono, empresa, cargo, rut

### Paso 4: Procesar
1. Validación automática de datos
2. Procesamiento en lotes
3. Reporte detallado de resultados

## 🧪 Validaciones Automáticas

### Email
- ✅ Formato válido
- ✅ Único en el sistema
- ❌ Error si duplicado (a menos que se configure actualización)

### RUT (Chile)
- ✅ Formato: `12345678-9`
- ✅ Dígito verificador válido
- ✅ Limpieza automática de caracteres extra

### Teléfono
- 🔧 Limpieza automática de caracteres no numéricos
- ✅ Conserva códigos de país (+56)

### Nombres
- ✅ Sin caracteres especiales peligrosos
- 🔧 Trim automático de espacios

## 📊 Reporte de Resultados

### Métricas
- **Procesados:** Total de filas procesadas
- **Nuevos:** Visitantes creados
- **Actualizados:** Visitantes modificados
- **Errores:** Filas con problemas

### Log de Errores
- **Detallado:** Fila específica y motivo del error
- **Exportable:** Para corrección posterior
- **Específico:** Tipo de validación fallida

## 💡 Mejores Prácticas

### Preparación de Datos
1. **Limpieza previa:** Eliminar filas vacías
2. **Validación manual:** Verificar emails críticos
3. **Backup:** Respaldar datos antes de importar
4. **Prueba pequeña:** Probar con pocas filas primero

### Formato de Archivos
```csv
# ✅ CORRECTO
nombre,apellido,email,telefono,empresa,cargo,rut
Juan,Pérez,juan@email.com,+56912345678,Empresa ABC,Gerente,12345678-9

# ❌ INCORRECTO
"Juan Pérez","juan@email.com","12345678-9"  # Formato inconsistente
```

### Gestión de Errores
1. **Revisar log de errores** después de cada importación
2. **Corregir datos** en archivo original
3. **Re-importar** solo filas fallidas
4. **Usar actualización** para modificar existentes

## 🔄 Casos de Uso Comunes

### 1. **Evento Corporativo**
```csv
nombre,apellido,email,empresa,cargo
Juan,Pérez,juan.perez@corp.com,Corporación ABC,Gerente
María,López,maria.lopez@corp.com,Corporación ABC,Ejecutiva
```

### 2. **Conferencia Técnica**
```csv
nombre,apellido,email,telefono,empresa,rut
Carlos,Martínez,carlos@tech.com,+56912345678,Tech Startup,12345678-9
Ana,González,ana@dev.cl,987654321,Dev Company,98765432-1
```

### 3. **Seminario Educativo**
```csv
nombre,apellido,email,telefono
Pedro,Rodríguez,pedro@email.com,+56988776655
Sofía,Herrera,sofia@email.com,977123456
```

## 🚨 Solución de Problemas

### Error: "Email ya existe"
**Solución:** Activar "Actualizar visitantes existentes"

### Error: "RUT no válido"
**Solución:** Verificar formato `12345678-9` con guión

### Error: "Archivo no se puede leer"
**Solución:** Verificar codificación UTF-8 y formato CSV

### Error: "Columna no mapeada"
**Solución:** Asignar todas las columnas obligatorias

## 📈 Casos Avanzados

### Importación + Inscripción Automática
1. Seleccionar evento destino
2. Importar visitantes
3. Sistema inscribe automáticamente
4. Envía emails de confirmación

### Actualización Masiva
1. Activar "Actualizar existentes"
2. Usar mismo email como identificador
3. Sistema actualiza campos modificados
4. Mantiene inscripciones existentes

### Procesamiento en Lotes
- **Recomendado:** Máximo 1000 registros por archivo
- **Grandes volúmenes:** Dividir en múltiples archivos
- **Monitoreo:** Verificar memoria del servidor

## 🎯 Tips y Trucos

### Excel a CSV
1. **Guardar como:** CSV UTF-8
2. **Verificar:** Separadores y codificación
3. **Probar:** Con pocas filas primero

### Datos desde CRM
1. **Exportar:** En formato CSV
2. **Mapear:** Campos al template
3. **Limpiar:** Datos inconsistentes

### Automatización
- **Scripts:** Para procesamiento regular
- **APIs:** Para integraciones futuras
- **Cron jobs:** Para importación programada

---

## 📞 Soporte

Para problemas específicos:
1. Revisar logs de error
2. Verificar formato de archivo
3. Probar con template de ejemplo
4. Contactar administrador del sistema

¡El importador batch facilita la gestión de eventos grandes con cientos o miles de visitantes! 🚀
