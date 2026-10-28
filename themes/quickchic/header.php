<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'quickchic' ); ?></a>

<div id="page">

	<div class="row"> <!-- start row -->

		<header id="header">
			<?php the_custom_logo(); ?>

			<?php if ( is_front_page() && is_home() ) : ?>
				<h1 class="site-title"><a class="blogtitle" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
			<?php else : ?>
				<p class="site-title"><a class="blogtitle" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
			<?php endif; ?>

			<?php if ( get_bloginfo( 'description' ) ) : ?>
				<p class="description"><?php bloginfo( 'description' ); ?></p>
			<?php endif; ?>

			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location'       => 'primary',
					'container'            => 'nav',
					'container_class'      => 'main-menu',
					'container_aria_label' => __( 'Main menu', 'quickchic' ),
					'depth'                => 2,
				) );
			}
			?>
		</header>
