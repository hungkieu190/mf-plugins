# Plugin Structure - Quiz Importer for LearnPress

Complete overview of the plugin file structure and organization.

---

## 📁 Directory Structure

```
mf-quiz-importer-for-learnpress/
├── assets/                          # Frontend assets
│   ├── css/
│   │   ├── admin.css               # Admin styles
│   │   └── admin.min.css           # Minified admin styles
│   └── js/
│       └── admin.js                # Admin JavaScript
│
├── includes/                        # Core plugin files
│   ├── admin/                      # Admin functionality
│   │   ├── class-admin.php         # Admin class
│   │   ├── class-importer.php      # Import handler
│   │   └── views/
│   │       └── importer-page.php   # Admin page view
│   │
│   ├── class-quiz-parser.php       # Quiz data parser
│   ├── class-quiz-creator.php      # Quiz creator
│   └── class-question-importer.php # Question importer
│
├── languages/                       # Translation files
│   └── mf-quiz-importer-lp.pot     # Translation template
│
├── samples/                         # Sample files
│   ├── sample-quiz-complete.json   # Complete quiz JSON
│   ├── sample-quiz-with-questions.csv # Quiz with questions CSV
│   ├── sample-quiz.csv             # Simple quiz CSV
│   ├── sample-quiz.json            # Original quiz JSON
│   ├── sample-questions.json       # Questions JSON
│   └── sample-questions.csv        # Questions CSV
│
├── Documentation Files              # All .md files
│   ├── README.md                   # Main readme
│   ├── QUICK-START.md              # Quick start guide
│   ├── IMPORT-GUIDE.md             # Import guide
│   ├── QUESTION-TYPES.md           # Question types reference
│   ├── FEATURES.md                 # Features overview
│   ├── LEARNPRESS-INTEGRATION.md   # Integration guide
│   ├── UI-GUIDE.md                 # UI documentation
│   ├── DEBUG-GUIDE.md              # Debug guide
│   ├── TEST-GUIDE.md               # Test guide
│   ├── CHANGELOG.md                # Version history
│   ├── DOCUMENTATION-INDEX.md      # Documentation index
│   └── PLUGIN-STRUCTURE.md         # This file
│
└── mf-quiz-importer-for-learnpress.php # Main plugin file
```

---

## 🔧 Core Files

### Main Plugin File
**mf-quiz-importer-for-learnpress.php**
- Plugin header and metadata
- Main plugin class
- Initialization hooks
- Admin menu registration
- Asset enqueuing
- Activation/deactivation hooks

---

## 📦 Includes Directory

### Admin Classes

**includes/admin/class-admin.php**
- Admin functionality
- Settings registration
- AJAX handlers
- File upload handling
- Import processing

**includes/admin/class-importer.php**
- File format detection
- CSV parsing
- JSON parsing
- Excel parsing (placeholder)
- Quiz creation orchestration
- Question grouping

**includes/admin/views/importer-page.php**
- Admin page HTML
- Tab navigation
- Import forms
- Format guides
- Sample file links
- Documentation tab

---

### Core Classes

**includes/class-quiz-parser.php**
- Data parsing
- CSV parsing
- JSON parsing
- Data validation
- Data sanitization

**includes/class-quiz-creator.php**
- Quiz post creation
- Question creation
- Answer handling
- Meta data management
- LearnPress integration

**includes/class-question-importer.php**
- Question import logic
- Question type mapping
- Answer processing
- Quiz association
- Bulk import handling

---

## 🎨 Assets

### CSS Files

**assets/css/admin.css**
- Admin page styles
- Tab navigation
- Upload area
- Progress bars
- Result messages
- Documentation cards
- Responsive design

**assets/css/admin.min.css**
- Minified version
- Production use

---

### JavaScript Files

**assets/js/admin.js**
- File upload handling
- Form submission
- AJAX requests
- Progress tracking
- Result display
- Drag & drop support

---

## 📄 Documentation Files

### User Documentation

**README.md**
- Plugin overview
- Installation
- Quick start
- Features
- Examples
- Support

**QUICK-START.md**
- 3-step guide
- Quick examples
- Pro tips
- Common workflows

**IMPORT-GUIDE.md**
- Complete import instructions
- All file formats
- Detailed examples
- Best practices

**QUESTION-TYPES.md**
- Question type reference
- Type variations
- Format examples
- Common mistakes

**FEATURES.md**
- Complete feature list
- Technical details
- Use cases
- Roadmap

---

### Technical Documentation

**LEARNPRESS-INTEGRATION.md**
- Integration details
- API documentation
- Custom post types
- Meta fields
- Hooks and filters

**UI-GUIDE.md**
- UI/UX documentation
- Design principles
- Component library
- Styling guide

**DEBUG-GUIDE.md**
- Troubleshooting
- Error messages
- Debug mode
- Log files

**TEST-GUIDE.md**
- Test scenarios
- Quality assurance
- Validation steps

---

### Reference Documentation

**CHANGELOG.md**
- Version history
- Release notes
- Bug fixes
- Breaking changes

**DOCUMENTATION-INDEX.md**
- Documentation overview
- Reading order
- Quick reference

**PLUGIN-STRUCTURE.md**
- This file
- File structure
- Class overview

---

## 📦 Sample Files

### Quiz Samples

**sample-quiz-complete.json**
- 4 complete quizzes
- All question types
- With explanations
- 200+ lines

**sample-quiz-with-questions.csv**
- CSV with questions
- Multiple quizzes
- Shows grouping
- 10 rows

**sample-quiz.csv**
- Simple quiz metadata
- No questions
- Quick setup
- 3 rows

**sample-quiz.json**
- Original sample
- 3 quizzes
- Various examples
- 150+ lines

---

### Question Samples

**sample-questions.json**
- 5 sample questions
- All question types
- With explanations
- 80+ lines

**sample-questions.csv**
- 8 questions
- Various types
- Shows format
- 9 rows

---

## 🔌 Class Relationships

```
MF_Quiz_Importer_For_LearnPress (Main)
    │
    ├── MF_Quiz_Importer_Admin
    │   ├── Handles AJAX
    │   ├── Manages settings
    │   └── Renders admin page
    │
    ├── MF_Quiz_Importer
    │   ├── Detects file format
    │   ├── Parses data
    │   └── Creates quizzes
    │
    ├── MF_Quiz_Parser
    │   ├── Validates data
    │   ├── Sanitizes data
    │   └── Parses formats
    │
    ├── MF_Quiz_Creator
    │   ├── Creates quiz posts
    │   ├── Creates questions
    │   └── Manages meta data
    │
    └── MF_Question_Importer
        ├── Imports questions
        ├── Maps question types
        └── Associates with quiz
```

---

## 🔄 Data Flow

### Quiz Import Flow

```
1. User uploads file
   ↓
2. AJAX handler receives file
   ↓
3. File saved to temp directory
   ↓
4. MF_Quiz_Importer processes file
   ↓
5. MF_Quiz_Parser validates data
   ↓
6. MF_Quiz_Creator creates posts
   ↓
7. Questions created and linked
   ↓
8. Success/error response
   ↓
9. Temp file deleted
```

### Question Import Flow

```
1. User selects quiz & uploads file
   ↓
2. AJAX handler receives data
   ↓
3. File saved to temp directory
   ↓
4. MF_Quiz_Importer processes file
   ↓
5. MF_Question_Importer handles questions
   ↓
6. Questions created
   ↓
7. Questions linked to quiz
   ↓
8. Success/error response
   ↓
9. Temp file deleted
```

---

## 🗄️ Database Structure

### Custom Post Types

**lp_quiz**
- Quiz posts
- Standard WP post fields
- Custom meta fields

**lp_question**
- Question posts
- Standard WP post fields
- Custom meta fields

---

### Meta Fields

**Quiz Meta:**
- `_lp_duration` - Quiz duration
- `_lp_passing_grade` - Passing grade
- `_lp_retake_count` - Retake count
- `_lp_questions` - Array of question IDs

**Question Meta:**
- `_lp_type` - Question type
- `_lp_answer_options` - Answer options
- `_lp_answer` - Correct answer(s)
- `_lp_explanation` - Answer explanation

---

## 🔐 Security

### Implemented Security

1. **Nonce Verification**
   - All AJAX requests
   - Form submissions

2. **Capability Checks**
   - `manage_options` required
   - Admin-only access

3. **Data Sanitization**
   - All input sanitized
   - SQL injection prevention
   - XSS protection

4. **File Validation**
   - Type checking
   - Size limits
   - Extension validation

---

## 🎨 UI Components

### Admin Page Components

1. **Tab Navigation**
   - Import Quizzes
   - Import Questions
   - Documentation
   - Settings

2. **Upload Area**
   - Drag & drop
   - File selection
   - Visual feedback

3. **Progress Bar**
   - Animated progress
   - Status text
   - Smooth transitions

4. **Result Messages**
   - Success cards
   - Error cards
   - Statistics display

5. **Documentation Cards**
   - Guide links
   - Meta information
   - Download buttons

---

## 📊 Performance

### Optimization Techniques

1. **Efficient Parsing**
   - Stream processing
   - Memory management
   - Batch operations

2. **Asset Loading**
   - Minified CSS/JS
   - Conditional loading
   - Page-specific assets

3. **Database Queries**
   - Optimized queries
   - Proper indexing
   - Caching where possible

---

## 🔄 Hooks & Filters

### Actions

- `admin_init` - Register settings
- `admin_menu` - Add admin menu
- `admin_enqueue_scripts` - Enqueue assets
- `wp_ajax_mf_quiz_importer_upload` - Handle upload
- `wp_ajax_mf_quiz_importer_process` - Process import

### Filters

- `learn-press/default-question-types-support-answer-options`
- Custom sanitization filters
- Data validation filters

---

## 🌐 Internationalization

### Translation Ready

- Text domain: `mf-quiz-importer-lp`
- POT file included
- All strings translatable
- RTL support ready

---

## 📝 Code Standards

### WordPress Coding Standards

- PSR-4 autoloading
- WordPress naming conventions
- Proper documentation
- Security best practices

---

## 🔮 Future Structure

### Planned Additions

```
includes/
├── exporters/              # Export functionality
│   └── class-quiz-exporter.php
├── validators/             # Advanced validation
│   └── class-data-validator.php
└── integrations/           # Third-party integrations
    └── class-excel-handler.php
```

---

**Version:** 1.0.0
**Last Updated:** November 2024
