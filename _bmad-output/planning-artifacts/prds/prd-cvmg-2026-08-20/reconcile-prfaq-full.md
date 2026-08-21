# Réconciliation PRFAQ ↔ PRD — CVMG Voie Express (côté employeur)

Source : `prfaq-cvmg.md` (verdict `needs-heat`)
Cible : `prds/prd-cvmg-2026-08-20/prd.md` + `addendum.md`

Rappel du cadrage : le PRD réduit délibérément le périmètre au côté employeur, en pilote sur un seul quartier, et remplace les 20 entretiens employeurs du PRFAQ par le pilote lui-même. Ce rétrécissement est assumé et documenté — il n'est pas listé ci-dessous. Seuls les éléments suivants sont des écarts silencieux ou des inexactitudes factuelles.

---

## Écart 1 — Les 5 entretiens de gérants de cybercafé (next-step #2 du PRFAQ) ont disparu sans trace

**PRFAQ (§"Ce qu'il faudrait pour passer de needs-heat à forged", ligne 221-227) :**
> Dans cet ordre, et pas un autre :
> 1. Vingt entretiens d'employeurs...
> 2. **Cinq entretiens de gérants de cybercafés.** La commission proposée les intéresse-t-elle assez pour qu'ils recrutent activement ?
> 3. Écrire la règle de densité...
> 4. Régler le consentement, la conservation et la suppression...
> 5. Trancher le sort de la Voie Complète...

Le PRD (§0, "Objet du document") explique explicitement qu'il remplace l'étape 1 par le pilote lui-même. Il couvre aussi l'étape 3 (FR-8/FR-9), l'étape 4 (§4.5) et l'étape 5 (Non-Goals, Voie Complète gelée). **L'étape 2 — les 5 entretiens de gérants de cybercafé sur l'attractivité de la commission — n'est mentionnée nulle part** : ni dans le corps du PRD, ni dans les Non-Goals (§8), ni dans les Questions ouvertes (§11), ni dans l'addendum.

C'est d'autant plus notable que le PRFAQ présente cet ordre comme contraignant ("dans cet ordre, et pas un autre"), et que le PRD lui-même construit un mécanisme qui *dépend* de la réponse à cette question : FR-4/FR-6 prêtent à l'opérateur un rôle actif de recherche assistée et d'encaissement, et §6 (risque 3) mentionne "un accord de commission encore à définir" comme condition pour aligner l'incitation opérateur — sans jamais relier ce point à la validation (par entretien) que le PRFAQ jugeait nécessaire *avant* de construire quoi que ce soit côté opérateur.

**Recommandation :** ajouter au minimum une ligne dans les Questions ouvertes (§11) reconnaissant que l'étape 2 du PRFAQ n'a pas été menée, et expliquant pourquoi le pilote peut (ou ne peut pas) s'en passer — plutôt que de laisser croire, par l'exhaustivité apparente du traitement des étapes 1/3/4/5, que rien n'a été oublié.

---

## Écart 2 — FR-8/FR-9 revendiquent l'implémentation du gap PRFAQ #4, mais protègent une transaction différente

**PRFAQ, gap #4 (stage-3 notes, ligne 124) :**
> **Règle « ne pas encaisser dans un quartier vide » inexistante** — c'est aujourd'hui une intention, pas un mécanisme. Sans elle, le produit encaisse pour une promesse qu'il sait ne pas pouvoir tenir. *Arbitrage proposé : bloquant, à imposer par contrat opérateur ET par un garde-fou logiciel (seuil de densité par zone).*

**PRFAQ, FAQ Client Q1 (ligne 87), qui est la source concrète de ce gap :**
> Tant qu'aucun employeur ne consulte la recherche dans votre quartier, votre profil ne sert à rien... Si vous vous inscrivez dans un quartier encore vide, **l'opérateur doit vous le dire avant d'encaisser** [le travailleur].

Le problème identifié par le PRFAQ porte sur **l'encaissement du travailleur** (500-1000 Ar à l'inscription, côté Voie Express existant) dans une zone où aucun employeur ne cherchera jamais. Or le PRD (§4.4, FR-8/FR-9) construit un garde-fou qui bloque une transaction différente : **le déblocage de contact payé par l'employeur**, pas l'inscription payée par le travailleur. Le PRD affirme pourtant explicitement que ce mécanisme "traduit en logiciel la règle « ne pas encaisser dans un quartier vide » que le PRFAQ identifie comme un gap bloquant" (§4.4, description).

Ce n'est pas faux au sens large — les deux mécanismes visent le même risque de fond (encaisser sans valeur livrée) — mais c'est une substitution non signalée : le problème originel (le travailleur paie 500-1000 Ar pour s'inscrire dans un quartier qui ne débouchera jamais sur un appel) reste entièrement non traité par ce PRD, qui se concentre sur le côté employeur (cohérent avec son périmètre), **mais sans le dire**. Le lecteur qui voit "gap #4 : adressé par FR-8/FR-9" en §6 risque de conclure, à tort, que le problème décrit dans la FAQ Client (l'opérateur qui encaisse un travailleur dans une zone vide) est réglé — alors qu'il ne l'est pas, puisque le côté inscription travailleur est explicitement hors périmètre de construction (§2.1).

**Recommandation :** préciser en §4.4 ou en Non-Goals que le garde-fou de densité ne couvre que le déblocage employeur, et que le risque symétrique côté inscription travailleur (déjà signalé par le PRFAQ) reste ouvert / hors périmètre de ce chantier.

---

## Écart 3 — Un concurrent cité dans l'addendum n'existe pas dans le PRFAQ source

**Addendum, §"Preuves et paysage concurrentiel (détail PRFAQ)" (ligne 17) :**
> Concurrents identifiés diffusant déjà des offres... : Asako.mg (100 000+ abonnés), Portaljob Madagascar (publie déjà des offres « gardien-jardinier »), Job2mada, offre-emploi-madagascar.com, **madagascaremploi.com**.

Le PRFAQ (ligne 51 et ligne 214, les deux seuls endroits où la liste de concurrents apparaît) ne mentionne que : Asako.mg, Portaljob Madagascar, Job2mada, offre-emploi-madagascar.com. **"madagascaremploi.com" n'apparaît nulle part dans le PRFAQ.** Recherche confirmée par grep sur le fichier source : zéro occurrence.

Ce n'est pas anodin car la section de l'addendum est explicitement titrée "détail PRFAQ", ce qui affirme implicitement que tout son contenu provient du PRFAQ. Un cinquième concurrent a été ajouté sans source identifiable dans le document que le PRD est censé refléter fidèlement — soit une erreur de transcription, soit une information ajoutée depuis une autre source sans l'indiquer.

**Recommandation :** soit retirer "madagascaremploi.com" de la liste, soit le sourcer explicitement comme une addition externe au PRFAQ (avec sa propre justification), pour ne pas laisser croire que la recherche du PRFAQ l'a identifié.

---

## Écart 4 (mineur) — Taux de change non sourcé présenté comme un détail du PRFAQ

**Addendum (ligne 16) :**
> SMIG : 300 000 Ar/mois... Prix travailleur (500-1000 Ar) ≈ 0,2-0,3 % du SMIG mensuel, ≈ 0,10-0,20 € **(1 EUR ≈ 4876 Ar, mai 2026)**.

Le PRFAQ (ligne 60) donne uniquement "1 000 Ar ≈ 0,20 €" sans jamais préciser de taux de change chiffré ni de date ("mai 2026"). Le taux "4876 Ar" et sa date sont cohérents arithmétiquement avec le ratio du PRFAQ (1000/4876 ≈ 0,205 €) mais ne proviennent pas du texte source, alors que la section est présentée comme un "détail PRFAQ". Impact faible (le chiffre est plausible et non contradictoire), mais même remarque que l'écart 3 : la section prétend restituer le PRFAQ sans y ajouter d'information non sourcée.

**Recommandation :** faible priorité — à corriger seulement si l'addendum doit rester strictement traçable au PRFAQ.

---

## Points vérifiés sans écart

Pour mémoire, les éléments suivants ont été vérifiés et **ne constituent pas des écarts** (correctement repris, ou correctement traités en non-goal/question ouverte) :
- Modèle économique (500-1000 Ar = distribution, pas revenu ; calcul des 10 000 inscriptions/mois) — fidèle au PRFAQ.
- Seuil de densité non chiffré par le PRFAQ — correctement laissé en `[NOTE FOR PM]` / question ouverte.
- Statut juridique du cybercafé (intermédiation) — correctement repris en question ouverte §11.
- Consentement/conservation/suppression — correctement traité comme prérequis bloquant avant mise en ligne (FR-10/11/12), conforme au gap #3 et next-step #4 du PRFAQ.
- Gel de la Voie Complète — reprend fidèlement le next-step #5 et l'option "geler" suggérée par le PRFAQ.
- Multi-métier (un profil = un métier) — correctement listé en Non-Goal, conforme au gap #1 du PRFAQ (non bloquant selon le PRFAQ lui-même).
- Positionnement contre Facebook, contre le bouche-à-oreille — repris fidèlement dans l'addendum.
- Absence d'estimation de calendrier/taille d'équipe — respectée, conforme à la consigne explicite du PRFAQ de ne rien inventer.
- Dépendance Gemini/OCR CIN (risque de faisabilité du PRFAQ) — non reprise dans le PRD, mais raisonnablement hors périmètre puisqu'elle concerne le flux d'inscription travailleur déjà construit et explicitement gelé/hors scope de ce chantier ; à la limite de l'acceptable, non retenue comme écart faute de caractère bloquant pour le côté employeur.
