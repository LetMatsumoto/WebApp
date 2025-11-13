<?php
require_once 'funcoes.php';
require_once 'conexao.php';

if (!is_logged()) {
    redirect('login.php');
}

$id_evento = $_GET['id'] ?? 0;
$login = $_SESSION['login'];

$google_maps_api_key = "AIzaSyD1ymgJSOFD9yCS4hoC7hNeU8Km40bbQi0";

// Buscar informações do evento
$stmt = $conn->prepare("SELECT e.*, 
                        (SELECT COUNT(*) FROM reservas WHERE id_evento = e.id AND status = 'A') as reservas_ativas
                        FROM eventos e WHERE e.id = ?");
$stmt->bind_param("i", $id_evento);
$stmt->execute();
$result = $stmt->get_result();
$evento = $result->fetch_assoc();
$stmt->close();

if (!$evento) {
    redirect('pag_principal.php?erro=Evento não encontrado');
}

$coords = explode(',', $evento['local']);
$lat = floatval(trim($coords[0] ?? 0));
$lng = floatval(trim($coords[1] ?? 0));

$vagas_disponiveis = $evento['capacidade'] - $evento['reservas_ativas'];

// 🌤️ NOVO: Buscar previsão do tempo com Open-Meteo
$weather_info = null;

// Verificar se a data do evento está dentro dos próximos 16 dias (limite da Open-Meteo)
$event_date = new DateTime($evento['data']);
$today = new DateTime();
$today->setTime(0,0,0);
$max_forecast_date = (clone $today)->modify('+16 days');

if ($event_date >= $today && $event_date <= $max_forecast_date && $lat != 0 && $lng != 0) {
    // Formatar data para a API
    $start_date = $evento['data'];
    $end_date = $evento['data'];
    
    // URL da Open-Meteo
    $weather_url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lng}&daily=temperature_2m_max,temperature_2m_min,precipitation_sum,weathercode&timezone=auto&start_date={$start_date}&end_date={$end_date}";
    
    $weather_response = @file_get_contents($weather_url);
    
    if ($weather_response !== false) {
        $weather_data = json_decode($weather_response, true);
        
        if (isset($weather_data['daily']) && count($weather_data['daily']['time']) > 0) {
            $weather_code = $weather_data['daily']['weathercode'][0];
            $max_temp = $weather_data['daily']['temperature_2m_max'][0];
            $min_temp = $weather_data['daily']['temperature_2m_min'][0];
            $precipitation = $weather_data['daily']['precipitation_sum'][0];
            
            // Converter código do tempo em descrição
            $condition = convert_weather_code($weather_code);
            $icon = get_weather_icon($weather_code);
            
            $weather_info = [
                'condition' => $condition,
                'icon' => $icon,
                'max_temp' => round($max_temp),
                'min_temp' => round($min_temp),
                'precipitation' => $precipitation,
                'chance_rain' => $precipitation > 0 ? 'Possível' : 'Improvável'
            ];
        }
    }
} else {
    $weather_info = 'out_of_range';
}

// Função para converter código do tempo em descrição
function convert_weather_code($code) {
    $codes = [
        0 => 'Céu limpo',
        1 => 'Principalmente limpo',
        2 => 'Parcialmente nublado',
        3 => 'Nublado',
        45 => 'Nevoeiro',
        48 => 'Nevoeiro com geada',
        51 => 'Chuvisco leve',
        53 => 'Chuvisco moderado',
        55 => 'Chuvisco intenso',
        56 => 'Chuvisco leve e congelante',
        57 => 'Chuvisco intenso e congelante',
        61 => 'Chuva leve',
        63 => 'Chuva moderada',
        65 => 'Chuva forte',
        66 => 'Chuva leve e congelante',
        67 => 'Chuva forte e congelante',
        71 => 'Queda de neve leve',
        73 => 'Queda de neve moderada',
        75 => 'Queda de neve forte',
        77 => 'Grãos de neve',
        80 => 'Pancadas de chuva leves',
        81 => 'Pancadas de chuva moderadas',
        82 => 'Pancadas de chuva fortes',
        85 => 'Pancadas de neve leves',
        86 => 'Pancadas de neve fortes',
        95 => 'Tempestade',
        96 => 'Tempestade com granizo leve',
        99 => 'Tempestade com granizo forte'
    ];
    
    return $codes[$code] ?? 'Condição desconhecida';
}

// Função para obter ícone baseado no código do tempo
function get_weather_icon($code) {
    $icons = [
        0 => '☀️',   // Céu limpo
        1 => '🌤️',   // Principalmente limpo
        2 => '⛅',    // Parcialmente nublado
        3 => '☁️',    // Nublado
        45 => '🌫️',  // Nevoeiro
        48 => '🌫️',  // Nevoeiro com geada
        51 => '🌦️',  // Chuvisco leve
        53 => '🌦️',  // Chuvisco moderado
        55 => '🌧️',  // Chuvisco intenso
        61 => '🌧️',  // Chuva leve
        63 => '🌧️',  // Chuva moderada
        65 => '⛈️',   // Chuva forte
        71 => '🌨️',  // Neve leve
        73 => '🌨️',  // Neve moderada
        75 => '❄️',   // Neve forte
        80 => '🌦️',  // Pancadas de chuva
        81 => '🌧️',  // Pancadas moderadas
        82 => '⛈️',   // Pancadas fortes
        85 => '🌨️',  // Pancadas de neve
        86 => '❄️',   // Pancadas de neve fortes
        95 => '⛈️',   // Tempestade
        96 => '⛈️',   // Tempestade com granizo
        99 => '⛈️'    // Tempestade com granizo forte
    ];
    
    return $icons[$code] ?? '🌈';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar se o usuário já tem reserva ativa neste evento
    $stmt = $conn->prepare("SELECT * FROM reservas WHERE login_usuario = ? AND id_evento = ? AND status = 'A'");
    $stmt->bind_param("si", $login, $id_evento);
    $stmt->execute();
    $reserva_existente = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($reserva_existente) {
        $erro = "Você já possui uma reserva ativa para este evento.";
    } elseif ($vagas_disponiveis <= 0) {
        $erro = "Este evento está lotado.";
    } else {
        // Criar reserva
        $stmt = $conn->prepare("INSERT INTO reservas (login_usuario, id_evento, status) VALUES (?, ?, 'A')");
        $stmt->bind_param("si", $login, $id_evento);
        
        if ($stmt->execute()) {
            $stmt->close();
            redirect('pag_principal.php?success=Reserva realizada com sucesso!');
        } else {
            $erro = "Erro ao realizar reserva. Tente novamente.";
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fazer Reserva</title>
    <link rel="stylesheet" href="CSS/style.css">
    <!-- estilos para o mapa e clima -->
    <style>
        #map {
            width: 100%;
            height: 400px;
            margin: 20px 0;
            border-radius: 8px;
            border: 2px solid #9b59b6;
        }
        .weather-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .weather-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .weather-icon {
            font-size: 3em;
        }
        .weather-details {
            flex: 1;
        }
        .weather-condition {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .weather-temps {
            display: flex;
            gap: 15px;
            font-size: 0.9em;
        }
        .weather-loading {
            text-align: center;
            padding: 10px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Confirmar Reserva</h2>
    
    <?php if (isset($erro)): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <div class="evento-detalhes">
        <h3><?= htmlspecialchars($evento['nome']) ?></h3>
        <p><strong>Descrição:</strong> <?= htmlspecialchars($evento['descricao']) ?></p>
        <!-- exibir mapa com o local do evento -->
        <p><strong>Local:</strong></p>
        <div id="map"></div>
        <!-- 🌤️ SEÇÃO DO CLIMA -->
        <?php if (is_array($weather_info)): ?>
            <p><strong>Previsão do tempo:</strong></p>
            <div class="weather-info">
                <div class="weather-content">
                    <div class="weather-icon"><?= $weather_info['icon'] ?></div>
                    <div class="weather-details">
                        <div class="weather-condition"><?= $weather_info['condition'] ?></div>
                        <div class="weather-temps">
                            <span>🌡 Máx: <?= $weather_info['max_temp'] ?>°C</span>
                            <span>🌡 Mín: <?= $weather_info['min_temp'] ?>°C</span>
                            <span>💧 Chuva: <?= $weather_info['chance_rain'] ?></span>
                            <?php if ($weather_info['precipitation'] > 0): ?>
                                <span>📊 Precipitação: <?= $weather_info['precipitation'] ?>mm</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($weather_info === 'out_of_range'): ?>
            <div class="weather-loading">
                📅 A previsão do tempo estará disponível a partir de <?= $today->modify('-16 days')->format('d/m/Y') ?>
            </div>
        <?php else: ?>
            <div class="weather-loading">
                🌤️ Previsão do tempo não disponível para estas coordenadas
            </div>
        <?php endif; ?>
        <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($evento['data'])) ?></p>
        <p><strong>Hora:</strong> <?= date('H:i', strtotime($evento['hora'])) ?></p>
        <p><strong>Vagas disponíveis:</strong> <?= $vagas_disponiveis ?></p>
    </div>

    <?php if ($vagas_disponiveis > 0): ?>
        <form method="post">
            <button type="submit">Confirmar Reserva</button>
            <a href="pag_principal.php" class="btn-secondary">Cancelar</a>
        </form>
    <?php else: ?>
        <p class="alert">Este evento está lotado.</p>
        <a href="pag_principal.php" class="btn-secondary">Voltar</a>
    <?php endif; ?>
</div>

<!-- script do Google Maps -->
<script>
    function initMap() {
        const eventLocation = { lat: <?= $lat ?>, lng: <?= $lng ?> };
        
        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 15,
            center: eventLocation,
        });
        
        const marker = new google.maps.Marker({
            position: eventLocation,
            map: map,
            title: "<?= htmlspecialchars($evento['nome']) ?>",
        });
    }
</script>
<script async src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_maps_api_key; ?>&callback=initMap"></script>
</body>
</html>