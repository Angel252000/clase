<?php
$titulo = 'Materias';
$base   = '../';
require_once '../config/db.php';
require_once '../config/layout.php';

$errores = [];

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM materias WHERE id=$id");
    header('Location: index.php?ok=eliminado'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ncurso       = trim($_POST['ncurso'] ?? '');
    $cuatrimestre = trim($_POST['cuatrimestre'] ?? '');
    $anio         = (int)($_POST['anio'] ?? 0);
    $docente      = trim($_POST['docente'] ?? '');

    if (!$ncurso || strlen($ncurso) < 3) {
        $errores[] = 'El nombre del curso debe tener al menos 3 caracteres.';
    } elseif (strlen($ncurso) > 150) {
        $errores[] = 'El nombre del curso no debe superar 150 caracteres.';
    }

    if (!in_array($cuatrimestre, ['1ro','2do','3ro'])) {
        $errores[] = 'Selecciona un cuatrimestre válido.';
    }

    if ($anio < 2020 || $anio > 2030) {
        $errores[] = 'El año debe estar entre 2020 y 2030.';
    }

    if ($docente && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s\.]{3,150}$/', $docente)) {
        $errores[] = 'El nombre del docente solo debe contener letras.';
    }

    if (empty($errores)) {
        $n = $conn->real_escape_string($ncurso);
        $c = $conn->real_escape_string($cuatrimestre);
        $d = $conn->real_escape_string($docente);
        $conn->query("INSERT INTO materias (ncurso,cuatrimestre,anio,docente) VALUES ('$n','$c',$anio,'$d')");
        header('Location: index.php?ok=creado'); exit;
    }
}

$estado   = $_GET['ok'] ?? '';
$materias = $conn->query("SELECT * FROM materias ORDER BY anio DESC, cuatrimestre, ncurso");
?>

<?php if ($estado === 'creado'):   ?><div class="alert alert-success">✔ Materia registrada correctamente.</div><?php endif; ?>
<?php if ($estado === 'eliminado'): ?><div class="alert alert-success">🗑 Materia eliminada.</div><?php endif; ?>
<?php if (!empty($errores)): ?>
    <div class="alert alert-error">
        <?php foreach ($errores as $err): ?>
            <div>⚠ <?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h1>Materias</h1>
<small class="text-muted">Registrar y gestionar materias</small>

<div class="card">
    <div class="card-header"><h2>Nueva Materia</h2></div>
    <form method="POST">
        <div class="form-grid">
            <div class="form-group" style="grid-column:span 2">
                <label>Nombre del Curso *</label>
                <input type="text" name="ncurso"
                       placeholder="Ej: Lenguajes de Cuarta Generación"
                       value="<?= htmlspecialchars($_POST['ncurso'] ?? '') ?>"
                       minlength="3" maxlength="150" required>
                <small class="field-hint">Entre 3 y 150 caracteres</small>
            </div>
            <div class="form-group">
                <label>Cuatrimestre *</label>
                <select name="cuatrimestre" required>
                    <option value="">Seleccionar...</option>
                    <option value="1ro" <?= ($_POST['cuatrimestre'] ?? '') === '1ro' ? 'selected' : '' ?>>1ro</option>
                    <option value="2do" <?= ($_POST['cuatrimestre'] ?? '') === '2do' ? 'selected' : '' ?>>2do</option>
                    <option value="3ro" <?= ($_POST['cuatrimestre'] ?? '') === '3ro' ? 'selected' : '' ?>>3ro</option>
                </select>
            </div>
            <div class="form-group">
                <label>Año *</label>
                <input type="number" name="anio"
                       value="<?= (int)($_POST['anio'] ?? date('Y')) ?>"
                       min="2020" max="2030" required>
                <small class="field-hint">Entre 2020 y 2030</small>
            </div>
            <div class="form-group">
                <label>Docente</label>
                <input type="text" name="docente" id="docente"
                       placeholder="Ej: Prof. López"
                       value="<?= htmlspecialchars($_POST['docente'] ?? '') ?>"
                       maxlength="150">
                <small class="field-hint">Solo letras</small>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <span class="material-icons-round" style="font-size:1rem">save</span> Guardar
            </button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2>Lista de Materias</h2>
        <span class="text-muted"><?= $materias->num_rows ?> registros</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Nombre del Curso</th><th>Cuatrimestre</th><th>Año</th><th>Docente</th><th>Acción</th></tr>
            </thead>
            <tbody>
                <?php if ($materias->num_rows === 0): ?>
                    <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--color-info)">No hay materias registradas.</td></tr>
                <?php else: while ($m = $materias->fetch_assoc()): ?>
                <tr>
                    <td><?= $m['id'] ?></td>
                    <td><b><?= htmlspecialchars($m['ncurso']) ?></b></td>
                    <td><span class="nota-badge aprobado"><?= htmlspecialchars($m['cuatrimestre']) ?></span></td>
                    <td><?= $m['anio'] ?></td>
                    <td><?= htmlspecialchars($m['docente']) ?></td>
                    <td>
                        <a href="index.php?delete=<?= $m['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('¿Eliminar esta materia?')">
                            <span class="material-icons-round" style="font-size:.9rem">delete</span> Eliminar
                        </a>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.field-hint { color: var(--color-info); font-size: .7rem; margin-top: .1rem; }
</style>

<script>
document.getElementById('docente')?.addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s\.]/g, '');
});
</script>

<?php require_once '../config/layout_end.php'; ?>
