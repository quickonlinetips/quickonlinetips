=== Quick WordPress Version ===
Contributors: pchere
Tags: powered by wordpress, wordpress version, footer, footer credits, version check
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple "Powered by WordPress" line for your site, with or without the version number. See your installed and latest WordPress version at a glance.

== Description ==

Quick WordPress Version adds a small "Powered by WordPress" line to the bottom of your site. Activate it and the line appears, below your theme's footer, with no theme files to edit. It works with classic and block themes.

You choose what the line says:

* **Powered by WordPress** (the default, and the safest)
* **Powered by WordPress 7**, the main version number only
* **Powered by WordPress 7.1.3**, the full version
* **Your own text**, such as "Proudly running WordPress {version}", or anything else you like

Add extra text after the line too, for footer credits, a copyright or a link to your host.

**Know your version, keep it private**

Settings > Quick WordPress Version shows the WordPress version installed on your site, the latest version from WordPress.org, and whether an update is waiting, with a link to update. It uses the update check WordPress already does, so it adds no load to your site.

Showing your exact version number tells everyone, including attackers, which version your site runs. That is why the default line has no number. If you do show the number, it is hidden by itself while a WordPress update is waiting, so your site never advertises an out-of-date version. It comes back as soon as you update.

**Put the line where you want it**

* **Automatically** at the very bottom of every page, centred, left or right. This is all most sites need.

Optional other ways, if you want the line somewhere else:

* **Shortcode** `[quick_wp_version]` in a post, a page, or a Shortcode block in a block theme footer
* **Widget** in any footer or sidebar widget area
* **Template tag** `<?php quickversion(); ?>` for theme builders

Every place uses the same choices, so the line always looks the same.

**Light by design**

Tiny: no extra stylesheet or JavaScript on your site, and nothing loaded from outside. The settings page code runs only on its own screen.

This plugin was called Display WordPress Version until version 2.0. Sites already using `quickversion()` keep working.

== Installation ==

1. In your WordPress dashboard, go to Plugins > Add New, search for Quick WordPress Version, then Install and Activate.
2. The Powered by WordPress line now shows at the bottom of your site.
3. Go to Settings > Quick WordPress Version to choose what the line says and where it shows.

== Frequently Asked Questions ==

= Is it safe to show my WordPress version? =

Showing the exact number makes it easy for anyone to see which version you run, and to look for known problems in it. The default line, Powered by WordPress, shows no number. Showing only the main number (WordPress 7) gives away much less. If you show the full version, keep WordPress updated.

= What do visitors see if my WordPress is out of date? =

If you chose a line with a version number, visitors see just Powered by WordPress while an update is waiting. After you update, the number comes back by itself. You can turn this off in Settings > Quick WordPress Version, under When WordPress is out of date. Lines without a number never change.

= Where does the WordPress link go? =

To https://wordpress.org/, the official WordPress website. You can turn the link off.

= Does this hide the version number from my page source? =

No. This plugin only controls the line visitors see. WordPress itself also adds a version number to your page source. Security plugins can remove that if you want.

= My theme already calls quickversion(). Will the line show twice? =

No. Pages where your theme calls `quickversion()` skip the automatic line.

= Can I style the line? =

Yes. The line has the class `quick-wp-version`, and the WordPress link has the class `display-version`. Add your CSS in Appearance > Customize > Additional CSS, or in the Site Editor for block themes.

= Can developers change the line? =

Yes, with the `quick_wp_version_line` filter.

= Where can I send feedback? =

On the plugin page: https://www.quickonlinetips.com/archives/display-wordpress-version-plugin/

== Screenshots ==

1. Settings show your installed and latest WordPress version, and what the line says, with a live preview.
2. The line appears by itself at the bottom of every page. Optional other ways to add it are kept separate.
3. Use your own text and add extra text such as credits or a copyright.
4. The line at the bottom of a site using the Twenty Twenty-Five theme.

== Changelog ==

= 2.0 =
* New name: Quick WordPress Version (was Display WordPress Version). Same plugin, same address.
* Settings page shows your installed and latest WordPress version, and whether an update is waiting.
* Choose the line: Powered by WordPress, main version only, full version, or your own text.
* Extra text box for credits, a copyright or links.
* While a WordPress update is waiting, the version number is hidden from visitors until you update.
* The line can now be added automatically at the bottom of every page, with no theme files to edit.
* New `[quick_wp_version]` shortcode and widget.
* `quickversion()` still works and follows your choices.
* Tested with WordPress 7.1. Needs WordPress 6.0 and PHP 7.4 or later.

= 1.4 =
* Added class to the text which can be specifically tweaked as required.

= 1.3 =
* Now code can be added anywhere in theme template.
* PHP code needs to be manually added to template.
* No longer automatic addition to footer.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 2.0 =
New settings page with version check, more display choices, shortcode and widget. The line now shows Powered by WordPress without the number by default, and appears at the bottom of every page. Change both in Settings > Quick WordPress Version.
