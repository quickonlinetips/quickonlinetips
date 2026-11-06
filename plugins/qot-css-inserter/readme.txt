=== QOT CSS Inserter ===
Contributors: pchere
Tags: css, custom css, additional css, style, theme
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Paste your own CSS in one simple box and it's added to every page. A second box keeps CSS for one theme, so nothing is lost when you switch themes.

== Description ==

WordPress's Additional CSS belongs to the theme: switch themes and your CSS seems to vanish. QOT CSS Inserter keeps your CSS safe in the plugin, in two boxes:

* **CSS for every theme:** kept whatever theme you use. For things that belong to your site, like your brand colours or hiding an element.
* **CSS for one theme:** pick a theme from the list and give it its own CSS, which is added only while that theme is active. Try a new theme with its own tweaks and switch back without losing anything.

**What you get**

* **A proper code box** with CSS colours and line numbers (WordPress's own code editor).
* **Pretty print:** one click tidies messy or minified CSS into one declaration per line.
* **Copy and Download** for each box, so you can keep a backup (downloads carry the date in their name).
* **A live preview** of your home page that shows your CSS as you type, before anyone else sees it.
* **Smaller pages:** visitors get your CSS without comments and extra spaces, while your boxes keep them.
* **One switch** to pause all your CSS while you look for a problem.
* **Block editor too** (optional), so posts look the same while you write.

**Light by design**

* One style tag at the end of the page head, nothing else. No files to load.
* Your CSS can't break out of its style tag, so a paste mistake can't add scripts to your pages.

Find it under Appearance > CSS Inserter.

Made by Quick Online Tips, tech help that works since 2004. See all our [free WordPress plugins](https://www.quickonlinetips.com/archives/wordpress-plugins/).

== Installation ==

1. In your WordPress admin, go to Plugins > Add New Plugin and search for "QOT CSS Inserter".
2. Click Install Now, then Activate.
3. Open Appearance > CSS Inserter, paste your CSS and save.

== Frequently Asked Questions ==

= How is this different from Customize > Additional CSS? =

Additional CSS is saved with the theme, so it disappears from view when you change themes. Here, CSS for every theme stays with your site, and CSS for one theme waits for that theme to come back.

= Which comes first, the every-theme CSS or the theme CSS? =

Every-theme CSS first, then the active theme's CSS, both after your theme's own styles. So theme CSS can fine-tune the every-theme CSS.

= What happens to CSS for a theme I deleted? =

It is kept, in case you install the theme again.

= Can I paste a style tag? =

No need: paste the CSS rules only. Any style tags are removed when you save.

== Screenshots ==

1. The two CSS boxes with their tools and the live preview of the home page.

== Changelog ==

= 1.0 =
* First release.
