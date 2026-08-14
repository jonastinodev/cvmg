<?php
// renommer-cv.php — Change le titre d'un CV appartenant à l'utilisateur connecté.

require_once __DIR__ . '/exiger-connexion.php';
require_once __DIR__ . '/bdd.php';

header('Content-Type: application/json; charset=utf-8');

function repondreErreur(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['erreur' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

$entree = json_decode(file_get_contents('php://input'), true);
$cvId = $entree['cv_id'] ?? null;
$titre = trim($entree['titre'] ?? '');

if (!$cvId) {
    repondreErreur('Identifiant manquant.');
}
if ($titre === '') {
    repondreErreur('Le nom ne peut pas être vide.');
}
// La colonne titre est un VARCHAR(150) : on coupe avant l'insertion plutôt que
// de laisser MySQL tronquer silencieusement.
$titre = mb_substr($titre, 0, 150);

$pdo = bdd();
$stmt = $pdo->prepare('UPDATE cv SET titre = :titre, date_maj = NOW()
                        WHERE id = :id AND utilisateur_id = :uid');
$stmt->execute([':titre' => $titre, ':id' => $cvId, ':uid' => $_SESSION['utilisateur_id']]);

if ($stmt->rowCount() === 0) {
    repondreErreur("Ce CV n'existe pas ou ne vous appartient pas.", 404);
}

echo json_encode(['succes' => true, 'titre' => $titre], JSON_UNESCAPED_UNICODE);
