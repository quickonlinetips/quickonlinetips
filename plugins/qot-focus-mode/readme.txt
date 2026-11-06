=== QOT Focus Mode ===
Contributors: pchere
Tags: focus mode, reading mode, distraction free, reader, long read
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A Focus button on your posts. One click hides the header, sidebars, footer and everything else, leaving just the post to read.

== Description ==

Long posts deserve a calm place to read. QOT Focus Mode adds one small button to your posts. When a reader presses it, everything except the post disappears: the header, menus, sidebars, ads, widgets and footer. The post moves to the middle of the screen at a comfortable width. Press the button again, or the Escape key, and the page comes back exactly as it was.

That's all it does. No dark mode, no fonts, no reading timers, nothing to slow your site down.

**What you get**

* **Works with any theme.** It finds the post on the page and hides everything around it, so classic themes and block themes both work, with no theme changes.
* **Your choice of place:** at the top of the post text, or floating in a corner so it stays in view while people scroll.
* **Your look:** icon and text, text only or icon only, your own words, and your own colours.
* **Centred reading width** you can set, about 700 to 800 pixels being easiest to read.
* **Remember focus** (optional): readers who like it stay in focus mode as they go from post to post. Saved only in their own browser.
* **Posts, pages or both.**
* **A live preview** on the settings page. Click its button to try focus mode before you save.

**Light by design**

* Under 2 KB of CSS and script, added inline, only on the posts that show the button.
* Nothing is loaded on other pages, and nothing at all in your admin outside its own settings page.
* No cookies, no tracking, no calls to other sites.

**Good to know:** the button appears on your posts as soon as you activate the plugin. Visit Settings > QOT Focus Mode to change where it shows and how it looks.

Made by Quick Online Tips, tech help that works since 2004. See all our [free WordPress plugins](https://www.quickonlinetips.com/archives/wordpress-plugins/).

== Installation ==

1. In your WordPress admin, go to Plugins > Add New Plugin and search for "QOT Focus Mode".
2. Click Install Now, then Activate.
3. Open any post: the Focus button is at the top. Change it in Settings > QOT Focus Mode.

== Frequently Asked Questions ==

= Does it work with my theme? =

It should. Instead of relying on theme-specific names, the plugin starts from the post itself and hides everything beside it, up to the page body. If your theme keeps the post title outside the post box, the title may be hidden too; try the floating button in that case.

= Will hidden ads or widgets stop working? =

They are only hidden from view while the reader is in focus mode. Nothing is removed from the page, and everything is back the moment they leave.

= Can I hide the button on one post? =

Developers can use the `qot_focus_mode_show` filter and return false for that post.

= Does it remember the choice? =

Only if you tick "Keep focus on as readers move to the next post". It is saved in the reader's own browser (local storage), never on your server.

== Screenshots ==

1. The settings page with its live preview.
2. A post with the Focus button.
3. The same post in focus mode.

== Changelog ==

= 1.0 =
* First release.
