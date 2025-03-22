<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'vendor/fpdf/fpdf.php'; // Novo caminho para fpdf.php

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $quantidade_numeros = (int)$_POST['quantidade_numeros'];
        $quantidade_jogos = (int)$_POST['quantidade_jogos'];

        // Validação básica
        if ($quantidade_numeros < 15 || $quantidade_numeros > 20 || $quantidade_jogos < 1) {
            throw new Exception("Quantidade inválida.");
        }

        // Lógica para gerar jogos (exemplo simples)
        $jogos = [];
        for ($i = 0; $i < $quantidade_jogos; $i++) {
            $numeros = range(1, 25);
            shuffle($numeros);
            $jogos[] = array_slice($numeros, 0, $quantidade_numeros);
        }

        // Criar PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Jogos Gerados - Lotofacil', 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 12);
        foreach ($jogos as $index => $jogo) {
            $pdf->Cell(0, 10, 'Jogo ' . ($index + 1) . ': ' . implode(', ', $jogo), 0, 1);
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="jogos_lotofacil.pdf"');
        $pdf->Output('D', 'jogos_lotofacil.pdf');
        exit;
    } catch (Exception $e) {
        error_log("Erro em processar_jogos.php: " . $e->getMessage(), 3, "erros.log");
        http_response_code(500);
        echo "Erro interno: " . $e->getMessage();
        exit;
    }
} else {
    http_response_code(405);
    echo "Método não permitido.";
    exit;
}
?>