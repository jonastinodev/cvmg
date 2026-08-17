<?php
// templates/ocre.php — Gabarit à bandeau doré, photo encadrée, colonne
// latérale bleu clair pour contact/compétences/formation.

function genererCvOcre(array $cv): string {
    $p = $cv['personnel'] ?? [];
    $nom = $p['nom'] ?? '';
    $prenom = $p['prenom'] ?? '';
    $experiences = $cv['experiences'] ?? [];
    $formations = $cv['formations'] ?? [];
    $competences = array_filter($cv['competences'] ?? [], fn($c) => ($c['categorie'] ?? '') !== 'langue');
    $langues = $cv['langues'] ?? [];
    [$interets, $autresComplementaires] = separerComplementaire($cv['complementaire'] ?? []);

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

      .page { width: 210mm; }

      /* ===== Deux colonnes =====
         Un flottant (float) plutôt qu'un <table> : sous dompdf, un tableau à
         deux colonnes qui doit se poursuivre sur une deuxième page laisse des
         pages blanches et reporte tout son contenu plus loin (bug vérifié).
         Un flottant paginate normalement ; seule contrepartie, le texte de la
         colonne principale peut être légèrement resserré tant qu'il est à la
         hauteur de la colonne latérale — un compromis largement préférable à
         perdre du contenu.
         Photo et bandeau intégrés à chaque colonne (plutôt qu'un <header>
         flex en pleine largeur au-dessus) : un flex étiré au-dessus d'un
         flottant provoquait une photo déformée sous dompdf (bug vérifié). */
      .colonne-laterale { float: left; width: 68mm; background-color: #EAF2F8; padding: 9mm 8mm; }
      .colonne-principale { margin-left: 68mm; }

      .photo-boite { width: 40mm; height: 40mm; border-radius: 3mm; overflow: hidden; background: #fff;
        border: 0.5mm solid #D7E3ED; margin: 0 auto 6mm; display: flex; align-items: center;
        justify-content: center; color: #14213D; font-weight: 700; font-size: 16pt; }
      .photo-boite img { width: 100%; height: 100%; object-fit: cover; }

      .bandeau-ocre { background-color: #BFA046; color: #14213D; padding: 10mm 12mm; }
      .bandeau-ocre h1 { font-weight: 700; font-size: 18pt; line-height: 1.2; }
      .bandeau-ocre .titre-pro { font-weight: 600; font-size: 10.5pt; margin-top: 2.5mm;
        text-transform: uppercase; letter-spacing: 0.4pt; }

      .contenu-principal { padding: 9mm 10mm; }

      .cote-bloc { margin-bottom: 7mm; }
      .cote-bloc:last-child { margin-bottom: 0; }
      .cote-titre { font-weight: 700; font-size: 9.5pt; letter-spacing: 0.5pt; text-transform: uppercase;
        color: #14213D; margin-bottom: 3mm; padding-bottom: 1.5mm; border-bottom: 0.5mm solid #BFA046; }

      .coord-ligne { font-size: 9pt; color: #14213D; margin-bottom: 1.8mm; }

      .tags-cote { display: flex; flex-wrap: wrap; gap: 1.8mm; }
      .tag-cote { font-size: 8.5pt; padding: 1.2mm 2.8mm; background: #fff; border: 0.4mm solid #D7E3ED;
        border-radius: 1mm; color: #14213D; }

      .langue-ligne { display: flex; justify-content: space-between; font-size: 9pt; color: #14213D; margin-bottom: 1.6mm; }
      .langue-niveau { color: #C8622C; font-weight: 700; font-size: 8pt; text-transform: uppercase; }

      .form-item { margin-bottom: 3.5mm; font-size: 9pt; }
      .form-item:last-child { margin-bottom: 0; }
      .form-item .item-titre { font-weight: 700; font-size: 9.5pt; color: #14213D; }
      .form-item .item-sous { color: #5B6472; font-style: italic; }
      .form-item .item-dates { color: #C8622C; font-weight: 700; font-size: 8pt; }

      .titre-section { font-weight: 700; font-size: 12pt; color: #14213D; margin-bottom: 5mm;
        padding-bottom: 1.5mm; border-bottom: 0.6mm solid #BFA046; }
      section.bloc { margin-bottom: 7mm; }
      section.bloc:last-child { margin-bottom: 0; }

      .item { margin-bottom: 5mm; }
      .item:last-child { margin-bottom: 0; }
      .item-ligne { display: flex; justify-content: space-between; gap: 3mm; }
      .item-titre { font-weight: 700; font-size: 10.5pt; color: #14213D; }
      .item-sous { font-size: 9.5pt; color: #C8622C; font-weight: 700; margin-top: 0.3mm; }
      .item-dates { font-size: 9pt; color: #5B6472; font-weight: 700; white-space: nowrap; }
      .item-desc { font-size: 9.5pt; margin-top: 1.5mm; line-height: 1.45; color: #22262B; }

      .liste-simple-principale { list-style: none; font-size: 9.5pt; line-height: 1.9; color: #22262B; }
      .liste-simple-principale li::before { content: "• "; color: #C8622C; font-weight: 700; }
    </style>
    </head>
    <body>
    <div class="page">

      <div class="colonne-laterale">
        <div class="photo-boite">
          <?php if (!empty($p['photo_url'])): ?>
            <img src="<?= e($p['photo_url']) ?>" alt="">
          <?php else: ?>
            <?= e(initiales($nom, $prenom)) ?>
          <?php endif; ?>
        </div>

        <?php if (!empty($p['telephone']) || !empty($p['email']) || !empty($p['ville']) || !empty($p['adresse'])): ?>
          <div class="cote-bloc">
            <div class="cote-titre">Contact</div>
            <?php if (!empty($p['telephone'])): ?><div class="coord-ligne"><?= e($p['telephone']) ?></div><?php endif; ?>
            <?php if (!empty($p['email'])): ?><div class="coord-ligne"><?= e($p['email']) ?></div><?php endif; ?>
            <?php if (!empty($p['ville'])): ?><div class="coord-ligne"><?= e($p['ville']) ?></div><?php endif; ?>
            <?php if (!empty($p['adresse'])): ?><div class="coord-ligne"><?= e($p['adresse']) ?></div><?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($competences)): ?>
          <div class="cote-bloc">
            <div class="cote-titre">Compétences</div>
            <div class="tags-cote">
              <?php foreach ($competences as $c): ?>
                <span class="tag-cote"><?= e($c['libelle'] ?? '') ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if (!empty($langues)): ?>
          <div class="cote-bloc">
            <div class="cote-titre">Langues</div>
            <?php foreach ($langues as $l): ?>
              <div class="langue-ligne"><span><?= e($l['libelle'] ?? '') ?></span><span class="langue-niveau"><?= e($l['niveau'] ?? '') ?></span></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($formations)): ?>
          <div class="cote-bloc">
            <div class="cote-titre">Formation</div>
            <?php foreach ($formations as $f): ?>
              <div class="form-item">
                <div class="item-titre"><?= e($f['diplome'] ?? '') ?></div>
                <div class="item-sous"><?= e($f['etablissement'] ?? '') ?><?= !empty($f['ville']) ? ' — ' . e($f['ville']) : '' ?></div>
                <div class="item-dates"><?= e($f['annee_debut'] ?? '') ?><?= !empty($f['annee_debut']) && !empty($f['annee_fin']) ? '–' : '' ?><?= e($f['annee_fin'] ?? '') ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($interets)): ?>
          <div class="cote-bloc">
            <div class="cote-titre">Centres d'intérêt</div>
            <?php foreach ($interets as $it): ?>
              <div class="coord-ligne"><?= e($it['libelle'] ?? '') ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>

      <div class="colonne-principale">
        <div class="bandeau-ocre">
          <h1><?= e(mb_strtoupper($nom)) ?> <?= e($prenom) ?></h1>
          <?php if (!empty($p['titre_professionnel'])): ?>
            <div class="titre-pro"><?= e($p['titre_professionnel']) ?></div>
          <?php endif; ?>
        </div>

        <div class="contenu-principal">
          <?php if (!empty($p['profil_court'])): ?>
            <section class="bloc">
              <div class="item-desc"><?= e($p['profil_court']) ?></div>
            </section>
          <?php endif; ?>

          <?php if (!empty($experiences)): ?>
            <section class="bloc">
              <div class="titre-section">Expériences professionnelles</div>
              <?php foreach ($experiences as $exp): ?>
                <div class="item">
                  <div class="item-ligne">
                    <div>
                      <div class="item-titre"><?= e($exp['poste'] ?? '') ?></div>
                      <div class="item-sous"><?= e($exp['employeur'] ?? '') ?><?= !empty($exp['lieu']) ? ', ' . e($exp['lieu']) : '' ?></div>
                    </div>
                    <div class="item-dates"><?= e($exp['date_debut'] ?? '') ?> — <?= !empty($exp['poste_actuel']) ? "Aujourd'hui" : e($exp['date_fin'] ?? '') ?></div>
                  </div>
                  <?php if (!empty($exp['description'])): ?><div class="item-desc"><?= nl2br(e($exp['description'])) ?></div><?php endif; ?>
                </div>
              <?php endforeach; ?>
            </section>
          <?php endif; ?>

          <?php if (!empty($autresComplementaires)): ?>
            <section class="bloc">
              <div class="titre-section">Complémentaire</div>
              <ul class="liste-simple-principale">
                <?php foreach ($autresComplementaires as $ci): ?>
                  <li><b><?= e(libelleTypeComplementaire($ci['type'] ?? '')) ?> :</b> <?= e($ci['libelle'] ?? '') ?></li>
                <?php endforeach; ?>
              </ul>
            </section>
          <?php endif; ?>
        </div>
      </div>

      <div style="clear:both"></div>
    </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
