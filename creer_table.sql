-- À exécuter une fois dans phpMyAdmin (onglet SQL), sur la base créée dans Plesk.

CREATE TABLE IF NOT EXISTS cin_enregistrements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE NULL,
    lieu_naissance VARCHAR(150) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    numero_cin VARCHAR(20) NOT NULL UNIQUE,
    profession VARCHAR(50) NOT NULL,
    date_creation DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
