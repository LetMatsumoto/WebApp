<?php
require_once 'funcoes.php';
require_once 'conexao.php';

if (!is_logged() || $_SESSION['tipo'] != '0') {
    redirect('pag_principal.php');
}

$erro = '';
$sucesso = '';

// Criar diretório de uploads se não existir
$upload_dir = 'uploads/eventos/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Cadastrar novo evento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar'])) {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $local = trim($_POST['local'] ?? '');
    $data = $_POST['data'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $capacidade = intval($_POST['capacidade'] ?? 0);
    
    $coords_validas = false;
    if (!empty($local)) {
        $coords = explode(',', $local);
        if (count($coords) == 2) {
            $lat = floatval(trim($coords[0]));
            $lng = floatval(trim($coords[1]));
            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                $coords_validas = true;
                $local = $lat . ',' . $lng; // normalizar formato
            }
        }
    }
    
    if (empty($nome) || empty($descricao) || empty($local) || empty($data) || empty($hora) || $capacidade <= 0) {
        $erro = "Preencha todos os campos corretamente.";
    } elseif (!$coords_validas) {
        $erro = "Coordenadas inválidas. Use o formato: latitude,longitude (ex: -23.5505,-46.6333)";
    } else {
        $imagem_path = null;
        
        // Upload da imagem
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['imagem']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $imagem_path = $upload_dir . $new_filename;
                move_uploaded_file($_FILES['imagem']['tmp_name'], $imagem_path);
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO eventos (nome, descricao, local, data, hora, capacidade, imagem) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssis", $nome, $descricao, $local, $data, $hora, $capacidade, $imagem_path);
        
        if ($stmt->execute()) {
            $sucesso = "Evento cadastrado com sucesso!";
        } else {
            $erro = "Erro ao cadastrar evento.";
        }
        $stmt->close();
    }
}

// Deletar evento
if (isset($_GET['deletar'])) {
    $id = intval($_GET['deletar']);
    
    // Buscar imagem para deletar
    $stmt = $conn->prepare("SELECT imagem FROM eventos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $evento = $result->fetch_assoc();
    $stmt->close();
    
    if ($evento && !empty($evento['imagem']) && file_exists($evento['imagem'])) {
        unlink($evento['imagem']);
    }
    
    $stmt = $conn->prepare("DELETE FROM eventos WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $sucesso = "Evento deletado com sucesso!";
    }
    $stmt->close();
}

// Buscar todos os eventos
$eventos = $conn->query("SELECT e.*, 
                         (SELECT COUNT(*) FROM reservas WHERE id_evento = e.id AND status = 'A') as reservas_ativas
                         FROM eventos e ORDER BY e.data DESC");
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gerenciar Eventos</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<div class="header-bar">
    <div class="user-info">Administração de Eventos</div>
    <div class="header-links">
        <a href="pag_principal.php">Voltar</a> |
        <a href="admin_usuarios.php">Gerenciar Usuários</a> |
        <a href="logout.php">Sair</a>
    </div>
</div>

<div class="main-container">
    <h2>Cadastrar Novo Evento</h2>
    
    <?php if ($erro): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    
    <?php if ($sucesso): ?>
        <div class="success"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="form-evento">
        <input type="text" name="nome" placeholder="Nome do evento" required>
        <textarea name="descricao" placeholder="Descrição do evento" rows="4" required></textarea>
        <input type="text" name="local" placeholder="Coordenadas do local (ex: -23.5505,-46.6333)" required 
               pattern="^-?([0-8]?[0-9]|90)(\.[0-9]+)?\s*,\s*-?(1[0-7][0-9]|[0-9]?[0-9])(\.[0-9]+)?$"
               title="Formato: latitude,longitude (ex: -23.5505,-46.6333)">
        <input type="date" name="data" required>
        <input type="time" name="hora" required>
        <input type="number" name="capacidade" placeholder="Capacidade" min="1" required>
        <input type="file" name="imagem" accept="image/*">
        <button type="submit" name="cadastrar">Cadastrar Evento</button>
    </form>

    <h2 style="margin-top: 40px;">Eventos Cadastrados</h2>
    
    <table class="reservas-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Local</th>
                <th>Data/Hora</th>
                <th>Capacidade</th>
                <th>Reservas</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while($evento = $eventos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($evento['nome']) ?></td>
                    <td><?= htmlspecialchars($evento['local']) ?></td>
                    <td><?= date('d/m/Y', strtotime($evento['data'])) ?> às <?= date('H:i', strtotime($evento['hora'])) ?></td>
                    <td><?= $evento['capacidade'] ?></td>
                    <td><?= $evento['reservas_ativas'] ?> / <?= $evento['capacidade'] ?></td>
                    <td>
                        <a href="?deletar=<?= $evento['id'] ?>" 
                           onclick="return confirm('Deseja realmente deletar este evento?')" 
                           class="btn-cancelar">Deletar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
