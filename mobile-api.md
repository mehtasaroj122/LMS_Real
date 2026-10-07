# Mobile API Reference

Base URL for local testing:

```text
http://127.0.0.1:8001/api
```

Protected endpoints require:

```http
Authorization: Bearer {access_token}
Accept: application/json
```

## New Endpoints

### Staff Physical-Copy Issue Workflow

All endpoints in this section require a Sanctum bearer token for an `admin` or `staff` user.

`GET /api/staff/issue-books?search={title|isbn|author|accession_number}&per_page=20`

Returns only available, borrowing physical copies that have no active issue. Reference,
damaged, issued, and otherwise unavailable copies are excluded.

```json
{
  "success": true,
  "message": "Available book copies fetched successfully.",
  "data": [
    {
      "book_id": 12,
      "title": "Clean Architecture",
      "book_title": "Clean Architecture",
      "isbn": "9780134494166",
      "author": "Robert C. Martin",
      "book_copy_id": 101,
      "accession_number": "ACC-000101",
      "copy_type": "borrowing",
      "book_type": "borrowing",
      "status": "available",
      "shelf_location": "A-01",
      "condition": "good",
      "category": "Software Engineering"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

`GET /api/staff/students/search?query={name|student_id|roll_no|email|phone|department}`

Returns the existing staff student summary, including `can_issue`, `current_issued`, and
`max_books`. Private profile fields such as address are not included in search results.

`POST /api/staff/issues`

```json
{
  "student_id": 5,
  "book_copy_id": 101,
  "book_id": 12,
  "issue_date": "2026-10-07",
  "due_date": "2026-10-21",
  "remarks": "Issued from the Android staff app."
}
```

`book_id`, `issue_date`, `due_date`, and `remarks` are optional. When `book_id` is sent,
the API verifies that `book_copy_id` belongs to it. If dates are omitted, the existing
student/library issue-duration policy determines them. Existing accession-number request
fields remain supported for backward compatibility.

Successful responses use HTTP `201`. Validation failures use `422`, an already-issued or
otherwise conflicting copy uses `409`, missing resources use `404`, unauthenticated calls
use `401`, and non-staff/admin calls use `403`.

### Staff Physical-Copy Return Workflow

All endpoints require a Sanctum bearer token for an `admin` or `staff` user.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `GET` | `/api/staff/returns/students/search?query=...` | Search borrowers with active issues by name, student ID, roll number, email, or department. |
| `GET` | `/api/staff/return-books/student/{student_id}` | Load one student's active physical-copy issues. |
| `GET` | `/api/staff/return-books/accession/{accession_number}` | Resolve an issued copy, borrower, and active issue without student selection. |
| `POST` | `/api/staff/return-books/calculate-fine` | Preview the server-calculated return fine. |
| `POST` | `/api/staff/return-books` | Return one issue/copy pair. |
| `POST` | `/api/staff/return-books/bulk` | Atomically return multiple selected physical copies. |

Fine preview request:

```json
{
  "issue_id": 34,
  "book_copy_id": 101,
  "return_condition": "fair"
}
```

Fine preview response data includes `overdue_days`, `grace_days`,
`chargeable_overdue_days`, `overdue_fine`, `condition_fine`, `total_fine`,
`currency`, and `display_value`. Any fine value sent by a client is ignored.

Single return request:

```json
{
  "issue_id": 34,
  "book_copy_id": 101,
  "return_condition": "good",
  "remarks": "Returned at the circulation desk"
}
```

Valid conditions are `good`, `fair`, `damaged`, and `lost`. `return_date` is optional.
The server verifies the active issue/copy relationship, calculates and stores the final
fine, updates the issue and physical copy, refreshes title counters, and queues the existing
return notification after commit.

Bulk return request:

```json
{
  "items": [
    {
      "issue_id": 34,
      "book_copy_id": 101,
      "return_condition": "good"
    },
    {
      "issue_id": 35,
      "book_copy_id": 102,
      "return_condition": "damaged",
      "notes": "Cover damage"
    }
  ]
}
```

The complete bulk operation is transactional. Existing `/api/staff/returns/*`,
`/api/staff/issues/{issue}/return`, and accession-return routes remain available for
backward compatibility.

### Auth Check

`GET /api/auth/check`

```json
{
  "authenticated": true,
  "user": {
    "id": 1,
    "name": "Saroj Mehta",
    "email": "student@example.com",
    "role": "student",
    "status": "active"
  },
  "student_id": 5,
  "staff_id": null
}
```

### Categories

`GET /api/categories`

```json
{
  "data": [
    {
      "id": 1,
      "name": "Programming Languages",
      "books_count": 25
    }
  ]
}
```

### Notification Count

`GET /api/notifications/count`

```json
{
  "unread_count": 4,
  "total_count": 12
}
```

### Profile Photo

`POST /api/profile/photo`

Content type: `multipart/form-data`

Form-data field:

```text
photo: image file
```

Allowed formats: `jpg`, `jpeg`, `png`, `webp`. Maximum size: 2 MB.
Profile photo URLs require the Laravel public storage link (`php artisan storage:link`).

```json
{
  "message": "Profile photo uploaded successfully.",
  "profile_photo": "profile_photos/filename.jpg",
  "profile_photo_url": "http://127.0.0.1:8000/storage/profile_photos/filename.jpg"
}
```

If `profile_photo_url` is `null`, Android should show an initials avatar.

### Remove Profile Photo

`DELETE /api/profile/photo`

```json
{
  "message": "Profile photo removed successfully.",
  "profile_photo": null,
  "profile_photo_url": null
}
```

### Delete Eligibility

`GET /api/profile/delete-eligibility`

```json
{
  "can_delete": false,
  "message": "Account deletion is currently unavailable.",
  "issued_books": 3,
  "pending_fines": 50,
  "active_requests": 2,
  "reasons": [
    "You have 3 issued books.",
    "You have रु 50 pending fines.",
    "You have 2 active book requests."
  ]
}
```

### Delete Account

`DELETE /api/profile`

```json
{
  "confirmation": "DELETE"
}
```

Only student accounts can be deactivated from mobile. Students with issued books, pending fines, or active book requests receive `422`; admin and staff receive `403`.

### Student My Books Summary

`GET /api/student/my-books/summary`

```json
{
  "currently_issued": 1,
  "returned_books": 5,
  "overdue_books": 0,
  "due_soon": 0
}
```

### Student Requests Summary

`GET /api/student/requests/summary`

```json
{
  "total_requests": 6,
  "pending_requests": 2,
  "approved_requests": 4,
  "rejected_requests": 0,
  "cancelled_requests": 0
}
```

### Student Fines Summary

`GET /api/student/fines/summary`

```json
{
  "total_fines": 5,
  "pending_fines": 2,
  "paid_fines": 3,
  "pending_amount": 50,
  "paid_amount": 120,
  "total_amount": 170
}
```

### Password Reset

`POST /api/forgot-password`

```json
{
  "email": "student@example.com"
}
```

Response:

```json
{
  "message": "Password reset instructions have been sent if the email exists."
}
```

`POST /api/reset-password`

```json
{
  "email": "student@example.com",
  "token": "reset-token",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

## Role Access

| Endpoint group | Roles |
| --- | --- |
| `/api/books/*`, `/api/categories`, `/api/notifications/*`, `/api/profile`, `/api/auth/check`, `/api/library/settings` | admin, staff, student |
| `/api/student/*` | student |
| `/api/students/*`, `/api/issues/*`, `/api/fines`, `/api/fines/student/*`, `/api/overdue`, `/api/book-requests/*`, `/api/staff/dashboard` | admin, staff |
| `/api/admin/dashboard`, `/api/activity-logs` | admin |

Forbidden response:

```json
{
  "message": "Forbidden."
}
```

## Postman Testing Order

1. `POST /api/login` as a student and copy the Bearer token.
2. Test `GET /api/auth/check`.
3. Test `GET /api/categories`.
4. Test `GET /api/notifications/count`.
5. Test student summaries:
   `GET /api/student/my-books/summary`,
   `GET /api/student/requests/summary`,
   `GET /api/student/fines/summary`.
6. With the student token, verify these return `403`:
   `GET /api/students`,
   `POST /api/issues`,
   `GET /api/fines`,
   `GET /api/activity-logs`,
   `GET /api/admin/dashboard`.
7. Login as admin or staff and verify admin/staff protected endpoints.
