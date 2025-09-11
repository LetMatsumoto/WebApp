<?php
require_once 'conexao.php';
require_once 'funcoes.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $user['status'] === 'A' && password_verify($senha, $user['senha'])) {
        // reset tentativas, contar acesso
        $pdo->prepare("UPDATE usuarios SET tentativas=0, quant_acesso=quant_acesso+1 WHERE email=?")->execute([$email]);

        $_SESSION['email'] = $user['email'];
        $_SESSION['nome']  = $user['nome'];
        $_SESSION['tipo']  = $user['tipo'];

        if ($user['quant_acesso'] == 0) {
            redirect('primeiro_acesso.php');
        } else {
            redirect('painel.php');
        }
    } else {
        if ($user) {
            $tentativas = $user['tentativas'] + 1;
            $status = $user['status'];
            if ($tentativas >= 3) $status = 'B';
            $pdo->prepare("UPDATE usuarios SET tentativas=?, status=? WHERE email=?")
                ->execute([$tentativas, $status, $email]);
        }
        $erro = "Usuário ou senha inválidos.";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<div class="container">
  <h2>Login</h2>
  <?php if(!empty($_GET['success'])): ?><div class="success"><?=htmlspecialchars($_GET['success'])?></div><?php endif; ?>
  <?php if(!empty($erro)): ?><div class="alert"><?=$erro?></div><?php endif; ?>
  <form method="post">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="senha" placeholder="Senha" required>
    <button type="submit">Entrar</button>
  </form>
  <small><a href="cadastro.php">Cadastrar</a> • <a href="esqueci_senha.php">Esqueci a senha</a></small>
</div>
</body>
</html>
