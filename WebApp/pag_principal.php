<?php
require_once 'funcoes.php';

if (!is_logged()) {
    redirect('login.php');
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Página principal</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<div class="container">
    <h2>Login feito com sucesso</h2>
    <p>Bem-vindo, <?= htmlspecialchars($_SESSION['nome']) ?>!</p>
</div>
</body>
</html>
