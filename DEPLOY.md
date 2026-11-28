# Deploying ShuleTech to the live server (aaPanel + Nginx + Cloudflare)

First-time deployment of the full school system to **https://shuletech.co.ke**, replacing the coming-soon page.
Allow about 45 minutes. The coming-soon page stays up until step 6.

> **Do not run `git pull` on the server before step 6.** Pulling replaces the coming-soon page with the app,
> which shows errors until the database and config files from steps 2–5 are in place.

## What you need

The **deploy package** prepared on the development computer, in `backup/deploy/` (never in git):

| File | What it is |
|---|---|
| `shuletech-live.sql.gz` | Clean database: settings, KES currency, 2026-27 session, logos, admin account. No students or test data. |
| `uploads.zip` | Logos, login backgrounds, default photos |
| `config/database.php` | Database connection (you fill in the live password) |
| `config/shuletech_security.php` | Link-signing key, already generated for live |
| `config/api_keys.php` | Optional third-party keys (empty is fine) |
| `LIVE-SECRETS.txt` | Cron key and notes. Keep private. |

In the commands below, replace `SITE` with the site folder, usually `/www/wwwroot/shuletech.co.ke`.

## 1. PHP (aaPanel → App Store)

1. Use **PHP 8.2 or 8.3** (developed and tested on 8.3).
2. In that PHP version → **Install extensions**, make sure these are on:
   `mysqli`, `curl`, `gd`, `mbstring`, `zip`, `fileinfo`, `bcmath`, `intl`, `openssl`.
3. **Website → shuletech.co.ke → PHP version:** select it.

## 2. Database (aaPanel → Databases)

1. **Add database:** name `shuletech`, user `shuletech`, and a **long random password** (aaPanel can generate one). Save the password.
2. Upload `shuletech-live.sql.gz` to a private folder such as `/root/deploy/`, **not** inside `SITE`.
3. Import it:
   ```bash
   gunzip -c /root/deploy/shuletech-live.sql.gz | mysql -u shuletech -p shuletech
   ```

## 3. Config files

Copy the three files from `config/` into `SITE/application/config/`, then edit `database.php`:
```php
'username' => 'shuletech',
'password' => 'THE PASSWORD FROM STEP 2',
'database' => 'shuletech',
```
These files are git-ignored, so `git pull` never overwrites them.

## 4. Uploads

Upload `uploads.zip` to `SITE` and unzip it there. It creates `SITE/uploads/...`:
```bash
cd SITE && unzip -o uploads.zip && rm uploads.zip
```

## 5. Nginx rules (Website → shuletech.co.ke → URL rewrite)

Replace the contents with the following and save. Apache `.htaccess` files are ignored by Nginx, so these rules do that job.
```nginx
location ~ ^/(application|system|backup|temp)/ { deny all; return 403; }
location ~ /\.(?!well-known/) { deny all; return 404; }
location ~* ^/uploads/.*\.(php\d?|phtml|pht|phar|phps|pgif|shtml?|cgi|pl|py|sh|asp|aspx|jsp)$ { deny all; return 403; }
location ~* \.(md|sql|gz|log|bak|sh)$ { deny all; return 404; }
location / { try_files $uri $uri/ /index.php?/$request_uri; }
```

## 6. Pull the code (this switches the site live)

```bash
cd SITE
git config --global --add safe.directory SITE
git pull origin main
chown -R www:www SITE
chmod -R 775 SITE/uploads SITE/temp SITE/application/cache SITE/application/logs SITE/backend/captcha_images
```
The old coming-soon `index.html` is removed by the pull. `index.php` serves the landing page at `/`.

## 7. HTTPS

- Cloudflare **SSL/TLS mode: Full (strict)** with a valid certificate on the server (aaPanel Let's Encrypt or a Cloudflare Origin certificate).
- aaPanel **Force HTTPS: on.**

The app detects HTTPS behind Cloudflare automatically. It only answers to `shuletech.co.ke` and `www.shuletech.co.ke`, so add any new domain in `application/config/config.php` (`$st_allowed_hosts`).

## 8. Check it works

1. `https://shuletech.co.ke/`: landing page.
2. `https://shuletech.co.ke/site/login`: staff login. Sign in with the admin e-mail and your usual password.
3. Open the Dashboard, General Settings and Student Admission pages and check they load without errors.
4. Security checks (each should be **blocked**: 403 or 404):
   `https://shuletech.co.ke/application/config/database.php`, `https://shuletech.co.ke/.git/config`, `https://shuletech.co.ke/DEPLOY.md`.

## 9. Cron jobs (aaPanel → Cron)

Use the cron key from `LIVE-SECRETS.txt` (it is already saved in General Setting):

| Task | Schedule | Type: URL |
|---|---|---|
| Backups, fee & event reminders | Daily, 06:00 | `https://shuletech.co.ke/cron/KEY` |
| Scheduled SMS / e-mails | Every 5 minutes | `https://shuletech.co.ke/cron/schedulesmsemails/KEY` |

## 10. After go-live

- **Settings → General Setting:** school name, address, phone, e-mail.
- **Settings → Logo:** for the dark-mode logo, also place `shuletech-logo-white.png` / `shuletech-mark-white.png` next to the uploaded logos (already included in `uploads.zip`).
- **Payment Methods:** enter Paystack and/or M-Pesa (Daraja) keys, then tick them. The M-Pesa callback URL is `https://shuletech.co.ke/mpesa_callback/stk/…` and is generated automatically.
- **Optional:** the Firebase key for mobile app push notifications goes in `application/third_party/firebase_notification_key.json`. Without it, app pushes are skipped; SMS and e-mail still work.
- Delete `/root/deploy/` once everything works.

## Rolling back

If something goes wrong after step 6, put the coming-soon page back while you investigate:
```bash
cd SITE && git checkout 62685c2 -- index.html && mv index.php index.php.off
```
Undo with `mv index.php.off index.php && rm index.html`.
