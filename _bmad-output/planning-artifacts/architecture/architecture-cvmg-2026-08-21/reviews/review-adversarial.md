# Revue adversariale — ARCHITECTURE-SPINE.md (cvmg-zone-express)

**Cible :** `_bmad-output/planning-artifacts/architecture/architecture-cvmg-2026-08-21/ARCHITECTURE-SPINE.md`
**Méthode :** deux développeurs fictifs — Dev A (construit `express-cv.php` / `enregistrer-express.php`, capture la zone à l'inscription) et Dev B (construit plus tard l'écran de recherche employeur, "à construire") — respectent chacun à la lettre AD-1, AD-2, AD-3. Question posée pour chaque règle : existe-t-il une lecture honnête de Dev A et une lecture honnête de Dev B qui produisent des artefacts incompatibles entre eux ?

**Verdict global : TROUS TROUVÉS.** La spine fixe correctement *où* vit chaque donnée (colonne dédiée, fichier JSON, table dédiée) mais ne fixe pas assez précisément *la forme exacte* de la valeur qui doit circuler identique entre les trois représentations (`zones.json`, `cv.zone`, `zones_distances`), ni le contrat du JOIN de recherche. Le mode de défaillance dominant identifié est silencieux : un JOIN SQL sur chaînes de caractères qui ne matche pas ne lève pas d'erreur, il retourne simplement moins de résultats — invisible en test manuel superficiel.

---

## Constat 1 (majeur) — Trois représentations en texte brut, aucune garantie qu'elles restent identiques caractère pour caractère

AD-1 fixe `cv.zone` en `VARCHAR` texte brut, AD-2 fixe `zones.json` comme seule source des noms valides, AD-3 fixe `zones_distances(zone_depart, zone_arrivee, distance_km)` — la Consistency Convention confirme explicitement "pas de table de zones séparée de `zones.json`", donc pas d'identifiant numérique nulle part : le lien entre les trois n'existe que via l'égalité de chaînes.

Le motif observé pour `metiers.json` dans `express-cv.php` (lignes 9, 328-330 : `file_get_contents` + `json_decode`, rendu en `<option value="<?= htmlspecialchars($m) ?>">`) montre que côté inscription, la valeur écrite en base est bien la chaîne exacte de `zones.json` — un `<select>` empêche la saisie libre, donc **Dev A ne peut pas diverger** ici si `express-cv.php` suit fidèlement ce motif pour `zone`.

Le vrai trou est ailleurs : **le contenu de `zones_distances`**. La section Deferred dit explicitement que son contenu réel "dépend de la question #1 du PRD, non tranchée" — c'est-à-dire qu'il sera rempli séparément (à la main, ou par un script écrit par Dev B ou un tiers), sans qu'aucune Rule n'impose que `zone_depart`/`zone_arrivee` soient dérivés programmatiquement de `zones.json`. Rien n'interdit à quelqu'un de taper `"Quartier Nord"` dans `zones.json` et `"quartier nord"` ou `"Quartier Nord "` (espace final) dans les `INSERT` de `zones_distances`. AD-3 dit seulement que la recherche "fait un JOIN SQL dessus" — un JOIN sur chaînes qui ne matchent pas ne renvoie simplement aucune ligne, sans erreur.

**Impact concret :** Dev B construit la recherche en respectant AD-3 à la lettre (JOIN SQL, jamais de filtrage PHP) ; le JOIN peut échouer silencieusement pour tout ou partie des zones si les chaînes de `zones_distances` ne sont pas des copies exactes de `zones.json`, et personne ne le détecte tant que le contenu réel n'est pas peuplé — c'est-à-dire probablement après que les deux écrans sont déjà "terminés" indépendamment.

**Trou à combler :** une règle imposant que `zones_distances.zone_depart`/`zone_arrivee` soit strictement peuplé à partir des valeurs de `zones.json` (idéalement par un script qui lit `zones.json` et génère/valide les paires, plutôt que par saisie manuelle indépendante), plus une procédure de vérification (ex. requête de contrôle : toute valeur de `zones_distances` doit exister dans `zones.json`, et réciproquement toute zone de `zones.json` utilisée par un `cv.zone` doit avoir au moins une ligne de distance).

## Constat 2 (majeur) — Collation MySQL non spécifiée : la sensibilité à la casse/aux accents du JOIN dépend de la config serveur, pas d'une décision d'architecture

`creer_table_cv.sql` déclare `) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;` sans `COLLATE` explicite (ni au niveau table ni au niveau colonne). La future colonne `cv.zone` (AD-1) et la future table `zones_distances` (AD-3, pas encore de fichier `.sql`) hériteront donc de la collation par défaut du serveur MySQL de chaque environnement — laquelle détermine si `"Quartier Nord"` et `"quartier nord"` (ou avec/sans accents selon la collation `_ai`/`_as`) sont considérées égales par un `WHERE`/`JOIN`.

C'est une deuxième source du même problème que le Constat 1, mais indépendante de la discipline des développeurs : même si `zones.json`, `cv.zone` et `zones_distances` contiennent des chaînes rigoureusement identiques en octets, le comportement du JOIN de Dev B peut différer entre son environnement de dev et la prod si les collations par défaut diffèrent — la spine ne fixe rien ici alors qu'elle fixe déjà `DejaVu Sans`/`mm`/`isRemoteEnabled` pour dompdf avec la même minutie (cf. CLAUDE.md).

**Trou à combler :** une Rule (extension d'AD-1/AD-3 ou nouvelle AD) fixant explicitement la collation de `cv.zone` et `zones_distances.zone_depart`/`zone_arrivee` (typiquement `utf8mb4_bin` ou au moins une collation identique et documentée, pour rendre le comportement du JOIN prévisible et indépendant du serveur).

## Constat 3 (majeur) — Type de JOIN et cas "même zone" non tranchés : deux lectures honnêtes d'AD-3 divergent

AD-3 dit : *"La recherche FR-1 fait un JOIN SQL dessus [`zones_distances`] ; elle ne charge jamais cette donnée en PHP pour la filtrer côté application."* Cela fixe *que* le filtrage est en SQL, pas *comment* :

- Lecture A (INNER JOIN strict) : un profil dont `cv.zone` n'a pas de ligne correspondante dans `zones_distances` pour la zone recherchée par l'employeur est silencieusement exclu — y compris quand `cv.zone` est **exactement** la zone recherchée, si aucune ligne "zone vers elle-même" (distance 0) n'existe dans la table.
- Lecture B (LEFT JOIN + `COALESCE`) : traite l'absence de ligne "même zone" comme une distance implicite de 0, et n'exclut que les paires réellement absentes.

La section Deferred tranche la question voisine de la réciprocité A→B/B→A ("détail d'implémentation laissé à la construction") mais ne mentionne jamais le cas de la paire réflexive (zone == zone), qui est pourtant le cas le plus fréquent en usage réel (un travailleur et un employeur dans la même zone). Rien n'empêche Dev B de choisir l'une ou l'autre lecture en respectant AD-3 au mot près, et le choix change silencieusement qui apparaît dans les résultats.

**Trou à combler :** préciser dans AD-3 (ou une AD-4) si `zones_distances` doit contenir des lignes réflexives explicites (`zone_depart = zone_arrivee`, `distance_km = 0`) pour chaque zone, ou si la requête de recherche doit traiter l'absence de ligne comme une correspondance exacte (`UNION`/`COALESCE`) — et fixer INNER vs LEFT JOIN comme convention.

## Constat 4 (mineur/moyen) — Pas de garde-fou sur la dérive de `zones.json` dans le temps vis-à-vis des `cv.zone` déjà écrits

AD-1 et la Consistency Convention font de `cv.zone` une copie texte brut, permanente et non liée par clé étrangère à `zones.json` (voir aussi le motif équivalent déjà en place pour `titre`/métier). Si `zones.json` est modifié plus tard (renommage d'une zone, fusion de sous-quartiers, etc. — la section Deferred anticipe explicitement une "évolution multi-quartiers"), rien dans la spine n'impose une migration des lignes `cv.zone` existantes ni des `zones_distances` correspondantes. Les profils déjà inscrits sous l'ancien nom disparaîtraient silencieusement des résultats de recherche (leur `cv.zone` ne matchant plus aucune entrée de `zones.json` ni de `zones_distances`), sans qu'aucune des deux Rules AD-1/AD-2 ne soit techniquement violée.

**Trou à combler :** au minimum, documenter que tout renommage/suppression d'une entrée de `zones.json` doit être accompagné d'un script de migration sur `cv.zone` et `zones_distances` — ou, si jugé hors scope pilote, l'ajouter explicitement à la liste Deferred pour que ce ne soit pas un angle mort non reconnu.

## Constat 5 (mineur) — `cv.zone` NULL/vide non couvert par le contrat du JOIN

Rien dans AD-1/AD-3 ne dit ce que la recherche doit faire des lignes `type='express'` où `zone` est NULL ou vide (cas attendu pour les CV Express déjà existants avant l'ajout de cette colonne — cf. mémoire projet : *"express-cv.php ne collecte ni téléphone ni ville"*). Un comportement implicite (INNER JOIN les exclut de fait) n'est pas le même contrat qu'un comportement explicite documenté, et un futur correctif de données (backfill) pourrait supposer l'un ou l'autre. Mineur comparé aux constats 1 à 3, mais mérite une ligne dans Deferred ou dans AD-3 pour éviter une lecture divergente.

## Constat 6 (mineur, cosmétique) — "lit directement" (AD-2) ne fixe pas le mécanisme d'accès côté écran de recherche

Le motif observé pour `metiers.json` dans `express-cv.php` est un `include`/`file_get_contents` **côté serveur PHP**, rendu en HTML statique (le commentaire en ligne 8 du fichier dit explicitement *"plus besoin de l'exposer au JS"*). Si l'écran de recherche employeur (FR-1, "à construire") est piloté en JS/AJAX pour un filtrage dynamique sans rechargement de page, "les deux côtés le lisent directement" pourrait raisonnablement être lu comme un `fetch('/zones.json')` côté client — exposant le fichier par une route HTTP directe plutôt que par un include PHP. Ce n'est pas une incompatibilité de données (le contenu reste identique), mais c'est un flou d'implémentation qui n'est pas couvert par la Rule telle qu'écrite, et vaut d'être tranché si l'écran de recherche est interactif.

---

## Résumé des trous à combler

| # | Trou | Sévérité | Proposition |
|---|---|---|---|
| 1 | `zones_distances` peut diverger textuellement de `zones.json`/`cv.zone` (aucune garantie de provenance) | Majeur | Règle imposant génération/validation de `zones_distances` à partir de `zones.json` |
| 2 | Collation MySQL de `cv.zone`/`zones_distances` non fixée | Majeur | Fixer une collation explicite et identique (ex. `utf8mb4_bin` ou équivalent documenté) |
| 3 | Type de JOIN (INNER/LEFT) et cas réflexif (zone == zone) non tranchés | Majeur | Préciser AD-3 : lignes réflexives requises, ou requête garantissant le match exact par défaut |
| 4 | Pas de procédure de migration si `zones.json` change après coup | Moyen | Documenter l'obligation de migration ou l'ajouter explicitement à Deferred |
| 5 | Comportement de recherche pour `cv.zone` NULL/vide non spécifié | Mineur | Une phrase dans AD-3 ou Deferred |
| 6 | Mécanisme d'accès à `zones.json` côté recherche (PHP include vs endpoint JS) non fixé | Mineur | Clarifier si l'écran de recherche est interactif |
