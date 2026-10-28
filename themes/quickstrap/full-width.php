<?php
/**
 * Template Name: Full width, no sidebars
 * Template Post Type: post, page
 *
 * The same post or page layout, without the sidebars, so the post runs
 * across the whole page. Pick it in the editor under Post (or Page) > Template.
 * The file keeps its old name so pages already using it keep it.
 *
 * @package QuickStrap
 */

get_template_part( is_page() ? 'page' : 'single' );
