<?php
require_once __DIR__ . '/session.php';
$estConnecte = !empty($_SESSION['utilisateur_id']);
$nomUtilisateur = $estConnecte ? $_SESSION['utilisateur_nom'] : null;
$cvIdCharge = isset($_GET['cv_id']) ? (int)$_GET['cv_id'] : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Créer mon CV CVMG</title>
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#1863F2">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap');
  @import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined');
  :root {
    /* Thème de l'application (nouveau, bleu vif + orange) */
    --app-bleu: #1863F2; --app-bleu-fonce: #0B1F3D; --app-bleu-clair: #EAF2FF;
    --app-orange: #F7941D; --app-orange-fonce: #DB7A00; --app-rouge: #E5484D;
    --gris-fond: #F4F6F8; --gris-texte: #5B6472; --texte: #22262B;

    /* Couleurs figées du document CV — ne changent pas avec le thème de l'app */
    --cv-bleu: #1B3A6B; --cv-vert: #2E7D5B; --cv-rouge: #C8313E;
  }
  * { box-sizing: border-box; }
  body { font-family: 'Inter', Arial, sans-serif; color: var(--texte); background: #F0F2F5; margin: 0; }

  /* --- Bandeau de progression --- */
  .entete { background: #fff; border-bottom: 1px solid #E4E7EB; padding: 3.5mm 5mm 0; position: sticky; top: 0; z-index: 10; }
  .entete-interieur { max-width: 600px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 4mm; padding-bottom: 3.5mm; }

  .progression-barre { height: 3px; background: #E4E7EB; }
  .progression-remplissage { height: 100%; background: var(--app-bleu); border-radius: 0 2px 2px 0;
    transition: width .35s cubic-bezier(.4,0,.2,1); }
  .logo { font-family: 'Poppins', sans-serif; font-weight: 700; color: var(--app-bleu-fonce); font-size: 14pt; text-decoration: none; }
  .logo span { color: var(--app-orange); }

  .compte { display: flex; align-items: center; gap: 3mm; }
  .compte-pastille { width: 7mm; height: 7mm; border-radius: 50%; background: var(--app-bleu-clair);
    color: var(--app-bleu); font-weight: 700; font-size: 9pt; display: flex; align-items: center; justify-content: center; }
  .compte-lien { font-size: 9pt; font-weight: 600; color: var(--app-bleu); text-decoration: none; }
  .compte-lien:hover { text-decoration: underline; }
  .compte-lien.discret { color: var(--gris-texte); font-weight: 400; }

  .etape-compteur { font-size: 8pt; font-weight: 600; color: var(--gris-texte); letter-spacing: .4pt;
    text-transform: uppercase; margin-bottom: 3mm; }

  /* Transition douce entre écrans */
  .etape:not(.hidden) { animation: etape-entrer .22s ease; }
  @keyframes etape-entrer {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .pied-carte { margin-top: 7mm; padding-top: 4mm; border-top: 1px solid #EEF0F3; text-align: center; }
  .lien-reinitialiser { background: none; border: none; color: var(--gris-texte); font-size: 8.5pt;
    font-family: inherit; cursor: pointer; text-decoration: underline; padding: 1mm; }
  .lien-reinitialiser:hover { color: var(--app-rouge); }

  /* --- Mise en page (formulaire seul, plus d'aperçu en direct) --- */
  /* Mobile d'abord : la carte occupe toute la largeur de l'écran. Le double
     padding (conteneur 6mm + carte 6mm = 45px de chaque côté) ne laissait que
     285px de contenu utile sur un écran de 375px. La carte blanche détachée
     sur fond gris n'a de sens qu'à partir de 600px, où il y a de la marge. */
  .conteneur { max-width: 600px; margin: 0 auto; padding: 0; }
  .colonne-form { width: 100%; }

  .carte { background: #fff; padding: 5mm; }
  @media (min-width: 600px) {
    .conteneur { padding: 6mm; }
    .carte { border-radius: 3mm; padding: 6mm; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
  }

  h2.titre-etape { font-family: 'Poppins', sans-serif; font-size: 15pt; color: var(--app-bleu-fonce); margin: 0 0 2mm; }
  p.sous-titre { color: var(--gris-texte); font-size: 10pt; margin: 0 0 5mm; }

  label { display: block; font-size: 9.5pt; font-weight: 600; margin: 4mm 0 1.5mm; }
  label .facultatif { font-weight: 400; color: var(--gris-texte); }
  label .requis { color: var(--app-rouge); font-weight: 700; }
  .legende-requis { font-size: 8.5pt; color: var(--gris-texte); margin-bottom: 4mm; }
  .legende-requis span { color: var(--app-rouge); font-weight: 700; }
  input.en-erreur, textarea.en-erreur, select.en-erreur { border-color: var(--app-rouge) !important; }
  .msg-erreur-champ { font-size: 8.5pt; color: var(--app-rouge); margin-top: 1mm; }
  /* 16px minimum sur mobile : en dessous de ce seuil, iOS Safari zoome
     automatiquement sur le champ au focus et l'utilisateur doit dézoomer
     entre chaque question. On repasse à l'échelle en pt sur grand écran. */
  input[type=text], input[type=tel], input[type=email], input[type=date], select, textarea {
    width: 100%; padding: 3mm; border: 1.5px solid #DCE1E7; border-radius: 2mm; font-size: 16px; font-family: inherit;
  }
  @media (min-width: 600px) {
    input[type=text], input[type=tel], input[type=email], input[type=date], select, textarea { font-size: 10.5pt; }
  }
  input:focus, select:focus, textarea:focus { outline: 2px solid var(--app-bleu); border-color: var(--app-bleu); }
  textarea { resize: vertical; min-height: 20mm; }
  .aide { font-size: 8.5pt; color: var(--gris-texte); margin-top: 1mm; }

  /* Deux champs côte à côte tombent sous 150px de large sur mobile : on empile. */
  .ligne-2 { display: flex; flex-direction: column; }
  .ligne-2 > div { flex: 1; }
  @media (min-width: 600px) { .ligne-2 { flex-direction: row; gap: 4mm; } }

  /* min-height 44px : taille de cible tactile recommandée. Sans elle,
     « Choisir une photo » faisait 32px et « Précédent / Suivant » 43px. */
  .btn { font-family: inherit; font-size: 10.5pt; font-weight: 600; padding: 3.2mm 6mm; border-radius: 2mm;
    border: none; cursor: pointer; min-height: 44px;
    display: inline-flex; align-items: center; justify-content: center; }
  .btn-principal { background: var(--app-orange); color: #0B1F3D; }
  .btn-principal:hover { background: var(--app-orange-fonce); }
  .btn-secondaire { background: #fff; color: var(--app-bleu); border: 1.5px solid var(--app-bleu); }
  .btn-valider { background: var(--app-orange); color: #0B1F3D; }
  .btn-lien { background: none; color: var(--app-bleu); text-decoration: underline; padding: 2mm 0; }
  /* S'utilise sans la classe .btn (aspect lien) : sa zone tactile doit donc
     être posée ici. Le padding est invisible (fond transparent) mais rend le
     bouton atteignable au doigt — il faisait 26px de haut. */
  .btn-supprimer { background: none; border: none; font-family: inherit; cursor: pointer;
    color: var(--app-rouge); font-size: 9pt; text-decoration: underline;
    padding: 2.5mm 2mm; margin-right: -2mm; min-height: 40px; }
  .btn:disabled { opacity: .5; cursor: not-allowed; }

  /* Rédaction assistée : bouton flottant à l'intérieur du textarea. */
  .zona-ia { position: relative; }
  /* Le textarea cède 36px en bas pour que le bouton ne recouvre pas le texte saisi. */
  .zona-ia textarea { padding-bottom: 36px; }
  .btn-ia-flotant {
    position: absolute; bottom: 7px; right: 8px;
    background: var(--app-bleu-clair); color: var(--app-bleu);
    border: 1px solid #C3DAFF;
    font-family: inherit; font-size: 8.5pt; font-weight: 700; cursor: pointer;
    white-space: nowrap; padding: 1.5mm 3mm; border-radius: 5mm;
    display: inline-flex; align-items: center; gap: 1.5mm; line-height: 1;
    opacity: 0; pointer-events: none;
    transition: opacity .15s ease, background .15s ease;
  }
  /* Apparaît au survol ou au focus de la zone sur desktop */
  .zona-ia:hover .btn-ia-flotant,
  .zona-ia:focus-within .btn-ia-flotant { opacity: 1; pointer-events: auto; }
  /* Toujours visible sur mobile (pas de survol au doigt) */
  @media (max-width: 600px) { .btn-ia-flotant { opacity: 1; pointer-events: auto; } }
  .btn-ia-flotant:hover { background: #DBE9FF; }
  .btn-ia-flotant:disabled { opacity: .5 !important; cursor: wait; pointer-events: none; }
  .statut-ia { font-size: 8.5pt; margin-top: 1mm; }
  .statut-ia.erreur { color: var(--app-rouge); }

  /* Barre d'action collante sur mobile : l'étape 1 fait à elle seule 1,7 écran
     de haut, il fallait scroller jusqu'en bas pour trouver « Suivant » à chaque
     étape. Elle redevient un simple bloc en pied de carte sur grand écran. */
  .nav-etapes { display: flex; justify-content: space-between; gap: 3mm; margin-top: 6mm;
    position: sticky; bottom: 0; background: #fff; padding: 3mm 0;
    border-top: 1px solid #EEF0F3; z-index: 5; }
  .nav-etapes .btn { flex: 1; }
  @media (min-width: 600px) {
    .nav-etapes { position: static; background: none; border-top: none; padding: 0; }
    .nav-etapes .btn { flex: 0 0 auto; }
  }

  .bloc-repetable { border: 1px solid #E4E7EB; border-radius: 2mm; padding: 4mm; margin-top: 4mm; position: relative; }
  .bloc-repetable-entete { display: flex; justify-content: space-between; align-items: center; }

  .case-a-cocher { display: flex; align-items: flex-start; gap: 2.5mm; background: var(--app-bleu-clair); padding: 3.5mm; border-radius: 2mm; margin-top: 4mm; }
  .case-a-cocher input { margin-top: .6mm; }
  .case-a-cocher label { margin: 0; font-weight: 500; }

  .message-positif { background: #FFF4E8; border-left: 3px solid var(--app-orange); padding: 3.5mm; border-radius: 1mm; font-size: 9.5pt; margin-top: 4mm; }

  /* ===== Photo de profil ===== */
  .zone-photo { display: flex; align-items: center; gap: 4mm; margin-top: 4mm; }
  .photo-apercu { width: 20mm; height: 20mm; border-radius: 50%; background: var(--app-bleu-clair);
    display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;
    color: var(--app-bleu); font-weight: 700; font-size: 11pt; border: 1.5px solid #E4E7EB; }
  .photo-apercu img { width: 100%; height: 100%; object-fit: cover; }
  .photo-boutons { display: flex; flex-wrap: wrap; gap: 2mm; }
  .photo-boutons button { font-size: 9pt; padding: 2mm 4mm; }

  /* ===== Sélecteur de modèle de CV ===== */
  .zone-modeles { display: grid; grid-template-columns: repeat(2, 1fr); gap: 2.5mm; margin: 3mm 0 5mm; }
  @media (min-width: 480px) { .zone-modeles { grid-template-columns: repeat(4, 1fr); } }
  .carte-modele { border: 1.5px solid #E4E7EB; border-radius: 2mm; padding: 2.5mm; cursor: pointer;
    background: #fff; text-align: center; transition: border-color .15s ease; }
  .carte-modele:hover { border-color: var(--app-bleu); }
  .carte-modele.actif { border-color: var(--app-bleu); border-width: 2px; background: var(--app-bleu-clair); }
  .carte-modele .vignette { height: 14mm; border-radius: 1mm; margin-bottom: 1.5mm; display: flex; overflow: hidden; }
  .carte-modele .vignette .bande { width: 35%; }
  .carte-modele .vignette .reste { flex: 1; background: #fff; }
  .carte-modele .nom-modele { font-size: 8pt; font-weight: 600; color: var(--texte); }
  .modele-classique .bande { background: #1B3A6B; }
  .modele-bleu-marine .bande { background: #17325C; }
  .modele-olive .bande { background: #6F8158; }
  .modele-ocre .bande { background: #BFA046; }

  .tags-zone { display: flex; flex-wrap: wrap; gap: 2mm; padding: 2mm; border: 1.5px solid #DCE1E7; border-radius: 2mm; }
  .tag-pilule { background: var(--app-bleu-clair); padding: 1.5mm 1mm 1.5mm 2.5mm; border-radius: 1mm; font-size: 9.5pt; display: flex; align-items: center; gap: .5mm; }
  /* La croix de retrait est très sollicitée au doigt : 15px de haut, c'était
     intenable. On l'élargit sans grossir la pilule (padding, pas font-size). */
  .tag-pilule button { background: none; border: none; color: var(--gris-texte); cursor: pointer;
    font-size: 10pt; line-height: 1; padding: 2mm 2.5mm; margin: -2mm 0; }
  .tags-zone input { border: none; flex: 1; min-width: 40mm; padding: 1.5mm; }
  .tags-zone input:focus { outline: none; }

  .suggestions { display: flex; flex-wrap: wrap; gap: 2mm; margin-top: 2.5mm; }
  /* Sur mobile, taper une puce est le moyen principal d'ajouter une compétence
     (plus rapide que la saisie au clavier) : ces puces doivent être confortables. */
  .suggestion { font-size: 9.5pt; background: #fff; border: 1px dashed #C7CDD5; color: var(--gris-texte);
    padding: 2.5mm 3.5mm; border-radius: 5mm; cursor: pointer; min-height: 36px; }
  .suggestion:hover { border-color: var(--app-bleu); color: var(--app-bleu); }

  .hidden { display: none !important; }

  /* ===================== MODALE APERÇU DU CV ===================== */
  .modale-apercu-fond { position: fixed; inset: 0; background: rgba(11,31,61,.55); z-index: 40;
    display: none; align-items: center; justify-content: center; padding: 4mm; }
  .modale-apercu-fond.ouverte { display: flex; }
  .modale-apercu-boite { background: #fff; border-radius: 3mm; max-width: 720px; width: 100%;
    max-height: 94vh; display: flex; flex-direction: column; box-shadow: 0 8px 32px rgba(0,0,0,.25); }
  .modale-apercu-entete { display: flex; align-items: center; justify-content: space-between;
    padding: 4mm 5mm; border-bottom: 1px solid #E4E7EB; }
  .modale-apercu-entete strong { font-family: 'Poppins', sans-serif; font-size: 12pt; color: var(--app-bleu-fonce); }
  .modale-apercu-entete button { background: none; border: none; font-size: 13pt; color: var(--gris-texte); cursor: pointer; }
  .modale-apercu-corps { flex: 1; overflow-y: auto; padding: 4mm 5mm; background: #EDEFF2; }
  .apercu-note { font-size: 9pt; color: var(--gris-texte); text-align: center; margin-bottom: 3mm; }
  .apercu-cadre { overflow: hidden; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,.12); margin: 0 auto; }
  .apercu-cadre iframe { width: 794px; border: 0; display: block; transform-origin: top left; }
  .modale-apercu-pied { display: flex; gap: 3mm; padding: 4mm 5mm; border-top: 1px solid #E4E7EB; justify-content: flex-end; flex-wrap: wrap; }
  .modale-apercu-pied .btn { flex: 1; min-width: 32mm; }

  /* ===================== MODALE SCAN CIN ===================== */
  /* Portage fidèle de index.html (import) + ocr.html (vérification),
     isolé sous --cin-* / .cin-* pour ne pas interférer avec le thème CVMG.
     (La police d'icônes Material Symbols est importée en tête de feuille : un
      @import doit précéder toute règle CSS, sinon le navigateur l'ignore.) */

  .modale-cin-fond {
    position: fixed; inset: 0; background: rgba(28,27,31,.55); z-index: 50;
    display: none; align-items: center; justify-content: center; padding: 4mm;
    font-family: 'Roboto', 'Inter', sans-serif;
  }
  .modale-cin-fond.ouverte { display: flex; }
  .modale-cin {
    --cin-primaire: #6750a4; --cin-primaire-fonce: #4f378b; --cin-primaire-clair: #f5efff;
    --cin-conteneur: #eaddff; --cin-sur-conteneur: #21005d; --cin-surface: #fffbfe;
    --cin-fond: #fafafa; --cin-sur-surface-variante: #49454f; --cin-contour: #e0e0e0;
    --cin-erreur: #b00020; --cin-succes: #4CAF50; --cin-succes-fond: #E8F5E9;
    max-width: 480px; width: 100%; max-height: 92vh; overflow-y: auto;
    background: var(--cin-surface); border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,.2);
  }
  .cin-entete { display: flex; align-items: center; gap: 12px; padding: 20px;
    background: var(--cin-primaire); color: #fff; border-radius: 20px 20px 0 0; }
  .cin-entete .cin-drapeau { width: 32px; height: 24px; border-radius: 2px; object-fit: cover; }
  .cin-entete h3 { font-size: 18px; font-weight: 500; flex: 1; }
  .cin-entete button { background: none; border: none; color: #fff; font-size: 16pt; cursor: pointer; opacity: .85; }

  .cin-progression { display: flex; justify-content: space-between; margin: 20px; position: relative; }
  .cin-progression::before, .cin-progression::after { content: ""; position: absolute; top: 50%; left: 0; right: 0;
    height: 3px; transform: translateY(-50%); border-radius: 2px; }
  .cin-progression::before { background: var(--cin-contour); }
  .cin-progression::after { background: var(--cin-primaire); width: var(--cin-avancement, 0%); transition: width .3s; }
  .cin-etape { width: 28px; height: 28px; border-radius: 14px; background: var(--cin-contour);
    display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500;
    color: var(--cin-sur-surface-variante); z-index: 1; }
  .cin-etape.faite { background: var(--cin-primaire-fonce); color: #fff; }
  .cin-etape.active { background: var(--cin-primaire); color: #fff; }

  .cin-corps { padding: 4px 20px 20px; }
  .cin-corps h2 { font-size: 18px; font-weight: 500; margin-bottom: 14px; color: #212121; }
  .cin-corps > p { font-size: 13.5px; color: var(--cin-sur-surface-variante); margin-bottom: 14px; }

  /* --- Cartes d'import (reprises d'index.html) --- */
  .cin-carte-import {
    border: 2px dashed var(--cin-contour); border-radius: 12px; padding: 16px; text-align: center;
    margin-bottom: 12px; cursor: pointer; background: var(--cin-fond); display: flex; align-items: center;
    gap: 12px; transition: all .2s ease;
  }
  .cin-carte-import:hover { border-color: var(--cin-primaire); background: var(--cin-primaire-clair); }
  .cin-carte-import .material-symbols-outlined { font-size: 28px; color: var(--cin-primaire); }
  .cin-carte-import p { margin: 0; font-size: 14px; font-weight: 500; color: #212121; }
  .cin-carte-import.faite { border-color: var(--cin-succes); background: var(--cin-succes-fond); }
  .cin-carte-import.faite p, .cin-carte-import.faite .material-symbols-outlined { color: var(--cin-succes); }

  .cin-apercu-img { max-width: 100%; border-radius: 8px; margin-top: 8px; box-shadow: 0 2px 4px rgba(0,0,0,.1); }
  .cin-info-fichiers { margin-top: 14px; padding: 12px; background: var(--cin-fond); border-radius: 8px; font-size: 12.5px; }
  .cin-erreur-msg { color: var(--cin-erreur); font-size: 13px; margin-top: 8px; display: none; }
  .cin-erreur-msg.visible { display: block; }

  .cin-boutons-nav { display: flex; gap: 10px; margin-top: 20px; }
  .cin-boutons-nav button, .cin-btn { flex: 1; border: none; border-radius: 24px; padding: 14px 18px;
    font-size: 14.5px; font-weight: 500; cursor: pointer; font-family: inherit; }
  .cin-btn-suivant { background: var(--cin-primaire); color: #fff; }
  .cin-btn-suivant:hover { background: var(--cin-primaire-fonce); }
  .cin-btn-suivant:disabled { background: var(--cin-contour); color: #9a9a9a; cursor: not-allowed; }
  .cin-btn-precedent { background: var(--cin-fond); color: #212121; border: 1px solid var(--cin-contour) !important; }

  /* --- Écran de vérification (repris d'ocr.html) --- */
  .cin-titre-carte { text-align: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--cin-contour); }
  .cin-titre-carte div { color: var(--cin-primaire); font-size: 15px; margin-bottom: 4px; }
  .cin-champ { margin-bottom: 18px; }
  .cin-champ label { font-size: 13px; font-weight: 500; color: var(--cin-sur-surface-variante); margin-bottom: 6px; display: block; }
  .cin-champ input { width: 100%; padding: 14px; border: 1px solid var(--cin-contour); border-radius: 12px;
    font-size: 14pt; font-family: inherit; background: var(--cin-surface); color: #1c1b1f; }
  .cin-champ input:focus { outline: none; border-color: var(--cin-primaire); box-shadow: 0 0 0 2px var(--cin-conteneur); }
  .cin-btn-large { width: 100%; padding: 16px; margin-top: 8px; }
  .cin-statut { text-align: center; font-size: 13px; padding: 10px 0; color: var(--cin-sur-surface-variante); }
</style>
</head>
<body>

<div class="entete">
  <div class="entete-interieur">
    <a href="accueil.php" class="logo">CV<span>MG</span></a>
    <?php if ($estConnecte): ?>
      <div class="compte">
        <span class="compte-pastille"><?= htmlspecialchars(mb_strtoupper(mb_substr($nomUtilisateur, 0, 1))) ?></span>
        <a href="mes-cv.php" class="compte-lien">Mes CV</a>
        <a href="deconnexion.php" class="compte-lien discret">Se déconnecter</a>
      </div>
    <?php else: ?>
      <a href="connexion.php" class="compte-lien">Se connecter</a>
    <?php endif; ?>
  </div>
  <div class="progression-barre"><div class="progression-remplissage" id="barreProgression" style="width:12.5%"></div></div>
</div>

<div class="conteneur">

  <div class="colonne-form">
    <div class="carte">

      <div class="etape-compteur" id="etapeLabel">Étape 1 sur 8</div>

      <!-- ÉTAPE 1 : Identité -->
      <div class="etape" data-etape="1">
        <h2 class="titre-etape">Comment vous appelez-vous ?</h2>
        <p class="sous-titre">Votre nom apparaîtra en tête de votre CV.</p>

        <button type="button" class="btn btn-secondaire" id="btnScanCIN" style="width:100%;margin-bottom:3mm">📷 Remplir depuis ma carte d'identité</button>
        <p class="aide" id="scanStatut"></p>
        <p class="aide" style="color:var(--app-orange)" id="scanConfirmation"></p>

        <label for="nom">Nom <span class="requis">*</span></label>
        <input type="text" id="nom" required autocomplete="family-name" placeholder="Ex : RAKOTO">

        <label for="prenom">Prénoms <span class="facultatif">(facultatif)</span></label>
        <input type="text" id="prenom" autocomplete="given-name" placeholder="Ex : Jean">
      </div>

      <!-- ÉTAPE 2 : Métier -->
      <div class="etape hidden" data-etape="2">
        <h2 class="titre-etape">Quel métier cherchez-vous ?</h2>
        <p class="sous-titre">Ce titre apparaîtra en grand sur votre CV.</p>

        <label for="titrePro">Métier ou poste recherché <span class="requis">*</span></label>
        <input type="text" id="titrePro" required autocomplete="organization-title" placeholder="Ex : Vendeur, Agent de sécurité, Couturière">

        <label for="profilCourt" style="margin-top:5mm">Courte description <span class="facultatif">(facultatif)</span></label>
        <div class="zona-ia">
          <textarea id="profilCourt" placeholder="Ex : Personne sérieuse, motivée et ponctuelle, à la recherche d'un emploi dans la vente."></textarea>
          <button type="button" class="btn-ia-flotant" id="btnIaProfil" aria-label="Rédiger avec l'IA">✨ Rédiger</button>
        </div>
        <p class="statut-ia" id="statutIaProfil"></p>
      </div>

      <!-- ÉTAPE 3 : Coordonnées -->
      <div class="etape hidden" data-etape="3">
        <h2 class="titre-etape">Comment vous joindre ?</h2>
        <p class="sous-titre">Ces informations permettent aux employeurs de vous contacter.</p>

        <label for="telephone">Téléphone <span class="requis">*</span></label>
        <input type="tel" id="telephone" required autocomplete="tel" inputmode="tel" placeholder="034 12 345 67">

        <label for="email">E-mail <span class="facultatif">(facultatif)</span></label>
        <input type="email" id="email" autocomplete="email" inputmode="email" autocapitalize="off" spellcheck="false">

        <label for="ville">Ville / Région <span class="requis">*</span></label>
        <input type="text" id="ville" required autocomplete="address-level2" placeholder="Ex : Antananarivo">

        <label for="adresse">Adresse <span class="facultatif">(facultatif)</span></label>
        <input type="text" id="adresse" autocomplete="street-address">

        <div class="ligne-2">
          <div>
            <label for="dateNaissance">Date de naissance <span class="facultatif">(facultatif)</span></label>
            <input type="date" id="dateNaissance" autocomplete="bday">
          </div>
          <div>
            <label for="permis">Permis de conduire <span class="facultatif">(facultatif)</span></label>
            <select id="permis"><option value="">Ne pas préciser</option><option value="oui">Oui</option><option value="non">Non</option></select>
          </div>
        </div>
      </div>

      <!-- ÉTAPE 4 : Style du CV -->
      <div class="etape hidden" data-etape="4">
        <h2 class="titre-etape">Quel style vous plaît ?</h2>
        <p class="sous-titre">La mise en page peut être changée à tout moment.</p>

               <label style="margin-top:3mm">Photo <span class="facultatif">(facultatif)</span></label>
        <div class="zone-photo">
          <div class="photo-apercu" id="photoApercu">
            <span id="photoInitiales"></span>
            <img id="photoImg" style="display:none" alt="">
          </div>
          <div class="photo-boutons">
            <button type="button" class="btn btn-secondaire" id="btnChoisirPhoto">Choisir une photo</button>
            <button type="button" class="btn btn-lien hidden" id="btnRetirerPhoto">Retirer</button>
            <input type="file" id="photoFichier" accept="image/*" hidden>
          </div>
        </div>

        <label>Modèle de CV</label>
        <div class="zone-modeles" id="zoneModeles">
          <div class="carte-modele modele-classique" data-modele="classique" role="button" tabindex="0" aria-pressed="false" aria-label="Modèle Classique">
            <div class="vignette"><div class="bande"></div><div class="reste"></div></div>
            <div class="nom-modele">Classique</div>
          </div>
          <div class="carte-modele modele-bleu-marine" data-modele="bleu-marine" role="button" tabindex="0" aria-pressed="false" aria-label="Modèle Bleu marine">
            <div class="vignette"><div class="bande"></div><div class="reste"></div></div>
            <div class="nom-modele">Bleu marine</div>
          </div>
          <div class="carte-modele modele-olive" data-modele="olive" role="button" tabindex="0" aria-pressed="false" aria-label="Modèle Olive">
            <div class="vignette"><div class="bande"></div><div class="reste"></div></div>
            <div class="nom-modele">Olive</div>
          </div>
          <div class="carte-modele modele-ocre" data-modele="ocre" role="button" tabindex="0" aria-pressed="false" aria-label="Modèle Ocre">
            <div class="vignette"><div class="bande"></div><div class="reste"></div></div>
            <div class="nom-modele">Ocre</div>
          </div>
        </div>

 
        <p class="aide" id="photoStatut"></p>
      </div>

      <!-- ÉTAPE 5 : Expériences -->
      <div class="etape hidden" data-etape="5">
        <h2 class="titre-etape">Vos expériences</h2>
        <p class="sous-titre">Emplois, stages, missions, bénévolat, projets personnels; tout compte.</p>

        <div class="case-a-cocher">
          <input type="checkbox" id="sansExperience">
          <label for="sansExperience">Je n'ai pas encore d'expérience professionnelle</label>
        </div>
        <div class="message-positif hidden" id="messageSansExperience">
          Pas de souci. Vous pourrez mettre en valeur vos compétences, formations et activités bénévoles
          dans les étapes suivantes.
        </div>

        <div id="listeExperiences"></div>
        <button type="button" class="btn btn-lien" id="btnAjouterExperience">+ Ajouter une expérience</button>
      </div>

      <!-- ÉTAPE 6 : Formation -->
      <div class="etape hidden" data-etape="6">
        <h2 class="titre-etape">Votre formation</h2>
        <p class="sous-titre">Diplômes, formations suivies, certifications.</p>

        <div id="listeFormations"></div>
        <button type="button" class="btn btn-lien" id="btnAjouterFormation">+ Ajouter une formation</button>
      </div>

      <!-- ÉTAPE 7 : Compétences -->
      <div class="etape hidden" data-etape="7">
        <h2 class="titre-etape">Vos compétences</h2>
        <p class="sous-titre">Tapez une compétence puis appuyez sur Entrée pour l'ajouter.</p>

        <label for="saisieCompetence">Compétences techniques</label>
        <div class="tags-zone" id="zoneCompetences"><input type="text" id="saisieCompetence" placeholder="Ex : Vente, Cuisine, Conduite..."></div>
        <div class="suggestions" id="suggestionsCompetences"></div>

        <label for="saisieQualite" style="margin-top:6mm">Qualités personnelles</label>
        <div class="tags-zone" id="zoneQualites"><input type="text" id="saisieQualite" placeholder="Ex : Ponctuel, Motivé, Rigoureux..."></div>

        <label style="margin-top:6mm">Langues parlées</label>
        <div id="listeLangues"></div>
        <button type="button" class="btn btn-lien" id="btnAjouterLangue">+ Ajouter une langue</button>
      </div>

      <!-- ÉTAPE 8 : Compléments -->
      <div class="etape hidden" data-etape="8">
        <h2 class="titre-etape">Pour aller plus loin</h2>
        <p class="sous-titre">Tout est facultatif ne remplissez que ce qui vous concerne.</p>

        <label for="saisieInteret">Centres d'intérêt</label>
        <div class="tags-zone" id="zoneInterets"><input type="text" id="saisieInteret" placeholder="Ex : Football, Couture, Lecture..."></div>

        <label for="disponibilite">Disponibilité</label>
        <input type="text" id="disponibilite" placeholder="Ex : Disponible immédiatement">

        <label for="mobilite">Mobilité géographique</label>
        <input type="text" id="mobilite" placeholder="Ex : Mobile sur tout Antananarivo">

        <label for="benevolat">Activités bénévoles</label>
        <input type="text" id="benevolat">

        <label for="projets">Projets personnels</label>
        <input type="text" id="projets">

        <label for="certifications">Certifications</label>
        <input type="text" id="certifications">

        <label for="references">Références professionnelles</label>
        <input type="text" id="references">
      </div>

      <div class="nav-etapes">
        <button type="button" class="btn btn-secondaire" id="btnPrecedent">Précédent</button>
        <button type="button" class="btn btn-principal" id="btnSuivant">Suivant</button>
        <button type="button" class="btn btn-principal hidden" id="btnVoirApercu">Voir mon CV</button>
      </div>

      <div class="pied-carte">
        <button type="button" id="btnReinitialiser" class="lien-reinitialiser">Effacer et recommencer</button>
      </div>
    </div>
  </div>

</div>

<div class="modale-apercu-fond" id="modaleApercuFond">
  <div class="modale-apercu-boite" role="dialog" aria-modal="true" aria-label="Aperçu du CV">
    <div class="modale-apercu-entete">
      <strong>Votre CV</strong>
      <button type="button" id="btnFermerApercu" aria-label="Fermer">✕</button>
    </div>
    <div class="modale-apercu-corps">
      <p class="apercu-note">Vérifiez que tout est correct avant de télécharger.</p>
      <div class="apercu-cadre" id="apercuCadre">
        <iframe id="apercuIframe" title="Aperçu du CV"></iframe>
      </div>
    </div>
    <div class="modale-apercu-pied">
      <button type="button" class="btn btn-secondaire" id="btnCorriger">Corriger</button>
      <button type="button" class="btn btn-secondaire" id="btnTelecharger">Télécharger le PDF</button>
      <?php if ($estConnecte): ?>
      <button type="button" class="btn btn-principal" id="btnEnregistrerCompte">Enregistrer</button>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="modale-cin-fond" id="modaleCinFond">
  <div class="modale-cin" role="dialog" aria-modal="true" aria-label="Scanner ma carte d'identité">
    <div class="cin-entete">
      <img src="media/drapeau-madagascar.png" alt="MG" class="cin-drapeau" width="32" height="24" onerror="this.style.display='none'">
      <h3>Kara-panondrom-pirenena</h3>
      <button type="button" id="btnFermerCin" aria-label="Fermer">✕</button>
    </div>

    <div class="cin-progression" id="cinProgression">
      <div class="cin-etape active" id="cinEtape1">1</div>
      <div class="cin-etape" id="cinEtape2">2</div>
      <div class="cin-etape" id="cinEtape3">3</div>
    </div>

    <div class="cin-corps">

      <!-- Vue 1 : Import (reprise fidèle d'index.html) -->
      <div id="cinVueImport">
        <h2>Importer document</h2>

        <label class="cin-carte-import">
          <span class="material-symbols-outlined">upload_file</span>
          <p>Importer (1 PDF ou 2 images recto/verso)</p>
          <input type="file" id="cinFileInput" hidden accept="image/*,.pdf" multiple>
        </label>

        <label class="cin-carte-import" id="cinRectoCard">
          <span class="material-symbols-outlined">photo_camera</span>
          <p>Prendre photo Recto</p>
          <input type="file" id="cinCameraRecto" hidden accept="image/*" capture="environment">
        </label>

        <label class="cin-carte-import" id="cinVersoCard">
          <span class="material-symbols-outlined">photo_camera</span>
          <p>Prendre photo Verso</p>
          <input type="file" id="cinCameraVerso" hidden accept="image/*" capture="environment">
        </label>

        <div id="cinPreviewUpload"></div>
        <div class="cin-info-fichiers" id="cinFileInfo" style="display:none"></div>
        <div class="cin-erreur-msg" id="cinErrorMessage"></div>
        <div class="cin-boutons-nav" id="cinUploadButtons"></div>
      </div>

      <!-- Vue 2 : Confirmation (reprise fidèle du step-confirm d'index.html) -->
      <div id="cinVueConfirmation" class="hidden">
        <h2>Confirmation</h2>
        <p>Vos fichiers ont bien été importés. Cliquez sur <b>Suivant</b> pour lancer l'OCR.</p>
        <div id="cinPreviewConfirm"></div>
        <div class="cin-boutons-nav" id="cinConfirmButtons"></div>
      </div>

      <!-- Vue 3 : Vérification (reprise fidèle d'ocr.html) -->
      <div id="cinVueVerification" class="hidden">
        <div class="cin-titre-carte">
          <div>REPOBLIKAN'I MADAGASCAR</div>
          <div>KARA-PANONDROM-PIRENENA</div>
          <div style="font-size:12px;color:var(--cin-sur-surface-variante)">Vérifiez et corrigez si besoin</div>
        </div>

        <div class="cin-champ"><label>ANARANA / Nom</label><input type="text" id="cinNom"></div>
        <div class="cin-champ"><label>FANAMPIN'ANARANA / Prénom</label><input type="text" id="cinPrenom"></div>
        <div class="cin-champ"><label>TERAKA TAMIN'NY / Né le</label><input type="date" id="cinDateNaissance"></div>
        <div class="cin-champ"><label>TAO/a — Lieu de naissance</label><input type="text" id="cinLieuNaissance"></div>
        <div class="cin-champ"><label>FONENANA / Adresse</label><input type="text" id="cinAdresse"></div>

        <div class="cin-erreur-msg" id="cinErreurVerif"></div>
        <button type="button" class="cin-btn cin-btn-suivant cin-btn-large" id="btnValiderCin">Utiliser ces informations</button>
        <button type="button" class="cin-btn cin-btn-precedent cin-btn-large" id="btnRecommencerCin">Rescanner</button>
      </div>

    </div>
  </div>
</div>

<script>
// ===================== ÉTAT (tout en mémoire navigateur) =====================
const CLE_LOCALE = 'cvmg_brouillon';

let cv = {
  modele: 'classique',
  personnel: { nom:'', prenom:'', titrePro:'', profilCourt:'', telephone:'', email:'', ville:'', adresse:'', dateNaissance:'', permis:'', photo:'' },
  sansExperience: false,
  experiences: [],
  formations: [],
  competences: [],
  qualites: [],
  langues: [],
  interets: [],
  complementaire: { disponibilite:'', mobilite:'', benevolat:'', projets:'', certifications:'', references:'' }
};

// Reprise d'un brouillon existant (protection contre la perte de saisie)
try {
  const sauvegarde = localStorage.getItem(CLE_LOCALE);
  if (sauvegarde) cv = Object.assign(cv, JSON.parse(sauvegarde));
} catch(e) { /* brouillon illisible : on repart de zéro, sans bloquer l'utilisateur */ }

function sauvegarderLocalement() {
  localStorage.setItem(CLE_LOCALE, JSON.stringify(cv));
}

// ===================== NAVIGATION ENTRE ÉTAPES =====================
let etapeActuelle = 1;
const NB_ETAPES = 8;

function afficherEtape(n) {
  document.querySelectorAll('.etape').forEach(el => el.classList.add('hidden'));
  document.querySelector(`.etape[data-etape="${n}"]`).classList.remove('hidden');

  document.getElementById('etapeLabel').textContent = `Étape ${n} sur ${NB_ETAPES}`;
  document.getElementById('barreProgression').style.width = `${Math.round(n / NB_ETAPES * 100)}%`;

  document.getElementById('btnPrecedent').disabled = (n === 1);
  document.getElementById('btnSuivant').classList.toggle('hidden', n === NB_ETAPES);
  document.getElementById('btnVoirApercu').classList.toggle('hidden', n !== NB_ETAPES);

  etapeActuelle = n;
  window.scrollTo({ top: 0, behavior: 'smooth' });
  mettreAJourApercu();
}

// --- Validation des champs obligatoires (répartis sur les 3 premières étapes) ---
const CHAMPS_REQUIS = {
  1: [{ id: 'nom', libelle: 'Merci d\'indiquer votre nom.' }],
  2: [{ id: 'titrePro', libelle: 'Indiquez le métier ou le poste que vous recherchez.' }],
  3: [
    { id: 'telephone', libelle: 'Un numéro de téléphone est indispensable pour vous recontacter.' },
    { id: 'ville',     libelle: 'Indiquez votre ville ou votre région.' },
  ],
};

function effacerErreurs() {
  document.querySelectorAll('.en-erreur').forEach(el => el.classList.remove('en-erreur'));
  document.querySelectorAll('.msg-erreur-champ').forEach(el => el.remove());
}

function validerEtape(n) {
  effacerErreurs();
  const champs = CHAMPS_REQUIS[n] || [];
  let premierChampFautif = null;
  champs.forEach(champ => {
    const el = document.getElementById(champ.id);
    if (!el || !el.value.trim()) {
      if (el) {
        el.classList.add('en-erreur');
        const msg = document.createElement('p');
        msg.className = 'msg-erreur-champ';
        msg.textContent = champ.libelle;
        el.parentNode.insertBefore(msg, el.nextSibling);
        if (!premierChampFautif) premierChampFautif = el;
      }
    }
  });
  if (premierChampFautif) {
    premierChampFautif.focus();
    premierChampFautif.scrollIntoView({ block: 'center', behavior: 'smooth' });
    return false;
  }
  return true;
}

document.getElementById('btnSuivant').addEventListener('click', () => {
  if (!validerEtape(etapeActuelle)) return;
  if (etapeActuelle < NB_ETAPES) afficherEtape(etapeActuelle + 1);
});
document.getElementById('btnPrecedent').addEventListener('click', () => { if (etapeActuelle > 1) afficherEtape(etapeActuelle - 1); });

// ===================== ÉTAPE 1 : INFOS PERSONNELLES =====================
const champsPersonnel = ['nom','prenom','titrePro','profilCourt','telephone','email','ville','adresse','dateNaissance','permis'];
const idChamp = { titrePro:'titrePro', profilCourt:'profilCourt' };

function lierChamp(id, cle, objet) {
  const el = document.getElementById(id);
  el.value = objet[cle] || '';
  el.addEventListener('input', () => { objet[cle] = el.value; sauvegarderLocalement(); mettreAJourApercu(); });
}
lierChamp('nom', 'nom', cv.personnel);
lierChamp('prenom', 'prenom', cv.personnel);
document.getElementById('nom').addEventListener('input', () => majApercuPhoto());
document.getElementById('prenom').addEventListener('input', () => majApercuPhoto());
lierChamp('titrePro', 'titrePro', cv.personnel);
lierChamp('profilCourt', 'profilCourt', cv.personnel);
lierChamp('telephone', 'telephone', cv.personnel);
lierChamp('email', 'email', cv.personnel);
lierChamp('ville', 'ville', cv.personnel);
lierChamp('adresse', 'adresse', cv.personnel);
lierChamp('dateNaissance', 'dateNaissance', cv.personnel);
lierChamp('permis', 'permis', cv.personnel);

// ===================== RÉDACTION ASSISTÉE PAR IA =====================
// Un seul chemin pour les deux boutons (accroche du profil et missions d'une
// expérience) : c'est generer-texte.php qui choisit la consigne selon le "type".
// Le contexte est construit à la demande, au moment du clic, pour refléter ce
// que l'utilisateur vient de saisir.
async function genererTexteIA({ bouton, zone, statut, type, contexte }) {
  if (zone.value.trim() && !confirm("Remplacer le texte déjà saisi par une nouvelle proposition ?")) return;

  const libelleOrigine = bouton.textContent;
  bouton.disabled = true;
  bouton.textContent = 'Rédaction…';
  statut.className = 'statut-ia';
  statut.textContent = '';

  try {
    const res = await fetch('generer-texte.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ type, contexte: contexte() }),
    });
    const data = await res.json().catch(() => null);
    if (!res.ok || !data || data.erreur) {
      throw new Error((data && data.erreur) || `Erreur serveur (HTTP ${res.status})`);
    }

    zone.value = data.texte;
    // Rejoue l'événement d'entrée : c'est lui qui met à jour l'objet cv,
    // le brouillon local et l'aperçu (cf. lierChamp / creerBlocExperience).
    zone.dispatchEvent(new Event('input', { bubbles: true }));
    statut.textContent = 'Proposition de l’IA, relisez et corrigez librement.';
  } catch (err) {
    statut.className = 'statut-ia erreur';
    statut.textContent = err.message;
  } finally {
    bouton.disabled = false;
    bouton.textContent = libelleOrigine;
  }
}

document.getElementById('btnIaProfil').addEventListener('click', (e) => genererTexteIA({
  bouton: e.currentTarget,
  zone: document.getElementById('profilCourt'),
  statut: document.getElementById('statutIaProfil'),
  type: 'profil',
  contexte: () => ({
    titrePro: cv.personnel.titrePro,
    ville: cv.personnel.ville,
    sansExperience: cv.sansExperience,
    experiences: cv.experiences.map(x => [x.poste, x.employeur].filter(Boolean).join(' chez ')).filter(Boolean),
    competences: cv.competences,
    qualites: cv.qualites,
  }),
}));

// --- Photo de profil : redimensionnée et recadrée en carré côté navigateur
// (canvas), puis stockée directement en data URI dans le CV. Pas d'envoi au
// serveur ni de stockage de fichier séparé : ça reste cohérent avec le reste
// de l'app (tout est dans donnees_json), et ça évite d'avoir à gérer un
// dossier d'upload. La compression garde le poids raisonnable même si la
// photo d'origine vient d'un téléphone (plusieurs Mo). ---
const photoApercu = document.getElementById('photoApercu');
const photoInitialesEl = document.getElementById('photoInitiales');
const photoImgEl = document.getElementById('photoImg');
const btnRetirerPhoto = document.getElementById('btnRetirerPhoto');

function majApercuPhoto() {
  if (cv.personnel.photo) {
    photoImgEl.src = cv.personnel.photo;
    photoImgEl.style.display = 'block';
    photoInitialesEl.style.display = 'none';
    btnRetirerPhoto.classList.remove('hidden');
  } else {
    photoImgEl.style.display = 'none';
    photoInitialesEl.style.display = 'block';
    const i1 = (cv.personnel.prenom || '').trim().charAt(0);
    const i2 = (cv.personnel.nom || '').trim().charAt(0);
    photoInitialesEl.textContent = (i1 + i2).toUpperCase();
    btnRetirerPhoto.classList.add('hidden');
  }
}

document.getElementById('btnChoisirPhoto').addEventListener('click', () => document.getElementById('photoFichier').click());

document.getElementById('photoFichier').addEventListener('change', (e) => {
  const fichier = e.target.files[0];
  const statut = document.getElementById('photoStatut');
  e.target.value = '';
  if (!fichier) return;
  if (!fichier.type.startsWith('image/')) {
    statut.textContent = "Merci de choisir un fichier image.";
    return;
  }

  statut.textContent = 'Traitement de la photo...';
  const image = new Image();
  const lecteur = new FileReader();
  lecteur.onload = () => { image.src = lecteur.result; };
  image.onload = () => {
    // Recadrage carré centré, puis redimensionnement à une taille raisonnable
    // pour le PDF (400x400 suffit largement, même en haute résolution papier).
    const cote = Math.min(image.width, image.height);
    const decalageX = (image.width - cote) / 2;
    const decalageY = (image.height - cote) / 2;
    const taille = 400;
    const canevas = document.createElement('canvas');
    canevas.width = taille; canevas.height = taille;
    const ctx = canevas.getContext('2d');
    ctx.drawImage(image, decalageX, decalageY, cote, cote, 0, 0, taille, taille);
    cv.personnel.photo = canevas.toDataURL('image/jpeg', 0.85);
    majApercuPhoto();
    sauvegarderLocalement(); mettreAJourApercu();
    statut.textContent = '';
  };
  image.onerror = () => { statut.textContent = "Impossible de lire cette image."; };
  lecteur.onerror = () => { statut.textContent = "Impossible de lire ce fichier."; };
  lecteur.readAsDataURL(fichier);
});

btnRetirerPhoto.addEventListener('click', () => {
  cv.personnel.photo = '';
  majApercuPhoto();
  sauvegarderLocalement(); mettreAJourApercu();
});

majApercuPhoto();

// --- Sélecteur de modèle de CV ---
function selectionnerModele(id) {
  cv.modele = id;
  document.querySelectorAll('.carte-modele').forEach(carte => {
    const actif = carte.dataset.modele === id;
    carte.classList.toggle('actif', actif);
    carte.setAttribute('aria-pressed', actif ? 'true' : 'false');
  });
}
document.querySelectorAll('.carte-modele').forEach(carte => {
  carte.addEventListener('click', () => {
    selectionnerModele(carte.dataset.modele);
    sauvegarderLocalement(); mettreAJourApercu();
  });
  carte.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      selectionnerModele(carte.dataset.modele);
      sauvegarderLocalement(); mettreAJourApercu();
    }
  });
});
selectionnerModele(cv.modele);

// --- Scan CIN : modale dédiée, portage fidèle d'index.html + ocr.html + ocr.php ---
const cinFileInput = document.getElementById('cinFileInput');
const cinPreviewUpload = document.getElementById('cinPreviewUpload');
const cinUploadButtons = document.getElementById('cinUploadButtons');
const cinFileInfo = document.getElementById('cinFileInfo');
const cinErrorMessage = document.getElementById('cinErrorMessage');
const cinPreviewConfirm = document.getElementById('cinPreviewConfirm');
const cinConfirmButtons = document.getElementById('cinConfirmButtons');
const cinRectoCard = document.getElementById('cinRectoCard');
const cinVersoCard = document.getElementById('cinVersoCard');
const cinCameraRecto = document.getElementById('cinCameraRecto');
const cinCameraVerso = document.getElementById('cinCameraVerso');

let cinSelectedFiles = [];
let cinRectoFile = null;
let cinVersoFile = null;

function cinShowPreview(files, container) {
  container.innerHTML = '';
  [...files].forEach(file => {
    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = e => {
        const img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'cin-apercu-img';
        container.appendChild(img);
      };
      reader.readAsDataURL(file);
    } else if (file.type === 'application/pdf') {
      const p = document.createElement('p');
      p.textContent = 'PDF importé : ' + file.name;
      container.appendChild(p);
    }
  });
}

function cinUpdateFileInfo(files) {
  if (!files || files.length === 0) { cinFileInfo.style.display = 'none'; return; }
  cinFileInfo.style.display = 'block';
  cinFileInfo.innerHTML = '<strong>Fichiers sélectionnés :</strong><br>';
  [...files].forEach(file => { cinFileInfo.innerHTML += `• ${file.name} (${(file.size/1024).toFixed(1)} KB)<br>`; });
}

function cinShowError(message) {
  cinErrorMessage.textContent = message;
  cinErrorMessage.classList.add('visible');
  setTimeout(() => cinErrorMessage.classList.remove('visible'), 5000);
}

function cinEnableNextButton() {
  cinUploadButtons.innerHTML = '<button type="button" class="cin-btn-suivant">Suivant</button>';
  cinUploadButtons.querySelector('button').addEventListener('click', () => cinAllerVue('confirmation'));
}
function cinDisableNextButton() { cinUploadButtons.innerHTML = ''; }

if (cinFileInput) {
  cinFileInput.addEventListener('change', () => {
    const files = cinFileInput.files;
    cinPreviewUpload.innerHTML = '';
    cinErrorMessage.classList.remove('visible');

    if (!files.length) { cinDisableNextButton(); cinUpdateFileInfo([]); return; }

    if (files.length === 1 && files[0].type === 'application/pdf') {
      cinSelectedFiles = files;
      cinShowPreview(files, cinPreviewUpload); cinUpdateFileInfo(files); cinEnableNextButton();
      return;
    }
    if (files.length === 2 && [...files].every(f => f.type.startsWith('image/'))) {
      cinSelectedFiles = files;
      cinShowPreview(files, cinPreviewUpload); cinUpdateFileInfo(files); cinEnableNextButton();
      return;
    }
    cinShowError('Veuillez importer soit 1 PDF, soit exactement 2 images (recto+verso).');
    cinFileInput.value = ''; cinSelectedFiles = []; cinUpdateFileInfo([]); cinDisableNextButton();
  });
}

function cinCheckBothPhotos() {
  if (cinRectoFile && cinVersoFile) { cinSelectedFiles = [cinRectoFile, cinVersoFile]; cinEnableNextButton(); }
  else { cinDisableNextButton(); }
}
if (cinCameraRecto) {
  cinCameraRecto.addEventListener('change', () => {
    cinRectoFile = cinCameraRecto.files[0];
    if (cinRectoFile) { cinShowPreview([cinRectoFile], cinPreviewUpload); cinRectoCard.classList.add('faite'); cinUpdateFileInfo([cinRectoFile, cinVersoFile].filter(Boolean)); }
    cinCheckBothPhotos();
  });
}
if (cinCameraVerso) {
  cinCameraVerso.addEventListener('change', () => {
    cinVersoFile = cinCameraVerso.files[0];
    if (cinVersoFile) { cinShowPreview([cinVersoFile], cinPreviewUpload); cinVersoCard.classList.add('faite'); cinUpdateFileInfo([cinRectoFile, cinVersoFile].filter(Boolean)); }
    cinCheckBothPhotos();
  });
}

cinConfirmButtons.innerHTML = '<button type="button" class="cin-btn-precedent">Précédent</button><button type="button" class="cin-btn-suivant">Lancer OCR</button>';
cinConfirmButtons.querySelector('.cin-btn-precedent').addEventListener('click', () => cinAllerVue('import'));
cinConfirmButtons.querySelector('.cin-btn-suivant').addEventListener('click', async () => {
  const bouton = cinConfirmButtons.querySelector('.cin-btn-suivant');
  const texteOriginal = bouton.textContent;
  bouton.textContent = 'Traitement OCR en cours...'; bouton.disabled = true;

  try {
    const formData = new FormData();
    Array.from(cinSelectedFiles).forEach((file, i) => formData.append(`file${i+1}`, file));
    const res = await fetch('ocr.php', { method: 'POST', body: formData });
    const data = await res.json().catch(() => null);
    if (!res.ok) throw new Error((data && data.erreur) ? data.erreur : `Erreur réseau (HTTP ${res.status})`);
    if (data.erreur) throw new Error(data.erreur);

    document.getElementById('cinNom').value = data.nom || '';
    document.getElementById('cinPrenom').value = data.prenom || '';
    document.getElementById('cinDateNaissance').value = data.dateNaissance || '';
    document.getElementById('cinLieuNaissance').value = data.lieuNaissance || '';
    document.getElementById('cinAdresse').value = data.adresse || '';
    document.getElementById('cinErreurVerif').classList.remove('visible');
    cinAllerVue('verification');
  } catch (err) {
    alert("Erreur lors de l'envoi ou du traitement OCR : " + err.message);
  } finally {
    bouton.textContent = texteOriginal; bouton.disabled = false;
  }
});

function cinAllerVue(vue) {
  document.getElementById('cinVueImport').classList.toggle('hidden', vue !== 'import');
  document.getElementById('cinVueConfirmation').classList.toggle('hidden', vue !== 'confirmation');
  document.getElementById('cinVueVerification').classList.toggle('hidden', vue !== 'verification');
  if (vue === 'confirmation') cinShowPreview(cinSelectedFiles, cinPreviewConfirm);

  const etapes = { import: 1, confirmation: 2, verification: 3 };
  const n = etapes[vue];
  ['cinEtape1','cinEtape2','cinEtape3'].forEach((id, i) => {
    const el = document.getElementById(id);
    el.classList.toggle('active', i+1 === n);
    el.classList.toggle('faite', i+1 < n);
  });
  document.getElementById('cinProgression').style.setProperty('--cin-avancement', ((n-1)/2*100) + '%');
}

function ouvrirModaleCin() {
  cinSelectedFiles = []; cinRectoFile = null; cinVersoFile = null;
  cinFileInput.value = ''; cinCameraRecto.value = ''; cinCameraVerso.value = '';
  cinRectoCard.classList.remove('faite'); cinVersoCard.classList.remove('faite');
  cinPreviewUpload.innerHTML = ''; cinFileInfo.style.display = 'none'; cinDisableNextButton();
  cinAllerVue('import');
  document.getElementById('modaleCinFond').classList.add('ouverte');
}
function fermerModaleCin() { document.getElementById('modaleCinFond').classList.remove('ouverte'); }

document.getElementById('btnScanCIN').addEventListener('click', ouvrirModaleCin);
document.getElementById('btnFermerCin').addEventListener('click', fermerModaleCin);
document.getElementById('btnRecommencerCin').addEventListener('click', ouvrirModaleCin);

document.getElementById('btnValiderCin').addEventListener('click', () => {
  const nom = document.getElementById('cinNom').value;
  const prenom = document.getElementById('cinPrenom').value;
  const dateNaissance = document.getElementById('cinDateNaissance').value;
  const ville = document.getElementById('cinLieuNaissance').value;
  const adresse = document.getElementById('cinAdresse').value;

  if (nom) { cv.personnel.nom = nom; document.getElementById('nom').value = nom; }
  if (prenom) { cv.personnel.prenom = prenom; document.getElementById('prenom').value = prenom; }
  if (dateNaissance) { cv.personnel.dateNaissance = dateNaissance; document.getElementById('dateNaissance').value = dateNaissance; }
  if (ville) { cv.personnel.ville = ville; document.getElementById('ville').value = ville; }
  if (adresse) { cv.personnel.adresse = adresse; document.getElementById('adresse').value = adresse; }

  sauvegarderLocalement(); mettreAJourApercu();
  document.getElementById('scanConfirmation').textContent = '✅ Informations de la carte appliquées au formulaire.';
  fermerModaleCin();
});

// ===================== ÉTAPE 2 : EXPÉRIENCES =====================
const caseSansExperience = document.getElementById('sansExperience');
caseSansExperience.checked = cv.sansExperience;
document.getElementById('messageSansExperience').classList.toggle('hidden', !cv.sansExperience);
document.getElementById('listeExperiences').classList.toggle('hidden', cv.sansExperience);
document.getElementById('btnAjouterExperience').classList.toggle('hidden', cv.sansExperience);

caseSansExperience.addEventListener('change', () => {
  cv.sansExperience = caseSansExperience.checked;
  document.getElementById('messageSansExperience').classList.toggle('hidden', !cv.sansExperience);
  document.getElementById('listeExperiences').classList.toggle('hidden', cv.sansExperience);
  document.getElementById('btnAjouterExperience').classList.toggle('hidden', cv.sansExperience);
  sauvegarderLocalement(); mettreAJourApercu();
});

function creerBlocExperience(exp, index) {
  const bloc = document.createElement('div');
  bloc.className = 'bloc-repetable';
  bloc.innerHTML = `
    <div class="bloc-repetable-entete"><strong>Expérience ${index+1}</strong>
      <button type="button" class="btn-supprimer">Supprimer</button></div>
    <label>Poste occupé</label><input type="text" class="c-poste" placeholder="Ex : Vendeur">
    <div class="ligne-2">
      <div><label>Entreprise / employeur</label><input type="text" class="c-employeur"></div>
      <div><label>Ville / lieu</label><input type="text" class="c-lieu"></div>
    </div>
    <div class="ligne-2">
      <div><label>Date de début</label><input type="text" class="c-debut" placeholder="Ex : Mars 2024"></div>
      <div><label>Date de fin</label><input type="text" class="c-fin" placeholder="Ex : Février 2025"></div>
    </div>
    <div class="case-a-cocher" style="margin-top:2mm"><input type="checkbox" class="c-actuel"><label>C'est mon poste actuel</label></div>
    <label>Description des missions <span class="facultatif">(facultatif)</span></label>
    <div class="zona-ia">
      <textarea class="c-description"></textarea>
      <button type="button" class="btn-ia-flotant c-ia" aria-label="Rédiger avec l'IA">✨ Rédiger</button>
    </div>
    <p class="statut-ia c-ia-statut"></p>`;

  const champs = { poste:'.c-poste', employeur:'.c-employeur', lieu:'.c-lieu', debut:'.c-debut', fin:'.c-fin', description:'.c-description' };
  for (const [cle, sel] of Object.entries(champs)) {
    const el = bloc.querySelector(sel);
    el.value = exp[cle] || '';
    el.addEventListener('input', () => { exp[cle] = el.value; sauvegarderLocalement(); mettreAJourApercu(); });
  }
  const caseActuel = bloc.querySelector('.c-actuel');
  caseActuel.checked = !!exp.actuel;
  const champFin = bloc.querySelector('.c-fin');
  champFin.disabled = exp.actuel;
  caseActuel.addEventListener('change', () => { exp.actuel = caseActuel.checked; champFin.disabled = exp.actuel; sauvegarderLocalement(); mettreAJourApercu(); });

  bloc.querySelector('.c-ia').addEventListener('click', (e) => genererTexteIA({
    bouton: e.currentTarget,
    zone: bloc.querySelector('.c-description'),
    statut: bloc.querySelector('.c-ia-statut'),
    type: 'mission',
    contexte: () => ({
      poste: exp.poste, employeur: exp.employeur, lieu: exp.lieu,
      debut: exp.debut, fin: exp.fin, actuel: !!exp.actuel,
    }),
  }));

  bloc.querySelector('.btn-supprimer').addEventListener('click', () => {
    cv.experiences.splice(index, 1); sauvegarderLocalement(); redessinerExperiences(); mettreAJourApercu();
  });
  return bloc;
}

function redessinerExperiences() {
  const conteneur = document.getElementById('listeExperiences');
  conteneur.innerHTML = '';
  cv.experiences.forEach((exp, i) => conteneur.appendChild(creerBlocExperience(exp, i)));
}
document.getElementById('btnAjouterExperience').addEventListener('click', () => {
  cv.experiences.push({ poste:'', employeur:'', lieu:'', debut:'', fin:'', actuel:false, description:'' });
  redessinerExperiences(); sauvegarderLocalement();
});
redessinerExperiences();

// ===================== ÉTAPE 3 : FORMATIONS =====================
const NIVEAUX_DIPLOME = ['Aucun diplôme','CEPE','BEPC','Baccalauréat','CAP / Formation professionnelle','Licence','Master','Autre'];

function creerBlocFormation(form, index) {
  const bloc = document.createElement('div');
  bloc.className = 'bloc-repetable';
  const options = NIVEAUX_DIPLOME.map(n => `<option ${form.diplome===n?'selected':''}>${n}</option>`).join('');
  bloc.innerHTML = `
    <div class="bloc-repetable-entete"><strong>Formation ${index+1}</strong>
      <button type="button" class="btn-supprimer">Supprimer</button></div>
    <label>Diplôme, niveau ou formation suivie</label><select class="f-diplome"><option value="">Choisir...</option>${options}</select>
    <div class="ligne-2">
      <div><label>Établissement</label><input type="text" class="f-etablissement"></div>
      <div><label>Ville</label><input type="text" class="f-ville"></div>
    </div>
    <div class="ligne-2">
      <div><label>Année de début</label><input type="text" class="f-debut" placeholder="Ex : 2019"></div>
      <div><label>Année de fin</label><input type="text" class="f-fin" placeholder="Ex : 2021"></div>
    </div>
    <label>Description <span class="facultatif">(facultatif)</span></label><textarea class="f-description"></textarea>`;

  const champs = { diplome:'.f-diplome', etablissement:'.f-etablissement', ville:'.f-ville', debut:'.f-debut', fin:'.f-fin', description:'.f-description' };
  for (const [cle, sel] of Object.entries(champs)) {
    const el = bloc.querySelector(sel);
    if (el.tagName !== 'SELECT') el.value = form[cle] || '';
    el.addEventListener('input', () => { form[cle] = el.value; sauvegarderLocalement(); mettreAJourApercu(); });
    el.addEventListener('change', () => { form[cle] = el.value; sauvegarderLocalement(); mettreAJourApercu(); });
  }
  bloc.querySelector('.btn-supprimer').addEventListener('click', () => {
    cv.formations.splice(index, 1); sauvegarderLocalement(); redessinerFormations(); mettreAJourApercu();
  });
  return bloc;
}
function redessinerFormations() {
  const conteneur = document.getElementById('listeFormations');
  conteneur.innerHTML = '';
  cv.formations.forEach((f, i) => conteneur.appendChild(creerBlocFormation(f, i)));
}
document.getElementById('btnAjouterFormation').addEventListener('click', () => {
  cv.formations.push({ diplome:'', etablissement:'', ville:'', debut:'', fin:'', description:'' });
  redessinerFormations(); sauvegarderLocalement();
});
redessinerFormations();

// ===================== ÉTAPE 4 : COMPÉTENCES / LANGUES =====================
const SUGGESTIONS_COMPETENCES = ['Vente','Accueil client','Cuisine','Couture','Ménage','Gardiennage','Conduite','Informatique de base','Word / Excel','Réseaux sociaux','Réparation téléphone'];
const NIVEAUX_LANGUE = ['Débutant','Intermédiaire','Avancé','Courant','Langue maternelle'];

function creerZoneTags(idZone, idSaisie, liste) {
  const zone = document.getElementById(idZone);
  const saisie = document.getElementById(idSaisie);
  function redessiner() {
    zone.querySelectorAll('.tag-pilule').forEach(t => t.remove());
    liste.forEach((val, i) => {
      const pilule = document.createElement('span');
      pilule.className = 'tag-pilule';
      pilule.innerHTML = `${val} <button type="button" aria-label="Supprimer">✕</button>`;
      pilule.querySelector('button').addEventListener('click', () => { liste.splice(i,1); sauvegarderLocalement(); redessiner(); mettreAJourApercu(); });
      zone.insertBefore(pilule, saisie);
    });
  }
  saisie.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ',') {
      e.preventDefault();
      const val = saisie.value.trim();
      if (val && !liste.includes(val)) { liste.push(val); sauvegarderLocalement(); redessiner(); mettreAJourApercu(); }
      saisie.value = '';
    }
  });
  redessiner();
  return redessiner;
}
creerZoneTags('zoneCompetences', 'saisieCompetence', cv.competences);
creerZoneTags('zoneQualites', 'saisieQualite', cv.qualites);
creerZoneTags('zoneInterets', 'saisieInteret', cv.interets);

const zoneSuggestions = document.getElementById('suggestionsCompetences');
SUGGESTIONS_COMPETENCES.forEach(s => {
  const btn = document.createElement('button');
  btn.type = 'button'; btn.className = 'suggestion'; btn.textContent = '+ ' + s;
  btn.addEventListener('click', () => {
    if (!cv.competences.includes(s)) { cv.competences.push(s); sauvegarderLocalement(); creerZoneTags('zoneCompetences','saisieCompetence',cv.competences); mettreAJourApercu(); }
  });
  zoneSuggestions.appendChild(btn);
});

function creerBlocLangue(langue, index) {
  const bloc = document.createElement('div');
  bloc.className = 'ligne-2';
  bloc.style.marginTop = '2mm';
  const options = NIVEAUX_LANGUE.map(n => `<option ${langue.niveau===n?'selected':''}>${n}</option>`).join('');
  bloc.innerHTML = `
    <div><input type="text" class="l-nom" placeholder="Ex : Malagasy"></div>
    <div style="display:flex;gap:2mm">
      <select class="l-niveau" style="flex:1"><option value="">Niveau...</option>${options}</select>
      <button type="button" class="btn-supprimer" style="flex-shrink:0">✕</button>
    </div>`;
  const champNom = bloc.querySelector('.l-nom'); champNom.value = langue.nom || '';
  champNom.addEventListener('input', () => { langue.nom = champNom.value; sauvegarderLocalement(); mettreAJourApercu(); });
  const champNiveau = bloc.querySelector('.l-niveau');
  champNiveau.addEventListener('change', () => { langue.niveau = champNiveau.value; sauvegarderLocalement(); mettreAJourApercu(); });
  bloc.querySelector('.btn-supprimer').addEventListener('click', () => { cv.langues.splice(index,1); sauvegarderLocalement(); redessinerLangues(); mettreAJourApercu(); });
  return bloc;
}
function redessinerLangues() {
  const conteneur = document.getElementById('listeLangues');
  conteneur.innerHTML = '';
  cv.langues.forEach((l,i) => conteneur.appendChild(creerBlocLangue(l,i)));
}
document.getElementById('btnAjouterLangue').addEventListener('click', () => { cv.langues.push({nom:'',niveau:''}); redessinerLangues(); sauvegarderLocalement(); });
redessinerLangues();

// ===================== ÉTAPE 5 : COMPLÉMENTAIRE =====================
['disponibilite','mobilite','benevolat','projets','certifications','references'].forEach(cle => lierChamp(cle, cle, cv.complementaire));

// ===================== APERÇU retiré : formulaire seul =====================
// mettreAJourApercu() reste définie (mais ne fait plus rien) pour ne pas avoir
// à retirer ses nombreux appels dans chaque gestionnaire de champ.
function mettreAJourApercu() {}

// ===================== CONSTRUCTION DES DONNÉES (partagée) =====================
function construireDonneesCV() {
  const p = cv.personnel;
  return {
    modele: cv.modele || 'classique',
    personnel: {
      nom: p.nom, prenom: p.prenom, titre_professionnel: p.titrePro, profil_court: p.profilCourt,
      telephone: p.telephone, email: p.email, ville: p.ville,
      adresse: p.adresse, date_naissance: p.dateNaissance,
      permis_conduire: p.permis === 'oui', photo_url: p.photo,
    },
    experiences: cv.sansExperience ? [] : cv.experiences.map(e => ({
      poste: e.poste, employeur: e.employeur, lieu: e.lieu,
      date_debut: e.debut, date_fin: e.fin, poste_actuel: !!e.actuel, description: e.description,
    })),
    formations: cv.formations.map(f => ({
      diplome: f.diplome, etablissement: f.etablissement, ville: f.ville,
      annee_debut: f.debut, annee_fin: f.fin, description: f.description,
    })),
    competences: [
      ...cv.competences.map(c => ({ categorie:'technique', libelle:c })),
      ...cv.qualites.map(c => ({ categorie:'qualite', libelle:c })),
    ],
    langues: cv.langues.filter(l => l.nom).map(l => ({ libelle: l.nom, niveau: l.niveau })),
    complementaire: [
      cv.complementaire.disponibilite && { type:'disponibilite', libelle: cv.complementaire.disponibilite },
      cv.complementaire.mobilite && { type:'mobilite', libelle: cv.complementaire.mobilite },
      cv.complementaire.benevolat && { type:'benevolat', libelle: cv.complementaire.benevolat },
      cv.complementaire.projets && { type:'projet', libelle: cv.complementaire.projets },
      cv.complementaire.certifications && { type:'certification', libelle: cv.complementaire.certifications },
      cv.complementaire.references && { type:'reference', libelle: cv.complementaire.references },
      ...cv.interets.map(i => ({ type:'interet', libelle:i })),
    ].filter(Boolean),
  };
}

// ===================== APERÇU DU CV (fidèle au PDF) =====================
// L'aperçu est produit par apercu-cv.php, qui utilise le MÊME gabarit que le PDF.
// Il est affiché dans un iframe pour que les styles du CV n'entrent pas en
// conflit avec ceux de l'application.
const modaleApercuFond = document.getElementById('modaleApercuFond');
const apercuIframe = document.getElementById('apercuIframe');
const apercuCadre = document.getElementById('apercuCadre');

function ajusterEchelleApercu() {
  const largeurDisponible = apercuCadre.parentElement.clientWidth;
  const echelle = Math.min(1, largeurDisponible / 794);
  apercuIframe.style.transform = `scale(${echelle})`;
  const doc = apercuIframe.contentDocument;
  const hauteurReelle = doc ? Math.max(doc.body.scrollHeight, 1123) : 1123;
  apercuIframe.style.height = hauteurReelle + 'px';
  apercuCadre.style.width = (794 * echelle) + 'px';
  apercuCadre.style.height = (hauteurReelle * echelle) + 'px';
}

document.getElementById('btnVoirApercu').addEventListener('click', async () => {
  if (!validerEtape(1)) { afficherEtape(1); validerEtape(1); return; }

  const btn = document.getElementById('btnVoirApercu');
  const texteOriginal = btn.textContent;
  btn.textContent = 'Préparation...'; btn.disabled = true;

  try {
    const res = await fetch('apercu-cv.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(construireDonneesCV()),
    });
    if (!res.ok) throw new Error('Aperçu indisponible');
    const html = await res.text();

    apercuIframe.srcdoc = html;
    apercuIframe.onload = ajusterEchelleApercu;
    modaleApercuFond.classList.add('ouverte');
  } catch (err) {
    alert("Impossible d'afficher l'aperçu : " + err.message);
  } finally {
    btn.textContent = texteOriginal; btn.disabled = false;
  }
});

function fermerApercu() { modaleApercuFond.classList.remove('ouverte'); }
document.getElementById('btnFermerApercu').addEventListener('click', fermerApercu);
document.getElementById('btnCorriger').addEventListener('click', fermerApercu);
window.addEventListener('resize', () => { if (modaleApercuFond.classList.contains('ouverte')) ajusterEchelleApercu(); });

// Accessibilité des modales : fermeture au clavier (Échap) et au clic sur le fond.
document.addEventListener('keydown', (e) => {
  if (e.key !== 'Escape') return;
  if (modaleApercuFond.classList.contains('ouverte')) fermerApercu();
  const cinFond = document.getElementById('modaleCinFond');
  if (cinFond && cinFond.classList.contains('ouverte')) fermerModaleCin();
});
modaleApercuFond.addEventListener('click', (e) => { if (e.target === modaleApercuFond) fermerApercu(); });
document.getElementById('modaleCinFond').addEventListener('click', (e) => { if (e.target.id === 'modaleCinFond') fermerModaleCin(); });

// ===================== TÉLÉCHARGEMENT FINAL =====================
document.getElementById('btnTelecharger').addEventListener('click', async () => {
  const p = cv.personnel;
  if (!validerEtape(1)) { afficherEtape(1); validerEtape(1); return; }

  const btn = document.getElementById('btnTelecharger');
  const texteOriginal = btn.textContent;
  btn.textContent = 'Génération du PDF...'; btn.disabled = true;

  const donnees = construireDonneesCV();

  try {
    const res = await fetch('generer-pdf.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(donnees) });
    if (!res.ok) { const err = await res.json().catch(()=>null); throw new Error((err && err.erreur) || 'Erreur serveur'); }
    const blob = await res.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = 'CV_' + [p.nom, p.prenom].filter(Boolean).join('_') + '.pdf';
    document.body.appendChild(a); a.click(); a.remove();
    URL.revokeObjectURL(url);
  } catch (err) {
    alert('Impossible de générer le PDF : ' + err.message);
  } finally {
    btn.textContent = texteOriginal; btn.disabled = false;
  }
});

// ===================== ENREGISTREMENT SUR LE COMPTE =====================
const cvIdActuel = <?= $cvIdCharge ? (int)$cvIdCharge : 'null' ?>;
const btnEnregistrerCompte = document.getElementById('btnEnregistrerCompte');
if (btnEnregistrerCompte) {
  btnEnregistrerCompte.addEventListener('click', async () => {
    const p = cv.personnel;
    if (!validerEtape(1)) { afficherEtape(1); validerEtape(1); return; }

    const texteOriginal = btnEnregistrerCompte.textContent;
    btnEnregistrerCompte.textContent = 'Enregistrement...'; btnEnregistrerCompte.disabled = true;

    try {
      const res = await fetch('enregistrer-cv.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cv_id: cvIdActuel, donnees: construireDonneesCV() }),
      });
      const data = await res.json();
      if (!res.ok || data.erreur) throw new Error(data.erreur || 'Erreur serveur');
      localStorage.removeItem(CLE_LOCALE);
      window.location.href = 'mes-cv.php';
    } catch (err) {
      alert("Impossible d'enregistrer : " + err.message);
      btnEnregistrerCompte.textContent = texteOriginal; btnEnregistrerCompte.disabled = false;
    }
  });
}

// ===================== CHARGEMENT D'UN CV EXISTANT =====================
async function chargerCVExistant(id) {
  try {
    const res = await fetch('charger-cv.php?id=' + id);
    const data = await res.json();
    if (!res.ok || data.erreur) throw new Error(data.erreur || 'Erreur');
    const d = data.donnees;

    cv.modele = d.modele || 'classique';
    selectionnerModele(cv.modele);

    Object.assign(cv.personnel, {
      nom: d.personnel.nom, prenom: d.personnel.prenom, titrePro: d.personnel.titre_professionnel,
      profilCourt: d.personnel.profil_court, telephone: d.personnel.telephone, email: d.personnel.email,
      ville: d.personnel.ville, adresse: d.personnel.adresse, dateNaissance: d.personnel.date_naissance,
      permis: d.personnel.permis_conduire ? 'oui' : '', photo: d.personnel.photo_url || '',
    });
    champsPersonnel.forEach(cle => { const el = document.getElementById(cle === 'titrePro' ? 'titrePro' : cle === 'profilCourt' ? 'profilCourt' : cle); if (el) el.value = cv.personnel[cle] || ''; });
    majApercuPhoto();

    cv.experiences = (d.experiences || []).map(e => ({ poste:e.poste, employeur:e.employeur, lieu:e.lieu, debut:e.date_debut, fin:e.date_fin, actuel:!!e.poste_actuel, description:e.description }));
    cv.sansExperience = cv.experiences.length === 0 && (d.experiences || []).length === 0;
    cv.formations = (d.formations || []).map(f => ({ diplome:f.diplome, etablissement:f.etablissement, ville:f.ville, debut:f.annee_debut, fin:f.annee_fin, description:f.description }));
    cv.competences = (d.competences || []).filter(c => c.categorie === 'technique').map(c => c.libelle);
    cv.qualites = (d.competences || []).filter(c => c.categorie === 'qualite').map(c => c.libelle);
    cv.langues = (d.langues || []).map(l => ({ nom:l.libelle, niveau:l.niveau }));
    const comp = {};
    (d.complementaire || []).forEach(c => { if (['disponibilite','mobilite','benevolat'].includes(c.type)) comp[c.type] = c.libelle; if (c.type === 'projet') comp.projets = c.libelle; if (c.type === 'certification') comp.certifications = c.libelle; if (c.type === 'reference') comp.references = c.libelle; });
    Object.assign(cv.complementaire, comp);
    cv.interets = (d.complementaire || []).filter(c => c.type === 'interet').map(c => c.libelle);
    ['disponibilite','mobilite','benevolat','projets','certifications','references'].forEach(cle => { const el = document.getElementById(cle); if (el) el.value = cv.complementaire[cle] || ''; });

    redessinerExperiences(); redessinerFormations(); redessinerLangues();
    creerZoneTags('zoneCompetences','saisieCompetence',cv.competences);
    creerZoneTags('zoneQualites','saisieQualite',cv.qualites);
    creerZoneTags('zoneInterets','saisieInteret',cv.interets);
    document.getElementById('sansExperience').checked = cv.sansExperience;
    sauvegarderLocalement(); mettreAJourApercu();
  } catch (err) {
    alert('Impossible de charger ce CV : ' + err.message);
  }
}

// ===================== RÉINITIALISATION =====================
document.getElementById('btnReinitialiser').addEventListener('click', () => {
  if (!confirm('Effacer toutes les informations saisies et repartir de zéro ?')) return;
  localStorage.removeItem(CLE_LOCALE);
  window.location.href = 'creer-cv.php';
});

// ===================== DÉMARRAGE =====================
afficherEtape(1);
<?php if ($cvIdCharge): ?>
chargerCVExistant(<?= (int)$cvIdCharge ?>);
<?php endif; ?>
</script>
</body>
</html>
