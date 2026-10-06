# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**PolitiConnect** lets ordinary citizens follow Dutch national politics. It syncs data from the Tweede Kamer OData API (`https://gegevensmagazijn.tweedekamer.nl/OData/v5/`) into MySQL and exposes it through a JSON API. The main question it answers is "how did my party vote, and on what". Search is out of scope for v1.

- `Backend/`: Symfony 8.1 on PHP 8.4, Doctrine ORM 3, PHPUnit 13. All application code lives here.
- `Frontend/`: currently empty.
- `ROADMAP.md`: the current stage-by-stage plan with status checkboxes. Read it first and update it as stages progress.
- `Project-handover.md`: notes from an earlier session, including design decisions and known gaps. Some status rows in it are stale (for example, the fraction sync and `app:sync:all` now exist), so check the code before trusting it.

## Commands

The stack runs in Docker: `backend` (php-fpm), `nginx` on `localhost:8080`, and `db` (MySQL 8.0, exposed on host port **3308**). Run these from the repo root:

```sh
docker compose up -d --build
docker compose exec backend php bin/console <command>
```

On startup the backend entrypoint waits for the DB and runs `doctrine:migrations:migrate`.

Run these from `Backend/`, either inside the container or on the host when the `DATABASE_URL` override points at `127.0.0.1:3308`:

```sh
php bin/console app:sync:all                  # fraction → case → decision → vote
php bin/console app:sync:decision --top=250 --max=500
php bin/console app:sync:vote --decision-limit=100
php bin/console make:migration                # review the generated diff before applying
php bin/console doctrine:migrations:migrate
php bin/console debug:router

php bin/phpunit                                          # all tests
php bin/phpunit tests/Controller/FractionControllerTest.php
php bin/phpunit --filter testIndex
```

Most sync commands accept `--top` (page size) and `--max` (cap on records per run).

**Test bootstrap:** `tests/bootstrap.php` drops and recreates an empty test DB (`politiconnect_test`; Doctrine adds the `_test` suffix through `when@test`) before every run. No data is copied in, so each test seeds the entities it needs through the EntityManager.

Note that `Backend/.env` still contains the default Postgres `DATABASE_URL`. The real MySQL URL comes from `docker-compose.yml` and `.env.test`.

## Architecture: the sync pipeline

Every entity goes through the same pipeline. To add an entity, copy the existing set of classes:

```
Command (src/Command/Sync*Command.php, app:sync:<entity>)
  → builds a Filter (src/Api/Odata/Filter/*Filter.php: endpoint, $filter/$orderby/$top)
  → OdataInterface::search(filter, DtoClass)   (ApiProvider: lazy Generator, paginates
                                                with $skip while withSkip() is true and full pages come back)
  → Dto::fromArray()                           (src/Dto: raw API array → typed readonly DTO)
  → ImporterService::importerForDto(DtoClass)  (picks the Importer tagged app.importer.tag whose supports() matches)
  → Importer::import() → Repository::saveDtoAsEntity()   (DTO → entity, persist, flush + clear)
```

- **Field enums** (`src/Enum/*Field.php`) map PHP names to Dutch OData property names. The `Endpoint` case holds the OData entity set name (`Besluit`, `Stemming`, `Fractie`, `Zaak`). Filters and DTOs always go through these enums and never hard-code property strings.
- Implementations of `ImporterInterface` are auto-tagged through `#[AutoconfigureTag]` on the interface, so adding a new importer class is enough to register it.
- The HTTP clients are scoped clients (`goverment_odata_api.client`, `goverment_syncfeed_api.client`) defined in `config/packages/framework.yaml` and wired up in `config/services.yaml`. `XmlProvider` (SyncFeed) exists but no command uses it yet.

Entity mapping (Dutch name → class): Besluit → `Decision`, Stemming → `Vote`, Fractie → `Fraction`, Zaak → `GovermentCase`. The many-to-many link between Zaak and Besluit uses the `GovermentCaseDecision` pivot entity (composite key). "Goverment" is a consistent typo across the codebase. Keep it so names stay consistent.

## Rules and decisions (don't re-litigate)

- **OData literals:** quote `Edm.String` values (`Afkorting eq 'VVD'`, escaping `'` as `''`), but leave `Edm.Guid` values unquoted (`Besluit_Id eq b37b…`). The live API rejects `GuidField eq 'guid'` with a type error. `in (...)` accepts both forms. This was verified against the live API on 2026-10-05 and contradicts the handover's Decision #6.
- **Sync all decisions**, including ones that aren't linked to a Zaak (procedural votes). Filtering on `caseId IS NOT NULL` belongs in the query/display layer, not in the sync.
- **Sync all fractions**, including dissolved ones. Old votes reference them, so the only exclusion is the `Verwijderd` (deleted) flag.
- `Vote.fractionSize` and `Vote.fractionName` are intentional **point-in-time snapshots** taken at vote time. They are not duplicates of the `Fraction` relation.
- Repositories resolve foreign keys during import with `getReference()`, not `find()`. As a result, a missing parent row only surfaces at flush time as a DB foreign-key exception. Sync order therefore matters: fraction/case/decision must run before vote.
- Keep the granular sync commands separate. `app:sync:all` only runs them in dependency order.
- Sync scope assumes the Tweede Kamer only (`room: 'Tweede Kamer'` on the case filter).
