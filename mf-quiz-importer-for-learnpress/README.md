# Quiz Importer for LearnPress

A powerful and user-friendly WordPress plugin to import quizzes and questions into LearnPress LMS from CSV, JSON, and Excel files.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-5.8%2B-blue.svg)
![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)
![LearnPress](https://img.shields.io/badge/learnpress-4.0%2B-green.svg)

---

## 🌟 Features

### Dual Import Modes
- **Import Complete Quizzes** - Import quizzes with all questions included
- **Import Questions Only** - Add questions to existing quizzes

### Multiple File Formats
- ✅ JSON - Full support with nested questions
- ✅ CSV - Two formats (simple & with questions)
- ✅ Excel (XLSX) - Full support for Excel 2007+

### All LearnPress Question Types
- `true_or_false` - True/False questions
- `single_choice` - Single correct answer
- `multi_choice` - Multiple correct answers
- `fill_in_blanks` - Fill in the blanks

### Smart Features
- Auto-detect CSV format
- Support multiple correct answers
- Question explanations
- 20+ question type variations
- UTF-8 encoding support
- Real-time progress tracking

### Professional UI
- Modern, clean design
- Drag & drop file upload
- Animated progress bars
- Success/error messages
- Sample files included

---

## 📦 Installation

1. Download the plugin
2. Upload to `/wp-content/plugins/` directory
3. Activate through WordPress admin
4. Go to **LearnPress → Quiz Importer**

### Requirements
- WordPress 5.8 or higher
- PHP 7.4 or higher
- LearnPress 4.0 or higher
- PHP ZipArchive extension (for Excel support)
- PHP SimpleXML extension (for Excel support)

---

## 🚀 Quick Start

### Import a Complete Quiz

1. Go to **LearnPress → Quiz Importer**
2. Click **Import Quizzes** tab
3. Download a sample file
4. Modify with your content
5. Upload and import

### Import Questions to Existing Quiz

1. Go to **LearnPress → Quiz Importer**
2. Click **Import Questions** tab
3. Select target quiz
4. Upload questions file
5. Import

**See [QUICK-START.md](QUICK-START.md) for detailed examples**

---

## 📋 File Formats

### JSON Format (Recommended)

```json
[
  {
    "title": "My Quiz",
    "description": "Quiz description",
    "duration": 60,
    "passing_grade": 70,
    "retake_count": 0,
    "questions": [
      {
        "title": "What is 2+2?",
        "content": "Simple math",
        "type": "single_choice",
        "answers": [
          {"text": "3", "correct": false},
          {"text": "4", "correct": true}
        ],
        "explanation": "2+2 equals 4"
      }
    ]
  }
]
```

### CSV Format (Simple)

```csv
title,description,duration,passing_grade,retake_count
"My Quiz","Description",60,70,0
```

**💡 Tip:** Open CSV files in Excel or Google Sheets for easier editing!

### CSV Format (With Questions)

```csv
quiz_title,quiz_description,duration,passing_grade,retake_count,question_title,question_content,question_type,answer_1,answer_2,correct_answers,explanation
"My Quiz","Description",30,70,0,"Q1","Content",single_choice,A,B,2,"B is correct"
```

### Excel Format (XLSX)

Excel files use the same format as CSV - just open, edit, and save!

**How to use:**
1. Download sample CSV
2. Open in Excel or Google Sheets
3. Edit like a spreadsheet
4. Save as XLSX or CSV
5. Import to plugin

---

## 📚 Documentation

- **[QUICK-START.md](QUICK-START.md)** - Get started in 3 steps
- **[IMPORT-GUIDE.md](IMPORT-GUIDE.md)** - Complete import instructions
- **[QUESTION-TYPES.md](QUESTION-TYPES.md)** - Question types reference
- **[FEATURES.md](FEATURES.md)** - All features explained
- **[LEARNPRESS-INTEGRATION.md](LEARNPRESS-INTEGRATION.md)** - Integration details

---

## 🎯 Use Cases

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

## 💡 Examples

### Example 1: Math Quiz

```json
{
  "title": "Basic Math",
  "duration": 30,
  "passing_grade": 70,
  "questions": [
    {
      "title": "What is 5 + 3?",
      "type": "single_choice",
      "answers": [
        {"text": "7", "correct": false},
        {"text": "8", "correct": true},
        {"text": "9", "correct": false}
      ]
    }
  ]
}
```

### Example 2: True/False Quiz

```json
{
  "title": "Science Facts",
  "questions": [
    {
      "title": "Is the Sun a star?",
      "type": "true_or_false",
      "answers": [
        {"text": "True", "correct": true},
        {"text": "False", "correct": false}
      ],
      "explanation": "The Sun is a G-type main-sequence star"
    }
  ]
}
```

### Example 3: Multiple Choice

```json
{
  "title": "Programming",
  "questions": [
    {
      "title": "Select all programming languages",
      "type": "multi_choice",
      "answers": [
        {"text": "Python", "correct": true},
        {"text": "HTML", "correct": false},
        {"text": "JavaScript", "correct": true}
      ]
    }
  ]
}
```

---

## 🎨 Screenshots

### Import Quizzes Tab
Modern interface with drag & drop upload

### Import Questions Tab
Select target quiz and import questions

### Progress Tracking
Real-time progress with animated bars

### Success Messages
Clear feedback with import statistics

---

## ⚙️ Settings

Configure default values for imported quizzes:
- Default quiz duration (minutes)
- Default passing grade (%)
- Default retake count
- Auto-publish option

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
- All modern browsers
- UTF-8 encoding

---

## 📈 Roadmap

- [x] Excel (XLSX) import - ✅ Completed
- [ ] Excel (XLS) legacy format support
- [ ] Export functionality
- [ ] Bulk edit questions
- [ ] Question bank management
- [ ] Import from other LMS
- [ ] Advanced question types
- [ ] Media file support
- [ ] Import scheduling

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## 📝 Changelog

### Version 1.0.0 - Initial Release
- Dual import modes (Quiz & Questions)
- Support for JSON, CSV, and Excel (XLSX)
- 4 question types with 20+ variations
- Modern UI with drag & drop
- In-app documentation viewer
- Comprehensive validation and error handling
- 12 documentation guides
- 6 sample files included

See [CHANGELOG.md](CHANGELOG.md) for complete release notes.

---

## 📞 Support

- **Documentation:** Check included MD files
- **Samples:** Use provided sample files
- **Website:** [https://mamflow.com](https://mamflow.com)
- **Issues:** Check DEBUG-GUIDE.md

---

## 📄 License

GPL v2 or later

---

## 👨‍💻 Author

**MamFlow**
- Website: [https://mamflow.com](https://mamflow.com)
- Plugin URI: [https://mamflow.com/plugins/quiz-importer-for-learnpress](https://mamflow.com/plugins/quiz-importer-for-learnpress)

---

## ⭐ Show Your Support

If you find this plugin helpful, please consider:
- Rating it on WordPress.org
- Sharing with others
- Contributing to development
- Reporting bugs and suggestions

---

**Made with ❤️ for the LearnPress community**
