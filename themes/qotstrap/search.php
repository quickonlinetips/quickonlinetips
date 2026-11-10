<?php get_header(); ?>


    <h1>Search Results found for <em>&#8216;<?php echo $s ?>&#8217;</em></h1>
	 <?php get_template_part('loop'); ?>
    	<?php get_template_part('template-parts/nav/pagination'); ?>


		</div> 		<!-- end content col  -->


</div>		<!-- end row  -->
</main>		<!-- end container -->


<?php wp_footer(); ?>



<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-568061-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-568061-1');
</script>


</body>
</html>
