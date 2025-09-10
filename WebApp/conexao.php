<?php
// includes/conexao.php
$host = '127.0.0.1';
$db   = 'sistema_app';
$user = 'root';
$pass = ''; // ajuste conforme seu ambiente
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    die('Erro na conexão: ' . $e->getMessage());
}
