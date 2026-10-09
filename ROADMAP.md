# PolitiConnect — First-release roadmap

Working plan for `Project-handover.md` §8. Each stage is built, tested and merged on its own.
**Scope:** backend JSON API only. The frontend gets planned separately later.

**Decisions:**
- "Newest first" uses a **real vote date** synced from the API, not `modifiedOn`.
- Each feed item shows **outcome + tally**. The full per-fraction breakdown lives only on the decision detail page.
- Handover §6 decisions still apply, **except #6.** On GUID fields, quotes cause a type error (see Stage 0 findings).

Status legend: ✅ done · 🟡 in progress · ⬜ not started

| Stage | Delivers | Depends on | Status |
|---|---|---|---|
| 0 | Reliable sync + clean test setup | — | ✅ |
| A | `Decision.votedOn` (real vote date) | 0 | 🟡 |
| 1 | `GET /api/fractions` | A | ⬜ |
| 1.1 | `GET /api/fractions/{id}/votes` | A | ⬜ |
| 2 | `GET /api/decisions/{id}` + outcome service | A | ⬜ |
| 3 | `GET /api/decisions/recent` | 2 | ⬜ |
| 4 | Search | — | Deferred for v1 |

Order: 0 → A → 1 → 1.1 → 2 → 3. Stage 2 can be built in parallel with 1/1.1 once A is done.

---

## Conventions for all API stages

- **Routes:** under `/api/...`, using `#[Route]` attributes on `final` controllers in `src/Controller/`.
- **Layering:** controller → service (`src/Service/`) → repository query methods. Controllers stay thin. Queries live in the repositories, next to `findActive()` and `findIds()`.
- **Response shapes:** readonly classes in `src/Dto/Response/`. These are separate from the import DTOs that implement `DtoInterface`, and are returned with `$this->json()`.
- **Pagination:** `?page=1&limit=20`, with `limit` capped at 100. Uses Doctrine's `Paginator`. Responses look like `{ items, page, limit, total }`, built by one shared helper (`src/Service/Pagination.php`).
- **Not found:** an unknown ID returns 404 JSON.
- **Tests:** WebTestCases that seed their own entities through the EntityManager. The test DB starts empty, and a shared helper goes in `tests/Factory/` once it's first needed.

---

## Stage 0 — Make the sync trustworthy ✅

**Done (committed on `Development`):**
- [x] `FractionRepository::saveDtoAsEntity()`: removed the `?? false` fallbacks, so nullable fields now get `null`. The one exception is `deleted`: `Fraction::$deleted` is a non-nullable bool, so `$dto->deleted ?? false` stays on purpose (a null `Verwijderd` would otherwise be a `TypeError`).
- [x] `app:sync:fraction` now fetches every fraction, not just the first page:
  - `withSkip: true`, ordered by `Id asc` so paging is stable
  - includes nameless fractions
  - `--max` defaults to unlimited
  - fixed the copy-pasted description and log text
- [x] `FractionFilter`: removed the unused `includeInactiveFraction`. `FractionField::Endpoint` is now `Fractie`.
- [x] `Vote.fraction_id`: the entity now uses `onDelete: 'SET NULL'`. Migration `Version20261005185506` already sets the FK to `ON DELETE SET NULL`; it only needs to be applied.
- [x] `tests/bootstrap.php`: no longer copies the dev DB; it only drops, creates and builds the schema. Removed `SOURCE_DATABASE_URL` from `.env.test` and deleted the dead `tests/DocumentProviderTest.php`.
- [x] `docker-compose.yml`: the password in the backend's `DATABASE_URL` had been replaced with the literal text `[REDACTED]` in commit `e90dedf`. It's back to `secret`, which matches `MYSQL_PASSWORD`.

**Still to do. Run these inside the backend container (`docker compose exec backend sh`):**
- [x] Migration for the `vote.fraction_id` FK change already exists (`Version20261005185506`), so `make:migration` is not needed. Check `doctrine:migrations:status` first.
- [x] `php bin/console doctrine:migrations:migrate -n`
- [x] `php bin/console app:sync:all`. It should run without FK errors.
- [x] Spot-check: `vote` rows have non-null `decision_id` and, where expected, `fraction_id`.
- [x] Run the tests as root, because `user` can't drop or create `politiconnect_test`:
  `DATABASE_URL="mysql://root:rootsecret@db:3306/politiconnect?serverVersion=8.0&charset=utf8mb4" php bin/phpunit`
- [x] Commit Stage 0.
- [x] Optional: `SyncAllCommand` passes its own `$input` to each sub-command, so decisions and votes stay at their 500-record `--max` defaults. Decide whether `sync:all` should sync everything.

**Findings from the live API (checked 2026-10-05):**
- **Literal quoting:**
  - `Edm.Guid` fields must **not** be quoted. `Besluit_Id eq 'guid'` returns a type error, while `Besluit_Id eq guid` works.
  - `in (...)` accepts both forms.
  - `Edm.String` fields (`Afkorting`) do need quotes.
  - So `VoteFilter` and `DecisionFilter` were already correct.
- **Fractions:**
  - 60 fractions that aren't deleted, of which 43 are dissolved (`DatumInactief` is set).
  - None of them is nameless.
  - Seat counts are null or 0 for dissolved fractions.
- **No Eerste Kamer fractions:** the Senate-only party OSF isn't returned.
- **`Besluit.Status` values:** `Besluit` (664k), `Voorstel` (6.5k), `Concept voorstel` (26), `Nog te verwerken besluit` (18), and about 68k null. All four are in the `DecisionStatus` enum.
- **`BesluitTekst` for decisions that have votes** is exactly `Aangenomen.` (passed) or `Verworpen.` (rejected). Stage 2 derives the outcome from this.
- **Still unchecked:** whether `Fractie.Id` stays the same after a party rename.

---

## Stage A — Vote date on Decision ✅

Vote date comes from Decision → AgendaPoint → Activity (`Activity.Datum` in Europe/Amsterdam), not from a stored `Decision.votedOn` column.

- [x] Step 1: `Activity` (entity, DTO, `ActivityField`, repository, importer, filter, `app:sync:activity`). Tweede Kamer only. Migration `Version20261008091234`.
- [x] Step 2: `AgendaPoint` (same set, `app:sync:agendapoint`). Tweede Kamer only through `Activiteit/Kamer eq 'Tweede Kamer'`. `activity_id` is `ON DELETE SET NULL`, and a missing activity saves a null relation. Migration `Version20261008092430`. Both commands are in `app:sync:all`. `--max` stays at 500 (a larger run leaks memory on the dev laptop), so a full backfill takes many runs. `app:sync:agendapoint` and `app:sync:activity` fetch only what the synced decisions need (agenda ids from `decision`, activity ids from `agenda_point.activity_source_id`, migration `Version20261008100354`) and skip ids already stored, so repeated 500-record runs make progress. Importers link in both directions, so the sync order does not matter. `app:sync:all` order: fraction, case, decision, agendapoint, activity, vote.
- [x] Step 3: `Decision.agendaPoint` (`agenda_point_id`, nullable, `ON DELETE SET NULL`; raw `agendaItemId` stays). Migration `Version20261008094500`. Verified: all 591 synced decisions link to an agenda point.
- [x] Step 4: `Decision::getVotedOn()` returns the day (Europe/Amsterdam) from `Activity.Datum` through the agenda point, or null when unknown. "Ingediend" decisions are not special-cased: picking the result decision belongs in the query/display layer.

- [x] Tests: command and repository tests for activity and agendapoint, DTO and filter unit tests, and `tests/Unit/Entity/DecisionTest.php` for `getVotedOn()` (null cases, Amsterdam day, no day shift across time zones).
- [x] Verify coverage: run `app:sync:all` until done, then count decisions with votes that have a `getVotedOn()` date. Verified 2026-10-09: counts were stable after 2 runs (568 decisions, 184 agenda points, 65 activities, 439 votes), and 18 of 18 decisions with votes have a date (100%). Small sample: the second run added no decisions or votes.

**Done when:** most decisions that have votes have a `votedOn`. Stage A turns ✅ once the coverage check above passes.

## Stage 1 — Fraction list: `GET /api/fractions` ⬜

Returns the active fractions with their seats, ordered by seat count descending. Each fraction carries its 3 most recent votes, and each vote links to its decision.

- **Repository:** reuse `FractionRepository::findActive()`, but include `id`. It currently selects scalars without `id`.
- **Service:** replace the stub `FractionService::getFractionsWithSeats()`.
- **Recent votes:** add `VoteRepository::findRecentByFraction(array $fractionIds, int $perFraction)`. One query, ordered by `decision.votedOn`, fraction-level votes only (`personId IS NULL`).
- **Response:** `FractionSummary { id, abbreviation, name, seatCount, recentVotes: [{decisionId, type, votedOn}] }`.
- **Controller:** rewrite the stub `FractionController` (currently `GET /fraction`) and update `tests/Controller/FractionControllerTest.php`.

## Stage 1.1 — Party votes: `GET /api/fractions/{id}/votes?page&limit` ⬜

- **Contents:** all votes for one fraction, newest first by `decision.votedOn`.
- **Not filtered to case-linked decisions** (handover decision #2).
- **Repository:** add `VoteRepository::findPageByFraction(string $fractionId, int $page, int $limit)`, joined to the decision.
- **Each item:** `{ voteType, fractionSizeAtVote, fractionNameAtVote, decision: {id, textExcerpt, votedOn, outcome} }`. The size and name come from the `Vote` snapshot columns (handover decision #3).
- **404** if the fraction doesn't exist.

## Stage 2 — Decision detail: `GET /api/decisions/{id}` ⬜

Returns the text, status, outcome, `votedOn`, linked case(s), tally and per-fraction breakdown.

- **`DecisionOutcomeService` + `DecisionOutcome` enum (`Passed`, `Rejected`, `Unknown`):**
  - Takes the outcome from the `BesluitTekst` prefix (`Aangenomen` or `Verworpen`).
  - If there is no prefix, compares the seat tally.
  - Stages 1.1 and 3 reuse this service.
- **Tally:**
  - Fraction votes: sum `fractionSize` per `VoteType`.
  - Individual (hoofdelijke) votes, i.e. those with `personId` set: count 1 each.
  - Skip votes where `mistake` or `deleted` is true.
- **Breakdown:** one row per fraction vote, `{fractionId, fractionName (snapshot), fractionSize (snapshot), type}`.
- **Linked cases:** found through the `GovermentCaseDecision` pivot, returned as `{id, number, title}`.

## Stage 3 — Recent-votes timeline: `GET /api/decisions/recent?page&limit` ⬜

- **Contents:** only case-linked decisions that have at least one vote, newest first by `votedOn`.
- **Query:** `DecisionRepository::findRecentVotedCaseLinked(page, limit)`. It filters with `EXISTS` on `goverment_case_decision` and `EXISTS` on vote, and excludes deleted decisions.
- **Tallies:** computed in one grouped query per page (`GROUP BY decision_id, type`) to avoid N+1 queries, then passed to `DecisionOutcomeService`.
- **Each item:** `{ id, caseTitle, textExcerpt, votedOn, outcome, tally: {for, against, abstain} }`.

## Stage 4 — Search

Deferred for v1.

---

## Verification checklist (every stage)

1. **Schema:** `php bin/console doctrine:schema:validate`, and `make:migration` shows no unexpected diff.
2. **Sync** (stages 0 and A): `php bin/console app:sync:all`, then spot-check row counts and null FKs.
3. **Tests:** `php bin/phpunit tests/Controller/<Name>ControllerTest.php`. Cover the happy path, 404, pagination bounds and outcome derivation.
4. **Manual:** `curl http://localhost:8080/api/...` against real synced data.
