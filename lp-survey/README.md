# LearnPress – Course & Lesson Survey

**Version:** 1.0.0  
**Requires:** WordPress 5.0+, LearnPress 4.2+  
**License:** GPL v2 or later  
**Product ID:** 47233

## 📋 Description

LP Survey is a premium LearnPress add-on that collects student feedback immediately after completing lessons or courses. This helps instructors gather timely, accurate feedback to improve course content.

> **⚠️ License Required**: This plugin requires a valid license key from [mamflow.com](https://mamflow.com) to unlock all features.

## ✨ Features

### MVP (Phase 1)
- ✅ Auto-trigger surveys after lesson/course completion
- ✅ Two question types: Rating (1-5 stars) & Text
- ✅ Beautiful popup with animations
- ✅ Mobile-responsive design
- ✅ Admin dashboard with statistics
- ✅ CSV export for responses
- ✅ Per-course survey settings
- ✅ Permission-based access (Student/Instructor/Admin)

### Default Survey Questions

**Lesson Survey:**
- How easy was this lesson to understand? (rating)
- Was the lesson duration appropriate? (rating)
- What part did you find difficult or unclear? (text)

**Course Survey:**
- Overall course rating (rating)
- Did the course meet your expectations? (rating)
- Would you recommend this course to others? (rating)
- Additional feedback for the instructor (text)

## 📦 Installation

1. Upload the `lp-survey` folder to `/wp-content/plugins/`
2. Activate the plugin through WordPress admin
3. LearnPress must be installed and activated
4. Database tables will be created automatically
5. **Activate your license** to unlock all features

## 🔑 License Activation

### Step 1: Get Your License Key
- Purchase the plugin from [mamflow.com](https://mamflow.com)
- Check your order confirmation email for the license key
- Or login to your account at [mamflow.com/my-account](https://mamflow.com/my-account)

### Step 2: Activate License
1. Go to **LearnPress > Mamflow License**
2. Click on the **Survey** tab
3. Enter your license key
4. Click **Activate License**

### Step 3: Verify Activation
- Green "License Active" message should appear
- Check license expiration date
- All premium features are now unlocked

## ⚙️ Configuration

### Global Settings
Go to **LearnPress > Survey Settings**

- Enable/disable lesson surveys
- Enable/disable course surveys
- Display type (Popup/Inline)
- Allow students to skip surveys
- Maximum questions per survey

### Per-Course Settings
Edit any course and find the **Survey Settings** metabox to override global settings.

## 📊 Dashboard

Access **LearnPress > Survey** to view:
- Total surveys and responses
- Average rating across all surveys
- Recent responses (last 30 days)
- Response details with filters
- Export to CSV

## 🔐 Permissions

| Role | Permissions |
|------|------------|
| **Student** | Answer surveys only |
| **Instructor** | View surveys for their own courses |
| **Admin** | View all surveys, manage settings |

## 🗄️ Database Structure

**Tables created:**
- `wp_lp_surveys` - Survey configurations
- `wp_lp_survey_questions` - Survey questions
- `wp_lp_survey_answers` - Student responses

## 🎯 How It Works

1. Student completes a lesson/course
2. Survey popup appears automatically
3. Student rates and provides feedback
4. Responses saved to database
5. Instructor/Admin views results in dashboard

## 🚀 Future Enhancements

- Custom question builder
- Multiple survey templates
- Advanced analytics
- Email notifications
- AI-powered insights

## 🐛 Support

For issues or questions, contact: [support@mamflow.com](mailto:support@mamflow.com)

## 📄 License

GPL v2 or later - [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

## 👨‍💻 Author

**MamFlow** - [https://mamflow.com](https://mamflow.com)
