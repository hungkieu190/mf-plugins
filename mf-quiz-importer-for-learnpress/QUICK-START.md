# Quick Start Guide - Quiz Importer for LearnPress

## 🚀 Get Started in 3 Steps

### Step 1: Choose Import Type
- **Import Quizzes** - Import complete quizzes with questions
- **Import Questions** - Add questions to existing quiz

### Step 2: Prepare Your File
- Download a sample file
- Modify it with your content
- Save as JSON, CSV, or Excel (XLSX)

**💡 Pro Tip:** You can open CSV files in Excel or Google Sheets for easier editing!

### Step 3: Import
- Upload your file
- Click Import button
- Done! ✅

---

## 📋 Quick Examples

### Example 1: Import a Complete Quiz (JSON)

**File: my-quiz.json**
```json
[
  {
    "title": "My First Quiz",
    "description": "A simple quiz",
    "duration": 30,
    "passing_grade": 70,
    "retake_count": 0,
    "questions": [
      {
        "title": "What is 2+2?",
        "content": "Simple math",
        "type": "single_choice",
        "answers": [
          {"text": "3", "correct": false},
          {"text": "4", "correct": true},
          {"text": "5", "correct": false}
        ],
        "explanation": "2+2 equals 4"
      }
    ]
  }
]
```

**Steps:**
1. Go to **LearnPress → Quiz Importer**
2. Click **Import Quizzes** tab
3. Upload `my-quiz.json`
4. Click **Import Quizzes**

---

### Example 2: Import Questions Only (CSV)

**File: my-questions.csv**
```csv
title,content,type,answer_1,answer_2,answer_3,correct_answers,explanation
"Question 1","Content",single_choice,A,B,C,2,"B is correct"
"Question 2","Content",true_or_false,True,False,,1,"True is correct"
```

**Steps:**
1. Go to **LearnPress → Quiz Importer**
2. Click **Import Questions** tab
3. Select target quiz from dropdown
4. Upload `my-questions.csv`
5. Click **Import Questions**

---

### Example 3: Import Quiz with Questions (CSV)

**File: quiz-with-questions.csv**
```csv
quiz_title,quiz_description,duration,passing_grade,retake_count,question_title,question_content,question_type,answer_1,answer_2,answer_3,correct_answers,explanation
"My Quiz","Description",30,70,0,"Q1","Content",single_choice,A,B,C,2,"B is correct"
"My Quiz","Description",30,70,0,"Q2","Content",true_or_false,True,False,,1,"True is correct"
```

**Steps:**
1. Go to **LearnPress → Quiz Importer**
2. Click **Import Quizzes** tab
3. Upload `quiz-with-questions.csv`
4. Click **Import Quizzes**

---

## 🎯 Question Types

Use any of these in your files:

| Type | Use For | Example |
|------|---------|---------|
| `true_or_false` | Yes/No questions | Is Earth round? |
| `single_choice` | One correct answer | What is 2+2? |
| `multi_choice` | Multiple correct | Select all even numbers |
| `fill_in_blanks` | Fill in text | Capital of France is {blank} |

**Tip:** You can also use `boolean`, `multiple_choice`, `checkbox`, etc. - they'll be auto-converted!

---

## 📥 Sample Files

Download these from the plugin:

**For Quiz Import:**
- `sample-quiz-complete.json` - Complete example
- `sample-quiz-with-questions.csv` - CSV with questions
- `sample-quiz.csv` - Simple quiz only

**For Question Import:**
- `sample-questions.json` - JSON format
- `sample-questions.csv` - CSV format

---

## ⚡ Pro Tips

### 1. Start Small
Import 1-2 quizzes first to test the format

### 2. Use Templates
Download sample files and modify them

### 3. CSV & Excel Tips
- Use semicolon (;) for multiple correct answers: `1;3;5`
- Answer numbers start from 1, not 0
- Use UTF-8 encoding
- Wrap text with commas in quotes

**💡 Editing CSV Files:**
- **Excel:** File → Open → Select CSV → Edit → Save as CSV or XLSX
- **Google Sheets:** File → Import → Upload CSV → Edit → Download as CSV or XLSX
- **LibreOffice:** File → Open → Select CSV → Edit → Save as CSV or XLSX
- Both CSV and XLSX formats work perfectly!

### 4. JSON Tips
- Validate JSON before importing (use jsonlint.com)
- Use `multi_choice` not `multiple_choice`
- Always include explanations

### 5. Common Mistakes
❌ `"type": "multiple_choice"` → ✅ `"type": "multi_choice"`
❌ `correct_answers: "1, 3"` → ✅ `correct_answers: "1;3"`
❌ Answer index 0,1,2 → ✅ Answer index 1,2,3

---

## 🔧 Troubleshooting

### Import Failed?
- Check file format (JSON/CSV)
- Verify required fields are present
- Check for syntax errors
- Try with sample file first

### Questions Not Showing?
- Verify quiz ID is correct
- Check question type is valid
- Ensure answers are formatted correctly
- Refresh quiz page

### CSV Issues?
- Use UTF-8 encoding
- Escape commas with quotes
- Check column headers match exactly
- Use semicolon for multiple answers

---

## 📚 Need More Help?

- **Full Guide:** Read `IMPORT-GUIDE.md`
- **Question Types:** Check `QUESTION-TYPES.md`
- **Features:** See `FEATURES.md`
- **Support:** Visit https://mamflow.com

---

## 🎓 Common Workflows

### Workflow 1: Create New Course
1. Prepare quiz content in spreadsheet
2. Export as CSV with questions
3. Import all quizzes at once
4. Review and publish

### Workflow 2: Add to Existing Quiz
1. Create questions in JSON/CSV
2. Go to Import Questions tab
3. Select target quiz
4. Import questions
5. Verify in quiz editor

### Workflow 3: Migrate from Another LMS
1. Export quizzes from old system
2. Convert to JSON format
3. Import to LearnPress
4. Review and adjust
5. Test with students

---

## ✅ Checklist Before Import

- [ ] File format is correct (JSON or CSV)
- [ ] Required fields are present
- [ ] Question types are valid
- [ ] Answers are properly formatted
- [ ] Correct answers are marked
- [ ] Explanations are included
- [ ] File encoding is UTF-8
- [ ] Tested with sample file first
- [ ] Backup created (if updating)

---

## 🎉 You're Ready!

Now you know how to:
- ✅ Import complete quizzes
- ✅ Add questions to existing quizzes
- ✅ Use JSON and CSV formats
- ✅ Handle different question types
- ✅ Troubleshoot common issues

**Happy importing!** 🚀

---

**Version:** 1.0.0
**Plugin:** Quiz Importer for LearnPress
**Author:** MamFlow
