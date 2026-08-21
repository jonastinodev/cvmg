# Epic 2 Context: Consentement et droit au retrait du travailleur

<!-- Compiled from planning artifacts. Edit freely. Regenerate with compile-epic-context if planning docs change. -->

## Goal

Un travailleur inscrit via un opérateur doit consentir explicitement, au moment de son inscription, à ce que son profil soit visible en recherche — et lui, ou l'opérateur en son nom, doit pouvoir en demander le retrait à tout moment. Ce travail est désigné comme un prérequis explicite à toute mise en ligne réelle du pilote, à traiter avant l'ouverture plutôt que comme un chantier parmi d'autres. Il referme aussi un écart déjà présent dans le code livré : la recherche (Epic 1) retourne aujourd'hui tous les profils Express sans jamais avoir vérifié de consentement.

## Stories

- Story 2.1: Consentement horodaté à l'inscription, appliqué à la recherche existante
- Story 2.2: Suppression de profil sur demande

## Requirements & Constraints

- Un consentement explicite et horodaté du travailleur doit être recueilli et enregistré avant que son profil ne soit éligible à apparaître en recherche. La case de consentement ne doit jamais être pré-cochée — une action explicite est requise à chaque inscription.
- Le texte affiché avant la case de consentement doit être formulé pour être lu à voix haute au travailleur par l'opérateur, pas rédigé comme une mention légale qu'on coche sans y prêter attention.
- La recherche doit exclure par défaut tout profil sans consentement explicitement enregistré, y compris les profils créés avant l'existence de cette fonctionnalité (dont les CV de test) — l'exclusion par défaut couvre le passé et le futur sans mécanisme de confirmation rétroactive distinct.
- Un profil reste public indéfiniment par défaut ; il n'y a pas d'expiration automatique.
- Le travailleur, ou l'opérateur en son nom, peut demander la suppression d'un profil ; le profil doit disparaître immédiatement de la recherche et de la fiche publique dès l'exécution de la demande.
- La demande de suppression doit être horodatée, pour pouvoir vérifier après coup que l'engagement opérationnel de traitement sous 48h a été tenu — ce délai est un engagement à respecter, pas un mécanisme automatisé (aucune tâche planifiée n'existe dans l'application aujourd'hui).
- Contrainte de non-régression transversale : le numéro de CIN ne doit jamais apparaître sur une page publique.
- Conventions habituelles du projet applicables à tout nouvel endpoint : français partout (code, erreurs, clés JSON), protection des endpoints authentifiés, filtrage systématique par propriétaire sur les données sensibles.
- Aucune activation multi-zone tant que le pilote n'a pas conclu — toute story doit rester utilisable avec les sous-zones actuelles uniquement.

## Technical Decisions

- Aucune décision d'architecture formelle n'existe pour le consentement ou la suppression de profil — la spine d'architecture actuelle couvre uniquement le stockage et le calcul de la zone géographique. Le choix de représentation (colonne SQL dédiée, table séparée, ou champ dans le JSON existant) reste à faire par le développeur ; à traiter comme un risque d'implémentation, pas comme un choix déjà tranché.
- Motif déjà établi ailleurs dans le projet et réutilisable ici : tout état qu'une requête SQL doit filtrer ou croiser avec d'autres critères vit en colonne ou table SQL dédiée, même si une copie existe aussi dans le JSON de rendu — c'est le motif suivi pour la zone, le rayon de déplacement, le titre et la visibilité publique. Le consentement (à filtrer par la recherche) et l'état supprimé/actif d'un profil relèvent du même besoin.
- La modification de la requête de recherche existante pour filtrer par consentement enregistré est une obligation explicite de cet epic, pas une extension optionnelle laissée à l'appréciation du développeur.

## Cross-Story Dependencies

- Story 2.1 touche une page déjà livrée hors de cet epic (la recherche employeur d'Epic 1) : le filtre par consentement doit y être ajouté en plus du champ ajouté au formulaire d'inscription.
- Story 2.2 dépend de l'existence de profils (livrés par Epic 1, et enrichis par Story 2.1) pour avoir quelque chose à supprimer, mais sa logique de suppression n'a pas de dépendance technique sur celle du consentement.
- Aucune dépendance de cet epic vers Epic 3, 4 ou 5 n'est identifiée dans les artefacts de planification.
