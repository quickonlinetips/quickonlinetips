<?php
// Numbered page links: « Newer 1 2 3 … Older »
the_posts_pagination( array(
	'mid_size'  => 1,
	'prev_text' => '&laquo; ' . __( 'Newer', 'quickstrap' ),
	'next_text' => __( 'Older', 'quickstrap' ) . ' &raquo;',
) );
