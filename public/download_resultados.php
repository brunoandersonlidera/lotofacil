<?php
require_once realpath(__DIR__ . '/../includes/db.php');
require_once realpath(__DIR__ . '/../includes/auth.php');
requireAdmin();

$url = "https://servicebus2.caixa.gov.br/portaldeloterias/api/lotofacil"; // API do último concurso
$pdo = getDB();

function baixarResultadoAPI($url, $pdo) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $data !== false) {
        $json = json_decode($data, true);
        if (isset($json['numero']) && isset($json['dezenas'])) {
            $concurso = (int) $json['numero'];
            $numeros = array_map('intval', $json['dezenas']);
            $stmt = $pdo->prepare("INSERT INTO resultados (concurso, numeros) VALUES (?, ?) ON DUPLICATE KEY UPDATE numeros = ?");
            $stmt->execute([$concurso, json_encode($numeros), json_encode($numeros)]);
            return "Concurso $concurso importado com sucesso!";
        }
        return "Dados inválidos retornados pela API.";
    }
    return "Erro ao acessar a API: HTTP $httpCode";
}

$resultado = baixarResultadoAPI($url, $pdo);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Download Resultados - Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Download de Resultados</h2>
        <p><?= $resultado ?></p>
        <a href="adicionar_resultado.php" class="btn btn-secondary">Voltar</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>