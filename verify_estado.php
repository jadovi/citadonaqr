<?php
require_once 'classes/Database.php';
$db = Database::getInstance();

echo "=== VERIFICACIÓN COMPLETA ===" . PHP_EOL;

// Ver inscripción completa
$inscripcion = $db->query('SELECT * FROM inscripciones WHERE id = 5')->fetch();
echo 'Inscripción completa:' . PHP_EOL;
print_r($inscripcion);

// También verificar el registro de acceso
$acceso = $db->query('SELECT * FROM accesos WHERE inscripcion_id = 5 ORDER BY id DESC LIMIT 1')->fetch();
echo PHP_EOL . 'Último acceso registrado:' . PHP_EOL;
print_r($acceso);

// Verificar todas las inscripciones para comparar
echo PHP_EOL . "=== TODAS LAS INSCRIPCIONES ===" . PHP_EOL;
$todas = $db->query('SELECT id, estado FROM inscripciones ORDER BY id')->fetchAll();
foreach ($todas as $ins) {
    echo "ID: {$ins['id']}, Estado: '{$ins['estado']}'" . PHP_EOL;
}
?>
