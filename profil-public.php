<?php
// profil-public.php — Fiche publique d'un profil CV Express.
//
// Contexte de lecture : un employeur vient de scanner un QR code, il est
// sur un téléphone, souvent dehors et avec une connexion faible. Une seule
// action compte pour lui : appeler. Toute la page est bâtie autour de ça.
//
// Le numéro de CIN est volontairement absent du rendu : il est stocké pour
// l'opérateur, mais l'afficher sur une page publique exposerait un numéro
// d'identité nationale à n'importe qui possédant le lien.

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/bdd.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$erreur = '';

if ($id <= 0) {
    http_response_code(404);
    $erreur = 'Ce lien ne désigne aucun profil.';
} else {
    $stmt = bdd()->prepare(
        'SELECT donnees_json, rayon_km, date_creation
         FROM cv
         WHERE id = :id AND est_public = 1'
    );
    $stmt->execute([':id' => $id]);
    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ligne) {
        http_response_code(404);
        $erreur = "Ce profil n'existe pas ou n'est plus public.";
    } else {
        $donnees = json_decode($ligne['donnees_json'], true) ?: [];
        $pers    = $donnees['personnel'] ?? [];

        $prenom    = trim($pers['prenom'] ?? '');
        $nomFamille= trim($pers['nom'] ?? '');
        $nom       = trim($prenom . ' ' . $nomFamille);
        $metier    = trim($pers['titre_professionnel'] ?? '');
        $telephone = trim($pers['telephone'] ?? '');
        $email     = trim($pers['email'] ?? '');
        $ville     = trim($pers['ville'] ?? '');
        $photo     = trim($pers['photo_url'] ?? '');

        $rayon      = (int)$ligne['rayon_km'];
        $rayonLabel = $rayon >= 99 ? 'Plus de 10 km' : $rayon . ' km';

        // Initiales pour le monogramme, en repli si aucune photo.
        $initiales = mb_strtoupper(mb_substr($prenom, 0, 1) . mb_substr($nomFamille, 0, 1));

        $dateCreation = date('d/m/Y', strtotime($ligne['date_creation']));
        $telBrut      = preg_replace('/[^0-9+]/', '', $telephone);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $erreur ? 'Profil introuvable — CVMG' : htmlspecialchars($nom . ' — ' . $metier) ?></title>
<meta name="theme-color" content="#1655D8">
<?php if (!$erreur): ?>
<meta name="description" content="<?= htmlspecialchars($nom) ?>, <?= htmlspecialchars($metier) ?><?= $ville ? ' à ' . htmlspecialchars($ville) : '' ?>. Se déplace dans un rayon de <?= htmlspecialchars($rayonLabel) ?>.">
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="assets/cvmg.css">
<style>
  /* ---- Mise en page de la fiche ------------------------- */
  .page { min-height: 100vh; display: flex; flex-direction: column; }
  .contenu { flex: 1; padding-block: var(--e-6) var(--e-8); }

  /* ---- Bandeau d'identité ------------------------------- */
  .fiche { overflow: hidden; }

  .fiche__entete {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--e-3);
    padding: var(--e-3) var(--e-5);
    background: var(--c-surface-2);
    border-bottom: 1px solid var(--c-ligne);
  }

  .fiche__corps { padding: var(--e-5); }

  /* Identité : monogramme + nom + métier, alignés en ligne dès
     qu'il y a la place, empilés sur les très petits écrans. */
  .identite { display: flex; align-items: center; gap: var(--e-4); }

  .monogramme {
    width: 64px; height: 64px; flex-shrink: 0;
    border-radius: var(--r-rond);
    background: var(--c-bleu-pale);
    color: var(--c-bleu);
    display: grid; place-items: center;
    font-family: var(--police-titre);
    font-weight: 700; font-size: var(--t-xl);
    letter-spacing: .01em;
    overflow: hidden;
  }
  .monogramme img { width: 100%; height: 100%; object-fit: cover; }

  .identite__nom {
    font-size: var(--t-xl);
    font-weight: 700;
    letter-spacing: -.015em;
  }
  .identite__metier {
    font-size: var(--t-l);
    font-weight: 600;
    color: var(--c-bleu);
    margin-top: 2px;
  }

  /* ---- Faits : rayon, ville ----------------------------- */
  .faits {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: var(--e-4);
    margin-top: var(--e-5);
    padding-top: var(--e-5);
    border-top: 1px solid var(--c-ligne);
  }
  .fait__valeur {
    display: flex; align-items: center; gap: var(--e-2);
    font-size: var(--t-l); font-weight: 600;
    margin-top: var(--e-1);
  }
  .fait__valeur svg { width: 18px; height: 18px; color: var(--c-encre-3); flex-shrink: 0; }

  /* ---- Action d'appel -----------------------------------
     Le numéro lui-même fait office de bouton : l'employeur doit
     pouvoir le lire à voix haute autant que le taper du pouce. */
  .appel { margin-top: var(--e-5); }
  .appel__numero { font-size: var(--t-xl); letter-spacing: .01em; }
  .appel__note {
    margin-top: var(--e-2);
    font-size: var(--t-s);
    color: var(--c-encre-3);
    text-align: center;
  }

  /* ---- Pied de fiche ------------------------------------ */
  .fiche__pied {
    padding: var(--e-3) var(--e-5);
    background: var(--c-surface-2);
    border-top: 1px solid var(--c-ligne);
    font-size: var(--t-s);
    color: var(--c-encre-3);
    display: flex; justify-content: space-between; gap: var(--e-3); flex-wrap: wrap;
  }

  /* ---- Renvoi vers l'application ------------------------ */
  .renvoi {
    margin-top: var(--e-5);
    text-align: center;
    font-size: var(--t-s);
    color: var(--c-encre-3);
  }
  .renvoi a { color: var(--c-bleu); font-weight: 600; }
  .renvoi a:hover { text-decoration: underline; }

  /* ---- État vide ---------------------------------------- */
  .absent { text-align: center; padding-block: var(--e-8); }
  .absent__titre { font-size: var(--t-xl); font-weight: 700; margin-bottom: var(--e-2); }
  .absent p { color: var(--c-encre-2); margin-bottom: var(--e-5); }

  @media (max-width: 400px) {
    .identite { flex-direction: column; align-items: flex-start; gap: var(--e-3); }
    .fiche__corps { padding: var(--e-4); }
  }
</style>
</head>
<body class="page">

<header class="barre">
  <div class="enveloppe enveloppe--etroite barre__int">
    <a href="accueil.php" class="marque">CV<em>MG</em></a>
    <span class="libelle">Profil public</span>
  </div>
</header>

<main class="contenu">
  <div class="enveloppe enveloppe--etroite">

<?php if ($erreur): ?>

    <div class="absent">
      <h1 class="absent__titre">Profil introuvable</h1>
      <p><?= htmlspecialchars($erreur) ?></p>
      <a href="accueil.php" class="btn btn--principal">Retour à l'accueil</a>
    </div>

<?php else: ?>

    <article class="carte fiche">

      <div class="fiche__entete">
        <span class="libelle">CV Express</span>
        <span class="libelle chiffres">Créé le <?= $dateCreation ?></span>
      </div>

      <div class="fiche__corps">

        <div class="identite">
          <div class="monogramme" aria-hidden="true">
            <?php if ($photo): ?>
              <img src="<?= htmlspecialchars($photo) ?>" alt="">
            <?php else: ?>
              <?= htmlspecialchars($initiales) ?>
            <?php endif; ?>
          </div>
          <div>
            <h1 class="identite__nom"><?= htmlspecialchars($nom) ?></h1>
            <p class="identite__metier"><?= htmlspecialchars($metier) ?></p>
          </div>
        </div>

        <div class="faits">
          <div>
            <div class="libelle">Se déplace jusqu'à</div>
            <div class="fait__valeur">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                   stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                <circle cx="12" cy="10" r="3"/>
              </svg>
              <span><?= htmlspecialchars($rayonLabel) ?></span>
            </div>
          </div>

          <?php if ($ville): ?>
          <div>
            <div class="libelle">Basé à</div>
            <div class="fait__valeur">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                   stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 21h18M5 21V7l7-4 7 4v14"/>
                <path d="M10 21v-5h4v5"/>
              </svg>
              <span><?= htmlspecialchars($ville) ?></span>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <?php if ($telephone): ?>
          <div class="appel">
            <a href="tel:<?= htmlspecialchars($telBrut) ?>" class="btn btn--principal btn--large">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                   stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/>
              </svg>
              <span class="appel__numero chiffres"><?= htmlspecialchars($telephone) ?></span>
            </a>
            <p class="appel__note">Touchez le numéro pour appeler directement</p>
          </div>

        <?php elseif ($email): ?>
          <div class="appel">
            <a href="mailto:<?= htmlspecialchars($email) ?>" class="btn btn--principal btn--large">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                   stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="m2 7 10 6 10-6"/>
              </svg>
              <span>Écrire à <?= htmlspecialchars($prenom) ?></span>
            </a>
          </div>

        <?php else: ?>
          <div class="appel">
            <p class="appel__note">
              Aucun moyen de contact n'a été renseigné sur ce profil.
            </p>
          </div>
        <?php endif; ?>

      </div>

      <div class="fiche__pied">
        <span>Profil créé en cybercafé partenaire</span>
        <span>CVMG</span>
      </div>

    </article>

    <p class="renvoi">
      Vous cherchez du travail ?
      <a href="accueil.php">Créez votre CV gratuitement</a>
    </p>

<?php endif; ?>

  </div>
</main>

</body>
</html>
