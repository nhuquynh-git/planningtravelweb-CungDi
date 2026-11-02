# Proposed REST/JSON API Contract

**Status**: The Authentication operations below are approved at the operation
level; their field validation and response behavior must be verified against
Shield during implementation. Other resource paths and payloads remain
proposals. Only `GET /api/health` is currently implemented; this document does
not claim existing business endpoints.

## Conventions

- Base prefix: `/api` (**confirmed**). Resource path names below are proposed.
- Transport: HTTPS outside local development; JSON request/response bodies.
- Authentication: the approved browser policy stores the Shield Access Token
  in an HttpOnly cookie, expires it after 7 days without use, and revokes the
  current token server-side on logout. Public Guest reads omit credentials.
  The cookie is Secure in production and uses SameSite=Lax. Credentialed CORS
  allows only the configured frontend origin; state-changing requests require
  a CSRF token and Origin/Referer validation. Verify the cookie-to-Shield
  integration before implementation; do not assume Shield natively reads
  cookies.
- Success envelope, matching `BaseApiController`:

  ```json
  {
    "status": "success",
    "message": "Human-readable result",
    "data": {}
  }
  ```

- Error envelope, matching `BaseApiController`:

  ```json
  {
    "status": "error",
    "message": "Actionable, non-sensitive message",
    "errors": {
      "field": ["Validation message"]
    }
  }
  ```

  `errors` is optional and used for field-level validation. Never include a
  stack trace, SQL, token, secret or private-resource contents.
- Use HTTP methods/statuses that match the operation. Proposed mapping:
  `200` read/update, `201` create, `204` delete where no body is needed,
  `400` malformed request, `401` unauthenticated, `403` authenticated but
  forbidden, `404` missing resource (and consider the same result for a
  private resource outside membership to avoid existence disclosure),
  `409` state conflict/confirmation required, `422` validation failure,
  `503` unavailable external dependency. Final status policy is subject to API
  review and tests.
- No versioned `/v1` prefix is proposed; adding one would need a separate API
  compatibility decision.
- Exact paging, sort, search syntax, max page size and date/time format are
  open contract details. Do not invent defaults in clients before agreement.

## Shield identity and authorization boundary

- Shield v1.4.1 owns the canonical `users.id`. Business request/response
  resource references identify that account principal; they never expose or
  accept Shield identity, password, token-secret or login-attempt row IDs.
- Shield Access Tokens are authentication credentials stored/managed by Shield
  (the package uses auth identity records); they are not application User IDs.
  Protected API routes must explicitly apply Shield token authentication.
  Current `Auth.php` registers the `tokens` authenticator but defaults to
  `session`, and current API routes have no protected business endpoints.
- Shield's configured token authenticator currently uses the `Authorization`
  header. The approved HttpOnly-cookie transport therefore requires a verified
  secure integration that presents the cookie-held token to Shield without
  exposing it to JavaScript, URLs, or logs. Require HTTPS and `Secure` in
  production; settle `SameSite`, credentialed CORS, and CSRF controls in the
  security design before enabling cookie-authenticated state-changing requests.
  Configure the approved 7-day inactivity lifetime. Shield's unused-token
  lifetime is the relevant inactivity check; separately verify cookie renewal
  behavior so an active browser is not logged out while the token is in use.
- `user` and `admin` are the configured authenticated groups; Guest is an
  anonymous caller, not a stored group. Shield group membership identifies a
  coarse role only. Server-side checks must additionally enforce Plan owner or
  membership, content authorship, and the confirmed Admin operation boundary.
- Current `admin => ['*']` and `user => []` are existing configuration, not
  approved final permissions. Before release, replace the wildcard with
  least-privilege capabilities matching only approved Admin Plan/expense and
  minigame moderation actions. Candidate keys are
  `admin.plans.manage`, `admin.expenses.manage`, and
  `admin.minigames.moderate`; these names are proposals. Ordinary User access
  uses owner/member resource checks rather than static group permissions. Do
  not grant account-management powers or add roles without approved scope.
- Do not add a refresh flow or custom token scopes without approval. Email
  verification remains deferred by Q-015a.

## Approved Authentication contract

- `POST /api/auth/register` creates an account through Shield's configured
  registration flow. Do not add email verification or application profile
  fields that are not established in the Spec.
- `POST /api/auth/login` authenticates through Shield and sets the Access Token
  in an HttpOnly, SameSite=Lax cookie. The cookie is Secure in production and
  renewed during authenticated activity so the seven-day inactivity policy is
  consistent between browser and server. Never include the raw token in JSON.
- `POST /api/auth/logout` revokes only the current Access Token and expires its
  cookie. Other tokens owned by the account remain valid.
- `POST /api/auth/password-recovery` follows FR-014 and must not reveal whether
  an account exists. Email delivery configuration is a separate gate.
- All state-changing browser requests use CSRF-token and Origin/Referer checks.
  Credentialed CORS is restricted to the configured frontend origin.

## Operation catalog (proposed)

| Method and path | Actor | Purpose / authorization | Decision gate |
|---|---|---|---|
| `POST /api/auth/register` | Guest | Create account using Shield-supported flow. | Use Shield validation; do not add unapproved profile fields or email verification. |
| `POST /api/auth/login` | Guest | Authenticate and issue an Access Token with the approved 7-day inactivity lifetime in an HttpOnly, SameSite=Lax cookie; do not return the token to JavaScript. | Verify cookie renewal and cookie-to-Shield bridge. |
| `POST /api/auth/logout` | User/Admin | Revoke the current token server-side and expire the browser cookie. | Require CSRF token and Origin/Referer validation. |
| `POST /api/auth/password-recovery` | Guest | Request the confirmed email recovery event without disclosing whether an account exists. | Email provider/abuse controls are operational decisions. |
| `GET /api/destinations/search?q=...` | User | Search the chosen external place provider. | Provider is unselected; response fields depend on it. Manual entry remains available on provider failure. |
| `GET /api/plans` | User/Admin | List only Plans the caller owns or is a member of; Admin may list all. | Search fields/sort/pagination need review. |
| `POST /api/plans` | User/Admin | Create a Plan owned by the authenticated creator; an Admin-created Plan is owned by that Admin. | Do not accept a different owner for Admin-created Plans. |
| `GET /api/plans/{planId}` | Owner/member/Admin | Read a Plan and permitted summary. | Non-member must not learn private data/existence. |
| `PATCH /api/plans/{planId}` | Owner/Admin | Update allowed Plan fields. | No general member write permission. |
| `DELETE /api/plans/{planId}` | Owner/Admin | Delete after explicit confirmation and consistently remove confirmed dependent expenses/allocations. | Must be transactional; email notification follows confirmed event. |
| `POST /api/plans/{planId}/members` | Owner/Admin | Add a registered User directly to a private Plan. | Request identifier/lookup UX needs privacy review. |
| `DELETE /api/plans/{planId}/members/{userId}` | Owner/Admin | Remove member only when no expense allocation references them. | Confirmed block-on-reference rule. |
| `GET/POST /api/plans/{planId}/activities` | Owner/member/Admin read; owner/Admin write | List/add day-grouped itinerary activity. | Required fields confirmed; blank times/date boundary validation deferred. |
| `PATCH/DELETE /api/plans/{planId}/activities/{activityId}` | Owner/Admin write; permitted read | Update/delete activity belonging to the Plan. | Enforce parent Plan scope. |
| `GET/POST /api/plans/{planId}/expenses` | Owner/member/Admin read; owner/Admin write | List/create individual expense records; every record belongs to the Plan, and the payer in a group Plan must be a current member. | Validate payer membership server-side; personal expenses use a private Plan. |
| `GET/PATCH/DELETE /api/plans/{planId}/expenses/{expenseId}` | Owner/Admin write; owner/member/Admin read | Read/update/delete a single expense, including its allocations. | Validate transaction and total; optional fields in Q-017b. |
| `GET /api/plans/{planId}/expense-summary` | Owner/member/Admin | Read Plan/member/place/category aggregates computed from source expenses. | Personal Plan totals are computed from its Plan-scoped expenses. |
| `GET /api/plans/{planId}/print-summary` | Owner/member/Admin | Return authorized Plan details, day-grouped itinerary and expense totals for browser print. | Proposed JSON data endpoint; no PDF generation requirement. |
| `GET /api/minigames` | Guest/User/Admin | List approved public posts; filter by player count, duration and genre; free tags may be a filter. | Pending, hidden and deleted posts are excluded from Guest results. Exact range/filter semantics need design. |
| `GET /api/minigames/{postId}` | Guest/User/Admin | Read an approved post. | Guest cannot read pending, hidden or deleted content. |
| `POST /api/minigames` | User | Create own post with required title and rules; the new post awaits Admin approval. | Other listed post fields are optional; return its pending status to the author. |
| `PATCH/DELETE /api/minigames/{postId}` | Author; Admin only within confirmed moderation scope | Author updates/deletes own post; Admin can remove violating post. | Preserve ownership checks; edits to an approved post publish immediately without re-approval (Q-018c). |
| `GET /api/admin/minigames?status=pending` | Admin | List posts awaiting initial publication approval. | Proposed route for the confirmed Admin approval workflow. |
| `POST /api/admin/minigames/{postId}/approve` | Admin | Approve a pending post for public visibility. | Keep separate from report decisions; no public visibility before approval. |
| `PUT/DELETE /api/minigames/{postId}/like` | User | Add/remove current User's like on an approved post; repeated add is idempotent. | Confirmed interaction. |
| `PUT/DELETE /api/minigames/{postId}/favorite` | User | Add/remove current User's private favorite on an approved post; repeated add is idempotent. | Confirmed interaction. |
| `GET/POST /api/minigames/{postId}/comments` | Guest read only if public; User write | Read/add comments on an approved post with author/time. | Comment edit/delete/reply rules deferred (Q-019); do not add them yet. |
| `POST /api/minigames/{postId}/reports` | User | Report an approved post for Admin review. | Report fields, duplicate policy and lifecycle deferred (Q-019). |
| `GET /api/admin/minigame-reports` | Admin | Review reports. | Report state/filter contract deferred. |
| `POST /api/admin/minigame-reports/{reportId}/decision` | Admin | Keep post, hide it, or delete it; hidden post disappears from Guest views. | Confirmed actions; outcome metadata/notice policy deferred. |
| `GET /api/plans/{planId}/minigame-suggestions` | Owner/member/Admin | Suggest informational posts matching Plan member count. | Matching/ranking/empty state deferred; must not modify Plan or create game session. |

These are route candidates, not an instruction to expose every operation in the
first release. Scope must follow the approved FRs and the gates above.

## Request and domain rules

- Expense amount and custom allocation use positive integer VND only. No
  currency field or exchange rate.
- One expense has one payer and one or more member allocations; allocation
  totals must equal the expense total. Equal-share remainder is assigned in
  Plan member order.
- To implement the confirmed possible-duplicate warning, a candidate response
  is `409` with a stable code such as `POTENTIAL_DUPLICATE_EXPENSE`; the client
  then asks for confirmation and resubmits with an explicit confirmation field.
  The exact field/code needs contract review. Do not silently deduplicate.
- Member/location deletion is rejected while referenced; Plan deletion needs
  explicit user confirmation. Multi-row write/delete must not leave partial
  allocations or orphaned expense data.
- Email is triggered only for the confirmed recovery, member-added and
  Plan-deleted events. Email body/recipients and retry limit follow FR-014.
- No endpoint may create a payment, reimbursement, debt settlement or online
  minigame session.
- Admin create/update/delete Plan/expense operations are audited; reads are not.

## API contract validation

For each implemented operation, tests should verify actor, HTTP method/status,
content type, success/error envelope, field validation, resource scoping and
no private-data leakage. Add contract examples only after route and payload
fields are approved. Do not treat this proposal as a checked-in OpenAPI
specification or current implementation.
