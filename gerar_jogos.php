<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantidadeNumeros = intval($_POST['quantidade_numeros']);
    $quantidadeJogos = intval($_POST['quantidade_jogos']);
    $numerosFixos = array_filter(array_map('intval', explode(',', $_POST['numeros_fixos'])));
    $numerosExcluidos = array_filter(array_map('intval', explode(',', $_POST['numeros_excluidos'])));
    $estrategias = $_POST['estrategias'] ?? [];

    $pdo = getDB();
    $jogos = [];
    for ($i = 0; $i < $quantidadeJogos; $i++) {
        $jogo = gerarJogo($quantidadeNumeros, $numerosFixos, $numerosExcluidos, $estrategias, $pdo);
        $jogos[] = $jogo;
    }

    $loteId = date('YmdHis');
    $usuarioId = $_SESSION['user_id'];
    $concurso = getUltimoConcurso($pdo) + 1;
    $dataGeracao = date('d/m/Y H:i:s');

    $stmt = $pdo->prepare("INSERT INTO jogos_gerados (usuario_id, lote_id, jogos, data_geracao, concurso) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$usuarioId, $loteId, json_encode($jogos), $dataGeracao, $concurso]);

    // Geração de PDF e TXT (exemplo com TCPDF)
    require_once 'vendor/tcpdf/tcpdf.php';
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Write(0, json_encode($jogos));
    $pdfFile = "jogos_$loteId.pdf";
    $pdf->Output(__DIR__ . "/$pdfFile", 'F');

    $txtFile = "jogos_$loteId.txt";
    file_put_contents($txtFile, json_encode($jogos));

    $success = "Jogos gerados com sucesso! <a href='$pdfFile'>Baixar PDF</a> | <a href='$txtFile'>Baixar TXT</a>";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerar Jogos - Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Gerar Jogos</h2>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="quantidade_numeros" class="form-label">Quantidade de Números</label>
                <input type="number" class="form-control" id="quantidade_numeros" name="quantidade_numeros" min="15" max="20" required>
            </div>
            <div class="mb-3">
                <label for="quantidade_jogos" class="form-label">Quantidade de Jogos</label>
                <input type="number" class="form-control" id="quantidade_jogos" name="quantidade_jogos" min="1" required>
            </div>
            <div class="mb-3">
                <label for="numeros_fixos" class="form-label">Números Fixos (separados por vírgula)</label>
                <input type="text" class="form-control" id="numeros_fixos" name="numeros_fixos">
            </div>
            <div class="mb-3">
                <label for="numeros_excluidos" class="form-label">Números Excluídos (separados por vírgula)</label>
                <input type="text" class="form-control" id="numeros_excluidos" name="numeros_excluidos">
            </div>
            <button type="submit" class="btn btn-primary">Gerar Apostas</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>