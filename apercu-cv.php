<?php
// apercu-cv.php — Renvoie le CV en HTML, généré avec EXACTEMENT le même gabarit
// que le PDF (cv-template.php). Ce que l'utilisateur voit est donc fidèle au
// fichier qu'il téléchargera ensuite.

require_once __DIR__ . '/cv-template.php';

$entree = json_decode(file_get_contents('php://input'), true);

if (!is_array($entree)) {
    http_response_code(400);
    echo 'Données invalides.';
    exit;
}

header('Content-Type: text/html; charset=utf-8');
echo genererCvHtml($entree);
