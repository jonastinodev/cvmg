<?php
// voir-cv.php — Affiche un CV enregistré en lecture, dans la mise en page du
// site (en-tête conservé, actions accessibles), et non en plein écran brut.
// Le CV lui-même est rendu par le MÊME gabarit que le PDF (cv-template.php),
// isolé dans une iframe pour que ses styles n'entrent pas en conflit avec ceux
// de l'application — même principe que l'aperçu de creer-cv.php.

require_once __DIR__ . '/session.php';
if (empty($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}
require_once __DIR__ . '/bdd.php';
require_once __DIR__ . '/cv-template.php';

$cvId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$cvId) {
    header('Location: mes-cv.php');
    exit;
}

$pdo = bdd();
$stmt = $pdo->prepare('SELECT titre, donnees_json FROM cv WHERE id = :id AND utilisateur_id = :uid');
$stmt->execute([':id' => $cvId, ':uid' => $_SESSION['utilisateur_id']]);
$ligne = $stmt->fetch();

if (!$ligne) {
    http_response_code(404);
    $introuvable = true;
} else {
    $introuvable = false;
    $donnees = json_decode($ligne['donnees_json'], true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $introuvable ? 'CV introuvable' : htmlspecialchars($ligne['titre']) ?> — CVMG</title>
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#1863F2">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap');
  :root {
    --bleu: #1863F2; --bleu-marine: #0B1F3D; --bleu-clair: #EAF2FF;
    --orange: #F7941D; --orange-fonce: #DB7A00; --rouge: #B00020;
    --texte: #22262B; --gris-texte: #5B6472; --gris-fond: #F6F7F9;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', Arial, sans-serif; color: var(--texte); background: #F0F2F5; }
  a { color: inherit; text-decoration: none; }
  .enveloppe { max-width: 1180px; margin: 0 auto; padding: 0 6vw; }

  /* En-tête identique à mes-cv.php : on ne perd jamais la navigation. */
  .barre { background: #fff; border-bottom: 1px solid #E4E7EB; }
  nav { display: flex; align-items: center; justify-content: space-between; padding: 5mm 0; gap: 4mm; }
  .nav-logo { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 15pt; color: var(--bleu-marine); }
  .nav-logo span { color: var(--orange); }
  .nav-droite { display: flex; align-items: center; gap: 4mm; font-size: 9.5pt; }
  .nav-nom { color: var(--gris-texte); }
  .nav-droite a { color: var(--bleu); font-weight: 600; }

  main { padding: 6mm 0 14mm; }
  /* Barre d'actions collante : « Télécharger le PDF » reste accessible même
     quand on fait défiler le CV, qui fait toute la hauteur d'une page A4. */
  .entete-page { display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 5mm; flex-wrap: wrap; gap: 3mm;
    position: sticky; top: 0; z-index: 5; background: #F0F2F5; padding: 4mm 0 3mm; }
  .titre-zone h1 { font-family: 'Poppins', sans-serif; color: var(--bleu-marine); font-size: 15pt;
    overflow-wrap: anywhere; }
  .retour { font-size: 9pt; color: var(--gris-texte); display: inline-block; margin-bottom: 1.5mm; }
  .retour:hover { color: var(--bleu); }

  .actions { display: flex; gap: 2.5mm; flex-wrap: wrap; }
  .btn { display: inline-flex; align-items: center; justify-content: center; font-family: inherit;
    font-weight: 700; font-size: 9.5pt; padding: 2.6mm 5mm; border-radius: 2mm;
    border: 1.5px solid transparent; cursor: pointer; transition: background .15s ease; }
  .btn-orange { background: var(--orange); border-color: var(--orange); color: #0B1F3D; }
  .btn-orange:hover { background: var(--orange-fonce); }
  .btn-contour { background: #fff; border-color: #DCE1E7; color: var(--texte); }
  .btn-contour:hover { background: var(--gris-fond); }
  .btn:disabled { opacity: .6; cursor: not-allowed; }

  /* Cadre d'aperçu : le CV est rendu à sa taille native (794px = A4 à 96dpi)
     puis réduit par transform pour tenir dans la largeur disponible. */
  .cadre-apercu { display: flex; justify-content: center; }
  .apercu-boite { position: relative; overflow: hidden; background: #fff;
    box-shadow: 0 2px 14px rgba(11,31,61,.12); border-radius: 1mm; }
  .apercu-boite iframe { width: 794px; height: 1123px; border: 0; display: block;
    transform-origin: top left; }

  .message-vide { background: #fff; border: 1px solid #E4E7EB; border-radius: 3mm;
    padding: 14mm 6mm; text-align: center; color: var(--gris-texte); }
  .message-vide p { margin-bottom: 5mm; }
</style>
</head>
<body>

<div class="barre">
  <div class="enveloppe">
    <nav>
      <a href="accueil.php" class="nav-logo">CV<span>MG</span></a>
      <div class="nav-droite">
        <span class="nav-nom">Bonjour, <?= htmlspecialchars($_SESSION['utilisateur_nom']) ?></span>
        <a href="mes-cv.php">Mes CV</a>
        <a href="deconnexion.php">Se déconnecter</a>
      </div>
    </nav>
  </div>
</div>

<main class="enveloppe">
<?php if ($introuvable): ?>
  <div class="message-vide">
    <p>Ce CV n'existe pas ou ne vous appartient pas.</p>
    <a href="mes-cv.php" class="btn btn-orange">Retour à mes CV</a>
  </div>
<?php else: ?>
  <div class="entete-page">
    <div class="titre-zone">
      <a href="mes-cv.php" class="retour">&larr; Retour à mes CV</a>
      <h1><?= htmlspecialchars($ligne['titre']) ?></h1>
    </div>
    <div class="actions">
      <a href="creer-cv.php?cv_id=<?= $cvId ?>" class="btn btn-contour">Modifier</a>
      <button type="button" class="btn btn-orange" id="btnTelecharger">Télécharger le PDF</button>
    </div>
  </div>

  <div class="cadre-apercu">
    <div class="apercu-boite" id="apercuBoite">
      <iframe id="apercuIframe" title="Aperçu du CV"
              srcdoc="<?= htmlspecialchars(genererCvHtml($donnees), ENT_QUOTES, 'UTF-8') ?>"></iframe>
    </div>
  </div>

  <script>
  // Données du CV, réutilisées telles quelles pour la génération du PDF :
  // même structure que celle attendue par generer-pdf.php.
  const donneesCV = <?= json_encode($donnees, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;

  const boite = document.getElementById('apercuBoite');
  const cadre = document.getElementById('apercuIframe');

  // Le contenu du CV a une largeur fixe (210mm) : il ne peut pas se réadapter
  // en largeur, on le met donc à l'échelle pour qu'il tienne dans l'écran.
  function ajusterEchelle() {
    const dispo = boite.parentElement.clientWidth;
    const echelle = Math.min(1, dispo / 794);
    cadre.style.transform = `scale(${echelle})`;
    boite.style.width = (794 * echelle) + 'px';
    boite.style.height = (1123 * echelle) + 'px';
  }
  ajusterEchelle();
  window.addEventListener('resize', ajusterEchelle);

  document.getElementById('btnTelecharger').addEventListener('click', async (e) => {
    const btn = e.currentTarget;
    const texteOriginal = btn.textContent;
    btn.textContent = 'Génération du PDF...'; btn.disabled = true;
    try {
      const res = await fetch('generer-pdf.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(donneesCV),
      });
      if (!res.ok) {
        const err = await res.json().catch(() => null);
        throw new Error((err && err.erreur) || 'Erreur serveur');
      }
      const blob = await res.blob();
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      const p = donneesCV.personnel || {};
      a.href = url;
      a.download = 'CV_' + [p.nom, p.prenom].filter(Boolean).join('_') + '.pdf';
      document.body.appendChild(a); a.click(); a.remove();
      URL.revokeObjectURL(url);
    } catch (err) {
      alert('Impossible de générer le PDF : ' + err.message);
    } finally {
      btn.textContent = texteOriginal; btn.disabled = false;
    }
  });
  </script>
<?php endif; ?>
</main>

</body>
</html>
