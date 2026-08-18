<?php
// generer-texte.php — Rédige avec Gemini une courte description pour le CV :
// soit le profil d'accroche (type "profil"), soit les missions d'une expérience
// (type "mission"). La clé API reste côté serveur, comme dans ocr.php.
//
// Volontairement accessible sans compte : le parcours de création de CV
// fonctionne en mode invité (cf. apercu-cv.php / generer-pdf.php). L'endpoint
// est donc borné pour ne pas devenir un accès LLM gratuit :
//   - seules les clés de contexte listées ci-dessous sont lues, tronquées court ;
//   - la réponse est plafonnée en jetons (inutilisable pour du texte long) ;
//   - le nombre d'appels par session est limité.

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';

header('Content-Type: application/json; charset=utf-8');

function repondreErreur(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['erreur' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// --- 1. Limitation du nombre d'appels par session ---
const IA_MAX_APPELS   = 25;   // par fenêtre
const IA_FENETRE_SEC  = 3600; // 1 heure

$maintenant = time();
$_SESSION['ia_appels'] = array_values(array_filter(
    $_SESSION['ia_appels'] ?? [],
    fn($t) => $t > $maintenant - IA_FENETRE_SEC
));
if (count($_SESSION['ia_appels']) >= IA_MAX_APPELS) {
    repondreErreur("Trop de générations en peu de temps. Réessayez dans quelques minutes.", 429);
}

// --- 2. Lecture et validation de l'entrée ---
$entree = json_decode(file_get_contents('php://input'), true);
if (!is_array($entree)) {
    repondreErreur("Données invalides.");
}

$type = $entree['type'] ?? '';
if (!in_array($type, ['profil', 'mission'], true)) {
    repondreErreur("Type de génération inconnu.");
}

// Seules ces clés sont lues, et chacune est tronquée : le contexte transmis à
// l'IA reste court, ce qui empêche de détourner l'endpoint via un champ long.
$clesAutorisees = [
    'profil'  => ['titrePro', 'ville', 'experiences', 'competences', 'qualites', 'sansExperience'],
    'mission' => ['poste', 'employeur', 'lieu', 'debut', 'fin', 'actuel'],
];

function nettoyer($valeur, int $maxCar = 120): string {
    if (is_array($valeur)) {
        $valeur = implode(', ', array_slice(array_map('strval', $valeur), 0, 12));
    }
    $valeur = trim(preg_replace('/\s+/u', ' ', (string) $valeur));
    return mb_substr($valeur, 0, $maxCar);
}

$contexteBrut = is_array($entree['contexte'] ?? null) ? $entree['contexte'] : [];
$ctx = [];
foreach ($clesAutorisees[$type] as $cle) {
    if (isset($contexteBrut[$cle])) {
        $ctx[$cle] = is_bool($contexteBrut[$cle]) ? $contexteBrut[$cle] : nettoyer($contexteBrut[$cle]);
    }
}

// --- 3. Construction de la consigne ---
$reglesCommunes = <<<TXT
Règles impératives :
- Écris en français simple et courant, compréhensible par tous.
- Reste factuel : n'invente aucun diplôme, aucune entreprise, aucune durée, aucun chiffre qui ne figure pas dans les informations ci-dessus.
- Écris à la première personne sans dire "je" (style CV), ou à l'infinitif pour les missions.
- Pas de superlatif creux ("expert", "passionné par l'excellence"), pas d'anglicismes inutiles.
- Réponds UNIQUEMENT avec le texte demandé, sans guillemets, sans titre, sans puce, sans balise markdown, sans commentaire.
TXT;

if ($type === 'profil') {
    $sansExp = !empty($ctx['sansExperience']);
    $lignes = [];
    if (!empty($ctx['titrePro']))    $lignes[] = "Poste recherché : {$ctx['titrePro']}";
    if (!empty($ctx['ville']))       $lignes[] = "Ville : {$ctx['ville']}";
    if (!empty($ctx['experiences'])) $lignes[] = "Expériences : {$ctx['experiences']}";
    if (!empty($ctx['competences'])) $lignes[] = "Compétences : {$ctx['competences']}";
    if (!empty($ctx['qualites']))    $lignes[] = "Qualités : {$ctx['qualites']}";
    if ($sansExp)                    $lignes[] = "Cette personne n'a pas encore d'expérience professionnelle.";

    if (count($lignes) === 0) {
        repondreErreur("Renseignez au moins le métier recherché avant de générer la description.");
    }

    $infos = implode("\n", $lignes);
    $angle = $sansExp
        ? "La personne débute : mets en avant la motivation, le sérieux et les qualités personnelles, sans jamais laisser croire qu'elle a de l'expérience."
        : "Appuie-toi sur les expériences et compétences réellement listées.";

    $prompt = <<<TXT
Tu rédiges l'accroche d'un CV pour une personne à Madagascar, souvent peu diplômée, qui cherche un emploi.

Informations disponibles :
$infos

Rédige une accroche de 2 à 3 phrases (40 à 55 mots maximum) qui donne envie à un employeur de lire la suite.
$angle

$reglesCommunes
TXT;

} else { // mission
    $lignes = [];
    if (!empty($ctx['poste']))     $lignes[] = "Poste : {$ctx['poste']}";
    if (!empty($ctx['employeur'])) $lignes[] = "Employeur : {$ctx['employeur']}";
    if (!empty($ctx['lieu']))      $lignes[] = "Lieu : {$ctx['lieu']}";
    if (!empty($ctx['debut']))     $lignes[] = "Début : {$ctx['debut']}";
    if (!empty($ctx['fin']))       $lignes[] = "Fin : {$ctx['fin']}";
    if (!empty($ctx['actuel']))    $lignes[] = "Poste toujours occupé aujourd'hui.";

    if (empty($ctx['poste'])) {
        repondreErreur("Renseignez le poste occupé avant de générer la description.");
    }

    $infos = implode("\n", $lignes);
    $prompt = <<<TXT
Tu rédiges la description des missions d'une expérience professionnelle, dans le CV d'une personne à Madagascar.

Informations disponibles :
$infos

Rédige 2 à 4 missions typiques et crédibles de ce poste, une par ligne, chacune commençant par un verbe à l'infinitif (ex : "Accueillir et conseiller les clients").
Pas de tiret ni de puce en début de ligne : juste le texte, une mission par ligne.
Reste sur des tâches que ce poste implique réellement.

$reglesCommunes
TXT;
}

// --- 4. Appel à Gemini (clé gardée côté serveur, jamais exposée au navigateur) ---
// gemini-3.1-flash-lite : rapide (~3s), sans mode raisonnement, adapté aux textes
// courts de CV. GEMINI_MODEL pointe sur gemini-flash-latest qui résout désormais
// sur gemini-3.7-flash (modèle raisonnement) : inutilement lent et coûteux ici.
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent';

$corps = json_encode([
    'contents' => [[ 'parts' => [['text' => $prompt]] ]],
    'generationConfig' => [
        // Plafond volontairement bas : suffisant pour une accroche de CV,
        // insuffisant pour détourner l'endpoint vers de la génération longue.
        'maxOutputTokens' => 300,
        'temperature'     => 0.9, // laisse de la variété si l'utilisateur régénère
        'thinkingConfig'  => ['thinkingBudget' => 0], // désactive le raisonnement (inutile ici, ~3x plus lent)
    ],
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
    CURLOPT_TIMEOUT => 45,
]);
$reponseBrute = curl_exec($ch);
$erreurCurl   = curl_error($ch);
$httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($reponseBrute === false) {
    repondreErreur("Erreur réseau vers l'IA : $erreurCurl", 502);
}

$reponse = json_decode($reponseBrute, true);
if ($httpCode !== 200 || !isset($reponse['candidates'][0]['content']['parts'][0]['text'])) {
    repondreErreur("L'IA n'a pas pu répondre. Réessayez dans un instant.", 502);
}

// --- 5. Nettoyage du texte renvoyé ---
$texte = trim($reponse['candidates'][0]['content']['parts'][0]['text']);
$texte = preg_replace('/^```(?:\w+)?\s*|\s*```$/m', '', $texte); // garde-fou markdown
$texte = preg_replace('/^\s*[-*•]\s*/mu', '', $texte);           // puces éventuelles
$texte = trim(preg_replace('/\n{3,}/', "\n\n", $texte), " \t\n\"«»");

if ($texte === '') {
    repondreErreur("L'IA a renvoyé une réponse vide. Réessayez.", 502);
}

$_SESSION['ia_appels'][] = $maintenant;

echo json_encode(['texte' => $texte], JSON_UNESCAPED_UNICODE);
