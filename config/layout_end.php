    </main>
    <!-- Right panel placeholder - filled per page -->
    <?php if (isset($right_panel)) echo $right_panel; else: ?>
    <div class="right">
        <div class="summary">
            <h2>Accesos Rápidos</h2>
            <div class="updates">
                <div class="item">
                    <span class="icon">👩‍🎓</span>
                    <div>
                        <h4><a href="<?= $base ?? '' ?>estudiantes/index.php" class="primary">Estudiantes</a></h4>
                        <small>Registrar / ver lista</small>
                    </div>
                </div>
                <div class="item">
                    <span class="icon">📚</span>
                    <div>
                        <h4><a href="<?= $base ?? '' ?>materias/index.php" class="primary">Materias</a></h4>
                        <small>Registrar / ver lista</small>
                    </div>
                </div>
                <div class="item">
                    <span class="icon">📊</span>
                    <div>
                        <h4><a href="<?= $base ?? '' ?>calificaciones/index.php" class="primary">Calificaciones</a></h4>
                        <small>Registrar / historial</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="<?= $base ?? '' ?>assets/js/main.js"></script>
</body>
</html>
