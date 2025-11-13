<?php
require_once 'funcoes.php';
require_once 'conexao.php';

if (!is_logged() || $_SESSION['tipo'] != '0') {
    redirect('pag_principal.php');
}

$erro = '';
$sucesso = '';

// Cadastrar novo usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar'])) {
    $login = trim($_POST['login'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $tipo = $_POST['tipo'] ?? '1';
    $senha_padrao = '123456'; // Senha padrão para novos usuários
    
    if (empty($login) || empty($nome)) {
        $erro = "Preencha todos os campos.";
    } else {
        // Verificar se o login já existe
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $existe = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($existe) {
            $erro = "Este login já está cadastrado.";
        } else {
            $senha_hash = password_hash($senha_padrao, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (login, senha, nome, tipo, qtde_acesso, status) VALUES (?, ?, ?, ?, 0, 'A')");
            $stmt->bind_param("ssss", $login, $senha_hash, $nome, $tipo);
            
            if ($stmt->execute()) {
                $sucesso = "Usuário cadastrado com sucesso! Senha padrão: 123456";
            } else {
                $erro = "Erro ao cadastrar usuário.";
            }
            $stmt->close();
        }
    }
}

// Desbloquear usuário
if (isset($_GET['desbloquear'])) {
    $login = $_GET['desbloquear'];
    $stmt = $conn->prepare("UPDATE usuarios SET status = 'A' WHERE login = ?");
    $stmt->bind_param("s", $login);
    if ($stmt->execute()) {
        $sucesso = "Usuário desbloqueado com sucesso!";
    }
    $stmt->close();
}

// Buscar todos os usuários
$usuarios = $conn->query("SELECT * FROM usuarios ORDER BY nome");
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<div class="header-bar">
    <div class="user-info">Administração de Usuários</div>
    <div class="header-links">
        <a href="pag_principal.php">Voltar</a> |
        <a href="admin_eventos.php">Gerenciar Eventos</a> |
        <a href="logout.php">Sair</a>
    </div>
</div>

<div class="main-container">
    <h2>Cadastrar Novo Usuário</h2>
    
    <?php if ($erro): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    
    <?php if ($sucesso): ?>
        <div class="success"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <form method="post" class="form-inline">
        <input type="text" name="login" placeholder="Login" required>
        <input type="text" name="nome" placeholder="Nome completo" required>
        <select name="tipo" required>
            <option value="1">Usuário Comum</option>
            <option value="0">Administrador</option>
        </select>
        <button type="submit" name="cadastrar">Cadastrar Usuário</button>
    </form>

    <h2 style="margin-top: 40px;">Usuários Cadastrados</h2>
    
    <table class="reservas-table">
        <thead>
            <tr>
                <th>Login</th>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Acessos</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while($user = $usuarios->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($user['login']) ?></td>
                    <td><?= htmlspecialchars($user['nome']) ?></td>
                    <td><?= $user['tipo'] == '0' ? 'Admin' : 'Comum' ?></td>
                    <td><?= $user['qtde_acesso'] ?></td>
                    <td>
                        <?php if ($user['status'] == 'A'): ?>
                            <span class="status-ativo">Ativo</span>
                        <?php else: ?>
                            <span class="status-cancelado">Bloqueado</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user['status'] == 'B'): ?>
                            <a href="?desbloquear=<?= urlencode($user['login']) ?>" class="btn-small">Desbloquear</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
