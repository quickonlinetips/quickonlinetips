=== QuickStrap ===
Contributors: quickonlinetips
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A fast Bootstrap blog theme with one, two or three columns: no JavaScript, nothing to set up, Bootstrap's classes ready for your posts.

== Description ==

QuickStrap is for bloggers and small sites who like Bootstrap's clean look, want room for widgets on both sides, and don't want a slow, cluttered page. Your readers get your posts first and fast, and you can use Bootstrap's buttons, cards, alerts and grid right inside your posts.

* **Ultra light and fast.** Bootstrap's stylesheet plus one small theme stylesheet. No JavaScript and no web fonts out of the box.
* **Plug and play.** Activate it and start writing. There is no options panel to learn.
* **Familiar and simple.** Set up widgets and menus in the usual Appearance > Widgets and Menus screens. Posts written in the block editor look just right.
* **One, two or three columns.** Posts in the middle, a right sidebar, and an optional narrow left sidebar. Leave the left one empty and the posts widen to fill its space. On tablets and phones nothing is hidden: the sidebars move below the posts.
* **Custom-coded.** Every file is hand-written and easy to read if you want to change something in a child theme.
* **Modern touches.** Automatic dark mode using Bootstrap's own dark colours, text that scales smoothly from phone to desktop, an editor that looks like your site, and clean printouts.

Features: Bootstrap 5.3.8 included, header colour and accent colour pickers, dark mode (can be switched off), Full width template for posts and pages, widget areas in the right and left sidebars, above and below posts and in three footer columns, phone menu button that works without JavaScript, footer menu with social icons, custom logo, custom background, featured images, Updated date on edited posts, sticky posts, threaded comments, numbered pagination, theme colours and text sizes in the block editor, block styles and patterns, starter content for new sites, optional Bootstrap JavaScript and Font Awesome icons, Skip to content link, print styles, translation ready.

To change the dark top bar or the blue accent, go to Appearance > Customize > Colours and pick your own. The same screen lets you turn off dark mode if you want the site to always stay light.

More about the theme: https://www.quickonlinetips.com/archives/quickstrap-wordpress-theme/

== Frequently Asked Questions ==

= Does QuickStrap support the block editor? =

Yes for writing posts and pages: block content is styled to match the theme. QuickStrap itself is a classic theme, so the site layout is set with widgets and menus, not with the Site Editor.

= Can I use Bootstrap classes in my posts? =

Yes. Bootstrap's full stylesheet is loaded, so classes such as btn btn-primary, alert alert-info, card, row and col-md-6 work in a Custom HTML block or in a block's Advanced > Additional CSS class field.

= My Bootstrap dropdowns, modals or Font Awesome icons stopped working after updating. =

QuickStrap 1.4 no longer loads Bootstrap's JavaScript or Font Awesome on every page, because the theme itself needs neither. If your posts or widgets use them, go to Appearance > Customize > Bootstrap Extras and tick "Load Bootstrap's JavaScript" or "Load Font Awesome icons".

= Where do I add my menu? =

Go to Appearance > Menus, create a menu and tick Main Menu (the dark bar at the top) or Footer Menu. On phones and tablets the Main Menu folds behind a menu button.

= Why does a new site already show widgets and a menu? =

On a brand-new WordPress install, QuickStrap offers starter content in Appearance > Customize: sidebar widgets (Search and Recent Posts on the right, Categories on the left), three footer columns (About, Recent Comments, Pages), About and Contact pages and a main menu, so you see the whole design straight away. Nothing is saved until you click Publish, and sites that already have content never see it. Edit or remove any of it in Appearance > Widgets.

= Do I need the Classic Widgets plugin? =

No. QuickStrap uses the block widgets WordPress gives every site. Add any block (Latest Posts, Categories, Paragraph, Image and more) in Appearance > Widgets.

= What shows in the sidebar before I add widgets? =

While both sidebars are empty, the right one shows Search, Recent Posts and Recent Comments and the narrow left one shows Categories, so a new site shows all three columns and never looks empty. As soon as you add your own widgets to a sidebar, they take over there. Once the Right Sidebar has widgets, an empty Left Sidebar simply isn't shown and the posts widen (a two-column site). If your old widgets were moved to Inactive when you switched themes, drag them into the sidebars in Appearance > Widgets.

= How do the two sidebars work? =

The Right Sidebar is the main one. The Left Sidebar is a narrow column on the left on wide screens; add widgets for three columns, or leave it empty for a two-column site (while both sidebars are still empty, default widgets fill both). On tablets and phones the sidebars move below the posts (right sidebar first), so their widgets are never hidden.

= How do I make a page without the sidebars? =

In the editor, open the Post (or Page) settings, find Template and pick "Full width, no sidebars".

= Where can I add widgets? =

Appearance > Widgets has seven areas: Right Sidebar, Left Sidebar, Above Posts (above the title of each single post), Below Posts (under each single post, before the comments) and Footer 1, 2 and 3. Empty areas are hidden, so add only what you need.

= Can I show full posts on the home page, or shorter excerpts? =

Yes. Appearance > Customize > Post Lists lets you show a short excerpt with Read More (the default) or the full post, and set the excerpt length in words. With full posts, add a More block in a post to choose where it breaks. Search results always show excerpts.

= How do I add social icons? =

Create a menu in Appearance > Menus with links to your profiles (for example https://www.instagram.com/yourname) and assign it to Footer Menu. Links to well-known networks show as small icons; other links stay as text.

= Can I turn off dark mode? =

Yes. Appearance > Customize > Colours, untick "Dark mode for readers who use it".

= How is Bootstrap updated? =

The theme's code lives on GitHub (https://github.com/quickonlinetips/quickonlinetips). A small script there, tools/update-quickstrap-vendor.sh, fetches a new Bootstrap or Font Awesome release from its GitHub release and copies in just the files the theme uses, so each QuickStrap release can ship the latest Bootstrap 5.

== Changelog ==

= 1.4 - October 2026 =
* Bootstrap 5.3.0 updated to 5.3.8, Font Awesome 5.13 to 7.3.1; the theme folder shrinks from about 4 MB to 1 MB
* No JavaScript and no web fonts by default: the phone menu button now works with plain HTML and CSS
* Customize > Bootstrap Extras: switch Bootstrap's JavaScript or Font Awesome back on if your posts use them
* The left sidebar no longer disappears on tablets and phones: it moves below the posts; with no widgets the posts widen to fill its space
* Automatic dark mode that follows the reader's device, using Bootstrap's own dark colours, with an off switch
* Header colour and accent colour pickers in Customize > Colours
* Full width template now works for posts too
* Footer Menu is shown (it was registered but never printed), with social icons for well-known networks
* Editor looks like the site; theme colours and text sizes in the block editor
* Text sizes scale smoothly from phone to desktop
* Updated date shown on edited posts
* Customize > Post Lists: excerpts or full posts, and the excerpt length
* Read More also shows after hand-written excerpts
* Print styles
* Block styles (Accent bar for quotes, Soft box for groups) and two block patterns (newsletter sign-up box, about the author)
* Block widgets work out of the box; no Classic Widgets plugin needed
* Until you add widgets, the right sidebar shows Search, Recent Posts and Recent Comments, and the left sidebar shows Categories, so a new site shows all three columns
* Starter content for new sites: sidebar widgets, footer columns, About and Contact pages, main menu
* Custom logo and tagline in the header; widget wrappers fixed (they closed a paragraph that was never opened)
* Numbered pagination, featured image on single posts, previous and next post links
* Accessibility: Skip to content link, proper screen reader text, visible keyboard focus, underlined links in posts
* Archive titles work for tags, authors and dates; search form on empty results and the 404 page
* Comments: tighter layout, fix for password-protected posts
* wp_footer() moved to just before </body>; all text translatable and escaped
* Tested up to WordPress 7.1, requires PHP 7.4

= 1.3 =
* Earlier release.

== Copyright ==

QuickStrap WordPress Theme, Copyright 2026 QuickOnlineTips.com
QuickStrap is distributed under the terms of the GNU GPL v2 or later.

== Resources ==

* Bootstrap 5.3.8, https://getbootstrap.com, (C) 2011-2025 The Bootstrap Authors, MIT License (vendor/bootstrap/LICENSE).
* Font Awesome Free 7.3.1, https://fontawesome.com, (C) Fonticons, Inc. Icons CC BY 4.0, fonts SIL OFL 1.1, code MIT License (vendor/font-awesome/LICENSE.txt).
* Social icons in inc/social-icons.php: Simple Icons (https://simpleicons.org), CC0 1.0; LinkedIn and Email icons from the Twenty Twenty theme by the WordPress team, GPLv2 or later.
* screenshot.png, theme screenshot by QuickOnlineTips, GPLv2 or later. Photos shown in the screenshot:
* Sunrise from a high mountain peak over a sea of clouds, by Jos Velasco, CC0, https://wordpress.org/photos/photo/86169f2493/
* Green mountain hills under a cloudy sky, by gaby21, CC0, https://wordpress.org/photos/photo/1226a7538f/
* Cobbled street under historic street lamps in Prague at night, by Rita Robles Loaiza, CC0, https://wordpress.org/photos/photo/7306a5ee57/
