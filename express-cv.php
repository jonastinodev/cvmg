<?php
require_once __DIR__ . '/session.php';
if (empty($_SESSION['utilisateur_id']) || empty($_SESSION['est_operateur'])) {
    header('Location: connexion.php');
    exit;
}
$nomOperateur = $_SESSION['utilisateur_nom'] ?? '';
// La liste sert au rendu des <option> ; plus besoin de l'exposer au JS.
$metiers = json_decode(file_get_contents(__DIR__ . '/metiers.json') ?: '[]', true) ?: [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>CV Express — CVMG</title>
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#D97706">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --orange:       #D97706;
    --orange-fonce: #B45A08;
    --orange-clair: #FFF8EC;
    --bleu-marine:  #0B1F3D;
    --texte:        #1A2035;
    --gris:         #5B6472;
    --fond:         #F6F7F9;
    --blanc:        #fff;
    --bordure:      #E4E7EB;
  }

  html, body { height: 100%; }
  body {
    font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
    background: var(--fond);
    color: var(--texte);
    font-size: 16px;
    line-height: 1.5;
  }
  a { color: inherit; text-decoration: none; }
  .ecran-cache { display: none !important; }

  /* ── EN-TÊTE ────────────────────────────────────────── */
  #entete {
    background: var(--blanc);
    border-bottom: 1px solid var(--bordure);
    position: sticky; top: 0; z-index: 10;
  }
  #entete-interieur {
    max-width: 560px; margin: 0 auto; padding: 12px 20px;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
  }
  #entete-logo { display: flex; align-items: center; gap: 10px; }
  #logo {
    font-family: system-ui, sans-serif; font-weight: 800; font-size: 17pt;
    color: var(--bleu-marine); letter-spacing: -0.5px;
  }
  #logo span { color: var(--orange); }
  #badge-ops {
    font-size: 9pt; font-weight: 700; background: var(--orange);
    color: #fff; padding: 3px 9px; border-radius: 4px; letter-spacing: .03em;
  }
  #etape-label {
    font-size: 10pt; font-weight: 600; color: var(--gris);
    white-space: nowrap;
  }
  #barre-progression { height: 4px; background: var(--bordure); }
  #barre-remplissage {
    height: 100%; background: var(--orange);
    transition: width .35s cubic-bezier(.4,0,.2,1);
  }

  /* ── CONTENEUR ──────────────────────────────────────── */
  #conteneur {
    max-width: 560px; margin: 0 auto;
    padding: 28px 20px 100px;
  }

  /* ── ÉCRANS ─────────────────────────────────────────── */
  .titre-ecran {
    font-size: 19pt; font-weight: 700; color: var(--bleu-marine);
    line-height: 1.25; margin-bottom: 6px;
    text-wrap: balance;
  }
  .sous-titre { font-size: 10.5pt; color: var(--gris); margin-bottom: 24px; }

  /* ── CHAMPS ÉCRAN 1 ─────────────────────────────────── */
  .champ-groupe { margin-top: 18px; }
  .champ-groupe label {
    display: block; font-size: 10pt; font-weight: 600;
    color: var(--gris); margin-bottom: 6px; letter-spacing: .03em;
  }
  .requis    { color: var(--orange); }
  .facultatif { color: #9CA3AF; font-weight: 400; }
  .aide-champ { margin-top: 6px; font-size: 9pt; color: var(--gris); }

  .champ-groupe input[type=text],
  .champ-groupe input[type=tel] {
    width: 100%; padding: 14px 16px;
    border: 1.5px solid var(--bordure); border-radius: 10px;
    font-size: 15pt; font-family: inherit; color: var(--texte);
    background: var(--blanc);
    transition: border-color .15s;
  }
  .champ-groupe input[type=text]:focus,
  .champ-groupe input[type=tel]:focus {
    outline: none; border-color: var(--orange);
    box-shadow: 0 0 0 3px rgba(217,119,6,.15);
  }

  /* ── CHOIX DU MÉTIER ────────────────────────────────── */
  .libelle-champ {
    display: block; margin-bottom: 6px;
    font-size: 9.5pt; font-weight: 600; letter-spacing: .05em;
    text-transform: uppercase; color: var(--gris);
  }
  #select-metier, #metier-autre {
    width: 100%; padding: 14px 16px;
    border: 1.5px solid var(--bordure); border-radius: 10px;
    /* 16px minimum : en dessous, iOS zoome à la mise au point du champ. */
    font-size: 16px; font-family: inherit; color: var(--texte);
    background: var(--blanc);
    transition: border-color .15s, box-shadow .15s;
  }
  #select-metier:focus, #metier-autre:focus {
    outline: none; border-color: var(--orange);
    box-shadow: 0 0 0 3px rgba(217,119,6,.15);
  }
  #wrap-metier-autre { margin-top: 14px; }
  #metier-selectionne-label {
    margin-top: 14px;
    font-size: 10.5pt; font-weight: 600; color: var(--orange);
    min-height: 22px;
  }

  /* ── GRILLE RAYON ───────────────────────────────────── */
  #grille-rayon {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 12px; margin-top: 8px;
  }
  .btn-rayon {
    padding: 20px 10px; border: 2px solid var(--bordure);
    border-radius: 12px; background: var(--blanc);
    font-size: 14pt; font-weight: 700; font-family: inherit;
    color: var(--texte); cursor: pointer;
    transition: border-color .12s, background .12s, color .12s;
    text-align: center;
  }
  .btn-rayon:hover { border-color: var(--orange); background: var(--orange-clair); }
  .btn-rayon.actif {
    border-color: var(--orange); background: var(--orange);
    color: #fff;
  }

  /* ── RÉSUMÉ ─────────────────────────────────────────── */
  #resume {
    background: var(--blanc); border: 1px solid var(--bordure);
    border-radius: 12px; padding: 4px 0; margin-bottom: 24px;
  }
  .resume-ligne {
    display: flex; justify-content: space-between; align-items: baseline;
    gap: 12px; padding: 14px 18px;
    border-bottom: 1px solid var(--bordure);
  }
  .resume-ligne:last-child { border-bottom: none; }
  .resume-cle  { font-size: 10pt; color: var(--gris); font-weight: 500; flex-shrink: 0; }
  .resume-val  { font-size: 12pt; font-weight: 600; color: var(--texte); text-align: right; }

  #btn-publier {
    width: 100%; padding: 18px;
    background: var(--orange); color: #fff;
    font-size: 13pt; font-weight: 700; font-family: inherit;
    border: none; border-radius: 12px; cursor: pointer;
    transition: background .15s;
  }
  #btn-publier:hover   { background: var(--orange-fonce); }
  #btn-publier:disabled { opacity: .55; cursor: default; }

  #statut-publication {
    margin-top: 12px; text-align: center;
    font-size: 10.5pt; min-height: 20px;
  }
  #statut-publication.erreur { color: #B00020; font-weight: 500; }

  /* ── CARTE SUCCÈS (après publication) ──────────────── */
  #carte-succes {
    background: #ECFDF5; border: 1.5px solid #059669;
    border-radius: 12px; padding: 24px 20px; text-align: center;
    margin-bottom: 16px;
  }
  #carte-succes .succes-icone { font-size: 28pt; margin-bottom: 8px; }
  #carte-succes h3 { font-size: 14pt; font-weight: 700; color: #065F46; margin-bottom: 10px; }
  #carte-succes .lien-profil {
    display: inline-block; background: #fff; border: 1.5px solid #059669;
    border-radius: 8px; padding: 10px 18px; color: #059669;
    font-weight: 600; font-size: 10.5pt; word-break: break-all; margin-bottom: 16px;
  }
  #carte-succes .lien-profil:hover { background: #D1FAE5; }
  #zone-qrcode { margin: 16px 0; min-height: 40px; }
  .btn-nouveau-express {
    display: inline-block; padding: 13px 24px;
    background: var(--orange); color: #fff;
    font-weight: 700; font-size: 10.5pt; border-radius: 8px;
    border: none; cursor: pointer; font-family: inherit;
  }
  .btn-nouveau-express:hover { background: var(--orange-fonce); }

  /* ── NAVIGATION ─────────────────────────────────────── */
  #navigation {
    position: fixed; bottom: 0; left: 0; right: 0;
    background: var(--blanc); border-top: 1px solid var(--bordure);
    padding: 14px 20px;
    display: flex; justify-content: space-between; gap: 12px;
    max-width: 560px; margin: 0 auto;
    /* centrer la barre fixe sur la même largeur que le conteneur */
    left: 50%; transform: translateX(-50%);
    width: 100%;
  }
  #btn-precedent, #btn-suivant {
    flex: 1; padding: 15px;
    border: 2px solid var(--bordure); border-radius: 10px;
    font-size: 11.5pt; font-weight: 600; font-family: inherit;
    cursor: pointer; background: var(--blanc); color: var(--texte);
    transition: border-color .12s, background .12s;
  }
  #btn-precedent:hover { border-color: var(--gris); }
  #btn-suivant {
    background: var(--orange); border-color: var(--orange); color: #fff;
  }
  #btn-suivant:hover { background: var(--orange-fonce); border-color: var(--orange-fonce); }

  @media (prefers-reduced-motion: no-preference) {
    .ecran { animation: glisser .22s ease; }
    @keyframes glisser { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
  }

  /* ── SCAN CIN ───────────────────────────────────────── */
  #zone-scan-cin { margin-bottom: 20px; }
  .btn-scan {
    width: 100%; padding: 16px; margin-bottom: 10px;
    border: 2px dashed var(--bordure); border-radius: 12px;
    background: var(--blanc); cursor: pointer;
    font-size: 10.5pt; font-family: inherit; color: var(--gris);
    display: flex; align-items: center; justify-content: center; gap: 10px;
    transition: border-color .15s, background .15s;
  }
  .btn-scan:hover { border-color: var(--orange); background: var(--orange-clair); }
  .btn-scan.succes { border-color: #059669; background: #ECFDF5; color: #059669; border-style: solid; }
  .btn-scan.chargement { opacity: .65; cursor: default; }
  #scan-icone { font-size: 18pt; }
  #scan-apercu { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 6px; }
  #scan-apercu img { height: 60px; border-radius: 6px; object-fit: cover; border: 1px solid var(--bordure); }
  #scan-statut { font-size: 10pt; min-height: 18px; }
  #scan-statut.erreur  { color: #B00020; }
  #scan-statut.succes  { color: #059669; font-weight: 600; }
  #scan-statut.attente { color: var(--orange); }
</style>
</head>
<body>

<!-- ══ EN-TÊTE ══════════════════════════════════════════════════ -->
<div id="entete">
  <div id="entete-interieur">
    <div id="entete-logo">
      <a href="tableau-de-bord-operateur.php" id="logo">CV<span>MG</span></a>
      <span id="badge-ops">Opérateur</span>
    </div>
    <div id="etape-label">Étape 1 sur 4</div>
  </div>
  <div id="barre-progression">
    <div id="barre-remplissage" style="width:25%"></div>
  </div>
</div>

<!-- ══ CONTENEUR PRINCIPAL ══════════════════════════════════════ -->
<div id="conteneur">

  <!-- ── ÉCRAN 1 : Scan CIN ────────────────────────────────── -->
  <section class="ecran" data-ecran="1">
    <h2 class="titre-ecran">Scanner la carte d'identité</h2>
    <p class="sous-titre">Scannez la CIN du client, ou saisissez les informations à la main.</p>

    <!-- Zone scan CIN ───────────────────────── -->
    <div id="zone-scan-cin">
      <input type="file" id="exp-cin-fichier" accept="image/*,application/pdf" multiple style="display:none">

      <button type="button" id="btn-scan-cin" class="btn-scan">
        <span id="scan-icone">📷</span>
        <span id="scan-label">Scanner la CIN (1 PDF ou 2 photos recto/verso)</span>
      </button>

      <div id="scan-apercu"></div>
      <p id="scan-statut"></p>
    </div>

    <div class="champ-groupe">
      <label for="exp-nom">Nom <span class="requis">*</span></label>
      <input type="text" id="exp-nom" autocomplete="family-name" placeholder="Ex : RAKOTO">
    </div>
    <div class="champ-groupe">
      <label for="exp-prenom">Prénom <span class="requis">*</span></label>
      <input type="text" id="exp-prenom" autocomplete="given-name" placeholder="Ex : Jean">
    </div>
    <div class="champ-groupe">
      <label for="exp-telephone">Téléphone <span class="requis">*</span></label>
      <input type="tel" id="exp-telephone" autocomplete="tel" inputmode="tel" placeholder="Ex : 034 12 345 67">
      <p class="aide-champ">L'employeur appellera ce numéro — c'est le seul moyen d'être contacté.</p>
    </div>
    <div class="champ-groupe">
      <label for="exp-cin">Numéro CIN <span class="facultatif">(facultatif)</span></label>
      <input type="text" id="exp-cin" inputmode="numeric" placeholder="Ex : 101 234 567 890">
    </div>
  </section>

  <!-- ── ÉCRAN 2 : Métier ───────────────────────────────────── -->
  <section class="ecran ecran-cache" data-ecran="2">
    <h2 class="titre-ecran">Quel est son métier ?</h2>
    <p class="sous-titre">Choisissez dans la liste. Ce métier sera affiché sur le profil.</p>

    <!-- Liste rendue côté serveur : elle s'affiche même si le JavaScript
         échoue, et le sélecteur natif d'Android s'ouvre en plein écran. -->
    <label for="select-metier" class="libelle-champ">Métier</label>
    <select id="select-metier">
      <option value="">— Choisir un métier —</option>
      <?php foreach ($metiers as $m): ?>
        <option value="<?= htmlspecialchars($m) ?>"><?= htmlspecialchars($m) ?></option>
      <?php endforeach; ?>
      <option value="__autre">Autre métier (à préciser)</option>
    </select>

    <div id="wrap-metier-autre" class="ecran-cache">
      <label for="metier-autre" class="libelle-champ">Précisez le métier</label>
      <input type="text" id="metier-autre" autocomplete="off"
             placeholder="Ex : Réparateur de panneaux solaires">
    </div>

    <p id="metier-selectionne-label"></p>
  </section>

  <!-- ── ÉCRAN 3 : Rayon ────────────────────────────────────── -->
  <section class="ecran ecran-cache" data-ecran="3">
    <h2 class="titre-ecran">Jusqu'où peut-il se déplacer ?</h2>
    <p class="sous-titre">Zone de recherche d'emploi autour du cybercafé.</p>

    <div id="grille-rayon">
      <button type="button" class="btn-rayon" data-km="1">1 km</button>
      <button type="button" class="btn-rayon" data-km="2">2 km</button>
      <button type="button" class="btn-rayon" data-km="5">5 km</button>
      <button type="button" class="btn-rayon" data-km="10">10 km</button>
      <button type="button" class="btn-rayon" data-km="99">+ loin</button>
    </div>
  </section>

  <!-- ── ÉCRAN 4 : Résumé + confirmation ───────────────────── -->
  <section class="ecran ecran-cache" data-ecran="4">
    <h2 class="titre-ecran">Vérification avant publication</h2>
    <p class="sous-titre">Contrôlez les informations puis publiez le profil.</p>

    <div id="resume">
      <div class="resume-ligne"><span class="resume-cle">Nom complet</span><span id="res-nom" class="resume-val"></span></div>
      <div class="resume-ligne"><span class="resume-cle">Téléphone</span><span id="res-telephone" class="resume-val"></span></div>
      <div class="resume-ligne"><span class="resume-cle">CIN</span><span id="res-cin" class="resume-val"></span></div>
      <div class="resume-ligne"><span class="resume-cle">Métier</span><span id="res-metier" class="resume-val"></span></div>
      <div class="resume-ligne"><span class="resume-cle">Rayon</span><span id="res-rayon" class="resume-val"></span></div>
    </div>

    <!-- Zone résultat après publication — logique injectée tâches #16-19 -->
    <div id="zone-resultat"></div>

    <button type="button" id="btn-publier">⚡ Valider et publier</button>
    <p id="statut-publication"></p>
  </section>

  <!-- ── NAVIGATION ─────────────────────────────────────────── -->
  <div id="navigation">
    <button type="button" id="btn-precedent" class="ecran-cache">← Précédent</button>
    <button type="button" id="btn-suivant">Suivant →</button>
  </div>

</div><!-- /conteneur -->

<script>
const NB_ECRANS    = 4;
const POURCENTAGES = [25, 50, 75, 100];

// ── ÉTAT ───────────────────────────────────────────────────────
let express = { nom: '', prenom: '', telephone: '', cin: '', metier: '', rayon: null };
let ecranActuel = 1;

// ── NAVIGATION ─────────────────────────────────────────────────
function afficherEcran(n) {
  document.querySelectorAll('.ecran').forEach(s => {
    s.classList.toggle('ecran-cache', parseInt(s.dataset.ecran) !== n);
  });

  document.getElementById('etape-label').textContent = `Étape ${n} sur ${NB_ECRANS}`;
  document.getElementById('barre-remplissage').style.width = POURCENTAGES[n - 1] + '%';

  document.getElementById('btn-precedent').classList.toggle('ecran-cache', n === 1);
  document.getElementById('btn-suivant').classList.toggle('ecran-cache', n === NB_ECRANS);
  // Cacher la barre de navigation après la publication réussie
  const dejaPublie = document.getElementById('carte-succes');
  document.getElementById('navigation').classList.toggle('ecran-cache', !!dejaPublie);

  if (n === NB_ECRANS) remplirResume();

  window.scrollTo({ top: 0, behavior: 'smooth' });
  ecranActuel = n;
}

function validerEcran(n) {
  if (n === 1) {
    express.nom       = document.getElementById('exp-nom').value.trim();
    express.prenom    = document.getElementById('exp-prenom').value.trim();
    express.telephone = document.getElementById('exp-telephone').value.trim();
    express.cin       = document.getElementById('exp-cin').value.trim();
    if (!express.nom || !express.prenom) {
      alert('Merci d\'indiquer le nom et le prénom.');
      return false;
    }
    if (!express.telephone) {
      alert('Le numéro de téléphone est obligatoire — c\'est le seul moyen pour un employeur de contacter la personne.');
      return false;
    }
  }
  if (n === 2 && !express.metier) {
    alert('Veuillez sélectionner un métier.');
    return false;
  }
  if (n === 3 && express.rayon === null) {
    alert('Veuillez choisir un rayon.');
    return false;
  }
  return true;
}

document.getElementById('btn-suivant').addEventListener('click', () => {
  if (validerEcran(ecranActuel)) afficherEcran(ecranActuel + 1);
});
document.getElementById('btn-precedent').addEventListener('click', () => {
  if (ecranActuel > 1) afficherEcran(ecranActuel - 1);
});

// ── ÉCRAN 2 : CHOIX DU MÉTIER ─────────────────────────────────
// La liste est rendue par PHP ; ce bloc ne fait que reporter le choix
// dans l'état `express` et ouvrir le champ libre au besoin.
(function initChoixMetier() {
  const select     = document.getElementById('select-metier');
  const wrapAutre  = document.getElementById('wrap-metier-autre');
  const inputAutre = document.getElementById('metier-autre');
  const label      = document.getElementById('metier-selectionne-label');

  function majLabel() {
    label.textContent = express.metier ? '✓ ' + express.metier : '';
  }

  select.addEventListener('change', () => {
    const autre = select.value === '__autre';
    wrapAutre.classList.toggle('ecran-cache', !autre);
    if (autre) {
      express.metier = inputAutre.value.trim();
      inputAutre.focus();
    } else {
      inputAutre.value = '';
      express.metier = select.value;
    }
    majLabel();
  });

  inputAutre.addEventListener('input', () => {
    express.metier = inputAutre.value.trim();
    majLabel();
  });
})();

// ── ÉCRAN 3 : RAYON ────────────────────────────────────────────
document.querySelectorAll('.btn-rayon').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.btn-rayon').forEach(b => b.classList.remove('actif'));
    btn.classList.add('actif');
    express.rayon = parseInt(btn.dataset.km);
  });
});

// ── ÉCRAN 4 : PUBLICATION ──────────────────────────────────────
async function soumettreExpress() {
  const btn    = document.getElementById('btn-publier');
  const statut = document.getElementById('statut-publication');

  btn.disabled = true;
  btn.textContent = '⏳ Publication en cours…';
  statut.textContent = '';
  statut.className = '';

  try {
    const res  = await fetch('enregistrer-express.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        nom:       express.nom,
        prenom:    express.prenom,
        telephone: express.telephone,
        cin:       express.cin,
        metier:    express.metier,
        rayon:     express.rayon,
      }),
    });
    const data = await res.json().catch(() => null);
    if (!res.ok || (data && data.erreur)) throw new Error(data?.erreur || 'Erreur serveur');

    // Succès : afficher la carte résultat, cacher le bouton et la nav
    const urlPublique = data.url || ('profil-public.php?id=' + data.id);
    const urlComplete = window.location.origin + '/ocr-cin/' + urlPublique;

    btn.style.display = 'none';
    document.getElementById('navigation').classList.add('ecran-cache');

    document.getElementById('zone-resultat').innerHTML = `
      <div id="carte-succes">
        <div class="succes-icone">✅</div>
        <h3>Profil publié !</h3>
        <p style="font-size:10pt;color:#065F46;margin-bottom:12px;">
          Partagez ce lien ou le QR code avec le client.
        </p>
        <a href="${urlComplete}" target="_blank" rel="noopener" class="lien-profil">${urlComplete}</a>
        <div id="zone-qrcode"><!-- QR code injecté à la tâche #19 --></div>
        <button type="button" class="btn-nouveau-express"
          onclick="window.location.href='express-cv.php'">
          ⚡ Créer un autre CV Express
        </button>
      </div>`;

    // Déclencher l'affichage du QR code (tâche #19)
    if (typeof afficherQrCode === 'function') afficherQrCode(urlComplete, data.id);

  } catch (err) {
    statut.textContent = 'Échec : ' + err.message;
    statut.className = 'erreur';
    btn.disabled = false;
    btn.textContent = '⚡ Valider et publier';
  }
}

document.getElementById('btn-publier').addEventListener('click', soumettreExpress);

// ── ÉCRAN 4 : RÉSUMÉ ───────────────────────────────────────────
function remplirResume() {
  document.getElementById('res-nom').textContent =
    [express.nom, express.prenom].filter(Boolean).join(' ') || '—';
  document.getElementById('res-telephone').textContent = express.telephone || '—';
  document.getElementById('res-cin').textContent = express.cin || '—';
  document.getElementById('res-metier').textContent = express.metier || '—';
  document.getElementById('res-rayon').textContent =
    express.rayon === 99 ? 'Plus de 10 km'
    : express.rayon ? express.rayon + ' km'
    : '—';
}

// ── SCAN CIN ───────────────────────────────────────────────────
(function initScanCin() {
  const btnScan   = document.getElementById('btn-scan-cin');
  const fileInput = document.getElementById('exp-cin-fichier');
  const apercu    = document.getElementById('scan-apercu');
  const statut    = document.getElementById('scan-statut');
  const scanLabel = document.getElementById('scan-label');
  const scanIcone = document.getElementById('scan-icone');

  function setStatut(msg, classe) {
    statut.textContent = msg;
    statut.className = classe || '';
  }

  function afficherApercu(files) {
    apercu.innerHTML = '';
    [...files].forEach(f => {
      if (!f.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = e => {
        const img = document.createElement('img');
        img.src = e.target.result;
        apercu.appendChild(img);
      };
      reader.readAsDataURL(f);
    });
  }

  async function lancerOCR(files) {
    // Valider : 1 PDF ou 1-2 images
    const estPdf  = files.length === 1 && files[0].type === 'application/pdf';
    const estImgs = files.length >= 1 && files.length <= 2 && [...files].every(f => f.type.startsWith('image/'));
    if (!estPdf && !estImgs) {
      setStatut('Importez 1 PDF ou 1 à 2 photos (recto / verso).', 'erreur');
      return;
    }

    btnScan.classList.add('chargement');
    btnScan.disabled = true;
    setStatut('Analyse de la carte en cours…', 'attente');
    afficherApercu(files);

    try {
      const formData = new FormData();
      [...files].forEach((f, i) => formData.append('file' + (i + 1), f));

      const res  = await fetch('ocr.php', { method: 'POST', body: formData });
      const data = await res.json().catch(() => null);
      if (!res.ok || (data && data.erreur)) throw new Error(data?.erreur || 'Erreur serveur');

      // Pré-remplir l'état et les champs DOM
      if (data.nom)    { express.nom    = data.nom;    document.getElementById('exp-nom').value    = data.nom; }
      if (data.prenom) { express.prenom = data.prenom; document.getElementById('exp-prenom').value = data.prenom; }
      if (data.cin)    { express.cin    = data.cin;    document.getElementById('exp-cin').value    = data.cin; }

      scanIcone.textContent = '✅';
      scanLabel.textContent = 'CIN scannée — modifiez si besoin';
      btnScan.classList.replace('chargement', 'succes');
      setStatut('Informations extraites automatiquement.', 'succes');
    } catch (err) {
      setStatut('Scan échoué : ' + err.message + ' — saisissez manuellement.', 'erreur');
      btnScan.classList.remove('chargement');
    } finally {
      btnScan.disabled = false;
    }
  }

  btnScan.addEventListener('click', () => fileInput.click());
  fileInput.addEventListener('change', () => {
    if (fileInput.files.length) lancerOCR(fileInput.files);
  });
})();

// ── DÉMARRAGE ──────────────────────────────────────────────────
afficherEcran(1);

// ── QR CODE (tâche #19) ────────────────────────────────────────
// Appelée par soumettreExpress() juste après la création du profil.
// Utilise api.qrserver.com (service gratuit, pas de clé requise).
function afficherQrCode(url, id) {
    const zone = document.getElementById('zone-qrcode');
    if (!zone) return;

    const encoded = encodeURIComponent(url);
    const taille  = 180;

    const img = document.createElement('img');
    img.src   = 'https://api.qrserver.com/v1/create-qr-code/?data=' + encoded
              + '&size=' + taille + 'x' + taille + '&ecc=M&margin=6';
    img.alt    = 'QR Code du profil';
    img.width  = taille;
    img.height = taille;
    img.style.cssText = 'display:block;margin:0 auto;border-radius:10px;'
                      + 'border:2px solid #A7F3D0;background:#fff;padding:4px;';

    const legende = document.createElement('p');
    legende.style.cssText = 'text-align:center;font-size:9pt;color:#065F46;margin-top:8px;';
    legende.textContent = 'Scannez pour accéder au profil';

    zone.innerHTML = '';
    zone.appendChild(img);
    zone.appendChild(legende);
}
</script>

</body>
</html>
