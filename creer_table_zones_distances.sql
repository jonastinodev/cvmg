-- Table des distances entre sous-zones du quartier pilote Voie Express.
-- À exécuter une seule fois, après ajouter_zone_cv.sql.
-- Voir _bmad-output/planning-artifacts/architecture/architecture-cvmg-2026-08-21/ARCHITECTURE-SPINE.md
-- (AD-3, AD-4, AD-5) : matrice complète et symétrique (les deux sens de chaque
-- paire, plus une ligne réflexive par zone à distance 0), collation cohérente
-- avec cv.zone pour que le JOIN de la recherche (FR-1) ne rate jamais un
-- rapprochement à cause d'une différence de casse/accent/encodage.

CREATE TABLE IF NOT EXISTS zones_distances (
    zone_depart  VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    zone_arrivee VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    distance_km  DECIMAL(4,1) UNSIGNED NOT NULL,
    PRIMARY KEY (zone_depart, zone_arrivee)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contenu : quartier pilote Sotema, sous-zones Ambohimandamina, Antanimalandy,
-- Tanambao. Distances fournies par l'utilisateur le 2026-08-21 (~1 km entre
-- chaque paire). Matrice complète : 3 lignes réflexives + 3 paires × 2 sens.
INSERT INTO zones_distances (zone_depart, zone_arrivee, distance_km) VALUES
  ('Ambohimandamina', 'Ambohimandamina', 0.0),
  ('Antanimalandy',   'Antanimalandy',   0.0),
  ('Tanambao',         'Tanambao',        0.0),
  ('Ambohimandamina', 'Antanimalandy',   1.0),
  ('Antanimalandy',   'Ambohimandamina', 1.0),
  ('Ambohimandamina', 'Tanambao',        1.0),
  ('Tanambao',         'Ambohimandamina', 1.0),
  ('Antanimalandy',   'Tanambao',        1.0),
  ('Tanambao',         'Antanimalandy',   1.0)
ON DUPLICATE KEY UPDATE distance_km = VALUES(distance_km);
