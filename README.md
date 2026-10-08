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
