<?php /*
Example template
Author: mitcho (Michael Yoshitaka Erlewine)
*/
?>

<aside class="my-2">
<?php if ($related_query->have_posts()):?>

<h3>Related posts you will like</h3>
<ul class="">
    <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
    <li class="my-2 " style=""><a class="srpw-block" href="<?php the_permalink() ?>" ><?php the_title(); ?></a></li>
    <?php endwhile; ?>
</ul>
<?php else: ?>
<p>No related posts.</p>
<?php endif; ?>
	</aside>
