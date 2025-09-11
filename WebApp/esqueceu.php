<?php
require_once 'conexao.php';
require_once 'funcoes.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['email'] ?? '');
    
    $stmt = $conn->prepare("SELECT email FROM usuarios WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['reset_email'] = $login;
        redirect('reset_senha.php');
    } else {
        $erro = "Email não encontrado.";
    }
    $stmt->close();
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Esqueci a senha</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<div class="container">
    <h2>Esqueci a senha</h2>
    <?php if (!empty($erro)): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Digite seu email" required>
        <button type="submit">Continuar</button>
    </form>
    <small><a href="login.php">Voltar ao login</a></small>
</div>
</body>
</html>
