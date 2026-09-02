<?php
/**
 * Plugin Name:       Em Dash Remover
 * Plugin URI:        https://github.com/shadabrcspl/em-dash-remover
 * Description:       Replaces em dash (—) and en dash (–) along with HTML entities (&mdash;, &ndash;, &#8212;, &#8211;) with normal hyphens (-) in rendered public HTML. Leaves database, Elementor data, code blocks, scripts, styles, textareas, and SVGs completely untouched.
 * Version:           4.1.0
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
	const VERSION = '4.1.0';

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
	 * Render the Tools > Em Dash Remover status page.
	 */
	public static function admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1>Em Dash Remover <span style="font-size:0.5em;color:#666;font-weight:normal;">v<?php echo esc_html( self::VERSION ); ?></span></h1>
			<p><strong>Status: Active.</strong> Automatically replaces em dashes and en dashes with standard hyphens in rendered public HTML.</p>
			
			<table class="widefat striped" style="max-width:760px;margin-top:15px;">
				<thead>
					<tr>
						<th style="width:220px;">Target / Setting</th>
						<th>Behavior</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong>Em Dash (— &amp; entities)</strong></td>
						<td><code>—</code>, <code>&amp;mdash;</code>, <code>&amp;#8212;</code>, <code>&amp;#x2014;</code> &nbsp; → &nbsp; <code>-</code></td>
					</tr>
					<tr>
						<td><strong>En Dash (– &amp; entities)</strong></td>
						<td><code>–</code>, <code>&amp;ndash;</code>, <code>&amp;#8211;</code>, <code>&amp;#x2013;</code> &nbsp; → &nbsp; <code>-</code></td>
					</tr>
					<tr>
						<td><strong>Database &amp; Posts</strong></td>
						<td><span style="color:#008a20;font-weight:600;">Not modified (100% Safe)</span></td>
					</tr>
					<tr>
						<td><strong>Elementor / Page Builders</strong></td>
						<td><span style="color:#008a20;font-weight:600;">Not modified</span></td>
					</tr>
					<tr>
						<td><strong>HTML Attributes &amp; URLs</strong></td>
						<td><span style="color:#008a20;font-weight:600;">Protected (Untouched)</span></td>
					</tr>
					<tr>
						<td><strong>Scripts, Styles &amp; Code Blocks</strong></td>
						<td><span style="color:#008a20;font-weight:600;">Protected (Untouched)</span></td>
					</tr>
					<tr>
						<td><strong>SVGs &amp; Textareas</strong></td>
						<td><span style="color:#008a20;font-weight:600;">Protected (Untouched)</span></td>
					</tr>
				</tbody>
			</table>
			<p style="margin-top:18px;color:#555;">The replacement occurs dynamically during output rendering for public visitors without altering saved content.</p>
		</div>
		<?php
	}

	/**
	 * Start output buffer on public frontend requests.
	 */
	public static function start_output_buffer() {
		// Do not run in admin, AJAX, REST API, CLI, or CRON contexts.
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || wp_doing_cron() ) {
			return;
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}

		// Do not process feeds, robots.txt, trackbacks, or non-HTML endpoints.
		if ( is_feed() || is_robots() || is_trackback() || is_favicon() ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path        = (string) wp_parse_url( $request_uri, PHP_URL_PATH );

		if ( preg_match( '#/(feed|xmlrpc\.php)(/|$)#i', $path ) ) {
			return;
		}

		// Allow developers to conditionally bypass output buffer.
		if ( ! apply_filters( 'em_dash_remover_enabled', true ) ) {
			return;
		}

		ob_start( array( __CLASS__, 'process_html' ) );
	}

	/**
	 * Process rendered HTML to replace em/en dashes in visible text.
	 *
	 * @param string $html Rendered HTML.
	 * @return string Processed HTML.
	 */
	public static function process_html( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		// Only process actual HTML documents.
		if ( stripos( $html, '<html' ) === false && stripos( $html, '<!doctype html' ) === false ) {
			return $html;
		}

		/**
		 * Filters the target characters and entities.
		 *
		 * @param array<int, string> $targets Search patterns.
		 */
		$targets = apply_filters( 'em_dash_remover_targets', self::DEFAULT_TARGETS );

		if ( ! is_array( $targets ) || empty( $targets ) ) {
			return $html;
		}

		// Fast early-exit: check if any target character or entity exists before running regex callbacks.
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

		/**
		 * Filters the replacement string used for em/en dashes.
		 *
		 * @param string $replacement Replacement string (default: '-').
		 */
		$replacement = apply_filters( 'em_dash_remover_replacement', self::DEFAULT_REPLACEMENT );

		$protected = array();
		$counter   = 0;

		$protect = function( $matches ) use ( &$protected, &$counter ) {
			$key               = '___EM_DASH_REMOVER_PROTECTED_' . $counter . '___';
			$protected[ $key ] = $matches[0];
			$counter++;
			return $key;
		};

		/**
		 * Single-pass combined regex to protect:
		 * - HTML comments
		 * - Scripts & JSON-LD
		 * - Stylesheets
		 * - Preformatted text & Code snippets (<pre>, <code>, <kbd>, <samp>, <var>)
		 * - Textareas
		 * - SVGs
		 * - All HTML tags and attributes (<...>)
		 */
		$pattern = '/(<!--.*?-->|<script\b[^>]*>.*?<\/script\s*>|<style\b[^>]*>.*?<\/style\s*>|<pre\b[^>]*>.*?<\/pre\s*>|<code\b[^>]*>.*?<\/code\s*>|<textarea\b[^>]*>.*?<\/textarea\s*>|<svg\b[^>]*>.*?<\/svg\s*>|<kbd\b[^>]*>.*?<\/kbd\s*>|<samp\b[^>]*>.*?<\/samp\s*>|<var\b[^>]*>.*?<\/var\s*>|<[^>]+>)/is';

		$processed = preg_replace_callback( $pattern, $protect, $html );

		// Fail-safe: if regex encounters PCRE backtrack error, return original HTML untouched.
		if ( null === $processed || false === $processed ) {
			return $html;
		}

		// Replace target dashes in unprotected text nodes.
		$processed = str_replace( $targets, $replacement, $processed );

		// Restore protected sections in a single fast pass.
		if ( ! empty( $protected ) ) {
			$processed = strtr( $processed, $protected );
		}

		return $processed;
	}
}

Em_Dash_Remover::init();
