<?php
// dupliquer-cv.php — Crée une copie d'un CV existant appartenant à l'utilisateur connecté.

require_once __DIR__ . '/exiger-connexion.php';
require_once __DIR__ . '/bdd.php';

header('Content-Type: application/json; charset=utf-8');

$entree = json_decode(file_get_contents('php://input'), true);
$cvId = $entree['cv_id'] ?? null;

if (!$cvId) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Identifiant manquant.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = bdd();

$stmt = $pdo->prepare('SELECT titre, donnees_json FROM cv WHERE id = :id AND utilisateur_id = :uid');
$stmt->execute([':id' => $cvId, ':uid' => $_SESSION['utilisateur_id']]);
$original = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$original) {
    http_response_code(404);
    echo json_encode(['erreur' => "Ce CV n'existe pas ou ne vous appartient pas."], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->prepare('INSERT INTO cv (utilisateur_id, titre, donnees_json, date_creation, date_maj)
                        VALUES (:uid, :titre, :donnees, NOW(), NOW())');
$stmt->execute([
    ':uid' => $_SESSION['utilisateur_id'],
    ':titre' => 'Copie de ' . $original['titre'],
    ':donnees' => $original['donnees_json'],
]);

echo json_encode(['succes' => true, 'cv_id' => (int)$pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);
