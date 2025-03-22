<?php
function analisarFrequenciaUltimosN($pdo, $ultimosN = 50) {
    $stmt = $pdo->prepare("SELECT numeros FROM resultados ORDER BY concurso DESC LIMIT ?");
    $stmt->execute([$ultimosN]);
    $resultados = $stmt->fetchAll();
    $bolas = [];
    foreach ($resultados as $res) {
        $numeros = json_decode($res['numeros'], true);
        $bolas = array_merge($bolas, $numeros);
    }
    return array_count_values($bolas);
}

function gerarJogo($quantidadeNumeros, $numerosFixos, $numerosExcluidos, $estrategias, $pdo) {
    // Adaptar a lógica do Python aqui
    $numerosDisponiveis = range(1, 25);
    $numerosDisponiveis = array_diff($numerosDisponiveis, $numerosExcluidos);
    $numerosDisponiveis = array_values($numerosDisponiveis);
    $jogo = array_merge($numerosFixos);
    while (count($jogo) < $quantidadeNumeros) {
        $novoNumero = $numerosDisponiveis[array_rand($numerosDisponiveis)];
        if (!in_array($novoNumero, $jogo)) {
            $jogo[] = $novoNumero;
        }
    }
    sort($jogo);
    return $jogo;
}

function getUltimoConcurso($pdo) {
    $stmt = $pdo->query("SELECT MAX(concurso) as ultimo FROM resultados");
    $result = $stmt->fetch();
    return $result['ultimo'] ?? 0;
}
?>