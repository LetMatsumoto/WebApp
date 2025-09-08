<?php include("conexao.php"); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Esqueceu a Senha</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Esqueceu a Senha</h2>
<form method="post">
    Login (email): <input type="text" name="login" required><br>
    Nova senha: <input type="password" name="nova" required><br>
    Confirmar: <input type="password" name="confirma" required><br>
    <button type="submit" name="resetar">Redefinir</button>
</form>

<?php
if (isset($_POST['resetar'])) {
    $login = $_POST['login'];
    $nova = $_POST['nova'];
    $confirma = $_POST['confirma'];

    if ($nova === $confirma) {
        $hash = password_hash($nova, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET senha='$hash' WHERE login='$login'";
        if ($conn->query($sql)) {
            echo "Senha redefinida! Vá para <a href='index.php'>Login</a>";
        } else {
            echo "Erro: " . $conn->error;
        }
    } else {
        echo "As senhas não conferem!";
    }
}
?>
</body>
</html>