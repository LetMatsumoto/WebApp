<?php
// esqueci_senha/reset_senha.php
require_once __DIR__ . '/../includes/funcoes.php';
session_start();
if (empty($_SESSION['reset_email'])) {
    header('Location: index.php?erro=' . urlencode('Inicie o procedimento novamente'));
    exit;
}
$email = $_SESSION['reset_email'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Redefinir Senha</title>
  <link rel="stylesheet" href="/CSS/estilo.css">
</head>
<body>
  <div class="container">
    <h2>Nova senha para <?=htmlspecialchars($email)?></h2>
    <?php if(!empty($_GET['erro'])): ?>
      <div class="alert"><?=htmlspecialchars($_GET['erro'])?></div>
    <?php endif; ?>
    <form action="processa_reset.php" method="post">
      <input type="hidden" name="email" value="<?=htmlspecialchars($email)?>">
      <label>Nova senha</label>
      <input type="password" name="senha1" required>
      <label>Confirmar nova senha</label>
      <input type="password" name="senha2" required>
      <button type="submit">Atualizar senha</button>
    </form>
  </div>
</body>
</html>
