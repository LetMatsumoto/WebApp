<?php
require_once 'conexao.php';
require_once 'funcoes.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($login) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        // Usa prepared statement para prevenir injeção de SQL
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && $user['status'] === 'A' && password_verify($senha, $user['senha'])) {
            // Acesso bem-sucedido
            // Atualiza tentativas para 0 e incrementa a quantidade de acessos
            $stmt = $conn->prepare("UPDATE usuarios SET quant_acesso = quant_acesso + 1 WHERE login = ?");
            $stmt->bind_param("s", $login);
            $stmt->execute();
            $stmt->close();
            
            // Define as variáveis de sessão
            $_SESSION['email'] = $user['email'];
            $_SESSION['nome']  = $user['nome'];
            $_SESSION['tipo']  = $user['tipo'];
            
            // Redireciona com base na quantidade de acessos
            if ($user['quant_acesso'] == 0) {
                redirect('1°_acesso.php');
            } else {
                redirect('pag_principal.php');
            }
        }          
                $stmt = $conn->prepare("UPDATE usuarios SET status = ? WHERE login = ?");
                $stmt->bind_param("ss", $status, $email);
                $stmt->execute();
                $stmt->close();
    

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
    <?php if (!empty($_GET['success'])): ?>
        <div class="success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (!empty($erro)): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Entrar</button>
        <small>
            <a href="cadastro.php">Não tem conta? Clique aqui</a> 
        </small>
    </form>
</div>
</body>
</html>