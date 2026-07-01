<?php
$titulo = 'Inicio';
$base   = '';
require_once 'config/db.php';
require_once 'config/layout.php';

$total_est = (int)$conn->query("SELECT COUNT(*) FROM estudiantes")->fetch_row()[0];
$total_mat = (int)$conn->query("SELECT COUNT(*) FROM materias")->fetch_row()[0];
$total_cal = (int)$conn->query("SELECT COUNT(*) FROM calificaciones")->fetch_row()[0];
$promedio  = (float)($conn->query("SELECT ROUND(AVG(nota_final),1) FROM calificaciones")->fetch_row()[0] ?? 0);

// Para el círculo SVG: stroke-dasharray=220, offset = 220 - (valor/100*220)
function circleOffset(float $val, float $max): string {
    $pct = $max > 0 ? min($val / $max, 1) : 0;
    return number_format(220 - ($pct * 220), 2);
}

$ultimas = $conn->query("
    SELECT e.nombre, m.ncurso, c.nota_final
    FROM calificaciones c
    JOIN estudiantes e ON e.id = c.estudiante_id
    JOIN materias m    ON m.id = c.materia_id
    ORDER BY c.id DESC LIMIT 5
");

$recientes = $conn->query("SELECT nombre, pais FROM estudiantes ORDER BY id DESC LIMIT 4");

ob_start();
?>
<div class="right">
    <div class="summary">
        <h2>Últimas Notas</h2>
        <div class="updates">
            <?php
            $ultimas->data_seek(0);
            while ($u = $ultimas->fetch_assoc()):
                $aprobado = $u['nota_final'] >= 70;
            ?>
            <div class="item">
                <span class="icon"><?= $aprobado ? '✅' : '❌' ?></span>
                <div>
                    <h4><?= htmlspecialchars($u['nombre']) ?></h4>
                    <small><?= htmlspecialchars($u['ncurso']) ?> — <b class="<?= $aprobado ? 'success' : 'danger' ?>"><?= number_format($u['nota_final'],2) ?></b></small>
                </div>
            </div>
            <?php endwhile; ?>
            <?php if ($ultimas->num_rows === 0): ?>
                <p style="padding:.5rem 0;font-size:.8rem">Sin calificaciones aún.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="recent-students">
        <h2>Estudiantes Recientes</h2>
        <?php while ($r = $recientes->fetch_assoc()): ?>
        <div class="student-item">
            <div class="avatar"><?= mb_strtoupper(mb_substr($r['nombre'],0,1)) ?></div>
            <div>
                <h4><?= htmlspecialchars($r['nombre']) ?></h4>
                <small><?= htmlspecialchars($r['pais']) ?></small>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<?php
$right_panel = ob_get_clean();
?>

<h1>Dashboard</h1>
<small class="text-muted">Bienvenido al sistema de gestión académica</small>

<div class="stat-cards" style="margin-top:1.5rem">
    <!-- Estudiantes -->
    <div class="stat-card">
        <div class="icon-wrap">👩‍🎓</div>
        <h3>Estudiantes</h3>
        <div class="progress">
            <svg viewBox="0 0 100 100" width="80" height="80">
                <circle cx="45" cy="45" r="35" stroke-dasharray="220" stroke-dashoffset="<?= circleOffset($total_est, 50) ?>"/>
            </svg>
            <div class="number"><b><?= $total_est ?></b></div>
        </div>
        <small class="text-muted">Registrados</small>
    </div>

    <!-- Materias -->
    <div class="stat-card danger">
        <div class="icon-wrap">📚</div>
        <h3>Materias</h3>
        <div class="progress">
            <svg viewBox="0 0 100 100" width="80" height="80">
                <circle cx="45" cy="45" r="35" stroke-dasharray="220" stroke-dashoffset="<?= circleOffset($total_mat, 20) ?>"/>
            </svg>
            <div class="number"><b><?= $total_mat ?></b></div>
        </div>
        <small class="text-muted">Registradas</small>
    </div>

    <!-- Calificaciones -->
    <div class="stat-card success">
        <div class="icon-wrap">📊</div>
        <h3>Calificaciones</h3>
        <div class="progress">
            <svg viewBox="0 0 100 100" width="80" height="80">
                <circle cx="45" cy="45" r="35" stroke-dasharray="220" stroke-dashoffset="<?= circleOffset($total_cal, 100) ?>"/>
            </svg>
            <div class="number"><b><?= $total_cal ?></b></div>
        </div>
        <small class="text-muted">Registradas</small>
    </div>

    <!-- Promedio -->
    <div class="stat-card warning">
        <div class="icon-wrap">🏆</div>
        <h3>Promedio General</h3>
        <div class="progress">
            <svg viewBox="0 0 100 100" width="80" height="80">
                <circle cx="45" cy="45" r="35" stroke-dasharray="220" stroke-dashoffset="<?= circleOffset($promedio, 100) ?>"/>
            </svg>
            <div class="number"><b><?= $promedio ?></b></div>
        </div>
        <small class="text-muted">Sobre 100</small>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Historial de Notas</h2>
        <a href="calificaciones/index.php" class="btn btn-primary btn-sm">Ver todas</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Materia</th>
                    <th>Nota Final</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $ultimas->data_seek(0);
                if ($ultimas->num_rows === 0):
                ?>
                    <tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--color-info)">Sin registros.</td></tr>
                <?php else: while ($r = $ultimas->fetch_assoc()): ?>
                <tr>
                    <td><b><?= htmlspecialchars($r['nombre']) ?></b></td>
                    <td><?= htmlspecialchars($r['ncurso']) ?></td>
                    <td><b><?= number_format($r['nota_final'],2) ?></b></td>
                    <td>
                        <?php if ($r['nota_final'] >= 70): ?>
                            <span class="nota-badge aprobado">Aprobado</span>
                        <?php else: ?>
                            <span class="nota-badge suspendido">Reprobado</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'config/layout_end.php'; ?>
