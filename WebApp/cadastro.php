<?php
require_once 'conexao.php';
require_once 'funcoes.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $tipo  = $_POST['tipo'] ?? '1';

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {
        // Verifica se o e-mail já existe
        $stmt = $conn->prepare("SELECT email FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $erro = "Email já cadastrado.";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO usuarios (email, senha, nome, tipo, quant_acesso, status, tentativas) VALUES (?, ?, ?, ?, 0, 'A', 0)");
            $stmt->bind_param("sssi", $email, $hash, $nome, $tipo);
            
            if ($stmt->execute()) {
                redirect('login.php?success=Cadastro realizado com sucesso!');
            } else {
                $erro = "Erro ao cadastrar. Tente novamente.";
            }
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cadastro</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<div class="container">
    <h2>Cadastro</h2>
    <?php if (!empty($erro)): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <select name="tipo">
            <option value="1">Usuário comum</option>
            <option value="0">Administrador</option>
        </select>
        <button type="submit">Cadastrar</button>
    </form>
    <small><a href="login.php">Já tem conta? Login</a></small>
</div>
</body>
</html>