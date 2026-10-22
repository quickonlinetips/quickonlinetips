<?php
/**
 * Plugin Name:       Quick META Keywords
 * Plugin URI:        https://github.com/quickonlinetips/quickonlinetips/tree/main/plugins/quick-meta-keywords
 * Description:       Adds a META keywords tag to your posts, made from their categories and tags, plus any keywords you add. Choose what goes in and see a live preview under Settings > Quick META Keywords.
 * Version:           2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       quick-meta-keywords
 */

// Stop the file being opened directly in a browser.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'QUICK_META_KEYWORDS_OPTION', 'quick_meta_keywords' );

/* ------------------------------------------------------------------ *
 * Settings and their defaults
 * ------------------------------------------------------------------ */

/**
 * The saved settings, with defaults for anything not saved yet:
 * categories and tags on, no extra keywords.
 *
 * @return array{categories: bool, tags: bool, extra: string}
 */
function quick_meta_keywords_settings() {
	$defaults = array(
		'categories' => true,
		'tags'       => true,
		'extra'      => '',
	);

	$saved = get_option( QUICK_META_KEYWORDS_OPTION, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return array_merge( $defaults, array_intersect_key( $saved, $defaults ) );
}

/**
 * Clean the settings before they are saved.
 *
 * @param mixed $input Raw values from the settings form.
 * @return array{categories: bool, tags: bool, extra: string}
 */
function quick_meta_keywords_sanitize( $input ) {
	$input = is_array( $input ) ? $input : array();

	// Store the extra keywords as one tidy, comma-separated line.
	$extra = isset( $input['extra'] ) ? (string) $input['extra'] : '';
	$extra = implode( ', ', quick_meta_keywords_clean_list( preg_split( '/[,\r\n]+/', $extra ) ) );

	return array(
		'categories' => ! empty( $input['categories'] ),
		'tags'       => ! empty( $input['tags'] ),
		'extra'      => $extra,
	);
}

/* ------------------------------------------------------------------ *
 * Building the keywords
 * ------------------------------------------------------------------ */

/**
 * Tidy a list of keywords: plain text, trimmed, no blanks and no
 * repeats (ignoring capital letters). The first spelling is kept.
 *
 * @param string[] $words Keywords in the order they should appear.
 * @return string[]
 */
function quick_meta_keywords_clean_list( $words ) {
	$clean = array();

	foreach ( (array) $words as $word ) {
		// Term names are stored with HTML entities, such as Tips &amp; Tricks.
		$word = html_entity_decode( (string) $word, ENT_QUOTES, 'UTF-8' );
		$word = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $word ) ) );

		if ( '' === $word ) {
			continue;
		}

		$key = function_exists( 'mb_strtolower' ) ? mb_strtolower( $word, 'UTF-8' ) : strtolower( $word );
		if ( ! isset( $clean[ $key ] ) ) {
			$clean[ $key ] = $word;
		}
	}

	return array_values( $clean );
}

/**
 * A post's category names, leaving out the catch-all "Uncategorized".
 *
 * @param int $post_id Post ID.
 * @return string[]
 */
function quick_meta_keywords_category_names( $post_id ) {
	$names = array();

	foreach ( get_the_category( $post_id ) as $category ) {
		if ( 'uncategorized' === $category->slug ) {
			continue;
		}
		$names[] = $category->name;
	}

	return $names;
}

/**
 * A post's tag names.
 *
 * @param int $post_id Post ID.
 * @return string[]
 */
function quick_meta_keywords_tag_names( $post_id ) {
	$tags = get_the_tags( $post_id );

	return is_array( $tags ) ? wp_list_pluck( $tags, 'name' ) : array();
}

/**
 * The keywords for one post, in order: categories, tags, then your
 * extra keywords.
 *
 * Developers can change the list with the quick_meta_keywords filter:
 * add_filter( 'quick_meta_keywords', function ( $keywords, $post ) { ... }, 10, 2 );
 *
 * @param WP_Post $post The post.
 * @return string[]
 */
function quick_meta_keywords_for_post( $post ) {
	$settings = quick_meta_keywords_settings();
	$words    = array();

	if ( $settings['categories'] ) {
		$words = array_merge( $words, quick_meta_keywords_category_names( $post->ID ) );
	}
	if ( $settings['tags'] ) {
		$words = array_merge( $words, quick_meta_keywords_tag_names( $post->ID ) );
	}
	$words = array_merge( $words, explode( ',', $settings['extra'] ) );

	$words = apply_filters( 'quick_meta_keywords', quick_meta_keywords_clean_list( $words ), $post );

	return quick_meta_keywords_clean_list( $words );
}

/**
 * Print the META keywords tag in the page head, on single posts only.
 *
 * Other post types can be added with the quick_meta_keywords_post_types
 * filter, for example: array( 'post', 'product' ).
 */
function quick_meta_keywords_head() {
	$post_types = (array) apply_filters( 'quick_meta_keywords_post_types', array( 'post' ) );

	if ( ! is_singular( $post_types ) ) {
		return;
	}

	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$keywords = quick_meta_keywords_for_post( $post );
	if ( empty( $keywords ) ) {
		return;
	}

	echo '<meta name="keywords" content="' . esc_attr( implode( ', ', $keywords ) ) . '" />' . "\n";
}
add_action( 'wp_head', 'quick_meta_keywords_head', 1 );

/* ------------------------------------------------------------------ *
 * Settings page: Settings > Quick META Keywords
 * ------------------------------------------------------------------ */

/**
 * Register the setting so options.php saves it.
 */
function quick_meta_keywords_register_setting() {
	register_setting(
		'quick_meta_keywords',
		QUICK_META_KEYWORDS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'quick_meta_keywords_sanitize',
		)
	);
}
add_action( 'admin_init', 'quick_meta_keywords_register_setting' );

/**
 * Add the page to the Settings menu.
 */
function quick_meta_keywords_add_page() {
	$hook = add_options_page(
		__( 'Quick META Keywords', 'quick-meta-keywords' ),
		__( 'Quick META Keywords', 'quick-meta-keywords' ),
		'manage_options',
		'quick-meta-keywords',
		'quick_meta_keywords_render_page'
	);

	add_action( 'admin_enqueue_scripts', function ( $current ) use ( $hook ) {
		if ( $current === $hook ) {
			quick_meta_keywords_admin_assets();
		}
	} );
}
add_action( 'admin_menu', 'quick_meta_keywords_add_page' );

/**
 * Recent posts for the preview, each with its category and tag names.
 *
 * @return array<int, array{title: string, categories: string[], tags: string[]}>
 */
function quick_meta_keywords_preview_posts() {
	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'no_found_rows'  => true,
		)
	);

	$list = array();
	foreach ( $posts as $post ) {
		$title  = get_the_title( $post );
		$list[] = array(
			'title'      => '' === $title ? __( '(no title)', 'quick-meta-keywords' ) : html_entity_decode( $title, ENT_QUOTES, 'UTF-8' ),
			'categories' => quick_meta_keywords_clean_list( quick_meta_keywords_category_names( $post->ID ) ),
			'tags'       => quick_meta_keywords_clean_list( quick_meta_keywords_tag_names( $post->ID ) ),
		);
	}

	// A sample post, so the preview works on a brand new site too.
	if ( empty( $list ) ) {
		$list[] = array(
			'title'      => __( 'Sample post', 'quick-meta-keywords' ),
			'categories' => array( __( 'WordPress', 'quick-meta-keywords' ), __( 'Tutorials', 'quick-meta-keywords' ) ),
			'tags'       => array( __( 'plugins', 'quick-meta-keywords' ), __( 'SEO', 'quick-meta-keywords' ), __( 'beginners', 'quick-meta-keywords' ) ),
		);
	}

	return $list;
}

/**
 * Styles and the live preview script, on the settings page only.
 */
function quick_meta_keywords_admin_assets() {
	wp_register_style( 'quick-meta-keywords-admin', false, array(), '2.0' );
	wp_enqueue_style( 'quick-meta-keywords-admin' );
	wp_add_inline_style(
		'quick-meta-keywords-admin',
		'.qmk-wrap{display:flex;flex-wrap:wrap;gap:32px;align-items:flex-start}' .
		'.qmk-wrap form{max-width:640px;flex:1 1 480px}' .
		// Each section is a white card with its heading in a bar across the top.
		'.qmk-card{box-sizing:border-box;background:#fff;border:1px solid #c3c4c7;border-radius:4px;box-shadow:0 1px 1px rgba(0,0,0,.04);margin:0 0 20px;padding:0 20px 6px}' .
		'.qmk-card>h2{margin:0 -20px;padding:12px 20px;border-bottom:1px solid #dcdcde;background:#f0f6fc;border-radius:4px 4px 0 0;font-size:15px}' .
		// Heading colours change from card to card in a fixed order: blue, green, yellow, pink, purple,
		// then round again (qmk-c1 to qmk-c5, in page order). The preview card is always teal.
		'.qmk-c1>h2{background:#f0f6fc}.qmk-c2>h2{background:#edfaef}.qmk-c3>h2{background:#fcf9e8}' .
		'.qmk-c4>h2{background:#fcf0f1}.qmk-c5>h2{background:#f5f0fc}.qmk-preview>h2{background:#e8f6f5}' .
		'.qmk-card .form-table{margin-top:4px}.qmk-card .form-table th{padding-left:0;width:150px}' .
		'.qmk-card .qmk-intro{margin:14px 0 0;color:#50575e}.qmk-card .description{margin:6px 0 0}' .
		// Preview card: beside the form and sticky on wide screens, above it on narrow ones.
		'.qmk-side{flex:1 1 400px;max-width:640px;position:sticky;top:46px;padding-bottom:20px;margin-bottom:0}' .
		'@media (max-width:1279px){.qmk-wrap{gap:20px}.qmk-side{order:-1;position:static}}' .
		'.qmk-box{margin-top:20px}' .
		'#qmk-extra{width:100%}' .
		'.qmk-pick{margin:0 0 10px}.qmk-pick label{display:block;margin:0 0 6px;font-weight:600}.qmk-pick select{width:100%;max-width:100%}.qmk-legend{margin:0 0 10px;color:#50575e}' .
		'.qmk-chips{display:flex;flex-wrap:wrap;gap:6px;margin:0 0 14px;padding:0;list-style:none}' .
		'.qmk-chips li{margin:0;padding:3px 10px;border-radius:12px;font-size:12px;line-height:18px;border:1px solid}' .
		'.qmk-chips .qmk-cat{background:#f0f6fc;border-color:#9ec2e6;color:#0a4b78}' .
		'.qmk-chips .qmk-tag{background:#edfaef;border-color:#8fd19e;color:#00450c}' .
		'.qmk-chips .qmk-extra{background:#fcf9e8;border-color:#e6d58a;color:#614200}' .
		'.qmk-code{margin:0;padding:14px 16px;background:#1e2433;color:#e6edf3;border-radius:4px;font:13px/1.6 Consolas,Monaco,monospace;white-space:pre-wrap;word-break:break-word}' .
		'.qmk-code .t{color:#7ee787}.qmk-code .a{color:#79c0ff}.qmk-code .s{color:#a5d6ff}' .
		'.qmk-count{margin:10px 0 6px;color:#50575e}.qmk-empty{color:#f2cc60}' .
		'.qmk-legend .qmk-key:first-child{margin-left:0}.qmk-key{display:inline-block;width:10px;height:10px;border-radius:2px;margin:0 4px 0 12px;vertical-align:-1px;border:1px solid}' .
		'.qmk-key.qmk-cat{background:#f0f6fc;border-color:#9ec2e6}.qmk-key.qmk-tag{background:#edfaef;border-color:#8fd19e}.qmk-key.qmk-extra{background:#fcf9e8;border-color:#e6d58a}'
	);

	$script = <<<'JS'
( function () {
	var data = window.qmkPreview;
	var picker = document.getElementById( 'qmk-post' );
	var useCats = document.getElementById( 'qmk-categories' );
	var useTags = document.getElementById( 'qmk-tags' );
	var extra = document.getElementById( 'qmk-extra' );
	var chips = document.getElementById( 'qmk-chips' );
	var code = document.getElementById( 'qmk-code' );
	var count = document.getElementById( 'qmk-count' );

	function escapeHtml( text ) {
		return text.replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' ).replace( /"/g, '&quot;' );
	}

	// Same rules as the plugin: trimmed, no blanks, no repeats ignoring capitals.
	function build() {
		var post = data.posts[ picker.value ] || data.posts[ 0 ];
		var seen = {};
		var list = [];

		function add( words, kind ) {
			words.forEach( function ( word ) {
				word = word.replace( /\s+/g, ' ' ).trim();
				var key = word.toLowerCase();
				if ( word && ! seen[ key ] ) {
					seen[ key ] = true;
					list.push( { word: word, kind: kind } );
				}
			} );
		}

		if ( useCats.checked ) {
			add( post.categories, 'cat' );
		}
		if ( useTags.checked ) {
			add( post.tags, 'tag' );
		}
		add( extra.value.split( /[,\r\n]+/ ), 'extra' );

		chips.innerHTML = list.map( function ( item ) {
			return '<li class="qmk-' + item.kind + '">' + escapeHtml( item.word ) + '</li>';
		} ).join( '' );

		if ( ! list.length ) {
			code.innerHTML = '<span class="qmk-empty">' + escapeHtml( data.none ) + '</span>';
			count.textContent = '';
			return;
		}

		// Escaped twice: once as the plugin does in the real tag, once to show that source as text.
		var content = escapeHtml( escapeHtml( list.map( function ( item ) {
			return item.word;
		} ).join( ', ' ) ) );

		code.innerHTML = '<span class="t">&lt;meta</span> <span class="a">name</span>=<span class="s">"keywords"</span> ' +
			'<span class="a">content</span>=<span class="s">"' + content + '"</span> <span class="t">/&gt;</span>';
		count.textContent = ( 1 === list.length ? data.one : data.many ).replace( '%d', list.length );
	}

	[ picker, useCats, useTags ].forEach( function ( el ) {
		el.addEventListener( 'change', build );
	} );
	extra.addEventListener( 'input', build );
	build();
} )();
JS;

	wp_register_script( 'quick-meta-keywords-admin', false, array(), '2.0', true );
	wp_enqueue_script( 'quick-meta-keywords-admin' );
	wp_add_inline_script(
		'quick-meta-keywords-admin',
		'window.qmkPreview = ' . wp_json_encode(
			array(
				'posts' => quick_meta_keywords_preview_posts(),
				/* translators: %d: number of keywords. */
				'one'   => __( '%d keyword', 'quick-meta-keywords' ),
				/* translators: %d: number of keywords. */
				'many'  => __( '%d keywords', 'quick-meta-keywords' ),
				'none'  => __( 'No keywords, so no tag is added to this post.', 'quick-meta-keywords' ),
			)
		) . ';'
	);
	wp_add_inline_script( 'quick-meta-keywords-admin', $script );
}

/**
 * The name of a popular SEO plugin that is active, if any.
 *
 * @return string Empty when none is found.
 */
function quick_meta_keywords_seo_plugin() {
	$known = array(
		'Rank Math'         => class_exists( 'RankMath' ),
		'Yoast SEO'         => defined( 'WPSEO_VERSION' ),
		'All in One SEO'    => defined( 'AIOSEO_VERSION' ),
		'SEOPress'          => defined( 'SEOPRESS_VERSION' ),
		'The SEO Framework' => defined( 'THE_SEO_FRAMEWORK_VERSION' ),
	);

	foreach ( $known as $name => $active ) {
		if ( $active ) {
			return $name;
		}
	}

	return '';
}

/**
 * The settings page itself.
 */
function quick_meta_keywords_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = quick_meta_keywords_settings();
	$posts    = quick_meta_keywords_preview_posts();
	$name     = QUICK_META_KEYWORDS_OPTION;
	$seo      = quick_meta_keywords_seo_plugin();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Quick META Keywords', 'quick-meta-keywords' ); ?></h1>
		<p><?php esc_html_e( 'Choose what goes into the META keywords tag on your posts. The preview updates as you go.', 'quick-meta-keywords' ); ?></p>

		<?php if ( $seo ) : ?>
			<div class="notice notice-info inline">
				<p>
					<?php
					printf(
						/* translators: %s: name of an SEO plugin, such as Rank Math. */
						esc_html__( '%1$s is active too. That is fine: it looks after titles and descriptions, and this plugin adds only the keywords tag. If %1$s is also set to add META keywords, turn that off there so your posts do not get two.', 'quick-meta-keywords' ),
						esc_html( $seo )
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<div class="qmk-wrap">
		<form method="post" action="options.php">
			<?php settings_fields( 'quick_meta_keywords' ); ?>

			<div class="qmk-card qmk-c1">
				<h2><?php esc_html_e( 'Keywords from each post', 'quick-meta-keywords' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Categories', 'quick-meta-keywords' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="qmk-categories" name="<?php echo esc_attr( $name . '[categories]' ); ?>" value="1" <?php checked( $settings['categories'] ); ?> />
								<?php esc_html_e( 'Use the post\'s categories as keywords', 'quick-meta-keywords' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Tags', 'quick-meta-keywords' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="qmk-tags" name="<?php echo esc_attr( $name . '[tags]' ); ?>" value="1" <?php checked( $settings['tags'] ); ?> />
								<?php esc_html_e( 'Use the post\'s tags as keywords', 'quick-meta-keywords' ); ?>
							</label>
						</td>
					</tr>
				</table>
			</div>

			<div class="qmk-card qmk-c2">
				<h2><?php esc_html_e( 'Extra keywords', 'quick-meta-keywords' ); ?></h2>
				<p class="qmk-intro"><label for="qmk-extra"><?php esc_html_e( 'Added to every post, after its categories and tags. Separate them with commas. Leave empty for none.', 'quick-meta-keywords' ); ?></label></p>
				<p>
					<textarea id="qmk-extra" name="<?php echo esc_attr( $name . '[extra]' ); ?>" rows="3" placeholder="<?php esc_attr_e( 'For example: tech tips, how to', 'quick-meta-keywords' ); ?>"><?php echo esc_textarea( $settings['extra'] ); ?></textarea>
				</p>
			</div>

			<?php submit_button(); ?>
		</form>

		<div class="qmk-side qmk-card qmk-preview">
			<h2><?php esc_html_e( 'Preview', 'quick-meta-keywords' ); ?></h2>
			<div class="qmk-box">
				<p class="qmk-pick">
					<label for="qmk-post"><?php esc_html_e( 'Show the tag for', 'quick-meta-keywords' ); ?></label>
					<select id="qmk-post">
						<?php foreach ( $posts as $index => $post ) : ?>
							<option value="<?php echo esc_attr( $index ); ?>"><?php echo esc_html( $post['title'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p class="qmk-legend" aria-hidden="true">
					<span class="qmk-key qmk-cat"></span><?php esc_html_e( 'Category', 'quick-meta-keywords' ); ?>
					<span class="qmk-key qmk-tag"></span><?php esc_html_e( 'Tag', 'quick-meta-keywords' ); ?>
					<span class="qmk-key qmk-extra"></span><?php esc_html_e( 'Extra', 'quick-meta-keywords' ); ?>
				</p>
				<ul id="qmk-chips" class="qmk-chips"></ul>
				<p class="qmk-code" id="qmk-code" aria-live="polite"></p>
				<p class="qmk-count" id="qmk-count"></p>
				<p class="description"><?php esc_html_e( 'This is the tag added to the post\'s page head. Repeated words appear once. The catch-all "Uncategorized" category is left out.', 'quick-meta-keywords' ); ?></p>
			</div>
		</div>
		</div>
	</div>
	<?php
}

/**
 * Add a "Settings" link next to Deactivate on the Plugins screen.
 *
 * @param string[] $links Existing links.
 * @return string[]
 */
function quick_meta_keywords_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=quick-meta-keywords' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'quick-meta-keywords' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'quick_meta_keywords_action_links' );

/**
 * When someone deletes the plugin, remove its saved settings too.
 */
function quick_meta_keywords_uninstall() {
	delete_option( QUICK_META_KEYWORDS_OPTION );
}
register_uninstall_hook( __FILE__, 'quick_meta_keywords_uninstall' );
