---
title: Revue rubric-walker — ARCHITECTURE-SPINE cvmg-zone-express
reviewed: architecture-cvmg-2026-08-21/ARCHITECTURE-SPINE.md
lens: rubric-walker (une seule lentille, checklist point par point)
date: 2026-08-21
verdict: PROBLÈMES (correctifs mineurs à moyens, pas de refonte)
---

# Revue rubric-walker — ARCHITECTURE-SPINE cvmg-zone-express

## Résumé

Le cœur de la spine (AD-1, AD-2, AD-3) est solide : les trois décisions sont réelles, vérifiables, et ratifient correctement le code brownfield existant (vérifié directement dans le repo : `ville` n'est bien qu'une clé de `donnees_json.personnel.ville` jamais remplie côté Express, `titre`/`rayon_km`/`type`/`est_public` sont bien des colonnes SQL dédiées, `metiers.json` est bien un fichier JSON plat lu à la requête). Le document a néanmoins quatre défauts concrets, listés ci-dessous par ordre décroissant de gravité, plus un problème de cohérence de paradigme.

## 1. Fixe-t-elle les vrais points de divergence pour l'implémentation ?

**Partiellement.** AD-1/AD-2/AD-3 couvrent bien le point central (question #9 du PRD : `zone` colonne dédiée vs réutilisation de `ville`) et sa conséquence directe (forme du stockage des distances). Mais un point de divergence réel et pourtant explicitement dans le binding scope est **passé sous silence** :

- **FR-9 exige un seuil de densité « configurable » et « consultable a minima par un administrateur »**, mais aucune AD ne dit où vit cette valeur. Est-ce une constante dans `config.php` (pattern déjà existant pour les secrets/paramètres globaux, vérifié) ? Une ligne dans une table `parametres` à créer ? Un champ codé dans le PHP avec commentaire ? Rien ne le dit. La Capability Map affirme que FR-8/FR-9 sont « Governed by AD-1 (compte sur `cv.zone` pour grouper par zone) » — mais AD-1 ne fixe que la colonne `zone`, pas le stockage du seuil lui-même. Deux développeurs qui implémentent FR-9 indépendamment peuvent légitimement diverger (l'un ajoute une constante PHP, l'autre une table SQL) sans violer aucune Rule de cette spine. C'est exactement le type de divergence qu'une spine à ce niveau de scope est censée trancher, vu que FR-9 est listée dans le binding scope annoncé.

## 2. Chaque Rule est-elle vérifiable et empêche-t-elle vraiment la divergence visée ?

Les trois Rules sont individuellement bien formées (assertion testable, binaire, pas de vague). Deux réserves :

- **AD-1** : la Rule dit « remplie uniquement pour les lignes `type='express'` » et « jamais dérivée de, ni synchronisée avec, `donnees_json.personnel.ville` » — mais rien ne l'impose au niveau schéma (pas de `CHECK` constraint, MySQL le permettrait). C'est une convention de code, vérifiable seulement par revue, pas par la base. Ce n'est pas disqualifiant (le reste de l'app fonctionne déjà sur ce mode conventionnel — `est_public`, `type` ne sont pas non plus contraints par des `CHECK`), mais la Rule devrait le dire explicitement plutôt que de laisser croire à une garantie structurelle qu'elle n'offre pas.
- **AD-3** : la Rule interdit de charger les distances en PHP pour filtrer côté application, ce qui est vérifiable une fois FR-1 construit. Mais elle ne dit rien sur le sens du JOIN (voir point 3 ci-dessous) — deux implémentations FR-1 sincèrement conformes à la lettre de la Rule peuvent quand même diverger sur les résultats retournés.

## 3. La section Deferred laisse-t-elle une divergence possible avant construction ?

**Oui, un point concret.** « Réciprocité de `zones_distances` » (stocker A→B et B→A, ou un seul sens avec requête symétrique) est explicitement reporté comme « détail d'implémentation... pas un invariant structurant ». C'est une sous-estimation : la Rule d'AD-3 dit que FR-1 « fait un JOIN SQL » sur cette table — mais le JOIN littéral change de forme selon la convention retenue (jointure directe si les deux sens sont stockés, jointure avec `OR` ou requête `UNION`/self-join si un seul sens est stocké). Si le contenu de `zones_distances` est peuplé par une personne en supposant une convention et que la requête FR-1 est écrite par une autre en supposant l'autre convention, le résultat est un bug silencieux (des paires de zones valides qui ne remontent aucun résultat), pas une erreur visible. C'est précisément le genre de trou qu'un `Deferred` ne devrait pas laisser filer sans au moins fixer la convention de stockage (même si le contenu réel des lignes reste non rempli). Le reste des items Deferred (contenu réel, sens de « distance », évolution multi-quartier) sont correctement différés — ils n'ont pas d'impact sur la façon dont deux composants s'interfacent, seulement sur les valeurs ou sur un scope explicitement hors périmètre.

## 4. Ratifie-t-elle le brownfield plutôt que de le contredire ?

**Contredit sur un point vérifié dans le repo.** Le Structural Seed dit :

> `creer_table_cv.sql    # à étendre : ADD COLUMN zone VARCHAR(...)`

Or le repo contient déjà **deux** précédents pour ce cas exact — ajouter une colonne à une table déjà créée en production — et aucun des deux ne modifie le fichier `creer_table_*.sql` d'origine : `ajouter_colonnes_cv_express.sql` (a ajouté `type`, `rayon_km`, `est_public` à `cv`) et `ajouter_est_operateur.sql`. `creer_table_cv.sql` utilise `CREATE TABLE IF NOT EXISTS` — le rejouer ne fait rien sur une base existante, et y coller un `ALTER TABLE` en pied de fichier casse la convention déjà établie deux fois dans ce même projet pour cette même table. Le Structural Seed aurait dû prescrire un nouveau fichier du type `ajouter_colonne_zone_cv.sql`, cohérent avec le motif brownfield. Sur le reste (AD-1, AD-2), la spine ratifie correctement le code existant — vérifié directement : `enregistrer-express.php` insère bien `'ville' => ''` dans `donnees_json.personnel` (jamais remplie), et `titre`/`rayon_km`/`type`/`est_public` sont bien des colonnes SQL réelles de `cv`, comme l'affirme le memlog.

## 5. Chaque dimension attendue à ce niveau d'altitude est-elle tranchée, différée, ou en question ouverte ?

Deux trous de silence, pas seulement des questions ouvertes non documentées :

- **FR-16 est absent de la Capability → Architecture Map.** Il apparaît dans le frontmatter `binds`, dans le scope annoncé, et dans le `Binds` d'AD-1 — mais aucune ligne de la table « Capability → Architecture Map » ne l'attache à une AD, contrairement à FR-18, FR-1, FR-3, FR-8/FR-9 qui ont chacun leur ligne. Un lecteur qui ne consulte que ce tableau (l'usage prévu de cet artefact) peut légitimement croire que FR-16 est hors du scope architectural de cette spine.
- **Le stockage du seuil de densité (FR-9)**, déjà signalé au point 1, est une dimension entière laissée dans le silence — ni tranchée, ni listée en `Deferred`, ni notée comme question ouverte.

## 6. Le paradigme nommé est-il cohérent avec le reste du document ?

**Tension réelle, pas fatale.** Le paradigme énoncé est binaire : « données de référence statiques → fichier JSON plat » vs « état filtrable/mutable → colonne SQL dédiée ». Le corps du texte plie ensuite la distance entre zones dans la seconde catégorie : « sa distance à une autre zone [est] de l'état filtrable ». Mais une distance entre deux zones nommées n'est pas un état mutable par utilisateur au sens où `cv.zone` l'est (elle ne change pas quand un travailleur s'inscrit) — c'est une donnée de référence statique au même titre que les noms de zones, simplement stockée en table plutôt qu'en fichier. Le memlog confirme d'ailleurs que la vraie motivation d'AD-3 n'a jamais été la mutabilité mais la composabilité en une seule requête SQL (JOIN avec métier + rayon + seuil). La décision AD-3 elle-même est défendable et bien justifiée — mais le paradigme tel qu'écrit en tête de document ne la couvre pas honnêtement ; il faudrait soit reformuler le paradigme (« donnée interrogée seule → fichier ; donnée qui doit être jointe à d'autres filtres SQL → table »), soit assumer que le paradigme a une exception motivée par la performance/composabilité plutôt que de forcer `zones_distances` dans la case « état filtrable ».

## Respect du scope annoncé

Le scope annoncé (répondre à la question #9 du PRD + FR-1, FR-3, FR-8, FR-9, FR-16, FR-18) est globalement respecté — pas de dérive vers un chantier plus large (aucune mention de recherche employeur détaillée, de déblocage de contact, d'instrumentation FR-13/14/15, qui sont hors scope et le restent). Deux petites incohérences de bord :

- Le frontmatter `binds` et le `Binds` d'AD-1 incluent **FR-17** (capture du téléphone), qui n'a aucun rapport avec l'architecture de la zone et n'est pas dans le scope annoncé par l'utilisateur (FR-1, FR-3, FR-8, FR-9, FR-16, FR-18 — six items, sans FR-17). Vraisemblablement copié depuis la liste `binds` du PRD sans être élagué ; à corriger, sinon un lecteur peut croire que cette spine engage aussi FR-17.
- Comme noté au point 5, FR-16 est dans le scope annoncé et dans le `Binds` d'AD-1, mais absent du tableau Capability Map — la spine ne dérive pas vers un scope plus étroit sur le fond (elle traite bien la dépendance réelle : FR-16 a juste besoin de `cv.zone` pour savoir si le seuil est atteint dans la zone d'un profil), mais l'omission dans le tableau est un défaut de traçabilité, pas de scope.

## Verdict

**PROBLÈMES** — pas une refonte, mais quatre corrections concrètes avant de considérer la spine `final` :
1. Trancher (ou au moins nommer explicitement en `Deferred`) où vit le seuil de densité configurable de FR-9.
2. Fixer la convention de réciprocité de `zones_distances` (au moins la forme : sens unique + requête symétrique, ou double stockage) — ce n'est pas un détail d'implémentation, c'est ce qui fait qu'un JOIN retourne les bons résultats.
3. Corriger le Structural Seed : nouveau fichier `ajouter_colonne_*.sql` pour `zone`, pas une modification de `creer_table_cv.sql`, pour rester cohérent avec les deux précédents déjà dans le repo.
4. Ajouter FR-16 à la Capability → Architecture Map, et retirer FR-17 du binding scope de cette spine (ou justifier pourquoi il y figure).

Le paradigme mérite une reformulation mineure (point 6) mais n'invalide aucune décision.
