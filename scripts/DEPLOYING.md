# Deploying BK Traders from staging to production

The deployment script keeps two site copies:

- Staging: `/home/bktraders/newsite.bktraders.com`
- Live: `/home/bktraders/public_html`

It preserves the live `.htaccess`, `.well-known`, `cgi-bin` and `duckrace`
paths. It also
excludes development-only folders, lock files, and the deployment utility itself
so its server paths are not published through the live website.

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
