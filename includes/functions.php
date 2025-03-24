<?php
// includes/functions.php

const PRIMOS = [2, 3, 5, 7, 11, 13, 17, 19, 23];
const PARES = [2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24];
const TRIOS_COMUNS = [
    [20, 21, 22],
    [23, 24, 25]
];

function getUltimoConcurso($pdo) {
    $stmt = $pdo->query("SELECT concurso, numeros FROM resultados ORDER BY concurso DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return [$row['concurso'], json_decode($row['numeros'], true)];
}

function analisarFrequenciaUltimosN($pdo, $n = 50) {
    $stmt = $pdo->prepare("SELECT numeros FROM resultados ORDER BY concurso DESC LIMIT :limit");
    $stmt->bindValue(':limit', (int)$n, PDO::PARAM_INT);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $bolas = [];
    foreach ($resultados as $num) {
        $bolas = array_merge($bolas, json_decode($num, true));
    }
    return array_count_values($bolas);
}

// Adicione outras funções conforme necessário