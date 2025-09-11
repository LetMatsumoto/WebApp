<?php
require_once 'conexao.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $stmt = $pdo->prepare("SELECT email FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['reset_email'] = $email;
        header('Location: reset_senha.php');
        exit;
    } else {
        $erro = "Email não encontrado.";
    }
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
  <?php if(!empty($erro)): ?><div class="alert"><?=$erro?></div><?php endif; ?>
  <form method="post">
    <input type="email" name="email" placeholder="Digite seu email" required>
    <button type="submit">Continuar</button>
  </form>
  <small><a href="login.php">Voltar ao login</a></small>
</div>
</body>
</html>
