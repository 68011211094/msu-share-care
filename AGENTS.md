# AGENTS.md — MSU Share & Care

## 1. Project Context

This project is **MSU Share & Care**, a Web Application for MSU students.

Read `PROJECT.md` before making implementation decisions.

The project is a simple community bulletin board for students to share unused items through:

- Donate
- Exchange

It is **not** an online marketplace and must not become one without explicit approval.

## 2. Required Development Stack

Prefer the course-aligned stack:

- HTML5
- CSS3
- JavaScript
- jQuery where appropriate
- AJAX / Fetch where appropriate
- PHP
- MySQL
- Apache / XAMPP for local development
- Git / GitHub
- VS Code

Avoid introducing Laravel, React, Vue, Next.js, Node.js as the primary application architecture unless explicitly approved.

The code should be understandable to a second-year Web Programming student.

## 3. Scope Rules

Do not add unnecessary features.

Do NOT add these unless explicitly requested:

- Chat
- Notifications
- Payments
- Delivery/shipping
- Auction
- Matching/recommendation systems
- Social feed
- Likes/follows
- Comments
- Google Login / OAuth
- Mobile application
- Complex student-ID verification
- Request/approve/reject workflows

Core correctness is more important than feature count.

## 4. Requirement First

Before implementation, explain:

1. Which requirement is being implemented.
2. Which system area is affected.
3. Dependencies.
4. Database impact.
5. Security concerns.

If an important requirement is unclear, ask before implementing.

## 5. Plan Files Before Editing

Before changing code, state:

```text
สร้าง:
- path/file.ext

แก้:
- path/file.ext

ไม่แก้:
- path/file.ext
```

Give a short reason for each group.

Do not silently modify unrelated files.

## 6. Work in Small Steps

Break large features into small steps.

Example:

```text
1. Database schema
2. Validation
3. Create
4. Read
5. Update
6. Delete
7. Authorization
8. Testing
```

Do not implement the whole project in one giant rewrite.

## 7. Code Style

Write code that is:

- Beginner-readable
- Explicit
- Easy to debug
- Meaningfully named
- Minimally abstracted
- Consistent with the existing project

Do not over-engineer.

Do not introduce unnecessary frameworks, design patterns, services, repositories, or abstractions.

## 8. Authentication and Authorization

Authentication must use:

- PHP sessions
- `password_hash()`
- `password_verify()`

Authorization must be enforced server-side.

Never rely only on hiding buttons.

For item modification:

```text
current_user.id == item.owner_id
```

must be checked on the server before edit/delete/update/complete operations.

Admin pages must verify the Admin role server-side.

## 9. Security Requirements

Always consider:

- SQL Injection → prepared statements / parameterized queries
- XSS → output escaping such as `htmlspecialchars()`
- CSRF → CSRF tokens for state-changing requests where appropriate
- Session security
- Authorization / access control
- Server-side validation
- Safe error messages
- Secrets in environment/configuration, not source control

Never claim the application is "100% secure".

Never create fake API keys, passwords, tokens, or secrets.

Never commit `.env` files containing secrets.

## 10. Database Rules

Use a relational MySQL database.

Prefer simple, understandable tables and relationships.

The `items` table must identify its owner/user.

Use prepared statements for database input.

Do not store plaintext passwords.

## 11. UI / UX Rules

The design should look like a realistic student project:

- Clean
- Minimal
- Responsive
- Practical
- Easy to navigate
- Not overly animated
- Not excessively futuristic
- Not overloaded with gradients or visual effects

Correctness and usability are more important than visual complexity.

## 12. AI Behavior Rules

The AI must NOT:

- Guess important requirements
- Add features without approval
- Change architecture without explaining why
- Rewrite the entire project unnecessarily
- Delete important files without warning
- Claim tests were performed when they were not
- Claim security is perfect
- Invent secrets
- Hide implementation changes from the user

If uncertain, ask first.

## 13. After Every Implementation

Report:

- Files changed
- What was added
- Logic changed
- Security considerations
- How to test
- What has not been implemented yet

Never say "tested" unless the test was actually performed.

## 14. Git Rules

Use small, meaningful commits.

Examples:

```text
chore: add project structure
docs: add project specification
feat: add user authentication
feat: add item CRUD
feat: enforce item ownership
feat: add admin dashboard
security: add CSRF protection
test: verify ownership authorization
```

Before major changes, create a commit so the project has a rollback point.

Never commit secrets.

## 15. Definition of Done

A feature is done only when:

- Requirement is satisfied
- Code works
- Validation works
- Authorization works
- Relevant security is reviewed
- Happy path is tested
- Invalid input is tested
- Unauthorized access is tested
- No major errors remain
- Existing functionality is not unnecessarily broken
- The implementation can be explained to the professor

## 16. Required Ownership Tests

At minimum:

### Case A
User A creates an item.

Expected:
- A can edit it.
- A can delete it.

### Case B
User B views A's item.

Expected:
- B can view it if public access allows.
- B cannot edit it.
- B cannot delete it.

### Case C
User B changes an item ID in a URL/request.

Expected:
- Server denies unauthorized modification.

### Case D
Admin accesses the admin area.

Expected:
- Admin can access only the management functions defined by the project.

## 17. Development Phases

Follow this order:

1. Phase 0 — Requirement
2. Phase 1 — Architecture
3. Phase 2 — UI/UX
4. Phase 3 — Authentication
5. Phase 4 — Item CRUD
6. Phase 5 — Admin
7. Phase 6 — Security
8. Phase 7 — Testing
9. Phase 8 — Deployment
10. Phase 9 — Presentation

Do not skip directly to advanced features.

## 18. Decision Priority

When choosing between approaches:

1. Requirement
2. Security
3. Simplicity
4. Readability
5. Maintainability
6. Deployability
7. Aesthetics
8. Extra features

If a feature does not help the requirements and increases complexity, do not add it.
