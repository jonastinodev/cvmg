<?php
// apercu-pdf.php — Diffuse le PDF d'un CV enregistré : en ligne dans le
// navigateur par défaut (utilisé comme source d'un <iframe> pour que « Voir
// un CV » affiche exactement ce que produit le téléchargement), ou en pièce
// jointe si &telecharger=1. Dans les deux cas, même chaîne genererCvHtml()
// -> dompdf que generer-pdf.php : aucune divergence possible entre l'aperçu
// et le fichier téléchargé, contrairement à l'ancien aperçu en HTML/iframe
// (un navigateur affiche le CV en défilement continu, dompdf le pagine
// réellement — les deux ne peuvent pas être garantis identiques).

require_once __DIR__ . '/session.php';
if (empty($_SESSION['utilisateur_id'])) {
    http_response_code(401);
    exit('Non connecté.');
}
require_once __DIR__ . '/bdd.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/cv-template.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$cvId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$cvId) {
    http_response_code(400);
    exit('Identifiant manquant.');
}

$pdo = bdd();
$stmt = $pdo->prepare('SELECT titre, donnees_json FROM cv WHERE id = :id AND utilisateur_id = :uid');
$stmt->execute([':id' => $cvId, ':uid' => $_SESSION['utilisateur_id']]);
$ligne = $stmt->fetch();

if (!$ligne) {
    http_response_code(404);
    exit("Ce CV n'existe pas ou ne vous appartient pas.");
}

$donnees = json_decode($ligne['donnees_json'], true) ?: [];
$html = genererCvHtml($donnees);

$options = new Options();
$options->set('isRemoteEnabled', true); // nécessaire si une photo est chargée depuis une URL
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$nettoyer = fn($s) => preg_replace('/[^A-Za-z0-9_-]/', '', $s ?? '');
$p = $donnees['personnel'] ?? [];
$morceaux = array_filter([$nettoyer($p['nom'] ?? ''), $nettoyer($p['prenom'] ?? '')]);
$nomFichier = 'CV_' . implode('_', $morceaux ?: ['cv']) . '.pdf';

$telecharger = !empty($_GET['telecharger']);
$dompdf->stream($nomFichier, ['Attachment' => $telecharger]);
