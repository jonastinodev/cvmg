<?php
// charger-cv.php — Renvoie les données d'un CV enregistré (pour le rouvrir
// dans le formulaire). Vérifie que ce CV appartient bien à la personne connectée.

require_once __DIR__ . '/exiger-connexion.php';
require_once __DIR__ . '/bdd.php';

header('Content-Type: application/json; charset=utf-8');

$cvId = $_GET['id'] ?? null;
if (!$cvId) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Identifiant manquant.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = bdd();

$stmt = $pdo->prepare('SELECT titre, donnees_json FROM cv WHERE id = :id AND utilisateur_id = :uid');
$stmt->execute([':id' => $cvId, ':uid' => $_SESSION['utilisateur_id']]);
$ligne = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ligne) {
    http_response_code(404);
    echo json_encode(['erreur' => "Ce CV n'existe pas ou ne vous appartient pas."], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'succes' => true,
    'titre' => $ligne['titre'],
    'donnees' => json_decode($ligne['donnees_json'], true),
], JSON_UNESCAPED_UNICODE);
