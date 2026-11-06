=== QOT Editor Backup ===
Contributors: pchere
Tags: backup, theme editor, plugin editor, download, copy
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Copy and Download buttons above the Theme and Plugin File Editors, so you can keep a backup of any file before you change it.

== Description ==

Editing a theme or plugin file in WordPress is quick, and one wrong line can break your site. QOT Editor Backup puts two buttons above the file editor: **Copy** puts the whole file on your clipboard, and **Download** saves it to your computer. Take a backup in one click before you change anything.

**What you get**

* **Copy and Download buttons** above Appearance > Theme File Editor and Tools > Plugin File Editor, on every file.
* **Date and time in the file name,** for example `my-theme-style.2026-11-06-1432.css`, so each backup gets its own name and a new one never replaces an older one. Or just the date, or nothing.
* **Theme or plugin name first** (optional), so files from different themes are easy to tell apart.
* **Copies what you see,** including changes you haven't saved yet.
* **Your choice of buttons:** both, Download only or Copy only; icons and text, text only or icons only; your own words; WordPress-style buttons, empty boxes or your own colours.
* **A live preview** on the settings page shows the buttons and the name today's download would get.

**Light by design**

* A small inline script on the two editor screens only. Nothing on your site's front end.
* Files are made in your browser; nothing is stored on your server.

Made by Quick Online Tips, tech help that works since 2004. See all our [free WordPress plugins](https://www.quickonlinetips.com/archives/wordpress-plugins/).

== Installation ==

1. In your WordPress admin, go to Plugins > Add New Plugin and search for "QOT Editor Backup".
2. Click Install Now, then Activate.
3. Open Appearance > Theme File Editor: the buttons are above the code. Change them in Settings > QOT Editor Backup.

== Frequently Asked Questions ==

= I can't see the file editors at all. =

Your site has file editing switched off (the `DISALLOW_FILE_EDIT` setting in wp-config.php), which many hosts do for safety. Then there is nothing to back up from the editor, and the settings page tells you so.

= Is this a full site backup? =

No. It saves the one file you are editing. Use a backup plugin or your host's backups for the whole site.

= How do I restore a file? =

Open the downloaded file, copy everything, paste it into the editor and click Update File.

== Screenshots ==

1. The settings page with its live preview.
2. The Copy and Download buttons above the Theme File Editor.

== Changelog ==

= 1.0 =
* First release.
