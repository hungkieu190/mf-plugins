# Quiz & Question Import Guide

## Overview
This plugin provides two powerful import methods:
1. **Import Complete Quizzes** - Import quizzes with all questions included
2. **Import Questions Only** - Add questions to existing quizzes

---

## Excel & Spreadsheet Format

### Working with CSV and Excel

**Good News:** CSV and Excel (XLSX) use the same format! You can:
- ✅ Open CSV files in Excel or Google Sheets
- ✅ Edit them like a spreadsheet
- ✅ Save as CSV or XLSX (both work!)
- ✅ No need to manually edit text files

### How to Edit CSV Files

**Using Microsoft Excel:**
1. Open Excel
2. File → Open → Select your CSV file
3. Edit the data in spreadsheet view
4. File → Save As → Choose "CSV (Comma delimited)" or "Excel Workbook (.xlsx)"
5. Upload to plugin

**Using Google Sheets:**
1. Open Google Sheets
2. File → Import → Upload your CSV file
3. Edit the data in spreadsheet view
4. File → Download → Choose "Comma-separated values (.csv)" or "Microsoft Excel (.xlsx)"
5. Upload to plugin

**Using LibreOffice Calc:**
1. Open LibreOffice Calc
2. File → Open → Select your CSV file
3. Edit the data in spreadsheet view
4. File → Save As → Choose "Text CSV" or "Excel 2007-365 (.xlsx)"
5. Upload to plugin

### Creating New Files

**Option 1: Start with CSV, Edit in Excel**
1. Download sample CSV from plugin
2. Open in Excel/Google Sheets
3. Modify the data
4. Save as CSV or XLSX

**Option 2: Create Directly in Excel**
1. Open Excel/Google Sheets
2. Create headers in row 1
3. Add data in rows 2, 3, 4, etc.
4. Save as XLSX
5. Upload to plugin

### XLSX (Excel 2007+)
Excel files work exactly like CSV files - same columns, same format!

**Simple Quiz Excel:**
- Same columns as CSV: title, description, duration, passing_grade, retake_count
- Create in Excel or Google Sheets
- Save as .xlsx format

**Quiz with Questions Excel:**
- Same columns as CSV with questions
- Use semicolon (;) for multiple correct answers
- Save as .xlsx format

**Requirements:**
- PHP ZipArchive extension (usually enabled)
- PHP SimpleXML extension (usually enabled)

**Note:** XLS (Excel 97-2003) format has limited support. Please use XLSX or CSV instead.

See `samples/EXCEL-GUIDE.md` for detailed instructions on creating Excel files.

---

## 1. Import Complete Quizzes

### Supported Formats
- **JSON** (Recommended)
- **CSV**
- **Excel (XLSX)** - Excel 2007 and newer

### JSON Format Example
```json
[
  {
    "title": "Math Quiz",
    "description": "Basic mathematics questions",
    "duration": 60,
    "passing_grade": 70,
    "retake_count": 0,
    "questions": [
      {
        "title": "What is 2+2?",
        "content": "Simple arithmetic",
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

### CSV Format Examples

**Simple Quiz (No Questions):**
```csv
title,description,duration,passing_grade,retake_count
"Math Quiz","Basic mathematics",60,70,0
"Science Quiz","General science",45,75,1
```

**Quiz with Questions:**
```csv
quiz_title,quiz_description,duration,passing_grade,retake_count,question_title,question_content,question_type,answer_1,answer_2,answer_3,answer_4,correct_answers,explanation
"Math Quiz","Basic math",30,70,0,"What is 2+2?","Addition",single_choice,3,4,5,6,2,"2+2=4"
"Math Quiz","Basic math",30,70,0,"Is 10>5?","Compare",true_or_false,True,False,,,1,"10 is greater"
"Math Quiz","Basic math",30,70,0,"Even numbers","Select all",multi_choice,2,3,4,5,1;3,"2 and 4 are even"
```

**Note:** For quiz with questions CSV:
- Use `quiz_title` instead of `title`
- Use `question_title`, `question_content`, `question_type` for questions
- Multiple rows with same `quiz_title` will be grouped into one quiz
- Use semicolon (;) to separate multiple correct answers

### Question Types (LearnPress Compatible)
- `true_or_false` - True/False questions (also accepts: `true_false`, `boolean`, `bool`)
- `single_choice` - Single correct answer (also accepts: `single`, `radio`, `one_choice`)
- `multi_choice` - Multiple correct answers (also accepts: `multiple_choice`, `checkbox`, `multiple`)
- `fill_in_blanks` - Fill in the blanks (also accepts: `fill_in_blank`, `blanks`)

---

## 2. Import Questions Only

Perfect for adding questions to existing quizzes!

### JSON Format Example
```json
[
  {
    "title": "What is 2+2?",
    "content": "Simple arithmetic question",
    "type": "single_choice",
    "answers": [
      {"text": "3", "correct": false},
      {"text": "4", "correct": true},
      {"text": "5", "correct": false}
    ],
    "explanation": "2+2 equals 4"
  },
  {
    "title": "Is Earth round?",
    "content": "Geography question",
    "type": "true_or_false",
    "answers": [
      {"text": "True", "correct": true},
      {"text": "False", "correct": false}
    ]
  }
]
```

### CSV Format Example
```csv
title,content,type,answer_1,answer_2,answer_3,answer_4,correct_answers,explanation
"What is 2+2?","Simple math",single_choice,3,4,5,6,2,"2+2 equals 4"
"Is Earth round?","Geography",true_or_false,True,False,,,1,"Earth is spherical"
"Select primes","Math",multiple_choice,2,3,4,5,1;2;4,"2,3,5 are prime"
```

**CSV Notes:**
- `correct_answers` - Use semicolon (;) to separate multiple correct answer numbers
- Answer numbers start from 1 (not 0)
- For true/false, use answer_1 and answer_2 only

---

## How to Use

### Import Quizzes
1. Go to **LearnPress → Quiz Importer**
2. Click **Import Quizzes** tab
3. Upload your file (CSV, JSON, or Excel)
4. Click **Import Quizzes**
5. Wait for completion

### Import Questions
1. Go to **LearnPress → Quiz Importer**
2. Click **Import Questions** tab
3. Select target quiz from dropdown
4. Upload your questions file
5. Click **Import Questions**
6. Questions will be added to the selected quiz

---

## Sample Files

Download sample files from the plugin:

**Quiz Import:**
- `samples/sample-quiz-complete.json` - Complete quizzes with questions (JSON)
- `samples/sample-quiz.json` - Quiz with questions (original)
- `samples/sample-quiz-with-questions.csv` - Quiz with questions (CSV)
- `samples/sample-quiz.csv` - Quiz metadata only (CSV)

**Question Import:**
- `samples/sample-questions.json` - Questions only (JSON)
- `samples/sample-questions.csv` - Questions only (CSV)

---

## Tips & Best Practices

### For Quiz Import
- Always include required fields: `title`
- Use default settings for optional fields
- Test with small files first
- Check quiz after import

### For Question Import
- Ensure target quiz exists
- Verify question types are correct
- Use explanations for better learning
- Test answers before importing large batches

### File Preparation
- Use UTF-8 encoding for CSV files
- Validate JSON before uploading
- Keep file sizes reasonable (< 10MB)
- Use descriptive question titles

### Working with Spreadsheets

**Why Use Excel/Google Sheets?**
- ✅ Easier to edit than text files
- ✅ Visual table format
- ✅ Copy/paste from other sources
- ✅ Formula support for data generation
- ✅ Spell check and formatting
- ✅ Familiar interface

**Best Workflow:**
1. Download sample CSV
2. Open in Excel/Google Sheets
3. Edit and add your content
4. Save as CSV or XLSX
5. Import to plugin

**Tips for Spreadsheet Editing:**
- Use first row for headers (don't change them!)
- Each row = one quiz or question
- Use semicolon (;) for multiple correct answers
- Don't use merged cells
- Keep formatting simple (text only)
- Save with UTF-8 encoding

---

## Troubleshooting

### Import Failed
- Check file format is correct
- Verify all required fields are present
- Ensure file is not corrupted
- Check WordPress error logs

### Questions Not Showing
- Verify quiz ID is correct
- Check question type is supported
- Ensure answers are properly formatted
- Refresh quiz page

### CSV Issues
- Use proper CSV encoding (UTF-8)
- Escape commas in text with quotes
- Check column headers match exactly
- Use semicolon for multiple correct answers

---

## Support

For issues or questions:
1. Check sample files for reference
2. Review this guide
3. Check WordPress debug logs
4. Contact support at https://mamflow.com
