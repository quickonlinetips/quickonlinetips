<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
			<?php get_template_part('header-common'); ?>
            <?php wp_head(); ?>
		</head>

<body <?php body_class(); ?> >
	<?php wp_body_open(); ?>

		<header id="banner" class="">
			 	<?php get_template_part('template-parts/nav/topnav'); ?>
	     </header>

	<!--
	<header id="banner" class="">
			 	<?php get_template_part('template-parts/nav/topnav2'); ?>
	     </header>
	-->


            <main class="container mt-2">

			<div class="row">

				<!--
<div class="xcol-12 xcol-2-md column singlecol wide2of12 hide-sm hide-xs"></div>
-->
				<div class="xcol-12 xcol-8-md xcol-8-lg column middlecol wide8of12 p-2">

