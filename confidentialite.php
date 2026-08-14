<?php
require_once __DIR__ . '/session.php';
$estConnecte = !empty($_SESSION['utilisateur_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Confidentialité CVMG</title>
<meta name="description" content="Politique de confidentialité de CVMG : données personnelles, scan de carte d'identité, comptes et durée de conservation.">
<meta name="robots" content="index, follow">
<!-- Remplacer cvmg.mg par le domaine réel si différent -->
<link rel="canonical" href="https://cvmg.mg/confidentialite.php">
<meta property="og:type" content="website">
<meta property="og:site_name" content="CVMG">
<meta property="og:locale" content="fr_FR">
<meta property="og:title" content="Confidentialité — CVMG">
<meta property="og:url" content="https://cvmg.mg/confidentialite.php">
<meta name="theme-color" content="#1863F2">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap');
  :root {
    --bleu: #1863F2; --bleu-marine: #0B1F3D; --bleu-clair: #EAF2FF;
    --orange: #F7941D; --texte: #22262B; --gris-texte: #5B6472; --gris-fond: #F6F7F9;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', Arial, sans-serif; color: var(--texte); line-height: 1.6; }
  a { color: inherit; }
  .lien { color: var(--bleu); }
  .enveloppe { max-width: 1180px; margin: 0 auto; padding: 0 6vw; }
  main.enveloppe { max-width: 900px; }
  h1, h2 { font-family: 'Poppins', sans-serif; color: var(--bleu-marine); line-height: 1.2; }

  nav { display: flex; align-items: center; justify-content: space-between; padding: 5mm 0; }
  .nav-logo { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 15pt; color: var(--bleu-marine); text-decoration: none; }
  .nav-logo span { color: var(--orange); }
  .btn { display: inline-flex; align-items: center; justify-content: center; gap: 1.5mm; font-family: inherit;
    font-weight: 700; font-size: 10.5pt; padding: 3.6mm 7mm; border-radius: 2mm; border: 1.5px solid transparent;
    cursor: pointer; text-decoration: none; white-space: nowrap; transition: background .15s ease, transform .15s ease; }
  .btn:hover { transform: translateY(-1px); }
  .btn-sm { padding: 2.2mm 4.5mm; font-size: 9.5pt; border-radius: 1.6mm; }
  .btn-orange { background: var(--orange); border-color: var(--orange); color: #0B1F3D; }
  .btn-outline { background: none; color: var(--bleu); border-color: var(--bleu); }
  .btn-outline:hover { background: var(--bleu-clair); }
  .nav-liens { display: flex; align-items: center; gap: 3mm; font-weight: 600; font-size: 10pt; }
  .nav-liens > a:not(.btn) { display: none; }
  .nav-liens a:not(.btn) { text-decoration: none; }
  .nav-liens a:not(.btn):hover { color: var(--bleu); }
  @media (min-width: 800px) { .nav-liens { gap: 6mm; } .nav-liens > a:not(.btn) { display: inline; } }

  main { padding: 10mm 0 16mm; }
  main h1 { font-size: 22pt; margin-bottom: 2mm; }
  .maj { color: var(--gris-texte); font-size: 9pt; margin-bottom: 8mm; }
  main h2 { font-size: 13pt; margin: 8mm 0 3mm; }
  main p, main li { color: var(--gris-texte); font-size: 10.5pt; margin-bottom: 3mm; }
  main ul { padding-left: 5mm; margin-bottom: 3mm; }
  main p strong, main li strong { color: var(--texte); }

  .avis { background: #FFF4E8; border-left: 3px solid var(--orange); padding: 4mm 5mm; border-radius: 1mm; font-size: 9.5pt; margin-bottom: 8mm; }
  .avis strong { color: var(--texte); }

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
  <h1>Politique de confidentialité</h1>
  <p class="maj">Dernière mise à jour : 14 août 2026.</p>

  <!-- Note interne (non affichée au public) : faire relire cette politique par un
       juriste avant publication officielle — loi malgache n° 2014-038 sur la
       protection des données, d'autant que CVMG traite des photos de carte d'identité. -->

  <h2>1. Ce que nous collectons</h2>
  <p>Lorsque vous créez un CV, vous nous transmettez les informations que vous saisissez vous-même : nom, coordonnées, expériences, formation, compétences, et toute information complémentaire que vous choisissez d'ajouter. Chaque champ est optionnel sauf indication contraire dans le formulaire.</p>

  <h2>2. Le scan de carte d'identité</h2>
  <p>Si vous utilisez la fonction de scan pour préremplir vos informations, la photo ou le PDF de votre carte est envoyé à un service d'intelligence artificielle tiers (Google Gemini) dans le seul but d'en extraire le texte : nom, prénom, date et lieu de naissance, adresse. Ce transfert implique un traitement par Google, susceptible d'avoir lieu en dehors de Madagascar.</p>
  <ul>
    <li>Le numéro de votre carte d'identité n'est jamais extrait ni conservé.</li>
    <li>L'image que vous importez n'est pas enregistrée sur nos serveurs : elle est traitée puis immédiatement supprimée.</li>
    <li>Vous vérifiez et corrigez les informations extraites avant qu'elles ne soient utilisées rien n'est validé automatiquement.</li>
  </ul>

  <h2>3. Ce que nous stockons</h2>
  <p><strong>Sans compte (mode invité) :</strong> vos informations restent dans votre navigateur pendant la saisie (un brouillon est enregistré localement sur votre appareil). Elles ne sont transmises à nos serveurs que le temps de générer l'aperçu et le fichier PDF, et ne sont pas conservées.</p>
  <p><strong>Avec un compte :</strong> si vous vous connectez avec Google pour retrouver vos CV, nous conservons sur nos serveurs votre identifiant Google, votre adresse e-mail, votre nom d'affichage et l'adresse de votre photo de profil Google, ainsi que le contenu des CV que vous choisissez d'enregistrer. Ces données sont conservées tant que vous ne supprimez pas les CV concernés ou votre compte.</p>

  <h2>4. Cookies et traceurs</h2>
  <p>CVMG n'utilise aucun cookie de suivi ni outil de mesure d'audience. Un seul cookie technique est déposé, uniquement lorsque vous vous connectez, pour maintenir votre session ouverte : il est indispensable au fonctionnement du compte et n'effectue aucun suivi.</p>

  <h2>5. Vos droits</h2>
  <p>Conformément à la loi malgache sur la protection des données personnelles, vous disposez d'un droit d'accès, de rectification, de suppression et d'opposition concernant vos données. En mode invité, vous les maîtrisez directement en vidant les données de votre navigateur. Avec un compte, vous pouvez supprimer chaque CV depuis la page « Mes CV » ; pour supprimer l'intégralité de votre compte et des données associées, contactez-nous via la page Contact.</p>

  <h2>6. Nous contacter</h2>
  <p>Pour toute question sur cette politique, rendez-vous sur notre page <a href="contact.php" class="lien">Contact</a>.</p>
</main>

<footer>
  <div class="enveloppe footer-grille">
    <a href="accueil.php" class="nav-logo" style="font-size:12pt">CV<span>MG</span></a>
    <div class="footer-liens">
      <a href="apropos.php">À propos</a>
      <a href="contact.php" class="lien">Contact</a>
      <a href="confidentialite.php">Confidentialité</a>
      <a href="conditions.php">Conditions d'utilisation</a>
    </div>
  </div>
</footer>

</body>
</html>
