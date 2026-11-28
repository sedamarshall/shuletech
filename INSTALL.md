# Installing ShuleTech

ShuleTech ships as one zip with a web installer. It works on any PHP hosting (aaPanel, cPanel, Plesk, a VPS).

## 1. Prepare the server

- **PHP 8.2 or 8.3** with these extensions: `mysqli`, `curl`, `gd`, `mbstring`, `zip`, `fileinfo`, `bcmath`, `openssl`, `json`, `zlib`
- An **empty MySQL / MariaDB database** and a user with full rights on it
- **HTTPS** for the domain (e.g. Let's Encrypt, or Cloudflare in Full (strict) mode)

## 2. Upload

Upload `shuletech-<version>.zip` to the site's web root (e.g. `/www/wwwroot/yourschool.co.ke` or `public_html`) and extract it there.
The zip has no top folder, so `index.php` must end up directly in the web root.

Make these writable by the web server: `application/config`, `application/cache`, `application/logs`, `uploads`, `temp`, `backend/captcha_images`.
On aaPanel / Linux:
```bash
chown -R www:www .  &&  chmod -R 775 application/config application/cache application/logs uploads temp backend/captcha_images
```

## 3. Web server rules

**Apache / cPanel:** nothing to do; the included `.htaccess` files handle it.

**Nginx / aaPanel** (Website → your site → URL rewrite), paste:
```nginx
location ~ ^/(application|system|backup|temp)/ { deny all; return 403; }
location ~ ^/install/sql/ { deny all; return 403; }
location ~ /\.(?!well-known/) { deny all; return 404; }
location ~* ^/uploads/.*\.(php\d?|phtml|pht|phar|phps|pgif|shtml?|cgi|pl|py|sh|asp|aspx|jsp)$ { deny all; return 403; }
location ~* \.(md|sql|gz|log|bak|sh)$ { deny all; return 404; }
location / { try_files $uri $uri/ /index.php?/$request_uri; }
```

## 4. Run the installer

Open your domain in a browser. You are taken to **/install**:

1. **Requirements:** fix anything marked red, then continue.
2. **Database:** enter the database host, name, user and password.
3. **School & admin:** enter the school name and contact details, the site address, the time zone and the super admin's name, e-mail and password.
4. **Finish:** note the **cron job URLs** shown on this page.

The installer creates the tables, writes the config files with fresh security keys, and locks itself.

## 5. After installing

- **Delete the `install` folder.**
- Add the two **cron jobs** from the finish page (hosting panel → Cron, type URL): daily at 06:00, and every 5 minutes.
- Sign in at `/site/login`, then set up classes, sections, fees and staff.
- **Payment methods:** enter Paystack and/or M-Pesa (Daraja) keys and switch them on.
- **Optional:** for mobile-app push notifications, put the Firebase service-account file at `application/third_party/firebase_notification_key.json`.

## Reinstalling

The installer refuses to run on a configured site. To start over, delete `application/config/database.php` and `application/config/installed.lock`, empty the database, and open the site again.

---
*Building a release (developers):* `php application/third_party/build_release.php [version]` creates `dist/shuletech-<version>.zip` from the working tree, with a clean database built from a temporary copy of the local one.
