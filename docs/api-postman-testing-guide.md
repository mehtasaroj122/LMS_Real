# API Postman/cURL Testing Guide

Base URL:

```text
https://lms.saroj00.com.np/api
```

Use the configured test student account and configured test staff account. Do not save real passwords in Postman public workspaces, docs, screenshots, or source files.

## Headers

For every JSON request:

```text
Accept: application/json
Content-Type: application/json
```

For protected routes:

```text
Authorization: Bearer YOUR_TOKEN_HERE
```

## Login

Student login example with placeholders:

```bash
curl -X POST "https://lms.saroj00.com.np/api/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "student@example.com",
    "password": "password",
    "device_name": "android-mobile"
  }'
```

Staff login example with placeholders:

```bash
curl -X POST "https://lms.saroj00.com.np/api/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "staff@example.com",
    "password": "password",
    "device_name": "android-mobile"
  }'
```

Copy the `access_token` value into your Postman collection variable named `token`.

## Protected Request

```bash
curl -X GET "https://lms.saroj00.com.np/api/profile" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Student Smoke Tests

```bash
curl -X GET "https://lms.saroj00.com.np/api/student/dashboard" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_STUDENT_TOKEN"
```

```bash
curl -X GET "https://lms.saroj00.com.np/api/student/my-books" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_STUDENT_TOKEN"
```

```bash
curl -X GET "https://lms.saroj00.com.np/api/student/fines/summary" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_STUDENT_TOKEN"
```

## Staff Smoke Tests

```bash
curl -X GET "https://lms.saroj00.com.np/api/staff/dashboard" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_STAFF_TOKEN"
```

```bash
curl -X GET "https://lms.saroj00.com.np/api/staff/students" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_STAFF_TOKEN"
```

```bash
curl -X GET "https://lms.saroj00.com.np/api/staff/fines/summary" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_STAFF_TOKEN"
```

## Common API Smoke Tests

```bash
curl -X GET "https://lms.saroj00.com.np/api/notifications/count" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

```bash
curl -X GET "https://lms.saroj00.com.np/api/library/settings" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

```bash
curl -X GET "https://lms.saroj00.com.np/api/books" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Wrong-Role Checks

Student token against staff route:

```bash
curl -X GET "https://lms.saroj00.com.np/api/staff/dashboard" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_STUDENT_TOKEN"
```

Expected: JSON 403.

Staff token against student route:

```bash
curl -X GET "https://lms.saroj00.com.np/api/student/dashboard" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_STAFF_TOKEN"
```

Expected: JSON 403.

## Validation Check

```bash
curl -X PUT "https://lms.saroj00.com.np/api/profile" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{}'
```

Expected: JSON 422 with `message` and `errors`.

## Logout and Token Revocation

```bash
curl -X POST "https://lms.saroj00.com.np/api/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

Then retry:

```bash
curl -X GET "https://lms.saroj00.com.np/api/profile" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

Expected: JSON 401 after logout.

## Notes

- Login is throttled. If you see JSON 429, wait at least one minute before retrying.
- Do not run approve, reject, issue, return, pay, waive, delete, or read-all routes on live data unless you intentionally want to change records.
- Retest catalog routes after deployment if `/api/books`, `/api/categories`, or `/api/staff/books/search` return JSON 500.
