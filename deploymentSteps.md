# KIMS Deployment to cPanel — Full Steps & Modifications

Deployed to `https://kimsbd.online` via cPanel File Manager (no FTP/SSH). This
document records both the modifications made to the codebase to make this
deployment path work, and the exact steps to repeat it (e.g. redeploying, or
setting up a second environment).

## Part 1 — Code/config modifications made

These were real fixes uncovered while deploying, not just documentation:

1. **`config/config.php`** — added a small dependency-free `.env` loader.
   Previously `config/.env` was silently ignored (nothing ever read it into
   `$_ENV`), so `APP_URL`/`APP_DEBUG`/mail settings were hardcoded and DB
   credentials from `.env` were never actually used. Now `.env` is parsed on
   every request before constants are defined.

2. **`.htaccess`** — hardened and fixed for the reality that cPanel's
   document root ends up being the whole repo (not just `public/`):
   - Blocks direct browser access to `config/`, `src/`, `migrations/`,
     `logs/`, `vendor/`.
   - Blocks dev-only helper scripts (`install.php`, `test-db.php`,
     `check-*.php`, etc.) if left on the server.
   - Blocks serving `.sql`, `.md`, `.log`, `.lock`, `.json`, `.gitignore`
     files directly.
   - **Fixed a routing bug**: the original rewrite condition excluded real
     directories (`!-d`) from being rewritten — but the site root `/` *is* a
     directory, so hitting `https://kimsbd.online/` fell through to
     LiteSpeed's raw directory listing instead of the app. Added an explicit
     `RewriteRule ^$ public/index.php` for the root, plus `Options -Indexes`
     as a backstop against directory listings anywhere else.
   - `migrate.php` is deliberately left un-blocked, since it's meant to be
     hit once via browser after deployment, then deleted.

3. **`migrate.php`** — fixed two bugs:
   - The SQL statement splitter split `migrations/*.sql` on `;` and
     discarded any statement whose block happened to start with a
     `-- comment` line — which was nearly every `CREATE TABLE` in
     `001_initial_schema.sql` (each preceded by a comment). This silently
     dropped every table creation while still reporting success.
   - It only did `require 'config/database.php'`, never
     `require 'config/config.php'` — so the `.env` loader (which lives in
     `config.php`) never ran, and `migrate.php` always connected with the
     hardcoded local-dev fallback (`root`/empty password) instead of the
     real production credentials. Fixed by requiring `config.php` first.
   success. Fixed to strip full-line comments before splitting.

4. **`migrations/001_initial_schema.sql`** — hardcoded
   `CREATE DATABASE IF NOT EXISTS inventory_mgmt; USE inventory_mgmt;`.
   Updated to the real database name (`kimsbdon_inventory` for this
   deployment).

5. **`config/database.php`** / **`config/.env.example`** — default DB name
   updated to `kimsbdon_inventory`; `.env.example` updated with cPanel-style
   placeholder values and `APP_DEBUG=false` as the production default.

6. **`deployment/build-package.ps1`** (new) — packaging script that builds
   the upload zip. Went through two bugs before it worked:
   - First version used `Compress-Archive`, which stores **backslash** path
     separators in the zip (`src\Views\login.php`) instead of the
     ZIP-spec-required forward slash. Linux extractors (including PHP's
     `ZipArchive`, which is what cPanel File Manager uses) don't treat `\`
     as a directory separator, so every nested file — all 23 view files
     under `src/Views/`, everything in `config/`, `vendor/`, etc. —
     extracted as a mangled flat-named file instead of into real folders.
     This is what caused the live `View not found: auth/login` error.
   - Switching to .NET's `ZipFile::CreateFromDirectory` did **not** fix it —
     it produces the same backslash-separated entries on Windows.
   - Final fix: build the zip **manually**, entry by entry, via
     `ZipArchive.CreateEntry()` with each relative path explicitly
     converted to forward slashes. Verified with PHP's `ZipArchive` (the
     same extractor cPanel uses) that real nested folders are produced.

7. **`deployment/DEPLOYMENT.md`** (new) — the general step-by-step guide
   (superseded in explanatory detail by this file, but still the quick
   reference for future deploys).

8. **`.htaccess`** — fixed a static-asset 404 bug found after going live
   (symptom: sidebar rendered as plain stacked content at the top of every
   page on mobile — because `public/css/style.css` was 404ing, so its
   off-canvas sidebar CSS never loaded at all). The app's views reference
   assets with root-relative paths (`/css/...`, `/js/...`, `/favicon.svg`,
   `/uploads/...`), assuming `public/` itself is the document root. Since
   this deployment puts the whole repo at the document root instead (only
   dynamic routes get rewritten to `public/index.php`), every static asset
   request 404'd. Added explicit rewrite rules mapping
   `/css|js|images|uploads/*` and `/favicon.svg` to their real location
   under `public/`.

9. **`deployment/build-package.ps1`** — stopped creating a phantom
   top-level `uploads/` directory. The app actually writes uploads to
   `public/uploads/products/` (already included via the `public/` copy);
   the top-level one was dead weight. Also excludes the leftover
   `public/test-login-fail.png` debug screenshot from the package.

## Part 2 — Step-by-step deployment procedure

### 1. Build the upload package (local machine)

```powershell
powershell -ExecutionPolicy Bypass -File deployment\build-package.ps1
```

Produces `deployment\dist\kims-deploy.zip` (~5.2MB) containing `public/`,
`src/`, `config/` (no `.env`), `migrations/`, `vendor/`, `migrate.php`,
`.htaccess`, and empty `uploads/`/`logs/` dirs, all with correct forward-slash
paths. Dev-only files are excluded.

### 2. Create the database in cPanel

1. cPanel → **MySQL® Databases**.
2. **Create New Database**: `inventory` → cPanel prefixes it (e.g.
   `kimsbdon_inventory`).
3. **MySQL Users → Add New User**: create a user + strong password →
   cPanel prefixes it (e.g. `kimsbdon_dbuser`).
4. **Add User To Database**: add that user to the database with
   **ALL PRIVILEGES**. (Easy to forget this step — creating the DB and user
   separately does *not* automatically link them, and skipping it produces
   an `Access denied for user ...` error later.)
5. Note the three full prefixed values for step 4 below.

### 3. Upload and extract

1. cPanel → **File Manager** → the domain's document root (`public_html`
   for the primary domain).
2. **Upload** `kims-deploy.zip` → **Extract** into the current directory →
   delete the zip afterward.

Resulting structure directly under the document root:

```
public_html/
├── .htaccess
├── config/
│   ├── config.php
│   ├── database.php
│   └── .env.example
├── migrations/
│   ├── 001_initial_schema.sql
│   └── 002_all_updates.sql
├── migrate.php        (one-time use, delete after running — step 5)
├── public/            (actual document root the browser hits)
├── src/
├── uploads/           (needs to be writable — step 6)
├── logs/              (needs to be writable — step 6)
└── vendor/
```

### 4. Configure the environment

In `config/`, copy `.env.example` to `.env`, then edit it:

```
DB_HOST=localhost
DB_PORT=3306
DB_USER=kimsbdon_dbuser
DB_PASSWORD=<password from step 2>
DB_NAME=kimsbdon_inventory

APP_DEBUG=false
APP_URL=https://kimsbd.online
```

### 5. Run the migrations

**Option A — phpMyAdmin import (recommended):**
phpMyAdmin → select the database → **Import** → `migrations/001_initial_schema.sql`
→ Go. Repeat for `002_all_updates.sql`.

**Option B — browser-run `migrate.php`:**
Visit `https://kimsbd.online/migrate.php` once (idempotent, tracks progress
in a `migrations` table) → **delete `migrate.php` from the server
immediately after**.

Verify in phpMyAdmin: 9 tables present (`users`, `products`,
`product_variants`, `orders`, `order_items`, `expenses`,
`stock_adjustments`, `password_reset_tokens`, `migrations`), with a seeded
admin row in `users`.

### 6. Set folder permissions

`public/uploads/` (where the app actually writes product images) and
`logs/` → 755 (bump to 775 only if writes fail).

### 7. Verify and lock down

1. Visit `https://kimsbd.online/auth/login`, log in with
   `admin@jerseystore.com` / `admin123`.
2. **Change the admin password immediately.**
3. Confirm `migrate.php` was deleted — should 404.
4. Confirm internals aren't browsable — `config/database.php` and
   `migrations/001_initial_schema.sql` should both return 403.
5. Confirm the root URL loads the app (redirects to login) rather than
   showing a directory listing.
6. Confirm static assets actually load — `curl -I https://kimsbd.online/css/style.css`
   should return 200, not 404. If it 404s, the sidebar (and other layout
   CSS) silently fails to load, which shows up on mobile as the sidebar
   nav rendering as plain stacked content at the top of the page instead
   of a collapsible off-canvas menu.

## Future migrations

Add new `.sql` files to `migrations/`, list them in `migrate.php`'s
`$migrations` array, then repeat step 5. The `migrations` tracking table
skips anything already applied.

## Future redeploys / code updates — manual (zip)

Re-run `deployment\build-package.ps1`, upload the new zip, extract over the
existing files (this will not touch `config/.env`, `uploads/`, or `logs/`
since those aren't in the package). Re-run any new migrations the same way.

## Future redeploys / code updates — automated (cPanel Git Version Control)

This repo has a GitHub remote (`https://github.com/Aurpan/kims.git`) and a
`.cpanel.yml` at the repo root, so cPanel's **Git™ Version Control** feature
can pull and deploy without manually zipping/uploading anything.

**What changed to make this possible:**
- `.cpanel.yml` (new) — defines the deployment tasks cPanel runs when you
  click "Deploy HEAD Commit". It copies the same curated subset
  `deployment/build-package.ps1` already used — `public/`, `src/`,
  `migrations/`, `config/config.php` + `config/database.php` (never
  `.env`), `.htaccess`, `migrate.php` — into `/home/kimsbdon/public_html`.
  Dev-only files (`test-db.php`, `install.php`, `node_modules/`, docs,
  etc.) are never part of the copy, same as the zip approach.
- `vendor/` stays **out of git** (still gitignored) and out of
  `.cpanel.yml`'s copy list entirely. It's already sitting correctly on the
  server from the earlier manual zip upload, and it only changes when a
  Composer dependency is added/updated — a rare, deliberate event. Rather
  than committing ~2,300 third-party files to a public repo (or gambling on
  `composer` being available in cPanel's deploy shell), automated deploys
  simply leave `vendor/` alone. If you ever add a dependency: run
  `composer install` locally, then re-upload just the `vendor/` folder once
  via File Manager (or rebuild and use the zip for that one deploy) — the
  automated git-based flow resumes normally for every deploy after that.

**One-time setup in cPanel:**
1. cPanel → **Git™ Version Control** → **Create**.
2. Clone URL: `https://github.com/Aurpan/kims.git`.
3. Repository Path: something **outside** the web root, e.g.
   `/home/kimsbdon/repositories/kims` (do **not** set this to `public_html`
   — keeping the git checkout separate means `.git/`, dev scripts, docs,
   etc. are never inside the docroot at all, not even behind `.htaccess`).
4. Create. cPanel will detect `.cpanel.yml` automatically.

**Every time you want to deploy a change:**
1. Commit and `git push` to GitHub from your local machine, as normal.
2. In cPanel → Git™ Version Control → **Manage** the repo → **Pull or
   Deploy** tab → **Update from Remote** (fetches the new commit).
3. Click **Deploy HEAD Commit** — this runs the `.cpanel.yml` tasks, copying
   the updated files into `public_html`.

This never touches `config/.env`, `public/uploads/`, or `logs/` on the
server (they aren't part of the copy), so production config and
customer-uploaded images are safe across deploys. New DB migrations still
need a manual run (phpMyAdmin import or `migrate.php`, per above) — schema
changes are deliberately not automatic.
