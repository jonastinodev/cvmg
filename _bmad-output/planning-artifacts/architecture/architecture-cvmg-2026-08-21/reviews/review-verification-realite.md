# Revue — Vérification contre la réalité (AD-1, AD-2, AD-3)

**Document revu :** `ARCHITECTURE-SPINE.md` (cvmg-zone-express, 2026-08-21)
**Lentille :** chaque affirmation engagée par AD-1/AD-2/AD-3 est-elle vérifiée contre le code existant (ou le web, si une techno était nommée) plutôt qu'affirmée sans preuve ? Ce spine est du PHP/MySQL brut sans dépendance externe nommée — l'axe "version vérifiée sur le web" est donc sans objet ; la revue porte uniquement sur la vérification contre le code du repo.

**Méthode :** lecture de `creer_table_cv.sql`, `ajouter_colonnes_cv_express.sql`, `ajouter_est_operateur.sql`, `metiers.json`, `express-cv.php`, `enregistrer-express.php`, `enregistrer-cv.php`, `cv-template.php`, `AGENTS.md`, plus recherche de `zone`/`zones.json`/`zones_distances`/`zone_depart` dans tout le dépôt.

---

## Verdict global : PROBLÈMES (mineurs)

Les trois décisions (AD-1, AD-2, AD-3) reposent sur des affirmations qui se vérifient très majoritairement **vraies** contre le code réel. Deux points precis de la section "Structural Seed" / "Consistency Conventions" ne sont toutefois pas correctement vérifiés et devraient être corrigés avant que le spine serve de base d'implémentation.

---

## Affirmations vérifiées et confirmées

1. **`titre`, `rayon_km`, `type`, `est_public` sont des colonnes SQL dédiées** (paradigme, ligne 23) — **CONFIRMÉ**.
   - `titre VARCHAR(150) NOT NULL` existe dans `creer_table_cv.sql`.
   - `type ENUM('complet','express') NOT NULL DEFAULT 'complet'`, `rayon_km TINYINT UNSIGNED NULL`, `est_public TINYINT(1) UNSIGNED NOT NULL DEFAULT 0` existent, mais **dans un fichier séparé** : `ajouter_colonnes_cv_express.sql` (migration `ALTER TABLE ... ADD COLUMN IF NOT EXISTS`), pas dans `creer_table_cv.sql`. Le fait en lui-même (colonnes dédiées) est exact ; voir Problème 1 ci-dessous pour la conséquence sur le "Structural Seed".
   - `enregistrer-express.php` (lignes 74-86) fait bien un `INSERT ... type, rayon_km, est_public` — la colonne est utilisée en pratique, pas seulement déclarée.

2. **`ville` n'est pas une colonne SQL, elle vit dans `donnees_json.personnel.ville`** (AD-1) — **CONFIRMÉ**.
   - Absente de `creer_table_cv.sql` et de `ajouter_colonnes_cv_express.sql`.
   - `cv-template.php` (commentaire d'en-tête, lignes 10-11) documente `personnel => [..., 'ville', ...]` comme clé du JSON.
   - `enregistrer-express.php` (ligne 57) écrit `'ville' => ''` dans `donnees_json.personnel` — confirme qu'Express ne renseigne pas `ville`, cohérent avec la distinction posée par AD-1 ("jamais renseignée côté Express").

3. **`metiers.json` est un fichier JSON plat lu à la requête** (paradigme, AD-2) — **CONFIRMÉ**.
   - Le fichier est un simple tableau de 85 chaînes (`["Agent administratif", "Agent de nettoyage", ...]`), aucune structure imbriquée.
   - Lu via `file_get_contents(__DIR__ . '/metiers.json')` dans `creer-cv.php` (ligne 6) et `express-cv.php` (ligne 9), à chaque requête — pas de cache, pas de table SQL miroir.

4. **`bdd()` obligatoire, jamais `new PDO`** (Consistency Conventions, État & session) — **CONFIRMÉ** mot pour mot dans `AGENTS.md` ligne 16.

5. **`zone`, `zones.json`, `zones_distances`, `zone_depart` n'existent nulle part dans le code actuel** — **CONFIRMÉ** (recherche exhaustive, aucune occurrence hors le spine lui-même). Le spine les traite correctement comme "nouveau" dans le Structural Seed, et la case "recherche employeur" du diagramme mermaid comme "à construire" — aucun fichier de recherche employeur n'existe. Rien à redire ici.

---

## Problèmes trouvés

### Problème 1 — Structural Seed : "creer_table_cv.sql à étendre" contredit la pratique déjà établie dans le repo

Ligne 70 du spine :
> `creer_table_cv.sql    # à étendre : ADD COLUMN zone VARCHAR(...)`

Ceci n'est pas vérifié contre la réalité du dépôt, et se trouve même contredit par elle. `creer_table_cv.sql` est un script `CREATE TABLE IF NOT EXISTS` déjà exécuté sur les bases existantes (cf. CLAUDE.md : "à exécuter manuellement dans phpMyAdmin"). Le repo montre déjà **deux précédents concrets** pour ajouter une colonne après coup, et aucun des deux ne modifie `creer_table_cv.sql` :
- `ajouter_colonnes_cv_express.sql` (ajoute `type`, `rayon_km`, `est_public` via `ALTER TABLE ... ADD COLUMN IF NOT EXISTS`)
- `ajouter_est_operateur.sql` (même motif, pour `est_operateur`)

Modifier directement `creer_table_cv.sql` pour y ajouter `zone` n'aurait aucun effet sur une base déjà créée — il faudrait, comme pour `rayon_km`/`type`/`est_public`, un nouveau fichier `ajouter_*.sql` avec `ALTER TABLE`. Le spine propose donc un plan d'implémentation qui ne suit pas la convention déjà en place dans le même repo, alors que cette convention était directement vérifiable en lisant les fichiers `.sql` existants à la racine.

**Impact :** faible à modéré — n'invalide pas la décision AD-1 elle-même (colonne dédiée), mais le "Structural Seed" qui doit guider l'implémentation contient une instruction concrète incorrecte.

### Problème 2 — Consistency Conventions : l'analogie "`titre` stocke le métier en texte brut" n'est vraie que côté Express

Ligne 61 du spine :
> `cv.zone` stocke le nom de zone en texte brut (comme `titre` stocke le métier en texte brut)

Vérifié contre `enregistrer-cv.php` (Voie Complète, lignes 22-25) : `titre` n'y est **pas** le métier brut — c'est un libellé de CV avec repli `titre_professionnel — nom prenom` si l'utilisateur n'a pas donné de titre explicite. Ce n'est que côté Express (`enregistrer-express.php` ligne 82 : `':titre' => $metier`) que `titre` correspond exactement au métier brut.

L'affirmation générale du tableau ("comme `titre` stocke le métier en texte brut") n'est donc vraie que pour une partie des lignes de la table `cv` (celles de type `express`), pas en général comme le texte le suggère. Ce n'est pas une erreur qui casse AD-1/AD-2/AD-3, mais c'est une généralisation non vérifiée contre le chemin "Voie Complète" du même fichier `enregistrer-cv.php` qui aurait dû être consulté.

**Impact :** mineur — affecte la justesse de l'analogie, pas la décision elle-même.

---

## Ce qui n'a pas pu être vérifié (hors périmètre du code actuel, normal)

- Le contenu réel de `zones.json` / `zones_distances` est explicitement différé (section "Deferred") — rien à vérifier, le spine le reconnaît lui-même.
- Aucune techno/version externe n'est nommée dans ce document ; la vérification web n'avait pas de cible.

---

## Recommandation

Corriger la ligne du Structural Seed pour refléter le motif réel du repo (nouveau fichier `ajouter_colonne_zone_cv.sql` ou équivalent, pas une modification de `creer_table_cv.sql`), et nuancer l'analogie sur `titre` dans les Consistency Conventions (préciser "côté Express" ou remplacer par un exemple valable pour toutes les lignes, par ex. `type`). Les décisions AD-1/AD-2/AD-3 elles-mêmes restent solides — les corrections sont d'ordre présentation/plan d'exécution, pas de fond.
