<?php
require_once 'conexao.php';
require_once 'funcoes.php';

if (empty($_SESSION['resert_email'])) {
    redirect('esqueceu_senha.php');
}

$email = $_SESSION['resert_email'];
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s1 = $_POST['senha1'] ?? '';
    $s2 = $_POST['senha2'] ?? '';

    if (empty($s1) || empty($s2)) {
        $erro = "Por favor, preencha ambos os campos de senha.";
    } elseif ($s1 !== $s2) {
        $erro = "As senhas não conferem.";
    } else {
        $hash = password_hash($s1, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET senha = ?, tentativas = 0, status = 'A' WHERE login = ?");
        $stmt->bind_param("ss", $hash, $login);
        $stmt->execute();
        $stmt->close();
        
        unset($_SESSION['resert_email']);
        redirect('login.php?success=Senha redefinida com sucesso!');
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resetar senha</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<div class="container">
    <h2>Nova senha</h2>
    <p>Email: <?= htmlspecialchars($email) ?></p>
    <?php if (!empty($erro)): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="password" name="senha1" placeholder="Nova senha" required>
        <input type="password" name="senha2" placeholder="Confirmar senha" required>
        <button type="submit">Atualizar</button>
    </form>
</div>
</body>
</html>