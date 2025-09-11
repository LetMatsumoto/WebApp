<?php
require_once 'conexao.php';
session_start();

if (empty($_SESSION['reset_email'])) {
    header('Location: esqueci_senha.php');
    exit;
}
$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s1 = $_POST['senha1'] ?? '';
    $s2 = $_POST['senha2'] ?? '';
    if ($s1 && $s2 && $s1 === $s2) {
        $hash = password_hash($s1, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE usuarios SET senha=?, tentativas=0, status='A' WHERE email=?")
            ->execute([$hash, $email]);
        unset($_SESSION['reset_email']);
        header('Location: login.php?success=Senha redefinida');
        exit;
    } else {
        $erro = "Senhas não conferem.";
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
  <p>Email: <?=$email?></p>
  <?php if(!empty($erro)): ?><div class="alert"><?=$erro?></div><?php endif; ?>
  <form method="post">
    <input type="password" name="senha1" placeholder="Nova senha" required>
    <input type="password" name="senha2" placeholder="Confirmar senha" required>
    <button type="submit">Atualizar</button>
  </form>
</div>
</body>
</html>
