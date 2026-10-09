# PolitiConnect — Project Handoff

> **Stale since 2026-10-09.** ROADMAP.md and CLAUDE.md are authoritative. The status rows here are out of date, Decision #6 (GUID quoting) is wrong, and the endpoint list has been replaced by ROADMAP.md.

Status snapshot as of tonight's session. Written for a coding agent picking this up fresh.

> **Tip:** if you want Claude Code to auto-load this as project context on every session, rename this file to `CLAUDE.md` (or `.claude/CLAUDE.md`) in the repo root — Claude Code reads that filename automatically at launch.

---

## 1. What the app is

**PolitiConnect** — a tool for the average citizen to follow Dutch national politics without wading through parliamentary procedure. It syncs data from the Tweede Kamer (Dutch House of Representatives) OData API and surfaces it as something readable: what got decided, whether it passed, and how each party voted.

Core value proposition settled on tonight: **"how did my party vote, and on what."** Everything in the first-release scope supports that loop.

**Stack:** Symfony (PHP), Doctrine ORM, MySQL 8.0 (Docker), PHPUnit. Source API: `https://gegevensmagazijn.tweedekamer.nl/OData/v5/`.

---

## 2. Architecture pattern

One pipeline, reused per entity:

```
ApiProvider (generic OData client, paginates via $top/$skip)
  → Filter (per-entity: builds $filter/$orderby/$top query params)
  → Dto::fromArray() (maps raw API array → typed DTO)
  → Importer / Command (loops the ApiProvider generator, calls importer)
  → Repository::saveDtoAsEntity() (DTO → Doctrine entity, persist + flush)
```

`ApiProvider::search()` yields DTOs lazily via a `Generator`, paginating automatically while `withSkip()` is true and a full page comes back.

Entities covered so far: **Besluit** (`Decision`), **Stemming** (`Vote`), **Fractie** (`Fraction`). Referenced but not actively synced in this session: **Zaak** (`GovermentCase` — note: typo'd as "Goverment" consistently in the existing codebase, kept as-is rather than silently renamed), **Agendapunt**.

---

## 3. Entity relations (current state)

```
Decision (Besluit)
  ├─ ManyToOne → Agendapunt (not modeled as a relation yet, just an id-ish field conceptually)
  ├─ OneToMany → Vote           ✅ wired
  └─ caseId / Zaak              ❌ NOT a real relation — see Gap #2 below

Vote (Stemming)
  ├─ ManyToOne → Decision (not null, cascade delete)   ✅ wired
  ├─ ManyToOne → Fraction (nullable, set null on delete) ✅ wired
  ├─ fractionSize (int, nullable)   — kept as a point-in-time snapshot, NOT derived from Fraction
  └─ fractionName (string, nullable) — kept as a point-in-time snapshot, NOT derived from Fraction

Fraction (Fractie)
  └─ activeFrom / activeUntil — exists on the entity, but no sync command populates it yet
```

---

## 4. Component status

Legend: ✅ done & confirmed working · ⚠️ built but last known broken/unconfirmed · ❌ not started

| Component | Status | Notes |
|---|---|---|
| `Decision`/`DecisionDto`/`DecisionField`/`DecisionRepository` | ✅ | Updated for OData v5 field renames (`BesluitSoort`→`Soort`, `Opmerking`→`Notitie`, `AgendapuntZaakBesluitVolgorde`→`Volgorde`). `StemmingsSoort`/`voteMethod` removed — not in v5 response. |
| `DecisionFilter` | ✅ | `$orderby=GewijzigdOp desc` added. **Latent bug**: `agendaItemId eq %s` condition is unquoted (same class of bug as #7 below) — not yet hit because nothing passes a real `agendaItemId` yet. Fix before using it. |
| `SyncDecisionCommand` | ✅ | Pulls all `Besluit` directly, independent of `Zaak`. This is what resolved the FK-violation class of bug — see Decision Log #1. |
| `Vote`/`VoteDto`/`VoteField`/`VoteRepository` | ✅ | `VoteDto` never needed changes — raw API shape was fine. Repository resolves `Decision`/`Fraction` via `getReference()` (lazy proxy, no existence check — see Decision Log #4). |
| `VoteFilter` | ⚠️ | Rewritten to filter by a batch of `decisionIds` via OData `in (...)`. **The GUIDs must be single-quoted** (`in ('guid','guid')`) or the API silently returns `[]` instead of erroring. This was fixed, then found *regressed* (unquoted again) in the last code paste shared. **Verify the quoting is actually present in the live file before trusting any sync:vote output.** |
| `SyncVoteCommand` | ⚠️ | Rewritten to pull `decisionIds` from `DecisionRepository::findIds()`, batch them (chunks of 50), sync per batch. Mechanically sound; blocked by the `VoteFilter` quoting question above, and currently fails outright — see Gap #1. |
| `FractionDto` / `FractionField` / `FractionFilter` / `SyncFractionCommand` | ❌ | **Never written.** This is the #1 blocker — see Gap #1. |
| `FractionRepository` | ✅ | `findActive()` added (deleted=false AND activeUntil null-or-future). `saveDtoAsEntity()` had `?? false` fallbacks on nullable columns fixed to `?? null`. |
| `FractionController` (`GET /api/fractions`, `GET /api/fractions/{id}/votes`) | ⚠️ | Written, never run against real data. Will 500/FK-fail until Gap #1 is closed. |
| `app:sync:all` orchestrator | ❌ | Only sketched as illustrative example code during a design discussion — never actually added to the codebase. Intended order: `sync:case` → `sync:decision` → `sync:fraction` (doesn't exist) → `sync:vote`. |
| Migrations | ❌ | Several are needed (drop `vote_method` column, `decision_id`/`fraction_id` → real FKs, `fraction_size`/`fraction_name` column changes). Discussed but none generated in this session — run `bin/console make:migration` and review the diff before applying. |
| `tests/bootstrap.php` (drop/create/copy-data test DB refresh) | ⚠️ | Mechanically works now (fixed: wrong `Application` class, DBAL 4 DSN parsing, progress logging added). **Blocked on an unresolved question** — see Gap #3. |

---

## 5. Critical gaps, in priority order

### Gap #1 — Fraction sync doesn't exist (active blocker)
The most recent concrete signal from testing was a FK violation on `vote.fraction_id` — votes reference fractions that were never imported, because `FractionDto`, `FractionField`, `FractionFilter`, and `SyncFractionCommand` were never built. `sync:vote` cannot complete successfully until this exists.

When building it, mirror `SyncDecisionCommand`'s shape, but:
- **Sync ALL fractions, not just currently-active ones.** Old votes reference fractions that may be dissolved/historical. If `FractionFilter` filters to "active only," FK violations on `fraction_id` will persist for any historical vote. `activeUntil` in the past should not exclude a fraction from being synced — only an explicit `Verwijderd`/deleted flag should.
- **Unresolved question:** does the `Fractie` endpoint return historical/dissolved fractions by default, or only current ones? Check with a plain `$top=250` request and eyeball for any `activeUntil` values in the past before assuming either way.
- **Unresolved question:** does `Fractie.Id` stay stable across a party rename/merger, or does the API mint a new GUID? This determines whether the `fractionName` snapshot on `Vote` is filling a real gap or just cheap insurance. Not blocking, but worth checking once there's real data to inspect.
- **Unresolved question:** no room/chamber field exists on `Fraction` (Tweede Kamer vs Eerste Kamer). Everything synced so far assumes Tweede-Kamer-only end to end (matching `GovermentCaseFilter`'s `room: 'Tweede Kamer'`). If the `Fractie` endpoint can return Eerste Kamer fractions too, "currently active" as defined for the fraction-list endpoint needs a stronger filter than `activeUntil`.

### Gap #2 — `Decision.caseId` / `Zaak` relation doesn't actually persist
`DecisionDto` carries `caseId`, but `Decision` the entity has no `caseId` column or relation — it's silently dropped at `saveDtoAsEntity()` time. This blocks:
- Roadmap point 3 (recent-votes feed filtered to `caseId IS NOT NULL`)
- Any "what case is this decision part of" UI

Needs: a `caseId` column (or better, a real relation to a `GovermentCase`/`Zaak` entity, following the same `ManyToOne`/`OneToMany` pattern already used for `Decision`↔`Vote`) added to `Decision`, wired through `DecisionRepository::saveDtoAsEntity()`.

### Gap #3 — Test bootstrap's data source is unconfirmed / may not exist
`tests/bootstrap.php` copies data from `SOURCE_DATABASE_URL` into the test DB before each run. That env var was filled with a **placeholder** value during this session, never a real one. The one `docker-compose` snippet shared defines only **one** MySQL container, on port `3308` — the same port the test DB already uses. This raises a real possibility that there's no second database to copy from at all right now.

Before relying on this bootstrap:
1. Decide what "copy data" should actually mean — a second live dev DB on a different port/container? A seed SQL dump checked into the repo? Fixtures (e.g. `doctrine/doctrine-fixtures-bundle`)?
2. If a second DB is wanted, add it as its own service in `docker-compose` with its own port, and point `SOURCE_DATABASE_URL` at it for real.
3. If a dump/fixtures approach is preferred instead, the `copyDataFromSource()` function in `tests/bootstrap.php` needs a different implementation — the current table-by-table `TRUNCATE` + row-copy logic assumes a live second connection.

---

## 6. Decisions made tonight (don't re-litigate these)

1. **Sync ALL decisions, not just ones linked to a `Zaak`.** A `Besluit` can exist with no `Zaak` (most are procedural). Scoping decision-sync to "only decisions reachable via case" permanently loses complete MP/fraction voting records for procedural votes, and doesn't even avoid the FK-violation problem — it just relocates it from "sync ordering" to "silently missing data." Extra `Decision` rows with `caseId = null` cost almost nothing; missing votes cost real information.
2. **`caseId IS NOT NULL` filtering belongs at the display/query layer, not the sync layer.** Case-centric views (the main feed) should filter to case-linked decisions. Fraction/person voting-record views should NOT filter this way — a procedural vote is still a real vote with real consequences.
3. **`Vote.fractionSize` and `Vote.fractionName` are point-in-time snapshots, kept independently of the `Fraction` relation — not redundant with it.** `Fraction.seatCount`/name reflect *current* state; a vote from years ago should permanently show the party's size/name *as of that vote*, since seat counts and (possibly) party identity change via elections, splits, and mergers. The relation exists for identity/navigation ("which fraction, let me look them up"); the snapshot columns exist for historical accuracy ("how big were they *then*"). Both are needed; neither substitutes for the other.
4. **`getReference()` (lazy Doctrine proxy) is used to resolve FK relations during import, not `find()`.** This avoids an extra `SELECT` per row, but means a dangling reference only fails at `flush()`-time with a DB-level FK exception, not earlier with a clear message. Known tradeoff, not a bug — revisit only if clearer failure messages become worth the extra query cost.
5. **Granular sync commands (`sync:case`, `sync:decision`, `sync:fraction`, `sync:vote`) stay separate; a thin `app:sync:all` orchestrator calls them in dependency order.** Rejected collapsing into one big command — would lose the ability to re-run/backfill a single entity type independently, and tends to accumulate entity-specific logic in one place (which is exactly how the missing-braces bug happened).
6. **OData quirks confirmed through manual Postman testing against the live API** (worth remembering for any new filter written against this service): string/Guid literals in `eq`/`in` clauses must be single-quoted, or the service silently returns an empty result set rather than erroring. This is easy to regress without noticing — there's no exception to catch, just quietly-wrong results.

---

## 7. Bugs found & fixed this session (changelog)

1. `DecisionDto::fromArray()` — `caseId: $caseId !== null` assigned a **boolean** to a `?string` property. Fixed to `caseId: $caseId`.
2. `SyncGovermentCaseCommand` — `foreach` over `$dto->decisions` was missing braces, so only the *last* decision per case was ever actually imported (the import call was outside the loop body). Fixed.
3. OData unquoted-literal bug (see Decision Log #6) — hit in `VoteFilter`'s `in (...)` clause; same latent issue still present in `DecisionFilter`'s `agendaItemId eq %s` (unused so far, not yet exercised).
4. `FractionRepository::saveDtoAsEntity()` — `?? false` fallbacks on nullable string/int/date columns (would store boolean `false` into typed `?string`/`?int`/`?DateTimeImmutable` fields). Fixed to `?? null`.
5. `FractionService::getFractionsWithSeats()` — invalid PHP (`$fractions->filter_>seatcount`, not a real method on arrays). Flagged; recommend removing or replacing with a proper repository-level query rather than in-memory filtering.
6. Test bootstrap: wrong `Application` class (`Symfony\Component\Console\Application` expects a string name; needed `Symfony\Bundle\FrameworkBundle\Console\Application`, which takes a `KernelInterface` and knows how to run Symfony/Doctrine commands).
7. Test bootstrap: Doctrine DBAL 4.x removed automatic `url` parsing from `DriverManager::getConnection()` — needed `Doctrine\DBAL\Tools\DsnParser` to parse the DSN string first.

---

## 8. First-release roadmap status

| # | Feature | Status |
|---|---|---|
| 1 | Fraction pages — active fractions + recent votes per fraction, clickable through to decision detail | API built (`FractionController`), blocked by Gap #1 |
| 1.1 | Party-specific page — all votes for one fraction, paginated | Built as `GET /api/fractions/{id}/votes` |
| 2 | Decision detail page — `BesluitTekst`, status, per-fraction vote breakdown | ❌ Not started |
| 3 | Recent-votes timeline — voted decisions only, `caseId IS NOT NULL`, newest first | ❌ Not started, blocked by Gap #2 |
| 4 | Search | Explicitly deprioritized for v1 |

Open design question not yet answered: should the overview feed (point 3) show a full per-fraction roll-call grid, or just outcome + vote tally with full breakdown reserved for the decision detail page? Leaned toward the latter for scannability, not finalized.

---

## 9. Suggested next steps, in order

1. **Build the fraction sync** (`FractionDto`, `FractionField`, `FractionFilter`, `SyncFractionCommand`) — unblocks everything downstream. Follow `SyncDecisionCommand`'s shape.
2. **Verify `VoteFilter::buildConditions()` actually has the GUID-quoting fix applied**, then run `sync:case` → `sync:decision` → `sync:fraction` → `sync:vote` end to end and confirm real vote rows land with real `fraction_id`/`decision_id` values.
3. **Generate and review the pending migrations** (`bin/console make:migration`) before they drift further from the entity definitions.
4. **Add `caseId`/`Zaak` relation to the `Decision` entity** (Gap #2) — needed for the roadmap point 3 feed.
5. **Resolve the test bootstrap's data source question** (Gap #3) before depending on it for CI or regular local testing.
6. **Build `app:sync:all`** for real, once the fraction sync exists and the dependency order is confirmed working.
7. Once 1–4 are solid: decision detail endpoint, then the recent-votes timeline endpoint.