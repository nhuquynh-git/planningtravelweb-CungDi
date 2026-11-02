# Local Quickstart and Validation Guide

**Status**: Safe local setup and baseline smoke checks for the current
repository, followed by acceptance scenarios for future approved feature work.
Business API scenarios are not runnable yet: the inspected API currently
implements only `GET /api/health`.

## Prerequisites

- Git checkout of this repository.
- Docker Desktop with Docker Compose.
- Node.js/npm compatible with the versions in `frontend/package-lock.json`.
- Composer 2 and PHP compatible with `backend/composer.json` only if running
  backend tests on the host.

Do not run schema migrations as part of this guide. The current
`database/init.sql` only creates the `test_connection` connectivity table on a
new MySQL volume; it is not the business schema.

## Configure local environment

Run from the repository root in PowerShell:

```powershell
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
if (-not (Test-Path backend\.env)) { Copy-Item backend\.env.example backend\.env }
if (-not (Test-Path frontend\.env)) { Copy-Item frontend\.env.example frontend\.env }
```

Review the local values and use matching MySQL credentials in `.env` and
`backend\.env`. Keep local secrets out of Git; do not overwrite an existing
environment file. `frontend\.env.example` points the SPA to
`http://localhost:8001/api`.

Install the existing dependencies:

```powershell
Push-Location backend
composer install
Pop-Location

Push-Location frontend
npm ci
Pop-Location
```

## Start the current local stack

From the repository root:

```powershell
docker compose up --build -d
docker compose ps
```

Wait for the `db` health check and PHP service. The current Compose mapping is
API port `8001` and MySQL host port `3308`. Do not use `docker compose down -v`:
removing the named volume destroys local database data.

Start the SPA in another PowerShell terminal:

```powershell
Push-Location frontend
npm run dev
```

Open the Vite URL printed in the terminal (normally `http://localhost:5173`).
Stop this foreground process with `Ctrl+C`; stop the Compose services, without
deleting the database volume, with `docker compose down`.

## Baseline checks available now

### API and database connectivity

```powershell
$response = Invoke-RestMethod http://localhost:8001/api/health
$response.status
$response.data.backend
$response.data.database
```

Expected output:

```text
success
connected
connected
```

If the database is unavailable, the health endpoint should return HTTP `503`
with the existing JSON error envelope; it must not report a successful
connection.

### Frontend static checks

```powershell
Push-Location frontend
npm run lint
npm run build
Pop-Location
```

Expected result: Oxlint exits successfully and Vite creates the production
bundle in `frontend/dist/`.

### Backend tests

```powershell
Push-Location backend
composer test
Pop-Location
```

Expected result: the existing PHPUnit suite completes successfully. No
business-flow coverage should be inferred from this baseline suite.

## Feature acceptance scenarios for implementation

These are validation targets from the approved product requirements and
conditional design. They are **not currently executable**; implement and test
them only after the relevant DEFERRED decisions are resolved. See the
[API contract](./contracts/api-contract.md) and
[conditional data model](./data-model.md) for proposed shapes.

| Scenario | Expected validation evidence |
|---|---|
| Guest access boundary | Guest can read only Admin-approved public minigame content; pending, hidden or deleted posts and private Plans reveal no private data. |
| Plan ownership and lifecycle | User-created Plans are owned by that User; Admin-created Plans are owned by that Admin. Create, list, read, update and explicitly confirm deletion; only owner/member can access private Plan data, and Admin actions are auditable. |
| Membership integrity | Directly add a registered User; reject duplicate membership; block removal while expense allocations reference that member. |
| Itinerary | Persist Plan activities grouped by date; default ordering is chronological; manual ordering is honored only when explicitly selected. |
| Expense integrity | Create multiple individual expenses at one Plan location; enforce one payer who is a current member of a group Plan, positive integer VND, allocation sum equal to expense total and deterministic equal-share remainder. |
| Expense edge cases | Warn about a possible duplicate without silently discarding it; reject invalid totals; block referenced member/location deletion; require explicit Plan deletion confirmation. |
| Expense summary and print | Independently recompute Plan/member/location/category totals from source expenses; ensure print content is authorized and matches the displayed data. |
| Minigame community | Create a post with required title and rules/instructions; other specified post fields may be omitted. New posts remain pending and unavailable to Guests until Admin approval; enforce author-only edit/delete; filter by confirmed genres/player count/duration; like/favorite are unique per User/post. |
| Admin moderation | Admin can approve pending posts, review reports and keep, hide or delete violating posts; pending/hidden content is absent from public results; non-Admin cannot invoke moderation actions. |
| Email events | Verify recovery, member-added and Plan-deleted messages reach only confirmed recipients; test provider failure/retry behavior from FR-014. |
| Responsive/accessibility | Exercise agreed viewports (375, 768 and 1280 px), keyboard flows, labels, focus, validation, empty/loading/error states and non-color-only status. |
| Requirement traceability | Map each implemented FR/AC to a test and preserve synthetic-data evidence for coursework reporting. |

No AWS deployment, migration, seed, destructive database cleanup or real
payment operation is part of this quickstart.
