<?php
require_once 'classes/Database.php';

echo "Estructura de la tabla inscripciones:\n";
$db = Database::getInstance();
$result = $db->query('DESCRIBE inscripciones')->fetchAll();

foreach($result as $row) {
    echo $row['Field'] . ' (' . $row['Type'] . ")\n";
}
?>
