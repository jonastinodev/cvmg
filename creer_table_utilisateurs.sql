-- À exécuter une fois dans phpMyAdmin, sur la même base que le reste du projet.

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    google_id VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    nom_affichage VARCHAR(150) NOT NULL,
    photo_url VARCHAR(500) NULL,
    date_creation DATETIME NOT NULL,
    derniere_connexion DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
