-- Ajoute la demande de suppression sur demande à la table cv (Story 2.2).
-- À exécuter une seule fois. Idempotent via ADD COLUMN IF NOT EXISTS.
-- NULL = pas de demande de suppression ; valeur = horodatage durable de la demande.
-- La ligne n'est jamais supprimée physiquement : seule la visibilité change.

ALTER TABLE cv
  ADD COLUMN IF NOT EXISTS suppression_demandee_le DATETIME NULL DEFAULT NULL
      COMMENT 'Horodatage de la demande de suppression du profil par l\'opérateur ; NULL = pas de demande';
