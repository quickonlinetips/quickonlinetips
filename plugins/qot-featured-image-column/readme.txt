=== QOT Featured Image Column ===
Contributors: quickonlinetips
Tags: featured image, thumbnail, admin column, missing featured image, image seo
Requires at least: 5.5
Tested up to: 6.8
Requires PHP: 7.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

See every post's featured image in the Posts list, and find the ones that are missing, too small, broken or without alt text.

== Description ==

QOT Featured Image Column adds a thumbnail column to the Posts, Pages and other admin lists, and points out featured images that need fixing. Made for sites with hundreds or thousands of posts.

= Features =

* **Thumbnail column**, on or off from **Screen Options**. Nothing to set up.
* **"No Featured Image" view** beside All, Published and Drafts, with a count. One click lists every post that still needs an image.
* **Problem badges** under each thumbnail:
  * *Small*: narrower than 1200px, the size Google Discover and social cards look best at.
  * *No alt*: the image has no alt text.
  * *File missing*: the image file is gone from the server.
  * *Broken*: the image was deleted from the Media Library.
* **Bigger preview on hover** (or keyboard focus), with the image size. The larger image loads only when you hover.
* Click a thumbnail to open the post editor.
* Works on Posts, Pages and any custom post type with featured images. WooCommerce products are skipped, because WooCommerce has its own image column.
* Light: thumbnails lazy-load, so nothing downloads while the column is hidden. No settings page, no database tables, no ads.

Part of the QOT plugin family by [Quick Online Tips](https://www.quickonlinetips.com/).

== Installation ==

1. Install from Plugins > Add New, or upload the zip and activate.
2. Go to Posts > All Posts.
3. Open **Screen Options** and tick **Featured Image**.

== Frequently Asked Questions ==

= I can't see the column after activating =

Open Screen Options on the Posts screen and tick Featured Image.

= My images are on a CDN or S3. Will they all show "File missing"? =

No. The file check only runs for images stored in your own uploads folder.

= Can I change the 1200px width check? =

Yes, with the `qot_featured_image_column_min_width` filter. Return 0 to turn it off.

= Can I limit it to some post types? =

Yes, with the `qot_featured_image_column_post_types` filter, which receives the array of post type names.

= Other filters =

* `qot_featured_image_column_check_files` (bool, attachment ID): turn the file check off.
* `qot_featured_image_column_issues` (array, attachment ID): add or remove badges.

== Changelog ==

= 1.1.0 =
* New: "No Featured Image" view with a count.
* New: Small, No alt, File missing and Broken badges.
* New: bigger preview on hover and keyboard focus.
* Changed: WooCommerce products are skipped (they already have an image column).

= 1.0.0 =
* First release.
