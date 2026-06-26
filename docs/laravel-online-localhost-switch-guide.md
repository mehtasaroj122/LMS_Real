# Laravel Online / Localhost Switch Guide

## 1. Current Active Mode

The project is currently prepared for online hosting:

```text
https://lms.saroj00.com.np
```

API base:

```text
https://lms.saroj00.com.np/api
```

## 2. Online Hosting Mode

Use online mode when:

- Project hosted on cPanel/server
- Mobile app is using hosted API
- Final demo or real-device testing
- Domain and SSL are active

Recommended production `.env` values without real secrets:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lms.saroj00.com.np

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpanelusername_lms
DB_USERNAME=cpanelusername_lmsuser
DB_PASSWORD=your_database_password

FILESYSTEM_DISK=public
```

Commands:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan migrate --force
php artisan storage:link
```

Use `migrate --force` only if database tables are not already imported. Use `storage:link` for image/file access. Do not keep `APP_DEBUG=true` online.

## 3. Localhost Development Mode

Use localhost mode when:

- Development on local computer
- Testing API in browser/Postman
- Android emulator using local Laravel server
- Running `php artisan serve`

Local `.env` example:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lms
DB_USERNAME=root
DB_PASSWORD=
```

Local command:

```bash
php artisan serve
```

Android emulator uses:

```text
http://10.0.2.2:8000/api
```

but Laravel APP_URL should usually stay:

```text
http://127.0.0.1:8000
```

## 4. How to Switch From Online to Localhost

1. Open `.env`.
2. Change:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
```

3. Change database credentials to local MySQL:

```env
DB_DATABASE=lms
DB_USERNAME=root
DB_PASSWORD=
```

4. Run:

```bash
php artisan optimize:clear
```

5. Start local server:

```bash
php artisan serve
```

6. In mobile app, use:

```text
http://10.0.2.2:8000/api/
```

## 5. How to Switch From Localhost to Online Again

1. Upload latest Laravel code to hosting.
2. Update online `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lms.saroj00.com.np
```

3. Set online database credentials.
4. Run:

```bash
php artisan optimize:clear
php artisan config:cache
```

5. Make sure storage link exists:

```bash
php artisan storage:link
```

6. Test API in Postman:

```text
POST https://lms.saroj00.com.np/api/login
```

7. In mobile app, use:

```text
https://lms.saroj00.com.np/api/
```

## 6. API Testing Checklist

- [ ] Domain opens correctly
- [ ] SSL/HTTPS works
- [ ] `/api/login` works in Postman
- [ ] Login returns access token
- [ ] Protected API works with Bearer token
- [ ] Dashboard API returns JSON
- [ ] Books API returns JSON
- [ ] Profile API returns JSON
- [ ] Logout API works
- [ ] Validation errors return JSON
- [ ] Unauthenticated requests return JSON 401
- [ ] Images/files return full online URL
- [ ] No active hardcoded localhost URLs in API responses
- [ ] `APP_DEBUG=false` on hosting
- [ ] `storage` and `bootstrap/cache` are writable

## 7. Common Problems and Fixes

### API returns 404

Possible causes:

- Route missing in `routes/api.php`
- Wrong domain path
- Hosting points to wrong folder
- `.htaccess` problem

### API returns 500

Possible causes:

- Wrong database credentials
- Missing APP_KEY
- Storage/cache permission issue
- Config cache old
- Missing PHP extension

### Login works but dashboard says unauthenticated

Possible causes:

- Bearer token not sent
- Wrong middleware
- Token deleted/expired
- Android header issue

### Images do not load

Possible causes:

- Storage link missing
- Backend returns relative/local path
- APP_URL wrong
- File permission issue

### API redirects to login page

Possible causes:

- Route is in `web.php` instead of `api.php`
- API exception handling not returning JSON
- Missing Accept: application/json header

## 8. cPanel Hosting Notes

The domain should point to Laravel's `public` folder. The API should be accessible without `/public` in the URL:

```text
https://lms.saroj00.com.np/api/login
```

If hosting currently requires this URL, the document root is pointing to the wrong folder:

```text
https://lms.saroj00.com.np/public/api/login
```

Safest fix: set the web root to Laravel `public`. Do not expose `.env`, `storage`, `vendor`, or source folders publicly. If cPanel does not allow changing the web root, move only public files into `public_html` and adjust `index.php` paths carefully.

For uploaded images/files, run:

```bash
php artisan storage:link
```

If cPanel does not allow `storage:link`, create the symlink manually if possible. Use `public/uploads` only if the project is already designed for that path.

Required writable folders:

```text
storage/
bootstrap/cache/
```

Recommended permission command:

```bash
chmod -R 775 storage bootstrap/cache
```

If cPanel does not allow command line access, update permissions from File Manager.

## 9. Production Cache and Database Commands

After changing `.env`, always run:

```bash
php artisan optimize:clear
php artisan config:cache
```

Recommended production cache commands:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If `route:cache` fails because of Closure routes, skip route cache.

Database commands:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Only seed if required. If the online database was imported manually from local phpMyAdmin, `migrate --force` may not be needed.
