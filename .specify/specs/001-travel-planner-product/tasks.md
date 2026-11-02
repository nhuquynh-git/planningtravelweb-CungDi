---

description: "Dependency-ordered implementation tasks for Cùng Đi – Travel Planner"
---

# Tasks: Cùng Đi – Travel Planner

**Input**: `spec.md`, `plan.md`, `research.md`, `data-model.md`,
`contracts/api-contract.md`, `quickstart.md`, and
`.specify/memory/constitution.md`.

**Status**: Planning artifact only. No code, migrations, database, ERD or Figma
changes are performed by creating this checklist. Every task remains unassigned
for the four-person team.

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
  health only. The CodeIgniter Models directory has no business models and the
  business schema is not implemented.
- Reuse React 19/Vite 8/JavaScript, CodeIgniter 4/PHP, Shield, MySQL 8.4,
  Docker Compose, the `/api` prefix, the existing JSON envelope and current
  lint/build/test commands. Do not add a framework or role.
- The latest `spec.md` and the decision table later in `plan.md` resolve
  Q-016, Q-017a, Q-018a, Q-018b and Q-024. An older summary paragraph at the
  beginning of `plan.md` still says five product decisions are unresolved;
  T005 reconciles that documentation from the approved Specify without
  changing any decision.
- Tasks marked **BLOCKED** require the listed product/technical decision or
  design approval before implementation. They must not be completed by
  assuming a value. Other work in the same story can proceed when independent.
- All migration tasks below describe future work after design approval; no
  migration is created or run as part of this `/speckit-tasks` step.

## Phase 1: Setup — Shared Infrastructure

**Purpose**: Make the existing repository easy for a four-person team to build
and validate without duplicating already-installed frameworks or assigning
work to individuals.

- [ ] T001 [P] [DOC] Update `README.md` with the verified frontend, backend, and Docker Compose startup steps and links to `.specify/specs/001-travel-planner-product/quickstart.md`; retain the existing stack and warn against committing local secrets or deleting database volumes.
- [ ] T002 [P] [DOC] Add `docs/development/team-workflow.md` describing branch-per-task, pull-request review, API/schema change coordination, and a neutral four-workstream handoff format without naming or assigning members.

## Phase 2: Foundational — Shared Infrastructure & Integration

**Purpose**: Approve shared contracts/design gates and establish reusable API,
database-test, and UI conventions. Protected product-feature work depends on
the applicable foundation tasks below; public/documentation work can proceed
independently.

- [ ] T003 [P] [DOC] Review and record the approved endpoint, request/response, validation, status-code, and authorization contract in `.specify/specs/001-travel-planner-product/contracts/api-contract.md`; keep proposed routes clearly labeled until approved.
- [ ] T004 [P] [DB][DOC] Review `.specify/specs/001-travel-planner-product/data-model.md` against `docs/database/ERD.png` and `docs/database/database-note.md`; obtain team approval for the conceptual schema and 3NF direction before physical schema work, and do not overwrite or edit the supplied ERD during this review task.
- [ ] T005 [DOC] Reconcile the stale unresolved-decision summary in `.specify/specs/001-travel-planner-product/plan.md` with the current `spec.md` and its decision table; preserve Q-016/Q-017a/Q-018a/Q-018b/Q-024 as resolved and list only genuinely deferred gates without inventing answers.
- [ ] T006 BLOCKED [BE][DOC] Decide and record the Shield Access Token lifetime, revocation/logout behavior, scopes, and browser-storage policy for Q-015b in `.specify/specs/001-travel-planner-product/spec.md` and `.specify/specs/001-travel-planner-product/contracts/api-contract.md`; do not implement token persistence or a logout promise before this decision.
- [ ] T007 [P] [BE][TEST] Add focused feature tests in `backend/tests/feature/Api/ResponseEnvelopeTest.php` for the existing `backend/app/Controllers/Api/BaseApiController.php` success/error envelope, field errors, and non-leaking failures; preserve the current `status`, `message`, `data`, and optional `errors` shape.
- [ ] T008 [P] [FE] Build reusable loading, empty, validation-error, permission-denied, and confirmation components under `frontend/src/components/`; use semantic controls and keyboard-operable dialogs without adding a UI framework.
- [ ] T009 [P] [DB][TEST] Configure an isolated MySQL 8.4 integration-test setup in `backend/tests/Support/DatabaseTestCase.php` and the existing PHPUnit configuration; prove tests do not target the developer or production database and document the command in `backend/README.md`.

**Checkpoint**: Shared contracts/design are reviewed, base API behavior has
tests, and the isolated integration-test path is ready. A protected-feature
token flow remains blocked until T006 is decided.

## Phase 3: User Story 1 — Authentication & User (P1, proposed)

**Goal**: Allow Guest registration/login and provide access to protected User
areas using Shield, without duplicating Shield identity data or silently
choosing token behavior.

**Independent test**: Valid Guest registration/login follows the approved
Shield flow; invalid credentials receive the shared error envelope; anonymous
requests cannot read protected data. Password recovery sends only the
confirmed recovery email content. No email-verification flow is added unless
separately approved.

- [ ] T010 [P] [US1] [TEST] Write Shield authentication feature tests in `backend/tests/feature/Auth/AuthenticationTest.php` for valid/invalid registration and login, anonymous access denial, and the approved token response; tests depend on T003 and T006 and must fail before implementation.
- [ ] T011 [US1] [BE] After T006, configure API token authentication and least-privilege `user`/`admin` permissions in `backend/app/Config/Auth.php`, `backend/app/Config/AuthToken.php`, `backend/app/Config/AuthGroups.php`, and `backend/app/Config/Filters.php`; Guest remains anonymous, and the Admin wildcard is not retained for business APIs.
- [ ] T012 [US1] [BE] After T011, implement Shield-backed registration and login handlers in `backend/app/Controllers/Api/AuthController.php` and `backend/app/Services/AuthService.php`; use Shield's User model and never create a duplicate credential/user table.
- [ ] T013 [US1] BLOCKED [BE][FE] Implement protected-route token issuance, logout/revocation, and browser token handling in `backend/app/Config/Routes.php`, `backend/app/Controllers/Api/AuthController.php`, and `frontend/src/features/auth/`; depends on T006 and must match its approved lifetime, revocation, and storage policy.
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
- [ ] T018 [US2] [DB] After T004 approval, add reviewed CodeIgniter migrations under `backend/app/Database/Migrations/` for the approved Plan, Plan-member, destination-snapshot, and Plan-location schema; reference Shield `users.id`, enforce unique `(plan_id,user_id)`, avoid provider-specific columns until Q-021 is decided, and do not add public visibility, invite codes, or duplicate owner/creator columns.
- [ ] T019 [US2] [BE] Implement Plan persistence and transactional ownership/member policies in `backend/app/Models/PlanModel.php`, `backend/app/Models/PlanMemberModel.php`, and `backend/app/Services/PlanService.php`; require the authenticated creator to own the Plan and keep Plan access private.
- [ ] T020 [US2] [BE] Implement Plan and direct-member REST operations in `backend/app/Controllers/Api/Plans.php` and `backend/app/Config/Routes.php`; enforce owner/member/Admin access on every operation, read-only member permissions, duplicate-member rejection, and parent-Plan scoping; integrate confirmed Plan deletion with expense/allocation removal only after T028.
- [ ] T021 [P] [US2] [FE] Build authorized Plan list, create/edit/detail, member-add, and confirmed-delete screens in `frontend/src/features/plans/` and `frontend/src/pages/`; show no public sharing or invite-code join and distinguish owner actions from read-only member access.
- [ ] T022 [US2] BLOCKED [BE] Select and approve the external destination provider and its returned-field/error contract for Q-021, then implement search/manual fallback in `backend/app/Services/DestinationSearchService.php` and `backend/app/Controllers/Api/Destinations.php`; manual entry has no provider ID and provider failure must not block a Plan from being saved.
- [ ] T023 [US2] BLOCKED [BE] Resolve Q-021's blank-time and same-day/date-boundary rules before implementing itinerary validation and CRUD in `backend/app/Services/ItineraryService.php` and `backend/app/Controllers/Api/PlanActivities.php`; enforce the confirmed activity fields and ordering only after optionality is decided.
- [ ] T024 [US2] BLOCKED [FE] After Q-021 and the relevant Figma frame review, build the day-grouped itinerary editor and chronological/manual-order controls in `frontend/src/features/plans/itinerary/`; preserve the approved layout and display validation next to affected fields.
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
- [ ] T027 [US3] BLOCKED [DB] After T004 approval and Q-017b field-optionalities are decided, add reviewed expense/location/allocation migrations under `backend/app/Database/Migrations/`; require every expense's Plan reference, one payer, per-expense allocations, unique allocation membership, and no paid/settlement/repayment table or state.
- [ ] T028 [US3] [BE] Implement transactional expense creation/update/deletion and aggregate calculation in `backend/app/Services/ExpenseService.php` and `backend/app/Models/ExpenseModel.php`; every expense belongs to a Plan, a group payer is a current member, amounts/allocations are positive whole VND, and allocations equal the expense total.
- [ ] T029 [US3] [BE] Implement Plan-scoped expense REST endpoints in `backend/app/Controllers/Api/PlanExpenses.php` and `backend/app/Config/Routes.php`; enforce owner/Admin writes and owner/member/Admin reads, parent-resource scoping, possible-duplicate warning plus explicit confirmation, and no success-shaped storage failures.
- [ ] T030 [US3] BLOCKED [FE] After Q-017b is decided and the Plan expense API is approved, implement expense entry/edit/detail forms in `frontend/src/features/expenses/`; require positive whole-VND amounts, current Plan-member payer, equal/custom allocations, exact allocation sum, and duplicate confirmation.
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
- [ ] T035 [US7] BLOCKED [DB][BE] Resolve Q-023 storage, retention, and reader-access policy before implementing Admin audit persistence in `backend/app/Services/AdminAuditService.php` and `backend/app/Models/AdminAuditModel.php`; record only actor/time/action/target for Plan/expense writes and never audit read-only views.
- [ ] T036 [P] [US7] [FE] Build Admin Plan/expense list, detail, and edit screens under `frontend/src/features/admin/plans/` and `frontend/src/features/admin/expenses/`; show destructive confirmation, audit-relevant outcome, and no account-management controls.
- [ ] T037 [US7] BLOCKED [BE] Implement report decision operations in `backend/app/Controllers/Api/Admin/MinigameReports.php` and `backend/app/Services/MinigameModerationService.php`; depend on US6 report persistence and Q-019 decisions, and restrict outcomes to the confirmed keep/hide/delete behavior.
- [ ] T038 [US7] BLOCKED [FE][TEST] Implement and verify the Admin moderation/report queue in `frontend/src/features/admin/minigames/` and `backend/tests/feature/Admin/MinigameModerationTest.php`; depend on US6 and Q-019, verify pending approval separately from report decisions, and prove hidden/deleted posts disappear from Guest results.

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
- [ ] T049 [US6] [DB] After T004 approval, create reviewed post, genre-catalog, tag, like, favorite, and comment create/read migrations under `backend/app/Database/Migrations/`; defer genre seed contents to Q-022, implement only confirmed comment fields, and do not create report-lifecycle schema before Q-019.
- [ ] T050 [US6] [BE] Implement author-owned Minigame create/delete and pending-post edit in `backend/app/Services/MinigamePostService.php` and `backend/app/Controllers/Api/Minigames.php`; require title and rules, keep description/image/player-count/duration/equipment/genre optional, and omit editing an already-approved post until Q-018c is decided.
- [ ] T051 [US6] [BE] Implement Guest-visible approved-post browse/detail and player-count/duration filters in `backend/app/Controllers/Api/Minigames.php`; exclude pending/hidden/deleted posts and add the controlled-genre filter only after Q-022 catalog approval.
- [ ] T052 [US6] [BE] Implement the initial Admin approval queue and approve transition in `backend/app/Controllers/Api/Admin/Minigames.php` and `backend/app/Services/MinigameModerationService.php`; a newly approved post becomes public, while non-Admin callers cannot approve or read pending content.
- [ ] T053 [US6] [DB][BE] Implement unique per-User/post like and favorite operations in `backend/app/Models/MinigameLikeModel.php`, `backend/app/Models/MinigameFavoriteModel.php`, and `backend/app/Controllers/Api/MinigameInteractions.php`; repeated add/remove requests are idempotent and favorites remain private to their owner.
- [ ] T054 [US6] [BE] Implement comment create/read in `backend/app/Controllers/Api/MinigameComments.php` and `backend/app/Services/MinigameCommentService.php`; attach author/time, validate content, and do not implement edit/delete/replies until Q-019 is resolved.
- [ ] T055 [US6] BLOCKED [DB][BE] Resolve Q-019 report reason, duplicate-report, and lifecycle rules before adding report schema in `backend/app/Database/Migrations/` and implementing submission/storage in `backend/app/Controllers/Api/MinigameReports.php` and `backend/app/Services/MinigameReportService.php`; reuse only the already-confirmed Admin keep/hide/delete outcomes.
- [ ] T056 [US6] BLOCKED [BE] Resolve Q-020 ranking/range/no-match behavior and Q-022 genre catalog before implementing Plan-member-count suggestions in `backend/app/Controllers/Api/PlanMinigameSuggestions.php`; suggestions must never modify a Plan or create a game session.
- [ ] T057 [P] [US6] [FE] Build Minigame feed, detail, and create forms in `frontend/src/features/minigames/`; require title/rules, mark other specified fields optional, show pending approval to the author, and show only approved posts in Guest feed.
- [ ] T058 [US6] [FE][TEST] Integrate like/favorite/comment controls and approved-post filters in `frontend/src/features/minigames/` and verify via `quickstart.md`; keep report controls blocked until Q-019 and suggestion UI blocked until Q-020/Q-022 are decided.

## Phase 10: Polish & Cross-Cutting Concerns — Shared Integration

**Purpose**: Complete course deliverables, operational decisions, and
cross-story validation. These tasks do not change product scope by implication.

- [ ] T059 [DOC] Complete the comparable-website survey and source-backed findings in `docs/research/competitor-survey.md`; map each finding to a product decision and do not present unsourced competitor claims.
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
  is a product-owner gate, not a technical guess. T007/T008/T009 are separate
  backend-test, frontend-component, and database-test tracks.
- **US1** requires the API contract and token decision (T003/T006) for protected
  token behavior. Registration/recovery UI may be developed independently of
  token persistence; the latter remains blocked by Q-015b.
- **US2** requires approved data design (T004) and the shared response/API
  foundation. Provider integration and itinerary field validation additionally
  depend on Q-021 (T022–T024).
- **US3** requires the approved model (T004), Plan/member foundation, and Q-017b
  for field optionality before final forms/schema (T027/T030). Calculation
  tests (T026) can be prepared independently from the UI.
- **US7 Plan/expense administration** depends on US2/US3. Audit persistence
  (T035) is blocked by Q-023. Minigame report moderation (T037–T038) depends
  on US6's post/report foundations and Q-019.
- **US4** summaries/print depend on US2/US3; Plan search T040/T043 remains
  gated on approval of the proposed search criteria.
- **US5** public landing/navigation can proceed after its proposed scope and
  content are approved; Guest access tests depend on the US6 publication rules.
- **US6** requires US1 authentication for author interactions. Genre filtering
  depends on Q-022; approved-post editing on Q-018c; comment/report details on
  Q-019; suggestions on Q-020 and Q-022. Initial post approval behavior itself
  is confirmed and is not blocked by those deferred decisions.
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
  work (T019–T020) proceeds; destination/itinerary tasks remain gated by Q-021.
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
| US2 — Plan & Itinerary | Creator owns the Plan; Admin-created Plan owner is Admin; direct members can read but not write; non-members cannot infer private data; valid itinerary order and date rules pass after Q-021 is resolved. |
| US3 — Expense & Expense Sharing | Multiple expense records per location persist; payer membership and whole-VND allocation rules pass; equal-share remainder and all totals reconcile; invalid writes roll back; no payment/settlement is recorded. |
| US7 — Admin | Admin Plan/expense CRUD follows the same integrity rules; writes have actor/time/action/target audit records and reads do not; report decisions affect public visibility after US6/Q-019 gates. |
| US4 — Search/Statistics/Printing | Search is authorized; independently calculated Plan/member/category totals reconcile; print includes only permitted trip, itinerary, and cost data. |
| US5 — Guest Discovery | Guest sees only approved product copy, can navigate to registration/login, and receives no private Plan data. |
| US6 — Community/Minigame | Required title/rules validate; new posts remain private until Admin approval; only approved posts are public; authorship/likes/favorites/comments follow approved rules; no online game/session exists. |

### Implementation strategy

1. **Foundation first**: finish Phase 1/2, review API/data contracts, and keep
   each deferred decision as an explicit gate.
2. **MVP first (US1 only)**: deliver the independent registration/login
   increment; protected token behavior remains gated on Q-015b. Stop and
   validate authentication before expanding the product workflow.
3. **Plan increment**: add US2 Plan/itinerary after Q-021 is decided, then
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
