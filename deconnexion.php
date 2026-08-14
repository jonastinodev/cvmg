<?php
// deconnexion.php — Ferme la session et renvoie vers l'accueil.
session_start();
$_SESSION = [];
session_destroy();
header('Location: accueil.html');
exit;
