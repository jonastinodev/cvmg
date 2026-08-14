<?php
// supprimer-cv.php — Supprime un CV appartenant à l'utilisateur connecté.

require_once __DIR__ . '/exiger-connexion.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$entree = json_decode(file_get_contents('php://input'), true);
$cvId = $entree['cv_id'] ?? null;

if (!$cvId) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Identifiant manquant.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$stmt = $pdo->prepare('DELETE FROM cv WHERE id = :id AND utilisateur_id = :uid');
$stmt->execute([':id' => $cvId, ':uid' => $_SESSION['utilisateur_id']]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['erreur' => "Ce CV n'existe pas ou ne vous appartient pas."], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['succes' => true], JSON_UNESCAPED_UNICODE);
