# Mobile API Guide

Base URL for a local Android emulator:

```text
http://10.0.2.2:8000/api
```

Base URL for a physical device on the same network:

```text
http://YOUR_COMPUTER_IP:8000/api
```

Use this header for all protected endpoints:

```http
Authorization: Bearer <access_token>
Accept: application/json
Content-Type: application/json
```

## Folder Structure

```text
app/
└── Http/
    ├── Controllers/
    │   └── Api/
    │       ├── AuthController.php
    │       ├── BookController.php
    │       ├── StudentController.php
    │       ├── IssueController.php
    │       └── DashboardController.php
    ├── Requests/
    │   └── Api/
    │       ├── LoginRequest.php
    │       ├── SearchRequest.php
    │       ├── IssueStoreRequest.php
    │       └── IssueReturnRequest.php
    └── Resources/
        ├── BookResource.php
        ├── StudentResource.php
        ├── IssueResource.php
        ├── FineResource.php
        └── UserResource.php
```

## Authentication

### POST /api/login

```json
{
  "email": "admin@example.com",
  "password": "password",
  "device_name": "Pixel 8"
}
```

```json
{
  "message": "Login successful.",
  "token_type": "Bearer",
  "access_token": "1|plain-text-token",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "phone": "9800000000",
    "role": "admin",
    "status": "active"
  }
}
```

### POST /api/logout

Revokes the current Sanctum token.

### GET /api/profile

Returns the authenticated user and linked student or staff data.

### GET /api/test-unauthorized

Returns a manual 401 JSON response for Android/Postman auth testing.

```json
{
  "message": "Unauthenticated."
}
```

## Books

Endpoints:

```text
GET /api/books
GET /api/books/{id}
GET /api/books/search?q=clean+code
GET /api/books/category/Science
GET /api/books/category/1
GET /api/books/available
```

Book response fields:

```json
{
  "id": 10,
  "accession_no": "9780132350884",
  "title": "Clean Code",
  "author": "Robert C. Martin",
  "publisher": "Prentice Hall",
  "category": "Programming",
  "quantity": 5,
  "available_quantity": 3,
  "status": "available"
}
```

`accession_no` is mapped from the existing `books.isbn` column.

## Students

Endpoints:

```text
GET /api/students
GET /api/students/{id}
GET /api/students/search?q=riya
```

Student response fields:

```json
{
  "id": 7,
  "name": "Riya Sharma",
  "roll_no": "BCA-001",
  "email": "riya@example.com",
  "phone": "9811111111",
  "faculty": "BCA",
  "semester": "4"
}
```

## Issues

Endpoints:

```text
POST /api/issues
GET /api/issues
GET /api/issues/{id}
GET /api/issues/student/{studentId}
POST /api/issues/return/{id}
```

Issue one book:

```json
{
  "student_id": 7,
  "book_id": 10
}
```

Issue multiple books:

```json
{
  "student_id": 7,
  "book_ids": [10, 11],
  "issue_date": "2026-06-05",
  "due_date": "2026-06-19"
}
```

Return a book:

```json
{
  "condition": "good",
  "return_date": "2026-06-20"
}
```

Issue response fields:

```json
{
  "issue_id": 15,
  "student": {
    "id": 7,
    "name": "Riya Sharma",
    "roll_no": "BCA-001",
    "email": "riya@example.com",
    "phone": "9811111111",
    "faculty": "BCA",
    "semester": "4"
  },
  "book": {
    "id": 10,
    "accession_no": "9780132350884",
    "title": "Clean Code",
    "author": "Robert C. Martin",
    "publisher": "Prentice Hall",
    "category": "Programming",
    "quantity": 5,
    "available_quantity": 2,
    "status": "available"
  },
  "issue_date": "2026-06-05",
  "due_date": "2026-06-19",
  "return_date": null,
  "fine_amount": 0,
  "status": "issued"
}
```

## Dashboard

### GET /api/dashboard

```json
{
  "total_books": 350,
  "total_students": 120,
  "issued_books": 41,
  "returned_books": 209,
  "overdue_books": 6
}
```

## Overdue Books

```text
GET /api/overdue
```

Returns issue resources where `return_date` is null and `due_date` is before today.

## Fines

Endpoints:

```text
GET /api/fines
GET /api/fines/student/{id}
```

Fine response fields:

```json
{
  "id": 3,
  "issue_id": 15,
  "student": {},
  "book": {},
  "amount": 25,
  "days_late": 5,
  "status": "paid",
  "paid_on": null,
  "remarks": "Overdue fine"
}
```

## Postman Examples

1. Login

```http
POST http://127.0.0.1:8000/api/login
Accept: application/json
Content-Type: application/json
```

```json
{
  "email": "admin@example.com",
  "password": "password",
  "device_name": "Postman"
}
```

2. Set a collection variable named `token` to the returned `access_token`.

3. List books

```http
GET http://127.0.0.1:8000/api/books
Authorization: Bearer {{token}}
Accept: application/json
```

4. Issue a book

```http
POST http://127.0.0.1:8000/api/issues
Authorization: Bearer {{token}}
Accept: application/json
Content-Type: application/json
```

```json
{
  "student_id": 7,
  "book_id": 10
}
```

5. Return a book

```http
POST http://127.0.0.1:8000/api/issues/return/15
Authorization: Bearer {{token}}
Accept: application/json
Content-Type: application/json
```

```json
{
  "condition": "good"
}
```

## Retrofit Notes

Use `@Header("Authorization") token: String` with value `Bearer <access_token>`.

Paginated list endpoints return Laravel's standard `data`, `links`, and `meta` keys, which map cleanly to a generic Gson pagination model.
