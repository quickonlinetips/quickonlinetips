=== Quick Feed Copyright ===
Contributors: pchere
Tags: rss, feed, copyright, scraper, attribution
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a copyright notice and a link to the original article at the end of every post in your feed, so copied feeds still credit you.

== Description ==

Your RSS feed travels far beyond your site. Feed readers show it, and scraper sites and autoblogs often copy it word for word onto their own pages. Quick Feed Copyright adds a short notice to the end of every post in your feed, so wherever your posts end up, they still say who wrote them and link back to the original.

**Refreshed for 2026.** First released in 2007 as Simple Feed Copyright, version 2.0 is rebuilt with a settings page and a live preview. Choose every part of the notice, change the wording, write your own, and add extras below it. Far more control than the original fixed notice, and more useful than ever now that feeds are copied more widely.

Out of the box, every post in your feed ends like this:

*Original article: Title of the Post* (linked to the post)

*© 2026 Your Blog Name. All Rights Reserved.* (your blog name linked to your site)

Those links tell readers and search engines where the article came from, even on a site that copied it.

**Choose your own wording**

Go to Settings > Quick Feed Copyright and pick each part of the copyright line, with a live preview as you go:

* **Symbol:** ©, (c), Copyright, Copyright ©, or none
* **Year:** this year, the year each post was published, a range such as 2006–2026, or none
* **Name:** your site title or another name, such as your company, linked to your home page or not
* **Rights:** All Rights Reserved, Some Rights Reserved, your own wording (links allowed, for example to a Creative Commons licence), or nothing

**Write your own notice**

Prefer your own words? Write the whole notice yourself, with HTML and handy placeholders such as `{title}`, `{link}`, `{site_link}`, `{author}` and `{copyright}`.

**Extras below the notice (all off until you switch them on)**

* A list of your latest posts, so feed readers find more of your articles
* Any extra text, such as a newsletter link or a short message

**Also**

* Adds the copyright line to the feed's own details (`<copyright>` in RSS, `<rights>` in Atom), which feed readers and directories read
* Can add the notice to summary feeds too, not just full-text feeds
* Only touches your feeds. Your site's pages are never changed.
* Lightweight: one setting saved in the database, no files or scripts loaded on your site, and nothing at all outside the feed and its settings page

Formerly called Simple Feed Copyright. Updating keeps your feed exactly as it was until you change a setting.

Made by Quick Online Tips, tech help that works since 2004. Plugin page: https://www.quickonlinetips.com/archives/simple-feed-copyright-wordpress-plugin/ More security tips: https://www.quickonlinetips.com/archives/category/security/

== Installation ==

1. In your dashboard, go to Plugins > Add New, search for **Quick Feed Copyright**, then click Install and Activate.
2. Your feed now has the notice. Visit yoursite.com/feed/ to see it, or read it in any feed reader.
3. To change the wording, go to Settings > Quick Feed Copyright.

== Frequently Asked Questions ==

= Where does the notice appear? =

Only in your feeds, at the end of each post. Your site's pages are not changed.

= My feed shows summaries. Will the notice appear? =

Yes, if you switch on "Also add it when your feed shows summaries" under Where it appears. Full-text feeds always get the notice. You choose full text or summary in Settings > Reading.

= I updated from Simple Feed Copyright. Has my feed changed? =

No. Until you save new settings, the notice is exactly the one the plugin always added: the linked post title, then the copyright year, your linked site title and All Rights Reserved.

= Which words can I use in my own notice? =

`{copyright}` the copyright line, `{title}` the post title, `{link}` the post address, `{site}` your site title, `{site_url}` your site address, `{site_link}` your site title linked to your site, `{author}` the post author, `{date}` the date the post was published and `{year}` this year. They also work in the latest posts heading and the extra text.

= Does the latest posts list slow down my feed? =

No. It is looked up once each time your feed is built, not once for every post, and only when you switch it on.

= Can developers change the notice? =

Yes. Use the `simple_feed_copyright_footer` filter, which receives the notice HTML and the post.

= Where can I send feedback about this plugin? =

Please use the support forum on WordPress.org, or the plugin page above.

== Screenshots ==

1. The settings page, with a live preview of the notice as it will look in a feed reader.
2. Writing your own notice, here with a Creative Commons licence.
3. A post in a feed reader, ending with the notice, the latest posts and a short extra message.

== Changelog ==

= 2.0 =
* 2026 refresh of the plugin first released in 2007, with far more control over the notice.
* New name: Quick Feed Copyright, formerly Simple Feed Copyright.
* New settings page with a live preview, under Settings > Quick Feed Copyright.
* Choose the copyright symbol, year (including the year each post was published, or a range), name and rights wording.
* Write your own notice, with HTML and placeholders.
* Optional list of your latest posts, and optional extra text, below the notice.
* Optional notice in summary feeds too.
* Adds the copyright line to the feed's own details (`<copyright>` in RSS, `<rights>` in Atom).
* Fixed: the notice's HTML was not closed properly.
* Updated for WordPress 7.1 and PHP 8.
* Your feed looks the same as before until you change a setting.

= 1.2 =
* Cleaned code. Updated links.

= 1.1 =
* Added Original Article title which links back to the original article permalink. Followed by the blog copyright notice.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 2.0 =
Now called Quick Feed Copyright, with a settings page to choose your own wording. Your feed stays the same until you change a setting.
