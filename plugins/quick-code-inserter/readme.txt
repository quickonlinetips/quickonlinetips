=== Quick Code Inserter ===
Contributors: pchere
Tags: insert code, header footer, analytics, ads, code snippets
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Paste any code and pick where it goes: the page head, before or after posts, after a paragraph, the sidebar or the footer. A live map shows each spot.

== Description ==

Quick Code Inserter puts your code exactly where you want it, without touching your theme files. Paste the code, pick a spot from one list, and save.

It is made for the everyday codes every site needs: Google Analytics, site verification tags, ad codes, a notice bar, a newsletter form after each post, a chat widget. Nothing more, so it stays small and easy.

**What you get**

* **12 places in one list.** The page head, right after the body tag, below the header, before the post, after any paragraph, after the post, above or below the comment form, the top or bottom of the sidebar, above the footer, and the very end of the page.
* **As many codes as you need.** Each code has its own card, so your analytics, ads and banners stay separate and easy to find.
* **A live map of the page.** As you choose places, a plan of a typical page shows a coloured tag where each code goes. No guessing what "wp_footer" means.
* **After paragraph 2, 3 or any number.** Put an ad or a sign-up box inside your posts, where readers actually are.
* **Show on every page, or just posts, pages or the homepage.**
* **Hide from logged-in users,** so your own visits don't count in your analytics.
* **Pause without deleting.** Switch any code off, or every code at once.
* **Safe view.** If a code breaks your layout, add `?qci-off` to any address while logged in as an admin to see the page without your code, then fix it.
* **Shortcodes work** in every place except the hidden page head.
* **Honest about your theme.** Block themes and classic themes have different spots. The list marks any place your current theme doesn't have.

**Why it is light and safe**

* No stylesheet or script on your site at all. Only your own code is added.
* Pages that have no code for them get nothing extra, not even the plugin's hooks.
* No PHP code, so a typo can't take your whole site down.
* Only people allowed to add scripts on your site can save scripts. On a multisite network, other admins can add plain HTML only.

Made by Quick Online Tips, tech help that works since 2004. See the [Quick Code Inserter plugin page](https://www.quickonlinetips.com/archives/quick-code-inserter-wordpress-plugin/) for more.

== Installation ==

1. In your WordPress dashboard, go to Plugins > Add New and search for "Quick Code Inserter".
2. Click Install Now, then Activate.
3. Go to Settings > Quick Code Inserter.
4. Paste your code, choose where it goes, and click Save Changes.

To install by hand, upload the `quick-code-inserter` folder to `/wp-content/plugins/` and activate it on the Plugins screen.

== Frequently Asked Questions ==

= Where do I put Google Analytics? =

Paste the code Google gives you and choose "In the page head". Tick "Hide from logged-in users" so your own visits aren't counted.

= Where does Google Tag Manager go? =

Google Tag Manager has two parts. Add the first as one code in the page head, and the second (the noscript part) as another code right after the body tag.

= Can I add PHP code? =

No, and that is on purpose. PHP mistakes can stop a whole site from loading. Quick Code Inserter takes HTML, JavaScript in script tags, CSS in style tags and shortcodes.

= A place says "not in your theme". Why? =

Block themes (like Twenty Twenty-Five) and older classic themes are built differently. Block themes have no widget sidebar, and classic themes have no standard spot below the header. The plugin only offers what WordPress itself provides, so it works with any theme. If you switch themes, the place may start working.

= My code broke how the site looks. What now? =

While logged in as an admin, open any page with `?qci-off` at the end of the address, for example `https://example.com/?qci-off`. The page shows without your code. Then go to Settings > Quick Code Inserter and fix or switch off the code.

= The code doesn't show on my homepage or archive pages. =

"Before the post", "After the post" and "After a paragraph" go into the text of posts. Archive pages that show short summaries instead of full posts don't show them. Places like the page head, sidebar and footer work on every page.

= Does it slow my site down? =

No. It adds no files of its own. Your code is the only thing added, and only on the pages you choose.

= Where can I send feedback? =

Please use the support forum on WordPress.org, or the plugin page above.

== Screenshots ==

1. The settings page, with a code card and the live map of where it goes.
2. Several codes, each in its own colour on the map.
3. Putting a sign-up box after paragraph 2 of every post.

== Changelog ==

= 1.0 =
* First release.
