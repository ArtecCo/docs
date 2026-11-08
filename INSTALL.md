# Araha Docs — installation

## Hosting layout

Set the subdomain document root to the repository's `public/` directory. Do **not** point the subdomain at the repository root.

Expected layout:

```
/home/ACCOUNT/docs/
  config.php                 # private; outside public web root
  private-documents/         # private file storage
  database.sql
  public/                    # subdomain document root
    index.php
    api.php
    assets/
```

1. Upload the repository contents to a directory outside `public_html` if your hosting account permits it.
2. Set the subdomain document root to the `public` directory.
3. In cPanel Directory Privacy, protect the entire subdomain document root. Use HTTPS.
4. Create a MySQL database and user in cPanel; grant the user all privileges on that database.
5. Import `database.sqlphpMyAdmin.
6. Copy `config.example.php` to `config.php` in the parent directory. Update database credentials. Set a private writable storage path outside the web root.
7. Ensure the storage directory is writable by PHP (start with 0700; adjust only as required by your host).
8. Confirm that a direct URL to a guessed storage file is impossible because storage is outside the document root.
9. Test upload, preview, download, rename, tag filtering and delete.

## Security notes

- Directory Privacy provides the outer HTTP Basic Authentication gate. PHP checks `REMOTE_USER` on each request; verify this is populated by your host. If your Apache/PHP setup does not expose it, do not simply disable the check—ask SpidyHost how authenticated requests are exposed to PHP or use application-level login.
- Keep `config.php` and `private-documents/` outside the subdomain document root. If your hosting plan forces all files inside `public_html`, create them in a sibling directory and verify no URL maps to them.
- The application only accepts PDF, JPEG, PNG, GIF and WebP based on server-detected MIME type. Upload size is limited to 15 MB by application config; PHP's `upload_max_filesize` and `post_max_size` must be at least that large.
- Keep PHP updated. Use HTTPS. Back up the database and private storage together.
- Service worker caches only the app shell, never API responses or documents. Private documents are streamed with `Cache-Control: no-store`.
- cPanel Basic Auth is not a complete account/session management system. Protect all paths, including `api.php`, and verify the host's auth behavior before storing sensitive documents.

## Current limitations

This first version is intentionally single-library and intended for one trusted cPanel-authenticated user/group. It does not provide per-user permissions, audit history, versioning, OCR, or sharing links. The PWA requires HTTPS. Browser PDF support varies by device.
