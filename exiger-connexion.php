<?php
// exiger-connexion.php — À inclure en haut de tout script qui doit être
// réservé aux utilisateurs connectés. Coupe la requête avec une erreur claire
// si aucune session valide n'existe.

require_once __DIR__ . '/session.php';

if (empty($_SESSION['utilisateur_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(401);
    echo json_encode(['erreur' => 'Vous devez être connecté.'], JSON_UNESCAPED_UNICODE);
    exit;
}
