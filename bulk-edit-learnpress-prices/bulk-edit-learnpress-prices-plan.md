# Bulk Edit LearnPress Course Prices - Plugin Concept

**Plugin Name:** Bulk Edit LearnPress Course Prices  
**Plugin Slug:** `bulk-edit-learnpress-prices`  
**Folder Name:** `bulk-edit-learnpress-prices`  
**Current Version:** 1.0.0  
**Author:** Mamflow  
**Short Description:** Powerful bulk editing tool for LearnPress course prices. Edit regular price and sale price of hundreds of courses in seconds.

---

## 1. Objective

This plugin solves a common pain point for LearnPress website owners: the need to quickly update prices across many courses without editing them one by one.

### Target Users
- Websites with 20 to 1000+ courses
- Online course creators who frequently run promotions and sales
- Agencies and freelancers managing multiple LearnPress sites

---

## 2. Core Features

### Phase 1 – MVP (Version 1.0.0)

- Dedicated admin page under LearnPress menu
- Advanced filtering system:
  - All / Paid / Free courses
  - Course categories
  - Price range
  - Instructor (if available)
  - Post status (publish, draft, etc.)
- Table view with checkbox multi-select
- Bulk Actions:
  - Set Regular Price (`_lp_price`)
  - Set Sale Price (`_lp_sale_price`)
  - Remove Sale Price
  - Increase Price by Percentage
  - Decrease Price by Percentage
- Preview changes before applying
- Confirmation modal with summary
- Success report showing number of updated courses

### Phase 2 (Version 1.1.0+)

- WooCommerce integration (sync prices with linked WooCommerce products)
- Export current course prices to CSV
- Import prices from CSV
- Price change history / log
- Bulk edit additional fields (`_lp_duration`, `_lp_level`, etc.)
- "Apply to all filtered courses" option
- Search by course name

---

## 3. Plugin Folder Structure

bulk-edit-learnpress-prices/
├── bulk-edit-learnpress-prices.php          # Main plugin file
├── readme.txt
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
├── includes/
│   ├── class-bulk-edit-lp-price.php
│   ├── class-lp-price-list-table.php        # WP_List_Table
│   └── functions.php
├── templates/
│   └── admin-page.php
└── languages/
└── bulk-edit-learnpress-prices.pot


---

## 4. Technical Details

### Key Post Meta
- `_lp_price` – Regular Price
- `_lp_sale_price` – Sale Price
- Post Type: `lp_course`

### Main Classes
- `Bulk_Edit_LP_Price` (core class)
- `LP_Price_List_Table` extends `WP_List_Table`

### AJAX Actions
- `bulk_edit_lp_load_courses`
- `bulk_edit_lp_update_prices`
- `bulk_edit_lp_preview_changes` (optional)

### Security Requirements
- Proper nonce verification on all AJAX requests
- Capability checks (`manage_options` or `edit_others_lp_courses`)
- Full data sanitization and validation

---

## 5. Admin Interface

**Page Title:** Bulk Edit LearnPress Course Prices

**Layout:**
- Filter bar at the top
- "Load / Refresh Courses" button
- DataTable / WP_List_Table with columns:
  - Checkbox
  - Course ID
  - Course Name (linked to edit page)
  - Current Regular Price
  - Current Sale Price
  - Status
- Bulk action panel at the bottom:
  - Action selector
  - Value input (price or percentage)
  - Preview & Apply buttons

---

## 6. Development Notes

- Use `WP_List_Table` for the course list
- Handle both `_lp_price` and `_lp_sale_price`
- Support percentage calculations safely
- Add action hooks and filters for future extensibility
- Ensure compatibility with LearnPress 4.x and 5.x

---

## 7. Future Roadmap (Pro Version)

- Scheduled price changes (automatic promotion)
- Role-based permissions
- Multi-site support
- Bulk edit more LearnPress meta fields
- One-click integration with popular LearnPress add-ons

---

## 8. Testing Checklist

- Test with 100+ courses
- Test percentage increase/decrease
- Test sale price removal
- Test with WooCommerce synced courses
- Test on PHP 8.0+ and latest WordPress

---

**Recommended Plugin Name for WordPress.org:**
"Bulk Edit LearnPress Course Prices – Fast & Easy"

**Plugin Slug:** `bulk-edit-learnpress-prices`

---

This document is ready to be handed over to any developer for implementation.
