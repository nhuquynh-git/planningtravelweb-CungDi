# ERD and UI/UX Design Review

**Review date**: 2026-11-02
**Status**: Evidence-based review and conditional proposals only. No ERD, Figma,
source code or database was changed.

## Evidence and access

| Artifact | Result |
|---|---|
| `docs/database/ERD.png` | Exists and was visually read. The diagram includes User, Plan, Plan Member, Route, Location Item, Location, Expense Share, Mini Game, Mini Game Tag and additional social/subscription/affiliate/notification entities. |
| `docs/database/database-note.md` | Exists and contains Mermaid-style ERD text corresponding to the image. Read. |
| `docs/ui-ux/figma-link.md` | Exists; contains the project Figma URL. |
| Figma URL | Reopened in the integrated browser. This time the Figma editor loaded in Dev Mode. File title: `web plan du lịch`; one visible page, `Page 1`. |
| Figma inventory inspected | Read the `Page 1` layer list and canvas overview. Visible top-level labels include guest/logged-in home, time, budget, trip type, view/join Plan, add location, created/saved Plan, actual/estimated cost edits, location reviews, two Minigame layers, Premium, invite-code join and confirmation overlays for leaving/deleting a member/deleting a Plan. `Frame 32`, `Frame 33`, `Frame 34`, `overlay` and a terms/privacy consent layer are also listed. |
| Frame detail inspected | The overview was visible, but individual full-size frame contents and interactions were not separately inspected at readable scale. Therefore this is a screen/layer inventory, not a complete visual or prototype-flow audit. The editor indicates no sections are marked “Ready for development”. |
| Product UI in repository | `frontend/src/App.jsx` is a backend/database health-check screen with loading, error and connected states. No product workflow screen or screenshot was found in the repository. |

The prior attempt returned a sign-up/login prompt; on this retry the same link
was readable in the browser editor. No Figma MCP connection was available, so
the evidence is limited to the browser's page/layer tree and canvas overview.
Do not infer detailed form behavior from frame names. Review individual frames
at readable scale before implementation or changing the design.

## Existing ERD audit

The supplied ERD contains useful Plan/route/location and minigame concepts, but
its cost structure and several publication/access assumptions do not match the
confirmed product requirements.

| ERD area | Issue observed | Business impact | Proposed correction (not applied) | Need |
|---|---|---|---|---|
| Account/Auth | ERD `USER` uses string ID and combines name, email, `password_hash`, avatar and premium/subscription fields. Shield v1.4.1 provides `users.id` as unsigned integer; email/password/token identities and group assignments are in Shield-owned tables. | Competing account principals/credential copies, incompatible FK types and unapproved premium scope. Profile display fields are not specified. | Replace conceptual ERD auth `USER` with Shield `users`; align all business User FKs to `users.id`; do not copy email/password/token material. Consider a separate 1:1 profile table only if display-name/avatar fields are approved. Exclude premium/subscription unless separately approved. | **Required** for identity/FK alignment; profile extension **conditional** |
| Shield groups and permissions | Repository defines default `user`, groups `user`/`admin`, no explicit User permissions, and `admin => ['*']`; Guest is not a group. No business route currently applies a Shield auth filter. | Group labels alone do not enforce ownership/member/privacy rules; wildcard is broader than confirmed Admin scope; configured token authenticator does not by itself protect routes. | Use Shield groups for coarse User/Admin distinction, anonymous policy for Guest, explicit least-privilege Admin permissions, and resource-level checks. Wire token auth to protected routes in implementation. Do not add a Guest group or new roles. | **Required** before protected API implementation |
| Plan privacy | `PLAN.visibility` allows `public`, invite code, Plan likes/saves/comments/sharing are present. | Conflicts with private membership: only owner and directly-added members can read; no public Plan sharing. | Require owner/member checks; exclude public Plan social/share flows and invitation workflow from this scope. | **Required** |
| Plan totals | Estimated/actual total fields coexist with detailed items and Expense Share. | Stale or double-counted totals; estimated-cost requirement is not confirmed. | Derive actual cost from expense source rows; retain estimate only after separate approval. | **Required for actual totals** |
| Membership | Role/status fields lack an explicit unique `(plan_id,user_id)` invariant; pending/left states imply unapproved request/leave workflows. | Duplicate members alter permissions, aggregates and remainder allocation order. | Require unique member association and one owner/member invariant; add lifecycle states only when approved. | **Required** |
| Expense | `EXPENSE_SHARE` is keyed to `LOCATION_ITEM` and member, and holds owed/paid/settled fields. No independent expense/payer record. | Cannot reliably preserve multiple expense records per place; no clear single payer; payment state suggests repayment/real transactions that are out of scope. | Add one Expense source row with one payer and one or more per-expense allocations; remove payment/settlement concepts. | **Required** |
| Location Item | Stores actual cost and estimated cost per head. | Actual cost overlaps the missing Expense entity, obscuring which member paid each item. | Actual cost becomes an aggregate of expense rows; estimate is conditional. | **Required for actual cost** |
| Route/itinerary | `ROUTE` groups location items but does not state a Plan-day date; distance/duration imply route optimization. | Cannot directly express the confirmed day-grouped itinerary, activity fields, or ordering behavior. | Add itinerary activity date, name, start/end, location, instructions, notes and ordering; exclude route optimization. | **Required** |
| Destination | `PLAN.destination` is a free string; `LOCATION` has provider/cache fields. | Selected provider ID/name/address/coordinates cannot be consistently preserved in Plan. | Store selected destination source and provider metadata when present; support manual entry without provider ID. Do not select provider/caching policy here. | **Required**, provider details gated |
| Minigame | `MINI_GAME` has no author, created time or publication/moderation state; tags cover equipment/setting, not genre. | Cannot enforce author edit/delete, Guest published reads, reports or Admin moderation. | Model a user-authored post, pending Admin-approval state and genre catalog; title and rules are required, listed remaining fields optional. Re-approval of edited published posts remains deferred (Q-018c). | **Required**, edit lifecycle deferred |
| Community interactions | Like/save/comment relations are for `PLAN`; no minigame Like/Favorite/Comment/Report entities. | Confirmed minigame features have no data relationship or user uniqueness constraints. | Associate interactions/reports with minigame posts; leave comment/report lifecycle for a later decision. | **Required** |
| Audit | No Admin audit entity. | Cannot prove required actor/time/action/target for Admin create/update/delete Plan/expense. | Add a minimal audit-event concept; retention/read permissions remain deferred. | **Required** |
| Unapproved features | Subscription, public Plan, sharing/social Plans, location review, affiliate booking, route distance/time, general notification feed. | Increases schema and UI scope and implies commerce/public sharing not confirmed. | Exclude from MVP unless separately approved; do not delete/rewrite the supplied artifact in this review. | **Out of scope for this feature** |

Detailed field, relationship and 3NF recommendations are in
[data-model.md](./data-model.md). These are proposed changes to review, not
instructions to replace the group's ERD without approval.

## Proposed ERD direction after review (not applied)

The conceptual direction in `data-model.md` replaces the current
`LOCATION_ITEM -> EXPENSE_SHARE` source-of-truth pattern with:

```text
Plan ──< Plan Member
Plan ──< Plan Location ──< Itinerary Activity
Plan ──< Expense ──< Expense Allocation >── Plan Member
Plan ── 1 Destination snapshot
User ──< Minigame Post ──< Like / Favorite / Comment / Report
Minigame Post >──< Genre Catalog
Admin User ──< Admin Audit Event
```

Conditional branches:

- Q-016 confirms every Expense references a Plan, including a private
  single-owner personal Plan; Q-017a requires a group-Plan payer to be a current
  member.
- Q-018a/b confirm required minigame fields and Admin approval before public
  visibility. Re-approval after edits remains deferred (Q-018c).
- Q-024 confirms that an Admin-created Plan is owned by that Admin.

No cardinalities beyond already confirmed rules, physical field types, table
names or FK deletion actions are approved by this review. Do not modify the
existing image or note until the owner/team approves a revision.

## UI inventory: repository and Figma

### Existing repository screens

| Screen/artifact | Actor | Current behavior evidenced | Status |
|---|---|---|---|
| `frontend/src/App.jsx` health check | Developer/operator, not a confirmed product actor screen | Shows “Travel Planner”, stack label, loading message, backend error or backend/MySQL connected status. | Existing scaffold; not a product workflow. |
| Figma `Page 1` | Multiple actors; frame/layer names suggest mixed workflows | Visible layer labels: `TRANG CHỦ - chưa đăng nhập`, `TRANG CHỦ - đã đăng nhập`, `Thời gian`, `Ngân sách`, `Loại hình du lịch`, `Xem plan`, `Tham gia plan`, `Thêm location`, `plan đã tạo`, `Plan đã lưu`, `Chỉnh sửa plan - chi phí thực tế`, `Chỉnh sửa plan - chi phí dự tính`, `Đọc đánh giá location`, two `minigame` layers, `Premium`, `Tham gia plan - nhập mã mời`, `Xác nhận rời plan`, two `Xác nhận xóa thành viên` layers, `Xác nhận xóa plan`, plus `Frame 32/33/34`, `overlay` and a terms/privacy consent layer. | Figma is now accessible; labels and canvas overview inspected. Full-size contents/interactions not individually verified. |
| Other product screens in source/docs | — | No product workflow screens found in inspected repository source; Figma layer inventory is the available design evidence. | Not implemented in repository. |

### User Story-to-screen mapping

All screens below are **required/proposed mappings**, not screens found in the
current UI. Behavior tied to decisions still marked DEFERRED must not be
designed as final before those decisions are made.

| User Story | Screen(s) needed | Main behavior/state coverage | Current status / gate |
|---|---|---|---|
| US1 — Auth | Registration, login, password recovery, protected-session expiry/error | Invalid fields, loading, wrong credentials, unauthenticated access, recovery request/success/failure | No clearly named login/register frame; only a consent text layer is visible. Token storage/lifecycle and email verification remain deferred. |
| US2 — Plan | Plan list/search; create/edit; Plan detail; destination search/manual fallback; day-grouped itinerary editor; delete confirmation | Private list, no results, date errors, provider failure/manual entry, activity order mode, confirmation | Figma labels cover time/budget/type, Plan view/join/create/saved, location add, cost edit and delete confirmations. No explicit day-grouped itinerary frame identified by label. Destination provider and date/optional-time rules deferred. |
| US3 — Expenses | Plan expense list; expense create/edit/detail; member × Plan-location aggregate; totals/category summary | One payer, equal/custom split, remainder, sum mismatch, possible-duplicate confirm, empty state, blocked member/location deletion | Actual/predicted cost-edit frames exist by label; no clearly identified payer/allocation/member × location summary frame. Apply the confirmed Plan scope and current-member payer rule. |
| US4 — Search/summary/print | Authorized search/results; Plan summary; print preview/layout | Empty results, permission-filtered data, print-safe content, totals reconciliation | No screen. Search fields remain proposed; print content confirmed. |
| US5 — Guest | Public landing and public minigame browse/detail | Accurate product description, login/register navigation, no private Plan leakage | Guest home and two Minigame layers are listed. Detailed visibility and interactions were not individually verified; Guest sees approved public posts only. |
| US6 — Community minigame | Feed/search/filter; post detail/create/edit; like/favorite/comment/report; related suggestions | Optional post fields, invalid image/form, interaction states, report status, no matching suggestions, pending-approval feedback | Two Minigame layers exist by name, but post authoring, search/filter, interaction and report controls were not confirmed from full-size views. Title/rules are required; new posts wait for Admin approval. Comment/report details and re-approval after edits remain deferred. |
| US7 — Admin | Plan/expense list/detail/create/edit; pending-post approval; minigame reports/moderation queue/detail | CRUD confirmation, audit record status, approve/keep/hide/delete, hidden post not public, forbidden account-management actions | No Admin-specific frame label identified. An Admin-created Plan is owned by that Admin. |

## UI improvement recommendations (not applied)

1. **Preserve layout pending detailed audit**: retain the existing major layout
   and relative region order. Inspect each relevant frame at readable scale
   before recommending any UI changes; do not infer behavior from its name.
2. **Make role boundaries visible**: distinguish owner actions, read-only
   member state and Admin controls without exposing controls the actor cannot
   use; the backend remains authoritative.
3. **Expense integrity feedback**: show payer, per-member allocations,
   VND-only totals, remainder behavior and a clear possible-duplicate
   confirmation. Do not add a settle/pay button.
4. **Schedule affordances**: communicate day grouping, default chronological
   order and a deliberate switch to persistent manual order. Keep time/date
   validation adjacent to fields.
5. **Minigame form**: visibly distinguish controlled genre selection from
   optional free tags; require title and rules while keeping the other
   specified fields optional; show pending-approval status and feedback.
6. **Moderation**: provide an Admin pending-approval queue and report context,
   with explicit approve/keep/hide/delete actions and confirmation where
   destructive; disclose moderation outcome only as specified later.
7. **Accessibility/responsive**: use semantic labels, keyboard-operable
   dialogs, focus/error state, non-color-only status and the spec's 375/768/1280
   pixel viewport checks. Mobile reflow may change stacking but must preserve
   desktop information architecture unless a redesign is approved.
8. **Resolve scope mismatches before reusing screens**: labels for Plan invite
    codes, Premium, location reviews, Plan-saved/social surfaces and Plan
    leave/member removal appear in Figma, but are not all part of the confirmed
    product scope. In particular, confirmed Plan access is private and members
    are added directly; do not treat invite-code join or public/social Plan
    sharing as approved. The two Minigame layers and actual/estimated cost edit
    labels need detailed frame review against approved requirements.
9. **Shared feedback**: reuse consistent loading, empty, success, validation,
   permission-denied and service-failure patterns. Never show a success state
   when a write did not complete.

## Approval boundary

This document records evidence, discrepancies and candidate changes only. The
owner/team must approve ERD modifications, any new UI layout, final screen
behavior for decisions still marked DEFERRED and any scope change before
implementation or editing Figma/source/database artifacts.
