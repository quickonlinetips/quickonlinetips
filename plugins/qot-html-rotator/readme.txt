=== QOT HTML Rotator ===
Contributors: pchere
Tags: ad rotator, rotate ads, banner, widget, shortcode
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Rotate several HTML boxes (ads, banners, offers) in one sidebar widget or shortcode, in the ratio you choose. Works with page caching.

== Description ==

One sidebar spot, several things to show: two ad networks, your own book, a course, a sale. QOT HTML Rotator holds them all as boxes and shows each visitor one of them. Give a box more weight and it shows more often: weights 1 and 1 share the spot equally, weights 5 and 1 show the first five times as often.

**What you get**

* **Boxes of any HTML,** including ad codes with scripts, banners, images, forms and shortcodes.
* **Weights for each box** (1 to 100), so you decide the ratio.
* **Several rotations:** name them (for example "main" and "footer") and use each in its own place.
* **A sidebar widget** and a **shortcode**, `[qot_html_rotator group="main"]`, for posts, pages and the Shortcode block.
* **Works with page caching:** every box is in the page, hidden, and the visitor's browser picks one, so even cached pages rotate.
* **Scripts in the picked box run,** so ad codes work; boxes that aren't picked never load.
* **Same box for the whole visit** (optional), or a new pick on every page.
* **Pause a box** without deleting it.
* **A live chart** on the settings page shows the share of visitors each box gets.

**Light by design**

* One small inline script, only on pages that show a rotation. No CSS files.
* No cookies or tracking.

Made by Quick Online Tips, tech help that works since 2004. See all our [free WordPress plugins](https://www.quickonlinetips.com/archives/wordpress-plugins/).

== Installation ==

1. In your WordPress admin, go to Plugins > Add New Plugin and search for "QOT HTML Rotator".
2. Click Install Now, then Activate.
3. Add your boxes in Settings > QOT HTML Rotator.
4. Add the "QOT HTML Rotator" widget to a sidebar, or the shortcode to a post.

== Frequently Asked Questions ==

= Does it work with Google AdSense and other ad codes? =

Yes, with modern ad codes that load asynchronously, which is almost all of them today. Very old codes that use document.write can't be added after a page has loaded. Check each ad network's rules on how many ads you may show per page.

= Why does a visitor without JavaScript see the first box? =

Picking happens in the browser. Without JavaScript the first live box of the rotation is shown instead, so the spot is never empty.

= Who can add scripts? =

Only users allowed to add unfiltered HTML on your site (administrators on a normal site). Others can save plain HTML only.

= Does it count views or clicks? =

No. It keeps no statistics and stores nothing about visitors. Use your ad network's reports.

== Screenshots ==

1. Boxes with weights, and the live chart of how often each one shows.

== Changelog ==

= 1.0 =
* First release.
