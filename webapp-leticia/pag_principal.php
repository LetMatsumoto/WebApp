<?php
require_once 'funcoes.php';
require_once 'conexao.php';

if (!is_logged()) {
    redirect('login.php');
}

$login = $_SESSION['login'];
$nome = $_SESSION['nome'];
$tipo = $_SESSION['tipo'];

// Buscar todos os eventos disponíveis
$sql = "SELECT e.*, 
        (SELECT COUNT(*) FROM reservas WHERE id_evento = e.id AND status = 'A') as reservas_ativas
        FROM eventos e 
        ORDER BY e.data ASC, e.hora ASC";
$result = $conn->query($sql);

// Buscar reservas do usuário
$stmt = $conn->prepare("SELECT r.*, e.nome as nome_evento, e.data, e.hora 
                        FROM reservas r 
                        JOIN eventos e ON r.id_evento = e.id 
                        WHERE r.login_usuario = ? 
                        ORDER BY e.data ASC");
$stmt->bind_param("s", $login);
$stmt->execute();
$minhas_reservas = $stmt->get_result();
$stmt->close();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sistema de Eventos</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<div class="header-bar">
    <div class="user-info">
        Bem-vindo, <?= htmlspecialchars($nome) ?>!
        <?php if ($tipo == '0'): ?>
            <span class="badge-admin">Administrador</span>
        <?php endif; ?>
    </div>
    <div class="header-links">
        <?php if ($tipo == '0'): ?>
            <a href="admin_eventos.php">Gerenciar Eventos</a> |
            <a href="admin_usuarios.php">Gerenciar Usuários</a> |
        <?php endif; ?>
        <a href="logout.php">Sair</a>
    </div>
</div>

<div class="main-container">
    <h2>Eventos Disponíveis</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['erro'])): ?>
        <div class="alert"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <div class="eventos-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while($evento = $result->fetch_assoc()): ?>
                <?php
                $vagas_disponiveis = $evento['capacidade'] - $evento['reservas_ativas'];
                $data_formatada = date('d/m/Y', strtotime($evento['data']));
                $hora_formatada = date('H:i', strtotime($evento['hora']));
                ?>
                <div class="evento-card">
                    <?php if (!empty($evento['imagem']) && file_exists($evento['imagem'])): ?>
                        <img src="<?= htmlspecialchars($evento['imagem']) ?>" alt="<?= htmlspecialchars($evento['nome']) ?>">
                    <?php else: ?>
                        <div class="evento-placeholder">
                            <span>📅</span>
                        </div>
                    <?php endif; ?>
                    <div class="evento-content">
                        <h3><?= htmlspecialchars($evento['nome']) ?></h3>
                        <p class="evento-desc"><?= htmlspecialchars($evento['descricao']) ?></p>
                        <p class="evento-info">📍 <?= htmlspecialchars($evento['local']) ?></p>
                        <p class="evento-info">📅 <?= $data_formatada ?> às <?= $hora_formatada ?></p>
                        <p class="evento-info">👥 <?= $vagas_disponiveis ?> vagas disponíveis</p>
                        
                        <?php if ($vagas_disponiveis > 0): ?>
                            <a href="reservar.php?id=<?= $evento['id'] ?>" class="btn-reservar">Fazer Reserva</a>
                        <?php else: ?>
                            <button class="btn-lotado" disabled>Lotado</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhum evento cadastrado no momento.</p>
        <?php endif; ?>
    </div>

    <h2 style="margin-top: 40px;">Minhas Reservas</h2>
    
    <?php if ($minhas_reservas->num_rows > 0): ?>
        <table class="reservas-table">
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while($reserva = $minhas_reservas->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($reserva['nome_evento']) ?></td>
                        <td><?= date('d/m/Y', strtotime($reserva['data'])) ?></td>
                        <td><?= date('H:i', strtotime($reserva['hora'])) ?></td>
                        <td>
                            <?php if ($reserva['status'] == 'A'): ?>
                                <span class="status-ativo">Ativa</span>
                            <?php else: ?>
                                <span class="status-cancelado">Cancelada</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($reserva['status'] == 'A'): ?>
                                <a href="cancelar_reserva.php?id=<?= $reserva['id'] ?>" 
                                   onclick="return confirm('Deseja realmente cancelar esta reserva?')" 
                                   class="btn-cancelar">Cancelar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Você ainda não possui reservas.</p>
    <?php endif; ?>
</div>
</body>
</html>
