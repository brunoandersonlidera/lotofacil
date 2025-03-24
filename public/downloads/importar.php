<?php
$json = file_get_contents('resultados.json'); // Substitua pelo caminho do seu arquivo JSON
$data = json_decode($json, true);

$sql = "INSERT INTO resultados (concurso, numeros) VALUES\n";
$values = [];
foreach ($data as $concurso => $numeros) {
    $numerosJson = json_encode($numeros);
    $values[] = "($concurso, '$numerosJson')";
}
$sql .= implode(",\n", $values) . "\nON DUPLICATE KEY UPDATE numeros = VALUES(numeros);";

file_put_contents('resultados.sql', $sql);
echo "SQL gerado em resultados.sql";
?>