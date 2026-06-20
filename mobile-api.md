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
    "You have Rs. 50 pending fines.",
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
