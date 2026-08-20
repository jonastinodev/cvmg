<?php
// profil-public.php — Affiche un profil CV Express sans authentification.
// Accessible via profil-public.php?id=X (lien QR code ou tableau de bord opérateur).

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/bdd.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(404);
    $erreur = 'Identifiant manquant.';
} else {
    $pdo  = bdd();
    $stmt = $pdo->prepare(
        'SELECT donnees_json, type, rayon_km, date_creation
         FROM cv
         WHERE id = :id AND est_public = 1'
    );
    $stmt->execute([':id' => $id]);
    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ligne) {
        http_response_code(404);
        $erreur = 'Profil introuvable ou non public.';
    } else {
        $donnees  = json_decode($ligne['donnees_json'], true) ?: [];
        $pers     = $donnees['personnel'] ?? [];
        $nom      = trim(($pers['prenom'] ?? '') . ' ' . ($pers['nom'] ?? ''));
        $metier   = $pers['titre_professionnel'] ?? '';
        $telephone = $pers['telephone'] ?? '';
        $email     = $pers['email'] ?? '';
        $ville     = $pers['ville'] ?? '';
        $photo     = $pers['photo_url'] ?? '';
        $rayon     = (int)$ligne['rayon_km'];
        $rayonLabel = $rayon >= 99 ? 'Plus de 10 km' : $rayon . ' km';
        $dateCreation = date('d/m/Y', strtotime($ligne['date_creation']));
        $erreur = '';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $erreur ? 'Profil introuvable — CVMG' : htmlspecialchars($nom . ' · ' . $metier . ' — CVMG') ?></title>
<meta name="theme-color" content="#D97706">
<?php if (!$erreur): ?>
<meta name="description" content="<?= htmlspecialchars($nom) ?> — <?= htmlspecialchars($metier) ?>, disponible dans un rayon de <?= htmlspecialchars($rayonLabel) ?>.">
<?php endif; ?>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap');

  :root {
    --orange: #D97706; --orange-fonce: #B45A08; --orange-clair: #FFF8EC;
    --bleu-marine: #0B1F3D; --texte: #22262B; --gris: #5B6472;
    --fond: #F6F7F9; --blanc: #fff; --bordure: #E4E7EB;
    --vert: #16A34A; --vert-clair: #F0FDF4;
  }
  @media (prefers-color-scheme: dark) {
    :root:not([data-theme="light"]) {
      --fond: #0F1923; --blanc: #1A2535; --bordure: #263044;
      --texte: #E8EDF4; --gris: #8B97A8; --bleu-marine: #C8D8F0;
      --orange-clair: #2A1E08; --vert-clair: #052E16;
    }
  }
  :root[data-theme="dark"] {
    --fond: #0F1923; --blanc: #1A2535; --bordure: #263044;
    --texte: #E8EDF4; --gris: #8B97A8; --bleu-marine: #C8D8F0;
    --orange-clair: #2A1E08; --vert-clair: #052E16;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Inter', Arial, sans-serif;
    background: var(--fond); color: var(--texte);
    min-height: 100vh;
  }
  a { color: inherit; text-decoration: none; }

  /* ── nav ── */
  nav {
    background: var(--blanc); border-bottom: 1px solid var(--bordure);
  }
  .nav-int {
    max-width: 680px; margin: 0 auto; padding: 0 20px;
    display: flex; align-items: center; justify-content: space-between; height: 52px;
  }
  .logo {
    font-family: 'Poppins', sans-serif; font-weight: 800;
    font-size: 14pt; color: var(--bleu-marine); letter-spacing: -.3px;
  }
  .logo span { color: var(--orange); }
  .nav-lien { font-size: 9.5pt; color: var(--gris); }
  .nav-lien:hover { color: var(--texte); }

  /* ── contenu ── */
  main {
    max-width: 680px; margin: 0 auto;
    padding: 32px 20px 80px;
  }

  /* ── carte profil ── */
  .carte {
    background: var(--blanc); border: 1px solid var(--bordure);
    border-radius: 14px; overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
  }

  /* bandeau orange en haut */
  .carte-header {
    background: var(--orange); padding: 28px 28px 56px;
    position: relative;
  }
  .badge-express {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(255,255,255,.2); color: #fff;
    font-size: 8.5pt; font-weight: 600; letter-spacing: .04em; text-transform: uppercase;
    padding: 3px 10px; border-radius: 20px; margin-bottom: 12px;
  }

  /* avatar (photo ou initiales) */
  .avatar-wrap {
    position: absolute; bottom: -40px; left: 28px;
    width: 80px; height: 80px; border-radius: 50%;
    border: 4px solid var(--blanc);
    overflow: hidden; background: var(--orange-clair);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Poppins', sans-serif; font-size: 22pt; font-weight: 700;
    color: var(--orange); box-shadow: 0 2px 6px rgba(0,0,0,.12);
  }
  .avatar-wrap img {
    width: 100%; height: 100%; object-fit: cover;
  }

  /* corps de la carte */
  .carte-body { padding: 52px 28px 28px; }

  .nom-complet {
    font-family: 'Poppins', sans-serif; font-weight: 700;
    font-size: 17pt; color: var(--bleu-marine); margin-bottom: 4px;
    text-wrap: balance;
  }
  .metier-titre {
    font-size: 12pt; color: var(--orange); font-weight: 600;
    margin-bottom: 20px;
  }

  /* puces d'info */
  .infos { display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px; }
  .info-ligne {
    display: flex; align-items: center; gap: 10px;
    font-size: 10.5pt; color: var(--gris);
  }
  .info-icone {
    width: 32px; height: 32px; border-radius: 8px;
    background: var(--orange-clair); color: var(--orange);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; flex-shrink: 0;
  }
  .info-ligne strong { color: var(--texte); }

  /* disponibilité */
  .dispo {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--vert-clair); color: var(--vert);
    font-size: 9.5pt; font-weight: 600; padding: 5px 12px;
    border-radius: 20px; margin-bottom: 24px;
  }
  .dispo::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--vert); }

  /* bouton contact */
  .btn-contact {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; background: var(--orange); color: #fff;
    font-weight: 600; font-size: 11pt; padding: 13px 20px;
    border: none; border-radius: 9px; cursor: pointer;
    transition: background .15s; text-decoration: none;
  }
  .btn-contact:hover { background: var(--orange-fonce); }

  /* ── date de création ── */
  .date-creation {
    text-align: center; font-size: 9pt; color: var(--gris);
    margin-top: 16px;
  }

  /* ── page d'erreur ── */
  .erreur-page {
    text-align: center; padding: 80px 20px;
  }
  .erreur-icone { font-size: 40pt; margin-bottom: 16px; }
  .erreur-titre {
    font-family: 'Poppins', sans-serif; font-size: 16pt;
    font-weight: 700; color: var(--bleu-marine); margin-bottom: 10px;
  }
  .erreur-msg { color: var(--gris); font-size: 11pt; margin-bottom: 24px; }
  .btn-retour {
    display: inline-block; background: var(--orange); color: #fff;
    font-weight: 600; padding: 11px 24px; border-radius: 8px;
    transition: background .15s;
  }
  .btn-retour:hover { background: var(--orange-fonce); }

  /* ── footer ── */
  .pied {
    text-align: center; font-size: 9pt; color: var(--gris);
    padding: 24px 20px 0; border-top: 1px solid var(--bordure);
    margin-top: 40px;
  }
  .pied a { color: var(--orange); }

  @media (max-width: 480px) {
    .carte-body { padding: 52px 18px 22px; }
    .carte-header { padding: 22px 18px 52px; }
  }
</style>
</head>
<body>

<nav>
  <div class="nav-int">
    <a href="accueil.html" class="logo">CV<span>MG</span></a>
    <a href="accueil.html" class="nav-lien">Accueil</a>
  </div>
</nav>

<main>

<?php if ($erreur): ?>
  <div class="erreur-page">
    <div class="erreur-icone">🔍</div>
    <div class="erreur-titre">Profil introuvable</div>
    <p class="erreur-msg"><?= htmlspecialchars($erreur) ?></p>
    <a href="accueil.html" class="btn-retour">Retour à l'accueil</a>
  </div>

<?php else: ?>
  <div class="carte">

    <div class="carte-header">
      <div class="badge-express">⚡ CV Express</div>

      <div class="avatar-wrap">
        <?php if ($photo): ?>
          <img src="<?= htmlspecialchars($photo) ?>" alt="Photo de <?= htmlspecialchars($nom) ?>">
        <?php else: ?>
          <?= mb_strtoupper(mb_substr($pers['prenom'] ?? 'X', 0, 1)) ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="carte-body">
      <h1 class="nom-complet"><?= htmlspecialchars($nom) ?></h1>
      <p class="metier-titre"><?= htmlspecialchars($metier) ?></p>

      <div class="dispo">Disponible</div>

      <div class="infos">
        <div class="info-ligne">
          <div class="info-icone">📍</div>
          <span>Rayon de déplacement : <strong><?= htmlspecialchars($rayonLabel) ?></strong></span>
        </div>

        <?php if ($ville): ?>
        <div class="info-ligne">
          <div class="info-icone">🏙️</div>
          <span>Ville : <strong><?= htmlspecialchars($ville) ?></strong></span>
        </div>
        <?php endif; ?>

        <?php if ($telephone): ?>
        <div class="info-ligne">
          <div class="info-icone">📞</div>
          <span><strong><?= htmlspecialchars($telephone) ?></strong></span>
        </div>
        <?php endif; ?>

        <?php if ($email): ?>
        <div class="info-ligne">
          <div class="info-icone">✉️</div>
          <span><strong><?= htmlspecialchars($email) ?></strong></span>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($telephone): ?>
        <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $telephone)) ?>" class="btn-contact">
          📞 Contacter <?= htmlspecialchars($pers['prenom'] ?? '') ?>
        </a>
      <?php elseif ($email): ?>
        <a href="mailto:<?= htmlspecialchars($email) ?>" class="btn-contact">
          ✉️ Envoyer un message
        </a>
      <?php else: ?>
        <a href="accueil.html" class="btn-contact">
          ⚡ Créer votre propre CV Express
        </a>
      <?php endif; ?>

      <p class="date-creation">Profil créé le <?= $dateCreation ?></p>
    </div>
  </div>

<?php endif; ?>

</main>

<footer class="pied">
  Propulsé par <a href="accueil.html">CVMG</a> — Générateur de CV pour Madagascar
</footer>

</body>
</html>
