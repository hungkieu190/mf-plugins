=== LearnPress - Live Studio ===
Contributors: mamflow
Tags: learnpress, live, zoom, google meet, agora, livestream, video conferencing
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Add live streaming sessions to LearnPress courses with Zoom, Google Meet, and Agora integration.

== Description ==

LearnPress Live Studio transforms your LearnPress courses by adding powerful live streaming capabilities. Conduct interactive live sessions with your students using industry-leading platforms like Zoom, Google Meet, and Agora.

= Key Features =

* **Multiple Platform Support**: Choose between Zoom, Google Meet, or Agora for your live sessions
* **Seamless Integration**: Live sessions appear directly in your course curriculum
* **Automated Reminders**: Send email notifications 1 hour and 15 minutes before sessions
* **Attendance Tracking**: Automatically track which students attended your live sessions
* **Tutor Rating System**: Collect feedback from students after each session
* **Recording Management**: Automatically save and display session recordings
* **Countdown Timers**: Show students exactly when sessions start
* **Enrollment Protection**: Only enrolled students can join live sessions

= Platform Features =

**Zoom Integration**
* Create meetings or webinars automatically
* Server-to-Server OAuth support
* Attendance reports via webhooks
* Auto-recording support

**Google Meet Integration**
* OAuth 2.0 authentication
* Calendar integration
* Automatic meeting link generation
* Email invitations to enrolled students

**Agora Integration**
* Embedded video/audio directly in your site
* Low-latency streaming
* Interactive features (hand-raising, co-host)
* No redirects required

= Requirements =

* LearnPress 4.2.0 or higher
* WordPress 6.0 or higher
* PHP 8.0 or higher
* API credentials from your chosen platform(s)

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/lp-live-studio/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to LearnPress → Live Studio to configure your platform credentials
4. Create a new lesson and enable "Live Session" option
5. Configure your live session settings and publish

== Frequently Asked Questions ==

= Do I need accounts with Zoom/Google/Agora? =

Yes, you need to create a developer account with at least one platform and obtain API credentials.

= Can I use multiple platforms? =

Yes! You can configure all three platforms and choose which one to use for each individual session.

= Are recordings automatically saved? =

Yes, if your platform supports it (Zoom), recordings are automatically saved and linked to the lesson.

= Can students rate the tutor? =

Yes, students who attended the session can rate both the tutor and content quality within 7 days after the session.

= Is there a limit on participants? =

Participant limits depend on your platform subscription. You can set a limit in the lesson settings.

== Screenshots ==

1. Admin settings page with platform configuration
2. Live lesson settings in curriculum builder
3. Student view of upcoming live session
4. Live room with countdown timer
5. Rating form after session completion
6. Attendance report dashboard

== Changelog ==

= 1.0.0 - 2026-02-12 =
* Initial release
* Zoom integration
* Google Meet integration
* Agora integration
* Attendance tracking
* Tutor rating system
* Email reminders
* Recording management

== Upgrade Notice ==

= 1.0.0 =
Initial release of LearnPress Live Studio.

== Support ==

For support, please visit https://mamflow.com/support/
