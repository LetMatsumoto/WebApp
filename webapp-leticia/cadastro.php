<?php
require_once 'conexao.php';
session_start();

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST['nome']);
    $login = trim($_POST['login']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $tipo = $_POST['tipo']; // '0' = Admin, '1' = Normal
    $qtde_acesso = 0;
    $status = '0';

    // Verifica se login/email já existe
    $sqlCheck = "SELECT * FROM USUARIOS WHERE login=?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("s", $login);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        $error = "Este e-mail já está cadastrado!";
    } else {
        // Insere usuário
        $sqlInsert = "INSERT INTO USUARIOS (login, senha, nome, tipo, qtde_acesso, status)
                          VALUES (?, ?, ?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            // tipos: login(s), senha(s), nome(s), tipo(s), qtde_acesso(i), status(s)
            $stmtInsert->bind_param("ssssis", $login, $senha, $nome, $tipo, $qtde_acesso, $status);

        if ($stmtInsert->execute()) {
            $success = "Usuário cadastrado com sucesso!";
        } else {
            $error = "Erro ao cadastrar: " . $stmtInsert->error;
        }
    }
}


?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>

<body>
    <div class="cadastro-container">
        <h2>Cadastro de Usuário</h2>

        <?php if ($success != ""): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if ($error != ""): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form id="cadastroForm" method="post">
            <div class="input-group">
                <input type="text" id="nome" name="nome" placeholder="Digite seu nome" required>
            </div>

            <div class="input-group">
                <input type="email" id="login" name="login" placeholder="Digite seu e-mail/login" required>
            </div>

            <div class="input-group">
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>

            <div class="input-group" >
                <select id="tipo" name="tipo" required>
                    <option value="1">Normal</option>
                    <option value="0">Administrador</option>
                </select>
            </div>
            <div>
                <button type="submit">Cadastrar</button>

            </div>
    </div>
    </form>
    <a href="login.php">
        <p>Já tem conta? Ir para o login</p>
    </a>
    </div>

</body>

</html>
