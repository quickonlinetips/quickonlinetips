<?php
// Numbered page links: « Newer 1 2 3 … Older »
the_posts_pagination( array(
	'mid_size'  => 1,
	'prev_text' => '&laquo; ' . __( 'Newer', 'quickpress' ),
	'next_text' => __( 'Older', 'quickpress' ) . ' &raquo;',
) );
