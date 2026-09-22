# Deploying BK Traders from staging to production

The deployment script keeps two site copies:

- Staging: `/home/bktraders/newsite.bktraders.com`
- Live: `/home/bktraders/public_html`

It preserves the live `.htaccess`, `.well-known`, `cgi-bin` and `duckrace`
paths. It also
excludes development-only folders, lock files, and the deployment utility itself
so its server paths are not published through the live website. The root
`README.md` and `DASHBOARD-HANDOFF.md` files also remain staging-only.

## 1. Preview the deployment

Run this from cPanel Terminal or SSH:

```bash
bash /home/bktraders/newsite.bktraders.com/scripts/deploy-live.sh
```

This is a dry run. Nothing is changed. Lines reporting deletions should be
reviewed carefully, especially if another domain or application has been placed
inside `public_html`.

## 2. Publish the approved staging site

```bash
bash /home/bktraders/newsite.bktraders.com/scripts/deploy-live.sh --deploy
```

The live directory is synchronized to staging. Files that no longer exist in
staging are removed from live, except for the explicitly protected root paths.
The script also checks for leftover WordPress directories and reports a warning
instead of claiming a clean deployment when they remain.

## 3. After the first launch

- Confirm the production `.htaccess` still contains the required legacy URL redirects.
- Remove production password protection if it is still enabled.
- Test the homepage, legal pages, broker pages, CTA links and legacy redirects.
- Check `/robots.txt` and `/sitemap.xml` on the main domain.
- Point the market-data cron jobs at the live scripts so the production ticker cache stays current.

Suggested live cron commands:

```bash
/usr/local/bin/ea-php81 /home/bktraders/public_html/scripts/update-market-quotes.php
/usr/local/bin/ea-php81 /home/bktraders/public_html/scripts/update-ticker-cache.php
```

Use the PHP CLI path shown in cPanel if it differs from the example above.

## Missing pages and crawler traps

Deploy the lightweight root `404.html` before adding this directive to the live
`.htaccess` (the deployment script preserves that file):

```apache
ErrorDocument 404 /404.html
```

Use the local path shown above, not a full URL, so missing requests keep their
404 status. The page has inline styles and no scripts, images or external fonts.

Remove the obsolete WordPress catch-all that rewrites nonexistent paths to
`/index.php`. Preserve legitimate redirects, cPanel PHP settings and rules needed
by other applications. Keep a backup outside the public document root.

Verify a real page still returns 200 and a made-up nested URL returns 404 with
the new error page. If stale successful responses persist, check the active
server/CDN caches and inherited error handling. Compare equal-duration access
log windows after the change; old crawler requests can continue after the fix.

## Installing the clean root .htaccess

The repository root `.htaccess` replaces the supplied legacy WordPress/W3TC
configuration. It preserves the supplied cPanel PHP settings and handler,
authorization forwarding, retired WordPress directory 404s and Referrer-Policy.
It uses native missing-file handling, a local 404 document, text compression,
one-hour CSS/JS caching and 30-day image/font caching. It removes WordPress page
cache and AVIF/WebP negotiation rules; original image URLs serve their original
files. Existing directory-based PHP redirects remain in place.

The live `.htaccess` is still excluded from automatic deployment. Update staging
from Git, deploy `404.html`, then install the reviewed configuration manually.
If cPanel added staging-only authentication or newer PHP settings, preserve those
before updating staging. Compare live settings again if they changed since the
supplied configuration was captured.

From cPanel Terminal, after deploying the 404 page:

```bash
# Keep the backup outside the public document root.
cp /home/bktraders/public_html/.htaccess /home/bktraders/htaccess-before-cleanup-$(date +%Y%m%d-%H%M%S).bak
cp /home/bktraders/newsite.bktraders.com/.htaccess /home/bktraders/public_html/.htaccess
```

Confirm homepage and legal/broker pages return 200, existing legacy redirects
return 301, and both random and deeply nested missing URLs return 404 with the
small error page. Check `/duckrace/` and `/api/market-quotes.php` still work as
expected. Parent/vhost configuration and subdirectory applications can override
behavior, so local review does not replace these server checks. To roll back,
copy the timestamped backup over the live `.htaccess`.
