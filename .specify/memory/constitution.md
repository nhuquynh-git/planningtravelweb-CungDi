<!--
Sync Impact Report
Version change: unratified Spec Kit scaffold -> 1.0.0
Modified principles: None; this is the first adoption.
Added principles: I-XIV (all fourteen Core Principles).
Added sections: Academic Requirements and Scope; Compliance Evidence.
Removed sections: None; scaffold placeholders and examples were replaced.
Follow-up TODO: Record the original ratification date when confirmed.
-->

# Cùng Đi – Travel Planner Constitution

## Core Principles

### I. Technology Stack and Technical Constraints
The agreed implementation stack is React 19, Vite 8, and JavaScript without
TypeScript for the frontend; PHP and CodeIgniter 4 for the backend; and MySQL 8.4
for the database. Docker Compose is the development environment. The team MUST
not replace these choices without a documented technical rationale and approval
through the change process in Principle XIV. Git and GitHub are used for version
control and collaboration; Figma is the agreed UI design tool. AWS is the
deployment target, but no particular AWS service or topology is implied.

**Compliance:** New code and dependency changes MUST remain within the agreed
stack unless an approved change records the reason, impact, and migration plan.
Project setup and deployment documentation MUST distinguish confirmed tools from
services or infrastructure that are still undecided.

### II. Client–Server Architecture
The React frontend and PHP backend MUST remain separate applications and
communicate through the backend REST API under the `/api` prefix. The frontend
MUST NOT connect directly to MySQL or depend on backend implementation details.
The backend owns business rules and data access; the frontend owns presentation
and client-side interaction.

**Compliance:** Feature designs and reviews MUST show API boundaries for
client-server interactions and MUST NOT introduce direct browser-to-database
access or duplicate authoritative business rules in the client.

### III. MVC and Separation of Concerns
Backend features MUST follow CodeIgniter 4 MVC conventions. Controllers handle
HTTP concerns and coordinate work; models and other appropriate backend
components handle persistence and domain responsibilities; views are used only
where server-rendered output is explicitly required. Cross-cutting concerns
MUST NOT be accumulated in controllers.

**Compliance:** Backend reviews MUST verify that controllers do not contain
unrelated persistence, presentation, or domain logic and that each component has
a clear responsibility consistent with the approved feature design.

### IV. REST API and JSON Conventions
The `/api` interface MUST use resource-oriented HTTP methods and status codes
appropriate to each result, and MUST exchange JSON for API request and response
bodies. Responses MUST follow the shared project envelope: successful responses
use `status`, `message`, and `data`; error responses use `status` and `message`,
with `errors` when field-level details apply. API changes MUST preserve
consistent naming and document breaking contract changes.

**Compliance:** API tests or an equivalent contract check MUST verify the
response shape, content type, relevant HTTP status, and documented behavior for
each changed endpoint.

### V. Database Design, Integrity, and Migrations
Business data structures MUST be designed from approved requirements and
documented using an ERD. Relational design MUST target Third Normal Form (3NF)
unless a documented, reviewed need justifies a deliberate exception. Tables
MUST use appropriate keys, constraints, relationships, and data types to
preserve integrity. Schema changes MUST be reproducible through reviewed
CodeIgniter migrations; migrations MUST NOT be run against shared or production
data without an approved deployment procedure.

**Compliance:** Database changes MUST include an updated ERD or equivalent
design record, a migration, and applicable tests. Reviews MUST check
normalization, referential integrity, and whether the schema matches the
approved feature scope.

### VI. Authentication and Authorization
CodeIgniter Shield is the agreed authentication foundation. Access-token
authentication is the direction for API access; its concrete token behavior
MUST be specified and reviewed before implementation. The only established
application roles are Guest, User, and Admin. Authorization MUST be explicit
and based on approved requirements; this Constitution does not define
role-specific business permissions.

**Compliance:** Protected endpoints MUST identify their authentication and
authorization requirements in the feature specification and have tests for
allowed and denied access. New roles or detailed permissions MUST NOT be
introduced without an approved requirement.

### VII. Security and Privacy
Every feature MUST protect user and application data. The backend MUST validate
untrusted input, enforce authorization at the server boundary, use
parameterized database operations, and avoid exposing secrets or internal
diagnostics in client responses. Credentials and environment-specific secrets
MUST be supplied through protected configuration, not committed to Git. Logs
MUST NOT contain passwords, access tokens, or unnecessary personal data.

**Compliance:** Security-sensitive changes MUST document relevant threats and
controls, include tests for access boundaries and invalid input, and be reviewed
for secret exposure and sensitive logging before merge.

### VIII. Frontend and UI/UX
Frontend implementation MUST use React and JavaScript and MUST communicate with
the backend through the documented API. UI design MUST be prepared in Figma and
must map to an approved feature requirement. Screens MUST provide clear
interaction feedback, support the target viewport sizes in the feature
specification, and use accessible labels and semantic controls.

**Compliance:** A UI change MUST be checked against its approved design and
acceptance criteria, including keyboard-operable controls, understandable
loading/empty/error states where applicable, and layout behavior at the
specified viewport sizes.

### IX. Validation and Error Handling
All externally supplied data MUST be validated on the server; client-side
validation MAY improve usability but MUST NOT replace server validation.
Validation and operational failures MUST produce an appropriate HTTP status and
a consistent, actionable client-facing message. Error responses MUST NOT
disclose stack traces, secrets, or private implementation details. Failures
MUST be logged safely when operational diagnosis is needed.

**Compliance:** Changed forms and endpoints MUST test valid input, invalid input,
and relevant failure cases. Tests MUST confirm that responses do not leak
internal error details.

### X. Testing and Definition of Done
Each feature MUST have acceptance criteria and proportionate automated tests
covering its changed behavior. API contracts, authorization boundaries, and
database integrity MUST receive integration or equivalent verification when
affected. A change is complete only when its required tests and applicable
frontend lint/build checks pass, documentation and design artifacts are
updated, and review confirms compliance with this Constitution.

**Compliance:** Pull requests MUST report the checks run and their results.
Unverified behavior or a failing required check MUST be disclosed and resolved
or explicitly accepted before merge; passing a build alone is not proof of
feature correctness.

### XI. Git Workflow and Team Collaboration
Git history MUST be reviewable and MUST NOT contain credentials or generated
secrets. Work MUST be scoped to approved features or fixes, and changes MUST be
reviewed by a teammate before integration when team workflow permits. Conflicts,
API contract changes, and database changes MUST be communicated to affected
contributors.

**Compliance:** Each merge request MUST state its purpose, link the approved
specification or issue when available, summarize relevant tests, and call out
API, schema, or configuration impacts. Commits MUST NOT include unrelated
changes.

### XII. Documentation and Academic Compliance
Project documentation MUST stay consistent with the implementation and approved
specifications. The team MUST maintain the technical documentation and
academic report required for the IS207 project. The coursework requires the
team to survey comparable real websites; identify user roles and corresponding
functions; design and normalize a database toward 3NF; apply UML; design
function-appropriate interfaces; use PHP, MySQL, CodeIgniter MVC, JavaScript,
and AJAX; provide search, data display, add, delete, edit, statistics,
printing, and email capabilities; test the project; and prepare technical
documents and the final project report.

**Compliance:** The report and project artifacts MUST provide evidence for each
applicable coursework requirement. Course-level capabilities MUST be broken
down into approved feature specifications before implementation; this list
does not assert that any capability is already implemented or define its
business details.

### XIII. Deployment and Operations
Docker Compose is the agreed local development environment, and AWS is the
deployment target. Deployment MUST use environment-specific configuration,
protect secrets, expose only required services, and provide documented
operational checks. The Constitution does not select AWS services or claim a
deployment has occurred.

**Compliance:** A deployment change MUST include reproducible instructions,
configuration requirements, health verification, and a rollback or recovery
approach appropriate to its scope. Production secrets MUST be managed outside
the repository.

### XIV. Spec-Driven Development and Change Management
New functionality, behavior changes, and material technical decisions MUST be
captured in an approved specification before implementation. Specifications
MUST state scope, actors, observable behavior, acceptance criteria, and
relevant data/API impacts. Ambiguity MUST be resolved in the specification
rather than filled by assumed product behavior. Google or Facebook Login is a
possible future direction only and MUST NOT be treated as an approved or
implemented feature without a separate specification.

**Compliance:** Reviews MUST be traceable to an approved specification or
explicitly scoped maintenance task. Changes to agreed technologies,
architecture, roles, or API conventions MUST follow Governance and document
compatibility and migration impacts.

## Academic Requirements and Scope

Coursework requirements are obligations for the project deliverables; they are
not evidence that a specific feature or artifact already exists. The team MUST
map each required capability to a specification, acceptance criteria, and
report evidence. The concrete entities, workflows, permissions, and behavior
for those capabilities MUST be established by approved requirements, not
inferred from this Constitution.

The technology stack and architecture in Principle I are team technical
decisions, while the academic requirements in Principle XII are course
obligations. Figma and AWS are agreed design and deployment directions. Future
ideas, including social login, remain out of scope until approved through the
specification process. No Constitution statement establishes completion status
for business features or the business database.

## Compliance Evidence

For each feature, the team MUST keep a traceable path from the applicable
coursework or product requirement to its specification, design artifacts,
implementation, tests, and report evidence. Evidence MUST be proportionate to
the requirement and reflect the actual result; planned work MUST be labeled as
planned rather than reported as completed. At project review, the team MUST be
able to identify the evidence for the required website survey, role/function
analysis, ERD and 3NF rationale, UML, UI design, required web technologies and
capabilities, testing, deployment, technical documentation, and final report.

## Governance

This Constitution governs project decisions and supersedes conflicting local
development guidance. A contributor proposing an amendment MUST describe its
rationale, affected principles, compatibility impact, and any migration or
documentation work. Amendments require review and agreement by the project
team, and MUST be recorded in version control. Implementation plans and
features MUST comply with the current Constitution or explicitly propose an
amendment before relying on an exception.

The Constitution uses semantic versioning. The MAJOR version increases when a
principle is removed or incompatibly redefined; the MINOR version increases
when a principle or governance section is added or materially expanded; the
PATCH version increases for non-semantic clarification and corrections.
Contributors MUST review compliance during specification, code review, and
project reporting, and MUST resolve or document deviations before merge.

**Version**: 1.0.0 | **Ratified**: TODO(RATIFICATION_DATE): confirm original adoption date | **Last Amended**: 2026-11-02
