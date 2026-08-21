# PRD Quality Review — CVMG — Voie Express, côté employeur (pilote instrumenté)

## Overall verdict

This PRD has excellent process discipline — real trade-offs, honest open questions, a tight assumption/note-for-PM apparatus, and a thesis (hypothesis-testing pilot, not a launch) that actually drives scope. But it rests on an unverified brownfield premise: the "zone/quartier" attribute that FR-1, FR-3, FR-8, FR-9 and FR-16 all search and gate on **does not exist anywhere in the current code or database** — the worker-registration flow (`express-cv.php` / `enregistrer-express.php`) captures no location field at all, and the `cv` table schema has no such column. This is exactly the kind of omission the PRD's own discipline elsewhere goes out of its way to surface — and here it doesn't, which is the main risk a developer building from this document would hit first.

## Decision-readiness — strong

Trade-offs are named with what's given up, not smoothed. §5 states plainly that the worker-side 500-1000 Ar fee "ne sont **pas** un revenu pour CVMG" and quantifies why (~10 000 inscriptions/mois needed for viability) — a real concession, not a hedge. §4.4's `[NOTE FOR PM]` sits at a genuine tension (FR-8/FR-9 only guard the *employer* transaction, not the *worker's* payment into a possibly-dead zone) rather than a safe checkpoint. §11's eight open questions are actually open — none are answered in the next sentence — and several are explicitly triaged by whether they block code (FR-9's threshold value) versus block pilot launch (Q7, legal status of cybercafé intermediation) versus neither (Q5, qualitative follow-up). That triage is itself a decision-readiness signal few PRDs bother with.

No findings — this dimension does its job.

## Substance over theater — strong

Four personas, each driving distinct FRs (Employeur → FR-1–9, Opérateur → FR-4/FR-6, "Porteur du produit" → FR-13–15 instrumentation) — none is decorative. The Vision (§1) is specific to this product's actual gap ("le côté offre... est fini et fonctionnel. Le côté demande n'existe pas") rather than swappable boilerplate. §7 Contraintes reads as specific engineering constraints (`exiger-connexion.php`, filtrage par propriétaire, no payment gateway) rather than NFR boilerplate ("must be scalable/secure"). The addendum's "Cadrages de positionnement rejetés" section is the opposite of innovation theater — it records claims the team considered and discarded, with reasons.

No findings.

## Strategic coherence — adequate

The thesis is stated and load-bearing: this is not a launch but an instrument to settle "un employeur malgache cherchera un travailleur en ligne" (§0, §10). Feature prioritization follows it — §4.6 instrumentation is argued into scope as core, not nice-to-have ("pas une option secondaire"), and counter-metrics (SM-C1, SM-C2) are named specifically to prevent the pilot from mistaking traffic for validated intent. This is a real thesis-driven MVP, not a backlog with headings.

### Findings
- **medium** Vision overclaims the starting state the thesis depends on (§1) — "Le côté offre de la Voie Express... est fini et fonctionnel" is stated flatly, but the search feature this PRD specifies (FR-1, FR-3, FR-8/9, FR-16) requires a zone/quartier attribute that the offer side does not currently capture (see Scope honesty below). The strategic framing implies the demand side is the only missing piece; in fact the offer side needs a data-model change too. *Fix:* qualify §1 to note the offer side needs a location field added before the demand side can search against it, or explicitly fold that change into scope.

## Done-ness clarity — thin

Most FRs carry genuinely testable consequences (FR-2, FR-6, FR-7, FR-8, FR-10, FR-11 are all concretely verifiable — e.g. FR-7's "vérifiable par inspection des réponses HTTP"). But the dimension is undercut by one structural gap that cascades through five FRs.

### Findings
- **critical** FR-1/FR-3/FR-8/FR-9/FR-16 all key off a "zone" that doesn't exist in the data model (§3 Glossaire: "Zone... déterminée par le champ quartier saisi à l'inscription du travailleur"; §12 lists no assumption about it). Verified against the actual registration flow: `enregistrer-express.php` inserts `donnees_json` with `'ville' => ''` and `'adresse' => ''` hardcoded empty, `express-cv.php`'s form has no location step (only nom/prénom/CIN/métier/rayon), and `creer_table_cv.sql` has no zone/quartier/ville column. A repo-wide search for "quartier" in `.php` files returns zero data-field hits. As written, FR-1's testable consequence ("La recherche retourne uniquement des travailleurs dont la zone correspond...") cannot be implemented without first adding a zone-capture step to the worker-registration flow — a change to the côté offre this PRD's Vision (§1) declares already "fini et fonctionnel" and never lists as an FR or in §9.1 scope. *Fix:* add an explicit FR (or a scope note in §9.1/§7) for capturing a zone/quartier field at worker registration, or correct §1/§3 to state this is new work, not existing data to search against.
- **medium** UJ-1's result list shows "quartier/distance" (§2.3, line 47), but FR-1's testable consequences only specify a zone-match + rayon-coverage filter (§4.1) — no FR defines how a numeric "distance" is computed or displayed, and FR-3's excluded-scope explicitly rules out employer geolocation ("Pas de géolocalisation précise de l'employeur"). Without employer coordinates, "distance" per result has no defined source. *Fix:* either drop "distance" from the UJ-1 result description or add a testable consequence in FR-1/FR-3 for what "distance" means absent geolocation (e.g., just the rayon band, not a computed figure).
- **low** FR-3 states rayon options as "1 km / 5 km / plus de 10 km" — the actual registration UI (`express-cv.php`, lines 341-345) offers five bands: 1/2/5/10/"+ loin" (stored as 99). The PRD's FR undercounts the real option set. *Fix:* align FR-3's stated bands with the actual five values, or note the two mid-values collapse for search purposes.

## Scope honesty — adequate

The `[ASSUMPTION]`/`[NOTE FOR PM]` apparatus is real and round-trips cleanly: all three inline assumptions (§4.1 open search, §4.3 cash-only unblocking, §4.5/FR-11 7-day deletion) are indexed in §12 with none extra or missing. §8 Non-Goals is substantive (multi-métier gap, no background checks, no online payment) and §11's open-questions triage (code-blocking vs. launch-blocking vs. neither) is unusually disciplined for a pilot PRD.

### Findings
- **high** The zone/quartier data gap (see Done-ness) is the one omission this section's own standard should have caught and didn't — it is stated as settled fact in the Glossaire (§3) rather than flagged `[ASSUMPTION]` or `[NOTE FOR PM]`, despite being exactly the kind of unverified inference the rest of the document is careful to tag. *Fix:* same as the Done-ness finding — tag it explicitly rather than asserting it.

## Downstream usability — adequate

Glossary (§3) terms are used consistently; FR/UJ/SM IDs are unique and cross-references resolve (e.g., §6 risk 2 correctly points to FR-8/FR-9 and separately to FR-16, §10 SM-1/SM-2 correctly cite the FRs they validate). Standalone-PRD caveat applies loosely since this feeds a developer directly rather than a further UX/architecture pass, so traceability rigor here is appropriately lighter than a chain-top PRD would need.

### Findings
- **low** FR-16 is defined in §4.4 between FR-9 and the start of §4.5 (out of numeric sequence — FR-10 through FR-15 appear later, after FR-16). The placement is explained by content grouping (FR-16 is thematically part of the density guardrail), but a reader or downstream tool scanning IDs in document order will hit 1,2,3,4,5,6,7,8,9,16,10,11,... which reads as a discontinuity even though no ID is actually missing or duplicated. *Fix:* either renumber so IDs appear in document order, or add a one-line note at first mention of FR-16 explaining the out-of-sequence placement (the NOTE FOR PM in §4.4 does this implicitly but doesn't call out the numbering itself).

## Shape fit — adequate

This is a consumer-facing, multi-stakeholder pilot (employer, worker, operator) — UJs with named protagonists are the right shape, and the PRD delivers on it for two of three (Mme Rasoa, M. Randria). Brownfield references are mostly accurate (`exiger-connexion.php`, `metiers.json`, session-based operator auth in FR-4 all check out against the actual code) and the addendum keeps implementation pistes clearly non-prescriptive.

### Findings
- **medium** UJ-3's protagonist ("un opérateur de cybercafé... gérant de cybercafé récemment activé") has no name, unlike UJ-1/UJ-2 — a floating-protagonist pattern the rubric flags even though the role and context are otherwise well specified.
- **medium** §3 Glossaire and the addendum's implementation pistes both assert an existing masking pattern that isn't in the code: "le masquage du numéro y est déjà géré côté page appelante et ne doit pas être déplacé dans le profil lui-même" (§3) and "réutiliser le motif déjà en place dans `profil-public.php`, où la décision d'afficher ou masquer le numéro se prend côté page appelante" (addendum). Inspected `profil-public.php`: it has no masking logic at all — `$telephone` is read from `donnees_json` and rendered unconditionally whenever `est_public = 1` and the row is found by `?id=`. There is no query parameter, no conditional, and no other page in the repo that currently calls it with a masking decision. The "calling page decides" pattern is a reasonable *architecture recommendation* for the new search feature, but is phrased as if it already exists and can be reused — it doesn't. *Fix:* rephrase to prescriptive ("the search results page should decide visibility, following the same call-site-owns-masking principle used for CIN exclusion") rather than descriptive, so a developer doesn't go looking for a nonexistent precedent.

## Mechanical notes

- Glossary drift: none found — "Zone"/"quartier" are used interchangeably but the Glossaire explicitly defines them as synonyms (§3), so this is by design, not drift.
- ID continuity: FR-1–FR-16 all present and unique; only issue is FR-16's out-of-sequence placement in the document body (see Downstream usability finding above). UJ-1–3 and SM-1/2/3/C1/C2 are contiguous and clean.
- Assumptions Index roundtrip: clean. All three inline `[ASSUMPTION]` tags (§4.1, §4.3, §4.5/FR-11) are indexed in §12; no orphan index entries.
- UJ protagonist naming: UJ-1 (Mme Rasoa) and UJ-2 (M. Randria) are named; UJ-3's operator is not (see Shape fit).
- Required sections: all present for a pilot/capability-spec-leaning consumer PRD — Vision, Users, Glossary, Features/FRs, Business model, Risks, Constraints, Non-Goals, MVP scope, Success metrics, Open questions, Assumptions index.
