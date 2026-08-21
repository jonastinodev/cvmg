<?php
// demander-suppression-cv.php — Demande la suppression d'un profil CV Express
// appartenant à l'utilisateur connecté. N'efface jamais la ligne : horodate
// la demande, ce qui suffit à exclure immédiatement le profil de recherche.php
// et profil-public.php. Le délai de traitement 48h reste un engagement humain.

require_once __DIR__ . '/exiger-connexion.php';
require_once __DIR__ . '/bdd.php';

header('Content-Type: application/json; charset=utf-8');

$entree = json_decode(file_get_contents('php://input'), true);
$cvId = $entree['cv_id'] ?? null;

if (!is_numeric($cvId)) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Identifiant manquant.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = bdd();

// Contrôle d'existence/propriété découplé de l'UPDATE : rowCount() sur un
// UPDATE reflète les lignes modifiées, pas les lignes matchées (bdd.php
// n'active pas PDO::MYSQL_ATTR_FOUND_ROWS). S'appuyer dessus pour le 404
// donnerait un faux 404 si l'horodatage ne change pas (résolution à la
// seconde, requêtes en double).
$stmtLecture = $pdo->prepare(
    "SELECT id, suppression_demandee_le FROM cv
     WHERE id = :id AND utilisateur_id = :uid AND type = 'express'"
);
$stmtLecture->execute([':id' => $cvId, ':uid' => $_SESSION['utilisateur_id']]);
$cv = $stmtLecture->fetch(PDO::FETCH_ASSOC);

if (!$cv) {
    http_response_code(404);
    echo json_encode(['erreur' => "Ce CV n'existe pas ou ne vous appartient pas."], JSON_UNESCAPED_UNICODE);
    exit;
}

// Idempotent : une demande déjà enregistrée n'est jamais réécrite, pour ne
// pas fausser la mesure du délai de traitement 48h avec un nouvel horodatage.
if ($cv['suppression_demandee_le'] !== null) {
    echo json_encode([
        'succes' => true,
        'date'   => date('d/m/Y', strtotime($cv['suppression_demandee_le'])),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmtEcriture = $pdo->prepare(
    "UPDATE cv SET suppression_demandee_le = NOW() WHERE id = :id"
);
$stmtEcriture->execute([':id' => $cv['id']]);

$stmtRelecture = $pdo->prepare('SELECT suppression_demandee_le FROM cv WHERE id = :id');
$stmtRelecture->execute([':id' => $cv['id']]);
$horodatage = $stmtRelecture->fetchColumn();

echo json_encode([
    'succes' => true,
    'date'   => date('d/m/Y', strtotime($horodatage)),
], JSON_UNESCAPED_UNICODE);
