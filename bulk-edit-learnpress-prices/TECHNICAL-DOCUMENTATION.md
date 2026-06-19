# Bulk Edit LearnPress Course Prices - Technical Documentation

## Product Summary

Bulk Edit LearnPress Course Prices is a WordPress admin plugin for managing LearnPress course pricing in bulk. It provides a course list, filters, safe preview workflow, AJAX updates, scheduled sale support using native LearnPress metadata, per-course price history, and release packaging.

Plugin slug: `bulk-edit-learnpress-prices`

Version: `1.0.0`

Author: Mamflow

## Runtime Requirements

- WordPress `6.0+`
- PHP `8.0+`
- LearnPress `4.x` or `5.x`
- Admin user with the LearnPress course management capability when available, otherwise `manage_options`

## File Structure

```text
bulk-edit-learnpress-prices/
  bulk-edit-learnpress-prices.php
  includes/
    class-bulk-edit-lp-price.php
    class-lp-price-list-table.php
    functions.php
  templates/
    admin-page.php
  assets/
    css/style.css
    js/script.js
  languages/
    bulk-edit-learnpress-prices.pot
  scripts/
    release.ps1
  readme.txt
  DEVELOPER-HOOKS.md
  TECHNICAL-DOCUMENTATION.md
  implementation-plan.md
  package.json
```

## Main Components

### Bootstrap

File: `bulk-edit-learnpress-prices.php`

Responsibilities:

- Defines plugin metadata and constants.
- Defines LearnPress price meta constants.
- Loads shared helpers and the main controller.
- Registers activation and deactivation hooks.
- Boots the singleton controller.

Important constants:

- `BELPCP_VERSION`
- `BELPCP_COURSE_POST_TYPE`
- `BELPCP_REGULAR_PRICE_META_KEY`
- `BELPCP_ACTIVE_PRICE_META_KEY`
- `BELPCP_SALE_PRICE_META_KEY`
- `BELPCP_SALE_START_META_KEY`
- `BELPCP_SALE_END_META_KEY`
- `BELPCP_PRICE_HISTORY_META_KEY`
- `BELPCP_MAX_PRICE_HISTORY_ITEMS`
- `BELPCP_MAX_SELECTED_COURSES`

### Main Controller

File: `includes/class-bulk-edit-lp-price.php`

Responsibilities:

- Registers WordPress hooks.
- Registers admin menu under LearnPress when available.
- Enqueues admin CSS/JS only on the plugin admin screen.
- Localizes AJAX config, nonce, action names, and UI strings.
- Sanitizes filters and bulk action payloads.
- Queries courses.
- Builds preview rows.
- Applies updates.
- Syncs active LearnPress price metadata.
- Records price history.
- Renders the AJAX course table.
- Clears WordPress and LearnPress course caches after updates.

### Course List Table

File: `includes/class-lp-price-list-table.php`

Responsibilities:

- Extends `WP_List_Table`.
- Renders selectable LearnPress courses.
- Supports sortable columns.
- Supports pagination.
- Displays regular price, sale price, sale schedule, status, instructor, and price history count.

### Admin Template

File: `templates/admin-page.php`

Responsibilities:

- Renders filters.
- Renders table action bar.
- Renders the course list region.
- Renders bulk action modal.
- Renders preview, confirmation, update report, and price history modal.

### Admin JavaScript

File: `assets/js/script.js`

Responsibilities:

- Tracks selected course count.
- Enables `Bulk price action` only after at least one course is selected.
- Hides the selection hint after course selection.
- Shows and validates action-specific fields.
- Runs preview, update, course reload, and history AJAX calls.
- Keeps filter pagination and sorting inside the AJAX table flow.
- Prevents direct navigation to `admin-ajax.php?paged=...`.
- Refreshes the table after successful updates.
- Manages modal focus, Escape close behavior, and basic accessibility states.

### Admin CSS

File: `assets/css/style.css`

Responsibilities:

- Styles the filter bar, table action bar, bulk action modal, preview/report blocks, and history modal.
- Keeps controls responsive on narrow admin screens.
- Separates input controls from action buttons in the bulk action modal.

## LearnPress Data Model

The plugin intentionally uses LearnPress' native course pricing metadata.

| Purpose | Meta key | Notes |
| --- | --- | --- |
| Editable regular price | `_lp_regular_price` | Current LearnPress edit screens use this as the regular price. |
| Active/current price | `_lp_price` | LearnPress uses this for active price compatibility, queries, and older data fallback. |
| Sale price | `_lp_sale_price` | Empty or missing means no sale price. |
| Sale start | `_lp_sale_start` | Native LearnPress scheduled sale start field. |
| Sale end | `_lp_sale_end` | Native LearnPress scheduled sale end field. |
| Plugin history | `_belpcp_price_history` | Plugin-owned per-course audit trail. |

Regular price reads use `_lp_regular_price` when it exists and fall back to `_lp_price` for older LearnPress data.

Scheduled sales do not use a custom cron system. LearnPress already evaluates `_lp_sale_start` and `_lp_sale_end` in its sale-price logic, so this plugin writes those fields directly.

## Active Price Sync

After a successful update, the plugin syncs `_lp_price` to the effective active price:

- If there is no valid sale price, `_lp_price` becomes the regular price.
- If there is a valid sale price and no schedule window, `_lp_price` becomes the sale price.
- If there is a valid schedule window and the current WordPress-local time is inside the window, `_lp_price` becomes the sale price.
- If the sale schedule is in the future or expired, `_lp_price` remains the regular price.

This keeps LearnPress compatibility while avoiding the earlier mismatch where `_lp_price` was incorrectly treated as the editable regular price.

## Admin Features

### Course Browsing

The admin page supports:

- Course table under LearnPress or Tools fallback.
- Course ID column.
- Course title with edit link.
- Current regular price.
- Current sale price.
- Sale schedule display.
- Price history count.
- Status.
- Instructor.
- Pagination.
- Sorting.
- AJAX reload after filtering, sorting, pagination, and updates.

### Filters

Supported filters:

- Course type: all, paid, free.
- Course category.
- Minimum price.
- Maximum price.
- Instructor.
- Post status.
- Search keyword.
- Pagination.
- Sorting.

### Selection Flow

- The table action bar shows the number of selected courses.
- `Bulk price action` is disabled until at least one course is selected.
- A short hint asks the admin to select at least one course to get started.
- The hint hides automatically after a course is selected.

### Bulk Actions

Supported bulk actions:

- Set Regular Price
- Set Sale Price
- Schedule Sale Price
- Remove Sale Price
- Increase Price by Percentage
- Decrease Price by Percentage

### Preview and Apply Flow

The plugin uses a mandatory preview-first workflow:

1. Admin selects one or more courses.
2. Admin opens `Bulk price action`.
3. Admin selects an action and enters required values.
4. Admin clicks `Preview Changes`.
5. Server recalculates every preview row.
6. `Apply Changes` becomes available only after a valid preview.
7. Admin confirms the operation.
8. Server recalculates and applies changes again.
9. Admin receives an updated/skipped/failed/warnings report.
10. Course table reloads through AJAX.

The final update never trusts client-side preview data.

## Bulk Action Behavior

### Set Regular Price

Writes:

- `_lp_regular_price`

Then syncs:

- `_lp_price`

Validation:

- Value is required.
- Value must be numeric.
- Value cannot be negative.

### Set Sale Price

Writes:

- `_lp_sale_price`

Then syncs:

- `_lp_price`

Validation:

- Value is required.
- Value must be numeric.
- Value cannot be negative.
- Sale price cannot be greater than the regular price.

### Schedule Sale Price

Writes:

- `_lp_sale_price`
- `_lp_sale_start`
- `_lp_sale_end`

Then syncs:

- `_lp_price`, respecting the sale schedule window.

Validation:

- Sale price is required.
- Sale price must be numeric.
- Sale price cannot be negative.
- Sale price cannot be greater than the regular price.
- Sale start is required.
- Sale end is required.
- Sale start must be before sale end.

### Remove Sale Price

Deletes:

- `_lp_sale_price`

Then syncs:

- `_lp_price` back to the regular price.

### Increase Price by Percentage

Writes:

- `_lp_regular_price`

Then syncs:

- `_lp_price`

Behavior:

- Existing sale price is preserved.
- If the preserved sale price becomes invalid relative to the new regular price, the row includes a warning.

### Decrease Price by Percentage

Writes:

- `_lp_regular_price`

Then syncs:

- `_lp_price`

Behavior:

- Existing sale price is preserved.
- Decrease percentage cannot be greater than `100`.
- Final regular price cannot become negative.
- If the preserved sale price becomes invalid relative to the new regular price, the row includes a warning.

## Price History

History is stored in course post meta:

```text
_belpcp_price_history
```

Each entry can include:

- Local timestamp.
- GMT timestamp.
- User ID.
- User label.
- Bulk action key.
- Bulk action label.
- Bulk value.
- Regular price before.
- Regular price after.
- Sale price before.
- Sale price after.
- Sale start before.
- Sale start after.
- Sale end before.
- Sale end after.

The history list is capped by `BELPCP_MAX_PRICE_HISTORY_ITEMS`.

The course table shows the number of recorded changes. Admins can open a row-level history modal to inspect changes.

## AJAX Endpoints

All AJAX endpoints are registered for authenticated admin users.

### `bulk_edit_lp_load_courses`

Loads filtered/sorted/paginated courses and returns rendered table HTML.

Security:

- Nonce check.
- Capability check.

### `bulk_edit_lp_preview_changes`

Builds server-side preview rows for selected course IDs.

Security:

- Nonce check.
- Capability check.
- Selected course ID sanitization.
- Per-course edit capability checks.

### `bulk_edit_lp_update_prices`

Applies server-side recalculated updates.

Security:

- Nonce check.
- Capability check.
- Selected course ID sanitization.
- Per-course edit capability checks.
- Server-side price and date validation.

### `bulk_edit_lp_load_price_history`

Loads the price history for a selected course.

Security:

- Nonce check.
- Capability check.
- Course existence check.
- Per-course edit capability check.

## Security Model

The plugin uses:

- WordPress nonce verification for AJAX requests.
- Capability checks for admin page access and AJAX.
- Per-course `edit_post` checks before preview/update/history operations.
- Sanitized filter values.
- Sanitized course IDs.
- Sanitized action keys.
- Decimal validation for price values.
- Date validation for scheduled sale values.
- Server-side recalculation during update.
- Escaped output in templates and list table columns.

## Cache Handling

After course metadata changes, the plugin clears:

- WordPress post cache.
- WordPress post meta cache.
- Known LearnPress cache helpers when available:
  - `learn_press_delete_course_cache`
  - `learn_press_clean_course_cache`

## Hooks and Extensibility

Developer hooks are documented in `DEVELOPER-HOOKS.md`.

Key extension points include:

- Capability customization.
- Supported post statuses.
- Sanitized filters.
- Course category taxonomies.
- Category options.
- Instructor options.
- Supported bulk actions.
- Course query args.
- Preview rows.
- Update summaries.
- Before/after individual course updates.
- After complete bulk operation.

## Release Packaging

Release command:

```bash
npm run release
```

On Windows PowerShell with execution policy restrictions, use:

```bash
npm.cmd run release
```

Release output:

```text
release/bulk-edit-learnpress-prices-1.0.0.zip
```

The release archive includes runtime plugin files, documentation, assets, includes, languages, templates, and scripts. The local `release/` directory is ignored by git.

## Verification Commands

PHP syntax:

```powershell
Get-ChildItem -Recurse bulk-edit-learnpress-prices -Include *.php | ForEach-Object { php -l $_.FullName }
```

JavaScript syntax:

```powershell
node --check bulk-edit-learnpress-prices\assets\js\script.js
```

Translation template:

```powershell
wp i18n make-pot bulk-edit-learnpress-prices bulk-edit-learnpress-prices\languages\bulk-edit-learnpress-prices.pot --exclude=release,node_modules,agent
```

Release:

```powershell
npm.cmd run release
```

## Manual Test Coverage

Recommended manual checks:

- Plugin activates without fatal errors.
- LearnPress inactive state shows admin notice.
- Admin page loads for authorized users.
- Unauthorized users cannot access AJAX endpoints.
- Course filters work.
- Pagination works after filter reload.
- Sorting works after filter reload.
- `Bulk price action` is disabled until a course is selected.
- Selection hint hides after course selection.
- Preview is required before apply.
- Set Regular Price updates `_lp_regular_price`.
- Set Sale Price updates `_lp_sale_price`.
- Schedule Sale Price updates `_lp_sale_price`, `_lp_sale_start`, and `_lp_sale_end`.
- Remove Sale Price deletes `_lp_sale_price`.
- Percentage increase updates regular price only.
- Percentage decrease updates regular price only and cannot exceed 100%.
- Sale price greater than regular price is rejected.
- Course edit screen shows the same regular price as the plugin table.
- Final report counts updated/skipped/failed/warnings.
- Table reloads after apply.
- Price history entry is created after actual changes.
- Release ZIP installs cleanly.

## Known Limitations

Version `1.0.0` intentionally does not include:

- WooCommerce linked product price sync.
- CSV import/export.
- Apply to all filtered courses beyond the selected rows.
- Advanced searchable history reports.
- Rollback from history.
- WP-CLI commands.

These are planned for future versions.
