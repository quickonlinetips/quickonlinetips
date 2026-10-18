<?php
/*
Plugin Name: Simple Feed Copyright
Author: Quick Online Tips
Author URI: https://www.quickonlinetips.com
Version: 1.2
Description: Adds copyright notice at end of articles in full text RSS feeds, with back links to the blog and original article. 
Plugin URI: https://www.quickonlinetips.com/archives/simple-feed-copyright-wordpress-plugin/
*/
function simplefeedcopyright($content)
{
    $content = $content . '<p>Original article: <a href="' . get_permalink() . '" rel="bookmark" title="Permanent link to \'' . get_the_title() . '\'">' . get_the_title() . '</a><p>&copy;' . date("Y") . ' <a href="' . get_bloginfo('url') . '">' . get_bloginfo('name') . '</a>. All Rights Reserved.</p>';
    return $content;
}
add_filter('the_content_feed', 'simplefeedcopyright');
?>