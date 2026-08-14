<?php
// voir-cv.php — Affiche le rendu complet d'un CV enregistré, sans passer par
// le formulaire d'édition. Page HTML classique (lien ouvert dans un nouvel
// onglet), donc redirection manuelle plutôt que exiger-connexion.php (qui
// répond en JSON, pensé pour les endpoints appelés en fetch).

require_once __DIR__ . '/session.php';
if (empty($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}
require_once __DIR__ . '/bdd.php';
require_once __DIR__ . '/cv-template.php';

$cvId = $_GET['id'] ?? null;
if (!$cvId) {
    http_response_code(400);
    exit('Identifiant manquant.');
}

$pdo = bdd();
$stmt = $pdo->prepare('SELECT donnees_json FROM cv WHERE id = :id AND utilisateur_id = :uid');
$stmt->execute([':id' => $cvId, ':uid' => $_SESSION['utilisateur_id']]);
$ligne = $stmt->fetch();

if (!$ligne) {
    http_response_code(404);
    exit("Ce CV n'existe pas ou ne vous appartient pas.");
}

$donnees = json_decode($ligne['donnees_json'], true) ?: [];

header('Content-Type: text/html; charset=utf-8');
echo genererCvHtml($donnees);
