- source_spec: none
  summary: Story 2.2 — Suppression de profil sur demande (recherche.php et profil-public.php doivent exclure immédiatement un profil dont la suppression a été demandée, avec horodatage de la demande, sans mécanisme automatisé de délai 48h)
  evidence: L'intent initial de la commande /bmad-build demandait Epic 2 en entier (Story 2.1 + Story 2.2). Scindé au checkpoint multi-objectifs de step-01 : l'utilisateur a choisi de traiter Story 2.1 en premier (elle referme une faille déjà en prod) et de reporter Story 2.2 à une passe séparée.

- source_spec: `_bmad-output/implementation-artifacts/spec-2-2-suppression-profil-sur-demande.md`
  summary: Le texte de consentement lu à l'opérateur dans express-cv.php (Story 2.1) promet à l'employeur qu'il ne verra "jamais votre numéro de téléphone", mais profil-public.php affiche ce numéro en clair (lien tel:) à quiconque possède le lien — la promesse faite au travailleur n'est pas tenue par le code.
  evidence: Trouvé par la revue Blind Hunter en step-04 de la Story 2.2. Le texte a été écrit pendant la Story 2.1 (hors périmètre de la Story 2.2, spec déjà frozen et non modifiable) ; nécessite soit une reformulation du texte, soit une décision produit sur ce que le canal fiche publique doit réellement exposer.

- source_spec: `_bmad-output/implementation-artifacts/spec-2-2-suppression-profil-sur-demande.md`
  summary: Une fois le badge "Suppression demandée le ..." affiché sur tableau-de-bord-operateur.php, l'opérateur n'a aucun moyen de consulter, annuler ou contester la demande depuis l'interface — pas de bouton d'annulation en cas de clic erroné.
  evidence: Trouvé par la revue Blind Hunter en step-04 de la Story 2.2. Non requis par l'AC de la Story 2.2 (epics.md ligne 204-211) mais un vrai manque d'ergonomie à considérer si des erreurs de manipulation sont rapportées pendant le pilote.

- source_spec: none
  summary: Story 3.2 — Encaissement et révélation du numéro (déblocage de contact par l'opérateur : encaissement du prix fixe, révélation du numéro complet uniquement après confirmation, enregistrement du déblocage)
  evidence: L'intent initial de la commande /bmad-build demandait Epic 3 en entier (Stories 3.1 + 3.2 + 3.3). Scindé au checkpoint multi-objectifs de step-01 (aucune réponse reçue à la question posée à l'utilisateur, décision prise par défaut sur l'option recommandée) : traitement séquentiel story par story, en commençant par Story 3.1 qui livre déjà de la valeur seule et n'a aucune dépendance sur 3.2/3.3.

- source_spec: `_bmad-output/implementation-artifacts/spec-3-1-recherche-assistee-par-operateur.md`
  summary: "`sprint-status.yaml` marque encore `2-1-consentement-horodaté...` comme `in-progress` alors que son code manquant a été committé (commit `7e76e0d`) — le statut n'a jamais été corrigé à `review`/`done`."
  evidence: Trouvé par la revue Blind Hunter en step-oneshot de la Story 3.1. Hors périmètre de cette story (ne touche pas Epic 2) ; nécessite de confirmer que le consentement a bien été vérifié manuellement (case à cocher, horodatage en base) avant de faire avancer le statut.

- source_spec: `_bmad-output/implementation-artifacts/spec-3-1-recherche-assistee-par-operateur.md`
  summary: Les émojis utilisés comme préfixes de bouton/lien dans toute l'application (⚡, 📋, 🔍, ←...) ne sont jamais marqués `aria-hidden="true"`, donc un lecteur d'écran annonce le nom brut de l'icône avant le texte visible sur chaque élément interactif.
  evidence: Trouvé par la revue Blind Hunter en step-oneshot de la Story 3.1, sur les deux nouveaux boutons ajoutés (🔍, ←). C'est en réalité une convention déjà présente partout dans le code existant (ex. `tableau-de-bord-operateur.php` avant cette story) — corriger seulement les deux nouveaux boutons serait incohérent ; à traiter comme un passage d'accessibilité transversal, pas une story.

- source_spec: none
  summary: Story 3.3 — Instruction de déblocage sur la recherche en ligne (afficher le prix fixe 10 000 Ar et l'instruction "se rendre au cybercafé" sur chaque résultat de recherche.php pour l'employeur en ligne)
  evidence: Même scission que ci-dessus (checkpoint multi-objectifs de step-01, Epic 3). Story 3.3 est indépendante de 3.1/3.2 côté implémentation (touche uniquement recherche.php côté affichage) mais reportée pour garder un seul cycle plan→implémentation→revue à la fois.
