---
title: "PRFAQ Distillate: cvmg"
type: llm-distillate
source: "prfaq-cvmg.md"
created: "2026-08-20"
purpose: "Token-efficient context for downstream PRD creation"
---

## Positionnement retenu

- **Cadrage central :** inversion du sens de la recherche. Les plateformes malgaches existantes diffusent des *offres* (employeur → candidat) ; CVMG Express rend des *personnes trouvables* (travailleur → employeur). C'est la seule différenciation défendable.
- **Client primaire :** travailleur informel non numérisé (gardien, jardinier, femme de ménage, chauffeur, maçon). Pas de smartphone, pas de compte, parfois non lecteur.
- **Client payant probable :** l'employeur, via déblocage de contact. Non tranché.
- **Actif difficile à répliquer :** le réseau physique de cybercafés partenaires et les relations locales. Pas le code — reproductible en semaines.
- **Règle de discours :** ne jamais argumenter contre Facebook sur les fonctionnalités ; argumenter sur l'accessibilité côté offre (ni smartphone, ni compte, ni lecture requis).

## Cadrages rejetés

- « Générateur de CV pour Madagascar » — place le produit face à Canva/Word sans avantage ; le client primaire n'a pas d'adresse e-mail où envoyer un PDF.
- « La première plateforme de mise en relation locale de Madagascar » — revendication de primauté invérifiable et probablement fausse.
- « Trouvez un jardinier près de chez vous » — centre le récit sur l'employeur alors que le client primaire est le travailleur.
- Mise en avant du scan CIN comme gage de confiance — écarté du communiqué : laisse croire à une vérification d'antécédents, ce qui serait malhonnête.

## Renseignement concurrentiel (vérifié 2026-08-20)

- Plateformes existantes : Asako.mg (100 000+ abonnés revendiqués), Portaljob Madagascar, Job2mada (~2 500 offres), offre-emploi-madagascar.com, madagascaremploi.com. Toutes en sens employeur → candidat, orientées secteur formel. Portaljob publie effectivement des offres « gardien-jardinier ».
- **Vrai concurrent : les groupes Facebook emploi**, populaires car « n'imposant aucune barrière à l'entrée ». Faiblesses exploitables : publications qui disparaissent dans le fil, aucune recherche par métier + quartier, aucune vérification d'identité, smartphone et compte obligatoires.
- **Concurrent le plus redoutable : le bouche-à-oreille** — gratuit *et* porteur de garantie sociale. Non neutralisé par le concept actuel.
- **Correction d'hypothèse fondatrice :** l'affirmation « cela n'existe pas sur le marché malgache » est partiellement fausse. La formulation défendable est : *aucun canal existant ne fonctionne dans le sens travailleur → employeur, ni n'est utilisable sans accès internet.*

## Contexte marché (vérifié 2026-08-20)

- Emploi informel : 95,2% des emplois ; ~8,5M de travailleurs sur 10,7M d'actifs (INSTAT). Chômage officiel ~3% mais masque un sous-emploi massif.
- Pénétration internet : 20,4% (~6,6M début 2025), contre ~39% de moyenne africaine.
- Couverture du signal mobile : ~92% de la population ; 4G ~71%.
- Mobile money : ~60% des adultes utilisent MVola (Telma ~50% de part), Orange Money ou Airtel Money.
- SMIG : 300 000 Ar/mois au 1er mars 2026 (+14%). Salaire brut moyen ~500 000 Ar.
- Change : 1 EUR ≈ 4 876 Ar (mai 2026). **500-1 000 Ar ≈ 0,10-0,20 € ≈ 0,2-0,3% du SMIG mensuel.**
- **Asymétrie structurante :** on ne peut pas supposer le web côté travailleur, mais on peut supposer le téléphone et le paiement mobile.

## État réel du produit

- **Construit :** Voie Complète (formulaire 8 étapes, scan CIN, 4 modèles, PDF) ; Voie Express (parcours 4 écrans, enregistrement en base, fiche publique `profil-public.php`, QR code).
- **Non construit :** page de recherche employeur, déblocage de contact, tout mécanisme de paiement, enrôlement des opérateurs (statut opérateur activé par `UPDATE` SQL manuel).
- **Constat :** le côté offre est terminé, le côté demande n'existe pas — or c'est lui qui crée la valeur.

## Contraintes techniques

- PHP/MySQL sans framework, dompdf, pas de build, pas de CI, pas de tests automatisés. Hébergement local XAMPP à ce stade.
- Dépendance externe : Gemini pour l'OCR de la CIN (clé API, coût à l'échelle, disponibilité).
- Difficulté technique globale : faible. **Le risque d'exécution est opérationnel (réseau cybercafé), pas logiciel.**

## Signaux de périmètre

**Bloquant avant toute facturation réelle :**
- Modification d'un profil existant (changement de numéro, de métier) — impossible aujourd'hui.
- Consentement tracé, politique de conservation et procédure de suppression des données.
- Règle de densité par zone, inscrite dans le logiciel : ne pas encaisser dans un quartier sans profils ni employeurs.

**Évolution rapide, non bloquant :**
- Métiers multiples par profil (cas typique : maçon + jardinier).

**Hors périmètre / à trancher :**
- Sort de la Voie Complète — la geler le temps de prouver le côté demande, ou assumer deux produits. Voie Complète et Voie Express ne partagent ni utilisateur, ni mesure de succès, ni modèle économique : la mutualisation est technique, pas stratégique.

## Questions ouvertes (par ordre de priorité)

1. **Un employeur malgache chercherait-il en ligne plutôt que dans son réseau ?** → ~20 entretiens de ménages employeurs à Antananarivo. **Chemin critique : à faire avant d'écrire la page de recherche.** Si la réponse est non, le produit entier est sans objet.
2. **Quel montant un employeur accepte-t-il pour débloquer un contact ?** → mêmes entretiens. Détermine tout le modèle économique.
3. **Combien de profils faut-il dans un rayon de 5 km pour qu'une recherche aboutisse ?** → simulable une fois 50-100 profils réels créés dans un quartier.
4. **Les gérants de cybercafés accepteront-ils la commission proposée ?** → 5 entretiens suffisent.

## Modèle économique — conclusion à assumer

Les 500-1 000 Ar **ne sont pas un revenu, ils rémunèrent la distribution**. À 1 000 Ar avec moitié captée, il faudrait ~10 000 inscriptions/mois pour 1 000 € de revenu mensuel. Le revenu doit venir du côté employeur (déblocage de contact) ou d'un tiers. Rester indécis est acceptable ; présenter les 1 000 Ar comme le modèle ne l'est pas.

## Stratégie d'amorçage

Densité **locale**, pas volume national. Le rayon en km rend la géographie décisive : un employeur d'Itaosy ignore les profils de Toamasina. Saturer un quartier (ordre de grandeur : quelques dizaines de profils actifs dans 5 km) avant d'ouvrir le suivant, puis recruter les employeurs de ce même quartier via les cybercafés, qui les connaissent déjà.

## Risques majeurs

1. **Le côté demande ne décolle jamais** — les employeurs restent au bouche-à-oreille, gratuit et rassurant. Le plus probable, et le seul qui tue vraiment.
2. **Incitation opérateur mal alignée** — les cybercafés touchent à l'inscription et n'ont aucun intérêt à ce qu'elle serve ; risque d'inscriptions dans des zones vides et d'effondrement de réputation. Aucun mécanisme de contrôle n'existe.
3. **Exposition juridique** — numéros de CIN collectés, données personnelles publiées, consentement recueilli oralement par un tiers rémunéré, aucune procédure de retrait. Trois manquements distincts.
4. **Usage détourné** — un annuaire de femmes de ménage avec noms, quartiers et téléphones est un objet sensible.
5. **Fossé concurrentiel fragile sans exclusivité** — un concurrent qui rémunère mieux retourne les cybercafés. L'actif à protéger est la relation opérateur, pas le code.

## Décisions produit déjà actées

- **Deux canaux d'accès employeur**, pas un : page de recherche publique à contact masqué **et** cybercafé comme intermédiaire pour les employeurs peu à l'aise en ligne.
- **Règle de divulgation à deux vitesses :** numéro visible via QR code / lien direct remis en main propre (c'est une carte de visite) ; masqué en recherche publique. Le masquage se décide dans la page appelante, pas dans le profil — ne pas retirer le numéro de `profil-public.php`.
- **Métiers normalisés** via liste prédéfinie (83 entrées, `metiers.json`) — condition nécessaire de toute recherche.
- **Numéro de CIN jamais affiché sur une page publique** — respecté, à revérifier à chaque évolution.

## Verdict

**`needs-heat`.** Raison d'être solide, actif difficile à répliquer, mais hypothèse centrale non vérifiée et moitié créatrice de valeur non construite.

**Ordre imposé pour passer à `forged` :**
1. 20 entretiens employeurs (chercheraient-ils en ligne ? combien paieraient-ils ?)
2. 5 entretiens gérants de cybercafés (la commission les motive-t-elle ?)
3. Écrire la règle de densité dans le logiciel
4. Régler consentement / conservation / suppression avant toute mise en ligne réelle
5. Trancher le sort de la Voie Complète
