<?php
// templates/bleu-marine.php — Gabarit à sidebar bleu marine, photo en cercle,
// jalons chronologiques (timeline) pour formation et expériences.

function genererCvBleuMarine(array $cv): string {
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

      /* ===== Colonne latérale =====
         Un flottant (float) plutôt qu'un <table> : sous dompdf, un tableau à
         deux colonnes qui doit se poursuivre sur une deuxième page laisse des
         pages blanches et reporte tout son contenu plus loin (bug vérifié).
         La colonne principale est ELLE AUSSI un flottant (avec une largeur
         explicite), et non un bloc normal à côté du flottant : un bloc normal
         se réadapte à la largeur totale de la page dès qu'il dépasse la
         hauteur du flottant voisin (comportement CSS standard, pas un bug
         dompdf), ce qui décale son texte et fait déborder les bordures. Deux
         flottants côte à côte gardent chacun une largeur fixe sur toute leur
         hauteur, quelle que soit la hauteur de l'autre (vérifié en PDF). */
      /* Le padding est sur .colonne-laterale-int (wrapper interne), jamais
         directement sur le flottant : dompdf n'applique pas correctement
         box-sizing:border-box sur un élément flottant qui a du padding — le
         texte s'enroule à la largeur totale (padding compris) au lieu de la
         largeur de contenu réduite, et déborde ensuite en dehors de la boîte
         (bug vérifié, cause du texte tronqué constaté par l'utilisateur). */
      .colonne-laterale { float: left; width: 68mm; min-height: 260mm; background-color: #17325C; color: #fff; }
      .colonne-laterale-int { padding: 12mm 8mm; }

      .photo-cercle { width: 38mm; height: 38mm; border-radius: 50%; overflow: hidden; margin: 0 auto 9mm;
        background: rgba(255,255,255,.14); border: 0.7mm solid rgba(255,255,255,.35);
        text-align: center; line-height: 38mm; color: #fff; font-weight: 700; font-size: 15pt; }
      .photo-cercle img { width: 100%; height: 100%; object-fit: cover; vertical-align: top; }

      .cote-bloc { margin-bottom: 7mm; }
      .cote-titre { font-weight: 700; font-size: 9.5pt; letter-spacing: 0.6pt; text-transform: uppercase;
        margin-bottom: 3mm; padding-bottom: 1.5mm; border-bottom: 0.4mm solid rgba(255,255,255,.3); }

      .coord-ligne { font-size: 9pt; margin-bottom: 2.2mm; color: rgba(255,255,255,.9); line-height: 1.4; }
      .coord-ligne b { display: block; font-size: 7.5pt; letter-spacing: 0.4pt; text-transform: uppercase;
        color: rgba(255,255,255,.55); margin-bottom: 0.3mm; }

      .langue-item { margin-bottom: 3mm; font-size: 9pt; }
      .langue-nom { margin-bottom: 1mm; }
      .langue-niveau { color: rgba(255,255,255,.6); font-size: 8pt; }
      .barre-fond { background: rgba(255,255,255,.22); height: 1.6mm; border-radius: 1mm; overflow: hidden; }
      .barre-remplie { background: #fff; height: 100%; }

      .tags-cote { display: flex; flex-wrap: wrap; gap: 1.8mm; }
      .tag-cote { font-size: 8.5pt; padding: 1.2mm 2.8mm; background: rgba(255,255,255,.14);
        border-radius: 1mm; }

      .liste-cote { list-style: none; font-size: 9pt; line-height: 1.7; color: rgba(255,255,255,.9); }
      .liste-cote li::before { content: "• "; color: #fff; }

      /* ===== Colonne principale ===== */
      .colonne-principale { float: left; width: 142mm; }
      .colonne-principale-int { padding: 14mm 14mm 14mm 12mm; }

      .identite h1 { font-weight: 700; font-size: 19pt; color: #17325C; line-height: 1.15; }
      .identite .titre-pro { font-weight: 500; font-size: 11.5pt; color: #C8313E; margin-top: 1.5mm; }

      .profil { font-size: 10pt; line-height: 1.5; margin: 6mm 0; color: #5B6472; }

      section.bloc { margin-top: 9mm; }
      .titre-section { font-weight: 700; font-size: 11pt; letter-spacing: 0.5pt; text-transform: uppercase;
        color: #17325C; margin-bottom: 5mm; }

      .rail { border-left: 0.5mm solid #E2E6EC; padding-left: 7mm; margin-left: 1.5mm; }
      .item-rail { position: relative; margin-bottom: 5mm; }
      .item-rail::before { content: ""; position: absolute; left: -8.7mm; top: 1mm; width: 3mm; height: 3mm;
        border-radius: 50%; background: #17325C; border: 0.6mm solid #fff; box-shadow: 0 0 0 0.3mm #E2E6EC; }
      .item-rail:last-child { margin-bottom: 0; }

      .item-titre { font-weight: 700; font-size: 10.5pt; color: #22262B; }
      .item-sous { font-size: 9.5pt; color: #5B6472; margin-top: 0.3mm; }
      .item-dates { font-size: 8.5pt; color: #5B6472; font-style: italic; margin-top: 0.5mm; }
      .item-desc { font-size: 9.5pt; margin-top: 1.5mm; line-height: 1.45; color: #22262B; }

      .liste-simple-principale { list-style: none; font-size: 9.5pt; line-height: 1.9; color: #22262B; }
      .liste-simple-principale li::before { content: "• "; color: #17325C; font-weight: 700; }
    </style>
    </head>
    <body>
    <div class="page">

      <div class="colonne-laterale">
       <div class="colonne-laterale-int">
        <div class="photo-cercle">
          <?php if (!empty($p['photo_url'])): ?>
            <img src="<?= e($p['photo_url']) ?>" alt="">
          <?php else: ?>
            <?= e(initiales($nom, $prenom)) ?>
          <?php endif; ?>
        </div>

        <?php if (!empty($p['telephone']) || !empty($p['email']) || !empty($p['ville']) || !empty($p['adresse'])): ?>
          <div class="cote-bloc">
            <div class="cote-titre">Coordonnées</div>
            <?php if (!empty($p['telephone'])): ?><div class="coord-ligne"><b>Téléphone</b><?= e($p['telephone']) ?></div><?php endif; ?>
            <?php if (!empty($p['email'])): ?><div class="coord-ligne"><b>Email</b><?= e($p['email']) ?></div><?php endif; ?>
            <?php if (!empty($p['ville'])): ?><div class="coord-ligne"><b>Ville</b><?= e($p['ville']) ?></div><?php endif; ?>
            <?php if (!empty($p['adresse'])): ?><div class="coord-ligne"><b>Adresse</b><?= e($p['adresse']) ?></div><?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($langues)): ?>
          <div class="cote-bloc">
            <div class="cote-titre">Langues</div>
            <?php foreach ($langues as $l): ?>
              <div class="langue-item">
                <div class="langue-nom"><?= e($l['libelle'] ?? '') ?><?php if (!empty($l['niveau'])): ?><span class="langue-niveau"> — <?= e($l['niveau']) ?></span><?php endif; ?></div>
                <div class="barre-fond"><div class="barre-remplie" style="width:<?= niveauVersPourcentage($l['niveau'] ?? '') ?>%"></div></div>
              </div>
            <?php endforeach; ?>
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

        <?php if (!empty($interets)): ?>
          <div class="cote-bloc">
            <div class="cote-titre">Centres d'intérêt</div>
            <ul class="liste-cote">
              <?php foreach ($interets as $it): ?>
                <li><?= e($it['libelle'] ?? '') ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
       </div>
      </div>

      <div class="colonne-principale">
       <div class="colonne-principale-int">
        <div class="identite">
          <h1><?= e(mb_strtoupper($nom)) ?> <?= e($prenom) ?></h1>
          <?php if (!empty($p['titre_professionnel'])): ?>
            <div class="titre-pro"><?= e(mb_strtoupper($p['titre_professionnel'])) ?></div>
          <?php endif; ?>
        </div>

        <?php if (!empty($p['profil_court'])): ?>
          <div class="profil"><?= e($p['profil_court']) ?></div>
        <?php endif; ?>

        <?php if (!empty($formations)): ?>
          <section class="bloc">
            <div class="titre-section">Formation</div>
            <div class="rail">
              <?php foreach ($formations as $f): ?>
                <div class="item-rail">
                  <div class="item-titre"><?= e($f['diplome'] ?? '') ?></div>
                  <div class="item-sous"><?= e($f['etablissement'] ?? '') ?><?= !empty($f['ville']) ? ' — ' . e($f['ville']) : '' ?></div>
                  <div class="item-dates"><?= e($f['annee_debut'] ?? '') ?><?= !empty($f['annee_debut']) && !empty($f['annee_fin']) ? '–' : '' ?><?= e($f['annee_fin'] ?? '') ?></div>
                  <?php if (!empty($f['description'])): ?><div class="item-desc"><?= nl2br(e($f['description'])) ?></div><?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

        <?php if (!empty($experiences)): ?>
          <section class="bloc">
            <div class="titre-section">Expérience professionnelle</div>
            <div class="rail">
              <?php foreach ($experiences as $exp): ?>
                <div class="item-rail">
                  <div class="item-titre"><?= e($exp['poste'] ?? '') ?></div>
                  <div class="item-sous"><?= e($exp['employeur'] ?? '') ?><?= !empty($exp['lieu']) ? ' — ' . e($exp['lieu']) : '' ?></div>
                  <div class="item-dates"><?= e($exp['date_debut'] ?? '') ?> — <?= !empty($exp['poste_actuel']) ? "Aujourd'hui" : e($exp['date_fin'] ?? '') ?></div>
                  <?php if (!empty($exp['description'])): ?><div class="item-desc"><?= nl2br(e($exp['description'])) ?></div><?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
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
