<div id="sidebar" class="sidebar column right">

    <header id="header">
        <?php if (is_home()) { ?>
        <h1>
            <?php } ?>
            <a class="blogtitle" href="<?php echo esc_url( home_url() ); ?>">
                <?php bloginfo('name'); ?>
            </a>
            <?php if (is_home()) { ?>
        </h1>
        <?php } ?>
        <div class="description">
            <?php bloginfo('description'); ?>
        </div>
    </header>

    <aside>
        <ul>
            <li>
              <div>
				<?php if ( is_active_sidebar( 'sidebar' ) ) : ?>
                <?php dynamic_sidebar( 'sidebar' ); ?>
				<?php else : ?>
                <?php endif; ?>
              </div>
			</li>
        </ul>
    </aside>

</div>
<!-- /sidebar-->
