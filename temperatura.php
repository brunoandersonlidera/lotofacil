<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$pdo = getDB();

function getTemperaturaNumeros($pdo) {
    $freq = analisarFrequenciaUltimosN($pdo, 50);
    arsort($freq);
    $sortedFreq = array_keys($freq);
    $quentes = array_slice($sortedFreq, 0, 12);
    $mornos = array_slice($sortedFreq, 12, 6);
    $frios = array_slice($sortedFreq, 18, 5);
    $congelados = array_diff(range(1, 25), array_keys($freq));
    $quatroMaisQuentes = array_slice($sortedFreq, 0, 4);
    return [
        'quentes' => $quentes,
        'mornos' => $mornos,
        'frios' => $frios,
        'congelados' => array_values($congelados),
        'quatro_mais_quentes' => $quatroMaisQuentes,
        'frequencias' => $freq
    ];
}

function simularPrevisoesUltimos20($pdo) {
    $stmt = $pdo->query("SELECT concurso, numeros FROM resultados ORDER BY concurso DESC LIMIT 20");
    $resultados = $stmt->fetchAll();
    $simulacao = [];
    foreach ($resultados as $resultado) {
        $concurso = $resultado['concurso'];
        $numeros = json_decode($resultado['numeros'], true);
        $freq = analisarFrequenciaAteConcurso($pdo, $concurso);
        arsort($freq);
        $previsao = array_slice(array_keys($freq), 0, 15);
        $acertos = count(array_intersect($previsao, $numeros));
        $simulacao[] = [
            'concurso' => $concurso,
            'previsao' => $previsao,
            'resultado' => $numeros,
            'acertos' => $acertos
        ];
    }
    return array_reverse($simulacao);
}

function analisarFrequenciaAteConcurso($pdo, $concurso, $ultimosN = 50) {
    $stmt = $pdo->prepare("SELECT numeros FROM resultados WHERE concurso < ? ORDER BY concurso DESC LIMIT ?");
    $stmt->execute([$concurso, $ultimosN]);
    $resultados = $stmt->fetchAll();
    $bolas = [];
    foreach ($resultados as $res) {
        $numeros = json_decode($res['numeros'], true);
        $bolas = array_merge($bolas, $numeros);
    }
    return array_count_values($bolas);
}

$temperatura = getTemperaturaNumeros($pdo);
$simulacao = simularPrevisoesUltimos20($pdo);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temperatura dos Números - Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Lotofácil</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="gerar_jogos.php">Gerar Jogos</a></li>
                    <li class="nav-item"><a class="nav-link active" href="temperatura.php">Temperatura dos Números</a></li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link" href="adicionar_resultado.php">Adicionar Resultado</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin.php">Painel de Admin</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="jogos_gerados.php">Jogos Gerados</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center text-danger">Temperatura dos Números</h2>

        <!-- Seção de Temperatura -->
        <div class="temp-section">
            <h3 class="temp-title">🔥 Quentes (12 mais frequentes)</h3>
            <div class="temp-numbers">
                <?php foreach ($temperatura['quentes'] as $num): ?>
                    <span class="temp-number quente <?= in_array($num, $temperatura['quatro_mais_quentes']) ? 'top-4' : '' ?>" onclick="showFreq(<?= $num ?>)"><?= $num ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="temp-section">
            <h3 class="temp-title">🌞 Mornos (13º ao 18º)</h3>
            <div class="temp-numbers">
                <?php foreach ($temperatura['mornos'] as $num): ?>
                    <span class="temp-number morno" onclick="showFreq(<?= $num ?>)"><?= $num ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="temp-section">
            <h3 class="temp-title">❄️ Frios (19º ao 23º)</h3>
            <div class="temp-numbers">
                <?php foreach ($temperatura['frios'] as $num): ?>
                    <span class="temp-number frio" onclick="showFreq(<?= $num ?>)"><?= $num ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="temp-section">
            <h3 class="temp-title">🧊 Congelados (menos frequentes)</h3>
            <div class="temp-numbers">
                <?php foreach ($temperatura['congelados'] as $num): ?>
                    <span class="temp-number congelado" onclick="showFreq(<?= $num ?>)"><?= $num ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Simulação dos Últimos 20 Concursos -->
        <h3 class="text-center text-danger mt-5">Simulação dos Últimos 20 Concursos</h3>
        <table class="temp-table table table-striped">
            <thead>
                <tr>
                    <th>Concurso</th>
                    <th>Previsão</th>
                    <th>Resultado</th>
                    <th>Acertos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($simulacao as $sim): ?>
                    <tr>
                        <td><?= $sim['concurso'] ?></td>
                        <td>
                            <?php foreach ($sim['previsao'] as $num): ?>
                                <span class="prediction-span"><?= $num ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <?php foreach ($sim['resultado'] as $num): ?>
                                <span class="result-span"><?= $num ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td><?= $sim['acertos'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Gráfico -->
        <h3 class="text-center text-danger mt-5">Estatísticas de Acertos</h3>
        <canvas id="acertosChart" width="400" height="200"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function showFreq(numero) {
            const freqs = <?= json_encode($temperatura['frequencias']) ?>;
            alert(`Frequência do número ${numero}: ${freqs[numero] || 0} vezes nos últimos 50 concursos`);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('acertosChart').getContext('2d');
            const simulacao = <?= json_encode($simulacao) ?>;
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: simulacao.map(s => s.concurso),
                    datasets: [{
                        label: 'Número de Acertos',
                        data: simulacao.map(s => s.acertos),
                        borderColor: '#FF4444',
                        backgroundColor: 'rgba(255, 68, 68, 0.2)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true, max: 15, title: { display: true, text: 'Acertos' } },
                        x: { title: { display: true, text: 'Concurso' } }
                    }
                }
            });
        });
    </script>
</body>
</html>