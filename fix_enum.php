<?php
require_once 'classes/Database.php';
$db = Database::getInstance();

echo "=== ACTUALIZANDO ENUM ===" . PHP_EOL;
$sql = 'ALTER TABLE inscripciones MODIFY COLUMN estado ENUM("pendiente", "confirmado", "cancelado", "ingresado") DEFAULT "pendiente"';
$db->query($sql);
echo 'ENUM actualizado exitosamente' . PHP_EOL;

echo PHP_EOL . "=== VERIFICANDO NUEVA ESTRUCTURA ===" . PHP_EOL;
$result = $db->query('SHOW COLUMNS FROM inscripciones WHERE Field = "estado"')->fetch();
print_r($result);

echo PHP_EOL . "=== PROBANDO UPDATE CON 'ingresado' ===" . PHP_EOL;
$sql = "UPDATE inscripciones SET estado = 'ingresado' WHERE id = 1";
$result = $db->query($sql);
echo "Filas afectadas: " . $result->rowCount() . PHP_EOL;

$estado = $db->query('SELECT estado FROM inscripciones WHERE id = 1')->fetch();
echo "Estado de ID 1: '{$estado['estado']}'" . PHP_EOL;
?>
