# Changelog - Proyecto CAD

Todos los cambios notables en este proyecto serán documentados en este archivo.

## [1.0.0] - 2024-12-15

### ✨ Características Iniciales

#### Sistema de Autenticación
- Login/Logout con sesiones seguras
- Control de timeout de sesión (1 hora configurable)
- Hashing de contraseñas con bcrypt
- Redirección automática según estado de autenticación
- Mensajes de error personalizados

#### Gestión de Usuarios
- CRUD completo para usuarios tipo Gestión
- Validación de datos en servidor
- Prevención de duplicados (identificador único)
- Subida de fotos de perfil (JPG, PNG, GIF)
- Límite de tamaño de archivo (5MB)
- Estado activo/inactivo de usuarios

#### Tipos de Usuario
- **Directivo** (ID: 1): Puede gestionar usuarios de tipo Gestión
- **Gestión** (ID: 2): Usuario gestionado por directivos
- **Operativo** (ID: 3): Definido pero no implementado

#### Base de Datos
- 3 tablas principales:
  - `tipo_usuario`: Catálogo de tipos
  - `usuarios`: Información de usuarios
  - `auditoria_usuarios`: Registro de cambios
- Relaciones con foreign keys
- Campos JSON para auditoría
- Usuario admin por defecto (admin/admin123)

#### Auditoría
- Registro automático de todas las acciones (CREATE, UPDATE, DELETE)
- Almacenamiento de datos anteriores y nuevos en JSON
- Registro de usuario que realizó la acción
- Registro de IP y timestamp

#### Interfaz Web
- Dashboard personalizado por usuario
- Lista de usuarios con foto
- Formularios de creación y edición
- Confirmación para eliminaciones
- Mensajes de éxito y error
- Diseño responsive (móvil, tablet, desktop)

#### Docker
- Dockerfile optimizado con PHP 8.2 + Apache
- Docker Compose con 3 servicios:
  - Web (Apache + PHP)
  - Database (MySQL 8.0)
  - phpMyAdmin
- Volúmenes persistentes para base de datos
- Network interno para comunicación
- Inicialización automática de BD

#### Compatibilidad Azure
- Configuración para Azure App Service
- Soporte para Azure Database for MySQL
- Variables de entorno para configuración
- web.config para IIS
- Documentación de despliegue

#### Seguridad
- Prepared statements (PDO) contra SQL Injection
- Validación de tipos de archivo
- Protección de carpetas sensibles (.htaccess)
- Control de acceso por tipo de usuario
- Sesiones seguras con regeneración de ID

#### Documentación
- README.md completo
- GUIA_RAPIDA.md para inicio rápido
- AZURE_DEPLOY.md para despliegue
- ESTRUCTURA.md con arquitectura
- TESTING.md con casos de prueba
- LEEME.txt con instrucciones visuales

#### Scripts de Utilidad
- iniciar.bat (Windows)
- iniciar.sh (Linux/Mac)
- detener.bat (Windows)
- verificar.php (diagnóstico del sistema)

#### Archivos de Configuración
- .env.example (plantilla variables)
- .gitignore (archivos excluidos)
- .dockerignore (optimización Docker)
- composer.json (dependencias PHP)
- package.json (info del proyecto)

### 🔧 Configuración

- PHP 8.2
- MySQL 8.0
- Apache 2.4
- Puerto web: 8080
- Puerto MySQL: 3306
- Puerto phpMyAdmin: 8081
- Upload max: 10MB
- Session timeout: 3600s (1 hora)
- Max file size: 5MB

### 📦 Estructura de Archivos

```
22 archivos PHP
5 archivos de configuración
5 archivos de documentación
1 script SQL
3 scripts de inicio
1 Dockerfile
1 docker-compose.yml
```

### 🎨 Interfaz

- Paleta de colores: Gradiente púrpura (#667eea, #764ba2)
- Fuente: Segoe UI
- Responsive breakpoints: 768px
- Iconos: Emojis nativos

### 🔒 Seguridad Implementada

- ✅ Password hashing (bcrypt)
- ✅ SQL Injection prevention (PDO)
- ✅ XSS prevention (htmlspecialchars)
- ✅ CSRF protection (pending)
- ✅ File upload validation
- ✅ Session management
- ✅ Access control

### 📝 Campos de Usuario

- Identificador (único, requerido)
- Nombre completo (requerido)
- Contraseña (requerido, min 6 caracteres)
- Foto de perfil (opcional, max 5MB)
- Cargo (requerido)
- Tipo de usuario (automático: Gestión)
- Estado activo (boolean)

### 🚀 Despliegue

- ✅ Docker local
- ✅ Azure App Service (compatible)
- ⏳ AWS (no configurado)
- ⏳ Google Cloud (no configurado)

---

## [Próximas Versiones]

### [1.1.0] - Planificado

#### Mejoras Planeadas
- [ ] Paginación en lista de usuarios
- [ ] Búsqueda y filtros
- [ ] Exportación a Excel/PDF
- [ ] Recuperación de contraseña
- [ ] Cambio de contraseña desde perfil
- [ ] Vista de auditoría para directivos
- [ ] Dashboard con estadísticas
- [ ] Gráficos de actividad

#### Usuario Operativo
- [ ] Definir permisos y funcionalidades
- [ ] Implementar vistas específicas
- [ ] Documentar flujos de trabajo

#### Seguridad
- [ ] Autenticación de dos factores (2FA)
- [ ] Protección CSRF
- [ ] Límite de intentos de login
- [ ] Política de contraseñas robustas
- [ ] Logs de acceso

#### API
- [ ] API REST para gestión de usuarios
- [ ] Autenticación con tokens JWT
- [ ] Documentación con Swagger
- [ ] Rate limiting

#### Testing
- [ ] Tests unitarios (PHPUnit)
- [ ] Tests de integración
- [ ] Tests end-to-end
- [ ] CI/CD con GitHub Actions

### [1.2.0] - Futuro

- [ ] Notificaciones por email
- [ ] Múltiples idiomas (i18n)
- [ ] Temas claro/oscuro
- [ ] Roles personalizables
- [ ] Permisos granulares
- [ ] Historial de cambios visible
- [ ] Comentarios en usuarios
- [ ] Tags y categorías

---

## Notas de Versión

### Formato de Versionado

Este proyecto sigue [Semantic Versioning](https://semver.org/):
- MAJOR: Cambios incompatibles en la API
- MINOR: Nueva funcionalidad compatible
- PATCH: Correcciones de bugs

### Categorías de Cambios

- ✨ **Características**: Nuevas funcionalidades
- 🔧 **Configuración**: Cambios en configuración
- 🐛 **Bugs**: Correcciones de errores
- 🔒 **Seguridad**: Mejoras de seguridad
- 📚 **Documentación**: Cambios en docs
- ⚡ **Performance**: Mejoras de rendimiento
- ♻️ **Refactoring**: Mejoras de código
- 🎨 **UI/UX**: Cambios en interfaz

---

**Última actualización:** 15 de diciembre de 2024  
**Versión actual:** 1.0.0  
**Estado:** ✅ Estable y en producción
