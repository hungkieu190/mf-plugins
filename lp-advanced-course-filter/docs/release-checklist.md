# Release Checklist

Use this checklist before tagging or uploading Advanced Course Filter for LearnPress 1.0.0.

## Environment

- WordPress 6.0 or newer.
- PHP 8.1 or newer.
- LearnPress 4.2 or newer with sample `lp_course` posts.
- At least one course category, one free course, one paid course, and courses with different levels.
- Elementor installed for widget verification, if Elementor support is advertised.

## Functional Tests

- Activate the plugin while LearnPress is active.
- Deactivate LearnPress and confirm the admin notice appears without a fatal error.
- Add `[lp_advanced_course_filter layout="sidebar" per_page="9" columns="3"]` to a page and confirm the initial course grid renders.
- Change `layout` to `horizontal` and confirm the top filter bar renders.
- Add the "LearnPress Advanced Course Filter" widget to the course/archive sidebar and confirm it can replace the default filter widget.
- Filter by category, price, level, rating, and keyword.
- Combine multiple filters and confirm the result count updates.
- Use active filter tags to remove individual filters.
- Use Reset and confirm the default course list returns.
- Use sorting: Newest, Title A-Z, Price low to high, Price high to low.
- Use Load more and confirm the next page appends without duplicating the previous page.
- Confirm AJAX requests succeed for both logged-in and logged-out visitors.

## Editor Tests

- Insert the Gutenberg block and change Layout, Courses per page, and Columns.
- Confirm the server-side block preview renders in the editor and frontend.
- Insert the Elementor widget and change Layout, Courses per page, and Columns.
- Confirm the Elementor widget renders in editor preview and frontend.

## Compatibility Checks

- Test on the active production theme.
- Test on Eduma or the target ThimPress theme if available.
- Test desktop, tablet, and mobile widths.
- Confirm course thumbnails, title, category, level, rating, price, and link display correctly.
- Verify the rating filter against real LearnPress review data, because rating storage can vary by LearnPress setup/addons.

## Release Assets

- Regenerate `languages/lp-advanced-course-filter.pot` after text changes.
- Create Vietnamese `.po/.mo` files if Vietnamese release copy is required.
- Prepare screenshots for shortcode, Gutenberg, Elementor, sidebar, horizontal, and mobile layouts.
- Build a clean zip that contains only the `lp-advanced-course-filter` plugin directory.
- Install the zip on a clean WordPress site and repeat the smoke test.
