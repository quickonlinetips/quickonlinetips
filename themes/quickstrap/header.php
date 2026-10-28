<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'quickstrap' ); ?></a>

<?php do_action( 'quickstrap_before_header' ); // Hooks for child themes: see "Building on QuickStrap" in readme.txt. ?>

<header id="masthead" class="site-header navbar navbar-expand-lg navbar-dark">
	<div class="container">

		<div class="site-branding">
			<?php the_custom_logo(); ?>

			<?php if ( is_front_page() && is_home() ) : ?>
				<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
			<?php else : ?>
				<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
			<?php endif; ?>

			<?php if ( get_bloginfo( 'description' ) ) : ?>
				<p class="site-description"><?php bloginfo( 'description' ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( has_nav_menu( 'header-nav' ) ) : ?>
			<?php
			/*
			 * The menu button on phones and tablets works without JavaScript:
			 * the button is the label of a hidden checkbox, and ticking the box
			 * shows the menu (see "Header and menu" in style.css).
			 */
			?>
			<input type="checkbox" id="qs-menu-toggle" class="qs-menu-toggle">
			<label for="qs-menu-toggle" class="navbar-toggler">
				<span class="navbar-toggler-icon" aria-hidden="true"></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'quickstrap' ); ?></span>
			</label>

			<?php
			wp_nav_menu( array(
				'theme_location'       => 'header-nav',
				'container'            => 'nav',
				'container_id'         => 'qs-main-menu',
				'container_class'      => 'collapse navbar-collapse main-menu',
				'container_aria_label' => __( 'Main menu', 'quickstrap' ),
				'menu_class'           => 'navbar-nav ms-lg-auto',
				'depth'                => 3, // Sub-menus: dropdowns on wide screens, indented on phones.
			) );
			?>
		<?php endif; ?>

		<?php do_action( 'quickstrap_header_end' ); // Inside the top bar, after the menu. ?>

	</div>
</header>

<?php do_action( 'quickstrap_after_header' ); ?>

<div id="page" class="container">
	<?php do_action( 'quickstrap_before_content' ); // Full width, above the posts and sidebars. ?>
	<div class="row">
