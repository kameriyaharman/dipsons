# Dipsons — Pollution & Insurance Centre

Website + customer app (installable Android web app / PWA) + admin panel for Dipsons, NRL Bharat Petrol Pump, Simlaguri, Baksa, Assam.

Plain **PHP 8.1+ and MySQL** — no framework and no Composer, so the same code runs on Railway and on any Linux shared hosting (cPanel / Hostinger etc.).

| Part | URL |
|---|---|
| Website | `/` · `/services` · `/insurance` (any service) · `/contact` · `/about` |
| Customer app | `/app` (mobile number login, no OTP) — "Add to Home screen" installs it |
| Admin panel | `/admin` |

## Services
19 services in 3 groups, all editable in **Admin → Services & fees**:
- **Vehicle documents & RTO:** Insurance, Pollution (PUC), Road Tax, Fitness, RC Renewal, Name / Ownership Transfer, Permit, National Permit, Trust Permit, All RTO Work, Driving Licence
- **FASTag, GPS & safety devices:** FASTag, GPS Tracker, SLD (Speed Limiter), VLTD
- **Repair & home services:** Vehicle Mechanic, Garage / Vehicle Repairing, Car Service, Plumber

## How it installs itself
On the first request the app creates all tables (`src/schema.sql`) and fills in default services, contact details, FAQ and WhatsApp templates (`src/seed.php`). The first admin login is created from `ADMIN_USER` / `ADMIN_PASS`, or — if those are not set — by opening `/admin/setup` once.

## Deploy on Railway
1. New project → add **MySQL** (database template).
2. Add a service from this GitHub repo (it builds the `Dockerfile`).
3. Variables on the app service:
   - `MYSQL_URL` = `${{MySQL.MYSQL_URL}}`
   - `ADMIN_USER`, `ADMIN_PASS` (first login)
4. Add a **volume** mounted at `/var/www/html/storage` (keeps uploaded documents and logins across deploys).
5. Generate a domain → done. Health check path: `/health`.

## Move to Linux shared hosting
1. Upload all files into `public_html` (or a sub-domain folder).
2. Create a MySQL database + user in cPanel.
3. Copy `config.sample.php` → `config.php` and fill in the database details.
4. Make `storage/` writable (755 or 775).
5. Open the site — tables are created automatically. Then log in at `/admin`.

To move existing data from Railway: export the MySQL database (Railway → MySQL → Data, or `mysqldump`) and import it in phpMyAdmin; copy the `storage/uploads` folder from the volume.

`.htaccess` already blocks `src/`, `storage/` and `config.php`, and sends all other URLs to `index.php`. PHP extensions needed: `pdo_mysql`, `fileinfo`, `zip` (zip is only for "Download all documents").

## Local development
```
cp config.sample.php config.php   # fill in local MySQL
php -S localhost:8000 index.php
```

## WhatsApp
Replies and reminders open WhatsApp (wa.me) with the message already typed — staff press send. No paid WhatsApp API is needed. Templates are editable in **Admin → Website content**.
