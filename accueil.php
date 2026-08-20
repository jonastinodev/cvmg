<?php
require_once __DIR__ . '/session.php';
$estConnecte  = !empty($_SESSION['utilisateur_id']);
$estOperateur = !empty($_SESSION['est_operateur']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>CVMG Créez votre CV professionnel facilement</title>
<meta name="description" content="Créez gratuitement un CV professionnel depuis votre téléphone. Préremplissage par scan de carte d'identité, export PDF, sans inscription obligatoire.">
<!-- Remplacer cvmg.mg par le domaine réel si différent -->
<link rel="canonical" href="https://cvmg.mg/">
<meta property="og:type" content="website">
<meta property="og:site_name" content="CVMG">
<meta property="og:locale" content="fr_FR">
<meta property="og:title" content="CVMG — Créez votre CV professionnel gratuitement">
<meta property="og:description" content="CV professionnel gratuit depuis votre téléphone, en quelques minutes. Scan de carte d'identité, export PDF.">
<meta property="og:url" content="https://cvmg.mg/">
<!-- TODO SEO : ajouter une image de partage 1200x630 (illustrations/og-cvmg.png) puis décommenter :
<meta property="og:image" content="https://cvmg.mg/illustrations/og-cvmg.png"> -->
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="CVMG — Créez votre CV professionnel gratuitement">
<meta name="twitter:description" content="CV professionnel gratuit depuis votre téléphone. Scan de carte d'identité, export PDF.">
<meta name="theme-color" content="#1863F2">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap');

  :root {
    --bleu: #1863F2; --bleu-marine: #0B1F3D; --bleu-clair: #EAF2FF;
    --orange: #F7941D; --orange-fonce: #DB7A00;
    --texte: #22262B; --gris-texte: #5B6472; --gris-fond: #F6F7F9;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }
  body { font-family: 'Inter', Arial, sans-serif; color: var(--texte); line-height: 1.55; }
  img, svg { display: block; max-width: 100%; }
  a { color: inherit; }
  /* Padding plafonné (clamp) et non en 6vw pur : au-delà de max-width, la boîte
     cesse de grandir mais un padding en vw continue, lui, de suivre l'écran et
     rétrécit le contenu de l'intérieur (950px de contenu sur un écran 1920px). */
  .enveloppe { max-width: 1180px; margin: 0 auto; padding: 0 clamp(20px, 5vw, 40px); }

  h1, h2, h3 { font-family: 'Poppins', sans-serif; color: var(--bleu-marine); line-height: 1.2; }

  /* ===== Boutons : base commune + variantes de taille centralisées ===== */
  .btn { display: inline-flex; align-items: center; justify-content: center; gap: 1.5mm;
    font-family: inherit; font-weight: 700; border: 1.5px solid transparent; border-radius: 2mm; cursor: pointer;
    text-decoration: none; white-space: nowrap; line-height: 1.2;
    transition: background .15s ease, opacity .15s ease, transform .15s ease, box-shadow .15s ease; }
  .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(11,31,61,.12); }
  .btn:active { transform: translateY(0); box-shadow: none; }
  .btn:focus-visible { outline: 2px solid var(--bleu); outline-offset: 2px; }
  .btn:disabled, .btn[aria-disabled="true"] { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }

  /* Taille par défaut (grands boutons isolés : hero, bandeau CTA) */
  .btn { padding: 3.6mm 7mm; font-size: 10.5pt; }
  /* Taille compacte (boutons intégrés à une ligne dense : nav) */
  .btn-sm { padding: 2.2mm 4.5mm; font-size: 9.5pt; border-radius: 1.6mm; }

  .btn-orange { background: var(--orange); border-color: var(--orange); color: #0B1F3D; }
  .btn-orange:hover { background: var(--orange-fonce); border-color: var(--orange-fonce); }
  .btn-outline { background: none; color: var(--bleu); border-color: var(--bleu); }
  .btn-outline:hover { background: var(--bleu-clair); }
  .btn-blanc { background: #fff; border-color: #fff; color: var(--bleu); }
  .btn-blanc:hover { background: var(--bleu-clair); border-color: var(--bleu-clair); }
  /* Variante pour l'action secondaire sur fond coloré (bandeau bleu) : un
     bouton plein blanc à côté d'un contour blanc, jamais deux pleins. */
  .btn-contour-blanc { background: none; border-color: rgba(255,255,255,.7); color: #fff; }
  .btn-contour-blanc:hover { background: rgba(255,255,255,.12); border-color: #fff; }
  /* CV Express réutilise l'orange du site plutôt qu'une seconde teinte
     concurrente : deux oranges qui se répondent sur la même page ne se
     distinguent pas, ils se font concurrence. */
  .btn-express { background: var(--orange); border-color: var(--orange); color: #0B1F3D; }
  .btn-express:hover { background: var(--orange-fonce); border-color: var(--orange-fonce); }
  /* Dans le hero, l'Express reste secondaire (contour) : la plupart des
     visiteurs ne sont pas opérateurs et ne peuvent pas l'utiliser sans
     compte dédié — le CTA plein doit rester le CV complet, accessible
     à tout le monde sans condition. */
  /* Contour neutre, pas orange : l'orange ne doit apparaître qu'une seule
     fois dans le hero (le bouton principal), sinon il cesse d'être un
     accent et devient juste "la couleur de la page". */
  .btn-express-hero { background: none; color: var(--texte); border-color: #E4E7EB; }
  .btn-express-hero:hover { background: var(--gris-fond); border-color: #CBD2DC; }

  /* Lien discret pour l'opérateur dans la nav : un badge, pas un second
     bouton plein qui concurrencerait "Créer mon CV". */
  .nav-tag-express {
    display: inline-flex; align-items: center; gap: 1.2mm;
    font-size: 9pt; font-weight: 600; color: var(--gris-texte);
    border: 1px solid #E4E7EB; border-radius: 20px; padding: 1.3mm 3mm;
    transition: border-color .15s, color .15s;
  }
  .nav-tag-express:hover { border-color: var(--bleu); color: var(--bleu); }
  .btn-badge { font-size: 8pt; font-weight: 600; background: rgba(11,31,61,.08);
    padding: 1px 6px; border-radius: 10px; letter-spacing: .02em; }

  /* ===== Nav ===== */
  nav { display: flex; align-items: center; justify-content: space-between; padding: 5mm 0; }
  .nav-logo { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 15pt; color: var(--bleu-marine); }
  .nav-logo span { color: var(--orange); }
  /* Les boutons d'action restent visibles à toutes les tailles ; seuls les
     liens texte secondaires sont masqués sur mobile (pas de menu burger requis). */
  .nav-liens { display: flex; align-items: center; gap: 3mm; font-weight: 600; font-size: 10pt; }
  .nav-liens > a:not(.btn) { display: none; }
  .nav-liens a:not(.btn):hover { color: var(--bleu); }
  @media (min-width: 800px) { .nav-liens { gap: 6mm; } .nav-liens > a:not(.btn) { display: inline; } }

  /* ===== Hero ===== */
  .hero { padding: 8mm 0 14mm; }
  .hero-grille { display: grid; gap: 10mm; align-items: center; }
  @media (min-width: 800px) { .hero-grille { grid-template-columns: 1.1fr 1fr; gap: 14mm; } }
  /* Titre fluide : vaut exactement 28pt dès ~530px de large (rendu desktop
     inchangé), mais se réduit en dessous au lieu de rester figé et de forcer
     des coupures de mots sur mobile. */
  .hero h1 { font-size: clamp(24px, 7vw, 28pt); font-weight: 800; letter-spacing: -.3pt; }
  /* Bleu, pas orange : l'orange est déjà pris par le bouton principal
     juste en dessous, deux touches de la même couleur dans le même
     écran ne se renforcent pas, elles diluent l'accent. */
  .hero h1 .accent { color: var(--bleu); }
  .hero p.lead { font-size: 12pt; color: var(--gris-texte); margin: 5mm 0 7mm; max-width: 46ch; }
  /* .btn est en white-space:nowrap : les deux boutons du héro totalisent ~480px
     incompressibles. Sans flex-wrap ils débordaient de l'écran sous 530px de
     large ; sous 560px on les empile en pleine largeur (cible tactile plus
     large et alignement propre, plutôt qu'un repli en escalier). */
  .hero-boutons { display: flex; align-items: center; gap: 3.5mm; flex-wrap: wrap; }
  @media (max-width: 560px) {
    .hero-boutons { flex-direction: column; align-items: stretch; }
    .hero-boutons .btn { width: 100%; }
  }
  .hero-reassurance { margin-top: 4mm; font-size: 9pt; color: var(--gris-texte); }

  /* Illustration héro : plus de carte de fond, léger flottement continu */
  .hero-illu { display: flex; align-items: center; justify-content: center; }
  .hero-illu img { max-width: 100%; height: auto; animation: flotter 4.5s ease-in-out infinite; }
  /* Sur mobile l'illustration passe sous le texte : la brider évite qu'un carré
     de 500px repousse le reste de la page hors du premier écran. */
  @media (max-width: 560px) { .hero-illu img { max-width: 300px; } }
  @keyframes flotter { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }

  /* ===== Comment ça marche ===== */
  .section { padding: 12mm 0; }
  .section-titre { text-align: center; margin-bottom: 10mm; }
  .section-titre h2 { font-size: 19pt; }
  .section-titre p { color: var(--gris-texte); margin-top: 2mm; }

  .etapes { display: grid; gap: 6mm; }
  @media (min-width: 800px) { .etapes { grid-template-columns: repeat(3, 1fr); } }
  .etape-carte { background: var(--gris-fond); border-radius: 4mm; padding: 8mm 6mm; text-align: center;
    transition: transform .15s ease, box-shadow .15s ease; }
  .etape-carte:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(11,31,61,.09); }
  .etape-numero { width: 9mm; height: 9mm; border-radius: 50%; background: var(--bleu); color: #fff;
    display: flex; align-items: center; justify-content: center; font-weight: 700; margin: 0 auto 4mm; font-size: 10pt; }
  .etape-carte h3 { font-size: 12pt; margin-bottom: 2mm; }
  .etape-carte p { font-size: 9.5pt; color: var(--gris-texte); }

  /* ===== Fonctionnalités alternées ===== */
  .fonction { display: grid; gap: 8mm; align-items: center; padding: 10mm 0; }
  @media (min-width: 800px) { .fonction { grid-template-columns: 1fr 1fr; } }
  .fonction.inverse .fonction-texte { order: 2; }
  .fonction-illu { display: flex; align-items: center; justify-content: center; }
  .fonction-illu img { max-width: 100%; height: auto; }
  .fonction-texte h3 { font-size: 17pt; margin-bottom: 3mm; }
  .fonction-texte p { color: var(--gris-texte); font-size: 10.5pt; margin-bottom: 5mm; }
  .fonction-liste { list-style: none; font-size: 10pt; }
  .fonction-liste li { display: flex; gap: 2.5mm; margin-bottom: 2.5mm; }
  .fonction-liste li::before { content: "✓"; color: var(--orange); font-weight: 800; }

  /* ===== Témoignage ===== */
  .temoignage-carte { background: var(--gris-fond); border-radius: 5mm; padding: 8mm; display: flex; gap: 6mm;
    align-items: flex-start; max-width: 640px; margin: 0 auto; transition: transform .15s ease, box-shadow .15s ease; }
  .temoignage-carte:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(11,31,61,.09); }
  .temoignage-avatar { width: 14mm; height: 14mm; border-radius: 50%; background: var(--bleu); color: #fff;
    display: flex; align-items: center; justify-content: center; font-weight: 700; font-family: 'Poppins',sans-serif; flex-shrink: 0; font-size: 11pt; }
  .temoignage-texte p { font-size: 10.5pt; font-style: italic; margin-bottom: 3mm; }
  .temoignage-nom { font-weight: 700; font-size: 9.5pt; }
  .temoignage-role { font-size: 9pt; color: var(--gris-texte); }

  /* ===== Bandeau CTA ===== */
  .cta-bandeau { background: var(--bleu); color: #fff; border-radius: 6mm; padding: 12mm 8mm; text-align: center; }
  .cta-bandeau h2 { color: #fff; font-size: 18pt; margin-bottom: 3mm; }
  .cta-bandeau p { color: #C9DBFF; margin-bottom: 6mm; }

  /* ===== Footer ===== */
  footer { border-top: 1px solid #E4E7EB; padding: 8mm 0; margin-top: 6mm; }
  .footer-grille { display: flex; flex-direction: column; gap: 4mm; align-items: center; text-align: center; }
  @media (min-width: 800px) { .footer-grille { flex-direction: row; justify-content: space-between; text-align: left; } }
  .footer-liens { display: flex; gap: 6mm; font-size: 9.5pt; color: var(--gris-texte); flex-wrap: wrap; justify-content: center; }
  .footer-liens a:hover { color: var(--bleu); }

  /* ===== Animations d'apparition au défilement ===== */
  .reveal { opacity: 0; transform: translateY(18px); transition: opacity .6s ease, transform .6s ease; }
  .reveal.visible { opacity: 1; transform: none; }
  .reveal-groupe .reveal:nth-child(2) { transition-delay: .1s; }
  .reveal-groupe .reveal:nth-child(3) { transition-delay: .2s; }

  /* ===== Section deux voies =====
     Une carte neutre (fond blanc, bordure fine) plutôt que deux aplats de
     couleur qui se répondent en miroir : la couleur ne sert qu'à la puce
     d'icône et au bouton, pas à noyer toute la carte. */
  .deux-voies { display: grid; gap: 6mm; margin-top: 8mm; }
  @media (min-width: 640px) { .deux-voies { grid-template-columns: 1fr 1fr; gap: 7mm; } }

  .voie-carte {
    background: #fff; border: 1px solid #E4E7EB; border-radius: 5mm;
    padding: 8mm 7mm; display: flex; flex-direction: column; gap: 4mm;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
  }
  .voie-carte:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(11,31,61,.09); border-color: transparent; }

  .voie-tete { display: flex; align-items: center; gap: 3.5mm; }
  .voie-icone {
    width: 11mm; height: 11mm; border-radius: 3mm; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
  }
  .voie-icone svg { width: 5.5mm; height: 5.5mm; }
  .voie-carte.express .voie-icone { background: #FFF3E2; color: var(--orange-fonce); }
  .voie-carte.complet .voie-icone { background: var(--bleu-clair); color: var(--bleu); }

  .voie-titre { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 13.5pt; color: var(--bleu-marine); }
  .voie-tag { font-size: 8.5pt; color: var(--gris-texte); }

  .voie-desc { font-size: 10pt; color: var(--gris-texte); line-height: 1.55; }

  .voie-puces { list-style: none; font-size: 9.5pt; display: flex; flex-direction: column; gap: 2mm; }
  .voie-puces li { display: flex; gap: 2.5mm; align-items: flex-start; color: var(--texte); }
  .voie-puces li::before { content: "✓"; flex-shrink: 0; font-weight: 800; }
  .voie-carte.express .voie-puces li::before { color: var(--orange-fonce); }
  .voie-carte.complet .voie-puces li::before { color: var(--bleu); }

  .voie-carte .btn { margin-top: 2mm; width: 100%; }
  .btn-bleu { background: var(--bleu); border-color: var(--bleu); color: #fff; }
  .btn-bleu:hover { background: #1251CC; border-color: #1251CC; }

  /* ===== Respect de la préférence de réduction des animations ===== */
  /* Respect de la préférence de réduction des animations */
  @media (prefers-reduced-motion: reduce) {
    * { transition-duration: .001ms !important; animation-duration: .001ms !important; animation-iteration-count: 1 !important; }
    .etape-carte:hover, .temoignage-carte:hover, .btn:hover { transform: none; }
    .hero-illu img { animation: none; }
  }
</style>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "CVMG",
  "url": "https://cvmg.mg/",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web",
  "description": "Générateur de CV en ligne gratuit, pensé pour Madagascar : rapide, mobile, sans inscription obligatoire.",
  "offers": { "@type": "Offer", "price": "0", "priceCurrency": "MGA" },
  "inLanguage": "fr"
}
</script>
</head>
<body>

<div class="enveloppe">
  <nav>
    <div class="nav-logo">CV<span>MG</span></div>
    <div class="nav-liens">
      <a href="#comment-ca-marche">Comment ça marche</a>
      <?php if ($estConnecte): ?>
        <a href="mes-cv.php" class="btn btn-sm btn-outline">Mes CV</a>
      <?php else: ?>
        <a href="connexion.php" class="btn btn-sm btn-outline">Se connecter</a>
      <?php endif; ?>
      <?php if ($estOperateur): ?>
        <a href="express-cv.php" class="nav-tag-express">⚡ CV Express</a>
      <?php endif; ?>
      <a href="creer-cv.php" class="btn btn-sm btn-orange">Créer mon CV</a>
    </div>
  </nav>
</div>

<section class="hero">
  <div class="enveloppe hero-grille">
    <div class="reveal">
      <h1>Un CV professionnel,<br><span class="accent">en quelques minutes</span></h1>
      <p class="lead">Deux voies selon votre situation : un parcours express en cybercafé, ou un formulaire complet à remplir à votre rythme. Dans les deux cas, votre CV est prêt en moins de 5 minutes.</p>
      <div class="hero-boutons">
        <a href="creer-cv.php" class="btn btn-orange">Créer mon CV gratuitement</a>
        <a href="express-cv.php" class="btn btn-express-hero">⚡ Voie Express <span class="btn-badge">Cybercafé</span></a>
      </div>
      <p class="hero-reassurance">Gratuit · Sans engagement · Depuis votre téléphone</p>
     </div>
    <div class="hero-illu reveal">
      <!-- Dépose ton illustration téléchargée ici : illustrations/hero.svg -->
      <img src="illustrations/hero.svg" alt="Illustration : création de CV en ligne" width="500" height="500" decoding="async">
    </div>
  </div>
</section>

<section class="section" id="deux-voies">
  <div class="enveloppe">
    <div class="section-titre reveal">
      <h2>Choisissez votre voie</h2>
      <p>Express en cybercafé, ou complet depuis chez vous — même résultat professionnel.</p>
    </div>
    <div class="deux-voies reveal">

      <div class="voie-carte express">
        <div class="voie-tete">
          <div class="voie-icone">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z"/>
            </svg>
          </div>
          <div>
            <div class="voie-titre">CV Express</div>
            <div class="voie-tag">En cybercafé, avec un opérateur</div>
          </div>
        </div>
        <p class="voie-desc">L'opérateur scanne votre carte d'identité, choisit votre métier et votre rayon de déplacement. En moins de 2 minutes, votre profil est en ligne avec un QR code à partager.</p>
        <ul class="voie-puces">
          <li>Scan de carte d'identité automatique</li>
          <li>Métier choisi parmi 83 métiers</li>
          <li>Profil public accessible par QR code</li>
        </ul>
        <a href="express-cv.php" class="btn btn-express">Démarrer le CV Express</a>
      </div>

      <div class="voie-carte complet">
        <div class="voie-tete">
          <div class="voie-icone">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M8 2h6l4 4v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Z"/>
              <path d="M14 2v4h4"/>
              <path d="M9 13h6M9 17h6"/>
            </svg>
          </div>
          <div>
            <div class="voie-titre">CV Complet</div>
            <div class="voie-tag">Seul·e, à votre rythme</div>
          </div>
        </div>
        <p class="voie-desc">Remplissez votre CV étape par étape, depuis votre téléphone ou ordinateur. Expériences, formations, compétences, langues — tout y est, avec un export PDF prêt à imprimer.</p>
        <ul class="voie-puces">
          <li>Formulaire guidé en 8 étapes</li>
          <li>Expériences et formations détaillées</li>
          <li>Aperçu fidèle avant téléchargement</li>
        </ul>
        <a href="creer-cv.php" class="btn btn-bleu">Créer mon CV complet</a>
      </div>

    </div>
  </div>
</section>

<section class="section" id="comment-ca-marche">
  <div class="enveloppe">
    <div class="section-titre reveal">
      <h2>Comment ça marche ?</h2>
      <p>Trois étapes, aucune compétence technique nécessaire.</p>
    </div>
    <div class="etapes reveal-groupe">
      <div class="etape-carte reveal">
        <div class="etape-numero">1</div>
        <h3>Remplissez vos informations</h3>
        <p>Vos coordonnées, expériences et compétences ou scannez directement votre carte d'identité.</p>
      </div>
      <div class="etape-carte reveal">
        <div class="etape-numero">2</div>
        <h3>Vérifiez l'aperçu</h3>
        <p>Vous prévisualisez votre CV dans une mise en page claire et professionnelle, avant de le télécharger.</p>
      </div>
      <div class="etape-carte reveal">
        <div class="etape-numero">3</div>
        <h3>Téléchargez en PDF</h3>
        <p>Prêt à imprimer ou à envoyer par téléphone, en un seul fichier.</p>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="enveloppe">

    <div class="fonction reveal">
      <div class="fonction-texte">
        <h3>Votre carte d'identité fait le travail</h3>
        <p>Pas envie de tout taper sur un petit écran ? Prenez votre carte en photo, et votre nom, prénom et ville se remplissent tout seuls.</p>
        <ul class="fonction-liste">
          <li>Une photo recto-verso, ou un PDF</li>
          <li>Vous vérifiez et corrigez avant de valider</li>
          <li>Rien n'est envoyé sans votre accord</li>
        </ul>
      </div>
      <div class="fonction-illu">
        <!-- Dépose ton illustration téléchargée ici : illustrations/feature-cin.svg -->
        <img src="illustrations/feature-cin.svg" alt="Illustration : scan de la carte d'identité" width="500" height="500" loading="lazy" decoding="async">
      </div>
    </div>

    <div class="fonction inverse reveal">
      <div class="fonction-texte">
        <h3>Pensé pour votre quotidien</h3>
        <p>Connexion lente, téléphone d'entrée de gamme, premier CV de votre vie : CVMG s'adapte à vous, pas l'inverse.</p>
        <ul class="fonction-liste">
          <li>Fonctionne même avec une connexion faible</li>
          <li>Gros boutons, texte simple, aucun jargon</li>
          <li>Pas encore d'expérience ? Vos projets et formations comptent aussi</li>
        </ul>
      </div>
      <div class="fonction-illu">
        <!-- Dépose ton illustration téléchargée ici : illustrations/feature-accessible.svg -->
        <img src="illustrations/feature-accessible.svg" alt="Illustration : application accessible sur mobile" width="500" height="500" loading="lazy" decoding="async">
      </div>
    </div>

  </div>
</section>

<!-- Section témoignage retirée : le témoignage affiché était fictif. À rétablir
     uniquement avec de vrais avis signés (sinon risque de pratique commerciale
     trompeuse). -->

<section class="section">
  <div class="enveloppe">
    <div class="cta-bandeau reveal">
      <h2>Prêt à créer votre CV ?</h2>
      <p>Gratuit, sans engagement, en moins de 5 minutes.</p>
      <div class="hero-boutons" style="justify-content:center;">
        <a href="creer-cv.php" class="btn btn-blanc">Créer mon CV complet</a>
        <a href="express-cv.php" class="btn btn-contour-blanc">⚡ Voie Express</a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/partials/pied.php'; ?>

<script>
// Animation d'apparition au défilement — natif, sans librairie.
// Si l'utilisateur préfère moins d'animations, on affiche tout directement.
const prefereMoinsAnimations = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const elementsAAnimer = document.querySelectorAll('.reveal');

if (prefereMoinsAnimations) {
  elementsAAnimer.forEach(el => el.classList.add('visible'));
} else {
  const observateur = new IntersectionObserver((entrees) => {
    entrees.forEach(entree => {
      if (entree.isIntersecting) {
        entree.target.classList.add('visible');
        observateur.unobserve(entree.target);
      }
    });
  }, { threshold: 0.15 });

  elementsAAnimer.forEach(el => observateur.observe(el));
}
</script>

</body>
</html>
