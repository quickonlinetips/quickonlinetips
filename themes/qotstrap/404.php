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
		 		<?php get_template_part('template-parts/nav/topnav2'); ?>
	     </header>


 			<main class="container">
			<div class="row">
			<div class="column singlecol">



	  		<article class="text-center my-5">
                       <header class="header">
						<h1 class="entry-title" style="font-size: 3em; color: gray; margin-top: 50px;">404<br/>Page Not Found</h1>
						</header>

                        <p class="my-5 lead">
                            Its possible the link was mistyped or the page has moved!
                        </p>

				<u> <a class="" href="https://www.quickonlinetips.com/">Visit Homepage</a></u>
				<br />
				<u> <a class="" href="https://www.quickonlinetips.com/search/">Search here</a></u>


			 </article>

				<center>
		   	<?php if ( is_active_sidebar( '404-page' ) ) : ?>
                            <div class="my-5">
                                <?php dynamic_sidebar( '404-page' ); ?>
                            </div>
                    <?php endif; ?>
				</center>


		</div> 		<!-- end content col  -->


</div>		<!-- end row  -->
</main>		<!-- end container -->

<footer>
</footer>

<?php wp_footer(); ?>



<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-TR9TL577LP"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-TR9TL577LP');
</script>


</body>
</html>

