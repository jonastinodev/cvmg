<?php
// index.php — Sert de point d'entrée pour les hébergements (Nginx notamment)
// qui ignorent le DirectoryIndex défini dans .htaccess (directive Apache).
// La page d'accueil réelle reste accueil.php ; ce fichier ne fait que
// l'inclure pour que la racine du domaine affiche le bon contenu.
require __DIR__ . '/accueil.php';
