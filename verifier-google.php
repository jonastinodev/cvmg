<?php
// verifier-google.php — Reçoit le jeton (JWT) renvoyé par Google, vérifie sa
// validité auprès de Google via son point d'accès tokeninfo, puis crée ou
// retrouve le compte correspondant et ouvre une session.
//
// Pas de dépendance Composer nécessaire ici : on interroge Google directement
// en curl, comme pour Gemini dans ocr.php.

session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function repondreErreur(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['erreur' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

$entree = json_decode(file_get_contents('php://input'), true);
$jeton = $entree['credential'] ?? null;

if (!$jeton) {
    repondreErreur('Jeton manquant.');
}

// --- 1. Vérification auprès de Google ---
// Google vérifie lui-même la signature et l'expiration du jeton, et nous
// renvoie son contenu déchiffré si tout est valide.
$ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($jeton));
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15]);
$reponseBrute = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$payload = json_decode($reponseBrute, true);

if ($httpCode !== 200 || !is_array($payload) || empty($payload['sub'])) {
    repondreErreur('Jeton invalide ou expiré.', 401);
}

// --- Vérification indispensable : ce jeton a-t-il bien été émis pour NOTRE application ? ---
// Sans ce contrôle, un jeton valide pour un tout autre site pourrait être
// réutilisé ici — c'est l'étape qui garantit que non.
if ($payload['aud'] !== GOOGLE_CLIENT_ID) {
    repondreErreur('Jeton non destiné à cette application.', 401);
}

$googleId = $payload['sub'];
$email = $payload['email'];
$nom = $payload['name'] ?? $email;
$photo = $payload['picture'] ?? null;

// --- 2. Créer ou retrouver le compte correspondant ---
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    repondreErreur('Connexion à la base impossible.', 500);
}

$stmt = $pdo->prepare(
    'INSERT INTO utilisateurs (google_id, email, nom_affichage, photo_url, date_creation, derniere_connexion)
     VALUES (:google_id, :email, :nom, :photo, NOW(), NOW())
     ON DUPLICATE KEY UPDATE nom_affichage = :nom2, photo_url = :photo2, derniere_connexion = NOW()'
);
$stmt->execute([
    ':google_id' => $googleId,
    ':email' => $email,
    ':nom' => $nom,
    ':photo' => $photo,
    ':nom2' => $nom,
    ':photo2' => $photo,
]);

$idUtilisateur = $pdo->query(
    'SELECT id FROM utilisateurs WHERE google_id = ' . $pdo->quote($googleId)
)->fetchColumn();

// --- 3. Ouvrir la session ---
$_SESSION['utilisateur_id'] = $idUtilisateur;
$_SESSION['utilisateur_email'] = $email;
$_SESSION['utilisateur_nom'] = $nom;

echo json_encode(['succes' => true, 'nom' => $nom], JSON_UNESCAPED_UNICODE);
