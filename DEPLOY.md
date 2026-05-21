# G-TechX — Server deployment guide

## Why `/install` appeared

Laravel only showed the landing page when `storage/installed` existed.  
**Migrating the database was not enough** — the app still treated the site as “not installed”.

After the fix: if the DB is migrated + seeded (superadmin + settings), the app **auto-detects** installation and shows the **landing page** at `/`.

---

## One-time server setup (run in project root)

```bash
# 1. Pull code
git fetch origin master
git reset --hard origin/master
# If pull fails due to untracked files:
#   cp .env .env.backup && git clean -fd && git pull origin master

# 2. Dependencies
composer install --no-dev --optimize-autoloader

# 3. Environment
cp .env.example .env   # only if .env missing
nano .env              # set APP_URL, DB_*, APP_KEY

# Generate key if needed:
php artisan key:generate

# 4. Database
php artisan migrate --force
php artisan db:seed
php artisan brand:sync

# 5. Mark installed (optional — auto-detected after seed)
php artisan app:mark-installed

# 6. Storage link (exec() often disabled on shared hosting)
ln -sfn "$(pwd)/storage/app/public" "$(pwd)/public/storage"

# 7. Permissions
chmod -R 775 storage bootstrap/cache

# 8. Cache
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## `.env` minimum (production)

```env
APP_NAME=G-TechX
APP_ENV=production
APP_DEBUG=false
APP_URL=https://globalxtech.flygoride.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password

# Optional demo data in seeders
RUN_DEMO_SEEDER=false
```

---

## URLs after setup

| URL | Result |
|-----|--------|
| `/` | G-TechX landing page |
| `/login` | Login |
| `/install` | Redirects to `/` (landing) |
| `/dashboard` | App dashboard (after login) |

---

## Common issues

### `exec()` disabled — `php artisan storage:link` fails

Use manual symlink:

```bash
ln -sfn /full/path/to/project/storage/app/public /full/path/to/project/public/storage
```

### Still redirects to `/install`

```bash
php artisan migrate --force
php artisan db:seed
php artisan app:mark-installed
php artisan config:clear
```

### 500 error

```bash
tail -f storage/logs/laravel.log
chmod -R 775 storage bootstrap/cache
```

Ensure `APP_KEY` is set in `.env`.

---

## Git pull blocked by untracked files

```bash
cp .env .env.server-backup
git clean -fdn          # preview
git clean -fd           # removes untracked (keeps .env — in .gitignore)
git pull origin master
cp .env.server-backup .env
```

---

## Useful commands

```bash
php artisan app:mark-installed
php artisan brand:sync
php artisan migrate --force
php artisan db:seed
php artisan db:seed --class=DefultSetting
php artisan package:seed
```
