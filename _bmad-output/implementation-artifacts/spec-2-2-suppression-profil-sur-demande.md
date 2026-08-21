---
title: "Suppression de profil sur demande (Story 2.2)"
type: 'feature'
created: '2026-08-21'
status: 'done'
review_loop_iteration: 0
context: ['{project-root}/_bmad-output/implementation-artifacts/epic-2-context.md']
baseline_commit: 'ddc7dae3f21796a918323b7a45da1de26f4fb99b'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Un travailleur (ou l'opérateur en son nom) ne dispose d'aucun moyen de faire disparaître son profil Express de la recherche et de la fiche publique une fois publié — la seule sortie possible aujourd'hui serait une suppression manuelle en base par un humain avec accès phpMyAdmin.

**Approach:** Ajouter une action « Demander la suppression » sur le tableau de bord opérateur (`tableau-de-bord-operateur.php`), qui horodate durablement la demande sur le CV concerné via un nouvel endpoint. `recherche.php` et `profil-public.php` excluent immédiatement tout profil portant cet horodatage — pas de suppression physique de la ligne, pas de délai automatisé (le délai 48h reste un engagement opérationnel humain, vérifiable a posteriori grâce à l'horodatage).

## Boundaries & Constraints

**Always:**
- `suppression_demandee_le` est un `DATETIME` (pas un booléen) : `NULL` = pas de demande, valeur = horodatage durable de la demande.
- La demande est déclenchée uniquement par l'opérateur propriétaire du CV (même motif de propriété que `supprimer-cv.php` : `WHERE id = :id AND utilisateur_id = :uid`, `rowCount() === 0` = 404).
- `recherche.php` ET `profil-public.php` excluent tout profil avec `suppression_demandee_le IS NOT NULL`, dès l'exécution de la demande (pas de délai avant disparition).
- La ligne `cv` n'est jamais supprimée physiquement par cette story — seule la visibilité change (UPDATE, pas DELETE).
- Migration SQL idempotente (`ADD COLUMN IF NOT EXISTS`), même pattern que `ajouter_consentement_cv.sql`.
- Français partout : variables, clés JSON, messages d'erreur.
- Une fois la demande enregistrée, l'opérateur voit un état "Suppression demandée le J/M/A" à la place du bouton d'action (pas de double-demande possible depuis l'UI).

**Ask First:** Aucune décision bloquante identifiée — le schéma (colonne DATETIME sur `cv`) suit le pattern déjà établi par `consentement_horodatage` (Story 2.1).

**Never:**
- Ne pas construire de tâche planifiée, de rappel automatique, ni de mécanisme qui purge la ligne après 48h — le délai est un engagement humain, pas un automatisme.
- Ne pas ajouter de confirmation par le travailleur lui-même (pas de compte travailleur dans ce produit) — seul l'opérateur agit, en son nom.
- Ne pas toucher au flux CV "complet" (`creer-cv.php`, `supprimer-cv.php`, `mes-cv.php`) — hors périmètre, uniquement le flux Express.

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Demande par le propriétaire | POST `demander-suppression-cv.php` avec `cv_id` d'un CV Express appartenant à l'opérateur connecté | UPDATE réussi, `suppression_demandee_le = NOW()`, `{"succes": true}` | N/A |
| Demande sur un CV d'un autre opérateur | POST avec `cv_id` valide mais `utilisateur_id` différent | Aucun UPDATE, 404 | `{"erreur": "Ce CV n'existe pas ou ne vous appartient pas."}` |
| Demande sans session | POST sans session valide | Aucun UPDATE, 401 | `{"erreur": "Vous devez être connecté."}` |
| Demande sans `cv_id` | POST avec corps vide ou `cv_id` manquant | Aucun UPDATE, 400 | `{"erreur": "Identifiant manquant."}` |
| Recherche, profil avec suppression demandée | `cv.suppression_demandee_le` non NULL, correspond métier/zone/rayon | Profil absent des résultats | N/A |
| Fiche publique, profil avec suppression demandée | GET `profil-public.php?id=X` où X a une suppression demandée | 404, même message que profil inexistant | `{"erreur": "Ce profil n'existe pas ou n'est plus public."}`-équivalent HTML |

</frozen-after-approval>

## Code Map

- `ajouter_consentement_cv.sql` -- pattern de migration idempotente à répliquer pour la nouvelle colonne (nouveau fichier `ajouter_suppression_cv.sql`)
- `supprimer-cv.php` -- pattern exact à répliquer pour le nouvel endpoint : `exiger-connexion.php`, lecture `cv_id` du JSON, `UPDATE ... WHERE id = :id AND utilisateur_id = :uid`, `rowCount() === 0` → 404
- `exiger-connexion.php` -- à inclure en tête du nouvel endpoint (401 JSON si pas de session)
- `recherche.php:32-41` -- requête SQL -- ajouter `AND cv.suppression_demandee_le IS NULL` à la clause WHERE, à côté du filtre `consentement_horodatage` déjà présent (Story 2.1)
- `profil-public.php:22-27` -- requête SQL (`WHERE id = :id AND est_public = 1`) -- ajouter `AND suppression_demandee_le IS NULL`
- `tableau-de-bord-operateur.php:16-25` -- requête `SELECT` -- ajouter `suppression_demandee_le` aux colonnes sélectionnées pour piloter l'affichage de l'état
- `tableau-de-bord-operateur.php:138-169` -- tableau HTML (colonne "Lien public") -- ajouter une colonne/action "Demander la suppression" (bouton si `suppression_demandee_le` est NULL, badge horodaté sinon) + script JS `fetch` vers le nouvel endpoint

## Tasks & Acceptance

**Execution:**
- [x] `ajouter_suppression_cv.sql` (nouveau fichier, racine du projet) -- `ALTER TABLE cv ADD COLUMN IF NOT EXISTS suppression_demandee_le DATETIME NULL DEFAULT NULL` avec commentaire -- colonne durable, NULL par défaut
- [x] `demander-suppression-cv.php` (nouveau fichier, racine du projet) -- endpoint JSON calqué sur `supprimer-cv.php` : `require exiger-connexion.php`, lit `cv_id`, `UPDATE cv SET suppression_demandee_le = NOW() WHERE id = :id AND utilisateur_id = :uid AND type = 'express'`, `rowCount() === 0` → 404 -- AC : seul le propriétaire peut déclencher, jamais de DELETE physique
- [x] `recherche.php` -- ajouter `AND cv.suppression_demandee_le IS NULL` à la clause WHERE -- AC : disparition immédiate de la recherche
- [x] `profil-public.php` -- ajouter `AND suppression_demandee_le IS NULL` à la clause WHERE -- AC : disparition immédiate de la fiche publique (même comportement 404 qu'un profil inexistant)
- [x] `tableau-de-bord-operateur.php` -- ajouter `suppression_demandee_le` au SELECT, une colonne "Action" avec bouton de confirmation appelant `demander-suppression-cv.php` en `fetch`, remplacé par un badge horodaté une fois la demande enregistrée -- AC : pas de double-demande possible depuis l'UI

**Acceptance Criteria:**
- Given un opérateur connecté sur son tableau de bord avec un CV Express publié sans demande de suppression, when il clique sur "Demander la suppression" et confirme, then le profil disparaît immédiatement des résultats de `recherche.php` et de `profil-public.php`, et le tableau de bord affiche désormais un état horodaté à la place du bouton.
- Given un CV Express appartenant à un autre opérateur, when une requête directe à `demander-suppression-cv.php` cible son `cv_id`, then la requête échoue en 404 sans modifier la ligne.
- Given un profil dont la suppression a été demandée, when un employeur consulte le lien public directement (`profil-public.php?id=X`), then il obtient la même page d'erreur que pour un profil inexistant — jamais un aperçu partiel des données.

## Design Notes

`suppression_demandee_le` suit exactement le même motif que `consentement_horodatage` (Story 2.1) : une colonne DATETIME qui sert à la fois de flag (NULL/non-NULL) et de valeur d'audit pour vérifier après coup que l'engagement 48h a été tenu, sans backfill ni tâche planifiée à écrire.

Endpoint calqué sur `supprimer-cv.php`, mais `UPDATE` au lieu de `DELETE` :
```php
$stmt = $pdo->prepare(
    'UPDATE cv SET suppression_demandee_le = NOW()
     WHERE id = :id AND utilisateur_id = :uid AND type = \'express\''
);
$stmt->execute([':id' => $cvId, ':uid' => $_SESSION['utilisateur_id']]);
if ($stmt->rowCount() === 0) { /* 404 */ }
```

## Verification

**Manual checks (if no CLI):**
- `php -l` sur les 4 fichiers PHP modifiés/créés.
- Exécuter `ajouter_suppression_cv.sql` manuellement dans phpMyAdmin, vérifier la colonne sur `cv`.
- Sur le tableau de bord, demander la suppression d'un profil : vérifier le badge horodaté et l'absence de double-bouton.
- Vérifier que ce profil n'apparaît plus dans `recherche.php` pour des critères qui le matcheraient normalement.
- Vérifier que `profil-public.php?id=<id du profil supprimé>` renvoie la page "profil introuvable".
- Tenter une requête directe à `demander-suppression-cv.php` avec le `cv_id` d'un autre opérateur : vérifier le 404 et l'absence de modification en base.

## Suggested Review Order

**Initialisation du flux**

- Vérification de l'authentification opérateur, lecture JSON du `cv_id` avec validation numérique stricte
  [`demander-suppression-cv.php:1-19`](../../../demander-suppression-cv.php#L1)

**Contrôle d'existence et propriété**

- Requête SELECT découplée de l'UPDATE pour éviter faux 404 sur double-clic, vérification complète (id, utilisateur_id, type='express')
  [`demander-suppression-cv.php:28-39`](../../../demander-suppression-cv.php#L28)

**Idempotence et audit du délai 48h**

- Si déjà supprimé : retour immédiat sans récrire l'horodatage d'origine, essentiel pour vérifier après coup que le délai 48h a été tenu
  [`demander-suppression-cv.php:43-49`](../../../demander-suppression-cv.php#L43)

**Écriture de l'horodatage**

- INSERT du timestamp avec relecture pour assurer la date renvoyée au client
  [`demander-suppression-cv.php:51-63`](../../../demander-suppression-cv.php#L51)

**Exclusion immédiate de la recherche**

- Deux filtres WHERE ajoutés : consentement (Story 2.1) et suppression demandée, tous deux obligatoires pour apparaître
  [`recherche.php:39-40`](../../../recherche.php#L39)

**Exclusion immédiate de la fiche publique**

- Filtre WHERE sur la suppression : profil retiré disparaît immédiatement avec le même 404 que profil inexistant
  [`profil-public.php:25`](../../../profil-public.php#L25)

**Tableau de bord opérateur : SELECT**

- Ajout de `suppression_demandee_le` au SELECT pour piloter l'affichage du bouton vs badge
  [`tableau-de-bord-operateur.php:16`](../../../tableau-de-bord-operateur.php#L16)

**Tableau de bord opérateur : affichage conditionnel**

- Badge horodaté si déjà supprimé (non cliquable), bouton "Demander la suppression" sinon
  [`tableau-de-bord-operateur.php:182-188`](../../../tableau-de-bord-operateur.php#L182)

**Tableau de bord opérateur : fetch et mise à jour DOM**

- Appel fetch vers nouvel endpoint, gestion erreur/succès, remplacement du bouton par badge avec date renvoyée par le serveur (pas fabriquée côté client)
  [`tableau-de-bord-operateur.php:199-229`](../../../tableau-de-bord-operateur.php#L199)

**Vérification pendant le build (step-03, step-04) :** 
- `php -l` sans erreur sur les 4 fichiers PHP (2 nouveaux, 2 modifiés).
- Relecture des diffs confirmant que seules les lignes prévues ont changé, et que chaque ligne de la matrice I/O est couverte (propriété par `utilisateur_id`, messages 400/401/404 conformes, exclusion immédiate).
- Step-04 a remonté 3 findings de type "patch" ; tous corrigés : (1) découplage du contrôle d'existence de `rowCount()` sur UPDATE pour éviter faux 404 en cas de double-clic, (2) validation stricte de `cv_id` comme numérique, (3) date du badge retournée par le serveur au lieu de fabriquée côté client pour éviter la divergence avec une horloge mal réglée.
- Après correction : `php -l` propre sur les deux fichiers modifiés.

**Non vérifié en conditions réelles** — aucun accès CLI à une base MySQL n'était disponible dans cette session pour exécuter la migration ni tester bout-en-bout (colonne `suppression_demandee_le` inexistante tant que `ajouter_suppression_cv.sql` n'est pas exécuté manuellement). Les checks manuels énumérés plus haut restent donc à faire par un humain après exécution de la migration.
