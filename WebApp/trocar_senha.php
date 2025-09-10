<?php include("conexao.php"); session_start();
if (!isset($_SESSION['login'])) { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Trocar Senha</title>
<link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<h2>Trocar Senha</h2>
<form method="post">
    Nova senha: <input type="password" name="nova" required><br>
    Confirmar senha: <input type="password" name="confirma" required><br>
    <button type="submit" name="trocar">Trocar</button>
</form>

<?php
if (isset($_POST['trocar'])) {
    $nova = $_POST['nova'];
    $confirma = $_POST['confirma'];
    if ($nova === $confirma) {
        $hash = password_hash($nova, PASSWORD_DEFAULT);
        $login = $_SESSION['login'];
        $conn->query("UPDATE usuarios SET senha='$hash' WHERE login='$login'");
        echo "Senha alterada com sucesso! Vá para <a href='home.php'>Home</a>";
    } else {
        echo "Senhas não conferem!";
    }
}
?>
</body>
</html>