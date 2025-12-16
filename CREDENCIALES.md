# 🔐 Credenciales de Acceso - SAES 2.0

## Contraseña Universal
**Todos los usuarios utilizan la misma contraseña para pruebas:**
```
admin123
```

---

## 👔 Usuario Directivo (Administrador)

| Campo | Valor |
|-------|-------|
| **Usuario** | `admin` |
| **Contraseña** | `admin123` |
| **Nombre** | Carlos Rodríguez Martínez |
| **Correo** | admin@saes.mx |
| **Cargo** | Director General |
| **Permisos** | Gestionar usuarios de tipo Gestión |

---

## 👨‍💼 Usuarios de Gestión

### Gestión 1
- **Usuario:** `gestion1`
- **Contraseña:** `admin123`
- **Nombre:** María Elena Torres Ramírez
- **Correo:** mtorres@saes.mx
- **Permisos:** Inscribir alumnos, asignar materias y calificaciones

### Gestión 2
- **Usuario:** `gestion2`
- **Contraseña:** `admin123`
- **Nombre:** Juan Carlos Mendoza Silva
- **Correo:** jmendoza@saes.mx
- **Permisos:** Inscribir alumnos, asignar materias y calificaciones

### Gestión 3
- **Usuario:** `gestion3`
- **Contraseña:** `admin123`
- **Nombre:** Patricia Hernández Cruz
- **Correo:** phernandez@saes.mx
- **Permisos:** Inscribir alumnos, asignar materias y calificaciones

---

## 🎓 Alumnos

### Alumno 1
- **Matrícula:** `2021630001`
- **Contraseña:** `admin123`
- **Nombre:** Diego Alejandro Castro Ruiz
- **Edad:** 20 años
- **Permisos:** Ver sus materias y calificaciones

### Alumno 2
- **Matrícula:** `2021630002`
- **Contraseña:** `admin123`
- **Nombre:** Sofía Gabriela Ortiz Medina
- **Edad:** 19 años
- **Permisos:** Ver sus materias y calificaciones

### Alumno 3
- **Matrícula:** `2021630003`
- **Contraseña:** `admin123`
- **Nombre:** Miguel Ángel Vargas López
- **Edad:** 21 años
- **Permisos:** Ver sus materias y calificaciones

### Alumno 4
- **Matrícula:** `2021630004`
- **Contraseña:** `admin123`
- **Nombre:** Daniela Isabel Ramos Gutiérrez
- **Edad:** 20 años
- **Permisos:** Ver sus materias y calificaciones

### Alumno 5
- **Matrícula:** `2021630005`
- **Contraseña:** `admin123`
- **Nombre:** Fernando José Jiménez Navarro
- **Edad:** 22 años
- **Permisos:** Ver sus materias y calificaciones

---

## 📚 Materias Disponibles

1. **MAT001** - Cálculo Diferencial e Integral (8 créditos)
2. **MAT002** - Álgebra Lineal (6 créditos)
3. **PROG001** - Programación Orientada a Objetos (8 créditos)
4. **PROG002** - Estructuras de Datos (8 créditos)
5. **CLOUD001** - Cómputo en la Nube (6 créditos)
6. **DB001** - Bases de Datos (6 créditos)
7. **WEB001** - Desarrollo Web (6 créditos)
8. **NET001** - Redes de Computadoras (6 créditos)

---

## 🌐 URLs de Acceso

- **Aplicación Web:** http://localhost:8090
- **phpMyAdmin:** http://localhost:8082
  - Usuario: `root`
  - Contraseña: `rootpassword`

---

## ℹ️ Notas

- Las calificaciones se calculan automáticamente como el promedio de los 3 parciales
- La calificación mínima aprobatoria es 70
- Los alumnos tienen materias y calificaciones precargadas para pruebas
- Las contraseñas están hasheadas con bcrypt (PASSWORD_BCRYPT)

---

## 🔧 Cambiar Contraseñas

Para cambiar las contraseñas en producción, usa este comando SQL:

```sql
UPDATE usuarios SET password = '$2y$10$HASH_GENERADO' WHERE identificador = 'usuario';
UPDATE alumnos SET password = '$2y$10$HASH_GENERADO' WHERE identificador = 'matricula';
```

Para generar un hash en PHP:
```php
<?php
echo password_hash('tu_contraseña', PASSWORD_BCRYPT);
?>
```
