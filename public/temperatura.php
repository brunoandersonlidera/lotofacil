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
    $stmt = $pdo->prepare("SELECT numeros FROM resultados WHERE concurso < :concurso ORDER BY concurso DESC LIMIT :limit");
    $stmt->bindValue(':concurso', (int)$concurso, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$ultimosN, PDO::PARAM_INT);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $bolas = [];
    foreach ($resultados as $num) {
        $numeros = json_decode($num, true);
        if ($numeros === null) {
            error_log("Erro ao decodificar JSON em analisarFrequenciaAteConcurso: " . json_last_error_msg(), 3, "erros.log");
            continue;
        }
        $bolas = array_merge($bolas, $numeros);
    }
    return array_count_values($bolas);
}

try {
    $temperatura = getTemperaturaNumeros($pdo);
    $simulacao = simularPrevisoesUltimos20($pdo);
} catch (Exception $e) {
    echo '<p>Erro ao carregar Temperatura dos Números: ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}
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
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="border: 1px solid #ccc; padding: 5px;">Concurso</th>
                <th style="border: 1px solid #ccc; padding: 5px;">Previsão</th>
                <th style="border: 1px solid #ccc; padding: 5px;">Resultado</th>
                <th style="border: 1px solid #ccc; padding: 5px;">Acertos</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($simulacao as $sim): ?>
                <tr>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= $sim['concurso'] ?></td>
                    <td style="border: 1px solid #ccc; padding: 5px;">
                        <?php foreach ($sim['previsao'] as $num): ?>
                            <span class="prediction-span"><?= $num ?></span>
                        <?php endforeach; ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 5px;">
                        <?php foreach ($sim['resultado'] as $num): ?>
                            <span class="result-span"><?= $num ?></span>
                        <?php endforeach; ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 5px;"><?= $sim['acertos'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function showFreq(numero) {
        const freqs = <?= json_encode($temperatura['frequencias']) ?>;
        alert(`Frequência do número ${numero}: ${freqs[numero] || 0} vezes nos últimos 50 concursos`);
    }
</script>