-- Ajouter la zone géographique (quartier pilote Voie Express) sur la table cv.
-- À exécuter une seule fois. Idempotent : ne plante pas si la colonne existe déjà.
-- Voir _bmad-output/planning-artifacts/architecture/architecture-cvmg-2026-08-21/ARCHITECTURE-SPINE.md
-- (AD-1, AD-4) : zone est indépendante de donnees_json.personnel.ville, validée à
-- l'écriture contre zones.json, collation explicite pour un JOIN fiable avec
-- zones_distances.

ALTER TABLE cv
  ADD COLUMN IF NOT EXISTS zone VARCHAR(100) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL
  COMMENT 'Sous-zone du quartier pilote (Voie Express uniquement) — valeur validée contre zones.json, NOT NULL applicatif pour type=express';
