<?php
// session.php — Démarrage de session durci, à inclure AVANT tout accès à $_SESSION.
// Centralise les paramètres du cookie de session pour tout le site :
//  - httponly : le cookie n'est pas lisible en JavaScript (protège du vol par XSS).
//  - secure   : le cookie n'est transmis qu'en HTTPS (activé automatiquement en prod).
//  - samesite : Lax bloque l'envoi du cookie sur les requêtes inter-sites (anti-CSRF de base).

if (session_status() === PHP_SESSION_NONE) {
    $enHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $enHttps,
        'samesite' => 'Lax',
    ]);
    session_start();
}
