# Quiz Importer for LearnPress - Features

## 🎯 Main Features

### 1. Dual Import Modes
- **Import Complete Quizzes** - Import quizzes with all questions included
- **Import Questions Only** - Add questions to existing quizzes

### 2. Multiple File Formats
- ✅ **JSON** - Full support with nested questions
- ✅ **CSV** - Two formats:
  - Simple CSV (quiz metadata only)
  - Advanced CSV (quiz + questions in one file)
- ✅ **Excel (XLSX)** - Full support for Excel 2007+
  - Simple Excel (quiz metadata only)
  - Advanced Excel (quiz + questions in one file)
  - Questions only Excel

### 3. LearnPress Question Types
All standard LearnPress question types are supported:
- `true_or_false` - True/False questions
- `single_choice` - Single correct answer
- `multi_choice` - Multiple correct answers
- `fill_in_blanks` - Fill in the blanks

**Plus 20+ naming variations** automatically mapped to correct types!

### 4. Smart Features
- ✅ Auto-detect CSV format (simple vs. with questions)
- ✅ Group questions by quiz_title in CSV
- ✅ Support multiple correct answers
- ✅ Question explanations
- ✅ Flexible question type naming
- ✅ UTF-8 encoding support
- ✅ Validation and error handling

### 5. User-Friendly Interface
- 🎨 Modern, professional design
- 📊 Real-time progress tracking
- ✅ Success/error messages with details
- 📁 Drag & drop file upload
- 🎯 Quiz selection dropdown for question import
- 📥 Sample files included

---

## 📋 Import Options

### Tab 1: Import Quizzes
Import complete quizzes with questions from:
- JSON files with nested question arrays
- CSV files with quiz metadata only
- CSV files with quiz + questions combined

### Tab 2: Import Questions
Add questions to existing quizzes:
- Select target quiz from dropdown
- Import questions from JSON or CSV
- Questions are automatically added to selected quiz

### Tab 3: Settings
Configure default values:
- Default quiz duration
- Default passing grade
- Default retake count
- Auto-publish option

---

## 📦 Sample Files Included

### Quiz Import Samples
1. `sample-quiz-complete.json` - 4 complete quizzes with questions
2. `sample-quiz-with-questions.csv` - CSV with quiz + questions
3. `sample-quiz.csv` - Simple quiz metadata CSV
4. `sample-quiz.json` - Original quiz JSON

### Question Import Samples
1. `sample-questions.json` - 5 sample questions
2. `sample-questions.csv` - Questions in CSV format

---

## 🎨 UI/UX Features

### Upload Area
- Drag & drop support
- Click to browse
- File type validation
- Visual feedback on file selection
- Animated hover effects

### Progress Tracking
- Animated progress bar
- Real-time status updates
- Smooth transitions
- Loading indicators

### Results Display
- Success messages with counts
- Error messages with details
- Animated slide-in effects
- Color-coded feedback

### Download Buttons
- Icon-enhanced buttons
- Hover animations
- Clear file descriptions
- Direct download links

---

## 🔧 Technical Features

### Data Processing
- CSV parsing with proper escaping
- JSON validation
- Data sanitization
- Type mapping
- Error handling

### WordPress Integration
- Custom post types (lp_quiz, lp_question)
- Post meta management
- User permissions check
- AJAX file upload
- Nonce security

### Code Quality
- Object-oriented design
- Separation of concerns
- Reusable components
- Documented code
- Error logging

---

## 📚 Documentation

### Included Guides
1. **IMPORT-GUIDE.md** - Complete import instructions
2. **QUESTION-TYPES.md** - Question types reference
3. **FEATURES.md** - This file
4. **LEARNPRESS-INTEGRATION.md** - Integration details
5. **UI-GUIDE.md** - UI/UX documentation
6. **DEBUG-GUIDE.md** - Debugging help
7. **TEST-GUIDE.md** - Testing instructions

### In-App Help
- Format guide in each tab
- Sample file downloads
- Inline descriptions
- Tooltips and hints

---

## 🚀 Performance

- Efficient file processing
- Batch import support
- Memory-optimized parsing
- Temporary file cleanup
- Progress tracking

---

## 🔒 Security

- File type validation
- Nonce verification
- User capability checks
- Data sanitization
- SQL injection prevention
- XSS protection

---

## 🌐 Compatibility

- WordPress 5.8+
- PHP 7.4+
- LearnPress 4.0+
- PHP ZipArchive extension (for Excel)
- PHP SimpleXML extension (for Excel)
- Modern browsers
- UTF-8 encoding

---

## 📈 Future Enhancements

- [x] Excel (XLSX) import - ✅ Completed v1.0.3
- [ ] Excel (XLS) legacy format
- [ ] Export functionality
- [ ] Bulk edit questions
- [ ] Question bank management
- [ ] Import from other LMS
- [ ] Advanced question types
- [ ] Media file support
- [ ] Import scheduling

---

## 💡 Use Cases

### For Course Creators
- Quickly import quiz content
- Migrate from other platforms
- Bulk create quizzes
- Update existing quizzes

### For Developers
- Programmatic quiz creation
- Data migration scripts
- Automated testing
- Content generation

### For Administrators
- Manage quiz library
- Standardize quiz format
- Quality control
- Content backup

---

## 🎓 Best Practices

1. **Start Small** - Test with a few questions first
2. **Use Templates** - Download and modify sample files
3. **Validate Data** - Check format before importing
4. **Backup First** - Always backup before bulk imports
5. **Review Results** - Check imported content
6. **Use Explanations** - Help students learn from mistakes

---

## 📞 Support

- Documentation: Check included MD files
- Samples: Use provided sample files
- Website: https://mamflow.com
- Issues: Check DEBUG-GUIDE.md

---

**Version:** 1.0.0
**Last Updated:** November 2024
**Author:** MamFlow
