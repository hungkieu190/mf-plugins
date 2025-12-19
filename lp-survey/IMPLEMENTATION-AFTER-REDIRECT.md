# Implementation: Survey Popup After Page Redirect

## Problem
LearnPress redirects immediately after lesson/course completion, so we can't show popup before redirect.

## Solution
Show popup on the NEXT page after redirect, using completion status check.

## Implementation Steps

### 1. Remove Complex Hooks
- Remove all completion hooks (they're unreliable)
- Remove localStorage setting in PHP

### 2. Simple JavaScript Check
On every lesson page load:
- Check if previous lesson was just completed
- Use LearnPress's course progress data
- Show popup if needed

### 3. Flow
```
User on Lesson 1
↓
Click "Complete Lesson"
↓
LearnPress AJAX → Mark completed
↓
Redirect to Lesson 2 (or next page)
↓
Page loads → JS checks: "Was previous lesson just completed?"
↓
If YES → Show survey popup for Lesson 1
↓
User submits survey
↓
Continue with Lesson 2
```

### 4. Implementation
```javascript
// On page load, check URL params or session
if (lpSurvey.justCompleted) {
    // Show survey for the lesson that was just completed
    showSurveyPopup(lpSurvey.completedLessonId);
}
```

### 5. PHP Side
```php
// In frontend enqueue
if (is_singular('lp_lesson')) {
    // Check if coming from completion
    $just_completed = get_transient('lp_survey_show_for_user_' . $user_id);
    if ($just_completed) {
        wp_localize_script('lp-survey-frontend', 'lpSurvey', [
            'justCompleted' => true,
            'completedLessonId' => $just_completed['lesson_id'],
            'surveyId' => $just_completed['survey_id']
        ]);
        delete_transient('lp_survey_show_for_user_' . $user_id);
    }
}
```

## Benefits
- Simple and reliable
- No complex AJAX interception
- Works with LearnPress's natural flow
- Easy to debug
