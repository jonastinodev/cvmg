# Epic 3 Context: Débloquer le contact d'un travailleur

<!-- Compiled from planning artifacts. Edit freely. Regenerate with compile-epic-context if planning docs change. -->

## Goal

Un employeur — en ligne ou via l'opérateur en cybercafé — doit pouvoir obtenir le numéro complet d'un travailleur en payant un prix fixe unique (10 000 Ar), sans qu'aucune réponse serveur ne laisse fuiter ce numéro avant confirmation du paiement. Cet epic ferme la boucle de valeur centrale du pilote Voie Express (chercher → obtenir un contact réel exploitable). Point important : aucune architecture formelle n'existe pour ce mécanisme — la spine d'architecture actuelle ne couvre que le stockage/calcul de la zone géographique (Epic 1). Le design ci-dessous est donc une proposition raisonnable identifiée en party mode, pas un choix déjà validé ; à traiter comme un risque d'implémentation.

## Stories

- Story 3.1: Recherche assistée par l'opérateur
- Story 3.2: Encaissement et révélation du numéro
- Story 3.3: Instruction de déblocage sur la recherche en ligne

## Requirements & Constraints

- Un opérateur authentifié peut lancer une recherche pour un employeur présent physiquement, en réutilisant la même session que celle déjà utilisée pour l'inscription des travailleurs — pas de nouveau compte ni de page distincte, mêmes résultats masqués que la recherche publique (FR-4).
- Un employeur, en ligne ou via l'opérateur, peut initier un déblocage de contact à un prix fixe unique, affiché de façon identique pour tous les profils et toutes les zones ; l'employeur en ligne est orienté vers un cybercafé, aucun paiement en ligne n'est proposé (FR-5).
- L'opérateur encaisse le prix fixe et ne révèle le numéro complet qu'après confirmation explicite de l'encaissement — jamais de pré-affichage ; chaque déblocage confirmé doit être journalisable (FR-6, croise FR-14/Epic 5).
- Propriété transversale aux Stories 3.1 et 3.2 ensemble : aucune réponse serveur, sur aucune page (recherche publique, recherche opérateur, fiche publique), ne doit jamais contenir le numéro complet d'un profil tant qu'aucun déblocage n'a été confirmé pour ce couple employeur/travailleur (FR-7).
- Aucune nouvelle passerelle de paiement en ligne — le déblocage se finalise exclusivement en espèces au cybercafé (NFR2).
- `profil-public.php` continue d'afficher le numéro complet sans condition (carte de visite post-QR, comportement voulu) : ne pas y ajouter de logique de masquage — le masquage introduit par cet epic est propre à la recherche, pas une extension de cette page (NFR3).
- Conventions déjà en place à respecter : français partout (code, commentaires, erreurs, clés JSON), `require_once 'exiger-connexion.php'` en tête de tout endpoint JSON authentifié, filtrage systématique par propriétaire sur toute requête touchant des données sensibles (NFR4).
- Le pilote reste limité au quartier Sotema et à ses 3 sous-zones actuelles ; aucune story ne doit présumer d'une extension multi-zone (NFR5).
- Le taux de conversion recherche → déblocage confirmé (tous canaux) est la mesure de succès qui valide le prix fixe choisi — les stories doivent produire des événements exploitables pour ce calcul, sans l'implémenter elles-mêmes (relève d'Epic 5).

## Technical Decisions

- Choix central déjà acté (pas à re-discuter) : la révélation du numéro complet passe toujours par l'écran opérateur, dans une session opérateur authentifiée côté serveur — jamais par la page de recherche en ligne consultée par l'employeur. Aucune identité ni authentification employeur n'est donc nécessaire nulle part dans cet epic.
- Aucune décision d'architecture formelle n'existe pour le mécanisme de déblocage lui-même (table(s) de déblocages, statuts, modélisation) — contrairement à la zone géographique (Epic 1), qui a une spine dédiée (AD-1 à AD-5). Toute story de cet epic doit signaler ses choix de structure de données comme des décisions d'implémentation ouvertes, pas comme des invariants déjà tranchés.
- Un enregistrement de déblocage (profil, opérateur, horodatage, prix) doit être créé au moment de la confirmation — c'est le minimum nécessaire pour que la garantie de non-fuite (FR-7) reste vérifiable après coup, indépendamment de l'instrumentation complète prévue par Epic 5.
- Conventions existantes du projet à réutiliser sans les redécider : accès SQL uniquement via `bdd()`, activation d'opérateur par `UPDATE` SQL manuel (pas d'interface d'administration à construire ici).

## Cross-Story Dependencies

- Story 3.1 livre de la valeur seule, indépendamment de l'existence de Story 3.2 (réalise déjà le parcours employeur assisté en cybercafé).
- Story 3.2 s'appuie sur un résultat de recherche obtenu via Story 3.1 ou via la recherche publique (Epic 1).
- La garantie de non-fuite du numéro (FR-7) est une propriété conjointe des Stories 3.1 et 3.2 — aucune des deux ne la satisfait isolément.
- Story 3.3 (instruction en ligne) est indépendante de 3.1/3.2 côté implémentation mais doit rester cohérente avec le même prix fixe et le même message « se rendre en cybercafé ».
- Story 3.1 a déjà introduit une variable `$estOperateur` en tête de `recherche.php` (lecture de `$_SESSION['est_operateur']`) qui conditionne l'en-tête (lien de retour) et masque la note « rendez-vous en cybercafé » pour l'opérateur. Toute story qui retouche `recherche.php` (dont 3.2 et 3.3) doit réutiliser cette même variable plutôt que réinventer sa propre détection de session opérateur.
- Epic 4 (garde-fou de densité) dépend de l'existence du mécanisme de déblocage construit ici : le blocage s'applique au moment de l'encaissement (Story 3.2).
- Epic 5 (journalisation FR-14) dépend de cet epic pour avoir des événements de déblocage à journaliser.
