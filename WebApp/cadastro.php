<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
    <h2>Cadastro</h2>
    <form method="post">
        Login: <input type="text" name="login" required><br>
        Senha: <input type="password" name="senha" required><br>
        Nome: <input type="text" name="nome" required><br>
        Tipo: 
        <select name="tipo">
            <option value="1">Usuário</option>
            <option value="0">Administrador</option>
        </select><br>
        <button type="submit" name="cadastrar">Cadastrar</button>
    </form>

<?php

    include_once('conexao.php')
// quando clicar no botão cadastrar
if (isset($_POST['cadastrar'])) {
    $login = $_POST['login'];
    $senha = $_POST['senha'];
    $nome  = $_POST['nome'];
    $tipo  = $_POST['tipo'];

    // senha padrão "123456"
    $senha = password_hash("123456", PASSWORD_DEFAULT);
    $status = "A"; // ativo

    // insere o usuário no banco
    $sql = "INSERT INTO usuarios (login, senha, nome, tipo, quant_acesso, status)
            VALUES ('$login', '$senha', '$nome', '$tipo', 0, '$status')";

    if ($conn->query($sql) === TRUE) {
        // Cadastro feito, agora manda para login
        header("Location: index.php");
        exit; 
    } else {
        echo "Erro: " . $conn->error;
    }
}
?>
</body>
</html>