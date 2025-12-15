# Proyecto CAD - Sistema de Gestión de Usuarios

Sistema web desarrollado en PHP y MySQL para la gestión de usuarios con tres niveles de acceso: Directivo, Gestión y Operativo. Compatible con Docker y Azure.

## 📋 Características

- **Sistema de autenticación** con control de sesiones
- **Gestión de usuarios** tipo Gestión por parte de usuarios Directivos
- **CRUD completo** para usuarios (Crear, Leer, Actualizar, Eliminar)
- **Subida de fotos** de perfil
- **Auditoría** de cambios en usuarios
- **Interfaz responsive** moderna
- **Compatible con Docker** para desarrollo local
- **Listo para desplegar en Azure**

## 🛠️ Tecnologías

- PHP 8.2
- MySQL 8.0
- Docker & Docker Compose
- Azure App Service (compatible)
- HTML5, CSS3

## 📁 Estructura del Proyecto

```
ProyectoCAD/
├── config/                 # Configuración
│   ├── config.php         # Configuración general
│   ├── database.php       # Configuración de BD (crear desde .example)
│   └── database.example.php
├── database/              # Scripts de base de datos
│   └── init.sql          # Script de inicialización
├── docker/               # Configuración Docker
│   └── php.ini          # Configuración PHP
├── includes/            # Clases PHP
│   ├── Auth.php        # Autenticación
│   ├── Database.php    # Conexión a BD
│   └── Usuario.php     # Gestión de usuarios
├── public/             # Archivos públicos
│   ├── css/
│   │   └── style.css
│   ├── includes/
│   │   ├── header.php
│   │   └── footer.php
│   ├── usuarios/
│   │   ├── listar.php
│   │   ├── crear.php
│   │   ├── editar.php
│   │   └── eliminar.php
│   ├── auth/
│   │   └── logout.php
│   ├── index.php       # Dashboard
│   └── login.php       # Inicio de sesión
├── uploads/            # Fotos de perfil
├── .env.example       # Variables de entorno
├── .gitignore
├── docker-compose.yml
├── Dockerfile
├── AZURE_DEPLOY.md   # Guía de despliegue Azure
└── README.md
```

## 🚀 Instalación y Uso con Docker

### Prerrequisitos

- Docker Desktop instalado
- Git (opcional)

### Pasos de instalación

1. **Clonar o descargar el proyecto**

```bash
cd c:\Users\Rulig\Downloads\proy_extra_compa\ProyectoCAD
```

2. **Configurar el archivo de base de datos**

```bash
# En Windows PowerShell:
copy config\database.example.php config\database.php

# En Linux/Mac:
cp config/database.example.php config/database.php
```

3. **Levantar los contenedores Docker**

```bash
docker-compose up -d
```

Esto iniciará:
- Servidor web PHP en http://localhost:8080
- Base de datos MySQL en puerto 3306
- phpMyAdmin en http://localhost:8081

4. **Acceder a la aplicación**

Abre tu navegador en: http://localhost:8080

**Credenciales de acceso por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

## 👥 Tipos de Usuario

### 1. Directivo
- **Permisos:** Gestión completa de usuarios tipo Gestión
- **Funciones:**
  - Crear usuarios de gestión
  - Editar usuarios de gestión
  - Eliminar usuarios de gestión
  - Ver listado de usuarios

### 2. Gestión
- **Permisos:** Usuarios gestionados por directivos
- Campos: Identificador, Nombre completo, Contraseña, Foto de perfil, Cargo

### 3. Operativo
- Usuario operativo del sistema (pendiente implementación)

## 📊 Base de Datos

La base de datos se inicializa automáticamente con:
- 3 tipos de usuario
- 1 usuario directivo por defecto
- Tablas de auditoría

### Tablas principales:
- `tipo_usuario` - Tipos de usuario
- `usuarios` - Información de usuarios
- `auditoria_usuarios` - Registro de cambios

## 🔒 Seguridad

- Contraseñas hasheadas con bcrypt
- Protección contra SQL Injection (PDO)
- Control de sesiones con timeout
- Validación de tipos de archivo en uploads
- Auditoría de todas las acciones

## 🐳 Comandos Docker Útiles

```bash
# Ver logs
docker-compose logs -f web

# Detener contenedores
docker-compose down

# Reiniciar contenedores
docker-compose restart

# Acceder al contenedor web
docker exec -it proyectocad_web bash

# Acceder a MySQL
docker exec -it proyectocad_db mysql -u root -prootpassword proyecto_cad
```

## 🌐 Despliegue en Azure

Consulta el archivo [AZURE_DEPLOY.md](AZURE_DEPLOY.md) para instrucciones detalladas de despliegue en Azure App Service.

## 📝 Configuración de Producción

Para producción, asegúrate de:

1. Cambiar las contraseñas por defecto
2. Configurar variables de entorno en `.env`
3. Deshabilitar `display_errors` en `config/config.php`
4. Configurar SSL/HTTPS
5. Actualizar `BASE_URL` en la configuración
6. Configurar backups automáticos de la base de datos

## 🧪 Testing

### Pruebas básicas:

1. **Login:**
   - Iniciar sesión con credenciales válidas
   - Intentar con credenciales inválidas
   - Verificar timeout de sesión

2. **CRUD de Usuarios:**
   - Crear usuario de gestión
   - Editar información
   - Subir foto de perfil
   - Eliminar usuario
   - Verificar auditoría

3. **Seguridad:**
   - Intentar acceder sin autenticación
   - Verificar que usuarios no-directivos no accedan a gestión

## 🛠️ Mantenimiento

### Backup de base de datos:

```bash
docker exec proyectocad_db mysqldump -u root -prootpassword proyecto_cad > backup.sql
```

### Restaurar base de datos:

```bash
docker exec -i proyectocad_db mysql -u root -prootpassword proyecto_cad < backup.sql
```

## 📄 Licencia

Este proyecto es de uso educativo.

## 👨‍💻 Soporte

Para problemas o preguntas, consulta la documentación o crea un issue en el repositorio.

## 🔄 Próximas Mejoras

- [ ] Implementar funcionalidades para usuario Operativo
- [ ] Agregar paginación en listados
- [ ] Implementar búsqueda y filtros
- [ ] Agregar exportación a Excel/PDF
- [ ] Implementar recuperación de contraseña
- [ ] Agregar autenticación de dos factores
- [ ] Dashboard con estadísticas
- [ ] API REST
