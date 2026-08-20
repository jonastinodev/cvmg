<?php
// enregistrer-express.php — Reçoit les données du parcours Express (JSON),
// vérifie que l'appelant est un opérateur authentifié, puis insère le CV
// dans la table cv avec type='express' et est_public=1.

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/bdd.php';

header('Content-Type: application/json; charset=utf-8');

function repondreErreur(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['erreur' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

// --- Vérification : session valide + compte opérateur ---
if (empty($_SESSION['utilisateur_id'])) {
    repondreErreur('Authentification requise.', 401);
}
if (empty($_SESSION['est_operateur'])) {
    repondreErreur('Accès réservé aux opérateurs cybercafé.', 403);
}

// --- Lecture et validation du corps JSON ---
$entree = json_decode(file_get_contents('php://input'), true);
if (!is_array($entree)) {
    repondreErreur('Corps de requête JSON invalide.');
}

$nom    = trim($entree['nom']    ?? '');
$prenom = trim($entree['prenom'] ?? '');
$cin    = trim($entree['cin']    ?? '');
$metier = trim($entree['metier'] ?? '');
$rayon  = isset($entree['rayon']) ? (int)$entree['rayon'] : null;

if ($nom === '')        repondreErreur('Le nom est obligatoire.');
if ($prenom === '')     repondreErreur('Le prénom est obligatoire.');
if ($metier === '')     repondreErreur('Le métier est obligatoire.');
if ($rayon === null)    repondreErreur('Le rayon est obligatoire.');

// --- Construction du donnees_json (même structure que le CV complet) ---
// Les champs absents (téléphone, email…) restent vides : profil-public.php
// les affichera uniquement s'ils sont renseignés.
$donnees = [
    'modele' => 'classique',
    'personnel' => [
        'nom'                 => $nom,
        'prenom'              => $prenom,
        'titre_professionnel' => $metier,
        'cin_numero'          => $cin,
        'profil_court'        => '',
        'telephone'           => '',
        'email'               => '',
        'ville'               => '',
        'adresse'             => '',
        'date_naissance'      => '',
        'permis_conduire'     => false,
        'photo_url'           => '',
    ],
    'experiences'    => [],
    'formations'     => [],
    'competences'    => [],
    'langues'        => [],
    'complementaire' => [],
];

// --- INSERT en base ---
// utilisateur_id = l'opérateur qui crée le CV (pas le demandeur d'emploi)
// est_public = 1 → accessible via profil-public.php sans connexion
$pdo  = bdd();
$stmt = $pdo->prepare(
    'INSERT INTO cv
       (utilisateur_id, titre, donnees_json, date_creation, date_maj, type, rayon_km, est_public)
     VALUES
       (:uid, :titre, :donnees, NOW(), NOW(), :type, :rayon, 1)'
);
$stmt->execute([
    ':uid'    => (int)$_SESSION['utilisateur_id'],
    ':titre'  => $metier,
    ':donnees'=> json_encode($donnees, JSON_UNESCAPED_UNICODE),
    ':type'   => 'express',
    ':rayon'  => $rayon,
]);

$id = (int)$pdo->lastInsertId();

echo json_encode([
    'succes' => true,
    'id'     => $id,
    'url'    => 'profil-public.php?id=' . $id,
], JSON_UNESCAPED_UNICODE);
