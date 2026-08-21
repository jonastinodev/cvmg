---
title: 'Instruction de déblocage en ligne'
type: 'feature'
created: '2026-08-21'
status: 'done'
route: 'one-shot'
---

# Instruction de déblocage en ligne

## Intent

**Problem:** Un employeur qui cherche en ligne (Epic 1) voit des profils à contact masqué, mais rien ne lui indique le prix ni la marche à suivre pour obtenir un numéro — la note existante annonçait un déblocage en ligne « à venir », devenue trompeuse depuis que Story 3.2 a construit le mécanisme (côté opérateur uniquement).

**Approach:** Reformule la note déjà affichée sous la liste de résultats (ajoutée en Story 3.1) pour inclure le prix fixe (`$labelPrixDeblocage`, partagé avec Story 3.2 via `constantes-express.php`) et l'instruction cybercafé/espèces — sans paiement en ligne (NFR2). Aucun nouvel élément d'interface, aucune nouvelle requête : uniquement le texte affiché à l'employeur non-opérateur.

## Suggested Review Order

- Note affichée à l'employeur en ligne, seule sortie visible de cette story : prix, espèces, cybercafé, aucun paiement en ligne.
  [`recherche.php:225`](../../recherche.php#L225)

- Espace insécable dans le formatage du prix, pour qu'« 10 000 Ar » ne se coupe pas en deux lignes sur petit écran — corrige au passage le même risque sur le bouton opérateur de Story 3.2.
  [`recherche.php:23`](../../recherche.php#L23)
