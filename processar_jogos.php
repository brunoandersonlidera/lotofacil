<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'vendor/fpdf/fpdf.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

define('PRIMOS', [2, 3, 5, 7, 11, 13, 17, 19, 23]);
define('PARES', [2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24]);
define('IMPARES', [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25]);
define('TRIOS_COMUNS', [[20, 21, 22], [23, 24, 25]]);
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
        $trio = TRIOS_COMUNS[array_rand(TRIOS_COMUNS)];
        foreach ($trio as $n) {
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

    if (in_array('clustering', $estrategias)) {
        $zonas = array_chunk(range(1, 25), 5);
        $freq_por_zona = array_map(function($zona) use ($freq_dezenas) {
            return array_sum(array_map(fn($n) => $freq_dezenas[$n] ?? 0, $zona));
        }, $zonas);
        array_multisort($freq_por_zona, SORT_DESC, $zonas);
        foreach (array_slice($zonas, 0, 2) as $zona) {
            foreach ($zona as $n) {
                if (in_array($n, $disponiveis) && count($jogo) < $quantidade_numeros) {
                    $jogo[] = $n;
                    $disponiveis = array_diff($disponiveis, [$n]);
                }
            }
        }
    }

    while (count($jogo) < $quantidade_numeros && !empty($disponiveis)) {
        $pares_atuais = count(array_intersect($jogo, PARES));
        $alvo_pares = rand(7, 8);
        if ($pares_atuais < $alvo_pares && count(array_intersect(PARES, $disponiveis)) > 0) {
            $n = array_shift(array_values(array_intersect(PARES, $disponiveis)));
        } elseif (count($jogo) - $pares_atuais < ($quantidade_numeros - $alvo_pares) && count(array_intersect(IMPARES, $disponiveis)) > 0) {
            $n = array_shift(array_values(array_intersect(IMPARES, $disponiveis)));
        } else {
            $n = array_shift($disponiveis);
        }
        $jogo[] = $n;
        $disponiveis = array_diff($disponiveis, [$n]);
    }

    sort($jogo);
    if (in_array('soma', $estrategias)) {
        $soma = array_sum($jogo);
        while (($soma < 180 || $soma > 220) && !empty($disponiveis)) {
            array_pop($jogo);
            $n = array_shift($disponiveis);
            $jogo[] = $n;
            sort($jogo);
            $soma = array_sum($jogo);
        }
    }

    while (in_array($jogo, $jogos_anteriores) && !empty($disponiveis)) {
        array_pop($jogo);
        $n = array_shift($disponiveis);
        $jogo[] = $n;
        sort($jogo);
    }

    return $jogo;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $quantidade_numeros = filter_input(INPUT_POST, 'quantidade_numeros', FILTER_VALIDATE_INT, ['options' => ['min_range' => 15, 'max_range' => 20]]);
        $quantidade_jogos = filter_input(INPUT_POST, 'quantidade_jogos', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $numeros_fixos = !empty($_POST['numeros_fixos']) ? array_map('intval', explode(', ', $_POST['numeros_fixos'])) : [];
        $numeros_excluidos = !empty($_POST['numeros_excluidos']) ? array_map('intval', explode(', ', $_POST['numeros_excluidos'])) : [];
        $estrategias = array_filter($_POST['estrategias']) ?: ['frequencia', 'sequencias', 'soma'];

        if ($quantidade_numeros === false || $quantidade_jogos === false) {
            throw new Exception("Quantidade inválida.");
        }

        $pdo = getDB();
        $stmt = $pdo->query("SELECT numeros FROM resultados");
        $jogos_anteriores = array_map(function($n) { return json_decode($n); }, $stmt->fetchAll(PDO::FETCH_COLUMN));
        list($ultimo_concurso, $ultimo_sorteio) = get_ultimo_concurso($pdo);

        $jogos = [];
        $tentativas = 0;
        $max_tentativas = $quantidade_jogos * 20;

        if (in_array('desdobramento', $estrategias)) {
            $base = $numeros_fixos;
            $disponiveis = array_diff(range(1, 25), $numeros_excluidos, $base);
            $freq_dezenas = analisar_frequencia_ultimos_n($pdo);
            arsort($freq_dezenas);
            $base = array_merge($base, array_slice(array_keys($freq_dezenas), 0, 18 - count($base)));
            while (count($jogos) < $quantidade_jogos && count($base) >= $quantidade_numeros) {
                $jogo = array_rand(array_flip($base), $quantidade_numeros);
                sort($jogo);
                if (!in_array($jogo, $jogos) && !in_array($jogo, $jogos_anteriores)) {
                    $jogos[] = $jogo;
                }
            }
        } else {
            while (count($jogos) < $quantidade_jogos && $tentativas < $max_tentativas) {
                $jogo = gerar_jogo($pdo, $quantidade_numeros, $numeros_fixos, $numeros_excluidos, $estrategias, $jogos_anteriores, $ultimo_sorteio);
                if (!in_array($jogo, $jogos)) {
                    $jogos[] = $jogo;
                }
                $tentativas++;
            }
        }

        if (count($jogos) < $quantidade_jogos) {
            throw new Exception("Não foi possível gerar todos os jogos únicos.");
        }

        $lote_id = date('YmdHis');
        $proximo_concurso = $ultimo_concurso + 1;
        $pdf_path = "downloads/lotofacil_$lote_id.pdf";
        $txt_path = "downloads/lotofacil_$lote_id.txt";

        $stmt = $pdo->prepare("INSERT INTO jogos_gerados (user_id, lote_id, concurso, jogos, pdf_path, txt_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $lote_id, $proximo_concurso, json_encode($jogos), $pdf_path, $txt_path]);

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Relatorio de Apostas - Lotofacil', 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, "Lote: $lote_id | Concurso: $proximo_concurso", 0, 1);
        $pdf->Cell(0, 10, "Data: " . date('d/m/Y H:i:s'), 0, 1);
        $pdf->Ln(10);
        foreach ($jogos as $index => $jogo) {
            $pares = count(array_intersect($jogo, PARES));
            $primos = count(array_intersect($jogo, PRIMOS));
            $soma = array_sum($jogo);
            $pdf->Cell(0, 10, sprintf("JOGO %03d", $index + 1), 0, 1);
            $pdf->Cell(0, 10, "Numeros: " . implode(', ', $jogo), 0, 1);
            $pdf->Cell(0, 10, "Pares: $pares, Impares: " . ($quantidade_numeros - $pares) . ", Primos: $primos, Soma: $soma", 0, 1);
            $pdf->Ln(5);
        }
        $total_apostas = TABELA_PRECOS[$quantidade_numeros] * count($jogos);
        $pdf->Cell(0, 10, "Total de Jogos: " . count($jogos) . " | Custo Total: R$ " . number_format($total_apostas, 2, ',', '.'), 0, 1);
        $pdf->Output('F', $pdf_path);

        $txt_content = "";
        foreach ($jogos as $jogo) {
            $txt_content .= implode(', ', $jogo) . "\n";
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