---
stepsCompleted: [1, 2, 3, 4]
inputDocuments: ['_bmad-output/planning-artifacts/prds/prd-cvmg-2026-08-20/prd.md', '_bmad-output/planning-artifacts/architecture/architecture-cvmg-2026-08-21/ARCHITECTURE-SPINE.md']
---

# CVMG — Voie Express, côté employeur (pilote instrumenté) - Epic Breakdown

## Overview

Ce document décompose en epics et stories le PRD « CVMG — Voie Express, côté employeur (pilote instrumenté) » et la spine d'architecture associée (scope étroit : zone géographique uniquement). Six requirements (FR-1, FR-2, FR-3, FR-17, FR-18) sont déjà implémentés et testés de bout en bout hors du pipeline `bmad-build` — ce document les réconcilie comme travail fait, il ne les re-planifie pas comme travail futur.

## Requirements Inventory

### Functional Requirements

FR-1: Un employeur peut rechercher des travailleurs en sélectionnant un métier (`metiers.json`) et une zone, sans compte. La recherche ne retourne que les travailleurs dont la zone/rayon déclaré couvre la zone recherchée ; aucune saisie en texte libre. **✅ Implémenté (commit `4b40d65`)** pour la recherche elle-même — le signal de sous-densité au moment de la recherche (dernière conséquence testable de FR-1) était un orphelin sans story, identifié au sprint planning (2026-08-21) et fermé par la Story 4.3.

FR-2: Les résultats de recherche affichent prénom, métier, zone et rayon — jamais le numéro complet ni le CIN. **✅ Implémenté (commit `4b40d65`)**, vérifié par test (absence confirmée de fuite).

FR-3: Le filtrage géographique utilise le rayon de déplacement déclaré par le travailleur à l'inscription (`rayon_km`) ; pas de géolocalisation de l'employeur. **✅ Implémenté (commit `4b40d65`)**.

FR-4: Un opérateur authentifié peut effectuer une recherche (FR-1) depuis son interface, pour un employeur présent physiquement — réutilise FR-1/FR-2, pas une page distincte. Non implémenté.

FR-5: Un employeur (en ligne ou via l'opérateur) peut initier un déblocage de contact sur un profil, à un prix fixe unique (10 000 Ar). L'employeur en ligne est orienté vers un cybercafé — pas de paiement en ligne. Non implémenté.

FR-6: Un opérateur peut encaisser le prix fixe et révéler le numéro complet au moment de l'encaissement confirmé (pas de pré-affichage). Chaque déblocage confirmé est journalisé (FR-14). Non implémenté.

FR-7: Aucune réponse serveur (recherche, fiche publique, API) ne contient le numéro complet d'un travailleur tant qu'un déblocage n'a pas été confirmé pour ce couple employeur/travailleur. Non implémenté (dépend de FR-5/FR-6).

FR-8: Le système empêche la confirmation d'un déblocage de contact dans une zone sous le seuil de densité configuré — blocage côté serveur, message explicite. Non implémenté.

FR-9: Le nombre de profils actifs par zone et le seuil de densité sont consultables (a minima par un administrateur) ; le seuil est configurable, pas codé en dur. La valeur exacte du seuil reste non tranchée (question ouverte PRD, dépend d'un volume réel de 50-100 profils). Non implémenté.

FR-10: L'opérateur recueille et le système enregistre un consentement explicite et horodaté du travailleur à la publication de son profil. Un profil sans consentement n'apparaît pas en recherche (FR-1). Non implémenté.

FR-11: Un profil reste public indéfiniment par défaut (pas d'expiration automatique). Un travailleur ou l'opérateur en son nom peut demander sa suppression ; exécutée sous 48h, disparaît immédiatement des résultats de recherche et de la fiche publique. Non implémenté.

FR-12: Le numéro de CIN n'est jamais exposé sur une page publique (recherche comprise) — déjà respecté aujourd'hui, contrainte de non-régression. Pas une nouvelle construction, mais un point à couvrir par les critères d'acceptation de FR-2 et de toute page future.

FR-13: Le système journalise chaque recherche (horodatage, zone, métier, nombre de résultats, canal libre-service vs assisté) — le champ canal est nécessaire pour que SM-1a reste une mesure propre de l'hypothèse centrale. Non implémenté.

FR-14: Le système journalise chaque tentative de déblocage (initiée, bloquée par FR-8, ou confirmée) : horodatage, zone, métier, prix, statut. Non implémenté.

FR-15: Une personne autorisée peut consulter ou exporter les métriques du pilote par zone (recherches, taux de déblocage) sans requête SQL manuelle. Non implémenté.

FR-16: La fiche remise au travailleur indique si la recherche est déjà active dans sa zone (seuil atteint) ou encore en cours de constitution, mise à jour automatiquement. Non implémenté.

FR-17: Le formulaire d'inscription opérateur capture le numéro de téléphone du travailleur, obligatoire — refus explicite si absent. **✅ Implémenté (commit `12a86c2`)**, testé de bout en bout.

FR-18: Le formulaire d'inscription opérateur capture la zone (quartier) du travailleur, choisie dans une liste normalisée limitée à la zone pilote, obligatoire. **✅ Implémenté (commit `47c46c9`)**, testé de bout en bout y compris une requête de recherche simulée.

### NonFunctional Requirements

NFR1 (Sécurité/Confidentialité) : Aucun numéro complet ni numéro de CIN ne doit apparaître dans une réponse serveur avant déblocage confirmé — applicable à toute nouvelle page ou endpoint touchant des profils travailleurs (recoupe FR-2/FR-7/FR-12).

NFR2 (Contrainte technique) : Aucune nouvelle passerelle de paiement en ligne — le déblocage de contact se finalise exclusivement en espèces au cybercafé.

NFR3 (Contrainte technique) : `profil-public.php` continue d'afficher le numéro complet sans condition (comportement voulu, carte de visite post-QR code) — ne pas y ajouter de logique de masquage ; le masquage de FR-2/FR-7 est une logique neuve, propre à la page de recherche.

NFR4 (Convention de code) : Code, commentaires et messages d'erreur en français ; clés JSON en français ; `require_once 'exiger-connexion.php'` en tête de tout endpoint JSON authentifié ; filtrage systématique par propriétaire sur toute requête touchant des données sensibles (conventions `CLAUDE.md`/`AGENTS.md` existantes).

NFR5 (Périmètre) : Aucune activation multi-zone tant que le pilote sur le quartier Sotema n'a pas conclu — toute story doit rester utilisable avec les 3 sous-zones actuelles, sans présumer d'une extension.

### Additional Requirements

Issues de la spine d'architecture (`ARCHITECTURE-SPINE.md`, scope étroit : uniquement le stockage/calcul de la zone géographique) :

- AD-1 : `cv.zone` est une colonne SQL dédiée (`VARCHAR(100)`), indépendante de `donnees_json.personnel.ville`. **✅ Implémenté.**
- AD-2 : la liste des zones valides suit le motif `metiers.json` — fichier plat `zones.json`, lu à la requête, source canonique unique. **✅ Implémenté.**
- AD-3 : la distance entre deux zones vit dans une table SQL dédiée `zones_distances`, jamais chargée en PHP pour filtrage applicatif. **✅ Implémenté.**
- AD-4 : toute écriture dans `cv.zone` ou `zones_distances` est validée contre `zones.json` avant insertion ; collation `utf8mb4_unicode_ci` explicite sur les trois surfaces. **✅ Implémenté** (validation faite dans `enregistrer-express.php` et `recherche.php`).
- AD-5 : `zones_distances` stocke une matrice complète et symétrique (les deux sens + lignes réflexives) — toute requête de recherche géographique future fait un `INNER JOIN` simple, sans logique de symétrie côté requête. **✅ Implémenté et vérifié** (requête simulée FR-1).

**Important pour la suite de ce document :** ces 5 décisions couvrent uniquement l'architecture de la zone géographique. Aucune décision d'architecture formelle n'existe pour : le mécanisme de déblocage de contact (FR-5/FR-6/FR-7), le garde-fou de densité (FR-8/FR-9), le consentement/suppression (FR-10/FR-11), ni l'instrumentation (FR-13/FR-14/FR-15). Les epics couvrant ces zones devront le signaler comme risque d'implémentation plutôt que de supposer des choix techniques non actés.

### UX Design Requirements

Aucun document UX (`bmad-ux`) n'existe pour ce projet — section sans objet. Les nouvelles pages (`recherche.php`) prolongent le système visuel déjà établi (`assets/cvmg.css`, décrit dans `AGENTS.md`) plutôt que d'introduire de nouveaux tokens ou composants.

### FR Coverage Map

FR-1: Epic 1 - Recherche par métier et zone (+ Epic 4, Story 4.3, pour le signal de densité)
FR-2: Epic 1 - Résultats à contact masqué
FR-3: Epic 1 - Filtre par rayon de déplacement
FR-4: Epic 3 - Recherche assistée par l'opérateur
FR-5: Epic 3 - Initier un déblocage
FR-6: Epic 3 - Encaissement et révélation du numéro
FR-7: Epic 3 - Non-exposition avant déblocage confirmé
FR-8: Epic 4 - Blocage du déblocage sous le seuil
FR-9: Epic 4 - Seuil configurable et visible
FR-10: Epic 2 - Consentement horodaté à l'inscription
FR-11: Epic 2 - Suppression de profil sur demande
FR-12: Epic 1 - Non-exposition du CIN (contrainte de non-régression, couverte par les tests de FR-2)
FR-13: Epic 5 - Journalisation des recherches
FR-14: Epic 5 - Journalisation des déblocages
FR-15: Epic 5 - Export ou tableau de bord minimal
FR-16: Epic 4 - Statut de recherche visible pour le travailleur
FR-17: Epic 1 - Capture du téléphone à l'inscription
FR-18: Epic 1 - Capture de la zone à l'inscription

## Epic List

### Epic 1: Inscription complète et recherche employeur en ligne — ✅ COMPLET
Un opérateur peut inscrire un travailleur avec son téléphone et sa zone ; un employeur, sans compte, peut chercher par métier et zone et voir les profils disponibles à proximité, contact masqué.
**FRs couvertes :** FR-1, FR-2, FR-3, FR-12, FR-17, FR-18
**Statut :** livré et testé de bout en bout — commits `12a86c2` (FR-17), `47c46c9` (FR-18), `4b40d65` (FR-1/2/3). Réconcilié ici, pas re-planifié.

### Epic 2: Consentement et droit au retrait du travailleur
Un travailleur consent explicitement, au moment de son inscription, à ce que son profil soit visible en recherche ; lui ou l'opérateur en son nom peut demander le retrait du profil à tout moment, exécuté sous 48h.
**FRs couvertes :** FR-10, FR-11
**Notes d'implémentation :** le PRD (§4.5) signale ce point comme prérequis explicite *« avant toute mise en ligne réelle »* — antérieur à l'ouverture du pilote. Placé avant l'Epic 3 (décision utilisateur, 2026-08-21) : ne dépend pas du déblocage pour exister, et referme un écart déjà présent — `FR-1` (Epic 1, déjà livré) retourne aujourd'hui tous les profils Express sans jamais avoir vérifié de consentement (aucune vraie personne concernée à ce jour, un seul CV de test en base — écart de méthode, pas incident réel).

**Critère d'acceptation obligatoire (identifié en party mode, 2026-08-21) :** la story de consentement ne se limite pas à ajouter le champ au formulaire d'inscription — elle doit aussi retoucher la requête de `recherche.php` (Epic 1) pour filtrer les résultats par consentement enregistré. Sans cette clause explicite, le champ peut être livré sans que l'ancienne page ne le lise jamais.

### Epic 3: Débloquer le contact d'un travailleur
Un employeur — en ligne ou via l'opérateur en cybercafé — peut obtenir le numéro complet d'un travailleur en payant le prix fixe (10 000 Ar), sans que ce numéro ne fuite avant confirmation du paiement.
**FRs couvertes :** FR-4, FR-5, FR-6, FR-7
**Notes d'implémentation :** ferme la boucle de valeur centrale du pilote (chercher → obtenir un contact réel). Aucune décision d'architecture formelle n'existe pour le mécanisme de déblocage lui-même (la spine actuelle ne couvre que la zone géographique) — à traiter comme un risque d'implémentation, pas un choix déjà acté.

### Epic 4: Garde-fou de densité de zone
Un opérateur ne peut pas encaisser un déblocage de contact dans une zone qui n'a pas encore assez de profils travailleurs actifs ; un travailleur inscrit dans une telle zone voit un message honnête sur l'état de la recherche plutôt qu'une fausse promesse de visibilité.
**FRs couvertes :** FR-8, FR-9, FR-16 (+ le signal de densité de FR-1, Story 4.3)
**Notes d'implémentation :** dépend de l'existence du mécanisme de déblocage (Epic 3) — le blocage s'applique au moment de l'encaissement. La valeur du seuil lui-même reste une question ouverte du PRD (§11 #FR-8/FR-9), à observer une fois des inscriptions réelles accumulées.

### Epic 5: Mesurer le pilote
Le porteur du produit peut consulter, par zone et par canal (libre-service vs assisté), les métriques nécessaires pour juger si l'hypothèse centrale de la Voie Express tient.
**FRs couvertes :** FR-13, FR-14, FR-15

<!-- Repeat for each epic in epics_list (N = 1, 2, 3...) -->

## Epic 1: Inscription complète et recherche employeur en ligne — ✅ COMPLET

Un opérateur peut inscrire un travailleur avec son téléphone et sa zone ; un employeur, sans compte, peut chercher par métier et zone et voir les profils disponibles à proximité, contact masqué. Documenté rétroactivement — livré et testé de bout en bout avant l'écriture de ce document.

### Story 1.1: Capture du téléphone à l'inscription — ✅ FAIT (commit `12a86c2`)

As a opérateur de cybercafé,
I want saisir le numéro de téléphone du travailleur à l'inscription,
So that un employeur puisse un jour le contacter — sans lui, tout le côté employeur n'a rien à opérer.

**Acceptance Criteria:**

**Given** l'opérateur remplit le formulaire d'inscription Express
**When** il laisse le champ téléphone vide et tente de continuer
**Then** l'inscription est refusée avec un message explicite
**And** le numéro est stocké tel que saisi une fois renseigné, sans validation de format autre que non-vide (FR-17)

### Story 1.2: Capture de la zone à l'inscription — ✅ FAIT (commit `47c46c9`)

As a opérateur de cybercafé,
I want choisir la sous-zone du travailleur dans une liste normalisée à l'inscription,
So that les employeurs puissent un jour le retrouver par proximité géographique.

**Acceptance Criteria:**

**Given** l'opérateur remplit le formulaire d'inscription Express
**When** il tente de continuer sans choisir de sous-zone
**Then** l'inscription est refusée avec un message explicite
**And** seules les sous-zones du quartier pilote (`zones.json`) sont proposées — aucune saisie libre (FR-18)
**And** la valeur est validée côté serveur contre `zones.json` avant écriture en base (AD-4)

### Story 1.3: Recherche employeur par métier et zone, résultats masqués — ✅ FAIT (commit `4b40d65`)

As a employeur,
I want chercher un travailleur par métier et zone sans créer de compte,
So that je trouve rapidement quelqu'un de confiance près de chez moi.

**Acceptance Criteria:**

**Given** un employeur arrive sur `recherche.php` sans être authentifié
**When** il choisit un métier et une zone dans les listes normalisées et lance la recherche
**Then** il obtient la liste des travailleurs dont la zone est à portée de leur rayon déclaré, triée par distance (FR-1, FR-3)
**And** chaque résultat n'affiche que prénom, métier, zone et rayon — jamais le numéro complet ni le CIN (FR-2, FR-12)
**And** un métier ou une zone hors des listes normalisées est traité comme critère absent, sans requête exécutée
**And** une recherche sans résultat affiche un état vide explicite plutôt qu'une page cassée

## Epic 2: Consentement et droit au retrait du travailleur

Un travailleur consent explicitement, au moment de son inscription, à ce que son profil soit visible en recherche ; lui ou l'opérateur en son nom peut demander le retrait du profil à tout moment. Referme un écart déjà présent dans le code livré à l'Epic 1 (voir Story 2.1).

### Story 2.1: Consentement horodaté à l'inscription, appliqué à la recherche existante

As a travailleur inscrit via un opérateur,
I want que mon accord explicite à être visible soit demandé et enregistré au moment de mon inscription,
So that mon profil ne soit jamais public sans que j'aie su et accepté ce que ça implique.

**Acceptance Criteria:**

**Given** l'opérateur arrive à l'étape finale du formulaire d'inscription Express
**When** il tente de publier le profil sans confirmer explicitement le consentement du travailleur
**Then** la publication est refusée avec un message explicite (FR-10)
**And** la case de consentement n'est jamais pré-cochée par défaut — une action explicite est requise à chaque inscription
**And** le texte de consentement affiché juste avant la case est formulé pour être lu à voix haute au travailleur par l'opérateur, pas comme une mention légale à cocher sans y prêter attention (point soulevé en party mode, non vérifiable techniquement mais un effort de conception délibéré)
**And** un horodatage du consentement est enregistré de façon durable, associé au profil
**And** `recherche.php` (Epic 1, Story 1.3) est modifié pour n'inclure que les profils avec un consentement enregistré — critère obligatoire, identifié en party mode le 2026-08-21, pour ne pas répéter l'écart trouvé sur le code déjà livré
**And** le filtre de la requête exclut par défaut tout profil sans consentement explicitement enregistré, y compris les profils antérieurs à cette story (dont les CV de test) — aucun mécanisme de « confirmation rétroactive » distinct n'est nécessaire, l'exclusion par défaut couvre le passé et le futur de la même façon (simplifié en party mode, 2026-08-21 : cohérent avec l'ordre déjà décidé, consentement avant toute ouverture réelle du pilote — §4.5 du PRD)

### Story 2.2: Suppression de profil sur demande

As a travailleur inscrit, ou l'opérateur qui l'a inscrit en son nom,
I want pouvoir faire retirer le profil de la recherche et de la fiche publique,
So that je reprenne le contrôle sur des données personnelles que je n'ai pas moi-même publiées.

**Acceptance Criteria:**

**Given** un profil existant, créé par un opérateur
**When** l'opérateur déclenche une demande de suppression depuis son tableau de bord
**Then** le profil disparaît immédiatement des résultats de `recherche.php` et de `profil-public.php` (FR-11)
**And** la demande est horodatée pour permettre de mesurer le délai de traitement
**And** un profil reste public indéfiniment tant qu'aucune demande de ce type n'est faite — pas d'expiration automatique
**Notes d'implémentation :** le délai de 48h fixé par le PRD est un engagement opérationnel à respecter, pas un mécanisme automatisé — l'application ne dispose d'aucune tâche planifiée aujourd'hui ; l'horodatage de la demande est ce qui permet de vérifier après coup que l'engagement a été tenu.

## Epic 3: Débloquer le contact d'un travailleur

Un employeur — en ligne ou via l'opérateur en cybercafé — peut obtenir le numéro complet d'un travailleur en payant le prix fixe (10 000 Ar), sans que ce numéro ne fuite avant confirmation du paiement.

**⚠️ Aucune architecture formelle ne couvre ce mécanisme** (la spine actuelle ne traite que la zone géographique). La conception ci-dessous est une proposition raisonnable, pas une décision déjà validée — signalé en party mode par Winston et Amelia. Choix central : `FR-6` révèle le numéro « à l'écran opérateur », jamais à l'employeur en ligne — aucune identité employeur n'est donc nécessaire, la révélation passe toujours par une session opérateur authentifiée.

### Story 3.1: Recherche assistée par l'opérateur

As a opérateur de cybercafé, avec un employeur présent devant moi qui ne navigue pas seul sur internet,
I want effectuer la recherche à sa place,
So that cet employeur puisse quand même voir qui est disponible, sans jamais avoir eu à toucher un écran.

**Acceptance Criteria:**

**Given** un opérateur authentifié (session existante, réutilisée depuis l'inscription Express)
**When** il effectue une recherche par métier et zone pour un employeur présent (FR-4)
**Then** il obtient les mêmes résultats que la recherche publique (Epic 1, Story 1.3), contact toujours masqué à ce stade
**And** cette story livre déjà de la valeur seule — réaliser UJ-2 pour un employeur qui ne pourrait pas utiliser `recherche.php` par lui-même, indépendamment de l'existence du déblocage (Story 3.2)

### Story 3.2: Encaissement et révélation du numéro

As a opérateur de cybercafé,
I want encaisser le prix fixe et révéler le numéro d'un profil trouvé (Story 3.1),
So that l'employeur reparte avec un contact réel, et que je sois rémunéré pour ce service.

**Acceptance Criteria:**

**Given** un résultat de recherche obtenu via Story 3.1 (ou la recherche publique, Epic 1)
**When** l'opérateur déclenche un déblocage sur un profil précis
**Then** le numéro complet ne s'affiche qu'après confirmation explicite de l'encaissement du prix fixe — jamais de pré-affichage (FR-5, FR-6)
**And** un enregistrement du déblocage (profil, opérateur, horodatage, prix) est créé au moment de la confirmation — nécessaire pour que la garantie ci-dessous soit vérifiable, indépendamment de l'instrumentation complète de l'Epic 5
**And**, propriété transversale de cette story et de la Story 3.1 ensemble : aucune réponse serveur, sur aucune page (recherche publique, recherche opérateur, fiche publique), ne contient jamais le numéro complet d'un profil pour lequel aucun déblocage n'a été confirmé (FR-7)

### Story 3.3: Instruction de déblocage sur la recherche en ligne

As a employeur cherchant en ligne, sans compte,
I want voir clairement comment obtenir le contact d'un profil qui m'intéresse,
So that je sache exactement quoi faire ensuite, sans me heurter à une impasse.

**Acceptance Criteria:**

**Given** un employeur consulte les résultats de `recherche.php` (Epic 1, Story 1.3)
**When** il voit un profil qui l'intéresse
**Then** un prix fixe unique (10 000 Ar) est affiché de façon identique pour tous les profils et toutes les zones (FR-5)
**And** l'instruction de marche à suivre est claire : se rendre au cybercafé partenaire pour finaliser le déblocage — aucun paiement en ligne n'est proposé (§4.3 du PRD, NFR2)

## Epic 4: Garde-fou de densité de zone

Un opérateur ne peut pas encaisser un déblocage de contact dans une zone qui n'a pas encore assez de profils travailleurs actifs ; un travailleur inscrit dans une telle zone voit un message honnête sur l'état de la recherche. Dépend de l'existence du mécanisme de déblocage (Epic 3, Story 3.1).

### Story 4.1: Blocage du déblocage sous le seuil de densité

As a porteur du produit,
I want qu'un opérateur ne puisse pas encaisser un déblocage dans une zone encore trop peu peuplée,
So that les cybercafés ne soient jamais payés pour un service que la zone ne peut pas encore rendre.

**Acceptance Criteria:**

**Given** une zone dont le nombre de profils Express actifs est sous le seuil configuré
**When** un opérateur tente de confirmer un déblocage (Story 3.1) pour un travailleur de cette zone
**Then** la confirmation est refusée côté serveur avec un message explicite (« pas encore assez de profils inscrits dans ce quartier ») — jamais un échec silencieux (FR-8)
**And** le blocage s'applique côté serveur, pas seulement en désactivant un bouton à l'écran — un opérateur ne peut pas le contourner
**And** le seuil est une valeur unique stockée en base, modifiable par une requête SQL directe — même motif que l'activation d'un opérateur, déjà établi dans ce projet (FR-9)
**And** le nombre de profils actifs par zone est calculable par une requête groupée sur `cv.zone`
**Notes d'implémentation :** la valeur exacte du seuil reste un point ouvert du PRD (§11) — cette story construit le mécanisme, pas la valeur, qui s'observera une fois des inscriptions réelles accumulées.

### Story 4.2: Statut de recherche visible pour le travailleur

As a travailleur inscrit via la Voie Express,
I want savoir si la recherche est déjà active dans ma zone,
So that je ne me fasse pas de fausses idées sur mes chances d'être contacté.

**Acceptance Criteria:**

**Given** un travailleur inscrit dans une zone sous le seuil de densité
**When** il consulte sa fiche (`profil-public.php`, remise physiquement ou via QR)
**Then** un message honnête indique que la recherche n'est pas encore active dans sa zone, plutôt qu'une promesse de visibilité immédiate (FR-16)
**And** ce statut se met à jour automatiquement dès que la zone franchit le seuil — aucune ressaisie manuelle par l'opérateur
**Notes d'implémentation :** ceci ajoute une information sur `profil-public.php`, ce n'est pas la contrainte NFR3 (« ne pas ajouter de masquage de numéro ») qui reste inchangée — un statut de zone n'est pas un masquage de contact.

### Story 4.3: Signal de densité au moment de la recherche

As a employeur cherchant en ligne,
I want savoir dès la recherche si ma zone n'a pas encore assez de profils, plutôt qu'au moment de payer,
So that je ne me déplace pas en cybercafé pour découvrir un blocage que la recherche aurait pu m'éviter.

**Acceptance Criteria:**

**Given** une recherche sur `recherche.php` (Epic 1, Story 1.3) dont la zone est sous le seuil de densité
**When** les résultats s'affichent (avec ou sans profil trouvé)
**Then** un message informationnel l'indique clairement — même formulation que le statut travailleur de la Story 4.2, pas un blocage de la recherche elle-même (FR-1, troisième conséquence testable)
**And** ce message réutilise le même seuil et le même calcul de comptage par zone que la Story 4.1 — pas une deuxième logique de seuil à maintenir en parallèle
**Notes d'implémentation :** identifié comme orphelin lors du sprint planning (2026-08-21) — le PRD (§4.1) l'exige explicitement, `recherche.php` n'était touché par aucune story d'Epic 4 avant celle-ci. Ferme le Finding 2 de la revue adversariale du PRD (contradiction UJ-1/FR-8 : l'employeur ne devait pas découvrir le blocage seulement au paiement).

## Epic 5: Mesurer le pilote

Le porteur du produit peut consulter, par zone et par canal, les métriques nécessaires pour juger si l'hypothèse centrale de la Voie Express tient. Dépend de l'existence de la recherche (Epic 1) et du déblocage (Epic 3, Epic 4) pour avoir quoi que ce soit à journaliser.

### Story 5.1: Journalisation des recherches, par canal

As a porteur du produit,
I want savoir combien de recherches ont lieu, où, et par quel canal,
So that je puisse mesurer SM-1a sans confondre recherche en ligne et recherche assistée en cybercafé.

**Acceptance Criteria:**

**Given** une recherche effectuée sur la page publique (Epic 1, Story 1.3) ou par un opérateur (Epic 3, Story 3.1)
**When** la recherche s'exécute, avec ou sans résultat
**Then** un enregistrement est créé : horodatage, zone, métier recherché, nombre de résultats, et canal (libre-service ou assisté) (FR-13)
**And** le canal distingue sans ambiguïté les deux origines — c'est ce champ qui permet à SM-1a de rester une mesure propre de l'hypothèse centrale plutôt qu'un chiffre agrégé des deux canaux

### Story 5.2: Journalisation des déblocages

As a porteur du produit,
I want savoir combien de déblocages sont initiés, bloqués, ou confirmés,
So that je puisse mesurer SM-2 et SM-3 et calibrer le seuil de densité sur des faits.

**Acceptance Criteria:**

**Given** une tentative de déblocage sur un profil (Epic 3, Story 3.1)
**When** elle est initiée, bloquée par le garde-fou de densité (Epic 4, Story 4.1), ou confirmée
**Then** un enregistrement est créé : horodatage, zone, métier, prix, statut (FR-14)
**And** ce statut permet de calculer plus tard, sans requête ad hoc, un taux de blocage par zone (SM-3) et un taux de conversion recherche → déblocage (SM-2)

### Story 5.3: Export ou tableau de bord minimal des métriques du pilote

As a porteur du produit,
I want consulter les métriques du pilote par zone sans écrire de requête SQL,
So that je puisse juger, à un moment donné, si l'hypothèse centrale est confirmée ou infirmée.

**Acceptance Criteria:**

**Given** des données journalisées par les Stories 5.1 et 5.2
**When** un opérateur authentifié consulte la page ou l'export dédié
**Then** il obtient, pour les zones où il opère uniquement, le nombre de recherches (par canal), le taux de conversion en déblocage, et le taux de blocage par densité (FR-15)
**And** ces données sont accessibles en CSV ou via une vue simple — sans requête SQL manuelle
**And** un opérateur ne voit jamais les données d'un autre opérateur — décision prise en anticipation d'un futur pilote à plusieurs cybercafés (party mode, 2026-08-21), même si un seul opérateur existe au lancement
**Notes d'implémentation :** cet filtrage par opérateur couvre l'usage courant, mais pas le besoin du porteur du produit de juger l'hypothèse sur l'ensemble du pilote (tous opérateurs confondus) — cette vue globale reste, pour ce pilote, une requête SQL directe, même motif que l'activation d'un opérateur (Story 4.1) : pas d'interface d'administration distincte pour un besoin aussi rare. Le suivi qualitatif (l'employeur a-t-il réellement appelé ?) reste un processus humain hors du code (l'opérateur rappelle le travailleur 3 jours après un déblocage, déjà décidé au PRD §4.6) — cette story fournit la matière quantitative, pas cette boucle de suivi.
