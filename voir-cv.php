<?php
// voir-cv.php — Affiche un CV enregistré en lecture, dans la mise en page du
// site (en-tête conservé, actions accessibles), et non en plein écran brut.
// Le CV lui-même est affiché via apercu-pdf.php : le VRAI PDF (dompdf) rendu
// nativement par le navigateur dans une iframe — pas une re-simulation HTML,
// qui ne peut pas être garantie identique au fichier téléchargé (un
// navigateur affiche le CV en défilement continu, dompdf le pagine
// réellement pour de vrai en A4).

require_once __DIR__ . '/session.php';
if (empty($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}
require_once __DIR__ . '/bdd.php';

$cvId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$cvId) {
    header('Location: mes-cv.php');
    exit;
}

$pdo = bdd();
$stmt = $pdo->prepare('SELECT titre FROM cv WHERE id = :id AND utilisateur_id = :uid');
$stmt->execute([':id' => $cvId, ':uid' => $_SESSION['utilisateur_id']]);
$ligne = $stmt->fetch();
$introuvable = !$ligne;
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

  /* Cadre d'aperçu : le navigateur affiche le PDF avec son propre lecteur
     natif (zoom, défilement, pages) — pas de mise à l'échelle manuelle. */
  .cadre-apercu iframe { width: 100%; height: 80vh; min-height: 500px; border: 0;
    display: block; background: #fff; box-shadow: 0 2px 14px rgba(11,31,61,.12); border-radius: 1mm; }

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
      <a href="apercu-pdf.php?id=<?= $cvId ?>&telecharger=1" class="btn btn-orange">Télécharger le PDF</a>
    </div>
  </div>

  <div class="cadre-apercu">
    <iframe src="apercu-pdf.php?id=<?= $cvId ?>" title="CV en PDF"></iframe>
  </div>
<?php endif; ?>
</main>

</body>
</html>
