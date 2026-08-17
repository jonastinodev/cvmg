# Déploiement sur Plesk (upload manuel)

Pas de build, pas de CI : c'est du PHP servi tel quel, comme en local avec
XAMPP. Le déploiement consiste à uploader les fichiers, créer la base, et
remplir `config.php` avec les vraies valeurs de production.

## 1. Base de données

Dans Plesk → **Bases de données** :

1. Créer une base MySQL (ex. `cvmg`) et un utilisateur dédié avec tous les
   droits sur cette base (ne pas utiliser un compte partagé entre plusieurs
   sites).
2. Ouvrir **phpMyAdmin** depuis Plesk sur cette base, puis exécuter les deux
   scripts SQL du dépôt **dans cet ordre** (contrainte de clé étrangère) :
   - `creer_table_utilisateurs.sql`
   - `creer_table_cv.sql`
3. Noter l'hôte, le nom de la base, l'utilisateur et le mot de passe : ils
   vont dans `config.php` (étape 3).

La table `cin_enregistrements` (flux CIN historique) n'est plus utilisée par
aucun code actif — inutile de la créer.

## 2. Fichiers à uploader

Uploader **tout le contenu du dépôt**, à l'exception de :

- `.git/`, `.claude/`, `.gitignore`
- `config.php` (n'existe pas encore en local de toute façon — voir étape 3)
- `config.php.example` et `DEPLOIEMENT.md` (documentation, pas nécessaires
  en prod, mais sans risque si uploadés — `.htaccess` bloque déjà l'accès
  direct aux `.md`)

**Important : inclure `vendor/`** même s'il est dans `.gitignore` (ce
`.gitignore` est pour Git, pas pour le déploiement). Il n'y a pas de
`composer.lock` dans ce projet — ne pas lancer `composer install` sur le
serveur, cela pourrait installer une autre version de dompdf que celle
testée en local. Uploader le dossier `vendor/` tel quel.

Destination : la racine du domaine dans Plesk (généralement
`httpdocs/`).

## 3. config.php (secrets)

Ce fichier n'est jamais dans le dépôt. Sur le serveur, via le **Gestionnaire
de fichiers Plesk** (pas d'upload FTP pour un fichier de secrets si évitable) :

1. Dupliquer `config.php.example` en `config.php`, dans le même dossier.
2. Remplir les 4 blocs : clé API Gemini, ID client Google, puis les
   identifiants MySQL créés à l'étape 1 (`DB_HOST` reste `localhost` dans la
   grande majorité des hébergements Plesk).

`.htaccess` bloque déjà l'accès HTTP direct à `config.php` — vérifier après
coup que `https://votredomaine.tld/config.php` renvoie bien une erreur
403/404 et pas le contenu du fichier.

## 4. Connexion Google — origine autorisée

Dans **Google Cloud Console → API et services → Identifiants**, sur le
client OAuth utilisé (`GOOGLE_CLIENT_ID`), ajouter le domaine de production
dans **Authorized JavaScript origins** :

```
https://votredomaine.tld
```

Sans ça, le bouton de connexion Google échoue silencieusement (erreur
d'origine dans la console navigateur) même si le reste du site fonctionne.

## 5. Vérifications côté hébergement Plesk

- **Version PHP** : 8.x (celle utilisée en local). À définir dans Plesk →
  domaine → Paramètres d'hébergement PHP.
- **Extensions PHP requises** — à activer dans Plesk → Paramètres PHP :
  - `pdo_mysql` (connexion base de données)
  - `mbstring` (requis par dompdf)
  - `dom` (requis par dompdf)
  - `curl` (appels à l'API Gemini et à `oauth2.googleapis.com`)
  - `gd` (nécessaire à dompdf pour intégrer les photos dans le PDF — c'est
    l'extension qu'il avait fallu activer en local, penser à la cocher ici
    aussi)
- **HTTPS** : activer un certificat SSL (Let's Encrypt via Plesk) avant la
  mise en ligne réelle. `session.php` détecte automatiquement HTTPS pour
  sécuriser le cookie de session (`secure: true`) — aucune modification de
  code nécessaire, juste s'assurer que le certificat est actif.
- **`.htaccess`** : le fichier utilisé en local (`Require all denied`,
  `mod_headers`, `mod_deflate`, `mod_expires`) est en syntaxe Apache 2.4,
  standard sur Plesk. Si des sections semblent ignorées, vérifier que
  `AllowOverride All` est actif pour le domaine (Plesk → Apache & nginx
  Settings).

## 6. Vérification post-déploiement

Dans l'ordre :

1. `https://votredomaine.tld/diag.php` — confirme que curl est chargé et que
   l'appel à l'API Gemini aboutit (accès bloqué au public par `.htaccess`,
   donc à tester uniquement depuis un accès direct type navigateur avec
   l'URL exacte pendant la vérification, à supprimer ou laisser bloqué
   ensuite).
2. `https://votredomaine.tld/test-pdf.php` — génère un CV d'exemple en PDF,
   valide toute la chaîne `cv-template.php` → dompdf.
3. Connexion réelle via Google, création d'un CV, upload d'une photo,
   téléchargement du PDF — pour vérifier la base de données et le champ
   photo de bout en bout.
4. Une fois les vérifications faites, envisager de supprimer `diag.php` et
   `test-pdf.php` du serveur de production (ils restent utiles pour du
   debug futur, mais n'ont pas besoin d'être accessibles en permanence).
