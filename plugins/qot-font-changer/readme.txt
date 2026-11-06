=== QOT Font Changer ===
Contributors: pchere
Tags: fonts, typography, web safe fonts, font family, speed
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

See which fonts your theme uses and where, then swap any of them for a fast, web-safe font. Or set the font and weight for each part of the page.

== Description ==

Changing a font in WordPress usually means digging through your theme's CSS. QOT Font Changer does the digging for you. It reads your theme's stylesheet (and the font settings of block themes), lists every font it uses and where, and lets you swap any of them with a dropdown.

It changes fonts only. Your theme files are never touched: untick one box and your theme's own fonts are back.

**What you get**

* **Your theme's fonts, listed:** each font with the places that use it.
* **One-dropdown swaps** to web-safe fonts: the system font, Arial, Arial Narrow, Helvetica, Verdana, Tahoma, Trebuchet MS, Lucida Sans, Georgia, Times New Roman, Palatino, Garamond, Courier New and a monospace font for code.
* **Fonts by part of the page:** body text, headings, site title, menus, buttons and forms, and code, each with its own font and weight (light, normal, medium, semi-bold, bold, black).
* **Works with block themes too:** changing a theme font setting changes it everywhere the theme uses it.
* **Same fonts in the editor** while you write posts (optional).
* **A live preview** of the site title, menu, heading, text, button and code.

**Why web-safe fonts?** They are already on your readers' phones and computers, so nothing extra downloads and your pages open faster. Each choice comes with fallbacks for devices that don't have it.

Google Fonts and uploaded fonts are planned for a later version.

Find it under Appearance > Font Changer.

Made by Quick Online Tips, tech help that works since 2004. See all our [free WordPress plugins](https://www.quickonlinetips.com/archives/wordpress-plugins/).

== Installation ==

1. In your WordPress admin, go to Plugins > Add New Plugin and search for "QOT Font Changer".
2. Click Install Now, then Activate. Nothing changes until you choose a font.
3. Open Appearance > Font Changer, pick fonts and save.

== Frequently Asked Questions ==

= A font on my site isn't in the list. =

The list comes from your theme's stylesheet and theme font settings. Fonts added by a page builder or another plugin may not appear. Set that part of the page under "Fonts by part of the page" instead.

= Will my fonts be lost when I update my theme? =

No. Your choices are saved by the plugin, not in the theme, and are applied on top of it.

= Can I go back to my theme's fonts? =

Yes. Untick "Use my font changes", or set a font back to "Keep".

= Does it add web font files? =

No. It only uses fonts already on your readers' devices.

== Screenshots ==

1. The fonts found in the theme, the parts of the page, and the live preview.

== Changelog ==

= 1.0 =
* First release.
