=== QOT All Archives ===
Contributors: pchere
Tags: archives, categories, tags, sitemap, shortcode
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

One shortcode builds a complete archives page: every category or tag in its own box, with its latest posts and a "View all" link.

== Description ==

A good archives page helps readers find your older posts, and helps search engines find them too. QOT All Archives builds one for you. Click "Make a categories page", publish, and every category on your site appears in its own neat box with its latest posts and a link to see them all.

**What you get**

* **Every category, or every tag,** each in its own box: name, number of posts, the latest posts and a "View all 57 posts" link.
* **One click** makes a draft archives page (or tags page) with everything in place.
* **Two shortcodes:** `[qot_category_archives]` and `[qot_tag_archives]`.
* **Columns that fit:** as many boxes side by side as your page has room for, or exactly 2, 3 or 4. Phones always get one column.
* **Your choices:** posts per box, A to Z or biggest first, leave out small categories or tags, post counts, dates, top-level categories only, your own "View all" words and heading colours.
* **Pick and mix per page** with shortcode settings: `include`, `exclude`, `columns`, `posts`, `order`, `min_posts`, `top_only`, `counts`, `dates`.
* **Fast, even on big sites:** the archives are built once and kept ready. Publishing or editing a post refreshes them straight away.
* **A live preview** made from your own categories.

**Light by design**

* No scripts. A little CSS, only on pages that show archives. The boxes take your theme's fonts and link colours.

Made by Quick Online Tips, tech help that works since 2004. See all our [free WordPress plugins](https://www.quickonlinetips.com/archives/wordpress-plugins/).

== Installation ==

1. In your WordPress admin, go to Plugins > Add New Plugin and search for "QOT All Archives".
2. Click Install Now, then Activate.
3. Go to Settings > QOT All Archives and click "Make a categories page". Publish the draft that opens.

== Frequently Asked Questions ==

= How do I show only some categories? =

`[qot_category_archives include="news,reviews,guides"]` shows just those, in that order. Use the names from each category's address (its slug) or its ID number. `exclude` leaves categories out.

= My site has hundreds of tags. Will the page be huge? =

Set "Smallest box" to 5 or 10 posts, so only your main tags get a box. You can also do this on one page with `min_posts="10"`.

= Can the archives fill the whole width of the page? =

The boxes fill your content area. For a wider page, choose your theme's full-width template for the archives page.

= Does it slow my site? =

No. The archives are built once and kept ready for the hours you choose (12 by default), and rebuilt when you publish or edit a post.

== Screenshots ==

1. An archives page with category boxes.
2. The settings page with the one-click page buttons and the live preview.

== Changelog ==

= 1.0 =
* First release.
