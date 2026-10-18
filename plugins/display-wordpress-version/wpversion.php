<?php
/*
Plugin Name: Display WordPress Version
Author: Quick Online Tips
Author URI: https://www.quickonlinetips.com
Version: 1.4
Description: Displays a Powered by WordPress version (X.Y.Z) notice anywhere in your blog template by manually adding php code to template.
Plugin URI: https://www.quickonlinetips.com/archives/display-wordpress-version-plugin/

Instructions: Add the following code
<?php quickversion(); ?> 
to the template wherever you want the notice to appear.

*/
function quickversion()
{
    echo "Powered by <a class='display-version' href='https://wordpress.org'>WordPress</a> ";
    echo bloginfo('version');
}
?>
