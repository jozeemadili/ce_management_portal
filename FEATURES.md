# CE Management Portal — Feature Documentation

## Overview

This application is a **Laravel-based church management portal**, but the codebase is a **fork of an insurance/broker platform called "PolicyPro"** (built for the Tanzanian market, integrating with **TIRA**, the Tanzania Insurance Regulatory Authority) that has been **mid-transitioned** into a church management system. Substantial parts of the old insurance platform (controllers, models, views, Livewire components, exports, TIRA API clients) remain in the repository but are **not wired into the live application** — they are dead/orphaned code from before the pivot.

This document separates the two:
1. [Active Church Management Features](#1-active-church-management-features) — what actually works today
2. [Legacy / Dormant Code](#2-legacy--dormant-insurance-platform-code) — inherited code still present but disconnected
3. [Architecture Notes](#3-architecture-notes) — access control, data model, and gaps worth knowing about

---

## 1. Active Church Management Features

### 1.1 Authentication & Portal Access
- Login page and authentication (`PortalUsersController`), restricted to users with `status == 'Active'`
- Logout (session flush + Sanctum token revoke)
- "How to use" static help page
- Password reset — **not implemented** (returns a placeholder "Upcoming Soon!" message)

### 1.2 Dashboard
- Summary / general dashboard landing pages (currently static templates, not yet wired to live data)

### 1.3 Church Hierarchy Management
*(`ChurchManagementController`)*

- **List churches** — scoped by organizational hierarchy: users at the "root" designation see all churches; other users see only their own church and its direct sub-churches (paginated)
- **Create a church** — enforces strict hierarchy rules (a church's designation level must correctly match its parent's designation in the chain)
- **Update a church** — same hierarchy validation as creation
- **Activate / deactivate a church**
- **Transfer a church** — reassign a church to a new parent one designation level up; keeps a full audit trail by closing out the old hierarchy record (`end_date`) and creating a new one
- **Church hierarchy tree view** — renders the full organizational tree of active churches by designation
- **Church transfer history** — view the historical record of a church's parent changes over time
- **Churches-by-designation lookup** (AJAX/JSON) — used to populate "parent church" dropdowns

### 1.4 Member Management
*(`MemberManagementController` + `CreateMember` Livewire component)*

- **List members** — scoped to the logged-in user's church + sub-churches, with roles/designations, cell groups, and departments eager-loaded
- **Create a member** — creates a linked user account and member record together in one transaction, with one or more role/designation assignments
- **Update a member**, including church-specific discipleship tracking:
  - Foundation classes attended + date
  - Baptism status + date
  - Marriage status + date(s)
  - Re-assignment of member designations/roles
  - Sync of cell group and department memberships

### 1.5 Cell Group Management
*(`CellManagementController` + `CreateCellGroup` Livewire component)*

- **List cell groups** — scoped by church hierarchy, with members and their roles eager-loaded
- **Create a cell group** — dynamic multi-member form with per-member role assignment
  - Business rule: only one Cell Leader and one Assistant Cell Leader allowed per cell
  - Members added must belong to the same church as the cell
  - Member picker excludes anyone already in a cell group (one cell per member)
- **Update a cell group** (name/description)
- **Remove a member from a cell group**

### 1.6 Security & User Administration
*(`PortalUsersController`)*

- List/search system users (by phone, ID number, or name), scoped by company for non-super-admin users
- View and manage own profile
- Change password (enforces complexity: upper/lower/symbol, min. 8 characters; forces re-login after change)
- Register a new portal user (employee ID, branch, title, ID type/number, DOB, role, company)
- Placeholder pages for security configurations and audit trail (views exist, no backing logic yet)

---

## 2. Legacy / Dormant Insurance-Platform Code

Present in the repository but **not reachable from any active route** — inherited from the pre-pivot "PolicyPro" insurance platform. Flagged here for awareness/cleanup rather than as working features:

- **Companies module** (`CompaniesController`) — CRUD for insurers/branches; references models that no longer exist in the codebase, so it would error if invoked
- **TIRA integration client** (`app/TIRAClient`) — SOAP/REST client for Tanzania's insurance regulator: policy submission, claims (intimation/assessment/payment/rejection), cover-note verification, motor verification; plus a `JubileeClient` and an `EvmakPaymentGateaway` payment integration
- **Excel exports/imports** — insurance workflow status exports (Accepted, Approved, Canceled, Disbursed, Rejected, Quotations) and a product bulk-import
- **Legacy Livewire components** — product management, sales operations, HR registration, vehicle details, quotation builder, insurance-domain reports (sales, claims, cover notes, customers, payments), customer management
- **Old insurance route set** (`routes/web.phpbkp`) — full routes for companies, intermediaries, products, risks, plans, pricing, customers, quotations, policies, claims, payments, SMS
- **Bootstrap admin theme boilerplate** (`routes/admin_web.php`, ~150 routes) — demo pages for a generic admin dashboard template (UI kits, charts, form builders, e-commerce mockups, email templates, kanban, file manager, etc.), unrelated to either the insurance or church domain
- **SMS OTP flow** (Africa's Talking integration) — built but not wired into the current login flow
- Miscellaneous unused `PortalUsersController` methods: `getEmployees`, a password-reset method with a hardcoded default password, `updateProdyctStatus` (calls a legacy internal ESB endpoint)

---

## 3. Architecture Notes

### Access Control
- No third-party permission package (e.g. Spatie) is used. A **custom RBAC scaffold** exists (`User` → `MemberDesignation` → `RolePermission` → `Permission`, exposed via `User::hasPermission()`), but it is **not actively used** by the current controllers.
- Real access control today is **hierarchy-based data scoping**: each controller/component independently checks whether the logged-in user is at the "root" designation (sees everything) or not (sees only their own church + sub-churches). This logic is duplicated across the Church, Member, and Cell controllers and the two Livewire components rather than centralized.
- Admin-only UI (e.g. "System Users" menu item) is gated by a simple `role == 'ADMIN'` check in the Blade sidebar, not the RBAC scaffold.

### Data Model (Church Domain)
Core models: `Church` (self-referencing tree via `parent_church_id`), `ChurchDesignation` (hierarchy levels), `ChurchHierarchy` (parent-change audit trail), `Member`, `MemberDesignation`, `MemberRole`, `CellGroup` / `CellGroupMember`, `Department` / `DepartmentMember`, `Permission` / `RolePermission`, and `User` (still carries legacy insurance fields like `emp_id`, `branch_id`, `company_id`).

### Known Gaps
- **No migrations exist for any church-domain table** (`churches`, `members`, `cell_groups`, `departments`, `permissions`, etc.) — only the 4 stock Laravel migrations (users, password_resets, failed_jobs, personal_access_tokens) are tracked. The live database schema for the church features is not reproducible from this repo alone.
- `routes/api.php` is essentially empty scaffolding — no REST API has been built out yet.
- Several routes/views are static placeholders with no backing logic (dashboard summary, security configurations, audit trail, "properties/reports").
- A hardcoded password bypass exists in the login flow (`PortalUsersController@loginWeb`) — worth reviewing for removal.
- New member accounts are created with a **default password equal to the member's email address** — worth reviewing as a security consideration.
