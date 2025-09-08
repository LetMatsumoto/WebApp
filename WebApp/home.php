<?php session_start();
if (!isset($_SESSION['login'])) { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Home</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Bem-vindo, <?php echo $_SESSION['login']; ?>!</h2>
<a href="trocar.php">Trocar Senha</a> | <a href="index.php">Sair</a>
</body>
</html>