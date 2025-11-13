<?php
require_once 'conexao.php';
require_once 'funcoes.php';


$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $senha_atual = $_POST['senha_atual'] ?? '';
    $senha_nova = $_POST['senha_nova'] ?? '';
    $senha_confirmar = $_POST['senha_confirmar'] ?? '';

    if (empty($login) || empty($senha_atual) || empty($senha_nova) || empty($senha_confirmar)) {
        $erro = "Preencha todos os campos.";
    } elseif ($senha_nova !== $senha_confirmar) {
        $erro = "A nova senha e a confirmação não coincidem.";
    } elseif (strlen($senha_nova) < 6) {
        $erro = "A nova senha deve ter pelo menos 6 caracteres.";
    } else {
        // Busca o usuário e verifica a senha atual
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($senha_atual, $user['senha'])) {
            // Senha atual está correta, atualiza para a nova
            $hash = password_hash($senha_nova, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE login = ?");
            $stmt->bind_param("ss", $hash, $login);
            
            if ($stmt->execute()) {
                $sucesso = "Senha alterada com sucesso!";
                // Redireciona para o login após 2 segundos
                header("refresh:2;url=login.php?success=" . urlencode($sucesso));
            } else {
                $erro = "Erro ao alterar senha. Tente novamente.";
            }
            $stmt->close();
        } else {
            $erro = "Login ou senha atual incorretos.";
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trocar Senha</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<div class="container">
    <h2>Trocar Senha</h2>

    <?php if ($erro): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <?php if ($sucesso): ?>
        <div class="success"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="login" placeholder="Login" required>
        <input type="password" name="senha_atual" placeholder="Senha Atual" required>
        <input type="password" name="senha_nova" placeholder="Nova Senha" required>
        <input type="password" name="senha_confirmar" placeholder="Confirmar Nova Senha" required>
        <button type="submit">Alterar Senha</button>
        <small style="display: block; text-align: center; margin-top: 15px;">
            <a href="login.php">Voltar para o login</a>
        </small>
    </form>
</div>
</body>
</html>
