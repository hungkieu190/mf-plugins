=== Bulk Edit LearnPress Course Prices ===
Contributors: mamflow
Tags: learnpress, course, prices, bulk edit, admin
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Powerful bulk editing tool for LearnPress course prices. Edit regular price and sale price of hundreds of courses in seconds.

== Description ==

Bulk Edit LearnPress Course Prices helps LearnPress site owners update course pricing from a dedicated WordPress admin screen.

* A dedicated admin page under LearnPress.
* Filters for paid/free courses, course categories, price ranges, instructors, and post status.
* A course table with checkbox selection.
* Bulk actions for regular prices, sale prices, scheduled sale prices, sale removal, and percentage adjustments.
* Preview and confirmation before applying changes.
* A final success report after updates complete.
* Per-course price change history for admin review.

== Supported Bulk Actions ==

* Set Regular Price updates the LearnPress `_lp_regular_price` meta value for selected courses and syncs `_lp_price` for LearnPress active price compatibility.
* Set Sale Price updates the LearnPress `_lp_sale_price` meta value for selected courses.
* Schedule Sale Price updates `_lp_sale_price`, `_lp_sale_start`, and `_lp_sale_end` using LearnPress' built-in scheduled sale fields.
* Remove Sale Price deletes the `_lp_sale_price` meta value and syncs `_lp_price` back to the regular price.
* Increase Price by Percentage adjusts regular prices only and preserves existing sale prices.
* Decrease Price by Percentage adjusts regular prices only, prevents negative prices, and preserves existing sale prices.

Every update is recalculated server-side during the final apply request, so the plugin does not trust client-provided preview values.

== Price Change History ==

When a bulk action actually changes a course price, the plugin records the time, admin user, action, previous regular/sale prices, and new regular/sale prices. The course table shows the number of recorded price changes for each course, and admins can open the history from that row.

== Installation ==

1. Upload the `bulk-edit-learnpress-prices` folder to `/wp-content/plugins/`.
2. Activate the plugin through the WordPress Plugins screen.
3. Open the plugin admin page from the LearnPress menu.

== Compatibility ==

This release targets LearnPress 4.x and 5.x course price metadata and WordPress 6.0 or newer. The plugin does not require WooCommerce and does not sync linked WooCommerce product prices in version 1.0.0.

== Known Limitations ==

Version 1.0.0 updates LearnPress course price metadata only. WooCommerce product price sync, CSV import/export, advanced history reporting, and "apply to all filtered courses" are planned for future versions.
The built-in price history records the latest course price changes in post meta; advanced reporting and rollback are planned for a future version.

Developer hooks and filters are documented in `DEVELOPER-HOOKS.md`.

== Frequently Asked Questions ==

= Does this plugin require LearnPress? =

Yes. This plugin is designed for the LearnPress `lp_course` post type and LearnPress price meta fields.

= Which price fields does it update? =

The plugin updates `_lp_regular_price` for the editable regular price and `_lp_sale_price` for sale price. Scheduled sale updates also write `_lp_sale_start` and `_lp_sale_end`. It keeps LearnPress' `_lp_price` active price meta synchronized after regular or sale price changes.

= Who can use the bulk editor? =

Users need the LearnPress course management capability when available, or `manage_options` as a fallback. Each selected course is also checked before price metadata is changed.

= What happens when I remove a sale price? =

The `_lp_sale_price` meta value is deleted. The final report marks the course as failed if the value cannot be removed.

= Does it sync WooCommerce products? =

WooCommerce price sync is planned for a future version.

= Why are some selected courses skipped? =

Courses are skipped when they are not LearnPress courses, the current user cannot edit them, or the requested sale price would be greater than the regular price.

== Changelog ==

= 1.0.0 =

* Initial release.

== Upgrade Notice ==

= 1.0.0 =

Initial release.
