# API Route Testing Report

## 1. Project API Base URL

```text
https://lms.saroj00.com.np/api
```

## 2. Test Accounts

```text
Student test account: configured
Staff test account: configured
```

Real passwords are not stored in this report.

## 3. Route Inventory Summary

Generated from `php artisan route:list --path=api --json`.

| Group | Count | Access |
|---|---:|---|
| Total API routes | 89 | Public/protected |
| Public routes | 4 | No Bearer token |
| Common protected routes | 25 | Any authenticated role |
| Student routes | 16 | `student` role |
| Staff/Admin routes | 42 | `staff` or `admin` role |
| Admin-only routes | 2 | `admin` role |

All registered API routes currently have no route name.

## 4. Public API Routes

| Method | Endpoint | Controller | Middleware | Status | Notes |
|---|---|---|---|---|---|
| POST | `/api/login` | `Api\AuthController@login` | `api`, `throttle:5,1` | Working | Student and staff login returned JSON 200 with `message`, `token_type`, `access_token`, and `user`. Repeated tests can return JSON 429 because of throttling. |
| POST | `/api/forgot-password` | `Api\PasswordResetController@forgot` | `api`, `throttle:5,1` | Working | Empty-body validation returned JSON 422. Email-send path was not triggered to avoid sending test mail. |
| POST | `/api/reset-password` | `Api\PasswordResetController@reset` | `api`, `throttle:5,1` | Working | Empty-body validation returned JSON 422. |
| GET | `/api/test-unauthorized` | `Api\AuthController@testUnauthorized` | `api` | Working | Returns JSON 401 with `message`. |

Expected login request body: `email`, `password`, optional `device_name`.

Expected login response:

```json
{
  "message": "Login successful.",
  "token_type": "Bearer",
  "access_token": "TOKEN",
  "user": {
    "id": 1,
    "role": "student"
  }
}
```

Android note: the live login token key is `access_token`, not `token` and not `data.token`.

## 5. Student API Routes

| Method | Endpoint | Middleware | Requires Role | Status | Notes |
|---|---|---|---|---|---|
| GET | `/api/student/dashboard` | `auth:sanctum`, `role:student` | student | Working | Student token returned JSON 200 with `message`, `data`. Staff token returned JSON 403. |
| GET | `/api/student/my-books` | `auth:sanctum`, `role:student` | student | Working | JSON 200 with Laravel resource pagination: `data`, `links`, `meta`. |
| GET | `/api/student/my-books/summary` | `auth:sanctum`, `role:student` | student | Working | JSON 200 with `status`, `data`. |
| GET | `/api/student/my-books/current` | `auth:sanctum`, `role:student` | student | Working | JSON 200 with `data`, `links`, `meta`. |
| GET | `/api/student/my-books/history` | `auth:sanctum`, `role:student` | student | Working | JSON 200 with `data`, `links`, `meta`. |
| GET | `/api/student/my-books/due-soon` | `auth:sanctum`, `role:student` | student | Working | JSON 200 with `data`, `links`, `meta`. |
| GET | `/api/student/requests` | `auth:sanctum`, `role:student` | student | Working | JSON 200 with `data`, `links`, `meta`. |
| POST | `/api/student/requests` | `auth:sanctum`, `role:student` | student | Working validation | Empty-body test returned JSON 422. Valid create was not run to avoid changing live data. Expected body includes `book_id`. |
| GET | `/api/student/requests/summary` | `auth:sanctum`, `role:student` | student | Working | JSON 200 with request count fields. |
| GET | `/api/student/requests/{id}` | `auth:sanctum`, `role:student` | student | Ownership protected | Sample id returned JSON 403 because it did not belong to the test student. Use a request owned by the authenticated student for 200. |
| POST | `/api/student/requests/{id}/cancel` | `auth:sanctum`, `role:student` | student | Registered, not mutation-tested | Avoided because it changes request state. |
| GET | `/api/student/fines` | `auth:sanctum`, `role:student` | student | Working | JSON 200 with `data`, `links`, `meta`. |
| GET | `/api/student/fines/pending` | `auth:sanctum`, `role:student` | student | Working | JSON 200 with `data`, `meta`. |
| GET | `/api/student/fines/paid` | `auth:sanctum`, `role:student` | student | Working | JSON 200 with `data`, `links`, `meta`. |
| GET | `/api/student/fines/summary` | `auth:sanctum`, `role:student` | student | Working | JSON 200 with fine summary fields. |
| GET | `/api/student/fines/{id}` | `auth:sanctum`, `role:student` | student | Ownership protected | Sample id returned JSON 403 because it did not belong to the test student. Use a fine owned by the authenticated student for 200. |

## 6. Staff API Routes

| Method | Endpoint | Middleware | Requires Role | Status | Notes |
|---|---|---|---|---|---|
| GET | `/api/staff/dashboard` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | Staff token returned JSON 200 with `success`, `message`, `data`. Student token returned JSON 403. |
| GET | `/api/students` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `data`, `links`, `meta`. |
| GET | `/api/students/search` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working validation | Missing search query returned JSON 422. |
| GET | `/api/students/{id}` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | Sample id returned JSON 200 with `data`. |
| GET | `/api/issues` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `data`, `links`, `meta`. |
| POST | `/api/issues` | `auth:sanctum`, `role:admin,staff` | staff/admin | Registered, not mutation-tested | Legacy issue-create endpoint. Avoided because it creates issue records. |
| GET | `/api/issues/student/{studentId}` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | Sample id returned JSON 200. |
| GET | `/api/issues/{id}` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | Sample id returned JSON 200. |
| POST | `/api/issues/return/{id}` | `auth:sanctum`, `role:admin,staff` | staff/admin | Registered, not mutation-tested | Avoided because it returns books and may create fines. |
| GET | `/api/overdue` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `data`, `links`, `meta`. |
| GET | `/api/fines` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `data`, `links`, `meta`. |
| GET | `/api/fines/student/{id}` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | Sample id returned JSON 200. |
| GET | `/api/book-requests` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `data`, `links`, `meta`. |
| GET | `/api/book-requests/{id}` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | Sample id returned JSON 200. |
| POST | `/api/book-requests/{id}/approve` | `auth:sanctum`, `role:admin,staff` | staff/admin | Registered, not mutation-tested | Avoided because it changes request state. |
| POST | `/api/book-requests/{id}/reject` | `auth:sanctum`, `role:admin,staff` | staff/admin | Registered, not mutation-tested | Avoided because it changes request state. |
| GET | `/api/staff/students` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `success`, `message`, `data`, `meta`. |
| GET | `/api/staff/students/search` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `success`, `message`, `data`, `meta`. |
| GET | `/api/staff/students/{student}` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | Sample id returned JSON 200. |
| GET | `/api/staff/students/{student}/issue-privileges` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | Sample id returned JSON 200. |
| GET | `/api/staff/books/search` | `auth:sanctum`, `role:admin,staff` | staff/admin | Issue | Live endpoint returned JSON 500 before the PSR-4 import cleanup. Retest after deployment. |
| GET | `/api/staff/issues/search` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `success`, `message`, `data`. |
| POST | `/api/staff/issues/preview` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working validation | Empty-body test returned JSON 422. |
| POST | `/api/staff/issues` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working validation | Empty-body test returned JSON 422. Valid create was not run. |
| POST | `/api/staff/issues/{issue}/return` | `auth:sanctum`, `role:admin,staff` | staff/admin | Registered, not mutation-tested | Avoided because it changes issue/fine data. |
| GET | `/api/staff/returns/settings` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `success`, `message`, `data`. |
| GET | `/api/staff/returns/students/search` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `success`, `message`, `data`. |
| GET | `/api/staff/returns/students/{student}` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | Sample id returned JSON 200. |
| POST | `/api/staff/returns/preview` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working validation | Empty-body test returned JSON 422. |
| POST | `/api/staff/returns` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working validation | Empty-body test returned JSON 422. Valid return was not run. |
| GET | `/api/staff/fines` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `success`, `message`, `data`, `meta`. |
| GET | `/api/staff/fines/summary` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `success`, `message`, `data`. |
| GET | `/api/staff/fines/students` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `success`, `message`, `data`. |
| GET | `/api/staff/fines/students/{student}` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | Sample id returned JSON 200. |
| GET | `/api/staff/fines/{fine}` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | Sample id returned JSON 200. |
| POST | `/api/staff/fines/{fine}/pay` | `auth:sanctum`, `role:admin,staff` | staff/admin | Registered, not mutation-tested | Avoided because it changes payment status. |
| POST | `/api/staff/fines/{fine}/waive` | `auth:sanctum`, `role:admin,staff` | staff/admin | Registered, not mutation-tested | Avoided because it changes fine status. |
| GET | `/api/staff/book-requests/summary` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `success`, `message`, `data`. |
| GET | `/api/staff/book-requests` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | JSON 200 with `success`, `message`, `data`, `meta`. |
| GET | `/api/staff/book-requests/{bookRequest}` | `auth:sanctum`, `role:admin,staff` | staff/admin | Working | Sample id returned JSON 200. |
| POST | `/api/staff/book-requests/{bookRequest}/approve` | `auth:sanctum`, `role:admin,staff` | staff/admin | Registered, not mutation-tested | Avoided because it changes request state. |
| POST | `/api/staff/book-requests/{bookRequest}/reject` | `auth:sanctum`, `role:admin,staff` | staff/admin | Registered, not mutation-tested | Avoided because it changes request state. |

## 7. Common API Routes

| Method | Endpoint | Middleware | Access | Status | Notes |
|---|---|---|---|---|---|
| POST | `/api/logout` | `auth:sanctum` | student/staff/admin | Working | Logout returned JSON 200. The same token returned JSON 401 after logout. |
| GET | `/api/auth/check` | `auth:sanctum` | student/staff/admin | Working | JSON 200 with `authenticated`, `user`, `student_id`, `staff_id`. |
| GET | `/api/profile` | `auth:sanctum` | student/staff/admin | Working | Student and staff tokens returned JSON 200 with `success`, `message`, `data`. No token returned JSON 401. |
| PUT | `/api/profile` | `auth:sanctum` | student/staff/admin | Working validation | Empty body returned JSON 422. |
| DELETE | `/api/profile` | `auth:sanctum` | student/staff/admin | Registered, not mutation-tested | Avoided because it can deactivate/delete accounts. |
| DELETE | `/api/profile/account` | `auth:sanctum` | student/staff/admin | Registered, not mutation-tested | Avoided because it can deactivate/delete accounts. |
| GET | `/api/profile/delete-eligibility` | `auth:sanctum` | student/staff/admin | Working | Student and staff tokens returned JSON 200. |
| POST | `/api/profile/password` | `auth:sanctum` | student/staff/admin | Working validation | Empty body returned JSON 422. |
| POST | `/api/profile/change-password` | `auth:sanctum` | student/staff/admin | Working validation | Empty body returned JSON 422. |
| POST | `/api/profile/photo` | `auth:sanctum` | student/staff/admin | Registered, not mutation-tested | Avoided because it uploads files. |
| DELETE | `/api/profile/photo` | `auth:sanctum` | student/staff/admin | Registered, not mutation-tested | Avoided because it removes files/profile data. |
| GET | `/api/books` | `auth:sanctum` | student/staff/admin | Issue | Live endpoint returned JSON 500 before the PSR-4 import cleanup. Retest after deployment. |
| GET | `/api/books/search` | `auth:sanctum` | student/staff/admin | Working validation | Missing query returned JSON 422. Retest success path with `?q=...` after catalog fix deployment. |
| GET | `/api/books/available` | `auth:sanctum` | student/staff/admin | Issue | Live endpoint returned JSON 500 before the PSR-4 import cleanup. Retest after deployment. |
| GET | `/api/books/category/{category}` | `auth:sanctum` | student/staff/admin | Issue | Live endpoint returned JSON 500 before the PSR-4 import cleanup. Retest after deployment. |
| GET | `/api/books/{id}` | `auth:sanctum` | student/staff/admin | Issue | Live endpoint returned JSON 500 before the PSR-4 import cleanup. Retest after deployment. |
| GET | `/api/categories` | `auth:sanctum` | student/staff/admin | Issue | Live endpoint returned JSON 500 before the PSR-4 import cleanup. Retest after deployment. |
| GET | `/api/dashboard` | `auth:sanctum` | student/staff/admin | Issue | Live endpoint returned JSON 500 before the PSR-4 import cleanup. Retest after deployment. |
| GET | `/api/notifications` | `auth:sanctum` | student/staff/admin | Working | JSON 200 with `data`, `links`, `meta`. |
| GET | `/api/notifications/unread` | `auth:sanctum` | student/staff/admin | Working | JSON 200 with `data`, `links`, `meta`. |
| GET | `/api/notifications/count` | `auth:sanctum` | student/staff/admin | Working | JSON 200 with `unread_count`, `total_count`. |
| POST | `/api/notifications/read-all` | `auth:sanctum` | student/staff/admin | Registered, not mutation-tested | Avoided because it changes read state. |
| POST | `/api/notifications/{id}/read` | `auth:sanctum` | student/staff/admin | Registered, not mutation-tested | Avoided because it changes read state. |
| DELETE | `/api/notifications/{id}` | `auth:sanctum` | student/staff/admin | Registered, not mutation-tested | Avoided because it deletes notification data. |
| GET | `/api/library/settings` | `auth:sanctum` | student/staff/admin | Working | JSON 200 with `message`, `data`. |

## 8. Admin API Routes

| Method | Endpoint | Middleware | Requires Role | Status | Notes |
|---|---|---|---|---|---|
| GET | `/api/admin/dashboard` | `auth:sanctum`, `role:admin` | admin | Access protected | Staff token returned JSON 403. Admin token was not provided, so admin success path was not tested. |
| GET | `/api/activity-logs` | `auth:sanctum`, `role:admin` | admin | Access protected | Staff token returned JSON 403. Admin token was not provided, so admin success path was not tested. |

## 9. Failed or Risky APIs

- `GET /api/books` returned JSON 500 on live hosting.
- `GET /api/books/available` returned JSON 500 on live hosting.
- `GET /api/books/category/{category}` returned JSON 500 on live hosting.
- `GET /api/books/{id}` returned JSON 500 on live hosting.
- `GET /api/categories` returned JSON 500 on live hosting.
- `GET /api/dashboard` returned JSON 500 on live hosting.
- `GET /api/staff/books/search` returned JSON 500 on live hosting.
- The likely code-side portability risk was lowercase model references to `App\Models\book` and `App\Models\category`. These were changed to `App\Models\Book` and `App\Models\Category`. Deploy and retest the 500 endpoints.
- Routes with destructive or state-changing behavior were not live-tested beyond validation where safe.
- `POST /api/login`, `POST /api/forgot-password`, and `POST /api/reset-password` are throttled at 5 requests/minute and may return JSON 429 during repeated Postman testing.

No tested API returned HTML. Unauthenticated access returned JSON 401. Wrong-role access returned JSON 403.

## 10. Hardcoded URL Check

Search terms checked:

```text
localhost
127.0.0.1
10.0.2.2
http://
:8000
```

Findings:

| Area | File/Pattern | Fixed? | Reason |
|---|---|---|---|
| API response helpers | `app/Http/Resources/Concerns/IncludesProfilePhoto.php`, `app/Http/Controllers/Api/Concerns/FormatsStaffStudentPayloads.php` | No code change needed | They use `asset('storage/...')` or preserve already-full `http/https` URLs. No hardcoded localhost response URL was found. |
| Documentation | `docs/mobile-api.md`, `docs/laravel-online-localhost-switch-guide.md`, `docs/mobile-api-documentation.md` | Left as documentation | These contain local development examples and placeholders. |
| Config defaults | `config/database.php`, `config/cache.php`, `config/queue.php`, `config/mail.php`, `config/sanctum.php` | Left as config defaults | Localhost values are Laravel/dev defaults controlled by environment variables, not direct API response URLs. |
| Frontend dev server | `vite.config.js` | Left as dev config | `127.0.0.1` is for local Vite development. |
| Tests | `tests/Feature/...` | Left as tests | Local IPs are test fixtures/expectations. |

Live response scan: tested JSON responses did not contain `localhost`, `127.0.0.1`, `10.0.2.2`, or `:8000`.

## 11. Android Compatibility Notes

- Login response token key: `access_token`.
- Login role location: `user.role`.
- Profile response format: `success`, `message`, `data`.
- Student dashboard response format: `message`, `data`.
- Staff dashboard response format: `success`, `message`, `data`.
- Books response format should be Laravel resource pagination: `data`, `links`, `meta`, but live catalog endpoints currently need retesting after deployment because they returned JSON 500.
- Notification list format: `data`, `links`, `meta`.
- Notification count format: `unread_count`, `total_count`.
- Image/profile URL format uses `asset()`/hosted storage URLs. Ensure production `APP_URL=https://lms.saroj00.com.np`.
- Pagination format is mixed but predictable: Laravel resource collections return `data`, `links`, `meta`; some custom staff endpoints return `success`, `message`, `data`, `meta`.

## 12. Online Hosting Compatibility

- `.env.example` uses `APP_ENV=production`, `APP_DEBUG=false`, and `APP_URL=https://lms.saroj00.com.np`.
- `public/.htaccess` exists and includes Authorization header forwarding for Bearer tokens.
- API URL works without `/public`: `https://lms.saroj00.com.np/api/...`.
- Hosting must have `storage` and `bootstrap/cache` writable.
- Run `php artisan storage:link` on hosting if uploaded profile/book images are not publicly visible.
- After deployment, run `php artisan optimize:clear` and `composer dump-autoload -o`.

## 13. Recommended API Test Order

- [ ] POST `/api/login` with student account
- [ ] POST `/api/login` with staff account
- [ ] GET one protected student API with student token
- [ ] GET one protected staff API with staff token
- [ ] Test wrong-role access
- [ ] Test dashboard APIs
- [ ] Test books APIs
- [ ] Test notification APIs
- [ ] Test profile APIs
- [ ] Test logout
- [ ] Test token after logout

## 14. Verification Performed

- `php artisan route:list --path=api --json`: passed, 89 API routes found.
- Live login with student test account: JSON 200, Bearer token returned.
- Live login with staff test account: JSON 200, Bearer token returned.
- Live unauthenticated protected request: JSON 401.
- Live wrong-role checks: JSON 403.
- Live logout checks: token revoked and subsequent request returned JSON 401.
- Syntax check on changed PHP files: passed.
- Targeted tests: `MobileProfileApiTest` passed; `StaffFineApiTest` has one existing data-expectation failure where local summary total was `165` instead of expected `400`.
