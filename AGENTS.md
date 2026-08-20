<!-- bmad:context -->
<!-- Verified 2026-08-20 against 4e77a3e. Managed by bmad-project-context; edits inside this block are replaced on refresh. Keep anything you want preserved outside the markers. -->

## CVMG

Générateur de CV pour Madagascar (Voie Complète) + mise en relation locale via cybercafé (Voie Express). PHP/MySQL (PDO), aucun build ni bundler, servi tel quel par XAMPP/Apache. Documentation technique complète dans `CLAUDE.md` à la racine — la lire avant de coder ici. Planification produit (PRFAQ, PRD) dans `_bmad-output/planning-artifacts/`.

## Where things are

- Contexte technique complet (contrat de données CV, contraintes dompdf, sécurité, langue du code) : `CLAUDE.md` — à lire en premier.
- Artefacts de planification BMAD : `_bmad-output/planning-artifacts/`.
- Socle CSS partagé, cible pour tout nouveau travail : `assets/cvmg.css` — voir Conventions ci-dessous.

## Conventions that differ from defaults

- Tout nouvel accès à la base passe par `bdd()` (`bdd.php`) — jamais `new PDO` directement. Connexion unique, `EMULATE_PREPARES` désactivé, erreurs jamais renvoyées avec le DSN ni les identifiants.
- `assets/cvmg.css` est le socle visuel cible pour toute nouvelle page ou refonte (tokens couleur/typo/espacement uniques). La migration n'est pas terminée : la plupart des pages ont encore leur propre bloc `:root` dupliqué (palette bleu+orange sur les pages marketing, palette orange isolée sur `express-cv.php`/`tableau-de-bord-operateur.php`). Ne pas supposer que la palette d'une page reflète le standard actuel.
- `ocr.php` est un point d'entrée partagé, appelé depuis deux pages distinctes (`creer-cv.php`, modale de scan ; `express-cv.php`, écran 1). Aucun flux CIN autonome ne subsiste par ailleurs — supprimé (voir Known pitfalls s'il faut comprendre pourquoi une référence ailleurs peut sembler dire le contraire).

## Known pitfalls

- Une fonction JS définie dans le `<script>` inline d'une page PHP n'existe pas dans celui d'une autre page — chaque page est un scope JS séparé. L'appeler depuis une autre page échoue en silence (`ReferenceError` en console, aucune erreur visible à l'écran). Vérifier qu'une fonction utilisée est bien définie dans le même fichier avant de copier un pattern d'une page à l'autre.
- Une déclaration `--variable: valeur;` placée hors d'un bloc `:root`/sélecteur (entre deux règles) est du CSS invalide qui casse silencieusement le parsing de la règle suivante — a déjà rendu des boutons sans fond visible.
- Un code HTTP 200 + une correspondance de texte dans le HTML ne prouve pas qu'un écran dépendant du JS fonctionne (select, grille, étapes dynamiques). Vérifier l'exécution réelle (navigateur, ou rendu du DOM après script) avant d'annoncer un test de bout en bout.

<!-- /bmad:context -->
