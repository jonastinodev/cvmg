<?php
// enregistrer-cv.php — Sauvegarde un CV (nouveau ou existant) sur le compte
// de l'utilisateur connecté. Reçoit la même structure de données que
// generer-pdf.php, plus un titre et, en cas de modification, l'id du CV.

require_once __DIR__ . '/exiger-connexion.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function repondreErreur(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['erreur' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

$entree = json_decode(file_get_contents('php://input'), true);
if (!is_array($entree) || empty($entree['donnees'])) {
    repondreErreur('Données manquantes.');
}

$titre = trim($entree['titre'] ?? '');
if ($titre === '') {
    $p = $entree['donnees']['personnel'] ?? [];
    $titre = trim(($p['titre_professionnel'] ?? 'CV') . ' — ' . ($p['nom'] ?? '') . ' ' . ($p['prenom'] ?? ''));
}
$donneesJson = json_encode($entree['donnees'], JSON_UNESCAPED_UNICODE);
$cvId = $entree['cv_id'] ?? null;

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    repondreErreur('Connexion à la base impossible.', 500);
}

$utilisateurId = $_SESSION['utilisateur_id'];

if ($cvId) {
    // Modification : on vérifie que ce CV appartient bien à l'utilisateur connecté
    // avant de le modifier — sans ça, n'importe qui pourrait écraser le CV d'un autre.
    $stmt = $pdo->prepare('UPDATE cv SET titre = :titre, donnees_json = :donnees, date_maj = NOW()
                            WHERE id = :id AND utilisateur_id = :uid');
    $stmt->execute([':titre' => $titre, ':donnees' => $donneesJson, ':id' => $cvId, ':uid' => $utilisateurId]);
    if ($stmt->rowCount() === 0) {
        repondreErreur("Ce CV n'existe pas ou ne vous appartient pas.", 404);
    }
    echo json_encode(['succes' => true, 'cv_id' => (int)$cvId], JSON_UNESCAPED_UNICODE);
} else {
    // Création
    $stmt = $pdo->prepare('INSERT INTO cv (utilisateur_id, titre, donnees_json, date_creation, date_maj)
                            VALUES (:uid, :titre, :donnees, NOW(), NOW())');
    $stmt->execute([':uid' => $utilisateurId, ':titre' => $titre, ':donnees' => $donneesJson]);
    echo json_encode(['succes' => true, 'cv_id' => (int)$pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);
}
