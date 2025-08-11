<?php
require_once 'config/config.php';

try {
    echo "<h2>🔄 Migración columnas Lugar/Zona en inscripciones</h2><hr>";
    $db = Database::getInstance();

    $colLugar = $db->fetch("SHOW COLUMNS FROM inscripciones LIKE 'lugar'");
    if (!$colLugar) {
        $db->query("ALTER TABLE inscripciones ADD COLUMN lugar VARCHAR(100) NULL AFTER asiento");
        echo "✅ Columna 'lugar' agregada<br>";
    } else {
        echo "✅ Columna 'lugar' ya existe<br>";
    }

    $colZona = $db->fetch("SHOW COLUMNS FROM inscripciones LIKE 'zona'");
    if (!$colZona) {
        $db->query("ALTER TABLE inscripciones ADD COLUMN zona VARCHAR(100) NULL AFTER lugar");
        echo "✅ Columna 'zona' agregada<br>";
    } else {
        echo "✅ Columna 'zona' ya existe<br>";
    }

    echo "<br><div style='background:#d4edda;padding:12px;border-radius:6px'>Migración finalizada</div>";
} catch (Exception $e) {
    echo "<div style='background:#f8d7da;padding:12px;border-radius:6px'>Error: ".htmlspecialchars($e->getMessage())."</div>";
}

?>
