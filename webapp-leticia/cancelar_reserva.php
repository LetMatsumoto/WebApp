<?php
require_once 'funcoes.php';
require_once 'conexao.php';

if (!is_logged()) {
    redirect('login.php');
}

$id_reserva = $_GET['id'] ?? 0;
$login = $_SESSION['login'];

// Verificar se a reserva pertence ao usuário
$stmt = $conn->prepare("SELECT * FROM reservas WHERE id = ? AND login_usuario = ?");
$stmt->bind_param("is", $id_reserva, $login);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($reserva && $reserva['status'] == 'A') {
    // Cancelar a reserva
    $stmt = $conn->prepare("UPDATE reservas SET status = 'C' WHERE id = ?");
    $stmt->bind_param("i", $id_reserva);
    
    if ($stmt->execute()) {
        redirect('pag_principal.php?success=Reserva cancelada com sucesso!');
    } else {
        redirect('pag_principal.php?erro=Erro ao cancelar reserva.');
    }
    $stmt->close();
} else {
    redirect('pag_principal.php?erro=Reserva não encontrada.');
}
?>
