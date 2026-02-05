=== LP Telegram Notifier ===
Contributors: mamflow
Tags: learnpress, telegram, notifications, enrollment
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Instant Telegram notifications for LearnPress instructors when students enroll in courses.

== Description ==

LP Telegram Notifier is an add-on for LearnPress that sends real-time notifications to Telegram when students enroll in courses.

= Features =

* Real-time Telegram notifications on course enrollment
* Easy configuration with Bot Token and Chat ID
* Test connection button to verify setup
* No data storage - notifications sent directly to Telegram
* Lightweight and fast - doesn't affect enrollment performance

= Requirements =

* LearnPress plugin must be installed and activated
* Telegram account
* Telegram Bot (create via @BotFather)

= How to Setup =

1. Create a Telegram Bot via @BotFather (https://t.me/BotFather)
2. Get your Chat ID from @userinfobot (https://t.me/userinfobot)
3. Go to LearnPress → Settings → Telegram
4. Enter your Bot Token and Chat ID
5. Click "Send Test Message" to verify connection
6. Enable notifications

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/lp-telegram-notifier/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Ensure LearnPress is installed and activated
4. Go to LearnPress → Settings → Telegram to configure

== Frequently Asked Questions ==

= Do I need a Telegram account? =

Yes, you need a Telegram account and a Telegram Bot to use this plugin.

= How do I create a Telegram Bot? =

Message @BotFather on Telegram and follow the instructions to create a new bot. You'll receive a Bot Token.

= How do I find my Chat ID? =

Message @userinfobot on Telegram, and it will reply with your Chat ID.

= Will this affect enrollment performance? =

No, the notification is sent asynchronously and won't block the enrollment process.

= What happens if Telegram is down? =

The enrollment will still complete successfully. The notification will fail silently and log an error.

== Changelog ==

= 1.0.0 =
* Initial release
* Real-time enrollment notifications
* Admin settings integration with LearnPress
* Test connection feature
