<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Cambia si tu usuario es diferente
define('DB_PASS', 'root');        // Contraseña por defecto de MAMP
define('DB_NAME', 'academia_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:20px;background:#fee;border:1px solid red;margin:20px;border-radius:8px;">
        <strong>Error de conexión:</strong> ' . $conn->connect_error . '<br><br>
        Verifica que MySQL esté corriendo y que los datos en <code>config/db.php</code> sean correctos.
    </div>');
}
