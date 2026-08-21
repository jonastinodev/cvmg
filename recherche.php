<?php
// recherche.php — Recherche employeur en ligne, libre-service (FR-1/FR-2/FR-3),
// et recherche assistée par l'opérateur (FR-4) : même page, pas de page distincte.
// Un employeur (ou un opérateur en son nom) choisit un métier et une sous-zone
// dans des listes normalisées (jamais de texte libre), et voit les profils
// Express qui correspondent — contact masqué : jamais le numéro complet ni le
// CIN dans cette page (FR-2, FR-12).
//
// Le déblocage de contact (FR-5/FR-6/FR-7, Story 3.2) se fait exclusivement
// depuis l'écran opérateur de cette même page, via debloquer-contact.php —
// jamais de numéro dans le HTML tant que l'encaissement n'est pas confirmé
// (voir la boucle de résultats plus bas).

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/bdd.php';
require_once __DIR__ . '/constantes-express.php';

$labelPrixDeblocage = number_format(PRIX_DEBLOCAGE_AR, 0, ',', ' ') . ' Ar';

// Recherche assistée par l'opérateur (FR-4, Story 3.1) : réutilise cette même
// page — pas de page distincte — pour un opérateur avec une session active.
// Résultats et masquage identiques à la recherche publique, seul l'en-tête
// change pour permettre un retour au tableau de bord.
$estOperateur = !empty($_SESSION['est_operateur']);

$metiers = json_decode(file_get_contents(__DIR__ . '/metiers.json') ?: '[]', true) ?: [];
$zones   = json_decode(file_get_contents(__DIR__ . '/zones.json')   ?: '[]', true) ?: [];

$metierChoisi = trim($_GET['metier'] ?? '');
$zoneChoisie  = trim($_GET['zone']   ?? '');

// Validation contre les listes canoniques (même discipline que l'inscription,
// architecture AD-4) : une valeur hors liste est traitée comme une absence de
// critère plutôt que transmise telle quelle à la requête.
if (!in_array($metierChoisi, $metiers, true)) $metierChoisi = '';
if (!in_array($zoneChoisie, $zones, true))    $zoneChoisie  = '';

$aRecherche = $metierChoisi !== '' && $zoneChoisie !== '';
$resultats  = [];

if ($aRecherche) {
    $stmt = bdd()->prepare(
        'SELECT cv.id, cv.donnees_json, cv.zone, cv.rayon_km, zd.distance_km
         FROM cv
         JOIN zones_distances zd ON zd.zone_arrivee = cv.zone
         WHERE zd.zone_depart = :zoneRecherchee
           AND cv.titre = :metier
           AND cv.type = \'express\' AND cv.est_public = 1
           AND cv.consentement_horodatage IS NOT NULL
           AND cv.suppression_demandee_le IS NULL
           AND zd.distance_km <= cv.rayon_km
         ORDER BY zd.distance_km ASC, cv.date_creation DESC'
    );
    $stmt->execute([
        ':zoneRecherchee' => $zoneChoisie,
        ':metier'         => $metierChoisi,
    ]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
        $pers   = json_decode($ligne['donnees_json'], true)['personnel'] ?? [];
        $rayon  = (int)$ligne['rayon_km'];
        // Résultat volontairement minimal : prénom, métier, zone, rayon.
        // Ne jamais ajouter telephone ni cin_numero ici (FR-2, FR-12).
        $resultats[] = [
            'id'          => (int)$ligne['id'],
            'prenom'      => trim($pers['prenom'] ?? ''),
            'zone'        => $ligne['zone'],
            'rayonLabel'  => $rayon >= 99 ? 'Plus de 10 km' : $rayon . ' km',
            'distanceKm'  => (float)$ligne['distance_km'],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Trouver un travailleur — CVMG</title>
<meta name="theme-color" content="#1655D8">
<meta name="description" content="Trouvez un travailleur disponible près de chez vous à Sotema : gardien, femme de ménage, jardinier, chauffeur et plus.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="assets/cvmg.css">
<style>
  .page { min-height: 100vh; display: flex; flex-direction: column; }
  .contenu { flex: 1; padding-block: var(--e-6) var(--e-8); }

  .entete-recherche { text-align: center; margin-bottom: var(--e-6); }
  .entete-recherche h1 { font-size: var(--t-2xl); margin-bottom: var(--e-2); }
  .entete-recherche p { color: var(--c-encre-2); }

  .form-recherche {
    display: grid; grid-template-columns: 1fr 1fr auto;
    gap: var(--e-3); align-items: end;
    padding: var(--e-4); margin-bottom: var(--e-6);
  }
  .form-recherche label { display: block; margin-bottom: var(--e-1); }
  .form-recherche select {
    width: 100%; font: inherit; font-size: var(--t-m); color: var(--c-encre);
    padding: var(--e-2) var(--e-3); border-radius: var(--r-s);
    border: 1px solid var(--c-ligne-forte); background: var(--c-surface);
  }
  @media (max-width: 620px) {
    .form-recherche { grid-template-columns: 1fr; }
  }

  .liste-resultats { display: flex; flex-direction: column; gap: var(--e-3); }
  .resultat {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: var(--e-3); padding: var(--e-4);
  }
  .resultat__identite { display: flex; align-items: center; gap: var(--e-3); }
  .resultat__avatar {
    width: 44px; height: 44px; border-radius: var(--r-rond); flex-shrink: 0;
    background: var(--c-bleu-pale); color: var(--c-bleu);
    display: grid; place-items: center;
    font-family: var(--police-titre); font-weight: 700; font-size: var(--t-l);
  }
  .resultat__nom { font-weight: 600; }
  .resultat__zone { font-size: var(--t-s); color: var(--c-encre-2); }
  .resultat__distance {
    font-size: var(--t-xs); font-weight: 600; color: var(--c-encre-3);
    white-space: nowrap;
  }

  .etat-vide { text-align: center; padding: var(--e-7) var(--e-4); color: var(--c-encre-2); }
  .etat-vide strong { display: block; color: var(--c-encre); margin-bottom: var(--e-2); font-size: var(--t-l); }

  .note-contact {
    text-align: center; font-size: var(--t-s); color: var(--c-encre-3);
    margin-top: var(--e-5);
  }

  .lien-retour { text-decoration: underline; text-underline-offset: 2px; }
  .lien-retour:hover { color: var(--c-bleu); }

  .resultat__deblocage { flex-shrink: 0; }
  .resultat__telephone {
    font-weight: 700; color: var(--c-bleu); font-size: var(--t-m);
    white-space: nowrap; text-decoration: underline;
  }
</style>
</head>
<body class="page">

<header class="barre">
  <div class="enveloppe enveloppe--etroite barre__int">
    <a href="accueil.php" class="marque">CV<em>MG</em></a>
    <?php if ($estOperateur): ?>
      <a href="tableau-de-bord-operateur.php" class="libelle lien-retour">← Tableau de bord</a>
    <?php else: ?>
      <span class="libelle">Trouver un travailleur</span>
    <?php endif; ?>
  </div>
</header>

<main class="contenu">
  <div class="enveloppe enveloppe--etroite">

    <div class="entete-recherche">
      <h1>Trouver un travailleur près de chez vous</h1>
      <p>Gardien, femme de ménage, jardinier, chauffeur… disponibles à Sotema.</p>
    </div>

    <form method="get" class="carte form-recherche">
      <div>
        <label for="metier">Métier</label>
        <select id="metier" name="metier" required>
          <option value="">— Choisir un métier —</option>
          <?php foreach ($metiers as $m): ?>
            <option value="<?= htmlspecialchars($m) ?>" <?= $m === $metierChoisi ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="zone">Votre sous-zone</label>
        <select id="zone" name="zone" required>
          <option value="">— Choisir une sous-zone —</option>
          <?php foreach ($zones as $z): ?>
            <option value="<?= htmlspecialchars($z) ?>" <?= $z === $zoneChoisie ? 'selected' : '' ?>><?= htmlspecialchars($z) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn--principal">Rechercher</button>
    </form>

    <?php if ($aRecherche && empty($resultats)): ?>

      <div class="carte etat-vide">
        <strong>Personne disponible pour l'instant</strong>
        <p>Aucun profil « <?= htmlspecialchars($metierChoisi) ?> » trouvé près de « <?= htmlspecialchars($zoneChoisie) ?> ». Réessayez plus tard ou avec un autre métier.</p>
      </div>

    <?php elseif ($aRecherche): ?>

      <div class="libelle" style="margin-bottom:var(--e-3)"><?= count($resultats) ?> résultat<?= count($resultats) > 1 ? 's' : '' ?></div>
      <div class="liste-resultats">
        <?php foreach ($resultats as $r): ?>
          <div class="carte resultat">
            <div class="resultat__identite">
              <div class="resultat__avatar" aria-hidden="true"><?= htmlspecialchars(mb_strtoupper(mb_substr($r['prenom'], 0, 1)) ?: '?') ?></div>
              <div>
                <div class="resultat__nom"><?= htmlspecialchars($r['prenom'] ?: 'Profil') ?> — <?= htmlspecialchars($metierChoisi) ?></div>
                <div class="resultat__zone"><?= htmlspecialchars($r['zone']) ?></div>
              </div>
            </div>
            <div class="resultat__distance"><?= $r['distanceKm'] == (int)$r['distanceKm'] ? (int)$r['distanceKm'] : $r['distanceKm'] ?> km</div>
            <?php if ($estOperateur): ?>
              <div class="resultat__deblocage" data-cv-id="<?= $r['id'] ?>">
                <button type="button" class="btn btn--principal btn--compact btn-debloquer">Débloquer — <?= htmlspecialchars($labelPrixDeblocage) ?></button>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (!$estOperateur): ?>
        <p class="note-contact">Pour contacter un profil, rendez-vous dans un cybercafé partenaire — le déblocage de contact en ligne arrive bientôt.</p>
      <?php endif; ?>

    <?php endif; ?>

  </div>
</main>

<script>
const labelPrixDeblocage = <?= json_encode($labelPrixDeblocage, JSON_UNESCAPED_UNICODE) ?>;

document.querySelectorAll('.btn-debloquer').forEach(function (bouton) {
  bouton.addEventListener('click', function () {
    if (!confirm('Confirmer que les ' + labelPrixDeblocage + ' ont été encaissés et révéler le numéro ?')) {
      return;
    }
    const conteneur = bouton.closest('.resultat__deblocage');
    const cvId = conteneur.dataset.cvId;
    bouton.disabled = true;
    bouton.textContent = 'Encaissement…';
    fetch('debloquer-contact.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ cv_id: cvId })
    })
      .then(function (reponse) { return reponse.json().then(function (data) { return { ok: reponse.ok, data: data }; }); })
      .then(function (resultat) {
        if (!resultat.ok) {
          alert(resultat.data.erreur || "Impossible de débloquer ce contact.");
          bouton.disabled = false;
          bouton.textContent = 'Débloquer — ' + labelPrixDeblocage;
          return;
        }
        // Construit via DOM (jamais innerHTML) : le numéro vient d'un champ
        // saisi librement à l'inscription (FR-17, pas de validation de
        // format), donc potentiellement hostile. Le href tel: est réduit
        // aux chiffres et au + (même règle que profil-public.php) car un
        // format libre ("034.../033... ") casserait l'appel en un clic ;
        // le texte affiché, lui, reste tel quel via textContent.
        const lien = document.createElement('a');
        lien.className = 'resultat__telephone';
        lien.href = 'tel:' + resultat.data.telephone.replace(/[^0-9+]/g, '');
        lien.textContent = resultat.data.telephone;
        conteneur.replaceChildren(lien);
      })
      .catch(function () {
        alert("Impossible de débloquer ce contact. Vérifiez votre connexion.");
        bouton.disabled = false;
        bouton.textContent = 'Débloquer — ' + labelPrixDeblocage;
      });
  });
});
</script>

</body>
</html>
