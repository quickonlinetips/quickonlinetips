<?php
/**
 * Plugin Name:       QOT GitHub Sync
 * Description:       Pull the latest QuickOnlineTips plugins and themes from GitHub with one click, and preview each plugin's readme.txt the way WordPress.org will show it. For test sites only.
 * Version:           1.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Quick Online Tips
 * Author URI:        https://www.quickonlinetips.com
 * License:           GPLv2 or later
 *
 * Tools > QOT GitHub Sync lists every plugin and theme in the GitHub repo with
 * a Pull button. Each plugin also has a Readme button, which opens Tools >
 * QOT Readme Preview: the plugin's readme.txt from GitHub laid out like its
 * WordPress.org page, with the banner, icon and screenshots, plus a few checks.
 *
 * This helper lives in the repo's tools/ folder, so it can update itself too.
 */

defined( 'ABSPATH' ) || exit;

const QOT_SYNC_REPO = 'quickonlinetips/quickonlinetips';

// Files the site does not need: plugin page images (they go to WordPress.org's
// separate assets folder) and plugin screenshots. Theme screenshot.png is kept,
// since Appearance > Themes shows it.
const QOT_SYNC_SKIP = '#^(assets/|screenshot-\d+\.(png|jpe?g|gif|webp)$)#i';

/* ------------------------------------------------------------------ *
 * Talking to GitHub
 * ------------------------------------------------------------------ */

/**
 * Call the GitHub API (only for the latest commit and the file list; file
 * contents come from raw.githubusercontent.com, which has no hourly limit).
 *
 * @param string $path Path after /repos/owner/repo/.
 * @return array|WP_Error Decoded JSON.
 */
function qot_sync_github( $path ) {
	$response = wp_remote_get(
		'https://api.github.com/repos/' . QOT_SYNC_REPO . '/' . $path,
		array(
			'timeout' => 30,
			'headers' => array(
				'Accept'     => 'application/vnd.github+json',
				'User-Agent' => 'qot-github-sync',
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'qot_sync', 'Could not reach GitHub: ' . $response->get_error_message() . ' In Playground, check that network access is switched on.' );
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 403 === $code || 429 === $code ) {
		return new WP_Error( 'qot_sync', 'GitHub says too many requests from this connection for now. Wait a few minutes and try again.' );
	}
	if ( 404 === $code || 422 === $code ) {
		return new WP_Error( 'qot_sync', 'GitHub could not find that branch. Check the Branch box.' );
	}
	if ( 200 !== $code || ! is_array( $body ) ) {
		return new WP_Error( 'qot_sync', 'GitHub answered with an error (' . $code . '). Try again in a minute.' );
	}
	return $body;
}

/**
 * Download one file from the repo at an exact commit.
 *
 * @param string $sha  Commit.
 * @param string $path File path in the repo.
 * @return string|WP_Error File contents.
 */
function qot_sync_raw( $sha, $path ) {
	$response = wp_remote_get(
		qot_sync_raw_url( $sha, $path ),
		array(
			'timeout' => 30,
			'headers' => array( 'User-Agent' => 'qot-github-sync' ),
		)
	);
	if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
		return wp_remote_retrieve_body( $response );
	}

	// If that address can't be reached, ask the GitHub API for the file instead.
	$file = qot_sync_github( 'contents/' . implode( '/', array_map( 'rawurlencode', explode( '/', $path ) ) ) . '?ref=' . $sha );
	if ( is_wp_error( $file ) ) {
		return new WP_Error( 'qot_sync', 'Could not download ' . $path . '. ' . $file->get_error_message() );
	}
	if ( isset( $file['encoding'], $file['content'] ) && 'base64' === $file['encoding'] ) {
		return base64_decode( $file['content'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- GitHub sends file contents in base64.
	}
	return new WP_Error( 'qot_sync', 'Could not download ' . $path . '.' );
}

/**
 * Public address of a file in the repo at an exact commit.
 *
 * @param string $sha  Commit.
 * @param string $path File path in the repo.
 * @return string
 */
function qot_sync_raw_url( $sha, $path ) {
	return 'https://raw.githubusercontent.com/' . QOT_SYNC_REPO . '/' . $sha . '/' . implode( '/', array_map( 'rawurlencode', explode( '/', $path ) ) );
}

/**
 * The latest commit on a branch and every file in it.
 *
 * @param string $branch Branch name.
 * @return array|WP_Error { sha, message, files: path => true }
 */
function qot_sync_snapshot( $branch ) {
	static $cache = array();
	if ( isset( $cache[ $branch ] ) ) {
		return $cache[ $branch ];
	}

	$commit = qot_sync_github( 'commits/' . rawurlencode( $branch ) );
	if ( is_wp_error( $commit ) ) {
		return $commit;
	}
	$tree = qot_sync_github( 'git/trees/' . $commit['sha'] . '?recursive=1' );
	if ( is_wp_error( $tree ) ) {
		return $tree;
	}

	$files = array();
	foreach ( (array) $tree['tree'] as $item ) {
		if ( 'blob' === $item['type'] ) {
			$files[ $item['path'] ] = true;
		}
	}

	$cache[ $branch ] = array(
		'sha'     => $commit['sha'],
		'message' => strtok( (string) $commit['commit']['message'], "\n" ),
		'files'   => $files,
	);

	// Remember which plugins have a readme, for the links on the Plugins screen.
	$with_readme = array();
	foreach ( array_keys( $files ) as $path ) {
		if ( preg_match( '#^plugins/([^/]+)/readme\.txt$#', $path, $m ) ) {
			$with_readme[] = $m[1];
		}
	}
	update_option( 'qot_sync_readmes', $with_readme, false );

	return $cache[ $branch ];
}

/**
 * The plugins, themes and tools on GitHub.
 *
 * @param array $snapshot From qot_sync_snapshot().
 * @return array<string, string[]> { plugins: names, themes: names, tools: names }
 */
function qot_sync_items( $snapshot ) {
	$items = array(
		'plugins' => array(),
		'themes'  => array(),
		'tools'   => array(),
	);
	foreach ( array_keys( $snapshot['files'] ) as $path ) {
		if ( preg_match( '#^(plugins|themes|tools)/([^/]+)/#', $path, $m ) ) {
			$items[ $m[1] ][ $m[2] ] = $m[2];
		}
	}
	foreach ( $items as $kind => $names ) {
		sort( $names );
		$items[ $kind ] = $names;
	}
	return $items;
}

/**
 * Where an item lives on this site. Tools are plugins too.
 *
 * @param string $kind plugins, themes or tools.
 * @return string Folder.
 */
function qot_sync_base( $kind ) {
	return 'themes' === $kind ? get_theme_root() : WP_PLUGIN_DIR;
}

/* ------------------------------------------------------------------ *
 * Pulling a plugin or theme
 * ------------------------------------------------------------------ */

/**
 * Pull one plugin, theme or tool from GitHub and replace the copy on this site.
 *
 * Everything is downloaded into a new folder first. Only when every file has
 * arrived is the old folder swapped out, so a failed pull leaves the site as it was.
 * Saved settings are kept, because they live in the database, not the folder.
 *
 * @param string $kind   plugins, themes or tools.
 * @param string $name   Folder name, such as simple-feed-copyright.
 * @param string $branch Branch name.
 * @return string|WP_Error Success message.
 */
function qot_sync_run( $kind, $name, $branch ) {
	if ( ! in_array( $kind, array( 'plugins', 'themes', 'tools' ), true ) || ! preg_match( '/^[a-z0-9._-]+$/i', $name ) ) {
		return new WP_Error( 'qot_sync', 'Unknown plugin or theme.' );
	}

	$snapshot = qot_sync_snapshot( $branch );
	if ( is_wp_error( $snapshot ) ) {
		return $snapshot;
	}

	$prefix = $kind . '/' . $name . '/';
	$wanted = array();
	foreach ( array_keys( $snapshot['files'] ) as $path ) {
		if ( 0 === strpos( $path, $prefix ) ) {
			$relative = substr( $path, strlen( $prefix ) );
			if ( ! preg_match( QOT_SYNC_SKIP, $relative ) ) {
				$wanted[] = $relative;
			}
		}
	}
	if ( ! $wanted ) {
		return new WP_Error( 'qot_sync', 'There is no ' . $prefix . ' folder on the "' . $branch . '" branch.' );
	}

	$base   = qot_sync_base( $kind );
	$target = $base . '/' . $name;
	$fresh  = $base . '/.qot-sync-new-' . $name;
	$old    = $base . '/.qot-sync-old-' . $name;
	qot_sync_delete( $fresh );
	qot_sync_delete( $old );

	foreach ( $wanted as $relative ) {
		$contents = qot_sync_raw( $snapshot['sha'], $prefix . $relative );
		if ( is_wp_error( $contents ) ) {
			qot_sync_delete( $fresh );
			return $contents;
		}
		$file = $fresh . '/' . $relative;
		wp_mkdir_p( dirname( $file ) );
		if ( false === file_put_contents( $file, $contents ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			qot_sync_delete( $fresh );
			return new WP_Error( 'qot_sync', 'Could not write ' . $relative . ' on this site.' );
		}
	}

	// Swap the new folder in.
	if ( is_dir( $target ) && ! rename( $target, $old ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
		qot_sync_delete( $fresh );
		return new WP_Error( 'qot_sync', 'Could not replace the old ' . $name . ' folder.' );
	}
	rename( $fresh, $target ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
	qot_sync_delete( $old );

	if ( function_exists( 'opcache_reset' ) ) {
		opcache_reset();
	}

	$done = sprintf( 'Pulled %d files of %s from the "%s" branch. Latest change: "%s" (%s).', count( $wanted ), $name, $branch, $snapshot['message'], substr( $snapshot['sha'], 0, 7 ) );

	// Plugins and tools: switch on if not already. Themes are left for you to activate.
	if ( 'themes' !== $kind ) {
		$main = qot_sync_main_file( $name );
		if ( ! $main ) {
			return new WP_Error( 'qot_sync', $done . ' But no main plugin file was found, so it was not activated.' );
		}
		if ( ! is_plugin_active( $main ) ) {
			$result = activate_plugin( $main );
			if ( is_wp_error( $result ) ) {
				return new WP_Error( 'qot_sync', $done . ' But it could not be activated: ' . $result->get_error_message() );
			}
			$done .= ' Activated.';
		}
	}

	return $done;
}

/**
 * The plugin's main file, such as simple-feed-copyright/simplefeedcopyright.php.
 *
 * @param string $name Plugin folder name.
 * @return string Empty if none was found.
 */
function qot_sync_main_file( $name ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	wp_clean_plugins_cache( false );
	foreach ( array_keys( get_plugins( '/' . $name ) ) as $file ) {
		return $name . '/' . $file;
	}
	return '';
}

/**
 * Delete a folder and everything in it.
 *
 * @param string $dir Folder.
 */
function qot_sync_delete( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	$items = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ( $items as $item ) {
		$item->isDir() ? rmdir( $item->getPathname() ) : unlink( $item->getPathname() ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	rmdir( $dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/* ------------------------------------------------------------------ *
 * Admin pages
 * ------------------------------------------------------------------ */

add_action(
	'admin_menu',
	function () {
		add_management_page( 'QOT GitHub Sync', 'QOT GitHub Sync', 'manage_options', 'qot-github-sync', 'qot_sync_page' );
		add_management_page( 'QOT Readme Preview', 'QOT Readme Preview', 'manage_options', 'qot-readme-preview', 'qot_readme_page' );
	}
);

/**
 * Link to a plugin's readme preview.
 *
 * @param string $name Plugin folder.
 * @return string
 */
function qot_readme_url( $name ) {
	return add_query_arg(
		array(
			'page'   => 'qot-readme-preview',
			'plugin' => $name,
		),
		admin_url( 'tools.php' )
	);
}

// A "Readme preview" link under each QOT plugin on the Plugins screen.
add_filter(
	'plugin_row_meta',
	function ( $links, $file ) {
		$folder = dirname( $file );
		if ( current_user_can( 'manage_options' ) && in_array( $folder, (array) get_option( 'qot_sync_readmes', array() ), true ) ) {
			$links[] = '<a href="' . esc_url( qot_readme_url( $folder ) ) . '">Readme preview</a>';
		}
		return $links;
	},
	10,
	2
);

/**
 * Tools > QOT GitHub Sync: a Pull button for each plugin and theme on GitHub.
 */
function qot_sync_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	$branch = get_option( 'qot_sync_branch', 'main' );
	$notice = null;

	if ( isset( $_POST['qot_sync_branch'] ) && check_admin_referer( 'qot_sync' ) ) {
		$branch = sanitize_text_field( wp_unslash( $_POST['qot_sync_branch'] ) );
		$branch = '' !== $branch ? $branch : 'main';
		update_option( 'qot_sync_branch', $branch, false );

		// The button pressed says what to pull, as "kind|name".
		$pull = isset( $_POST['qot_sync_pull'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['qot_sync_pull'] ) ), 2 ) : array();
		if ( 2 === count( $pull ) ) {
			$result = qot_sync_run( $pull[0], $pull[1], $branch );
			$notice = is_wp_error( $result ) ? array( 'error', $result->get_error_message() ) : array( 'success', $result );
		}
	}

	$snapshot = qot_sync_snapshot( $branch );
	$labels   = array(
		'plugins' => 'Plugins',
		'themes'  => 'Themes',
		'tools'   => 'Tools',
	);
	?>
	<div class="wrap">
		<h1>QOT GitHub Sync</h1>
		<p>Pull the latest version of a plugin or theme straight from <a href="<?php echo esc_url( 'https://github.com/' . QOT_SYNC_REPO ); ?>" target="_blank" rel="noopener"><?php echo esc_html( QOT_SYNC_REPO ); ?></a>. It replaces the copy on this site and keeps your settings. For test sites only.</p>

		<?php if ( $notice ) : ?>
			<div class="notice notice-<?php echo esc_attr( $notice[0] ); ?>"><p><?php echo esc_html( $notice[1] ); ?></p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'qot_sync' ); ?>
			<p>
				<label for="qot-branch"><strong>Branch</strong></label>
				<input type="text" id="qot-branch" name="qot_sync_branch" value="<?php echo esc_attr( $branch ); ?>" class="regular-text" style="width:200px" />
				<span class="description">Usually <code>main</code>.</span>
			</p>

			<?php if ( is_wp_error( $snapshot ) ) : ?>
				<div class="notice notice-error inline"><p><?php echo esc_html( $snapshot->get_error_message() ); ?></p></div>
				<p><button type="submit" class="button">Try again</button></p>
			<?php else : ?>
				<p class="description">Latest change on GitHub: <strong><?php echo esc_html( $snapshot['message'] ); ?></strong> (<?php echo esc_html( substr( $snapshot['sha'], 0, 7 ) ); ?>)</p>
				<?php foreach ( qot_sync_items( $snapshot ) as $kind => $names ) : ?>
					<?php
					if ( ! $names ) {
						continue;
					}
					?>
					<h2><?php echo esc_html( $labels[ $kind ] ); ?></h2>
					<table class="widefat striped" style="max-width:860px">
						<tbody>
						<?php
						foreach ( $names as $name ) :
							$installed = is_dir( qot_sync_base( $kind ) . '/' . $name );
							$title     = $name;
							$state     = 'Not on this site';
							if ( $installed && 'themes' === $kind ) {
								$theme = wp_get_theme( $name );
								$title = $theme->exists() ? $theme->get( 'Name' ) : $name;
								$state = 'Version ' . $theme->get( 'Version' ) . ( get_stylesheet() === $name ? ', active' : '' );
							} elseif ( $installed ) {
								$main = qot_sync_main_file( $name );
								if ( $main ) {
									$data  = get_plugin_data( WP_PLUGIN_DIR . '/' . $main, false, false );
									$title = $data['Name'];
									$state = 'Version ' . $data['Version'] . ( is_plugin_active( $main ) ? ', active' : ', inactive' );
								}
							}
							$has_readme = 'plugins' === $kind && isset( $snapshot['files'][ 'plugins/' . $name . '/readme.txt' ] );
							?>
							<tr>
								<td><strong><?php echo esc_html( $title ); ?></strong><br><code><?php echo esc_html( $kind . '/' . $name ); ?></code></td>
								<td><?php echo esc_html( $state ); ?></td>
								<td style="text-align:right;white-space:nowrap">
									<?php if ( $has_readme ) : ?>
										<a class="button" href="<?php echo esc_url( qot_readme_url( $name ) ); ?>">Readme</a>
									<?php endif; ?>
									<button type="submit" class="button button-primary" name="qot_sync_pull" value="<?php echo esc_attr( $kind . '|' . $name ); ?>">
										<?php echo $installed ? 'Pull latest' : 'Install from GitHub'; ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endforeach; ?>
				<p class="description" style="margin-top:16px">Plugins are activated after pulling. Themes are not: activate them in Appearance &gt; Themes. Plugin banners, icons and screenshots are skipped, since the site doesn't need them. <strong>Readme</strong> shows how a plugin's page will look on WordPress.org.</p>
			<?php endif; ?>
		</form>
	</div>
	<?php
}

/* ------------------------------------------------------------------ *
 * Readme preview
 * ------------------------------------------------------------------ */

/**
 * Split a readme.txt into its parts, the way WordPress.org reads it.
 *
 * @param string $raw Readme text.
 * @return array { name, fields, short, sections: title => { intro, items: [ [title, body] ] } }
 */
function qot_readme_parse( $raw ) {
	$raw   = str_replace( array( "\r\n", "\r" ), "\n", $raw );
	$out   = array(
		'name'     => '',
		'fields'   => array(),
		'short'    => '',
		'sections' => array(),
	);
	$first = preg_split( '/^==\s*[^=]/m', $raw, 2 ); // Everything before the first == Section ==.
	$head  = $first[0];

	if ( preg_match( '/^===\s*(.+?)\s*===/m', $head, $m ) ) {
		$out['name'] = $m[1];
		$head        = substr( $head, strpos( $head, $m[0] ) + strlen( $m[0] ) );
	}
	$short = array();
	foreach ( explode( "\n", trim( $head ) ) as $line ) {
		if ( preg_match( '/^([A-Za-z ]+):\s*(.+)$/', $line, $m ) && ! $short ) {
			$out['fields'][ trim( $m[1] ) ] = trim( $m[2] );
		} elseif ( '' !== trim( $line ) ) {
			$short[] = trim( $line );
		}
	}
	$out['short'] = implode( ' ', $short );

	if ( preg_match_all( '/^==\s*([^=\n]+?)\s*==\s*$\n(.*?)(?=^==\s*[^=\n]|\z)/ms', $raw, $all, PREG_SET_ORDER ) ) {
		foreach ( $all as $s ) {
			$parts = preg_split( '/^=\s*(.+?)\s*=\s*$/m', $s[2], -1, PREG_SPLIT_DELIM_CAPTURE );
			$items = array();
			for ( $i = 1; $i + 1 < count( $parts ); $i += 2 ) {
				$items[] = array( $parts[ $i ], $parts[ $i + 1 ] );
			}
			$out['sections'][ $s[1] ] = array(
				'intro' => $parts[0],
				'items' => $items,
			);
		}
	}
	return $out;
}

/**
 * Turn readme Markdown into HTML: paragraphs, lists, **bold**, *italic*,
 * `code`, [links](url) and bare web addresses, as WordPress.org does.
 *
 * @param string $text Markdown.
 * @return string HTML.
 */
function qot_readme_md( $text ) {
	$html  = '';
	$list  = '';   // ul or ol while inside a list.
	$para  = array();
	$flush = function () use ( &$para, &$html ) {
		if ( $para ) {
			$html .= '<p>' . qot_readme_inline( implode( ' ', $para ) ) . "</p>\n";
			$para  = array();
		}
	};

	foreach ( explode( "\n", str_replace( "\r", '', trim( $text, "\n" ) ) ) as $line ) {
		if ( preg_match( '/^\s*(\*|-|\d+\.)\s+(.*)$/', $line, $m ) ) {
			$flush();
			$type = ctype_digit( rtrim( $m[1], '.' ) ) ? 'ol' : 'ul';
			if ( $list !== $type ) {
				$html .= $list ? "</$list>\n" : '';
				$html .= "<$type>\n";
				$list  = $type;
			}
			$html .= '<li>' . qot_readme_inline( $m[2] ) . "</li>\n";
		} elseif ( '' === trim( $line ) ) {
			$flush();
			if ( $list ) {
				$html .= "</$list>\n";
				$list  = '';
			}
		} elseif ( $list && preg_match( '/^\s{2,}\S/', $line ) ) {
			// A wrapped list item: join it to the item above.
			$html = preg_replace( '#</li>\n$#', ' ' . qot_readme_inline( trim( $line ) ) . "</li>\n", $html );
		} else {
			if ( $list ) {
				$html .= "</$list>\n";
				$list  = '';
			}
			$para[] = trim( $line );
		}
	}
	$flush();
	if ( $list ) {
		$html .= "</$list>\n";
	}
	return $html;
}

/**
 * Inline Markdown for one line or paragraph.
 *
 * @param string $text Text.
 * @return string HTML.
 */
function qot_readme_inline( $text ) {
	$keep = array();
	$hold = function ( $html ) use ( &$keep ) {
		$keep[] = $html;
		return "\x01" . ( count( $keep ) - 1 ) . "\x02";
	};

	// `code` first, shown exactly as typed.
	$text = preg_replace_callback(
		'/`([^`]+)`/',
		function ( $m ) use ( $hold ) {
			return $hold( '<code>' . esc_html( $m[1] ) . '</code>' );
		},
		$text
	);
	// [text](url)
	$text = preg_replace_callback(
		'/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/',
		function ( $m ) use ( $hold ) {
			return $hold( '<a href="' . esc_url( $m[2] ) . '" target="_blank" rel="noopener">' . esc_html( $m[1] ) . '</a>' );
		},
		$text
	);
	// Bare web addresses, which WordPress.org turns into links.
	$text = preg_replace_callback(
		'#\bhttps?://[^\s<>"\x01\x02]+[^\s<>"\x01\x02.,;:!?)]#',
		function ( $m ) use ( $hold ) {
			return $hold( '<a href="' . esc_url( $m[0] ) . '" target="_blank" rel="noopener">' . esc_html( $m[0] ) . '</a>' );
		},
		$text
	);
	$text = preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text );
	$text = preg_replace( '/(?<![\w*])\*(?!\s)(.+?)(?<!\s)\*(?![\w*])/s', '<em>$1</em>', $text );

	$text = preg_replace_callback(
		"/\x01(\d+)\x02/",
		function ( $m ) use ( $keep ) {
			return $keep[ (int) $m[1] ];
		},
		$text
	);
	return $text;
}

/**
 * Checks worth knowing before a release.
 *
 * @param array  $readme   Parsed readme.
 * @param int    $n_shots  Screenshot images found.
 * @param string $version  Version in the main plugin file.
 * @return array[] [ ok|bad, text ]
 */
function qot_readme_checks( $readme, $n_shots, $version ) {
	$out    = array();
	$fields = $readme['fields'];

	$n     = function_exists( 'mb_strlen' ) ? mb_strlen( $readme['short'] ) : strlen( $readme['short'] );
	$out[] = array( $n <= 150 ? 'ok' : 'bad', 'Short description: ' . $n . ' of 150 characters' . ( $n <= 150 ? '' : '. WordPress.org cuts off the rest.' ) );

	$tags  = array_filter( array_map( 'trim', explode( ',', isset( $fields['Tags'] ) ? $fields['Tags'] : '' ) ) );
	$out[] = array( count( $tags ) <= 5 ? 'ok' : 'bad', 'Tags: ' . count( $tags ) . ' of 5' . ( count( $tags ) <= 5 ? '' : '. Only the first 5 show.' ) );

	$caps  = isset( $readme['sections']['Screenshots'] ) ? preg_match_all( '/^\d+\.\s/m', $readme['sections']['Screenshots']['intro'] ) : 0;
	$out[] = array( $caps === $n_shots ? 'ok' : 'bad', 'Screenshots: ' . $n_shots . ' images, ' . $caps . ' captions' );

	$stable = isset( $fields['Stable tag'] ) ? $fields['Stable tag'] : '';
	if ( $version ) {
		$out[] = array( $stable === $version ? 'ok' : 'bad', $stable === $version ? 'Stable tag ' . $stable . ' matches the plugin file' : 'Stable tag ' . $stable . ' but the plugin file says ' . $version . '. The release will stop.' );
	}
	$logged = false;
	if ( isset( $readme['sections']['Changelog'] ) ) {
		foreach ( $readme['sections']['Changelog']['items'] as $item ) {
			$logged = $logged || trim( $item[0] ) === $stable;
		}
	}
	$out[] = array( $logged ? 'ok' : 'bad', $logged ? 'Changelog has version ' . $stable : 'No changelog entry for version ' . $stable );

	return $out;
}

/**
 * Tools > QOT Readme Preview.
 */
function qot_readme_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$branch   = get_option( 'qot_sync_branch', 'main' );
	$snapshot = qot_sync_snapshot( $branch );

	echo '<div class="wrap qot-rp">';
	if ( is_wp_error( $snapshot ) ) {
		echo '<h1>Readme preview</h1><div class="notice notice-error"><p>' . esc_html( $snapshot->get_error_message() ) . '</p></div></div>';
		return;
	}

	$plugins = array();
	foreach ( array_keys( $snapshot['files'] ) as $path ) {
		if ( preg_match( '#^plugins/([^/]+)/readme\.txt$#', $path, $m ) ) {
			$plugins[] = $m[1];
		}
	}
	sort( $plugins );
	$slug = isset( $_GET['plugin'] ) ? sanitize_file_name( wp_unslash( $_GET['plugin'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only picks which readme to show.
	if ( ! in_array( $slug, $plugins, true ) ) {
		$slug = $plugins ? $plugins[0] : '';
	}

	echo '<style>' . qot_readme_css() . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed CSS.
	?>
	<div class="qot-rp-top">
		<h1>Readme preview</h1>
		<p>Each plugin's <code>readme.txt</code> laid out the way WordPress.org will show it, read fresh from GitHub (branch <code><?php echo esc_html( $branch ); ?></code>, latest change: <?php echo esc_html( $snapshot['message'] ); ?>). <a href="<?php echo esc_url( admin_url( 'tools.php?page=qot-github-sync' ) ); ?>">Back to QOT GitHub Sync</a></p>
		<nav class="qot-rp-tabs">
			<?php foreach ( $plugins as $p ) : ?>
				<a href="<?php echo esc_url( qot_readme_url( $p ) ); ?>"<?php echo $p === $slug ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $p ); ?></a>
			<?php endforeach; ?>
		</nav>
	</div>
	<?php
	if ( $slug ) {
		qot_readme_render( $slug, $snapshot );
	}
	echo '</div>';
}

/**
 * One plugin, laid out like its WordPress.org page.
 *
 * @param string $slug     Plugin folder.
 * @param array  $snapshot From qot_sync_snapshot().
 */
function qot_readme_render( $slug, $snapshot ) {
	$dir = 'plugins/' . $slug . '/';
	$raw = qot_sync_raw( $snapshot['sha'], $dir . 'readme.txt' );
	if ( is_wp_error( $raw ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html( $raw->get_error_message() ) . '</p></div>';
		return;
	}
	$readme = qot_readme_parse( $raw );
	$sec    = $readme['sections'];
	$url    = function ( $file ) use ( $snapshot ) {
		return qot_sync_raw_url( $snapshot['sha'], $file );
	};

	// Images: screenshots sit in the plugin folder or its assets folder.
	$shots  = array();
	$banner = '';
	$icon   = '';
	foreach ( array_keys( $snapshot['files'] ) as $path ) {
		if ( 0 !== strpos( $path, $dir ) ) {
			continue;
		}
		$rel = substr( $path, strlen( $dir ) );
		if ( preg_match( '#^(?:assets/)?screenshot-(\d+)\.(png|jpe?g|gif|webp)$#i', $rel, $m ) ) {
			$shots[ (int) $m[1] ] = $path;
		} elseif ( preg_match( '#^assets/banner-1544x500\.(png|jpe?g)$#', $rel ) || ( ! $banner && preg_match( '#^assets/banner-772x250\.(png|jpe?g)$#', $rel ) ) ) {
			$banner = $path;
		} elseif ( preg_match( '#^assets/icon-256x256\.(png|jpe?g)$#', $rel ) || ( ! $icon && preg_match( '#^assets/icon-128x128\.(png|jpe?g)$#', $rel ) ) ) {
			$icon = $path;
		}
	}
	ksort( $shots );
	$caps = array();
	if ( isset( $sec['Screenshots'] ) && preg_match_all( '/^\d+\.\s+(.*)$/m', $sec['Screenshots']['intro'], $mm ) ) {
		$caps = $mm[1];
	}

	// Version in the main plugin file, for the Stable tag check.
	$version = '';
	foreach ( array_keys( $snapshot['files'] ) as $path ) {
		if ( preg_match( '#^' . preg_quote( $dir, '#' ) . '[^/]+\.php$#', $path ) ) {
			$php = qot_sync_raw( $snapshot['sha'], $path );
			if ( ! is_wp_error( $php ) && preg_match( '/^\s*\*?\s*Plugin Name:/mi', $php ) && preg_match( '/^\s*\*?\s*Version:\s*(\S+)/mi', $php, $vm ) ) {
				$version = $vm[1];
				break;
			}
		}
	}

	$nav   = array();
	$parts = array();
	$add   = function ( $id, $title, $body ) use ( &$nav, &$parts ) {
		$nav[]   = '<a href="#qot-rp-' . $id . '">' . esc_html( $title ) . '</a>';
		$parts[] = '<section id="qot-rp-' . $id . '" class="sec"><h2>' . esc_html( $title ) . '</h2>' . $body . '</section>';
	};

	if ( isset( $sec['Description'] ) ) {
		$body = qot_readme_md( $sec['Description']['intro'] );
		foreach ( $sec['Description']['items'] as $item ) {
			$body .= '<h3>' . esc_html( $item[0] ) . '</h3>' . qot_readme_md( $item[1] );
		}
		$add( 'description', 'Description', $body );
	}
	if ( $shots ) {
		$figs = '';
		$i    = 0;
		foreach ( $shots as $path ) {
			$cap   = isset( $caps[ $i ] ) ? qot_readme_md( $caps[ $i ] ) : '<p><em>No caption in readme.txt</em></p>';
			$figs .= '<figure><img src="' . esc_url( $url( $path ) ) . '" alt="Screenshot ' . ( $i + 1 ) . '" loading="lazy"><figcaption>' . $cap . '</figcaption></figure>';
			++$i;
		}
		$add( 'screenshots', 'Screenshots', '<div class="shots">' . $figs . '</div>' );
	}
	if ( isset( $sec['Installation'] ) ) {
		$add( 'installation', 'Installation', qot_readme_md( $sec['Installation']['intro'] ) );
	}
	if ( isset( $sec['Frequently Asked Questions'] ) ) {
		$s    = $sec['Frequently Asked Questions'];
		$body = qot_readme_md( $s['intro'] );
		foreach ( $s['items'] as $item ) {
			$body .= '<details><summary>' . esc_html( $item[0] ) . '</summary><div>' . qot_readme_md( $item[1] ) . '</div></details>';
		}
		$add( 'faq', 'FAQ', $body );
	}
	if ( isset( $sec['Changelog'] ) ) {
		$s    = $sec['Changelog'];
		$body = qot_readme_md( $s['intro'] );
		foreach ( $s['items'] as $item ) {
			$body .= '<h4>' . esc_html( $item[0] ) . '</h4>' . qot_readme_md( $item[1] );
		}
		$add( 'changelog', 'Changelog', $body );
	}
	if ( isset( $sec['Upgrade Notice'] ) ) {
		$notes = '';
		foreach ( $sec['Upgrade Notice']['items'] as $item ) {
			$notes .= '<h4>' . esc_html( $item[0] ) . '</h4>' . qot_readme_md( $item[1] );
		}
		$parts[] = '<aside class="upgrade"><p class="eyebrow">Upgrade notice</p><p class="hint">Not on the plugin page. WordPress shows this in people\'s dashboards next to the update.</p>' . $notes . '</aside>';
	}

	$f    = $readme['fields'];
	$meta = '';
	foreach ( array(
		'Version'            => 'Stable tag',
		'Requires WordPress' => 'Requires at least',
		'Tested up to'       => 'Tested up to',
		'Requires PHP'       => 'Requires PHP',
		'License'            => 'License',
	) as $label => $key ) {
		if ( ! empty( $f[ $key ] ) ) {
			$meta .= '<div><dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $f[ $key ] ) . '</dd></div>';
		}
	}
	$tags = '';
	foreach ( array_filter( array_map( 'trim', explode( ',', isset( $f['Tags'] ) ? $f['Tags'] : '' ) ) ) as $tag ) {
		$tags .= '<span>' . esc_html( $tag ) . '</span>';
	}
	$checks = '';
	foreach ( qot_readme_checks( $readme, count( $shots ), $version ) as $c ) {
		$checks .= '<li class="' . esc_attr( $c[0] ) . '">' . esc_html( $c[1] ) . '</li>';
	}
	$live = 'https://wordpress.org/plugins/' . $slug . '/';
	?>
	<article class="plugin">
		<?php if ( $banner ) : ?>
			<img class="banner" src="<?php echo esc_url( $url( $banner ) ); ?>" alt="">
		<?php endif; ?>
		<header class="title<?php echo $banner ? '' : ' no-banner'; ?>">
			<?php if ( $icon ) : ?>
				<img class="icon" src="<?php echo esc_url( $url( $icon ) ); ?>" width="96" height="96" alt="">
			<?php endif; ?>
			<div><h1><?php echo esc_html( $readme['name'] ); ?></h1><p class="by">By <span>QuickOnlineTips</span></p></div>
		</header>
		<p class="short"><?php echo esc_html( $readme['short'] ); ?></p>
		<div class="cols">
			<div class="main">
				<nav class="secnav"><?php echo implode( '', $nav ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts. ?></nav>
				<?php echo wp_kses_post( implode( '', $parts ) ); ?>
			</div>
			<aside class="side">
				<dl class="meta"><?php echo wp_kses_post( $meta ); ?></dl>
				<div class="tags"><p class="eyebrow">Tags</p><div><?php echo wp_kses_post( $tags ); ?></div></div>
				<div class="checks"><p class="eyebrow">Readme checks</p><ul><?php echo wp_kses_post( $checks ); ?></ul></div>
				<p class="live"><a href="<?php echo esc_url( $live ); ?>" target="_blank" rel="noopener">Current live page</a> (shows the last released version)</p>
				<p class="src"><a href="<?php echo esc_url( 'https://github.com/' . QOT_SYNC_REPO . '/blob/' . $snapshot['sha'] . '/' . $dir . 'readme.txt' ); ?>" target="_blank" rel="noopener"><code><?php echo esc_html( $dir . 'readme.txt' ); ?></code></a></p>
			</aside>
		</div>
	</article>
	<?php
}

/**
 * Styles for the preview, kept inside .qot-rp so the rest of the admin is untouched.
 *
 * @return string CSS.
 */
function qot_readme_css() {
	return '
.qot-rp { --fg:#1e1e1e; --muted:#50575e; --line:#dcdcde; --band:#f6f7f7; --chip:#f0f0f1; --link:#3858e9; --ok:#00703c; --bad:#b32d2e;
  max-width:1060px; color:var(--fg); font-size:16px; line-height:1.6; }
.qot-rp a { color:var(--link); }
.qot-rp img { max-width:100%; height:auto; display:block; }
.qot-rp-top { background:var(--band); border:1px solid var(--line); border-radius:4px; padding:14px 18px 0; margin:10px 0 24px; }
.qot-rp-top h1 { font-size:20px; margin:0 0 4px; padding:0; }
.qot-rp-top p { margin:0 0 12px; color:var(--muted); font-size:14px; }
.qot-rp-tabs { display:flex; gap:4px; overflow-x:auto; }
.qot-rp-tabs a { padding:8px 14px; color:var(--muted); text-decoration:none; border-bottom:3px solid transparent; white-space:nowrap; font-size:15px; }
.qot-rp-tabs a[aria-current] { color:var(--fg); border-bottom-color:var(--link); font-weight:600; }
.qot-rp .plugin { background:#fff; border:1px solid var(--line); border-radius:4px; padding:20px; }
.qot-rp .banner { width:100%; border-radius:4px; }
.qot-rp .title { display:flex; gap:18px; align-items:center; margin-top:-40px; padding-left:20px; position:relative; }
.qot-rp .title.no-banner { margin-top:0; padding-left:0; }
.qot-rp .icon { width:96px; height:96px; flex:none; border-radius:4px; }
.qot-rp .title h1 { margin:34px 0 0; padding:0; font-size:30px; line-height:1.2; font-weight:600; }
.qot-rp .title.no-banner h1 { margin-top:0; }
.qot-rp .by { margin:2px 0 0; color:var(--muted); font-size:14px; }
.qot-rp .by span { color:var(--link); }
.qot-rp .short { font-size:19px; margin:18px 0 8px; max-width:70ch; }
.qot-rp .cols { display:grid; grid-template-columns:minmax(0,1fr) 280px; gap:48px; margin-top:20px; align-items:start; }
.qot-rp .secnav { display:flex; flex-wrap:wrap; gap:6px 18px; padding:10px 0; border-block:1px solid var(--line); font-size:15px; }
.qot-rp .secnav a { text-decoration:none; }
.qot-rp .sec { padding-top:22px; scroll-margin-top:40px; }
.qot-rp .sec h2 { font-size:22px; margin:0 0 10px; }
.qot-rp .sec h3 { font-size:18px; margin:26px 0 6px; }
.qot-rp .sec h4 { font-size:16px; margin:20px 0 4px; }
.qot-rp .sec p, .qot-rp .sec li { font-size:16px; max-width:70ch; }
.qot-rp .sec ul { list-style:disc; padding-left:1.4em; margin-left:0; }
.qot-rp .sec ol { list-style:decimal; padding-left:1.4em; margin-left:0; }
.qot-rp .sec li { margin:4px 0; }
.qot-rp code { font-size:0.88em; background:var(--chip); padding:1px 5px; border-radius:3px; }
.qot-rp details { border:1px solid var(--line); border-radius:4px; margin:10px 0; }
.qot-rp summary { cursor:pointer; padding:12px 16px; font-weight:600; }
.qot-rp details > div { padding:0 16px 6px; }
.qot-rp .shots { display:grid; gap:28px; }
.qot-rp .shots figure { margin:0; display:grid; gap:8px; }
.qot-rp .shots img { border:1px solid var(--line); }
.qot-rp figcaption p { margin:0; color:var(--muted); font-size:15px; }
.qot-rp .upgrade { margin-top:32px; padding:14px 18px; background:var(--band); border:1px dashed var(--line); border-radius:4px; }
.qot-rp .upgrade h4 { margin:8px 0 2px; }
.qot-rp .hint { color:var(--muted); font-size:14px; }
.qot-rp .eyebrow { margin:0; font-size:12px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:var(--muted); }
.qot-rp .side { display:grid; gap:22px; font-size:15px; min-width:0; }
.qot-rp .meta { margin:0; }
.qot-rp .meta div { display:flex; justify-content:space-between; gap:12px; padding:9px 0; border-bottom:1px solid var(--line); }
.qot-rp .meta dt { color:var(--muted); }
.qot-rp .meta dd { margin:0; font-weight:600; text-align:right; }
.qot-rp .tags div { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
.qot-rp .tags span { background:var(--chip); border-radius:3px; padding:2px 8px; font-size:14px; }
.qot-rp .checks ul { list-style:none; padding:0; margin:8px 0 0; display:grid; gap:6px; }
.qot-rp .checks li { padding-left:22px; position:relative; margin:0; }
.qot-rp .checks li::before { position:absolute; left:0; font-weight:700; }
.qot-rp .checks li.ok::before { content:"\2713"; color:var(--ok); }
.qot-rp .checks li.bad { color:var(--bad); }
.qot-rp .checks li.bad::before { content:"!"; color:var(--bad); }
.qot-rp .live, .qot-rp .src { margin:0; }
.qot-rp .src code { font-size:13px; word-break:break-all; }
@media (max-width:900px) { .qot-rp .cols { grid-template-columns:1fr; gap:28px; } .qot-rp .title { margin-top:12px; padding-left:0; } .qot-rp .title h1 { margin-top:0; font-size:24px; } }
';
}
