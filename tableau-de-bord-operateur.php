<?php
require_once __DIR__ . '/session.php';
if (empty($_SESSION['utilisateur_id']) || empty($_SESSION['est_operateur'])) {
    header('Location: connexion.php');
    exit;
}
require_once __DIR__ . '/bdd.php';

$pdo = bdd();
$nomOperateur = $_SESSION['utilisateur_nom'] ?? '';

// La colonne `type` est ajoutée par la tâche #10 (ajouter_type_cv.sql).
// Avant cette migration, la requête échoue et on affiche une liste vide.
try {
    $stmt = $pdo->prepare(
        'SELECT id, titre, date_creation, donnees_json, suppression_demandee_le
         FROM cv
         WHERE utilisateur_id = :uid AND type = :type
         ORDER BY date_creation DESC'
    );
    $stmt->execute([':uid' => $_SESSION['utilisateur_id'], ':type' => 'express']);
    $cvExpress = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $cvExpress = [];
}

$total = count($cvExpress);

function dateRelativeTdb(string $dateSql): string {
    $ts = strtotime($dateSql);
    $ecart = time() - $ts;
    if ($ecart < 60)       return 'il y a quelques secondes';
    if ($ecart < 3600)     { $n = (int)floor($ecart / 60);    return "il y a $n min"; }
    if ($ecart < 86400)    { $n = (int)floor($ecart / 3600);  return "il y a $n h"; }
    if ($ecart < 2592000)  { $n = (int)floor($ecart / 86400); return "il y a $n j"; }
    return date('d/m/Y', $ts);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tableau de bord — CVMG</title>
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#D97706">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600&display=swap');
  :root {
    --orange: #D97706; --orange-fonce: #B45A08;
    --bleu: #1863F2; --bleu-marine: #0B1F3D;
    --texte: #22262B; --gris: #5B6472; --fond: #F6F7F9;
    --blanc: #fff; --bordure: #E4E7EB;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', Arial, sans-serif; background: var(--fond); color: var(--texte); min-height: 100vh; }
  a { color: inherit; text-decoration: none; }

  /* ── nav ── */
  nav { background: var(--blanc); border-bottom: 1px solid var(--bordure); }
  .nav-int { max-width: 900px; margin: 0 auto; padding: 0 20px;
    display: flex; align-items: center; justify-content: space-between; height: 56px; }
  .logo { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 14pt; color: var(--bleu-marine); }
  .logo span { color: var(--orange); }
  .badge-ops { font-size: 9pt; font-weight: 600; background: var(--orange); color: #fff;
    padding: 1.5mm 3mm; border-radius: 2mm; }
  .nav-droite { display: flex; align-items: center; gap: 12px; font-size: 9.5pt; }
  .nav-droite a { color: var(--gris); }
  .nav-droite a:hover { color: var(--texte); }

  /* ── contenu ── */
  main { max-width: 900px; margin: 0 auto; padding: 28px 20px 60px; }

  .entete-page { display: flex; align-items: baseline; justify-content: space-between;
    flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
  h1 { font-family: 'Poppins', sans-serif; font-size: 17pt; color: var(--bleu-marine); }
  .compteur { font-size: 9.5pt; color: var(--gris); }

  .btn-nouveau { display: inline-flex; align-items: center; gap: 6px;
    background: var(--orange); color: #fff; font-weight: 600; font-size: 10pt;
    padding: 9px 18px; border-radius: 7px; transition: background .15s; }
  .btn-nouveau:hover { background: var(--orange-fonce); }
  .actions-entete { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
  .btn-recherche { display: inline-flex; align-items: center; gap: 6px;
    background: none; border: 1.5px solid var(--bleu); color: var(--bleu);
    font-weight: 600; font-size: 10pt; padding: 8px 16px; border-radius: 7px;
    transition: background .15s; }
  .btn-recherche:hover { background: #EEF3FF; }

  /* ── tableau ── */
  .wrap-table { overflow-x: auto; border-radius: 10px; border: 1px solid var(--bordure);
    background: var(--blanc); box-shadow: 0 1px 3px rgba(0,0,0,.05); }
  table { width: 100%; border-collapse: collapse; min-width: 520px; }
  thead th { padding: 10px 14px; text-align: left; font-size: 10.5px;
    font-weight: 600; letter-spacing: .07em; text-transform: uppercase;
    color: var(--gris); border-bottom: 1px solid var(--bordure); background: var(--blanc); }
  tbody tr { border-top: 1px solid var(--bordure); transition: background .1s; }
  tbody tr:hover { background: #FFF8EC; }
  td { padding: 11px 14px; font-size: 13.5px; vertical-align: middle; }
  td.nom   { font-weight: 500; }
  td.metier { color: var(--gris); }
  td.date  { color: var(--gris); white-space: nowrap; font-size: 12.5px; }
  td.lien  { white-space: nowrap; }
  td.lien a { color: var(--bleu); font-weight: 500; font-size: 12.5px; }
  td.lien a:hover { text-decoration: underline; }
  td.action { white-space: nowrap; }
  .btn-supprimer {
    background: none; border: 1px solid #D64545; color: #D64545;
    font: inherit; font-size: 12px; font-weight: 600;
    padding: 6px 12px; border-radius: 6px; cursor: pointer; transition: background .15s;
  }
  .btn-supprimer:hover { background: #FDEDED; }
  .btn-supprimer:disabled { opacity: .6; cursor: not-allowed; }
  .badge-supprime {
    display: inline-block; font-size: 11.5px; font-weight: 600; color: var(--gris);
    background: var(--fond); border: 1px solid var(--bordure);
    padding: 5px 10px; border-radius: 6px;
  }

  /* ── état vide ── */
  .vide { text-align: center; padding: 60px 20px; color: var(--gris); }
  .vide-icone { font-size: 32pt; margin-bottom: 12px; }
  .vide p { font-size: 11pt; margin-bottom: 20px; }
</style>
</head>
<body>

<nav>
  <div class="nav-int">
    <div style="display:flex;align-items:center;gap:10px;">
      <a href="accueil.php" class="logo">CV<span>MG</span></a>
      <span class="badge-ops">Opérateur</span>
    </div>
    <div class="nav-droite">
      <span><?= htmlspecialchars($nomOperateur) ?></span>
      <a href="deconnexion.php">Se déconnecter</a>
    </div>
  </div>
</nav>

<main>
  <div class="entete-page">
    <div>
      <h1>CV Express créés</h1>
      <p class="compteur"><?= $total ?> profil<?= $total > 1 ? 's' : '' ?> publié<?= $total > 1 ? 's' : '' ?></p>
    </div>
    <div class="actions-entete">
      <a href="recherche.php" class="btn-recherche">🔍 Rechercher pour un employeur</a>
      <a href="express-cv.php" class="btn-nouveau">⚡ Nouveau CV Express</a>
    </div>
  </div>

  <?php if (empty($cvExpress)): ?>
    <div class="vide">
      <div class="vide-icone">📋</div>
      <p>Aucun CV Express créé pour l'instant.<br>Cliquez sur « Nouveau CV Express » pour commencer.</p>
      <a href="express-cv.php" class="btn-nouveau">⚡ Créer le premier CV Express</a>
    </div>
  <?php else: ?>
    <div class="wrap-table">
      <table>
        <thead>
          <tr>
            <th>Nom complet</th>
            <th>Métier</th>
            <th>Créé</th>
            <th>Lien public</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cvExpress as $cv):
            $donnees   = json_decode($cv['donnees_json'], true) ?: [];
            $personnel = $donnees['personnel'] ?? [];
            $nomComplet = trim(($personnel['nom'] ?? '') . ' ' . ($personnel['prenom'] ?? '')) ?: '—';
            $metier     = htmlspecialchars($personnel['titre_professionnel'] ?? '—');
            $dateAff    = dateRelativeTdb($cv['date_creation']);
            $dateTip    = date('d/m/Y H:i', strtotime($cv['date_creation']));
            $suppressionDemandeeLe = $cv['suppression_demandee_le'] ?? null;
          ?>
          <tr data-cv-id="<?= (int)$cv['id'] ?>">
            <td class="nom"><?= htmlspecialchars($nomComplet) ?></td>
            <td class="metier"><?= $metier ?></td>
            <td class="date" title="<?= $dateTip ?>"><?= $dateAff ?></td>
            <td class="lien">
              <a href="profil-public.php?id=<?= (int)$cv['id'] ?>" target="_blank" rel="noopener">
                Voir le profil →
              </a>
            </td>
            <td class="action">
              <?php if ($suppressionDemandeeLe): ?>
                <span class="badge-supprime">Suppression demandée le <?= htmlspecialchars(date('d/m/Y', strtotime($suppressionDemandeeLe))) ?></span>
              <?php else: ?>
                <button type="button" class="btn-supprimer" data-cv-id="<?= (int)$cv['id'] ?>">
                  Demander la suppression
                </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>

<script>
document.querySelectorAll('.btn-supprimer').forEach(function (bouton) {
  bouton.addEventListener('click', function () {
    if (!confirm('Confirmer la demande de suppression de ce profil ? Il disparaîtra immédiatement de la recherche et de la fiche publique.')) {
      return;
    }
    const cvId = bouton.dataset.cvId;
    bouton.disabled = true;
    fetch('demander-suppression-cv.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ cv_id: cvId })
    })
      .then(function (reponse) { return reponse.json().then(function (data) { return { ok: reponse.ok, data: data }; }); })
      .then(function (resultat) {
        if (!resultat.ok) {
          alert(resultat.data.erreur || "Impossible d'enregistrer la demande de suppression.");
          bouton.disabled = false;
          return;
        }
        const cellule = bouton.closest('td');
        const dateAff = resultat.data.date;
        cellule.innerHTML = '<span class="badge-supprime">Suppression demandée le ' + dateAff + '</span>';
      })
      .catch(function () {
        alert("Impossible d'enregistrer la demande de suppression. Vérifiez votre connexion.");
        bouton.disabled = false;
      });
  });
});
</script>

</body>
</html>
