<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAdmin(); // Apenas admin pode executar

// URL fictícia - substitua pela URL real do arquivo Lotofácil.xlsx
$url = "https://loterias.caixa.gov.br/lotofacil/historico/Lotofácil.xlsx";
$destino = __DIR__ . "/downloads/Lotofácil.xlsx";

function downloadArquivo($url, $destino) {
    // Verifica se o diretório downloads existe, senão cria
    $dir = dirname($destino);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Usa cURL para baixar o arquivo
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Segue redirecionamentos
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"); // Simula um navegador
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignora SSL (use com cuidado)
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $data !== false) {
        file_put_contents($destino, $data);
        return true;
    } else {
        return "Erro ao baixar: HTTP $httpCode";
    }
}

function importarResultados($arquivo, $pdo) {
    // Aqui você precisará de uma biblioteca como PhpSpreadsheet
    require_once 'vendor/autoload.php';
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($arquivo);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    foreach ($rows as $index => $row) {
        if ($index == 0) continue; // Pula o cabeçalho
        $concurso = (int) $row[0]; // Assume que a coluna 0 é o número do concurso
        $numeros = array_map('intval', array_slice($row, 2, 15)); // Assume 15 números a partir da coluna 2
        if (count($numeros) == 15) {
            $stmt = $pdo->prepare("INSERT INTO resultados (concurso, numeros) VALUES (?, ?) ON DUPLICATE KEY UPDATE numeros = ?");
            $stmt->execute([$concurso, json_encode($numeros), json_encode($numeros)]);
        }
    }
    return true;
}

$pdo = getDB();
$resultado = downloadArquivo($url, $destino);

if ($resultado === true) {
    importarResultados($destino, $pdo);
    $mensagem = "Arquivo baixado e resultados importados com sucesso!";
} else {
    $mensagem = $resultado;
}
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
        <p><?= $mensagem ?></p>
        <a href="adicionar_resultado.php" class="btn btn-secondary">Voltar</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>