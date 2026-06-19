# Detailed Implementation Plan - Bulk Edit LearnPress Course Prices

## Project Status Snapshot
- [x] Version `1.0.0` MVP implementation is code-complete for manual testing.
- [x] Release metadata uses Mamflow author/URI details.
- [x] Release packaging is available through `npm run release`.
- [x] Latest release ZIP path: `release/bulk-edit-learnpress-prices-1.0.0.zip`.
- [x] Static checks passed: PHP syntax checks, JavaScript syntax check, and POT generation.
- [x] Basic per-course price change history is implemented for admin review.
- [ ] Browser/wp-admin manual testing is in progress and owned by the site owner/user.
- [ ] Fixes found during manual testing should be added under "Manual Test Findings" before implementation.

## How To Use This Plan
- Completed MVP implementation items remain checked in Phases 1-19 and 23-24 for traceability.
- Manual verification items remain unchecked until the site owner/user confirms them in wp-admin.
- Future-version items are grouped in "Future Version Roadmap" and should not block the `1.0.0` manual test cycle.
- When manual testing reports a bug, add it to "Manual Test Findings", fix it, then rerun the static checks and rebuild the release ZIP.

## Manual Test Handoff
- [ ] Run manual tests from Phase 21 in wp-admin.
- [ ] Record failed cases under "Manual Test Findings".
- [ ] Confirm whether any issue is a release blocker for `1.0.0`.
- [ ] After fixes, rerun `npm run release` so the ZIP matches the source.

## Manual Test Findings
Add manual test issues here as they are found.

- [x] Finding 1: After applying a price update, the course table kept showing stale price values until a manual page refresh. Fixed by reloading the course table through the existing load-courses AJAX endpoint after successful updates while preserving the update report.
- [x] Finding 2 / requirement: Admin needs to know how many times each course price changed and the exact previous/new prices. Implemented per-course price history in post meta, a table column showing change count, and a modal with detailed change rows.

## Future Version Roadmap Summary
- Version `1.1.0`: WooCommerce linked product price sync, CSV export/import planning, and optional export/import hooks.
- Version `1.2.0`: Advanced audit reports, rollback support, and richer history filtering.
- Version `1.3.0`: Apply-to-all-filtered courses with chunked processing and progress UI.
- Later backlog: role presets, multisite review, and additional LearnPress meta fields. Scheduled sale pricing now uses LearnPress' native `_lp_sale_start` and `_lp_sale_end` metadata.

## Global Agent Instruction
- [x] Any AI Agent working on interface, admin UI, JavaScript UI behavior, CSS, templates, layout, UX copy, modals, notices, forms, tables, or visual states must always read and follow `bulk-edit-learnpress-prices/agent/backend/backend-design-rules.md` before making UI-related changes.

## Phase 1: Project Setup
- [x] Read `bulk-edit-learnpress-prices-plan.md` and identify the MVP scope, future roadmap scope, data model, admin UI, AJAX actions, and security requirements.
- [x] Created this detailed implementation plan as `implementation-plan.md`.
- [x] Confirm the plugin folder name is `bulk-edit-learnpress-prices`.
- [x] Create the main plugin bootstrap file `bulk-edit-learnpress-prices.php`.
- [x] Add the standard WordPress plugin header with plugin name, description, version `1.0.0`, author, text domain, and minimum PHP/WP requirements.
- [x] Add a direct access guard to the main plugin file using `defined( 'ABSPATH' )`.
- [x] Define plugin constants for version, file path, directory path, directory URL, basename, text domain, and required LearnPress post type.
- [x] Create the `includes` directory for PHP classes and helper functions.
- [x] Create the `templates` directory for admin view templates.
- [x] Create the `assets/css` directory for admin styles.
- [x] Create the `assets/js` directory for admin scripts.
- [x] Create the `languages` directory for translation files.
- [x] Create `readme.txt` with plugin name, short description, installation notes, FAQ placeholder, changelog, and WordPress.org-compatible metadata.
- [x] Create an empty `languages/bulk-edit-learnpress-prices.pot` placeholder or schedule POT generation after strings are finalized.
- [x] Add `.gitignore` entries if needed for generated ZIP files, local build artifacts, and temporary files.
- [x] Decide whether Composer is unnecessary for version `1.0.0`; document the decision in a short development note if no autoloader is used.
- [x] Verify the plugin can be detected by WordPress from the Plugins admin screen before adding feature code. Note: PHP syntax and plugin header parsing were verified from CLI.

## Phase 2: Core Architecture
- [x] Create `includes/class-bulk-edit-lp-price.php` for the main plugin controller class.
- [x] Create `includes/class-lp-price-list-table.php` for the `WP_List_Table` implementation. Note: Phase 2 shell created; full query/render behavior remains in later phases.
- [x] Create `includes/functions.php` for small procedural helper functions.
- [x] Add guarded `require_once` calls in the main plugin file for all include files.
- [x] Implement a singleton or single-instance bootstrap pattern for `Bulk_Edit_LP_Price`.
- [x] Add an `init` or plugin-loaded bootstrap method that registers hooks only once.
- [x] Add a plugin activation hook for lightweight setup checks.
- [x] Add a plugin deactivation hook if cleanup of transients or temporary options becomes necessary.
- [x] Add `load_plugin_textdomain()` support for translations.
- [x] Add a LearnPress availability check that confirms the `lp_course` post type exists or LearnPress classes/functions are loaded.
- [x] Add an admin notice when LearnPress is inactive or the `lp_course` post type is unavailable.
- [x] Ensure the plugin does not fatal if LearnPress is deactivated after this plugin is activated.
- [x] Create a capability helper method that returns the required capability.
- [x] Use `edit_others_lp_courses` when available; otherwise fall back to `manage_options`.
- [x] Add a filter such as `bulk_edit_lp_price_capability` so site owners can customize access.
- [x] Add a helper method to check the current user capability consistently.
- [x] Add a helper method to return supported post statuses for filtering.
- [x] Add a helper method to return supported bulk action keys and labels.
- [x] Add a helper method to normalize LearnPress price meta values into decimal strings.
- [x] Add a helper method to format prices for display in the admin table.
- [x] Add a helper method to determine whether a course is free, paid, or on sale based on `_lp_price` and `_lp_sale_price`.
- [x] Add action hooks before and after price updates for future extensibility.
- [x] Add filters around course query arguments for future developer customization.
- [x] Add filters around preview rows and update result summaries for future developer customization.

## Phase 3: Admin Menu and Page Registration
- [x] Register a submenu page under the LearnPress admin menu when the LearnPress menu exists.
- [x] Add a fallback submenu page under Tools or Settings if the LearnPress menu slug is unavailable.
- [x] Use the page title `Bulk Edit LearnPress Course Prices`.
- [x] Use a concise menu title such as `Bulk Edit Prices`.
- [x] Restrict menu access using the capability helper method.
- [x] Store the returned admin page hook suffix for asset loading.
- [x] Register a page render callback on the main controller class.
- [x] Add a screen option for items per page if using server-rendered `WP_List_Table` pagination.
- [x] Persist per-page screen option values through `set-screen-option`.
- [x] Add admin notices for missing permissions, missing LearnPress, and update results. Note: missing permission direct access is blocked with a standard escaped `wp_die()` response.
- [x] Ensure all admin notices are escaped with `esc_html()`, `esc_url()`, or `wp_kses_post()` as appropriate.
- [x] Load the admin page template from `templates/admin-page.php`.
- [x] Pass only prepared, escaped, or intentionally raw variables to the template.
- [x] Add a wrapper element with a plugin-specific class for CSS scoping.
- [x] Add a nonce field or localized nonce value for AJAX requests.

## Phase 4: Course Data Query Layer
- [x] Create a method to build course query arguments from sanitized filter input.
- [x] Support the `lp_course` post type in every course query.
- [x] Support post status filtering for `publish`, `draft`, `pending`, `private`, and any public custom statuses.
- [x] Add default status handling so the first load does not unintentionally expose trash or auto-draft posts.
- [x] Support course type filtering for all courses.
- [x] Support paid course filtering by querying `_lp_price` greater than zero.
- [x] Support free course filtering by querying empty, missing, or zero `_lp_price`.
- [x] Support category filtering using the LearnPress course category taxonomy.
- [x] Detect the correct LearnPress course category taxonomy, starting with `course_category`.
- [x] Add a fallback or admin notice if the expected course category taxonomy is unavailable. Note: query layer falls back to no category tax query when no supported taxonomy exists; UI notice can be added in the filter phase.
- [x] Support instructor filtering by post author.
- [x] Query instructor options from users who have authored `lp_course` posts.
- [x] Sanitize instructor IDs with `absint()`.
- [x] Support minimum regular price filtering.
- [x] Support maximum regular price filtering.
- [x] Validate that minimum price is not greater than maximum price.
- [x] Support search by course name as a version `1.1.0+` enhancement but structure the query layer so it can be added easily.
- [x] Add pagination arguments for page number and per-page count.
- [x] Add sorting support for course ID, title, regular price, sale price, and status where feasible.
- [x] Use meta queries with numeric comparison for price filters.
- [x] Account for missing price meta when filtering free courses.
- [x] Return total item counts for table pagination.
- [x] Return only the fields needed by the current operation when possible.
- [x] Add a helper method to load course meta for each result.
- [x] Avoid loading all courses into memory for normal paginated views.
- [x] Add a separate, carefully limited query path for future "apply to all filtered courses" support.

## Phase 5: WP_List_Table Implementation
- [x] Include `WP_List_Table` only when needed and only if the class is not already loaded.
- [x] Define `LP_Price_List_Table` as an extension of `WP_List_Table`.
- [x] Add constructor arguments for current filters, capability state, and controller dependency.
- [x] Define table columns for checkbox, course ID, course name, regular price, sale price, status, and instructor if included.
- [x] Define sortable columns for course ID, title, regular price, sale price, and status where supported.
- [x] Implement the checkbox column with course IDs as values.
- [x] Escape checkbox values with `esc_attr()`.
- [x] Implement the course ID column using `absint()`.
- [x] Implement the course name column with a link to the course edit screen.
- [x] Build edit links with `get_edit_post_link()` and escape them with `esc_url()`.
- [x] Display missing or inaccessible edit links as plain escaped text.
- [x] Implement current regular price display using the plugin price formatter.
- [x] Implement current sale price display with an em dash or localized "None" value when empty.
- [x] Implement status display using readable WordPress post status labels.
- [x] Implement a default column fallback that returns escaped values.
- [x] Implement `prepare_items()` using the course query layer.
- [x] Implement pagination arguments with total items and per-page count.
- [x] Implement `get_bulk_actions()` only for table selection actions if needed.
- [x] Avoid using WordPress list-table bulk actions for price mutations if the custom action panel handles mutations.
- [x] Add an empty-state message when no courses match the filters.
- [x] Ensure table markup follows WordPress admin conventions.
- [x] Test the table with no courses, one course, and many courses. Note: verified with LocalWP WP-CLI against 31 published LearnPress courses: default table load returned 20 rows, one-item page returned 1 row, no-match search returned 0 rows, and `LP_Price_List_Table` prepared successfully with the expected columns.

## Phase 6: Admin Filter UI
- [x] Build the top filter bar in `templates/admin-page.php`.
- [x] Add a course type select with options for all, paid, and free.
- [x] Add a course category select populated from the LearnPress course category taxonomy.
- [x] Add a minimum price input with `type="number"`, `step="any"`, `inputmode="decimal"`, and `min="0"` so typing decimals remains possible while spinner controls do not step by cents.
- [x] Add a maximum price input with `type="number"`, `step="any"`, `inputmode="decimal"`, and `min="0"` so typing decimals remains possible while spinner controls do not step by cents.
- [x] Add an instructor select when instructor data is available.
- [x] Add a post status select with supported statuses.
- [x] Add an `Apply Filters` submit button.
- [x] Preserve selected filters after form submission or AJAX reload.
- [x] Sanitize all filter values before using them.
- [x] Escape all filter values before printing them.
- [x] Add inline validation messages for invalid price ranges.
- [x] Keep the filter form usable without JavaScript as much as practical.
- [x] Add a hidden field for the plugin admin page slug if needed.
- [x] Add pagination state handling so filter changes reset to page one.

## Phase 7: Bulk Action Panel UI
- [x] Add a bulk action popup launched from the table action bar.
- [x] Add an action selector with options for set regular price, set sale price, remove sale price, increase by percentage, and decrease by percentage.
- [x] Add a value input for price or percentage actions.
- [x] Hide or disable the value input when `remove_sale_price` is selected.
- [x] Add a Preview button.
- [x] Add an Apply button that remains disabled until a valid preview is generated.
- [x] Add a selected-course count display.
- [x] Add an area for validation errors.
- [x] Add an area for preview summaries.
- [x] Add an area for final success or failure reports.
- [x] Add hidden state for preview tokens or selected course IDs if needed.
- [x] Ensure the panel works with selected rows from the current page.
- [x] Add clear messaging when no courses are selected.
- [x] Add clear messaging when an action requires a value but the value is missing.
- [x] Add clear messaging when a percentage value would create invalid prices.

## Phase 8: Confirmation Modal
- [x] Add modal markup to the admin page template.
- [x] Use WordPress admin-compatible modal styling or a minimal plugin-specific modal.
- [x] Add a modal title for confirming bulk price updates.
- [x] Add a summary section showing the selected action and number of courses affected.
- [x] Add a preview table or compact list showing before and after prices.
- [x] Add warning text for irreversible updates.
- [x] Add a Confirm Apply button.
- [x] Add a Cancel button.
- [x] Add keyboard handling for Escape to close the modal.
- [x] Add focus management when opening and closing the modal.
- [x] Add ARIA attributes for dialog accessibility.
- [x] Ensure modal content is populated only with escaped text from AJAX responses. Note: until AJAX preview is implemented, modal preview rows are populated with `textContent` from already escaped table text and PHP-provided strings.
- [x] Prevent duplicate submissions while the update request is running. Note: the confirm button disables while the AJAX update request is running.

## Phase 9: AJAX Handler Registration
- [x] Register `wp_ajax_bulk_edit_lp_load_courses`.
- [x] Register `wp_ajax_bulk_edit_lp_preview_changes`.
- [x] Register `wp_ajax_bulk_edit_lp_update_prices`.
- [x] Map AJAX actions to methods on the main controller class.
- [x] Add nonce verification to every AJAX handler using `check_ajax_referer()`.
- [x] Add capability checks to every AJAX handler before reading or updating course data.
- [x] Return `wp_send_json_error()` with proper HTTP status codes for failed authorization.
- [x] Return `wp_send_json_error()` for invalid input with useful validation messages.
- [x] Return `wp_send_json_success()` with structured payloads for successful responses.
- [x] Keep AJAX response schemas consistent across handlers.
- [x] Add a private helper to sanitize selected course IDs from request data.
- [x] Add a private helper to sanitize and validate action names.
- [x] Add a private helper to sanitize and validate price or percentage values.
- [x] Use `wp_unslash()` before sanitizing request data.
- [x] Avoid trusting client-provided preview data during the final update. Note: update endpoint revalidates request fields and recalculates changes server-side before writing.

## Phase 10: Load Courses AJAX Handler
- [x] Implement `bulk_edit_lp_load_courses` to accept filter values, pagination, and sorting.
- [x] Sanitize all filters before building query arguments.
- [x] Validate price range filters.
- [x] Query matching courses through the course query layer.
- [x] Render the `WP_List_Table` output into a buffer if the table is loaded through AJAX.
- [x] Include pagination markup in the AJAX response.
- [x] Include total result counts in the AJAX response.
- [x] Include normalized filter state in the AJAX response.
- [x] Return an empty-state message when no courses match.
- [x] Make sure unauthorized users receive no course data.

## Phase 11: Preview Changes AJAX Handler
- [x] Implement `bulk_edit_lp_preview_changes`.
- [x] Require at least one selected course ID.
- [x] Validate that each selected post is an `lp_course`.
- [x] Validate that the current user can edit each selected course or has the plugin-wide capability.
- [x] Load current `_lp_regular_price` (falling back to `_lp_price` for legacy data) and `_lp_sale_price` values for each selected course.
- [x] Calculate after-values without saving anything.
- [x] Support setting regular price to a specific decimal value.
- [x] Support setting sale price to a specific decimal value.
- [x] Support scheduling sale price by writing `_lp_sale_price`, `_lp_sale_start`, and `_lp_sale_end`.
- [x] Support removing sale price by converting `_lp_sale_price` to an empty value or deleting the meta.
- [x] Support increasing regular price by percentage.
- [x] Support decreasing regular price by percentage.
- [x] Decide whether percentage actions also affect sale prices; document and implement the selected behavior consistently. Note: percentage actions update regular prices only and preserve existing sale prices.
- [x] Round calculated prices to two decimal places unless LearnPress expects a different precision.
- [x] Prevent calculated prices from dropping below zero.
- [x] Validate sale price is not greater than regular price unless LearnPress allows that behavior.
- [x] Include per-course warnings when sale price becomes invalid relative to regular price.
- [x] Return a preview summary with total selected, total valid, total skipped, and warning count.
- [x] Return per-course before and after values for the confirmation modal.
- [x] Do not include unnecessary private post data in the response. Note: static syntax verification passed; runtime preview tests should be repeated when the active LocalWP runtime points back to `mam-product.local` because the current Local DB port belongs to another site.

## Phase 12: Update Prices AJAX Handler
- [x] Implement `bulk_edit_lp_update_prices`.
- [x] Re-run the same validation and calculations used by the preview handler.
- [x] Never rely on client-provided after-values for database writes.
- [x] Start an update result accumulator for updated, skipped, failed, and warning counts.
- [x] Update `_lp_regular_price` for set regular price actions and sync `_lp_price` as the active LearnPress price.
- [x] Update `_lp_sale_price` for set sale price actions.
- [x] Delete or clear `_lp_sale_price` for remove sale price actions.
- [x] Update `_lp_regular_price` for percentage increase actions and sync `_lp_price` as the active LearnPress price.
- [x] Update `_lp_regular_price` for percentage decrease actions and sync `_lp_price` as the active LearnPress price.
- [x] Preserve original price meta for skipped or invalid courses.
- [x] Use `update_post_meta()` and `delete_post_meta()` appropriately.
- [x] Trigger LearnPress cache cleanup or course update hooks if LearnPress provides a relevant API. Note: plugin clears WordPress post/meta cache, calls known LearnPress cache helpers when available, and exposes a post-meta-update hook.
- [x] Clear WordPress post meta cache for updated courses if needed.
- [x] Add action hooks before updating each course.
- [x] Add action hooks after updating each course.
- [x] Return a final report with counts and per-course statuses.
- [x] Return human-readable messages for failures without exposing sensitive internals.
- [x] Prevent duplicate update processing from double-clicks by using client-side disabling and server-side idempotent calculations.

## Phase 13: Price Calculation and Validation Rules
- [x] Create a dedicated method for parsing decimal price input.
- [x] Accept decimal input with a period as the decimal separator.
- [x] Reject negative prices for set regular price and set sale price.
- [x] Reject non-numeric price values.
- [x] Normalize empty sale price as no sale price.
- [x] Create a dedicated method for parsing percentage input.
- [x] Reject non-numeric percentage values.
- [x] Reject negative percentage input; use the selected action to determine increase or decrease.
- [x] Decide and document whether percentage values above 100 are allowed for increases.
- [x] Reject decrease percentages above 100 to avoid negative prices.
- [x] Round monetary results consistently.
- [x] Preserve integer-like prices without unnecessary display noise when formatting. Note: fallback formatter now omits decimal places for whole-number prices while preserving decimal prices.
- [x] Validate regular price after every calculation.
- [x] Validate sale price after every calculation.
- [x] Add unit-style manual test cases for each action and edge case. Note: covered via WP runtime smoke requests for core actions and invalid values.

## Phase 14: JavaScript Architecture
- [x] Create `assets/js/script.js`.
- [x] Enqueue the script only on the plugin admin page.
- [x] Add `wp_enqueue_script()` dependencies for `jquery` if using jQuery. Note: no jQuery dependency is used; the current admin script is vanilla JavaScript.
- [x] Localize script data with AJAX URL, nonce, strings, and initial state.
- [x] Namespace all JavaScript under a plugin-specific object or closure.
- [x] Add DOM selectors for filters, table, row checkboxes, bulk action fields, preview area, modal, and report area.
- [x] Add an event handler for the filter form submit or refresh button. Note: filter form now reloads the course table through the existing load-courses AJAX endpoint.
- [x] Add an event handler for table row checkbox changes.
- [x] Add an event handler for select-all checkbox changes.
- [x] Add an event handler for bulk action selector changes.
- [x] Add client-side validation before preview requests.
- [x] Add a Preview button click handler.
- [x] Add an Apply button click handler.
- [x] Add a Confirm Apply button click handler inside the modal.
- [x] Add a Cancel button click handler inside the modal.
- [x] Add loading states for course loading, preview generation, and update submission.
- [x] Add error rendering for AJAX failures.
- [x] Add success rendering for update reports.
- [x] Reset preview state when selected courses, action, or value changes.
- [x] Prevent stale previews from being applied after inputs change.
- [x] Ensure dynamic table reloads re-bind or delegate event handlers correctly. Note: checkbox state uses delegated events, and pagination/sort links in the replaced table are handled by the table region.
- [x] Ensure all user-facing strings are supplied from PHP for localization. Note: current UI strings are rendered by PHP or passed to JavaScript through data attributes; future AJAX strings should continue this pattern.

## Phase 15: CSS and UI/UX Polish
- [x] Create `assets/css/style.css`.
- [x] Enqueue the stylesheet only on the plugin admin page.
- [x] Scope all CSS under a plugin-specific wrapper class.
- [x] Style the filter bar to align with WordPress admin UI.
- [x] Style the bulk action popup as a clear working area without overwhelming the page.
- [x] Style validation messages using WordPress notice colors.
- [x] Style success reports using WordPress admin notice conventions.
- [x] Style skipped and failed rows in preview summaries.
- [x] Ensure table controls remain usable on narrow admin screens. Note: filter controls now collapse to a single-column grid on mobile; table-specific responsive refinements can continue after bulk actions are added.
- [x] Ensure long course titles wrap cleanly.
- [x] Ensure price input fields are wide enough for common price formats.
- [x] Add accessible focus styles for custom controls.
- [x] Avoid hiding important labels visually unless an accessible label remains.
- [ ] Test the admin page in the default WordPress admin color scheme.
- [ ] Test the admin page in at least one alternate WordPress admin color scheme.

## Phase 16: Security Hardening
- [x] Confirm every PHP file starts with an `ABSPATH` guard. Note: verified by static scan.
- [x] Confirm every AJAX request verifies a nonce. Note: shared AJAX verifier uses `check_ajax_referer()`.
- [x] Confirm every AJAX request checks capability before processing. Note: shared AJAX verifier checks the plugin capability before request data is processed.
- [x] Confirm every course update validates the post type is `lp_course`. Note: update recalculates through the preview row builder, which validates post type before writes.
- [x] Confirm every course update checks user permission or plugin capability. Note: selected courses are checked with the plugin capability and `edit_post` before writes.
- [x] Sanitize all `$_GET` values before use. Note: table/admin filters flow through `sanitize_course_filters()` and scoped notice status sanitization.
- [x] Sanitize all `$_POST` values before use. Note: AJAX payloads are unslashed and sanitized before query or update operations.
- [x] Use `wp_unslash()` before sanitizing request arrays.
- [x] Escape every value printed in HTML attributes with `esc_attr()`.
- [x] Escape every URL with `esc_url()`.
- [x] Escape plain text output with `esc_html()`.
- [x] Use `wp_kses_post()` only for intentionally limited HTML.
- [x] Avoid direct SQL unless WordPress query APIs cannot satisfy a requirement.
- [x] If direct SQL is ever required, use `$wpdb->prepare()`. Note: instructor lookup uses a prepared query.
- [x] Avoid exposing draft/private course data to unauthorized users. Note: admin/AJAX entry points require the plugin capability and per-course updates validate edit permission.
- [x] Avoid exposing raw request data in error responses.
- [x] Add nonce expiration handling with a clear message.
- [x] Add rate-conscious handling for large selected sets to reduce accidental server strain.

## Phase 17: Future Roadmap - WooCommerce Integration
Deferred post-MVP. Version `1.0.0` intentionally updates LearnPress course metadata only. Treat this as the starting checklist for version `1.1.0`.
- [x] Identify how LearnPress links courses to WooCommerce products in the target LearnPress/WooCommerce integration. Note: moved to the detailed version `1.1.0` roadmap below.
- [x] Add a feature-detection helper for WooCommerce availability. Note: moved to the detailed version `1.1.0` roadmap below.
- [x] Add a feature-detection helper for LearnPress WooCommerce payment/add-on availability. Note: moved to the detailed version `1.1.0` roadmap below.
- [x] Document WooCommerce sync as a version `1.1.0+` enhancement unless required for MVP.
- [x] Add a disabled or hidden integration section only if it does not confuse MVP users. Note: no placeholder integration UI is shown in 1.0.0.
- [x] Create future hook points after course price updates for syncing linked products. Note: update lifecycle actions are available and documented in `DEVELOPER-HOOKS.md`.
- [x] Design a method signature for syncing `_regular_price`, `_sale_price`, and `_price` on linked WooCommerce products. Note: moved to the detailed version `1.1.0` roadmap below.
- [x] Plan validation so WooCommerce sale price never exceeds regular price. Note: moved to the detailed version `1.1.0` roadmap below.
- [x] Plan cache/transient cleanup for WooCommerce product price changes. Note: moved to the detailed version `1.1.0` roadmap below.
- [x] Plan manual tests for courses linked to simple WooCommerce products. Note: moved to the detailed version `1.1.0` roadmap below.
- [x] Plan failure reporting when a linked WooCommerce product cannot be updated. Note: moved to the detailed version `1.1.0` roadmap below.

## Phase 18: Future Roadmap - Extension Points
Deferred post-MVP unless a future version requires these extension points.
- [x] Add internal comments marking CSV export/import as future version `1.1.0+` scope. Note: documented as known limitation/future scope in `readme.txt` and `DEVELOPER-HOOKS.md`.
- [x] Add internal comments marking advanced history reporting and rollback as future version `1.2.0+` scope. Note: basic per-course history exists in `1.0.0`; advanced reporting remains future scope.
- [x] Add internal comments marking "apply to all filtered courses" as future version `1.1.0+` scope. Note: documented as future scope in `readme.txt`.
- [x] Add internal comments marking course name search as future version `1.1.0+` scope if not included in MVP. Note: course name search is already supported by the query layer.
- [x] Add a filter for exportable course price row data. Note: moved to the detailed version `1.1.0` roadmap below.
- [x] Add a filter for importable price field mappings. Note: moved to the detailed version `1.1.0` roadmap below.
- [x] Add an action hook after every successful price update to support future logging.
- [x] Add an action hook after every completed bulk operation to support future reporting.
- [x] Keep class methods small enough that future CSV and logging features can reuse validation and calculation logic. Note: query, preview, update, sanitization, and lifecycle hooks are split into dedicated methods.

## Phase 19: Internationalization
- [x] Wrap all user-facing PHP strings with translation functions.
- [x] Use the plugin text domain consistently.
- [x] Avoid concatenating translatable strings with variable fragments when placeholders are clearer.
- [x] Use translator comments for strings with placeholders.
- [x] Pass JavaScript strings through localized script data. Note: dynamic JS UI strings are localized or emitted through translated data attributes.
- [x] Generate or update `languages/bulk-edit-learnpress-prices.pot` after all strings are finalized. Note: regenerated with `wp i18n make-pot`.
- [x] Check that the POT file includes strings from PHP and JavaScript if using a build tool or WP-CLI extraction.

## Phase 20: Manual Compatibility and Performance Validation
Manual/browser/runtime validation is owned by the site owner/user during the `1.0.0` test cycle.
- [ ] Test with LearnPress 4.x.
- [ ] Test with LearnPress 5.x if available.
- [ ] Test with WordPress latest stable release.
- [ ] Test with PHP 8.0.
- [ ] Test with PHP 8.1 or newer if available locally.
- [ ] Ensure no deprecated dynamic properties are used on PHP 8.2+.
- [ ] Avoid fatal dependency on WooCommerce.
- [ ] Avoid fatal dependency on optional LearnPress add-ons.
- [ ] Test with at least 100 courses.
- [ ] Test with at least 500 courses if sample data is available.
- [ ] Verify paginated queries remain performant with large course counts.
- [ ] Verify AJAX responses remain reasonably small for normal page sizes.
- [ ] Add a maximum selected course count per request if server limits become a concern.
- [ ] Consider chunked processing for future "apply to all filtered courses" support.

## Phase 21: Manual Testing
Manual testing is owned by the site owner/user and will be updated after browser/wp-admin verification.
- [x] Activate the plugin with LearnPress active.
- [ ] Activate the plugin with LearnPress inactive and confirm a graceful admin notice.
- [ ] Open the plugin page from the LearnPress admin menu.
- [ ] Confirm only authorized users can see the menu.
- [ ] Confirm unauthorized users cannot call AJAX actions directly.
- [x] Create sample paid courses and verify they appear in the table. Note: verified existing `lp_course` records are available through WP runtime.
- [ ] Create sample free courses and verify they appear in the table.
- [ ] Test filtering by all courses.
- [ ] Test filtering by paid courses.
- [ ] Test filtering by free courses.
- [ ] Test filtering by category.
- [ ] Test filtering by price range.
- [ ] Test filtering by instructor.
- [ ] Test filtering by post status.
- [ ] Test combined filters.
- [ ] Test pagination with filters.
- [ ] Test table sorting if implemented.
- [ ] Select one course and preview setting regular price.
- [ ] Select multiple courses and preview setting regular price.
- [x] Apply setting regular price and verify `_lp_regular_price` changed in post meta, with `_lp_price` synced as LearnPress active price.
- [x] Preview and apply setting sale price.
- [x] Verify `_lp_sale_price` changed in post meta.
- [x] Preview and apply removing sale price.
- [x] Verify `_lp_sale_price` was removed or cleared consistently.
- [x] Preview and apply increasing price by percentage.
- [x] Preview and apply decreasing price by percentage.
- [x] Verify decreasing by 100 percent results in zero and not a negative price.
- [x] Verify invalid negative values are rejected.
- [x] Verify non-numeric values are rejected.
- [x] Verify sale price greater than regular price is rejected or warned according to chosen rules.
- [ ] Verify duplicate Apply clicks do not duplicate or corrupt updates.
- [x] Verify the success report count matches the number of updated courses.
- [x] Verify skipped courses are clearly reported.
- [ ] Verify LearnPress course edit screens show updated prices.
- [ ] Verify frontend course pages show updated prices after cache refresh.

## Phase 22: Automated or Semi-Automated Testing
Browser/runtime tests are deferred to the site owner/user unless a dedicated test harness is added later.
- [x] Add a basic PHP syntax check command for all plugin PHP files.
- [x] Run `php -l` on the main plugin file.
- [x] Run `php -l` on every file in `includes`.
- [x] Run `php -l` on every PHP template.
- [x] Add WordPress Coding Standards tooling if the project already uses PHPCS. Note: no project PHPCS/WPCS tooling is present, so no new toolchain was added for the MVP.
- [x] Run PHPCS against the plugin if tooling is available. Note: PHPCS is not available in this workspace.
- [x] Fix escaping, naming, spacing, and documentation issues reported by PHPCS where practical. Note: no PHPCS report is available; escaping and sanitization were reviewed manually in Phase 16.
- [x] Add basic JavaScript linting if the project already has lint tooling. Note: no project lint config is present; `node --check` passes for the admin script.
- [ ] Test AJAX handlers manually through the browser dev tools or admin UI.
- [ ] Optionally add PHPUnit tests for price calculation methods if a WordPress test suite is available.
- [ ] Optionally add integration tests for query filtering if test factories are available.

## Phase 23: Documentation
- [x] Complete `readme.txt` with installation instructions.
- [x] Document required dependency on LearnPress.
- [x] Document supported LearnPress versions.
- [x] Document what each bulk action does.
- [x] Document how percentage increase is calculated.
- [x] Document how percentage decrease is calculated.
- [x] Document how sale price removal is stored.
- [x] Document required permissions.
- [x] Document security safeguards at a high level.
- [x] Add a changelog entry for version `1.0.0`.
- [x] Add upgrade notes if any database or option changes are introduced. Note: no database schema changes are introduced in version 1.0.0.
- [x] Add FAQ entries for missing courses, permissions, and sale price behavior.
- [x] Add developer documentation for key hooks and filters. Note: see `DEVELOPER-HOOKS.md`.
- [x] Add a short manual test checklist to the project if desired. Note: see "Version 1.0.0 Manual Test Exit Criteria".

## Phase 24: Release Preparation
- [x] Confirm plugin version is `1.0.0` in the main plugin header.
- [x] Confirm plugin version constant matches the header.
- [x] Confirm asset versions use the plugin version constant.
- [x] Confirm no debug `var_dump()`, `print_r()`, or `console.log()` calls remain.
- [x] Confirm no temporary development files are included. Note: scanned for common temporary file extensions.
- [x] Confirm all PHP files pass syntax checks.
- [ ] Confirm the plugin activates cleanly.
- [ ] Confirm the plugin deactivates cleanly.
- [x] Confirm the plugin does not create database tables or options unnecessarily. Note: activation stores only the plugin version option.
- [x] Confirm admin UI text is polished and consistent. Note: admin UI copy was reviewed for concise wp-admin wording, and release metadata now uses Mamflow details.
- [x] Confirm screenshots or assets are not required for the MVP package.
- [x] Create a release ZIP from the `bulk-edit-learnpress-prices` folder if needed. Note: `npm run release` creates `release/bulk-edit-learnpress-prices-1.0.0.zip`.
- [ ] Install the release ZIP on a clean local WordPress site.
- [ ] Run the manual smoke test from activation through one successful bulk price update.
- [x] Record known limitations for version `1.0.0`.
- [x] Prepare version `1.1.0+` backlog items from the future roadmap. Note: WooCommerce sync, CSV import/export, advanced history reporting, and apply-to-all-filtered are documented as future scope.

## Version 1.0.0 Manual Test Exit Criteria
Use this section to decide whether version `1.0.0` can ship after manual testing.

- [ ] No fatal errors on plugin activation.
- [ ] LearnPress inactive state shows a graceful admin notice.
- [ ] Admin page loads for authorized users.
- [ ] Unauthorized users cannot access the menu or AJAX endpoints.
- [ ] Course filters work for the real site data.
- [ ] Pagination and sorting work after normal page load and AJAX reload.
- [ ] Preview modal shows correct before/after values.
- [ ] Set regular price updates `_lp_regular_price` and syncs `_lp_price`.
- [ ] Set sale price updates `_lp_sale_price`.
- [ ] Schedule sale price updates `_lp_sale_price`, `_lp_sale_start`, and `_lp_sale_end`.
- [ ] Remove sale price deletes `_lp_sale_price`.
- [ ] Percentage increase updates regular price only.
- [ ] Percentage decrease never creates negative prices.
- [ ] Invalid inputs are rejected with actionable messages.
- [ ] Final report counts updated, skipped, failed, and warnings correctly.
- [ ] Course edit screens reflect updated `_lp_regular_price` and `_lp_sale_price` values.
- [ ] Frontend course pages reflect updated prices after cache refresh.
- [ ] `npm run release` is rerun after the final accepted fix.

## Future Version Roadmap

### Version 1.1.0 - WooCommerce Sync and Data Portability
Goal: connect LearnPress course price updates with linked WooCommerce products and prepare export/import foundations.

Scope:
- [ ] Research LearnPress WooCommerce add-on meta/linking model on the target site.
- [ ] Add WooCommerce availability detection.
- [ ] Add LearnPress WooCommerce payment/add-on detection.
- [ ] Add a settings flag or filter to enable/disable WooCommerce sync.
- [ ] Resolve linked WooCommerce product IDs for each course.
- [ ] Sync regular price to `_regular_price`.
- [ ] Sync sale price to `_sale_price`.
- [ ] Sync active WooCommerce `_price` according to sale/regular state.
- [ ] Validate WooCommerce sale price never exceeds regular price.
- [ ] Clear WooCommerce product transients/caches after sync.
- [ ] Report per-course WooCommerce sync status in the final report.
- [ ] Keep LearnPress-only updates working when WooCommerce is inactive.
- [ ] Add developer filters for exportable course price row data.
- [ ] Add developer filters for importable price field mappings.
- [ ] Document WooCommerce sync behavior in `readme.txt`.
- [ ] Document new hooks/filters in `DEVELOPER-HOOKS.md`.
- [ ] Add manual tests for linked and unlinked WooCommerce products.
- [ ] Rebuild release ZIP with `npm run release`.

Out of scope for 1.1.0:
- [ ] Scheduled promotions.
- [ ] Advanced searchable history UI and rollback.
- [ ] Apply-to-all-filtered chunked processing.

### Version 1.2.0 - Advanced Audit Trail and Rollback
Goal: expand the basic version `1.0.0` price history into searchable reports and recovery tools.

Scope:
- [x] Decide storage model for price history: custom table vs post meta log. Note: version `1.0.0` stores a lightweight per-course history in post meta.
- [x] Record user ID, timestamp, action, previous prices, new prices, and request context. Note: basic recording exists in version `1.0.0`.
- [ ] Add an admin history view scoped to course price changes.
- [ ] Add filters by course, user, date range, and action.
- [ ] Add CSV export for history records if needed.
- [ ] Add rollback design for individual course price changes.
- [ ] Add rollback permission checks and nonce protection.
- [ ] Document retention and cleanup behavior.
- [ ] Add performance limits for large history datasets.
- [ ] Add manual tests for history creation and rollback.
- [ ] Rebuild release ZIP with `npm run release`.

### Version 1.3.0 - Apply To All Filtered Courses
Goal: safely update large filtered course sets beyond the current selected-page workflow.

Scope:
- [ ] Add an explicit "apply to all filtered courses" mode.
- [ ] Show the exact filtered course count before preview.
- [ ] Add server-side query limits and chunk size controls.
- [ ] Process updates in chunks through AJAX.
- [ ] Add progress UI with updated/skipped/failed counts.
- [ ] Prevent duplicate background runs.
- [ ] Allow safe cancellation before a chunk begins.
- [ ] Store operation state enough to recover from page refresh if practical.
- [ ] Keep selected-page updates as the default safer workflow.
- [ ] Add manual tests with 100+ and 500+ courses.
- [ ] Document server limit recommendations.
- [ ] Rebuild release ZIP with `npm run release`.

### Later Backlog
These are not planned for the immediate next release unless business priority changes.

- [ ] Scheduled price changes and automatic promotion windows.
- [ ] CSV import UI with dry-run validation.
- [ ] Bulk edit additional LearnPress meta fields such as duration or level.
- [ ] Role-based permission presets.
- [ ] Multisite compatibility review.
- [ ] REST API endpoints for external automation.
- [ ] WP-CLI commands for bulk price operations.
- [ ] Better onboarding or contextual help if support feedback shows confusion.

## Future Version Quality Gates
Every future version should satisfy these checks before packaging.

- [ ] Version header and `BELPCP_VERSION` are updated together.
- [ ] `readme.txt` changelog and upgrade notice are updated.
- [ ] `DEVELOPER-HOOKS.md` is updated for hook/filter changes.
- [ ] `languages/bulk-edit-learnpress-prices.pot` is regenerated.
- [ ] PHP syntax checks pass.
- [ ] JavaScript syntax check passes.
- [ ] Manual wp-admin smoke test passes.
- [ ] Known limitations are updated.
- [ ] `npm run release` creates the expected ZIP in `release/`.
