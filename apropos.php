<?php
require_once __DIR__ . '/session.php';
$estConnecte = !empty($_SESSION['utilisateur_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>À propos CVMG</title>
<meta name="description" content="CVMG aide chacun à Madagascar à créer un CV clair et professionnel, gratuitement, même sans expérience ni diplôme.">
<!-- Remplacer cvmg.mg par le domaine réel si différent -->
<link rel="canonical" href="https://cvmg.mg/apropos.php">
<meta property="og:type" content="website">
<meta property="og:site_name" content="CVMG">
<meta property="og:locale" content="fr_FR">
<meta property="og:title" content="À propos de CVMG">
<meta property="og:description" content="CVMG aide chacun à Madagascar à créer un CV clair et professionnel, gratuitement.">
<meta property="og:url" content="https://cvmg.mg/apropos.php">
<meta name="twitter:card" content="summary">
<meta name="theme-color" content="#1863F2">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap');
  :root {
    --bleu: #1863F2; --bleu-marine: #0B1F3D; --bleu-clair: #EAF2FF;
    --orange: #F7941D; --orange-fonce: #DB7A00;
    --texte: #22262B; --gris-texte: #5B6472; --gris-fond: #F6F7F9;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', Arial, sans-serif; color: var(--texte); line-height: 1.6; }
  a { color: inherit; }
  .lien { color: var(--bleu); }
  .enveloppe { max-width: 1180px; margin: 0 auto; padding: 0 clamp(20px, 5vw, 40px); }
  main.enveloppe { max-width: 900px; }
  h1, h2 { font-family: 'Poppins', sans-serif; color: var(--bleu-marine); line-height: 1.2; }

  .btn { display: inline-flex; align-items: center; justify-content: center; gap: 1.5mm; font-family: inherit;
    font-weight: 700; font-size: 10.5pt; padding: 3.6mm 7mm; border-radius: 2mm; border: 1.5px solid transparent;
    cursor: pointer; text-decoration: none; white-space: nowrap;
    transition: background .15s ease, transform .15s ease, opacity .15s ease; }
  .btn:hover { transform: translateY(-1px); }
  .btn:focus-visible { outline: 2px solid var(--bleu); outline-offset: 2px; }
  .btn-sm { padding: 2.2mm 4.5mm; font-size: 9.5pt; border-radius: 1.6mm; }
  .btn-orange { background: var(--orange); border-color: var(--orange); color: #0B1F3D; }
  .btn-orange:hover { background: var(--orange-fonce); }
  .btn-outline { background: none; color: var(--bleu); border-color: var(--bleu); }
  .btn-outline:hover { background: var(--bleu-clair); }

  nav { display: flex; align-items: center; justify-content: space-between; padding: 5mm 0; }
  .nav-logo { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 15pt; color: var(--bleu-marine); text-decoration: none; }
  .nav-logo span { color: var(--orange); }
  .nav-liens { display: flex; align-items: center; gap: 3mm; font-weight: 600; font-size: 10pt; }
  .nav-liens > a:not(.btn) { display: none; }
  .nav-liens a:not(.btn) { text-decoration: none; transition: color .15s ease; }
  .nav-liens a:not(.btn):hover { color: var(--bleu); }
  @media (min-width: 800px) { .nav-liens { gap: 6mm; } .nav-liens > a:not(.btn) { display: inline; } }

  main { padding: 10mm 0 16mm; }
  main h1 { font-size: 24pt; margin-bottom: 6mm; }
  main h2 { font-size: 14pt; margin: 8mm 0 3mm; }
  main p { color: var(--gris-texte); font-size: 10.5pt; margin-bottom: 3mm; }
  main p strong { color: var(--texte); }
  .encart { background: var(--gris-fond); border-radius: 3mm; padding: 6mm; margin-top: 8mm; }

  footer { border-top: 1px solid #E4E7EB; padding: 8mm 0; margin-top: 10mm; }
  .footer-grille { display: flex; flex-direction: column; gap: 4mm; align-items: center; text-align: center; }
  @media (min-width: 800px) { .footer-grille { flex-direction: row; justify-content: space-between; text-align: left; } }
  .footer-liens { display: flex; gap: 6mm; font-size: 9.5pt; color: var(--gris-texte); flex-wrap: wrap; justify-content: center; }
  .footer-liens a { color: var(--gris-texte); text-decoration: none; }
  .footer-liens a:hover { color: var(--bleu); }
</style>
</head>
<body>

<div class="enveloppe">
  <nav>
    <a href="accueil.php" class="nav-logo">CV<span>MG</span></a>
    <div class="nav-liens">
      <a href="accueil.php#comment-ca-marche">Comment ça marche</a>
      <?php if ($estConnecte): ?>
        <a href="mes-cv.php" class="btn btn-sm btn-outline">Mes CV</a>
      <?php else: ?>
        <a href="connexion.php" class="btn btn-sm btn-outline">Se connecter</a>
      <?php endif; ?>
      <a href="creer-cv.php" class="btn btn-sm btn-orange">Créer mon CV</a>
    </div>
  </nav>
</div>

<main class="enveloppe">
  <h1>À propos de CVMG</h1>

  <p>CVMG est né d'un constat simple : à Madagascar, beaucoup de personnes qui cherchent un emploi n'ont jamais eu l'occasion d'apprendre à rédiger un CV pas parce qu'elles n'ont rien à montrer, mais parce que personne ne le leur a enseigné.</p>

  <p>Vendeurs, chauffeurs, agents de sécurité, aides ménagères, artisans, jeunes sans diplôme : ce sont eux, avant tout, que CVMG a été pensé pour aider.</p>

  <h2>Notre promesse</h2>
  <p>Créer un CV clair et professionnel ne devrait pas demander de compétences en informatique, ni une bonne connexion Internet, ni un ordinateur. Chez CVMG, tout est pensé pour fonctionner depuis un téléphone d'entrée de gamme, avec une connexion limitée, en français simple.</p>

  <h2>Comment ça fonctionne</h2>
  <p>Vous répondez à quelques questions simples vos informations, votre parcours, vos compétences ou vous scannez directement votre carte d'identité pour aller plus vite. En quelques minutes, vous repartez avec un CV en PDF, prêt à imprimer ou à envoyer.</p>

  <h2>Gratuit, et ça le reste</h2>
  <p>CVMG est gratuit pour les personnes qui créent leur CV. Ça ne changera pas c'est la raison d'être du projet.</p>

  <div class="encart">
    <p style="margin:0"><strong>Une question, une suggestion ?</strong> Rendez-vous sur notre page <a href="contact.php" class="lien">Contact</a>.</p>
  </div>

  <div style="margin-top:10mm">
    <a href="creer-cv.php" class="btn btn-orange">Créer mon CV gratuitement</a>
  </div>
</main>

<?php include __DIR__ . '/partials/pied.php'; ?>

</body>
</html>
