<?php
require_once __DIR__ . '/session.php';
if (empty($_SESSION['utilisateur_id']) || empty($_SESSION['est_operateur'])) {
    header('Location: connexion.php');
    exit;
}
$nomOperateur = $_SESSION['utilisateur_nom'] ?? '';
$metiersJson  = file_get_contents(__DIR__ . '/metiers.json') ?: '[]';
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
  /* Styles minimaux pour que la navigation fonctionne avant la tâche #12 */
  .ecran-cache { display: none; }
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

    <!-- Zone scan CIN — logique injectée à la tâche #13 -->
    <div id="zone-scan-cin"></div>

    <div class="champ-groupe">
      <label for="exp-nom">Nom <span class="requis">*</span></label>
      <input type="text" id="exp-nom" autocomplete="family-name" placeholder="Ex : RAKOTO">
    </div>
    <div class="champ-groupe">
      <label for="exp-prenom">Prénom <span class="requis">*</span></label>
      <input type="text" id="exp-prenom" autocomplete="given-name" placeholder="Ex : Jean">
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

    <div id="grille-metiers">
      <!-- Boutons générés en JS depuis METIERS_CV -->
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
const METIERS_CV   = <?= $metiersJson ?>;
const NB_ECRANS    = 4;
const POURCENTAGES = [25, 50, 75, 100];

// ── ÉTAT ───────────────────────────────────────────────────────
let express = { nom: '', prenom: '', cin: '', metier: '', rayon: null };
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
  document.getElementById('btn-publier').closest ? null : null; // mis à jour en #16

  if (n === NB_ECRANS) remplirResume();

  window.scrollTo({ top: 0, behavior: 'smooth' });
  ecranActuel = n;
}

function validerEcran(n) {
  if (n === 1) {
    express.nom    = document.getElementById('exp-nom').value.trim();
    express.prenom = document.getElementById('exp-prenom').value.trim();
    express.cin    = document.getElementById('exp-cin').value.trim();
    if (!express.nom || !express.prenom) {
      alert('Merci d\'indiquer le nom et le prénom.');
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

// ── ÉCRAN 2 : GRILLE DE MÉTIERS ────────────────────────────────
(function construireGrilleMetiers() {
  const grille = document.getElementById('grille-metiers');
  METIERS_CV.forEach(metier => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-metier';
    btn.textContent = metier;
    btn.addEventListener('click', () => {
      document.querySelectorAll('.btn-metier').forEach(b => b.classList.remove('actif'));
      btn.classList.add('actif');
      express.metier = metier;
      document.getElementById('metier-selectionne-label').textContent = '✓ ' + metier;
    });
    grille.appendChild(btn);
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

// ── ÉCRAN 4 : RÉSUMÉ ───────────────────────────────────────────
function remplirResume() {
  document.getElementById('res-nom').textContent =
    [express.nom, express.prenom].filter(Boolean).join(' ') || '—';
  document.getElementById('res-cin').textContent = express.cin || '—';
  document.getElementById('res-metier').textContent = express.metier || '—';
  document.getElementById('res-rayon').textContent =
    express.rayon === 99 ? 'Plus de 10 km'
    : express.rayon ? express.rayon + ' km'
    : '—';
}

// ── DÉMARRAGE ──────────────────────────────────────────────────
afficherEcran(1);
</script>

</body>
</html>
