---
title: 'Recherche assistée par l''opérateur'
type: 'feature'
created: '2026-08-21'
status: 'done'
route: 'one-shot'
---

# Recherche assistée par l'opérateur

## Intent

**Problem:** Un opérateur de cybercafé avec un employeur présent physiquement (UJ-2) n'a aucun moyen indiqué d'effectuer une recherche métier/zone pour lui — `recherche.php` (Epic 1) n'est lié depuis aucune page opérateur, et rien ne distingue une session opérateur d'un visiteur anonyme sur cette page.

**Approach:** FR-4 impose explicitement de réutiliser `recherche.php` plutôt que de construire une page distincte. `recherche.php` détecte désormais une session opérateur active (`$_SESSION['est_operateur']`) pour adapter son en-tête (lien de retour vers le tableau de bord) et masquer la note d'orientation cybercafé (non pertinente pour un opérateur déjà sur place) — sans changer la requête ni le masquage du contact, identiques à la recherche publique. `tableau-de-bord-operateur.php` gagne un bouton d'entrée vers cette recherche assistée.

## Suggested Review Order

**Détection de session et branchement de l'en-tête**

- Point d'entrée : la variable qui pilote tout le comportement conditionnel de la page.
  [`recherche.php:20`](../../recherche.php#L20)

- L'en-tête bascule entre le libellé public et un lien de retour vers le tableau de bord opérateur.
  [`recherche.php:140`](../../recherche.php#L140)

- La note d'orientation « rendez-vous en cybercafé » est masquée pour un opérateur déjà sur place.
  [`recherche.php:203`](../../recherche.php#L203)

**Découvrabilité depuis le tableau de bord**

- Nouveau bouton, seul point d'entrée réel de cette story — sans lui, `recherche.php` reste invisible pour l'opérateur.
  [`tableau-de-bord-operateur.php:148`](../../tableau-de-bord-operateur.php#L148)

**Style (péripherique)**

- Affordance de lien (soulignement) pour que le retour au tableau de bord se lise comme cliquable.
  [`recherche.php:131`](../../recherche.php#L131)

- Style du bouton secondaire (contour bleu), cohérent avec la palette déjà en place sur cette page.
  [`tableau-de-bord-operateur.php:84`](../../tableau-de-bord-operateur.php#L84)
