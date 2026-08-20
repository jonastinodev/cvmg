-- Ajouter le flag opérateur cybercafé sur la table utilisateurs.
-- À exécuter une seule fois. Idempotent : ne plante pas si la colonne existe déjà.

ALTER TABLE utilisateurs
  ADD COLUMN IF NOT EXISTS est_operateur TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
  COMMENT '1 = compte opérateur cybercafé, 0 = utilisateur standard';
