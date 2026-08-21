---
title: CVMG — Voie Express, côté employeur (pilote instrumenté)
status: draft
created: 2026-08-20
updated: 2026-08-20
---

# PRD: CVMG — Voie Express, côté employeur (pilote instrumenté)
*Titre de travail — à confirmer.*

## 0. Objet du document

Ce PRD cadre le **côté employeur** de la Voie Express de CVMG : recherche de travailleurs et déblocage de contact. Il fait suite au PRFAQ CVMG (`prfaq-cvmg.md`, verdict `needs-heat`), dont il hérite directement le constat central : l'hypothèse *« un employeur malgache cherchera un travailleur en ligne »* n'est pas vérifiée, et rien de ce côté n'existe dans le code aujourd'hui.

Ce document ne spécifie donc pas un lancement, mais un **pilote instrumenté** : la plus petite version du côté employeur qui, déployée sur un seul quartier, sert elle-même de test de l'hypothèse — remplaçant les 20 entretiens employeurs recommandés par le PRFAQ plutôt que de les précéder. Il s'adresse au développeur qui va construire ce pilote et à toute personne qui devra juger, à la fin, si l'hypothèse est confirmée ou infirmée.

Vocabulaire : les termes du §3 Glossaire sont utilisés tels quels dans tout le document — pas de synonyme. Les FR sont numérotées globalement (FR-1 à FR-18, dans l'ordre de rédaction — voir note en tête de §4) et les parcours UJ-1 à UJ-3. Contexte technique : voir `CLAUDE.md` à la racine du repo (stack PHP/MySQL sans build, `metiers.json`, table `cv`/`utilisateurs`, conventions de code en français). Contexte produit complet : `prfaq-cvmg.md` et `prfaq-cvmg-distillate.md`.

## 1. Vision

CVMG existe aujourd'hui comme générateur de CV (Voie Complète). La Voie Express inverse le sens habituel de la recherche d'emploi : au lieu de diffuser des offres (employeur → candidat), elle rend des **personnes trouvables** (travailleur → employeur), pour les 8,5 millions de travailleurs informels malgaches (gardien, jardinier, femme de ménage, chauffeur, maçon) qui n'ont ni compte, ni CV, ni présence en ligne, mais peuvent répondre au téléphone.

Le côté offre de la Voie Express — inscription du travailleur en cybercafé, fiche publique, QR code — est largement fonctionnel, à une réserve près identifiée par ce PRD : il ne capte aujourd'hui ni le téléphone ni la zone du travailleur (§4.0), deux données dont dépend tout le côté demande. Le côté demande, lui, n'existe pas du tout : pas de page de recherche employeur, pas de mécanisme de déblocage de contact, aucun moyen de vérifier qu'un employeur ira réellement chercher un travailleur en ligne. C'est précisément le côté qui crée la valeur, et c'est celui qui manque — complété par la petite réserve côté offre que ce PRD referme en même temps qu'il construit le côté demande.

Ce PRD spécifie ce côté manquant, mais volontairement à l'échelle d'un pilote : un seul quartier, saturé en profils travailleurs avant ouverture de la recherche, avec une instrumentation qui permet de mesurer si des employeurs cherchent réellement, et à quel prix ils acceptent de débloquer un contact. Si l'hypothèse tient, ce pilote fournit la preuve terrain qui manquait au PRFAQ. Si elle ne tient pas, il permet de le savoir avant d'investir davantage — sans avoir construit un produit à l'échelle nationale sur une hypothèse invérifiée.

## 2. Utilisateurs cibles

### 2.1 Jobs To Be Done

- **Employeur** (ménage local) : trouver rapidement quelqu'un de confiance pour un service ponctuel ou régulier (ménage, gardiennage, jardinage...) près de chez soi, sans passer par le bouche-à-oreille classique ni par une petite annonce qui disparaît.
- **Travailleur** (déjà inscrit via la Voie Express, hors scope de construction de ce PRD) : être trouvable par des employeurs de son quartier sans avoir à démarcher lui-même.
- **Opérateur de cybercafé** : générer un revenu de commission en étant utile deux fois — à l'inscription du travailleur, puis à la recherche/déblocage de contact pour l'employeur — sans avoir à être commercial pour autant.
- **Porteur du produit (toi)** : savoir, avec le moins de code possible et sur un seul quartier, si l'hypothèse centrale de la Voie Express est vraie avant d'y engager plus de ressources.

### 2.2 Non-utilisateurs (pilote)

- Employeurs hors du quartier pilote — la recherche n'est pas ouverte ailleurs tant que le quartier pilote n'a pas donné de signal.
- Entreprises ou recruteurs professionnels (le produit cible des ménages, pas des employeurs commerciaux).
- Utilisateurs de la Voie Complète (CV classique) — aucune interaction prévue entre les deux parcours dans ce PRD.

### 2.3 Parcours utilisateurs clés

- **UJ-1. Mme Rasoa cherche et débloque le contact d'une femme de ménage, en ligne.**
  - **Persona + contexte :** mère de famille active à Antananarivo, smartphone et réseau mobile, pas de temps pour démarcher son quartier.
  - **État d'entrée :** non authentifiée, arrive sur la page de recherche publique de cvmg.mg.
  - **Parcours :** choisit le métier « femme de ménage » et son quartier → voit une liste de résultats (prénom, métier, rayon de déplacement déclaré, contact masqué — pas de distance calculée, aucune géolocalisation de l'employeur, cf. FR-3) → ouvre un profil → voit le bouton « Débloquer ce contact — {prix fixe} Ar » → le système lui indique comment procéder (se rendre au cybercafé indiqué le plus proche).
  - **Climax :** elle se rend au cybercafé, paie le prix fixe, l'opérateur lui communique le numéro complet.
  - **Résolution :** elle appelle directement la travailleuse.
  - **Cas limite :** si le quartier choisi n'a pas encore atteint le seuil de densité de profils, la recherche affiche « pas encore assez de profils inscrits dans ce quartier » plutôt que des résultats vides ou un déblocage sans valeur.

- **UJ-2. M. Randria, peu à l'aise en ligne, passe par le cybercafé pour chercher un gardien.**
  - **Persona + contexte :** ne navigue pas sur internet, se déplace directement au cybercafé de son quartier, comme il l'a déjà fait pour d'autres démarches.
  - **État d'entrée :** en personne, face à l'opérateur.
  - **Parcours :** demande un gardien de son quartier → l'opérateur lance la recherche pour lui, lit les résultats à voix haute → M. Randria choisit un profil → l'opérateur encaisse le prix fixe sur place.
  - **Climax :** l'opérateur lui communique immédiatement le numéro complet (verbalement ou par écrit).
  - **Résolution :** il repart avec le contact, sans avoir touché un écran.

- **UJ-3. Nirina, gérant de cybercafé, tente d'activer la recherche dans un quartier encore vide.**
  - **Persona + contexte :** gérant de cybercafé récemment activé comme opérateur, a inscrit quelques travailleurs mais pas encore atteint le seuil.
  - **État d'entrée :** un employeur se présente en cybercafé (comme en UJ-2).
  - **Parcours :** l'opérateur lance une recherche dans son quartier → le système signale que le seuil de densité n'est pas atteint et bloque le déblocage de contact.
  - **Climax :** l'opérateur comprend qu'il doit inscrire davantage de travailleurs dans ce quartier avant de pouvoir monétiser des déblocages.
  - **Résolution :** l'opérateur retourne au recrutement de travailleurs — son incitation s'aligne sur la valeur réellement livrée plutôt que sur la seule inscription. *(Ce garde-fou opérationnalise directement le risque n°2 identifié par le PRFAQ : « cybercafés encaissent sans servir ».)*

## 3. Glossaire

- **Voie Express** — le produit de mise en relation locale (inscription travailleur en cybercafé + recherche/déblocage employeur), distinct de la Voie Complète.
- **Voie Complète** — le générateur de CV existant (`creer-cv.php` et associés). Gelé pendant ce chantier (§8).
- **Travailleur** — personne inscrite via la Voie Express, disposant d'une fiche publique (nom, métier, quartier, rayon de déplacement, numéro masqué).
- **Employeur** — ménage cherchant à recruter un travailleur, via recherche en ligne ou en cybercafé.
- **Opérateur** — gérant de cybercafé, habilité (statut activé) à inscrire des travailleurs et, avec ce PRD, à effectuer des recherches assistées et encaisser des déblocages de contact.
- **Zone** — unité géographique de recherche (quartier), déterminée par le champ quartier saisi à l'inscription du travailleur.
- **Seuil de densité** — nombre minimal de profils travailleurs actifs dans une zone en dessous duquel le déblocage de contact est bloqué.
- **Déblocage de contact** — action payante par laquelle un employeur obtient le numéro complet d'un travailleur, jusque-là masqué.
- **Fiche publique** — page existante (`profil-public.php`) affichant le profil d'un travailleur, numéro complet visible sans condition (comportement voulu : c'est la « carte de visite » remise après le QR code/lien direct). Vérification code : cette page n'a aujourd'hui aucune logique de masquage — le masquage requis en recherche (FR-2/FR-7) est donc à construire sur la nouvelle page de recherche, pas réutilisé depuis `profil-public.php`.
- **Pilote instrumenté** — déploiement limité à une seule zone, dont l'objectif explicite est de mesurer si l'hypothèse centrale est vraie, pas de générer de la croissance.
- **Hypothèse centrale** — *« un employeur malgache cherchera un travailleur en ligne plutôt que de demander autour de lui »*, non vérifiée à ce jour (PRFAQ).

## 4. Fonctionnalités

*Numérotation des FR dans l'ordre de rédaction, pas de lecture : §4.0 a été ajoutée après une revue technique qui a révélé un prérequis manquant, d'où FR-17/FR-18 malgré leur place en tête de section.*

### 4.0 Prérequis : téléphone et zone à l'inscription travailleur

**Description :** Vérification faite sur le code existant (`enregistrer-express.php`) : le formulaire d'inscription travailleur n'enregistre aujourd'hui **ni le numéro de téléphone ni aucune zone géographique** — ces champs sont insérés vides (`'telephone' => ''`, `'ville' => ''`). Seuls le rayon de déplacement (`rayon_km`) et l'identité/métier sont réellement captés. La Voie Express n'a donc pas le « côté offre entièrement fini » que le PRFAQ et la première version de ce PRD supposaient : sans correction, FR-1 (recherche par zone), FR-3, FR-6 (révéler un numéro qui n'existe pas), FR-8/FR-9 et FR-16 (mécanismes fondés sur la zone) n'ont aucune donnée à opérer. Ce prérequis précède donc la construction de tout le reste de ce PRD.

**Exigences fonctionnelles :**

#### FR-17 : Capture du téléphone à l'inscription — ✅ implémenté (2026-08-21, commit `12a86c2`)
Le formulaire d'inscription opérateur capture le numéro de téléphone du travailleur, obligatoire.

**Conséquences (testables) :**
- Une inscription sans numéro de téléphone est refusée avec un message explicite — tout le côté employeur dépend de pouvoir appeler.

#### FR-18 : Capture de la zone à l'inscription — ✅ implémenté (2026-08-21, commit `47c46c9`)
Le formulaire d'inscription opérateur capture la zone (quartier) du travailleur, choisie dans une liste normalisée limitée à la zone pilote (pas de texte libre), obligatoire. Architecture tranchée (spine du 2026-08-21, AD-1 à AD-5) : colonne `cv.zone` dédiée, indépendante de `ville` — pas de réutilisation, comme envisagé initialement.

**Conséquences (testables) :**
- Une inscription sans zone est refusée.
- La liste de zones proposée est limitée à celles du quartier pilote (cohérent avec §9 — un seul quartier).

**Notes :** FR-17/FR-18 sont un prérequis dur, pas une extension optionnelle du côté offre — modification mineure d'un formulaire déjà existant (deux champs), pas une refonte. Signalé également en §6 (risque n°7) et §11 (question n°9).

### 4.1 Recherche employeur en ligne (libre-service)

**Description :** Page publique, sans création de compte, accessible depuis cvmg.mg. L'employeur choisit un métier (liste normalisée `metiers.json`) et une zone, et obtient une liste de profils correspondants avec contact masqué. Réalise UJ-1. `[ASSUMPTION: la recherche est libre d'accès et ne nécessite aucune identification de l'employeur — cohérent avec le canal 2 (recherche publique) déjà acté dans le PRFAQ.]`

**Exigences fonctionnelles :**

#### FR-1 : Recherche par métier et zone — ✅ implémenté avec FR-2/FR-3 (2026-08-21, commit `4b40d65`), garde-fou densité non couvert (voir §11 #FR-8/FR-9)
Un employeur peut rechercher des travailleurs en sélectionnant un métier (`metiers.json`) et une zone, sans compte.

**Conséquences (testables) :**
- La recherche retourne uniquement des travailleurs dont la zone correspond et dont le rayon de déplacement déclaré couvre la zone recherchée.
- Aucune recherche en texte libre n'est proposée — sélection uniquement dans les listes normalisées de métiers et de zones.
- Si la zone recherchée est sous le seuil de densité (FR-8/FR-9), la recherche l'indique dès ce stade (même message que FR-16 côté travailleur — vérification informationnelle, pas un blocage) plutôt que de laisser l'employeur découvrir le blocage seulement au moment de payer en cybercafé (FR-8). Réalise le cas limite de UJ-1.

#### FR-2 : Résultats à contact masqué — ✅ implémenté avec FR-1
Les résultats de recherche affichent prénom, métier, zone et rayon de déplacement déclaré — jamais le numéro complet ni le numéro de CIN.

**Conséquences (testables) :**
- Le payload retourné par la page de recherche ne contient à aucun moment le numéro complet avant confirmation d'un déblocage de contact (FR-5, FR-7).
- Le numéro de CIN n'apparaît sur aucune page publique, y compris les résultats de recherche (cf. FR-12).

#### FR-3 : Filtre par rayon de déplacement — ✅ implémenté avec FR-1
Le filtrage géographique utilise le rayon de déplacement choisi par le travailleur à l'inscription (1 km / 2 km / 5 km / 10 km / plus loin, valeurs actuelles du formulaire `express-cv.php`) — champ déjà capté aujourd'hui (`rayon_km`), aucune saisie nouvelle à construire côté travailleur.

**Périmètre exclu :** *(optionnel)*
- Pas de géolocalisation précise de l'employeur — la zone est choisie manuellement, pas détectée.
- Utile même sur un seul quartier pilote (un travailleur au rayon de 1 km peut ne pas correspondre selon la sous-zone exacte de l'employeur) ; pas un filtre laissé pour un usage multi-quartier futur.

### 4.2 Recherche assistée en cybercafé

**Description :** Pour l'employeur peu à l'aise en ligne, l'opérateur effectue la même recherche depuis son propre poste, à l'écran ou à voix haute. Réalise UJ-2. Réutilise FR-1/FR-2 côté opérateur, pas une page distincte à maintenir.

**Exigences fonctionnelles :**

#### FR-4 : Recherche par un opérateur pour le compte d'un employeur
Un opérateur authentifié peut effectuer une recherche (FR-1) depuis son interface, pour un employeur présent physiquement.

**Conséquences (testables) :**
- L'interface opérateur est accessible depuis la même session que celle utilisée pour l'inscription travailleur existante (pas de nouveau compte à créer pour l'opérateur).

### 4.3 Déblocage de contact

**Description :** Mécanisme payant par lequel un employeur obtient le numéro complet d'un travailleur. Réalise UJ-1, UJ-2. `[ASSUMPTION: le paiement se fait en espèces au cybercafé (canal déjà existant pour l'inscription travailleur), pas via une passerelle de paiement en ligne — aucune intégration de paiement n'existe dans le code, et en créer une sortirait largement du périmètre d'un pilote. L'employeur qui cherche en ligne (UJ-1) est donc renvoyé vers un cybercafé pour finaliser le déblocage.]`

**Exigences fonctionnelles :**

#### FR-5 : Initier un déblocage depuis un résultat de recherche
Un employeur (en ligne ou via l'opérateur) peut initier un déblocage de contact sur un profil donné, à un prix fixe unique.

**Conséquences (testables) :**
- Le prix affiché est identique pour tous les profils et toutes les zones pendant le pilote (pas de variation testée, décision utilisateur).
- L'employeur en ligne (UJ-1) voit une instruction claire de la marche à suivre (se rendre en cybercafé) — pas de paiement en ligne proposé.

#### FR-6 : Encaissement et révélation du numéro par l'opérateur
Un opérateur peut encaisser le prix fixe et révéler le numéro complet du travailleur à l'employeur.

**Conséquences (testables) :**
- Le numéro n'est révélé à l'écran opérateur qu'après confirmation explicite d'un encaissement (pas de pré-affichage).
- Chaque déblocage confirmé est journalisé (FR-14) avec zone, métier, horodatage.

#### FR-7 : Non-exposition du numéro avant déblocage confirmé
Aucune réponse serveur (recherche, fiche publique, API) ne contient le numéro complet d'un travailleur tant qu'un déblocage n'a pas été confirmé pour ce couple employeur/travailleur.

**Conséquences (testables) :**
- Vérifiable par inspection des réponses HTTP de la page de recherche et de la fiche publique : absence du numéro complet dans le HTML/JSON avant déblocage.

### 4.4 Garde-fou de densité de zone

**Description :** Empêche un opérateur d'encaisser un **déblocage de contact employeur** dans une zone qui n'a pas encore assez de profils travailleurs actifs pour qu'une recherche ait une chance raisonnable d'aboutir. Réalise UJ-3. `[NOTE FOR PM: le gap bloquant identifié par le PRFAQ (« ne pas encaisser dans un quartier vide ») visait en premier lieu l'inscription du travailleur lui-même — le risque qu'un travailleur paie 500-1000 Ar pour un profil que personne ne cherchera. FR-8/FR-9 ne couvrent que la transaction employeur (déblocage) ; ils ne bloquent pas l'inscription travailleur, qui doit au contraire continuer sous le seuil pour permettre à la zone de l'atteindre. FR-16 ci-dessous adresse le côté travailleur de ce même gap, par la transparence plutôt que par un blocage.]`

**Exigences fonctionnelles :**

#### FR-8 : Blocage du déblocage sous le seuil
Le système empêche la confirmation d'un déblocage de contact (FR-6) dans une zone dont le nombre de profils travailleurs actifs est sous le seuil de densité configuré.

**Conséquences (testables) :**
- Une tentative de déblocage dans une zone sous le seuil renvoie un message explicite (« pas encore assez de profils inscrits dans ce quartier ») plutôt qu'un échec silencieux ou un déblocage autorisé.
- Le blocage s'applique côté serveur, pas seulement en affichage — un opérateur ne peut pas le contourner depuis son interface.

#### FR-9 : Seuil configurable et visible par zone
Le nombre de profils actifs par zone et le seuil de densité sont consultables (a minima par un administrateur).

**Conséquences (testables) :**
- Le seuil est une valeur configurable, pas codée en dur — sa valeur exacte reste un point ouvert (§11).

**Notes :** Le PRFAQ ne fixe pas de valeur au seuil (« simulable une fois 50-100 profils réels créés dans un quartier »). FR-9 fournit le mécanisme ; la valeur elle-même est un `[NOTE FOR PM]` à trancher une fois les premières inscriptions réelles observées dans le quartier pilote.

#### FR-16 : Statut de recherche visible pour le travailleur
La fiche remise au travailleur inscrit (physique/QR) indique si la recherche est déjà active dans sa zone (seuil atteint) ou encore en cours de constitution.

**Conséquences (testables) :**
- Un travailleur inscrit dans une zone sous le seuil voit un message honnête (« votre profil est en ligne ; la recherche s'activera dans votre quartier une fois assez de profils inscrits ») plutôt qu'une promesse de visibilité immédiate.
- Ce statut se met à jour automatiquement dès que la zone franchit le seuil (pas de ressaisie manuelle par l'opérateur).

**Notes :** Traite le volet travailleur du gap bloquant « ne pas encaisser dans un quartier vide » du PRFAQ — par la transparence sur ce qui est payé, puisque bloquer l'inscription elle-même empêcherait la zone d'atteindre le seuil.

### 4.5 Consentement, conservation et suppression des données

**Description :** Le pilote publie des données personnelles réelles (nom, métier, quartier, téléphone) de personnes souvent non numérisées, recrutées par un tiers rémunéré (l'opérateur). Le PRFAQ liste ce point comme prérequis explicite avant toute mise en ligne réelle (next-step #4) — il précède donc l'ouverture du pilote, pas seulement son build.

**Exigences fonctionnelles :**

#### FR-10 : Consentement horodaté à l'inscription
L'opérateur recueille et le système enregistre un consentement explicite du travailleur à la publication de son profil (nom, métier, quartier, statut de recherche), horodaté.

**Conséquences (testables) :**
- Un profil sans consentement enregistré n'est pas éligible à apparaître dans les résultats de recherche (FR-1).

#### FR-11 : Suppression de profil sur demande
Un profil reste public par défaut, indéfiniment, tant que personne n'en demande le retrait — il n'y a pas d'expiration automatique. Un travailleur, ou l'opérateur en son nom, peut demander la suppression d'un profil ; la demande est exécutée sous **48h** (décision utilisateur, 2026-08-21 — le numéro de téléphone exposé justifie un délai court plutôt que les 7 jours ouvrés initialement suggérés).

**Conséquences (testables) :**
- Un profil supprimé disparaît des résultats de recherche (FR-1) et de la fiche publique immédiatement à l'exécution de la demande.

#### FR-12 : Non-exposition du numéro de CIN
Le numéro de CIN n'est jamais exposé sur une page publique, y compris les résultats de recherche et la fiche publique — contrainte déjà respectée aujourd'hui dans le code, à ne pas régresser avec ce chantier.

### 4.6 Instrumentation du pilote

**Description :** Puisque ce pilote sert lui-même d'instrument de validation de l'hypothèse centrale (remplaçant les entretiens employeurs prévus par le PRFAQ), le minimum de mesure nécessaire pour juger, à la fin, si l'hypothèse tient, est du scope de ce PRD — pas une option secondaire. `[NOTE FOR PM: seule la recherche en libre-service (FR-1, UJ-1) teste directement l'hypothèse centrale (« un employeur cherche en ligne »). La recherche assistée en cybercafé (FR-4, UJ-2) reste utile au produit mais mesure autre chose — un employeur qui se déplace déjà vers un opérateur agit plus près de « demander autour de lui » que de « chercher en ligne ». D'où FR-13 qui distingue les deux canaux : ne pas agréger leurs volumes sans distinction dans le jugement final du pilote (§10).]`

**Exigences fonctionnelles :**

#### FR-13 : Journalisation des recherches
Le système journalise chaque recherche effectuée : horodatage, zone, métier recherché, nombre de résultats retournés, et **canal** (libre-service FR-1 vs assisté par opérateur FR-4) — ce dernier champ est ce qui permet à SM-1 de rester une mesure de l'hypothèse centrale plutôt qu'un chiffre agrégé des deux canaux.

#### FR-14 : Journalisation des déblocages
Le système journalise chaque tentative de déblocage (initiée, bloquée par FR-8, ou confirmée) : horodatage, zone, métier, prix, statut.

#### FR-15 : Export ou tableau de bord minimal
Une personne autorisée peut consulter ou exporter les métriques du pilote par zone (recherches, taux de déblocage) pour juger de l'hypothèse.

**Conséquences (testables) :**
- Les données de FR-13/FR-14 sont exportables (CSV ou vue admin simple) sans requête SQL manuelle.

**Notes :** Le suivi qualitatif (l'employeur a-t-il réellement appelé et obtenu le service ?) reste un processus opérationnel hors du code — FR-15 fournit la matière quantitative, pas la boucle de suivi humaine. Mécanisme retenu (décision utilisateur, 2026-08-21) : l'opérateur cybercafé rappelle le travailleur 3 jours après un déblocage confirmé pour demander s'il a été contacté. Aucun développement supplémentaire — s'appuie sur la relation opérateur/travailleur déjà existante.

## 5. Modèle économique du pilote

Tranché par le PRFAQ : les 500-1000 Ar payés par le travailleur à l'inscription ne sont **pas** un revenu pour CVMG (ils rémunèrent la commission cybercafé) — non viable comme modèle à eux seuls (~10 000 inscriptions/mois nécessaires pour 1000 €/mois). Le revenu doit venir du côté employeur.

Pour ce pilote, le prix de déblocage de contact est **fixe, imposé dès le départ** (décision utilisateur) — pas de test multi-prix ni de sondage de volonté de payer séparé. **Montant retenu : 10 000 Ar** (décision utilisateur, 2026-08-21 — écarté d'une première estimation à 100 000 Ar jugée irréaliste par l'utilisateur et son responsable). Le modèle économique définitif de la Voie Express (répartition employeur/opérateur/CVMG, éventuel tiers payeur) reste explicitement non tranché au-delà de ce pilote — le pilote informe cette décision, il ne la prend pas.

## 6. Risques et scénarios d'échec

Repris et complété à partir du PRFAQ, avec le mécanisme de ce PRD qui les adresse (ou non) :

1. **Le côté demande ne décolle jamais** (risque le plus probable, seul qui tue vraiment le produit). Ce pilote existe pour trancher ce risque — voir §10 Métriques de succès.
2. **Cybercafés encaissent sans servir** (zone vide, côté employeur) — adressé par FR-8/FR-9 (garde-fou de densité sur le déblocage de contact), à condition que le seuil soit fixé avant l'ouverture du pilote (§11). Le même gap côté travailleur (payer pour un profil que personne ne cherchera) est traité séparément par FR-16 (transparence de statut), pas par un blocage.
3. **Incitation opérateur mal alignée** (rémunéré à l'inscription, indifférent à l'usage réel) — FR-4/FR-6 rendent *possible* une commission opérateur sur les déblocages, ce qui alignerait son intérêt sur l'usage réel, mais l'accord de commission lui-même n'est pas tranché (§11 Q6) : tant qu'il ne l'est pas, ce risque n'est pas mitigé, seulement mitigeable. Le PRFAQ recommande aussi 5 entretiens de gérants de cybercafés pour vérifier que la commission proposée les motive réellement à recruter — non menés à ce jour (§11 Q8).
4. **Exposition légale** (CIN, données personnelles publiques, consentement non tracé, absence de procédure de retrait) — adressé par FR-10/FR-11/FR-12, à un niveau minimal proportionné à un pilote sur un seul quartier, pas à une conformité complète.
5. **Usage détourné des données** (annuaire sensible : femmes de ménage avec nom/quartier/téléphone) — atténué par le masquage (FR-2/FR-7) et le consentement (FR-10), non éliminé ; reste un point de vigilance opérationnelle.
6. **Fossé concurrentiel fragile sans exclusivité** — l'actif défendable est la relation avec les opérateurs de cybercafé, pas le code (reproductible en quelques semaines). Rien dans ce PRD ne sécurise une exclusivité contractuelle ; un concurrent qui rémunère mieux peut retourner un opérateur. Hors scope logiciel — dépend d'un accord contractuel, non traité ici.
7. **Données manquantes pour tout construire dessus** — vérification du code existant : le formulaire d'inscription travailleur ne capte aujourd'hui ni téléphone ni zone (§4.0). Sans FR-17/FR-18, l'ensemble de ce PRD (recherche, déblocage, garde-fou) n'a aucune donnée réelle à opérer. Risque de méthode, pas de marché — mais bloquant en premier, avant tout le reste.

## 7. Contraintes et garde-fous

- **Technique :** aucune nouvelle passerelle de paiement en ligne (§4.3) ; `profil-public.php` continue d'afficher le numéro sans condition (comportement voulu, ne pas y ajouter de masquage) — le masquage de recherche (FR-2/FR-7) est une logique neuve sur la page de recherche, pas une extension de `profil-public.php` ; code, commentaires, messages d'erreur en français, conventions `CLAUDE.md` (clés JSON en français, `require_once 'exiger-connexion.php'` pour les endpoints authentifiés, filtrage systématique par propriétaire sur toute requête touchant des données sensibles).
- **Confidentialité :** voir §4.5 — consentement horodaté, suppression sur demande, non-exposition du CIN.
- **Périmètre géographique :** un seul quartier pilote (§9) — aucune activation multi-zone tant que le pilote n'a pas conclu.
- **Ressources :** aucun budget marketing ni calendrier n'est fixé par ce PRD — conformément au PRFAQ, aucune estimation de délai ou de taille d'équipe n'est inventée ici.

## 8. Non-Goals (explicites)

- **Voie Complète gelée** pendant ce chantier — aucune nouvelle fonctionnalité, seuls les correctifs critiques restent possibles. Décision produit reprise directement du next-step #5 du PRFAQ (les deux voies ne partagent ni utilisateur, ni modèle économique, ni mesure de succès).
- Pas de lancement multi-quartier ni national — un seul quartier pilote.
- Pas de vérification d'antécédents ou d'enquête de moralité de l'employeur ou du travailleur (le scan CIN identifie, il ne garantit pas).
- Pas de paiement en ligne intégré — le déblocage de contact se finalise en cybercafé.
- Pas d'interface d'enrôlement des opérateurs — l'activation reste manuelle (`UPDATE` SQL) à l'échelle du pilote.
- Pas de gestion du cas multi-métier (un travailleur = un métier) — gap connu, non résolu ici.
- Pas de modification de profil travailleur (changement de numéro, de métier) au-delà de la suppression (FR-11).
- Pas de modèle économique définitif pour la Voie Express au-delà du prix fixe testé (§5).

## 9. Périmètre du pilote (MVP)

### 9.1 Dans le périmètre
- Prérequis : capture du téléphone et de la zone à l'inscription travailleur (§4.0).
- Recherche employeur en ligne et assistée (§4.1, §4.2), sur un seul quartier.
- Déblocage de contact à prix fixe, en cybercafé (§4.3).
- Garde-fou de densité de zone et statut de recherche visible pour le travailleur (§4.4).
- Consentement, conservation et suppression minimale des données (§4.5).
- Instrumentation quantitative du pilote (§4.6).

### 9.2 Hors périmètre pour ce pilote
- Tout ce qui est listé en §8.
- Choix du quartier pilote et nombre de cybercafés participants — **question ouverte**, voir §11 : ce PRD ne fixe pas de chiffre, conformément à la décision utilisateur de le laisser ouvert plutôt que d'inventer une cible.

## 10. Métriques de succès (go/no-go du pilote)

*Le pilote n'a qu'un seul objectif : trancher l'hypothèse centrale. Les métriques ci-dessous en sont la mesure directe — pas des métriques de croissance. Aucune échéance calendaire n'est fixée (le PRFAQ interdit d'inventer un délai) ; le pilote se conclut quand le quartier a atteint son seuil de densité et généré un volume de recherches suffisant pour juger.*

**Primaire**
- **SM-1a** : Taux d'initiation de recherche en **libre-service** (canal FR-1 uniquement, cf. distinction de canal en FR-13) — proportion d'employeurs exposés au service qui initient une recherche sans passer par un opérateur. C'est la mesure directe de l'hypothèse centrale (« un employeur cherche en ligne »). Valide FR-1. `[NOTE FOR PM: cible chiffrée non fixée — à définir une fois un ordre de grandeur d'exposition connu ; le dénominateur « employeurs exposés » n'est pas mesuré par une FR (pas de tracking des flyers/bouche-à-oreille), c'est une estimation opérationnelle, pas une métrique système précise.]`
- **SM-1b** : Volume de recherches assistées en cybercafé (canal FR-4). Suivi séparément — un volume élevé ici sans volume correspondant en SM-1a indiquerait que la demande existe mais pas *en ligne*, ce qui infirmerait l'hypothèse centrale telle que formulée même si le produit « marche ». Valide FR-4.
- **SM-2** : Taux de conversion recherche → déblocage confirmé, tous canaux. Valide si le prix fixe choisi (§5) est acceptable. Valide FR-5, FR-6.

**Secondaire**
- **SM-3** : Taux de déblocages bloqués par le garde-fou de densité (FR-8) — indicateur que le seuil ou le rythme d'inscription est mal calibré si ce taux reste élevé après la phase de saturation initiale.

**Contre-métriques (à ne pas optimiser)**
- **SM-C1** : Volume de recherches sans déblocage — un volume élevé de recherches curieuses sans déblocage ne valide pas l'hypothèse ; contrebalance SM-1 (ne pas confondre trafic et intention réelle de recruter).
- **SM-C2** : Volume d'inscriptions travailleurs sans recherche employeur correspondante dans la même zone — signal du risque n°2 (§6), à ne pas laisser masquer par un objectif de volume d'inscriptions.

## 11. Questions ouvertes

1. ~~Quel quartier pilote (et combien de cybercafés participants) ?~~ **Résolu (2026-08-21) :** quartier **Sotema**, sous-zones **Ambohimandamina, Antanimalandy, Tanambao** (~1 km entre chaque paire), **1 cybercafé** au lancement. Pas de géolocalisation automatique (FR-3 confirmé tel quel : zone choisie manuellement, pas détectée). Schéma et contenu implémentés : `zones.json`, `ajouter_zone_cv.sql`, `creer_table_zones_distances.sql` (commit `256068e`) — testé en base (requête FR-1 simulée, INNER JOIN correct). Reste : `express-cv.php`/`enregistrer-express.php` ne capturent pas encore ce champ (FR-18, pas encore implémenté).
2. ~~Quelle est la valeur du seuil de densité de zone (FR-8/FR-9) ?~~ **Résolu (2026-08-21) :** confirmé tel quel — pas de valeur a priori, simulée une fois 50-100 profils réels créés dans le quartier pilote.
3. ~~Quel est le montant du prix fixe de déblocage de contact (§5) ?~~ **Résolu (2026-08-21) : 10 000 Ar** (voir §5).
4. ~~Quel délai pour l'exécution d'une demande de suppression de profil (FR-11) ?~~ **Résolu (2026-08-21) : 48h** (voir FR-11).
5. ~~Comment le suivi qualitatif post-déblocage sera-t-il mené ?~~ **Résolu (2026-08-21) :** l'opérateur cybercafé rappelle le travailleur 3 jours après un déblocage confirmé (voir §4.6).
6. Quel accord de commission avec les opérateurs sur les déblocages de contact (au-delà de l'inscription) ? Nécessaire pour atténuer le risque n°3 (§6), non tranché ici — à discuter avec le responsable de l'utilisateur.
7. Le statut juridique du cybercafé encaissant pour le compte de CVMG (intermédiation) a-t-il été vérifié ? Signalé par le PRFAQ comme non vérifié — hors périmètre logiciel de ce PRD mais bloquant avant ouverture réelle du pilote.
8. Les 5 entretiens de gérants de cybercafés recommandés par le PRFAQ (la commission proposée les motive-t-elle à recruter activement ?) ont-ils été menés ? Contrairement aux 20 entretiens employeurs — remplacés par ce pilote lui-même (§0) — le PRFAQ ne propose pas d'alternative à ces entretiens opérateurs ; ce PRD ne les remplace pas et ils restent à faire, idéalement avant d'ouvrir le pilote à plus d'un cybercafé.
9. Le champ zone de FR-18 réutilise-t-il le champ `ville` existant (aujourd'hui non rempli), ou faut-il une colonne distincte pour porter une granularité quartier ? Question d'architecture à trancher avant d'implémenter FR-1/FR-18.

## 12. Index des hypothèses

- §4.0 (FR-18) — Le champ `ville` existant (non rempli aujourd'hui) est réutilisé/renommé pour porter la zone, plutôt qu'une nouvelle colonne.
- §4.1 — La recherche est libre d'accès, sans identification de l'employeur.
- §4.3 — Le déblocage de contact se paie en espèces au cybercafé, pas en ligne.
- §4.5 (FR-11) — Délai de suppression de profil proposé à 7 jours ouvrés.
