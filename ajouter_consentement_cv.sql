-- Ajoute le consentement horodaté du travailleur à la table cv (Story 2.1).
-- À exécuter une seule fois. Idempotent via ADD COLUMN IF NOT EXISTS.
-- NULL = pas de consentement enregistré (couvre aussi les profils déjà en base).

ALTER TABLE cv
  ADD COLUMN IF NOT EXISTS consentement_horodatage DATETIME NULL DEFAULT NULL
      COMMENT 'Horodatage du consentement explicite du travailleur à être visible en recherche ; NULL = pas de consentement';
