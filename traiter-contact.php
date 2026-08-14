<?php
// traiter-contact.php — Reçoit le formulaire de contact et envoie un e-mail.
// Fonctionne avec la fonction mail() native de PHP, disponible par défaut
// sur la plupart des hébergements Plesk (à vérifier une fois en ligne —
// sur XAMPP en local, mail() ne fonctionne pas sans configuration SMTP
// supplémentaire, c'est normal).

header('Content-Type: application/json; charset=utf-8');

// --- À REMPLIR avant mise en ligne ---
define('EMAIL_DESTINATAIRE', 'contact@cvmg.mg');

function repondreErreur(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['erreur' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

$entree = json_decode(file_get_contents('php://input'), true);
if (!is_array($entree)) {
    repondreErreur('Requête invalide.');
}

$nom = trim($entree['nom'] ?? '');
$email = trim($entree['email'] ?? '');
$message = trim($entree['message'] ?? '');

if ($nom === '' || $email === '' || $message === '') {
    repondreErreur('Tous les champs sont obligatoires.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    repondreErreur('Adresse e-mail invalide.');
}

$sujet = 'Nouveau message CVMG — de ' . $nom;
$corps = "Nom : $nom\nE-mail : $email\n\nMessage :\n$message";
$entetes = "From: CVMG <no-reply@cvmg.mg>\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8";

$envoye = mail(EMAIL_DESTINATAIRE, $sujet, $corps, $entetes);

if (!$envoye) {
    repondreErreur("L'envoi a échoué. Vérifie la configuration mail() de l'hébergement.", 500);
}

echo json_encode(['succes' => true], JSON_UNESCAPED_UNICODE);
