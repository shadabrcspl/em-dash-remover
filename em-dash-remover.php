<?php
/**
 * Plugin Name:       Em Dash Remover
 * Plugin URI:        https://github.com/shadabrcspl/em-dash-remover
 * Description:       Replaces AI-telltale em dashes (—, &mdash;, &#8212;, &#x2014;) with normal hyphens (-) in rendered public HTML. Leaves database, Elementor data, code blocks, scripts, styles, textareas, and SVGs completely untouched.
 * Version:           3.1.0
 * Requires at least: 5.0
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
	 * Default target em-dash characters and HTML entities.
	 *
	 * @var array<int, string>
	 */
	private const DEFAULT_TARGETS = array(
		'—',            // UTF-8 Literal Em Dash (U+2014)
		'&mdash;',      // Lowercase HTML named entity
		'&MDASH;',      // Uppercase HTML named entity
		'&#8212;',      // Decimal HTML entity
		'&#x2014;',     // Lowercase hexadecimal HTML entity
		'&#x02014;',    // Padded lowercase hexadecimal HTML entity
		'&#X2014;',     // Uppercase hexadecimal HTML entity
		'&#X02014;',    // Padded uppercase hexadecimal HTML entity
	);

	/**
	 * Default replacement character.
	 *
	 * @var string
	 */
	private const DEFAULT_REPLACEMENT = '-';

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'start_output_buffer' ), 0 );
	}

	/**
	 * Start the output buffer on public frontend requests.
	 *
	 * @return void
	 */
	public function start_output_buffer() {
		// Do not run in admin, AJAX, REST API, CLI, or CRON contexts.
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		if ( ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		// Do not process feeds, robots.txt, trackbacks, or non-HTML endpoints.
		if ( is_feed() || is_robots() || is_trackback() || is_favicon() ) {
			return;
		}

		// Additional request URI checks for feeds, sitemaps, and raw assets.
		$request_uri = isset( $_SERVER['REQUEST_URI'] )
			? strtolower( (string) $_SERVER['REQUEST_URI'] )
			: '';

		if (
			strpos( $request_uri, 'feed' ) !== false ||
			strpos( $request_uri, '.xml' ) !== false ||
			strpos( $request_uri, '.json' ) !== false ||
			strpos( $request_uri, 'wp-json' ) !== false
		) {
			return;
		}

		// Allow developers to conditionally bypass buffer.
		if ( ! apply_filters( 'em_dash_remover_enabled', true ) ) {
			return;
		}

		ob_start( array( $this, 'replace_rendered_text' ) );
	}

	/**
	 * Replace em dashes in rendered HTML text nodes while preserving tags, attributes, and protected blocks.
	 *
	 * @param string $html The buffered HTML output.
	 * @return string The processed HTML.
	 */
	public function replace_rendered_text( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		/**
		 * Filters the list of target em dash characters and entities to search for.
		 *
		 * @param array<int, string> $targets Array of search patterns.
		 */
		$targets = apply_filters( 'em_dash_remover_targets', self::DEFAULT_TARGETS );

		if ( ! is_array( $targets ) || empty( $targets ) ) {
			return $html;
		}

		// Quick check: If none of the targets are in the HTML, return early without regex splitting.
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
		 * Filters the replacement string used for em dashes.
		 *
		 * @param string $replacement Replacement string (default: '-').
		 */
		$replacement = apply_filters( 'em_dash_remover_replacement', self::DEFAULT_REPLACEMENT );

		/**
		 * Regex splits HTML into protected blocks vs plain text nodes:
		 * - HTML comments: <!-- ... -->
		 * - Scripts & JSON-LD: <script> ... </script>
		 * - Stylesheets: <style> ... </style>
		 * - Preformatted text: <pre> ... </pre>
		 * - Code blocks: <code> ... </code>
		 * - Textareas: <textarea> ... </textarea>
		 * - SVGs: <svg> ... </svg>
		 * - Keyboard/Sample/Variable blocks: <kbd>, <samp>, <var>
		 * - All other HTML tags: <...>
		 */
		$split_pattern = '/(<!--.*?-->|<script\b[^>]*>.*?<\/script\s*>|<style\b[^>]*>.*?<\/style\s*>|<pre\b[^>]*>.*?<\/pre\s*>|<code\b[^>]*>.*?<\/code\s*>|<textarea\b[^>]*>.*?<\/textarea\s*>|<svg\b[^>]*>.*?<\/svg\s*>|<kbd\b[^>]*>.*?<\/kbd\s*>|<samp\b[^>]*>.*?<\/samp\s*>|<var\b[^>]*>.*?<\/var\s*>|<[^>]+>)/is';

		$parts = preg_split(
			$split_pattern,
			$html,
			-1,
			PREG_SPLIT_DELIM_CAPTURE
		);

		// Fail-safe: if regex split fails or hits PCRE backtrack limits, return original HTML untouched.
		if ( false === $parts ) {
			return $html;
		}

		foreach ( $parts as $index => $part ) {
			if ( '' === $part ) {
				continue;
			}

			// Skip HTML tags, comments, scripts, code, and other protected blocks.
			if ( isset( $part[0] ) && '<' === $part[0] ) {
				continue;
			}

			// Replace all target em dash representations in text node.
			$parts[ $index ] = str_replace(
				$targets,
				$replacement,
				$part
			);
		}

		return implode( '', $parts );
	}
}

// Initialize the plugin.
Em_Dash_Remover::get_instance();
