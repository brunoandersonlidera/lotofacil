<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'fpdf/fpdf.php'; // Biblioteca para gerar PDF

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantidade_numeros = $_POST['quantidade_numeros'];
    $quantidade_jogos = $_POST['quantidade_jogos'];

    // Lógica para gerar jogos (adicione sua implementação aqui)
    $jogos = []; // Exemplo: array com os jogos gerados

    // Geração de PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Jogos Gerados - Lotofacil', 0, 1, 'C');
    // Adicione os jogos ao PDF...

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="jogos_lotofacil.pdf"');
    $pdf->Output('D', 'jogos_lotofacil.pdf');
    exit;
}
?>