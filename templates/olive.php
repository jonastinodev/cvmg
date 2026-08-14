<?php
// templates/olive.php — Gabarit à deux colonnes, en-tête large avec photo,
// barres de compétences/langues, colonne latérale gris-clair.

function genererCvOlive(array $cv): string {
    $p = $cv['personnel'] ?? [];
    $nom = $p['nom'] ?? '';
    $prenom = $p['prenom'] ?? '';
    $experiences = $cv['experiences'] ?? [];
    $formations = $cv['formations'] ?? [];
    $competencesTechniques = array_values(array_filter($cv['competences'] ?? [], fn($c) => ($c['categorie'] ?? '') === 'technique'));
    $qualites = array_values(array_filter($cv['competences'] ?? [], fn($c) => ($c['categorie'] ?? '') === 'qualite'));
    $langues = $cv['langues'] ?? [];
    $complementaire = $cv['complementaire'] ?? [];
    $interets = array_values(array_filter($complementaire, fn($c) => ($c['type'] ?? '') === 'interet'));
    $autresComplementaires = array_values(array_filter($complementaire, fn($c) => ($c['type'] ?? '') !== 'interet'));

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

      .page { width: 210mm; padding: 12mm 14mm; }

      /* ===== En-tête ===== */
      header { display: flex; align-items: flex-start; gap: 8mm; padding-bottom: 6mm;
        border-bottom: 0.5mm solid #E7EBE0; margin-bottom: 6mm; }
      .identite { flex: 1; }
      .identite h1 { font-weight: 700; font-size: 19pt; color: #22262B; line-height: 1.15; }
      .identite .titre-pro { font-weight: 500; font-size: 12pt; color: #5B6472; margin-top: 1.5mm; }

      .photo-tete { width: 30mm; height: 30mm; border-radius: 50%; border: 0.7mm solid #6F8158;
        overflow: hidden; flex-shrink: 0; background: #E7EBE0; display: flex;
        align-items: center; justify-content: center; color: #6F8158; font-weight: 700; font-size: 14pt; }
      .photo-tete img { width: 100%; height: 100%; object-fit: cover; }

      .coord-tete { font-size: 9pt; color: #5B6472; line-height: 1.7; }
      .coord-tete b { color: #22262B; }

      .profil { font-size: 10pt; line-height: 1.55; margin-bottom: 7mm; color: #5B6472; }

      /* ===== Deux colonnes =====
         Un flottant (float) plutôt qu'un <table> : sous dompdf, un tableau à
         deux colonnes qui doit se poursuivre sur une deuxième page laisse des
         pages blanches et reporte tout son contenu plus loin (bug vérifié).
         Un flottant paginate normalement ; seule contrepartie, le texte de la
         colonne principale peut être légèrement resserré tant qu'il est à la
         hauteur de la colonne latérale — un compromis largement préférable à
         perdre du contenu. */
      .colonne-laterale { float: left; width: 62mm; background-color: #FAFBF8; border: 0.4mm solid #E7EBE0; padding: 6mm; }
      .colonne-principale { overflow: hidden; padding-left: 9mm; }

      .cote-bloc { margin-bottom: 7mm; }
      .cote-bloc:last-child { margin-bottom: 0; }
      .cote-titre { font-weight: 700; font-size: 9.5pt; letter-spacing: 0.5pt; text-transform: uppercase;
        color: #6F8158; margin-bottom: 3.5mm; }

      .barre-item { margin-bottom: 3mm; font-size: 9pt; }
      .barre-item:last-child { margin-bottom: 0; }
      .barre-nom { margin-bottom: 1mm; color: #22262B; }
      .barre-fond { background: #E7EBE0; height: 1.6mm; border-radius: 1mm; overflow: hidden; }
      .barre-remplie { background: #6F8158; height: 100%; }

      .liste-cote { list-style: none; font-size: 9pt; line-height: 1.7; color: #22262B; }
      .liste-cote li::before { content: "• "; color: #6F8158; font-weight: 700; }

      .titre-section { font-weight: 700; font-size: 11pt; letter-spacing: 0.4pt; text-transform: uppercase;
        color: #22262B; margin-bottom: 4mm; padding-bottom: 1.5mm; border-bottom: 0.4mm solid #6F8158; }
      section.bloc { margin-bottom: 8mm; }
      section.bloc:last-child { margin-bottom: 0; }

      .rail { border-left: 0.5mm solid #E7EBE0; padding-left: 6mm; margin-left: 1.5mm; }
      .item-rail { position: relative; margin-bottom: 5mm; }
      .item-rail:last-child { margin-bottom: 0; }
      .item-rail::before { content: ""; position: absolute; left: -7.7mm; top: 1mm; width: 2.6mm; height: 2.6mm;
        border-radius: 50%; background: #6F8158; border: 0.5mm solid #fff; box-shadow: 0 0 0 0.3mm #E7EBE0; }

      .item-ligne { display: flex; justify-content: space-between; gap: 3mm; }
      .item-titre { font-weight: 700; font-size: 10.5pt; color: #22262B; }
      .item-sous { font-size: 9.5pt; color: #5B6472; font-style: italic; margin-top: 0.3mm; }
      .item-dates { font-size: 8.5pt; color: #6F8158; font-weight: 700; white-space: nowrap; }
      .item-desc { font-size: 9.5pt; margin-top: 1.5mm; line-height: 1.45; color: #22262B; }
    </style>
    </head>
    <body>
    <div class="page">

      <header>
        <div class="identite">
          <h1><?= e(mb_strtoupper($nom)) ?> <?= e($prenom) ?></h1>
          <?php if (!empty($p['titre_professionnel'])): ?>
            <div class="titre-pro"><?= e(mb_strtoupper($p['titre_professionnel'])) ?></div>
          <?php endif; ?>
        </div>
        <div class="photo-tete">
          <?php if (!empty($p['photo_url'])): ?>
            <img src="<?= e($p['photo_url']) ?>" alt="">
          <?php else: ?>
            <?= e(initiales($nom, $prenom)) ?>
          <?php endif; ?>
        </div>
        <div class="coord-tete">
          <?php if (!empty($p['telephone'])): ?><div><b>Tél :</b> <?= e($p['telephone']) ?></div><?php endif; ?>
          <?php if (!empty($p['email'])): ?><div><b>Email :</b> <?= e($p['email']) ?></div><?php endif; ?>
          <?php if (!empty($p['ville'])): ?><div><b>Ville :</b> <?= e($p['ville']) ?></div><?php endif; ?>
          <?php if (!empty($p['adresse'])): ?><div><b>Adresse :</b> <?= e($p['adresse']) ?></div><?php endif; ?>
        </div>
      </header>

      <?php if (!empty($p['profil_court'])): ?>
        <div class="profil"><?= e($p['profil_court']) ?></div>
      <?php endif; ?>

      <div class="colonne-laterale">
        <?php if (!empty($competencesTechniques)): ?>
          <div class="cote-bloc">
            <div class="cote-titre">Compétences</div>
            <?php foreach ($competencesTechniques as $i => $c): ?>
              <div class="barre-item">
                <div class="barre-nom"><?= e($c['libelle'] ?? '') ?></div>
                <div class="barre-fond"><div class="barre-remplie" style="width:<?= 90 - ($i % 4) * 8 ?>%"></div></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($langues)): ?>
          <div class="cote-bloc">
            <div class="cote-titre">Langues</div>
            <?php foreach ($langues as $l): ?>
              <div class="barre-item">
                <div class="barre-nom"><?= e($l['libelle'] ?? '') ?></div>
                <div class="barre-fond"><div class="barre-remplie" style="width:<?= niveauVersPourcentage($l['niveau'] ?? '') ?>%"></div></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($qualites)): ?>
          <div class="cote-bloc">
            <div class="cote-titre">Qualités</div>
            <ul class="liste-cote">
              <?php foreach ($qualites as $q): ?><li><?= e($q['libelle'] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if (!empty($interets)): ?>
          <div class="cote-bloc">
            <div class="cote-titre">Intérêts</div>
            <ul class="liste-cote">
              <?php foreach ($interets as $it): ?><li><?= e($it['libelle'] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if (!empty($autresComplementaires)): ?>
          <div class="cote-bloc">
            <div class="cote-titre">Complémentaire</div>
            <ul class="liste-cote">
              <?php foreach ($autresComplementaires as $ci): ?><li><?= e($ci['libelle'] ?? '') ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>

      <div class="colonne-principale">
        <?php if (!empty($formations)): ?>
          <section class="bloc">
            <div class="titre-section">Diplômes</div>
            <div class="rail">
              <?php foreach ($formations as $f): ?>
                <div class="item-rail">
                  <div class="item-ligne">
                    <div>
                      <div class="item-titre"><?= e($f['diplome'] ?? '') ?></div>
                      <div class="item-sous"><?= e($f['etablissement'] ?? '') ?><?= !empty($f['ville']) ? ' — ' . e($f['ville']) : '' ?></div>
                    </div>
                    <div class="item-dates"><?= e($f['annee_debut'] ?? '') ?><?= !empty($f['annee_debut']) && !empty($f['annee_fin']) ? '–' : '' ?><?= e($f['annee_fin'] ?? '') ?></div>
                  </div>
                  <?php if (!empty($f['description'])): ?><div class="item-desc"><?= nl2br(e($f['description'])) ?></div><?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

        <?php if (!empty($experiences)): ?>
          <section class="bloc">
            <div class="titre-section">Expériences pratiques et projets</div>
            <div class="rail">
              <?php foreach ($experiences as $exp): ?>
                <div class="item-rail">
                  <div class="item-ligne">
                    <div>
                      <div class="item-titre"><?= e($exp['poste'] ?? '') ?></div>
                      <div class="item-sous"><?= e($exp['employeur'] ?? '') ?><?= !empty($exp['lieu']) ? ' — ' . e($exp['lieu']) : '' ?></div>
                    </div>
                    <div class="item-dates"><?= e($exp['date_debut'] ?? '') ?> — <?= !empty($exp['poste_actuel']) ? "Aujourd'hui" : e($exp['date_fin'] ?? '') ?></div>
                  </div>
                  <?php if (!empty($exp['description'])): ?><div class="item-desc"><?= nl2br(e($exp['description'])) ?></div><?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>
      </div>

      <div style="clear:both"></div>
    </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
