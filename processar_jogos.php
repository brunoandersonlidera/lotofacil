<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'vendor/fpdf/fpdf.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

define('PRIMOS', [2, 3, 5, 7, 11, 13, 17, 19, 23]);
define('PARES', range(2, 24, 2));
define('IMPARES', array_diff(range(1, 25), PARES));
define('TABELA_PRECOS', [15 => 3.00, 16 => 48.00, 17 => 408.00, 18 => 2448.00, 19 => 11628.00, 20 => 38760.00]);

function get_ultimo_concurso($pdo) {
    $stmt = $pdo->query("SELECT concurso, numeros FROM resultados ORDER BY concurso DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? [$row['concurso'], json_decode($row['numeros'])] : [0, []];
}

function analisar_frequencia_ultimos_n($pdo, $n = 50) {
    $stmt = $pdo->prepare("SELECT numeros FROM resultados ORDER BY concurso DESC LIMIT ?");
    $stmt->execute([$n]);
    $numeros = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $todos_numeros = [];
    foreach ($numeros as $n) {
        $todos_numeros = array_merge($todos_numeros, json_decode($n));
    }
    return array_count_values($todos_numeros);
}

function numeros_atrasados($pdo, $n = 50) {
    $freq = analisar_frequencia_ultimos_n($pdo, $n);
    return array_diff(range(1, 25), array_keys($freq));
}

function gerar_jogo($pdo, $quantidade_numeros, $numeros_fixos, $numeros_excluidos, $estrategias, $jogos_anteriores, $ultimo_sorteio) {
    $jogo = $numeros_fixos;
    $disponiveis = array_diff(range(1, 25), $numeros_excluidos, $jogo);
    shuffle($disponiveis);
    $freq_dezenas = analisar_frequencia_ultimos_n($pdo);
    $atrasados = numeros_atrasados($pdo);

    if (in_array('frequencia', $estrategias)) {
        arsort($freq_dezenas);
        $mais_frequentes = array_intersect(array_keys($freq_dezenas), $disponiveis);
        foreach (array_slice($mais_frequentes, 0, rand(10, 12) - count($jogo)) as $n) {
            if (count($jogo) < $quantidade_numeros) {
                $jogo[] = $n;
                $disponiveis = array_diff($disponiveis, [$n]);
            }
        }
    }

    if (in_array('primos', $estrategias)) {
        $primos_atuais = count(array_intersect($jogo, PRIMOS));
        $primos_faltantes = rand(3, 5) - $primos_atuais;
        if ($primos_faltantes > 0) {
            $primos_disponiveis = array_intersect(PRIMOS, $disponiveis);
            shuffle($primos_disponiveis);
            foreach (array_slice($primos_disponiveis, 0, $primos_faltantes) as $n) {
                if (count($jogo) < $quantidade_numeros) {
                    $jogo[] = $n;
                    $disponiveis = array_diff($disponiveis, [$n]);
                }
            }
        }
    }

    if (in_array('repeticao', $estrategias)) {
        $repetidos = array_intersect($ultimo_sorteio, $disponiveis);
        shuffle($repetidos);
        foreach (array_slice($repetidos, 0, rand(6, 9)) as $n) {
            if (count($jogo) < $quantidade_numeros) {
                $jogo[] = $n;
                $disponiveis = array_diff($disponiveis, [$n]);
            }
        }
    }

    if (in_array('sequencias', $estrategias) && count($jogo) < $quantidade_numeros) {
        $sequencias = [[20, 21, 22], [23, 24, 25]];
        $seq = $sequencias[array_rand($sequencias)];
        foreach ($seq as $n) {
            if (in_array($n, $disponiveis) && count($jogo) < $quantidade_numeros) {
                $jogo[] = $n;
                $disponiveis = array_diff($disponiveis, [$n]);
            }
        }
    }

    if (in_array('atrasados', $estrategias)) {
        $atrasados_disponiveis = array_intersect($atrasados, $disponiveis);
        shuffle($atrasados_disponiveis);
        foreach (array_slice($atrasados_disponiveis, 0, rand(1, 2)) as $n) {
            if (count($jogo) < $quantidade_numeros) {
                $jogo[] = $n;
                $disponiveis = array_diff($disponiveis, [$n]);
            }
        }
    }

    if (in_array('soma', $estrategias)) {
        while (count($jogo) < $quantidade_numeros && !empty($disponiveis)) {
            $n = array_shift($disponiveis);
            $jogo[] = $n;
        }
        $soma = array_sum($jogo);
        while (($soma < 180 || $soma > 220) && count($disponiveis) > 0) {
            array_pop($jogo);
            $n = array_shift($disponiveis);
            $jogo[] = $n;
            sort($jogo);
            $soma = array_sum($jogo);
        }
    }

    while (count($jogo) < $quantidade_numeros && !empty($disponiveis)) {
        $n = array_shift($disponiveis);
        $jogo[] = $n;
    }

    sort($jogo);
    return $jogo;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $quantidade_numeros = filter_input(INPUT_POST, 'quantidade_numeros', FILTER_VALIDATE_INT, ['options' => ['min_range' => 15, 'max_range' => 20]]);
        $quantidade_jogos = filter_input(INPUT_POST, 'quantidade_jogos', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $numeros_fixos = !empty($_POST['numeros_fixos']) ? array_map('intval', explode(',', $_POST['numeros_fixos'])) : [];
        $numeros_excluidos = !empty($_POST['numeros_excluidos']) ? array_map('intval', explode(',', $_POST['numeros_excluidos'])) : [];
        $estrategias = !empty($_POST['estrategias']) ? $_POST['estrategias'] : ['frequencia'];

        if ($quantidade_numeros === false || $quantidade_jogos === false) {
            throw new Exception("Quantidade inválida.");
        }

        $pdo = getDB();
        $stmt = $pdo->query("SELECT numeros FROM resultados");
        $jogos_anteriores = array_map(function($n) { return json_decode($n); }, $stmt->fetchAll(PDO::FETCH_COLUMN));
        list($ultimo_concurso, $ultimo_sorteio) = get_ultimo_concurso($pdo);

        $jogos = [];
        for ($i = 0; $i < $quantidade_jogos; $i++) {
            $jogo = gerar_jogo($pdo, $quantidade_numeros, $numeros_fixos, $numeros_excluidos, $estrategias, array_merge($jogos_anteriores, $jogos), $ultimo_sorteio);
            if (!in_array($jogo, $jogos)) {
                $jogos[] = $jogo;
            }
        }

        $lote_id = date('YmdHis');
        $proximo_concurso = $ultimo_concurso + 1;
        $pdf_path = "downloads/lotofacil_$lote_id.pdf";
        $txt_path = "downloads/lotofacil_$lote_id.txt";

        // Salvar no banco
        $stmt = $pdo->prepare("INSERT INTO jogos_gerados (user_id, lote_id, concurso, jogos, pdf_path, txt_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $lote_id, $proximo_concurso, json_encode($jogos), $pdf_path, $txt_path]);

        // Gerar PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Jogos Gerados - Lotofacil', 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 12);
        foreach ($jogos as $index => $jogo) {
            $pdf->Cell(0, 10, 'Jogo ' . ($index + 1) . ': ' . implode(', ', $jogo), 0, 1);
        }
        $pdf->Output('F', $pdf_path);

        // Gerar TXT
        $txt_content = "Jogos Gerados - Lotofacil\n\n";
        foreach ($jogos as $index => $jogo) {
            $txt_content .= "Jogo " . ($index + 1) . ": " . implode(', ', $jogo) . "\n";
        }
        file_put_contents($txt_path, $txt_content);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="lotofacil_' . $lote_id . '.pdf"');
        readfile($pdf_path);
        exit;
    } catch (Exception $e) {
        error_log("Erro em processar_jogos.php: " . $e->getMessage(), 3, "erros.log");
        http_response_code(500);
        echo "Erro interno: " . $e->getMessage();
        exit;
    }
}
?>