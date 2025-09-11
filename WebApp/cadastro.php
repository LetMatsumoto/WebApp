<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $tipo = $_POST['tipo'] ?? '1';

    if ($nome && $email && $senha) {
        $stmt = $pdo->prepare("SELECT email FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erro = "Email já cadastrado.";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (email, senha, nome, tipo, quant_acesso, status, tentativas) VALUES (?, ?, ?, ?, 0, 'A', 0)");
            $stmt->execute([$email, $hash, $nome, $tipo]);
            header('Location: login.php?success=Cadastro realizado');
            exit;
        }
    } else {
        $erro = "Preencha todos os campos.";
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
  <?php if(!empty($erro)): ?><div class="alert"><?=$erro?></div><?php endif; ?>
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
