# AcademiaDB — Dashboard académico en PHP

Sistema web para registrar estudiantes, materias y calificaciones de una
carrera universitaria, con un panel que resume promedios y últimas notas.
Hecho para la clase de **Lenguajes de Cuarta Generación**.

## Funcionalidades

- **Inicio** (`index.php`): total de estudiantes, materias y calificaciones,
  promedio general, últimas 5 notas y estudiantes recientes.
- **Estudiantes**: alta con validación, listado, borrado y vista de detalle
  con sus notas, promedio y materias que aún no tiene calificadas.
- **Materias**: alta por curso, cuatrimestre (1ro/2do/3ro), año y docente.
- **Calificaciones**: una nota por estudiante y materia, con cálculo en vivo
  de la nota final mientras se escribe.

## Stack

- PHP (sin framework) con la extensión `mysqli`
- MySQL 5.7+ (usa una columna generada para la nota final)
- HTML, CSS y JavaScript sin dependencias
- Material Icons Round desde Google Fonts

## Estructura

```
index.php              Dashboard de inicio
config/db.php          Conexión a MySQL (credenciales locales)
config/layout.php      Cabecera, menú y panel lateral compartidos
config/layout_end.php  Cierre del layout
estudiantes/           Listado, alta, borrado y detalle.php
materias/              Listado, alta y borrado
calificaciones/        Registro e historial de notas
assets/css/style.css   Estilos del dashboard
assets/js/main.js      Nota final en vivo y cierre de alertas
database.sql           Esquema y datos de ejemplo
```

## Base de datos

`database.sql` crea `academia_db` con tres tablas:

| Tabla | Campos principales |
|---|---|
| `estudiantes` | nombre, cédula (única), teléfono, correo, país |
| `materias` | ncurso, cuatrimestre, anio, docente |
| `calificaciones` | estudiante_id, materia_id, cuatro rubros y `nota_final` |

Borrar un estudiante o una materia borra también sus calificaciones
(`ON DELETE CASCADE`). El script incluye 3 estudiantes, 3 materias y
3 calificaciones de ejemplo.

## Cómo correrlo (MAMP)

1. Copia la carpeta dentro de `htdocs` de MAMP.
2. Abre phpMyAdmin e importa `database.sql`.
3. Revisa `config/db.php`: por defecto usa `root` / `root` en `localhost`,
   que es lo que trae MAMP.
4. Entra a `http://localhost:8888/<carpeta>/index.php`.

Si la conexión falla, la página muestra el error de MySQL en lugar del panel.

## Reglas de negocio

**Nota final** = promedio simple de los cuatro rubros
(devocionales, cotidianos, complementarios, proyectos). La calcula MySQL en
la columna `nota_final`; el formulario la muestra antes de guardar.
Se aprueba con **70** o más.

Validaciones del servidor:

- Cada rubro entre 0 y 100; no se repite la pareja estudiante + materia.
- Cédula con formato `001-1234567-8` y sin duplicados.
- Teléfono opcional con formato `809-555-0000`; correo opcional válido.
- Materia: nombre de 3 a 150 caracteres y año entre 2020 y 2030.

## Limitaciones conocidas

- Es un proyecto de clase para uso local: no tiene login.
- El borrado se hace por `GET` (`?delete=id`) con un `confirm()` del navegador,
  sin token CSRF.
- Las consultas usan `real_escape_string` y casteo a entero en vez de
  sentencias preparadas.
- `config/db.php` trae las credenciales por defecto de MAMP; cámbialas antes
  de usarlo fuera de tu máquina.
