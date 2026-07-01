<?php
$pagina = basename(dirname($_SERVER['PHP_SELF']));
if ($pagina === 'pagina normal' || $pagina === '.' || $pagina === 'academia') $pagina = 'inicio';

function isActive($p) {
    global $pagina;
    return $pagina === $p ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Dashboard Académico' ?></title>
    <link rel="stylesheet" href="<?= $base ?? '' ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>
<body>

<header>
    <div class="logo">
        <span style="font-size:1.6rem">🎓</span>
        <h2>AcademiaDB</h2>
    </div>
    <nav class="navbar">
        <a href="<?= $base ?? '' ?>index.php" class="<?= isActive('inicio') ?>">
            <span class="material-icons-round" style="font-size:1.1rem">dashboard</span> Inicio
        </a>
        <a href="<?= $base ?? '' ?>estudiantes/index.php" class="<?= isActive('estudiantes') ?>">
            <span class="material-icons-round" style="font-size:1.1rem">people</span> Estudiantes
        </a>
        <a href="<?= $base ?? '' ?>materias/index.php" class="<?= isActive('materias') ?>">
            <span class="material-icons-round" style="font-size:1.1rem">menu_book</span> Materias
        </a>
        <a href="<?= $base ?? '' ?>calificaciones/index.php" class="<?= isActive('calificaciones') ?>">
            <span class="material-icons-round" style="font-size:1.1rem">grade</span> Calificaciones
        </a>
    </nav>
</header>

<div class="container">
    <!-- Aside: perfil -->
    <aside>
        <div class="profile">
            <div class="top">
                <div class="profile-photo">🎓</div>
                <div>
                    <h3>Admin</h3>
                    <small class="text-muted">Sistema Académico</small>
                </div>
            </div>
            <div class="about">
                <p><b>Sistema:</b> Dashboard Universitario</p>
                <p><b>Módulo:</b> <?= $titulo ?? 'Inicio' ?></p>
                <p><b>Año:</b> <?= date('Y') ?></p>
                <span>Lenguajes de 4ta Generación</span>
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <main>
