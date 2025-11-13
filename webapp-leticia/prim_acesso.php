<?php
require_once 'conexao.php'; 
require_once 'funcoes.php';

if (!is_logged()) {
    redirect('login.php');
}

$login = $_SESSION['login'];
$erro = "";
$sucesso = "";

// Consulta o número de acessos do usuário no banco de dados (usa login da sessão)
$sql = "SELECT qtde_acesso FROM usuarios WHERE login = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Define se o container de redefinição deve ser mostrado
$showContainer = ($user['qtde_acesso'] == 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $novaSenha = trim($_POST['nova_senha'] ?? '');
    $confirmarSenha = trim($_POST['confirmar_senha'] ?? '');

    if (empty($novaSenha) || empty($confirmarSenha)) {
        $erro = "Preencha todos os campos.";
    } elseif ($novaSenha !== $confirmarSenha) {
        $erro = "As senhas não coincidem!";
    } elseif (strlen($novaSenha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
    $stmtUpdate = $conn->prepare("UPDATE usuarios SET senha = ?, qtde_acesso = qtde_acesso + 1 WHERE login = ?");
    $stmtUpdate->bind_param("ss", $senhaHash, $login);

        if ($stmtUpdate->execute()) {
            $sucesso = "Senha definida com sucesso! Redirecionando...";
            header("refresh:2;url=pag_principal.php");
        } else {
            $erro = "Erro ao atualizar senha. Tente novamente.";
        }
        $stmtUpdate->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Primeiro Acesso - Definir Senha</title>
    <link rel="stylesheet" href="CSS/style.css"> 
</head>
<body>
    <?php if ($showContainer): ?> 
        <div class="container">
            <h2>Primeiro Acesso - Defina sua Senha</h2>
            <p>Olá, <?= htmlspecialchars($_SESSION['nome']) ?>! Por favor, defina uma nova senha.</p>

            <?php if ($sucesso): ?>
                <div class="success"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>
            
            <?php if ($erro): ?>
                <div class="alert"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="password" name="nova_senha" placeholder="Nova senha" required>
                <input type="password" name="confirmar_senha" placeholder="Confirmar nova senha" required>
                <button type="submit">Salvar nova senha</button>
            </form>
        </div>
    <?php else: ?>
        <div class="container">
            <h2>Acesso Negado</h2>
            <p>Você já definiu sua senha anteriormente. <a href="pag_principal.php">Clique aqui</a> para ir à página principal.</p>
        </div> 
    <?php endif; ?>
</body>
</html>
