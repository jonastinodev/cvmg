<?php
require_once __DIR__ . '/session.php';
if (empty($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}
require_once __DIR__ . '/bdd.php';

$pdo = bdd();
$stmt = $pdo->prepare('SELECT id, titre, date_maj FROM cv WHERE utilisateur_id = :uid ORDER BY date_maj DESC');
$stmt->execute([':uid' => $_SESSION['utilisateur_id']]);
$mesCV = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mes CV CVMG</title>
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
  .entete-page { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8mm; flex-wrap: wrap; gap: 4mm; }

  .btn { display: inline-flex; align-items: center; font-family: inherit; font-weight: 700; font-size: 10pt;
    padding: 3mm 6mm; border-radius: 2mm; border: 1.5px solid transparent; cursor: pointer; text-decoration: none;
    transition: background .15s ease, transform .15s ease; }
  .btn:hover { transform: translateY(-1px); }
  .btn-orange { background: var(--orange); border-color: var(--orange); color: #0B1F3D; }
  .btn-orange:hover { background: var(--orange-fonce); }

  .liste-cv { display: grid; gap: 4mm; grid-template-columns: 1fr; }
  @media (min-width: 700px) { .liste-cv { grid-template-columns: 1fr 1fr; } }

  .carte-cv { background: #fff; border: 1px solid #E4E7EB; border-radius: 3mm; padding: 6mm; }
  .carte-cv h3 { font-family: 'Poppins', sans-serif; font-size: 12.5pt; color: var(--bleu-marine); margin-bottom: 1.5mm; }
  .carte-cv .maj { font-size: 9pt; color: var(--gris-texte); margin-bottom: 4mm; }
  .actions-cv { display: flex; gap: 2.5mm; flex-wrap: wrap; }
  .actions-cv button, .actions-cv a { font-size: 9pt; padding: 2mm 3.5mm; border-radius: 1.6mm; border: 1px solid #DCE1E7;
    background: #fff; color: var(--texte); cursor: pointer; font-family: inherit; }
  .actions-cv button:hover, .actions-cv a:hover { background: var(--gris-fond); }
  .actions-cv .supprimer { color: var(--rouge); border-color: #F3D2D6; }

  .etat-vide { text-align: center; padding: 16mm 4mm; color: var(--gris-texte); }
  .etat-vide p { margin-bottom: 6mm; }
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
    <a href="creer-cv.php" class="btn btn-orange">+ Créer un nouveau CV</a>
  </div>

  <?php if (empty($mesCV)): ?>
    <div class="etat-vide">
      <p>Vous n'avez pas encore de CV enregistré.</p>
      <a href="creer-cv.php" class="btn btn-orange">Créer mon premier CV</a>
    </div>
  <?php else: ?>
    <div class="liste-cv">
      <?php foreach ($mesCV as $cv_item): ?>
        <div class="carte-cv" data-id="<?= (int)$cv_item['id'] ?>">
          <h3><?= htmlspecialchars($cv_item['titre']) ?></h3>
          <div class="maj">Modifié le <?= date('d/m/Y à H:i', strtotime($cv_item['date_maj'])) ?></div>
          <div class="actions-cv">
            <a href="creer-cv.php?cv_id=<?= (int)$cv_item['id'] ?>">Modifier</a>
            <button type="button" class="btn-dupliquer">Dupliquer</button>
            <button type="button" class="supprimer">Supprimer</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<script>
document.querySelectorAll('.btn-dupliquer').forEach(btn => {
  btn.addEventListener('click', async () => {
    const carte = btn.closest('.carte-cv');
    const id = carte.dataset.id;
    btn.textContent = '...'; btn.disabled = true;
    try {
      const res = await fetch('dupliquer-cv.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cv_id: id }),
      });
      const data = await res.json();
      if (!res.ok || data.erreur) throw new Error(data.erreur || 'Erreur');
      location.reload();
    } catch (err) {
      alert('Erreur : ' + err.message);
      btn.textContent = 'Dupliquer'; btn.disabled = false;
    }
  });
});

document.querySelectorAll('.supprimer').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm('Supprimer ce CV définitivement ?')) return;
    const carte = btn.closest('.carte-cv');
    const id = carte.dataset.id;
    try {
      const res = await fetch('supprimer-cv.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cv_id: id }),
      });
      const data = await res.json();
      if (!res.ok || data.erreur) throw new Error(data.erreur || 'Erreur');
      carte.remove();
    } catch (err) {
      alert('Erreur : ' + err.message);
    }
  });
});
</script>

</body>
</html>
