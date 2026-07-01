<?php
$titulo = 'Estudiantes';
$base   = '../';
require_once '../config/db.php';
require_once '../config/layout.php';

$errores = [];

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM estudiantes WHERE id=$id");
    header('Location: index.php?ok=eliminado'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $cedula   = trim($_POST['cedula'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $pais     = trim($_POST['pais'] ?? '');

    // ── Validaciones ──
    if (!$nombre || strlen($nombre) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres.';
    } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]{3,150}$/', $nombre)) {
        $errores[] = 'El nombre solo debe contener letras y espacios.';
    }

    if (!$cedula) {
        $errores[] = 'La cédula es obligatoria.';
    } elseif (!preg_match('/^\d{3}-\d{7}-\d{1}$/', $cedula)) {
        $errores[] = 'Formato de cédula inválido. Usa: 001-1234567-8';
    } else {
        $dup = $conn->query("SELECT id FROM estudiantes WHERE cedula='" . $conn->real_escape_string($cedula) . "'");
        if ($dup->num_rows > 0) $errores[] = 'Ya existe un estudiante con esa cédula.';
    }

    if ($telefono && !preg_match('/^\d{3}-\d{3}-\d{4}$/', $telefono)) {
        $errores[] = 'Formato de teléfono inválido. Usa: 809-555-0000';
    }

    if ($correo && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    }

    if ($pais && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]{2,80}$/', $pais)) {
        $errores[] = 'El país solo debe contener letras.';
    }

    if (empty($errores)) {
        $n = $conn->real_escape_string($nombre);
        $c = $conn->real_escape_string($cedula);
        $t = $conn->real_escape_string($telefono);
        $e = $conn->real_escape_string($correo);
        $p = $conn->real_escape_string($pais);
        $conn->query("INSERT INTO estudiantes (nombre,cedula,telefono,correo,pais) VALUES ('$n','$c','$t','$e','$p')");
        header('Location: index.php?ok=creado'); exit;
    }
}

$estado      = $_GET['ok'] ?? '';
$estudiantes = $conn->query("
    SELECT e.*,
           COUNT(c.id) AS total_cal,
           ROUND(AVG(c.nota_final),1) AS promedio
    FROM estudiantes e
    LEFT JOIN calificaciones c ON c.estudiante_id = e.id
    GROUP BY e.id
    ORDER BY e.nombre
");
?>

<?php if ($estado === 'creado'):   ?><div class="alert alert-success">✔ Estudiante registrado correctamente.</div><?php endif; ?>
<?php if ($estado === 'eliminado'): ?><div class="alert alert-success">🗑 Estudiante eliminado.</div><?php endif; ?>
<?php if (!empty($errores)): ?>
    <div class="alert alert-error">
        <?php foreach ($errores as $err): ?>
            <div>⚠ <?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h1>Estudiantes</h1>
<small class="text-muted">Registrar y gestionar estudiantes</small>

<div class="card">
    <div class="card-header"><h2>Nuevo Estudiante</h2></div>
    <form method="POST" id="form-estudiante">
        <div class="form-grid">
            <div class="form-group">
                <label>Nombre completo *</label>
                <input type="text" name="nombre" id="nombre"
                       placeholder="Ej: María García"
                       value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                       minlength="3" maxlength="150" required>
                <small class="field-hint">Solo letras y espacios</small>
            </div>
            <div class="form-group">
                <label>Cédula *</label>
                <input type="text" name="cedula" id="cedula"
                       placeholder="001-1234567-8"
                       value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>"
                       maxlength="13" required>
                <small class="field-hint">Formato: 001-1234567-8</small>
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" id="telefono"
                       placeholder="809-555-0000"
                       value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                       maxlength="12">
                <small class="field-hint">Formato: 809-555-0000</small>
            </div>
            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="correo" id="correo"
                       placeholder="correo@ejemplo.com"
                       value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
                <small class="field-hint">Debe ser un correo real</small>
            </div>
            <div class="form-group">
                <label>País</label>
                <input type="text" name="pais" id="pais"
                       placeholder="República Dominicana"
                       value="<?= htmlspecialchars($_POST['pais'] ?? '') ?>"
                       maxlength="80">
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
        <h2>Lista de Estudiantes</h2>
        <span class="text-muted"><?= $estudiantes->num_rows ?> registros</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Cédula</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>País</th>
                    <th>Promedio</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($estudiantes->num_rows === 0): ?>
                    <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--color-info)">No hay estudiantes registrados.</td></tr>
                <?php else: while ($e = $estudiantes->fetch_assoc()): ?>
                <tr>
                    <td><?= $e['id'] ?></td>
                    <td><b><?= htmlspecialchars($e['nombre']) ?></b></td>
                    <td><?= htmlspecialchars($e['cedula']) ?></td>
                    <td><?= htmlspecialchars($e['telefono']) ?></td>
                    <td><?= htmlspecialchars($e['correo']) ?></td>
                    <td><?= htmlspecialchars($e['pais']) ?></td>
                    <td>
                        <?php if ($e['total_cal'] > 0): ?>
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar-fill <?= $e['promedio'] >= 70 ? 'success' : 'danger' ?>"
                                         style="width:<?= min($e['promedio'],100) ?>%"></div>
                                </div>
                                <span class="<?= $e['promedio'] >= 70 ? 'success' : 'danger' ?>">
                                    <b><?= $e['promedio'] ?>%</b>
                                </span>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">Sin notas</span>
                        <?php endif; ?>
                    </td>
                    <td style="display:flex;gap:.4rem;flex-wrap:wrap">
                        <a href="detalle.php?id=<?= $e['id'] ?>" class="btn btn-primary btn-sm">
                            <span class="material-icons-round" style="font-size:.9rem">visibility</span> Ver
                        </a>
                        <a href="index.php?delete=<?= $e['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('¿Eliminar a <?= htmlspecialchars($e['nombre']) ?>?')">
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
.field-hint { color: var(--color-info); font-size: .7rem; margin-top: .1rem; }
.progress-bar-wrap {
    width: 80px; height: 8px;
    background: var(--color-light);
    border-radius: 4px; overflow: hidden;
}
.progress-bar-fill {
    height: 100%; border-radius: 4px; transition: width .4s;
}
.progress-bar-fill.success { background: var(--color-success); }
.progress-bar-fill.danger  { background: var(--color-danger); }
</style>

<script>
// Auto-formato cédula: 001-1234567-8
document.getElementById('cedula').addEventListener('input', function() {
    let v = this.value.replace(/\D/g,'');
    if (v.length > 3)  v = v.slice(0,3) + '-' + v.slice(3);
    if (v.length > 11) v = v.slice(0,11) + '-' + v.slice(11);
    this.value = v.slice(0,13);
});

// Auto-formato teléfono: 809-555-0000
document.getElementById('telefono').addEventListener('input', function() {
    let v = this.value.replace(/\D/g,'');
    if (v.length > 3) v = v.slice(0,3) + '-' + v.slice(3);
    if (v.length > 7) v = v.slice(0,7) + '-' + v.slice(7);
    this.value = v.slice(0,12);
});

// Solo letras en nombre y país
['nombre','pais'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', function() {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]/g, '');
    });
});
</script>

<?php require_once '../config/layout_end.php'; ?>
