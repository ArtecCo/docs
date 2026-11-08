# Araha Docs — SpidyHost installation

## Folder layout

Upload the repository contents directly into the subdomain's existing document-root folder:

```
your-subdomain/
├── index.php
├── api.php
├── config.php
├── .htaccess
├── includes/
│   ├── functions.php
│   └── .htaccess
├── assets/
│   ├── style.css
│   ├── app.js
│   └── icon.svg
├── storage/
│   └── .htaccess
├── database.sql
├── manifest.webmanifest
└── sw.js
```

The document files are stored in `storage/`. That directory is inside the subdomain folder, but its own Apache access rules deny direct web requests. All previews and downloads go through `api.php`.

## Setup

1. Upload the repository files into the subdomain folder.
2. In cPanel → Directory Privacy, enable password protection for the **entire subdomain folder**. This protects the frontend and all PHP endpoints with the same cPanel credentials. Keep it enabled.
3. Ensure the subdomain uses HTTPS.
4. In cPanel → MySQL Databases, create a database and database user, then grant that user all privileges on the database.
5. Import `database.sql` using phpMyAdmin. This creates the `tags` table as well as the document tables. Add tag names manually in phpMyAdmin, for example: `INSERT INTO tags (name) VALUES ('Invoices'), ('Contracts'), ('Personal');`. Upload and edit dialogs load tag choices from this table.
6. Open `config.php` and replace the database name, username and password placeholders with your cPanel database credentials. Do not share this file or commit real credentials.
7. Ensure PHP can write to `storage/`. Start with permissions 0700; if your host requires different permissions, use the narrowest permissions that work.
8. Open the subdomain. Add at least one row to `tags` in phpMyAdmin, then upload a test PDF and image. Test preview, download, rename, tag filtering and deletion.
9. Test direct storage isolation: after uploading a file, copy its generated storage filename from the cPanel File Manager and try opening `https://your-subdomain.example/storage/GENERATED_FILENAME`. It must return HTTP 403 (or 404). Also try opening `https://your-subdomain.example/storage/`; it must not list files.
10. Test opening `https://your-subdomain.example/config.php`, `database.sql`, and `includes/functions.php`. These must not reveal source contents.

## Security model and important checks

- cPanel Directory Privacy is the authentication boundary. Apache authenticates requests before PHP executes. The app intentionally does not depend on `REMOTE_USER`, which is not consistently exposed by shared-hosting PHP handlers.
- `storage/.htaccess` denies direct HTTP access. The application never exposes storage paths in document listings; it streams documents by database ID through PHP.
- The root `.htaccess` denies direct access to configuration and database/install files. `includes/.htaccess` denies access to helper code.
- Uploads are validated using server-detected MIME types. Random storage names are generated; user-provided filenames are never used as disk paths.
- Mutations require POST and a session-bound CSRF token. Database queries use PDO prepared statements.
- The service worker caches only static CSS, JavaScript, icon and manifest assets. It does not cache PHP pages, API responses, previews or downloads.
- **Do not upload confidential documents until the direct-storage URL tests above return 403/404.** This security boundary depends on Apache/LiteSpeed honoring the `.htaccess` rules. If direct requests succeed, contact SpidyHost and do not use this storage arrangement until fixed.
- PHP should have PDO MySQL and Fileinfo enabled. mbstring is recommended. Set PHP `upload_max_filesize` and `post_max_size` to at least 16M.
- The app's default maximum file size is 15 MB; change `max_upload_bytes` in `config.php` if needed, while keeping PHP limits aligned.
- Back up the MySQL database and `storage/` directory together.

## Scope

This is a simple shared document library for people who know the cPanel directory password. It does not include separate user accounts, per-user roles, public share links, version history, OCR or audit logging. The PWA requires HTTPS. PDF preview depends on the browser's built-in PDF support.
