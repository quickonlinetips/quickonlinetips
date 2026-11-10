<?php
/*
Template Name: Subscribe
*/
?>
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
						<h1 class="entry-title" style="font-size: 3em; color: gray; margin-top: 50px;">Subscribe Newsletter</h1>
						</header>

                        <p class="my-5 lead">
Get free tips and tricks in your inbox. Once a week. No Spam. Unsubscribe anytime.
				</p>


			 </article>

				<center>
		   	<?php if ( is_active_sidebar( 'subscribe-page' ) ) : ?>
                            <div class="my-5">
                                <?php dynamic_sidebar( 'subscribe-page' ); ?>
                            </div>
                    <?php endif; ?>
				</center>


		</div> 		<!-- end content col  -->


</div>		<!-- end row  -->
</main>		<!-- end container -->


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

