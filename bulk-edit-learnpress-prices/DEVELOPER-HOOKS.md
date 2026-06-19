# Developer Hooks

This document lists the extension points available in Bulk Edit LearnPress Course Prices 1.0.0.

## Filters

### `bulk_edit_lp_price_capability`

Change the capability required to access the bulk price editor.

```php
add_filter( 'bulk_edit_lp_price_capability', function ( $capability ) {
	return 'manage_options';
} );
```

### `bulk_edit_lp_price_supported_post_statuses`

Adjust the post statuses available in the status filter.

### `bulk_edit_lp_price_sanitized_course_filters`

Inspect or adjust sanitized course filter values before they are used for queries.

### `bulk_edit_lp_price_course_category_taxonomies`

Customize the taxonomy candidates used for course categories. The plugin checks `course_category` and `lp_course_category` by default.

### `bulk_edit_lp_price_category_options`

Adjust category options shown in the admin filter.

### `bulk_edit_lp_price_instructor_options`

Adjust instructor options shown in the admin filter.

### `bulk_edit_lp_price_supported_bulk_actions`

Adjust the available bulk price action labels or keys.

### `bulk_edit_lp_price_course_query_args`

Modify `WP_Query` arguments before courses are loaded.

### `bulk_edit_lp_price_preview_rows`

Inspect or adjust preview and update report rows before they are returned in AJAX responses.

### `bulk_edit_lp_price_update_summary`

Inspect or adjust preview/update summary counts before they are returned in AJAX responses.

## Actions

### `bulk_edit_lp_price_before_course_update`

Runs before a course price update is applied.

Parameters:

* `$course_id` - Course post ID.
* `$changes` - Planned regular and sale price changes.
* `$context` - Bulk action context.

### `bulk_edit_lp_price_course_price_meta_updated`

Runs after price metadata is written and cache cleanup is requested.

Parameters:

* `$course_id` - Course post ID.
* `$changes` - Applied regular and sale price changes.
* `$action` - Bulk action key.

### `bulk_edit_lp_price_after_course_update`

Runs after a course update attempt finishes.

Parameters:

* `$course_id` - Course post ID.
* `$changes` - Planned or applied price changes.
* `$result` - Per-course result row.
* `$context` - Bulk action context.

### `bulk_edit_lp_price_after_bulk_operation`

Runs after the complete bulk update operation finishes.

Parameters:

* `$summary` - Updated, skipped, failed, and warning counts.
* `$rows` - Per-course result rows.
* `$context` - Bulk action context.

## Future Integration Notes

WooCommerce sync, CSV import/export, and price history should use the update actions above rather than duplicating update logic. Version 1.0.0 updates LearnPress course metadata only.

Regular price edits are written to `_lp_regular_price`, which is the editable regular price used by current LearnPress course screens. Sale price edits are written to `_lp_sale_price`. Scheduled sale edits additionally write `_lp_sale_start` and `_lp_sale_end`, matching LearnPress' built-in schedule fields. The plugin also syncs `_lp_price` after each successful update because LearnPress uses that meta as an active/cache price for compatibility and querying.

## Price History Storage

Version 1.0.0 stores lightweight per-course price history in the `_belpcp_price_history` post meta key. Each entry includes the local timestamp, GMT timestamp, user ID, user label, bulk action, bulk value, previous regular/sale prices, and new regular/sale prices.

The history is capped by `BELPCP_MAX_PRICE_HISTORY_ITEMS` to keep post meta small. Advanced searchable reports or rollback workflows should use a future dedicated storage model if the dataset grows beyond per-course review needs.
