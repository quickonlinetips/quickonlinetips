=== QuickStrap ===
Contributors: quickonlinetips
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A free Bootstrap 5 WordPress starter theme: Bootstrap's own markup, no JavaScript, no build step. Activate and write, or build on it.

== Description ==

QuickStrap is a Bootstrap 5 WordPress starter theme for developers, freelancers and bloggers who know Bootstrap and want a clean, fast base without a toolchain. There is no npm, Sass, webpack or jQuery to set up: Bootstrap 5.3.8 is bundled, the theme adds one small stylesheet, and nothing runs in the visitor's browser. Use it as it is for a blog, news or job site, or treat it as the starting point for your own theme with a child theme.

* **Bootstrap 5, the real thing.** The templates use Bootstrap's own markup: the navbar, the grid, .pagination, .input-group, .form-control and .btn btn-primary. Change a Bootstrap variable or class and the whole theme follows, and every Bootstrap class works in your posts.
* **No JavaScript, no build step.** The phone menu button and the dropdown sub-menus work with plain HTML and CSS. Bootstrap's JavaScript, Bootstrap Icons and Font Awesome are one tick away in Customize > Bootstrap Extras when you need them.
* **Made to build on.** Ten action hooks, five filters, helper functions for the grid and container, CSS variables for the colours, and a blank child theme. See "Building on QuickStrap" below.
* **Plug and play.** Activate it and the site already looks finished: default widgets fill both sidebars until you add your own. There is no options panel to learn.
* **Familiar and simple.** Set up widgets and menus in the usual Appearance > Widgets and Menus screens. Posts written in the block editor look just right.
* **One, two or three columns.** Posts in the middle, a right sidebar, and an optional left sidebar. Leave the left one empty and the posts widen to fill its space. Customize > Layout widens the left sidebar or swaps the two sides. On tablets and phones nothing is hidden: the sidebars move below the posts.
* **Bootstrap with a click.** Card and Alert styles for the Group block, Bootstrap-style outline buttons, and ready-made Three cards, Hero box, Call to action, FAQ and Notice patterns. Bootstrap's classes still work in your posts too.
* **Menus with sub-menus.** Dropdowns on wide screens and indented links on phones, with no JavaScript.
* **Custom-coded.** Every file is hand-written and easy to read if you want to change something in a child theme.
* **Modern touches.** Automatic dark mode using Bootstrap's own dark colours, text that scales smoothly from phone to desktop, an editor that looks like your site, and clean printouts.

Features: Bootstrap 5.3.8 included, header colour and accent colour pickers, dark mode (can be switched off), light or dark top bar, centred or full-width page, three sidebar layouts, Full width template for posts and pages, widget areas in the right and left sidebars, above and below posts, between posts in lists and in three footer columns, main menu with dropdown sub-menus and a phone menu button, all without JavaScript, footer menu with social icons, custom logo, custom background, featured images, Updated date on edited posts, sticky posts, threaded comments, numbered pagination, theme colours and text sizes in the block editor, Bootstrap block styles (Card, Alerts, outline buttons) and patterns, starter content for new sites, optional Bootstrap JavaScript, Bootstrap Icons and Font Awesome, Skip to content link, print styles, translation ready.

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

= Can I widen the left sidebar, or put the sidebar on the left? =

Yes. Appearance > Customize > Layout has three choices: a narrow Left Sidebar with a wider Right Sidebar (the default), both sidebars the same width, or swapped, with the Right Sidebar's widgets on the left and the Left Sidebar's on the right. With the Left Sidebar empty, "swapped" gives a two-column site with its sidebar on the left.

= Why doesn't the left sidebar hide on phones any more? =

Hidden widgets confused people: they added widgets, saw them on their computer and couldn't find them on their phone. Now the left sidebar moves below the posts on tablets and phones, with its lists in columns.

= Does the menu support sub-menus? =

Yes, up to three levels. Add sub-items in Appearance > Menus by dragging a link under another. On wide screens they open as a dropdown when you point at the parent or reach it with the keyboard; on phones they are listed, indented, in the opened menu. No JavaScript needed.

= How do I use Bootstrap's look in my posts? =

Add a Group block and pick Card, Alert: info, Alert: success or Alert: warning under Styles; pick Outline for a Button. Or insert the Three cards, Hero box, Call to action, FAQ or Notice pattern from the QuickStrap patterns. Bootstrap's classes (like btn btn-primary, table, alert alert-info) also work in Additional CSS class or the HTML block.

= How do I make a page without the sidebars? =

In the editor, open the Post (or Page) settings, find Template and pick "Full width, no sidebars".

= Where can I add widgets? =

Appearance > Widgets has eight areas: Right Sidebar, Left Sidebar, Above Posts (above the title of each single post), Below Posts (under each single post, before the comments), Between Posts (in post lists after the third post) and Footer 1, 2 and 3. Empty areas are hidden, so add only what you need.

= Can I show full posts on the home page, or shorter excerpts? =

Yes. Appearance > Customize > Post Lists lets you show a short excerpt with Read More (the default) or the full post, and set the excerpt length in words. With full posts, add a More block in a post to choose where it breaks. Search results always show excerpts.

= How do I add social icons? =

Create a menu in Appearance > Menus with links to your profiles (for example https://www.instagram.com/yourname) and assign it to Footer Menu. Links to well-known networks show as small icons; other links stay as text.

= Can I turn off dark mode? =

Yes. Appearance > Customize > Colours, untick "Dark mode for readers who use it".

= Can I have a light top bar, or a full-width page? =

Yes. Appearance > Customize > Colours > "Light top bar" switches to Bootstrap's light navbar (light grey with dark text). Appearance > Customize > Layout > "Full-width page" stretches the top bar, posts, sidebars and footer to the edges of big screens.

= Can I use Bootstrap Icons? =

Yes. Appearance > Customize > Bootstrap Extras > "Load Bootstrap Icons", then add an icon in a Custom HTML block or widget, for example <i class="bi bi-star"></i>. They load only when ticked.

= How is Bootstrap updated? =

The theme's code lives on GitHub (https://github.com/quickonlinetips/quickonlinetips). A small script there, tools/update-quickstrap-vendor.sh, fetches a new Bootstrap, Bootstrap Icons or Font Awesome release from its GitHub release and copies in just the files the theme uses, so each QuickStrap release can ship the latest Bootstrap 5.

== Building on QuickStrap ==

QuickStrap is meant as a starting point. Make your changes in a child theme, so they survive updates: download the blank QuickStrap Child theme from https://www.quickonlinetips.com/archives/quickstrap-wordpress-theme/ and put your CSS in its style.css (it loads after Bootstrap and QuickStrap) and your PHP in its functions.php.

Bootstrap: version 5.3.8 is in vendor/bootstrap. Page numbers, the search form (searchform.php) and the comment form use Bootstrap's own markup (.pagination, .input-group, .form-control, .btn btn-primary), so Bootstrap's variables restyle them. Columns come from Bootstrap's grid; quickstrap_column_class( 'content' | 'right' | 'left' ) returns their classes and quickstrap_container_class() returns .container or .container-fluid.

Colours: change these CSS variables in a child theme (or use Customize > Colours):

* --qs-brand: the top bar (default #212529)
* --qs-accent: links, buttons, page numbers, .btn-primary (default #0d6efd)
* --qs-on-brand and --qs-on-accent: text on those colours (default #fff)
* --bs-body-font-size: base text size (default 0.9375rem, 15px)
* Greys, borders and backgrounds come from Bootstrap's own --bs- variables, so dark mode follows them.

Hooks (add_action in a child theme's functions.php):

* quickstrap_before_header, quickstrap_header_end (inside the top bar, after the menu), quickstrap_after_header
* quickstrap_before_content, quickstrap_after_content (full width, above and below the posts and sidebars)
* quickstrap_before_post_content, quickstrap_after_post_content (single posts: under the title, after the text and tags)
* quickstrap_before_footer, quickstrap_footer_start (in the footer, above the copyright), quickstrap_after_footer

Example: add_action( 'quickstrap_after_post_content', function () { echo '<p class="alert alert-info">Thanks for reading!</p>'; } );

Filters:

* quickstrap_show_default_sidebar: return false to turn off the default sidebar widgets on empty sidebars
* quickstrap_list_limit: lines shown in the theme's long starter lists before "N more" (default 10)
* quickstrap_between_posts_after: the post after which Between Posts shows (default 3)
* quickstrap_social_icons: return false to show footer menu links as text instead of icons
* quickstrap_custom_background_args: the Custom Background defaults

Body classes: qs-light-header (light top bar), qs-full-width (Full width template), qs-light-only (dark mode switched off).

== Changelog ==

= 1.4 - October 2026 =
* Bootstrap 5.3.0 updated to 5.3.8, Font Awesome 5.13 to 7.3.1; the theme folder shrinks from about 4 MB to 1 MB
* No JavaScript and no web fonts by default: the phone menu button now works with plain HTML and CSS
* Sub-menus finally show: dropdowns on wide screens and indented links on phones, up to three levels, still without JavaScript
* Customize > Layout: narrow left sidebar (default), both sidebars the same width, or the two sides swapped
* Group block styles Card, Alert: info, Alert: success and Alert: warning, Bootstrap-style outline buttons, and Three cards, Hero box, Call to action, FAQ (no JavaScript) and Notice patterns
* New Between Posts widget area in post lists, after the third post
* Page numbers, search form and comment form use Bootstrap's own markup; Bootstrap's primary buttons follow the Accent colour
* Light top bar and Full-width page options
* Ten action hooks for child themes, listed under Building on QuickStrap
* Customize > Bootstrap Extras: switch Bootstrap's JavaScript or Font Awesome back on if your posts use them, or load Bootstrap Icons, Bootstrap's own icon set
* The left sidebar no longer disappears on tablets and phones: it moves below the posts with its lists in columns; with no widgets the posts widen to fill its space
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
* Bootstrap Icons 1.13.2, https://icons.getbootstrap.com, (C) The Bootstrap Authors, MIT License (vendor/bootstrap-icons/LICENSE).
* Font Awesome Free 7.3.1, https://fontawesome.com, (C) Fonticons, Inc. Icons CC BY 4.0, fonts SIL OFL 1.1, code MIT License (vendor/font-awesome/LICENSE.txt).
* Social icons in inc/social-icons.php: Simple Icons (https://simpleicons.org), CC0 1.0; LinkedIn and Email icons from the Twenty Twenty theme by the WordPress team, GPLv2 or later.
* screenshot.png, theme screenshot by QuickOnlineTips, GPLv2 or later. Photos shown in the screenshot:
* Sunrise from a high mountain peak over a sea of clouds, by Jos Velasco, CC0, https://wordpress.org/photos/photo/86169f2493/
* Green mountain hills under a cloudy sky, by gaby21, CC0, https://wordpress.org/photos/photo/1226a7538f/
* Cobbled street under historic street lamps in Prague at night, by Rita Robles Loaiza, CC0, https://wordpress.org/photos/photo/7306a5ee57/
