# PolitiConnect: API endpoints roadmap

This replaces the earlier roadmap. Its stages 0 and A are finished and merged in `b25af27`:
- the sync
- `Activity`/`AgendaPoint`
- `Decision::getVotedOn()`

**Scope:** 4 JSON endpoints in `Backend/`. Search and the frontend come later.

Status legend: `[x]` done · `[~]` in progress · `[ ]` not started

| Stage | Delivers | Depends on | Status |
|---|---|---|---|
| 0 | Housekeeping | — | [x] |
| 1 | Foundations: response DTOs, `PageRequest`/`Page`, `ApiTestCase` | 0 | [ ] |
| 2 | `DecisionOutcome` + `Tally` + `TallyCalculator` + `DecisionOutcomeService` | 1 | [ ] |
| 3 | `GET /api/fractions`, `GET /api/fractions/{id}` | 1 | [ ] |
| 4 | `GET /api/decisions/{id}` | 2 | [ ] |
| 5 | `GET /api/decisions?page&limit` | 2, 4 | [ ] |

Stage 3 can be built in parallel with Stage 2.

**No schema migration is needed.** Every stage only reads columns that already exist. Confirm with `doctrine:schema:validate`.

---

## Conventions

**Layering**
- `final` controllers with `#[Route]` → service in `src/Service/` → repository query methods.
- Controllers only parse input and turn `null` into a 404.

**Responses**
- Response classes are `final readonly` in `src/Dto/Response/`, with public promoted properties, returned via `$this->json()`.
- `symfony/serializer` is not installed, so `json()` uses `json_encode`. That means:
  - Format dates as `'Y-m-d'` strings yourself. Never return a `DateTimeImmutable`.
  - Backed enums encode to their value.

**Errors**
- Errors are always JSON: `{"error": "<message>"}`, returned with `$this->json([...], 404|400)`.
- Don't throw HTTP exceptions. They render as HTML.

**Pagination**
- Defaults: `page=1`, `limit=20`.
- Malformed input (non-integer, `page < 1`, `limit < 1`) returns **400**.
- A `limit` above 100 is **clamped** to 100. The response echoes the effective `limit`.
- Response shape: `{ items, page, limit, total }`.

**Nullable flags and filters**
- `Vote.mistake`, `Vote.deleted` and `Decision.deleted` are nullable. Filter them with `COALESCE(x, false) = false`.
- Counted vote: not a mistake, not deleted, and `type IS NOT NULL`.
- Active fraction: `!deleted && (activeUntil === null || activeUntil >= now)`.

**Existing conventions to keep**
- Keep the `Goverment` spelling.
- The `Vote.fractionName` and `Vote.fractionSize` snapshots are what the API returns.

---

## Stage 0: Housekeeping [x]
- [x] Replace the whole of `ROADMAP.md` with this document. The old notes stay in git history.
- [x] Add a banner at the top of `Project-handover.md`: "Stale since 2026-10-09. ROADMAP.md and CLAUDE.md are authoritative." The reasons:
  - its status rows are out of date
  - its Decision #6 on GUID quoting is wrong
  - its endpoint list has been replaced

---

## Stage 1: Foundations [ ]

**Files**
- `src/Http/PageRequest.php`: `final readonly`, with `DEFAULT_LIMIT = 20` and `MAX_LIMIT = 100`.
  - `fromQuery(InputBag): self` validates with `FILTER_VALIDATE_INT` and clamps `limit`.
  - `offset(): int`.
- `src/Http/InvalidPageRequest.php`: extends `\InvalidArgumentException`.
- `src/Dto/Response/Page.php`: the generic wrapper `{items, page, limit, total}`.
- `tests/Support/ResetsDatabase.php`: a trait holding the truncate logic, moved out of `tests/Support/DatabaseTestCase.php`. `DatabaseTestCase` now uses the trait.
- `tests/Support/ApiTestCase.php`: abstract, extends `WebTestCase`, uses `ResetsDatabase`.
  - Why it's needed: `createClient()` refuses an already-booted kernel, so `DatabaseTestCase` can't be reused.
  - `setUp()`: create the client, get the EM from the container, then truncate.
  - Constants: the existing ID constants, plus `FRACTION_ID_2` and `DECISION_ID_2`.
  - Helpers, which go through `saveDtoAsEntity(new XDto(...))` and `GovermentCaseDecisionRepository::link()`:
    - `seedFraction(...)`
    - `seedVotedDecision(id, text, datetime, ?caseId)`, which creates activity → agenda point → decision → case link. Seed in that order: `DecisionRepository` resolves the agenda point with `find()`.
    - `seedVote(...)`
    - `getJson(uri): array`

**Tests:** `tests/Unit/Http/PageRequestTest.php`
- Defaults.
- `limit` is clamped to the cap.
- `0`, `-1`, `abc` and `1.5` each throw.
- `offset()` returns the right value.

**Done when:** the full suite is green, including the existing `DatabaseTestCase` tests.

---

## Stage 2: Outcome and tally [ ]

**Files**
- `src/Enum/DecisionOutcome.php`: `Passed='passed'`, `Failed='failed'`, `Unknown='unknown'`.
- `src/Dto/Response/Tally.php`: `for`, `against`, `abstain` (all int), and `isEmpty()`.
- `src/Service/TallyCalculator.php`:
  - `fromGroupedRows(array $rows): array<string, Tally>`, keyed by decision id.
  - `fromVotes(iterable<Vote>): Tally`.
  - Counting rule: a fraction vote (`personId === null`) adds `fractionSize ?? 0`. An individual vote adds 1.
- `src/Service/DecisionOutcomeService.php`: `outcomeFor(?string $text, Tally): DecisionOutcome`.
  1. If the trimmed text starts with `Aangenomen` (case-insensitive), return Passed. If it starts with `Verworpen`, return Failed.
  2. Otherwise, an empty tally returns Unknown.
  3. Otherwise, `for > against` returns Passed. Anything else, including a tie, returns Failed.

**Tests**
- `tests/Unit/Service/DecisionOutcomeServiceTest.php`:
  - The text wins even when the tally contradicts it.
  - Whitespace and casing don't matter.
  - The tally fallback cases: for > against, against > for, a tie, and an empty tally.
- `tests/Unit/Service/TallyCalculatorTest.php`:
  - fraction votes
  - individual votes
  - a mix of both
  - a null size
  - multiple decisions
  - `fromVotes` and `fromGroupedRows` give the same result

**Done when:** both unit tests are green, with no DB.

---

## Stage 3: Fraction endpoints [ ]

**Files**
- `src/Repository/FractionRepository.php`, `findActive()`:
  - add `f.id` to the select
  - add `f.seatCount > 0`, because dissolved fractions have 0 seats, not null
  - update the phpdoc
- `src/Service/FractionService.php` (make it `final`). This replaces the stub `getFractionsWithSeats()`.
  - `getActiveFractions(): list<FractionSummary>`.
  - `getFraction(string $id): ?FractionDetail`. It returns `null` if the fraction is missing or `deleted`. Dissolved fractions are returned with `active: false`, because old votes link to them.
- `src/Dto/Response/FractionSummary.php`: `id`, `abbreviation`, `nameNl`, `nameEn`, `seatCount`.
- `src/Dto/Response/FractionDetail.php`: the summary fields plus `activeFrom`, `activeUntil` (as `Y-m-d`) and `active`. This is the per-fraction space; future detail gets added here.
- `src/Controller/FractionController.php`: rewrite the maker stub (`/fraction`).
  - `GET /api/fractions` (`api_fraction_list`) returns `{"items":[...]}`.
  - `GET /api/fractions/{id}` (`api_fraction_show`) returns the detail, or 404 `{"error":"Fraction not found"}`.
- `tests/Controller/FractionControllerTest.php`: rewrite it on top of `ApiTestCase`.

**Response shapes**
```json
GET /api/fractions
{"items":[{"id":"…","abbreviation":"PVV","nameNl":"Partij voor de Vrijheid","nameEn":"Party for Freedom","seatCount":37}]}

GET /api/fractions/{id}
{"id":"…","abbreviation":"PvdA","nameNl":"…","nameEn":"…","seatCount":0,"activeFrom":"1946-02-09","activeUntil":"2025-06-10","active":false}
```

**Tests**
- The list:
  - is sorted by seats, descending
  - excludes dissolved, deleted, and null-seat or 0-seat fractions
  - includes a fraction whose `activeUntil` is in the future
- Show:
  - an active fraction returns `active: true`
  - a dissolved fraction returns 200 with `active: false`
  - an unknown or deleted fraction returns a 404 JSON body
- The old `/fraction` route returns 404.

**Done when:**
- the tests are green
- `debug:router` shows both routes
- curl returns the current Kamer

---

## Stage 4: Decision detail [ ]

**Files**
- `src/Repository/DecisionRepository.php`: `findOneWithVotedOn(string $id): ?Decision`.
  - Left join-fetch `agendaPoint` and `agendaPoint.activity`, so `getVotedOn()` doesn't trigger lazy loads.
  - Exclude deleted decisions.
- `src/Repository/VoteRepository.php`: `findCountedByDecision(string $decisionId): list<Vote>`. Only counted votes, ordered by `fractionSize DESC, fractionName ASC`.
- `src/Repository/GovermentCaseDecisionRepository.php`: `findCasesForDecisions(list<string> $ids)`.
  - Returns `{decisionId, id, number, title}` rows.
  - Excludes deleted cases and orders by `gc.number`.
  - Stage 5 reuses it.
- `src/Service/DecisionService.php` (new, `final`): `getDecision(string $id): ?DecisionDetail`.
  - The tally comes from `TallyCalculator::fromVotes()`.
  - The outcome comes from `DecisionOutcomeService`.
  - The breakdown is built from the votes where `personId === null`.
- `src/Dto/Response/`:
  - `DecisionDetail`: `id`, `text`, `status`, `votedOn`, `outcome`, `cases`, `tally`, `breakdown`
  - `CaseRef`: `id`, `number`, `title`
  - `FractionVote`: `fractionId`, `fractionName`, `fractionSize`, `voteType`
- `src/Controller/DecisionController.php` (new, `final`): `GET /api/decisions/{id}` (`api_decision_show`).

**Response shape**
```json
{"id":"…","text":"Aangenomen.","status":"Besluit","votedOn":"2026-10-07","outcome":"passed",
 "cases":[{"id":"…","number":"36600-XV-12","title":"Motie van het lid …"}],
 "tally":{"for":88,"against":62,"abstain":0},
 "breakdown":[{"fractionId":"…","fractionName":"PVV","fractionSize":37,"voteType":"Voor"}]}
```

**Behaviour**
- A decision without a case or without votes still returns 200, with:
  - empty `cases` and `breakdown`
  - a zero tally
  - the outcome taken from the text
- Individual (hoofdelijke) votes count in the tally, but they are not part of the breakdown yet. Grouping them per fraction is deferred.

**Tests:** `tests/Controller/DecisionControllerTest.php`
- The happy path asserts every field, including `votedOn` as the Amsterdam day.
- The breakdown uses the snapshot name and size, even after the `Fraction` row is renamed.
- Mistake and deleted votes are excluded.
- An individual-vote decision counts 1 per person.
- With no outcome text, the outcome falls back to the tally.
- A decision with no votes and no case returns 200 with an `unknown` outcome.
- Multiple cases come back ordered by number.
- Unknown and deleted decisions return 404.

**Done when:** the tests are green, and curl on a real id matches the Tweede Kamer website.

---

## Stage 5: Decisions feed [ ]

**Files**
- `src/Repository/DecisionRepository.php`: `countFeed(): int` and `findFeedRows(int $offset, int $limit)`.
  - Both share a private `feedQueryBuilder()` with three conditions:
    - the decision is not deleted
    - `EXISTS` a counted vote
    - `EXISTS` a pivot row whose case is not deleted
  - `findFeedRows`:
    - Left-joins `agendaPoint → activity` and orders by `act.date DESC, d.id DESC`. A null date ends up last; a test covers this.
    - Selects the scalars `d.id`, `d.text` and `act.date`, with `setFirstResult` and `setMaxResults`. No `Paginator` needed.
- Extract the Amsterdam-day logic from `Decision::getVotedOn()` into a static helper, `Decision::votedOnFromActivityDate(?\DateTimeImmutable)`, so the detail and the feed share it.
- `src/Repository/VoteRepository.php`: `findTallyRows(list<string> $ids)`.
  - One grouped query `GROUP BY decision, type, (personId IS NULL)` returning `SUM(COALESCE(fractionSize, 0))` and `COUNT(id)`.
  - Use native SQL if DQL rejects the grouping, and leave a comment saying why.
- `src/Service/DecisionService.php`: `getFeed(PageRequest): Page`.
  - Exactly **4 queries**: count, rows, tally, cases.
  - `caseTitle` is the title of the first case by number.
  - `excerpt` is `mb_strimwidth(trim(text), 0, 200, '…')`.
- `src/Dto/Response/DecisionFeedItem.php`: `id`, `caseTitle`, `excerpt`, `votedOn`, `outcome`, `tally`.
- `src/Controller/DecisionController.php`: add `GET /api/decisions` (`api_decision_feed`). It catches `InvalidPageRequest` and returns 400.

**Response shape**
```json
GET /api/decisions?page=2&limit=20
{"items":[{"id":"…","caseTitle":"Motie van het lid … over …","excerpt":"Verworpen.","votedOn":"2026-10-07",
           "outcome":"failed","tally":{"for":61,"against":89,"abstain":0}}],
 "page":2,"limit":20,"total":18}
```

**Tests:** `tests/Controller/DecisionFeedControllerTest.php`
- **Scope:** the feed excludes:
  - decisions without votes
  - decisions with only deleted or mistake votes
  - decisions with no case link
  - decisions whose only case is deleted
  - deleted decisions
- **Order:** newest first, a null date last, and a tie broken by id descending.
- **Pagination:**
  - `total` is correct
  - page 2 has the right items
  - a page past the end returns empty `items`
  - `limit=500` gives a response with `limit: 100`
  - `limit=0`, `page=abc` and `page=0` each return 400 JSON
- **Content:**
  - the tally and outcome match the detail endpoint
  - `caseTitle` comes from the lowest case number
- **No N+1:** the query count for a 5-item page is at most 4. Use the profiler's `db` collector if it's available in test. If it isn't, use a middleware counter, or drop the check and note why.

**Done when:** the tests are green, and curl `/api/decisions?limit=5` on synced data returns the newest votes, with outcomes matching their text.

---

## Verification (every stage)

Run these inside the container: `docker compose exec backend sh`. PHPUnit runs as **root**, because `user` can't drop or create the test DB.
```sh
php bin/console doctrine:schema:validate
php bin/console debug:router | grep api_
DATABASE_URL="mysql://root:rootsecret@db:3306/politiconnect?serverVersion=8.0&charset=utf8mb4" php bin/phpunit
vendor/bin/phpstan analyse
```
PHPUnit fails on any deprecation, notice or warning.

Manual checks after `app:sync:all`:
```sh
curl -s localhost:8080/api/fractions | jq
curl -s localhost:8080/api/fractions/<id> | jq
curl -si localhost:8080/api/fractions/00000000-0000-0000-0000-000000000000   # 404 JSON
curl -s 'localhost:8080/api/decisions?page=1&limit=5' | jq
curl -si 'localhost:8080/api/decisions?limit=abc'                            # 400 JSON
curl -s localhost:8080/api/decisions/<id> | jq
```

## Later (not in this roadmap)
- `GET /api/fractions/{id}/votes`: per-fraction vote history, built on `FractionDetail`.
- Grouping individual votes per fraction in the breakdown.
- Search (deferred for v1).
