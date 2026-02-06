# LP Telegram Notifier - Installation & Usage Guide

This plugin sends instant Telegram notifications to instructors whenever a student enrolls in a LearnPress course.

## 1. Installation & Activation

1. Install and activate the plugin.
2. Go to **LearnPress > Settings > License**.
3. Enter your License Key and activate it to unlock features.

## 2. Create a Telegram Bot

To receive notifications, you need to create a Telegram Bot:

1. Open Telegram and search for **@BotFather**.
2. Chat `/newbot`.
3. Enter a display name for your Bot (e.g., `My Lms Bot`).
4. Enter a username for your Bot (must end in `bot`, e.g., `my_lms_notification_bot`).
5. @BotFather will send you an **HTTP API Token** (e.g., `123456789:ABC...`).
   👉 **Copy this Token** for step 4.

## 3. Get Chat ID & Start the Bot (IMPORTANT)

For the Bot to send messages to you, you must get your Chat ID and initialize the conversation:

1. **Find & Start your Bot**:
   - Search for the bot username you created in step 2 on Telegram.
   - Click the **Start** button or type `/start` to the bot.
   - ⚠️ **Note**: If you skip this step, the Bot will not have permission to send you messages.

2. **Get your Chat ID**:
   - Search for **@userinfobot** on Telegram.
   - Click **Start** or type anything.
   - The bot will reply with your info. Look for the `Id:` line.
   - 👉 **Copy this ID number** (e.g., `123456789`).

## 4. Plugin Configuration

1. Go to **LearnPress > Settings > Telegram**.
2. **Enable Notifications**: Select `Yes`.
3. **Bot Token**: Paste the API Token copied in Step 2.
4. **Chat ID**: Paste the ID copied in Step 3.
5. Click **Save Settings**.

## 5. Verify Connection

- After saving, click the **Test Connection** button (appears next to the Chat ID field).
- If you receive a test message on Telegram -> Setup successful! ✅
