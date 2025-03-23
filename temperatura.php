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