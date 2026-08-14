<?php
// ocr.php — Reçoit 1 PDF (clé "file1") ou 2 photos recto/verso ("file1" + "file2")
// d'une CIN malgache, les envoie à Gemini pour extraction, et renvoie un JSON
// prêt à remplir le formulaire (ocr.html).

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function repondreErreur(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['erreur' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// --- 1. Récupération des fichiers envoyés ---
$fichiers = [];
foreach (['file1', 'file2'] as $cle) {
    if (isset($_FILES[$cle]) && $_FILES[$cle]['error'] === UPLOAD_ERR_OK) {
        $fichiers[] = $_FILES[$cle];
    }
}

if (count($fichiers) === 0) {
    repondreErreur("Aucun fichier valide reçu (attendu : 1 PDF ou 2 images).");
}
if (count($fichiers) > 2) {
    repondreErreur("Trop de fichiers envoyés (1 PDF ou 2 images maximum).");
}

$typesAutorises = [
    'application/pdf' => 'application/pdf',
    'image/jpeg'       => 'image/jpeg',
    'image/jpg'        => 'image/jpeg',
    'image/png'        => 'image/png',
    'image/webp'       => 'image/webp',
];

// --- 2. Construction des parties du message pour Gemini ---
$parties = [];
$TAILLE_MAX = 8 * 1024 * 1024; // 8 Mo par fichier
foreach ($fichiers as $fichier) {
    if (($fichier['size'] ?? 0) > $TAILLE_MAX) {
        repondreErreur("Fichier trop volumineux (8 Mo maximum par fichier).");
    }
    // Type réel déduit du CONTENU du fichier (finfo), et non de l'en-tête MIME
    // envoyé par le navigateur, qui est falsifiable.
    $type = (new finfo(FILEINFO_MIME_TYPE))->file($fichier['tmp_name']);
    if (!isset($typesAutorises[$type])) {
        repondreErreur("Type de fichier non supporté : $type");
    }
    $contenu = file_get_contents($fichier['tmp_name']);
    if ($contenu === false) {
        repondreErreur("Impossible de lire le fichier envoyé.");
    }
    $parties[] = [
        'inline_data' => [
            'mime_type' => $typesAutorises[$type],
            'data' => base64_encode($contenu),
        ],
    ];
}

$modeDeuxImages = count($fichiers) === 2;

$consigne = $modeDeuxImages
    ? "Voici le RECTO et le VERSO d'une carte d'identité nationale malgache (CIN / Kara-panondrom-pirenena). Combine les informations des deux faces."
    : "Voici une carte d'identité nationale malgache (CIN / Kara-panondrom-pirenena) en PDF.";

$prompt = <<<TXT
$consigne

Extrait UNIQUEMENT les champs suivants et réponds avec UN SEUL objet JSON valide, sans texte autour, sans balises markdown :

{
  "nom": "",
  "prenom": "",
  "dateNaissance": "",
  "lieuNaissance": "",
  "adresse": ""
}

Règles :
- "nom" : ANARANA, tel qu'écrit sur la carte, en majuscules.
- "prenom" : FANAMPIN'ANARANA.
- "dateNaissance" : TERAKA TAMIN'NY, au format AAAA-MM-JJ (convertis le format si besoin).
- "lieuNaissance" : lieu de naissance (TAO/a). Corrige les fautes de frappe/OCR évidentes et écris le nom de la localité malgache en majuscules (ex: "foanarantsoa" -> "FIANARANTSOA").
- "adresse" : FONENANA, adresse actuelle si présente sur la carte.
- Si un champ est illisible ou absent, mets une chaîne vide "".
- Ne renvoie rien d'autre que ce JSON.
TXT;

array_unshift($parties, ['text' => $prompt]);

// --- 3. Appel à l'API Gemini (clé gardée côté serveur, jamais exposée au navigateur) ---
$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent';

$corps = json_encode([
    'contents' => [[ 'parts' => $parties ]],
], JSON_UNESCAPED_UNICODE);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-goog-api-key: ' . GEMINI_API_KEY,
    ],
    CURLOPT_POSTFIELDS => $corps,
    CURLOPT_TIMEOUT => 60,
]);
$reponseBrute = curl_exec($ch);
$erreurCurl   = curl_error($ch);
$httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($reponseBrute === false) {
    repondreErreur("Erreur réseau vers Gemini : $erreurCurl", 502);
}

$reponse = json_decode($reponseBrute, true);

if ($httpCode !== 200 || !isset($reponse['candidates'][0]['content']['parts'][0]['text'])) {
    repondreErreur("Réponse Gemini invalide (HTTP $httpCode) : " . substr($reponseBrute, 0, 300), 502);
}

$texteBrut = $reponse['candidates'][0]['content']['parts'][0]['text'];

// Gemini renvoie parfois le JSON entouré de ```json ... ``` : on nettoie avant de parser.
$texteNettoye = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($texteBrut));
$donnees = json_decode($texteNettoye, true);

if (!is_array($donnees)) {
    repondreErreur("Impossible d'interpréter la réponse de l'IA.", 502);
}

// --- 4. Normalisation finale : on garantit toujours les mêmes clés pour le formulaire ---
function normaliserDate(string $date): string {
    $date = trim($date);
    if ($date === '') return '';
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return $date;
    if (preg_match('#^(\d{1,2})[/-](\d{1,2})[/-](\d{4})$#', $date, $m)) {
        return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
    }
    return $date; // format non reconnu : renvoyé tel quel, l'utilisateur pourra corriger
}

echo json_encode([
    'nom'            => $donnees['nom'] ?? '',
    'prenom'         => $donnees['prenom'] ?? '',
    'dateNaissance'  => normaliserDate($donnees['dateNaissance'] ?? ''),
    'lieuNaissance'  => $donnees['lieuNaissance'] ?? '',
    'adresse'        => $donnees['adresse'] ?? '',
], JSON_UNESCAPED_UNICODE);
