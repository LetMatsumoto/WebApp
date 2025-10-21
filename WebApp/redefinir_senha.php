<?php
require_once 'conexao.php';
require_once 'funcoes.php';

session_start();

// Verifica se o usuário está logado. Se não houver e-mail na sessão, ele será redirecionado para o login
// Isso evita que alguém acesse a página sem estar autenticado
if (empty($_SESSION['email'])) {
    redirect('login.php');
    exit;
}

// Armazena o e-mail do usuário logado para uso posterior
$email = $_SESSION['email'];

// Inicializa variáveis para exibir mensagens na tela
$erro = '';
$sucesso = '';

// Quando o formulário for enviado (método POST), o código abaixo é executado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtém os valores digitados nos campos de senha
    $senha_nova = $_POST['senha_nova'] ?? '';
    $senha_confirmar = $_POST['senha_confirmar'] ?? '';

    // Validações básicas antes de alterar a senha
    if (empty($senha_nova) || empty($senha_confirmar)) {
        // Verifica se ambos os campos foram preenchidos
        $erro = "Preencha todos os campos.";
    } elseif ($senha_nova !== $senha_confirmar) {
        // Verifica se as duas senhas são iguais
        $erro = "As senhas não coincidem.";
    } elseif (strlen($senha_nova) < 6) {
        // Garante um tamanho mínimo de senha
        $erro = "A nova senha deve ter pelo menos 6 caracteres.";
    } else {
        // Caso tudo esteja certo, a senha é criptografada
        $hash = password_hash($senha_nova, PASSWORD_DEFAULT);
        // Atualiza a senha e marca o usuário como tendo feito o 1º acesso
        // Aqui também atualizamos o campo quant_acesso = 1
        // para indicar que o usuário já redefiniu a senha inicial
        $stmt = $conn->prepare("UPDATE usuarios SET senha = ?, quant_acesso = 1 WHERE email = ?");
        $stmt->bind_param("ss", $hash, $email);
        
        // Se a atualização for bem-sucedida, mostra mensagem e redireciona
        if ($stmt->execute()) {
            $sucesso = "Senha redefinida com sucesso! Você será redirecionado...";
            
            // Redireciona automaticamente para a página principal após 2 segundos
            header("refresh:2;url=pag_principal.php");
        } else {
            // Caso ocorra algum erro na execução da query (consulta)
            $erro = "Erro ao redefinir senha. Tente novamente.";
        }

        // Fecha o statement para liberar o recurso
        $stmt->close();
    }
}
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="CSS/style.css"
</head>
<body>
<div class="container">
    <h2>Redefinir Senha</h2>

    <!-- Exibe mensagens de erro -->
    <?php if ($erro): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <!-- Exibe mensagem de sucesso -->
    <?php if ($sucesso): ?>
        <div class="success"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <!-- Formulário para redefinir senha -->
    <form method="post">
        <input type="password" name="senha_nova" placeholder="Nova Senha" required>
        <input type="password" name="senha_confirmar" placeholder="Confirmar Nova Senha" required>
        <button type="submit">Salvar Nova Senha</button>
    </form>
</div>
</body>
</html>
