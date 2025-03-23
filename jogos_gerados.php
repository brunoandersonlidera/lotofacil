<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM jogos_gerados WHERE user_id = ? ORDER BY data_geracao DESC");
$stmt->execute([$_SESSION['user_id']]);
$jogos_gerados = $stmt->fetchAll(PDO::FETCH_ASSOC);

function verificar_resultados($pdo, $jogo, $concurso) {
    $stmt = $pdo->prepare("SELECT numeros FROM resultados WHERE concurso = ?");
    $stmt->execute([$concurso]);
    $resultado = $stmt->fetchColumn();
    if ($resultado) {
        $numeros_sorteados = json_decode($resultado);
        return count(array_intersect($jogo, $numeros_sorteados));
    }
    return null;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogos Gerados - Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Jogos Gerados</h2>
        <?php if (empty($jogos_gerados)): ?>
            <p>Nenhum jogo gerado ainda.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Concurso</th>
                        <th>Jogos</th>
                        <th>Data</th>
                        <th>Downloads</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jogos_gerados as $jg): ?>
                        <?php $jogos = json_decode($jg['jogos'], true); ?>
                        <tr>
                            <td><?= $jg['lote_id'] ?></td>
                            <td><?= $jg['concurso'] ?></td>
                            <td>
                                <?php foreach ($jogos as $index => $jogo): ?>
                                    Jogo <?= $index + 1 ?>: <?= implode(', ', $jogo) ?><br>
                                <?php endforeach; ?>
                            </td>
                            <td><?= $jg['data_geracao'] ?></td>
                            <td>
                                <a href="<?= $jg['pdf_path'] ?>" class="btn btn-sm btn-primary" download>PDF</a>
                                <a href="<?= $jg['txt_path'] ?>" class="btn btn-sm btn-secondary" download>TXT</a>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="verResultados('<?= $jg['lote_id'] ?>', <?= $jg['concurso'] ?>)">Ver Resultados</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verResultados(loteId, concurso) {
            fetch(`ver_resultados.php?concurso=${concurso}&lote_id=${loteId}`)
                .then(response => response.json())
                .then(data => {
                    let mensagem = `Resultados do Concurso ${concurso}:\n`;
                    data.forEach((acertos, index) => {
                        mensagem += `Jogo ${index + 1}: ${acertos} acertos\n`;
                    });
                    alert(mensagem);
                })
                .catch(error => alert('Erro ao verificar resultados: ' + error));
        }
    </script>
</body>
</html>