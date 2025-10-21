<?php
require_once 'conexao.php';
require_once 'funcoes.php';

// Inicia a sessão para poder usar variáveis globais
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura e limpa os dados do formulário
    $login = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    // Verifica se os campos foram preenchidos
    if (empty($login) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        // Prepara uma consulta segura (impede SQL Injection)
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        // Verifica se o usuário existe, está ativo e se a senha confere
        if ($user && $user['status'] === 'A' && password_verify($senha, $user['senha'])) {

            // Atualiza o contador de acessos no banco de dados
            $stmt = $conn->prepare("UPDATE usuarios SET quant_acesso = quant_acesso + 1 WHERE login = ?");
            $stmt->bind_param("s", $login);
            $stmt->execute();
            $stmt->close();
            
            // Salva as informações do usuário na sessão
            $_SESSION['id']    = $user['id'];      // ID do usuário
            $_SESSION['email'] = $user['email'];   // Email
            $_SESSION['nome']  = $user['nome'];    // Nome
            $_SESSION['tipo']  = $user['tipo'];    // Tipo (admin, comum etc.)

            // Redireciona conforme o número de acessos
            // Se for o primeiro acesso, vai para a página de redefinir senha
            if ($user['quant_acesso'] == 0) {
                redirect('1°_acesso.php');
            } else {
                redirect('pag_principal.php');
            }
        } else {
            // Caso o login ou senha estejam incorretos
            $erro = "Usuário ou senha inválidos.";
        }
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

    <!-- Mensagem de sucesso (vinda por GET, se houver) -->
    <?php if (!empty($_GET['success'])): ?>
        <div class="success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <!-- Mensagem de erro (validação ou login incorreto) -->
    <?php if (!empty($erro)): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <!-- Formulário de login -->
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
