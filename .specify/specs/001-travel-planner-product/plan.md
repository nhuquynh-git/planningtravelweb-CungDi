# Implementation Plan: Cùng Đi – Travel Planner

**Branch**: `main` | **Date**: 2026-11-02 | **Spec**: [spec.md](./spec.md)

**Plan status**: Conditional design proposal. The plan itself does not approve
new business rules, change the existing ERD/Figma, or commit the AWS topology.
The locally applied schema migrations were separately authorized by the
Product Owner and are documented in [data-model.md](./data-model.md).

## Summary

Deliver the confirmed travel-planning, private Plan membership, per-expense
group-cost tracking, informational minigame community, Admin management and
IS207 requirements as one React SPA and one CodeIgniter 4 REST API backed by
MySQL. Keep the existing browser-to-API separation and `/api` prefix. Use
CodeIgniter Shield API Access Tokens and the existing JSON response envelope.
Do not introduce microservices or a new framework.

The current ERD and proposed UI are reviewed in
[design-review.md](./design-review.md); a conditional, conceptual data design
is in [data-model.md](./data-model.md), API proposal in
[contracts/api-contract.md](./contracts/api-contract.md), and validation guide
in [quickstart.md](./quickstart.md). The P1 decisions Q-016, Q-017a, Q-018a,
Q-018b and Q-024, and token policy Q-015b are resolved in the Spec. Remaining
DEFERRED decisions stay gated at their relevant implementation steps.

## Technical Context

**Language/Version**: Frontend JavaScript (React 19.3.1, Vite 8.3.5 observed);
backend PHP `^8.2` (Composer), CodeIgniter 4.7, Shield 1.4.
**Primary Dependencies**: Existing React/Vite and CodeIgniter/Shield packages;
no additional framework or package is proposed.
**Storage**: MySQL 8.4 in the existing Docker Compose development environment.
Shield and all confirmed business tables, including permanent Admin audit
events, are implemented locally through CodeIgniter migrations.
**Testing**: Existing CodeIgniter PHPUnit setup (`composer test`), frontend
Oxlint (`npm run lint`) and Vite production build (`npm run build`). Add
feature-focused PHPUnit tests using existing test tooling; do not assume a
frontend test runner is installed.
**Target Platform**: Browser SPA and PHP web application; local Docker Compose;
AWS deployment target, service topology undecided.
**Project Type**: Separate frontend and backend web applications.
**Performance Goals**: Use the measurable demo goals in the spec as acceptance
targets, not as measured repository performance.
**Constraints**: REST/JSON under `/api`; no direct browser-to-MySQL connection;
Shield Access Token; MySQL relational integrity; no real-money settlement;
VND only; preserve approved privacy and role rules; do not implement unresolved
branches before their decision gates.
**Scale/Scope**: IS207 project scope. No production user/throughput estimate is
provided; do not size AWS resources or promise availability without a later
deployment decision.

## Architecture Overview

```text
Guest / User / Admin browser
        |
        | React 19 + Vite SPA (JavaScript; built static assets)
        | HTTPS JSON requests; HttpOnly-cookie token (Shield bridge to verify)
        v
REST API: /api
CodeIgniter 4.7 (PHP 8.2+)
  Routes / Filters -> API Controllers -> Application Services
                                  -> CodeIgniter Models / Entities
                                  -> Validation / DB transactions
        |                           |
        | destination lookup*       | MySQL connection via protected env
        v                           v
External place provider*       MySQL 8.4

* Provider/API and AWS services are not selected.
```

The browser owns presentation, navigation, local form feedback and API
consumption. The backend is authoritative for input validation, authentication,
ownership/member checks, expense calculations, moderation permissions and
database integrity. The browser never connects directly to MySQL. Keep the
frontend and API separate; do not split the backend into services.

### Frontend structure

Current code is a small health-check screen with a shared `fetch` helper under
`frontend/src/services/`. The following is a proposed growth path, not a claim
that these modules already exist:

```text
frontend/src/
├── app/                 # router, application shell, auth state
├── components/          # shared forms, tables, dialogs, status feedback
├── features/
│   ├── auth/
│   ├── plans/
│   ├── expenses/
│   ├── minigames/
│   └── admin/
├── pages/               # route-level screens
├── services/            # existing API helper; resource API clients
└── styles/              # existing CSS plus approved design tokens
```

Use the existing API helper and Fetch API unless a concrete requirement
justifies changing it. Keep tokens out of URLs and logs. The approved policy is
a Shield Access Token that expires after 7 days of inactivity, is stored in an
HttpOnly cookie, and is revoked server-side on current-token logout; no refresh
flow is approved. JavaScript must not read the token. Cookie controls are
Secure in production and SameSite=Lax; credentialed CORS is restricted to the
configured frontend origin, and state-changing requests require a CSRF token
plus Origin/Referer validation. Verify cookie renewal and the cookie-to-Shield
bridge before enabling protected browser requests. Map loading, empty, validation,
permission, confirmation and network-error states to approved user journeys.
The Figma `Page 1` layer inventory is now readable in the integrated browser;
preserve the major layout, but verify individual frames at readable scale
before detailed implementation. Its visible invite-code, Premium, location
review and Plan-save/social labels are not automatically approved scope.

### Backend structure

Current CodeIgniter structure has `app/Controllers/Api/Health`,
`BaseApiController`, Shield configuration and an otherwise empty application
Models directory. Proposed additions follow those existing locations:

```text
backend/app/
├── Controllers/Api/       # thin HTTP/resource controllers
├── Entities/              # typed business records where useful
├── Models/                 # persistence and query constraints
├── Services/               # use cases, authorization coordination,
│                           # transactional expense/minigame operations
├── Validation/             # reusable request rules when justified
├── Database/Migrations/    # applied staged schema and future reviewed changes
└── Database/Seeds/         # synthetic test/demo data only
backend/tests/
├── unit/
├── feature/
└── database/               # only where existing test setup supports it
```

Controllers parse HTTP input, invoke validation/use cases, and produce the
shared JSON envelope. Services coordinate multi-model operations and
transactions; they must not become a generic framework or duplicate data
access. Models own persistence queries and database-facing constraints.
Authorization should be explicit in route filters and resource-level policy
checks; a route-level Admin/User group alone does not establish Plan ownership.
Views are not needed for API operations; use them only if a later approved
server-rendered page requires them.

### MySQL and API boundaries

The API uses the existing `/api` prefix and JSON request/response bodies.
Resource names, HTTP methods, status mapping and conditional operations are
proposed in [contracts/api-contract.md](./contracts/api-contract.md); they are
not current endpoints. Keep success/error payloads compatible with
`BaseApiController`; do not expose SQL, stack traces, secrets, or private
resource contents.

MySQL stores the approved account references, Plans, membership, destinations,
activities, expenses/allocations, and minigame posts/interactions/reports.
Admin audit events are stored permanently in MySQL; Admins and system operators
may read them, with operator access restricted outside the business-role model.
Shield remains authoritative for credentials, account identity, authentication
attempts/tokens and authorization group assignments; business tables reference
Shield `users.id` and never copy credentials, tokens or identity secrets.
Business references restrict account deletion until a deletion workflow is
approved. Store derived totals as query results, not duplicated mutable
columns, unless later measured evidence justifies a documented exception. The
conditional conceptual ERD and 3NF review are in `data-model.md`.

## Authentication, Authorization, Validation and Security

### Shield ownership boundary and current repository state

The locked dependency is CodeIgniter Shield **v1.4.1** (`backend/composer.lock`,
commit `93c5f1f...`). Shield's migration/configuration defines these
authentication-owned tables (default names are currently configured in
`backend/app/Config/Auth.php`):

| Shield table | Purpose and relationship |
|---|---|
| `users` | Canonical account principal and integer unsigned `id`; includes account status/activity fields, not the ERD's application password hash or subscription tier. |
| `auth_identities` | One-to-many with `users`; stores credential/authentication identities including password and access-token identity material. Business tables must not reference individual identities. |
| `auth_logins` | Login-attempt history; preserves identifiers for security history rather than acting as an application profile or business audit table. |
| `auth_token_logins` | Token-login-attempt history, separate from domain activity/audit records. |
| `auth_remember_tokens` | Remember-me records; separate from API resource ownership. |
| `auth_groups_users` | User-to-group assignments (`user_id`, group name); Shield migration adds FK to `users`. |
| `auth_permissions_users` | User-specific permission assignments (`user_id`, permission name); Shield migration adds FK to `users`. |

Shield's configured group names, permission catalog and group-to-permission
matrix live in `AuthGroups` configuration; the current migration persists
user/group and user/permission assignments, not separate group/permission
master tables. Do not design application-owned copies of these Shield
authentication tables. Shield migration also cascades its identity,
remember-token and authorization-assignment rows from a deleted user, while
login-attempt history intentionally remains; this is Shield-owned behavior and
does **not** determine how future business data should be deleted.

The existing ERD `USER` is not a compatible replacement for Shield `users`: it
uses string IDs and combines name/email/password hash/avatar with premium and
subscription state. Proposed change: treat Shield `users` as the sole account
principal; map all approved business `*_user_id` foreign keys to the same
unsigned integer type as `users.id`. If approved product requirements need
profile-only fields not supplied by Shield (for example display name/avatar),
consider a separate one-to-one profile extension keyed by `user_id` with an FK
to `users.id`; do not duplicate email/password or add that table/fields until
the required profile fields are confirmed. Remove premium/subscription
concepts from the proposed MVP model unless separately approved. This is an ERD
proposal, not an applied change.

Current configuration evidence:

- `Auth.php` registers Shield's `tokens` authenticator, but its
  `defaultAuthenticator` remains `session`.
- `AuthToken.php` currently uses the `Authorization` header and sets an
  unused-token lifetime of one year. Q-015b now approves expiry after 7 days of
  inactivity, server-side revocation of the current token on logout, and
  HttpOnly-cookie storage. Verify sliding cookie renewal and implement a secure
  cookie-to-authenticator bridge; do not assume Shield natively reads cookies.
- `AuthGroups.php` sets new users' default group to `user`, defines `user` and
  `admin`, gives `user` no permissions and currently gives `admin` wildcard
  `*`. Guest is not a persisted Shield group. `Auth.php` has no registration or
  login action configured, so email verification must not be inferred; Q-015a
  remains deferred. Admin-group provisioning also needs an explicit,
  least-privilege operational procedure before deployment.
- `Routes.php` currently has only `/api/health` and CORS; no business route is
  protected by Shield yet. `Filters.php` has no explicit Shield filter alias.
  Registration of the token filter and applying it to every protected API
  route are implementation work, not completed behavior.

### Groups, permissions and resource authorization

| Actor | Proposed authorization boundary based on confirmed scope |
|---|---|
| Guest | Anonymous; no Shield user row or group assignment. Public landing and published minigame reads only, plus approved registration/login/recovery flows. Cannot read private Plans or write protected content. |
| User | Authenticated Shield user in the `user` group by default. Owns/manages own Plans; owner may add registered Users directly. Plan members can read that Plan and expenses but cannot mutate expenses. Users manage their own minigame posts and confirmed community interactions. |
| Admin | Authenticated Shield user assigned `admin`. CRUD across all Plans/expenses; review reports and keep, hide or delete violating minigame posts. No inferred account-management powers. |

Use Shield groups for the coarse, confirmed role distinction, not as the only
resource authorization check. The current `admin => ['*']` and empty User
permission matrix are configuration facts, not an approved final policy.
Before protected routes are implemented, replace the broad wildcard with only
the explicit Admin capabilities required by the approved product scope, or
document why a particular capability is safely enforced by a resource policy.
Candidate permission keys are `admin.plans.manage`,
`admin.expenses.manage`, and `admin.minigames.moderate`; these names are
implementation proposals, not new business permissions or roles. Ordinary
User access to a Plan remains a dynamic owner/member resource check, not a
static group permission.
Use server-side resource checks for ownership, Plan membership, author
ownership, and Admin-only actions; never infer that every User can mutate every
resource or that Admin manages accounts. Guest access is anonymous/public-route
policy, not a `guest` group or stored role. Do not add further roles.

Use Shield's configured Access Token authenticator for protected API requests.
Route configuration must select and apply the token authenticator explicitly;
do not rely on Shield's session default. The current authenticator expects an
Authorization header, while browser storage is approved as an HttpOnly cookie;
use a verified secure bridge between them. Enforce the approved 7-day
inactivity timeout and current-token server-side logout revocation. Use
production Secure and SameSite=Lax cookies, credentialed CORS restricted to
the configured frontend origin, and CSRF token plus Origin/Referer validation
for state-changing requests. Do not invent refresh behavior or custom token
scopes. Test each allowed/denied actor-resource pair.
When a User or Admin creates a Plan, that creator is its owner. Admin CRUD
actions are still attributed separately in the Admin audit event.

Validate request data on the server and provide matching client feedback.
Enforce date ranges, positive whole-VND amounts, allocation sums, membership
scope, duplicate-label behavior and Plan deletion confirmation from the spec.
Potentially duplicate expense submissions must warn and require confirmation,
not silently reject or double-submit on a network retry. The previously open
P1 decisions Q-016, Q-017a, Q-018a, Q-018b and Q-024 are resolved and reflected
in the design below. Keep remaining DEFERRED decisions in the Spec gated until
their stated design or implementation stage. Use parameterized query-builder/model operations, transactions for
multi-row writes, safe operational logs, environment-provided secrets, HTTPS
for deployment and restrictive CORS for the approved frontend origin.

## Testing Strategy

- **Unit**: service rules for expense totals/equal-share remainder, plan date
  checks, duplicate-warning detection, role policy and minigame filtering.
- **API/feature**: request validation, response envelope/status, Shield
  authentication, owner/member/Admin permissions, CRUD, private-resource
  non-disclosure and audit write/no-write behavior.
- **Database/integration**: foreign keys/unique constraints, transactional
  expense plus allocation updates, total equals allocations, Plan cascade
  deletion after explicit confirmation, stale/missing resource behavior.
- **Frontend**: run current Oxlint/build; when approved screens are implemented,
  verify keyboard access, viewport sizes from spec, loading/empty/error states,
  form validation, confirm dialogs and only authorized content in print.
- **End-to-end/coursework evidence**: manually or with existing test tooling
  verify a confirmed User workflow through browser → `/api` → MySQL, with
  synthetic data. Do not claim test coverage or browser automation until
  executed and recorded.
- **External failures**: destination provider unavailable/manual entry,
  email retry exhaustion and network/storage failure must produce the
  specified user-visible result without success-shaped fallbacks.

The detailed runnable validation matrix is in [quickstart.md](./quickstart.md).

## AWS Deployment Plan (Options, Not a Selection)

AWS is the confirmed target; the following is a deployment outline, not an
approved topology:

1. Build the React/Vite SPA to static assets and host via an AWS static web
   hosting/CDN option.
2. Deploy the CodeIgniter API as one PHP application, with the `/api` origin
   reachable only over HTTPS and CORS limited to the frontend origin.
3. Use managed MySQL 8.4 if the selected AWS service/version and coursework
   constraints support it; restrict network access to the API tier.
4. Inject credentials and Shield encryption keys from protected runtime
   configuration, not repository files. Separate dev/staging/prod settings.
5. Before deployment, decide domain/TLS, region, networking, backup/restore,
   health monitoring, logging/retention, scaling, and rollback.
6. Deploy schema changes only through reviewed CodeIgniter migrations in the
   later implementation/deployment workflow; this plan does not run migrations.

Candidate combinations (pending approval): static frontend hosting via Amplify
Hosting or S3 + CloudFront; PHP API via Elastic Beanstalk or ECS; managed MySQL
via RDS. A small-coursework topology should avoid unnecessary orchestration, but
final selection requires cost, hosting constraints and the team's AWS access.
No option is selected in this plan.

## IS207 Coursework Traceability

| Coursework requirement | Planned system/design component and evidence |
|---|---|
| PHP, CodeIgniter MVC, client-server | `backend/app/Controllers/Api` + Services + Models; React SPA calls `/api`; architecture diagram and integration checks. |
| MySQL and database design | MySQL 8.4; conceptual model and later ERD/data dictionary; reviewed migrations and integrity tests after approval. |
| JavaScript and AJAX | React JavaScript uses Fetch API for asynchronous JSON calls; Fetch is accepted by the user clarification. |
| Create/read/update/delete and display | Plan, activity, expense and minigame resource flows; Admin Plan/expense management; authorized list/detail screens and API tests. |
| Search | Plan list/search within authorized data; minigame search/filter by confirmed criteria, category catalog and optional free tags. |
| Statistics | Plan totals, paid/share difference, member × location aggregate and expense-type totals; compare to independent fixture calculations. |
| Printing | Authorized Plan summary with trip details, day-grouped itinerary and expense totals; print acceptance check. |
| Email | Password recovery, member-added and Plan-deleted events with confirmed recipients/content and retry limit; provider/config/test evidence. |
| ERD and 3NF | Preserve and review `docs/database/ERD.png`; document discrepancies and a conditional normalized proposal in `data-model.md`; approve before implementation. |
| UML | Produce role/use-case and sequence/activity diagrams only for approved flows; trace each to FR and AC. No UML is created by this Plan. |
| UI/UX and Figma | Audit `Page 1` layer inventory and map User Stories to named screens; retain major layout; verify full-size frames and resolve design labels outside approved scope before detailed visual implementation. |
| Testing | PHPUnit feature/unit tests, frontend lint/build, contract/permission/integration scenarios and recorded coursework evidence. |
| Deployment | AWS candidate topology, environment/secret/health/recovery plan; actual target and deploy evidence are later decisions. |
| Technical report | Trace requirement → design → implementation → tests → deployment, record status truthfully, include research and artifact citations. |

## Decision Gates and Risks

| Resolved decision | Approved rule | Design consequence |
|---|---|---|
| Q-016 | Every expense belongs to a Plan; personal expenses use a private Plan that may contain only its owner. | Require Plan scope for expense records; do not design standalone expenses. |
| Q-017a | A group-Plan payer must be a current Plan member. | Validate payer membership against the Plan on every expense write. |
| Q-017b | Expense date is required; note and Plan location are optional. | Require the date and keep note/location nullable in the schema and API. |
| Q-018a | Minigame title and rules/instructions are required; the other listed fields may be omitted. | Apply only these required-field checks; keep optional fields nullable/optional. |
| Q-018b | A new minigame post waits for Admin approval before Guest publication. | Model pending approval; exclude pending posts from public queries and provide an Admin approval queue. |
| Q-018c | Author edits to an approved Minigame post are published immediately without re-approval. | Do not add a re-approval state for edits. |
| Q-019 | Authors may edit/delete their comments; reports require a reason and are unique per User/post; Admin may keep, hide or soft-delete a reported post. | Preserve report references through soft deletion and constrain report lifecycle to the approved outcomes. |
| Q-023 | Audit is stored permanently in MySQL and readable by Admins and system operators. | Keep operator access outside the business-role model; never create audit events for read-only access. |
| Q-024 | A Plan creator owns the Plan, including an Admin who creates one. | Set owner from the authenticated creator; keep the Admin audit actor in the separate audit event, not a duplicate Plan creator column. |

Remaining DEFERRED decisions must be resolved at the stages listed in Spec §12.
Q-015a (email verification), Q-020 (suggestion ranking/empty state), Q-021
(destination provider selection), and Q-022 (genre seed values) remain open.
They do not block the business tables already applied.

Other risks: current ERD includes unapproved public sharing/subscriptions/booking
and social interactions on Plans; its cost shape cannot enforce the confirmed
per-expense model. Figma layer names/canvas overview are readable, but
individual frames have not been fully inspected and several labels suggest
unapproved scope. AWS cost, availability and recovery cannot be estimated
without topology and region.
Shield's configured one-year unused-token lifetime does not match the approved
7-day inactivity timeout and must be configured and tested. HttpOnly-cookie
transport needs a verified bridge to Shield's Authorization-header
authenticator; also verify cookie renewal and CSRF controls. The API currently
exposes only health check, so all product contracts remain proposed except the
approved Authentication contract.

## Constitution Check

| Principle | Check before design | Post-design |
|---|---|---|
| I. Agreed stack | PASS — React 19/Vite 8/JavaScript, PHP/CI4, MySQL 8.4, Docker Compose, AWS target retained. | PASS — no replacement or added framework selected. |
| II. Client-server | PASS — separate React client and REST API under `/api`. | PASS — no direct browser-to-MySQL access. |
| III. MVC | PASS — CodeIgniter MVC with thin controllers and focused services/models. | PASS — no microservices or unnecessary repository abstraction. |
| IV. REST/JSON | PASS — proposed resource API preserves existing response envelope. | CONDITIONAL — endpoint/schema details are proposal pending approval and Q gates. |
| V. Data integrity/migrations | PASS — design targets 3NF, constraints and reproducible migrations. | CONDITIONAL — Shield and confirmed business tables, including Admin audit events, are applied locally through migrations; integrity behavior still needs feature tests. |
| VI. Authentication/authorization | PASS — Shield is the identity/auth foundation; only Guest/User/Admin are established. | CONDITIONAL — business references target Shield `users.id`; token filter wiring, cookie-to-Shield transport, cookie renewal, CSRF controls, and least-privilege permissions remain implementation/security gates; the seven-day inactivity/HttpOnly/current-token-revocation policy and CSRF/CORS approach are resolved; Plan ownership follows the resolved creator-is-owner rule. |
| VII. Security/privacy | PASS — server-side validation, access checks, safe errors/logging and protected secrets. | PASS — private Plan access and no real-money settlement preserved. |
| VIII. UI/UX | PASS — UI tied to approved requirements, accessible states and responsive checks. | CONDITIONAL — Figma Page 1 inventory/canvas overview inspected, but individual full-size frames/interactions remain unverified; reconcile labels outside approved scope before implementation. |
| IX. Validation/errors | PASS — explicit validation, status/error envelope and failure tests proposed. | PASS — duplicate expense warning is not converted into hard uniqueness. |
| X. Tests/DoD | PASS — proportionate PHPUnit/lint/build/acceptance evidence. | CONDITIONAL — no product feature is claimed implemented or tested. |
| XI. Git/teamwork | PASS — scoped documentation proposal; no source/database changes. | PASS — this plan does not create tasks or commits. |
| XII. Academic compliance | PASS — all supplied IS207 capabilities are mapped above. | CONDITIONAL — survey/UML/ERD/Figma/deploy artifacts remain future deliverables. |
| XIII. Operations | PASS — AWS is target; services/topology remain alternatives. | CONDITIONAL — no AWS topology selected. |
| XIV. Spec-driven change | PASS — unresolved choices remain gates; proposals are labeled. | PASS — no new business decision is presented as approved. |

**Overall gate**: Architecture and plan artifacts pass Constitution review.
The resolved P1 and schema questions are reflected in the applied tables.
The remaining deferred choices gate only their related provider, suggestion,
seed, or email-verification work. Product API behavior is not implemented
merely because its schema exists. The original ERD remains unchanged pending
its separate team review.

## Project Structure

### Documentation (this feature)

```text
.specify/specs/001-travel-planner-product/
├── plan.md                  # This conditional implementation plan
├── research.md              # Stack research, options, unresolved decisions
├── data-model.md            # Existing ERD audit and conditional 3NF proposal
├── design-review.md         # ERD/UI/Figma evidence and screen mapping
├── contracts/
│   └── api-contract.md      # Proposed JSON/REST resource contract
├── quickstart.md            # Validation scenarios and local commands
└── tasks.md                 # Not created; remains /speckit-tasks output
```

### Source Code (repository root)

```text
backend/
├── app/
│   ├── Controllers/Api/     # Existing BaseApiController, Health; add thin resources
│   ├── Entities/            # Proposed domain data representations
│   ├── Models/              # Existing empty application model area
│   ├── Services/            # Proposed use cases / transaction coordination
│   ├── Validation/          # Proposed reusable rules when useful
│   └── Database/
│       ├── Migrations/      # Applied staged schema plus future reviewed changes
│       └── Seeds/           # Synthetic data only
└── tests/                   # Existing PHPUnit setup; add focused tests
frontend/
└── src/
    ├── app/                 # Proposed routes and auth state
    ├── components/          # Proposed shared UI
    ├── features/            # Proposed auth, plans, expenses, minigames, admin
    ├── pages/               # Proposed route screens
    └── services/            # Existing Fetch API helper; extend by resource
database/
└── init.sql                 # Current connectivity-only init; no business schema
docs/
├── database/                # Existing ERD.png + database-note.md; review only
└── ui-ux/                   # Existing figma-link.md; Page 1 inventory reviewed
```

**Structure Decision**: Retain the existing `frontend/` and `backend/` split,
CodeIgniter's current `app/` conventions, and the current Docker Compose
development services. Proposed feature folders are not yet present.

## Complexity Tracking

No Constitution exception or additional project/service is proposed. The
conceptual application services are limited to multi-step business rules and
transaction coordination; direct query/model access remains appropriate for
simple reads and writes.
