<?php
require_once 'classes/Database.php';
$db = Database::getInstance();

echo "=== ESTRUCTURA COLUMNA ESTADO ===" . PHP_EOL;
$result = $db->query('SHOW COLUMNS FROM inscripciones WHERE Field = "estado"')->fetch();
print_r($result);

echo PHP_EOL . "=== INTENTAR UPDATE MANUAL ===" . PHP_EOL;
$sql = "UPDATE inscripciones SET estado = 'ingresado' WHERE id = 1";
$result = $db->query($sql);
echo "Filas afectadas: " . $result->rowCount() . PHP_EOL;

echo PHP_EOL . "=== VERIFICAR DESPUÉS DEL UPDATE ===" . PHP_EOL;
$estado = $db->query('SELECT estado FROM inscripciones WHERE id = 1')->fetch();
echo "Estado de ID 1: '{$estado['estado']}'" . PHP_EOL;
?>
