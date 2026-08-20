# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Exécution

Pas de build, pas de bundler, pas de suite de tests. Le dossier est servi tel quel par XAMPP/Apache : `http://localhost/ocr-cin/<fichier>.php`. Modifier un fichier suffit, il n'y a rien à recompiler.

- `vendor/` est présent dans le dossier ; il n'y a **pas** de `composer.lock`. Éviter `composer install`/`update` sans raison (cela peut changer la version de dompdf déjà en place).
- Vérifications manuelles à la place des tests :
  - `test-pdf.php` — ouvre un CV d'exemple en PDF, valide toute la chaîne `cv-template.php` → dompdf sans passer par le formulaire.
  - `diag.php` — vérifie que curl est chargé et que l'appel à l'API Gemini aboutit (affiche l'erreur exacte).

## Langue du code

Tout est en français : commentaires, noms de variables et de fonctions (`repondreErreur`, `genererCvHtml`, `construireDonneesCV`, `utilisateur_id`, `donnees_json`), messages d'erreur renvoyés au navigateur. Le nouveau code doit suivre cette convention, y compris les clés JSON (`erreur`, `succes`, `donnees`).

## Une seule application

`accueil.php` → `connexion.php` → `creer-cv.php` / `mes-cv.php` / `express-cv.php`, plus les pages statiques (`apropos.php`, `contact.php`, `confidentialite.php`, `conditions.php`).

Le flux CIN autonome historique (`index.html`, `script.js`, `ocr.html`, `script-ocr.js`, `enregistrer.php`, table `cin_enregistrements`) a été **supprimé** (commit `78894bf`, durcissement sécurité — l'ancien `enregistrer.php` écrivait des identités sans authentification). Seul `ocr.php` subsiste, appelé depuis deux points d'entrée distincts : la modale de scan de `creer-cv.php` (fonctions préfixées `cin*`) et l'écran 1 d'`express-cv.php`. Une correction du scan doit être vérifiée sur les deux appelants.

## Contrat de données du CV

Trois représentations différentes, à garder synchronisées :

| Où | Forme | Exemple |
|---|---|---|
| État navigateur dans `creer-cv.php` (`let cv`) | camelCase, listes à plat | `titrePro`, `competences: ['PHP']`, `qualites: [...]` |
| Payload serveur / `donnees_json` en base | snake_case, listes typées | `titre_professionnel`, `competences: [{categorie:'technique', libelle:'PHP'}]` |
| Rendu | consommé par `genererCvHtml()` | idem payload |

La conversion se fait dans deux fonctions **miroir** de `creer-cv.php` : `construireDonneesCV()` (aller) et `chargerCVExistant()` (retour). Ajouter un champ au CV implique donc de toucher : le HTML du formulaire, `construireDonneesCV()`, `chargerCVExistant()` et `cv-template.php`. Oublier la fonction retour donne un champ qui se sauvegarde mais disparaît à la réouverture.

Le payload snake_case est envoyé tel quel à `apercu-cv.php`, `generer-pdf.php` et `enregistrer-cv.php` — les trois attendent la même structure.

## Aperçu et PDF : un seul gabarit

`cv-template.php::genererCvHtml()` est la **seule** source du rendu du CV. `apercu-cv.php` le renvoie en HTML (affiché dans une `<iframe srcdoc>` pour isoler les styles) et `generer-pdf.php` le passe à dompdf. Ne jamais dupliquer ce balisage côté client : l'aperçu ne serait plus fidèle au PDF.

Contraintes dompdf à respecter dans ce gabarit :
- unités en `mm`/`pt`, `@page { margin: 0 }`, largeur de page fixée à 210mm ;
- police `DejaVu Sans` (nécessaire pour les accents) ;
- `isRemoteEnabled` est activé uniquement pour les photos chargées par URL ;
- dompdf ne supporte qu'un sous-ensemble de CSS — toute modification de style doit être vérifiée en PDF (`test-pdf.php`), pas seulement dans l'aperçu.

Les couleurs du document CV (`--cv-bleu`, `--cv-vert`, `--cv-rouge`) sont volontairement distinctes du thème de l'application (`--app-*`) : le CV ne doit pas changer d'apparence quand le thème du site évolue.

## Authentification et sécurité

- Connexion **uniquement** via Google Identity Services. `verifier-google.php` valide le JWT auprès de `oauth2.googleapis.com/tokeninfo`, puis **vérifie que `aud === GOOGLE_CLIENT_ID`** — ce contrôle est indispensable, ne pas le retirer.
- Session : `$_SESSION['utilisateur_id' | 'utilisateur_email' | 'utilisateur_nom']`.
- Endpoints JSON : `require_once 'exiger-connexion.php'` en tête → 401 JSON si pas de session. Pages HTML : redirection manuelle `header('Location: connexion.php')` (voir `mes-cv.php`).
- Toute requête sur la table `cv` filtre sur `utilisateur_id` (y compris les `UPDATE`/`DELETE`, dont le `rowCount() === 0` sert de contrôle de propriété). Conserver ce motif pour tout nouvel endpoint CV.
- Réponses d'erreur uniformes : `{"erreur": "..."}` avec un code HTTP ; le front lit `data.erreur`.

## Base de données

MySQL via PDO. Connexion centralisée dans `bdd()` (`bdd.php`) : tout script y accède via `bdd()`, jamais `new PDO` directement — `EMULATE_PREPARES` désactivé, erreurs jamais renvoyées avec le DSN ni les identifiants. Aucun outil de migration : les fichiers `creer_table*.sql` sont à exécuter manuellement dans phpMyAdmin, dans l'ordre `utilisateurs` → `cv` (clé étrangère).

Le contenu du CV est stocké en JSON dans `cv.donnees_json` (LONGTEXT) — il n'y a pas de schéma relationnel pour les expériences/formations/compétences.

## config.php

Contient de vrais secrets (clé API Gemini, client ID Google, identifiants MySQL) et **n'est pas** un fichier d'exemple. Ne pas le publier, ne pas le coller dans une réponse, ne pas l'inclure dans un dépôt ni dans un artefact. `ocr.php` appelle Gemini côté serveur précisément pour que la clé ne soit jamais exposée au navigateur : garder tout appel à Gemini côté PHP.

## Brouillon local

`creer-cv.php` sauvegarde l'état complet dans `localStorage["cvmg_brouillon"]` à chaque frappe, et le supprime après un enregistrement réussi sur le compte. Un changement de forme de l'objet `cv` doit rester tolérant aux anciens brouillons (`Object.assign` sur l'objet par défaut, lecture entourée d'un `try`).
