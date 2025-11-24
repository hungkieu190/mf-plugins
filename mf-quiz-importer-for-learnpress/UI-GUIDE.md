# UI Guide

## Admin Page

The plugin admin page is divided into 4 main tabs:

### 1. Import Quizzes Tab

**URL:** `wp-admin/admin.php?page=mf-quiz-importer&tab=import-quiz`

**Features:**
- Upload CSV, Excel, or JSON files
- Import quizzes into LearnPress
- View import results
- File format guide
- Download sample files

**Layout:**
- Left column: Upload form and import
- Right column: Format guide and sample downloads

**Components:**

1. **Upload Area**
   - Drag & drop or click to select file
   - Display selected file name
   - "Import Quizzes" button

2. **Progress Bar**
   - Shows during import
   - Loading animation
   - Status text

3. **Result Message**
   - Success: Green color, shows imported/failed count
   - Error: Red color, shows error message

4. **File Format Guide**
   - CSV format instructions
   - JSON format instructions
   - Question types list
   - Sample file download buttons

### 2. Import Questions Tab

**URL:** `wp-admin/admin.php?page=mf-quiz-importer&tab=import-questions`

**Features:**
- Select target quiz
- Upload questions file
- Import questions to existing quiz
- View import results

**Layout:**
- Single column layout
- Quiz selector at top
- Upload area below
- Format guide on right

**Components:**

1. **Quiz Selector**
   - Dropdown with all quizzes
   - Shows quiz title and ID
   - Required field

2. **Upload Area**
   - Same as Import Quizzes tab
   - File type validation

3. **Questions Format Guide**
   - CSV format for questions
   - JSON format for questions
   - Question types reference

### 3. Documentation Tab

**URL:** `wp-admin/admin.php?page=mf-quiz-importer&tab=documentation`

**Features:**
- View all documentation
- Read docs in modal
- Download documentation files
- Access sample files

**Layout:**
- Card-based layout
- Main guides section
- Additional resources grid
- Sample files section

**Components:**

1. **Documentation Cards**
   - Title with icon
   - Description
   - View and Download buttons
   - Meta information (read time, tags)

2. **Documentation Modal**
   - Full-screen overlay
   - Markdown rendering
   - Close button
   - Scrollable content

3. **Sample Files Section**
   - Quiz import samples
   - Question import samples
   - Download buttons with icons

### 4. Settings Tab

**URL:** `wp-admin/admin.php?page=mf-quiz-importer&tab=settings`

**Features:**
- Configure default settings for quizzes
- Save settings to database

**Layout:**
- Single column, centered
- WordPress standard form table

**Settings:**

1. **Default Quiz Duration**
   - Input: Number (minutes)
   - Default: 60 minutes
   - Used when import file doesn't specify duration

2. **Default Passing Grade**
   - Input: Number (0-100%)
   - Default: 70%
   - Used when import file doesn't specify passing_grade

3. **Default Retake Count**
   - Input: Number (0 = unlimited)
   - Default: 0
   - Used when import file doesn't specify retake_count

4. **Auto Publish**
   - Checkbox
   - Default: Unchecked (save as draft)
   - Automatically publish quizzes after import

## Navigation

### Tab Navigation
- Tabs at top of page, below title
- Active tab has blue color and white background
- Hover effect on mouse over
- Icons for each tab:
  - Import Quizzes: Learn icon
  - Import Questions: Help icon
  - Documentation: Book icon
  - Settings: Settings icon

### Breadcrumb
```
LearnPress > Quiz Importer
```

## Responsive Design

### Desktop (>= 1024px)
- Import tabs: 2 columns layout
- Documentation: Card grid
- Settings tab: Single column, max-width 800px
- Full width tabs navigation

### Tablet (768px - 1023px)
- Import tabs: Single column
- Documentation: Single column cards
- Settings tab: Single column
- Stacked layout

### Mobile (< 768px)
- All single column
- Smaller padding
- Touch-friendly buttons
- Responsive form inputs
- Full-width buttons

## Color Scheme

### Primary Colors
- Blue: `#2271b1` (WordPress admin blue)
- Green: `#00a32a` (Success)
- Red: `#d63638` (Error)
- White: `#ffffff` (Text on buttons)

### Background Colors
- White: `#fff` (Cards)
- Light gray: `#f6f7f7` (Upload area)
- Light blue: `#f0f6fc` (File info, hover states)
- Gradient: Blue gradient for buttons and headers

### Border Colors
- Gray: `#ccd0d4` (Default borders)
- Blue: `#2271b1` (Active/hover)
- Green: `#00a32a` (Success)
- Red: `#d63638` (Error)

## Typography

### Headings
- H1: Page title (WordPress default)
- H2: Card titles (20px, bold)
- H3: Section titles (17px, semi-bold)
- H4: Subsection titles (15px, semi-bold)

### Body Text
- Regular: 14px
- Small: 13px (code, descriptions)
- Large: 16px (buttons, upload text)

### Font Family
- System fonts (WordPress default)
- Monospace for code blocks

## Animations

### Transitions
- Tab hover: 0.2s ease
- Button hover: 0.3s ease
- Upload area hover: 0.4s cubic-bezier
- Card hover: 0.3s ease

### Loading Animations
- Progress bar fill: 0.5s cubic-bezier
- Shimmer effect: 1.5s infinite
- Button spinner: 0.8s linear infinite
- Modal fade: 0.3s ease
- Slide down: 0.3s ease

### Hover Effects
- Button lift: translateY(-2px)
- Card lift: translateY(-2px)
- Shadow increase on hover
- Color transitions

## Accessibility

### Keyboard Navigation
- Tab key: Navigate between tabs and form fields
- Enter: Submit forms, open modals
- Space: Toggle checkboxes
- Escape: Close modals

### Screen Readers
- Proper ARIA labels
- Semantic HTML
- Alt text for icons
- Descriptive button text

### Focus States
- Visible focus outline
- High contrast focus indicators
- Focus trap in modals

## Best Practices

### User Experience
1. Clear visual feedback for all actions
2. Loading states for async operations
3. Success/error messages with details
4. Helpful tooltips and descriptions
5. Sample files for reference
6. In-app documentation viewer
7. Smooth animations

### Performance
1. Minimal JavaScript
2. Optimized CSS
3. Lazy loading where appropriate
4. Efficient AJAX requests
5. Conditional asset loading

### Consistency
1. Follow WordPress admin design patterns
2. Use WordPress UI components
3. Consistent spacing and alignment
4. Standard form layouts
5. Dashicons for icons

## Documentation Modal

### Features
- Full-screen overlay
- Markdown rendering
- Syntax highlighting
- Scrollable content
- Close on ESC or outside click
- Beautiful typography

### Markdown Support
- Headers (H1-H4)
- Bold and italic
- Code blocks
- Inline code
- Links
- Lists
- Blockquotes
- Horizontal rules
- Tables

### Styling
- Gradient header
- Clean white body
- Syntax-highlighted code
- Responsive design
- Smooth animations

## Future Enhancements

Potential improvements for future versions:

1. **Import History Tab**
   - View past imports
   - Re-import or undo
   - Export logs

2. **Advanced Settings Tab**
   - Custom field mapping
   - Import filters
   - Batch processing options

3. **Help Tab**
   - Video tutorials
   - FAQ section
   - Troubleshooting guide

4. **Dashboard Widget**
   - Quick import from dashboard
   - Recent imports summary
   - Quick stats

5. **Bulk Operations**
   - Select multiple files
   - Batch import
   - Progress tracking

## Screenshots

### Import Quizzes Tab
```
┌─────────────────────────────────────────────────────────┐
│ Quiz Importer                                           │
├─────────────────────────────────────────────────────────┤
│ [Import Quizzes] [Import Questions] [Docs] [Settings]  │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ ┌──────────────────┐  ┌──────────────────┐            │
│ │ Import Quizzes   │  │ File Format Guide│            │
│ │                  │  │                  │            │
│ │ [Upload Area]    │  │ CSV Options      │            │
│ │                  │  │ JSON Format      │            │
│ │ [Import Button]  │  │ Question Types   │            │
│ │                  │  │                  │            │
│ │ [Progress Bar]   │  │ [Sample Files]   │            │
│ │                  │  │                  │            │
│ │ [Result Message] │  │                  │            │
│ └──────────────────┘  └──────────────────┘            │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Documentation Tab
```
┌─────────────────────────────────────────────────────────┐
│ Quiz Importer                                           │
├─────────────────────────────────────────────────────────┤
│ [Import Quizzes] [Import Questions] [Docs] [Settings]  │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ ┌──────────────────────────────────────┐               │
│ │ 📖 Quick Start Guide                 │               │
│ │ Get started in 3 simple steps        │               │
│ │ [View Guide] [Download]              │               │
│ │ ⏱ 5 min read • ⭐ Recommended        │               │
│ └──────────────────────────────────────┘               │
│                                                         │
│ ┌──────────────────────────────────────┐               │
│ │ 📚 Complete Import Guide             │               │
│ │ Comprehensive instructions           │               │
│ │ [View Guide] [Download]              │               │
│ │ ⏱ 15 min read • JSON, CSV formats    │               │
│ └──────────────────────────────────────┘               │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

## Code Structure

### HTML Structure
```html
<div class="wrap mf-quiz-importer-wrap">
    <h1>Quiz Importer</h1>
    
    <nav class="nav-tab-wrapper">
        <a href="?tab=import-quiz" class="nav-tab nav-tab-active">
            <span class="dashicons dashicons-welcome-learn-more"></span>
            Import Quizzes
        </a>
        <a href="?tab=import-questions" class="nav-tab">
            <span class="dashicons dashicons-editor-help"></span>
            Import Questions
        </a>
        <a href="?tab=documentation" class="nav-tab">
            <span class="dashicons dashicons-book"></span>
            Documentation
        </a>
        <a href="?tab=settings" class="nav-tab">
            <span class="dashicons dashicons-admin-settings"></span>
            Settings
        </a>
    </nav>
    
    <div class="mf-quiz-importer-container">
        <!-- Tab content here -->
    </div>
</div>
```

### CSS Classes
- `.mf-quiz-importer-wrap` - Main wrapper
- `.nav-tab-wrapper` - Tabs navigation
- `.nav-tab` - Individual tab
- `.nav-tab-active` - Active tab
- `.mf-quiz-importer-container` - Content container
- `.mf-quiz-importer-card` - Content card
- `.mf-doc-card` - Documentation card
- `.mf-doc-modal` - Documentation modal
- `.mf-upload-area` - Upload area
- `.mf-progress` - Progress bar container
- `.mf-result` - Result message

### JavaScript Events
- File selection
- Form submission
- AJAX upload
- AJAX processing
- Success/error handling
- Modal open/close
- Documentation loading
- Markdown rendering

## Component Library

### Buttons
- Primary: Blue gradient, white text
- Secondary: White background, blue border
- Download: With download icon
- View: With visibility icon

### Cards
- Standard card: White background, shadow
- Documentation card: Hover effects
- Support card: Blue gradient background

### Forms
- Upload area: Drag & drop support
- File info: Green border when file selected
- Progress bar: Animated gradient
- Result messages: Color-coded

### Modal
- Full-screen overlay
- Centered content
- Gradient header
- Scrollable body
- Close button with animation

---

**Version:** 1.0.0
**Last Updated:** November 2024
