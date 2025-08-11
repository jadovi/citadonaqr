<?php
require_once 'config/config.php';

try {
    echo "<h2>🔄 Migración de columnas Mesa/Asiento</h2><hr>";
    $db = Database::getInstance();

    // Agregar columnas mesa y asiento si no existen
    $colMesa = $db->fetch("SHOW COLUMNS FROM inscripciones LIKE 'mesa'");
    if (!$colMesa) {
        $db->query("ALTER TABLE inscripciones ADD COLUMN mesa VARCHAR(50) NULL AFTER estado");
        echo "✅ Columna 'mesa' agregada<br>";
    } else {
        echo "✅ Columna 'mesa' ya existe<br>";
    }

    $colAsiento = $db->fetch("SHOW COLUMNS FROM inscripciones LIKE 'asiento'");
    if (!$colAsiento) {
        $db->query("ALTER TABLE inscripciones ADD COLUMN asiento VARCHAR(50) NULL AFTER mesa");
        echo "✅ Columna 'asiento' agregada<br>";
    } else {
        echo "✅ Columna 'asiento' ya existe<br>";
    }

    echo "<br><div style='background:#d4edda;padding:12px;border-radius:6px'>Migración finalizada</div>";
    echo "<p>Puede asignar valores de mesa/asiento desde el admin o importador en futuras versiones.</p>";
} catch (Exception $e) {
    echo "<div style='background:#f8d7da;padding:12px;border-radius:6px'>Error: ".htmlspecialchars($e->getMessage())."</div>";
}

?>
