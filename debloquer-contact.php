<?php
// debloquer-contact.php — Encaissement et révélation du numéro (Story 3.2,
// FR-5/FR-6). Réservé à une session opérateur : c'est la seule voie par
// laquelle un numéro complet quitte le serveur depuis la recherche — distinct
// de profil-public.php qui l'affiche déjà sans condition une fois la fiche
// remise au travailleur (NFR3, ne pas toucher à cette page-là).
// Pas d'identité employeur à vérifier (choix acté, Epic 3) : l'opérateur
// encaisse en espèces puis clique — le clic EST la confirmation (NFR2, pas
// de passerelle de paiement en ligne).

require_once __DIR__ . '/exiger-connexion.php';
require_once __DIR__ . '/bdd.php';
require_once __DIR__ . '/constantes-express.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['est_operateur'])) {
    http_response_code(403);
    echo json_encode(['erreur' => 'Réservé aux opérateurs.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$entree = json_decode(file_get_contents('php://input'), true);
$cvId   = $entree['cv_id'] ?? null;

if (!is_numeric($cvId)) {
    http_response_code(400);
    echo json_encode(['erreur' => 'Identifiant manquant.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = bdd();

// Pas de filtre sur utilisateur_id ici : contrairement à la gestion d'un CV
// (CLAUDE.md), le déblocage doit fonctionner sur un profil enregistré par
// n'importe quel opérateur — c'est le principe même de la recherche
// mutualisée entre cybercafés (Epic 1). Les mêmes conditions de visibilité
// que recherche.php sont revérifiées ici, pour ne jamais débloquer un profil
// retiré entre l'instant de la recherche et le clic sur « Débloquer ».
$stmt = $pdo->prepare(
    "SELECT donnees_json FROM cv
     WHERE id = :id AND type = 'express' AND est_public = 1
       AND consentement_horodatage IS NOT NULL
       AND suppression_demandee_le IS NULL"
);
$stmt->execute([':id' => $cvId]);
$cv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cv) {
    http_response_code(404);
    echo json_encode(['erreur' => "Ce profil n'est plus disponible."], JSON_UNESCAPED_UNICODE);
    exit;
}

$telephone = trim(json_decode($cv['donnees_json'], true)['personnel']['telephone'] ?? '');

if ($telephone === '') {
    http_response_code(500);
    echo json_encode(['erreur' => 'Numéro introuvable pour ce profil.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Enregistré au moment de la confirmation, jamais avant : c'est cette ligne,
// pas l'affichage écran, qui rend la garantie de non-fuite (FR-7) vérifiable
// après coup. Aucune contrainte d'unicité : un même profil peut être
// débloqué plusieurs fois par des employeurs différents.
$stmtEcriture = $pdo->prepare(
    'INSERT INTO deblocages (cv_id, operateur_id, horodatage, prix_ar)
     VALUES (:cv_id, :operateur_id, NOW(), :prix)'
);
$stmtEcriture->execute([
    ':cv_id'        => (int)$cvId,
    ':operateur_id' => (int)$_SESSION['utilisateur_id'],
    ':prix'         => PRIX_DEBLOCAGE_AR,
]);

echo json_encode(['succes' => true, 'telephone' => $telephone], JSON_UNESCAPED_UNICODE);
