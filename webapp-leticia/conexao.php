<?php
$host = "localhost";
$user = "root"; // seu usuário do MySQL
$pass = "";     // sua senha do MySQL (se tiver)
$db   = "bd_crud_php";

$conn = new mysqli($host, $user, $pass, $db);

// Verifica a conexão
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Define o conjunto de caracteres para evitar problemas de acentuação
$conn->set_charset("utf8");
?>
