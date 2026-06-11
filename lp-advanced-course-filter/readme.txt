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

Advanced Course Filter for LearnPress adds a WordPress widget, shortcode, Gutenberg block, and Elementor widget that replace the default LearnPress course filter and update the native LearnPress course archive/list.

Features:

* Uses the native LearnPress course archive AJAX/list refresh flow.
* Category, price, tag, author, level, type, and keyword filters.
* Sidebar and horizontal layouts.
* Active filter tags, reset, sorting, and load more.
* WordPress widget, shortcode, Gutenberg block, and Elementor widget.

== Usage ==

Add this shortcode to a page:

[lp_advanced_course_filter layout="sidebar" target=".lp-list-courses-default"]

Full native replacement example:

[lp_advanced_course_filter layout="sidebar" target=".lp-list-courses-default" fields="search,price,category,tag,author,level,type,btn_submit,btn_reset" category_depth="2" rest="0" hide_count_zero="1" search_suggestion="1"]

Use layout="horizontal" for a top filter bar.
The filter targets the native LearnPress course list `.lp-list-courses-default` by default.

To replace the default sidebar filter, add the "LearnPress Advanced Course Filter" widget in Appearance > Widgets or the Site Editor widget area.
The widget uses the native LearnPress course list target automatically.

== Shortcode Attributes ==

* layout: sidebar or horizontal. Default: sidebar.
* target: CSS selector for the LearnPress course list to update. Default: .lp-list-courses-default.
* fields: comma-separated LearnPress filter fields. Default: search,price,category,tag,author,level,type,btn_submit,btn_reset.
* category_depth: number of category levels shown. Default: 2.
* rest: load the filter widget via LearnPress REST widget flow. Default: 0.
* hide_count_zero: hide filter options with zero matching courses. Default: 1.
* search_suggestion: enable LearnPress keyword suggestions. Default: 1.

== Gutenberg Block ==

Search for "Advanced Course Filter" in the block inserter.

== Elementor Widget ==

The widget is registered automatically when Elementor is active.

== Testing Notes ==

Before release, test the plugin with real LearnPress course data. The filter should update the existing LearnPress course archive/list, not render a second course result area inside the widget.

== Changelog ==

= 1.0.0 =
* Initial MVP.
