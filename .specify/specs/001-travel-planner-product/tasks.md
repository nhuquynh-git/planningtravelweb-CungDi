---

description: "Dependency-ordered implementation tasks for Cùng Đi – Travel Planner"
---

# Tasks: Cùng Đi – Travel Planner

**Input**: `spec.md`, `plan.md`, `research.md`, `data-model.md`,
`contracts/api-contract.md`, `quickstart.md`, and
`.specify/memory/constitution.md`.

**Status**: Execution checklist. Implementation has begun for tasks marked
complete below. The staged Plan foundation migration is recorded separately
from the unchanged supplied ERD. Suggested assignments use temporary labels
Thành viên 1–4 because names and individual strengths have not been provided;
replace the labels with the team's names after agreement.

**Tests**: Included because the specification and Constitution require
testable acceptance, API/authorization coverage, and database integrity checks.
Frontend has no test runner in the current package manifest; use the approved
manual/browser acceptance guide unless the team separately approves test
tooling.

**Organization**: Setup and shared foundations first, then one phase per
Specification User Story in priority order. Each task description names its
discipline: `[BE]`, `[FE]`, `[DB]`, `[TEST]`, or `[DOC]`. `[P]` means the task
can run in parallel after its stated prerequisites, without editing the same
deliverable as another parallel task.

## Existing baseline and task gates

- The current React app is a backend-health screen; the current API implements
  health only. The CodeIgniter Models directory has no business models. Shield
  and all resolved Plan, itinerary, expense/allocation, Minigame, and Admin
  audit table schemas are applied locally. Product endpoints/services remain
  unimplemented.
- Reuse React 19/Vite 8/JavaScript, CodeIgniter 4/PHP, Shield, MySQL 8.4,
  Docker Compose, the `/api` prefix, the existing JSON envelope and current
  lint/build/test commands. Do not add a framework or role.
- The latest `spec.md` resolves Q-016, Q-017a, Q-018a, Q-018b, Q-024 and
  Q-015b. Remaining deferred questions are explicit gates in `spec.md`,
  `plan.md` and the relevant contracts.
- Tasks marked **BLOCKED** require the listed product/technical decision or
  design approval before implementation. They must not be completed by
  assuming a value. Other work in the same story can proceed when independent.
- T018, T027, and T049 database schema tasks are complete under explicit
  Product Owner authorization. The Minigame report and Admin audit schemas are
  also applied; report and audit API/service work remains. Q-023 is resolved.

## Proposed Assignment for Four Team Members

These are workstream proposals, not assumptions about anyone's skills. Each
task ID appears under exactly one primary owner. Coordinate shared contracts,
schema, routes, and final acceptance with the other affected workstreams; an
owner is responsible for coordination, not unilateral approval of product or
data decisions.

| Temporary owner | Primary workstream | Assigned task IDs |
|---|---|---|
| **Thành viên 1 — API, Authentication & Security** | API contract and Shield auth; recovery/email delivery; security/privacy verification; competitor research; Admin audit policy/service coordination. | T003, T005–T007, T010–T016, T035, T059, T063–T064, T068 |
| **Thành viên 2 — Plan, Itinerary & Frontend Integration** | Shared UI feedback components; Plan/member/itinerary flows; Guest navigation; Figma frame audit; Admin moderation UI; end-to-end traceability coordination. | T001, T008, T017, T019–T025, T038, T045–T047, T062, T067 |
| **Thành viên 3 — Database, Expenses & Reporting** | ERD/data-model review; isolated database test setup and migrations; expense sharing; statistics, print, search; final ERD and AWS deployment work. | T004, T009, T018, T026–T032, T039–T044, T061, T065–T066 |
| **Thành viên 4 — Community, Admin Operations & Academic Docs** | Team workflow; Admin Plan/expense operations; Minigame post/community/moderation backend and frontend; role matrix/UML. | T002, T033–T034, T036–T037, T048–T058, T060 |

**Balance check**: Thành viên 1 owns 16 tasks; Thành viên 2 owns 16;
Thành viên 3 owns 19; Thành viên 4 owns 17. Counts are not estimates of effort.
Database, backend and documentation workload can differ substantially from
frontend or research tasks. Before implementation, the four members should
confirm task fit and exchange work if skills or availability warrant it.

**Shared decisions**: T004 schema approval, T022–T024
destination/itinerary rules, T040 search criteria, T059 findings, T061 ERD
approval and T065 AWS topology require team/product-owner review; ownership
does not authorize one member to decide these alone.

## Phase 1: Setup — Shared Infrastructure

**Purpose**: Make the existing repository easy for a four-person team to build
and validate without duplicating already-installed frameworks.

- [ ] T001 [P] [DOC] Update `README.md` with the verified frontend, backend, and Docker Compose startup steps and links to `.specify/specs/001-travel-planner-product/quickstart.md`; retain the existing stack and warn against committing local secrets or deleting database volumes.
- [ ] T002 [P] [DOC] Add `docs/development/team-workflow.md` describing branch-per-task, pull-request review, API/schema change coordination, and a neutral four-workstream handoff format without naming or assigning members.

## Phase 2: Foundational — Shared Infrastructure & Integration

**Purpose**: Approve shared contracts/design gates and establish reusable API,
database-test, and UI conventions. Protected product-feature work depends on
the applicable foundation tasks below; public/documentation work can proceed
independently.

- [x] T003 [P] [DOC] Review and record the approved endpoint, request/response, validation, status-code, and authorization contract in `.specify/specs/001-travel-planner-product/contracts/api-contract.md`; Authentication operations are approved at the operation level, while field-level Shield behavior must be verified during implementation and all other routes remain clearly labeled as proposals.
- [ ] T004 [P] [DB][DOC] Review `.specify/specs/001-travel-planner-product/data-model.md` against `docs/database/ERD.png` and `docs/database/database-note.md`; record the Product Owner's staged-scope authorization and keep team review pending unless evidenced; do not overwrite or edit the supplied ERD.
- [x] T005 [DOC] Reconcile the stale unresolved-decision summary in `.specify/specs/001-travel-planner-product/plan.md` with the current `spec.md` and its decision table; preserve Q-016/Q-017a/Q-018a/Q-018b/Q-024 as resolved and list only genuinely deferred gates without inventing answers.
- [x] T006 [BE][DOC] Record the approved Shield Access Token policy for Q-015b in `.specify/specs/001-travel-planner-product/spec.md`, `.specify/specs/001-travel-planner-product/plan.md`, `.specify/specs/001-travel-planner-product/research.md` and `.specify/specs/001-travel-planner-product/contracts/api-contract.md`: expire after 7 days without use, revoke the current token server-side on logout, and store it in an HttpOnly cookie. Record approved controls: production Secure, SameSite=Lax, configured-origin credentialed CORS, and CSRF token plus Origin/Referer validation; verify the Shield cookie bridge and cookie renewal in implementation.
- [x] T007 [P] [BE][TEST] Add focused feature tests in `backend/tests/feature/Api/ResponseEnvelopeTest.php` for the existing `backend/app/Controllers/Api/BaseApiController.php` success/error envelope, field errors, and non-leaking failures; preserve the current `status`, `message`, `data`, and optional `errors` shape.
- [ ] T008 [P] [FE] Build reusable loading, empty, validation-error, permission-denied, and confirmation components under `frontend/src/components/`; use semantic controls and keyboard-operable dialogs without adding a UI framework.
- [ ] T009 [P] [DB][TEST] Configure an isolated MySQL 8.4 integration-test setup in `backend/tests/Support/DatabaseTestCase.php` and the existing PHPUnit configuration; prove tests do not target the developer or production database and document the command in `backend/README.md`.

**Checkpoint**: The API contract is recorded, the initial Plan schema is
implemented under Product Owner authorization, and base API behavior has tests.
Team review remains pending under T004; the isolated integration-test path is
pending under T009. Protected-feature token flow remains gated on verified
cookie-to-Shield integration and security controls.

## Phase 3: User Story 1 — Authentication & User (P1, proposed)

**Goal**: Allow Guest registration/login and provide access to protected User
areas using Shield, without duplicating Shield identity data and following the
approved 7-day/HttpOnly/current-token-revocation policy.

**Independent test**: Valid Guest registration/login follows the approved
Shield flow; invalid credentials receive the shared error envelope; anonymous
requests cannot read protected data. Password recovery sends only the
confirmed recovery email content. No email-verification flow is added unless
separately approved.

- [ ] T010 [P] [US1] [TEST] Write Shield authentication feature tests in `backend/tests/feature/Auth/AuthenticationTest.php` for valid/invalid registration and login, anonymous access denial, expiry after 7 days of inactivity, current-token logout revocation, HttpOnly-cookie behavior and authenticated cookie renewal; tests depend on the approved Authentication contract in T003, T006 and the isolated test database setup in T009, and must fail before implementation.
- [ ] T011 [US1] [BE] After T010, configure API token authentication and least-privilege `user`/`admin` permissions in `backend/app/Config/Auth.php`, `backend/app/Config/AuthToken.php`, `backend/app/Config/AuthGroups.php`, and `backend/app/Config/Filters.php`; set the 7-day inactivity limit, verify cookie-to-Shield integration and renewal, keep Guest anonymous, and do not retain the Admin wildcard for business APIs.
- [ ] T012 [US1] [BE] After T011, implement Shield-backed registration and login handlers in `backend/app/Controllers/Api/AuthController.php` and `backend/app/Services/AuthService.php`; use Shield's User model and never create a duplicate credential/user table.
- [ ] T013 [US1] [BE][FE] Implement protected-route token issuance, logout/revocation, and browser cookie handling in `backend/app/Config/Routes.php`, `backend/app/Controllers/Api/AuthController.php`, and `frontend/src/features/auth/`; depends on T006 and T011 plus the approved CSRF-token, Origin/Referer, SameSite=Lax, configured-origin credentialed-CORS, and Shield cookie-bridge design, and must enforce the 7-day inactivity timeout, current-token revocation, and HttpOnly policy.
- [ ] T014 [US1] [BE] Implement password-recovery email through CodeIgniter's existing email abstraction in `backend/app/Services/AuthService.php` and `backend/app/Config/Email.php`; use a test transport until T063 is configured, send only the recovery link/instructions, and never reveal account existence or Plan/expense data.
- [ ] T015 [P] [US1] [FE] Create accessible registration, login, and password-recovery forms in `frontend/src/features/auth/` and `frontend/src/pages/`; display field errors and pending/success/failure states using the shared components from T008.
- [ ] T016 [US1] [TEST] Complete the independent authentication acceptance checks in `backend/tests/feature/Auth/AuthenticationTest.php` and `quickstart.md`; verify allowed/denied access, response envelope, safe recovery errors, and absence of unapproved email verification.

## Phase 4: User Story 2 — Plan & Itinerary (P1)

**Goal**: Create and privately manage a single-destination Plan, its direct
members, and day-grouped itinerary while preserving the confirmed creator-is-
owner rule (including Admin-created Plans).

**Independent test**: A User can create/read/update/delete their Plan, add a
registered member, and manage its itinerary; only the owner, added members,
and Admin can read it. An Admin-created Plan is owned by that Admin.

- [ ] T017 [P] [US2] [TEST] Add Plan privacy, creator-ownership, date-range, and membership feature tests in `backend/tests/feature/Plans/PlanAccessTest.php`; cover User-created and Admin-created ownership, direct member addition, and denial to a non-member.
- [x] T018 [US2] [DB] Under the explicit Product Owner authorization recorded in `data-model.md`, add and apply the reviewed CodeIgniter migrations in `backend/app/Database/Migrations/` for the staged Plan, Plan-member, destination-snapshot, and Plan-location schema; reference Shield `users.id`, enforce unique `(plan_id,user_id)`, restrict business-referenced user deletion, use vendor-neutral destination references while Q-021 remains open, and add no public visibility, invite codes, or duplicate owner/creator columns. Team review remains outstanding under T004.
- [ ] T019 [US2] [BE] Implement Plan persistence and transactional ownership/member policies in `backend/app/Models/PlanModel.php`, `backend/app/Models/PlanMemberModel.php`, and `backend/app/Services/PlanService.php`; require the authenticated creator to own the Plan and keep Plan access private.
- [ ] T020 [US2] [BE] Implement Plan and direct-member REST operations in `backend/app/Controllers/Api/Plans.php` and `backend/app/Config/Routes.php`; enforce owner/member/Admin access on every operation, read-only member permissions, duplicate-member rejection, and parent-Plan scoping; integrate confirmed Plan deletion with expense/allocation removal only after T028.
- [ ] T021 [P] [US2] [FE] Build authorized Plan list, create/edit/detail, member-add, and confirmed-delete screens in `frontend/src/features/plans/` and `frontend/src/pages/`; show no public sharing or invite-code join and distinguish owner actions from read-only member access.
- [ ] T022 [US2] BLOCKED [BE] Select and approve the external destination provider and its returned-field/error contract for Q-021, then implement search/manual fallback in `backend/app/Services/DestinationSearchService.php` and `backend/app/Controllers/Api/Destinations.php`; manual entry has no provider ID and provider failure must not block a Plan from being saved.
- [ ] T023 [US2] [BE] Implement itinerary validation and CRUD in `backend/app/Services/ItineraryService.php` and `backend/app/Controllers/Api/PlanActivities.php`; allow single-day Plans and independently optional start/end times, require end later than start when both are supplied, constrain activity dates to the inclusive Plan date range, and honor chronological/manual ordering.
- [ ] T024 [US2] BLOCKED [FE] After the relevant Figma frame review, build the day-grouped itinerary editor and chronological/manual-order controls in `frontend/src/features/plans/itinerary/`; preserve the approved layout and display validation next to affected fields.
- [ ] T025 [US2] [BE][TEST] Implement member-added and Plan-deleted email events in `backend/app/Services/PlanNotificationService.php` and `backend/tests/feature/Plans/PlanNotificationTest.php`; use a test transport until T063 is configured, send only event plus Plan name to confirmed recipients, and do not report success when delivery fails.

## Phase 5: User Story 3 — Expense & Expense Sharing (P1)

**Goal**: Store individual Plan expenses and member allocations; calculate
paid/share/difference totals without recording repayment or real-money
transactions.

**Independent test**: An owner creates, edits, and deletes multiple expenses,
including multiple records at one location; allocations sum exactly to the
expense amount, the current-member payer rule is enforced, and members can
read but not mutate expenses.

- [ ] T026 [P] [US3] [TEST] Write calculation and authorization tests in `backend/tests/unit/Expenses/ExpenseCalculationTest.php` for positive integer VND, equal/custom splits, remainder by Plan-member order, allocation totals, personal-Plan owner allocation, and current-member payer validation.
- [x] T027 [US3] [DB] Add and apply the reviewed expense/allocation schema in `backend/app/Database/Migrations/2026-11-02-143000_CreateRemainingBusinessTables.php`; require every expense's Plan reference, current-member payer, required expense date, optional note/location, unique per-expense allocation membership, positive whole-VND amounts, and no paid/settlement/repayment table or state. Follow-up migration `2026-11-02-144000_RestrictBusinessUserDeletion.php` protects referenced accounts. Allocation-sum equality remains a transactional service rule.
- [ ] T028 [US3] [BE] Implement transactional expense creation/update/deletion and aggregate calculation in `backend/app/Services/ExpenseService.php` and `backend/app/Models/ExpenseModel.php`; every expense belongs to a Plan, a group payer is a current member, amounts/allocations are positive whole VND, and allocations equal the expense total.
- [ ] T029 [US3] [BE] Implement Plan-scoped expense REST endpoints in `backend/app/Controllers/Api/PlanExpenses.php` and `backend/app/Config/Routes.php`; enforce owner/Admin writes and owner/member/Admin reads, parent-resource scoping, possible-duplicate warning plus explicit confirmation, and no success-shaped storage failures.
- [ ] T030 [US3] BLOCKED [FE] After the Plan expense API is approved, implement expense entry/edit/detail forms in `frontend/src/features/expenses/`; require positive whole-VND amounts, current Plan-member payer, equal/custom allocations, exact allocation sum, and duplicate confirmation.
- [ ] T031 [US3] [FE] Build the member-by-location paid-total matrix and Plan/member share/difference summaries in `frontend/src/features/expenses/summary/`; each cell is an aggregate of source expenses, not a stored source record, and no repay/settle control is shown.
- [ ] T032 [US3] [TEST] Verify expense API/database integration in `backend/tests/feature/Expenses/ExpenseWorkflowTest.php`; cover multiple expenses per location, transaction rollback, membership/location deletion guards, confirmed Plan deletion with all allocations, duplicate confirmation, exact VND totals, and no orphaned records.

## Phase 6: User Story 7 — Admin Plan/Expense Operations (P1)

**Goal**: Let Admin manage all Plans and expenses within the approved boundary,
make Admin-created Plans Admin-owned, and audit Admin writes but not reads.
Minigame moderation tasks are listed here but depend on the post/report work in
US6 and its deferred decision gates.

**Independent test**: After US2 and US3, Admin can create/read/update/delete
any Plan/expense and the audit trail records each create/update/delete with
actor, time, action, and target; viewing alone creates no audit event.

- [ ] T033 [P] [US7] [TEST] Add Admin boundary tests in `backend/tests/feature/Admin/AdminAuthorizationTest.php`; prove Admin Plan/expense CRUD is allowed, unauthenticated/ordinary-user Admin operations are denied, Admin-created Plan ownership is self, and account-management powers are not inferred.
- [ ] T034 [US7] [BE] Add Admin list/detail/create/update/delete operations for Plans and expenses in `backend/app/Controllers/Api/Admin/Plans.php`, `backend/app/Controllers/Api/Admin/Expenses.php`, and `backend/app/Config/Routes.php`; reuse the same domain services and data-integrity rules as User operations.
- [ ] T035 [US7] [BE][TEST] Implement `backend/app/Services/AdminAuditService.php`, `backend/app/Models/AdminAuditModel.php`, and the authorized audit read operation using `admin_audit_events`; record Admin Plan/expense create/update/delete with actor, time, action and target, retain permanently, allow Admin access through the application and operators only through restricted system tooling, and never audit read-only views or store secrets/content snapshots.
- [ ] T036 [P] [US7] [FE] Build Admin Plan/expense list, detail, and edit screens under `frontend/src/features/admin/plans/` and `frontend/src/features/admin/expenses/`; show destructive confirmation, audit-relevant outcome, and no account-management controls.
- [ ] T037 [US7] [BE] Implement report decision operations in `backend/app/Controllers/Api/Admin/MinigameReports.php` and `backend/app/Services/MinigameModerationService.php`; use the applied report schema and restrict outcomes to the confirmed keep/hide/soft-delete behavior.
- [ ] T038 [US7] [FE][TEST] Implement and verify the Admin moderation/report queue in `frontend/src/features/admin/minigames/` and `backend/tests/feature/Admin/MinigameModerationTest.php`; verify pending approval separately from report decisions, author comment controls are owner-only, and hidden/deleted posts disappear from Guest results.

## Phase 7: User Story 4 — Search, Statistics & Printing (P2, proposed)

**Goal**: Deliver authorized Plan search, accurate expense statistics, and a
printable Plan summary. Core statistics and print content are specified;
proposed Plan-search criteria must be approved before implementation.

**Independent test**: Search results respect access boundaries; Plan/member/
category totals reconcile with source expenses; print includes trip details,
day-grouped itinerary, and authorized cost totals.

- [ ] T039 [P] [US4] [TEST] Add summary/print reconciliation tests in `backend/tests/feature/Reports/PlanSummaryTest.php`; compare Plan, member paid/share/difference, location matrix, and expense-type totals with independent synthetic fixtures.
- [ ] T040 [US4] BLOCKED [BE][DOC] Obtain approval for the proposed Plan-search fields, sorting, and pagination in `.specify/specs/001-travel-planner-product/spec.md` and `.specify/specs/001-travel-planner-product/contracts/api-contract.md` before implementing search; do not expose fields beyond the approved criteria.
- [ ] T041 [US4] [BE] Implement authorized Plan, member, and expense-type aggregate queries in `backend/app/Services/PlanSummaryService.php` and `backend/app/Controllers/Api/PlanSummaries.php`; group missing expense labels as “Chưa phân loại” and calculate totals from source records, not mutable duplicate totals.
- [ ] T042 [US4] [BE] Implement the authorized print-summary JSON operation in `backend/app/Controllers/Api/PlanPrintSummary.php`; include trip information, day-grouped itinerary, and expense totals only for resources the caller may view.
- [ ] T043 [US4] BLOCKED [BE] After T040 approval, implement Plan search in `backend/app/Services/PlanSearchService.php` and `backend/app/Controllers/Api/Plans.php`; filter within the caller's authorized Plans and return an explicit empty result rather than an error.
- [ ] T044 [P] [US4] [FE][TEST] Build summary, empty-search, and print layouts in `frontend/src/features/reports/` and validate them with `.specify/specs/001-travel-planner-product/quickstart.md`; verify print privacy, readable paper output, and the spec target of 95% of list/search operations under 3 seconds on the agreed 100-Plan/1,000-expense demo dataset.

## Phase 8: User Story 5 — Guest Discovery (P2, proposed)

**Goal**: Give Guest an accurate public introduction and links into registration
and login without exposing private Plans or claiming unimplemented features.

**Independent test**: Guest opens the public page, sees only approved product
claims, and can navigate to the correct authentication flow.

- [ ] T045 [US5] BLOCKED [FE] Obtain approval for the proposed Guest landing-page content and screen mapping in `frontend/src/pages/GuestHome.jsx` and `.specify/specs/001-travel-planner-product/design-review.md`; do not add unapproved public destination or Plan content.
- [ ] T046 [US5] [FE] After T045 content approval, wire Guest registration/login navigation in `frontend/src/pages/GuestHome.jsx` and `frontend/src/features/auth/`; ensure protected Plan URLs return to authentication without rendering private data.
- [ ] T047 [P] [US5] [TEST] Add Guest landing/navigation and private-Plan denial acceptance cases to `backend/tests/feature/Guest/GuestAccessTest.php` and `.specify/specs/001-travel-planner-product/quickstart.md`; Minigame publication visibility is covered by US6 tests.

## Phase 9: User Story 6 — Community / Minigame (P2, core scope)

**Goal**: Publish informational Minigame posts, approve new posts before Guest
visibility, and support the confirmed discovery/interactions without
implementing online gameplay.

**Independent test**: User creates a post with required title and rules;
new posts remain pending until Admin approval; Guests can read only approved
posts; author ownership and confirmed likes/favorites work; no game session is
created.

- [ ] T048 [P] [US6] [TEST] Write post validation/state/visibility tests in `backend/tests/feature/Minigames/MinigamePostTest.php`; require title and rules, permit the other specified fields to be omitted, and prove pending/hidden/deleted posts are not Guest-readable.
- [x] T049 [US6] [DB] Create and apply reviewed post, genre-catalog, tag, like, favorite, comment, and report migrations in `backend/app/Database/Migrations/2026-11-02-143000_CreateRemainingBusinessTables.php`; leave genre seed contents for Q-022, preserve approved-post reports with soft deletion, and use only confirmed comment/report fields.
- [ ] T050 [US6] [BE] Implement author-owned Minigame create/delete and edit in `backend/app/Services/MinigamePostService.php` and `backend/app/Controllers/Api/Minigames.php`; require title and rules, keep other specified fields optional, and publish edits to approved posts immediately without re-approval.
- [ ] T051 [US6] [BE] Implement Guest-visible approved-post browse/detail and player-count/duration filters in `backend/app/Controllers/Api/Minigames.php`; exclude pending/hidden/deleted posts and add the controlled-genre filter only after Q-022 catalog approval.
- [ ] T052 [US6] [BE] Implement the initial Admin approval queue and approve transition in `backend/app/Controllers/Api/Admin/Minigames.php` and `backend/app/Services/MinigameModerationService.php`; a newly approved post becomes public, while non-Admin callers cannot approve or read pending content.
- [ ] T053 [US6] [DB][BE] Implement unique per-User/post like and favorite operations in `backend/app/Models/MinigameLikeModel.php`, `backend/app/Models/MinigameFavoriteModel.php`, and `backend/app/Controllers/Api/MinigameInteractions.php`; repeated add/remove requests are idempotent and favorites remain private to their owner.
- [ ] T054 [US6] [BE] Implement comment create/read/edit/delete in `backend/app/Controllers/Api/MinigameComments.php` and `backend/app/Services/MinigameCommentService.php`; attach author/time, validate content, and allow only each comment's author to edit/delete it; replies are out of scope.
- [ ] T055 [US6] [BE] Implement report submission and persistence in `backend/app/Controllers/Api/MinigameReports.php` and `backend/app/Services/MinigameReportService.php` using the applied report schema; require a reason and enforce one report per User/post.
- [ ] T056 [US6] BLOCKED [BE] Resolve Q-020 ranking/range/no-match behavior and Q-022 genre catalog before implementing Plan-member-count suggestions in `backend/app/Controllers/Api/PlanMinigameSuggestions.php`; suggestions must never modify a Plan or create a game session.
- [ ] T057 [P] [US6] [FE] Build Minigame feed, detail, and create forms in `frontend/src/features/minigames/`; require title/rules, mark other specified fields optional, show pending approval to the author, and show only approved posts in Guest feed.
- [ ] T058 [US6] [FE][TEST] Integrate like/favorite/comment/report controls and approved-post filters in `frontend/src/features/minigames/` and verify via `quickstart.md`; keep suggestion UI gated until Q-020/Q-022 are decided.

## Phase 10: Polish & Cross-Cutting Concerns — Shared Integration

**Purpose**: Complete course deliverables, operational decisions, and
cross-story validation. These tasks do not change product scope by implication.

- [x] T059 [DOC] Complete the comparable-website survey and source-backed findings in `docs/research/competitor-survey.md`; map each finding to a product decision and do not present unsourced competitor claims.
- [ ] T060 [P] [DOC] Produce the approved role/function matrix and only the UML diagrams needed to explain confirmed flows in `docs/analysis/role-function-matrix.md` and `docs/uml/`; trace each diagram to a User Story/FR and exclude unapproved workflows.
- [ ] T061 [DB][DOC] After T004 and relevant gates are approved, publish the final ERD, data dictionary, keys/cardinalities, constraints, and 3NF rationale in `docs/database/ERD.png`, `docs/database/database-note.md`, and `docs/database/data-dictionary.md`; obtain team review before replacing the supplied ERD.
- [ ] T062 BLOCKED [DOC] Inspect relevant Figma frames at readable scale and map approved screens/states to User Stories in `docs/ui-ux/figma-link.md` and `.specify/specs/001-travel-planner-product/design-review.md`; propose any additions for approval and do not alter Figma's major layout without approval.
- [ ] T063 BLOCKED [BE][DOC] Select an email delivery transport and environment configuration without adding an unapproved provider dependency; configure `backend/app/Config/Email.php`, `backend/.env.example`, and `.specify/specs/001-travel-planner-product/quickstart.md` without committing secrets.
- [ ] T064 [TEST] Verify all confirmed email events and failure handling in `backend/tests/feature/Email/EmailDeliveryTest.php`; cover password recovery, member-added and Plan-deleted recipients/content, up to three retries on transient failure, and explicit failure after exhaustion.
- [ ] T065 BLOCKED [DOC] Decide AWS services, region, domain/TLS, backup/monitoring, and rollback for Q-014 in `.specify/specs/001-travel-planner-product/plan.md` and `docs/deployment/`; do not treat the candidate topology as approved.
- [ ] T066 BLOCKED [BE][FE][DOC] After T065 approval, deploy the built React app and CodeIgniter API with private MySQL connectivity and environment-provided secrets; document health checks and recovery in `docs/deployment/aws-runbook.md` and do not claim deployment before verifying it.
- [ ] T067 [P] [TEST][DOC] Complete synthetic-data end-to-end acceptance and requirement traceability in `.specify/specs/001-travel-planner-product/quickstart.md` and `docs/reports/requirement-traceability.md`; record actual test commands/results for CRUD, search, statistics, print, email, authorization, responsive behavior, and deployment.
- [ ] T068 [TEST] Run the approved final security/privacy/performance review and record findings in `docs/reports/quality-evidence.md`; verify no unauthorized data leakage, no real-money settlement/gameplay, and the measurable success criteria in `spec.md`.

## Dependencies & Execution Order

### Phase dependencies

- **Phase 1** has no prerequisites. T001 and T002 may run in parallel.
- **Phase 2** follows repository setup. T003 (API contract), T004 (data-model
  review), and T005 (Plan/Spec reconciliation) can proceed in parallel; T006
  records the product owner's resolved token policy. T007/T008/T009 are separate
  backend-test, frontend-component, and database-test tracks.
- **US1** requires the API contract and resolved token policy (T003/T006) for
  protected token behavior, plus verified Shield cookie integration and CSRF
  controls before implementation (T011/T013). Registration/recovery UI may be
  developed independently of token persistence.
- **US2** requires approved data design (T004) and the shared response/API
  foundation. Only external destination-provider integration depends on Q-021
  (T022); itinerary date/time rules are resolved.
- **US3** requires the approved model (T004), Plan/member foundation, and shared
  API contract. Q-017b is resolved and T027's schema is applied; calculation
  tests (T026) can be prepared independently from the UI.
- **US7 Plan/expense administration** depends on US2/US3. Audit write/read
  integration (T035) depends on the applied `admin_audit_events` schema and
  shared authorization. Minigame report moderation (T037–T038) depends on
  US6's post/report foundations; Q-019 is resolved.
- **US4** summaries/print depend on US2/US3; Plan search T040/T043 remains
  gated on approval of the proposed search criteria.
- **US5** public landing/navigation can proceed after its proposed scope and
  content are approved; Guest access tests depend on the US6 publication rules.
- **US6** requires US1 authentication for author interactions. Genre seed
  contents depend on Q-022; suggestion ranking/empty-state depends on Q-020.
  Approved-post editing and comment/report rules are resolved; report schema
  exists, while API/service and moderation UI remain to implement.
- **Polish** follows the relevant stories. ERD/Figma updates require team
  approval; AWS implementation requires Q-014; email event implementation
  requires a selected delivery transport.

### User Story dependency graph

```text
Phase 1
  └─ Phase 2 shared foundation
      ├─ US1 Authentication & User ──────────────┐
      ├─ US2 Plan & Itinerary ─────┐             │
      │                            ├─ US3 Expense & Sharing
      │                            ├─ US7 Admin Plan/Expense operations
      │                            └─ US4 Search/Statistics/Print
      ├─ US5 Guest discovery ──────── US1 links; public Minigame reads depend on US6
      └─ US6 Community/Minigame ────── US1 author auth; Admin moderation in US7
                                       └─ Final cross-cutting validation
```

Stories are only parallel after shared contracts/schema boundaries are agreed.
US2 and US3 share Plan/member data; US7 depends on both; US4 consumes their
records. The graph shows feature dependencies, not a forced four-person
assignment.

### Parallel opportunities for a four-person team

- After Phase 2: separate contributors can prepare US1 auth, US2 Plan UI/API,
  US3 expense calculations/API, and US6 Minigame design/tests against the
  approved contracts. Do not merge conflicting migration or route changes
  without coordinating the shared files.
- Within US2: Plan UI (T021) can proceed against T003 while Plan service/API
  work (T019–T020) proceeds; only provider selection/integration (T022) remains
  gated by Q-021.
- Within US3: calculation tests (T026) can be written while the approved
  schema/API is reviewed; UI (T030–T031) can start only against the settled
  field contract.
- Within US6: post form/feed (T057), post tests (T048), and unique
  like/favorite work (T053) can proceed in separate files after the model/API
  contract is agreed. Report, genre, and suggestion work stays gated.
- In the final phase: website survey (T059), UML/role matrix (T060), and
  documented quality evidence (T067) can run in parallel with implementation
  when the relevant requirements are stable.

### Independent test criteria per story

| Story | Independent acceptance evidence |
|---|---|
| US1 — Authentication & User | Shield registration/login succeeds for valid input, rejects invalid input, uses the approved token policy, and denies anonymous protected access; recovery email follows FR-014 without exposing account/Plan data. |
| US2 — Plan & Itinerary | Creator owns the Plan; Admin-created Plan owner is Admin; direct members can read but not write; non-members cannot infer private data; single-day Plans and independently optional times work, with end later than start when both are supplied. |
| US3 — Expense & Expense Sharing | Multiple expense records per location persist; payer membership and whole-VND allocation rules pass; equal-share remainder and all totals reconcile; invalid writes roll back; no payment/settlement is recorded. |
| US7 — Admin | Admin Plan/expense CRUD follows the same integrity rules; writes have actor/time/action/target audit records and reads do not; report decisions affect public visibility after US6 report workflows are implemented. |
| US4 — Search/Statistics/Printing | Search is authorized; independently calculated Plan/member/category totals reconcile; print includes only permitted trip, itinerary, and cost data. |
| US5 — Guest Discovery | Guest sees only approved product copy, can navigate to registration/login, and receives no private Plan data. |
| US6 — Community/Minigame | Required title/rules validate; new posts remain private until Admin approval; only approved posts are public; author edits to approved posts publish immediately; authorship/likes/favorites/comments/reports follow approved rules; no online game/session exists. |

### Implementation strategy

1. **Foundation first**: finish Phase 1/2, review API/data contracts, and keep
   each deferred decision as an explicit gate.
2. **MVP first (US1 only)**: deliver registration/login under the approved
   token policy; protected browser requests remain gated on verified
   cookie-to-Shield integration and CSRF/security controls. Stop and validate
   authentication before expanding the product workflow.
3. **Plan increment**: add US2 Plan/itinerary using the resolved date/time
   rules, then
   independently validate the private owner/member flow.
4. **Core travel increment**: add US3 expenses and then US7 Admin Plan/expense
   operations; verify allocations, access boundaries, and audit behavior.
5. **Coursework/product increment**: add US4 search/statistics/printing, US5
   Guest discovery, and US6 Minigame content with Admin approval.
6. **Release readiness**: complete required website research, UML, approved
   ERD/3NF and Figma traceability, email transport/delivery tests, AWS decision/deployment,
   end-to-end tests, and truthful report evidence.

## Notes

- No task assigns a named team member; the group can distribute work after
  reviewing dependencies and shared-file conflicts.
- `[P]` tasks may run simultaneously only when their explicit prerequisites
  are complete and they do not edit the same file.
- “BLOCKED” tasks wait for a recorded decision/approval; do not mark them done
  by choosing a default on behalf of the product owner.
- Do not add Google/Facebook login, extra roles, public Plans/invite codes,
  repayment/payment processing, or online Minigame gameplay.
- This file plans future work. It does not create source code, migrations,
  schemas, ERD/Figma edits, deployment changes, or test evidence.
