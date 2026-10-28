<?php
/**
 * Template Name: Full width, no sidebar
 * Template Post Type: post, page
 *
 * The same post or page layout, without the right column, so the post
 * runs across the whole page. Pick it in the editor under
 * Post (or Page) > Template.
 *
 * @package QuickPress
 */

get_template_part( is_page() ? 'page' : 'single' );
