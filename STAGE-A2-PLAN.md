# Stage A2 — Date-driven, scheduled sync: plan




## Step 2 — Progress markers

- [ ] `AgendaPoint::$decisionsSyncedAt` (nullable `DateTimeImmutable`).
- [ ] `Decision::$votesSyncedAt` (nullable `DateTimeImmutable`).
- [ ] `php bin/console make:migration`, review the diff, then migrate.
- [ ] `php bin/console doctrine:schema:validate`.
- [ ] Repository queries:
  - `AgendaPointRepository::findIdsToCheck(\DateTimeImmutable $now, int $recheckDays)` — only agenda points whose `Activity.Datum <= $now`, and `decisionsSyncedAt IS NULL`, or (`decisionsSyncedAt` older than 1 day AND `Activity.Datum` within `$recheckDays`).
  - `DecisionRepository::findIdsToCheck(...)` — same rule on `votesSyncedAt`.
- [ ] Repository tests: never checked, checked today, checked >1 day ago inside the window, checked >1 day ago outside the window.

## Step 3 — Rework the sync commands

- [ ] `app:sync:activity`: fetch by `since` (order `Id asc`, paging with `$skip`); always re-fetch activities inside the re-check window.
- [ ] Skip `Verwijderd` records on import in the activity, agenda point, decision and vote importers.
- [ ] `app:sync:agendapoint`: fetch by `since`; no longer driven by decision ids.
- [ ] `app:sync:decision`: take agenda point ids from `findIdsToCheck()`, fetch `Agendapunt_Id in (...)` in batches, then stamp `decisionsSyncedAt` on every agenda point in the batch, **including those with zero decisions**.
- [ ] `app:sync:case`: fetch only cases linked to synced decisions; keep `room: 'Tweede Kamer'`.
- [ ] `app:sync:vote`: take decision ids from `findIdsToCheck()`, process **one decision at a time**, stamp `votesSyncedAt` after its votes are saved (also when zero). `--max` is checked between decisions, so a decision is never cut off. On an API error: log, skip without stamping, continue; abort after 3 consecutive failures.
- [ ] Test that importing the same vote twice leaves one row.
- [ ] Keep every command granular; each still accepts `--max` for manual use.
- [ ] Update the command tests; add cases for "no votes still gets stamped" and "max reached between decisions".

Check: run each command twice; the second run must do (almost) nothing.

## Step 4 — Shared budget in `app:sync:all`

- [ ] Introduce a small budget object (e.g. `SyncBudget`, remaining rows) passed through the steps in order: fractions → activities → agenda points → decisions → cases → votes.
- [ ] The budget counts rows fetched from the API. When it runs out, stop cleanly; the next run continues from the markers.
- [ ] Log one line per step (`fetched`, `saved`, new/updated) plus a final total line.
- [ ] Manual `app:sync:all` ignores `caught_up_at` but still takes the lock and respects `--max`.
- [ ] Update `ROADMAP.md` notes on `app:sync:all` order.
- [ ] Test: a run with a budget of N saves at most N rows plus the tail of the current decision.

## Step 5 — Lock and sync state

- [ ] Install `symfony/lock`; use a database-backed store.
- [ ] `sync_state` table (one row): `caught_up_at` (nullable), `last_run_at`. Entity + migration.
- [ ] `SyncRunner` service:
  1. Take the lock; if held, log and exit.
  2. If `caught_up_at` is less than 1 hour ago, exit immediately.
  3. Run `app:sync:all` logic with the 500-row budget.
  4. If the budget was **not** exhausted, set `caught_up_at = now`; otherwise clear it.
- [ ] Make the one-hour gap a setting (`SYNC_IDLE_MINUTES=60`).
- [ ] Tests: lock held, caught-up skip, budget exhausted clears the state.

## Step 6 — Scheduler and worker container

- [ ] Install `symfony/scheduler` and `symfony/messenger` (if not present).
- [ ] Schedule provider: one recurring message every 10 minutes → handler calls `SyncRunner`.
- [ ] Messenger transport for the scheduler (`scheduler_default`).
- [ ] New `worker` service in `docker-compose.yml`: same image as `backend`, command `php bin/console messenger:consume scheduler_default --limit=20 --memory-limit=256M` (PHP `memory_limit=512M` as the hard ceiling) with `restart: unless-stopped` so each process stays short-lived.
- [ ] Log "caught up" and "nothing to do" lines so progress is visible.

Check: `docker compose up -d --build`, watch `docker compose logs -f worker`.

## Step 7 — Verify and finish

- [ ] Let the worker run until it reports caught up.
- [ ] Run `schema:validate`, `php bin/phpunit`, phpstan and cs-fix.
- [ ] Stage A coverage check (below). If most decisions with votes have a date, mark Stage A and A2 ✅ in `ROADMAP.md`.

```sql
SELECT decision_id, COUNT(v.id) AS votesPerDecision
FROM politiconnect.vote AS v
JOIN decision AS d ON d.id = v.decision_id
GROUP BY decision_id
ORDER BY decision_id;
```

```sql
SELECT COUNT(*) AS decisions_with_votes,
       SUM(has_date) AS with_vote_date,
       ROUND(100 * SUM(has_date) / COUNT(*), 1) AS pct_covered
FROM (
    SELECT d.id, MAX(a.date IS NOT NULL) AS has_date
    FROM decision d
    JOIN vote v ON v.decision_id = d.id
    LEFT JOIN agenda_point ap ON ap.id = d.agenda_point_id
    LEFT JOIN activity a ON a.id = ap.activity_id
    GROUP BY d.id
) t;
```

## Rules to keep

- `--max` stays 500 (unlimited runs leak memory on the laptop).
- Guid literals are unquoted in OData filters; strings are quoted.
- Sync all decisions, not only case-linked ones; case filtering belongs to the query layer.
- Sync all fractions (except `Verwijderd`).
- Keep the "Goverment" spelling.
- Repositories use `getReference()` for foreign keys, so sync order matters.

## Open points to confirm while building

- None left from the interview. The timezone boundary error is accepted (see Decisions), and `Fractie.Id` changes on a rename (handled in the roadmap, Stage 1.1).

# background info

Work through the steps in order. Each step ends with tests and a commit. Run commands from `Backend/` (inside the container, or on the host with the `DATABASE_URL` override on port 3308).

## Background

`app:sync:vote` takes every decision id ordered by `modifiedOn DESC`, fetches votes in batches of 50 and stops at `--max 500`. The order never changes, so every run starts with the same decisions and one decision can have ~150 votes. Result: 622 decisions, 269 votes, only 8 decisions with votes.

## Target design

```
fractions (full)
  → activities        Datum ge SYNC_SINCE, Kamer = Tweede Kamer
  → agenda points     Activiteit/Datum ge SYNC_SINCE
  → decisions         Agendapunt_Id in (unchecked agenda points)
  → cases             only those linked to synced decisions
  → votes             Besluit_Id in (unchecked decisions)
```

- `SYNC_SINCE` and `SYNC_RECHECK_DAYS` live in `.env`; every command reads them.
- One shared 500-row budget (rows fetched from the API) per scheduled run, checked **between decisions**.
- Checked markers: `AgendaPoint.decisionsSyncedAt`, `Decision.votesSyncedAt`.
- Re-check a checked row once a day while its activity is within `SYNC_RECHECK_DAYS`; never after.
- Scheduler runs every 10 minutes. Lock stops overlap. `sync_state` stores "caught up at".

## Decisions (from the plan interview)

- **Cutoff:** `SYNC_SINCE=2026-01-01`. Backfill by lowering the date or using `--since`; per-row markers mean only new rows get picked up.
- **API failure:** log it, skip the decision without stamping it, continue. Abort the run after 3 consecutive failures. A partial vote set is never stamped.
- **Idempotent import:** re-importing is harmless; `VoteRepository::saveDtoAsEntity` already upserts by vote id. Add a test that importing the same vote twice leaves one row.
- **Timezone:** accept the small error at the cutoff boundary. Store dates as returned, no conversion.
- **Cases:** no separate case re-check; cases refresh when their decision is re-checked.
- **Existing data:** keep it (the user may reset the DB anyway). All markers start NULL, so old rows are re-checked once.
- **Manual runs:** `app:sync:all` ignores `caught_up_at`, but still takes the lock and stops at its own `--max`.
- **Future activities:** `findIdsToCheck` only returns agenda points whose activity date is `<= now`. Planning ahead may come in a later version.
- **Activity re-fetch:** always re-fetch activities with `Datum ge now - SYNC_RECHECK_DAYS`, plus anything newer than the last stored one. The upsert handles unchanged rows.
- **Budget:** 500 counts rows fetched from the API (saved or not). Pages are 250 rows; votes run one decision at a time, so a run can slightly overshoot 500 but never cuts a decision off.
- **Logging:** one line per step (`activities: fetched 250, saved 31 (new 12, updated 19)`) plus a final total line.
- **Deleted records:** skip `Verwijderd` records on import; leave stored rows alone. No delete propagation.
- **Worker:** `--limit=20 --memory-limit=256M`; PHP `memory_limit=512M` as the hard ceiling; `restart: unless-stopped`.
- **Successor parties:** `Fractie.Id` changes on a rename or merger (verified on the live API). Out of scope here; tracked in `ROADMAP.md` under Stage 1.1.


# done

## Step 1 — Config and date filters

- [ ] Add `SYNC_SINCE=2026-01-01` and `SYNC_RECHECK_DAYS=14` to `Backend/.env` (and `.env.test`).
- [ ] Bind both as parameters in `config/services.yaml`; inject into the sync commands.
- [ ] `ActivityFilter`: add a `since` argument → condition `Datum ge <date>T00:00:00Z`.
- [ ] `DecisionFilter`: add `agendaItemIds` (list, unquoted GUIDs) → `Agendapunt_Id in (...)`. Keep the single-id option.
- [ ] Use the field enums, never hard-coded property strings.
- [ ] Unit tests for the three filters (conditions, empty lists, GUIDs unquoted).
- [ ] Add a `--since` option to the commands that overrides the `.env` value.

Check: `php bin/phpunit tests/Unit`.