<?php
require_once 'conexao.php';
require_once 'funcoes.php';

if (!is_logged()) {
    redirect('login.php');
}

$email = $_SESSION['email'];

$stmt = $conn->prepare("SELECT quant_acesso FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['quant_acesso'] > 1) {
    redirect('painel.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova = $_POST['nova'] ?? '';
    $conf = $_POST['conf'] ?? '';
    
    if (empty($nova) || empty($conf)) {
        $erro = "Por favor, preencha ambos os campos.";
    } elseif ($nova !== $conf) {
        $erro = "As senhas não conferem.";
    } else {
        $hash = password_hash($nova, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
        $stmt->bind_param("ss", $hash, $email);
        $stmt->execute();
        $stmt->close();
        
        redirect('painel.php');
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
    <?php if (!empty($erro)): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="password" name="nova" placeholder="Nova senha" required>
        <input type="password" name="conf" placeholder="Confirmar senha" required>
        <button type="submit">Salvar</button>
    </form>
</div>
</body>
</html>
