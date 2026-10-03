# Ohnisko Menu PDF architecture

WordPress is the only source of structured menu content. ACF Free stores menu item data; WordPress renders the complete protected print document. Paged.js paginates the flowing HTML to A4. The separate Node/Chromium runtime only opens that page, waits for the readiness contract, and prints a PDF. The worker claims and updates jobs; it contains no menu data or layout.

## ACF Free menu item fields

The WordPress title is the product name. ACF fields are `menu_section`, `sort_order`, `description`, `note`, `details`, `portion`, `origin`, `allergens`, `badge`, `spiciness`, price fields (`price_type`, `price_amount`, `price_unit`, `price_custom`), `active`, and the narrowly scoped `meta_order` display exception for the one existing item whose allergen/portion order differs. There is no duplicate name override and no page, column, coordinate, HTML, or PDF layout controls. The editor changes product data, category, and order only.

`menu_section` selects one of the fixed logical section definitions in `includes/menu.php`. That definition is the single PHP source for section keys, labels, display order, and ACF choices. There is no editable `Menu → Sections` taxonomy. Items are sorted by `sort_order` and post ID. Only published items with `active=1` are rendered, and a section with no active published items is omitted entirely. Section subtitles, shared Josper sauce price, BBQ copy, the order-only promotion, dog menu, allergen note, review and social footer remain renderer configuration/template content, never item data.

## Print contract

`/?ohnisko_print=1&token=…` returns the full current menu HTML only for a valid token and is marked noindex/no-cache. WordPress and Node both read the token from the private runtime `config.json` `print_token`; there is no WordPress option copy. A missing/invalid token returns 403. The token is never stored in Git or printed in logs. It never creates a PDF. CSS, Paged.js, fonts, logo variants, pattern, and chilli icon are local plugin assets; print generation has no runtime asset dependency on external hosts.

The PHP admin, print endpoint, public current PDF, and CRON worker all use `includes/runtime.php` for the runtime directory. It is computed from the plugin location (`dirname(OHNISKO_MENU_DIR, 5) . '/ohnisko-pdf-runtime'`) and does not depend on `HOME` or PHP-FPM environment variables. The runtime, jobs, generated PDFs, and config stay outside the repository.

The HTML is a flow document. Logical categories and items determine order, while CSS styles special branded blocks such as Josper and the paired menu columns. The complete `NA OBJEDNÁVKU` promotion is one wrapper that moves to the next page when it cannot fit. A4 page count follows actual content. Items avoid internal breaks and section headings stay with their first item where possible. No fixed page number is part of the data model.

`assets/print.js` sets `window.__OHNISKO_PRINT_READY__` only after Paged.js has completed, `document.fonts.ready` resolves, and every document image has loaded and decoded. On an asset failure it sets `window.__OHNISKO_PRINT_ERROR__` instead. Chromium waits for the ready flag and fails on the error or timeout.

## Brand rules

Use Fire Yellow `#DEB76C` and Forest Green `#396360`. Headings use Bebas Neue; paragraph copy uses Montserrat. Supplied color and monochrome logo variants follow the logo manual's safe-zone principle. Pattern and icon assets are derived from the supplied manual. Do not add other brand colors or typefaces without an approved brand source. Font assets carry their OFL license notices.

## PDF jobs and version publishing

Generate writes a complete `pending` job to a temporary file and atomically renames it into `jobs/`; each Generate creates a new job. The PHP CLI CRON launcher uses `flock` to prevent overlapping workers. Node atomically records `pending → processing → ready|failed`; stale processing jobs are requeued after 45 minutes. PDF output is first written to a temporary path and exposed as ready only after it is complete. Failed and old ready files remain visible in history and are retained.

In WordPress, each ready version has Preview, Download, and Publish actions. Publish stores the chosen version as the current menu; it does not happen during generation. Ready job JSON and PDFs remain as history and can be published again. The stable public address `/?ohnisko_menu=1` redirects to the currently published PDF. No automatic version cleanup is configured.

## Local verification

Renderer checks: `npm run check` and `npm test`. The suite covers atomic JSON visibility, allowed job transitions, explicit Paged.js readiness, and short/long fixture pagination with a long fixture exceeding four pages. WordPress contract checks are in `tests/contracts.test.mjs`. PHP lint and live WordPress/CRON endpoint checks require PHP and a WordPress deployment environment.

## One-shot menu import

`tools/menu-2026.json` is a versioned import snapshot sourced from `Jedalny-2026-SVK-NOVY2.pdf`; WordPress remains the only runtime source of menu data. The importer validates all records before writing, matches posts by `_ohnisko_import_id`, updates existing matches, and never removes/deactivates unrelated posts. Back up the database and run the preview first from the deployed site root:

```sh
cd ~/ohnisko.com/web
wp eval-file wp-content/plugins/ohnisko-menu/tools/import-menu.php wp-content/plugins/ohnisko-menu/tools/menu-2026.json dry-run
wp eval-file wp-content/plugins/ohnisko-menu/tools/import-menu.php wp-content/plugins/ohnisko-menu/tools/menu-2026.json
```

WP-CLI passes trailing positional arguments to the evaluated file in `$args`; the first is the snapshot path and optional second argument `dry-run` enables preview. The importer also accepts its adjacent snapshot by default. The dry run prints CREATE/UPDATE actions without writes. Re-running the real command updates the same posts by stable import ID.
