<?php
require_once __DIR__ . '/session.php';
if (empty($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}
require_once __DIR__ . '/bdd.php';
require_once __DIR__ . '/cv-template.php';

$pdo = bdd();
$stmt = $pdo->prepare('SELECT id, titre, date_maj, donnees_json FROM cv WHERE utilisateur_id = :uid ORDER BY date_maj DESC');
$stmt->execute([':uid' => $_SESSION['utilisateur_id']]);
$mesCV = $stmt->fetchAll();

// Date relative : « il y a 9 minutes » se lit d'un coup d'œil sur une carte,
// contrairement à un horodatage complet. La date exacte reste en infobulle.
function dateRelative(string $dateSql): string {
    $ts = strtotime($dateSql);
    $ecart = time() - $ts;
    if ($ecart < 60)    return "il y a quelques secondes";
    if ($ecart < 3600)  { $n = (int)floor($ecart / 60);    return "il y a $n minute" . ($n > 1 ? 's' : ''); }
    if ($ecart < 86400) { $n = (int)floor($ecart / 3600);  return "il y a $n heure"  . ($n > 1 ? 's' : ''); }
    if ($ecart < 2592000) { $n = (int)floor($ecart / 86400); return "il y a $n jour" . ($n > 1 ? 's' : ''); }
    return 'le ' . date('d/m/Y', $ts);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mes CV — CVMG</title>
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
  body { font-family: 'Inter', Arial, sans-serif; color: var(--texte); }
  a { color: inherit; text-decoration: none; }
  .enveloppe { max-width: 1180px; margin: 0 auto; padding: 0 6vw; }
  h1 { font-family: 'Poppins', sans-serif; color: var(--bleu-marine); font-size: 20pt; }

  nav { display: flex; align-items: center; justify-content: space-between; padding: 5mm 0; }
  .nav-logo { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 15pt; color: var(--bleu-marine); }
  .nav-logo span { color: var(--orange); }
  .nav-droite { display: flex; align-items: center; gap: 4mm; font-size: 9.5pt; }
  .nav-nom { color: var(--gris-texte); }
  .nav-droite a { color: var(--bleu); font-weight: 600; }

  main { padding: 8mm 0 16mm; }
  .entete-page { margin-bottom: 8mm; }

  /* Grille de vignettes : colonnes de largeur fixe (200px), pour que la mise à
     l'échelle de l'aperçu ci-dessous reste exacte. */
  .liste-cv { display: grid; gap: 7mm 5mm; grid-template-columns: repeat(auto-fill, 200px); }

  /* Carte « Créer un nouveau CV », en tête de grille : même gabarit A4 que les
     vignettes pour que la grille reste régulière. */
  .carte-nouveau { display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 2mm; aspect-ratio: 210 / 297; border: 2px dashed #C9D0D8; border-radius: 2mm;
    color: var(--gris-texte); font-weight: 600; font-size: 10pt; text-align: center; padding: 4mm;
    transition: border-color .15s ease, color .15s ease, background .15s ease; }
  .carte-nouveau:hover { border-color: var(--bleu); color: var(--bleu); background: var(--bleu-clair); }
  .carte-nouveau .plus { font-size: 20pt; line-height: 1; font-weight: 400; }

  /* La zone vignette sert de référence de positionnement pour le bouton ⋮ et
     son menu : sans ça, ils se placeraient par rapport à la carte entière
     (légende comprise) et viendraient se poser sur le titre. */
  .vignette-zone { position: relative; }

  /* Aperçu : le CV est rendu à sa taille native (794px = A4 à 96 dpi) par le
     MÊME gabarit que le PDF, puis réduit visuellement. Pas de génération
     d'image côté serveur (GD/Imagick indisponibles), et l'aperçu reste fidèle. */
  .cv-miniature { position: relative; width: 100%; aspect-ratio: 210 / 297; overflow: hidden;
    background: #fff; border: 1px solid #E4E7EB; border-radius: 2mm;
    transition: box-shadow .15s ease, border-color .15s ease; }
  .cv-miniature iframe { position: absolute; top: 0; left: 0; width: 794px; height: 1123px;
    border: 0; transform-origin: top left; transform: scale(0.2519); pointer-events: none; }
  .carte-cv:hover .cv-miniature { box-shadow: 0 4px 14px rgba(11,31,61,.14); border-color: #CFD6DE; }
  .vignette-lien { display: block; }
  .vignette-lien:focus-visible .cv-miniature { outline: 2px solid var(--bleu); outline-offset: 2px; }

  /* Menu ⋮ : les actions secondaires (dont la suppression) sortent de la carte
     pour ne pas être à portée de clic accidentel. */
  .btn-menu { position: absolute; right: 2.5mm; bottom: 2.5mm; width: 9mm; height: 9mm;
    border: 1px solid #E4E7EB; border-radius: 50%; background: #fff; cursor: pointer;
    font-size: 13pt; line-height: 1; color: var(--gris-texte); display: flex;
    align-items: center; justify-content: center; box-shadow: 0 1px 4px rgba(11,31,61,.14); }
  .btn-menu:hover { background: var(--gris-fond); color: var(--texte); }

  .menu { position: absolute; right: 2.5mm; bottom: 12mm; z-index: 10; min-width: 42mm;
    background: #fff; border: 1px solid #E4E7EB; border-radius: 2mm; padding: 1.5mm 0;
    box-shadow: 0 6px 18px rgba(11,31,61,.16); }
  .menu[hidden] { display: none; }
  .menu button, .menu a { display: block; width: 100%; text-align: left; background: none;
    border: 0; font-family: inherit; font-size: 9.5pt; color: var(--texte); padding: 2.2mm 4mm;
    cursor: pointer; }
  .menu button:hover, .menu a:hover { background: var(--gris-fond); }
  .menu .separateur { height: 1px; background: #EEF0F3; margin: 1.5mm 0; }
  .menu .danger { color: var(--rouge); }

  .carte-legende { padding: 3mm 1mm 0; }
  .titre-cv { font-family: 'Poppins', sans-serif; font-size: 10.5pt; color: var(--bleu-marine);
    font-weight: 600; line-height: 1.3; margin-bottom: 1mm;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  .champ-titre { width: 100%; font-family: 'Poppins', sans-serif; font-size: 10.5pt;
    color: var(--bleu-marine); font-weight: 600; border: 1.5px solid var(--bleu);
    border-radius: 1.2mm; padding: 1mm 1.5mm; }
  .maj { font-size: 8.5pt; color: var(--gris-texte); }
</style>
</head>
<body>

<div class="enveloppe">
  <nav>
    <a href="accueil.php" class="nav-logo">CV<span>MG</span></a>
    <div class="nav-droite">
      <span class="nav-nom">Bonjour, <?= htmlspecialchars($_SESSION['utilisateur_nom']) ?></span>
      <a href="deconnexion.php">Se déconnecter</a>
    </div>
  </nav>
</div>

<main class="enveloppe">
  <div class="entete-page">
    <h1>Mes CV</h1>
  </div>

  <div class="liste-cv">
    <a href="creer-cv.php" class="carte-nouveau">
      <span class="plus">+</span>
      <span>Créer un nouveau CV</span>
    </a>

    <?php foreach ($mesCV as $cv_item): ?>
      <?php
        $id = (int)$cv_item['id'];
        $donnees = json_decode($cv_item['donnees_json'], true) ?: [];
      ?>
      <div class="carte-cv" data-id="<?= $id ?>">
        <div class="vignette-zone">
          <a href="voir-cv.php?id=<?= $id ?>" class="vignette-lien" aria-label="Voir <?= htmlspecialchars($cv_item['titre']) ?>">
            <div class="cv-miniature">
              <iframe srcdoc="<?= htmlspecialchars(genererCvHtml($donnees), ENT_QUOTES, 'UTF-8') ?>" tabindex="-1" aria-hidden="true"></iframe>
            </div>
          </a>

          <button type="button" class="btn-menu" aria-haspopup="true" aria-expanded="false" aria-label="Actions">⋮</button>
          <div class="menu" hidden>
            <a href="voir-cv.php?id=<?= $id ?>">Voir</a>
            <a href="creer-cv.php?cv_id=<?= $id ?>">Modifier</a>
            <button type="button" class="act-renommer">Renommer</button>
            <button type="button" class="act-dupliquer">Dupliquer</button>
            <button type="button" class="act-pdf">Télécharger le PDF</button>
            <div class="separateur"></div>
            <button type="button" class="act-supprimer danger">Supprimer</button>
          </div>
        </div>

        <div class="carte-legende">
          <h3 class="titre-cv"><?= htmlspecialchars($cv_item['titre']) ?></h3>
          <div class="maj" title="<?= date('d/m/Y à H:i', strtotime($cv_item['date_maj'])) ?>">
            Modifié <?= dateRelative($cv_item['date_maj']) ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<script>
// ===================== MENU ⋮ =====================
function fermerTousLesMenus() {
  document.querySelectorAll('.menu').forEach(m => m.hidden = true);
  document.querySelectorAll('.btn-menu').forEach(b => b.setAttribute('aria-expanded', 'false'));
}

document.querySelectorAll('.btn-menu').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    const menu = btn.parentElement.querySelector('.menu');
    const etaitOuvert = !menu.hidden;
    fermerTousLesMenus();
    if (!etaitOuvert) { menu.hidden = false; btn.setAttribute('aria-expanded', 'true'); }
  });
});

document.addEventListener('click', fermerTousLesMenus);
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') fermerTousLesMenus(); });

// ===================== ACTIONS =====================
function carteDe(el) { return el.closest('.carte-cv'); }

async function appeler(url, corps) {
  const res = await fetch(url, {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(corps),
  });
  const data = await res.json().catch(() => null);
  if (!res.ok || !data || data.erreur) throw new Error((data && data.erreur) || 'Erreur serveur');
  return data;
}

// --- Renommer : édition sur place, plutôt qu'une boîte de dialogue système ---
document.querySelectorAll('.act-renommer').forEach(btn => {
  btn.addEventListener('click', () => {
    const carte = carteDe(btn);
    const titre = carte.querySelector('.titre-cv');
    const ancien = titre.textContent.trim();

    const champ = document.createElement('input');
    champ.type = 'text'; champ.className = 'champ-titre'; champ.value = ancien; champ.maxLength = 150;
    titre.replaceWith(champ);
    champ.focus(); champ.select();

    let termine = false;
    const restaurer = (texte) => {
      if (termine) return;
      termine = true;
      const h3 = document.createElement('h3');
      h3.className = 'titre-cv'; h3.textContent = texte;
      champ.replaceWith(h3);
    };

    const valider = async () => {
      const nouveau = champ.value.trim();
      if (termine) return;
      if (nouveau === '' || nouveau === ancien) { restaurer(ancien); return; }
      try {
        const data = await appeler('renommer-cv.php', { cv_id: carte.dataset.id, titre: nouveau });
        restaurer(data.titre);
      } catch (err) {
        alert('Impossible de renommer : ' + err.message);
        restaurer(ancien);
      }
    };

    champ.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); valider(); }
      if (e.key === 'Escape') { e.preventDefault(); restaurer(ancien); }
    });
    champ.addEventListener('blur', valider);
  });
});

document.querySelectorAll('.act-dupliquer').forEach(btn => {
  btn.addEventListener('click', async () => {
    try {
      await appeler('dupliquer-cv.php', { cv_id: carteDe(btn).dataset.id });
      location.reload();
    } catch (err) { alert('Erreur : ' + err.message); }
  });
});

document.querySelectorAll('.act-supprimer').forEach(btn => {
  btn.addEventListener('click', async () => {
    const carte = carteDe(btn);
    const nom = carte.querySelector('.titre-cv').textContent.trim();
    if (!confirm(`Supprimer définitivement « ${nom} » ?`)) return;
    try {
      await appeler('supprimer-cv.php', { cv_id: carte.dataset.id });
      carte.remove();
    } catch (err) { alert('Erreur : ' + err.message); }
  });
});

// --- PDF : on recharge les données du CV puis on les envoie au générateur,
// les deux points d'accès existants étant réutilisés tels quels. ---
document.querySelectorAll('.act-pdf').forEach(btn => {
  btn.addEventListener('click', async () => {
    const carte = carteDe(btn);
    fermerTousLesMenus();
    try {
      const res = await fetch('charger-cv.php?id=' + carte.dataset.id);
      const data = await res.json();
      if (!res.ok || data.erreur) throw new Error(data.erreur || 'Erreur');

      const resPdf = await fetch('generer-pdf.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data.donnees),
      });
      if (!resPdf.ok) {
        const err = await resPdf.json().catch(() => null);
        throw new Error((err && err.erreur) || 'Erreur serveur');
      }
      const blob = await resPdf.blob();
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      const p = data.donnees.personnel || {};
      a.href = url;
      a.download = 'CV_' + [p.nom, p.prenom].filter(Boolean).join('_') + '.pdf';
      document.body.appendChild(a); a.click(); a.remove();
      URL.revokeObjectURL(url);
    } catch (err) {
      alert('Impossible de générer le PDF : ' + err.message);
    }
  });
});
</script>

</body>
</html>
