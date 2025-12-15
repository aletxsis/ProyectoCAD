# 👥 USUARIOS DEL SISTEMA - Proyecto CAD

## 🔑 Contraseña Universal
**Todos los usuarios tienen la misma contraseña:** `admin123`

---

## 👔 USUARIOS DIRECTIVOS (Tipo 1)
*Pueden gestionar usuarios de tipo Gestión (crear, editar, eliminar)*

| Identificador | Nombre Completo | Cargo |
|---------------|-----------------|-------|
| `admin` | Carlos Rodríguez Martínez | Director General |
| `director1` | Ana María González López | Directora de Operaciones |
| `director2` | Roberto Sánchez Pérez | Director de Recursos Humanos |

**Permisos:**
- ✅ Acceso completo al sistema
- ✅ Crear usuarios de Gestión
- ✅ Editar usuarios de Gestión
- ✅ Eliminar usuarios de Gestión
- ✅ Ver auditoría de cambios

---

## 📊 USUARIOS DE GESTIÓN (Tipo 2)
*Usuarios gestionados por los Directivos*

| Identificador | Nombre Completo | Cargo |
|---------------|-----------------|-------|
| `gestor1` | María Elena Torres Ramírez | Gerente de Ventas |
| `gestor2` | Juan Carlos Mendoza Silva | Gerente de Marketing |
| `gestor3` | Patricia Hernández Cruz | Gerente de Finanzas |
| `gestor4` | Luis Alberto Flores Vega | Gerente de Logística |
| `gestor5` | Carmen Beatriz Morales Díaz | Gerente de Recursos Humanos |

**Permisos:**
- ✅ Acceso al sistema
- ✅ Ver su perfil
- ❌ No pueden gestionar otros usuarios
- 📝 *Permisos específicos pendientes de implementación*

---

## 🔧 USUARIOS OPERATIVOS (Tipo 3)
*Personal operativo del sistema*

| Identificador | Nombre Completo | Cargo |
|---------------|-----------------|-------|
| `operador1` | Diego Alejandro Castro Ruiz | Analista de Datos |
| `operador2` | Sofía Gabriela Ortiz Medina | Coordinadora de Proyectos |
| `operador3` | Miguel Ángel Vargas López | Técnico de Soporte |
| `operador4` | Daniela Isabel Ramos Gutiérrez | Asistente Administrativa |
| `operador5` | Fernando José Jiménez Navarro | Operador de Sistema |

**Permisos:**
- ✅ Acceso al sistema
- ✅ Ver su perfil
- ❌ No pueden gestionar usuarios
- 📝 *Funcionalidades específicas pendientes de implementación*

---

## 🧪 USUARIOS PARA TESTING

### Escenarios de Prueba

#### 1. Login como Directivo
```
Usuario: admin
Contraseña: admin123
Resultado: Acceso completo, puede gestionar usuarios
```

#### 2. Login como Gestión
```
Usuario: gestor1
Contraseña: admin123
Resultado: Acceso básico, no puede gestionar usuarios
```

#### 3. Login como Operativo
```
Usuario: operador1
Contraseña: admin123
Resultado: Acceso básico, funcionalidades limitadas
```

---

## 📋 RESUMEN

| Tipo | Cantidad | Permisos Principales |
|------|----------|---------------------|
| **Directivo** | 3 | Gestión completa de usuarios de Gestión |
| **Gestión** | 5 | Acceso al sistema, perfil propio |
| **Operativo** | 5 | Acceso básico al sistema |
| **TOTAL** | **13 usuarios** | - |

---

## 🔒 SEGURIDAD

### Cambiar Contraseñas en Producción

Para cambiar la contraseña de un usuario específico:

1. **Desde phpMyAdmin** (http://localhost:8082):
```sql
UPDATE usuarios 
SET password = '$2y$10$NUEVO_HASH_AQUI' 
WHERE identificador = 'admin';
```

2. **Generar hash de contraseña**:
```php
<?php
echo password_hash('nueva_contraseña', PASSWORD_BCRYPT);
?>
```

### Recomendaciones
- ⚠️ **Cambiar todas las contraseñas antes de producción**
- ✅ Usar contraseñas únicas por usuario
- ✅ Implementar política de complejidad de contraseñas
- ✅ Habilitar cambio de contraseña desde perfil
- ✅ Implementar recuperación de contraseña

---

## 📊 VERIFICAR USUARIOS

### Consulta SQL para ver todos los usuarios:
```sql
SELECT 
    u.identificador,
    u.nombre_completo,
    u.cargo,
    t.nombre as tipo_usuario,
    u.activo,
    u.fecha_creacion
FROM usuarios u
INNER JOIN tipo_usuario t ON u.tipo_usuario_id = t.id
ORDER BY t.id, u.nombre_completo;
```

### Desde terminal:
```bash
docker exec -it proyectocad_db mysql -u root -prootpassword proyecto_cad \
  -e "SELECT identificador, nombre_completo, cargo FROM usuarios ORDER BY tipo_usuario_id;"
```

---

## 🎯 CASOS DE USO

### Directivo gestiona usuarios de Gestión
1. Login como `admin`
2. Ir a "Usuarios"
3. Ver los 5 usuarios de Gestión
4. Crear, editar o eliminar según necesidad

### Usuario de Gestión accede al sistema
1. Login como `gestor1`
2. Ver dashboard personalizado
3. Acceso limitado (no ve menú de usuarios)

### Usuario Operativo (funcionalidad pendiente)
1. Login como `operador1`
2. Ver dashboard básico
3. Funcionalidades específicas por implementar

---

**Fecha de creación:** 15 de diciembre de 2025  
**Total de usuarios:** 13 (3 Directivos, 5 Gestión, 5 Operativos)  
**Contraseña de prueba:** admin123 (⚠️ CAMBIAR EN PRODUCCIÓN)
