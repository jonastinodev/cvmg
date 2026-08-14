<?php
// enregistrer.php — Reçoit les données validées par l'utilisateur (bouton
// "Enregistrer les données" sur ocr.html) et les insère dans la base MySQL.

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function repondreErreur(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['erreur' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// Accepte du JSON (fetch avec Content-Type: application/json) ou un formulaire classique.
$entree = json_decode(file_get_contents('php://input'), true);
if (!is_array($entree)) {
    $entree = $_POST;
}

$champsRequis = ['nom', 'prenom', 'dateNaissance', 'lieuNaissance', 'adresse', 'numero', 'profession'];
foreach ($champsRequis as $champ) {
    if (empty($entree[$champ])) {
        repondreErreur("Champ manquant ou vide : $champ");
    }
}

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

$sql = "INSERT INTO cin_enregistrements
        (nom, prenom, date_naissance, lieu_naissance, adresse, numero_cin, profession, date_creation)
        VALUES (:nom, :prenom, :date_naissance, :lieu_naissance, :adresse, :numero_cin, :profession, NOW())";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nom'            => $entree['nom'],
        ':prenom'         => $entree['prenom'],
        ':date_naissance' => $entree['dateNaissance'],
        ':lieu_naissance' => $entree['lieuNaissance'],
        ':adresse'        => $entree['adresse'],
        ':numero_cin'     => $entree['numero'],
        ':profession'     => $entree['profession'],
    ]);
} catch (PDOException $e) {
    // Cas fréquent : numero_cin déjà enregistré (contrainte UNIQUE)
    repondreErreur("Enregistrement impossible (numéro CIN déjà existant ?).", 409);
}

echo json_encode(['succes' => true, 'id' => $pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);
