# 🚀 Guía Rápida de Inicio - Proyecto CAD

## Inicio Rápido (3 pasos)

### 1️⃣ Iniciar Docker

**En Windows:**
```bash
iniciar.bat
```

**En Linux/Mac:**
```bash
chmod +x iniciar.sh
./iniciar.sh
```

### 2️⃣ Verificar el Sistema

Abre en tu navegador: http://localhost:8080/verificar.php

Todos los checks deben estar en ✅ verde.

### 3️⃣ Iniciar Sesión

Ve a: http://localhost:8080

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

## 📱 URLs del Sistema

| Servicio | URL | Descripción |
|----------|-----|-------------|
| Aplicación Web | http://localhost:8080 | Sistema principal |
| phpMyAdmin | http://localhost:8081 | Gestión de BD |
| Verificador | http://localhost:8080/verificar.php | Diagnóstico |

## 👤 Gestión de Usuarios

### Crear Usuario de Gestión

1. Inicia sesión como Directivo (`admin`)
2. Ve a "Usuarios" en el menú
3. Haz clic en "+ Crear Usuario"
4. Completa el formulario:
   - **Identificador:** nombre de usuario único
   - **Nombre Completo:** nombre y apellidos
   - **Contraseña:** mínimo 6 caracteres
   - **Cargo:** puesto del usuario
   - **Foto:** (opcional) imagen JPG/PNG

### Editar Usuario

1. En la lista de usuarios, clic en "✏️ Editar"
2. Modifica los campos necesarios
3. Deja la contraseña en blanco si no quieres cambiarla
4. Marca/desmarca "Usuario Activo" para habilitar/deshabilitar

### Eliminar Usuario

1. En la lista, clic en "🗑️ Eliminar"
2. Confirma la acción

> ⚠️ **Nota:** La eliminación es permanente. Todos los cambios quedan registrados en auditoría.

## 🛠️ Comandos Docker Útiles

```bash
# Ver logs en tiempo real
docker-compose logs -f web

# Detener todo
docker-compose down

# Reiniciar servicios
docker-compose restart

# Ver contenedores activos
docker-compose ps

# Acceder a la base de datos
docker exec -it proyectocad_db mysql -u root -prootpassword proyecto_cad
```

## 📊 Acceso a Base de Datos

### Credenciales MySQL

- **Host:** localhost
- **Puerto:** 3306
- **Usuario:** root
- **Contraseña:** rootpassword
- **Base de datos:** proyecto_cad

### Usar phpMyAdmin

1. Abre: http://localhost:8081
2. Usuario: `root`
3. Contraseña: `rootpassword`

## ❌ Solución de Problemas

### Error: "No se puede conectar a la base de datos"

```bash
# Verificar que los contenedores están corriendo
docker-compose ps

# Reiniciar los contenedores
docker-compose restart
```

### Error: "No se puede subir foto"

Verifica permisos de la carpeta `uploads/`:
```bash
# En Linux/Mac
chmod 777 uploads/

# O desde el contenedor
docker exec proyectocad_web chmod 777 /var/www/html/uploads
```

### Error: Puerto 8080 en uso

Cambia el puerto en `docker-compose.yml`:
```yaml
ports:
  - "9090:80"  # Cambia 8080 por 9090
```

### Resetear todo

```bash
# Detener y eliminar todo (incluida la BD)
docker-compose down -v

# Volver a iniciar
docker-compose up -d
```

## 🔒 Seguridad

### Cambiar contraseña de admin

1. Inicia sesión como admin
2. Ve al perfil o usa phpMyAdmin:
```sql
UPDATE usuarios 
SET password = '$2y$10$NUEVO_HASH' 
WHERE identificador = 'admin';
```

Para generar un hash:
```php
<?php echo password_hash('nueva_contraseña', PASSWORD_BCRYPT); ?>
```

### Cambiar contraseña de MySQL

Edita `docker-compose.yml` y cambia:
```yaml
MYSQL_ROOT_PASSWORD: tu_nueva_contraseña
```

## 📈 Próximos Pasos

- [ ] Cambiar credenciales por defecto
- [ ] Crear usuarios de gestión
- [ ] Revisar logs de auditoría
- [ ] Configurar backups automáticos
- [ ] Preparar para producción (ver AZURE_DEPLOY.md)

## 📞 Recursos

- **README completo:** [README.md](README.md)
- **Despliegue Azure:** [AZURE_DEPLOY.md](AZURE_DEPLOY.md)
- **Código fuente:** Ver carpetas `public/`, `includes/`, `config/`

---

**¿Listo para producción?** Consulta [AZURE_DEPLOY.md](AZURE_DEPLOY.md) para desplegar en Azure.
