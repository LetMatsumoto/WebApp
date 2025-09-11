<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $login = trim($_POST['login'] ?? ''); // Alterado de 'email' para 'login'
    $senha = $_POST['senha'] ?? '';
    $tipo = $_POST['tipo'] ?? '1';

    if ($nome && $login && $senha) {
        // Verifica se o login (e-mail) já existe
        $stmt = $pdo->prepare("SELECT login FROM usuarios WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $erro = "Login já cadastrado.";
        } else {
            // Criptografa a senha antes de inserir
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Query de INSERT corrigida para a estrutura da tabela
            $stmt = $pdo->prepare("INSERT INTO usuarios (login, senha, nome, tipo, quant_acesso, status, tentativas) VALUES (?, ?, ?, ?, 0, 'A', 0)");
            
            // Executa a inserção com os valores corretos
            $stmt->execute([$login, $hash, $nome, $tipo]);
            
            // Redireciona em caso de sucesso
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
    <input type="email" name="login" placeholder="Email" required> <input type="password" name="senha" placeholder="Senha" required>
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