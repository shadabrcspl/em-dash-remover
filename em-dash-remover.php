<?php
/**
 * Plugin Name:       Em Dash Remover
 * Plugin URI:        https://github.com/shadabrcspl/em-dash-remover
 * Description:       Permanently cleans or dynamically replaces em dash (—) and en dash (–) along with HTML entities (&mdash;, &ndash;, &#8212;, &#8211;) in posts, pages, titles, excerpts, and Elementor builder content. Includes 1-Click Permanent Database Cleaner.
 * Version:           5.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Arshad Faraz & Shadab Alam
 * Author URI:        https://github.com/shadabrcspl
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       em-dash-remover
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Em_Dash_Remover {

	/**
	 * Plugin version.
	 */
	const VERSION = '5.0.0';

	/**
	 * Default target em-dash and en-dash characters and HTML entities.
	 *
	 * @var array<int, string>
	 */
	private const DEFAULT_TARGETS = array(
		'—',            // UTF-8 Literal Em Dash (U+2014)
		'–',            // UTF-8 Literal En Dash (U+2013)
		'&mdash;',      // Lowercase HTML named entity
		'&MDASH;',      // Uppercase HTML named entity
		'&ndash;',      // Lowercase en-dash named entity
		'&NDASH;',      // Uppercase en-dash named entity
		'&#8212;',      // Decimal HTML entity (em dash)
		'&#8211;',      // Decimal HTML entity (en dash)
		'&#x2014;',     // Hexadecimal HTML entity (em dash)
		'&#x02014;',    // Padded hexadecimal HTML entity (em dash)
		'&#X2014;',     // Uppercase hex HTML entity (em dash)
		'&#X02014;',    // Padded uppercase hex HTML entity (em dash)
		'&#x2013;',     // Hexadecimal HTML entity (en dash)
		'&#x02013;',    // Padded hexadecimal HTML entity (en dash)
		'&#X2013;',     // Uppercase hex HTML entity (en dash)
		'&#X02013;',    // Padded uppercase hex HTML entity (en dash)
	);

	/**
	 * Default replacement character.
	 *
	 * @var string
	 */
	private const DEFAULT_REPLACEMENT = '-';

	/**
	 * Initialize plugin hooks.
	 */
	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'start_output_buffer' ), 0 );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'wp_ajax_em_dash_remover_get_posts', array( __CLASS__, 'ajax_get_posts' ) );
		add_action( 'wp_ajax_em_dash_remover_process_batch', array( __CLASS__, 'ajax_process_batch' ) );
	}

	/**
	 * Register Tools admin menu page.
	 */
	public static function admin_menu() {
		add_management_page(
			'Em Dash Remover',
			'Em Dash Remover',
			'manage_options',
			'em-dash-remover',
			array( __CLASS__, 'admin_page' )
		);
	}

	/**
	 * Render the Tools > Em Dash Remover management & database cleaning page.
	 */
	public static function admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$nonce = wp_create_nonce( 'em_dash_remover_action' );
		?>
		<div class="wrap" style="max-width:850px;">
			<h1>Em Dash Remover <span style="font-size:0.5em;color:#666;font-weight:normal;">v<?php echo esc_html( self::VERSION ); ?></span></h1>
			
			<div class="notice notice-info inline" style="margin-top:15px;padding:12px 15px;">
				<p style="margin:0;font-size:14px;line-height:1.6;">
					<strong>How it works:</strong> You can either keep this plugin active as a <strong>live runtime filter</strong>, or run the <strong>1-Click Permanent Database Cleaner</strong> below to permanently update all posts and pages in your database so you can safely uninstall the plugin afterwards.
				</p>
			</div>

			<!-- Card: Permanent Database Cleaner -->
			<div class="card" style="margin-top:20px;padding:20px;border-left:4px solid #2271b1;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
				<h2 style="margin-top:0;font-size:18px;color:#1d2327;">💾 1-Click Permanent Database Cleaner</h2>
				<p style="color:#555;font-size:13.5px;line-height:1.5;">
					This tool scans all your <strong>Posts, Pages, Titles, Excerpts, and Elementor builder content</strong> and permanently replaces all em dashes (<code>—</code>) and en dashes (<code>–</code>) with normal hyphens (<code>-</code>) directly in your WordPress database.
				</p>
				<p style="color:#2271b1;font-weight:600;">
					✅ Once cleaned, the changes remain in your database permanently, even if you deactivate or delete this plugin!
				</p>

				<div style="margin:15px 0;">
					<label style="display:block;margin-bottom:8px;">
						<input type="checkbox" id="em_clean_content" checked /> Clean Post &amp; Page Content
					</label>
					<label style="display:block;margin-bottom:8px;">
						<input type="checkbox" id="em_clean_titles" checked /> Clean Post &amp; Page Titles and Excerpts
					</label>
					<label style="display:block;margin-bottom:8px;">
						<input type="checkbox" id="em_clean_elementor" checked /> Clean Elementor &amp; Page Builder metadata (if present)
					</label>
				</div>

				<div style="margin-top:15px;">
					<button type="button" id="em-dash-start-clean" class="button button-primary button-hero" style="font-size:15px;height:42px;line-height:40px;">
						🚀 Clean All Content in Database Permanently
					</button>
				</div>

				<!-- Progress Container -->
				<div id="em-dash-progress-wrap" style="display:none;margin-top:20px;">
					<div style="background:#e0e0e0;border-radius:4px;height:24px;overflow:hidden;position:relative;">
						<div id="em-dash-progress-bar" style="background:#2271b1;height:100%;width:0%;transition:width 0.3s ease;"></div>
						<span id="em-dash-progress-text" style="position:absolute;top:0;left:0;right:0;text-align:center;line-height:24px;color:#fff;font-weight:bold;font-size:12px;text-shadow:0 1px 2px rgba(0,0,0,0.4);">0%</span>
					</div>
					<div id="em-dash-log" style="margin-top:12px;max-height:160px;overflow-y:auto;background:#f6f7f7;border:1px solid #ccd0d4;padding:10px;font-family:monospace;font-size:12px;border-radius:3px;"></div>
				</div>
			</div>

			<!-- Card: Live Runtime Status -->
			<div class="card" style="margin-top:20px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
				<h2 style="margin-top:0;font-size:18px;color:#1d2327;">⚡ Live Runtime Filter Status</h2>
				<p style="color:#555;">When this plugin is active, it automatically replaces dashes in public rendered HTML on the fly with zero database impact:</p>
				<table class="widefat striped" style="margin-top:10px;">
					<tbody>
						<tr>
							<td style="width:220px;"><strong>Em Dash (— &amp; entities)</strong></td>
							<td><code>—</code>, <code>&amp;mdash;</code>, <code>&amp;#8212;</code>, <code>&amp;#x2014;</code> &nbsp; → &nbsp; <code>-</code></td>
						</tr>
						<tr>
							<td><strong>En Dash (– &amp; entities)</strong></td>
							<td><code>–</code>, <code>&amp;ndash;</code>, <code>&amp;#8211;</code>, <code>&amp;#x2013;</code> &nbsp; → &nbsp; <code>-</code></td>
						</tr>
						<tr>
							<td><strong>Code Snippets &amp; Scripts</strong></td>
							<td><span style="color:#008a20;font-weight:600;">Strictly Protected (Untouched)</span></td>
						</tr>
						<tr>
							<td><strong>HTML Attributes &amp; URLs</strong></td>
							<td><span style="color:#008a20;font-weight:600;">Strictly Protected (Untouched)</span></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<script>
		(function($) {
			$('#em-dash-start-clean').on('click', function(e) {
				e.preventDefault();
				if (!confirm('This will permanently replace em dashes and en dashes in your database content with normal hyphens (-). It is recommended to have a site backup. Do you want to proceed?')) {
					return;
				}

				var $btn = $(this);
				var $progressWrap = $('#em-dash-progress-wrap');
				var $progressBar = $('#em-dash-progress-bar');
				var $progressText = $('#em-dash-progress-text');
				var $log = $('#em-dash-log');

				$btn.prop('disabled', true).text('⏳ Preparing Content...');
				$progressWrap.show();
				$progressBar.css('width', '0%');
				$progressText.text('0%');
				$log.html('<div>Fetching post list from database...</div>');

				var cleanContent = $('#em_clean_content').is(':checked') ? 1 : 0;
				var cleanTitles = $('#em_clean_titles').is(':checked') ? 1 : 0;
				var cleanElementor = $('#em_clean_elementor').is(':checked') ? 1 : 0;

				$.post(ajaxurl, {
					action: 'em_dash_remover_get_posts',
					nonce: '<?php echo esc_js( $nonce ); ?>'
				}, function(response) {
					if (!response.success || !response.data.posts) {
						$log.append('<div style="color:red;">Failed to retrieve posts. ' + (response.data || '') + '</div>');
						$btn.prop('disabled', false).text('🚀 Clean All Content in Database Permanently');
						return;
					}

					var postIds = response.data.posts;
					var total = postIds.length;
					if (total === 0) {
						$progressBar.css('width', '100%');
						$progressText.text('100%');
						$log.append('<div style="color:green;font-weight:bold;">No posts found to clean!</div>');
						$btn.prop('disabled', false).text('🚀 Clean All Content in Database Permanently');
						return;
					}

					$log.append('<div>Found ' + total + ' posts/pages to check. Processing in batches...</div>');

					var batchSize = 20;
					var processedCount = 0;
					var totalUpdated = 0;
					var totalReplacements = 0;

					function processNextBatch(startIndex) {
						if (startIndex >= total) {
							$progressBar.css('width', '100%');
							$progressText.text('100% Completed');
							$log.append('<div style="color:#008a20;font-weight:bold;margin-top:6px;">🎉 Complete! Scanned ' + total + ' posts. Permanently updated ' + totalUpdated + ' posts with ' + totalReplacements + ' replacements saved to database.</div>');
							$log.append('<div style="color:#2271b1;margin-top:4px;">You can now safely deactivate or uninstall the plugin, and your changes will remain permanently!</div>');
							$btn.prop('disabled', false).text('✅ Database Clean Complete (Run Again)');
							return;
						}

						var batch = postIds.slice(startIndex, startIndex + batchSize);
						$btn.text('⏳ Processing ' + (startIndex + 1) + ' - ' + Math.min(startIndex + batchSize, total) + ' of ' + total + '...');

						$.post(ajaxurl, {
							action: 'em_dash_remover_process_batch',
							nonce: '<?php echo esc_js( $nonce ); ?>',
							post_ids: batch,
							clean_content: cleanContent,
							clean_titles: cleanTitles,
							clean_elementor: cleanElementor
						}, function(res) {
							if (res.success && res.data) {
								processedCount += batch.length;
								totalUpdated += res.data.updated_posts || 0;
								totalReplacements += res.data.replacements || 0;

								var percent = Math.min(100, Math.round((processedCount / total) * 100));
								$progressBar.css('width', percent + '%');
								$progressText.text(percent + '% (' + processedCount + '/' + total + ')');

								$log.append('<div>Batch (' + (startIndex + 1) + '-' + Math.min(startIndex + batchSize, total) + '): Updated ' + (res.data.updated_posts || 0) + ' posts (' + (res.data.replacements || 0) + ' replacements)</div>');
								$log.scrollTop($log[0].scrollHeight);
							} else {
								$log.append('<div style="color:orange;">Warning: Batch error on posts ' + batch.join(',') + '</div>');
							}
							processNextBatch(startIndex + batchSize);
						}).fail(function() {
							$log.append('<div style="color:red;">Error processing batch starting at ' + startIndex + '. Retrying next batch...</div>');
							processNextBatch(startIndex + batchSize);
						});
					}

					processNextBatch(0);
				}).fail(function() {
					$log.append('<div style="color:red;">AJAX request failed. Please check permissions or connection.</div>');
					$btn.prop('disabled', false).text('🚀 Clean All Content in Database Permanently');
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * AJAX Handler: Get all post IDs for public post types.
	 */
	public static function ajax_get_posts() {
		check_ajax_referer( 'em_dash_remover_action', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		global $wpdb;

		// Get all public post types.
		$post_types = get_post_types( array( 'public' => true ) );
		if ( empty( $post_types ) ) {
			$post_types = array( 'post', 'page' );
		}

		$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
		$query        = $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type IN ($placeholders) AND post_status NOT IN ('trash', 'auto-draft', 'inherit') ORDER BY ID ASC",
			array_values( $post_types )
		);

		$post_ids = $wpdb->get_col( $query );

		wp_send_json_success( array( 'posts' => array_map( 'intval', $post_ids ) ) );
	}

	/**
	 * AJAX Handler: Process a batch of post IDs and permanently update database.
	 */
	public static function ajax_process_batch() {
		check_ajax_referer( 'em_dash_remover_action', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$post_ids        = isset( $_POST['post_ids'] ) && is_array( $_POST['post_ids'] ) ? array_map( 'intval', $_POST['post_ids'] ) : array();
		$clean_content   = ! empty( $_POST['clean_content'] );
		$clean_titles    = ! empty( $_POST['clean_titles'] );
		$clean_elementor = ! empty( $_POST['clean_elementor'] );

		if ( empty( $post_ids ) ) {
			wp_send_json_success( array( 'updated_posts' => 0, 'replacements' => 0 ) );
		}

		global $wpdb;
		$updated_posts      = 0;
		$total_replacements = 0;

		$targets     = apply_filters( 'em_dash_remover_targets', self::DEFAULT_TARGETS );
		$replacement = apply_filters( 'em_dash_remover_replacement', self::DEFAULT_REPLACEMENT );

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				continue;
			}

			$post_modified = false;
			$data_to_update = array();

			// 1. Clean Post Content
			if ( $clean_content && ! empty( $post->post_content ) ) {
				$cleaned_content = self::sanitize_text_nodes( $post->post_content, $targets, $replacement, $replacements_count );
				if ( $cleaned_content !== $post->post_content ) {
					$data_to_update['post_content'] = $cleaned_content;
					$total_replacements            += $replacements_count;
					$post_modified                  = true;
				}
			}

			// 2. Clean Post Title & Excerpt
			if ( $clean_titles ) {
				if ( ! empty( $post->post_title ) ) {
					$cleaned_title = self::sanitize_text_nodes( $post->post_title, $targets, $replacement, $t_count );
					if ( $cleaned_title !== $post->post_title ) {
						$data_to_update['post_title'] = $cleaned_title;
						$total_replacements          += $t_count;
						$post_modified                = true;
					}
				}

				if ( ! empty( $post->post_excerpt ) ) {
					$cleaned_excerpt = self::sanitize_text_nodes( $post->post_excerpt, $targets, $replacement, $e_count );
					if ( $cleaned_excerpt !== $post->post_excerpt ) {
						$data_to_update['post_excerpt'] = $cleaned_excerpt;
						$total_replacements            += $e_count;
						$post_modified                  = true;
					}
				}
			}

			// Update wp_posts directly in DB to prevent unintended hook triggers.
			if ( ! empty( $data_to_update ) ) {
				$wpdb->update(
					$wpdb->posts,
					$data_to_update,
					array( 'ID' => $post_id )
				);
				clean_post_cache( $post_id );
			}

			// 3. Clean Elementor Page Builder Data (if present)
			if ( $clean_elementor ) {
				$elementor_raw = get_post_meta( $post_id, '_elementor_data', true );
				if ( ! empty( $elementor_raw ) ) {
					$elementor_data = is_array( $elementor_raw ) ? $elementor_raw : json_decode( $elementor_raw, true );
					if ( is_array( $elementor_data ) ) {
						$elem_replacements = 0;
						$cleaned_elem_data = self::sanitize_elementor_data_recursive( $elementor_data, $targets, $replacement, $elem_replacements );
						if ( $elem_replacements > 0 ) {
							update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $cleaned_elem_data ) ) );
							$total_replacements += $elem_replacements;
							$post_modified       = true;
						}
					}
				}
			}

			if ( $post_modified ) {
				$updated_posts++;
			}
		}

		wp_send_json_success( array(
			'updated_posts' => $updated_posts,
			'replacements'  => $total_replacements,
		) );
	}

	/**
	 * Recursively sanitize Elementor JSON arrays.
	 *
	 * @param mixed $data Data element.
	 * @param array $targets Search targets.
	 * @param string $replacement Replacement string.
	 * @param int $replacements Output count.
	 * @return mixed Cleaned data.
	 */
	private static function sanitize_elementor_data_recursive( $data, $targets, $replacement, &$replacements ) {
		if ( is_array( $data ) ) {
			$cleaned = array();
			foreach ( $data as $key => $val ) {
				// Avoid altering url keys or icon names.
				if ( in_array( $key, array( 'url', 'icon', 'custom_css', 'id', 'elType', 'widgetType' ), true ) ) {
					$cleaned[ $key ] = $val;
				} else {
					$cleaned[ $key ] = self::sanitize_elementor_data_recursive( $val, $targets, $replacement, $replacements );
				}
			}
			return $cleaned;
		} elseif ( is_string( $data ) && '' !== $data ) {
			$cleaned_str = self::sanitize_text_nodes( $data, $targets, $replacement, $count );
			$replacements += $count;
			return $cleaned_str;
		}
		return $data;
	}

	/**
	 * Core sanitization method: preserves tags, attributes, scripts, styles, code blocks, SVGs, etc.
	 *
	 * @param string $html Input text or HTML.
	 * @param array $targets Targets to replace.
	 * @param string $replacement Replacement string.
	 * @param int $replacements Count of replacements made.
	 * @return string Processed text.
	 */
	public static function sanitize_text_nodes( $html, $targets, $replacement, &$replacements = 0 ) {
		$replacements = 0;
		if ( ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		// Quick check before running regex.
		$found = false;
		foreach ( $targets as $target ) {
			if ( strpos( $html, $target ) !== false ) {
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			return $html;
		}

		$protected = array();
		$counter   = 0;

		$protect = function( $matches ) use ( &$protected, &$counter ) {
			$key               = '___EM_DASH_REMOVER_PROTECTED_' . $counter . '___';
			$protected[ $key ] = $matches[0];
			$counter++;
			return $key;
		};

		$pattern = '/(<!--.*?-->|<script\b[^>]*>.*?<\/script\s*>|<style\b[^>]*>.*?<\/style\s*>|<pre\b[^>]*>.*?<\/pre\s*>|<code\b[^>]*>.*?<\/code\s*>|<textarea\b[^>]*>.*?<\/textarea\s*>|<svg\b[^>]*>.*?<\/svg\s*>|<kbd\b[^>]*>.*?<\/kbd\s*>|<samp\b[^>]*>.*?<\/samp\s*>|<var\b[^>]*>.*?<\/var\s*>|<\[[^\]]+\]>|<[^>]+>)/is';

		$processed = preg_replace_callback( $pattern, $protect, $html );
		if ( null === $processed || false === $processed ) {
			return $html;
		}

		// Count replacements.
		foreach ( $targets as $t ) {
			$replacements += substr_count( $processed, $t );
		}

		$processed = str_replace( $targets, $replacement, $processed );

		if ( ! empty( $protected ) ) {
			$processed = strtr( $processed, $protected );
		}

		return $processed;
	}

	/**
	 * Start output buffer on public frontend requests.
	 */
	public static function start_output_buffer() {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || wp_doing_cron() ) {
			return;
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}

		if ( is_feed() || is_robots() || is_trackback() || is_favicon() ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path        = (string) wp_parse_url( $request_uri, PHP_URL_PATH );

		if ( preg_match( '#/(feed|xmlrpc\.php)(/|$)#i', $path ) ) {
			return;
		}

		if ( ! apply_filters( 'em_dash_remover_enabled', true ) ) {
			return;
		}

		ob_start( array( __CLASS__, 'process_html' ) );
	}

	/**
	 * Process rendered HTML at runtime.
	 *
	 * @param string $html Rendered HTML.
	 * @return string Processed HTML.
	 */
	public static function process_html( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		if ( stripos( $html, '<html' ) === false && stripos( $html, '<!doctype html' ) === false ) {
			return $html;
		}

		$targets     = apply_filters( 'em_dash_remover_targets', self::DEFAULT_TARGETS );
		$replacement = apply_filters( 'em_dash_remover_replacement', self::DEFAULT_REPLACEMENT );

		return self::sanitize_text_nodes( $html, $targets, $replacement );
	}
}

Em_Dash_Remover::init();
