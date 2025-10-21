<?php
require_once 'conexao.php'; 
require_once 'funcoes.php';


// Guarda o ID do usuário logado na variável $id
$id = $user['id']; // se você pegou o ID antes do login

// Consulta o número de acessos do usuário no banco de dados
$sql = "SELECT quant_acesso FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("id", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Define se o container de redefinição deve ser mostrado
$showContainer = ($user['quant_acesso'] == 0);

// Variáveis para mensagens de feedback
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Captura as senhas digitadas no formulário
    $novaSenha = trim($_POST['nova_senha']);
    $confirmarSenha = trim($_POST['confirmar_senha']);

    // Verifica se as senhas digitadas são iguais
    if ($novaSenha !== $confirmarSenha) {
        $error = "As senhas não coincidem!";
    } else {
        // Gera o hash seguro da nova senha
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

        // Atualiza a senha e marca que o usuário já fez o primeiro acesso
        $sqlUpdate = "UPDATE USUARIOS SET senha = ?, quant_acesso = quant_acesso + 1 WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("si", $senhaHash, $id);

        // Executa a atualização
        if ($stmtUpdate->execute()) {
            $success = "Senha redefinida com sucesso!";
            // Redireciona automaticamente para a página inicial após 2 segundos
            header("refresh:2;url=home.php");
        } else {
            $error = "Erro ao atualizar senha. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="CSS/style.css"> 
</head>

<body>
    <?php if ($showContainer): ?> 
        <!-- Exibe o bloco   lilás apenas se for o primeiro acesso -->
        <div class="cadastro-container">
            <h2>Redefinir Senha</h2>

            <!-- Mensagens de sucesso ou erro -->
            <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>
            <?php if ($error): ?><p class="alert"><?= $error ?></p><?php endif; ?>

            <!-- Formulário de redefinição de senha -->
            <form method="post">
                <div class="input-group">
                    <input type="password" name="nova_senha" placeholder="Nova senha" required>
                </div>
                <div class="input-group">
                    <input type="password" name="confirmar_senha" placeholder="Confirmar nova senha" required>
                </div>
                <div>
                    <button type="submit">Salvar nova senha</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</body>

</html>
