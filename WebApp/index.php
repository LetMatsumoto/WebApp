<?php include_once("conexao.php"); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<h2>Login</h2>
<form method="post">
    Login: <input type="text" name="login" required><br>
    Senha: <input type="password" name="senha" required><br>
    <button type="submit" name="entrar">Entrar</button>
    <a href="cadastro.php">Cadastrar</a> |
     <a href="esqueceu.php">Esqueceu a senha?</a>
</form>


<?php
if (isset($_POST['entrar'])) {
    $login = $_POST['login'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuarios WHERE login='$login' AND status='A'";
    $res = $conn->query($sql);

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();

        if (password_verify($senha, $user['senha'])) {
            $_SESSION['login'] = $user['login'];
            
            // Contar acessos
            $acessos = $user['quant_acesso'] + 1;
            $conn->query("UPDATE usuarios SET quant_acesso=$acessos WHERE login='$login'");

            // Se a senha for a padrão, redirecionar para a pagina trocar de senha
            if (password_verify("123456", $user['senha'])) {
                header("Location: trocar.php");
            } else {
                header("Location: home.php");
            }
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usuário não encontrado ou inativo!";
    }
}
?>
</body>
</html>