# 🎓 SAES 2.0 - Sistema de Administración Escolar en la Nube

Sistema web de gestión de calificaciones de estudiantes desarrollado en PHP y MySQL, compatible con Azure y Docker.

## 📋 Características Principales

### Tres Tipos de Usuarios

1. **👔 Directivo (Administrador)**
   - Gestionar usuarios de tipo Gestión (crear, editar, eliminar)
   - Ver estadísticas del sistema
   - Acceso completo a la plataforma

2. **👨‍💼 Gestión**
   - Inscribir nuevos alumnos
   - Asignar materias a estudiantes
   - Registrar calificaciones (3 parciales)
   - Ver reportes de alumnos

3. **🎓 Alumno**
   - Ver sus materias inscritas
   - Consultar calificaciones por parcial
   - Ver boleta de calificaciones
   - Consultar promedio general

### Sistema de Calificaciones

- **Parciales:** 3 calificaciones parciales por materia
- **Calificación Final:** Promedio automático de los 3 parciales
- **Calificación Mínima:** 70 para aprobar
- **Triggers MySQL:** Cálculo automático del promedio

## 🚀 Inicio Rápido con Docker

### Requisitos Previos

- Docker Desktop instalado
- Puertos disponibles: 8090 (Web), 3307 (MySQL), 8082 (phpMyAdmin)

### Instalación

1. **Clonar el repositorio**
```bash
git clone <tu-repositorio>
cd ProyectoCAD
```

2. **Iniciar los contenedores**
```bash
docker-compose up -d
```

3. **Acceder a la aplicación**
- **Web:** http://localhost:8090
- **phpMyAdmin:** http://localhost:8082

4. **Credenciales de acceso**
Ver archivo [CREDENCIALES.md](CREDENCIALES.md)

## 🏗️ Estructura del Proyecto

```
ProyectoCAD/
├── config/
│   ├── config.php              # Configuración general
│   └── database.php            # Configuración de base de datos
├── database/
│   └── saes_schema.sql         # Schema completo del SAES 2.0
├── includes/
│   ├── Auth.php                # Autenticación y autorización
│   ├── Database.php            # Conexión a base de datos
│   └── Usuario.php             # Clase de gestión de usuarios
├── public/
│   ├── directivo/
│   │   └── dashboard.php       # Dashboard del directivo
│   ├── gestion/
│   │   └── dashboard.php       # Dashboard de gestión
│   ├── alumno/
│   │   └── dashboard.php       # Dashboard del alumno (boleta)
│   ├── usuarios/               # CRUD de usuarios Gestión
│   │   ├── listar.php
│   │   ├── crear.php
│   │   ├── editar.php
│   │   └── eliminar.php
│   ├── css/
│   │   └── style.css           # Estilos
│   ├── includes/
│   │   ├── header.php
│   │   └── footer.php
│   ├── index.php               # Punto de entrada (redirección)
│   └── login.php               # Página de inicio de sesión
├── uploads/                    # Fotos de perfil (futuro Azure Blob)
├── docker-compose.yml          # Orquestación de contenedores
├── Dockerfile                  # Imagen del servidor web
└── README.md                   # Este archivo
```

## 🗃️ Base de Datos

### Tablas Principales

1. **tipo_usuario** - Tipos de usuarios (Directivo, Gestión, Alumno)
2. **usuarios** - Usuarios Directivo y Gestión
3. **alumnos** - Estudiantes del sistema
4. **materias** - Catálogo de materias
5. **inscripciones** - Relación alumno-materia con calificaciones
6. **auditoria** - Registro de cambios

### Diagrama de Relaciones

```
usuarios (1) ----< (N) auditoria
alumnos (1) ----< (N) inscripciones
materias (1) ----< (N) inscripciones
```

### Campos Especiales

#### Tabla `usuarios` (Directivo/Gestión)
- `identificador` - Usuario único
- `nombre_completo` - Nombre del usuario
- `correo` - Email (solo Gestión)
- `password` - Hash bcrypt
- `cargo` - Puesto (solo Directivo)
- `tipo_usuario_id` - 1=Directivo, 2=Gestión

#### Tabla `alumnos`
- `identificador` - Matrícula del estudiante
- `nombre_completo` - Nombre del alumno
- `edad` - Edad del estudiante
- `password` - Hash bcrypt
- `foto_perfil` - Ruta de la foto

#### Tabla `inscripciones`
- `alumno_id` - FK a alumnos
- `materia_id` - FK a materias
- `parcial_1`, `parcial_2`, `parcial_3` - Calificaciones
- `calificacion_final` - Promedio automático (trigger)

## 🔐 Seguridad

- **Passwords:** Hasheados con bcrypt (PASSWORD_BCRYPT)
- **Sesiones:** Timeout automático (2 horas)
- **Autorización:** Middleware por rol (requireDirectivo, requireGestion, requireAlumno)
- **SQL Injection:** PDO con prepared statements
- **XSS:** htmlspecialchars en todas las salidas

## 📝 Próximas Funcionalidades

### Pendientes (Siguiente Sprint)

1. **✅ CRUD de Alumnos** (Gestión puede crear/editar/eliminar)
   - Crear: `/alumnos/crear.php`
   - Listar: `/alumnos/listar.php`
   - Editar: `/alumnos/editar.php`
   - Eliminar: `/alumnos/eliminar.php`

2. **✅ Gestión de Materias**
   - Listar materias: `/materias/listar.php`
   - Ver alumnos inscritos por materia

3. **✅ Asignación de Calificaciones**
   - Formulario de captura: `/calificaciones/asignar.php`
   - Seleccionar alumno, materia y parcial
   - Validación de calificaciones (0-100)

4. **🔜 Azure Blob Storage** (Fotos de perfil)
   - Integrar Azure SDK for PHP
   - Subir fotos a blob storage
   - Actualizar URLs en base de datos

5. **🔜 Validación de Contraseñas**
   - Al menos 8 caracteres
   - 1 mayúscula, 1 minúscula
   - 1 número, 1 carácter especial
   - Regex: `/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/`

6. **🔜 Reportes y Estadísticas**
   - Promedio por materia
   - Índice de aprobación
   - Alumnos en riesgo (<70)

## ☁️ Despliegue en Azure

### Arquitectura Recomendada

1. **Azure App Service** (Web tier)
   - PHP 8.2 runtime
   - 3+ instancias para load balancer
   - Escalado automático

2. **Azure Database for MySQL**
   - Flexible Server
   - SSL habilitado
   - Firewall configurado

3. **Azure Blob Storage**
   - Container para fotos de perfil
   - Acceso público de lectura
   - CDN para mejor rendimiento

4. **Azure Load Balancer**
   - Distribución entre 3 VMs
   - Health probes en `/health.php`
   - Session affinity

### Pasos de Despliegue

Ver archivo `AZURE_DEPLOY.md` para instrucciones detalladas.

```bash
# 1. Crear recursos en Azure
az group create --name saes-rg --location eastus
az mysql flexible-server create --resource-group saes-rg --name saes-mysql
az appservice plan create --name saes-plan --resource-group saes-rg

# 2. Configurar Web App
az webapp create --resource-group saes-rg --plan saes-plan --name saes-web
az webapp config set --php-version 8.2

# 3. Configurar variables de entorno
az webapp config appsettings set --settings DB_HOST=<mysql-host> DB_NAME=proyecto_cad
```

## 🛠️ Comandos Útiles

### Docker

```bash
# Iniciar contenedores
docker-compose up -d

# Ver logs
docker-compose logs -f

# Reiniciar con base de datos limpia
docker-compose down -v && docker-compose up -d

# Acceder a MySQL
docker exec -it proyectocad_db mysql -uroot -prootpassword proyecto_cad

# Ver usuarios registrados
docker exec proyectocad_db mysql -uroot -prootpassword proyecto_cad -e "SELECT * FROM usuarios"
```

### Base de Datos

```sql
-- Ver todas las inscripciones con calificaciones
SELECT a.nombre_completo, m.nombre, i.parcial_1, i.parcial_2, i.parcial_3, i.calificacion_final
FROM inscripciones i
INNER JOIN alumnos a ON i.alumno_id = a.id
INNER JOIN materias m ON i.materia_id = m.id;

-- Promedio general de un alumno
SELECT AVG(calificacion_final) as promedio
FROM inscripciones
WHERE alumno_id = 1 AND calificacion_final IS NOT NULL;

-- Materias con más reprobados
SELECT m.nombre, COUNT(*) as reprobados
FROM inscripciones i
INNER JOIN materias m ON i.materia_id = m.id
WHERE i.calificacion_final < 70
GROUP BY m.id
ORDER BY reprobados DESC;
```

## 📊 Testing

### Casos de Prueba

1. **Login como Directivo**
   - Usuario: `admin`
   - Contraseña: `admin123`
   - Debe redirigir a `/directivo/dashboard.php`
   - Debe mostrar estadísticas de usuarios/alumnos/materias

2. **Login como Gestión**
   - Usuario: `gestion1`
   - Contraseña: `admin123`
   - Debe redirigir a `/gestion/dashboard.php`
   - Debe tener acceso a inscribir alumnos y asignar calificaciones

3. **Login como Alumno**
   - Matrícula: `2021630001`
   - Contraseña: `admin123`
   - Debe redirigir a `/alumno/dashboard.php`
   - Debe mostrar boleta con calificaciones
   - Debe calcular promedio correctamente

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto es parte de un proyecto académico de Cómputo en la Nube.

## 📞 Soporte

- Ver [CREDENCIALES.md](CREDENCIALES.md) para acceso al sistema
- Documentación de Azure en `AZURE_DEPLOY.md`
- Issues: Reportar en GitHub

---

**Desarrollado con ❤️ para el curso de Cómputo en la Nube**
