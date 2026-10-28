<?php
/**
 * Search form, as a Bootstrap input group: the box and the button side by side.
 * Used by get_search_form() (empty search results, the 404 page and widgets).
 *
 * @package QuickStrap
 */

$quickstrap_search_id = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="visually-hidden" for="<?php echo esc_attr( $quickstrap_search_id ); ?>"><?php esc_html_e( 'Search for:', 'quickstrap' ); ?></label>
	<div class="input-group">
		<input type="search" id="<?php echo esc_attr( $quickstrap_search_id ); ?>" class="form-control" placeholder="<?php echo esc_attr_x( 'Search&hellip;', 'placeholder', 'quickstrap' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
		<button type="submit" class="btn btn-primary"><?php echo esc_html_x( 'Search', 'submit button', 'quickstrap' ); ?></button>
	</div>
</form>
