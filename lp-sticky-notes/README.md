# Sticky Notes Add-on for LearnPress

A comprehensive note-taking add-on for LearnPress that allows students to create sticky notes and highlight content in lessons.

## Features

### Note Types
- **Text Notes**: Simple text notes for any lesson
- **Highlight Notes**: Select text in lesson content and add notes to specific highlighted text

### Key Features
- **Per-lesson notes**: Notes are organized by lesson and course
- **User-specific**: Each user has their own private notes
- **Highlight functionality**: Click and drag to select text, then add notes to highlights
- **Profile integration**: View all notes in a dedicated "My Notes" tab in user profile
- **Course filtering**: Filter notes by specific courses in profile
- **Responsive design**: Works on all devices
- **AJAX-powered**: Smooth, fast interactions without page reloads

### Database Structure
The plugin creates a `learnpress_sticky_notes` table with the following fields:
- `id`: Primary key
- `user_id`: User who created the note
- `course_id`: Course the note belongs to
- `lesson_id`: Lesson the note belongs to
- `note_type`: 'text' or 'highlight'
- `highlight_text`: The highlighted text (for highlight notes)
- `position`: JSON data for highlight positioning
- `content`: The note content
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

## Installation

1. Download the plugin zip file
2. Go to WordPress Admin > Plugins > Add New
3. Upload the zip file and activate
4. The plugin will automatically create the necessary database tables

## Usage

### For Students

#### Adding Notes in Lessons
1. Navigate to any lesson in a course you're enrolled in
2. Scroll down to find the "Sticky Notes" section
3. Click "Add Note" to create a text note
4. Or select text in the lesson content and click the "Add Note" button that appears

#### Managing Notes
- Edit notes by clicking the "Edit" button
- Delete notes by clicking the "Delete" button
- All notes are automatically saved and synced

#### Viewing All Notes
1. Go to your Profile page
2. Click on the "My Notes" tab
3. Filter notes by course using the dropdown
4. Click "View Lesson" to go back to the lesson where the note was created

### For Administrators

#### Database Management
- The plugin automatically creates the database table on activation
- Notes are automatically cleaned up when lessons or courses are deleted
- No additional configuration required

## Requirements

- WordPress 6.0+
- LearnPress 4.2.0+
- PHP 7.4+

## Files Structure

```
lp-sticky-notes/
├── learnpress-sticky-notes.php          # Main plugin file
├── inc/
│   ├── class-lp-sticky-notes-database.php  # Database operations
│   ├── class-lp-sticky-notes-ajax.php      # AJAX handlers
│   ├── class-lp-sticky-notes-hooks.php     # Frontend hooks
│   └── class-lp-sticky-notes-profile.php   # Profile integration
├── templates/
│   ├── sticky-notes-section.php           # Lesson notes template
│   └── profile-notes-tab.php              # Profile notes template
├── assets/
│   ├── css/
│   │   ├── sticky-notes.css               # Frontend styles
│   │   └── profile-notes.css              # Profile styles
│   └── js/
│       ├── sticky-notes.js                # Frontend JavaScript
│       └── profile-notes.js               # Profile JavaScript
├── languages/
│   └── learnpress-sticky-notes.pot        # Translation template
└── README.md                              # This file
```

## Hooks and Filters

### Actions
- `learn-press/sticky-notes/note-added` - Fires when a note is added
- `learn-press/sticky-notes/note-updated` - Fires when a note is updated
- `learn-press/sticky-notes/note-deleted` - Fires when a note is deleted

### Filters
- `learn-press/sticky-notes/can-add-note` - Control if user can add notes
- `learn-press/sticky-notes/note-content` - Filter note content before saving
- `learn-press/sticky-notes/highlight-text` - Filter highlighted text

## Security

- All AJAX requests are nonce-protected
- User permission checks for all operations
- Input sanitization and validation
- XSS protection with wp_kses_post()

## Performance

- AJAX-powered for fast interactions
- Efficient database queries with proper indexing
- Minimal CSS/JS loading (only on relevant pages)
- Caching support for better performance

## Browser Support

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## Changelog

### 1.0.0
- Initial release
- Basic note-taking functionality
- Highlight feature
- Profile integration
- Responsive design

## Support

For support, please:
1. Check the LearnPress documentation
2. Visit the LearnPress support forum
3. Create an issue on GitHub

## License

This plugin is licensed under the GPLv3 License.

## Credits

Developed by ThimPress for LearnPress LMS.