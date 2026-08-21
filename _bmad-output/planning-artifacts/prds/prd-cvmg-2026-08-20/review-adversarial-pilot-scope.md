# Revue adversariale — PRD CVMG Voie Express (pilote instrumenté)

Cible : `prd.md` + `addendum.md`, dossier `prd-cvmg-2026-08-20`.
Angle : le pilote livre-t-il vraiment un instrument de validation minimal de l'hypothèse centrale (« un employeur cherchera en ligne plutôt que de demander autour de lui »), ou construit-il davantage tout en affirmant le contraire ?

---

## Finding 1 — Les deux canaux (en ligne vs assisté cybercafé) ne sont pas distingués dans les métriques, ce qui invalide le test de l'hypothèse centrale

**Sévérité :** critical
**Emplacement :** §3 Glossaire (« hypothèse centrale »), §4.1/§4.2 (FR-1/FR-4), §4.6 (FR-13), §10 (SM-1, SM-2, SM-C1)

**Problème :** L'hypothèse centrale telle que définie au §3 est spécifiquement *« un employeur cherchera un travailleur **en ligne** plutôt que de **demander autour de lui** »*. Le PRD construit pourtant deux parcours de recherche : UJ-1/FR-1 (employeur seul, en ligne) et UJ-2/FR-4 (employeur en personne, l'opérateur cherche pour lui « à voix haute »). Ce second canal n'est pas structurellement différent de « demander autour de soi » — c'est un opérateur qui joue exactement ce rôle, seulement outillé par le système. FR-13 (journalisation des recherches) ne capture ni canal ni acteur initiateur (seulement horodatage, zone, métier, nombre de résultats), et SM-1/SM-2/SM-C1 ne sont pas ventilées par canal. Un pilote qui « réussit » uniquement grâce à des conversions UJ-2 ne prouverait rien sur le comportement en ligne — il prouverait seulement que les opérateurs de cybercafé sont efficaces comme intermédiaires humains, ce que personne ne conteste.

**Scénario concret :** Le quartier pilote atteint son seuil, SM-1 et SM-2 affichent de bons chiffres, le pilote est jugé « GO ». Mais 90 % des déblocages proviennent d'employeurs venus en personne au cybercafé (UJ-2), 10 % de recherches en ligne autonomes (UJ-1). Le produit conclut à tort que l'hypothèse centrale est validée, alors que le comportement testé (recherche en ligne autonome) reste quasi inexistant.

**Correctif suggéré :** Ajouter un champ « canal » (en_ligne / assisté_cybercafé) à FR-13 et FR-14, puis scinder SM-1 et SM-2 en deux versions par canal (ou au minimum rapporter la part de chaque canal dans le total). Sans cela, le pilote ne peut pas trancher la question qu'il prétend trancher.

---

## Finding 2 — Contradiction entre le « cas limite » de UJ-1 et le mécanisme réellement spécifié par FR-8

**Sévérité :** high
**Emplacement :** §2.3 UJ-1 (cas limite), §4.4 FR-8

**Problème :** Le cas limite de UJ-1 énonce : *« si le quartier choisi n'a pas encore atteint le seuil de densité de profils, **la recherche affiche** « pas encore assez de profils inscrits dans ce quartier » »* — c'est-à-dire que le blocage intervient au moment de la **recherche**. Mais FR-8 spécifie explicitement que le système *« empêche la **confirmation d'un déblocage de contact** »* — le blocage intervient au moment du **paiement en cybercafé**, pas à l'affichage des résultats. Aucune FR ne couvre le comportement décrit par UJ-1 (message au niveau de la page de recherche elle-même) ; FR-9 ne rend le compte de profils/seuil consultable qu'« a minima par un administrateur », pas à l'employeur en recherche.

**Scénario concret :** Un employeur cherche dans une zone sous le seuil. Selon FR-8 seul, la recherche renvoie des résultats normaux (profils à contact masqué), l'employeur choisit un profil, se déplace au cybercafé, paie — et se fait refuser le déblocage à ce stade. C'est l'inverse de la résolution promise par UJ-1 (« pas de déblocage sans valeur » signalé *avant* le déplacement), et une expérience bien plus dégradée que celle spécifiée.

**Correctif suggéré :** Soit ajouter une FR explicite couvrant le filtrage/message au niveau recherche pour les zones sous le seuil (cohérent avec UJ-1), soit corriger le cas limite de UJ-1 pour refléter fidèlement le comportement de FR-8 (blocage seulement à la confirmation du déblocage).

---

## Finding 3 — SM-1 est présentée comme un taux mais son dénominateur n'est instrumenté nulle part

**Sévérité :** medium
**Emplacement :** §10, SM-1

**Problème :** SM-1 est définie comme *« proportion d'employeurs exposés au service (via flyer cybercafé, bouche-à-oreille de l'opérateur) qui initient au moins une recherche »*. Aucune FR (FR-13, FR-14, FR-15) n'instrumente le numérateur de cette exposition — ni distribution de flyers, ni comptage de personnes touchées par le bouche-à-oreille. Présenter ceci comme une « métrique primaire » de go/no-go donne une fausse impression de rigueur quantitative alors que le dénominateur ne peut être qu'estimé à dire d'expert, pas mesuré par le système.

**Correctif suggéré :** Soit ajouter une FR minimale de suivi d'exposition (ex. nombre de flyers distribués, compteur simple tenu par l'opérateur), soit reformuler SM-1 comme un volume absolu de recherches initiées plutôt qu'un taux, en admettant explicitement que le dénominateur reste qualitatif/estimé.

---

## Finding 4 — FR-3 (filtre par rayon de déplacement) est une fonctionnalité multi-zone dans un pilote mono-zone

**Sévérité :** medium
**Emplacement :** §4.1 FR-3, §2.2 Non-utilisateurs, §9 Périmètre

**Problème :** Le pilote est explicitement limité à *« un seul quartier »* (§9, §7), et §2.2 exclut les employeurs hors du quartier pilote. Dans ce cadre, tous les travailleurs et employeurs actifs appartiennent à la même zone : le filtre par rayon de déplacement (1 km / 5 km / plus de 10 km), dont l'intérêt est de faire correspondre des zones *différentes* mais proches, devient une logique quasi vide de sens pour un pilote à une seule zone — construire et tester cette logique de correspondance inter-zones ajoute du périmètre d'ingénierie que l'hypothèse testée ne requiert pas.

**Correctif suggéré :** Pour le pilote, simplifier FR-1/FR-3 à une correspondance stricte de zone (le champ rayon reste stocké pour l'inscription mais n'est pas utilisé en filtrage tant qu'il n'y a qu'une zone active), et différer la logique de rayon à l'ouverture multi-quartier.

---

## Finding 5 — FR-16 est un ajout scope-creep légitime mais non explicité comme tel : il ne sert aucune métrique de succès

**Sévérité :** low
**Emplacement :** §4.4 FR-16, §9.1, §10

**Problème :** FR-16 (statut de recherche visible pour le travailleur) est justifié par une préoccupation éthique réelle (transparence côté travailleur, risque n°2 du PRFAQ), mais elle ne contribue à aucune des métriques SM-1 à SM-C2 — elle ne teste rien côté hypothèse centrale (comportement employeur). Elle est néanmoins listée sans distinction dans le périmètre du pilote MVP (§9.1) aux côtés de fonctionnalités qui, elles, instrumentent directement l'hypothèse. Le PRD ne signale nulle part que FR-16 est une extension volontaire au-delà du strict nécessaire pour la validation — ce qui est exactly le type de dérive silencieuse que le cadrage du §0 dit vouloir éviter.

**Correctif suggéré :** Ajouter une phrase explicite en §9.1 ou §4.4 reconnaissant que FR-16 est un garde-fou éthique/produit ajouté au-delà du strict besoin de validation de l'hypothèse (contrairement au reste du §4.4), pour que ce choix soit assumé plutôt qu'implicite.

---

## Finding 6 — FR-16 rompt l'ordre de numérotation, source de confusion pour un lecteur qui parcourt les FR séquentiellement

**Sévérité :** low
**Emplacement :** §4.4 (emplacement de FR-16, entre FR-9 et FR-10 dans le texte, mais numérotée après FR-15)

**Problème :** FR-16 apparaît dans le corps du document entre FR-9 et FR-10, mais porte un numéro supérieur à FR-10–FR-15 qui la suivent dans l'ordre de lecture. §0 précise bien que « les FR sont numérotées globalement (FR-1 à FR-16) », donc ce n'est pas une erreur de référence (aucune FR manquante ou dupliquée constatée — la numérotation FR-1…FR-16 est complète et cohérente partout ailleurs, y compris dans les renvois de §6 et §10), mais l'ordre non séquentiel reste une source de confusion pour un lecteur qui utilise les numéros comme repère de progression.

**Correctif suggéré :** Ajouter une note discrète au premier usage de FR-16 (« numérotée FR-16 car ajoutée après une première passe de rédaction ») pour éviter que le lecteur ne pense à une erreur ou cherche une FR-16 manquante ailleurs.

---

## Finding 7 — FR-7 présuppose une identité « employeur » traçable alors que le PRD exclut explicitement toute identification employeur

**Sévérité :** low/medium
**Emplacement :** §4.3 FR-7, §4.1 (assumption : recherche libre d'accès sans identification employeur), addendum (piste technique FR-5/FR-6)

**Problème :** FR-7 est formulée ainsi : *« tant qu'un déblocage n'a pas été confirmé pour **ce couple employeur/travailleur** »*. Or §4.1 pose en hypothèse assumée qu'il n'y a *« aucune identification de l'employeur »*. Sans identifiant employeur, la notion de « couple employeur/travailleur » persistant n'a pas de support de données défini dans le PRD — l'addendum propose « un statut par couple (recherche employeur, profil travailleur) » sans clarifier si « employeur » désigne une session éphémère, une transaction de déblocage isolée, ou autre chose.

**Correctif suggéré :** Clarifier que « couple employeur/travailleur » désigne en réalité une instance de transaction de déblocage (par ex. identifiée par la recherche + le profil + l'horodatage), et non une identité employeur persistante — pour éviter toute ambiguïté d'implémentation ou l'apparence trompeuse d'un suivi employeur qui n'existe pas.

---

## Synthèse

Le PRD respecte globalement son cadrage annoncé (§0/§1) : la plupart des FR sont directement justifiées par l'instrumentation du pilote, les non-goals sont cohérents, et les questions ouvertes sont honnêtement listées plutôt que tranchées arbitrairement. Le problème le plus sérieux n'est pas un excès de fonctionnalités superflues, mais un **défaut de conception expérimentale** : en combinant recherche en ligne (UJ-1) et recherche assistée en cybercafé (UJ-2) dans les mêmes métriques sans distinction de canal, le pilote risque de produire un signal positif qui ne dit rien sur l'hypothèse réellement testée (Finding 1) — c'est le risque le plus critique à corriger avant l'ouverture du pilote. Le second problème sérieux est une incohérence de comportement entre UJ-1 et FR-8 (Finding 2) qui doit être résolue avant l'implémentation, sous peine de livrer une expérience utilisateur différente de celle spécifiée.
