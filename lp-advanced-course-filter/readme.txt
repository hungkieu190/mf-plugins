=== Advanced Course Filter for LearnPress ===
Contributors: mamflow
Tags: learnpress, courses, filter, ajax
Requires at least: 6.0
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
Text Domain: lp-advanced-course-filter
Domain Path: /languages

AJAX course filters for LearnPress.

== Description ==

Advanced Course Filter for LearnPress adds a WordPress widget, shortcode, Gutenberg block, and Elementor widget for filtering LearnPress courses by category, price, level, rating, and keyword.

Features:

* AJAX course filtering without a page reload.
* Category, price, level, rating, and keyword filters.
* Sidebar and horizontal layouts.
* Active filter tags, reset, sorting, and load more.
* WordPress widget, shortcode, Gutenberg block, and Elementor widget.

== Usage ==

Add this shortcode to a page:

[lp_advanced_course_filter layout="sidebar" per_page="9" columns="3"]

Use layout="horizontal" for a top filter bar.

To replace the default sidebar filter, add the "LearnPress Advanced Course Filter" widget in Appearance > Widgets or the Site Editor widget area.

== Shortcode Attributes ==

* layout: sidebar or horizontal. Default: sidebar.
* per_page: 1 to 48. Default: 9.
* columns: 1 to 4. Default: 3.

== Gutenberg Block ==

Search for "Advanced Course Filter" in the block inserter.

== Elementor Widget ==

The widget is registered automatically when Elementor is active.

== Testing Notes ==

Before release, test the plugin with real LearnPress course data. The rating filter should be verified on the target site because LearnPress review/rating metadata can vary by setup and installed addons.

== Changelog ==

= 1.0.0 =
* Initial MVP.
