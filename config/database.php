<?php
/**
 * Configuración de base de datos
 */

return [
    'host' => 'localhost',
    'dbname' => 'evento_acreditacion',
    'username' => 'root',
    'password' => '123123',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
