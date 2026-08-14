<?php
// templates/classique.php — Gabarit historique de CVMG (bande rouge, sidebar
// bleu marine). Extrait tel quel de cv-template.php, sans changement visuel :
// c'est la fonction genererCvHtml() d'origine, simplement renommée.

function genererCvClassique(array $cv): string {
    $p = $cv['personnel'] ?? [];
    $nom = $p['nom'] ?? '';
    $prenom = $p['prenom'] ?? '';
    $experiences = $cv['experiences'] ?? [];
    $formations = $cv['formations'] ?? [];
    $competences = array_filter($cv['competences'] ?? [], fn($c) => ($c['categorie'] ?? '') !== 'langue');
    $langues = $cv['langues'] ?? [];
    $complementaire = $cv['complementaire'] ?? [];

    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
    <meta charset="UTF-8">
    <title>CV_<?= e($nom) ?>_<?= e($prenom) ?></title>
    <style>
      * { box-sizing: border-box; margin: 0; padding: 0; }
      @page { margin: 0; }
      body { font-family: 'DejaVu Sans', Arial, sans-serif; color: #22262B; }

      :root {
        --bleu: #1B3A6B; --vert: #2E7D5B; --rouge-mg: #C8313E;
        --gris-fond: #F4F6F8; --gris-texte: #5B6472;
      }

      .page {
        width: 210mm; min-height: 297mm;
        border-left: 5mm solid var(--rouge-mg);
        padding: 14mm 16mm 14mm 14mm;
      }

      header { display: flex; align-items: center; gap: 6mm;
        padding-bottom: 6mm; border-bottom: 0.5mm solid var(--gris-fond); margin-bottom: 7mm; }

      .photo { width: 28mm; height: 28mm; border-radius: 50%; background: var(--gris-fond);
        border: 0.6mm solid var(--bleu); flex-shrink: 0; display: flex; align-items: center;
        justify-content: center; color: var(--bleu); font-weight: 700; font-size: 16pt; overflow: hidden; }
      .photo img { width: 100%; height: 100%; object-fit: cover; }

      .identite h1 { font-weight: 700; font-size: 20pt; color: var(--bleu); line-height: 1.15; }
      .identite .titre-pro { font-weight: 500; font-size: 12pt; color: var(--vert); margin-top: 1mm; }
      .coordonnees { margin-top: 2.5mm; font-size: 9.5pt; color: var(--gris-texte); }
      .coordonnees span { margin-right: 5mm; }
      .coordonnees b { color: #22262B; }

      .profil { font-size: 10.5pt; line-height: 1.5; margin-bottom: 7mm; padding-left: 3mm;
        border-left: 0.6mm solid var(--gris-fond); }

      section.bloc { margin-bottom: 6.5mm; }
      .titre-section { font-weight: 700; font-size: 10.5pt; letter-spacing: 0.8pt; text-transform: uppercase;
        color: var(--bleu); margin-bottom: 3mm; border-bottom: 0.4mm solid var(--gris-fond); padding-bottom: 1.5mm; }

      .item { margin-bottom: 4mm; }
      .item-ligne { display: flex; justify-content: space-between; }
      .item-titre { font-weight: 700; font-size: 10.5pt; }
      .item-sous { font-size: 9.5pt; color: var(--gris-texte); margin-top: 0.3mm; }
      .item-dates { font-size: 9pt; color: var(--gris-texte); white-space: nowrap; }
      .item-desc { font-size: 9.5pt; margin-top: 1.3mm; line-height: 1.45; }

      .tags { display: flex; flex-wrap: wrap; gap: 2mm; }
      .tag { font-size: 9pt; padding: 1.3mm 3.2mm; background: var(--gris-fond); border-radius: 1mm; font-weight: 500; }

      .langue-ligne { display: flex; justify-content: space-between; font-size: 9.5pt; max-width: 70mm; margin-bottom: 1.5mm; }
      .langue-niveau { color: var(--gris-texte); }

      .deux-colonnes { display: flex; gap: 10mm; }
      .deux-colonnes > div { flex: 1; }

      .liste-simple { list-style: none; font-size: 9.5pt; line-height: 1.6; }
      .liste-simple li::before { content: "• "; color: var(--rouge-mg); font-weight: 700; }
    </style>
    </head>
    <body>
    <div class="page">

      <header>
        <div class="photo">
          <?php if (!empty($p['photo_url'])): ?>
            <img src="<?= e($p['photo_url']) ?>" alt="">
          <?php else: ?>
            <?= e(initiales($nom, $prenom)) ?>
          <?php endif; ?>
        </div>
        <div class="identite">
          <h1><?= e(mb_strtoupper($nom)) ?> <?= e($prenom) ?></h1>
          <?php if (!empty($p['titre_professionnel'])): ?>
            <div class="titre-pro"><?= e($p['titre_professionnel']) ?></div>
          <?php endif; ?>
          <div class="coordonnees">
            <?php if (!empty($p['telephone'])): ?><span><b>Tél :</b> <?= e($p['telephone']) ?></span><?php endif; ?>
            <?php if (!empty($p['email'])): ?><span><b>Email :</b> <?= e($p['email']) ?></span><?php endif; ?>
            <?php if (!empty($p['ville'])): ?><span><b>Ville :</b> <?= e($p['ville']) ?></span><?php endif; ?>
            <?php if (!empty($p['adresse'])): ?><span><b>Adresse :</b> <?= e($p['adresse']) ?></span><?php endif; ?>
            <?php if (!empty($p['date_naissance'])): ?><span><b>Né(e) le :</b> <?= e($p['date_naissance']) ?></span><?php endif; ?>
            <?php if (!empty($p['permis_conduire'])): ?><span><b>Permis :</b> Oui</span><?php endif; ?>
          </div>
        </div>
      </header>

      <?php if (!empty($p['profil_court'])): ?>
        <div class="profil"><?= e($p['profil_court']) ?></div>
      <?php endif; ?>

      <?php if (!empty($experiences)): ?>
        <section class="bloc">
          <div class="titre-section">Expériences professionnelles</div>
          <?php foreach ($experiences as $exp): ?>
            <div class="item">
              <div class="item-ligne">
                <div>
                  <div class="item-titre"><?= e($exp['poste'] ?? '') ?></div>
                  <div class="item-sous"><?= e($exp['employeur'] ?? '') ?><?= !empty($exp['lieu']) ? ' — ' . e($exp['lieu']) : '' ?></div>
                </div>
                <div class="item-dates">
                  <?= e($exp['date_debut'] ?? '') ?> — <?= !empty($exp['poste_actuel']) ? 'Aujourd\'hui' : e($exp['date_fin'] ?? '') ?>
                </div>
              </div>
              <?php if (!empty($exp['description'])): ?>
                <div class="item-desc"><?= nl2br(e($exp['description'])) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>

      <?php if (!empty($formations)): ?>
        <section class="bloc">
          <div class="titre-section">Formation</div>
          <?php foreach ($formations as $f): ?>
            <div class="item">
              <div class="item-ligne">
                <div>
                  <div class="item-titre"><?= e($f['diplome'] ?? '') ?></div>
                  <div class="item-sous"><?= e($f['etablissement'] ?? '') ?><?= !empty($f['ville']) ? ' — ' . e($f['ville']) : '' ?></div>
                </div>
                <div class="item-dates"><?= e($f['annee_debut'] ?? '') ?> — <?= e($f['annee_fin'] ?? '') ?></div>
              </div>
              <?php if (!empty($f['description'])): ?>
                <div class="item-desc"><?= nl2br(e($f['description'])) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>

      <?php if (!empty($competences) || !empty($langues) || !empty($complementaire)): ?>
        <section class="bloc deux-colonnes">
          <?php if (!empty($competences) || !empty($langues)): ?>
            <div>
              <?php if (!empty($competences)): ?>
                <div class="titre-section">Compétences</div>
                <div class="tags">
                  <?php foreach ($competences as $c): ?>
                    <span class="tag"><?= e($c['libelle'] ?? '') ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
              <?php if (!empty($langues)): ?>
                <div style="height:4mm"></div>
                <?php foreach ($langues as $l): ?>
                  <div class="langue-ligne"><span><?= e($l['libelle'] ?? '') ?></span><span class="langue-niveau"><?= e($l['niveau'] ?? '') ?></span></div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($complementaire)): ?>
            <div>
              <div class="titre-section">Complémentaire</div>
              <ul class="liste-simple">
                <?php foreach ($complementaire as $ci): ?>
                  <li><?= e($ci['libelle'] ?? '') ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </section>
      <?php endif; ?>

    </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
