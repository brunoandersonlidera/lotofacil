<?php
function analisarFrequenciaUltimosN($pdo, $n = 50) {
    try {
        $stmt = $pdo->prepare("SELECT numeros FROM resultados ORDER BY concurso DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$n, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $bolas = [];
        foreach ($resultados as $num) {
            $numeros = json_decode($num, true);
            if ($numeros === null) {
                error_log("Erro ao decodificar JSON em analisarFrequenciaUltimosN: " . json_last_error_msg(), 3, "erros.log");
                continue;
            }
            $bolas = array_merge($bolas, $numeros);
        }
        return array_count_values($bolas);
    } catch (Exception $e) {
        error_log("Erro em analisarFrequenciaUltimosN: " . $e->getMessage(), 3, "erros.log");
        throw $e; // Re-throw para ser capturado no temperatura.php
    }
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

<?php
function getUltimoConcurso($pdo) {
    try {
        $stmt = $pdo->query("SELECT concurso, numeros FROM resultados ORDER BY concurso DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? [$row['concurso'], json_decode($row['numeros'])] : [0, []];
    } catch (Exception $e) {
        error_log("Erro em getUltimoConcurso: " . $e->getMessage(), 3, __DIR__ . "/../erros.log");
        return [0, []];
    }
}
?>