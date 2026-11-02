# Research: Cùng Đi – Travel Planner

**Branch**: `main`
**Date**: 2026-11-02
**Purpose**: Record verified stack facts, inspect available ERD/UI artifacts, and
preserve open product decisions without choosing answers for the product owner.

## Confirmed technical baseline and repository evidence

| Topic | Finding | Source |
|---|---|---|
| Frontend | `frontend/package.json` identifies React 19.3.1, Vite 8.3.5, JavaScript/ES modules, Oxlint and no installed UI test runner. `App.jsx` is a backend-health screen; `services/api.js` is a Fetch-based JSON helper. | Repository `frontend/package.json`, `frontend/src/App.jsx`, `frontend/src/services/api.js` |
| Backend | Composer requires PHP `^8.2`, CodeIgniter `^4.7`, Shield `^1.4`; PHPUnit 10.5 is configured. Application controllers include API health and a shared JSON response helper; business Models directory is empty. | `backend/composer.json`, `backend/app/Controllers/Api/`, `backend/app/Models/` |
| API | Current route group is `/api`, with public `GET /api/health` and CORS. `BaseApiController` returns success `{status,message,data}` and error `{status,message,errors?}`. No business endpoints exist yet. | `backend/app/Config/Routes.php`, `backend/app/Controllers/Api/BaseApiController.php` |
| Authentication | Composer lock pins Shield v1.4.1. The token authenticator and `Authorization` header are configured, but default auth is still `session` and no API route currently applies a Shield auth filter. Groups are `user` and `admin`; `admin` currently has wildcard permission, `user` has none. The Spec establishes only Guest/User/Admin and their approved product rules. | `backend/composer.lock`, `backend/app/Config/Auth.php`, `AuthToken.php`, `AuthGroups.php`, `Filters.php`, `Routes.php`; [spec.md](./spec.md) |
| Token lifetime | The repository sets Shield `unusedTokenLifetime` to one year. This is a configuration fact, not an approved product/security lifetime. Refresh/revocation/browser storage policy remains to be reviewed before production. | `backend/app/Config/AuthToken.php`; Constitution Principle VI |
| Database | Docker Compose uses `mysql:8.4`, exposes local DB port 3308, and mounts `database/init.sql`. The init script creates only `test_connection`; no business migrations/schema were found. | `compose.yaml`, `database/init.sql`, `backend/app/Database/` |
| Local development | Docker Compose runs PHP and MySQL. The React dev server is separate (`npm run dev`); it is not a Compose service. | `compose.yaml`, `frontend/package.json` |
| Deployment | AWS is the approved target; no selected service, region, topology or production configuration is present. | Constitution Principles I/XIII; `compose.yaml` |

## Stack research

### CodeIgniter 4.7 MVC, REST, validation and migrations

- **Decision**: Keep CodeIgniter 4 MVC. Use resource-oriented controllers for
  HTTP, focused Services for cross-model business operations and transactions,
  and Models for persistence. Use the existing response envelope and server
  validation.
- **Rationale**: This follows the agreed stack, Constitution Principles III–V,
  and the current `BaseApiController` helper. CodeIgniter's REST guide describes
  `ResourceController`, resource routing and JSON responses; its migration
  guide provides ordered `up`/`down` schema changes.
- **Alternatives considered**: New framework, microservices, generic repository
  layer, direct business logic in controllers. None is needed for this
  coursework scope; they would increase moving parts or weaken separation.
- **Sources**: [CodeIgniter 4.7 MVC](https://github.com/codeigniter4/CodeIgniter4/blob/v4.7.0/user_guide_src/source/concepts/mvc.rst),
  [RESTful resources](https://github.com/codeigniter4/CodeIgniter4/blob/v4.7.0/user_guide_src/source/incoming/restful.rst),
  [controller validation](https://github.com/codeigniter4/CodeIgniter4/blob/v4.7.0/user_guide_src/source/incoming/controllers.rst),
  [validation library](https://github.com/codeigniter4/CodeIgniter4/blob/v4.7.0/user_guide_src/source/libraries/validation.rst),
  [migrations](https://github.com/codeigniter4/CodeIgniter4/blob/v4.7.0/user_guide_src/source/dbmgmt/migration.rst).

### Shield 1.4 Access Tokens

- **Decision**: Retain the configured Shield Access Token authenticator and the
  `Authorization` header. Treat Shield `users.id` as the sole account
  principal. Build resource authorization on the confirmed Guest/User/Admin
  and Plan-owner/member rules, rather than treating token validity or a role
  group as sufficient authorization.
- **Rationale**: This is the Constitution's selected authentication foundation
  and matches current configuration and locked package. Shield's migration
  defines `users`, `auth_identities`, `auth_logins`, `auth_token_logins`,
  `auth_remember_tokens`, `auth_groups_users` and `auth_permissions_users`.
  Credential and token identity records belong to Shield; all business actor,
  owner and membership FKs should target `users.id`, never an identity/token
  row. Shield docs describe bearer-token use, hashed token storage, token
  scopes and revocation.
- **Alternatives considered**: A different authentication package or a
  browser-managed custom token system. Not proposed because Shield is approved.
- **ERD/profile impact**: Replace the proposed business `USER` auth entity with
  Shield `users`; align business User FK types to its unsigned integer ID. The
  existing ERD's email/password hash are duplicated auth data; premium fields
  are out of confirmed scope. A separate one-to-one profile extension for
  display name/avatar is only a candidate if the product owner confirms those
  profile fields are required.
- **Authorization impact**: Shield stores user/group and user/permission
  assignments, while group/permission catalogs and the group matrix are
  configured in `AuthGroups`; the inspected migration does not create group or
  permission master tables. Guest is anonymous, not a persisted group. Current
  `user` default, empty User permission set and Admin wildcard are observed
  settings, not a final least-privilege policy. Use coarse groups plus
  server-side resource checks; document an explicit Admin capability set before
  wiring routes.
- **Current integration gap**: `Auth.php` registers `tokens`, but
  `defaultAuthenticator` is `session`; `AuthToken.php` sets the
  `Authorization` header, and the one-year unused-token lifetime is not
  approved. `Auth.php` has no registration/login action configured, so email
  verification is not configured and remains deferred. `Routes.php` currently
  exposes only `/api/health` under CORS, with no protected business route;
  `Filters.php` contains no explicit Shield filter alias. Protected API routes
  must explicitly select token auth during implementation, and Admin group
  provisioning needs a reviewed operational path.
- **Not decided**: Token expiry policy, scopes, how the client stores a token,
  refresh/revocation UX and whether email verification is required. Do not copy
  the current one-year unused-token value into a product requirement.
- **Sources**: [locked Shield v1.4.1 migration](https://github.com/codeigniter4/shield/blob/93c5f1f1481ced93ceb4071ffa3a08a9647a7c8b/src/Database/Migrations/2020-12-28-223112_create_auth_tables.php),
  [Shield AuthGroups configuration](https://github.com/codeigniter4/shield/blob/93c5f1f1481ced93ceb4071ffa3a08a9647a7c8b/src/Config/AuthGroups.php),
  [Shield API Tokens guide](https://github.com/codeigniter4/shield/blob/v1.4.1/docs/guides/api_tokens.md).

### MySQL 8.4

- **Decision**: Keep MySQL and use normalized relational tables, foreign keys,
  unique constraints and transactions for related writes. Use aggregate queries
  for totals rather than storing redundant totals.
- **Rationale**: This follows Constitution Principle V, approved MySQL 8.4 and
  the expense allocation invariants.
- **Alternatives considered**: NoSQL storage or storing a denormalized
  member-by-place matrix as source data. Neither fits the approved relational
  ERD/3NF coursework requirement or individual expense history.
- **Verification limitation**: MySQL's official manual endpoints returned HTTP
  403 in this research run. The proposed use of constraints and transactions
  must be checked against the selected MySQL 8.4 configuration and CI4 driver
  when the physical schema is designed.
- **Sources**: [CREATE TABLE](https://dev.mysql.com/doc/refman/8.4/en/create-table.html),
  [foreign keys](https://dev.mysql.com/doc/refman/8.4/en/create-table-foreign-keys.html),
  [CHECK constraints](https://dev.mysql.com/doc/refman/8.4/en/create-table-check-constraints.html),
  [InnoDB transactions](https://dev.mysql.com/doc/refman/8.4/en/innodb-autocommit-commit-rollback.html).

### AWS deployment alternatives (not selected)

The approved deployment target is AWS, but the service choice belongs to the
deployment plan and is not inferred as an existing decision. A possible
single-application deployment combines a static frontend, one PHP API runtime
and managed MySQL. Candidate services:

| Layer | Option | Trade-off |
|---|---|---|
| React static assets | Amplify Hosting | Integrated static hosting/build/deploy flow; confirm team access and expected hosting controls. |
| React static assets | S3 + CloudFront | Separates object storage and CDN; requires more explicit routing/TLS/cache configuration. S3 website endpoints are HTTP-only. |
| PHP API | Elastic Beanstalk PHP platform | Managed application deployment with lower container orchestration overhead. |
| PHP API | ECS (Fargate or EC2 capacity) | Container-based control; more deployment/networking configuration than needed unless team requirements justify it. |
| MySQL | Amazon RDS for MySQL | Managed database and backup options; verify selected region's MySQL 8.4 engine version and coursework constraints. |

**Recommendation for discussion, not approval**: Prefer the least complex
combination compatible with the team account, course demonstration and cost
limits. Do not provision resources before the team chooses region, services,
domain/TLS, backup/restore and monitoring. No runtime, region or cost is
asserted here.

**Sources**: [Vite static deployment](https://vite.dev/guide/static-deploy.html),
[Amplify Hosting](https://docs.aws.amazon.com/amplify/latest/userguide/welcome.html),
[S3 website hosting](https://docs.aws.amazon.com/AmazonS3/latest/userguide/WebsiteHosting.html),
[S3 endpoints and HTTPS limitation](https://docs.aws.amazon.com/AmazonS3/latest/userguide/WebsiteEndpoints.html),
[CloudFront](https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/GettingStarted.html),
[Elastic Beanstalk PHP](https://docs.aws.amazon.com/elasticbeanstalk/latest/dg/create_deploy_PHP.container.html),
[ECS](https://docs.aws.amazon.com/AmazonECS/latest/developerguide/Welcome.html),
[Fargate](https://docs.aws.amazon.com/AmazonECS/latest/developerguide/AWS_Fargate.html),
[RDS MySQL](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/CHAP_MySQL.html),
[RDS supported MySQL versions](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/MySQL.Concepts.VersionMgmt.html).

## Existing design artifacts inspected

- `docs/database/ERD.png` exists and was visually read in this session. Its
  entity-level findings and a conditional improvement proposal are recorded in
  [design-review.md](./design-review.md) and [data-model.md](./data-model.md).
  It has not been edited.
- `docs/database/database-note.md` contains the diagram's Mermaid-style entity
  definition as text. It was inspected; it has not been edited.
- `docs/ui-ux/figma-link.md` points to the user-provided Figma file. The first
  attempt presented “Want to check out this file? Sign up or Log in”; on the
  later retry, the integrated browser opened the editor in Dev Mode. The file
  title is `web plan du lịch`, and the visible page is `Page 1`. Its layer list
  includes guest/logged-in home, Plan filter/view/create/join, location,
  actual/estimated cost, location review, Minigame, Premium, invite-code,
  leave/delete confirmations, overlay and consent labels. The canvas overview
  was inspected, but individual full-size frame contents/interactions were not
  verified; no Figma MCP connection was available. See
  [design-review.md](./design-review.md) for the visible inventory and scope
  mismatches. Do not infer detailed behavior from layer names.
- The frontend source contains only the service health-check UI in `App.jsx`.
  No product screen, screenshot deck or additional UI specification was found
  in the inspected project documentation directories.
- Repository documentation inventory also found no comparable-website survey.
  Complete that academic research in its own workstream with sources; no
  competitor claims are invented here.

## Product decisions integrated from clarification

These are confirmed product rules, not technical recommendations. Their impacts
are reflected in the Spec and design artifacts:

| Decision | Confirmed rule | Affected layers |
|---|---|---|
| Q-016 personal expenses | Every expense belongs to a Plan; personal expenses use a private Plan that may contain only its owner. | Required Plan relationship, access control, personal totals, expense UI and ERD. |
| Q-017a payer membership | The payer in a group Plan must be a current Plan member. | Payer validation, membership relationship, expense form and tests. |
| Q-018a required minigame fields | Title and rules/instructions are required; description, image, player count, duration, equipment and genre may be omitted. | Validation, create/edit form and data model. |
| Q-018b publication | A newly submitted post waits for Admin approval before Guest publication. | Pending state, public-list filtering, Admin queue, user feedback and tests. |
| Q-024 Admin-created Plan owner | The Admin who creates a Plan is its owner. | Owner reference, Admin create contract and separate audit actor attribution. |

Q-018c (whether edits to an approved post require re-approval), Q-017b, Q-019
through Q-023, Q-015a/b and the other items marked DEFERRED in Spec §12 remain
for their stated design or implementation stages. They are not resolved by
technical research and must not be inferred.
