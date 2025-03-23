<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT lote_id, concurso, jogos, pdf_path, txt_path FROM jogos_gerados WHERE user_id = ? ORDER BY lote_id DESC");
$stmt->execute([$_SESSION['user_id']]);
$jogos_gerados = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <?php
        if ($jogos_gerados) {
            echo '<table class="table table-striped">';
            echo '<thead><tr><th>Lote</th><th>Concurso</th><th>Jogos</th><th>Downloads</th></tr></thead>';
            echo '<tbody>';
            foreach ($jogos_gerados as $jogo) {
                $jogos = json_decode($jogo['jogos'], true);
                echo '<tr>';
                echo '<td>' . htmlspecialchars($jogo['lote_id']) . '</td>';
                echo '<td>' . htmlspecialchars($jogo['concurso']) . '</td>';
                echo '<td>';
                foreach ($jogos as $index => $numeros) {
                    echo sprintf("Jogo %02d: %s<br>", $index + 1, implode(', ', $numeros));
                }
                echo '</td>';
                echo '<td>';
                if (file_exists($jogo['pdf_path'])) {
                    echo '<a href="' . htmlspecialchars($jogo['pdf_path']) . '" download>PDF</a> ';
                }
                if (file_exists($jogo['txt_path'])) {
                    echo '<a href="' . htmlspecialchars($jogo['txt_path']) . '" download>TXT</a>';
                }
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>Nenhum jogo gerado ainda.</p>';
        }
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>