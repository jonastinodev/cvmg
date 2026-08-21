---
title: 'Encaissement et révélation du numéro'
type: 'feature'
created: '2026-08-21'
status: 'done'
route: 'one-shot'
---

# Encaissement et révélation du numéro

## Intent

**Problem:** Une fois qu'un opérateur trouve un profil via la recherche assistée (Story 3.1), rien ne lui permet de conclure la mise en relation : le numéro complet du travailleur n'est jamais atteignable, ni par lui ni par l'employeur présent.

**Approach:** Ajoute un bouton « Débloquer » par résultat, visible uniquement en session opérateur, qui déclenche une confirmation d'encaissement (`confirm()` — pas de passerelle de paiement, NFR2) puis appelle `debloquer-contact.php`. Cet endpoint revalide les conditions de visibilité du profil, enregistre le déblocage dans une nouvelle table `deblocages` (profil, opérateur, horodatage, prix) et renvoie le numéro — jamais pré-affiché, jamais présent dans le HTML initial de la page.

## Suggested Review Order

**Encaissement et révélation côté serveur**

- Point d'entrée : endpoint dédié, réservé à une session opérateur, qui revalide le profil avant de révéler quoi que ce soit.
  [`debloquer-contact.php:17`](../../debloquer-contact.php#L17)

- L'enregistrement du déblocage est créé au moment de la confirmation, jamais avant — c'est cette ligne qui rend FR-7 vérifiable après coup.
  [`debloquer-contact.php:68`](../../debloquer-contact.php#L68)

- Nouvelle table d'audit financier, `ON DELETE RESTRICT` plutôt que `CASCADE` pour ne jamais perdre l'historique silencieusement.
  [`creer_table_deblocages.sql:14`](../../creer_table_deblocages.sql#L14)

**Interaction côté opérateur**

- `cv.id` ajouté au résultat de recherche — nécessaire pour cibler le bon profil au clic (absent avant cette story).
  [`recherche.php:65`](../../recherche.php#L65)

- Le bouton n'existe que pour une session opérateur — jamais rendu pour un employeur anonyme.
  [`recherche.php:211`](../../recherche.php#L211)

- Confirmation, appel serveur, puis révélation construite via DOM (pas `innerHTML`) : le numéro est un champ libre non validé à la saisie (FR-17), donc traité comme potentiellement hostile ; le `href` `tel:` est réduit aux chiffres et au `+` (même règle que `profil-public.php`) pour rester cliquable même si le format saisi est libre.
  [`recherche.php:231`](../../recherche.php#L231)

**Prix partagé (péripherique)**

- Constante unique, pour ne jamais laisser dériver le prix affiché à l'opérateur et celui réellement enregistré.
  [`constantes-express.php:6`](../../constantes-express.php#L6)
