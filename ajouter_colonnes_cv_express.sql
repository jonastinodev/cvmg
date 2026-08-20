-- Étend la table cv pour supporter la Voie Express.
-- À exécuter une seule fois. Idempotent via ADD COLUMN IF NOT EXISTS.

ALTER TABLE cv
  ADD COLUMN IF NOT EXISTS type       ENUM('complet','express') NOT NULL DEFAULT 'complet'
      COMMENT 'complet = formulaire 8 étapes, express = parcours cybercafé',
  ADD COLUMN IF NOT EXISTS rayon_km   TINYINT UNSIGNED NULL DEFAULT NULL
      COMMENT 'Rayon de recherche en km (Express uniquement)',
  ADD COLUMN IF NOT EXISTS est_public TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
      COMMENT '1 = profil accessible sans connexion (Express), 0 = privé';
