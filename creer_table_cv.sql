-- À exécuter dans phpMyAdmin, après creer_table_utilisateurs.sql (table liée par clé étrangère).

CREATE TABLE IF NOT EXISTS cv (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    titre VARCHAR(150) NOT NULL,
    donnees_json LONGTEXT NOT NULL,
    date_creation DATETIME NOT NULL,
    date_maj DATETIME NOT NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
