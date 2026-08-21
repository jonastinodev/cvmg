---
title: "Consentement horodaté à l'inscription Express, appliqué à la recherche existante"
type: 'feature'
created: '2026-08-21'
status: 'in-review'
review_loop_iteration: 0
context: ['{project-root}/_bmad-output/implementation-artifacts/epic-2-context.md']
baseline_commit: 'ddc7dae3f21796a918323b7a45da1de26f4fb99b'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** `recherche.php` retourne aujourd'hui tous les profils Express publiés, sans jamais avoir vérifié qu'un travailleur a consenti à être visible en recherche — un écart de conformité déjà en production.

**Approach:** Ajouter une case de consentement obligatoire (jamais pré-cochée) à l'écran final d'`express-cv.php`, valider ce consentement côté serveur dans `enregistrer-express.php` en l'horodatant durablement, puis filtrer `recherche.php` pour exclure par défaut tout profil (nouveau ou déjà existant) sans consentement enregistré.

## Boundaries & Constraints

**Always:**
- Le consentement est validé côté serveur dans `enregistrer-express.php`, pas seulement côté client (le client peut être contourné) ; publication refusée avec `{"erreur": "..."}` sinon.
- La case de consentement n'est jamais pré-cochée.
- Le texte affiché avant la case est rédigé pour être lu à voix haute par l'opérateur au travailleur, pas comme une mention légale.
- `consentement_horodatage` est un `DATETIME` (pas un booléen) : `NULL` = pas de consentement, valeur = horodatage durable du consentement.
- `recherche.php` exclut tout profil avec `consentement_horodatage IS NULL`, y compris les profils créés avant cette story — sans mécanisme de confirmation rétroactive séparé.
- Migration SQL idempotente (`ADD COLUMN IF NOT EXISTS`), même pattern que `ajouter_colonnes_cv_express.sql`, à exécuter manuellement en phpMyAdmin (pas d'outil de migration dans ce projet).
- Français partout : variables, clés JSON, messages d'erreur.

**Ask First:** Aucune décision bloquante identifiée — le schéma (colonne DATETIME sur `cv`) suit un pattern déjà établi dans le projet.

**Never:**
- Ne pas modifier `profil-public.php` dans cette story — son filtrage par consentement n'est pas demandé par l'AC de la Story 2.1 (la disparition de fiche publique est le périmètre de la Story 2.2, déjà déférée).
- Ne pas construire de tâche planifiée, de mécanisme de rappel, ou de confirmation rétroactive distincte pour les profils existants.
- Ne pas toucher au flux CV "complet" (`creer-cv.php`, `cv-template.php`) — hors périmètre, uniquement le flux Express.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Publication sans case cochée | POST `enregistrer-express.php` sans `consentement: true` | Aucun INSERT, 400 | `{"erreur": "Le consentement du travailleur doit être confirmé."}` |
| Publication avec case cochée | POST avec `consentement: true` | INSERT réussi, `consentement_horodatage = NOW()` | N/A |
| Recherche, profil consenti | `cv.consentement_horodatage` non NULL, correspond métier/zone/rayon | Profil retourné, comportement inchangé | N/A |
| Recherche, profil non consenti (ancien ou nouveau) | `cv.consentement_horodatage IS NULL` | Profil absent des résultats | N/A |

</frozen-after-approval>

## Code Map

- `express-cv.php:368-386` -- écran 4 ("Vérification avant publication") -- ajouter case à cocher + texte de consentement, avant `#btn-publier`
- `express-cv.php:425-460` -- `validerEcran(n)` + listeners "Suivant" -- pattern de validation bloquante existant à répliquer pour le consentement
- `express-cv.php:507-564` (payload construit inline l.517-529) -- `soumettreExpress()` -- bloquer l'appel si case non cochée, sinon ajouter `consentement: true` au payload
- `express-cv.php:566` -- listener direct sur `#btn-publier` (ne passe pas par `validerEcran`) -- point d'ancrage du blocage
- `enregistrer-express.php:18-21` -- vérif session opérateur déjà en place (`utilisateur_id`, `est_operateur`) -- réutiliser tel quel
- `enregistrer-express.php:31-37` -- lecture des champs JSON -- ajouter lecture de `consentement`
- `enregistrer-express.php:83-97` -- `bdd()`, INSERT dans `cv` -- ajouter colonne `consentement_horodatage`
- `recherche.php:32-41` -- requête SQL (`WHERE ... AND cv.est_public = 1`) -- ajouter `AND cv.consentement_horodatage IS NOT NULL`
- `ajouter_colonnes_cv_express.sql` -- pattern de migration idempotente à suivre pour la nouvelle colonne

## Tasks & Acceptance

**Execution:**
- [x] `ajouter_consentement_cv.sql` (nouveau fichier, racine du projet) -- `ALTER TABLE cv ADD COLUMN IF NOT EXISTS consentement_horodatage DATETIME NULL DEFAULT NULL` -- colonne durable, NULL par défaut couvre automatiquement tous les profils existants
- [x] `express-cv.php` (écran 4) -- ajouter case à cocher non pré-cochée + texte de consentement lisible à voix haute -- AC Story 2.1 : case jamais pré-cochée, texte conçu pour l'oral
- [x] `express-cv.php` (`soumettreExpress`, listener `#btn-publier`) -- bloquer la publication côté client si la case n'est pas cochée (message explicite), sinon inclure `consentement: true` dans le payload -- AC : publication refusée sans confirmation explicite
- [x] `enregistrer-express.php` -- valider `consentement === true` côté serveur avant tout INSERT, sinon 400 `{"erreur": "..."}` ; si valide, écrire `consentement_horodatage = NOW()` sur l'INSERT -- AC : validation serveur obligatoire, horodatage durable associé au profil
- [x] `recherche.php` -- ajouter `AND cv.consentement_horodatage IS NOT NULL` à la clause WHERE -- AC : exclusion par défaut de tout profil sans consentement, passés et futurs confondus

**Acceptance Criteria:**
- Given l'opérateur est à l'écran 4 sans avoir coché la case de consentement, when il clique sur "Valider et publier", then la publication est refusée côté client et, si tentée directement, côté serveur — aucun profil n'est créé.
- Given l'opérateur coche la case et publie, when la requête atteint `enregistrer-express.php`, then le profil est créé avec `consentement_horodatage` renseigné à l'horodatage de la requête.
- Given des profils Express déjà en base avant cette story, when `recherche.php` est interrogé avec des critères qui les matcheraient normalement, then ils n'apparaissent jamais tant qu'aucun consentement n'est enregistré pour eux.
- Given un profil avec consentement enregistré et correspondant aux critères de recherche (métier, zone, rayon), when `recherche.php` est interrogé, then il apparaît dans les résultats comme avant cette story.

## Spec Change Log

## Design Notes

`consentement_horodatage` sert à la fois de flag (NULL/non-NULL) et de valeur d'audit — évite une colonne booléenne séparée qui dupliquerait l'information sans utilité, et couvre nativement "y compris les profils antérieurs" (AC Story 2.1) puisque toute ligne existante a déjà `NULL` par défaut de migration, sans backfill à écrire.

Validation serveur minimale attendue dans `enregistrer-express.php` :
```php
if (($donnees['consentement'] ?? false) !== true) {
    repondreErreur('Le consentement du travailleur doit être confirmé avant publication.', 400);
}
```

## Verification

**Manual checks (if no CLI):**
- Exécuter `ajouter_consentement_cv.sql` manuellement dans phpMyAdmin, vérifier que la colonne apparaît sur `cv` avec des valeurs `NULL` pour les lignes existantes.
- Dans `express-cv.php`, tenter de publier sans cocher la case : vérifier le blocage et le message.
- Publier avec la case cochée : vérifier en base que `consentement_horodatage` est renseigné pour la nouvelle ligne.
- Appeler `recherche.php` avec des critères correspondant à un profil pré-existant (consentement NULL) : vérifier qu'il est absent des résultats.
- Appeler `recherche.php` avec des critères correspondant au nouveau profil consenti : vérifier qu'il apparaît, comme avant cette story.

**Vérifié pendant le build (step-03) :** `php -l` sans erreur sur les 3 fichiers PHP modifiés ; relecture de la requête `recherche.php` et de l'ordre des vérifications dans `enregistrer-express.php` (session → champs obligatoires → consentement → zone → INSERT) confirmant que les 4 lignes de la matrice I/O sont couvertes par le code. **Non vérifié en conditions réelles** — `enregistrer-express.php` exige une session opérateur authentifiée (Google Identity Services) avant même de lire le champ consentement, donc un test bout-en-bout par requête HTTP directe est impossible sans passer par le navigateur connecté ; et la colonne `consentement_horodatage` n'existe pas encore en base tant que la migration n'a pas été exécutée manuellement. Les 5 manual checks ci-dessus restent donc à faire par un humain après exécution de la migration.
