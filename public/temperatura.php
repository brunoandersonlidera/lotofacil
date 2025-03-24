<?php
// Removido session_start(), pois já está em index.php
require_once realpath(__DIR__ . '/../includes/db.php');
require_once realpath(__DIR__ . '/../includes/auth.php');
require_once realpath(__DIR__ . '/../includes/functions.php');

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = getDB();

// Restante do código permanece igual...
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

// Restante do código continua como está...


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
            error_log("Erro ao decodificar JSON em analisarFrequenciaAteConcurso para concurso $concurso");
            continue;
        }
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
    <title>Temperatura dos Números - Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .quente { background-color: #ff4d4d; color: white; }
        .morno { background-color: #ffcc00; color: black; }
        .frio { background-color: #4da8ff; color: white; }
        .congelado { background-color: #cccccc; color: black; }
        .numero { display: inline-block; width: 40px; height: 40px; line-height: 40px; text-align: center; margin: 5px; border-radius: 50%; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Temperatura dos Números (Últimos 50 Concursos)</h2>

        <h4>Quentes (12 mais frequentes)</h4>
        <div>
            <?php foreach ($temperatura['quentes'] as $num): ?>
                <span class="numero quente"><?= $num ?></span>
            <?php endforeach; ?>
        </div>

        <h4>Mornos (6 intermediários)</h4>
        <div>
            <?php foreach ($temperatura['mornos'] as $num): ?>
                <span class="numero morno"><?= $num ?></span>
            <?php endforeach; ?>
        </div>

        <h4>Frios (5 menos frequentes)</h4>
        <div>
            <?php foreach ($temperatura['frios'] as $num): ?>
                <span class="numero frio"><?= $num ?></span>
            <?php endforeach; ?>
        </div>

        <h4>Congelados (não sorteados)</h4>
        <div>
            <?php if (empty($temperatura['congelados'])): ?>
                <p>Nenhum número congelado.</p>
            <?php else: ?>
                <?php foreach ($temperatura['congelados'] as $num): ?>
                    <span class="numero congelado"><?= $num ?></span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h4>Quatro Mais Quentes</h4>
        <div>
            <?php foreach ($temperatura['quatro_mais_quentes'] as $num): ?>
                <span class="numero quente"><?= $num ?></span>
            <?php endforeach; ?>
        </div>

        <h3 class="mt-5">Simulação de Previsões (Últimos 20 Concursos)</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Concurso</th>
                    <th>Previsão (15 números)</th>
                    <th>Resultado Real</th>
                    <th>Acertos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($simulacao as $sim): ?>
                    <tr>
                        <td><?= $sim['concurso'] ?></td>
                        <td><?= implode(', ', $sim['previsao']) ?></td>
                        <td><?= implode(', ', $sim['resultado']) ?></td>
                        <td><?= $sim['acertos'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>