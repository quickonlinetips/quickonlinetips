<?php
/**
 * Template Name: Wide, for big photos
 * Template Post Type: post, page
 *
 * The same post or page, on a wider page (about 1060px of content instead
 * of 660px), so large photos, galleries and wide images get the room they
 * deserve. Pick it in the editor under Post (or Page) > Template.
 *
 * @package QuickPic
 */

get_template_part( is_page() ? 'page' : 'single' );
