<?php
$titulo = 'Calificaciones';
$base   = '../';
require_once '../config/db.php';
require_once '../config/layout.php';

$errores = [];

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM calificaciones WHERE id=$id");
    header('Location: index.php?ok=eliminado'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $est_id = (int)($_POST['estudiante_id'] ?? 0);
    $mat_id = (int)($_POST['materia_id'] ?? 0);
    $dev    = (float)($_POST['devocionales'] ?? 0);
    $cot    = (float)($_POST['cotidianos'] ?? 0);
    $comp   = (float)($_POST['complementarios'] ?? 0);
    $proy   = (float)($_POST['proyectos'] ?? 0);

    if (!$est_id) $errores[] = 'Debes seleccionar un estudiante.';
    if (!$mat_id) $errores[] = 'Debes seleccionar una materia.';

    foreach ([
        'Devocionales'    => $dev,
        'Cotidianos'      => $cot,
        'Complementarios' => $comp,
        'Proyectos'       => $proy,
    ] as $label => $val) {
        if ($val < 0 || $val > 100) {
            $errores[] = "$label debe estar entre 0 y 100.";
        }
    }

    // Evitar duplicado estudiante+materia
    if ($est_id && $mat_id) {
        $dup = $conn->query("SELECT id FROM calificaciones WHERE estudiante_id=$est_id AND materia_id=$mat_id");
        if ($dup->num_rows > 0) {
            $errores[] = 'Este estudiante ya tiene calificación registrada en esa materia.';
        }
    }

    if (empty($errores)) {
        $conn->query("INSERT INTO calificaciones (estudiante_id,materia_id,devocionales,cotidianos,complementarios,proyectos)
                      VALUES ($est_id,$mat_id,$dev,$cot,$comp,$proy)");
        header('Location: index.php?ok=creado'); exit;
    }
}

$estado      = $_GET['ok'] ?? '';
$estudiantes = $conn->query("SELECT id,nombre FROM estudiantes ORDER BY nombre");
$materias    = $conn->query("SELECT id,ncurso,cuatrimestre,anio FROM materias ORDER BY anio DESC, ncurso");
$historial   = $conn->query("
    SELECT c.id, e.nombre AS estudiante, m.ncurso AS materia,
           m.cuatrimestre, m.anio,
           c.devocionales, c.cotidianos, c.complementarios, c.proyectos, c.nota_final,
           e.id AS est_id
    FROM calificaciones c
    JOIN estudiantes e ON e.id = c.estudiante_id
    JOIN materias m    ON m.id = c.materia_id
    ORDER BY c.id DESC
");
?>

<?php if ($estado === 'creado'):   ?><div class="alert alert-success">✔ Calificación registrada.</div><?php endif; ?>
<?php if ($estado === 'eliminado'): ?><div class="alert alert-success">🗑 Calificación eliminada.</div><?php endif; ?>
<?php if (!empty($errores)): ?>
    <div class="alert alert-error">
        <?php foreach ($errores as $err): ?>
            <div>⚠ <?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h1>Calificaciones</h1>
<small class="text-muted">Registrar notas y ver historial</small>

<div class="card">
    <div class="card-header"><h2>Nueva Calificación</h2></div>
    <form method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label>Estudiante *</label>
                <select name="estudiante_id" required>
                    <option value="">Seleccionar...</option>
                    <?php while ($e = $estudiantes->fetch_assoc()): ?>
                    <option value="<?= $e['id'] ?>" <?= ($_POST['estudiante_id'] ?? '') == $e['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['nombre']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Materia *</label>
                <select name="materia_id" required>
                    <option value="">Seleccionar...</option>
                    <?php while ($m = $materias->fetch_assoc()): ?>
                    <option value="<?= $m['id'] ?>" <?= ($_POST['materia_id'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['ncurso']) ?> — <?= $m['cuatrimestre'] ?> <?= $m['anio'] ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <?php
            $campos = ['devocionales' => 'Devocionales', 'cotidianos' => 'Cotidianos',
                       'complementarios' => 'Complementarios', 'proyectos' => 'Proyectos'];
            foreach ($campos as $campo => $label):
            ?>
            <div class="form-group">
                <label><?= $label ?> <small class="field-hint">(0–100)</small></label>
                <input type="number" id="<?= $campo ?>" name="<?= $campo ?>"
                       value="<?= (float)($_POST[$campo] ?? 0) ?>"
                       min="0" max="100" step="0.01">
            </div>
            <?php endforeach; ?>

            <div class="form-group">
                <label>Nota Final (automática)</label>
                <div id="nota-preview">
                    <span id="nota-resultado"><?= isset($_POST['devocionales'])
                        ? number_format(((float)$_POST['devocionales']+(float)$_POST['cotidianos']+(float)$_POST['complementarios']+(float)$_POST['proyectos'])/4,2)
                        : '0.00' ?></span>
                    <small>(devoc + cotid + compl + proy) / 4</small>
                </div>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <span class="material-icons-round" style="font-size:1rem">save</span> Guardar Calificación
            </button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2>Historial de Calificaciones</h2>
        <span class="text-muted"><?= $historial->num_rows ?> registros</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Estudiante</th><th>Materia</th><th>Cuatrimestre</th>
                    <th>Devoc.</th><th>Cotid.</th><th>Compl.</th><th>Proyec.</th>
                    <th>Nota Final</th><th>%</th><th>Estado</th><th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($historial->num_rows === 0): ?>
                    <tr><td colspan="11" style="text-align:center;padding:2rem;color:var(--color-info)">Sin calificaciones registradas.</td></tr>
                <?php else: while ($c = $historial->fetch_assoc()): ?>
                <tr>
                    <td>
                        <a href="../estudiantes/detalle.php?id=<?= $c['est_id'] ?>" class="primary">
                            <b><?= htmlspecialchars($c['estudiante']) ?></b>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($c['materia']) ?></td>
                    <td><span class="nota-badge aprobado"><?= $c['cuatrimestre'] ?> <?= $c['anio'] ?></span></td>
                    <td><?= number_format($c['devocionales'],1) ?></td>
                    <td><?= number_format($c['cotidianos'],1) ?></td>
                    <td><?= number_format($c['complementarios'],1) ?></td>
                    <td><?= number_format($c['proyectos'],1) ?></td>
                    <td><b><?= number_format($c['nota_final'],2) ?></b></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.4rem">
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill <?= $c['nota_final'] >= 70 ? 'success' : 'danger' ?>"
                                     style="width:<?= min($c['nota_final'],100) ?>%"></div>
                            </div>
                            <small><?= number_format($c['nota_final'],0) ?>%</small>
                        </div>
                    </td>
                    <td>
                        <span class="nota-badge <?= $c['nota_final'] >= 70 ? 'aprobado' : 'suspendido' ?>">
                            <?= $c['nota_final'] >= 70 ? 'Aprobado' : 'Reprobado' ?>
                        </span>
                    </td>
                    <td>
                        <a href="index.php?delete=<?= $c['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('¿Eliminar esta calificación?')">
                            <span class="material-icons-round" style="font-size:.9rem">delete</span>
                        </a>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.field-hint { color: var(--color-info); font-size: .7rem; }
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
