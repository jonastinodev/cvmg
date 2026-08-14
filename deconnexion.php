<?php
// deconnexion.php — Ferme la session et renvoie vers l'accueil.
require_once __DIR__ . '/session.php';
$_SESSION = [];
session_destroy();
header('Location: accueil.php');
exit;
