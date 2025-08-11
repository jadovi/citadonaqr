# EventAccess - Sistema de Acreditación de Visitantes

Sistema completo de gestión de eventos y acreditación de visitantes con códigos QR, desarrollado en PHP 8+, Bootstrap 5, JavaScript vanilla y MariaDB.

## 🚀 Características

- **Escáner QR con cámara móvil**: Acreditación instantánea usando la cámara del dispositivo
- **QR Personal dinámico**: Códigos QR únicos por visitante con renovación cada 7 segundos
- **Gestión completa de eventos**: CRUD de eventos con enlaces de inscripción únicos
- **Gestión de visitantes**: CRUD completo con búsqueda avanzada
- **Importador batch**: Carga masiva de visitantes desde CSV/Excel
- **Inscripciones automáticas**: Formularios públicos con códigos QR automáticos
- **Sistema de correos**: Confirmaciones y recordatorios automáticos
- **Panel de administración**: Dashboard completo con estadísticas
- **Responsive Design**: Compatible con dispositivos móviles y desktop
- **Reportes y estadísticas**: Control total de accesos e inscripciones

## 📋 Requisitos del Sistema

- **Servidor Web**: Apache/Nginx con PHP 8.0+
- **Base de datos**: MariaDB 10.4+ o MySQL 8.0+
- **Extensiones PHP**:
  - PDO MySQL
  - JSON
  - Session
  - OpenSSL
  - GD (opcional para QR)
- **Navegador**: Moderno con soporte para cámara web

## 🛠️ Instalación

### 1. Configuración del Servidor

#### XAMPP (Desarrollo Local)
1. Instalar XAMPP desde [https://www.apachefriends.org](https://www.apachefriends.org)
2. Iniciar Apache y MySQL desde el panel de control
3. Clonar o descargar el proyecto en `C:\xampp\htdocs\claudeson4-qr`

#### Servidor Linux
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install apache2 php8.1 php8.1-mysql php8.1-json php8.1-session mariadb-server

# CentOS/RHEL
sudo yum install httpd php php-mysql php-json mariadb-server
```

### 2. Configuración de la Base de Datos

1. **Acceder a MariaDB**:
```sql
mysql -u root -p
```

2. **Crear la base de datos**:
```sql
CREATE DATABASE evento_acreditacion;
USE evento_acreditacion;
```

3. **Importar estructura**:
```bash
mysql -u root -p evento_acreditacion < database.sql
```

O copiar y ejecutar el contenido de `database.sql` en phpMyAdmin.

### 3. Configuración de la Aplicación

1. **Configurar credenciales** en `config/database.php`:
```php
return [
    'host' => 'localhost',
    'dbname' => 'evento_acreditacion',
    'username' => 'root',
    'password' => '123123',
    // ... resto de configuración
];
```

2. **Configurar URL base** en `config/config.php`:
```php
define('BASE_URL', 'http://localhost/claudeson4-qr');
```

3. **Permisos de directorios**:
```bash
chmod 755 uploads/ qr_codes/
chmod 644 config/*.php
```

### 4. Configuración de Email (Opcional)

Editar configuraciones SMTP en la tabla `configuracion` de la base de datos:
- `smtp_host`: Servidor SMTP
- `smtp_port`: Puerto (587 para TLS)
- `smtp_username`: Usuario SMTP
- `smtp_password`: Contraseña SMTP
- `smtp_from_email`: Email remitente

## 🔐 Acceso al Sistema

### Usuario Administrador Por Defecto
- **Usuario**: `admin`
- **Contraseña**: `password`
- **URL**: `http://localhost/claudeson4-qr/admin/`

### URLs Principales
- **Sitio Principal**: `http://localhost/claudeson4-qr/`
- **Escáner QR**: `http://localhost/claudeson4-qr/scanner.php`
- **Buscar Visitantes**: `http://localhost/claudeson4-qr/buscar.php`
- **Panel Admin**: `http://localhost/claudeson4-qr/admin/`

## 📱 Uso del Sistema

### 1. Crear un Evento (Admin)
1. Acceder al panel de administración
2. Ir a "Eventos" → "Nuevo Evento"
3. Completar datos del evento
4. Copiar el enlace de inscripción generado

### 2. Inscribir Visitantes
1. Compartir el enlace de inscripción del evento
2. Los visitantes completan el formulario
3. Reciben email con código QR automáticamente

### 3. Acreditar Visitantes
1. Usar el escáner QR desde móvil o desktop
2. Escanear código QR del visitante
3. Confirmar acceso automáticamente

### 4. Buscar Visitantes
1. Acceder al buscador
2. Buscar por nombre, email, empresa o RUT
3. Ver detalles e historial de cada visitante

### 5. Importación Masiva
1. Ir a Admin → Importar Visitantes
2. Descargar template CSV o usar archivo propio
3. Configurar separadores y mapeo de columnas
4. Procesar importación con validación automática
5. Opcionalmente inscribir a evento específico

### 6. QR Personal Dinámico por Evento (Nuevo)
1. **Cada evento tiene hash único** SHA-256 para identificación
2. **Página dedicada del evento** muestra todos los visitantes confirmados
3. **QR individual por visitante** que se renueva cada 7 segundos
4. **Formulario con logo** generado automáticamente por empresa
5. **URLs únicos** imposibles de adivinar para máxima seguridad
6. **Compatibilidad dual** con sistema legacy mantenida

## 🔧 Funcionalidades Avanzadas

### Nuevos URLs del Sistema (Hash por Evento)
- **Formulario de inscripción:** `inscripcion.php?event={hash_evento}`
- **Página QR visitantes:** `qr_display.php?access={hash_evento}`
- **Migración de eventos:** `migrar_hash_eventos.php`
- **Demo del sistema:** `demo_sistema_completo.php`

### Códigos QR
- Generación automática por inscripción
- Únicos e irrepetibles
- Validación en tiempo real
- Historial de accesos

### QR Personal Dinámico por Evento (Nuevo)
- **Hash único por evento** SHA-256 para identificación segura
- **Página dedicada del evento** con lista de todos los visitantes
- **QR individual dinámico** renovado cada 7 segundos por visitante
- **Logo automático** generado para cada empresa
- **URLs imposibles de adivinar** para máxima seguridad
- **Formulario personalizado** con hash del evento
- **Búsqueda de participantes** en tiempo real
- **Modal QR optimizado** para dispositivos móviles
- **Wake Lock API** para mantener pantalla activa
- **Compatibilidad dual** con sistema legacy

### Sistema de Correos
- Confirmación de inscripción automática
- Recordatorios programables
- Templates HTML personalizables
- Log de envíos

### Importador Batch
- Carga masiva desde CSV/Excel
- Mapeo flexible de columnas
- Validación automática (email, RUT chileno)
- Actualización de visitantes existentes
- Inscripción automática a eventos
- Template CSV descargable
- Reporte detallado de errores

### Reportes y Estadísticas
- Dashboard en tiempo real
- Estadísticas por evento
- Control de accesos diarios
- Exportación de datos

### Seguridad
- Autenticación de administradores
- Validación de formularios
- Protección contra ataques XSS/CSRF
- Encriptación de contraseñas

## 🚨 Solución de Problemas

### Error de Conexión a Base de Datos
1. Verificar credenciales en `config/database.php`
2. Asegurar que MariaDB esté ejecutándose
3. Verificar que la base de datos existe

### Cámara No Funciona
1. Verificar permisos de cámara en el navegador
2. Usar HTTPS en producción
3. Verificar compatibilidad del navegador

### Emails No Se Envían
1. Configurar SMTP correctamente
2. Verificar firewall/puertos
3. Revisar logs de error de PHP

### Errores de Permisos
```bash
# Linux
sudo chown -R www-data:www-data /path/to/claudeson4-qr
sudo chmod -R 755 /path/to/claudeson4-qr
```

## 📊 Estructura del Proyecto

```
claudeson4-qr/
├── admin/                  # Panel de administración
│   ├── index.php          # Dashboard
│   ├── eventos.php        # Gestión de eventos
│   ├── visitantes.php     # Gestión de visitantes
│   └── login.php          # Login admin
├── api/                   # APIs REST
│   ├── verificar_qr.php   # Validación QR
│   ├── confirmar_acceso.php # Confirmación acceso
│   └── estadisticas.php   # Estadísticas
├── classes/               # Clases principales
│   ├── Database.php       # Conexión BD
│   ├── Auth.php          # Autenticación
│   └── Mailer.php        # Sistema correos
├── config/               # Configuraciones
│   ├── config.php        # Config general
│   └── database.php      # Config BD
├── models/               # Modelos de datos
│   ├── Evento.php        # Modelo eventos
│   ├── Visitante.php     # Modelo visitantes
│   └── Inscripcion.php   # Modelo inscripciones
├── index.php             # Página principal
├── scanner.php           # Escáner QR
├── buscar.php           # Búsqueda visitantes
├── inscripcion.php      # Formulario inscripción
└── database.sql         # Estructura BD
```

## 🔄 Actualizaciones y Mantenimiento

### Backup de Base de Datos
```bash
mysqldump -u root -p evento_acreditacion > backup_$(date +%Y%m%d).sql
```

### Logs del Sistema
- Revisar logs de PHP: `/var/log/apache2/error.log`
- Logs de aplicación en tabla `log_emails`

### Optimización
- Limpiar accesos antiguos periódicamente
- Optimizar tablas de base de datos
- Monitorear uso de disco

## 📞 Soporte y Contribuciones

### Reportar Issues
1. Describir el problema detalladamente
2. Incluir logs de error
3. Especificar versión de PHP/MariaDB

### Contribuir
1. Fork del repositorio
2. Crear rama feature
3. Commit cambios
4. Pull request

## 📄 Licencia

Este proyecto está bajo licencia MIT. Ver archivo LICENSE para más detalles.

## 👥 Créditos

- **Desarrollado por**: Claude Sonnet 4
- **Bootstrap**: Framework CSS
- **jsQR**: Librería JavaScript para QR
- **DataTables**: Tablas interactivas
- **Bootstrap Icons**: Iconografía

---

## 🎯 Roadmap Futuro

- [ ] API REST completa
- [ ] Aplicación móvil nativa
- [ ] Integración con sistemas externos
- [ ] Reportes avanzados PDF
- [ ] Multi-idioma
- [ ] Notificaciones push
- [ ] Integración calendario

¡Gracias por usar EventAccess! 🎉
# citadonaqr
