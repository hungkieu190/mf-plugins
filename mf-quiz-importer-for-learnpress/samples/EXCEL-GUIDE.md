# Excel & CSV File Guide

## Working with Spreadsheets

**Good News:** You don't need to manually edit text files! 

Both CSV and Excel (XLSX) formats work with the plugin, and you can use familiar spreadsheet applications to create and edit them.

---

## Why Use Spreadsheets?

✅ **Easier to Edit** - Visual table format instead of text  
✅ **Familiar Interface** - Use Excel, Google Sheets, or LibreOffice  
✅ **Copy/Paste** - Import data from other sources  
✅ **No Syntax Errors** - Spreadsheet handles formatting  
✅ **Spell Check** - Built-in tools help avoid typos  
✅ **Both Formats Work** - Save as CSV or XLSX  

---

## Quick Start: Edit CSV in Excel

### Method 1: Microsoft Excel

1. **Download** sample CSV from plugin
2. **Open Excel** → File → Open
3. **Select** your CSV file
4. **Edit** the data in spreadsheet view
5. **Save** as:
   - CSV: File → Save As → "CSV (Comma delimited)"
   - XLSX: File → Save As → "Excel Workbook (.xlsx)"
6. **Upload** to plugin

### Method 2: Google Sheets

1. **Download** sample CSV from plugin
2. **Open** Google Sheets
3. **Import**: File → Import → Upload CSV
4. **Edit** the data in spreadsheet view
5. **Download** as:
   - CSV: File → Download → "Comma-separated values (.csv)"
   - XLSX: File → Download → "Microsoft Excel (.xlsx)"
6. **Upload** to plugin

### Method 3: LibreOffice Calc (Free)

1. **Download** sample CSV from plugin
2. **Open** LibreOffice Calc
3. **Open** your CSV file
4. **Edit** the data in spreadsheet view
5. **Save** as:
   - CSV: File → Save As → "Text CSV"
   - XLSX: File → Save As → "Excel 2007-365 (.xlsx)"
6. **Upload** to plugin

---

## Creating Excel Files for Import

You can also create files directly in Excel or Google Sheets:

---

## Recommended Workflow

### For Beginners

1. **Download** sample CSV from plugin
2. **Open** in Excel or Google Sheets
3. **Study** the format and examples
4. **Modify** with your own content
5. **Save** as CSV or XLSX
6. **Import** to plugin
7. **Check** results in LearnPress

### For Advanced Users

1. **Create** new spreadsheet in Excel/Google Sheets
2. **Add** headers in row 1 (see formats below)
3. **Fill** data in rows 2, 3, 4, etc.
4. **Save** as XLSX
5. **Import** to plugin

---

## File Formats

### CSV vs XLSX - Which to Use?

**CSV (Comma-Separated Values):**
- ✅ Smaller file size
- ✅ Universal compatibility
- ✅ Easy to edit in any spreadsheet app
- ✅ Good for simple data

**XLSX (Excel 2007+):**
- ✅ Better for complex data
- ✅ Preserves formatting during editing
- ✅ Native Excel format
- ✅ Handles special characters better

**Both work perfectly with the plugin!** Choose whichever you prefer.

---

## 1. Simple Quiz Excel (No Questions)

**File name:** `sample-quiz.xlsx`

**Columns:**
- title
- description
- duration
- passing_grade
- retake_count

**Example data:**

| title | description | duration | passing_grade | retake_count |
|-------|-------------|----------|---------------|--------------|
| Math Quiz | Basic mathematics | 60 | 70 | 0 |
| Science Quiz | General science | 45 | 75 | 1 |
| History Quiz | World history | 50 | 80 | 2 |

**Steps to create:**
1. Open Excel or Google Sheets
2. Create headers in row 1
3. Add data in rows 2, 3, 4, etc.
4. Save as `.xlsx` format
5. Upload to plugin

---

## 2. Quiz with Questions Excel

**File name:** `sample-quiz-with-questions.xlsx`

**Columns:**
- quiz_title
- quiz_description
- duration
- passing_grade
- retake_count
- question_title
- question_content
- question_type
- answer_1
- answer_2
- answer_3
- answer_4
- correct_answers
- explanation

**Example data:**

| quiz_title | quiz_description | duration | passing_grade | retake_count | question_title | question_content | question_type | answer_1 | answer_2 | answer_3 | answer_4 | correct_answers | explanation |
|------------|------------------|----------|---------------|--------------|----------------|------------------|---------------|----------|----------|----------|----------|-----------------|-------------|
| Math Quiz | Basic math | 30 | 70 | 0 | What is 2+2? | Addition | single_choice | 3 | 4 | 5 | 6 | 2 | 2+2=4 |
| Math Quiz | Basic math | 30 | 70 | 0 | Is 10>5? | Compare | true_or_false | True | False | | | 1 | 10 is greater |
| Math Quiz | Basic math | 30 | 70 | 0 | Even numbers | Select all | multi_choice | 2 | 3 | 4 | 5 | 1;3 | 2 and 4 are even |

**Steps to create:**
1. Open Excel or Google Sheets
2. Create headers in row 1
3. Add data - multiple rows with same quiz_title will be grouped
4. Use semicolon (;) for multiple correct answers
5. Save as `.xlsx` format
6. Upload to plugin

---

## 3. Questions Only Excel

**File name:** `sample-questions.xlsx`

**Columns:**
- title
- content
- type
- answer_1
- answer_2
- answer_3
- answer_4
- correct_answers
- explanation

**Example data:**

| title | content | type | answer_1 | answer_2 | answer_3 | answer_4 | correct_answers | explanation |
|-------|---------|------|----------|----------|----------|----------|-----------------|-------------|
| What is 2+2? | Simple math | single_choice | 3 | 4 | 5 | 6 | 2 | 2+2=4 |
| Is Earth round? | Geography | true_or_false | True | False | | | 1 | Earth is spherical |
| Select even | Choose all | multi_choice | 2 | 3 | 4 | 5 | 1;3 | 2 and 4 are even |

**Steps to create:**
1. Open Excel or Google Sheets
2. Create headers in row 1
3. Add questions data
4. Use semicolon (;) for multiple correct answers
5. Save as `.xlsx` format
6. Select target quiz in plugin
7. Upload file

---

## Important Notes

### File Format
- **XLSX (Excel 2007+):** ✅ Fully supported
- **XLS (Excel 97-2003):** ⚠️ Limited support - please convert to XLSX or CSV

### Requirements
- PHP ZipArchive extension (usually enabled by default)
- PHP SimpleXML extension (usually enabled by default)

### Tips
1. **Use XLSX or CSV** - both work perfectly
2. **Keep it simple** - avoid complex formatting
3. **No merged cells** - each cell should be independent
4. **Text only** - no formulas or special formatting
5. **UTF-8 encoding** - for special characters
6. **First row is header** - column names (don't change them!)
7. **Data starts from row 2**
8. **Edit in spreadsheet** - easier than text editor

### Spreadsheet Best Practices

**Do:**
- ✅ Use Excel, Google Sheets, or LibreOffice
- ✅ Copy/paste data from other sources
- ✅ Use spell check before importing
- ✅ Save frequently while editing
- ✅ Test with small files first
- ✅ Keep a backup of your original file

**Don't:**
- ❌ Merge cells
- ❌ Use formulas (they won't import)
- ❌ Add extra formatting (colors, borders)
- ❌ Change header names
- ❌ Leave empty rows between data
- ❌ Use special characters in file names

### Common Issues

**Issue:** "ZipArchive extension is not available"
**Solution:** Contact your hosting provider to enable ZipArchive

**Issue:** "Could not open XLSX file"
**Solution:** Make sure file is saved as .xlsx (not .xls)

**Issue:** "Empty or invalid file"
**Solution:** Check that file has headers and data

---

## Converting CSV to Excel

If you have CSV files, you can convert them to Excel:

### Using Excel
1. Open Excel
2. File → Open → Select CSV file
3. File → Save As → Choose "Excel Workbook (.xlsx)"

### Using Google Sheets
1. Open Google Sheets
2. File → Import → Upload CSV
3. File → Download → Microsoft Excel (.xlsx)

### Using LibreOffice Calc
1. Open LibreOffice Calc
2. File → Open → Select CSV file
3. File → Save As → Choose "Excel 2007-365 (.xlsx)"

---

## Example: Creating a Complete Quiz

### Step 1: Open Excel
Create a new workbook

### Step 2: Add Headers (Row 1)
```
quiz_title | quiz_description | duration | passing_grade | retake_count | question_title | question_content | question_type | answer_1 | answer_2 | answer_3 | answer_4 | correct_answers | explanation
```

### Step 3: Add Quiz Data (Row 2)
```
Programming 101 | Intro to coding | 45 | 75 | 1 | What is a variable? | Basic concept | single_choice | A storage location | A function | A loop | A class | 1 | Variables store data
```

### Step 4: Add More Questions (Rows 3, 4, etc.)
Keep same quiz_title to group questions together

### Step 5: Save
File → Save As → Choose `.xlsx` format

### Step 6: Import
Upload to Quiz Importer plugin

---

## Testing Your Excel File

Before importing large files:

1. **Create a test file** with 1-2 quizzes
2. **Import the test file** first
3. **Check the results** in LearnPress
4. **If successful**, import your full file
5. **If errors**, check format and try again

---

## Need Help?

- Check the Import Guide for more details
- Review Question Types reference
- Try CSV format if Excel has issues
- Contact support at mamflow.com

---

**Last Updated:** November 2024
**Plugin Version:** 1.0.3
