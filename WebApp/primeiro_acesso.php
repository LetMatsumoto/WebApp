<?php
require_once 'conexao.php';
require_once 'funcoes.php';

if (!is_logged()) redirect('login.php');
$email = $_SESSION['email'];

$stmt = $pdo->prepare("SELECT quant_acesso FROM usuarios WHERE email=?");
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user || $user['quant_acesso'] > 1) redirect('painel.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $n = $_POST['nova'] ?? '';
    $c = $_POST['conf'] ?? '';
    if ($n && $c && $n === $c) {
        $hash = password_hash($n, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE usuarios SET senha=? WHERE email=?")->execute([$hash, $email]);
        redirect('painel.php');
    } else {
        $erro = "Senhas não conferem.";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Primeiro acesso</title>
  <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<div class="container">
  <h2>Troque sua senha (Primeiro acesso)</h2>
  <?php if(!empty($erro)): ?><div class="alert"><?=$erro?></div><?php endif; ?>
  <form method="post">
    <input type="password" name="nova" placeholder="Nova senha" required>
    <input type="password" name="conf" placeholder="Confirmar senha" required>
    <button type="submit">Salvar</button>
  </form>
</div>
</body>
</html>
