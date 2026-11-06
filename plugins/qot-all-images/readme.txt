=== QOT All Images ===
Contributors: pchere
Tags: images, gallery, image archive, thumbnails, shortcode
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

One shortcode builds an image archive: the pictures from your posts as a grid of thumbnails, each linking to its post.

== Description ==

Readers often remember a picture before they remember a title. QOT All Images gives them one page with the images from your blog, as a neat grid of thumbnails. Click any image to open the post it came from.

**What you get**

* **Images from inside your posts,** not just featured images. Pictures you reused in several posts are found too.
* **One image per post** for a tidy grid on big blogs, or **every image**.
* **"Load more"** shows the next set (48 by default), so the page stays quick however many images you have. It's a plain link, so it works without JavaScript and with page caching.
* **Your choice of look:** columns that fit the page or 3 to 6, square, wide or natural thumbnails, post titles under each image, open in a new tab.
* **One category only** with `[qot_all_images category="recipes"]`.
* **Fast:** each post's images are listed once, when you save it, so the page doesn't search through your posts on every visit. A button on the settings page lists all your older posts at once.
* **Light:** small thumbnails (the medium size of images from your media library), lazy loading, no scripts on your site.
* **A live preview** made from your own images.

Made by Quick Online Tips, tech help that works since 2004. See all our [free WordPress plugins](https://www.quickonlinetips.com/archives/wordpress-plugins/).

== Installation ==

1. In your WordPress admin, go to Plugins > Add New Plugin and search for "QOT All Images".
2. Click Install Now, then Activate.
3. In Settings > QOT All Images, click "Find images in all posts now".
4. Add the shortcode `[qot_all_images]` to a page (in a Shortcode block) and publish it.

== Frequently Asked Questions ==

= My blog has thousands of images. Will the page be slow? =

No. Images are shown one load at a time, and "One image per post" keeps the grid tidy. The list of images is made once and kept, not searched on every visit.

= Which images are left out? =

Emoji, avatars and SVG icons. Images inside drafts and private posts are never shown.

= I added an image to an old post. When does it show? =

As soon as you save the post.

= Can it also show category boxes? =

That's our sister plugin, QOT All Archives: an archives page with every category and its latest posts.

== Screenshots ==

1. The settings page with the live preview of your images.

== Changelog ==

= 1.0 =
* First release.
