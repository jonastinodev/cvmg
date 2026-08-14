<?php
// generer-pdf.php — Reçoit les données d'un CV (JSON en POST) et renvoie
// directement le fichier PDF à télécharger.

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/cv-template.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$entree = json_decode(file_get_contents('php://input'), true);

if (!is_array($entree) || empty($entree['personnel']['nom'])) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Le nom est obligatoire.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$html = genererCvHtml($entree);

$options = new Options();
$options->set('isRemoteEnabled', true); // nécessaire si une photo est chargée depuis une URL
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$nettoyer = fn($s) => preg_replace('/[^A-Za-z0-9_-]/', '', $s ?? '');
$morceaux = array_filter([$nettoyer($entree['personnel']['nom']), $nettoyer($entree['personnel']['prenom'] ?? '')]);
$nomFichier = 'CV_' . implode('_', $morceaux) . '.pdf';

$dompdf->stream($nomFichier, ['Attachment' => true]);
