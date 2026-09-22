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

## Managing server .htaccess files

The root `.htaccess` is intentionally untracked and ignored by Git. Each server
keeps its own configuration, including any cPanel PHP settings or staging access
protection. The deployment script also excludes the live `.htaccess`.

Do not copy the staging `.htaccess` over production automatically. Make routing
and error-page changes directly in the intended server's configuration after
backing it up outside the public document root. Deploy `404.html` before setting
`ErrorDocument 404 /404.html`.

After editing live routing, verify the homepage and legal/broker pages, legacy
redirects, `/duckrace/`, and `/api/market-quotes.php`. Missing URLs should return
404 with the small error page. Preserve cPanel-managed PHP settings.
