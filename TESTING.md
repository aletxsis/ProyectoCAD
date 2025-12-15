# 🧪 TESTING Y VALIDACIÓN - Proyecto CAD

## ✅ Lista de Verificación Completa

### 1. Verificación de Entorno

```bash
# Verificar Docker está instalado
docker --version
docker-compose --version

# Verificar que los puertos están disponibles
# Windows PowerShell:
Test-NetConnection -ComputerName localhost -Port 8080
Test-NetConnection -ComputerName localhost -Port 3306
Test-NetConnection -ComputerName localhost -Port 8081

# Linux/Mac:
nc -zv localhost 8080
nc -zv localhost 3306
nc -zv localhost 8081
```

### 2. Inicio del Proyecto

```bash
# Opción 1: Script automático (Windows)
iniciar.bat

# Opción 2: Script automático (Linux/Mac)
chmod +x iniciar.sh
./iniciar.sh

# Opción 3: Manual
docker-compose up -d
```

### 3. Verificación de Contenedores

```bash
# Ver contenedores activos
docker-compose ps

# Deberías ver:
# proyectocad_web        running   0.0.0.0:8080->80/tcp
# proyectocad_db         running   0.0.0.0:3306->3306/tcp
# proyectocad_phpmyadmin running   0.0.0.0:8081->80/tcp

# Ver logs
docker-compose logs web
docker-compose logs db
```

### 4. Test de Conectividad Web

**A. Abrir en navegador:**
- http://localhost:8080 → Debe redirigir a login
- http://localhost:8080/verificar.php → Debe mostrar checks verdes
- http://localhost:8081 → phpMyAdmin

**B. Verificar certificado (si usas HTTPS en producción):**
```bash
curl -I http://localhost:8080
```

### 5. Test de Base de Datos

```bash
# Conectar a MySQL desde contenedor
docker exec -it proyectocad_db mysql -u root -prootpassword proyecto_cad

# Una vez dentro, ejecutar:
SHOW TABLES;
# Deberías ver: tipo_usuario, usuarios, auditoria_usuarios

SELECT * FROM tipo_usuario;
# Deberías ver: Directivo, Gestión, Operativo

SELECT identificador, nombre_completo, cargo FROM usuarios;
# Deberías ver el usuario admin

# Salir
EXIT;
```

### 6. Test de Autenticación

#### Test Case 1: Login Exitoso
1. Ir a http://localhost:8080
2. Usuario: `admin`
3. Contraseña: `admin123`
4. **Resultado esperado:** Redirige a dashboard con mensaje de bienvenida

#### Test Case 2: Login Fallido
1. Ir a http://localhost:8080
2. Usuario: `admin`
3. Contraseña: `incorrecta`
4. **Resultado esperado:** Mensaje de error "Usuario o contraseña incorrectos"

#### Test Case 3: Timeout de Sesión
1. Iniciar sesión exitosamente
2. Esperar 1 hora (o modificar SESSION_TIMEOUT en config.php a 60 segundos para testing)
3. Intentar navegar
4. **Resultado esperado:** Redirige a login con mensaje de sesión expirada

#### Test Case 4: Acceso sin autenticación
1. Cerrar sesión
2. Intentar acceder a http://localhost:8080/public/usuarios/listar.php
3. **Resultado esperado:** Redirige a login

### 7. Test CRUD de Usuarios

#### Test Case 5: Crear Usuario
1. Iniciar sesión como admin
2. Ir a "Usuarios" → "Crear Usuario"
3. Llenar formulario:
   - Identificador: `gestor1`
   - Nombre: `Juan Pérez`
   - Contraseña: `123456`
   - Cargo: `Gerente de Ventas`
4. Subir una foto (opcional)
5. Clic en "Crear Usuario"
6. **Resultado esperado:** 
   - Redirige a lista con mensaje "Usuario creado exitosamente"
   - Usuario aparece en la tabla

#### Test Case 6: Editar Usuario
1. En lista de usuarios, clic en "Editar" del usuario creado
2. Cambiar nombre a `Juan Carlos Pérez`
3. Cambiar cargo a `Director de Ventas`
4. NO cambiar contraseña (dejar en blanco)
5. Clic en "Actualizar Usuario"
6. **Resultado esperado:**
   - Mensaje "Usuario actualizado exitosamente"
   - Cambios reflejados en la lista

#### Test Case 7: Editar con Nueva Contraseña
1. Editar usuario
2. Escribir nueva contraseña: `nuevapass123`
3. Guardar
4. Cerrar sesión
5. Intentar login con nueva contraseña
6. **Resultado esperado:** Login exitoso con nueva contraseña

#### Test Case 8: Subir Foto de Perfil
1. Editar usuario
2. Seleccionar imagen JPG/PNG
3. Guardar
4. **Resultado esperado:**
   - Foto aparece en lista de usuarios
   - Archivo existe en carpeta `uploads/`

#### Test Case 9: Eliminar Usuario
1. En lista, clic en "Eliminar"
2. Confirmar en el diálogo
3. **Resultado esperado:**
   - Usuario eliminado de la lista
   - Mensaje "Usuario eliminado exitosamente"

#### Test Case 10: Validación de Duplicados
1. Crear usuario con identificador `gestor1`
2. Intentar crear otro usuario con mismo identificador
3. **Resultado esperado:** Error "El identificador ya existe"

### 8. Test de Validaciones

#### Test Case 11: Validación de Campos Requeridos
1. Intentar crear usuario sin llenar todos los campos
2. **Resultado esperado:** Navegador muestra validación HTML5

#### Test Case 12: Validación de Longitud de Contraseña
1. Intentar crear usuario con contraseña de menos de 6 caracteres
2. **Resultado esperado:** Validación HTML5 de minlength

#### Test Case 13: Validación de Tipo de Archivo
1. Intentar subir archivo .txt como foto
2. **Resultado esperado:** Error o rechazo del archivo

#### Test Case 14: Validación de Tamaño de Archivo
1. Intentar subir imagen mayor a 5MB
2. **Resultado esperado:** Error de tamaño excedido

### 9. Test de Auditoría

```bash
# Verificar registros de auditoría
docker exec -it proyectocad_db mysql -u root -prootpassword proyecto_cad

SELECT 
    a.accion,
    u.nombre_completo as usuario_modificado,
    m.nombre_completo as modificado_por,
    a.fecha_accion
FROM auditoria_usuarios a
LEFT JOIN usuarios u ON a.usuario_id = u.id
LEFT JOIN usuarios m ON a.usuario_modificador_id = m.id
ORDER BY a.fecha_accion DESC;

EXIT;
```

**Resultado esperado:** Ver todas las acciones CREATE, UPDATE, DELETE

### 10. Test de Permisos

#### Test Case 15: Acceso de Usuario No-Directivo
1. Crear usuario de tipo Gestión
2. Modificar manualmente en BD: `UPDATE usuarios SET tipo_usuario_id = 2 WHERE identificador = 'gestor1'`
3. Cerrar sesión admin
4. Iniciar sesión como gestor1
5. Intentar acceder a /public/usuarios/listar.php
6. **Resultado esperado:** Redirige con mensaje "No tienes permisos"

### 11. Test de Responsive Design

1. Abrir en diferentes tamaños de pantalla:
   - Desktop (1920x1080)
   - Tablet (768x1024)
   - Mobile (375x667)
2. **Resultado esperado:** Interfaz se adapta correctamente

### 12. Test de Navegación

```
Login → Dashboard → Usuarios → Crear → Lista
                  ↓           ↓
              Logout      Editar → Lista
                              ↓
                          Eliminar → Lista
```

**Resultado esperado:** Todas las rutas funcionan sin errores

### 13. Test de Performance

```bash
# Test de carga con Apache Bench (si está instalado)
ab -n 100 -c 10 http://localhost:8080/public/login.php

# Resultado esperado: 
# - Requests per second > 50
# - No hay errores 500
```

### 14. Test de Logs

```bash
# Ver logs de Apache
docker exec proyectocad_web tail -f /var/log/apache2/error.log

# Ver logs de MySQL
docker exec proyectocad_db tail -f /var/log/mysql/error.log

# Resultado esperado: No errores críticos
```

### 15. Test de Backup y Restore

```bash
# Crear backup
docker exec proyectocad_db mysqldump -u root -prootpassword proyecto_cad > backup_test.sql

# Verificar que el archivo se creó
ls -lh backup_test.sql

# Eliminar una tabla
docker exec -it proyectocad_db mysql -u root -prootpassword proyecto_cad -e "DROP TABLE usuarios;"

# Restaurar
docker exec -i proyectocad_db mysql -u root -prootpassword proyecto_cad < backup_test.sql

# Verificar que la tabla está de vuelta
docker exec -it proyectocad_db mysql -u root -prootpassword proyecto_cad -e "SHOW TABLES;"
```

## 🔍 Checklist Final

Antes de considerar el proyecto listo para producción:

- [ ] Todos los contenedores Docker inician correctamente
- [ ] Verificador muestra todos los checks en verde
- [ ] Login funciona con credenciales correctas
- [ ] Login falla con credenciales incorrectas
- [ ] Crear usuario funciona
- [ ] Editar usuario funciona
- [ ] Eliminar usuario funciona
- [ ] Subida de fotos funciona
- [ ] Validaciones funcionan correctamente
- [ ] Auditoría registra todas las acciones
- [ ] Permisos por tipo de usuario funcionan
- [ ] Responsive design funciona en móvil
- [ ] No hay errores en logs
- [ ] Backup y restore funcionan

## 🐛 Debugging

### Ver errores de PHP
```bash
docker-compose logs web | grep -i error
```

### Ver queries de MySQL
```bash
docker exec -it proyectocad_db mysql -u root -prootpassword

SET GLOBAL general_log = 'ON';
SHOW VARIABLES LIKE 'general_log%';
```

### Verificar permisos de archivos
```bash
docker exec proyectocad_web ls -la /var/www/html/uploads
docker exec proyectocad_web stat /var/www/html/uploads
```

## 📊 Resultados Esperados

**✅ Proyecto 100% Funcional si:**
- Todos los tests pasan
- No hay errores en logs
- Interfaz es responsive
- Seguridad implementada
- Auditoría funciona
- Docker funciona correctamente

---

**Estado del Testing:** ✅ LISTO PARA EJECUTAR

Ejecuta cada test en orden y marca los completados. ¡Buena suerte! 🚀
