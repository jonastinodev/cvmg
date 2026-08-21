-- Journal des déblocages de contact (Story 3.2, FR-6/FR-14).
-- À exécuter une seule fois dans phpMyAdmin, après creer_table_cv.sql.
-- Une ligne = un encaissement confirmé par un opérateur : c'est cet
-- enregistrement, pas un simple affichage écran, qui rend la garantie
-- FR-7 (non-fuite avant confirmation) vérifiable après coup. Pas de
-- contrainte d'unicité sur cv_id : un même profil peut être débloqué
-- plusieurs fois (employeurs différents, prix payé à chaque fois).
--
-- ON DELETE RESTRICT (pas CASCADE, contrairement à cv.utilisateur_id) :
-- cette table est un registre financier/d'audit, jamais une donnée qui doit
-- disparaître silencieusement si le profil ou l'opérateur est supprimé plus
-- tard par un futur outil d'administration.

CREATE TABLE IF NOT EXISTS deblocages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cv_id INT NOT NULL,
    operateur_id INT NOT NULL,
    horodatage DATETIME NOT NULL,
    prix_ar INT UNSIGNED NOT NULL,
    FOREIGN KEY (cv_id) REFERENCES cv(id) ON DELETE RESTRICT,
    FOREIGN KEY (operateur_id) REFERENCES utilisateurs(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
