<?php
$titulo = 'Detalle Estudiante';
$base   = '../';
require_once '../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }

$est = $conn->query("SELECT * FROM estudiantes WHERE id=$id")->fetch_assoc();
if (!$est) { header('Location: index.php'); exit; }

// Calificaciones del estudiante con info de materia
$cal = $conn->query("
    SELECT c.*, m.ncurso, m.cuatrimestre, m.anio, m.docente
    FROM calificaciones c
    JOIN materias m ON m.id = c.materia_id
    WHERE c.estudiante_id = $id
    ORDER BY m.anio DESC, m.cuatrimestre
");

// Materias SIN calificaciones para este estudiante
$faltantes = $conn->query("
    SELECT m.ncurso, m.cuatrimestre, m.anio, m.docente
    FROM materias m
    LEFT JOIN calificaciones c ON c.materia_id = m.id AND c.estudiante_id = $id
    WHERE c.id IS NULL
    ORDER BY m.anio DESC
");

// Promedio general
$prom = $conn->query("SELECT ROUND(AVG(nota_final),2), COUNT(*) FROM calificaciones WHERE estudiante_id=$id")->fetch_row();
$promedio   = (float)($prom[0] ?? 0);
$total_mats = (int)($prom[1] ?? 0);

// Cuatrimestre actual (el más reciente)
$cuatri_actual = $conn->query("
    SELECT m.cuatrimestre, m.anio
    FROM calificaciones c JOIN materias m ON m.id = c.materia_id
    WHERE c.estudiante_id = $id
    ORDER BY m.anio DESC, m.cuatrimestre DESC LIMIT 1
")->fetch_assoc();

require_once '../config/layout.php';
?>

<h1><?= htmlspecialchars($est['nombre']) ?></h1>
<small class="text-muted">Perfil académico completo</small>

<!-- Info del estudiante -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.6rem;margin-top:1.5rem">

    <div class="card" style="margin-top:0">
        <div class="card-header"><h2>Datos Personales</h2></div>
        <table style="font-size:.85rem">
            <tr><td style="padding:.5rem .8rem;color:var(--color-info);width:130px">Cédula</td>
                <td><b><?= htmlspecialchars($est['cedula']) ?></b></td></tr>
            <tr><td style="padding:.5rem .8rem;color:var(--color-info)">Teléfono</td>
                <td><?= htmlspecialchars($est['telefono'] ?: '—') ?></td></tr>
            <tr><td style="padding:.5rem .8rem;color:var(--color-info)">Correo</td>
                <td><?= htmlspecialchars($est['correo'] ?: '—') ?></td></tr>
            <tr><td style="padding:.5rem .8rem;color:var(--color-info)">País</td>
                <td><?= htmlspecialchars($est['pais'] ?: '—') ?></td></tr>
            <tr><td style="padding:.5rem .8rem;color:var(--color-info)">Cuatrimestre actual</td>
                <td><b class="primary"><?= $cuatri_actual ? $cuatri_actual['cuatrimestre'].' — '.$cuatri_actual['anio'] : 'Sin registros' ?></b></td></tr>
        </table>
    </div>

    <div class="card" style="margin-top:0;text-align:center">
        <div class="card-header"><h2>Rendimiento General</h2></div>
        <div style="position:relative;width:120px;height:120px;margin:0 auto .8rem">
            <svg viewBox="0 0 110 110" width="120" height="120">
                <circle cx="50" cy="50" r="40" fill="none"
                    stroke="var(--color-light)" stroke-width="10"
                    transform="translate(5,5)"/>
                <circle cx="50" cy="50" r="40" fill="none"
                    stroke="<?= $promedio >= 70 ? 'var(--color-success)' : 'var(--color-danger)' ?>"
                    stroke-width="10" stroke-linecap="round"
                    transform="translate(5,5) rotate(-90,50,50)"
                    stroke-dasharray="251.2"
                    stroke-dashoffset="<?= number_format(251.2 - ($promedio / 100 * 251.2), 2) ?>"/>
            </svg>
            <div style="position:absolute;top:0;left:0;width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center">
                <span style="font-size:1.5rem;font-weight:800;color:<?= $promedio >= 70 ? 'var(--color-success)' : 'var(--color-danger)' ?>">
                    <?= $promedio ?>
                </span>
                <small class="text-muted">/ 100</small>
            </div>
        </div>
        <p><b><?= $total_mats ?></b> materia(s) calificada(s)</p>
        <p style="margin-top:.4rem">
            <span class="nota-badge <?= $promedio >= 70 ? 'aprobado' : 'suspendido' ?>">
                <?= $promedio >= 70 ? '✔ Aprobado' : '✘ En riesgo' ?>
            </span>
        </p>
    </div>
</div>

<!-- Calificaciones por materia -->
<div class="card">
    <div class="card-header">
        <h2>Calificaciones por Materia</h2>
        <span class="text-muted"><?= $cal->num_rows ?> registros</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Materia</th>
                    <th>Cuatrimestre</th>
                    <th>Año</th>
                    <th>Docente</th>
                    <th>Devoc.</th>
                    <th>Cotid.</th>
                    <th>Compl.</th>
                    <th>Proyec.</th>
                    <th>Nota Final</th>
                    <th>%</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($cal->num_rows === 0): ?>
                    <tr><td colspan="11" style="text-align:center;padding:2rem;color:var(--color-info)">Sin calificaciones registradas.</td></tr>
                <?php else: while ($r = $cal->fetch_assoc()): ?>
                <tr>
                    <td><b><?= htmlspecialchars($r['ncurso']) ?></b></td>
                    <td><?= htmlspecialchars($r['cuatrimestre']) ?></td>
                    <td><?= $r['anio'] ?></td>
                    <td><?= htmlspecialchars($r['docente']) ?></td>
                    <td><?= number_format($r['devocionales'],1) ?></td>
                    <td><?= number_format($r['cotidianos'],1) ?></td>
                    <td><?= number_format($r['complementarios'],1) ?></td>
                    <td><?= number_format($r['proyectos'],1) ?></td>
                    <td><b><?= number_format($r['nota_final'],2) ?></b></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.4rem">
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill <?= $r['nota_final'] >= 70 ? 'success' : 'danger' ?>"
                                     style="width:<?= min($r['nota_final'],100) ?>%"></div>
                            </div>
                            <small><?= number_format($r['nota_final'],0) ?>%</small>
                        </div>
                    </td>
                    <td>
                        <span class="nota-badge <?= $r['nota_final'] >= 70 ? 'aprobado' : 'suspendido' ?>">
                            <?= $r['nota_final'] >= 70 ? 'Aprobado' : 'Reprobado' ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Materias faltantes -->
<?php if ($faltantes->num_rows > 0): ?>
<div class="card" style="border-left: 4px solid var(--color-warning)">
    <div class="card-header">
        <h2>⚠ Materias sin Calificación</h2>
        <span class="text-muted"><?= $faltantes->num_rows ?> pendiente(s)</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Materia</th><th>Cuatrimestre</th><th>Año</th><th>Docente</th><th>Acción</th></tr>
            </thead>
            <tbody>
                <?php while ($f = $faltantes->fetch_assoc()): ?>
                <tr>
                    <td><b><?= htmlspecialchars($f['ncurso']) ?></b></td>
                    <td><?= htmlspecialchars($f['cuatrimestre']) ?></td>
                    <td><?= $f['anio'] ?></td>
                    <td><?= htmlspecialchars($f['docente']) ?></td>
                    <td>
                        <a href="../calificaciones/index.php" class="btn btn-primary btn-sm">
                            <span class="material-icons-round" style="font-size:.9rem">add</span> Registrar nota
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div style="margin-top:1rem">
    <a href="index.php" class="btn btn-primary">
        <span class="material-icons-round" style="font-size:1rem">arrow_back</span> Volver a Estudiantes
    </a>
</div>

<style>
.progress-bar-wrap {
    width: 70px; height: 7px;
    background: var(--color-light);
    border-radius: 4px; overflow: hidden;
}
.progress-bar-fill { height:100%; border-radius:4px; transition:width .4s; }
.progress-bar-fill.success { background: var(--color-success); }
.progress-bar-fill.danger  { background: var(--color-danger); }
</style>

<?php require_once '../config/layout_end.php'; ?>
