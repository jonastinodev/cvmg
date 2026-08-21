# Addendum — CVMG, côté employeur de la Voie Express

Contenu qui appuie le PRD (`prd.md`) sans y avoir sa place : options rejetées, preuves détaillées, pistes techniques pour l'implémentation. À consulter par le développeur qui construit le pilote, ou par une future itération architecture.

## Cadrages de positionnement rejetés (PRFAQ)

- *« Générateur de CV pour Madagascar »* — rejeté : pas d'avantage face à Canva/Word.
- *« Première plateforme de mise en relation de Madagascar »* — rejeté : invérifiable, et des concurrents existent déjà côté offre (voir ci-dessous).
- *« Trouvez un jardinier près de chez vous »* — rejeté : centre le récit sur l'employeur plutôt que sur le travailleur, qui est le client primaire du produit.
- Scan CIN comme argument de confiance affiché — rejeté : risque de laisser croire à une vérification d'antécédents, alors qu'il s'agit d'une identification, pas d'une enquête de moralité.

## Preuves et paysage concurrentiel (détail PRFAQ)

- Emploi informel : 95,2 % (INSTAT).
- Pénétration internet : ~20,4 % ; couverture mobile : ~92 % ; mobile money : ~60 % des adultes.
- SMIG : 300 000 Ar/mois (depuis le 1/3/2026). Prix travailleur (500-1000 Ar) ≈ 0,2-0,3 % du SMIG mensuel.
- Concurrents identifiés diffusant déjà des offres (sens employeur → candidat, pas l'inverse) : Asako.mg (100 000+ abonnés), Portaljob Madagascar (publie déjà des offres « gardien-jardinier »), Job2mada, offre-emploi-madagascar.com.
- Reformulation défendable de la thèse produit retenue par le PRFAQ après cette recherche : *« aucun canal existant ne fonctionne dans le sens travailleur → employeur, ni n'est utilisable sans accès internet »* — plus étroite que l'affirmation initiale, mais tenable.

## FAQ client — objections non reprises telles quelles dans le PRD

- *« Pourquoi pas Facebook ? »* → un profil persistant, cherchable par métier/quartier, contre un post qui disparaît — mais ne prétend pas remplacer Facebook pour qui l'utilise déjà bien à cet effet.
- *« Pourquoi un employeur me ferait confiance ? »* → non résolu par le produit : identité vérifiée par scan CIN + présence de l'opérateur, mais explicitement pas une enquête de moralité (cf. Non-Goals du PRD).
- *« Deux métiers pour un même travailleur (ex. maçon + jardinier) ? »* → impossible aujourd'hui (un profil = un métier), gap connu, arbitrage non tranché — voir Non-Goals du PRD.

## Pistes techniques pour l'implémentation (non prescriptives)

Ces pistes n'engagent pas le PRD — elles documentent des options envisageables pour le développeur, à valider en architecture.

- **Recherche (FR-1/FR-2/FR-3) :** requête SQL simple sur la table des profils Voie Express, filtrée par métier normalisé (`metiers.json`) et zone (champ ajouté par FR-18 — probablement une réutilisation/renommage du champ `ville` existant, aujourd'hui non rempli), croisée avec le rayon de déplacement déjà déclaré à l'inscription (`rayon_km`, déjà capté). Le PRFAQ qualifie cette requête de triviale — pas de moteur de recherche dédié nécessaire à l'échelle d'un pilote sur un seul quartier.
- **Masquage (FR-2/FR-7) :** `profil-public.php` n'a aujourd'hui aucune logique de masquage (le numéro s'affiche sans condition dès qu'il est renseigné — comportement voulu pour cette page). Le masquage de recherche est donc une logique neuve à écrire sur la page de recherche : ne renvoyer prénom/métier/zone dans la réponse, jamais le téléphone, jusqu'à confirmation d'un déblocage (FR-6).
- **Garde-fou de densité (FR-8/FR-9) :** un compteur de profils actifs par zone (recalculé à l'inscription/suppression d'un profil), comparé à un seuil configurable stocké en base ou en configuration — pas besoin de job asynchrone à l'échelle du pilote, un calcul à la volée suffit.
- **Journalisation (FR-13/FR-14) :** une table d'événements simple (horodatage, type d'événement, zone, métier, statut) suffit pour le pilote ; pas besoin d'outil d'analytics tiers. FR-15 peut être une page admin listant/exportant cette table en CSV.
- **Consentement (FR-10) :** un champ horodaté sur le profil travailleur (`consentement_a` ou équivalent), coché par l'opérateur à l'inscription — pas de flux de re-consentement complexe nécessaire pour un pilote.
- **Déblocage de contact (FR-5/FR-6) :** un statut par couple (recherche employeur, profil travailleur) — `initié` / `bloqué` (FR-8) / `confirmé` — plutôt qu'un système de paiement complet, cohérent avec l'absence d'intégration de paiement en ligne.

## Risques opérationnels non couverts par le logiciel

- Statut juridique du cybercafé encaissant pour le compte de CVMG (intermédiation) — signalé par le PRFAQ comme à vérifier, hors périmètre de ce PRD (question ouverte §11 du PRD).
- Accord de commission opérateur sur les déblocages de contact — nécessaire pour aligner l'incitation (risque n°3 du PRD §6), à négocier hors logiciel.
- Suivi qualitatif post-déblocage (l'employeur a-t-il réellement appelé/recruté ?) — processus humain (appel de suivi, sondage), non spécifié ici.
