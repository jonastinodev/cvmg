<?php
// diag.php — Test isolé : PHP peut-il appeler Gemini ? Sans upload de fichier,
// pour éliminer toute variable et voir le message d'erreur exact.

header('Content-Type: text/plain; charset=utf-8');

echo "=== Diagnostic OCR CIN ===\n\n";
echo "Version PHP : " . phpversion() . "\n";
echo "Extension curl chargée : " . (extension_loaded('curl') ? 'OUI' : 'NON — c\'est le problème, active-la dans php.ini') . "\n";

if (!extension_loaded('curl')) {
    exit;
}

require_once __DIR__ . '/config.php';

echo "Modèle configuré : " . GEMINI_MODEL . "\n";
echo "Clé configurée (aperçu) : " . substr(GEMINI_API_KEY, 0, 12) . "...\n\n";

$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent';

$corps = json_encode([
    'contents' => [[ 'parts' => [['text' => 'Réponds juste "bonjour" en un mot.']] ]],
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-goog-api-key: ' . GEMINI_API_KEY,
    ],
    CURLOPT_POSTFIELDS => $corps,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_VERBOSE => false,
]);

echo "Appel de Gemini en cours...\n\n";
$reponse = curl_exec($ch);
$erreurCurl = curl_error($ch);
$numeroErreur = curl_errno($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP retourné par Gemini : $code\n";
echo "Numéro d'erreur cURL : $numeroErreur\n";
echo "Message d'erreur cURL : " . ($erreurCurl ?: '(aucun)') . "\n\n";
echo "Réponse brute :\n";
echo $reponse ?: '(vide — la requête a échoué avant toute réponse)';
echo "\n";
