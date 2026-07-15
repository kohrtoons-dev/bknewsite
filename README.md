# BK Traders site structure

- `index.php` — page shell and include order
- `includes/` — one file per page section; Daily Intel reads cached macro headlines in `ticker.php`
- `assets/css/styles.css` — site styles, responsive layout, sticky header and ticker
- `assets/js/site.js` — header scroll state, accessible mobile navigation, ticker loop and reveal effects
- `images/` — localized site images and media logos
- `images/hero-market.jpg` — responsive hero artwork with the display headline embedded
- `_old/bktraders_home_v34.html` — untouched original

## Daily Intel cron setup (cPanel LAMP)

The public page reads `storage/cache/ticker.json`. Only the cron script contacts
the Federal Reserve and ECB feeds, so a slow or unavailable feed never delays a
visitor's page request. Failed updates preserve the last successful cache.

Required PHP extensions: cURL, SimpleXML/libxml and JSON. These are normally
enabled on cPanel PHP installations.

1. In cPanel Terminal, test the updater with the full account path:

   `/usr/local/bin/php -q /home/CPANEL_USER/public_html/scripts/update-ticker-cache.php`

   Some hosts use `/usr/bin/php` instead. `which php` shows the correct path.

2. Confirm that the command reports `Ticker cache updated`.

3. In cPanel > Cron Jobs, select `Once Per Five Minutes` and use:

   `/usr/local/bin/php -q /home/CPANEL_USER/public_html/scripts/update-ticker-cache.php`

4. Keep `storage` and `storage/cache` writable by the account (normally `755`).
   The included `storage/.htaccess` blocks web access to cached files.

The generated cache and lock files are intentionally ignored by Git.

This cron integration updates the macro-headline portion of Daily Intel. The ES,
NQ, Gold and EUR/USD values remain static until an authorized market-data widget
or licensed public-display feed is connected.
