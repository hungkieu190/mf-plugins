# LearnPress Question Types Reference

## Supported Question Types

This plugin supports all standard LearnPress question types with multiple naming variations for flexibility.

---

## 1. True or False

**Primary Type:** `true_or_false`

**Accepted Variations:**
- `true_or_false`
- `true_false`
- `truefalse`
- `boolean`
- `bool`

**Description:** Questions with only two options: True or False

**Example JSON:**
```json
{
  "title": "Is the Earth round?",
  "content": "Geography question",
  "type": "true_or_false",
  "answers": [
    {"text": "True", "correct": true},
    {"text": "False", "correct": false}
  ],
  "explanation": "The Earth is approximately spherical"
}
```

**Example CSV:**
```csv
title,content,type,answer_1,answer_2,correct_answers,explanation
"Is Earth round?","Geography",true_or_false,True,False,1,"Earth is spherical"
```

---

## 2. Single Choice

**Primary Type:** `single_choice`

**Accepted Variations:**
- `single_choice`
- `single`
- `singlechoice`
- `radio`
- `one_choice`

**Description:** Questions with multiple options but only ONE correct answer

**Example JSON:**
```json
{
  "title": "What is 2+2?",
  "content": "Simple math",
  "type": "single_choice",
  "answers": [
    {"text": "3", "correct": false},
    {"text": "4", "correct": true},
    {"text": "5", "correct": false},
    {"text": "6", "correct": false}
  ],
  "explanation": "2+2 equals 4"
}
```

**Example CSV:**
```csv
title,content,type,answer_1,answer_2,answer_3,answer_4,correct_answers,explanation
"What is 2+2?","Math",single_choice,3,4,5,6,2,"2+2=4"
```

---

## 3. Multiple Choice

**Primary Type:** `multi_choice`

**Accepted Variations:**
- `multi_choice`
- `multiple_choice`
- `multiplechoice`
- `multichoice`
- `multiple`
- `checkbox`
- `many_choice`

**Description:** Questions with multiple options and MULTIPLE correct answers

**Example JSON:**
```json
{
  "title": "Select all even numbers",
  "content": "Choose all that apply",
  "type": "multi_choice",
  "answers": [
    {"text": "2", "correct": true},
    {"text": "3", "correct": false},
    {"text": "4", "correct": true},
    {"text": "5", "correct": false},
    {"text": "6", "correct": true}
  ],
  "explanation": "Even numbers: 2, 4, 6"
}
```

**Example CSV:**
```csv
title,content,type,answer_1,answer_2,answer_3,answer_4,answer_5,correct_answers,explanation
"Select even","Math",multi_choice,2,3,4,5,6,1;3;5,"2,4,6 are even"
```

**Note:** In CSV, use semicolon (;) to separate multiple correct answer numbers

---

## 4. Fill in the Blanks

**Primary Type:** `fill_in_blanks`

**Accepted Variations:**
- `fill_in_blanks`
- `fill_in_blank`
- `fillinblanks`
- `fill_blanks`
- `blanks`

**Description:** Questions where students fill in missing words or phrases

**Example JSON:**
```json
{
  "title": "Complete the sentence",
  "content": "The capital of France is {blank}",
  "type": "fill_in_blanks",
  "answers": [
    {"text": "Paris", "correct": true},
    {"text": "paris", "correct": true}
  ],
  "explanation": "Paris is the capital of France"
}
```

**Example CSV:**
```csv
title,content,type,answer_1,answer_2,correct_answers,explanation
"Capital of France","Fill: Capital is {blank}",fill_in_blanks,Paris,paris,1;2,"Paris is correct"
```

---

## CSV Format Rules

### For Questions Import

**Column Names:**
- `title` - Question title (required)
- `content` - Question content/description
- `type` - Question type (use any variation above)
- `answer_1`, `answer_2`, `answer_3`, etc. - Answer options
- `correct_answers` - Correct answer numbers (1-based), separated by semicolon for multiple
- `explanation` - Answer explanation (optional)

**Example:**
```csv
title,content,type,answer_1,answer_2,answer_3,answer_4,correct_answers,explanation
"Question 1","Content",single_choice,A,B,C,D,2,"B is correct"
"Question 2","Content",multi_choice,A,B,C,D,1;3,"A and C are correct"
"Question 3","Content",true_or_false,True,False,,,1,"True is correct"
```

### For Quiz with Questions Import

**Column Names:**
- `quiz_title` - Quiz title (required)
- `quiz_description` - Quiz description
- `duration` - Quiz duration in minutes
- `passing_grade` - Passing grade percentage
- `retake_count` - Number of retakes allowed
- `question_title` - Question title
- `question_content` - Question content
- `question_type` - Question type
- `answer_1`, `answer_2`, etc. - Answer options
- `correct_answers` - Correct answer numbers
- `explanation` - Answer explanation

**Example:**
```csv
quiz_title,quiz_description,duration,passing_grade,retake_count,question_title,question_content,question_type,answer_1,answer_2,answer_3,correct_answers,explanation
"Math Quiz","Basic math",30,70,0,"What is 2+2?","Addition",single_choice,3,4,5,2,"2+2=4"
"Math Quiz","Basic math",30,70,0,"Is 10>5?","Compare",true_or_false,True,False,,1,"10 is greater"
```

**Note:** Multiple rows with the same `quiz_title` will be grouped into one quiz with multiple questions.

---

## JSON Format Rules

### For Questions Import

**Structure:**
```json
[
  {
    "title": "Question title",
    "content": "Question content",
    "type": "question_type",
    "answers": [
      {"text": "Answer 1", "correct": true},
      {"text": "Answer 2", "correct": false}
    ],
    "explanation": "Optional explanation"
  }
]
```

### For Quiz Import

**Structure:**
```json
[
  {
    "title": "Quiz title",
    "description": "Quiz description",
    "duration": 60,
    "passing_grade": 70,
    "retake_count": 0,
    "questions": [
      {
        "title": "Question title",
        "content": "Question content",
        "type": "question_type",
        "answers": [
          {"text": "Answer", "correct": true}
        ],
        "explanation": "Explanation"
      }
    ]
  }
]
```

---

## Best Practices

1. **Use Standard Types:** While variations are supported, use standard types (`true_or_false`, `single_choice`, `multi_choice`, `fill_in_blanks`) for consistency

2. **Always Include Explanations:** Help students learn by providing explanations for correct answers

3. **Test Small Batches First:** Import a few questions first to verify format before importing large files

4. **Validate JSON:** Use a JSON validator before importing to catch syntax errors

5. **CSV Encoding:** Always use UTF-8 encoding for CSV files to support special characters

6. **Answer Order:** For CSV, correct_answers uses 1-based indexing (1 = first answer, 2 = second answer, etc.)

7. **Multiple Correct Answers:** Use semicolon (;) without spaces to separate multiple correct answer numbers in CSV

---

## Common Mistakes to Avoid

❌ **Wrong:** `"type": "multiple_choice"` in JSON (should be `multi_choice`)
✅ **Correct:** `"type": "multi_choice"`

❌ **Wrong:** `correct_answers: "1, 3"` (comma with space)
✅ **Correct:** `correct_answers: "1;3"` (semicolon, no space)

❌ **Wrong:** 0-based indexing in CSV (0,1,2...)
✅ **Correct:** 1-based indexing in CSV (1,2,3...)

❌ **Wrong:** Missing required fields (title, type)
✅ **Correct:** Always include title and type

---

## Support

For more information:
- Check sample files in `samples/` directory
- Read `IMPORT-GUIDE.md` for detailed instructions
- Visit https://mamflow.com for support
