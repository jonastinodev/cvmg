- source_spec: none
  summary: Story 2.2 — Suppression de profil sur demande (recherche.php et profil-public.php doivent exclure immédiatement un profil dont la suppression a été demandée, avec horodatage de la demande, sans mécanisme automatisé de délai 48h)
  evidence: L'intent initial de la commande /bmad-build demandait Epic 2 en entier (Story 2.1 + Story 2.2). Scindé au checkpoint multi-objectifs de step-01 : l'utilisateur a choisi de traiter Story 2.1 en premier (elle referme une faille déjà en prod) et de reporter Story 2.2 à une passe séparée.

- source_spec: `_bmad-output/implementation-artifacts/spec-2-2-suppression-profil-sur-demande.md`
  summary: Le texte de consentement lu à l'opérateur dans express-cv.php (Story 2.1) promet à l'employeur qu'il ne verra "jamais votre numéro de téléphone", mais profil-public.php affiche ce numéro en clair (lien tel:) à quiconque possède le lien — la promesse faite au travailleur n'est pas tenue par le code.
  evidence: Trouvé par la revue Blind Hunter en step-04 de la Story 2.2. Le texte a été écrit pendant la Story 2.1 (hors périmètre de la Story 2.2, spec déjà frozen et non modifiable) ; nécessite soit une reformulation du texte, soit une décision produit sur ce que le canal fiche publique doit réellement exposer.

- source_spec: `_bmad-output/implementation-artifacts/spec-2-2-suppression-profil-sur-demande.md`
  summary: Une fois le badge "Suppression demandée le ..." affiché sur tableau-de-bord-operateur.php, l'opérateur n'a aucun moyen de consulter, annuler ou contester la demande depuis l'interface — pas de bouton d'annulation en cas de clic erroné.
  evidence: Trouvé par la revue Blind Hunter en step-04 de la Story 2.2. Non requis par l'AC de la Story 2.2 (epics.md ligne 204-211) mais un vrai manque d'ergonomie à considérer si des erreurs de manipulation sont rapportées pendant le pilote.
