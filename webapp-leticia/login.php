<?php
require_once 'conexao.php';
require_once 'funcoes.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura e limpa os dados do formulário
    $login = trim($_POST['login'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    // Verifica se os campos foram preenchidos
    if (empty($login) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        // Verifica se o usuário existe
        if ($user) {
            // Verifica se a conta está bloqueada
            if ($user['status'] === 'B') {
                $erro = "Sua conta foi bloqueada. Entre em contato com o administrador.";
            } elseif (password_verify($senha, $user['senha'])) {

                // Salva as informações do usuário na sessão
                $_SESSION['login'] = $user['login'];
                $_SESSION['nome']  = $user['nome'];
                $_SESSION['tipo']  = $user['tipo'];

                // Redireciona conforme o número de acessos
                if ($user['qtde_acesso'] == 0) {
                    redirect('prim_acesso.php');
                } else {
                    // Login bem-sucedido - Atualiza o contador de acessos
                    $stmt = $conn->prepare("UPDATE usuarios SET qtde_acesso = qtde_acesso + 1 WHERE login = ?");
                    $stmt->bind_param("s", $login);
                    $stmt->execute();
                    $stmt->close();
                    redirect('pag_principal.php');
                }
            } else {
                // Senha incorreta - incrementar tentativas
                // Verifica quantas tentativas já foram feitas usando sessão
                if (!isset($_SESSION['tentativas_' . $login])) {
                    $_SESSION['tentativas_' . $login] = 0;
                }
                $_SESSION['tentativas_' . $login]++;

                if ($_SESSION['tentativas_' . $login] >= 3) {
                    // Bloqueia a conta após 3 tentativas
                    $stmt = $conn->prepare("UPDATE usuarios SET status = 'B' WHERE login = ?");
                    $stmt->bind_param("s", $login);
                    $stmt->execute();
                    $stmt->close();
                    $erro = "Você errou a senha 3 vezes. Sua conta foi bloqueada.";
                    unset($_SESSION['tentativas_' . $login]);
                } else {
                    // Não exibir o hash/senha por questões de segurança
                    $tentativas_restantes = 3 - $_SESSION['tentativas_' . $login];
                    $erro = "Usuário ou senha inválidos. Tentativas restantes: $tentativas_restantes";
                }
            }
        } else {
            $erro = "Usuário ou senha inválidos.";
        }
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Login - Sistema de Eventos</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>

<body>
    <div class="container">
        <h2>Sistema de Eventos - Login</h2>

        <?php if (!empty($_GET['success'])): ?>
            <div class="success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div class="alert"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="login" placeholder="Login" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Entrar</button>
            <small style="display: block; text-align: center; margin-top: 15px;">
                <a href="redefinir_senha.php">Trocar senha</a>
            </small>
        </form>
    </div>
</body>

</html>