# CE Mobile API

REST API backing the CE mobile app(s) and any other client that needs to log in, work with Pledges (browse campaigns, make a pledge for yourself or on behalf of another member, review pledge/fulfillment history, download a pledge PDF), and Programs & Attendance (browse programs, register yourself or invite someone else, review your registrations, view/download/share a registration, and scan a QR code to check someone in).

All endpoints live under `routes/api.php` in `ce_management_portal`, prefixed `/api/v1`. Controllers are in `app/Http/Controllers/Api/Mobile/`.

## How to use this Bruno collection

1. In Bruno: **Open Collection** → select this folder (`bruno/ce-pledges-mobile-api`).
2. Pick the **Local** environment (top-right environment selector) - it points `baseUrl` at `http://127.0.0.1:8000/api/v1`, which is what `php artisan serve` listens on locally. A **Live** environment is included as a placeholder for the production server (edit its `baseUrl` once deployed).
3. Run **Auth → Login** first. Its post-response script automatically saves the returned token into the `token` environment variable, so every other request (they all send `Authorization: Bearer {{token}}`) works immediately afterwards - no manual copy/paste needed.
4. Run requests in the numbered order shown in each folder for the smoothest walkthrough (e.g. Search Members before Create Pledge (On Behalf), Create Pledge before Pledge Detail / Download PDF - creating a pledge auto-fills the `pledgeId` variable those use).

## Authentication

Token-based via [Laravel Sanctum](https://laravel.com/docs/9.x/sanctum) personal access tokens (`Authorization: Bearer <token>`), issued by `POST /login`. Tokens don't expire by default (`config/sanctum.php`'s `expiration` is `null`) and are revoked via `POST /logout`.

## Endpoints

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/login` | Public | Authenticate with email + password, receive a bearer token |
| POST | `/logout` | Bearer | Revoke the current token |
| GET | `/me` | Bearer | Current user + linked member summary |
| GET | `/dashboard` | Bearer | Upcoming events + the member's own pledge summary |
| GET | `/pledges/campaigns` | Bearer | Active campaigns visible to the member (global + own church) |
| GET | `/pledges/members/search?q=` | Bearer | Search members in your own church, to pledge on their behalf |
| POST | `/pledges` | Bearer | Create a pledge - for yourself, or (with `member_id`) for someone else |
| GET | `/pledges?status=` | Bearer | List your own pledges, plus any you recorded for someone else |
| GET | `/pledges/{id}` | Bearer | Pledge detail, including contribution/fulfillment history |
| GET | `/pledges/{id}/pdf` | Bearer | Download the pledge as a PDF (raw bytes, `Content-Type: application/pdf`) |
| GET | `/programs` | Bearer | Browse special/active programs visible to the member, with `already_registered` |
| GET | `/programs/members/search?q=` | Bearer | Search members in your own church, to register/invite them |
| POST | `/programs/{program}/register` | Bearer | Register - for yourself by default, or (`member_id` / visitor fields) for someone else |
| GET | `/programs/registrations` | Bearer | List your own registrations, plus any you made for someone else |
| GET | `/programs/registrations/{id}` | Bearer | Registration detail: program, member, checked-in status, `scan_url` |
| GET | `/programs/registrations/{id}/pdf` | Bearer | Download the registration as a PDF (raw bytes) |
| GET | `/programs/scan/{registration}` | Bearer | Scan a registration's QR - check eligibility before confirming |
| POST | `/programs/scan/{registration}/check-in` | Bearer | Confirm check-in (marks attendance for today's occurrence) |

### POST /login

```json
// Request
{ "email": "member@example.org", "password": "secret" }

// 200 Response
{
  "token": "1|abcdef...",
  "user": {
    "id": 29, "first_name": "Josephat", "last_name": "Madili",
    "email": "member@example.org", "mobile": 745821083, "role": "Customer Admin",
    "member": { "id": 3, "first_name": "Josephat", "last_name": "Madili", "phone": "0745...", "church_id": 2, "church": "CHRIST EMBASSY MBEZI" }
  }
}
```

`401` invalid credentials, `403` account `status` isn't `Active`.

### GET /dashboard

```json
{
  "upcoming_programs": [
    { "id": 24, "name": "...", "description": "...", "banner_url": "https://.../storage/program-banners/....png", "location": "...", "start_date": "2026-10-01", "start_time": "18:00:00" }
  ],
  "pledge_summary": {
    "count": 2, "total_pledged": 20050000, "total_fulfilled": 0, "outstanding": 20050000,
    "recent": [ { "id": 31, "reference": "PLG-000031", "campaign": "HEALING STREAM JULY 2026", "currency": "TZS", "amount": 50000, "status": "pledged" } ]
  }
}
```

`pledge_summary` is `null` if the logged-in user has no linked member record.

### GET /pledges/campaigns

```json
[
  {
    "id": 14, "name": "HEALING STREAM JULY 2026", "description": "...", "banner_url": "https://...",
    "target_amount": 1000000000, "currency": "TZS", "start_date": "2026-09-11", "end_date": "2026-12-20",
    "allow_anonymous": true, "total_pledged": 550000, "progress_percent": 0.06
  }
]
```

### GET /pledges/members/search?q=

Searches `first_name`, `last_name`, `phone`, `email` - scoped to the acting member's own church plus its direct sub-churches (not the full staff-wide hierarchy).

```json
[
  {
    "id": 2, "first_name": "Ediwin", "last_name": "Nyela", "name": "Ediwin Nyela",
    "phone": "0745821082", "church": "CHRIST EMBASSY MBEZI",
    "existing_pledges": [
      { "campaign_id": 14, "campaign_name": "HEALING STREAM JULY 2026", "currency": "TZS", "amount": 200000 }
    ]
  }
]
```

`existing_pledges` is how the client flags "already pledged to this campaign" before submitting a new one - there's no server-side block on duplicate pledges, only this signal.

### POST /pledges

```json
// Request - self
{ "campaign_id": 14, "amount": 50000, "frequency": "one_time", "notes": "optional" }

// Request - on behalf of someone else (must resolve to a member in your own church/sub-churches)
{ "campaign_id": 14, "amount": 25000, "frequency": "monthly", "member_id": 2 }
```

`frequency` ∈ `one_time | weekly | monthly | custom`. `422` if the campaign isn't `active` or doesn't belong to the target member's church; `403` if `member_id` resolves outside your own church scope. Response (`201`) is the same shape as `GET /pledges/{id}` below, minus `contributions`.

### GET /pledges?status=

```json
{
  "data": [
    {
      "id": 31, "reference": "PLG-000031", "campaign_id": 14, "campaign": "HEALING STREAM JULY 2026",
      "currency": "TZS", "amount": 50000, "frequency": "one_time", "status": "pledged",
      "total_fulfilled": 0, "outstanding": 50000, "notes": "...", "pledged_at": "2026-09-21 11:54:57",
      "made_for_self": true, "member": { "id": 3, "name": "Josephat Madili" }
    }
  ],
  "current_page": 1, "last_page": 1, "total": 2
}
```

Optional `status` query filter: `pledged | partially_fulfilled | fulfilled | cancelled`.

### GET /pledges/{id}

Same shape as a list item, plus `contributions`:

```json
{
  "...": "...same fields as above...",
  "contributions": [
    { "id": 5, "amount": 20000, "payment_date": "2026-09-01", "payment_method": "Mobile Money", "payment_reference": "TXN123", "notes": null }
  ]
}
```

### GET /pledges/{id}/pdf

Raw PDF bytes (`Content-Type: application/pdf`, `Content-Disposition: attachment; filename="PLG-000031.pdf"`). The Flutter app fetches these bytes with the auth header attached, writes them to a temp file, and opens them in the device's PDF viewer - a plain browser/URL open can't carry the `Authorization` header.

### GET /programs

```json
[
  {
    "id": 24, "name": "CHURCH CONSOLIDATION MISSION CONFERENCE", "description": "...",
    "banner_url": "https://.../storage/program-banners/....png", "category": "service",
    "location": "...", "start_date": "2026-10-01", "end_date": "2026-10-04", "start_time": "18:00:00",
    "access_type": "free", "registration_fee": 0, "currency": "TZS", "already_registered": true
  }
]
```

Special, active, not-yet-ended programs visible to the member (global, or the member's own church/department/cell scope). There's no capacity/seat limit anywhere in this app - registration is unbounded.

### GET /programs/members/search?q=

Same shape/scope as `/pledges/members/search`, but without `existing_pledges` - member registration duplicates are enforced server-side (see below), not just flagged.

```json
[ { "id": 2, "name": "Ediwin Nyela", "phone": "0745821082", "church": "CHRIST EMBASSY MBEZI" } ]
```

### POST /programs/{program}/register

```json
// Request - register yourself (no body needed)
{}

// Request - invite an existing member (must resolve to your own church/sub-churches)
{ "member_id": 2 }

// Request - invite a brand-new visitor (creates a member_type=new_soul row, matched again by phone next time)
{ "first_name": "Bruno", "last_name": "Tester", "phone": "0700111222", "church_id": 2 }
```

`422` if the program isn't `special`/`active`, or if that member is **already registered** for this program (unlike Pledges, Programs hard-blocks duplicate registrations rather than just flagging them). `403` if `member_id` resolves outside your own church scope. `201` response:

```json
{
  "id": 25, "reference": "REG-000025", "program_id": 24, "program": "CHURCH CONSOLIDATION MISSION CONFERENCE",
  "banner_url": "https://...", "location": "...", "start_date": "2026-10-01", "start_time": "18:00:00",
  "registration_status": "registered", "payment_status": "free", "registered_at": "2026-09-21 12:45:00",
  "made_for_self": false, "member": { "id": 28, "name": "Bruno Tester" },
  "scan_url": "http://.../v1/programs/scan/25"
}
```

### GET /programs/registrations

Same paginated `{ data, current_page, last_page, total }` envelope as `/pledges`. Each item is the same shape shown above, minus `checked_in`/`checked_in_at` (only present on the detail endpoint). Unlike `/pledges`, there's no server-side `status` query filter here yet - filter client-side on `registration_status`/`made_for_self` if needed.

### GET /programs/registrations/{id}

Same shape as a list item, plus:

```json
{ "...": "...", "checked_in": true, "checked_in_at": "2026-09-21 12:55:26" }
```

`403` unless you made this registration (`registered_by`) or it's your own (`member_id`).

### GET /programs/registrations/{id}/pdf

Raw PDF bytes, same pattern as the pledge PDF. Note this is **more restrictive** than the web version (which lets any authenticated user download any registration's PDF, since front-desk staff print for others) - the mobile endpoint uses the same ownership check as the detail endpoint, so a member can't fetch an arbitrary registration's PDF by guessing ids.

### GET /programs/scan/{registration} and POST .../check-in

The QR code printed on the PDF / shown on the detail page encodes the **web page URL** `.../v1/programs/scan/{id}` (not a bare id or token) - the app's camera scanner should extract the trailing numeric id from whatever it scans and call these mobile endpoints with that id.

`GET /programs/scan/{registration}` checks eligibility without changing anything:

```json
{
  "ok": true, "message": "Ready to check in.",
  "registration": { "id": 25, "reference": "REG-000025", "registration_status": "registered", "payment_status": "free" },
  "program": { "id": 24, "name": "CHURCH CONSOLIDATION MISSION CONFERENCE", "location": "..." },
  "member": { "id": 28, "name": "Bruno Tester", "church": "CHRIST EMBASSY MBEZI" }
}
```

`POST /programs/scan/{registration}/check-in` re-checks the same conditions, then records attendance (`attendance_status: present`, `check_in_method: qr`) for **today's** occurrence (`Program::occurrenceForDate(today)` - not the program's original date, so scanning on the actual event day is what matters). `201` on success (adds `checked_in_at`); `422` with the same `ok: false` shape if it's no longer eligible - cancelled program/registration, unpaid on a paid program, or **already checked in today** (checked via a DB `exists()` query before inserting, so a second scan never errors, it just reports the reason). Never throws a validation exception either way - always a clean JSON response.

## Notes on scope/authorization

- Every Pledges/Programs endpoint that acts on "your own member" requires the logged-in user to have a linked `member` record (`users.id` ↔ `members.user_id`) - otherwise `403`.
- "Pledge/register on behalf of someone else" is bounded to the acting member's own church + its direct sub-churches, mirroring the staff web UI's `scopedMemberChurchIds()` pattern, but without the staff-only unrestricted/hierarchy-wide access.
- "My Pledges" / "My Registrations" both return records where you're the member **or** the one who recorded/registered it - so ones you made for someone else show up too, tagged `"made_for_self": false`.
- Business logic (creating pledges/registrations, fulfillment math, campaign/program visibility, attendance check-in, PDF rendering) is not reimplemented here - it all calls straight into the same models the web Pledges/Programs modules use (`app/Models/Pledge.php`, `PledgeCampaign.php`, `Program.php`, `ProgramRegistration.php`, `ProgramAttendance.php`), so behavior stays identical between the web portal and this API.
